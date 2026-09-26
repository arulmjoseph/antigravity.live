<?php
/**
 * Database schema and migrations.
 *
 * @package SobiForms
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles custom tables and schema upgrades.
 */
class Sobiforms_DB {

	const DB_VERSION     = '1.0.8';
	const TABLE          = 'sobiforms_submissions';
	const FORMS_TABLE    = 'sobiforms_forms';
	const VERSION_OPTION = 'sobiforms_db_version';

	/**
	 * Run on plugin activation.
	 */
	public static function activate() {
		self::create_tables();
		self::register_capabilities();
		Sobiforms_File_Upload::maybe_harden_upload_directory();
		update_option( self::VERSION_OPTION, self::DB_VERSION );
	}

	/**
	 * Grant plugin capabilities to default roles.
	 */
	public static function register_capabilities() {
		$admin = get_role( 'administrator' );
		if ( $admin && ! $admin->has_cap( 'sobiforms_manage_submissions' ) ) {
			$admin->add_cap( 'sobiforms_manage_submissions' );
		}
	}

	/**
	 * Check for schema upgrades on load.
	 */
	public static function maybe_upgrade() {
		$installed = get_option( self::VERSION_OPTION, '0' );
		if ( version_compare( $installed, self::DB_VERSION, '>=' ) ) {
			return;
		}

		self::create_tables();
		self::register_capabilities();

		if ( version_compare( $installed, '1.0.2', '<' ) ) {
			self::migrate_to_v102();
		}

		if ( version_compare( $installed, '1.0.6', '<' ) ) {
			self::migrate_to_v106();
		}

		update_option( self::VERSION_OPTION, self::DB_VERSION );
	}

	/**
	 * Submissions table name.
	 *
	 * @return string
	 */
	public static function table_name() {
		global $wpdb;
		return $wpdb->prefix . self::TABLE;
	}

	/**
	 * Forms table name.
	 *
	 * @return string
	 */
	public static function forms_table_name() {
		global $wpdb;
		return $wpdb->prefix . self::FORMS_TABLE;
	}

	/**
	 * Create or update all tables via dbDelta.
	 */
	public static function create_tables() {
		global $wpdb;

		$charset = $wpdb->get_charset_collate();

		$forms = self::forms_table_name();
		$sql1  = "CREATE TABLE {$forms} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			title varchar(255) NOT NULL DEFAULT '',
			slug varchar(255) NOT NULL DEFAULT '',
			recipient_email varchar(255) NOT NULL DEFAULT '',
			success_message varchar(500) NOT NULL DEFAULT '',
			error_message varchar(500) NOT NULL DEFAULT '',
			success_action varchar(20) NOT NULL DEFAULT 'message',
			redirect_page_id bigint(20) unsigned NOT NULL DEFAULT 0,
			fields longtext NOT NULL,
			submit_button_text varchar(100) NOT NULL DEFAULT '',
			save_submissions tinyint(1) NOT NULL DEFAULT 0,
			retention_days int unsigned NOT NULL DEFAULT 90,
			paused tinyint(1) NOT NULL DEFAULT 0,
			close_enabled tinyint(1) NOT NULL DEFAULT 0,
			close_at datetime DEFAULT NULL,
			close_limit_enabled tinyint(1) NOT NULL DEFAULT 0,
			close_limit int unsigned DEFAULT NULL,
			submission_count int unsigned NOT NULL DEFAULT 0,
			unavailable_message varchar(500) NOT NULL DEFAULT '',
			send_confirmation_email tinyint(1) NOT NULL DEFAULT 0,
			confirmation_email_field_id varchar(64) NOT NULL DEFAULT '',
			is_default tinyint(1) NOT NULL DEFAULT 0,
			created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			updated_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			UNIQUE KEY slug (slug),
			KEY is_default (is_default)
		) {$charset};";

		$subs = self::table_name();
		$sql2 = "CREATE TABLE {$subs} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			form_id bigint(20) unsigned NOT NULL DEFAULT 0,
			name varchar(255) NOT NULL DEFAULT '',
			email varchar(255) NOT NULL DEFAULT '',
			message text NOT NULL,
			data longtext NOT NULL,
			mail_status varchar(20) NOT NULL DEFAULT 'sent',
			is_read tinyint(1) NOT NULL DEFAULT 0,
			is_starred tinyint(1) NOT NULL DEFAULT 0,
			admin_note text NOT NULL,
			ip_hash varchar(64) NOT NULL DEFAULT '',
			spam_status varchar(10) NOT NULL DEFAULT 'ham',
			source_title varchar(255) NOT NULL DEFAULT '',
			source_path varchar(2048) NOT NULL DEFAULT '',
			created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY form_id (form_id),
			KEY mail_status (mail_status),
			KEY is_read (is_read),
			KEY is_starred (is_starred),
			KEY spam_status (spam_status),
			KEY created_at (created_at),
			KEY inbox_list (spam_status, created_at),
			KEY inbox_form (form_id, spam_status, created_at),
			KEY inbox_unread (spam_status, is_read, form_id),
			KEY purge (form_id, created_at)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql1 );
		dbDelta( $sql2 );
	}

	/**
	 * Move global save/retention options to per-form columns (1.0.2).
	 */
	private static function migrate_to_v102() {
		global $wpdb;

		$forms_table      = self::forms_table_name();
		$global_save      = (bool) get_option( 'sobiforms_save_submissions', 0 );
		$global_retention = (int) get_option( 'sobiforms_retention_days', 90 );

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- One-time migration; table name from Sobiforms_DB only.
		if ( $global_save ) {
			$wpdb->query( "UPDATE {$forms_table} SET save_submissions = 1" );
		}

		if ( $global_retention > 0 ) {
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$forms_table} SET retention_days = %d",
					$global_retention
				)
			);
		}
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter

		delete_option( 'sobiforms_save_submissions' );
		delete_option( 'sobiforms_retention_days' );
	}

	/**
	 * Backfill submission_count for forms that save to the database (1.0.6).
	 */
	private static function migrate_to_v106() {
		global $wpdb;

		$forms_table = self::forms_table_name();
		$subs_table  = self::table_name();

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- One-time migration; table names from Sobiforms_DB only.
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$forms_table} f
				SET submission_count = COALESCE(
					( SELECT COUNT(*) FROM {$subs_table} s WHERE s.form_id = f.id AND s.spam_status != %s ),
					0
				)
				WHERE f.save_submissions = 1",
				'spam'
			)
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
	}

	/**
	 * Drop all plugin tables on uninstall.
	 */
	public static function drop_tables() {
		global $wpdb;
		$forms_table = self::forms_table_name();
		$table       = self::table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Plugin uninstall; table names are internal constants.
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $forms_table );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Plugin uninstall; table names are internal constants.
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $table );
	}
}
