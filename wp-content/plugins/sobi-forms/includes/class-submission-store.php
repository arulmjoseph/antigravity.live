<?php
/**
 * Submission persistence layer (insert gated per form; inbox always available).
 *
 * @package SobiForms
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom plugin tables; table names come from Sobiforms_DB only.

/**
 * CRUD for the sobiforms_submissions custom table.
 */
class Sobiforms_Submission_Store {

	const MAX_NOTE_LENGTH = 2000;
	const SPAM_HAM        = 'ham';
	const SPAM_SPAM       = 'spam';
	const UNREAD_COUNT_TRANSIENT = 'sobiforms_unread_count';
	const UNREAD_COUNT_TTL       = 45;
	const MAX_SOURCE_TITLE       = 255;
	const MAX_SOURCE_PATH        = 2048;

	/**
	 * Register cron hook for async retention purge.
	 */
	public static function init() {
		add_action( 'sobiforms_purge_old', array( __CLASS__, 'run_purge_old' ) );
	}

	/**
	 * Bust cached unread count used for the admin menu badge.
	 */
	public static function bust_unread_count_cache() {
		delete_transient( self::UNREAD_COUNT_TRANSIENT );
	}

	/**
	 * Cached unread count for admin menu badge (short TTL).
	 *
	 * @return int
	 */
	public static function get_cached_unread_count() {
		$cached = get_transient( self::UNREAD_COUNT_TRANSIENT );
		if ( false !== $cached ) {
			return (int) $cached;
		}

		$count = self::count_unread();
		set_transient( self::UNREAD_COUNT_TRANSIENT, $count, self::UNREAD_COUNT_TTL );

		return $count;
	}

	/**
	 * Insert a new submission row.
	 *
	 * @param array      $data Submission data.
	 * @param array|null $form Optional hydrated form (avoids extra DB read).
	 * @return int|false
	 */
	public static function insert( $data, $form = null ) {
		$form_id = absint( $data['form_id'] ?? 0 );
		if ( ! $form_id ) {
			return false;
		}

		if ( null === $form ) {
			if ( ! Sobiforms_Form_Repository::form_saves_submissions( $form_id ) ) {
				return false;
			}
		} elseif ( ! Sobiforms_Form_Repository::form_saves_submissions( $form ) ) {
			return false;
		}

		global $wpdb;

		$spam_status = self::sanitize_spam_status( $data['spam_status'] ?? self::SPAM_HAM );

		$inserted = $wpdb->insert(
			Sobiforms_DB::table_name(),
			array(
				'form_id'     => absint( $data['form_id'] ?? 0 ),
				'name'        => sanitize_text_field( $data['name'] ?? '' ),
				'email'       => sanitize_email( $data['email'] ?? '' ),
				'message'     => sanitize_textarea_field( $data['message'] ?? '' ),
				'data'        => wp_json_encode( $data['values'] ?? array() ),
				'mail_status' => 'pending',
				'is_read'     => 0,
				'ip_hash'     => $data['ip_hash'] ?? '',
				'spam_status'  => $spam_status,
				'source_title' => self::sanitize_source_title( $data['source_title'] ?? '' ),
				'source_path'  => self::sanitize_source_path( $data['source_path'] ?? '' ),
				'created_at'   => current_time( 'mysql' )),
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( ! $inserted ) {
			return false;
		}

		self::bust_unread_count_cache();
		self::schedule_purge_old( $form_id );

		return (int) $wpdb->insert_id;
	}

	/**
	 * @param int    $id     Row ID.
	 * @param string $status sent|failed.
	 */
	public static function update_mail_status( $id, $status ) {
		if ( ! $id ) {
			return;
		}

		global $wpdb;
		$wpdb->update(
			Sobiforms_DB::table_name(),
			array( 'mail_status' => $status ),
			array( 'id' => $id ),
			array( '%s' ),
			array( '%d' )
		);
	}

	/**
	 * Replace stored field values JSON for a submission.
	 *
	 * @param int   $id     Submission ID.
	 * @param array $values Field values map.
	 * @return bool
	 */
	public static function update_values( $id, $values ) {
		if ( ! $id || ! is_array( $values ) ) {
			return false;
		}

		global $wpdb;
		$updated = $wpdb->update(
			Sobiforms_DB::table_name(),
			array( 'data' => wp_json_encode( $values ) ),
			array( 'id' => absint( $id ) ),
			array( '%s' ),
			array( '%d' )
		);

		return false !== $updated;
	}

	/**
	 * @param int    $form_id      Filter by form (0 = all).
	 * @param int    $limit        Max rows.
	 * @param string $search       Optional search on submission content or form title.
	 * @param bool   $unread_only   Only unread rows.
	 * @param bool   $starred_only  Only starred rows.
	 * @param bool   $spam_only        Only spam rows.
	 * @param bool   $read_only        Only read rows.
	 * @param bool   $not_starred_only Only not-starred rows.
	 * @return array
	 */
	public static function get_all( $form_id = 0, $limit = 100, $search = '', $unread_only = false, $starred_only = false, $spam_only = false, $offset = 0, $read_only = false, $not_starred_only = false ) {
		return self::get_list( $form_id, $limit, $search, $unread_only, $starred_only, $spam_only, $offset, $read_only, $not_starred_only, true );
	}

	/**
	 * List submissions for inbox (light rows without data longtext by default).
	 *
	 * @param int    $form_id          Filter by form (0 = all).
	 * @param int    $limit            Max rows.
	 * @param string $search           Optional search on submission content or form title.
	 * @param bool   $unread_only      Only unread rows.
	 * @param bool   $starred_only     Only starred rows.
	 * @param bool   $spam_only        Only spam rows.
	 * @param int    $offset           Pagination offset.
	 * @param bool   $read_only        Only read rows.
	 * @param bool   $not_starred_only Only not-starred rows.
	 * @param bool   $include_data     Include full data longtext (detail views).
	 * @return array
	 */
	public static function get_list( $form_id = 0, $limit = 100, $search = '', $unread_only = false, $starred_only = false, $spam_only = false, $offset = 0, $read_only = false, $not_starred_only = false, $include_data = false ) {
		global $wpdb;
		$table  = Sobiforms_DB::table_name();
		$limit  = absint( $limit );
		$offset = absint( $offset );
		$parts  = self::list_query_parts( $form_id, $search, $unread_only, $starred_only, $spam_only, $read_only, $not_starred_only );

		$columns = $include_data
			? 's.*'
			: 's.id, s.form_id, s.name, s.email, s.message, s.is_read, s.is_starred, s.spam_status, s.mail_status, s.created_at';

		$sql = "SELECT {$columns} FROM {$table} s{$parts['join']}";
		if ( ! empty( $parts['where'] ) ) {
			$sql .= ' WHERE ' . implode( ' AND ', $parts['where'] );
		}
		$sql     .= ' ORDER BY s.created_at DESC LIMIT %d OFFSET %d';
		$args     = array_merge( $parts['args'], array( $limit, $offset ) );

		$hydrate = $include_data ? 'hydrate_row' : 'hydrate_list_row';

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return array_map( array( __CLASS__, $hydrate ), $wpdb->get_results( $wpdb->prepare( $sql, ...$args ), ARRAY_A ) );
	}

	/**
	 * Inbox tab counts + current filter total in one aggregate query.
	 *
	 * @param int    $form_id          Filter by form (0 = all).
	 * @param string $search           Optional search.
	 * @param bool   $unread_only      Current list: unread only.
	 * @param bool   $starred_only     Current list: starred only.
	 * @param bool   $spam_only        Current list: spam only.
	 * @param bool   $read_only        Current list: read only.
	 * @param bool   $not_starred_only Current list: not starred only.
	 * @return array{items_total: int, count_all: int, count_unread: int, count_starred: int, count_spam: int, total_submissions: int}
	 */
	public static function get_inbox_counts( $form_id = 0, $search = '', $unread_only = false, $starred_only = false, $spam_only = false, $read_only = false, $not_starred_only = false ) {
		global $wpdb;

		$table       = Sobiforms_DB::table_name();
		$forms_table = Sobiforms_DB::forms_table_name();
		$form_id     = absint( $form_id );
		$search      = sanitize_text_field( $search );
		$join        = '';
		$where       = array();
		$args        = array();

		if ( '' !== $search ) {
			$join    = " LEFT JOIN {$forms_table} f ON f.id = s.form_id ";
			$like    = '%' . $wpdb->esc_like( $search ) . '%';
			$where[] = '(s.name LIKE %s OR s.email LIKE %s OR s.message LIKE %s OR s.data LIKE %s OR s.mail_status LIKE %s OR s.admin_note LIKE %s OR f.title LIKE %s)';
			$args    = array_merge( $args, array( $like, $like, $like, $like, $like, $like, $like ) );
		}

		if ( $form_id ) {
			$where[] = 's.form_id = %d';
			$args[]  = $form_id;
		}

		$where_sql = ! empty( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '';
		$spam      = self::SPAM_SPAM;

		$sql = "SELECT
			SUM( CASE WHEN s.spam_status != %s THEN 1 ELSE 0 END ) AS count_all,
			SUM( CASE WHEN s.spam_status != %s AND s.is_read = 0 THEN 1 ELSE 0 END ) AS count_unread,
			SUM( CASE WHEN s.spam_status != %s AND s.is_starred = 1 THEN 1 ELSE 0 END ) AS count_starred,
			SUM( CASE WHEN s.spam_status = %s THEN 1 ELSE 0 END ) AS count_spam,
			SUM( CASE WHEN s.spam_status != %s AND s.is_read = 1 THEN 1 ELSE 0 END ) AS count_read,
			SUM( CASE WHEN s.spam_status != %s AND s.is_starred = 0 THEN 1 ELSE 0 END ) AS count_not_starred
			FROM {$table} s{$join}{$where_sql}";

		$prepare_args = array_merge( array( $spam, $spam, $spam, $spam, $spam, $spam ), $args );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$row = $wpdb->get_row( $wpdb->prepare( $sql, ...$prepare_args ), ARRAY_A );

		$count_all     = (int) ( $row['count_all'] ?? 0 );
		$count_unread  = (int) ( $row['count_unread'] ?? 0 );
		$count_starred = (int) ( $row['count_starred'] ?? 0 );
		$count_spam    = (int) ( $row['count_spam'] ?? 0 );
		$count_read    = (int) ( $row['count_read'] ?? 0 );
		$count_not_starred = (int) ( $row['count_not_starred'] ?? 0 );

		if ( $spam_only ) {
			$items_total = $count_spam;
		} elseif ( $unread_only ) {
			$items_total = $count_unread;
		} elseif ( $starred_only ) {
			$items_total = $count_starred;
		} elseif ( $read_only ) {
			$items_total = $count_read;
		} elseif ( $not_starred_only ) {
			$items_total = $count_not_starred;
		} else {
			$items_total = $count_all;
		}

		$total_submissions = ( 0 === $form_id && '' === $search )
			? $count_all
			: self::count_filtered( 0, '', false, false, false );

		return array(
			'items_total'       => $items_total,
			'count_all'         => $count_all,
			'count_unread'      => $count_unread,
			'count_starred'     => $count_starred,
			'count_spam'        => $count_spam,
			'total_submissions' => $total_submissions,
		);
	}

	/**
	 * Count submissions matching list filters (no row limit).
	 *
	 * @param int    $form_id      Filter by form (0 = all).
	 * @param string $search       Optional search.
	 * @param bool   $unread_only  Only unread rows.
	 * @param bool   $starred_only Only starred rows.
	 * @param bool   $spam_only        Only spam rows.
	 * @param bool   $read_only        Only read rows.
	 * @param bool   $not_starred_only Only not-starred rows.
	 * @return int
	 */
	public static function count_filtered( $form_id = 0, $search = '', $unread_only = false, $starred_only = false, $spam_only = false, $read_only = false, $not_starred_only = false ) {
		global $wpdb;
		$table = Sobiforms_DB::table_name();
		$parts = self::list_query_parts( $form_id, $search, $unread_only, $starred_only, $spam_only, $read_only, $not_starred_only );

		$sql = "SELECT COUNT(*) FROM {$table} s{$parts['join']}";
		if ( ! empty( $parts['where'] ) ) {
			$sql .= ' WHERE ' . implode( ' AND ', $parts['where'] );
		}

		if ( empty( $parts['args'] ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			return (int) $wpdb->get_var( $sql );
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->get_var( $wpdb->prepare( $sql, ...$parts['args'] ) );
	}

	/**
	 * Shared JOIN/WHERE fragments for submission list queries.
	 *
	 * @param int    $form_id      Filter by form (0 = all).
	 * @param string $search       Optional search.
	 * @param bool   $unread_only  Only unread rows.
	 * @param bool   $starred_only Only starred rows.
	 * @param bool   $spam_only        Only spam rows.
	 * @param bool   $read_only        Only read rows.
	 * @param bool   $not_starred_only Only not-starred rows.
	 * @return array{join: string, where: string[], args: array}
	 */
	private static function list_query_parts( $form_id, $search, $unread_only, $starred_only, $spam_only = false, $read_only = false, $not_starred_only = false ) {
		global $wpdb;

		$forms_table = Sobiforms_DB::forms_table_name();
		$form_id     = absint( $form_id );
		$search      = sanitize_text_field( $search );
		$join        = '';
		$where       = array();
		$args        = array();

		if ( '' !== $search ) {
			$join    = " LEFT JOIN {$forms_table} f ON f.id = s.form_id ";
			$like    = '%' . $wpdb->esc_like( $search ) . '%';
			$where[] = '(s.name LIKE %s OR s.email LIKE %s OR s.message LIKE %s OR s.data LIKE %s OR s.mail_status LIKE %s OR s.admin_note LIKE %s OR f.title LIKE %s)';
			$args    = array_merge( $args, array( $like, $like, $like, $like, $like, $like, $like ) );
		}

		if ( $unread_only ) {
			$where[] = 's.is_read = 0';
		}

		if ( $read_only ) {
			$where[] = 's.is_read = 1';
		}

		if ( $starred_only ) {
			$where[] = 's.is_starred = 1';
		}

		if ( $not_starred_only ) {
			$where[] = 's.is_starred = 0';
		}

		if ( $spam_only ) {
			$where[] = 's.spam_status = %s';
			$args[]  = self::SPAM_SPAM;
		} else {
			$where[] = 's.spam_status != %s';
			$args[]  = self::SPAM_SPAM;
		}

		if ( $form_id ) {
			$where[] = 's.form_id = %d';
			$args[]  = $form_id;
		}

		return array(
			'join'  => $join,
			'where' => $where,
			'args'  => $args);
	}

	/**
	 * Get a single submission by ID.
	 *
	 * @param int $id Submission ID.
	 * @return array|null
	 */
	public static function get_by_id( $id ) {
		if ( ! $id ) {
			return null;
		}

		global $wpdb;
		$table = Sobiforms_DB::table_name();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", absint( $id ) ),
			ARRAY_A
		);

		return $row ? self::hydrate_row( $row ) : null;
	}

	/**
	 * Toggle starred flag on a submission.
	 *
	 * @param int $id Submission ID.
	 * @return int|false New is_starred value (0|1), or false on failure.
	 */
	public static function toggle_starred( $id ) {
		$row = self::get_by_id( $id );
		if ( ! $row ) {
			return false;
		}

		global $wpdb;
		$new = $row['is_starred'] ? 0 : 1;

		$updated = $wpdb->update(
			Sobiforms_DB::table_name(),
			array( 'is_starred' => $new ),
			array( 'id' => $id ),
			array( '%d' ),
			array( '%d' )
		);

		return false !== $updated ? $new : false;
	}

	/**
	 * Format a stored field value for admin display.
	 *
	 * @param array $item Field item with type and value keys.
	 * @return string
	 */
	public static function format_field_display( $item ) {
		$type  = $item['type'] ?? 'text';
		$value = $item['value'] ?? '';

		if ( 'file' === $type ) {
			return (string) ( $item['original_name'] ?? '' );
		}

		if ( is_array( $value ) ) {
			$parts = array();
			foreach ( $value as $part ) {
				$part = (string) $part;
				if ( '' !== $part ) {
					$parts[] = $part;
				}
			}
			return implode( ' • ', $parts );
		}

		if ( 'checkbox' === $type ) {
			return $value ? __( 'Yes', 'sobi-forms' ) : __( 'No', 'sobi-forms' );
		}

		return (string) $value;
	}

	/**
	 * List values for multi-select fields (e.g. checkbox group).
	 *
	 * @param array $item Field item with type and value keys.
	 * @return string[]
	 */
	public static function format_field_display_list( $item ) {
		$value = $item['value'] ?? '';

		if ( ! is_array( $value ) ) {
			return array();
		}

		$parts = array();
		foreach ( $value as $part ) {
			$part = (string) $part;
			if ( '' !== $part ) {
				$parts[] = $part;
			}
		}

		return $parts;
	}

	/**
	 * @param int $form_id Filter by form (0 = all).
	 * @return int
	 */
	public static function count_unread( $form_id = 0, $search = '' ) {
		return self::count_filtered( $form_id, $search, true, false, false );
	}

	/**
	 * @param string $status Spam status slug.
	 * @return string
	 */
	public static function sanitize_spam_status( $status ) {
		return self::SPAM_SPAM === $status ? self::SPAM_SPAM : self::SPAM_HAM;
	}

	/**
	 * @param int    $id     Submission ID.
	 * @param string $status ham|spam.
	 * @return bool
	 */
	public static function update_spam_status( $id, $status ) {
		if ( ! $id ) {
			return false;
		}

		global $wpdb;
		$updated = $wpdb->update(
			Sobiforms_DB::table_name(),
			array( 'spam_status' => self::sanitize_spam_status( $status ) ),
			array( 'id' => $id ),
			array( '%s' ),
			array( '%d' )
		);

		return false !== $updated;
	}

	/**
	 * @param array $row Submission row.
	 * @return bool
	 */
	public static function is_spam( $row ) {
		return is_array( $row ) && self::SPAM_SPAM === ( $row['spam_status'] ?? self::SPAM_HAM );
	}

	/**
	 * @param int $id Submission ID.
	 * @return bool
	 */
	public static function mark_read( $id ) {
		if ( ! $id ) {
			return false;
		}

		global $wpdb;
		$updated = $wpdb->update(
			Sobiforms_DB::table_name(),
			array( 'is_read' => 1 ),
			array( 'id' => $id ),
			array( '%d' ),
			array( '%d' )
		);

		if ( false !== $updated && $updated > 0 ) {
			self::bust_unread_count_cache();
		}

		return false !== $updated && $updated > 0;
	}

	/**
	 * @param int $id Submission ID.
	 * @return bool
	 */
	public static function mark_unread( $id ) {
		if ( ! $id ) {
			return false;
		}

		global $wpdb;
		$updated = $wpdb->update(
			Sobiforms_DB::table_name(),
			array( 'is_read' => 0 ),
			array( 'id' => $id ),
			array( '%d' ),
			array( '%d' )
		);

		if ( false !== $updated && $updated > 0 ) {
			self::bust_unread_count_cache();
		}

		return false !== $updated && $updated > 0;
	}

	/**
	 * @param int  $id       Submission ID.
	 * @param bool $starred  Starred state.
	 * @return bool
	 */
	public static function set_starred( $id, $starred ) {
		if ( ! $id ) {
			return false;
		}

		global $wpdb;
		$updated = $wpdb->update(
			Sobiforms_DB::table_name(),
			array( 'is_starred' => $starred ? 1 : 0 ),
			array( 'id' => $id ),
			array( '%d' ),
			array( '%d' )
		);

		return false !== $updated;
	}

	/**
	 * Mark submissions matching list filters as read.
	 *
	 * @param int    $form_id          Filter by form (0 = all).
	 * @param string $search           Optional search.
	 * @param bool   $unread_only      Only unread rows.
	 * @param bool   $starred_only     Only starred rows.
	 * @param bool   $spam_only        Only spam rows.
	 * @param bool   $read_only        Only read rows.
	 * @param bool   $not_starred_only Only not-starred rows.
	 * @return int Rows updated.
	 */
	public static function mark_filtered_read( $form_id = 0, $search = '', $unread_only = false, $starred_only = false, $spam_only = false, $read_only = false, $not_starred_only = false ) {
		global $wpdb;
		$table = Sobiforms_DB::table_name();
		$parts = self::list_query_parts( $form_id, $search, $unread_only, $starred_only, $spam_only, $read_only, $not_starred_only );
		$where = array_merge( $parts['where'], array( 's.is_read = 0' ) );

		$sql = "UPDATE {$table} s{$parts['join']} SET s.is_read = 1 WHERE " . implode( ' AND ', $where );

		if ( empty( $parts['args'] ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$updated = (int) $wpdb->query( $sql );
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$updated = (int) $wpdb->query( $wpdb->prepare( $sql, ...$parts['args'] ) );
		}

		if ( $updated > 0 ) {
			self::bust_unread_count_cache();
		}

		return $updated;
	}

	/**
	 * Delete submissions matching list filters.
	 *
	 * @param int    $form_id          Filter by form (0 = all).
	 * @param string $search           Optional search.
	 * @param bool   $unread_only      Only unread rows.
	 * @param bool   $starred_only     Only starred rows.
	 * @param bool   $spam_only        Only spam rows.
	 * @param bool   $read_only        Only read rows.
	 * @param bool   $not_starred_only Only not-starred rows.
	 * @return int Rows deleted.
	 */
	public static function purge_filtered( $form_id = 0, $search = '', $unread_only = false, $starred_only = false, $spam_only = false, $read_only = false, $not_starred_only = false ) {
		global $wpdb;
		$table = Sobiforms_DB::table_name();
		$parts = self::list_query_parts( $form_id, $search, $unread_only, $starred_only, $spam_only, $read_only, $not_starred_only );

		$select_sql = "SELECT s.data FROM {$table} s{$parts['join']}";
		if ( ! empty( $parts['where'] ) ) {
			$select_sql .= ' WHERE ' . implode( ' AND ', $parts['where'] );
		}

		if ( empty( $parts['args'] ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$rows = $wpdb->get_col( $select_sql );
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$rows = $wpdb->get_col( $wpdb->prepare( $select_sql, ...$parts['args'] ) );
		}

		foreach ( $rows as $data_json ) {
			$values = json_decode( (string) $data_json, true );
			if ( is_array( $values ) ) {
				Sobiforms_File_Upload::delete_files_from_values( $values );
			}
		}

		$sql = "DELETE s FROM {$table} s{$parts['join']}";
		if ( ! empty( $parts['where'] ) ) {
			$sql .= ' WHERE ' . implode( ' AND ', $parts['where'] );
		}

		if ( empty( $parts['args'] ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$deleted = (int) $wpdb->query( $sql );
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$deleted = (int) $wpdb->query( $wpdb->prepare( $sql, ...$parts['args'] ) );
		}

		if ( $deleted > 0 ) {
			self::bust_unread_count_cache();
		}

		return $deleted;
	}

	/**
	 * @param array $ids Submission IDs.
	 * @return int Rows deleted.
	 */
	public static function delete_ids( $ids ) {
		$ids = array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );
		if ( empty( $ids ) ) {
			return 0;
		}

		foreach ( $ids as $id ) {
			$row = self::get_by_id( $id );
			if ( $row ) {
				Sobiforms_File_Upload::delete_files_from_values( $row['values'] );
			}
		}

		global $wpdb;
		$table        = Sobiforms_DB::table_name();
		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$sql          = "DELETE FROM {$table} WHERE id IN ({$placeholders})";

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$deleted = (int) $wpdb->query( $wpdb->prepare( $sql, ...$ids ) );

		if ( $deleted > 0 ) {
			self::bust_unread_count_cache();
		}

		return $deleted;
	}

	/**
	 * @param int $form_id Filter by form (0 = all).
	 * @return int Rows updated.
	 */
	public static function mark_all_read( $form_id = 0 ) {
		global $wpdb;
		$table   = Sobiforms_DB::table_name();
		$form_id = absint( $form_id );

		if ( $form_id ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$updated = (int) $wpdb->query(
				$wpdb->prepare(
					"UPDATE {$table} SET is_read = 1 WHERE is_read = 0 AND spam_status != %s AND form_id = %d",
					self::SPAM_SPAM,
					$form_id
				)
			);
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$updated = (int) $wpdb->query(
				$wpdb->prepare(
					"UPDATE {$table} SET is_read = 1 WHERE is_read = 0 AND spam_status != %s",
					self::SPAM_SPAM
				)
			);
		}

		if ( $updated > 0 ) {
			self::bust_unread_count_cache();
		}

		return $updated;
	}

	/**
	 * @param int    $id   Submission ID.
	 * @param string $note Admin note text.
	 * @return bool
	 */
	public static function update_note( $id, $note ) {
		if ( ! $id ) {
			return false;
		}

		$note = sanitize_textarea_field( $note );
		if ( strlen( $note ) > self::MAX_NOTE_LENGTH ) {
			$note = substr( $note, 0, self::MAX_NOTE_LENGTH );
		}

		global $wpdb;
		$updated = $wpdb->update(
			Sobiforms_DB::table_name(),
			array( 'admin_note' => $note ),
			array( 'id' => $id ),
			array( '%s' ),
			array( '%d' )
		);

		return false !== $updated;
	}

	/**
	 * @return int
	 */
	public static function count_failed() {
		global $wpdb;
		$table = Sobiforms_DB::table_name();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$table} WHERE mail_status = 'failed'"
		);
	}

	/**
	 * @param int $id Submission ID.
	 * @return bool
	 */
	public static function delete( $id ) {
		if ( ! $id ) {
			return false;
		}

		$row = self::get_by_id( $id );
		if ( $row ) {
			Sobiforms_File_Upload::delete_files_from_values( $row['values'] );
		}

		global $wpdb;
		$deleted = $wpdb->delete( Sobiforms_DB::table_name(), array( 'id' => $id ), array( '%d' ) );
		if ( false !== $deleted && $deleted > 0 ) {
			self::bust_unread_count_cache();
			return true;
		}
		return false;
	}

	/**
	 * @return int
	 */
	public static function purge_all() {
		global $wpdb;
		$table = Sobiforms_DB::table_name();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->query( "DELETE FROM {$table}" );
	}

	/**
	 * Delete submissions older than this form's retention period.
	 *
	 * @param int $form_id Form ID.
	 */
	public static function run_purge_old( $form_id ) {
		self::maybe_purge_old( absint( $form_id ) );
	}

	/**
	 * Schedule async retention purge (debounced per form).
	 *
	 * @param int $form_id Form ID.
	 */
	public static function schedule_purge_old( $form_id ) {
		$form_id = absint( $form_id );
		if ( ! $form_id ) {
			return;
		}

		if ( wp_next_scheduled( 'sobiforms_purge_old', array( $form_id ) ) ) {
			return;
		}

		wp_schedule_single_event( time() + 60, 'sobiforms_purge_old', array( $form_id ) );
	}

	/**
	 * Delete submissions older than this form's retention period.
	 *
	 * @param int $form_id Form ID.
	 */
	public static function maybe_purge_old( $form_id ) {
		$form = Sobiforms_Form_Repository::get_by_id( absint( $form_id ) );
		if ( ! $form || ! Sobiforms_Form_Repository::form_saves_submissions( $form ) ) {
			return;
		}

		$days = (int) ( $form['retention_days'] ?? Sobiforms_Form_Repository::DEFAULT_RETENTION_DAYS );
		if ( $days <= 0 ) {
			return;
		}

		global $wpdb;
		$table = Sobiforms_DB::table_name();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$table} WHERE form_id = %d AND created_at < DATE_SUB( %s, INTERVAL %d DAY )",
				absint( $form_id ),
				current_time( 'mysql' ),
				$days
			)
		);
	}

	private static function hydrate_list_row( $row ) {
		$row['id']          = (int) $row['id'];
		$row['form_id']     = (int) $row['form_id'];
		$row['is_read']     = (int) ( $row['is_read'] ?? 0 );
		$row['is_starred']  = (int) ( $row['is_starred'] ?? 0 );
		$row['spam_status'] = self::sanitize_spam_status( $row['spam_status'] ?? self::SPAM_HAM );
		$row['values']      = array();
		return $row;
	}

	/**
	 * @param string $title Frozen page title at submit.
	 * @return string
	 */
	public static function sanitize_source_title( $title ) {
		$title = sanitize_text_field( (string) $title );
		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $title, 0, self::MAX_SOURCE_TITLE );
		}
		return substr( $title, 0, self::MAX_SOURCE_TITLE );
	}

	/**
	 * @param string $path Frozen pathname at submit (no query string).
	 * @return string
	 */
	public static function sanitize_source_path( $path ) {
		$path = sanitize_text_field( (string) $path );
		$query_pos = strpos( $path, '?' );
		if ( false !== $query_pos ) {
			$path = substr( $path, 0, $query_pos );
		}
		$hash_pos = strpos( $path, '#' );
		if ( false !== $hash_pos ) {
			$path = substr( $path, 0, $hash_pos );
		}
		if ( function_exists( 'mb_substr' ) ) {
			return mb_substr( $path, 0, self::MAX_SOURCE_PATH );
		}
		return substr( $path, 0, self::MAX_SOURCE_PATH );
	}

	/**
	 * Normalize a DB row for admin use.
	 *
	 * @param array $row Raw DB row.
	 * @return array
	 */
	private static function hydrate_row( $row ) {
		$row['id']           = (int) $row['id'];
		$row['form_id']      = (int) $row['form_id'];
		$row['is_read']      = (int) ( $row['is_read'] ?? 0 );
		$row['is_starred']   = (int) ( $row['is_starred'] ?? 0 );
		$row['spam_status']  = self::sanitize_spam_status( $row['spam_status'] ?? self::SPAM_HAM );
		$row['source_title'] = (string) ( $row['source_title'] ?? '' );
		$row['source_path']  = (string) ( $row['source_path'] ?? '' );
		$row['values']       = json_decode( $row['data'] ?? '', true );
		if ( ! is_array( $row['values'] ) ) {
			$row['values'] = array();
		}
		return $row;
	}
}

// phpcs:enable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
