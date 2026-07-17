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
		// Existing installs: show Welcome once until dismissed (does not overwrite 0).
		add_option( 'itmms_show_welcome', 1, '', false );

		add_action( 'admin_menu', [ $this, 'register_menu' ] );
		add_action( 'admin_menu', [ $this, 'reorder_submenu' ], 999 );
		add_action( 'admin_enqueue_scripts', [ $this, 'maybe_switch_ui_locale' ], 1 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_filter( 'admin_body_class', [ $this, 'add_fullscreen_class' ] );
		add_action( 'admin_bar_menu', [ $this, 'add_adminbar_exit_link' ], 999 );
	}

	/**
	 * Switch WordPress locale to the plugin UI language on MasjidOS pages
	 * so PHP strings and wp_set_script_translations use the same packs.
	 */
	public function maybe_switch_ui_locale(): void {
		if ( ! $this->is_itmms_page() ) {
			return;
		}

		$locale = ITMMS_Settings::ui_locale();
		if ( ! preg_match( '/^[A-Za-z0-9_@.-]+$/D', $locale ) ) {
			return;
		}

		if ( function_exists( 'switch_to_locale' ) ) {
			switch_to_locale( $locale );
		}

		unload_textdomain( 'masjidos' );
		if ( in_array( $locale, [ 'en_US', 'en' ], true ) ) {
			return;
		}

		$wporg_file   = WP_LANG_DIR . '/plugins/masjidos-' . $locale . '.mo';
		$bundled_file = ITMMS_PLUGIN_DIR . 'languages/masjidos-' . $locale . '.mo';
		if ( file_exists( $wporg_file ) ) {
			load_textdomain( 'masjidos', $wporg_file );
		} elseif ( file_exists( $bundled_file ) ) {
			load_textdomain( 'masjidos', $bundled_file );
		}
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

		add_submenu_page(
			'masjidos',
			__( 'Dashboard', 'masjidos' ),
			__( 'Dashboard', 'masjidos' ),
			$capability,
			'masjidos',
			[ $this, 'render_app' ]
		);

		if ( post_type_exists( ITMMS_Duas_Library::POST_TYPE ) ) {
			add_submenu_page(
				'masjidos',
				__( 'Duas Library', 'masjidos' ),
				__( 'Duas Library', 'masjidos' ),
				'edit_posts',
				'edit.php?post_type=' . ITMMS_Duas_Library::POST_TYPE
			);
		}
	}

	/**
	 * Keep Dashboard first, then All Articles, then the rest.
	 */
	public function reorder_submenu(): void {
		global $submenu;

		if ( empty( $submenu['masjidos'] ) || ! is_array( $submenu['masjidos'] ) ) {
			return;
		}

		$dashboard = null;
		$articles  = null;
		$rest      = [];

		foreach ( $submenu['masjidos'] as $item ) {
			$slug = isset( $item[2] ) ? (string) $item[2] : '';

			if ( 'masjidos' === $slug ) {
				$dashboard = $item;
				continue;
			}

			if ( false !== strpos( $slug, 'post_type=itmms_article' ) ) {
				$articles = $item;
				continue;
			}

			$rest[] = $item;
		}

		$ordered = [];
		if ( null !== $dashboard ) {
			$ordered[] = $dashboard;
		}
		if ( null !== $articles ) {
			$ordered[] = $articles;
		}

		$submenu['masjidos'] = array_merge( $ordered, $rest );
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
			'itmms-fonts',
			ITMMS_PLUGIN_URL . 'public/assets/css/tv-fonts.css',
			[],
			ITMMS_VERSION
		);

		wp_enqueue_style(
			'itmms-admin',
			ITMMS_PLUGIN_URL . 'admin/assets/css/admin.css',
			[ 'itmms-fonts' ],
			ITMMS_VERSION
		);

		wp_enqueue_style(
			'itmms-public',
			ITMMS_PLUGIN_URL . 'public/assets/css/public.css',
			[ 'itmms-fonts' ],
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
			'itmms-admin-welcome',
			ITMMS_PLUGIN_URL . 'admin/assets/js/modules/welcome.js',
			[ 'itmms-admin-shared', 'itmms-admin-dashboard' ],
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
			'itmms-admin-minbar',
			ITMMS_PLUGIN_URL . 'admin/assets/js/modules/minbar.js',
			[ 'itmms-admin-shared', 'wp-i18n' ],
			ITMMS_VERSION,
			true
		);

		wp_register_script(
			'itmms-admin-khutbah',
			ITMMS_PLUGIN_URL . 'admin/assets/js/modules/khutbah.js',
			[ 'itmms-admin-minbar' ],
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
				'itmms-admin-welcome',
				'itmms-admin-settings',
				'itmms-admin-announcements',
				'itmms-admin-docs',
				'itmms-admin-events',
				'itmms-admin-minbar',
				'itmms-admin-khutbah',
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

		$this->inject_ui_script_translations( 'itmms-admin' );

		wp_localize_script(
			'itmms-admin-shared',
			'itmmData',
			[
				'restUrl'      => rest_url( 'masjidos/v1/' ),
				'nonce'        => wp_create_nonce( 'wp_rest' ),
				'version'      => ITMMS_VERSION,
				'locale'       => ITMMS_Settings::ui_locale(),
				'uiLanguage'   => ITMMS_Settings::ui_language(),
				'uiLocale'     => ITMMS_Settings::ui_locale(),
				'uiIsRtl'      => ITMMS_Settings::ui_is_rtl(),
				'languagesUrl' => ITMMS_PLUGIN_URL . 'languages/',
				'i18nRev'      => (string) max(
					(int) @filemtime( ITMMS_PLUGIN_DIR . 'languages/masjidos-bn_BD-itmms-admin.json' ),
					(int) @filemtime( ITMMS_PLUGIN_DIR . 'languages/masjidos-ar-itmms-admin.json' )
				),
				'siteUrl'      => get_site_url(),
				'adminUrl'     => admin_url(),
				'siteTimezone' => wp_timezone_string(),
				'settings'     => ITMMS_Settings::get_all(),
				'modules'      => ITMMS_Settings::module_definitions(),
				'timetable'    => ITMMS_Prayer_Timetable::summary(),
				'showWelcome'  => (int) get_option( 'itmms_show_welcome', 0 ) === 1,
				'user'         => [
					'id'   => get_current_user_id(),
					'name' => wp_get_current_user()->display_name,
				],
			]
		);
	}

	/**
	 * Force-load Jed locale data for the plugin UI language.
	 * Ensures translations work even when WordPress does not have that locale installed.
	 *
	 * @param string $handle Script handle (file suffix: masjidos-{locale}-{handle}.json).
	 */
	private function inject_ui_script_translations( string $handle ): void {
		$locale = ITMMS_Settings::ui_locale();
		if ( in_array( $locale, [ 'en_US', 'en' ], true ) ) {
			return;
		}

		$path = ITMMS_PLUGIN_DIR . 'languages/masjidos-' . $locale . '-' . $handle . '.json';
		if ( ! is_readable( $path ) ) {
			return;
		}

		$decoded = json_decode( (string) file_get_contents( $path ), true );
		if ( empty( $decoded['locale_data']['messages'] ) || ! is_array( $decoded['locale_data']['messages'] ) ) {
			return;
		}

		wp_add_inline_script(
			'wp-i18n',
			sprintf(
				'wp.i18n.setLocaleData( %s, %s );',
				wp_json_encode( $decoded['locale_data']['messages'] ),
				wp_json_encode( 'masjidos' )
			),
			'after'
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
			$ui = ITMMS_Settings::ui_language();
			$classes .= ' itmms-ui-' . $ui;
			if ( 'ar' === $ui ) {
				$classes .= ' itmms-ui-rtl rtl';
			}
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
