<?php
/**
 * Plugin Name: MasjidOS
 * Description: Prayer times, Jumuah schedules, monthly timetables, Qibla, and mosque notices for WordPress.
 * Version:     1.0.0
 * Author:      MasjidOS Team
 * Author URI:  https://profiles.wordpress.org/itsmanzur/
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: masjidos
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

define( 'ITMMS_VERSION', '1.0.0' );
define( 'ITMMS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'ITMMS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'ITMMS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
// Internal database/capability schema version. This does not need to match the public plugin version.
define( 'ITMMS_DB_VERSION', '1.1' );

// Load classes immediately because activation hooks run early.
require_once ITMMS_PLUGIN_DIR . 'includes/class-itmms-roles.php';
require_once ITMMS_PLUGIN_DIR . 'includes/class-itmms-installer.php';
require_once ITMMS_PLUGIN_DIR . 'includes/class-itmms-settings.php';
require_once ITMMS_PLUGIN_DIR . 'includes/class-itmms-prayer-times.php';
require_once ITMMS_PLUGIN_DIR . 'includes/class-itmms-announcements.php';
require_once ITMMS_PLUGIN_DIR . 'includes/class-itmms-events.php';
require_once ITMMS_PLUGIN_DIR . 'includes/class-itmms-rest.php';
require_once ITMMS_PLUGIN_DIR . 'includes/class-itmms-core.php';
require_once ITMMS_PLUGIN_DIR . 'admin/class-itmms-admin.php';
require_once ITMMS_PLUGIN_DIR . 'public/class-itmms-public.php';

register_activation_hook( __FILE__, [ 'ITMMS_Installer', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'ITMMS_Installer', 'deactivate' ] );

add_action(
	'init',
	static function (): void {
		$locale = determine_locale();
		if ( ! preg_match( '/^[A-Za-z0-9_@.-]+$/D', $locale ) ) {
			return;
		}

		$wporg_file = WP_LANG_DIR . '/plugins/masjidos-' . $locale . '.mo';
		$bundled_file = ITMMS_PLUGIN_DIR . 'languages/masjidos-' . $locale . '.mo';
		if ( ! file_exists( $wporg_file ) && file_exists( $bundled_file ) ) {
			load_textdomain( 'masjidos', $bundled_file );
		}
	}
);

add_action(
	'plugins_loaded',
	static function (): void {
		ITMMS_Core::get_instance();
	}
);
