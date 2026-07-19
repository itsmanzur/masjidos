<?php
/**
 * Handles plugin activation, deactivation, and database setup.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ITMMS_Installer
 */
class ITMMS_Installer {

	/**
	 * Run on plugin activation.
	 */
	public static function activate(): void {
		self::check_requirements();
		self::create_tables();
		ITMMS_Roles::setup_roles();
		ITMMS_Settings::install_defaults();
		ITMMS_Education::install_defaults();

		update_option( 'itmms_db_version', ITMMS_DB_VERSION, false );
		update_option( 'itmms_show_welcome', 1, false );
		flush_rewrite_rules();
	}

	/**
	 * Apply future schema and capability updates after a plugin update.
	 */
	public static function maybe_upgrade(): void {
		$installed = (string) get_option( 'itmms_db_version', '0' );
		if ( version_compare( $installed, ITMMS_DB_VERSION, '>=' ) ) {
			return;
		}

		self::create_tables();
		ITMMS_Roles::setup_roles();
		ITMMS_Settings::install_defaults();
		update_option( 'itmms_db_version', ITMMS_DB_VERSION, false );
		// Existing installs see Welcome once after this update lands.
		add_option( 'itmms_show_welcome', 1, '', false );
		add_action( 'init', [ self::class, 'complete_upgrade' ], 20 );
	}

	/**
	 * Finish upgrade tasks that require WordPress rewrite/taxonomy setup.
	 */
	public static function complete_upgrade(): void {
		ITMMS_Education::install_defaults();
		flush_rewrite_rules();
	}

	/**
	 * Run on plugin deactivation.
	 * Data is intentionally preserved.
	 */
	public static function deactivate(): void {
		flush_rewrite_rules();
	}

	/**
	 * Verify minimum WordPress and PHP versions.
	 * Deactivates the plugin and shows an error if requirements are not met.
	 */
	private static function check_requirements(): void {
		$errors = [];

		if ( version_compare( get_bloginfo( 'version' ), '6.2', '<' ) ) {
			$errors[] = __( 'MasjidOS requires WordPress 6.2 or higher.', 'masjidos' );
		}

		if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
			$errors[] = __( 'MasjidOS requires PHP 7.4 or higher.', 'masjidos' );
		}

		if ( ! empty( $errors ) ) {
			deactivate_plugins( ITMMS_PLUGIN_BASENAME );
			wp_die(
				'<p>' . implode( '</p><p>', array_map( 'esc_html', $errors ) ) . '</p>',
				esc_html__( 'MasjidOS - Activation Error', 'masjidos' ),
				[ 'back_link' => true ]
			);
		}
	}

	/**
	 * Create the custom database tables used by the current release.
	 */
	public static function create_tables(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset = $wpdb->get_charset_collate();

		dbDelta( "CREATE TABLE {$wpdb->prefix}itmms_announcements (
			id                  BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			title               VARCHAR(255) NOT NULL,
			content             TEXT NULL DEFAULT NULL,
			announcement_type   VARCHAR(50) NOT NULL DEFAULT 'general',
			priority            INT NOT NULL DEFAULT 0,
			start_date          DATETIME NOT NULL,
			end_date            DATETIME NULL DEFAULT NULL,
			is_active           TINYINT(1) NOT NULL DEFAULT 1,
			created_by          BIGINT(20) UNSIGNED NOT NULL,
			created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY is_active (is_active),
			KEY start_date (start_date)
		) $charset;" );

		dbDelta( "CREATE TABLE {$wpdb->prefix}itmms_events (
			id                  BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			title               VARCHAR(255) NOT NULL,
			description         TEXT NULL DEFAULT NULL,
			start_time          DATETIME NOT NULL,
			end_time            DATETIME NULL DEFAULT NULL,
			location            VARCHAR(255) NULL DEFAULT NULL,
			image_url           VARCHAR(255) NULL DEFAULT NULL,
			created_by          BIGINT(20) UNSIGNED NOT NULL,
			created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY start_time (start_time)
		) $charset;" );

		dbDelta( "CREATE TABLE {$wpdb->prefix}itmms_khutbah_archive (
			id                  BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			date                DATE NOT NULL,
			topic               VARCHAR(255) NOT NULL,
			khatib              VARCHAR(255) NOT NULL,
			language            VARCHAR(100) NULL DEFAULT NULL,
			summary             TEXT NULL DEFAULT NULL,
			audio_url           VARCHAR(255) NULL DEFAULT NULL,
			category            VARCHAR(100) NULL DEFAULT NULL,
			tags                VARCHAR(255) NULL DEFAULT NULL,
			quran_refs          LONGTEXT NULL DEFAULT NULL,
			hadith_refs         LONGTEXT NULL DEFAULT NULL,
			doc_url             VARCHAR(255) NULL DEFAULT NULL,
			is_public           TINYINT(1) NOT NULL DEFAULT 1,
			duration_minutes    SMALLINT UNSIGNED NULL DEFAULT NULL,
			outline             LONGTEXT NULL DEFAULT NULL,
			PRIMARY KEY  (id),
			KEY date (date),
			KEY category (category),
			KEY is_public (is_public)
		) $charset;" );

		dbDelta( "CREATE TABLE {$wpdb->prefix}itmms_khatib_profiles (
			id                  BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id             BIGINT(20) UNSIGNED NULL DEFAULT NULL,
			name                VARCHAR(255) NOT NULL,
			title               VARCHAR(255) NULL DEFAULT NULL,
			phone               VARCHAR(50) NULL DEFAULT NULL,
			email               VARCHAR(190) NULL DEFAULT NULL,
			photo_url           VARCHAR(255) NULL DEFAULT NULL,
			expertise           VARCHAR(255) NULL DEFAULT NULL,
			languages           VARCHAR(255) NULL DEFAULT NULL,
			location            VARCHAR(255) NULL DEFAULT NULL,
			website             VARCHAR(255) NULL DEFAULT NULL,
			facebook_url        VARCHAR(255) NULL DEFAULT NULL,
			youtube_url         VARCHAR(255) NULL DEFAULT NULL,
			instagram_url       VARCHAR(255) NULL DEFAULT NULL,
			linkedin_url        VARCHAR(255) NULL DEFAULT NULL,
			x_url               VARCHAR(255) NULL DEFAULT NULL,
			tiktok_url          VARCHAR(255) NULL DEFAULT NULL,
			bio                 TEXT NULL DEFAULT NULL,
			is_active           TINYINT(1) NOT NULL DEFAULT 1,
			created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY is_active (is_active),
			KEY user_id (user_id)
		) $charset;" );

		dbDelta( "CREATE TABLE {$wpdb->prefix}itmms_khatib_schedule (
			id                  BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			khatib_id           BIGINT(20) UNSIGNED NOT NULL,
			scheduled_date      DATE NOT NULL,
			type                VARCHAR(50) NOT NULL DEFAULT 'jumuah',
			topic               VARCHAR(255) NULL DEFAULT NULL,
			status              VARCHAR(30) NOT NULL DEFAULT 'confirmed',
			notes               TEXT NULL DEFAULT NULL,
			created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY scheduled_date (scheduled_date),
			KEY khatib_id (khatib_id),
			KEY status (status)
		) $charset;" );
	}
}
