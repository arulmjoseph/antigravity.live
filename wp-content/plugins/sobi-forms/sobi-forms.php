<?php
/**
 * Plugin Name:       Sobi Forms
 * Plugin URI:        https://sobiforms.com
 * Description:       The lightweight, performance-first form builder with built-in lead management.
 * Version:           1.5.1
 * Author:            alesas
 * Author URI:        https://profiles.wordpress.org/alesas/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       sobi-forms
 * Requires at least: 6.0
 * Requires PHP:      7.4
 */

// Sécurité absolue : Bloquer l'accès direct par URL.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SOBIFORMS_VERSION', '1.5.1' );
define( 'SOBIFORMS_FILE', __FILE__ );
define( 'SOBIFORMS_PATH', plugin_dir_path( __FILE__ ) );
define( 'SOBIFORMS_URL', plugin_dir_url( __FILE__ ) );
define( 'SOBIFORMS_BASENAME', plugin_basename( __FILE__ ) );

require_once SOBIFORMS_PATH . 'includes/class-db.php';
require_once SOBIFORMS_PATH . 'includes/class-akismet.php';
require_once SOBIFORMS_PATH . 'includes/class-form-repository.php';
require_once SOBIFORMS_PATH . 'includes/class-file-upload.php';
require_once SOBIFORMS_PATH . 'includes/class-submission-store.php';
require_once SOBIFORMS_PATH . 'includes/class-form-renderer.php';
require_once SOBIFORMS_PATH . 'includes/class-form-handler.php';
require_once SOBIFORMS_PATH . 'includes/class-forms-admin.php';
require_once SOBIFORMS_PATH . 'includes/class-admin-icons.php';
require_once SOBIFORMS_PATH . 'includes/class-admin.php';
require_once SOBIFORMS_PATH . 'includes/class-block.php';

/**
 * Main plugin bootstrap.
 */
final class Sobiforms_Plugin {

	/** @var Sobiforms_Plugin|null */
	private static $instance = null;

	/**
	 * @return Sobiforms_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		register_activation_hook( SOBIFORMS_FILE, array( 'Sobiforms_DB', 'activate' ) );
		add_action( 'plugins_loaded', array( 'Sobiforms_DB', 'maybe_upgrade' ) );
		add_action( 'plugins_loaded', array( 'Sobiforms_File_Upload', 'maybe_harden_upload_directory' ), 20 );
		add_action( 'init', array( $this, 'register_shortcode' ) );

		Sobiforms_Akismet::init();
		Sobiforms_Form_Renderer::register_assets();
		Sobiforms_Form_Handler::init();
		Sobiforms_Submission_Store::init();
		Sobiforms_Admin::init();
		Sobiforms_Forms_Admin::init();
		Sobiforms_Block::init();
	}

	public function register_shortcode() {
		add_shortcode( 'sobiforms', array( 'Sobiforms_Form_Renderer', 'shortcode' ) );
	}
}

Sobiforms_Plugin::instance();
