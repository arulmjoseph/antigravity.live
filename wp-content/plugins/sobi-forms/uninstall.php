<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package SobiForms
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

require_once plugin_dir_path( __FILE__ ) . 'includes/class-db.php';

Sobiforms_DB::drop_tables();

delete_option( 'sobiforms_db_version' );
delete_option( 'sobiforms_save_submissions' );
delete_option( 'sobiforms_retention_days' );
