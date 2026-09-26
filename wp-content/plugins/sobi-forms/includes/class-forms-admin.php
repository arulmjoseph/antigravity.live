<?php
/**
 * Forms list, builder UI, and CRUD handlers.
 *
 * @package SobiForms
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin screens for multi-form management.
 */
class Sobiforms_Forms_Admin {

	/**
	 * Register hooks.
	 */
	public static function init() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_init', array( __CLASS__, 'handle_actions' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_builder_assets' ) );
		add_action( 'wp_ajax_sobiforms_save_form', array( __CLASS__, 'ajax_save_form' ) );
		add_filter( 'admin_body_class', array( __CLASS__, 'admin_body_class' ) );
	}

	/**
	 * Body class on the form builder screen (full-bleed layout).
	 *
	 * @param string $classes Space-separated admin body classes.
	 * @return string
	 */
	public static function admin_body_class( $classes ) {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Admin screen routing only.
		if (
			isset( $_GET['page'], $_GET['action'] )
			&& 'sobiforms-forms' === $_GET['page']
			&& 'edit' === $_GET['action']
		) {
			$classes .= ' sobiforms-form-builder-screen';
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		return $classes;
	}

	/**
	 * Enqueue builder assets on form edit screen only.
	 *
	 * @param string $hook Admin page hook.
	 */
	public static function enqueue_builder_assets( $hook ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( false === strpos( $hook, 'sobiforms-forms' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Admin screen routing only.
		if ( ! isset( $_GET['action'] ) || 'edit' !== $_GET['action'] ) {
			return;
		}

		$script_path = SOBIFORMS_PATH . 'build/admin-builder/index.tsx.js';
		$asset_path  = SOBIFORMS_PATH . 'build/admin-builder/index.tsx.asset.php';

		if ( ! file_exists( $script_path ) ) {
			return;
		}

		// Ensure admin.css loads before builder (hook slug is sobiforms-*, not sobi-forms).
		wp_enqueue_style( 'sobiforms-admin', SOBIFORMS_URL . 'assets/css/admin.css', array(), SOBIFORMS_VERSION );
		wp_enqueue_style( 'wp-components' );

		$asset = file_exists( $asset_path )
			? include $asset_path
			: array(
				'dependencies' => array( 'wp-i18n' ),
				'version'      => SOBIFORMS_VERSION);

		wp_enqueue_style(
			'sobiforms-builder',
			SOBIFORMS_URL . 'build/admin-builder/index.tsx.css',
			array( 'sobiforms-admin' ),
			$asset['version']
		);

		wp_enqueue_script(
			'sobiforms-builder',
			SOBIFORMS_URL . 'build/admin-builder/index.tsx.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'sobiforms-builder', 'sobi-forms', SOBIFORMS_PATH . 'languages' );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Admin screen routing only.
		$form_id = isset( $_GET['sobiforms_form_id'] ) ? absint( $_GET['sobiforms_form_id'] ) : 0;
		$form    = $form_id ? Sobiforms_Form_Repository::get_by_id( $form_id ) : null;

		$defaults = array(
			'title'            => '',
			'recipient_email'  => '',
			'success_message'  => __( 'Thank you! Your message has been sent.', 'sobi-forms' ),
			'error_message'    => __( 'Something went wrong. Please try again.', 'sobi-forms' ),
			'success_action'   => Sobiforms_Form_Repository::SUCCESS_ACTION_MSG,
			'redirect_page_id' => 0,
			'fields'           => Sobiforms_Form_Repository::default_fields(),
			'submit_button_text' => Sobiforms_Form_Repository::default_submit_button_text(),
			'save_submissions'   => 1,
			'retention_days'     => 0,
			'paused'             => 0,
			'close_enabled'      => 0,
			'close_at'           => '',
			'close_limit_enabled' => 0,
			'close_limit'        => 0,
			'submission_count'   => 0,
			'unavailable_message' => '',
			'send_confirmation_email' => 0,
			'confirmation_email_field_id' => '');

		$form = $form ? array_merge( $defaults, $form ) : $defaults;

		wp_localize_script(
			'sobiforms-builder',
			'sobiformsBuilder',
			array(
				'formId'                  => $form_id,
				'ajaxUrl'                 => admin_url( 'admin-ajax.php' ),
				'pages'                   => self::get_page_options(),
				'defaultSubmitButtonText' => Sobiforms_Form_Repository::default_submit_button_text(),
				'serverMaxUploadMb'       => Sobiforms_File_Upload::get_server_max_upload_mb(),
				'fileExtensionOptions'    => Sobiforms_File_Upload::get_selectable_extension_catalog(),
				'defaultFileExtensions'   => Sobiforms_File_Upload::get_default_extensions_for_new_field(),
				'form'                    => array(
					'title'            => $form['title'],
					'slug'             => $form['slug'] ?? '',
					'recipient_email'  => $form['recipient_email'],
					'success_message'  => $form['success_message'],
					'error_message'    => $form['error_message'],
					'success_action'   => $form['success_action'],
					'redirect_page_id' => (int) $form['redirect_page_id'],
					'submit_button_text' => $form['submit_button_text'] ?? '',
					'save_submissions'   => (bool) ( $form['save_submissions'] ?? 0 ),
					'retention_days'     => (int) ( $form['retention_days'] ?? Sobiforms_Form_Repository::DEFAULT_RETENTION_DAYS ),
					'paused'             => (bool) ( $form['paused'] ?? 0 ),
					'close_enabled'      => (bool) ( $form['close_enabled'] ?? 0 ),
					'close_at'           => Sobiforms_Form_Repository::format_close_at_for_input( $form ),
					'close_limit_enabled' => (bool) ( $form['close_limit_enabled'] ?? 0 ),
					'close_limit'        => (int) ( $form['close_limit'] ?? 0 ),
					'submission_count'   => (int) ( $form['submission_count'] ?? 0 ),
					'unavailable_message' => $form['unavailable_message'] ?? '',
					'send_confirmation_email' => (bool) ( $form['send_confirmation_email'] ?? 0 ),
					'confirmation_email_field_id' => $form['confirmation_email_field_id'] ?? '',
					'fields'             => $form['fields']))
		);
	}

	/**
	 * Published pages for redirect dropdown in form settings.
	 *
	 * @return array<int, array{id: int, title: string}>
	 */
	private static function get_page_options() {
		$pages = get_pages(
			array(
				'post_status' => 'publish',
				'sort_column' => 'post_title',
				'sort_order'  => 'ASC')
		);

		$options = array();
		foreach ( $pages as $page ) {
			$options[] = array(
				'id'    => (int) $page->ID,
				'title' => $page->post_title);
		}

		return $options;
	}

	/**
	 * Handle form CRUD actions.
	 */
	public static function handle_actions() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Save form (POST). Skip during admin-ajax — builder uses wp_ajax_sobiforms_save_form.
		if (
			! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) &&
			isset( $_POST['sobiforms_save_form'] ) &&
			isset( $_POST['sobiforms_form_nonce'] ) &&
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sobiforms_form_nonce'] ) ), 'sobiforms_save_form' )
		) {
			self::handle_save();
			return;
		}

		// Delete (GET).
		if (
			isset( $_GET['sobiforms_delete_form'] ) &&
			isset( $_GET['_wpnonce'] ) &&
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'sobiforms_delete_form' )
		) {
			$id = absint( $_GET['sobiforms_delete_form'] );
			if ( $id ) {
				Sobiforms_Form_Repository::delete( $id );
			}
			wp_safe_redirect( self::forms_list_url( array( 'deleted' => 1 ) ) );
			exit;
		}

		// Duplicate (GET).
		if (
			isset( $_GET['sobiforms_duplicate_form'] ) &&
			isset( $_GET['_wpnonce'] ) &&
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'sobiforms_duplicate_form' )
		) {
			$id = absint( $_GET['sobiforms_duplicate_form'] );
			if ( $id ) {
				Sobiforms_Form_Repository::duplicate( $id );
			}
			wp_safe_redirect( self::forms_list_url( array( 'duplicated' => 1 ) ) );
			exit;
		}

	}

	/**
	 * Build forms list URL preserving search.
	 *
	 * @param array $extra Extra query args.
	 * @return string
	 */
	private static function forms_list_url( $extra = array() ) {
		$args = array_merge(
			array( 'page' => 'sobiforms-forms' ),
			$extra
		);

		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Preserve admin list search from the current request.
		if ( isset( $_GET['s'] ) && '' !== $_GET['s'] ) {
			$args['s'] = sanitize_text_field( wp_unslash( $_GET['s'] ) );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		return add_query_arg( $args, admin_url( 'admin.php' ) );
	}

	/**
	 * Validate and persist form data from builder POST fields.
	 *
	 * @return array{success:bool,error?:string,form_id?:int,warn_no_delivery?:bool,slug?:string}
	 */
	private static function process_save_request() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Nonce verified by caller.

		$id = isset( $_POST['sobiforms_form_id'] ) ? absint( $_POST['sobiforms_form_id'] ) : 0;
		$fields = isset( $_POST['sobiforms_fields_json'] )
			? sanitize_textarea_field( wp_unslash( $_POST['sobiforms_fields_json'] ) )
			: '[]';

		$recipient_raw = isset( $_POST['sobiforms_recipient_email'] )
			? sanitize_text_field( wp_unslash( $_POST['sobiforms_recipient_email'] ) )
			: '';
		$recipient_email = Sobiforms_Form_Repository::sanitize_recipient_emails( $recipient_raw );

		if ( false === $recipient_email ) {
			return array(
				'success' => false,
				'error'   => 'invalid_email',
			);
		}

		$success_action = Sobiforms_Form_Repository::sanitize_success_action(
			isset( $_POST['sobiforms_success_action'] )
				? sanitize_text_field( wp_unslash( $_POST['sobiforms_success_action'] ) )
				: Sobiforms_Form_Repository::SUCCESS_ACTION_MSG
		);
		$redirect_page_id = Sobiforms_Form_Repository::sanitize_redirect_page_id(
			isset( $_POST['sobiforms_redirect_page_id'] ) ? absint( wp_unslash( $_POST['sobiforms_redirect_page_id'] ) ) : 0
		);

		if ( Sobiforms_Form_Repository::SUCCESS_ACTION_REDIRECT === $success_action && $redirect_page_id <= 0 ) {
			return array(
				'success' => false,
				'error'   => 'invalid_redirect',
			);
		}

		$close_enabled = ! empty( $_POST['sobiforms_close_enabled'] );
		$close_at_raw  = isset( $_POST['sobiforms_close_at'] )
			? sanitize_text_field( wp_unslash( $_POST['sobiforms_close_at'] ) )
			: '';
		if ( $close_enabled && false === Sobiforms_Form_Repository::sanitize_close_at( $close_at_raw, true ) ) {
			return array(
				'success' => false,
				'error'   => 'invalid_close',
			);
		}

		$close_limit_enabled = ! empty( $_POST['sobiforms_close_limit_enabled'] );
		$close_limit_raw     = isset( $_POST['sobiforms_close_limit'] )
			? absint( wp_unslash( $_POST['sobiforms_close_limit'] ) )
			: 0;
		if ( $close_limit_enabled && false === Sobiforms_Form_Repository::sanitize_close_limit( $close_limit_raw, true ) ) {
			return array(
				'success' => false,
				'error'   => 'invalid_limit',
			);
		}

		$data = array(
			'title'            => isset( $_POST['sobiforms_form_title'] ) ? sanitize_text_field( wp_unslash( $_POST['sobiforms_form_title'] ) ) : '',
			'recipient_email'  => $recipient_email,
			'success_message'  => isset( $_POST['sobiforms_success_message'] ) ? sanitize_text_field( wp_unslash( $_POST['sobiforms_success_message'] ) ) : '',
			'error_message'    => isset( $_POST['sobiforms_error_message'] ) ? sanitize_text_field( wp_unslash( $_POST['sobiforms_error_message'] ) ) : '',
			'success_action'   => $success_action,
			'redirect_page_id' => $redirect_page_id,
			'fields'             => $fields,
			'submit_button_text' => isset( $_POST['sobiforms_submit_button_text'] )
				? sanitize_text_field( wp_unslash( $_POST['sobiforms_submit_button_text'] ) )
				: '',
			'save_submissions' => ! empty( $_POST['sobiforms_save_submissions'] ) ? 1 : 0,
			'retention_days'   => ! empty( $_POST['sobiforms_purge_old'] )
				? max(
					1,
					isset( $_POST['sobiforms_retention_days'] )
						? absint( wp_unslash( $_POST['sobiforms_retention_days'] ) )
						: Sobiforms_Form_Repository::DEFAULT_RETENTION_DAYS
				)
				: 0,
			'paused'             => ! empty( $_POST['sobiforms_paused'] ) ? 1 : 0,
			'close_enabled'      => $close_enabled ? 1 : 0,
			'close_at'           => $close_at_raw,
			'close_limit_enabled' => $close_limit_enabled ? 1 : 0,
			'close_limit'        => $close_limit_raw,
			'unavailable_message' => isset( $_POST['sobiforms_unavailable_message'] )
				? sanitize_textarea_field( wp_unslash( $_POST['sobiforms_unavailable_message'] ) )
				: '',
			'send_confirmation_email' => ! empty( $_POST['sobiforms_send_confirmation_email'] ) ? 1 : 0,
			'confirmation_email_field_id' => isset( $_POST['sobiforms_confirmation_email_field_id'] )
				? sanitize_key( wp_unslash( $_POST['sobiforms_confirmation_email_field_id'] ) )
				: '',
		);

		$warn_no_delivery = '' === $recipient_email && empty( $data['save_submissions'] );

		if ( $id ) {
			$ok = Sobiforms_Form_Repository::update( $id, $data );
			if ( ! $ok ) {
				return array(
					'success' => false,
					'error'   => 'save_failed',
				);
			}
			$saved_id = $id;
		} else {
			$saved_id = Sobiforms_Form_Repository::create( $data );
			if ( ! $saved_id ) {
				return array(
					'success' => false,
					'error'   => 'save_failed',
				);
			}
		}

		$form = Sobiforms_Form_Repository::get_by_id( $saved_id );

		// phpcs:enable WordPress.Security.NonceVerification.Missing

		return array(
			'success'          => true,
			'form_id'          => $saved_id,
			'warn_no_delivery' => $warn_no_delivery,
			'slug'             => $form['slug'] ?? '',
			'submission_count' => (int) ( $form['submission_count'] ?? 0 ),
		);
	}

	/**
	 * Save form from builder POST (fallback when JS is unavailable).
	 */
	private static function handle_save() {
		if (
			! isset( $_POST['sobiforms_form_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sobiforms_form_nonce'] ) ), 'sobiforms_save_form' )
		) {
			return;
		}

		$id = isset( $_POST['sobiforms_form_id'] ) ? absint( $_POST['sobiforms_form_id'] ) : 0;
		$edit_base = $id
			? admin_url( 'admin.php?page=sobiforms-forms&action=edit&sobiforms_form_id=' . $id )
			: admin_url( 'admin.php?page=sobiforms-forms&action=edit' );

		$result = self::process_save_request();

		if ( ! $result['success'] ) {
			$error = $result['error'] ?? 'save_failed';
			if ( 'save_failed' === $error ) {
				wp_safe_redirect( add_query_arg( 'error', 1, $edit_base ) );
			} else {
				wp_safe_redirect( add_query_arg( 'error', $error, $edit_base ) );
			}
			exit;
		}

		$saved_args = array( 'saved' => 1 );
		if ( ! empty( $result['warn_no_delivery'] ) ) {
			$saved_args['warn_no_delivery'] = 1;
		}

		wp_safe_redirect(
			add_query_arg(
				$saved_args,
				admin_url( 'admin.php?page=sobiforms-forms&action=edit&sobiforms_form_id=' . $result['form_id'] )
			)
		);
		exit;
	}

	/**
	 * AJAX save for the React builder (no full page reload).
	 */
	public static function ajax_save_form() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'code' => 'forbidden' ), 403 );
		}

		check_ajax_referer( 'sobiforms_save_form', 'sobiforms_form_nonce' );

		$result = self::process_save_request();

		if ( ! $result['success'] ) {
			wp_send_json_error(
				array(
					'code' => $result['error'] ?? 'save_failed',
				)
			);
		}

		wp_send_json_success(
			array(
				'formId'           => $result['form_id'],
				'slug'             => $result['slug'] ?? '',
				'warnNoDelivery'   => ! empty( $result['warn_no_delivery'] ),
				'submissionCount'  => (int) ( $result['submission_count'] ?? 0 ),
			)
		);
	}

	/**
	 * Route list vs edit screen.
	 */
	public static function render_list_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Admin screen routing only.
		$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : 'list';

		if ( 'edit' === $action ) {
			self::render_edit_page();
			return;
		}

		self::render_forms_list();
	}

	/**
	 * Forms list table.
	 */
	private static function render_forms_list() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Admin list filters and flash notices.
		$search       = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$forms        = Sobiforms_Form_Repository::get_all( $search );
		$items_total  = Sobiforms_Form_Repository::count( $search );
		$count_all    = Sobiforms_Form_Repository::count( $search );
		$add_url      = admin_url( 'admin.php?page=sobiforms-forms&action=edit' );
		$all_view_url = add_query_arg( array( 'page' => 'sobiforms-forms' ), admin_url( 'admin.php' ) );
		if ( '' !== $search ) {
			$all_view_url = add_query_arg( 's', $search, $all_view_url );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		?>
		<div class="wrap sobiforms-admin-list sobiforms-forms-list">
			<?php // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Flash notice query args after redirect. ?>
			<?php if ( isset( $_GET['deleted'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Form deleted.', 'sobi-forms' ); ?></p></div>
			<?php endif; ?>
			<?php if ( isset( $_GET['duplicated'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Form duplicated.', 'sobi-forms' ); ?></p></div>
			<?php endif; ?>
			<?php // phpcs:enable WordPress.Security.NonceVerification.Recommended ?>
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Forms', 'sobi-forms' ); ?></h1>
			<a href="<?php echo esc_url( $add_url ); ?>" class="page-title-action"><?php esc_html_e( 'Add New', 'sobi-forms' ); ?></a>
			<hr class="wp-header-end" />

			<form method="get" id="sobiforms-forms-filter">
				<input type="hidden" name="page" value="sobiforms-forms" />

				<div class="sobiforms-admin-list__views-row">
					<ul class="subsubsub">
						<li>
							<a href="<?php echo esc_url( $all_view_url ); ?>" class="current">
								<?php esc_html_e( 'All', 'sobi-forms' ); ?>
								<span class="count">(<?php echo esc_html( number_format_i18n( $count_all ) ); ?>)</span>
							</a>
						</li>
					</ul>
					<p class="search-box">
						<label class="screen-reader-text" for="sobiforms-forms-search"><?php esc_html_e( 'Search forms', 'sobi-forms' ); ?></label>
						<input type="search" id="sobiforms-forms-search" name="s" value="<?php echo esc_attr( $search ); ?>" />
						<?php submit_button( __( 'Search Forms', 'sobi-forms' ), '', '', false, array( 'id' => 'sobiforms-forms-search-submit' ) ); ?>
					</p>
					<br class="clear" />
				</div>

				<div class="tablenav top sobiforms-admin-list__filters">
					<div class="tablenav-pages">
						<span class="displaying-num"><?php echo esc_html( Sobiforms_Admin::format_list_item_count( $items_total ) ); ?></span>
					</div>
					<br class="clear" />
				</div>

			<?php if ( empty( $forms ) ) : ?>
				<p>
					<?php
					echo esc_html(
						$search
							? __( 'No forms match your search.', 'sobi-forms' )
							: __( 'No forms yet. Create your first form.', 'sobi-forms' )
					);
					?>
				</p>
			<?php else : ?>
				<table class="wp-list-table widefat fixed striped table-view-list">
					<thead>
						<tr>
							<th scope="col" class="manage-column column-primary column-title"><?php esc_html_e( 'Title', 'sobi-forms' ); ?></th>
							<th scope="col" class="manage-column"><?php esc_html_e( 'Shortcode', 'sobi-forms' ); ?></th>
							<th scope="col" class="manage-column"><?php esc_html_e( 'Email', 'sobi-forms' ); ?></th>
							<th scope="col" class="manage-column"><?php esc_html_e( 'Fields', 'sobi-forms' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $forms as $form ) : ?>
							<?php
							$edit_url = admin_url( 'admin.php?page=sobiforms-forms&action=edit&sobiforms_form_id=' . $form['id'] );
							$dup_url  = wp_nonce_url(
								self::forms_list_url( array( 'sobiforms_duplicate_form' => $form['id'] ) ),
								'sobiforms_duplicate_form'
							);
							$del_url  = wp_nonce_url(
								self::forms_list_url( array( 'sobiforms_delete_form' => $form['id'] ) ),
								'sobiforms_delete_form'
							);
							?>
							<tr>
								<td class="title column-title column-primary" data-colname="<?php esc_attr_e( 'Title', 'sobi-forms' ); ?>">
									<strong>
										<a class="row-title" href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( $form['title'] ); ?></a>
									</strong>
									<div class="row-actions">
										<span class="edit">
											<a href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'Edit', 'sobi-forms' ); ?></a> |
										</span>
										<span class="duplicate">
											<a href="<?php echo esc_url( $dup_url ); ?>"><?php esc_html_e( 'Duplicate', 'sobi-forms' ); ?></a> |
										</span>
										<span class="trash">
											<a href="<?php echo esc_url( $del_url ); ?>" class="submitdelete sobiforms-delete-submission"
												onclick="return confirm('<?php echo esc_js( __( 'Delete this form?', 'sobi-forms' ) ); ?>');"><?php esc_html_e( 'Delete', 'sobi-forms' ); ?></a>
										</span>
									</div>
									<button type="button" class="toggle-row"><span class="screen-reader-text"><?php esc_html_e( 'Show more details', 'sobi-forms' ); ?></span></button>
								</td>
								<td class="column-shortcode" data-colname="<?php esc_attr_e( 'Shortcode', 'sobi-forms' ); ?>">
									<code>[sobiforms id="<?php echo esc_attr( $form['id'] ); ?>"]</code>
								</td>
								<td class="column-email" data-colname="<?php esc_attr_e( 'Email', 'sobi-forms' ); ?>">
									<?php
									if ( Sobiforms_Form_Repository::has_recipient_emails( $form ) ) {
										echo esc_html( $form['recipient_email'] );
									} else {
										echo '<span class="sobiforms-muted">' . esc_html( '—' ) . '</span>';
									}
									?>
								</td>
								<td class="column-fields" data-colname="<?php esc_attr_e( 'Fields', 'sobi-forms' ); ?>"><?php echo esc_html( count( $form['fields'] ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Form edit screen with builder.
	 */
	private static function render_edit_page() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Admin screen routing and flash notices.
		$form_id = isset( $_GET['sobiforms_form_id'] ) ? absint( $_GET['sobiforms_form_id'] ) : 0;
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		$form    = $form_id ? Sobiforms_Form_Repository::get_by_id( $form_id ) : null;

		if ( $form_id && ! $form ) {
			echo '<div class="wrap"><p>' . esc_html__( 'Form not found.', 'sobi-forms' ) . '</p></div>';
			return;
		}

		$defaults = array(
			'title'            => '',
			'recipient_email'  => '',
			'success_message'  => __( 'Thank you! Your message has been sent.', 'sobi-forms' ),
			'error_message'    => __( 'Something went wrong. Please try again.', 'sobi-forms' ),
			'success_action'   => Sobiforms_Form_Repository::SUCCESS_ACTION_MSG,
			'redirect_page_id' => 0,
			'fields'           => Sobiforms_Form_Repository::default_fields(),
			'slug'               => '',
			'id'                 => 0,
			'submit_button_text' => Sobiforms_Form_Repository::default_submit_button_text(),
			'save_submissions'   => 1,
			'retention_days'     => 0,
			'paused'             => 0,
			'close_enabled'      => 0,
			'close_at'           => '',
			'close_limit_enabled' => 0,
			'close_limit'        => 0,
			'submission_count'   => 0,
			'unavailable_message' => '',
			'send_confirmation_email' => 0,
			'confirmation_email_field_id' => '');

		$form     = $form ? array_merge( $defaults, $form ) : $defaults;
		$fields    = wp_json_encode( $form['fields'] );
		$has_build = file_exists( SOBIFORMS_PATH . 'build/admin-builder/index.tsx.js' );
		?>
		<div class="sobiforms-form-edit">
			<?php // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Flash notice query args after redirect. ?>
			<?php if ( isset( $_GET['error'] ) ) : ?>
				<?php
				$error_code = sanitize_key( wp_unslash( $_GET['error'] ) );
				if ( 'invalid_email' === $error_code ) :
					?>
					<div class="notice notice-error is-dismissible">
						<p><?php esc_html_e( 'Could not save form. Enter valid recipient email addresses (separate multiple with commas or semicolons, max 10).', 'sobi-forms' ); ?></p>
					</div>
				<?php elseif ( 'invalid_redirect' === $error_code ) : ?>
					<div class="notice notice-error is-dismissible">
						<p><?php esc_html_e( 'Could not save form. Select a published page to redirect to, or switch back to success message.', 'sobi-forms' ); ?></p>
					</div>
				<?php elseif ( 'invalid_close' === $error_code ) : ?>
					<div class="notice notice-error is-dismissible">
						<p><?php esc_html_e( 'Could not save form. Set a valid close date and time, or disable auto-close.', 'sobi-forms' ); ?></p>
					</div>
				<?php elseif ( 'invalid_limit' === $error_code ) : ?>
					<div class="notice notice-error is-dismissible">
						<p><?php esc_html_e( 'Could not save form. Enter a maximum submission count of at least 1, or disable the submission limit.', 'sobi-forms' ); ?></p>
					</div>
				<?php else : ?>
					<div class="notice notice-error is-dismissible"><p><?php esc_html_e( 'Could not save form. Check required fields.', 'sobi-forms' ); ?></p></div>
				<?php endif; ?>
			<?php endif; ?>
			<?php // phpcs:enable WordPress.Security.NonceVerification.Recommended ?>
			<?php if ( ! $has_build ) : ?>
				<div class="notice notice-error">
					<p>
						<?php
						esc_html_e(
							'Form builder assets are missing. Run npm install && npm run build in the plugin directory.',
							'sobi-forms'
						);
						?>
					</p>
				</div>
			<?php endif; ?>

			<form method="post" id="sobiforms-form-builder-form">
				<?php wp_nonce_field( 'sobiforms_save_form', 'sobiforms_form_nonce' ); ?>
				<input type="hidden" name="sobiforms_save_form" value="1" />
				<input type="hidden" name="sobiforms_form_id" value="<?php echo esc_attr( $form_id ); ?>" />
				<input type="hidden" name="sobiforms_fields_json" id="sobiforms-fields-json" value="<?php echo esc_attr( $fields ); ?>" />

				<div id="sobiforms-builder-root" class="sobiforms-builder-app"></div>
			</form>
		</div>
		<?php
	}
}
