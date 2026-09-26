<?php
/**
 * Secure file upload handling for file fields.
 *
 * @package SobiForms
 */

defined( 'ABSPATH' ) || exit;

/**
 * Validates, stores, and deletes submission file uploads.
 */
class Sobiforms_File_Upload {

	const UPLOAD_SUBDIR              = 'sobiforms';
	const HTACCESS_MARKER            = 'sobiforms-htaccess-v2';
	const HTACCESS_VERSION           = 2;
	const HTACCESS_VERSION_OPTION    = 'sobiforms_upload_htaccess_version';
	const UPLOAD_PROBE_TRANSIENT     = 'sobiforms_upload_dir_web_exposed';
	const UPLOAD_PROBE_TTL           = 43200; // 12 hours.
	const MAX_ORIGINAL_NAME_LENGTH   = 150;
	const DEFAULT_MAX_SIZE_MB        = 5;
	const ALLOWED_FAMILY_APPLICATION = 'application';
	const ALLOWED_FAMILY_IMAGE       = 'image';
	const ALLOWED_FAMILY_TEXT        = 'text';

	/**
	 * Extensions always rejected regardless of field settings.
	 *
	 * @return string[]
	 */
	public static function get_blacklisted_extensions() {
		return array(
			'php',
			'php3',
			'php4',
			'php5',
			'phtml',
			'phar',
			'pl',
			'py',
			'cgi',
			'sh',
			'exe',
			'js',
			'htm',
			'asp',
			'aspx',
			'jsp',
		);
	}

	/**
	 * Server upload limit in bytes.
	 *
	 * @return int
	 */
	public static function get_server_max_upload_bytes() {
		return (int) wp_max_upload_size();
	}

	/**
	 * Server upload limit in megabytes (floor).
	 *
	 * @return int
	 */
	public static function get_server_max_upload_mb() {
		$bytes = self::get_server_max_upload_bytes();
		if ( $bytes <= 0 ) {
			return 1;
		}

		return max( 1, (int) floor( $bytes / 1048576 ) );
	}

	/**
	 * Whether a form schema contains file upload fields.
	 *
	 * @param array $form Form row.
	 * @return bool
	 */
	public static function form_has_file_fields( $form ) {
		$fields = $form['fields'] ?? array();
		if ( ! is_array( $fields ) ) {
			return false;
		}

		foreach ( $fields as $field ) {
			if ( is_array( $field ) && 'file' === ( $field['type'] ?? '' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Extension keys grouped for the builder picker.
	 *
	 * @return array<string, string[]>
	 */
	public static function get_extension_catalog_groups() {
		return array(
			self::ALLOWED_FAMILY_APPLICATION => array(
				'pdf',
				'doc',
				'docx',
				'xls',
				'xlsx',
				'ppt',
				'pptx',
				'json',
				'odt',
			),
			self::ALLOWED_FAMILY_IMAGE => array(
				'jpg',
				'jpeg',
				'png',
				'gif',
				'heic',
				'webp',
				'bmp',
				'psd',
			),
			self::ALLOWED_FAMILY_TEXT => array(
				'txt',
				'csv',
			),
		);
	}

	/**
	 * @return string[]
	 */
	public static function get_application_extension_keys() {
		return self::get_extension_catalog_groups()[ self::ALLOWED_FAMILY_APPLICATION ];
	}

	/**
	 * Human-readable labels for selectable extensions.
	 *
	 * @return array<string, string>
	 */
	public static function get_extension_labels() {
		return array(
			'pdf'  => 'PDF',
			'doc'  => 'Word (.doc)',
			'docx' => 'Word (.docx)',
			'xls'  => 'Excel (.xls)',
			'xlsx' => 'Excel (.xlsx)',
			'ppt'  => 'PowerPoint (.ppt)',
			'pptx' => 'PowerPoint (.pptx)',
			'json' => 'JSON',
			'odt'  => 'OpenDocument Text',
			'jpg'  => 'JPEG',
			'jpeg' => 'JPEG',
			'png'  => 'PNG',
			'gif'  => 'GIF',
			'heic' => 'HEIC',
			'webp' => 'WebP',
			'bmp'  => 'BMP',
			'psd'  => 'Photoshop',
			'txt'  => 'Plain text',
			'csv'  => 'CSV',
		);
	}

	/**
	 * Explicit MIME types for catalog extensions (fallback when WP omits them).
	 *
	 * @return array<string, string>
	 */
	public static function get_explicit_extension_mimes() {
		return array(
			'pdf'  => 'application/pdf',
			'doc'  => 'application/msword',
			'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'xls'  => 'application/vnd.ms-excel',
			'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'ppt'  => 'application/vnd.ms-powerpoint',
			'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			'json' => 'application/json',
			'odt'  => 'application/vnd.oasis.opendocument.text',
			'jpg'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'png'  => 'image/png',
			'gif'  => 'image/gif',
			'heic' => 'image/heic',
			'webp' => 'image/webp',
			'bmp'  => 'image/bmp',
			'psd'  => 'application/vnd.adobe.photoshop',
			'txt'  => 'text/plain',
			'csv'  => 'text/csv',
		);
	}

	/**
	 * Builder + validation catalog (application + image + text).
	 *
	 * @return array<int, array{ext: string, label: string, group: string}>
	 */
	public static function get_selectable_extension_catalog() {
		$catalog = array();
		$labels  = self::get_extension_labels();

		foreach ( self::get_extension_catalog_groups() as $group => $extensions ) {
			foreach ( $extensions as $ext ) {
				if ( self::is_extension_blacklisted( 'file.' . $ext ) ) {
					continue;
				}
				$catalog[] = array(
					'ext'   => $ext,
					'label' => $labels[ $ext ] ?? strtoupper( $ext ),
					'group' => $group,
				);
			}
		}

		return $catalog;
	}

	/**
	 * @return string[]
	 */
	public static function get_selectable_extension_keys() {
		return wp_list_pluck( self::get_selectable_extension_catalog(), 'ext' );
	}

	/**
	 * Default extensions for a new file field (all application types).
	 *
	 * @return string[]
	 */
	public static function get_default_extensions_for_new_field() {
		return self::get_application_extension_keys();
	}

	/**
	 * Resolve allowed extensions for a file field definition.
	 *
	 * @param array $field Field definition.
	 * @return string[]
	 */
	public static function get_field_allowed_extensions( $field ) {
		$extensions = array();
		$allowed    = self::get_selectable_extension_keys();

		if ( ! empty( $field['allowedExtensions'] ) && is_array( $field['allowedExtensions'] ) ) {
			foreach ( $field['allowedExtensions'] as $ext ) {
				$ext = sanitize_key( $ext );
				if ( in_array( $ext, $allowed, true ) ) {
					$extensions[] = $ext;
				}
			}
		}

		if ( empty( $extensions ) ) {
			return self::get_default_extensions_for_new_field();
		}

		return array_values( array_unique( $extensions ) );
	}

	/**
	 * Allowed MIME map for explicit extension selection.
	 *
	 * @param string[] $extensions Extension keys.
	 * @return array<string, string>
	 */
	public static function get_allowed_mimes_for_extensions( $extensions ) {
		$extensions = self::get_field_allowed_extensions( array( 'allowedExtensions' => $extensions ) );
		$wp         = wp_get_mime_types();
		$explicit   = self::get_explicit_extension_mimes();
		$mimes      = array();

		foreach ( $extensions as $ext ) {
			$found = false;
			foreach ( $wp as $ext_group => $mime ) {
				$parts = explode( '|', $ext_group );
				if ( in_array( $ext, $parts, true ) ) {
					$mimes[ $ext_group ] = $mime;
					$found               = true;
					break;
				}
			}
			if ( ! $found && isset( $explicit[ $ext ] ) ) {
				$mimes[ $ext ] = $explicit[ $ext ];
			}
		}

		return $mimes;
	}

	/**
	 * HTML accept attribute for selected extensions.
	 *
	 * @param string[] $extensions Extension keys.
	 * @return string
	 */
	public static function get_accept_attribute_for_extensions( $extensions ) {
		$accepted = array();

		foreach ( self::get_field_allowed_extensions( array( 'allowedExtensions' => $extensions ) ) as $ext ) {
			if ( self::is_extension_blacklisted( 'file.' . $ext ) ) {
				continue;
			}
			$accepted[] = '.' . $ext;
		}

		$accepted = array_unique( $accepted );
		sort( $accepted );

		return implode( ',', $accepted );
	}

	/**
	 * Visitor-facing list of accepted formats.
	 *
	 * @param string[] $extensions Extension keys.
	 * @return string
	 */
	public static function get_accepted_formats_label( $extensions ) {
		$selected = array_flip( self::get_field_allowed_extensions( array( 'allowedExtensions' => $extensions ) ) );
		$labels   = array();

		foreach ( self::get_selectable_extension_catalog() as $item ) {
			if ( isset( $selected[ $item['ext'] ] ) ) {
				$labels[] = $item['label'];
			}
		}

		return implode( ', ', $labels );
	}

	/**
	 * MIME map for a family slug (legacy helper).
	 *
	 * @param string $family application|image|text.
	 * @return array<string, string> Extension => mime.
	 */
	public static function get_mimes_for_family( $family ) {
		$family = sanitize_key( $family );
		$groups = self::get_extension_catalog_groups();
		if ( ! isset( $groups[ $family ] ) ) {
			return array();
		}

		return self::get_allowed_mimes_for_extensions( $groups[ $family ] );
	}

	/**
	 * @param string $filename File name.
	 * @return bool
	 */
	public static function is_extension_blacklisted( $filename ) {
		$ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
		return '' !== $ext && in_array( $ext, self::get_blacklisted_extensions(), true );
	}

	/**
	 * Effective max bytes for a file field.
	 *
	 * @param array $field Field definition.
	 * @return int
	 */
	public static function get_field_max_bytes( $field ) {
		$mb = isset( $field['maxSizeMb'] ) ? (int) $field['maxSizeMb'] : self::DEFAULT_MAX_SIZE_MB;
		$mb = max( 1, min( $mb, self::get_server_max_upload_mb() ) );
		return $mb * 1048576;
	}

	/**
	 * Apache rules: deny all direct HTTP access (admin download uses PHP readfile).
	 *
	 * @return string
	 */
	public static function get_htaccess_rules() {
		$rules  = '# ' . self::HTACCESS_MARKER . "\n";
		$rules .= "# Sobi Forms — block direct web access to submission files.\n";
		$rules .= "Options -Indexes\n";
		$rules .= "<IfModule mod_authz_core.c>\n";
		$rules .= "  Require all denied\n";
		$rules .= "</IfModule>\n";
		$rules .= "<IfModule !mod_authz_core.c>\n";
		$rules .= "  Order deny,allow\n";
		$rules .= "  Deny from all\n";
		$rules .= "</IfModule>\n";

		return $rules;
	}

	/**
	 * Upgrade .htaccess on existing installs (once per version).
	 */
	public static function maybe_harden_upload_directory() {
		if ( (int) get_option( self::HTACCESS_VERSION_OPTION, 0 ) >= self::HTACCESS_VERSION ) {
			return;
		}

		$base = self::ensure_upload_directory();
		if ( ! $base ) {
			return;
		}

		$htaccess = $base . '/.htaccess';
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$current = file_exists( $htaccess ) ? (string) file_get_contents( $htaccess ) : '';
		if ( false !== strpos( $current, self::HTACCESS_MARKER ) ) {
			update_option( self::HTACCESS_VERSION_OPTION, self::HTACCESS_VERSION, false );
		}
	}

	/**
	 * Ensure base upload directory exists with hardening files.
	 *
	 * @return string|false Absolute base path or false.
	 */
	public static function ensure_upload_directory() {
		$upload = wp_upload_dir();
		if ( ! empty( $upload['error'] ) ) {
			return false;
		}

		$base = trailingslashit( $upload['basedir'] ) . self::UPLOAD_SUBDIR;
		if ( ! wp_mkdir_p( $base ) ) {
			return false;
		}

		$htaccess = $base . '/.htaccess';
		$rules    = self::get_htaccess_rules();
		$current  = file_exists( $htaccess )
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			? (string) file_get_contents( $htaccess )
			: '';

		if ( false === strpos( $current, self::HTACCESS_MARKER ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			file_put_contents( $htaccess, $rules );
		}

		$index = $base . '/index.php';
		if ( ! file_exists( $index ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			file_put_contents( $index, "<?php\n// Silence is golden.\n" );
		}

		return $base;
	}

	/**
	 * Validate an uploaded file before moving it to permanent storage.
	 *
	 * @param array $file    $_FILES slice.
	 * @param array $field     Field definition.
	 * @return array|WP_Error Validated metadata or error.
	 */
	public static function validate_uploaded_file( $file, $field ) {
		if ( ! is_array( $file ) ) {
			return new WP_Error( 'file', 'invalid' );
		}

		$error = isset( $file['error'] ) ? (int) $file['error'] : UPLOAD_ERR_NO_FILE;
		$required = ! empty( $field['required'] );

		if ( UPLOAD_ERR_NO_FILE === $error ) {
			if ( $required ) {
				return new WP_Error( 'required', 'missing' );
			}
			return array( 'empty' => true );
		}

		if ( UPLOAD_ERR_OK !== $error ) {
			return new WP_Error( 'file', 'upload_error' );
		}

		$tmp_name = $file['tmp_name'] ?? '';
		if ( '' === $tmp_name || ! is_uploaded_file( $tmp_name ) ) {
			return new WP_Error( 'file', 'invalid' );
		}

		$original_raw = isset( $file['name'] ) ? (string) $file['name'] : '';
		$original     = sanitize_file_name( $original_raw );

		if ( '' === $original ) {
			return new WP_Error( 'file', 'invalid_name' );
		}

		if ( mb_strlen( $original ) > self::MAX_ORIGINAL_NAME_LENGTH ) {
			return new WP_Error( 'file', 'name_too_long' );
		}

		if ( self::is_extension_blacklisted( $original ) ) {
			return new WP_Error( 'file', 'type_not_allowed' );
		}

		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		$size = @filesize( $tmp_name );
		if ( false === $size || $size <= 0 ) {
			return new WP_Error( 'file', 'empty' );
		}

		$size = (int) $size;

		if ( $size > self::get_field_max_bytes( $field ) ) {
			return new WP_Error( 'file', 'too_large' );
		}

		if ( $size > self::get_server_max_upload_bytes() ) {
			return new WP_Error( 'file', 'server_too_large' );
		}

		$extensions    = self::get_field_allowed_extensions( $field );
		$allowed_mimes = self::get_allowed_mimes_for_extensions( $extensions );
		if ( empty( $allowed_mimes ) ) {
			return new WP_Error( 'file', 'type_not_allowed' );
		}

		$checked = wp_check_filetype_and_ext( $tmp_name, $original, $allowed_mimes );
		$detected_type = isset( $checked['type'] ) ? (string) $checked['type'] : '';
		$detected_ext  = isset( $checked['ext'] ) ? (string) $checked['ext'] : '';

		if ( '' === $detected_type || '' === $detected_ext ) {
			return new WP_Error( 'file', 'type_not_allowed' );
		}

		if ( self::is_extension_blacklisted( 'file.' . $detected_ext ) ) {
			return new WP_Error( 'file', 'type_not_allowed' );
		}

		// Any validated image/* MIME (except SVG) must pass getimagesize — even on Documents+Images union fields.
		if ( 0 === strpos( $detected_type, 'image/' ) && 'image/svg+xml' !== $detected_type ) {
			$image_info = @getimagesize( $tmp_name );
			if ( false === $image_info ) {
				return new WP_Error( 'file', 'fake_image' );
			}
		}

		return array(
			'empty'         => false,
			'original_name' => $original,
			'tmp_name'      => $tmp_name,
			'size'          => $size,
			'mime'          => $detected_type,
			'ext'           => $detected_ext,
		);
	}

	/**
	 * Move a validated upload into permanent storage.
	 *
	 * @param array $validated Output from validate_uploaded_file().
	 * @param int   $form_id   Form ID.
	 * @param int   $submission_id Submission ID.
	 * @return array|WP_Error Stored metadata.
	 */
	public static function store_submission_file( $validated, $form_id, $submission_id ) {
		if ( ! empty( $validated['empty'] ) ) {
			return array();
		}

		$base = self::ensure_upload_directory();
		if ( ! $base ) {
			return new WP_Error( 'file', 'storage_unavailable' );
		}

		$form_id       = absint( $form_id );
		$submission_id = absint( $submission_id );
		$target_dir    = trailingslashit( $base ) . $form_id . '/' . $submission_id;

		if ( ! wp_mkdir_p( $target_dir ) ) {
			return new WP_Error( 'file', 'storage_unavailable' );
		}

		$ext         = sanitize_key( $validated['ext'] ?? 'bin' );
		$stored_name = wp_unique_filename( $target_dir, 'upload-' . wp_generate_password( 8, false, false ) . '.' . $ext );
		$destination = trailingslashit( $target_dir ) . $stored_name;

		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, Generic.PHP.ForbiddenFunctions.Found -- move_uploaded_file() is required for validated submission uploads.
		if ( ! @move_uploaded_file( $validated['tmp_name'], $destination ) ) {
			return new WP_Error( 'file', 'move_failed' );
		}

		$upload      = wp_upload_dir();
		$relative    = self::UPLOAD_SUBDIR . '/' . $form_id . '/' . $submission_id . '/' . $stored_name;
		$absolute    = trailingslashit( $upload['basedir'] ) . $relative;

		return array(
			'original_name' => $validated['original_name'],
			'stored_name'   => $stored_name,
			'mime'          => $validated['mime'],
			'size'          => (int) $validated['size'],
			'relative_path' => $relative,
			'absolute_path' => $absolute,
		);
	}

	/**
	 * Build a flat values[] entry for a stored file.
	 *
	 * @param array $field Field definition.
	 * @param array $stored Stored file metadata.
	 * @return array
	 */
	public static function build_value_entry( $field, $stored ) {
		return array(
			'label'         => $field['label'] ?? '',
			'type'          => 'file',
			'original_name' => $stored['original_name'] ?? '',
			'stored_name'   => $stored['stored_name'] ?? '',
			'mime'          => $stored['mime'] ?? '',
			'size'          => (int) ( $stored['size'] ?? 0 ),
			'relative_path' => $stored['relative_path'] ?? '',
		);
	}

	/**
	 * Spam-only stub without disk storage.
	 *
	 * @param array $field     Field definition.
	 * @param array $validated Validated upload metadata.
	 * @return array
	 */
	public static function build_spam_value_entry( $field, $validated ) {
		return array(
			'label'         => $field['label'] ?? '',
			'type'          => 'file',
			'original_name' => $validated['original_name'] ?? '',
			'stored_name'   => '',
			'mime'          => $validated['mime'] ?? '',
			'size'          => (int) ( $validated['size'] ?? 0 ),
			'relative_path' => '',
		);
	}

	/**
	 * Delete files referenced in submission values.
	 *
	 * @param array $values Submission values map.
	 */
	public static function delete_files_from_values( $values ) {
		if ( ! is_array( $values ) ) {
			return;
		}

		foreach ( $values as $item ) {
			if ( ! is_array( $item ) || 'file' !== ( $item['type'] ?? '' ) ) {
				continue;
			}

			$path = self::resolve_absolute_path( $item['relative_path'] ?? '' );
			if ( $path && file_exists( $path ) ) {
				wp_delete_file( $path );
			}
		}
	}

	/**
	 * Whether a stored relative path belongs to the given submission.
	 *
	 * @param string $relative_path Path relative to uploads basedir.
	 * @param int    $form_id       Form ID.
	 * @param int    $submission_id Submission ID.
	 * @return bool
	 */
	public static function relative_path_matches_submission( $relative_path, $form_id, $submission_id ) {
		$form_id       = absint( $form_id );
		$submission_id = absint( $submission_id );

		if ( ! $form_id || ! $submission_id ) {
			return false;
		}

		$relative_path = ltrim( str_replace( '\\', '/', (string) $relative_path ), '/' );
		$expected      = self::UPLOAD_SUBDIR . '/' . $form_id . '/' . $submission_id . '/';

		if ( 0 !== strpos( $relative_path, $expected ) ) {
			return false;
		}

		$basename = substr( $relative_path, strlen( $expected ) );

		return '' !== $basename
			&& false === strpos( $basename, '/' )
			&& false === strpos( $basename, '\\' )
			&& false === strpos( $basename, '..' );
	}

	/**
	 * Resolve and validate an absolute path inside uploads/sobiforms/.
	 *
	 * @param string $relative_path Path relative to uploads basedir.
	 * @return string|false
	 */
	public static function resolve_absolute_path( $relative_path ) {
		$relative_path = ltrim( str_replace( '\\', '/', (string) $relative_path ), '/' );
		if ( '' === $relative_path || 0 !== strpos( $relative_path, self::UPLOAD_SUBDIR . '/' ) ) {
			return false;
		}

		if ( false !== strpos( $relative_path, '..' ) ) {
			return false;
		}

		$upload = wp_upload_dir();
		if ( ! empty( $upload['error'] ) ) {
			return false;
		}

		$absolute = trailingslashit( $upload['basedir'] ) . $relative_path;
		$real     = realpath( $absolute );
		$base     = realpath( trailingslashit( $upload['basedir'] ) . self::UPLOAD_SUBDIR );

		if ( ! $real || ! $base || 0 !== strpos( $real, $base ) ) {
			return false;
		}

		return $real;
	}

	/**
	 * Roll back moved files and optional submission row.
	 *
	 * @param int      $submission_id Submission ID (0 to skip row delete).
	 * @param string[] $absolute_paths Files to unlink.
	 */
	public static function rollback_submission_files( $submission_id, $absolute_paths ) {
		if ( is_array( $absolute_paths ) ) {
			foreach ( $absolute_paths as $path ) {
				if ( is_string( $path ) && file_exists( $path ) ) {
					wp_delete_file( $path );
				}
			}
		}

		if ( $submission_id ) {
			Sobiforms_Submission_Store::delete( $submission_id );
		}
	}

	/**
	 * Whether the web server looks like Nginx (Apache uses .htaccess instead).
	 *
	 * @return bool
	 */
	public static function is_likely_nginx() {
		if ( ! empty( $_SERVER['NGINX_VERSION'] ) ) {
			return true;
		}

		$software = '';
		if ( isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
			$software = sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) );
		}
		if ( '' === $software ) {
			return false;
		}

		return (bool) preg_match( '/nginx/i', $software );
	}

	/**
	 * Clear cached upload-directory web access probe (e.g. after form save).
	 */
	public static function clear_upload_web_access_cache() {
		delete_transient( self::UPLOAD_PROBE_TRANSIENT );
	}

	/**
	 * Whether submission files in uploads/sobiforms/ are reachable over HTTP.
	 *
	 * @param bool $force Skip transient cache.
	 * @return bool|null True when exposed, false when blocked, null when unknown.
	 */
	public static function is_upload_directory_web_exposed( $force = false ) {
		/**
		 * Disable the HTTP probe that checks whether uploads/sobiforms/ is web-accessible.
		 *
		 * @param bool $skip Default false.
		 */
		if ( apply_filters( 'sobiforms_skip_upload_directory_web_probe', false ) ) {
			return null;
		}

		if ( ! $force ) {
			$cached = get_transient( self::UPLOAD_PROBE_TRANSIENT );
			if ( false !== $cached ) {
				return '1' === $cached ? true : false;
			}
		}

		$result = self::probe_upload_directory_web_access();
		if ( null === $result ) {
			return null;
		}

		set_transient( self::UPLOAD_PROBE_TRANSIENT, $result ? '1' : '0', self::UPLOAD_PROBE_TTL );

		return $result;
	}

	/**
	 * HTTP GET probe: create a marker file and check if it is publicly readable.
	 *
	 * @return bool|null
	 */
	private static function probe_upload_directory_web_access() {
		$base = self::ensure_upload_directory();
		if ( ! $base ) {
			return null;
		}

		$upload = wp_upload_dir();
		if ( ! empty( $upload['error'] ) ) {
			return null;
		}

		$token     = wp_generate_password( 24, false, false );
		$filename  = '.sobiforms-probe-' . wp_generate_password( 12, false, false ) . '.txt';
		$filepath  = trailingslashit( $base ) . $filename;
		$probe_url = trailingslashit( $upload['baseurl'] ) . self::UPLOAD_SUBDIR . '/' . $filename;

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		if ( false === file_put_contents( $filepath, $token ) ) {
			return null;
		}

		$response = wp_remote_get(
			$probe_url,
			array(
				'timeout'     => 8,
				'redirection' => 0,
				'sslverify'   => apply_filters( 'https_local_ssl_verify', false ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress core filter for local HTTPS probes.
				'headers'     => array(
					'Cache-Control' => 'no-cache',
				),
			)
		);

		wp_delete_file( $filepath );

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		if ( in_array( $code, array( 401, 403, 404 ), true ) ) {
			return false;
		}

		if ( 200 !== $code ) {
			return null;
		}

		$body = (string) wp_remote_retrieve_body( $response );

		return hash_equals( $token, trim( $body ) );
	}

	/**
	 * Whether admins should see the Nginx upload hardening notice.
	 *
	 * @return bool
	 */
	public static function should_show_nginx_upload_security_notice() {
		if ( ! self::is_likely_nginx() ) {
			return false;
		}

		if ( ! Sobiforms_Form_Repository::any_form_has_file_fields() ) {
			return false;
		}

		$exposed = self::is_upload_directory_web_exposed();
		if ( true !== $exposed ) {
			return false;
		}

		$dismissed_at = (int) get_user_meta( get_current_user_id(), 'sobiforms_dismiss_nginx_upload_notice', true );
		if ( $dismissed_at > 0 && ( time() - $dismissed_at ) < WEEK_IN_SECONDS ) {
			return false;
		}

		return true;
	}
}
