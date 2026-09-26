<?php
/**
 * AJAX submission handler — validation, email, optional DB storage.
 *
 * @package SobiForms
 */

defined( 'ABSPATH' ) || exit;

/**
 * Processes dynamic form submissions.
 */
class Sobiforms_Form_Handler {

	/** Rate limit: max submissions per IP per hour. */
	const RATE_LIMIT = 5;

	/**
	 * Register AJAX hooks.
	 */
	public static function init() {
		add_action( 'wp_ajax_sobiforms_submit', array( __CLASS__, 'handle' ) );
		add_action( 'wp_ajax_nopriv_sobiforms_submit', array( __CLASS__, 'handle' ) );
		add_action( 'wp_ajax_sobiforms_nonce', array( __CLASS__, 'handle_nonce' ) );
		add_action( 'wp_ajax_nopriv_sobiforms_nonce', array( __CLASS__, 'handle_nonce' ) );
	}

	/**
	 * Return a fresh submit nonce (cache-safe; not embedded in cached HTML).
	 */
	public static function handle_nonce() {
		wp_send_json_success(
			array(
				'nonce' => wp_create_nonce( 'sobiforms_submit' ),
			)
		);
	}

	/**
	 * Handle AJAX form submission.
	 */
	public static function handle() {
		if ( self::is_post_max_size_exceeded() ) {
			wp_send_json_error(
				array( 'message' => __( 'The uploaded file exceeds the server maximum upload limit.', 'sobi-forms' ) ),
				413
			);
		}

		check_ajax_referer( 'sobiforms_submit', 'nonce' );

		// Honeypot — bots only; nonce verified above.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Public AJAX form submission.
		$honeypot = isset( $_POST['sobiforms_website'] )
			? sanitize_text_field( wp_unslash( $_POST['sobiforms_website'] ) )
			: '';
		if ( '' !== $honeypot ) {
			wp_send_json_error( array( 'message' => __( 'Something went wrong.', 'sobi-forms' ) ), 400 );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Public AJAX form submission.
		$form_id = isset( $_POST['sobiforms_form_id'] ) ? absint( wp_unslash( $_POST['sobiforms_form_id'] ) ) : 0;
		$form    = Sobiforms_Form_Repository::get_by_id( $form_id );

		if ( ! $form ) {
			wp_send_json_error( array( 'message' => __( 'Invalid form.', 'sobi-forms' ) ), 400 );
		}

		if ( ! Sobiforms_Form_Repository::is_accepting_submissions( $form ) ) {
			wp_send_json_error(
				array( 'message' => Sobiforms_Form_Repository::get_unavailable_message( $form ) ),
				403
			);
		}

		$ip_hash = self::hash_ip();
		if ( self::is_rate_limited( $ip_hash ) ) {
			wp_send_json_error(
				array( 'message' => __( 'Too many requests. Please try again later.', 'sobi-forms' ) ),
				429
			);
		}

		$validated = self::validate_submission( $form );
		if ( is_wp_error( $validated ) ) {
			wp_send_json_error( array( 'message' => $form['error_message'] ), 400 );
		}

		$source_meta = self::parse_submission_source_from_post();

		$pending_uploads = $validated['pending_uploads'] ?? array();
		if ( ! empty( $pending_uploads ) && ! Sobiforms_Form_Repository::form_saves_submissions( $form ) ) {
			wp_send_json_error( array( 'message' => $form['error_message'] ), 400 );
		}

		self::increment_rate_limit( $ip_hash );

		$email_configured = Sobiforms_Form_Repository::has_recipient_emails( $form );
		$storage_enabled  = Sobiforms_Form_Repository::form_saves_submissions( $form );
		$is_spam          = Sobiforms_Akismet::is_submission_spam( $form, $validated );

		if ( ! $email_configured && ! $storage_enabled && ! $is_spam ) {
			wp_send_json_error(
				array(
					'message' => __(
						'This form cannot accept submissions. Add a recipient email or enable database storage for this form in Settings.',
						'sobi-forms'
					),
				),
				500
			);
		}

		if ( $is_spam ) {
			if ( $storage_enabled ) {
				$spam_values = self::build_spam_file_values( $validated );
				$submission_id = Sobiforms_Submission_Store::insert(
					array_merge(
						array(
							'form_id'     => $form_id,
							'values'      => $spam_values,
							'name'        => $validated['name'],
							'email'       => $validated['email'],
							'message'     => $validated['message'],
							'ip_hash'     => $ip_hash,
							'spam_status' => Sobiforms_Submission_Store::SPAM_SPAM,
						),
						$source_meta
					),
					$form
				);
				if ( $submission_id ) {
					Sobiforms_Submission_Store::update_mail_status( $submission_id, 'skipped' );
				}
			}

			wp_send_json_success( self::resolve_success_response( $form ) );
		}

		if ( ! empty( $form['close_limit_enabled'] ) ) {
			$reserved = Sobiforms_Form_Repository::try_reserve_submission_slot( $form_id );
			if ( null === $reserved ) {
				wp_send_json_error( array( 'message' => $form['error_message'] ), 500 );
			}
			if ( ! $reserved ) {
				wp_send_json_error(
					array( 'message' => Sobiforms_Form_Repository::get_unavailable_message( $form ) ),
					403
				);
			}
		}

		$submission_id = 0;
		if ( $storage_enabled ) {
			$submission_id = Sobiforms_Submission_Store::insert(
				array_merge(
					array(
						'form_id' => $form_id,
						'values'  => $validated['values'],
						'name'    => $validated['name'],
						'email'   => $validated['email'],
						'message' => $validated['message'],
						'ip_hash' => $ip_hash,
					),
					$source_meta
				),
				$form
			);

			if ( ! $submission_id ) {
				wp_send_json_error( array( 'message' => $form['error_message'] ), 500 );
			}

			if ( ! empty( $pending_uploads ) ) {
				$stored_values = self::persist_submission_files( $form_id, $submission_id, $validated );
				if ( is_wp_error( $stored_values ) ) {
					wp_send_json_error( array( 'message' => $form['error_message'] ), 500 );
				}
				$validated['values'] = $stored_values;
			}
		}

		if ( ! $email_configured ) {
			if ( $submission_id ) {
				Sobiforms_Submission_Store::update_mail_status( $submission_id, 'skipped' );
			}
			self::maybe_send_confirmation_email( $form, $validated );
			wp_send_json_success( self::resolve_success_response( $form ) );
		}

		$sent = self::send_email( $form, $validated );

		if ( $submission_id ) {
			Sobiforms_Submission_Store::update_mail_status( $submission_id, $sent ? 'sent' : 'failed' );
		}

		if ( ! $sent && ! $submission_id ) {
			wp_send_json_error( array( 'message' => $form['error_message'] ), 500 );
		}

		self::maybe_send_confirmation_email( $form, $validated );
		wp_send_json_success( self::resolve_success_response( $form ) );
	}

	/**
	 * Build AJAX success payload — inline message and/or server-side redirect URL.
	 *
	 * Falls back to inline message when redirect page is missing or unpublished.
	 *
	 * @param array $form Form definition.
	 * @return array{message: string, redirect_url?: string}
	 */
	private static function resolve_success_response( $form ) {
		$message = ! empty( $form['success_message'] )
			? $form['success_message']
			: __( 'Thank you! Your message has been sent.', 'sobi-forms' );

		if ( Sobiforms_Form_Repository::SUCCESS_ACTION_REDIRECT !== ( $form['success_action'] ?? Sobiforms_Form_Repository::SUCCESS_ACTION_MSG ) ) {
			return array( 'message' => $message );
		}

		$page_id = (int) ( $form['redirect_page_id'] ?? 0 );
		if ( $page_id <= 0 ) {
			return array( 'message' => $message );
		}

		$permalink = get_permalink( $page_id );
		if ( ! $permalink || is_wp_error( $permalink ) ) {
			return array( 'message' => $message );
		}

		$post = get_post( $page_id );
		if ( ! $post || 'page' !== $post->post_type || 'publish' !== $post->post_status ) {
			return array( 'message' => $message );
		}

		return array(
			'message'      => $message,
			'redirect_url' => $permalink);
	}

	/**
	 * Validate POST data against form schema.
	 *
	 * @param array $form Form definition.
	 * @return array|WP_Error
	 */
	private static function validate_submission( $form ) {
		$values          = array();
		$pending_uploads = array();
		$name            = '';
		$email           = '';
		$message         = '';

		foreach ( $form['fields'] as $field ) {
			$type = $field['type'];

			if ( Sobiforms_Form_Repository::is_layout_field( $type ) ) {
				continue;
			}

			$key = 'sobiforms_field_' . $field['id'];

			if ( Sobiforms_Form_Repository::is_file_field( $type ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Public AJAX form submission; validated in validate_uploaded_file().
				$file = isset( $_FILES[ $key ] ) ? $_FILES[ $key ] : array( 'error' => UPLOAD_ERR_NO_FILE );
				$upload_valid = Sobiforms_File_Upload::validate_uploaded_file( $file, $field );
				if ( is_wp_error( $upload_valid ) ) {
					return $upload_valid;
				}
				if ( ! empty( $upload_valid['empty'] ) ) {
					continue;
				}

				$pending_uploads[ $field['id'] ] = array(
					'field'     => $field,
					'validated' => $upload_valid,
				);

				$values[ $field['id'] ] = array(
					'label'         => $field['label'],
					'type'          => 'file',
					'original_name' => $upload_valid['original_name'],
				);
				continue;
			}

			// Nonce verified via check_ajax_referer() in handle(); values sanitized below per field type.
			// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Public AJAX form submission.
			$raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';

			if ( 'checkbox' === $type && Sobiforms_Form_Repository::field_has_checkbox_options( $field ) ) {
				$raw = is_array( $raw ) ? $raw : array();
				$allowed = $field['options'] ?? array();
				$selected = array();

				foreach ( $raw as $part ) {
					$part = sanitize_text_field( (string) $part );
					if ( '' === $part || ! in_array( $part, $allowed, true ) ) {
						continue;
					}
					if ( ! in_array( $part, $selected, true ) ) {
						$selected[] = $part;
					}
				}

				$value = $selected;

				if ( ! empty( $field['required'] ) && empty( $selected ) ) {
					return new WP_Error( 'required', 'missing' );
				}
			} elseif ( 'checkbox' === $type ) {
				$value = ! empty( $raw ) ? '1' : '';

				if ( ! empty( $field['required'] ) && empty( $raw ) ) {
					return new WP_Error( 'required', 'missing' );
				}
			} elseif ( 'textarea' === $type ) {
				$value = sanitize_textarea_field( $raw );
			} elseif ( 'email' === $type ) {
				$value = sanitize_email( $raw );
			} elseif ( 'phone' === $type ) {
				$value = self::sanitize_phone( $raw );
			} elseif ( 'url' === $type ) {
				$value = self::sanitize_url_field( $raw );
				if ( false === $value ) {
					return new WP_Error( 'url', 'invalid' );
				}
			} elseif ( 'number' === $type ) {
				$value = is_numeric( $raw ) ? (string) $raw : sanitize_text_field( $raw );
			} elseif ( Sobiforms_Form_Repository::is_hidden_field( $type ) ) {
				$value = sanitize_text_field( $raw );
			} else {
				$value = sanitize_text_field( $raw );
			}

			if ( ! empty( $field['required'] ) && 'checkbox' !== $type && '' === $value ) {
				return new WP_Error( 'required', 'missing' );
			}

			if ( 'email' === $type && ! empty( $field['required'] ) && ! is_email( $value ) ) {
				return new WP_Error( 'email', 'invalid' );
			}

			if ( 'number' === $type && '' !== $value && ! is_numeric( $value ) ) {
				return new WP_Error( 'number', 'invalid' );
			}

			if ( 'number' === $type && '' !== $value && is_numeric( $value ) ) {
				$num = (float) $value;
				if ( isset( $field['min'] ) && $num < (float) $field['min'] ) {
					return new WP_Error( 'number', 'invalid' );
				}
				if ( isset( $field['max'] ) && $num > (float) $field['max'] ) {
					return new WP_Error( 'number', 'invalid' );
				}
			}

			if ( 'textarea' === $type && '' !== $value && isset( $field['maxLength'] ) ) {
				$max_length = (int) $field['maxLength'];
				if ( $max_length > 0 && mb_strlen( $value ) > $max_length ) {
					return new WP_Error( 'maxlength', 'invalid' );
				}
			}

			if ( 'text' === $type && '' !== $value ) {
				$len = mb_strlen( $value );
				if ( isset( $field['minLength'] ) && $len < (int) $field['minLength'] ) {
					return new WP_Error( 'minlength', 'invalid' );
				}
				$max_length = Sobiforms_Form_Repository::field_effective_max_length( $field );
				if ( $max_length > 0 && $len > $max_length ) {
					return new WP_Error( 'maxlength', 'invalid' );
				}
			}

			if ( in_array( $type, array( 'select', 'radio' ), true ) ) {
				$allowed = isset( $field['options'] ) && is_array( $field['options'] ) ? $field['options'] : array();
				if ( '' !== $value && ! in_array( $value, $allowed, true ) ) {
					return new WP_Error( 'option', 'invalid' );
				}
			}

			$values[ $field['id'] ] = array(
				'label' => $field['label'],
				'type'  => $type,
				'value' => $value);

			if ( 'text' === $type && '' === $name && $value ) {
				$name = $value;
			}
			if ( 'email' === $type && is_email( $value ) ) {
				$email = $value;
			}
			if ( 'textarea' === $type && $value ) {
				$message = $value;
			}
		}

		return array(
			'values'          => $values,
			'pending_uploads' => $pending_uploads,
			'name'            => $name,
			'email'           => $email,
			'message'         => $message,
		);
	}

	/**
	 * Whether PHP dropped POST/FILES due to post_max_size.
	 *
	 * @return bool
	 */
	private static function is_post_max_size_exceeded() {
		$method = isset( $_SERVER['REQUEST_METHOD'] )
			? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) )
			: '';
		if ( 'POST' !== $method ) {
			return false;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Checked before nonce is available.
		if ( ! empty( $_POST ) || ! empty( $_FILES ) ) {
			return false;
		}

		$content_length = isset( $_SERVER['CONTENT_LENGTH'] ) ? (int) $_SERVER['CONTENT_LENGTH'] : 0;
		if ( $content_length <= 0 ) {
			return false;
		}

		$post_max = wp_convert_hr_to_bytes( ini_get( 'post_max_size' ) );
		if ( $post_max > 0 ) {
			return $content_length > $post_max;
		}

		return true;
	}

	/**
	 * Replace pending file placeholders with spam-safe metadata (no disk write).
	 *
	 * @param array $validated Validated submission.
	 * @return array
	 */
	private static function build_spam_file_values( $validated ) {
		$values  = $validated['values'];
		$pending = $validated['pending_uploads'] ?? array();

		foreach ( $pending as $field_id => $item ) {
			$values[ $field_id ] = Sobiforms_File_Upload::build_spam_value_entry(
				$item['field'],
				$item['validated']
			);
		}

		return $values;
	}

	/**
	 * Move validated uploads to storage and update submission values.
	 *
	 * @param int   $form_id       Form ID.
	 * @param int   $submission_id Submission ID.
	 * @param array $validated     Validated submission.
	 * @return array|WP_Error
	 */
	private static function persist_submission_files( $form_id, $submission_id, $validated ) {
		$values        = $validated['values'];
		$pending       = $validated['pending_uploads'] ?? array();
		$moved_paths   = array();

		foreach ( $pending as $field_id => $item ) {
			$stored = Sobiforms_File_Upload::store_submission_file(
				$item['validated'],
				$form_id,
				$submission_id
			);

			if ( is_wp_error( $stored ) ) {
				Sobiforms_File_Upload::rollback_submission_files( $submission_id, $moved_paths );
				return $stored;
			}

			if ( ! empty( $stored['absolute_path'] ) ) {
				$moved_paths[] = $stored['absolute_path'];
			}

			$values[ $field_id ] = Sobiforms_File_Upload::build_value_entry( $item['field'], $stored );
		}

		if ( ! Sobiforms_Submission_Store::update_values( $submission_id, $values ) ) {
			Sobiforms_File_Upload::rollback_submission_files( $submission_id, $moved_paths );
			return new WP_Error( 'file', 'persist_failed' );
		}

		return $values;
	}

	/**
	 * Send notification email.
	 *
	 * @param array $form      Form definition.
	 * @param array $validated Validated submission.
	 * @return bool
	 */
	private static function send_email( $form, $validated ) {
		if ( ! Sobiforms_Form_Repository::has_recipient_emails( $form ) ) {
			return false;
		}

		$to = Sobiforms_Form_Repository::sanitize_recipient_emails( $form['recipient_email'] ?? '' );
		if ( false === $to || '' === $to ) {
			return false;
		}

		$site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
		$subject   = sprintf(
			/* translators: 1: site name, 2: form title */
			__( '[%1$s] New submission — %2$s', 'sobi-forms' ),
			$site_name,
			$form['title']
		);

		$body  = '<html><body style="font-family:sans-serif;line-height:1.5">';
		$body .= '<h2>' . esc_html( $form['title'] ) . '</h2>';

		foreach ( $validated['values'] as $item ) {
			$body .= '<p><strong>' . esc_html( $item['label'] ) . ':</strong> ';
			if ( is_array( $item['value'] ?? null ) ) {
				$body .= '<ul>';
				foreach ( $item['value'] as $part ) {
					$body .= '<li>' . esc_html( (string) $part ) . '</li>';
				}
				$body .= '</ul>';
			} elseif ( 'file' === ( $item['type'] ?? '' ) ) {
				$file_name = $item['original_name'] ?? '';
				$body     .= esc_html( $file_name );
				if ( $file_name ) {
					$inbox_url = admin_url( 'admin.php?page=sobiforms-submissions' );
					$body     .= '<br /><a href="' . esc_url( $inbox_url ) . '">' . esc_html__( 'View in inbox', 'sobi-forms' ) . '</a>';
				}
			} elseif ( 'checkbox' === $item['type'] ) {
				$body .= esc_html( $item['value'] ? __( 'Yes', 'sobi-forms' ) : __( 'No', 'sobi-forms' ) );
			} elseif ( 'textarea' === $item['type'] ) {
				$body .= nl2br( esc_html( (string) $item['value'] ) );
			} else {
				$body .= esc_html( (string) $item['value'] );
			}
			$body .= '</p>';
		}

		$body .= '</body></html>';

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		$reply_to = sanitize_email( $validated['email'] ?? '' );
		if ( $reply_to ) {
			$headers[] = 'Reply-To: ' . $reply_to;
		}

		return self::send_html_mail( $to, $subject, $body, $headers );
	}

	/**
	 * Send HTML mail with site name as sender (not default "WordPress").
	 *
	 * @param string|string[] $to      Recipient(s).
	 * @param string          $subject Subject line.
	 * @param string          $body    HTML body.
	 * @param string[]        $headers Optional extra headers.
	 * @return bool
	 */
	private static function send_html_mail( $to, $subject, $body, $headers = array() ) {
		if ( empty( $headers ) ) {
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		}

		add_filter( 'wp_mail_from_name', array( __CLASS__, 'filter_mail_from_name_site' ) );
		add_filter( 'wp_mail_content_type', array( __CLASS__, 'filter_mail_content_type_html' ) );
		$sent = wp_mail( $to, $subject, $body, $headers );
		remove_filter( 'wp_mail_from_name', array( __CLASS__, 'filter_mail_from_name_site' ) );
		remove_filter( 'wp_mail_content_type', array( __CLASS__, 'filter_mail_content_type_html' ) );

		return $sent;
	}

	/**
	 * Send visitor confirmation email when enabled (fire-and-forget).
	 *
	 * @param array $form      Form definition.
	 * @param array $validated Validated submission.
	 */
	private static function maybe_send_confirmation_email( $form, $validated ) {
		if ( ! Sobiforms_Form_Repository::form_sends_confirmation( $form ) ) {
			return;
		}

		self::send_confirmation_email( $form, $validated );
	}

	/**
	 * Send a simple confirmation email to the visitor (no submitted field values).
	 *
	 * @param array $form      Form definition.
	 * @param array $validated Validated submission.
	 * @return bool
	 */
	private static function send_confirmation_email( $form, $validated ) {
		$field_id = sanitize_key( $form['confirmation_email_field_id'] ?? '' );
		if ( '' === $field_id ) {
			return false;
		}

		$values = $validated['values'] ?? array();
		if ( ! isset( $values[ $field_id ] ) ) {
			return false;
		}

		$raw = $values[ $field_id ]['value'] ?? '';
		$to  = sanitize_email( is_string( $raw ) ? $raw : '' );
		if ( ! is_email( $to ) ) {
			return false;
		}

		$template = Sobiforms_Form_Repository::get_confirmation_email_template( $form );

		return self::send_html_mail( $to, $template['subject'], $template['body'] );
	}

	/**
	 * @return string
	 */
	public static function filter_mail_from_name_site() {
		return wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	}

	/**
	 * @return string
	 */
	public static function filter_mail_content_type_html() {
		return 'text/html';
	}

	/**
	 * Permissive phone sanitization (digits, plus, spaces, dashes).
	 *
	 * @param string $raw Raw POST value.
	 * @return string
	 */
	private static function sanitize_phone( $raw ) {
		$raw = sanitize_text_field( $raw );
		return preg_replace( '/[^\d+\s\-]/', '', $raw );
	}

	/**
	 * Sanitize a visitor URL field (http/https only). Empty input returns ''.
	 *
	 * @param mixed $raw Raw POST value.
	 * @return string|false Sanitized URL, empty string, or false when non-empty but invalid.
	 */
	private static function sanitize_url_field( $raw ) {
		$raw = is_string( $raw ) ? trim( $raw ) : '';
		if ( '' === $raw ) {
			return '';
		}

		if ( mb_strlen( $raw ) > Sobiforms_Form_Repository::MAX_URL_LENGTH ) {
			return false;
		}

		$url = esc_url_raw( $raw );
		if ( ! self::is_allowed_http_url( $url ) ) {
			return false;
		}

		return $url;
	}

	/**
	 * Whether a URL is valid and uses http or https.
	 *
	 * @param string $url Sanitized URL.
	 * @return bool
	 */
	private static function is_allowed_http_url( $url ) {
		if ( '' === $url || ! wp_http_validate_url( $url ) ) {
			return false;
		}

		$parsed = wp_parse_url( $url );
		if ( empty( $parsed['scheme'] ) ) {
			return false;
		}

		return in_array( strtolower( $parsed['scheme'] ), array( 'http', 'https' ), true );
	}

	/**
	 * Frozen page title and path from the browser at submit (display metadata only).
	 *
	 * @return array{source_title: string, source_path: string}
	 */
	private static function parse_submission_source_from_post() {
		if ( ! apply_filters( 'sobiforms_capture_submission_source', true ) ) {
			return array(
				'source_title' => '',
				'source_path'  => '',
			);
		}

		// phpcs:disable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Public AJAX; nonce verified in handle() before storage.
		$title = isset( $_POST['source_title'] )
			? Sobiforms_Submission_Store::sanitize_source_title( wp_unslash( $_POST['source_title'] ) )
			: '';
		$path = isset( $_POST['source_path'] )
			? Sobiforms_Submission_Store::sanitize_source_path( wp_unslash( $_POST['source_path'] ) )
			: '';
		// phpcs:enable WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		return array(
			'source_title' => $title,
			'source_path'  => $path,
		);
	}

	/**
	 * Hash visitor IP.
	 *
	 * @return string
	 */
	private static function hash_ip() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		return hash_hmac( 'sha256', $ip, wp_salt( 'auth' ) );
	}

	/**
	 * @param string $ip_hash Hashed IP.
	 * @return bool
	 */
	private static function is_rate_limited( $ip_hash ) {
		$key   = 'sobiforms_rate_' . substr( $ip_hash, 0, 32 );
		$count = (int) get_transient( $key );
		return $count >= self::RATE_LIMIT;
	}

	/**
	 * @param string $ip_hash Hashed IP.
	 */
	private static function increment_rate_limit( $ip_hash ) {
		$key   = 'sobiforms_rate_' . substr( $ip_hash, 0, 32 );
		$count = (int) get_transient( $key );
		set_transient( $key, $count + 1, HOUR_IN_SECONDS );
	}
}
