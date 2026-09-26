<?php
/**
 * Dynamic Gutenberg block registration.
 *
 * @package SobiForms
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the SobiForms contact block.
 */
class Sobiforms_Block {

	/**
	 * Editor script handle.
	 */
	const EDITOR_SCRIPT = 'sobiforms-block-editor';

	/**
	 * Register hooks.
	 */
	public static function init() {
		add_filter( 'block_categories_all', array( __CLASS__, 'register_category' ), 10, 2 );
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_editor_assets' ) );
	}

	/**
	 * @param array[] $categories Block categories.
	 * @return array[]
	 */
	public static function register_category( $categories ) {
		$categories[] = array(
			'slug'  => 'sobiforms',
			'title' => 'Sobi Forms',
			'icon'  => null);
		return $categories;
	}

	/**
	 * Register block type.
	 */
	public static function register() {
		wp_register_script(
			self::EDITOR_SCRIPT,
			SOBIFORMS_URL . 'blocks/sobiforms-contact/editor.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n' ),
			SOBIFORMS_VERSION,
			true
		);

		register_block_type(
			'sobiforms/contact',
			array(
				'api_version'     => 3,
				'title'           => __( 'Sobi Forms Contact', 'sobi-forms' ),
				'description'     => __( 'Insert a Sobi Forms form.', 'sobi-forms' ),
				'category'        => 'sobiforms',
				'icon'            => 'email-alt',
				'keywords'        => array( 'sobiforms', 'sobi forms', 'contact', 'email', 'formulaire' ),
				'attributes'      => array(
					'formId' => array(
						'type'    => 'number',
						'default' => 0)),
				'supports'        => array(
					'html'     => false,
					'multiple' => true),
				'editor_script'   => self::EDITOR_SCRIPT,
				'render_callback' => array( 'Sobiforms_Form_Renderer', 'render_block' ))
		);
	}

	/**
	 * Pass forms list to block editor script.
	 */
	public static function enqueue_editor_assets() {
		if ( ! wp_script_is( self::EDITOR_SCRIPT, 'registered' ) ) {
			return;
		}

		$forms = Sobiforms_Form_Repository::get_all_light();
		$list  = array();

		foreach ( $forms as $form ) {
			$list[] = array(
				'id'    => (int) $form['id'],
				'title' => $form['title']);
		}

		wp_localize_script(
			self::EDITOR_SCRIPT,
			'sobiformsBlockForms',
			array( 'forms' => $list )
		);
	}
}
