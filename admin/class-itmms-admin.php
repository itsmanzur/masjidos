<?php
/**
 * Admin UI bootstrap: menu registration, fullscreen mode, asset loading.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin bootstrap class.
 */
final class ITMMS_Admin {

	/** @var ITMMS_Admin|null */
	private static ?ITMMS_Admin $instance = null;

	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {}
	private function __clone() {}

	/**
	 * Wire hooks into WordPress.
	 */
	public function init(): void {
		add_action( 'admin_menu', [ $this, 'register_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_filter( 'admin_body_class', [ $this, 'add_fullscreen_class' ] );
		add_action( 'admin_bar_menu', [ $this, 'add_adminbar_exit_link' ], 999 );
	}

	/**
	 * Register top-level admin menu.
	 */
	public function register_menu(): void {
		$capability = 'manage_options';
		$caps       = [
			'itmms_manage_prayers',
			'itmms_manage_events',
			'itmms_manage_announcements',
			'itmms_view_reports',
			'itmms_manage_settings',
		];

		foreach ( $caps as $cap ) {
			if ( current_user_can( $cap ) ) {
				$capability = $cap;
				break;
			}
		}

		add_menu_page(
			__( 'MasjidOS', 'masjidos' ),
			__( 'MasjidOS', 'masjidos' ),
			$capability,
			'masjidos',
			[ $this, 'render_app' ],
			'dashicons-building',
			80
		);
	}

	/**
	 * Output the SPA mount point.
	 */
	public function render_app(): void {
		echo '<div id="itmms-app"></div>';
	}

	/**
	 * Enqueue styles and scripts only on MasjidOS admin pages.
	 *
	 * @param string $hook Current admin page hook suffix.
	 */
	public function enqueue_assets( string $hook ): void {
		if ( ! $this->is_itmms_page( $hook ) ) {
			return;
		}

		wp_enqueue_media();

		wp_enqueue_style(
			'itmms-admin',
			ITMMS_PLUGIN_URL . 'admin/assets/css/admin.css',
			[],
			ITMMS_VERSION
		);

		wp_enqueue_style(
			'itmms-public',
			ITMMS_PLUGIN_URL . 'public/assets/css/public.css',
			[],
			ITMMS_VERSION
		);

		wp_register_script(
			'itmms-admin-shared',
			ITMMS_PLUGIN_URL . 'admin/assets/js/modules/shared.js',
			[ 'wp-i18n' ],
			ITMMS_VERSION,
			true
		);

		wp_register_script(
			'itmms-admin-dashboard',
			ITMMS_PLUGIN_URL . 'admin/assets/js/modules/dashboard.js',
			[ 'itmms-admin-shared' ],
			ITMMS_VERSION,
			true
		);

		wp_register_script(
			'itmms-admin-settings',
			ITMMS_PLUGIN_URL . 'admin/assets/js/modules/settings.js',
			[ 'itmms-admin-shared' ],
			ITMMS_VERSION,
			true
		);

		wp_register_script(
			'itmms-admin-announcements',
			ITMMS_PLUGIN_URL . 'admin/assets/js/modules/announcements.js',
			[ 'itmms-admin-shared' ],
			ITMMS_VERSION,
			true
		);

		wp_register_script(
			'itmms-admin-docs',
			ITMMS_PLUGIN_URL . 'admin/assets/js/modules/docs.js',
			[ 'itmms-admin-shared' ],
			ITMMS_VERSION,
			true
		);

		wp_register_script(
			'itmms-admin-events',
			ITMMS_PLUGIN_URL . 'admin/assets/js/modules/events.js',
			[ 'itmms-admin-shared' ],
			ITMMS_VERSION,
			true
		);

		wp_register_script(
			'itmms-admin-features',
			ITMMS_PLUGIN_URL . 'admin/assets/js/modules/features.js',
			[ 'itmms-admin-shared' ],
			ITMMS_VERSION,
			true
		);

		wp_enqueue_script(
			'itmms-public',
			ITMMS_PLUGIN_URL . 'public/assets/js/public.js',
			[],
			ITMMS_VERSION,
			true
		);

		wp_enqueue_script(
			'itmms-admin',
			ITMMS_PLUGIN_URL . 'admin/assets/js/app.js',
			[
				'itmms-admin-shared',
				'itmms-admin-dashboard',
				'itmms-admin-settings',
				'itmms-admin-announcements',
				'itmms-admin-docs',
				'itmms-admin-events',
				'itmms-admin-features',
			],
			ITMMS_VERSION,
			true
		);

		wp_set_script_translations(
			'itmms-admin',
			'masjidos',
			ITMMS_PLUGIN_DIR . 'languages'
		);

		wp_localize_script(
			'itmms-admin-shared',
			'itmmData',
			[
				'restUrl'      => rest_url( 'masjidos/v1/' ),
				'nonce'        => wp_create_nonce( 'wp_rest' ),
				'version'      => ITMMS_VERSION,
				'locale'       => determine_locale(),
				'siteUrl'      => get_site_url(),
				'adminUrl'     => admin_url(),
				'siteTimezone' => wp_timezone_string(),
				'settings'     => ITMMS_Settings::get_all(),
				'modules'      => ITMMS_Settings::module_definitions(),
				'user'         => [
					'id'   => get_current_user_id(),
					'name' => wp_get_current_user()->display_name,
				],
			]
		);
	}

	/**
	 * Append fullscreen class to MasjidOS admin pages.
	 *
	 * @param string $classes Space-separated body class string.
	 * @return string
	 */
	public function add_fullscreen_class( string $classes ): string {
		if ( $this->is_itmms_page() ) {
			$classes .= ' itmms-fullscreen';
		}

		return $classes;
	}

	/**
	 * Add a WordPress escape link to the WP admin bar.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 */
	public function add_adminbar_exit_link( WP_Admin_Bar $wp_admin_bar ): void {
		if ( ! $this->is_itmms_page() ) {
			return;
		}

		$wp_admin_bar->add_node(
			[
				'id'    => 'itmms-exit',
				'title' => '&larr; ' . __( 'WordPress Dashboard', 'masjidos' ),
				'href'  => admin_url(),
				'meta'  => [ 'class' => 'itmms-exit-link' ],
			]
		);
	}

	/**
	 * Check whether the current request is a MasjidOS admin page.
	 *
	 * @param string $hook Optional hook suffix.
	 */
	private function is_itmms_page( string $hook = '' ): bool {
		if ( $hook && false !== strpos( $hook, 'masjidos' ) ) {
			return true;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		return 0 === strpos( $page, 'masjidos' );
	}
}
