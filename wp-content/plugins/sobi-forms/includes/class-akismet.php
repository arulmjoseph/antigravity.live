<?php
/**
 * Akismet integration for form spam detection.
 *
 * @package SobiForms
 */

defined( 'ABSPATH' ) || exit;

/**
 * Optional Akismet checks when the official plugin is active and configured.
 */
class Sobiforms_Akismet {

	const OPTION_USE_AKISMET = 'sobiforms_use_akismet';

	/**
	 * Bootstrap default option once Akismet may be loaded.
	 */
	public static function init() {
		add_action( 'plugins_loaded', array( __CLASS__, 'maybe_set_default_option' ), 20 );
	}

	/**
	 * Default ON when Akismet is ready on first run.
	 */
	public static function maybe_set_default_option() {
		if ( null !== get_option( self::OPTION_USE_AKISMET, null ) ) {
			return;
		}

		add_option( self::OPTION_USE_AKISMET, self::is_akismet_ready() ? 1 : 0 );
	}

	/**
	 * Whether the Akismet plugin is available with a valid API key.
	 *
	 * @return bool
	 */
	public static function is_akismet_ready() {
		if ( ! class_exists( 'Akismet' ) || ! method_exists( 'Akismet', 'get_api_key' ) ) {
			return false;
		}

		$api_key = Akismet::get_api_key();
		return is_string( $api_key ) && '' !== $api_key;
	}

	/**
	 * Whether form submissions should be checked via Akismet.
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		if ( ! self::is_akismet_ready() ) {
			return false;
		}

		/**
		 * Override Akismet enablement for form submissions.
		 *
		 * @param bool $enabled Whether Akismet checks run.
		 */
		$enabled = (bool) get_option( self::OPTION_USE_AKISMET, 0 );

		return (bool) apply_filters( 'sobiforms_akismet_is_enabled', $enabled );
	}

	/**
	 * Check validated submission data against Akismet.
	 *
	 * @param array $form      Form row.
	 * @param array $validated Validated submission from the handler.
	 * @return bool True when Akismet classifies as spam. False when ham or on API failure (fail-open).
	 */
	public static function is_submission_spam( $form, $validated ) {
		if ( ! self::is_enabled() ) {
			return false;
		}

		$payload = self::build_check_payload( $form, $validated );

		/**
		 * Akismet comment-check payload.
		 *
		 * @param array $payload Akismet request fields.
		 * @param array $form    Form row.
		 * @param array $validated Validated submission.
		 */
		$payload = apply_filters( 'sobiforms_akismet_check_data', $payload, $form, $validated );

		$is_spam = self::comment_check( $payload );

		/**
		 * Final spam verdict for a submission.
		 *
		 * @param bool  $is_spam   Whether Akismet flagged spam.
		 * @param array $payload   Payload sent to Akismet.
		 * @param array $form      Form row.
		 * @param array $validated Validated submission.
		 */
		return (bool) apply_filters( 'sobiforms_akismet_is_spam', $is_spam, $payload, $form, $validated );
	}

	/**
	 * Report a false positive to Akismet.
	 *
	 * @param array $submission Submission row from the store.
	 */
	public static function submit_ham( $submission ) {
		if ( ! self::is_akismet_ready() ) {
			return;
		}

		$payload = self::build_payload_from_submission( $submission );
		self::api_post( 'submit-ham', $payload );
	}

	/**
	 * Report missed spam to Akismet.
	 *
	 * @param array $submission Submission row from the store.
	 */
	public static function submit_spam( $submission ) {
		if ( ! self::is_akismet_ready() ) {
			return;
		}

		$payload = self::build_payload_from_submission( $submission );
		self::api_post( 'submit-spam', $payload );
	}

	/**
	 * Build Akismet payload from validated handler data.
	 *
	 * @param array $form      Form row.
	 * @param array $validated Validated submission.
	 * @return array
	 */
	public static function build_check_payload( $form, $validated ) {
		$content_parts = array();
		foreach ( $validated['values'] ?? array() as $item ) {
			$label = isset( $item['label'] ) ? (string) $item['label'] : '';
			$value = self::field_value_for_comment_content( $item );
			if ( '' === $value ) {
				continue;
			}
			$content_parts[] = ( '' !== $label ? $label . ': ' : '' ) . $value;
		}

		$permalink = '';
		if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
			$permalink = esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) );
		}

		return array(
			'blog'                 => home_url(),
			'blog_lang'            => get_locale(),
			'blog_charset'         => get_option( 'blog_charset' ),
			'user_ip'              => self::get_user_ip(),
			'user_agent'           => self::get_user_agent(),
			'referrer'             => isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '',
			'permalink'            => $permalink,
			'comment_type'         => 'contact-form',
			'comment_author'       => (string) ( $validated['name'] ?? '' ),
			'comment_author_email' => (string) ( $validated['email'] ?? '' ),
			'comment_author_url'   => '',
			'comment_content'      => implode( "\n", $content_parts ),
		);
	}

	/**
	 * Rebuild Akismet payload from a stored submission row.
	 *
	 * @param array $submission Hydrated submission row.
	 * @return array
	 */
	public static function build_payload_from_submission( $submission ) {
		$content_parts = array();
		foreach ( $submission['values'] ?? array() as $item ) {
			$label = isset( $item['label'] ) ? (string) $item['label'] : '';
			$value = self::field_value_for_comment_content( $item );
			if ( '' === $value ) {
				continue;
			}
			$content_parts[] = ( '' !== $label ? $label . ': ' : '' ) . $value;
		}

		if ( empty( $content_parts ) && ! empty( $submission['message'] ) ) {
			$content_parts[] = (string) $submission['message'];
		}

		return array(
			'blog'                 => home_url(),
			'blog_lang'            => get_locale(),
			'blog_charset'         => get_option( 'blog_charset' ),
			'user_ip'              => self::get_user_ip(),
			'user_agent'           => self::get_user_agent(),
			'referrer'             => '',
			'permalink'            => home_url(),
			'comment_type'         => 'contact-form',
			'comment_author'       => (string) ( $submission['name'] ?? '' ),
			'comment_author_email' => (string) ( $submission['email'] ?? '' ),
			'comment_author_url'   => '',
			'comment_content'      => implode( "\n", $content_parts ),
		);
	}

	/**
	 * Flatten a stored or validated field value for Akismet comment_content.
	 *
	 * Multi-checkbox values are arrays; Akismet expects plain text — space-joined labels.
	 *
	 * @param array $item Field item with type and value keys.
	 * @return string
	 */
	private static function field_value_for_comment_content( $item ) {
		$type  = $item['type'] ?? '';
		$value = $item['value'] ?? '';

		if ( 'file' === $type ) {
			return (string) ( $item['original_name'] ?? '' );
		}

		if ( is_array( $value ) ) {
			$parts = array();
			foreach ( $value as $part ) {
				$part = sanitize_text_field( (string) $part );
				if ( '' !== $part ) {
					$parts[] = $part;
				}
			}
			return implode( ' ', $parts );
		}

		if ( 'checkbox' === $type ) {
			return $value ? __( 'Yes', 'sobi-forms' ) : __( 'No', 'sobi-forms' );
		}

		return (string) $value;
	}

	/**
	 * @param array $payload Akismet fields.
	 * @return bool
	 */
	private static function comment_check( $payload ) {
		$response = self::api_post( 'comment-check', $payload );
		if ( false === $response || ! is_array( $response ) || ! isset( $response[1] ) ) {
			return false;
		}

		return 'true' === trim( (string) $response[1] );
	}

	/**
	 * POST to Akismet via plugin helpers when available.
	 *
	 * @param string $path    API path segment.
	 * @param array  $payload Request fields.
	 * @return array|false Response array (headers, body) or false.
	 */
	private static function api_post( $path, $payload ) {
		if ( ! self::is_akismet_ready() ) {
			return false;
		}

		$request = self::build_request_string( $payload );

		if ( function_exists( 'akismet_http_post' ) ) {
			return akismet_http_post( $request, $path );
		}

		if ( class_exists( 'Akismet' ) && method_exists( 'Akismet', 'http_post' ) ) {
			return Akismet::http_post( $request, $path );
		}

		return false;
	}

	/**
	 * @param array $payload Akismet fields.
	 * @return string
	 */
	private static function build_request_string( $payload ) {
		$parts = array();
		foreach ( $payload as $key => $value ) {
			$parts[] = rawurlencode( (string) $key ) . '=' . rawurlencode( (string) $value );
		}
		return implode( '&', $parts );
	}

	/**
	 * Visitor IP for Akismet (MVP: REMOTE_ADDR).
	 *
	 * @return string
	 */
	private static function get_user_ip() {
		if ( ! isset( $_SERVER['REMOTE_ADDR'] ) ) {
			return '';
		}

		return preg_replace( '/[^0-9., ]/', '', sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) );
	}

	/**
	 * @return string
	 */
	private static function get_user_agent() {
		if ( ! isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return '';
		}

		return sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) );
	}
}
