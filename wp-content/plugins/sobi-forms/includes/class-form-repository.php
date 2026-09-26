<?php
/**
 * CRUD for forms and field schema sanitization.
 *
 * @package SobiForms
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Custom plugin tables; table names come from Sobiforms_DB only.

/**
 * Form persistence and validation.
 */
class Sobiforms_Form_Repository {

	const CACHE_GROUP = 'sobiforms';
	const CACHE_TTL   = 3600;

	const ALLOWED_TYPES       = array( 'text', 'email', 'textarea', 'checkbox', 'phone', 'number', 'select', 'radio', 'url', 'title', 'paragraph', 'hidden', 'file' );
	const MAX_TEXTAREA_LENGTH = 10000;
	const MAX_TEXT_LENGTH     = 255;
	const MAX_URL_LENGTH      = 2048;
	const MAX_FIELD_OPTIONS   = 20;
	const PREFILL_SCALAR_TYPES = array( 'text', 'email', 'phone', 'url', 'number', 'textarea' );
	const MAX_RECIPIENTS      = 10;
	const SUCCESS_ACTION_MSG      = 'message';
	const SUCCESS_ACTION_REDIRECT = 'redirect';
	const MAX_SUBMIT_BUTTON_TEXT  = 100;
	const DEFAULT_RETENTION_DAYS  = 90;
	const MAX_CLOSE_LIMIT         = 100000;

	/**
	 * Count all forms.
	 *
	 * @return int
	 */
	public static function count( $search = '' ) {
		global $wpdb;
		$table  = Sobiforms_DB::forms_table_name();
		$search = sanitize_text_field( $search );

		if ( '' !== $search ) {
			$like = '%' . $wpdb->esc_like( $search ) . '%';
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			return (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$table} WHERE title LIKE %s OR slug LIKE %s OR recipient_email LIKE %s",
					$like,
					$like,
					$like
				)
			);
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
	}

	/**
	 * Get form by ID.
	 *
	 * @param int $id Form ID.
	 * @return array|null
	 */
	public static function get_by_id( $id ) {
		$id = absint( $id );
		if ( ! $id ) {
			return null;
		}

		$cache_key = self::form_cache_key_id( $id );
		$cached    = wp_cache_get( $cache_key, self::CACHE_GROUP );
		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;
		$table = Sobiforms_DB::forms_table_name();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ),
			ARRAY_A
		);

		if ( ! $row ) {
			return null;
		}

		$form = self::hydrate_row( $row );
		self::set_form_cache( $form );

		return $form;
	}

	/**
	 * Get form by slug.
	 *
	 * @param string $slug Form slug.
	 * @return array|null
	 */
	public static function get_by_slug( $slug ) {
		$slug = sanitize_title( $slug );
		if ( '' === $slug ) {
			return null;
		}

		$cache_key = self::form_cache_key_slug( $slug );
		$cached    = wp_cache_get( $cache_key, self::CACHE_GROUP );
		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;
		$table = Sobiforms_DB::forms_table_name();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE slug = %s", $slug ),
			ARRAY_A
		);

		if ( ! $row ) {
			return null;
		}

		$form = self::hydrate_row( $row );
		self::set_form_cache( $form );

		return $form;
	}

	/**
	 * List all forms.
	 *
	 * @param string $search Optional search on title, slug, or recipient email.
	 * @return array[]
	 */
	public static function get_all( $search = '' ) {
		global $wpdb;
		$table  = Sobiforms_DB::forms_table_name();
		$search = sanitize_text_field( $search );

		if ( '' !== $search ) {
			$like = '%' . $wpdb->esc_like( $search ) . '%';
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$table} WHERE title LIKE %s OR slug LIKE %s OR recipient_email LIKE %s ORDER BY title ASC",
					$like,
					$like,
					$like
				),
				ARRAY_A
			);
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$rows = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY title ASC", ARRAY_A );
		}

		return array_map( array( __CLASS__, 'hydrate_row' ), $rows );
	}

	/**
	 * Lightweight form list for dropdowns and maps (no fields JSON).
	 *
	 * @param string $search Optional search on title or slug.
	 * @return array<int, array{id: int, title: string, slug: string}>
	 */
	public static function get_all_light( $search = '' ) {
		global $wpdb;
		$table  = Sobiforms_DB::forms_table_name();
		$search = sanitize_text_field( $search );

		if ( '' !== $search ) {
			$like = '%' . $wpdb->esc_like( $search ) . '%';
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT id, title, slug FROM {$table} WHERE title LIKE %s OR slug LIKE %s ORDER BY title ASC",
					$like,
					$like
				),
				ARRAY_A
			);
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$rows = $wpdb->get_results( "SELECT id, title, slug FROM {$table} ORDER BY title ASC", ARRAY_A );
		}

		return array_map(
			static function ( $row ) {
				return array(
					'id'    => (int) $row['id'],
					'title' => (string) $row['title'],
					'slug'  => (string) $row['slug'],
				);
			},
			$rows
		);
	}

	/**
	 * Store hydrated form in object cache (by id and slug).
	 *
	 * @param array $form Hydrated form row.
	 */
	private static function set_form_cache( $form ) {
		if ( empty( $form['id'] ) ) {
			return;
		}

		wp_cache_set( self::form_cache_key_id( (int) $form['id'] ), $form, self::CACHE_GROUP, self::CACHE_TTL );

		if ( ! empty( $form['slug'] ) ) {
			wp_cache_set( self::form_cache_key_slug( (string) $form['slug'] ), $form, self::CACHE_GROUP, self::CACHE_TTL );
		}
	}

	/**
	 * Invalidate cached form rows after create, update, or delete.
	 *
	 * @param int    $id   Form ID.
	 * @param string $slug Form slug (optional).
	 */
	public static function flush_form_cache( $id = 0, $slug = '' ) {
		if ( $id ) {
			wp_cache_delete( self::form_cache_key_id( $id ), self::CACHE_GROUP );
		}
		if ( '' !== $slug ) {
			wp_cache_delete( self::form_cache_key_slug( $slug ), self::CACHE_GROUP );
		}
	}

	/**
	 * @param int $id Form ID.
	 * @return string
	 */
	private static function form_cache_key_id( $id ) {
		return 'form_id_' . absint( $id );
	}

	/**
	 * @param string $slug Form slug.
	 * @return string
	 */
	private static function form_cache_key_slug( $slug ) {
		return 'form_slug_' . sanitize_title( $slug );
	}

	/**
	 * Whether any saved form includes a file upload field.
	 *
	 * @return bool
	 */
	public static function any_form_has_file_fields() {
		foreach ( self::get_all() as $form ) {
			if ( Sobiforms_File_Upload::form_has_file_fields( $form ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Create a new form.
	 *
	 * @param array $data Form data.
	 * @return int|false
	 */
	public static function create( $data ) {
		global $wpdb;

		$fields = self::sanitize_fields_schema( $data['fields'] ?? array() );
		if ( false === $fields ) {
			return false;
		}

		$title = sanitize_text_field( $data['title'] ?? '' );
		if ( '' === $title ) {
			return false;
		}

		$email = self::sanitize_recipient_emails( $data['recipient_email'] ?? '' );
		if ( false === $email ) {
			return false;
		}

		$now  = current_time( 'mysql' );
		$slug = self::generate_unique_slug( $title );

		$close_enabled = ! empty( $data['close_enabled'] ) ? 1 : 0;
		$close_at      = null;
		if ( $close_enabled ) {
			$close_at = self::sanitize_close_at( $data['close_at'] ?? '', true );
			if ( false === $close_at ) {
				return false;
			}
		}

		$close_limit_enabled = ! empty( $data['close_limit_enabled'] ) ? 1 : 0;
		$close_limit         = null;
		if ( $close_limit_enabled ) {
			$close_limit = self::sanitize_close_limit( $data['close_limit'] ?? '', true );
			if ( false === $close_limit ) {
				return false;
			}
		}

		$data = self::sanitize_confirmation_settings( $data, $fields );
		$save_submissions = self::resolve_save_submissions_flag( $fields, $data );

		$inserted = $wpdb->insert(
			Sobiforms_DB::forms_table_name(),
			array(
				'title'           => $title,
				'slug'            => $slug,
				'recipient_email' => $email,
				'success_message'  => sanitize_text_field(
					$data['success_message'] ?? __( 'Thank you! Your message has been sent.', 'sobi-forms' )
				),
				'error_message'    => sanitize_text_field(
					$data['error_message'] ?? __( 'Something went wrong. Please try again.', 'sobi-forms' )
				),
				'success_action'   => self::sanitize_success_action( $data['success_action'] ?? self::SUCCESS_ACTION_MSG ),
				'redirect_page_id' => self::sanitize_redirect_page_id( $data['redirect_page_id'] ?? 0 ),
				'fields'            => wp_json_encode( $fields ),
				'submit_button_text' => self::sanitize_submit_button_text( $data['submit_button_text'] ?? '' ),
				'save_submissions'   => $save_submissions,
				'retention_days'     => self::sanitize_retention_days( $data['retention_days'] ?? self::DEFAULT_RETENTION_DAYS ),
				'paused'             => ! empty( $data['paused'] ) ? 1 : 0,
				'close_enabled'      => $close_enabled,
				'close_at'           => $close_at,
				'close_limit_enabled' => $close_limit_enabled,
				'close_limit'        => $close_limit,
				'submission_count'   => 0,
				'unavailable_message'  => self::sanitize_unavailable_message( $data['unavailable_message'] ?? '' ),
				'send_confirmation_email' => ! empty( $data['send_confirmation_email'] ) ? 1 : 0,
				'confirmation_email_field_id' => sanitize_key( $data['confirmation_email_field_id'] ?? '' ),
				'is_default'         => 0,
				'created_at'         => $now,
				'updated_at'         => $now),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%d', '%d', '%d', '%s', '%d', '%s', '%d', '%s', '%s' )
		);

		if ( $inserted && Sobiforms_File_Upload::form_has_file_fields( array( 'fields' => $fields ) ) ) {
			Sobiforms_File_Upload::clear_upload_web_access_cache();
		}

		if ( $inserted ) {
			self::flush_form_cache( (int) $wpdb->insert_id, $slug );
		}

		return $inserted ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Update an existing form.
	 *
	 * @param int   $id   Form ID.
	 * @param array $data Form data.
	 * @return bool
	 */
	public static function update( $id, $data ) {
		global $wpdb;

		$existing = self::get_by_id( $id );
		if ( ! $existing ) {
			return false;
		}

		$fields = self::sanitize_fields_schema( $data['fields'] ?? array() );
		if ( false === $fields ) {
			return false;
		}

		$title = sanitize_text_field( $data['title'] ?? '' );
		if ( '' === $title ) {
			return false;
		}

		$email = self::sanitize_recipient_emails( $data['recipient_email'] ?? '' );
		if ( false === $email ) {
			return false;
		}

		$close_enabled = ! empty( $data['close_enabled'] ) ? 1 : 0;
		if ( $close_enabled ) {
			$close_at = self::sanitize_close_at( $data['close_at'] ?? '', true );
			if ( false === $close_at ) {
				return false;
			}
		} else {
			$close_at = $existing['close_at'] ?? null;
		}

		$close_limit_enabled = ! empty( $data['close_limit_enabled'] ) ? 1 : 0;
		if ( $close_limit_enabled ) {
			$close_limit = self::sanitize_close_limit( $data['close_limit'] ?? '', true );
			if ( false === $close_limit ) {
				return false;
			}
		} else {
			$close_limit = isset( $existing['close_limit'] ) ? (int) $existing['close_limit'] : null;
			if ( $close_limit <= 0 ) {
				$close_limit = null;
			}
		}

		$data = self::sanitize_confirmation_settings( $data, $fields );
		$save_submissions = self::resolve_save_submissions_flag( $fields, $data );

		// Slug is assigned on create and preserved on update (embedded shortcodes stay valid).
		$slug = isset( $existing['slug'] ) ? (string) $existing['slug'] : '';
		if ( '' === $slug ) {
			$slug = self::generate_unique_slug( $title, $id );
		}

		$updated = $wpdb->update(
			Sobiforms_DB::forms_table_name(),
			array(
				'title'           => $title,
				'slug'            => $slug,
				'recipient_email' => $email,
				'success_message'  => sanitize_text_field( $data['success_message'] ?? '' ),
				'error_message'    => sanitize_text_field( $data['error_message'] ?? '' ),
				'success_action'   => self::sanitize_success_action( $data['success_action'] ?? self::SUCCESS_ACTION_MSG ),
				'redirect_page_id' => self::sanitize_redirect_page_id( $data['redirect_page_id'] ?? 0 ),
				'fields'            => wp_json_encode( $fields ),
				'submit_button_text' => self::sanitize_submit_button_text( $data['submit_button_text'] ?? '' ),
				'save_submissions'   => $save_submissions,
				'retention_days'     => self::sanitize_retention_days( $data['retention_days'] ?? self::DEFAULT_RETENTION_DAYS ),
				'paused'             => ! empty( $data['paused'] ) ? 1 : 0,
				'close_enabled'      => $close_enabled,
				'close_at'           => $close_at,
				'close_limit_enabled' => $close_limit_enabled,
				'close_limit'        => $close_limit,
				'unavailable_message'  => self::sanitize_unavailable_message( $data['unavailable_message'] ?? '' ),
				'send_confirmation_email' => ! empty( $data['send_confirmation_email'] ) ? 1 : 0,
				'confirmation_email_field_id' => sanitize_key( $data['confirmation_email_field_id'] ?? '' ),
				'is_default'         => 0,
				'updated_at'         => current_time( 'mysql' )),
			array( 'id' => $id ),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%d', '%d', '%d', '%s', '%d', '%d', '%s', '%d', '%s', '%d', '%s' ),
			array( '%d' )
		);

		if ( false !== $updated && Sobiforms_File_Upload::form_has_file_fields( array( 'fields' => $fields ) ) ) {
			Sobiforms_File_Upload::clear_upload_web_access_cache();
		}

		if ( false !== $updated ) {
			self::flush_form_cache( $id, $slug );
			if ( ! empty( $existing['slug'] ) && $existing['slug'] !== $slug ) {
				self::flush_form_cache( 0, (string) $existing['slug'] );
			}
		}

		return false !== $updated;
	}

	/**
	 * Delete a form.
	 *
	 * @param int $id Form ID.
	 * @return bool
	 */
	public static function delete( $id ) {
		$existing = self::get_by_id( $id );
		global $wpdb;
		$deleted = $wpdb->delete( Sobiforms_DB::forms_table_name(), array( 'id' => $id ), array( '%d' ) );
		if ( false !== $deleted && $deleted > 0 ) {
			if ( $existing ) {
				self::flush_form_cache( (int) $id, (string) ( $existing['slug'] ?? '' ) );
			} else {
				self::flush_form_cache( (int) $id );
			}
			return true;
		}
		return false;
	}

	/**
	 * Duplicate a form.
	 *
	 * @param int $id Source form ID.
	 * @return int|false
	 */
	public static function duplicate( $id ) {
		$form = self::get_by_id( $id );
		if ( ! $form ) {
			return false;
		}

		return self::create(
			array(
				'title'           => $form['title'] . ' ' . __( '(copy)', 'sobi-forms' ),
				'recipient_email' => $form['recipient_email'],
				'success_message'  => $form['success_message'],
				'error_message'    => $form['error_message'],
				'success_action'   => $form['success_action'],
				'redirect_page_id' => $form['redirect_page_id'],
				'fields'            => $form['fields'],
				'submit_button_text' => $form['submit_button_text'] ?? '',
				'save_submissions'   => $form['save_submissions'] ?? 0,
				'retention_days'     => $form['retention_days'] ?? self::DEFAULT_RETENTION_DAYS,
				'paused'             => $form['paused'] ?? 0,
				'close_enabled'      => $form['close_enabled'] ?? 0,
				'close_at'           => $form['close_at'] ?? null,
				'close_limit_enabled' => $form['close_limit_enabled'] ?? 0,
				'close_limit'        => $form['close_limit'] ?? null,
				'unavailable_message' => $form['unavailable_message'] ?? '',
				'send_confirmation_email' => $form['send_confirmation_email'] ?? 0,
				'confirmation_email_field_id' => $form['confirmation_email_field_id'] ?? '',
				'is_default'         => 0)
		);
	}

	/**
	 * Default front-end submit button label.
	 *
	 * @return string
	 */
	public static function default_submit_button_text() {
		return __( 'Send', 'sobi-forms' );
	}

	/**
	 * Resolved submit button text for display (custom or default).
	 *
	 * @param array $form Form row.
	 * @return string
	 */
	public static function get_submit_button_text( $form ) {
		$text = isset( $form['submit_button_text'] ) ? trim( (string) $form['submit_button_text'] ) : '';

		return '' !== $text ? $text : self::default_submit_button_text();
	}

	/**
	 * Sanitize custom submit button label.
	 *
	 * @param mixed $raw Raw POST value.
	 * @return string
	 */
	public static function sanitize_submit_button_text( $raw ) {
		$text = sanitize_text_field( is_string( $raw ) ? $raw : '' );

		if ( mb_strlen( $text ) > self::MAX_SUBMIT_BUTTON_TEXT ) {
			$text = mb_substr( $text, 0, self::MAX_SUBMIT_BUTTON_TEXT );
		}

		return $text;
	}

	/**
	 * Whitelist post-submit success behavior.
	 *
	 * @param mixed $value Raw action value.
	 * @return string `message` or `redirect`.
	 */
	public static function sanitize_success_action( $value ) {
		$value = is_string( $value ) ? sanitize_key( $value ) : '';
		return self::SUCCESS_ACTION_REDIRECT === $value ? self::SUCCESS_ACTION_REDIRECT : self::SUCCESS_ACTION_MSG;
	}

	/**
	 * Validate a published WordPress page ID for redirect.
	 *
	 * @param mixed $id Page ID.
	 * @return int Valid page ID or 0.
	 */
	public static function sanitize_redirect_page_id( $id ) {
		$id = absint( $id );
		if ( $id <= 0 ) {
			return 0;
		}

		$post = get_post( $id );
		if ( ! $post || 'page' !== $post->post_type || 'publish' !== $post->post_status ) {
			return 0;
		}

		return $id;
	}

	/**
	 * Parse, validate, and normalize one or more recipient emails.
	 *
	 * Accepts comma- or semicolon-separated input. Returns a comma-space
	 * separated string for storage and wp_mail(), or false if invalid.
	 *
	 * @param string $raw Raw recipient field value.
	 * @return string|false
	 */
	public static function sanitize_recipient_emails( $raw ) {
		$raw = is_string( $raw ) ? trim( $raw ) : '';
		if ( '' === $raw ) {
			return '';
		}

		$parts  = preg_split( '/[;,]+/', $raw );
		$emails = array();
		$seen   = array();

		foreach ( $parts as $part ) {
			$part = trim( $part );
			if ( '' === $part ) {
				continue;
			}

			$email = sanitize_email( $part );
			if ( ! is_email( $email ) ) {
				return false;
			}

			$key = strtolower( $email );
			if ( isset( $seen[ $key ] ) ) {
				continue;
			}

			$seen[ $key ] = true;
			$emails[]     = $email;
		}

		if ( empty( $emails ) || count( $emails ) > self::MAX_RECIPIENTS ) {
			return false;
		}

		return implode( ', ', $emails );
	}

	/**
	 * Whether a form has at least one valid recipient email configured.
	 *
	 * @param array|string $form_or_raw Form row or raw recipient field.
	 * @return bool
	 */
	/**
	 * Sanitize per-form retention days (0 = no auto-delete).
	 *
	 * @param mixed $value Raw value.
	 * @return int
	 */
	public static function sanitize_retention_days( $value ) {
		return absint( $value );
	}

	/**
	 * Sanitize visitor message when the form is not accepting submissions.
	 *
	 * @param mixed $value Raw value.
	 * @return string
	 */
	public static function sanitize_unavailable_message( $value ) {
		$message = sanitize_textarea_field( (string) $value );
		if ( strlen( $message ) > 500 ) {
			$message = substr( $message, 0, 500 );
		}
		return $message;
	}

	/**
	 * Parse admin close datetime (site timezone) for storage.
	 *
	 * @param string $value     Raw datetime-local or SQL string.
	 * @param bool   $required  When true, empty/invalid returns false.
	 * @return string|false|null SQL datetime, null when not required and empty, false when invalid.
	 */
	public static function sanitize_close_at( $value, $required = false ) {
		$value = trim( (string) $value );
		if ( '' === $value ) {
			return $required ? false : null;
		}

		$value = str_replace( 'T', ' ', $value );
		$tz    = wp_timezone();
		$dt    = \DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $value, $tz );
		if ( ! $dt ) {
			$dt = \DateTimeImmutable::createFromFormat( 'Y-m-d H:i', $value, $tz );
		}

		if ( ! $dt ) {
			return false;
		}

		return $dt->format( 'Y-m-d H:i:s' );
	}

	/**
	 * Whether the form accepts new submissions (not paused and not past close time).
	 *
	 * @param array $form Form row.
	 * @return bool
	 */
	public static function is_accepting_submissions( $form ) {
		if ( ! is_array( $form ) ) {
			return false;
		}

		if ( ! empty( $form['paused'] ) ) {
			return false;
		}

		if ( ! empty( $form['close_enabled'] ) && ! empty( $form['close_at'] ) ) {
			$close_at = self::parse_close_at( $form['close_at'] );
			if ( $close_at && $close_at <= self::site_now() ) {
				return false;
			}
		}

		if ( ! empty( $form['close_limit_enabled'] ) && ! empty( $form['close_limit'] ) ) {
			$count = isset( $form['submission_count'] ) ? (int) $form['submission_count'] : 0;
			$limit = (int) $form['close_limit'];
			if ( $limit > 0 && $count >= $limit ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Atomically reserve one submission slot when a per-form limit is enabled.
	 *
	 * @param int $form_id Form ID.
	 * @return bool|null True when reserved, false when cap reached, null on SQL error.
	 */
	public static function try_reserve_submission_slot( $form_id ) {
		global $wpdb;

		$form_id = absint( $form_id );
		if ( ! $form_id ) {
			return false;
		}

		$table = Sobiforms_DB::forms_table_name();

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table name from Sobiforms_DB only.
		$affected = $wpdb->query(
			$wpdb->prepare(
				"UPDATE {$table}
				SET submission_count = submission_count + 1
				WHERE id = %d
				AND close_limit_enabled = 1
				AND submission_count < close_limit",
				$form_id
			)
		);

		if ( false === $affected ) {
			return null;
		}

		return $affected > 0;
	}

	/**
	 * Sanitize optional submission cap (when enabled).
	 *
	 * @param mixed $raw      Raw limit value.
	 * @param bool  $required Whether a positive integer is required.
	 * @return int|false|null Limit, false when invalid and required, null when empty and optional.
	 */
	public static function sanitize_close_limit( $raw, $required = false ) {
		$limit = absint( $raw );

		if ( $limit < 1 ) {
			return $required ? false : null;
		}

		if ( $limit > self::MAX_CLOSE_LIMIT ) {
			$limit = self::MAX_CLOSE_LIMIT;
		}

		return $limit;
	}

	/**
	 * Message shown when the form is unavailable (custom or default).
	 *
	 * @param array $form Form row.
	 * @return string
	 */
	public static function get_unavailable_message( $form ) {
		$custom = isset( $form['unavailable_message'] ) ? trim( (string) $form['unavailable_message'] ) : '';
		if ( '' !== $custom ) {
			return $custom;
		}

		return __( 'This form is not accepting submissions at the moment.', 'sobi-forms' );
	}

	/**
	 * Format stored close_at for datetime-local input (site timezone).
	 *
	 * @param array $form Form row.
	 * @return string
	 */
	public static function format_close_at_for_input( $form ) {
		$close_at = $form['close_at'] ?? '';
		if ( '' === $close_at || '0000-00-00 00:00:00' === $close_at ) {
			return '';
		}

		$dt = self::parse_close_at( $close_at );
		return $dt ? $dt->format( 'Y-m-d\TH:i' ) : '';
	}

	/**
	 * Current time in the site timezone.
	 *
	 * @return \DateTimeImmutable
	 */
	private static function site_now() {
		return new \DateTimeImmutable( 'now', wp_timezone() );
	}

	/**
	 * Parse a stored close_at value in site timezone.
	 *
	 * @param string $stored Stored SQL datetime.
	 * @return \DateTimeImmutable|null
	 */
	private static function parse_close_at( $stored ) {
		$tz = wp_timezone();
		$dt = \DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', (string) $stored, $tz );

		return $dt ?: null;
	}

	/**
	 * Whether this form stores submissions in the database.
	 *
	 * @param array|int $form_or_id Form row or ID.
	 * @return bool
	 */
	public static function form_saves_submissions( $form_or_id ) {
		if ( is_numeric( $form_or_id ) ) {
			$form = self::get_by_id( absint( $form_or_id ) );
		} else {
			$form = $form_or_id;
		}

		return is_array( $form ) && ! empty( $form['save_submissions'] );
	}

	public static function has_recipient_emails( $form_or_raw ) {
		$raw = is_array( $form_or_raw ) ? ( $form_or_raw['recipient_email'] ?? '' ) : $form_or_raw;
		$parsed = self::sanitize_recipient_emails( $raw );

		return is_string( $parsed ) && '' !== $parsed;
	}

	/**
	 * Whether this form sends a visitor confirmation email after submit.
	 *
	 * @param array $form Form row.
	 * @return bool
	 */
	public static function form_sends_confirmation( $form ) {
		return is_array( $form )
			&& ! empty( $form['send_confirmation_email'] )
			&& '' !== sanitize_key( $form['confirmation_email_field_id'] ?? '' );
	}

	/**
	 * Email-type fields from a form schema.
	 *
	 * @param array $form_or_fields Form row or sanitized fields array.
	 * @return array[]
	 */
	public static function get_email_fields( $form_or_fields ) {
		$fields = is_array( $form_or_fields ) && isset( $form_or_fields['fields'] )
			? $form_or_fields['fields']
			: $form_or_fields;

		if ( ! is_array( $fields ) ) {
			return array();
		}

		$email_fields = array();
		foreach ( $fields as $field ) {
			if ( ! is_array( $field ) || 'email' !== ( $field['type'] ?? '' ) ) {
				continue;
			}
			$id = sanitize_key( $field['id'] ?? '' );
			if ( '' === $id ) {
				continue;
			}
			$email_fields[] = $field;
		}

		return $email_fields;
	}

	/**
	 * Whether the schema includes a required email field.
	 *
	 * @param array $form_or_fields Form row or sanitized fields array.
	 * @return bool
	 */
	public static function form_has_required_email_field( $form_or_fields ) {
		foreach ( self::get_email_fields( $form_or_fields ) as $field ) {
			if ( ! empty( $field['required'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Normalize confirmation settings against the saved field schema.
	 *
	 * Clears confirmation when the stored field id is missing or not an email field.
	 *
	 * @param array $data   Form data being saved.
	 * @param array $fields Sanitized fields schema.
	 * @return array
	 */
	public static function sanitize_confirmation_settings( $data, $fields ) {
		$send     = ! empty( $data['send_confirmation_email'] );
		$field_id = sanitize_key( $data['confirmation_email_field_id'] ?? '' );

		if ( ! $send ) {
			$data['send_confirmation_email'] = 0;
			$field_id = sanitize_key( $data['confirmation_email_field_id'] ?? '' );
			if ( '' !== $field_id ) {
				$still_valid = false;
				foreach ( self::get_email_fields( $fields ) as $field ) {
					if ( ( $field['id'] ?? '' ) === $field_id ) {
						$still_valid = true;
						break;
					}
				}
				if ( ! $still_valid ) {
					$email_fields = self::get_email_fields( $fields );
					$field_id     = count( $email_fields ) === 1
						? sanitize_key( $email_fields[0]['id'] ?? '' )
						: '';
				}
			}
			$data['confirmation_email_field_id'] = $field_id;
			return $data;
		}

		$email_fields = self::get_email_fields( $fields );
		$valid        = false;

		if ( '' !== $field_id ) {
			foreach ( $email_fields as $field ) {
				if ( ( $field['id'] ?? '' ) === $field_id ) {
					$valid = true;
					break;
				}
			}
		}

		if ( ! $valid && count( $email_fields ) === 1 ) {
			$field_id = sanitize_key( $email_fields[0]['id'] ?? '' );
			$valid    = '' !== $field_id;
		}

		if ( ! $valid ) {
			$data['send_confirmation_email']     = 0;
			$data['confirmation_email_field_id'] = '';
		} else {
			$data['send_confirmation_email']     = 1;
			$data['confirmation_email_field_id'] = $field_id;
		}

		return $data;
	}

	/**
	 * Fixed visitor confirmation email subject and HTML body (no submitted field values).
	 *
	 * @param array $form Form row.
	 * @return array{subject: string, body: string}
	 */
	public static function get_confirmation_email_template( $form ) {
		$site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

		$subject = sprintf(
			/* translators: %s: site name */
			__( 'Thank you — %s', 'sobi-forms' ),
			$site_name
		);

		$message = sprintf(
			/* translators: %s: site name */
			__( 'Thank you for your message. We have received it at %s.', 'sobi-forms' ),
			$site_name
		);

		$body  = '<html><body style="font-family:sans-serif;line-height:1.5">';
		$body .= '<p>' . esc_html( $message ) . '</p>';
		$body .= '</body></html>';

		return array(
			'subject' => $subject,
			'body'    => $body,
		);
	}

	/**
	 * Strict sanitization for field schema JSON.
	 *
	 * @param mixed $raw Raw fields array or JSON string.
	 * @return array|false
	 */
	public static function sanitize_fields_schema( $raw ) {
		if ( is_string( $raw ) ) {
			$raw = json_decode( $raw, true );
		}

		if ( ! is_array( $raw ) || empty( $raw ) ) {
			return false;
		}

		$clean            = array();
		$seen_ids         = array();
		$seen_prefill_keys = array();

		foreach ( $raw as $field ) {
			if ( ! is_array( $field ) ) {
				continue;
			}

			$type = isset( $field['type'] ) ? sanitize_key( $field['type'] ) : '';
			if ( ! in_array( $type, self::ALLOWED_TYPES, true ) ) {
				continue;
			}

			$id = isset( $field['id'] ) ? sanitize_key( $field['id'] ) : '';
			if ( '' === $id || isset( $seen_ids[ $id ] ) ) {
				$id = 'field_' . wp_generate_password( 8, false, false );
			}
			$seen_ids[ $id ] = true;

			if ( self::is_hidden_field( $type ) ) {
				$label = sanitize_text_field( $field['label'] ?? '' );
				if ( '' === $label ) {
					continue;
				}

				$clean_field = array(
					'id'          => $id,
					'type'        => 'hidden',
					'label'       => $label,
					'placeholder' => '',
					'required'    => false,
				);

				if ( ! empty( $field['prefillKey'] ) ) {
					$prefill_key = sanitize_key( $field['prefillKey'] );
					if ( '' !== $prefill_key && ! isset( $seen_prefill_keys[ $prefill_key ] ) ) {
						$clean_field['prefillKey'] = $prefill_key;
						$seen_prefill_keys[ $prefill_key ] = true;
					}
				}

				if ( isset( $field['defaultValue'] ) ) {
					$default_value = sanitize_text_field( $field['defaultValue'] );
					if ( '' !== $default_value ) {
						$clean_field['defaultValue'] = $default_value;
					}
				}

				$clean[] = $clean_field;
				continue;
			}

			if ( self::is_layout_field( $type ) ) {
				$clean_field = array(
					'id'          => $id,
					'type'        => $type,
					'label'       => '',
					'placeholder' => '',
					'required'    => false,
				);

				if ( 'title' === $type ) {
					$clean_field['label'] = sanitize_text_field( $field['label'] ?? '' );
				}

				if ( 'paragraph' === $type ) {
					$content = sanitize_textarea_field( $field['content'] ?? '' );
					if ( strlen( $content ) > self::MAX_TEXTAREA_LENGTH ) {
						$content = substr( $content, 0, self::MAX_TEXTAREA_LENGTH );
					}
					$clean_field['content'] = $content;
				}

				$clean[] = $clean_field;
				continue;
			}

			$clean_field = array(
				'id'          => $id,
				'type'        => $type,
				'label'       => sanitize_text_field( $field['label'] ?? '' ),
				'placeholder' => sanitize_text_field( $field['placeholder'] ?? '' ),
				'required'    => ! empty( $field['required'] ));

			if ( in_array( $type, array( 'select', 'radio' ), true ) ) {
				$options = self::sanitize_field_options( $field['options'] ?? array() );
				if ( count( $options ) < 2 ) {
					return false;
				}
				$clean_field['options'] = $options;
			}

			if ( 'checkbox' === $type && isset( $field['options'] ) && is_array( $field['options'] ) ) {
				$options = self::sanitize_field_options( $field['options'] );
				if ( count( $options ) < 1 ) {
					return false;
				}
				$clean_field['options'] = $options;
			}

			if ( 'text' === $type ) {
				if ( isset( $field['minLength'] ) && is_numeric( $field['minLength'] ) ) {
					$min_length = (int) $field['minLength'];
					if ( $min_length >= 0 && $min_length <= self::MAX_TEXT_LENGTH ) {
						$clean_field['minLength'] = $min_length;
					}
				}
				if ( isset( $field['maxLength'] ) && is_numeric( $field['maxLength'] ) ) {
					$max_length = (int) $field['maxLength'];
					if ( $max_length >= 1 && $max_length <= self::MAX_TEXT_LENGTH ) {
						$clean_field['maxLength'] = $max_length;
					}
				}
			}

			if ( 'number' === $type ) {
				if ( isset( $field['min'] ) && '' !== $field['min'] && is_numeric( $field['min'] ) ) {
					$clean_field['min'] = (int) $field['min'];
				}
				if ( isset( $field['max'] ) && '' !== $field['max'] && is_numeric( $field['max'] ) ) {
					$clean_field['max'] = (int) $field['max'];
				}
			}

			if ( 'textarea' === $type ) {
				if ( isset( $field['resizable'] ) && ! $field['resizable'] ) {
					$clean_field['resizable'] = false;
				}
				if ( isset( $field['maxLength'] ) && is_numeric( $field['maxLength'] ) ) {
					$max_length = (int) $field['maxLength'];
					if ( $max_length >= 1 && $max_length <= self::MAX_TEXTAREA_LENGTH ) {
						$clean_field['maxLength'] = $max_length;
					}
				}
			}

			if ( 'checkbox' !== $type && isset( $field['showLabel'] ) && ! $field['showLabel'] ) {
				$clean_field['showLabel'] = false;
			}

			if ( self::is_prefill_scalar_field( $type ) && ( ! empty( $field['urlPrefill'] ) || ! empty( $field['prefillKey'] ) ) ) {
				$clean_field['urlPrefill'] = true;
				if ( ! empty( $field['prefillKey'] ) ) {
					$prefill_key = sanitize_key( $field['prefillKey'] );
					if ( '' !== $prefill_key && ! isset( $seen_prefill_keys[ $prefill_key ] ) ) {
						$clean_field['prefillKey'] = $prefill_key;
						$seen_prefill_keys[ $prefill_key ] = true;
					}
				}
			}

			if ( 'file' === $type ) {
				$extensions = Sobiforms_File_Upload::get_field_allowed_extensions( $field );
				$clean_field['allowedExtensions'] = $extensions;

				$max_mb = isset( $field['maxSizeMb'] ) ? (int) $field['maxSizeMb'] : Sobiforms_File_Upload::DEFAULT_MAX_SIZE_MB;
				$max_mb = max( 1, min( $max_mb, Sobiforms_File_Upload::get_server_max_upload_mb() ) );
				$clean_field['maxSizeMb'] = $max_mb;
			}

			$clean[] = $clean_field;
		}

		return ! empty( $clean ) ? $clean : false;
	}

	/**
	 * File upload fields require database storage.
	 *
	 * @param array $fields Sanitized fields schema.
	 * @param array $data   Raw save payload.
	 * @return int 1 or 0.
	 */
	private static function resolve_save_submissions_flag( $fields, $data ) {
		if ( Sobiforms_File_Upload::form_has_file_fields( array( 'fields' => $fields ) ) ) {
			return 1;
		}

		return ! empty( $data['save_submissions'] ) ? 1 : 0;
	}

	/**
	 * Sanitize select/radio option labels (label = value in v1).
	 *
	 * @param mixed $raw Raw options array.
	 * @return string[]
	 */
	private static function sanitize_field_options( $raw ) {
		if ( ! is_array( $raw ) ) {
			return array();
		}

		$options = array();
		foreach ( $raw as $option ) {
			$label = sanitize_text_field( $option );
			if ( '' === $label || count( $options ) >= self::MAX_FIELD_OPTIONS ) {
				continue;
			}
			$options[] = $label;
		}

		return $options;
	}

	/**
	 * Whether a field type is a non-input layout block (title, paragraph).
	 *
	 * @param string $type Field type slug.
	 * @return bool
	 */
	public static function is_layout_field( $type ) {
		return in_array( $type, array( 'title', 'paragraph' ), true );
	}

	/**
	 * Whether a field type is a hidden metadata input (sidebar-configured).
	 *
	 * @param string $type Field type slug.
	 * @return bool
	 */
	public static function is_hidden_field( $type ) {
		return 'hidden' === $type;
	}

	/**
	 * Whether a field type is a file upload input.
	 *
	 * @param string $type Field type slug.
	 * @return bool
	 */
	public static function is_file_field( $type ) {
		return 'file' === $type;
	}

	/**
	 * Whether a field type supports URL parameter prefill (scalar inputs v1).
	 *
	 * @param string $type Field type slug.
	 * @return bool
	 */
	public static function is_prefill_scalar_field( $type ) {
		return in_array( $type, self::PREFILL_SCALAR_TYPES, true );
	}

	/**
	 * Whether a checkbox field uses multiple options (vs legacy single consent).
	 *
	 * @param array $field Field definition.
	 * @return bool
	 */
	public static function field_has_checkbox_options( $field ) {
		return 'checkbox' === ( $field['type'] ?? '' )
			&& isset( $field['options'] )
			&& is_array( $field['options'] )
			&& count( $field['options'] ) > 0;
	}

	/**
	 * Effective max character length for text fields.
	 *
	 * @param array $field Field definition.
	 * @return int
	 */
	public static function field_effective_max_length( $field ) {
		if ( 'text' !== ( $field['type'] ?? '' ) ) {
			return 0;
		}

		if ( isset( $field['maxLength'] ) && is_numeric( $field['maxLength'] ) ) {
			$max = (int) $field['maxLength'];
			if ( $max >= 1 && $max <= self::MAX_TEXT_LENGTH ) {
				return $max;
			}
		}

		return self::MAX_TEXT_LENGTH;
	}

	/**
	 * Generate a unique slug from title.
	 *
	 * @param string   $title      Form title.
	 * @param int|null $exclude_id Form ID to exclude from uniqueness check.
	 * @return string
	 */
	public static function generate_unique_slug( $title, $exclude_id = null ) {
		global $wpdb;

		$base = sanitize_title( $title );
		if ( '' === $base ) {
			$base = 'form';
		}

		$slug   = $base;
		$suffix = 2;
		$table  = Sobiforms_DB::forms_table_name();

		while ( true ) {
			if ( $exclude_id ) {
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$exists = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT id FROM {$table} WHERE slug = %s AND id != %d LIMIT 1",
						$slug,
						$exclude_id
					)
				);
			} else {
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$exists = $wpdb->get_var(
					$wpdb->prepare( "SELECT id FROM {$table} WHERE slug = %s LIMIT 1", $slug )
				);
			}

			if ( ! $exists ) {
				break;
			}

			$slug = $base . '-' . $suffix;
			++$suffix;
		}

		return $slug;
	}

	/**
	 * Default starter fields for a new form.
	 *
	 * @return array[]
	 */
	public static function default_fields() {
		return array(
			array(
				'id'          => 'field_name',
				'type'        => 'text',
				'label'       => __( 'Name', 'sobi-forms' ),
				'placeholder' => '',
				'required'    => false),
			array(
				'id'          => 'field_email',
				'type'        => 'email',
				'label'       => __( 'Email', 'sobi-forms' ),
				'placeholder' => '',
				'required'    => false),
			array(
				'id'          => 'field_message',
				'type'        => 'textarea',
				'label'       => __( 'Message', 'sobi-forms' ),
				'placeholder' => '',
				'required'    => false));
	}

	/**
	 * Decode fields JSON on a DB row.
	 *
	 * @param array $row Raw DB row.
	 * @return array
	 */
	private static function hydrate_row( $row ) {
		$row['fields'] = json_decode( $row['fields'], true );
		if ( ! is_array( $row['fields'] ) ) {
			$row['fields'] = array();
		}
		$row['id']                = (int) $row['id'];
		$row['is_default']        = (int) $row['is_default'];
		$row['redirect_page_id']  = isset( $row['redirect_page_id'] ) ? (int) $row['redirect_page_id'] : 0;
		$row['success_action']    = self::sanitize_success_action( $row['success_action'] ?? self::SUCCESS_ACTION_MSG );
		$row['save_submissions']  = (int) ( $row['save_submissions'] ?? 0 );
		$row['retention_days']    = isset( $row['retention_days'] )
			? (int) $row['retention_days']
			: self::DEFAULT_RETENTION_DAYS;
		$row['paused']            = (int) ( $row['paused'] ?? 0 );
		$row['close_enabled']     = (int) ( $row['close_enabled'] ?? 0 );
		$row['close_at']          = $row['close_at'] ?? null;
		$row['close_limit_enabled'] = (int) ( $row['close_limit_enabled'] ?? 0 );
		$row['close_limit']       = isset( $row['close_limit'] ) ? (int) $row['close_limit'] : 0;
		$row['submission_count']  = (int) ( $row['submission_count'] ?? 0 );
		$row['unavailable_message'] = $row['unavailable_message'] ?? '';
		$row['send_confirmation_email'] = (int) ( $row['send_confirmation_email'] ?? 0 );
		$row['confirmation_email_field_id'] = sanitize_key( $row['confirmation_email_field_id'] ?? '' );
		return $row;
	}
}

// phpcs:enable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
