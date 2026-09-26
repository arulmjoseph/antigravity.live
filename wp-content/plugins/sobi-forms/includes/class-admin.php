<?php
/**
 * Admin settings, submissions list, and dashboard notices.
 *
 * @package SobiForms
 */

defined( 'ABSPATH' ) || exit;

/**
 * WordPress admin UI for SobiForms settings and submissions.
 */
class Sobiforms_Admin {

	const SUBMISSIONS_INBOX_PER_PAGE = 50;

	public static function init() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_menu', array( __CLASS__, 'register_menus' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'admin_notices', array( __CLASS__, 'failed_mail_notice' ) );
		add_action( 'admin_notices', array( __CLASS__, 'nginx_upload_security_notice' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_dismiss_nginx_upload_notice' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_purge' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_auto_read_on_open' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_delete_submission' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_mark_read_submission' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_mark_all_read_submissions' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_mark_unread_submission' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_save_submission_note' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_toggle_star_submission' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_mark_submission_not_spam' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_mark_submission_spam' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_bulk_submissions' ) );
		add_action( 'admin_init', array( __CLASS__, 'handle_download_submission_file' ) );
		add_action( 'wp_dashboard_setup', array( __CLASS__, 'register_dashboard_widget' ) );
		add_filter( 'admin_body_class', array( __CLASS__, 'admin_body_class' ) );
		add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 2 );
	}

	/**
	 * Add a review link on the Plugins screen (after View details).
	 *
	 * @param string[] $plugin_meta Plugin row meta links.
	 * @param string   $plugin_file Plugin basename.
	 * @return string[]
	 */
	public static function plugin_row_meta( $plugin_meta, $plugin_file ) {
		if ( SOBIFORMS_BASENAME !== $plugin_file ) {
			return $plugin_meta;
		}

		$reviews_url = 'https://wordpress.org/plugins/sobi-forms/#reviews';
		$plugin_meta[] = sprintf(
			'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_url( $reviews_url ),
			esc_html__( 'Rate us on WordPress', 'sobi-forms' )
		);

		return $plugin_meta;
	}

	/**
	 * Add body classes for full-bleed admin screens.
	 *
	 * @param string $classes Space-separated admin body classes.
	 * @return string
	 */
	public static function admin_body_class( $classes ) {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Admin screen routing only.
		if ( isset( $_GET['page'] ) && 'sobiforms-submissions' === $_GET['page'] ) {
			$classes .= ' sobiforms-inbox-screen';
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		return $classes;
	}

	public static function register_menus() {
		$unread       = Sobiforms_Submission_Store::get_cached_unread_count();
		$unread_badge = self::format_unread_menu_badge( $unread );

		add_menu_page(
			__( 'Sobi Forms', 'sobi-forms' ),
			__( 'Sobi Forms', 'sobi-forms' ) . $unread_badge,
			'manage_options',
			'sobiforms-submissions',
			array( __CLASS__, 'render_submissions_page' ),
			'dashicons-email-alt',
			58
		);

		add_submenu_page(
			'sobiforms-submissions',
			__( 'Submissions', 'sobi-forms' ),
			__( 'Submissions', 'sobi-forms' ) . $unread_badge,
			'manage_options',
			'sobiforms-submissions',
			array( __CLASS__, 'render_submissions_page' )
		);

		add_submenu_page(
			'sobiforms-submissions',
			__( 'Forms', 'sobi-forms' ),
			__( 'Forms', 'sobi-forms' ),
			'manage_options',
			'sobiforms-forms',
			array( 'Sobiforms_Forms_Admin', 'render_list_page' )
		);

		add_submenu_page(
			'sobiforms-submissions',
			__( 'Resources', 'sobi-forms' ),
			__( 'Resources', 'sobi-forms' ),
			'manage_options',
			'sobiforms',
			array( __CLASS__, 'render_settings_page' )
		);
	}

	public static function register_settings() {
		register_setting(
			'sobiforms_settings',
			Sobiforms_Akismet::OPTION_USE_AKISMET,
			array(
				'type'              => 'boolean',
				'sanitize_callback' => static function ( $value ) {
					return ! empty( $value );
				},
				'default'           => false,
				'show_in_rest'        => false,
			)
		);
	}

	/**
	 * Dashboard widget: recent unread submissions (wp-admin home).
	 */
	public static function register_dashboard_widget() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_add_dashboard_widget(
			'sobiforms_unread_submissions',
			__( 'Sobi Forms — Unread submissions', 'sobi-forms' ),
			array( __CLASS__, 'render_dashboard_unread_widget' )
		);
	}

	/**
	 * Render unread submissions list for the dashboard widget.
	 */
	public static function render_dashboard_unread_widget() {
		$widget_limit  = 8;
		$unread_filter = array( 'unread' => '1' );
		$submissions   = Sobiforms_Submission_Store::get_list( 0, $widget_limit, '', true );
		$count_unread  = Sobiforms_Submission_Store::get_cached_unread_count();

		$forms_map = array();
		foreach ( Sobiforms_Form_Repository::get_all_light() as $form ) {
			$forms_map[ $form['id'] ] = $form['title'];
		}

		if ( empty( $submissions ) ) {
			$all_url = self::submissions_page_url( array(), array() );
			printf(
				'<p>%s</p>',
				wp_kses_post(
					sprintf(
						/* translators: %s: submissions admin URL */
						__( 'All caught up! No unread submissions. <a href="%s">View all submissions</a>', 'sobi-forms' ),
						esc_url( $all_url )
					)
				)
			);
			return;
		}

		echo '<ul class="sobiforms-dashboard-unread">';
		foreach ( $submissions as $row ) {
			$row_id     = absint( $row['id'] );
			$form_title = isset( $forms_map[ (int) $row['form_id'] ] ) ? $forms_map[ (int) $row['form_id'] ] : '—';
			$detail_url = self::submission_detail_url(
				$row_id,
				array( 'sobiforms_auto_read' => '1' ),
				$unread_filter
			);
			?>
			<li>
				<a href="<?php echo esc_url( $detail_url ); ?>">
					<?php echo esc_html( self::submission_row_title( $row ) ); ?>
				</a>
				<span class="sobiforms-dashboard-unread__meta">
					<?php echo esc_html( $form_title ); ?>
					<span aria-hidden="true"> · </span>
					<?php echo esc_html( self::dashboard_widget_date_display( $row['created_at'] ) ); ?>
				</span>
			</li>
			<?php
		}
		echo '</ul>';

		$footer_url = self::submissions_page_url( array(), $unread_filter );
		if ( $count_unread > $widget_limit ) {
			$footer_text = sprintf(
				/* translators: %s: number of unread submissions */
				__( 'View all %s unread', 'sobi-forms' ),
				number_format_i18n( $count_unread )
			);
		} else {
			$footer_text = __( 'View all unread', 'sobi-forms' );
		}

		printf(
			'<p class="sub"><a href="%s">%s</a></p>',
			esc_url( $footer_url ),
			esc_html( $footer_text )
		);
	}

	/**
	 * Compact relative date for inbox list and dashboard widget.
	 *
	 * Today (site timezone): time only (G:i). Same year: day + short month (j M).
	 * Older years: short month + year (M Y).
	 *
	 * @param string $mysql_datetime Row created_at (site-local mysql datetime).
	 * @return string
	 */
	private static function submission_relative_date_display( $mysql_datetime ) {
		if ( empty( $mysql_datetime ) ) {
			return '';
		}

		$timezone = wp_timezone();
		$datetime = date_create( $mysql_datetime, $timezone );
		if ( ! $datetime ) {
			return '';
		}

		$now = new DateTime( 'now', $timezone );
		if ( $datetime->format( 'Y-m-d' ) === $now->format( 'Y-m-d' ) ) {
			return date_i18n( 'G:i', $datetime->getTimestamp() );
		}

		if ( $datetime->format( 'Y' ) === $now->format( 'Y' ) ) {
			return date_i18n( 'j M', $datetime->getTimestamp() );
		}

		return date_i18n( 'M Y', $datetime->getTimestamp() );
	}

	/**
	 * Compact date for dashboard widget rows (narrow columns).
	 *
	 * @param string $mysql_datetime Row created_at (site-local mysql datetime).
	 * @return string
	 */
	private static function dashboard_widget_date_display( $mysql_datetime ) {
		return self::submission_relative_date_display( $mysql_datetime );
	}

	public static function handle_purge() {
		if (
			! isset( $_GET['sobiforms_purge'] ) ||
			! isset( $_GET['_wpnonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'sobiforms_purge' )
		) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$filter_args = self::get_submissions_filter_args();
		$state       = self::get_inbox_filter_state( $filter_args );
		$deleted     = Sobiforms_Submission_Store::purge_filtered(
			$state['form_id'],
			$state['search'],
			$state['unread_only'],
			$state['starred_only'],
			$state['spam_only'],
			$state['read_only'],
			$state['not_starred_only']
		);

		wp_safe_redirect(
			self::submissions_page_url(
				array( $deleted > 0 ? 'purged' : 'delete_error' => 1 ),
				$filter_args
			)
		);
		exit;
	}

	/**
	 * Mark a submission read when opened from the inbox list, then redirect to a clean URL.
	 *
	 * Avoids re-marking read after "Mark as unread" or other detail actions that reload the panel.
	 */
	public static function handle_auto_read_on_open() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- GET routing only; mark_read on list open.
		if (
			! isset( $_GET['page'] ) ||
			'sobiforms-submissions' !== sanitize_key( wp_unslash( $_GET['page'] ) ) ||
			! isset( $_GET['sobiforms_auto_read'] ) ||
			'1' !== $_GET['sobiforms_auto_read']
		) {
			return;
		}

		$id = isset( $_GET['sobiforms_submission_id'] ) ? absint( $_GET['sobiforms_submission_id'] ) : 0;
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( $id ) {
			Sobiforms_Submission_Store::mark_read( $id );
		}

		$filter_args = self::get_submissions_filter_args();
		wp_safe_redirect( self::submission_detail_url( $id, array(), $filter_args ) );
		exit;
	}

	public static function handle_delete_submission() {
		if (
			! isset( $_GET['sobiforms_delete_submission'] ) ||
			! isset( $_GET['_wpnonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'sobiforms_delete_submission' )
		) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$id      = absint( $_GET['sobiforms_delete_submission'] );
		$deleted = $id ? Sobiforms_Submission_Store::delete( $id ) : false;
		wp_safe_redirect(
			self::submissions_page_url(
				array( ( $deleted ? 'deleted' : 'delete_error' ) => 1 )
			)
		);
		exit;
	}

	/**
	 * Secure admin download for submission file fields.
	 */
	public static function handle_download_submission_file() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Verified below.
		if ( ! isset( $_GET['sobiforms_download_file'] ) ) {
			return;
		}

		if ( ! current_user_can( 'sobiforms_manage_submissions' ) ) {
			wp_die( esc_html__( 'You do not have permission to download this file.', 'sobi-forms' ) );
		}

		$submission_id = isset( $_GET['submission_id'] ) ? absint( $_GET['submission_id'] ) : 0;
		$field_id      = isset( $_GET['field_id'] ) ? sanitize_key( wp_unslash( $_GET['field_id'] ) ) : '';

		if ( ! $submission_id || '' === $field_id ) {
			wp_die( esc_html__( 'Invalid download request.', 'sobi-forms' ) );
		}

		check_admin_referer( 'sobiforms_download_file_' . $submission_id );

		$row = Sobiforms_Submission_Store::get_by_id( $submission_id );
		if ( ! $row || empty( $row['values'][ $field_id ] ) ) {
			wp_die( esc_html__( 'File not found.', 'sobi-forms' ) );
		}

		$item = $row['values'][ $field_id ];
		if ( 'file' !== ( $item['type'] ?? '' ) || empty( $item['relative_path'] ) ) {
			wp_die( esc_html__( 'File not found.', 'sobi-forms' ) );
		}

		$relative_path = (string) $item['relative_path'];
		if ( ! Sobiforms_File_Upload::relative_path_matches_submission( $relative_path, (int) $row['form_id'], $submission_id ) ) {
			wp_die( esc_html__( 'File not found.', 'sobi-forms' ) );
		}

		$path = Sobiforms_File_Upload::resolve_absolute_path( $relative_path );
		if ( ! $path || ! is_readable( $path ) ) {
			wp_die( esc_html__( 'File not found.', 'sobi-forms' ) );
		}

		$filename = sanitize_file_name( $item['original_name'] ?? basename( $path ) );
		$mime     = ! empty( $item['mime'] ) ? sanitize_mime_type( $item['mime'] ) : 'application/octet-stream';

		nocache_headers();
		header( 'Content-Type: ' . $mime );
		header( 'X-Content-Type-Options: nosniff' );
		header(
			'Content-Disposition: attachment; filename="' . str_replace( array( '"', "\r", "\n" ), '', $filename ) . '"'
		);
		header( 'Content-Length: ' . (string) filesize( $path ) );

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
		readfile( $path );
		exit;
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

	public static function handle_mark_read_submission() {
		if (
			! isset( $_GET['sobiforms_mark_read'] ) ||
			! isset( $_GET['_wpnonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'sobiforms_mark_read' )
		) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$id        = absint( $_GET['sobiforms_mark_read'] );
		$detail_id = isset( $_GET['sobiforms_submission_id'] ) ? absint( $_GET['sobiforms_submission_id'] ) : 0;
		$ok        = $id ? Sobiforms_Submission_Store::mark_read( $id ) : false;
		$key       = $ok ? 'marked_read' : 'mark_read_error';

		if ( $detail_id && $detail_id === $id ) {
			wp_safe_redirect( self::submission_detail_url( $id, array( $key => 1 ) ) );
		} else {
			wp_safe_redirect( self::submissions_page_url( array( $key => 1 ) ) );
		}
		exit;
	}

	/**
	 * Mark all submissions as read (optionally scoped to the filtered form).
	 */
	public static function handle_mark_all_read_submissions() {
		if (
			! isset( $_GET['sobiforms_mark_all_read'] ) ||
			! isset( $_GET['_wpnonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'sobiforms_mark_all_read' )
		) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$filter_args = self::get_submissions_filter_args();
		$state       = self::get_inbox_filter_state( $filter_args );

		Sobiforms_Submission_Store::mark_filtered_read(
			$state['form_id'],
			$state['search'],
			$state['unread_only'],
			$state['starred_only'],
			$state['spam_only'],
			$state['read_only'],
			$state['not_starred_only']
		);

		wp_safe_redirect( self::submissions_page_url( array( 'marked_all_read' => 1 ), $filter_args ) );
		exit;
	}

	/**
	 * Bulk actions on selected inbox rows (POST).
	 */
	public static function handle_bulk_submissions() {
		if (
			! isset( $_POST['sobiforms_bulk_action'] ) ||
			! isset( $_POST['sobiforms_bulk_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sobiforms_bulk_nonce'] ) ), 'sobiforms_bulk_submissions' )
		) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$action      = sanitize_key( wp_unslash( $_POST['sobiforms_bulk_action'] ) );
		$ids         = isset( $_POST['submission_ids'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['submission_ids'] ) ) : array();
		$ids         = array_values( array_unique( array_filter( $ids ) ) );
		$filter_args = self::get_submissions_filter_args( 'post' );
		$detail_id   = isset( $_POST['sobiforms_submission_id'] ) ? absint( $_POST['sobiforms_submission_id'] ) : 0;
		$flash       = 'bulk_error';

		if ( empty( $ids ) ) {
			wp_safe_redirect( self::submissions_page_url( array( $flash => 1 ), $filter_args ) );
			exit;
		}

		switch ( $action ) {
			case 'mark_read':
				$ok = true;
				foreach ( $ids as $id ) {
					if ( ! Sobiforms_Submission_Store::mark_read( $id ) ) {
						$ok = false;
					}
				}
				$flash = $ok ? 'bulk_marked_read' : 'bulk_error';
				break;

			case 'mark_unread':
				$ok = true;
				foreach ( $ids as $id ) {
					if ( ! Sobiforms_Submission_Store::mark_unread( $id ) ) {
						$ok = false;
					}
				}
				$flash = $ok ? 'bulk_marked_unread' : 'bulk_error';
				break;

			case 'star':
				foreach ( $ids as $id ) {
					Sobiforms_Submission_Store::set_starred( $id, true );
				}
				$flash = 'bulk_starred';
				break;

			case 'unstar':
				foreach ( $ids as $id ) {
					Sobiforms_Submission_Store::set_starred( $id, false );
				}
				$flash = 'bulk_unstarred';
				break;

			case 'mark_spam':
				foreach ( $ids as $id ) {
					$row = Sobiforms_Submission_Store::get_by_id( $id );
					if ( $row && ! Sobiforms_Submission_Store::is_spam( $row ) ) {
						Sobiforms_Akismet::submit_spam( $row );
						Sobiforms_Submission_Store::update_spam_status( $id, Sobiforms_Submission_Store::SPAM_SPAM );
					}
				}
				$flash = 'bulk_marked_spam';
				break;

			case 'mark_not_spam':
				foreach ( $ids as $id ) {
					$row = Sobiforms_Submission_Store::get_by_id( $id );
					if ( $row && Sobiforms_Submission_Store::is_spam( $row ) ) {
						Sobiforms_Akismet::submit_ham( $row );
						Sobiforms_Submission_Store::update_spam_status( $id, Sobiforms_Submission_Store::SPAM_HAM );
					}
				}
				$flash = 'bulk_marked_not_spam';
				break;

			case 'delete':
				$deleted = Sobiforms_Submission_Store::delete_ids( $ids );
				$flash     = $deleted > 0 ? 'bulk_deleted' : 'bulk_error';
				if ( $detail_id && in_array( $detail_id, $ids, true ) ) {
					$detail_id = 0;
				}
				break;
		}

		if ( $detail_id > 0 ) {
			wp_safe_redirect( self::submission_detail_url( $detail_id, array( $flash => 1 ), $filter_args ) );
		} else {
			wp_safe_redirect( self::submissions_page_url( array( $flash => 1 ), $filter_args ) );
		}
		exit;
	}

	public static function handle_mark_unread_submission() {
		if (
			! isset( $_GET['sobiforms_mark_unread'] ) ||
			! isset( $_GET['_wpnonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'sobiforms_mark_unread' )
		) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$id        = absint( $_GET['sobiforms_mark_unread'] );
		$detail_id = isset( $_GET['sobiforms_submission_id'] ) ? absint( $_GET['sobiforms_submission_id'] ) : 0;
		$ok        = $id ? Sobiforms_Submission_Store::mark_unread( $id ) : false;
		$key       = $ok ? 'marked_unread' : 'mark_unread_error';

		if ( $detail_id && $detail_id === $id ) {
			wp_safe_redirect( self::submission_detail_url( $id, array( $key => 1 ) ) );
		} else {
			wp_safe_redirect( self::submissions_page_url( array( $key => 1 ) ) );
		}
		exit;
	}

	public static function handle_save_submission_note() {
		if (
			! isset( $_POST['sobiforms_save_submission_note'] ) ||
			! isset( $_POST['sobiforms_note_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sobiforms_note_nonce'] ) ), 'sobiforms_save_submission_note' )
		) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$id   = isset( $_POST['sobiforms_submission_id'] ) ? absint( $_POST['sobiforms_submission_id'] ) : 0;
		$note = isset( $_POST['sobiforms_admin_note'] )
			? sanitize_textarea_field( wp_unslash( $_POST['sobiforms_admin_note'] ) )
			: '';
		$ok   = $id ? Sobiforms_Submission_Store::update_note( $id, $note ) : false;

		$key         = $ok ? 'note_saved' : 'note_error';
		$filter_args = self::get_submissions_filter_args( 'post' );
		if ( $id ) {
			wp_safe_redirect( self::submission_detail_url( $id, array( $key => 1 ), $filter_args ) );
		} else {
			wp_safe_redirect( self::submissions_page_url( array( $key => 1 ), $filter_args ) );
		}
		exit;
	}

	public static function handle_toggle_star_submission() {
		if (
			! isset( $_GET['sobiforms_toggle_star'] ) ||
			! isset( $_GET['_wpnonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'sobiforms_toggle_star' )
		) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$id        = absint( $_GET['sobiforms_toggle_star'] );
		$detail_id = isset( $_GET['sobiforms_submission_id'] ) ? absint( $_GET['sobiforms_submission_id'] ) : 0;

		if ( $id ) {
			Sobiforms_Submission_Store::toggle_starred( $id );
		}

		$filter_args = self::get_submissions_filter_args();
		if ( $detail_id ) {
			wp_safe_redirect( self::submission_detail_url( $detail_id, array(), $filter_args ) );
		} else {
			wp_safe_redirect( self::submissions_page_url( array(), $filter_args ) );
		}
		exit;
	}

	/**
	 * Mark a submission as not spam (ham) and report to Akismet.
	 */
	public static function handle_mark_submission_not_spam() {
		if (
			! isset( $_GET['sobiforms_not_spam'] ) ||
			! isset( $_GET['_wpnonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'sobiforms_not_spam' )
		) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$id        = absint( $_GET['sobiforms_not_spam'] );
		$detail_id = isset( $_GET['sobiforms_submission_id'] ) ? absint( $_GET['sobiforms_submission_id'] ) : 0;
		$row       = $id ? Sobiforms_Submission_Store::get_by_id( $id ) : null;
		$ok        = false;

		if ( $row && Sobiforms_Submission_Store::is_spam( $row ) ) {
			Sobiforms_Akismet::submit_ham( $row );
			$ok = Sobiforms_Submission_Store::update_spam_status( $id, Sobiforms_Submission_Store::SPAM_HAM );
		}

		$key = $ok ? 'marked_not_spam' : 'mark_not_spam_error';
		if ( $detail_id && $detail_id === $id ) {
			wp_safe_redirect( self::submission_detail_url( $id, array( $key => 1 ) ) );
		} else {
			wp_safe_redirect( self::submissions_page_url( array( $key => 1 ) ) );
		}
		exit;
	}

	/**
	 * Mark a submission as spam and report to Akismet.
	 */
	public static function handle_mark_submission_spam() {
		if (
			! isset( $_GET['sobiforms_mark_spam'] ) ||
			! isset( $_GET['_wpnonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'sobiforms_mark_spam' )
		) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$id        = absint( $_GET['sobiforms_mark_spam'] );
		$detail_id = isset( $_GET['sobiforms_submission_id'] ) ? absint( $_GET['sobiforms_submission_id'] ) : 0;
		$row       = $id ? Sobiforms_Submission_Store::get_by_id( $id ) : null;
		$ok        = false;

		if ( $row && ! Sobiforms_Submission_Store::is_spam( $row ) ) {
			Sobiforms_Akismet::submit_spam( $row );
			$ok = Sobiforms_Submission_Store::update_spam_status( $id, Sobiforms_Submission_Store::SPAM_SPAM );
		}

		$key = $ok ? 'marked_spam' : 'mark_spam_error';
		if ( $detail_id && $detail_id === $id ) {
			wp_safe_redirect( self::submission_detail_url( $id, array( $key => 1 ) ) );
		} else {
			wp_safe_redirect( self::submissions_page_url( array( $key => 1 ) ) );
		}
		exit;
	}

	/**
	 * Active submissions inbox filter query args from GET or POST.
	 *
	 * @param string $source Request bucket: get or post.
	 * @return array
	 */
	private static function get_submissions_filter_args( $source = 'get' ) {
		$args = array();

		// phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended -- Filter preservation for admin URLs.
		$bucket = ( 'post' === strtolower( $source ) ) ? $_POST : $_GET;

		if ( isset( $bucket['sobiforms_form_id'] ) ) {
			$form_id = absint( $bucket['sobiforms_form_id'] );
			if ( $form_id > 0 ) {
				$args['sobiforms_form_id'] = $form_id;
			}
		}

		if ( isset( $bucket['s'] ) && '' !== $bucket['s'] ) {
			$args['s'] = sanitize_text_field( wp_unslash( $bucket['s'] ) );
		}

		if ( isset( $bucket['unread'] ) && '1' === $bucket['unread'] ) {
			$args['unread'] = '1';
		}

		if ( isset( $bucket['starred'] ) && '1' === $bucket['starred'] ) {
			$args['starred'] = '1';
		}

		if ( isset( $bucket['spam'] ) && '1' === $bucket['spam'] ) {
			$args['spam'] = '1';
		}

		if ( isset( $bucket['read'] ) && '1' === $bucket['read'] ) {
			$args['read'] = '1';
		}

		if ( isset( $bucket['not_starred'] ) && '1' === $bucket['not_starred'] ) {
			$args['not_starred'] = '1';
		}

		if ( isset( $bucket['paged'] ) ) {
			$paged = absint( $bucket['paged'] );
			if ( $paged > 1 ) {
				$args['paged'] = $paged;
			}
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		return $args;
	}

	/**
	 * Normalized inbox list filter flags from query args.
	 *
	 * @param array $filter_args Filter query args.
	 * @return array
	 */
	private static function get_inbox_filter_state( $filter_args ) {
		return array(
			'form_id'          => isset( $filter_args['sobiforms_form_id'] ) ? absint( $filter_args['sobiforms_form_id'] ) : 0,
			'search'           => isset( $filter_args['s'] ) ? $filter_args['s'] : '',
			'unread_only'      => isset( $filter_args['unread'] ) && '1' === $filter_args['unread'],
			'starred_only'     => isset( $filter_args['starred'] ) && '1' === $filter_args['starred'],
			'spam_only'        => isset( $filter_args['spam'] ) && '1' === $filter_args['spam'],
			'read_only'        => isset( $filter_args['read'] ) && '1' === $filter_args['read'],
			'not_starred_only' => isset( $filter_args['not_starred'] ) && '1' === $filter_args['not_starred'],
		);
	}

	/**
	 * Whether the inbox list is scoped by form, search, or a view filter.
	 *
	 * @param array $state Normalized filter state.
	 * @return bool
	 */
	private static function submissions_inbox_filters_active( $state ) {
		return $state['form_id'] > 0
			|| '' !== $state['search']
			|| $state['unread_only']
			|| $state['starred_only']
			|| $state['spam_only']
			|| $state['read_only']
			|| $state['not_starred_only'];
	}

	/**
	 * URL that clears all inbox filters.
	 *
	 * @return string
	 */
	private static function submissions_inbox_reset_filters_url() {
		return self::submissions_page_url( array(), array() );
	}

	/**
	 * Active inbox view slug for toolbar highlighting.
	 *
	 * @param array $state Normalized filter state.
	 * @return string
	 */
	private static function get_inbox_active_view( $state ) {
		if ( $state['spam_only'] ) {
			return 'spam';
		}
		if ( $state['starred_only'] ) {
			return 'starred';
		}
		if ( $state['not_starred_only'] ) {
			return 'not_starred';
		}
		if ( $state['unread_only'] ) {
			return 'unread';
		}
		if ( $state['read_only'] ) {
			return 'read';
		}
		return 'all';
	}

	/**
	 * Build inbox filter URL for a view slug.
	 *
	 * @param string $view_slug   all|read|unread|starred|not_starred|spam|not_spam.
	 * @param array  $view_base   Preserved form + search args.
	 * @return string
	 */
	private static function inbox_view_url( $view_slug, $view_base = array() ) {
		$args = array_merge( array( 'page' => 'sobiforms-submissions' ), $view_base );

		switch ( $view_slug ) {
			case 'read':
				$args['read'] = '1';
				break;
			case 'unread':
				$args['unread'] = '1';
				break;
			case 'starred':
				$args['starred'] = '1';
				break;
			case 'not_starred':
				$args['not_starred'] = '1';
				break;
			case 'spam':
				$args['spam'] = '1';
				break;
			case 'not_spam':
				break;
			default:
				break;
		}

		return add_query_arg( $args, admin_url( 'admin.php' ) );
	}

	/**
	 * Hidden inputs so POST handlers preserve inbox filters on redirect.
	 *
	 * @param array $filter_args Filter query args.
	 */
	private static function render_submissions_filter_hidden_inputs( $filter_args ) {
		if ( isset( $filter_args['sobiforms_form_id'] ) ) {
			printf(
				'<input type="hidden" name="sobiforms_form_id" value="%s" />',
				esc_attr( $filter_args['sobiforms_form_id'] )
			);
		}

		if ( isset( $filter_args['s'] ) ) {
			printf(
				'<input type="hidden" name="s" value="%s" />',
				esc_attr( $filter_args['s'] )
			);
		}

		foreach ( array( 'unread', 'starred', 'spam', 'read', 'not_starred' ) as $flag ) {
			if ( isset( $filter_args[ $flag ] ) && '1' === $filter_args[ $flag ] ) {
				printf( '<input type="hidden" name="%s" value="1" />', esc_attr( $flag ) );
			}
		}

		if ( isset( $filter_args['paged'] ) && absint( $filter_args['paged'] ) > 1 ) {
			printf(
				'<input type="hidden" name="paged" value="%s" />',
				esc_attr( $filter_args['paged'] )
			);
		}
	}

	/**
	 * Build submissions admin URL preserving list filters.
	 *
	 * @param array      $extra       Extra query args.
	 * @param array|null $filter_args Explicit filter args; defaults to current request.
	 * @return string
	 */
	private static function submissions_page_url( $extra = array(), $filter_args = null ) {
		if ( null === $filter_args ) {
			$filter_args = self::get_submissions_filter_args();
		}

		$args = array_merge(
			array( 'page' => 'sobiforms-submissions' ),
			$filter_args,
			$extra
		);

		if ( isset( $args['paged'] ) && absint( $args['paged'] ) <= 1 ) {
			unset( $args['paged'] );
		}

		return add_query_arg( $args, admin_url( 'admin.php' ) );
	}

	/**
	 * URL for a single submission detail screen.
	 *
	 * @param int        $submission_id Submission ID.
	 * @param array      $extra         Extra query args.
	 * @param array|null $filter_args   Explicit filter args; defaults to current request.
	 * @return string
	 */
	private static function submission_detail_url( $submission_id, $extra = array(), $filter_args = null ) {
		return self::submissions_page_url(
			array_merge(
				array( 'sobiforms_submission_id' => absint( $submission_id ) ),
				$extra
			),
			$filter_args
		);
	}

	/**
	 * Primary list label for a submission row.
	 *
	 * @param array $row Submission row.
	 * @return string
	 */
	private static function submission_row_title( $row ) {
		$title_parts = array_filter( array( $row['name'] ?? '', $row['email'] ?? '' ) );
		if ( ! empty( $title_parts ) ) {
			return implode( ' — ', $title_parts );
		}

		return sprintf(
			/* translators: %d: submission ID */
			__( 'Submission #%d', 'sobi-forms' ),
			absint( $row['id'] )
		);
	}

	/**
	 * Whether a submission has frozen source page metadata.
	 *
	 * @param array $row Submission row.
	 * @return bool
	 */
	private static function submission_has_source( $row ) {
		$title = trim( (string) ( $row['source_title'] ?? '' ) );
		$path  = trim( (string) ( $row['source_path'] ?? '' ) );
		return '' !== $title || '' !== $path;
	}

	public static function failed_mail_notice() {
		$failed = Sobiforms_Submission_Store::count_failed();
		if ( $failed < 1 ) {
			return;
		}

		$url = admin_url( 'admin.php?page=sobiforms-submissions' );
		printf(
			'<div class="notice notice-warning is-dismissible"><p>%s</p></div>',
			wp_kses_post(
				sprintf(
					/* translators: 1: number of failed submissions, 2: submissions admin URL */
					_n(
						'<strong>Sobi Forms:</strong> %1$d submission was not delivered by email. <a href="%2$s">View submissions</a>',
						'<strong>Sobi Forms:</strong> %1$d submissions were not delivered by email. <a href="%2$s">View submissions</a>',
						$failed,
						'sobi-forms'
					),
					$failed,
					esc_url( $url )
				)
			)
		);
	}

	/**
	 * Warn when Nginx serves uploads/sobiforms/ without a deny rule.
	 */
	public static function nginx_upload_security_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! Sobiforms_File_Upload::should_show_nginx_upload_security_notice() ) {
			return;
		}

		$docs_url    = admin_url( 'admin.php?page=sobiforms&tab=usage' );
		$dismiss_url = wp_nonce_url(
			add_query_arg( 'sobiforms_dismiss_nginx_upload_notice', '1' ),
			'sobiforms_dismiss_nginx_upload_notice'
		);

		printf(
			'<div class="notice notice-error"><p>%s</p><p><a href="%s">%s</a> · <a href="%s">%s</a></p></div>',
			esc_html__(
				'Sobi Forms: your server appears to be running Nginx, and uploaded submission files may be reachable directly over the web. Add a deny rule for wp-content/uploads/sobiforms/ in your Nginx config (see Resources → Usage).',
				'sobi-forms'
			),
			esc_url( $docs_url ),
			esc_html__( 'View setup instructions', 'sobi-forms' ),
			esc_url( $dismiss_url ),
			esc_html__( 'Dismiss for one week', 'sobi-forms' )
		);
	}

	/**
	 * Dismiss the Nginx upload security notice for the current user.
	 */
	public static function handle_dismiss_nginx_upload_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( empty( $_GET['sobiforms_dismiss_nginx_upload_notice'] ) ) {
			return;
		}

		check_admin_referer( 'sobiforms_dismiss_nginx_upload_notice' );

		update_user_meta( get_current_user_id(), 'sobiforms_dismiss_nginx_upload_notice', time() );

		wp_safe_redirect( remove_query_arg( 'sobiforms_dismiss_nginx_upload_notice', wp_get_referer() ? wp_get_referer() : admin_url() ) );
		exit;
	}

	/**
	 * WordPress-style pending count bubble for unread submissions.
	 *
	 * @param int $count Unread count.
	 * @return string
	 */
	private static function format_unread_menu_badge( $count ) {
		if ( $count <= 0 ) {
			return '';
		}

		return sprintf(
			' <span class="awaiting-mod count-%1$d"><span class="pending-count">%2$s</span></span>',
			(int) $count,
			esc_html( number_format_i18n( $count ) )
		);
	}

	/**
	 * Human-readable mail delivery status for admin lists.
	 *
	 * @param string $status Raw status slug.
	 * @return string
	 */
	public static function mail_status_label( $status ) {
		$labels = array(
			'sent'    => __( 'Sent', 'sobi-forms' ),
			'failed'  => __( 'Failed', 'sobi-forms' ),
			'pending' => __( 'Pending', 'sobi-forms' ),
			'skipped' => __( 'No email', 'sobi-forms' ),
		);

		return $labels[ $status ] ?? ucfirst( $status );
	}

	public static function enqueue_assets( $hook ) {
		// Admin screen hooks use the `sobiforms-*` menu slug prefix (not the plugin folder name).
		if ( false === strpos( $hook, 'sobiforms' ) ) {
			return;
		}

		wp_enqueue_style( 'sobiforms-admin', SOBIFORMS_URL . 'assets/css/admin.css', array(), SOBIFORMS_VERSION );

		$inbox_hooks = array(
			'toplevel_page_sobiforms-submissions',
			'sobiforms-submissions_page_sobiforms-submissions',
		);
		if ( in_array( $hook, $inbox_hooks, true ) ) {
			wp_enqueue_script(
				'sobiforms-admin-inbox',
				SOBIFORMS_URL . 'assets/js/admin-inbox.js',
				array(),
				SOBIFORMS_VERSION,
				true
			);
			wp_localize_script(
				'sobiforms-admin-inbox',
				'sobiformsInbox',
				array(
					'confirmDeleteSelected' => __( 'Delete selected submissions?', 'sobi-forms' ),
					'confirmDeleteAll'      => __( 'Delete all submissions matching the current filters?', 'sobi-forms' ),
					'confirmMarkAllRead'    => __( 'Mark all submissions matching the current filters as read?', 'sobi-forms' ),
				)
			);
		}

		$list_hooks = array(
			'toplevel_page_sobiforms-submissions',
			'sobiforms-submissions_page_sobiforms-submissions',
			'sobiforms-submissions_page_sobiforms-forms',
		);
		if ( in_array( $hook, $list_hooks, true ) ) {
			wp_enqueue_script( 'common' );
		}
	}

	private static function get_settings_tabs() {
		return array(
			'usage'    => __( 'Usage', 'sobi-forms' ),
			'feedback' => __( 'Feedback', 'sobi-forms' ));
	}

	private static function get_active_tab() {
		$tabs    = self::get_settings_tabs();
		$tab_raw = filter_input( INPUT_GET, 'tab' );
		$tab     = is_string( $tab_raw ) && '' !== $tab_raw ? sanitize_key( $tab_raw ) : 'usage';
		return array_key_exists( $tab, $tabs ) ? $tab : 'usage';
	}

	private static function render_settings_tabs( $active ) {
		echo '<nav class="nav-tab-wrapper wp-clearfix">';
		foreach ( self::get_settings_tabs() as $slug => $label ) {
			$url   = add_query_arg( array( 'page' => 'sobiforms', 'tab' => $slug ), admin_url( 'admin.php' ) );
			$class = 'nav-tab' . ( $slug === $active ? ' nav-tab-active' : '' );
			printf( '<a href="%s" class="%s">%s</a>', esc_url( $url ), esc_attr( $class ), esc_html( $label ) );
		}
		echo '</nav>';
	}

	public static function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$active_tab = self::get_active_tab();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Resources', 'sobi-forms' ); ?></h1>
			<?php self::render_settings_tabs( $active_tab ); ?>
			<div class="sobiforms-settings-tab <?php echo esc_attr( 'usage' === $active_tab ? 'is-active' : '' ); ?>">
				<?php self::render_tab_usage(); ?>
			</div>
			<div class="sobiforms-settings-tab <?php echo esc_attr( 'feedback' === $active_tab ? 'is-active' : '' ); ?>">
				<?php self::render_tab_feedback(); ?>
			</div>
		</div>
		<?php
	}

	private static function render_tab_usage() {
		$forms_url     = admin_url( 'admin.php?page=sobiforms-forms' );
		$akismet_ready = Sobiforms_Akismet::is_akismet_ready();
		$use_akismet   = (bool) get_option( Sobiforms_Akismet::OPTION_USE_AKISMET, 0 );
		?>
		<div class="sobiforms-usage-card">
			<h2 style="margin-top:0;"><?php esc_html_e( 'Spam protection', 'sobi-forms' ); ?></h2>
			<?php if ( $akismet_ready ) : ?>
				<form method="post" action="options.php">
					<?php settings_fields( 'sobiforms_settings' ); ?>
					<input type="hidden" name="<?php echo esc_attr( Sobiforms_Akismet::OPTION_USE_AKISMET ); ?>" value="0" />
					<label class="sobiforms-settings-checkbox">
						<input
							type="checkbox"
							name="<?php echo esc_attr( Sobiforms_Akismet::OPTION_USE_AKISMET ); ?>"
							value="1"
							<?php checked( $use_akismet ); ?>
						/>
						<?php esc_html_e( 'Use Akismet to filter form spam', 'sobi-forms' ); ?>
					</label>
					<p class="description">
						<?php esc_html_e( 'When enabled, submission content is sent to Akismet. Spam is quarantined in the Submissions inbox and notification emails are not sent.', 'sobi-forms' ); ?>
					</p>
					<?php submit_button( __( 'Save spam settings', 'sobi-forms' ) ); ?>
				</form>
			<?php else : ?>
				<p><?php esc_html_e( 'Install and configure the Akismet plugin to enable spam filtering for forms.', 'sobi-forms' ); ?></p>
			<?php endif; ?>

			<h2><?php esc_html_e( 'Create forms', 'sobi-forms' ); ?></h2>
			<p><?php esc_html_e( 'Build and manage forms in the Forms screen. Per-form options (email, storage, retention) are in each form’s settings sidebar.', 'sobi-forms' ); ?></p>
			<p><a href="<?php echo esc_url( $forms_url ); ?>" class="button"><?php esc_html_e( 'Go to Forms', 'sobi-forms' ); ?></a></p>

			<h2><?php esc_html_e( 'Shortcode', 'sobi-forms' ); ?></h2>
			<p><?php esc_html_e( 'Works in Gutenberg, Divi, Elementor, widgets, and anywhere shortcodes are supported:', 'sobi-forms' ); ?></p>
			<code>[sobiforms id="1"]</code>
			<p class="description"><?php esc_html_e( 'Or by slug:', 'sobi-forms' ); ?> <code>[sobiforms slug="contact"]</code></p>

			<h2><?php esc_html_e( 'Gutenberg block', 'sobi-forms' ); ?></h2>
			<p><?php esc_html_e( 'Search for "Sobi Forms" and pick a form from the dropdown.', 'sobi-forms' ); ?></p>

			<h2><?php esc_html_e( 'Pre-fill from URL', 'sobi-forms' ); ?></h2>
			<p><?php esc_html_e( 'Pre-populate text, email, phone, link, number, and long-text fields from query parameters. Set the parameter name in each field’s settings (gear menu).', 'sobi-forms' ); ?></p>
			<code>https://example.com/contact/?name=Alex&amp;email=alex@example.com</code>
			<p class="description">
				<?php esc_html_e( 'Works with full-page HTML cache (values are applied in the browser).', 'sobi-forms' ); ?>
			</p>

			<h2><?php esc_html_e( 'Hidden fields', 'sobi-forms' ); ?></h2>
			<p><?php esc_html_e( 'Capture campaign or tracking metadata without cluttering the form. In the form builder sidebar, open Hidden fields and add rows with an inbox label, optional URL parameter, and optional default value. Visitors never see these fields; values are always submitted (even when empty) and appear in your inbox.', 'sobi-forms' ); ?></p>
			<code>https://example.com/landing/?utm_source=newsletter&amp;utm_campaign=spring</code>
			<p class="description">
				<?php esc_html_e( 'Values are resolved in the browser (URL parameter, then default, then empty).', 'sobi-forms' ); ?>
			</p>

			<h2><?php esc_html_e( 'Submission limit', 'sobi-forms' ); ?></h2>
			<p><?php esc_html_e( 'In Availability, enable Close after submission limit and set a maximum. The form closes automatically once that many non-spam submissions are received. The builder shows a read-only counter (e.g. 42 / 100) so you can raise the limit to reopen the form. The counter is not reset when you disable the limit.', 'sobi-forms' ); ?></p>

			<h2><?php esc_html_e( 'File uploads', 'sobi-forms' ); ?></h2>
			<p><?php esc_html_e( 'Add a File upload field to let visitors attach one file per field (for example a CV or photo). Choose allowed types by category (Application, Image, Text) or pick individual extensions. Default max size is 5 MB, capped by the server limit. Save to WordPress must stay enabled — file uploads require database storage.', 'sobi-forms' ); ?></p>
			<p><?php esc_html_e( 'Files are stored in a private folder under wp-content/uploads/sobiforms/ (not the Media Library). Download attachments from the Submissions inbox. Notification emails list the filename and link to the inbox; files are not attached to emails.', 'sobi-forms' ); ?></p>
			<p class="description"><?php esc_html_e( 'On Apache, Sobi Forms writes a .htaccess file that denies all direct HTTP access to the upload folder (files are downloaded through the admin inbox only). On Nginx, add a location block to deny all web access to that directory:', 'sobi-forms' ); ?></p>
			<pre class="sobiforms-code-snippet"><code>location ^~ /wp-content/uploads/sobiforms/ {
    deny all;
    return 403;
}</code></pre>
		</div>
		<?php
	}

	private static function render_tab_feedback() {
		$reviews_url  = 'https://wordpress.org/plugins/sobi-forms/#reviews';
		$support_url  = 'https://wordpress.org/support/plugin/sobi-forms/';
		$roadmap_url  = 'https://sobiforms.com/roadmap/';
		?>
		<div class="sobiforms-usage-card">
			<h2 style="margin-top:0;"><?php esc_html_e( 'Get help or share feedback', 'sobi-forms' ); ?></h2>
			<p><?php esc_html_e( 'Sobi Forms is built in the open. Choose the channel that fits what you need.', 'sobi-forms' ); ?></p>

			<h2><?php esc_html_e( 'Leave a review', 'sobi-forms' ); ?></h2>
			<p><?php esc_html_e( 'Enjoying Sobi Forms? A 5-star rating on WordPress.org helps others discover the plugin and supports ongoing development.', 'sobi-forms' ); ?></p>
			<p>
				<a href="<?php echo esc_url( $reviews_url ); ?>" class="button button-primary" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Rate on WordPress.org', 'sobi-forms' ); ?>
				</a>
			</p>

			<h2><?php esc_html_e( 'Support forum', 'sobi-forms' ); ?></h2>
			<p><?php esc_html_e( 'Ask questions, report bugs, or get help with setup on the official WordPress.org support forum.', 'sobi-forms' ); ?></p>
			<p>
				<a href="<?php echo esc_url( $support_url ); ?>" class="button" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Open support forum', 'sobi-forms' ); ?>
				</a>
			</p>

			<h2><?php esc_html_e( 'Roadmap & ideas', 'sobi-forms' ); ?></h2>
			<p><?php esc_html_e( 'See what we are building next, vote on upcoming features, and submit your own ideas for the plugin.', 'sobi-forms' ); ?></p>
			<p>
				<a href="<?php echo esc_url( $roadmap_url ); ?>" class="button" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'View roadmap', 'sobi-forms' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	public static function render_submissions_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		self::render_submissions_inbox_shell();
	}

	/**
	 * Shared inbox data: filters, counts, submissions, forms map, selection.
	 *
	 * @return array
	 */
	private static function get_submissions_inbox_context() {
		$filter_args    = self::get_submissions_filter_args();
		$state          = self::get_inbox_filter_state( $filter_args );
		$filter_form_id = $state['form_id'];
		$search         = $state['search'];
		$unread_only    = $state['unread_only'];
		$starred_only   = $state['starred_only'];
		$spam_only      = $state['spam_only'];
		$read_only      = $state['read_only'];
		$not_starred_only = $state['not_starred_only'];

		$selected_id = (int) filter_input( INPUT_GET, 'sobiforms_submission_id', FILTER_VALIDATE_INT );

		$per_page    = self::SUBMISSIONS_INBOX_PER_PAGE;
		$paged       = isset( $filter_args['paged'] ) ? absint( $filter_args['paged'] ) : 1;
		$counts      = Sobiforms_Submission_Store::get_inbox_counts(
			$filter_form_id,
			$search,
			$unread_only,
			$starred_only,
			$spam_only,
			$read_only,
			$not_starred_only
		);
		$items_total = $counts['items_total'];
		$total_pages = max( 1, (int) ceil( $items_total / $per_page ) );
		$paged       = min( max( 1, $paged ), $total_pages );
		$offset      = ( $paged - 1 ) * $per_page;

		if ( $paged > 1 ) {
			$filter_args['paged'] = $paged;
		} else {
			unset( $filter_args['paged'] );
		}

		$submissions = Sobiforms_Submission_Store::get_list(
			$filter_form_id,
			$per_page,
			$search,
			$unread_only,
			$starred_only,
			$spam_only,
			$offset,
			$read_only,
			$not_starred_only
		);
		$count_all           = $counts['count_all'];
		$count_unread        = $counts['count_unread'];
		$count_starred       = $counts['count_starred'];
		$count_spam          = $counts['count_spam'];
		$forms               = Sobiforms_Form_Repository::get_all_light();
		$forms_map           = array();
		$total_submissions   = $counts['total_submissions'];

		foreach ( $forms as $form ) {
			$forms_map[ $form['id'] ] = $form['title'];
		}

		$selected_row    = null;
		$selected_invalid = false;
		if ( $selected_id > 0 ) {
			$selected_row = Sobiforms_Submission_Store::get_by_id( $selected_id );
			if ( ! $selected_row ) {
				$selected_invalid = true;
			}
		}

		return array(
			'filter_args'      => $filter_args,
			'filter_form_id'   => $filter_form_id,
			'search'           => $search,
			'unread_only'      => $unread_only,
			'starred_only'     => $starred_only,
			'spam_only'        => $spam_only,
			'read_only'        => $read_only,
			'not_starred_only' => $not_starred_only,
			'filters_active'   => self::submissions_inbox_filters_active( $state ),
			'active_view'      => self::get_inbox_active_view( $state ),
			'selected_id'      => $selected_id,
			'selected_row'     => $selected_row,
			'selected_invalid' => $selected_invalid,
			'submissions'      => $submissions,
			'items_total'      => $items_total,
			'paged'            => $paged,
			'per_page'         => $per_page,
			'total_pages'      => $total_pages,
			'list_range_start' => $items_total > 0 ? $offset + 1 : 0,
			'list_range_end'   => $items_total > 0 ? min( $offset + count( $submissions ), $items_total ) : 0,
			'count_all'        => $count_all,
			'count_unread'     => $count_unread,
			'count_starred'    => $count_starred,
			'count_spam'       => $count_spam,
			'total_submissions' => $total_submissions,
			'forms'            => $forms,
			'forms_map'        => $forms_map,
		);
	}

	/**
	 * Submissions inbox: filters + split list/detail layout.
	 */
	private static function render_submissions_inbox_shell() {
		$ctx = self::get_submissions_inbox_context();

		self::render_submission_notices();
		?>
		<div class="wrap sobiforms-admin-list sobiforms-submissions-inbox-page">
			<div class="sobiforms-submissions-inbox" data-sobiforms-inbox>
				<div class="sobiforms-submissions-inbox__list" role="navigation" aria-label="<?php esc_attr_e( 'Submissions list', 'sobi-forms' ); ?>">
					<?php self::render_submissions_list_header( $ctx ); ?>
					<div class="sobiforms-inbox-list__body">
						<?php self::render_submissions_list_panel( $ctx ); ?>
					</div>
				</div>
				<div
					class="sobiforms-submissions-inbox__resize"
					role="separator"
					aria-orientation="vertical"
					aria-label="<?php esc_attr_e( 'Resize submission list', 'sobi-forms' ); ?>"
					tabindex="0"
				></div>
				<div
					class="sobiforms-submissions-inbox__detail"
					role="region"
					aria-label="<?php esc_attr_e( 'Submission detail', 'sobi-forms' ); ?>"
				>
					<?php self::render_submission_detail_panel( $ctx ); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Filters and search at the top of the inbox list column.
	 *
	 * @param array $ctx Inbox context from get_submissions_inbox_context().
	 */
	private static function render_submissions_list_header( $ctx ) {
		$filter_args      = $ctx['filter_args'];
		$filter_form_id   = $ctx['filter_form_id'];
		$search           = $ctx['search'];
		$selected_id      = $ctx['selected_id'];
		$forms            = $ctx['forms'];
		$items_total      = $ctx['items_total'];
		$paged            = $ctx['paged'];
		$total_pages      = $ctx['total_pages'];
		$list_range_start = $ctx['list_range_start'];
		$list_range_end   = $ctx['list_range_end'];
		$active_view      = $ctx['active_view'];
		$count_all        = $ctx['count_all'];
		$count_unread     = $ctx['count_unread'];
		$count_starred    = $ctx['count_starred'];
		$count_spam       = $ctx['count_spam'];

		$view_tabs = array(
			'all'     => array(
				'label' => __( 'All', 'sobi-forms' ),
				'count' => $count_all,
			),
			'unread'  => array(
				'label' => __( 'Unread', 'sobi-forms' ),
				'count' => $count_unread,
			),
			'starred' => array(
				'label' => __( 'Starred', 'sobi-forms' ),
				'count' => $count_starred,
			),
			'spam'    => array(
				'label' => __( 'Spam', 'sobi-forms' ),
				'count' => $count_spam,
			),
		);

		$select_scopes = array(
			'all'         => __( 'All', 'sobi-forms' ),
			'none'        => __( 'None', 'sobi-forms' ),
			'read'        => __( 'Read', 'sobi-forms' ),
			'unread'      => __( 'Unread', 'sobi-forms' ),
			'starred'     => __( 'Starred', 'sobi-forms' ),
			'not_starred' => __( 'Not starred', 'sobi-forms' ),
			'spam'        => __( 'Spam', 'sobi-forms' ),
			'not_spam'    => __( 'Not spam', 'sobi-forms' ),
		);

		$mark_all_url = wp_nonce_url(
			self::submissions_page_url( array( 'sobiforms_mark_all_read' => 1 ), $filter_args ),
			'sobiforms_mark_all_read'
		);
		$purge_url    = wp_nonce_url(
			self::submissions_page_url( array( 'sobiforms_purge' => 1 ), $filter_args ),
			'sobiforms_purge'
		);

		$view_base = array();
		if ( $filter_form_id ) {
			$view_base['sobiforms_form_id'] = $filter_form_id;
		}
		if ( '' !== $search ) {
			$view_base['s'] = $search;
		}
		if ( $selected_id ) {
			$view_base['sobiforms_submission_id'] = $selected_id;
		}

		$prev_url = $paged > 1
			? self::submissions_page_url( array( 'paged' => $paged - 1 ), $filter_args )
			: '';
		$next_url = $paged < $total_pages
			? self::submissions_page_url( array( 'paged' => $paged + 1 ), $filter_args )
			: '';

		$form_filter_args = $filter_args;
		unset( $form_filter_args['paged'] );

		$form_nav_extra = array();
		if ( $selected_id ) {
			$form_nav_extra['sobiforms_submission_id'] = $selected_id;
		}

		$all_forms_filter = $form_filter_args;
		unset( $all_forms_filter['sobiforms_form_id'] );
		$all_forms_url    = self::submissions_page_url( $form_nav_extra, $all_forms_filter );

		$current_form_label = __( 'All forms', 'sobi-forms' );
		if ( $filter_form_id ) {
			foreach ( $forms as $form ) {
				if ( (int) $form['id'] === $filter_form_id ) {
					$current_form_label = $form['title'];
					break;
				}
			}
		}
		?>
		<div class="sobiforms-inbox-list__header">
			<div class="sobiforms-inbox-list__form-tab-bar">
				<nav class="sobiforms-inbox-view-tabs" aria-label="<?php esc_attr_e( 'Submission views', 'sobi-forms' ); ?>">
					<?php foreach ( $view_tabs as $slug => $tab ) : ?>
						<?php
						$view_url   = self::inbox_view_url( $slug, $view_base );
						$is_active  = $active_view === $slug;
						$show_count = in_array( $slug, array( 'unread', 'starred', 'spam' ), true ) && $tab['count'] > 0;
						?>
						<a
							href="<?php echo esc_url( $view_url ); ?>"
							class="sobiforms-inbox-view-tab<?php echo $is_active ? ' is-active' : ''; ?>"
							<?php echo $is_active ? 'aria-current="page"' : ''; ?>
						>
							<?php echo esc_html( $tab['label'] ); ?>
							<?php if ( $show_count ) : ?>
								<span class="sobiforms-inbox-view-tab__count"><?php echo esc_html( number_format_i18n( $tab['count'] ) ); ?></span>
							<?php endif; ?>
						</a>
					<?php endforeach; ?>
				</nav>
				<div class="sobiforms-inbox-form-tab" data-sobiforms-form-filter>
					<button
						type="button"
						class="sobiforms-inbox-form-tab__trigger"
						data-sobiforms-form-filter-toggle
						aria-expanded="false"
						aria-haspopup="true"
						aria-label="<?php esc_attr_e( 'Filter by form', 'sobi-forms' ); ?>"
					>
						<?php Sobiforms_Admin_Icons::render( 'filter', array( 'size' => 16, 'class' => 'sobiforms-inbox-form-tab__icon' ) ); ?>
						<span class="sobiforms-inbox-form-tab__label"><?php echo esc_html( $current_form_label ); ?></span>
						<?php Sobiforms_Admin_Icons::render( 'chevron-down', array( 'size' => 16, 'class' => 'sobiforms-inbox-form-tab__chevron' ) ); ?>
					</button>
					<ul class="sobiforms-inbox-form-tab__menu" hidden>
						<li>
							<a href="<?php echo esc_url( $all_forms_url ); ?>" class="<?php echo ! $filter_form_id ? 'is-current' : ''; ?>">
								<?php esc_html_e( 'All forms', 'sobi-forms' ); ?>
							</a>
						</li>
						<?php foreach ( $forms as $form ) : ?>
							<?php
							$form_args = $form_filter_args;
							$form_args['sobiforms_form_id'] = $form['id'];
							$form_url = self::submissions_page_url( $form_nav_extra, $form_args );
							?>
							<li>
								<a
									href="<?php echo esc_url( $form_url ); ?>"
									class="<?php echo (int) $filter_form_id === (int) $form['id'] ? 'is-current' : ''; ?>"
								>
									<?php echo esc_html( $form['title'] ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>

			<form method="get" id="sobiforms-submissions-filter" class="sobiforms-inbox-list__search-form">
				<input type="hidden" name="page" value="sobiforms-submissions" />
				<?php if ( $selected_id ) : ?>
					<input type="hidden" name="sobiforms_submission_id" value="<?php echo esc_attr( $selected_id ); ?>" />
				<?php endif; ?>
				<?php self::render_submissions_filter_hidden_inputs( $filter_args ); ?>
				<label class="screen-reader-text" for="sobiforms-submissions-search"><?php esc_html_e( 'Search submissions', 'sobi-forms' ); ?></label>
				<div class="sobiforms-inbox-list__search-wrap">
					<?php Sobiforms_Admin_Icons::render( 'search', array( 'size' => 16, 'class' => 'sobiforms-inbox-list__search-icon' ) ); ?>
					<input
						type="search"
						id="sobiforms-submissions-search"
						name="s"
						class="sobiforms-inbox-list__search-input"
						value="<?php echo esc_attr( $search ); ?>"
						placeholder="<?php esc_attr_e( 'Search', 'sobi-forms' ); ?>"
					/>
				</div>
			</form>

			<div class="sobiforms-inbox-list__toolbar">
				<div class="sobiforms-inbox-toolbar__left">
					<div class="sobiforms-inbox-select" data-sobiforms-inbox-select>
						<input
							type="checkbox"
							id="sobiforms-inbox-select-all"
							class="sobiforms-inbox-select__master"
							aria-label="<?php esc_attr_e( 'Select all on this page', 'sobi-forms' ); ?>"
						/>
						<button
							type="button"
							class="sobiforms-inbox-select__toggle"
							aria-expanded="false"
							aria-haspopup="true"
							aria-label="<?php esc_attr_e( 'Selection options', 'sobi-forms' ); ?>"
						>
							<?php Sobiforms_Admin_Icons::render( 'chevron-down', array( 'size' => 16 ) ); ?>
						</button>
						<ul class="sobiforms-inbox-select__menu" hidden>
							<?php foreach ( $select_scopes as $slug => $label ) : ?>
								<li>
									<button
										type="button"
										class="sobiforms-inbox-select__scope"
										data-sobiforms-select-scope="<?php echo esc_attr( $slug ); ?>"
									>
										<?php echo esc_html( $label ); ?>
									</button>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>

					<div class="sobiforms-inbox-bulk-actions" data-sobiforms-bulk-actions hidden>
						<button type="button" class="sobiforms-inbox-bulk-actions__btn sobiforms-inbox-action-tip" data-bulk-action="mark_read" data-tooltip="<?php esc_attr_e( 'Mark as read', 'sobi-forms' ); ?>" aria-label="<?php esc_attr_e( 'Mark as read', 'sobi-forms' ); ?>" hidden>
							<?php Sobiforms_Admin_Icons::render( 'seen', array( 'size' => 16 ) ); ?>
						</button>
						<button type="button" class="sobiforms-inbox-bulk-actions__btn sobiforms-inbox-action-tip" data-bulk-action="mark_unread" data-tooltip="<?php esc_attr_e( 'Mark as unread', 'sobi-forms' ); ?>" aria-label="<?php esc_attr_e( 'Mark as unread', 'sobi-forms' ); ?>" hidden>
							<?php Sobiforms_Admin_Icons::render( 'unseen', array( 'size' => 16 ) ); ?>
						</button>
						<button type="button" class="sobiforms-inbox-bulk-actions__btn sobiforms-inbox-action-tip" data-bulk-action="mark_spam" data-tooltip="<?php esc_attr_e( 'Mark as spam', 'sobi-forms' ); ?>" aria-label="<?php esc_attr_e( 'Mark as spam', 'sobi-forms' ); ?>" hidden>
							<?php Sobiforms_Admin_Icons::render( 'warning', array( 'size' => 16 ) ); ?>
						</button>
						<button type="button" class="sobiforms-inbox-bulk-actions__btn sobiforms-inbox-action-tip" data-bulk-action="mark_not_spam" data-tooltip="<?php esc_attr_e( 'Not spam', 'sobi-forms' ); ?>" aria-label="<?php esc_attr_e( 'Not spam', 'sobi-forms' ); ?>" hidden>
							<?php Sobiforms_Admin_Icons::render( 'check', array( 'size' => 16 ) ); ?>
						</button>
						<button type="button" class="sobiforms-inbox-bulk-actions__btn sobiforms-inbox-bulk-actions__btn--danger sobiforms-inbox-action-tip" data-bulk-action="delete" data-tooltip="<?php esc_attr_e( 'Delete', 'sobi-forms' ); ?>" aria-label="<?php esc_attr_e( 'Delete', 'sobi-forms' ); ?>" hidden>
							<?php Sobiforms_Admin_Icons::render( 'trash', array( 'size' => 16 ) ); ?>
						</button>
					</div>
				</div>

				<div class="sobiforms-inbox-toolbar__right">
					<?php if ( $items_total > 0 ) : ?>
						<span class="sobiforms-inbox-toolbar__range">
							<?php
							printf(
								/* translators: 1: range start, 2: range end, 3: total items */
								esc_html__( '%1$s–%2$s of %3$s', 'sobi-forms' ),
								esc_html( number_format_i18n( $list_range_start ) ),
								esc_html( number_format_i18n( $list_range_end ) ),
								esc_html( number_format_i18n( $items_total ) )
							);
							?>
						</span>
						<?php if ( $prev_url || $next_url ) : ?>
						<span class="sobiforms-inbox-toolbar__page-nav">
							<?php if ( $prev_url ) : ?>
								<a class="sobiforms-inbox-toolbar__page-btn sobiforms-inbox-action-tip" href="<?php echo esc_url( $prev_url ); ?>" data-tooltip="<?php esc_attr_e( 'Previous page', 'sobi-forms' ); ?>" aria-label="<?php esc_attr_e( 'Previous page', 'sobi-forms' ); ?>">
									<?php Sobiforms_Admin_Icons::render( 'chevron-left', array( 'size' => 16 ) ); ?>
								</a>
							<?php endif; ?>
							<?php if ( $next_url ) : ?>
								<a class="sobiforms-inbox-toolbar__page-btn sobiforms-inbox-action-tip" href="<?php echo esc_url( $next_url ); ?>" data-tooltip="<?php esc_attr_e( 'Next page', 'sobi-forms' ); ?>" aria-label="<?php esc_attr_e( 'Next page', 'sobi-forms' ); ?>">
									<?php Sobiforms_Admin_Icons::render( 'chevron-right', array( 'size' => 16 ) ); ?>
								</a>
							<?php endif; ?>
						</span>
						<?php endif; ?>
					<?php endif; ?>

					<div class="sobiforms-inbox-more" data-sobiforms-inbox-more>
						<button type="button" class="sobiforms-inbox-toolbar__icon-btn sobiforms-inbox-action-tip" data-sobiforms-more-toggle aria-expanded="false" data-tooltip="<?php esc_attr_e( 'More actions', 'sobi-forms' ); ?>" aria-label="<?php esc_attr_e( 'More actions', 'sobi-forms' ); ?>">
							<?php Sobiforms_Admin_Icons::render( 'more-vertical', array( 'size' => 16 ) ); ?>
						</button>
						<ul class="sobiforms-inbox-more__menu" hidden>
							<li>
								<a
									href="<?php echo esc_url( $mark_all_url ); ?>"
									data-sobiforms-confirm="<?php echo esc_attr( __( 'Mark all submissions matching the current filters as read?', 'sobi-forms' ) ); ?>"
								>
									<?php esc_html_e( 'Mark all as read', 'sobi-forms' ); ?>
								</a>
							</li>
							<li>
								<a
									href="<?php echo esc_url( $purge_url ); ?>"
									class="sobiforms-inbox-more__danger"
									data-sobiforms-confirm="<?php echo esc_attr( __( 'Delete all submissions matching the current filters?', 'sobi-forms' ) ); ?>"
								>
									<?php esc_html_e( 'Delete all', 'sobi-forms' ); ?>
								</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Compact inbox list (left column).
	 *
	 * @param array $ctx Inbox context from get_submissions_inbox_context().
	 */
	private static function render_submissions_list_panel( $ctx ) {
		$filter_args      = $ctx['filter_args'];
		$filter_form_id   = $ctx['filter_form_id'];
		$search           = $ctx['search'];
		$unread_only      = $ctx['unread_only'];
		$starred_only     = $ctx['starred_only'];
		$spam_only        = $ctx['spam_only'];
		$read_only        = $ctx['read_only'];
		$not_starred_only = $ctx['not_starred_only'];
		$selected_id      = $ctx['selected_id'];
		$submissions      = $ctx['submissions'];
		$forms_map        = $ctx['forms_map'];
		$filters_active   = $ctx['filters_active'];

		if ( empty( $submissions ) ) {
			echo '<div class="sobiforms-inbox-list__empty">';
			if ( $filters_active ) {
				echo '<p class="sobiforms-inbox-list__empty-message">';
				esc_html_e( 'No submissions match your filters.', 'sobi-forms' );
				echo '</p>';
				echo '<p class="sobiforms-inbox-list__empty-action">';
				printf(
					'<a href="%s">%s</a>',
					esc_url( self::submissions_inbox_reset_filters_url() ),
					esc_html__( 'Clear filters', 'sobi-forms' )
				);
				echo '</p>';
			} else {
				echo '<p class="sobiforms-inbox-list__empty-message">';
				esc_html_e( 'No submissions yet.', 'sobi-forms' );
				echo '</p>';
			}
			echo '</div>';
			return;
		}
		?>
		<form method="post" id="sobiforms-bulk-form" class="sobiforms-inbox-bulk-form" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
			<?php wp_nonce_field( 'sobiforms_bulk_submissions', 'sobiforms_bulk_nonce' ); ?>
			<input type="hidden" name="page" value="sobiforms-submissions" />
			<input type="hidden" name="sobiforms_bulk_action" value="" id="sobiforms-bulk-action" />
			<?php if ( $selected_id ) : ?>
				<input type="hidden" name="sobiforms_submission_id" value="<?php echo esc_attr( $selected_id ); ?>" />
			<?php endif; ?>
			<?php self::render_submissions_filter_hidden_inputs( $filter_args ); ?>
			<ul class="sobiforms-inbox-list">
			<?php foreach ( $submissions as $row ) : ?>
				<?php
				$row_id       = absint( $row['id'] );
				$is_selected  = $selected_id === $row_id;
				$is_spam_row  = Sobiforms_Submission_Store::is_spam( $row );
				$is_read      = ! empty( $row['is_read'] );
				$is_starred   = ! empty( $row['is_starred'] );
				$row_classes  = array( 'sobiforms-inbox-row' );
				if ( $is_selected ) {
					$row_classes[] = 'is-selected';
				}
				if ( ! $is_read ) {
					$row_classes[] = 'sobiforms-submission--unread';
				}
				if ( $is_spam_row ) {
					$row_classes[] = 'sobiforms-submission--spam';
				}

				$detail_url = self::submission_detail_url(
					$row_id,
					array( 'sobiforms_auto_read' => '1' ),
					$filter_args
				);
				$star_extra = array( 'sobiforms_toggle_star' => $row_id );
				if ( $selected_id > 0 ) {
					$star_extra['sobiforms_submission_id'] = $selected_id;
				}
				$star_url   = wp_nonce_url(
					self::submissions_page_url( $star_extra, $filter_args ),
					'sobiforms_toggle_star'
				);
				$form_title   = isset( $forms_map[ (int) $row['form_id'] ] ) ? $forms_map[ (int) $row['form_id'] ] : '—';
				$row_title    = self::submission_row_title( $row );
				$date_display = self::submission_relative_date_display( $row['created_at'] );
				/* translators: %d: submission ID. */
				$select_label = sprintf( __( 'Select submission #%d', 'sobi-forms' ), $row_id );
				?>
				<li class="<?php echo esc_attr( implode( ' ', $row_classes ) ); ?>">
					<input
						type="checkbox"
						class="sobiforms-inbox-row__check"
						name="submission_ids[]"
						value="<?php echo esc_attr( $row_id ); ?>"
						data-is-read="<?php echo esc_attr( $is_read ? '1' : '0' ); ?>"
						data-is-starred="<?php echo esc_attr( $is_starred ? '1' : '0' ); ?>"
						data-is-spam="<?php echo esc_attr( $is_spam_row ? '1' : '0' ); ?>"
						aria-label="<?php echo esc_attr( $select_label ); ?>"
					/>
					<div class="sobiforms-inbox-row__content">
						<a
							class="sobiforms-inbox-row__link"
							href="<?php echo esc_url( $detail_url ); ?>"
							<?php echo $is_selected ? 'aria-current="true"' : ''; ?>
						>
							<span class="sobiforms-inbox-row__title"><?php echo esc_html( $row_title ); ?></span>
							<span class="sobiforms-inbox-row__meta"><?php echo esc_html( $form_title ); ?></span>
						</a>
						<time class="sobiforms-inbox-row__date" datetime="<?php echo esc_attr( mysql2date( 'c', $row['created_at'] ) ); ?>">
							<?php echo esc_html( $date_display ); ?>
						</time>
						<a
							href="<?php echo esc_url( $star_url ); ?>"
							class="sobiforms-inbox-row__star sobiforms-item-star"
							aria-pressed="<?php echo esc_attr( $is_starred ? 'true' : 'false' ); ?>"
							aria-label="<?php echo esc_attr( $is_starred ? __( 'Unstar submission', 'sobi-forms' ) : __( 'Star submission', 'sobi-forms' ) ); ?>"
						>
							<?php
							Sobiforms_Admin_Icons::render(
								$is_starred ? 'star-filled' : 'star-empty',
								array( 'size' => 16 )
							);
							?>
						</a>
					</div>
				</li>
			<?php endforeach; ?>
			</ul>
		</form>
		<?php
	}

	/**
	 * Submission detail or empty state (right column).
	 *
	 * @param array $ctx Inbox context from get_submissions_inbox_context().
	 */
	private static function render_submission_detail_panel( $ctx ) {
		$filter_args      = $ctx['filter_args'];
		$selected_id      = $ctx['selected_id'];
		$selected_row     = $ctx['selected_row'];
		$selected_invalid = $ctx['selected_invalid'];

		if ( ! $selected_id ) {
			self::render_submission_inbox_empty_state( $ctx );
			return;
		}

		if ( $selected_invalid || ! $selected_row ) {
			printf(
				'<div class="notice notice-error"><p>%s</p></div>',
				esc_html__( 'Submission not found.', 'sobi-forms' )
			);
			self::render_submission_inbox_empty_state( $ctx );
			return;
		}

		$row = $selected_row;

		$form           = Sobiforms_Form_Repository::get_by_id( $row['form_id'] );
		$form_title     = $form ? $form['title'] : '—';
		$form_edit_url  = $form
			? admin_url( 'admin.php?page=sobiforms-forms&action=edit&sobiforms_form_id=' . $form['id'] )
			: '';
		$admin_note     = isset( $row['admin_note'] ) ? (string) $row['admin_note'] : '';
		$date_display   = mysql2date(
			get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
			$row['created_at']
		);

		$page_title = self::submission_row_title( $row );
		?>
		<div class="sobiforms-inbox-detail">
			<div class="sobiforms-inbox-detail__header">
				<h2 class="sobiforms-inbox-detail__title"><?php echo esc_html( $page_title ); ?></h2>
			</div>

			<div class="sobiforms-submission-detail__layout">
				<main class="sobiforms-submission-detail__main">
					<section class="sobiforms-detail-card">
						<?php if ( ! empty( $row['values'] ) ) : ?>
							<div class="sobiforms-submission-fields">
								<?php foreach ( $row['values'] as $field_id => $item ) : ?>
									<?php
									$display     = Sobiforms_Submission_Store::format_field_display( $item );
									$list_values = Sobiforms_Submission_Store::format_field_display_list( $item );
									$type        = $item['type'] ?? 'text';
									$label       = $item['label'] ?? '';
									$download_url = '';
									if ( 'file' === $type && ! empty( $item['relative_path'] ) ) {
										$download_url = wp_nonce_url(
											add_query_arg(
												array(
													'sobiforms_download_file' => 1,
													'submission_id'           => $selected_id,
													'field_id'                => $field_id,
												),
												admin_url( 'admin.php' )
											),
											'sobiforms_download_file_' . $selected_id
										);
									}
									?>
									<div class="sobiforms-submission-field">
										<div class="sobiforms-submission-field__label"><?php echo esc_html( $label ); ?></div>
										<div class="sobiforms-submission-field__value">
											<?php if ( 'file' === $type && $download_url ) : ?>
												<span class="sobiforms-submission-field__filename"><?php echo esc_html( $display ); ?></span>
												<a class="button button-secondary button-small" href="<?php echo esc_url( $download_url ); ?>">
													<?php esc_html_e( 'Download', 'sobi-forms' ); ?>
												</a>
											<?php elseif ( ! empty( $list_values ) ) : ?>
												<ul class="sobiforms-submission-field__list">
													<?php foreach ( $list_values as $list_part ) : ?>
														<li><?php echo esc_html( $list_part ); ?></li>
													<?php endforeach; ?>
												</ul>
											<?php elseif ( '' === $display ) : ?>
												<span class="sobiforms-submission-field__empty">—</span>
											<?php elseif ( 'textarea' === $type ) : ?>
												<?php echo nl2br( esc_html( $display ) ); ?>
											<?php elseif ( 'email' === $type && is_email( $display ) ) : ?>
												<a href="mailto:<?php echo esc_attr( $display ); ?>"><?php echo esc_html( $display ); ?></a>
											<?php else : ?>
												<?php echo esc_html( $display ); ?>
											<?php endif; ?>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						<?php else : ?>
							<p class="sobiforms-submission-fields__empty"><?php esc_html_e( 'No field data stored.', 'sobi-forms' ); ?></p>
						<?php endif; ?>
					</section>
				</main>

				<aside class="sobiforms-submission-detail__sidebar">
					<section class="sobiforms-detail-card">
						<h3 class="sobiforms-detail-card__title"><?php esc_html_e( 'Details', 'sobi-forms' ); ?></h3>
						<ul class="sobiforms-detail-meta-list">
							<li class="sobiforms-detail-meta-item">
								<span class="sobiforms-detail-meta-item__label"><?php esc_html_e( 'Date', 'sobi-forms' ); ?></span>
								<span class="sobiforms-detail-meta-item__value"><?php echo esc_html( $date_display ); ?></span>
							</li>
							<li class="sobiforms-detail-meta-item">
								<span class="sobiforms-detail-meta-item__label"><?php esc_html_e( 'Form', 'sobi-forms' ); ?></span>
								<span class="sobiforms-detail-meta-item__value">
									<?php if ( $form_edit_url ) : ?>
										<a href="<?php echo esc_url( $form_edit_url ); ?>"><?php echo esc_html( $form_title ); ?></a>
									<?php else : ?>
										<?php echo esc_html( $form_title ); ?>
									<?php endif; ?>
								</span>
							</li>
							<li class="sobiforms-detail-meta-item">
								<span class="sobiforms-detail-meta-item__label"><?php esc_html_e( 'Source', 'sobi-forms' ); ?></span>
								<span class="sobiforms-detail-meta-item__value">
									<?php if ( self::submission_has_source( $row ) ) : ?>
										<?php
										$source_title = trim( (string) ( $row['source_title'] ?? '' ) );
										$source_path  = trim( (string) ( $row['source_path'] ?? '' ) );
										if ( '' !== $source_title ) {
											echo esc_html( $source_title );
										}
										if ( '' !== $source_path ) {
											if ( '' !== $source_title ) {
												echo ' ';
											}
											printf(
												'<code class="sobiforms-detail-meta-item__path" title="%1$s">(%2$s)</code>',
												esc_attr( $source_path ),
												esc_html( $source_path )
											);
										}
										?>
									<?php else : ?>
										<?php esc_html_e( 'Not recorded', 'sobi-forms' ); ?>
									<?php endif; ?>
								</span>
							</li>
						</ul>
					</section>

					<section class="sobiforms-detail-card">
						<h3 class="sobiforms-detail-card__title"><?php esc_html_e( 'Admin note', 'sobi-forms' ); ?></h3>
						<form method="post" class="sobiforms-submission-note-form sobiforms-submission-note-form--sidebar">
							<?php wp_nonce_field( 'sobiforms_save_submission_note', 'sobiforms_note_nonce' ); ?>
							<input type="hidden" name="sobiforms_save_submission_note" value="1" />
							<input type="hidden" name="sobiforms_submission_id" value="<?php echo esc_attr( $selected_id ); ?>" />
							<?php self::render_submissions_filter_hidden_inputs( $filter_args ); ?>
							<label for="sobiforms-admin-note-detail" class="screen-reader-text"><?php esc_html_e( 'Admin note', 'sobi-forms' ); ?></label>
							<textarea
								id="sobiforms-admin-note-detail"
								name="sobiforms_admin_note"
								class="sobiforms-submission-note-textarea"
								rows="5"
								placeholder="<?php esc_attr_e( 'Add a private note…', 'sobi-forms' ); ?>"
								maxlength="<?php echo esc_attr( Sobiforms_Submission_Store::MAX_NOTE_LENGTH ); ?>"
							><?php echo esc_textarea( $admin_note ); ?></textarea>
							<?php submit_button( __( 'Save note', 'sobi-forms' ), 'secondary', '', false ); ?>
						</form>
					</section>
				</aside>
			</div>
		</div>
		<?php
	}

	/**
	 * Right panel placeholder when no submission is selected.
	 *
	 * @param array $ctx Inbox context from get_submissions_inbox_context().
	 */
	private static function render_submission_inbox_empty_state( $ctx ) {
		$list_is_empty    = empty( $ctx['submissions'] );
		$filters_active   = ! empty( $ctx['filters_active'] );
		$has_forms        = ! empty( $ctx['forms'] );
		$total_submissions = isset( $ctx['total_submissions'] ) ? (int) $ctx['total_submissions'] : 0;
		$reviews_url      = 'https://wordpress.org/plugins/sobi-forms/#reviews';
		$forum_url        = 'https://wordpress.org/support/plugin/sobi-forms/';
		$roadmap_url      = 'https://sobiforms.com/roadmap/';
		$create_form_url  = admin_url( 'admin.php?page=sobiforms-forms&action=edit' );
		$show_review      = $total_submissions > 5;
		?>
		<div class="sobiforms-inbox-empty">
			<?php Sobiforms_Admin_Icons::render( 'inbox', array( 'size' => 42, 'class' => 'sobiforms-inbox-empty__icon' ) ); ?>
			<?php if ( $list_is_empty && $filters_active ) : ?>
				<p class="sobiforms-inbox-empty__title"><?php esc_html_e( 'No submissions match your filters', 'sobi-forms' ); ?></p>
				<p class="sobiforms-inbox-empty__hint">
					<a href="<?php echo esc_url( self::submissions_inbox_reset_filters_url() ); ?>">
						<?php esc_html_e( 'Clear filters', 'sobi-forms' ); ?>
					</a>
				</p>
			<?php elseif ( $list_is_empty ) : ?>
				<p class="sobiforms-inbox-empty__title"><?php esc_html_e( 'No submissions yet', 'sobi-forms' ); ?></p>
				<p class="sobiforms-inbox-empty__hint">
					<?php esc_html_e( 'Entries appear here when forms save to the database.', 'sobi-forms' ); ?>
				</p>
			<?php else : ?>
				<p class="sobiforms-inbox-empty__title"><?php esc_html_e( 'Select a submission', 'sobi-forms' ); ?></p>
				<p class="sobiforms-inbox-empty__hint">
					<?php esc_html_e( 'Pick one from the list.', 'sobi-forms' ); ?>
				</p>
			<?php endif; ?>

			<div class="sobiforms-inbox-empty__actions">
				<?php if ( ! $has_forms ) : ?>
					<a href="<?php echo esc_url( $create_form_url ); ?>" class="button button-primary">
						<?php esc_html_e( 'Create a form', 'sobi-forms' ); ?>
					</a>
				<?php endif; ?>
				<?php if ( $show_review ) : ?>
					<a href="<?php echo esc_url( $reviews_url ); ?>" class="button button-secondary" target="_blank" rel="noopener noreferrer">
						<?php Sobiforms_Admin_Icons::render( 'star-filled', array( 'size' => 16 ) ); ?>
						<?php esc_html_e( 'Leave a review', 'sobi-forms' ); ?>
					</a>
				<?php endif; ?>
				<a href="<?php echo esc_url( $forum_url ); ?>" class="button button-secondary" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Get help', 'sobi-forms' ); ?>
				</a>
				<a href="<?php echo esc_url( $roadmap_url ); ?>" class="button button-secondary" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Share an idea', 'sobi-forms' ); ?>
				</a>
			</div>
		</div>
		<?php
	}

	/**
	 * Localized "N item(s)" label for admin list tables.
	 *
	 * @param int $count Row count.
	 * @return string
	 */
	public static function format_list_item_count( $count ) {
		return sprintf(
			/* translators: %s: number of list items */
			_n( '%s item', '%s items', $count, 'sobi-forms' ),
			number_format_i18n( $count )
		);
	}

	/**
	 * Flash notices for submission admin actions.
	 */
	private static function render_submission_notices() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Flash notice query args after redirect.
		foreach (
			array(
				'purged'          => 'success',
				'deleted'         => 'success',
				'delete_error'    => 'error',
				'marked_read'       => 'success',
				'mark_read_error'   => 'error',
				'marked_all_read'   => 'success',
				'marked_unread'     => 'success',
				'mark_unread_error' => 'error',
				'marked_not_spam'   => 'success',
				'mark_not_spam_error' => 'error',
				'marked_spam'       => 'success',
				'mark_spam_error'   => 'error',
				'note_saved'        => 'success',
				'note_error'      => 'error',
				'bulk_deleted'    => 'success',
				'bulk_marked_read' => 'success',
				'bulk_marked_unread' => 'success',
				'bulk_starred'    => 'success',
				'bulk_unstarred'  => 'success',
				'bulk_marked_spam' => 'success',
				'bulk_marked_not_spam' => 'success',
				'bulk_error'      => 'error') as $key => $type
		) {
			if ( ! isset( $_GET[ $key ] ) ) {
				continue;
			}

			$messages = array(
				'purged'          => __( 'Matching submissions have been deleted.', 'sobi-forms' ),
				'deleted'         => __( 'Submission deleted.', 'sobi-forms' ),
				'delete_error'    => __( 'Could not delete submission.', 'sobi-forms' ),
				'marked_read'       => __( 'Submission marked as read.', 'sobi-forms' ),
				'mark_read_error'   => __( 'Could not mark submission as read.', 'sobi-forms' ),
				'marked_all_read'   => __( 'All submissions marked as read.', 'sobi-forms' ),
				'marked_unread'     => __( 'Submission marked as unread.', 'sobi-forms' ),
				'mark_unread_error' => __( 'Could not mark submission as unread.', 'sobi-forms' ),
				'marked_not_spam'   => __( 'Submission marked as not spam.', 'sobi-forms' ),
				'mark_not_spam_error' => __( 'Could not mark submission as not spam.', 'sobi-forms' ),
				'marked_spam'       => __( 'Submission marked as spam.', 'sobi-forms' ),
				'mark_spam_error'   => __( 'Could not mark submission as spam.', 'sobi-forms' ),
				'note_saved'        => __( 'Note saved.', 'sobi-forms' ),
				'note_error'      => __( 'Could not save note.', 'sobi-forms' ),
				'bulk_deleted'    => __( 'Selected submissions deleted.', 'sobi-forms' ),
				'bulk_marked_read' => __( 'Selected submissions marked as read.', 'sobi-forms' ),
				'bulk_marked_unread' => __( 'Selected submissions marked as unread.', 'sobi-forms' ),
				'bulk_starred'    => __( 'Selected submissions starred.', 'sobi-forms' ),
				'bulk_unstarred'  => __( 'Selected submissions unstarred.', 'sobi-forms' ),
				'bulk_marked_spam' => __( 'Selected submissions marked as spam.', 'sobi-forms' ),
				'bulk_marked_not_spam' => __( 'Selected submissions marked as not spam.', 'sobi-forms' ),
				'bulk_error'      => __( 'Could not complete the bulk action.', 'sobi-forms' ));

			printf(
				'<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
				esc_attr( 'error' === $type ? 'error' : 'success' ),
				esc_html( $messages[ $key ] )
			);
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
	}

}
