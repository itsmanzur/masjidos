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

		update_option( 'itmms_db_version', ITMMS_DB_VERSION, false );
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

		if ( version_compare( get_bloginfo( 'version' ), '6.0', '<' ) ) {
			$errors[] = __( 'MasjidOS requires WordPress 6.0 or higher.', 'masjidos' );
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
			created_by          BIGINT(20) UNSIGNED NOT NULL,
			created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY start_time (start_time)
		) $charset;" );
	}
}
