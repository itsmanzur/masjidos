<?php
/**
 * Plugin Name: MasjidOS
 * Description: Prayer times, Jumuah & Minbar, TV display, Duas, Quran, Hadith, articles, events, and notices for mosques.
 * Version:     1.2.1
 * Author:      MasjidOS Team
 * Author URI:  https://profiles.wordpress.org/itsmanzur/
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: masjidos
 * Domain Path: /languages
 * Requires at least: 6.2
 * Requires PHP: 7.4
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

define( 'ITMMS_VERSION', '1.2.1' );
define( 'ITMMS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ITMMS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ITMMS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
// Internal database/capability schema version. This does not need to match the public plugin version.
define( 'ITMMS_DB_VERSION', '1.6' );

// Load classes immediately because activation hooks run early.
require_once ITMMS_PLUGIN_DIR . 'includes/class-itmms-roles.php';
require_once ITMMS_PLUGIN_DIR . 'includes/class-itmms-installer.php';
require_once ITMMS_PLUGIN_DIR . 'includes/class-itmms-iqamah-rules.php';
require_once ITMMS_PLUGIN_DIR . 'includes/class-itmms-settings.php';
require_once ITMMS_PLUGIN_DIR . 'includes/class-itmms-pro-bridge.php';
require_once ITMMS_PLUGIN_DIR . 'includes/class-itmms-hijri.php';
require_once ITMMS_PLUGIN_DIR . 'includes/class-itmms-prayer-times.php';
require_once ITMMS_PLUGIN_DIR . 'includes/class-itmms-prayer-timetable.php';
require_once ITMMS_PLUGIN_DIR . 'includes/class-itmms-salahapi.php';
require_once ITMMS_PLUGIN_DIR . 'includes/class-itmms-duas-azkar.php';
require_once ITMMS_PLUGIN_DIR . 'includes/class-itmms-duas-library.php';
require_once ITMMS_PLUGIN_DIR . 'includes/class-itmms-announcements.php';
require_once ITMMS_PLUGIN_DIR . 'includes/class-itmms-events.php';
require_once ITMMS_PLUGIN_DIR . 'includes/class-itmms-khutbah.php';
require_once ITMMS_PLUGIN_DIR . 'includes/class-itmms-minbar.php';
require_once ITMMS_PLUGIN_DIR . 'includes/class-itmms-education.php';
require_once ITMMS_PLUGIN_DIR . 'includes/rest/trait-itmms-rest-permissions.php';
require_once ITMMS_PLUGIN_DIR . 'includes/rest/trait-itmms-rest-dashboard.php';
require_once ITMMS_PLUGIN_DIR . 'includes/rest/trait-itmms-rest-prayer.php';
require_once ITMMS_PLUGIN_DIR . 'includes/rest/trait-itmms-rest-widgets.php';
require_once ITMMS_PLUGIN_DIR . 'includes/rest/trait-itmms-rest-content.php';
require_once ITMMS_PLUGIN_DIR . 'includes/rest/trait-itmms-rest-minbar.php';
require_once ITMMS_PLUGIN_DIR . 'includes/class-itmms-rest.php';
require_once ITMMS_PLUGIN_DIR . 'includes/class-itmms-core.php';
require_once ITMMS_PLUGIN_DIR . 'admin/class-itmms-admin.php';
require_once ITMMS_PLUGIN_DIR . 'public/trait-itmms-public-helpers.php';
require_once ITMMS_PLUGIN_DIR . 'public/trait-itmms-public-designs.php';
require_once ITMMS_PLUGIN_DIR . 'public/trait-itmms-public-blocks.php';
require_once ITMMS_PLUGIN_DIR . 'public/trait-itmms-public-display.php';
require_once ITMMS_PLUGIN_DIR . 'public/class-itmms-public.php';

register_activation_hook( __FILE__, [ 'ITMMS_Installer', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'ITMMS_Installer', 'deactivate' ] );
add_action( 'init', [ 'ITMMS_Education', 'register_post_type' ] );

add_filter(
	'plugin_locale',
	static function ( string $locale, string $domain ): string {
		if ( 'masjidos' !== $domain ) {
			return $locale;
		}

		$ui_locale = ITMMS_Settings::ui_locale();
		return preg_match( '/^[A-Za-z0-9_@.-]+$/D', $ui_locale ) ? $ui_locale : $locale;
	},
	10,
	2
);

add_action(
	'init',
	static function (): void {
		$locale = ITMMS_Settings::ui_locale();
		if ( ! preg_match( '/^[A-Za-z0-9_@.-]+$/D', $locale ) ) {
			return;
		}

		// English uses source strings; no pack required.
		if ( 'en_US' === $locale || 'en' === $locale ) {
			return;
		}

		$wporg_file   = WP_LANG_DIR . '/plugins/masjidos-' . $locale . '.mo';
		$bundled_file = ITMMS_PLUGIN_DIR . 'languages/masjidos-' . $locale . '.mo';
		if ( ! file_exists( $wporg_file ) && file_exists( $bundled_file ) ) {
			load_textdomain( 'masjidos', $bundled_file );
		} elseif ( file_exists( $wporg_file ) ) {
			load_textdomain( 'masjidos', $wporg_file );
		}
	},
	1
);

add_action(
	'plugins_loaded',
	static function (): void {
		ITMMS_Core::get_instance();
	}
);
