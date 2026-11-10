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
		add_action( 'wp_dashboard_setup', [ $this, 'register_wp_dashboard_widget' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'maybe_switch_ui_locale' ], 1 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_filter( 'admin_body_class', [ $this, 'add_fullscreen_class' ] );
		add_action( 'admin_bar_menu', [ $this, 'add_adminbar_exit_link' ], 999 );
		add_action( 'admin_bar_menu', [ $this, 'add_adminbar_quick_menu' ], 85 );
		add_action( 'admin_post_masjidos_create_starter_pages', [ $this, 'handle_create_starter_pages' ] );
		add_action( 'admin_notices', [ $this, 'render_starter_pages_notice' ] );
	}

	/**
	 * Switch WordPress locale to the plugin UI language on MasjidOS (Free & Pro) pages
	 * so PHP strings and wp_set_script_translations use the same packs.
	 */
	public function maybe_switch_ui_locale(): void {
		if ( ! $this->is_itmms_page() && ! $this->is_masjidos_pro_page() ) {
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
		if ( defined( 'MASJIDOS_PRO_DIR' ) ) {
			unload_textdomain( 'masjidos-pro' );
		}
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

		if ( defined( 'MASJIDOS_PRO_DIR' ) ) {
			$pro_wporg   = WP_LANG_DIR . '/plugins/masjidos-pro-' . $locale . '.mo';
			$pro_bundled = MASJIDOS_PRO_DIR . 'languages/masjidos-pro-' . $locale . '.mo';
			if ( file_exists( $pro_wporg ) ) {
				load_textdomain( 'masjidos-pro', $pro_wporg );
			} elseif ( file_exists( $pro_bundled ) ) {
				load_textdomain( 'masjidos-pro', $pro_bundled );
			}
		}
	}

	/**
	 * Compute pending action counts across Free & Pro modules.
	 *
	 * @return array{total:int,ask_imam:int,donations:int,bookings:int,enrollments:int}
	 */
	public static function get_pending_counts(): array {
		$ask_imam = 0;
		if ( class_exists( 'ITMMS_Ask_Imam', false ) && post_type_exists( ITMMS_Ask_Imam::POST_TYPE ) ) {
			$counts   = wp_count_posts( ITMMS_Ask_Imam::POST_TYPE );
			$ask_imam = isset( $counts->pending ) ? (int) $counts->pending : 0;
		}

		$donations   = 0;
		$bookings    = 0;
		$enrollments = 0;

		if ( function_exists( 'masjidos_pro_is_active' ) && masjidos_pro_is_active() ) {
			if ( class_exists( 'MasjidOS_Pro_Donations', false ) ) {
				$don_sum   = MasjidOS_Pro_Donations::summary();
				$donations = (int) ( $don_sum['pending'] ?? 0 );
			}
			if ( class_exists( 'MasjidOS_Pro_Facilities', false ) ) {
				$fac_sum  = MasjidOS_Pro_Facilities::summary();
				$bookings = (int) ( $fac_sum['pending'] ?? 0 );
			}
			if ( class_exists( 'MasjidOS_Pro_Madrasa', false ) ) {
				$mad_sum     = MasjidOS_Pro_Madrasa::summary();
				$enrollments = (int) ( $mad_sum['pending'] ?? 0 );
			}
		}

		$total = $ask_imam + $donations + $bookings + $enrollments;

		return [
			'total'       => $total,
			'ask_imam'    => $ask_imam,
			'donations'   => $donations,
			'bookings'    => $bookings,
			'enrollments' => $enrollments,
		];
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

		$pending    = self::get_pending_counts();
		$menu_label = __( 'MasjidOS', 'masjidos' );
		if ( $pending['total'] > 0 ) {
			$menu_label .= sprintf(
				' <span class="awaiting-mod count-%1$d"><span class="pending-count">%2$s</span></span>',
				$pending['total'],
				esc_html( number_format_i18n( $pending['total'] ) )
			);
		}

		add_menu_page(
			__( 'MasjidOS', 'masjidos' ),
			$menu_label,
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

		if ( post_type_exists( ITMMS_Ask_Imam::POST_TYPE ) ) {
			$ask_label = __( 'Ask the Imam', 'masjidos' );
			if ( $pending['ask_imam'] > 0 ) {
				$ask_label .= sprintf(
					' <span class="awaiting-mod count-%1$d"><span class="pending-count">%2$s</span></span>',
					$pending['ask_imam'],
					esc_html( number_format_i18n( $pending['ask_imam'] ) )
				);
			}
			add_submenu_page(
				'masjidos',
				__( 'Ask the Imam', 'masjidos' ),
				$ask_label,
				'edit_posts',
				'edit.php?post_type=' . ITMMS_Ask_Imam::POST_TYPE
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
			ITMMS_VERSION . '.' . (string) @filemtime( ITMMS_PLUGIN_DIR . 'public/assets/css/tv-fonts.css' )
		);

		wp_enqueue_style(
			'itmms-admin',
			ITMMS_PLUGIN_URL . 'admin/assets/css/admin.css',
			[ 'itmms-fonts' ],
			ITMMS_VERSION . '.' . (string) @filemtime( ITMMS_PLUGIN_DIR . 'admin/assets/css/admin.css' )
		);

		wp_enqueue_style(
			'itmms-public',
			ITMMS_PLUGIN_URL . 'public/assets/css/public.css',
			[ 'itmms-fonts' ],
			ITMMS_VERSION . '.' . (string) @filemtime( ITMMS_PLUGIN_DIR . 'public/assets/css/public.css' )
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
			ITMMS_VERSION . '.' . (string) @filemtime( ITMMS_PLUGIN_DIR . 'admin/assets/js/modules/dashboard.js' ),
			true
		);

		wp_register_script(
			'itmms-admin-settings',
			ITMMS_PLUGIN_URL . 'admin/assets/js/modules/settings.js',
			[ 'itmms-admin-shared' ],
			ITMMS_VERSION . '.' . (string) @filemtime( ITMMS_PLUGIN_DIR . 'admin/assets/js/modules/settings.js' ),
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
			ITMMS_VERSION . '.' . (string) @filemtime( ITMMS_PLUGIN_DIR . 'admin/assets/js/modules/welcome.js' ),
			true
		);

		wp_register_script(
			'itmms-admin-docs',
			ITMMS_PLUGIN_URL . 'admin/assets/js/modules/docs.js',
			[ 'itmms-admin-shared' ],
			ITMMS_VERSION . '.' . (string) @filemtime( ITMMS_PLUGIN_DIR . 'admin/assets/js/modules/docs.js' ),
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
			ITMMS_VERSION . '.' . (string) @filemtime( ITMMS_PLUGIN_DIR . 'admin/assets/js/modules/features.js' ),
			true
		);

		wp_enqueue_script(
			'itmms-public',
			ITMMS_PLUGIN_URL . 'public/assets/js/public.js',
			[],
			ITMMS_VERSION,
			true
		);

		$admin_deps = [
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
		];

		/**
		 * Filter script handles that must load before the Free admin SPA (app.js).
		 * Pro may append its own registered module handles here.
		 *
		 * @param string[] $admin_deps Dependency handles.
		 */
		$admin_deps = (array) apply_filters( 'masjidos_admin_dependencies', $admin_deps );

		wp_enqueue_script(
			'itmms-admin',
			ITMMS_PLUGIN_URL . 'admin/assets/js/app.js',
			$admin_deps,
			ITMMS_VERSION . '.' . (string) @filemtime( ITMMS_PLUGIN_DIR . 'admin/assets/js/app.js' ),
			true
		);

		$this->inject_ui_script_translations( 'itmms-admin' );

		/**
		 * Filter Free admin sidebar nav extras.
		 * Each item: group_label, type (link|tab), label, icon, url|tab.
		 *
		 * @param array<int,array<string,mixed>> $nav Nav entries.
		 */
		$admin_nav = (array) apply_filters( 'masjidos_admin_nav', [] );

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
				'onboarding'   => ITMMS_REST::get_onboarding_status(),
				'showWelcome'  => (int) get_option( 'itmms_show_welcome', 0 ) === 1,
				'user'         => [
					'id'   => get_current_user_id(),
					'name' => wp_get_current_user()->display_name,
				],
				'pro'          => masjidos_pro_localize(),
				'proUrl'       => masjidos_pro_url(),
				'adminNav'     => $admin_nav,
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
			wp_add_inline_script(
				'wp-i18n',
				'if ( window.wp && window.wp.i18n && window.wp.i18n.setLocaleData ) { window.wp.i18n.setLocaleData( { "": { domain: "messages", lang: "en_US", "plural-forms": "nplurals=2; plural=(n != 1);" } }, "masjidos" ); }',
				'after'
			);
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
	 * Append fullscreen class to MasjidOS admin pages and sync UI language/RTL to Pro pages.
	 *
	 * @param string $classes Space-separated body class string.
	 * @return string
	 */
	public function add_fullscreen_class( string $classes ): string {
		$is_free = $this->is_itmms_page();
		$is_pro  = $this->is_masjidos_pro_page();

		if ( $is_free || $is_pro ) {
			if ( $is_free ) {
				$classes .= ' itmms-fullscreen';
			} else {
				$classes .= ' masjidos-pro-screen';
			}
			$ui       = ITMMS_Settings::ui_language();
			$classes .= ' itmms-ui-' . $ui;
			if ( 'ar' === $ui ) {
				$classes .= ' itmms-ui-rtl rtl';
			}
		}

		return $classes;
	}

	/**
	 * Add a WordPress escape link to the WP admin bar on fullscreen SPA pages.
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
	 * Add a MasjidOS quick-access menu + live notification badge in the WP Admin Bar.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
	 */
	public function add_adminbar_quick_menu( WP_Admin_Bar $wp_admin_bar ): void {
		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'itmms_manage_prayers' ) ) {
			return;
		}

		$pending = self::get_pending_counts();
		$badge   = '';
		if ( $pending['total'] > 0 ) {
			$badge = sprintf(
				' <span style="display:inline-block;padding:0 6px;border-radius:999px;background:#d63638;color:#fff;font-size:11px;line-height:18px;font-weight:700;vertical-align:middle;">%s</span>',
				esc_html( number_format_i18n( $pending['total'] ) )
			);
		}

		$wp_admin_bar->add_node(
			[
				'id'    => 'masjidos-quick-menu',
				'title' => '<span class="ab-icon dashicons dashicons-building" style="margin-top:2px;"></span><span class="ab-label">' . esc_html__( 'MasjidOS', 'masjidos' ) . '</span>' . $badge,
				'href'  => admin_url( 'admin.php?page=masjidos' ),
			]
		);

		$wp_admin_bar->add_node(
			[
				'parent' => 'masjidos-quick-menu',
				'id'     => 'masjidos-qm-dashboard',
				'title'  => __( 'MasjidOS Dashboard', 'masjidos' ),
				'href'   => admin_url( 'admin.php?page=masjidos' ),
			]
		);

		$wp_admin_bar->add_node(
			[
				'parent' => 'masjidos-quick-menu',
				'id'     => 'masjidos-qm-prayers',
				'title'  => __( 'Prayer Times & Jumuah', 'masjidos' ),
				'href'   => admin_url( 'admin.php?page=masjidos#prayer-times' ),
			]
		);

		$wp_admin_bar->add_node(
			[
				'parent' => 'masjidos-quick-menu',
				'id'     => 'masjidos-qm-announcements',
				'title'  => __( 'Announcements & Notices', 'masjidos' ),
				'href'   => admin_url( 'admin.php?page=masjidos#announcements' ),
			]
		);

		if ( post_type_exists( ITMMS_Ask_Imam::POST_TYPE ) ) {
			$ask_title = __( 'Ask the Imam Q&A', 'masjidos' );
			if ( $pending['ask_imam'] > 0 ) {
				$ask_title .= ' (' . number_format_i18n( $pending['ask_imam'] ) . ')';
			}
			$wp_admin_bar->add_node(
				[
					'parent' => 'masjidos-quick-menu',
					'id'     => 'masjidos-qm-ask-imam',
					'title'  => esc_html( $ask_title ),
					'href'   => admin_url( 'edit.php?post_type=' . ITMMS_Ask_Imam::POST_TYPE ),
				]
			);
		}

		if ( function_exists( 'masjidos_pro_is_active' ) && masjidos_pro_is_active() ) {
			$don_title = __( 'Pro: Donations & Campaigns', 'masjidos' );
			if ( $pending['donations'] > 0 ) {
				$don_title .= ' (' . number_format_i18n( $pending['donations'] ) . ')';
			}
			$wp_admin_bar->add_node(
				[
					'parent' => 'masjidos-quick-menu',
					'id'     => 'masjidos-qm-pro-donations',
					'title'  => esc_html( $don_title ),
					'href'   => admin_url( 'admin.php?page=masjidos-pro-donations' ),
				]
			);

			$wp_admin_bar->add_node(
				[
					'parent' => 'masjidos-quick-menu',
					'id'     => 'masjidos-qm-pro-accounts',
					'title'  => __( 'Pro: Accounts & Collections', 'masjidos' ),
					'href'   => admin_url( 'admin.php?page=masjidos-pro-accounts' ),
				]
			);

			$wp_admin_bar->add_node(
				[
					'parent' => 'masjidos-quick-menu',
					'id'     => 'masjidos-qm-pro-members',
					'title'  => __( 'Pro: Member Directory', 'masjidos' ),
					'href'   => admin_url( 'admin.php?page=masjidos-pro-members' ),
				]
			);

			$fac_title = __( 'Pro: Facility Bookings', 'masjidos' );
			if ( $pending['bookings'] > 0 ) {
				$fac_title .= ' (' . number_format_i18n( $pending['bookings'] ) . ')';
			}
			$wp_admin_bar->add_node(
				[
					'parent' => 'masjidos-quick-menu',
					'id'     => 'masjidos-qm-pro-facilities',
					'title'  => esc_html( $fac_title ),
					'href'   => admin_url( 'admin.php?page=masjidos-pro-facilities' ),
				]
			);

			$mad_title = __( 'Pro: School / Maktob', 'masjidos' );
			if ( $pending['enrollments'] > 0 ) {
				$mad_title .= ' (' . number_format_i18n( $pending['enrollments'] ) . ')';
			}
			$wp_admin_bar->add_node(
				[
					'parent' => 'masjidos-quick-menu',
					'id'     => 'masjidos-qm-pro-madrasa',
					'title'  => esc_html( $mad_title ),
					'href'   => admin_url( 'admin.php?page=masjidos-pro-madrasa' ),
				]
			);

			$wp_admin_bar->add_node(
				[
					'parent' => 'masjidos-quick-menu',
					'id'     => 'masjidos-qm-pro-volunteers',
					'title'  => __( 'Pro: Volunteers', 'masjidos' ),
					'href'   => admin_url( 'admin.php?page=masjidos-pro-volunteers' ),
				]
			);

			$wp_admin_bar->add_node(
				[
					'parent' => 'masjidos-quick-menu',
					'id'     => 'masjidos-qm-pro-templates',
					'title'  => '🎨 ' . __( 'Pro: Template Design Library', 'masjidos' ),
					'href'   => admin_url( 'admin.php?page=masjidos-pro-templates' ),
				]
			);
		}

		$wp_admin_bar->add_node(
			[
				'parent' => 'masjidos-quick-menu',
				'id'     => 'masjidos-qm-tv',
				'title'  => __( 'Open Live TV Display', 'masjidos' ),
				'href'   => home_url( '/masjidos-display/' ),
				'meta'   => [ 'target' => '_blank' ],
			]
		);

		if ( current_user_can( 'publish_pages' ) ) {
			$starter_url = wp_nonce_url(
				admin_url( 'admin-post.php?action=masjidos_create_starter_pages' ),
				'masjidos_create_starter_pages'
			);
			$wp_admin_bar->add_node(
				[
					'parent' => 'masjidos-quick-menu',
					'id'     => 'masjidos-qm-starter-pages',
					'title'  => '&#10024; ' . __( 'Create Ready-Made Pages', 'masjidos' ),
					'href'   => $starter_url,
				]
			);
		}
	}

	/**
	 * Create or reuse ready-made starter pages for Free & Pro modules.
	 *
	 * @return array<string,int> Map of starter key => page ID.
	 */
	public static function ensure_starter_pages(): array {
		$saved_map = get_option( 'itmms_starter_pages_map', [] );
		if ( ! is_array( $saved_map ) ) {
			$saved_map = [];
		}

		$definitions = [
			'prayer_times' => [
				'slug'    => 'prayer-times',
				'title'   => __( 'Prayer Times & Jumuah', 'masjidos' ),
				'content' => "<!-- wp:shortcode -->\n[masjidos_prayer_times]\n<!-- /wp:shortcode -->\n\n<!-- wp:shortcode -->\n[masjidos_jumuah]\n<!-- /wp:shortcode -->\n\n<!-- wp:shortcode -->\n[masjidos_monthly_prayer_times]\n<!-- /wp:shortcode -->",
			],
			'community'    => [
				'slug'    => 'masjid-community',
				'title'   => __( 'Masjid Notices, Events & Ask the Imam', 'masjidos' ),
				'content' => "<!-- wp:shortcode -->\n[masjidos_announcements]\n<!-- /wp:shortcode -->\n\n<!-- wp:shortcode -->\n[masjidos_events]\n<!-- /wp:shortcode -->\n\n<!-- wp:shortcode -->\n[masjidos_ask_imam]\n<!-- /wp:shortcode -->",
			],
		];

		if ( function_exists( 'masjidos_pro_is_active' ) && masjidos_pro_is_active() ) {
			$definitions['donate']       = [
				'slug'    => 'donate',
				'title'   => __( 'Donate to Our Masjid', 'masjidos' ),
				'content' => "<!-- wp:shortcode -->\n[masjidos_donation_form]\n<!-- /wp:shortcode -->\n\n<!-- wp:shortcode -->\n[masjidos_collections]\n<!-- /wp:shortcode -->",
			];
			$definitions['transparency'] = [
				'slug'    => 'financial-transparency',
				'title'   => __( 'Financial Transparency', 'masjidos' ),
				'content' => "<!-- wp:shortcode -->\n[masjidos_financials]\n<!-- /wp:shortcode -->\n\n<!-- wp:shortcode -->\n[masjidos_transparency_report]\n<!-- /wp:shortcode -->",
			];
			$definitions['services']     = [
				'slug'    => 'masjid-services',
				'title'   => __( 'Facility Booking, Classes & Volunteers', 'masjidos' ),
				'content' => "<!-- wp:shortcode -->\n[masjidos_facility_booking]\n<!-- /wp:shortcode -->\n\n<!-- wp:shortcode -->\n[masjidos_classes]\n<!-- /wp:shortcode -->\n\n<!-- wp:shortcode -->\n[masjidos_volunteers]\n<!-- /wp:shortcode -->",
			];
		}

		$result_map = [];
		foreach ( $definitions as $key => $def ) {
			$page_id = isset( $saved_map[ $key ] ) ? absint( $saved_map[ $key ] ) : 0;
			$post    = $page_id ? get_post( $page_id ) : null;

			if ( ! $post || 'page' !== $post->post_type || 'trash' === $post->post_status ) {
				$by_path = get_page_by_path( $def['slug'], OBJECT, 'page' );
				if ( $by_path && 'trash' !== $by_path->post_status ) {
					$page_id = (int) $by_path->ID;
				} else {
					$inserted = wp_insert_post(
						[
							'post_type'    => 'page',
							'post_status'  => 'publish',
							'post_title'   => $def['title'],
							'post_name'    => $def['slug'],
							'post_content' => $def['content'],
						],
						true
					);
					$page_id = ! is_wp_error( $inserted ) ? (int) $inserted : 0;
				}
			}

			if ( $page_id > 0 ) {
				$saved_map[ $key ]  = $page_id;
				$result_map[ $key ] = $page_id;
			}
		}

		update_option( 'itmms_starter_pages_map', $saved_map, false );
		if ( ! empty( $result_map['prayer_times'] ) ) {
			update_option( 'itmms_starter_page_id', (int) $result_map['prayer_times'], false );
		}

		return $result_map;
	}

	/**
	 * Handle one-click Starter Pages creation from WP Admin.
	 */
	public function handle_create_starter_pages(): void {
		if ( ! current_user_can( 'publish_pages' ) && ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Forbidden', 'masjidos' ) );
		}
		check_admin_referer( 'masjidos_create_starter_pages' );

		self::ensure_starter_pages();

		$referer  = wp_get_referer();
		$redirect = $referer ? $referer : admin_url( 'index.php' );
		// If triggered from fullscreen SPA, redirect to WP Pages or Pro screen so notice is visible.
		if ( false !== strpos( $redirect, 'page=masjidos' ) && false === strpos( $redirect, 'page=masjidos-pro' ) ) {
			$redirect = admin_url( 'edit.php?post_type=page' );
		}
		wp_safe_redirect( add_query_arg( 'masjidos_starter_pages', '1', $redirect ) );
		exit;
	}

	/**
	 * Show a notice with View/Edit links after creating starter pages.
	 */
	public function render_starter_pages_notice(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( empty( $_GET['masjidos_starter_pages'] ) || ! current_user_can( 'edit_pages' ) ) {
			return;
		}
		$map = get_option( 'itmms_starter_pages_map', [] );
		if ( ! is_array( $map ) || empty( $map ) ) {
			return;
		}

		echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html__( 'MasjidOS Ready-Made Pages are published and ready:', 'masjidos' ) . '</strong></p><ul style="margin:6px 0 8px 18px;list-style:disc;">';
		foreach ( $map as $page_id ) {
			$page_id = absint( $page_id );
			$post    = $page_id ? get_post( $page_id ) : null;
			if ( ! $post || 'trash' === $post->post_status ) {
				continue;
			}
			printf(
				'<li><strong>%s</strong> — <a href="%s" target="_blank" rel="noopener noreferrer">%s</a> | <a href="%s">%s</a></li>',
				esc_html( get_the_title( $post ) ),
				esc_url( get_permalink( $post ) ),
				esc_html__( 'View Page', 'masjidos' ),
				esc_url( (string) get_edit_post_link( $post->ID ) ),
				esc_html__( 'Edit in Gutenberg / Elementor', 'masjidos' )
			);
		}
		echo '</ul></div>';
	}

	/**
	 * Register native WordPress Admin Dashboard widget (wp-admin/index.php).
	 */
	public function register_wp_dashboard_widget(): void {
		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'itmms_manage_prayers' ) ) {
			return;
		}

		wp_add_dashboard_widget(
			'itmms_wp_dashboard_widget',
			__( 'MasjidOS — Prayer Times & Setup Checklist', 'masjidos' ),
			[ $this, 'render_wp_dashboard_widget' ]
		);
	}

	/**
	 * Render native WordPress Admin Dashboard widget.
	 */
	public function render_wp_dashboard_widget(): void {
		$settings     = ITMMS_Settings::get_all();
		$prayer_times = ITMMS_Prayer_Times::today();
		$next_prayer  = $prayer_times['next_prayer'] ?? [];
		$onboarding   = ITMMS_REST::get_onboarding_status( $settings );
		$hijri        = ITMMS_Hijri::for_date( new DateTimeImmutable( 'now', wp_timezone() ), (int) ( $settings['hijri_adjustment'] ?? 0 ), ITMMS_Settings::ui_language() );
		$pending      = self::get_pending_counts();

		$step_labels = [
			'location'      => __( 'Location & timezone configured', 'masjidos' ),
			'iqamah_jumuah' => __( 'Iqamah & Jumuah times set', 'masjidos' ),
			'starter_page'  => __( 'Prayer times page published', 'masjidos' ),
			'first_content' => __( 'First notice or khutbah added', 'masjidos' ),
			'tv_preview'    => __( 'Mosque TV display previewed', 'masjidos' ),
		];
		?>
		<div class="itmms-wp-widget" style="font-size:13px;line-height:1.5;color:#1e293b;">
			<div style="display:flex;justify-content:space-between;align-items:center;background:linear-gradient(135deg,#064e3b,#047857);color:#fff;padding:12px 14px;border-radius:8px;margin-bottom:12px;">
				<div>
					<div style="font-size:11px;opacity:.85;text-transform:uppercase;letter-spacing:.04em;"><?php echo esc_html__( 'Next Prayer', 'masjidos' ); ?></div>
					<strong style="font-size:16px;"><?php echo esc_html( (string) ( $next_prayer['name'] ?? '—' ) ); ?> · <?php echo esc_html( (string) ( $next_prayer['time'] ?? '' ) ); ?></strong>
				</div>
				<div style="text-align:right;font-size:12px;opacity:.92;">
					<div><?php echo esc_html( (string) ( $hijri['label'] ?? '' ) ); ?></div>
					<small><?php echo esc_html( (string) ( $settings['city'] ?? '' ) ); ?></small>
				</div>
			</div>

			<?php if ( function_exists( 'masjidos_pro_is_active' ) && masjidos_pro_is_active() ) : ?>
				<?php
				$don_sum = class_exists( 'MasjidOS_Pro_Donations', false ) ? MasjidOS_Pro_Donations::summary() : [];
				$acc_sum = class_exists( 'MasjidOS_Pro_Accounts', false ) ? MasjidOS_Pro_Accounts::summary() : [];
				$mem_sum = class_exists( 'MasjidOS_Pro_Members', false ) ? MasjidOS_Pro_Members::summary() : [];
				$curr    = (string) ( $don_sum['currency'] ?? 'BDT' );
				?>
				<div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;margin-bottom:12px;">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=masjidos-pro-donations' ) ); ?>" style="display:block;padding:8px 10px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;text-decoration:none;color:#14532d;">
						<span style="display:block;font-size:10px;text-transform:uppercase;font-weight:700;opacity:.8;"><?php echo esc_html__( 'Donations', 'masjidos' ); ?></span>
						<strong style="font-size:13px;"><?php echo esc_html( number_format_i18n( (float) ( $don_sum['total_raised'] ?? 0 ), 0 ) . ' ' . $curr ); ?></strong>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=masjidos-pro-accounts' ) ); ?>" style="display:block;padding:8px 10px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;text-decoration:none;color:#14532d;">
						<span style="display:block;font-size:10px;text-transform:uppercase;font-weight:700;opacity:.8;"><?php echo esc_html__( 'Net Balance', 'masjidos' ); ?></span>
						<strong style="font-size:13px;"><?php echo esc_html( number_format_i18n( (float) ( $acc_sum['net'] ?? 0 ), 0 ) . ' ' . $curr ); ?></strong>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=masjidos-pro-members' ) ); ?>" style="display:block;padding:8px 10px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;text-decoration:none;color:#14532d;">
						<span style="display:block;font-size:10px;text-transform:uppercase;font-weight:700;opacity:.8;"><?php echo esc_html__( 'Members', 'masjidos' ); ?></span>
						<strong style="font-size:13px;"><?php echo esc_html( number_format_i18n( (int) ( $mem_sum['active'] ?? 0 ) ) ); ?></strong>
					</a>
				</div>
			<?php endif; ?>

			<?php if ( $pending['total'] > 0 ) : ?>
				<div style="margin-bottom:12px;padding:8px 10px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;font-size:12px;color:#92400e;">
					<strong><?php echo esc_html__( 'Pending items needing attention:', 'masjidos' ); ?></strong>
					<div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:4px;">
						<?php if ( $pending['ask_imam'] > 0 ) : ?>
							<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . ITMMS_Ask_Imam::POST_TYPE ) ); ?>" style="color:#b45309;font-weight:600;"><?php echo esc_html( sprintf( /* translators: %d: count */ __( '%d Imam Questions', 'masjidos' ), $pending['ask_imam'] ) ); ?></a>
						<?php endif; ?>
						<?php if ( $pending['donations'] > 0 ) : ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=masjidos-pro-donations&tab=donations&status=pending' ) ); ?>" style="color:#b45309;font-weight:600;"><?php echo esc_html( sprintf( /* translators: %d: count */ __( '%d Pending Donations', 'masjidos' ), $pending['donations'] ) ); ?></a>
						<?php endif; ?>
						<?php if ( $pending['bookings'] > 0 ) : ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=masjidos-pro-facilities&tab=bookings&bk_status=pending' ) ); ?>" style="color:#b45309;font-weight:600;"><?php echo esc_html( sprintf( /* translators: %d: count */ __( '%d Facility Bookings', 'masjidos' ), $pending['bookings'] ) ); ?></a>
						<?php endif; ?>
						<?php if ( $pending['enrollments'] > 0 ) : ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=masjidos-pro-madrasa&tab=students' ) ); ?>" style="color:#b45309;font-weight:600;"><?php echo esc_html( sprintf( /* translators: %d: count */ __( '%d Madrasa Enrollments', 'masjidos' ), $pending['enrollments'] ) ); ?></a>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>

			<div style="margin-bottom:12px;padding:10px 12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;">
				<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
					<strong><?php echo esc_html__( 'Masjid Setup Progress', 'masjidos' ); ?></strong>
					<span style="font-weight:700;color:#047857;">
						<?php
						printf(
							/* translators: 1: completed steps, 2: total steps, 3: percent */
							esc_html__( '%1$d/%2$d (%3$d%%)', 'masjidos' ),
							(int) $onboarding['completed'],
							(int) $onboarding['total'],
							(int) $onboarding['percent']
						);
						?>
					</span>
				</div>
				<div style="height:7px;background:#e2e8f0;border-radius:999px;overflow:hidden;margin-bottom:8px;">
					<div style="height:100%;width:<?php echo esc_attr( (string) (int) $onboarding['percent'] ); ?>%;background:#059669;border-radius:999px;"></div>
				</div>
				<ul style="margin:0;padding:0;list-style:none;display:grid;gap:4px;">
					<?php foreach ( (array) $onboarding['steps'] as $step ) : ?>
						<?php
						$key   = (string) ( $step['key'] ?? '' );
						$done  = ! empty( $step['done'] );
						$label = $step_labels[ $key ] ?? $key;
						?>
						<li style="display:flex;align-items:center;gap:6px;color:<?php echo $done ? '#065f46' : '#64748b'; ?>;">
							<span style="display:inline-flex;align-items:center;justify-content:center;width:16px;height:16px;border-radius:50%;font-size:10px;font-weight:700;background:<?php echo $done ? '#d1fae5' : '#e2e8f0'; ?>;color:<?php echo $done ? '#047857' : '#64748b'; ?>;">
								<?php echo $done ? '&#10003;' : '&bull;'; ?>
							</span>
							<span><?php echo esc_html( $label ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div style="display:flex;flex-wrap:wrap;gap:6px;">
				<a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=masjidos' ) ); ?>"><?php echo esc_html__( 'Open MasjidOS', 'masjidos' ); ?></a>
				<?php if ( current_user_can( 'publish_pages' ) ) : ?>
					<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=masjidos_create_starter_pages' ), 'masjidos_create_starter_pages' ) ); ?>">&#10024; <?php echo esc_html__( 'Create Ready-Made Pages', 'masjidos' ); ?></a>
				<?php endif; ?>
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=masjidos&itmms_view=announcements' ) ); ?>"><?php echo esc_html__( 'Add Notice', 'masjidos' ); ?></a>
				<a class="button" href="<?php echo esc_url( home_url( '/masjidos-display/' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html__( 'TV Display', 'masjidos' ); ?></a>
			</div>
		</div>
		<?php
	}

	/**
	 * Check whether the current request is a MasjidOS Pro admin screen.
	 *
	 * @param string $hook Optional hook suffix.
	 */
	private function is_masjidos_pro_page( string $hook = '' ): bool {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		if ( $page && 0 === strpos( $page, 'masjidos-pro' ) ) {
			return true;
		}
		return $hook && false !== strpos( $hook, 'masjidos-pro' );
	}

	/**
	 * Check whether the current request is a MasjidOS Free SPA admin page.
	 * Sibling Pro screens (page=masjidos-pro-*) stay on normal WP admin chrome.
	 *
	 * @param string $hook Optional hook suffix.
	 */
	private function is_itmms_page( string $hook = '' ): bool {
		if ( $this->is_masjidos_pro_page( $hook ) ) {
			return false;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

		if ( $hook && false !== strpos( $hook, 'masjidos' ) ) {
			return true;
		}

		return '' !== $page && 0 === strpos( $page, 'masjidos' );
	}
}
