<?php
/**
 * Shared HTML renderer for shortcode and Gutenberg block.
 *
 * @package SobiForms
 */

defined( 'ABSPATH' ) || exit;

/**
 * Outputs dynamic form markup from stored schema.
 */
class Sobiforms_Form_Renderer {

	/**
	 * Whether front assets were enqueued this request.
	 *
	 * @var bool
	 */
	private static $assets_enqueued = false;

	/**
	 * Whether front stylesheet was enqueued this request.
	 *
	 * @var bool
	 */
	private static $styles_enqueued = false;

	/**
	 * Register front asset handles (enqueue happens on first render).
	 */
	public static function register_assets() {
		add_action( 'init', array( __CLASS__, 'register_front_assets' ), 20 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_enqueue_on_singular' ), 20 );
	}

	/**
	 * Enqueue front assets early on singular posts that contain a form (avoids FOUC).
	 */
	public static function maybe_enqueue_on_singular() {
		if ( is_admin() ) {
			return;
		}

		/**
		 * Allow themes or page builders to flag pages that include a Sobi form.
		 *
		 * @param bool $has_form Whether the current page has a form.
		 */
		if ( apply_filters( 'sobiforms_page_has_form', false ) ) {
			self::maybe_enqueue_front_assets();
			return;
		}

		if ( ! is_singular() ) {
			return;
		}

		$post = get_post();
		if ( ! $post instanceof WP_Post ) {
			return;
		}

		if (
			has_shortcode( $post->post_content, 'sobiforms' )
			|| has_block( 'sobiforms/contact', $post )
		) {
			self::maybe_enqueue_front_assets();
		}
	}

	/**
	 * Register stylesheet and script for conditional enqueue.
	 */
	public static function register_front_assets() {
		wp_register_style(
			'sobiforms',
			SOBIFORMS_URL . 'assets/css/form.css',
			array(),
			SOBIFORMS_VERSION
		);

		wp_register_script(
			'sobiforms',
			SOBIFORMS_URL . 'assets/js/form.js',
			array(),
			SOBIFORMS_VERSION,
			true
		);
	}

	/**
	 * Enqueue front stylesheet once (unavailable message or full form).
	 */
	private static function maybe_enqueue_front_styles() {
		if ( self::$styles_enqueued || is_admin() ) {
			return;
		}

		/**
		 * Allow themes or integrations to skip plugin CSS/JS (e.g. custom styling).
		 *
		 * @param bool $enqueue Whether to enqueue SobiForms front assets.
		 */
		if ( ! apply_filters( 'sobiforms_enqueue_front_assets', true ) ) {
			return;
		}

		self::$styles_enqueued = true;
		wp_enqueue_style( 'sobiforms' );
	}

	/**
	 * Enqueue front CSS and JS once, when a submittable form is output.
	 */
	private static function maybe_enqueue_front_assets() {
		self::maybe_enqueue_front_styles();

		if ( self::$assets_enqueued || is_admin() ) {
			return;
		}

		if ( ! apply_filters( 'sobiforms_enqueue_front_assets', true ) ) {
			return;
		}

		self::$assets_enqueued = true;

		wp_enqueue_script( 'sobiforms' );
		wp_localize_script(
			'sobiforms',
			'sobiformsData',
			array(
				'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
				'captureSource' => apply_filters( 'sobiforms_capture_submission_source', true ),
				'i18n'          => array(
					/* translators: %d: maximum number of characters allowed in the field */
					'maxLength' => __( 'Maximum %d characters allowed.', 'sobi-forms' ),
					/* translators: %d: minimum number of characters required in the field */
					'minLength' => __( 'Minimum %d characters required.', 'sobi-forms' ),
					/* translators: %s: maximum file size (e.g. 5 MB) */
					'fileTooLarge' => __( 'File must be smaller than %s.', 'sobi-forms' ),
					'networkError' => __( 'Network error. Please try again.', 'sobi-forms' ),
					'timeoutError' => __( 'Request timed out. Please try again.', 'sobi-forms' ),
				),
			)
		);

		if ( function_exists( 'wp_script_add_data' ) ) {
			wp_script_add_data( 'sobiforms', 'strategy', 'defer' );
		}
	}

	/**
	 * Shortcode callback.
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string
	 */
	public static function shortcode( $atts = array() ) {
		$atts = shortcode_atts(
			array(
				'id'   => 0,
				'slug' => ''),
			$atts,
			'sobiforms'
		);

		if ( ! self::should_render() ) {
			return '';
		}

		$form = self::resolve_form( absint( $atts['id'] ), sanitize_title( $atts['slug'] ) );
		if ( ! $form ) {
			return '';
		}

		return self::render( $form );
	}

	/**
	 * Gutenberg block render callback.
	 *
	 * @param array    $attributes Block attributes.
	 * @param string   $content    Block content.
	 * @param WP_Block $block      Block instance.
	 * @return string
	 */
	public static function render_block( $attributes, $content, $block ) {
		if ( ! self::should_render() ) {
			return '';
		}

		$form_id = isset( $attributes['formId'] ) ? absint( $attributes['formId'] ) : 0;
		$form    = self::resolve_form( $form_id, '' );
		if ( ! $form ) {
			return '';
		}
		return self::render( $form );
	}

	/**
	 * Skip form output in excerpts, feeds, and post list contexts.
	 *
	 * @return bool
	 */
	private static function should_render() {
		if ( doing_filter( 'get_the_excerpt' ) || doing_filter( 'the_excerpt' ) ) {
			return false;
		}

		if ( is_feed() ) {
			return false;
		}

		// Home, archives, search: render only on the single post/page view.
		if ( in_the_loop() && ! is_singular() ) {
			return false;
		}

		return true;
	}

	/**
	 * Resolve form by ID or slug.
	 *
	 * @param int    $id   Form ID.
	 * @param string $slug Form slug.
	 * @return array|null
	 */
	public static function resolve_form( $id = 0, $slug = '' ) {
		if ( $id ) {
			return Sobiforms_Form_Repository::get_by_id( $id );
		}
		if ( $slug ) {
			return Sobiforms_Form_Repository::get_by_slug( $slug );
		}

		return null;
	}

	/**
	 * Render a form from its stored definition.
	 *
	 * @param array $form Form row with fields array.
	 * @return string
	 */
	public static function render( $form ) {
		if ( empty( $form['fields'] ) || ! is_array( $form['fields'] ) ) {
			return '';
		}

		if ( ! Sobiforms_Form_Repository::is_accepting_submissions( $form ) ) {
			return self::render_unavailable( $form );
		}

		self::maybe_enqueue_front_assets();

		$form_id = (int) $form['id'];
		$uid     = 'sobiforms-form-' . $form_id;
		$prefill = self::build_prefill_config( $form['fields'] );
		$has_file_fields = Sobiforms_File_Upload::form_has_file_fields( $form );

		ob_start();
		?>
		<form class="sobiforms-form" id="<?php echo esc_attr( $uid ); ?>" method="post" novalidate
			<?php if ( $has_file_fields ) : ?>
				enctype="multipart/form-data"
			<?php endif; ?>
			data-form-id="<?php echo esc_attr( $form_id ); ?>"
			<?php if ( ! empty( $prefill ) ) : ?>
				data-sobiforms-prefill="<?php echo esc_attr( wp_json_encode( $prefill ) ); ?>"
			<?php endif; ?>>
			<div class="sobiforms-honeypot" aria-hidden="true">
				<label><?php esc_html_e( 'Website', 'sobi-forms' ); ?></label>
				<input type="text" name="sobiforms_website" tabindex="-1" autocomplete="off" />
			</div>
			<input type="hidden" name="sobiforms_form_id" value="<?php echo esc_attr( $form_id ); ?>" />

			<?php foreach ( $form['fields'] as $field ) : ?>
				<?php if ( Sobiforms_Form_Repository::is_hidden_field( $field['type'] ?? '' ) ) : ?>
					<?php echo self::render_hidden_field( $field ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endif; ?>
			<?php endforeach; ?>

			<?php foreach ( $form['fields'] as $field ) : ?>
				<?php if ( Sobiforms_Form_Repository::is_hidden_field( $field['type'] ?? '' ) ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<?php echo self::render_field( $field ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endforeach; ?>

			<p class="sobiforms-actions">
				<button type="submit" class="sobiforms-submit"><?php echo esc_html( Sobiforms_Form_Repository::get_submit_button_text( $form ) ); ?></button>
			</p>

			<div class="sobiforms-feedback" role="status" aria-live="polite" hidden></div>
		</form>
		<?php
		return ob_get_clean();
	}

	/**
	 * Build client-side URL prefill mapping for scalar fields.
	 *
	 * @param array $fields Form field schema.
	 * @return array<int, array{keys: string[], name: string}>
	 */
	private static function build_prefill_config( $fields ) {
		/**
		 * Allow themes or integrations to disable URL query prefill.
		 *
		 * @param bool $allow Whether URL prefill is enabled.
		 */
		$allow_prefill = apply_filters( 'sobiforms_allow_url_prefill', true );
		/**
		 * Allow themes or integrations to disable hidden metadata fields.
		 *
		 * @param bool $allow Whether hidden fields are enabled.
		 */
		$allow_hidden = apply_filters( 'sobiforms_allow_hidden_fields', true );

		if ( ! $allow_prefill && ! $allow_hidden ) {
			return array();
		}

		if ( ! is_array( $fields ) ) {
			return array();
		}

		$config = array();

		foreach ( $fields as $field ) {
			if ( ! is_array( $field ) ) {
				continue;
			}

			$type = $field['type'] ?? '';
			$id   = isset( $field['id'] ) ? sanitize_key( $field['id'] ) : '';
			if ( '' === $id ) {
				continue;
			}

			if ( Sobiforms_Form_Repository::is_hidden_field( $type ) ) {
				if ( ! $allow_hidden ) {
					continue;
				}

				$keys = array( $id );
				if ( ! empty( $field['prefillKey'] ) ) {
					array_unshift( $keys, sanitize_key( $field['prefillKey'] ) );
				}

				$keys = array_values( array_unique( array_filter( $keys ) ) );

				$entry = array(
					'keys'   => $keys,
					'name'   => 'sobiforms_field_' . $id,
					'hidden' => true,
				);

				if ( ! empty( $field['defaultValue'] ) ) {
					$entry['default'] = $field['defaultValue'];
				}

				$config[] = $entry;
				continue;
			}

			if ( ! $allow_prefill || ! Sobiforms_Form_Repository::is_prefill_scalar_field( $type ) ) {
				continue;
			}

			$prefill_enabled = ! empty( $field['urlPrefill'] ) || ! empty( $field['prefillKey'] );
			if ( ! $prefill_enabled ) {
				continue;
			}

			$keys = array( $id );
			if ( ! empty( $field['prefillKey'] ) ) {
				array_unshift( $keys, sanitize_key( $field['prefillKey'] ) );
			}

			$keys = array_values( array_unique( array_filter( $keys ) ) );

			$config[] = array(
				'keys' => $keys,
				'name' => 'sobiforms_field_' . $id,
			);
		}

		return $config;
	}

	/**
	 * Render a hidden metadata field (always submitted, value set client-side).
	 *
	 * @param array $field Field definition.
	 * @return string
	 */
	private static function render_hidden_field( $field ) {
		$field_id = sanitize_key( $field['id'] );
		$name     = 'sobiforms_field_' . $field_id;

		ob_start();
		?>
		<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="" />
		<?php
		return ob_get_clean();
	}

	/**
	 * Output visitor message when the form is paused or past its close time.
	 *
	 * @param array $form Form row.
	 * @return string
	 */
	private static function render_unavailable( $form ) {
		self::maybe_enqueue_front_styles();

		$message = Sobiforms_Form_Repository::get_unavailable_message( $form );

		ob_start();
		?>
		<div class="sobiforms sobiforms-unavailable" role="status">
			<p class="sobiforms-unavailable-message"><?php echo esc_html( $message ); ?></p>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render a single field from schema.
	 *
	 * @param array $field Field definition.
	 * @return string
	 */
	private static function render_field( $field ) {
		$field_id    = sanitize_key( $field['id'] );
		$label       = $field['label'];
		$required    = ! empty( $field['required'] );
		$name        = 'sobiforms_field_' . $field_id;
		$placeholder = $field['placeholder'] ?? '';
		$shows_label = self::field_shows_label( $field );

		ob_start();

		if ( Sobiforms_Form_Repository::is_layout_field( $field['type'] ) ) {
			if ( 'title' === $field['type'] ) {
				?>
				<div class="sobiforms-layout-block">
					<h2 class="sobiforms-title"><?php echo esc_html( $field['label'] ?? '' ); ?></h2>
				</div>
				<?php
				return ob_get_clean();
			}

			if ( 'paragraph' === $field['type'] ) {
				?>
				<div class="sobiforms-layout-block">
					<p class="sobiforms-paragraph"><?php echo esc_html( $field['content'] ?? '' ); ?></p>
				</div>
				<?php
				return ob_get_clean();
			}
		}

		if ( 'checkbox' === $field['type'] ) {
			if ( Sobiforms_Form_Repository::field_has_checkbox_options( $field ) ) {
				$options = $field['options'];
				?>
				<fieldset class="sobiforms-field sobiforms-checkbox-group"
					<?php if ( ! $shows_label ) : ?>
						aria-label="<?php echo esc_attr( self::field_accessible_name( $field ) ); ?>"
					<?php endif; ?>
					<?php self::render_aria_required_attr( $required ); ?>>
					<?php if ( $shows_label ) : ?>
						<legend>
							<?php echo esc_html( $label ); ?>
							<?php if ( $required ) : ?>
								<span class="sobiforms-required">*</span>
							<?php endif; ?>
						</legend>
					<?php endif; ?>
					<?php foreach ( $options as $option ) : ?>
						<label class="sobiforms-checkbox-option">
							<input
								type="checkbox"
								name="<?php echo esc_attr( $name ); ?>[]"
								value="<?php echo esc_attr( $option ); ?>"
							/>
							<span class="sobiforms-checkbox-option__label"><?php echo esc_html( $option ); ?></span>
						</label>
					<?php endforeach; ?>
				</fieldset>
				<?php
				return ob_get_clean();
			}
			?>
			<p class="sobiforms-field sobiforms-consent">
				<label>
					<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1"<?php self::render_required_attr( $required ); ?> />
					<?php echo esc_html( $label ); ?>
					<?php if ( $required ) : ?>
						<span class="sobiforms-required">*</span>
					<?php endif; ?>
				</label>
			</p>
			<?php
			return ob_get_clean();
		}

		if ( 'select' === $field['type'] ) {
			$options = isset( $field['options'] ) && is_array( $field['options'] ) ? $field['options'] : array();
			?>
			<p class="sobiforms-field">
				<?php if ( $shows_label ) : ?>
					<?php self::render_field_label( $field_id, $label, $required ); ?>
				<?php endif; ?>
				<select id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $name ); ?>"
					<?php self::render_aria_label_attr( $field ); ?>
					<?php self::render_aria_required_attr( $required ); ?>
					<?php self::render_required_attr( $required ); ?>>
					<?php if ( ! $required ) : ?>
						<option value="">
							<?php
							echo '' !== $placeholder
								? esc_html( $placeholder )
								: esc_html__( '— Select —', 'sobi-forms' );
							?>
						</option>
					<?php endif; ?>
					<?php foreach ( $options as $option ) : ?>
						<option value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $option ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<?php
			return ob_get_clean();
		}

		if ( 'radio' === $field['type'] ) {
			$options = isset( $field['options'] ) && is_array( $field['options'] ) ? $field['options'] : array();
			?>
			<fieldset class="sobiforms-field sobiforms-choice-group"
				<?php if ( ! $shows_label ) : ?>
					aria-label="<?php echo esc_attr( self::field_accessible_name( $field ) ); ?>"
				<?php endif; ?>
				<?php self::render_aria_required_attr( $required ); ?>>
				<?php if ( $shows_label ) : ?>
					<legend>
						<?php echo esc_html( $label ); ?>
						<?php if ( $required ) : ?>
							<span class="sobiforms-required">*</span>
						<?php endif; ?>
					</legend>
				<?php endif; ?>
				<div class="sobiforms-choice-list">
					<?php foreach ( $options as $i => $option ) : ?>
						<label class="sobiforms-choice-tile">
							<span class="sobiforms-choice-tile__badge" aria-hidden="true"><?php echo esc_html( self::choice_badge_letter( $i ) ); ?></span>
							<input
								type="radio"
								class="sobiforms-choice-tile__input"
								name="<?php echo esc_attr( $name ); ?>"
								value="<?php echo esc_attr( $option ); ?>"
								<?php self::render_required_attr( 0 === $i && $required ); ?>
							/>
							<span class="sobiforms-choice-tile__label"><?php echo esc_html( $option ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
			</fieldset>
			<?php
			return ob_get_clean();
		}

		?>
		<p class="sobiforms-field">
			<?php if ( $shows_label ) : ?>
				<?php self::render_field_label( $field_id, $label, $required ); ?>
			<?php endif; ?>
			<?php if ( 'textarea' === $field['type'] ) : ?>
				<?php
				$resizable      = ! isset( $field['resizable'] ) || $field['resizable'];
				$textarea_class = 'sobiforms-textarea' . ( $resizable ? '' : ' sobiforms-textarea--no-resize' );
				$max_length     = isset( $field['maxLength'] ) ? (int) $field['maxLength'] : 0;
				?>
				<textarea id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $name ); ?>"
					class="<?php echo esc_attr( $textarea_class ); ?>"
					rows="5" placeholder="<?php echo esc_attr( $placeholder ); ?>"
					<?php if ( $max_length > 0 ) : ?>
						maxlength="<?php echo esc_attr( (string) $max_length ); ?>"
						data-sobiforms-maxlength="<?php echo esc_attr( (string) $max_length ); ?>"
					<?php endif; ?>
					<?php self::render_aria_label_attr( $field ); ?>
					<?php self::render_aria_required_attr( $required ); ?>
					<?php self::render_required_attr( $required ); ?>></textarea>
				<span class="sobiforms-field-error" role="alert" hidden></span>
			<?php elseif ( 'email' === $field['type'] ) : ?>
				<input type="email" id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $name ); ?>"
					placeholder="<?php echo esc_attr( $placeholder ); ?>" maxlength="255"
					<?php self::render_aria_label_attr( $field ); ?>
					<?php self::render_aria_required_attr( $required ); ?>
					<?php self::render_required_attr( $required ); ?> />
			<?php elseif ( 'phone' === $field['type'] ) : ?>
				<input type="tel" id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $name ); ?>"
					inputmode="tel" autocomplete="tel" placeholder="<?php echo esc_attr( $placeholder ); ?>" maxlength="32"
					<?php self::render_aria_label_attr( $field ); ?>
					<?php self::render_aria_required_attr( $required ); ?>
					<?php self::render_required_attr( $required ); ?> />
			<?php elseif ( 'url' === $field['type'] ) : ?>
				<input type="url" id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $name ); ?>"
					inputmode="url" autocomplete="url" placeholder="<?php echo esc_attr( $placeholder ); ?>"
					maxlength="<?php echo esc_attr( (string) Sobiforms_Form_Repository::MAX_URL_LENGTH ); ?>"
					<?php self::render_aria_label_attr( $field ); ?>
					<?php self::render_aria_required_attr( $required ); ?>
					<?php self::render_required_attr( $required ); ?> />
				<span class="sobiforms-field-error" role="alert" hidden></span>
			<?php elseif ( 'number' === $field['type'] ) : ?>
				<input type="number" id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $name ); ?>"
					placeholder="<?php echo esc_attr( $placeholder ); ?>"
					<?php echo isset( $field['min'] ) ? ' min="' . esc_attr( (string) $field['min'] ) . '"' : ''; ?>
					<?php echo isset( $field['max'] ) ? ' max="' . esc_attr( (string) $field['max'] ) . '"' : ''; ?>
					<?php self::render_aria_label_attr( $field ); ?>
					<?php self::render_aria_required_attr( $required ); ?>
					<?php self::render_required_attr( $required ); ?> />
			<?php elseif ( Sobiforms_Form_Repository::is_file_field( $field['type'] ?? '' ) ) : ?>
				<?php
				$extensions  = Sobiforms_File_Upload::get_field_allowed_extensions( $field );
				$accept      = Sobiforms_File_Upload::get_accept_attribute_for_extensions( $extensions );
				$max_bytes   = Sobiforms_File_Upload::get_field_max_bytes( $field );
				$max_mb      = max( 1, (int) round( $max_bytes / 1048576 ) );
				$formats     = Sobiforms_File_Upload::get_accepted_formats_label( $extensions );
				$helper_text = sprintf(
					/* translators: 1: maximum file size in megabytes, 2: accepted format list */
					__( 'Maximum file size: %1$d MB. Accepted: %2$s.', 'sobi-forms' ),
					$max_mb,
					$formats
				);
				?>
				<input
					type="file"
					id="<?php echo esc_attr( $field_id ); ?>"
					name="<?php echo esc_attr( $name ); ?>"
					class="sobiforms-file-input"
					<?php if ( $accept ) : ?>
						accept="<?php echo esc_attr( $accept ); ?>"
					<?php endif; ?>
					data-sobiforms-max-file-bytes="<?php echo esc_attr( (string) $max_bytes ); ?>"
					<?php self::render_aria_label_attr( $field ); ?>
					<?php self::render_aria_required_attr( $required ); ?>
					<?php self::render_required_attr( $required ); ?>
				/>
				<span class="sobiforms-field-hint"><?php echo esc_html( $helper_text ); ?></span>
				<span class="sobiforms-field-error" role="alert" hidden></span>
			<?php else : ?>
				<input type="text" id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $name ); ?>"
					placeholder="<?php echo esc_attr( $placeholder ); ?>"
					<?php self::render_length_attrs( $field ); ?>
					<?php self::render_aria_label_attr( $field ); ?>
					<?php self::render_aria_required_attr( $required ); ?>
					<?php self::render_required_attr( $required ); ?> />
				<span class="sobiforms-field-error" role="alert" hidden></span>
			<?php endif; ?>
		</p>
		<?php
		return ob_get_clean();
	}

	/**
	 * Whether the field label is shown above the control on the front-end.
	 *
	 * @param array $field Field definition.
	 * @return bool
	 */
	private static function field_shows_label( $field ) {
		if ( 'checkbox' === ( $field['type'] ?? '' ) && ! Sobiforms_Form_Repository::field_has_checkbox_options( $field ) ) {
			return true;
		}

		return ! isset( $field['showLabel'] ) || $field['showLabel'];
	}

	/**
	 * Letter badge for choice tiles (A, B, C…).
	 *
	 * @param int $index Zero-based option index.
	 * @return string
	 */
	private static function choice_badge_letter( $index ) {
		$index = (int) $index;
		if ( $index < 0 ) {
			return '';
		}

		return chr( 65 + ( $index % 26 ) );
	}

	/**
	 * Output min/max length attributes for text fields.
	 *
	 * @param array $field Field definition.
	 */
	private static function render_length_attrs( $field ) {
		if ( 'text' !== ( $field['type'] ?? '' ) ) {
			return;
		}

		$max = Sobiforms_Form_Repository::field_effective_max_length( $field );
		if ( $max > 0 ) {
			echo ' maxlength="' . esc_attr( (string) $max ) . '"';
			echo ' data-sobiforms-maxlength="' . esc_attr( (string) $max ) . '"';
		}

		if ( isset( $field['minLength'] ) && is_numeric( $field['minLength'] ) ) {
			$min = (int) $field['minLength'];
			if ( $min > 0 ) {
				echo ' minlength="' . esc_attr( (string) $min ) . '"';
				echo ' data-sobiforms-minlength="' . esc_attr( (string) $min ) . '"';
			}
		}
	}

	/**
	 * Accessible name when the visible label is hidden (placeholder first, then stored label).
	 *
	 * @param array $field Field definition.
	 * @return string
	 */
	private static function field_accessible_name( $field ) {
		$placeholder = isset( $field['placeholder'] ) ? trim( (string) $field['placeholder'] ) : '';
		if ( '' !== $placeholder ) {
			return $placeholder;
		}

		return isset( $field['label'] ) ? trim( (string) $field['label'] ) : '';
	}

	/**
	 * Output aria-label when the visible label is hidden.
	 *
	 * @param array $field Field definition.
	 */
	private static function render_aria_label_attr( $field ) {
		if ( self::field_shows_label( $field ) ) {
			return;
		}

		$name = self::field_accessible_name( $field );
		if ( '' === $name ) {
			return;
		}

		echo ' aria-label="' . esc_attr( $name ) . '"';
	}

	/**
	 * Output aria-required for assistive tech when label may be hidden.
	 *
	 * @param bool $required Whether the field is required.
	 */
	private static function render_aria_required_attr( $required ) {
		if ( $required ) {
			echo ' aria-required="true"';
		}
	}

	/**
	 * Output a boolean HTML5 required attribute.
	 *
	 * @param bool $required Whether the field is required.
	 */
	private static function render_required_attr( $required ) {
		if ( $required ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Static boolean HTML5 attribute.
			echo ' required';
		}
	}

	/**
	 * Output a field label with optional required marker.
	 *
	 * @param string $field_id Field element ID.
	 * @param string $label    Field label text.
	 * @param bool   $required Whether the field is required.
	 */
	private static function render_field_label( $field_id, $label, $required ) {
		?>
		<label for="<?php echo esc_attr( $field_id ); ?>">
			<?php echo esc_html( $label ); ?>
			<?php if ( $required ) : ?>
				<span class="sobiforms-required">*</span>
			<?php endif; ?>
		</label>
		<?php
	}
}
