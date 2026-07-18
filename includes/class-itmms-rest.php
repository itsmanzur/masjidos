<?php
/**
 * REST API endpoints for the MasjidOS admin app.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers REST routes.
 */
final class ITMMS_REST {

	private const NAMESPACE = 'masjidos/v1';

	/**
	 * Wire routes.
	 */
	public function init(): void {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Register REST routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/dashboard',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_dashboard' ],
				'permission_callback' => [ $this, 'can_read' ],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/welcome/dismiss',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'dismiss_welcome' ],
				'permission_callback' => [ $this, 'can_read' ],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/settings',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_settings' ],
					'permission_callback' => [ $this, 'can_read' ],
				],
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_settings' ],
					'permission_callback' => [ $this, 'can_manage_settings' ],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/prayer-times/today',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_prayer_times_today' ],
				'permission_callback' => '__return_true',
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/prayer-times/date',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_prayer_times_date' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'date' => [
						'required'          => true,
						'validate_callback' => static function ( $param ): bool {
							return is_string( $param ) && (bool) preg_match( '/^\d{4}-\d{2}-\d{2}$/', $param );
						},
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/prayer-times/month',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_prayer_times_month_json' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'year'  => [
						'required'          => false,
						'validate_callback' => static function ( $param ): bool {
							return is_numeric( $param ) || empty( $param );
						},
						'sanitize_callback' => 'absint',
					],
					'month' => [
						'required'          => false,
						'validate_callback' => static function ( $param ): bool {
							return is_numeric( $param ) || empty( $param );
						},
						'sanitize_callback' => 'absint',
					],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/salahapi',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_salahapi_document' ],
				'permission_callback' => '__return_true',
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/salahapi/csv',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_salahapi_csv' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'fromDate' => [
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'toDate'   => [
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/prayer-times/timetable',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_prayer_timetable' ],
					'permission_callback' => [ $this, 'can_read' ],
				],
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'clear_prayer_timetable' ],
					'permission_callback' => [ $this, 'can_manage_prayers' ],
					'args'                => [
						'year' => [
							'required'          => false,
							'validate_callback' => static function ( $param ) {
								return is_numeric( $param ) || empty( $param );
							},
							'sanitize_callback' => 'absint',
						],
					],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/prayer-times/timetable/import',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'import_prayer_timetable' ],
				'permission_callback' => [ $this, 'can_manage_prayers' ],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/prayer-times/timetable/export',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'export_prayer_timetable' ],
				'permission_callback' => [ $this, 'can_manage_prayers' ],
				'args'                => [
					'source' => [
						'required'          => false,
						'sanitize_callback' => 'sanitize_key',
					],
					'year'   => [
						'required'          => false,
						'validate_callback' => function( $param ) {
							return is_numeric( $param ) || empty( $param );
						},
						'sanitize_callback' => 'absint',
					],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/prayer-times/timetable/sample',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'sample_prayer_timetable' ],
				'permission_callback' => [ $this, 'can_read' ],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/prayer-times/monthly',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_monthly_prayer_widget' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'month'    => [
						'required'          => false,
						'validate_callback' => function( $param ) {
							return is_numeric( $param ) || empty( $param );
						},
						'sanitize_callback' => 'absint',
					],
					'year'     => [
						'required'          => false,
						'validate_callback' => function( $param ) {
							return is_numeric( $param ) || empty( $param );
						},
						'sanitize_callback' => 'absint',
					],
					'design'   => [
						'required'          => false,
						'sanitize_callback' => 'sanitize_key',
					],
					'language' => [
						'required'          => false,
						'sanitize_callback' => 'sanitize_key',
					],
					'iqamah'   => [
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'extras'   => [
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'title'    => [
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/prayer-widget',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_prayer_widget' ],
				'permission_callback' => '__return_true',
				'args'                => $this->widget_preview_args(),
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/monthly-prayer-widget',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_monthly_prayer_widget' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'month'    => [
						'required'          => false,
						'validate_callback' => function( $param ) {
							return is_numeric( $param ) || empty( $param );
						},
						'sanitize_callback' => 'absint',
					],
					'year'     => [
						'required'          => false,
						'validate_callback' => function( $param ) {
							return is_numeric( $param ) || empty( $param );
						},
						'sanitize_callback' => 'absint',
					],
					'design'   => [
						'required'          => false,
						'sanitize_callback' => 'sanitize_key',
					],
					'language' => [
						'required'          => false,
						'sanitize_callback' => 'sanitize_key',
					],
					'iqamah'   => [
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'extras'   => [
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'title'    => [
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/calendar',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_calendar_widget' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'month'    => [
						'required'          => false,
						'validate_callback' => function( $param ) {
							return is_numeric( $param ) || empty( $param );
						},
						'sanitize_callback' => 'absint',
					],
					'year'     => [
						'required'          => false,
						'validate_callback' => function( $param ) {
							return is_numeric( $param ) || empty( $param );
						},
						'sanitize_callback' => 'absint',
					],
					'language' => [
						'required'          => false,
						'sanitize_callback' => 'sanitize_key',
					],
					'title'    => [
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/jumuah-widget',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_jumuah_widget' ],
				'permission_callback' => '__return_true',
				'args'                => $this->widget_preview_args(),
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/announcements-widget',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_announcements_widget' ],
				'permission_callback' => '__return_true',
				'args'                => $this->widget_preview_args(),
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/events-widget',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_events_widget' ],
				'permission_callback' => '__return_true',
				'args'                => $this->widget_preview_args(),
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/duas-azkar-widget',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_duas_azkar_widget' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'design'   => [
						'required'          => false,
						'sanitize_callback' => 'sanitize_key',
					],
					'language' => [
						'required'          => false,
						'sanitize_callback' => 'sanitize_key',
					],
					'category' => [
						'required'          => false,
						'sanitize_callback' => 'sanitize_key',
					],
					'limit'    => [
						'required'          => false,
						'validate_callback' => function( $param ) {
							return is_numeric( $param ) || empty( $param );
						},
						'sanitize_callback' => 'absint',
					],
					'counter'  => [
						'required'          => false,
						'sanitize_callback' => 'sanitize_key',
					],
					'share'    => [
						'required'          => false,
						'sanitize_callback' => 'sanitize_key',
					],
					'audio'    => [
						'required'          => false,
						'sanitize_callback' => 'sanitize_key',
					],
					'title'    => [
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/announcements',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_announcements' ],
					'permission_callback' => [ $this, 'can_read' ],
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_announcement' ],
					'permission_callback' => [ $this, 'can_manage_announcements' ],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/announcements/(?P<id>\d+)',
			[
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_announcement' ],
					'permission_callback' => [ $this, 'can_manage_announcements' ],
					'args'                => [
						'id' => [
							'validate_callback' => function( $param ) {
								return is_numeric( $param );
							},
							'sanitize_callback' => 'absint',
							'required'          => true,
						],
					],
				],
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_announcement' ],
					'permission_callback' => [ $this, 'can_manage_announcements' ],
					'args'                => [
						'id' => [
							'validate_callback' => function( $param ) {
								return is_numeric( $param );
							},
							'sanitize_callback' => 'absint',
							'required'          => true,
						],
					],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/announcements/public',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_public_announcements' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'limit' => [
						'required'          => false,
						'validate_callback' => function( $param ) {
							return is_numeric( $param ) || empty( $param );
						},
						'sanitize_callback' => 'absint',
					],
					'type'  => [
						'required'          => false,
						'sanitize_callback' => 'sanitize_key',
					],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/events',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_events' ],
					'permission_callback' => [ $this, 'can_read' ],
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_event' ],
					'permission_callback' => [ $this, 'can_manage_events' ],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/events/(?P<id>\d+)',
			[
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_event' ],
					'permission_callback' => [ $this, 'can_manage_events' ],
					'args'                => [
						'id' => [
							'validate_callback' => function( $param ) {
								return is_numeric( $param );
							},
							'sanitize_callback' => 'absint',
							'required'          => true,
						],
					],
				],
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_event' ],
					'permission_callback' => [ $this, 'can_manage_events' ],
					'args'                => [
						'id' => [
							'validate_callback' => function( $param ) {
								return is_numeric( $param );
							},
							'sanitize_callback' => 'absint',
							'required'          => true,
						],
					],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/events/public',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_public_events' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'limit' => [
						'required'          => false,
						'validate_callback' => function( $param ) {
							return is_numeric( $param ) || empty( $param );
						},
						'sanitize_callback' => 'absint',
					],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/khutbah',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_khutbahs' ],
					'permission_callback' => [ $this, 'can_read' ],
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_khutbah' ],
					'permission_callback' => [ $this, 'can_manage_khutbah' ],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/khutbah/(?P<id>\d+)',
			[
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_khutbah' ],
					'permission_callback' => [ $this, 'can_manage_khutbah' ],
					'args'                => [
						'id' => [
							'validate_callback' => function( $param ) {
								return is_numeric( $param );
							},
							'sanitize_callback' => 'absint',
							'required'          => true,
						],
					],
				],
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_khutbah' ],
					'permission_callback' => [ $this, 'can_manage_khutbah' ],
					'args'                => [
						'id' => [
							'validate_callback' => function( $param ) {
								return is_numeric( $param );
							},
							'sanitize_callback' => 'absint',
							'required'          => true,
						],
					],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/minbar/dashboard',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_minbar_dashboard' ],
				'permission_callback' => [ $this, 'can_manage_khutbah' ],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/minbar/profiles',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_minbar_profiles' ],
					'permission_callback' => [ $this, 'can_manage_khutbah' ],
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_minbar_profile' ],
					'permission_callback' => [ $this, 'can_manage_khutbah' ],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/minbar/profiles/(?P<id>\d+)',
			[
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_minbar_profile' ],
					'permission_callback' => [ $this, 'can_manage_khutbah' ],
				],
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_minbar_profile' ],
					'permission_callback' => [ $this, 'can_manage_khutbah' ],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/minbar/schedule',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_minbar_schedule' ],
					'permission_callback' => [ $this, 'can_manage_khutbah' ],
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_minbar_schedule' ],
					'permission_callback' => [ $this, 'can_manage_khutbah' ],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/minbar/schedule/(?P<id>\d+)',
			[
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_minbar_schedule' ],
					'permission_callback' => [ $this, 'can_manage_khutbah' ],
				],
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_minbar_schedule' ],
					'permission_callback' => [ $this, 'can_manage_khutbah' ],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/minbar/plans',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_minbar_plans' ],
					'permission_callback' => [ $this, 'can_manage_khutbah' ],
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'save_minbar_plan' ],
					'permission_callback' => [ $this, 'can_manage_khutbah' ],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/minbar/plans/(?P<id>[a-zA-Z0-9_-]+)',
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [ $this, 'delete_minbar_plan' ],
				'permission_callback' => [ $this, 'can_manage_khutbah' ],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/minbar/references',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'search_minbar_references' ],
				'permission_callback' => [ $this, 'can_manage_khutbah' ],
				'args'                => [
					'q'    => [
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'type' => [
						'required'          => false,
						'sanitize_callback' => 'sanitize_key',
					],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/minbar/bookmarks',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_minbar_bookmarks' ],
					'permission_callback' => [ $this, 'can_manage_khutbah' ],
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'add_minbar_bookmark' ],
					'permission_callback' => [ $this, 'can_manage_khutbah' ],
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			'/minbar/bookmarks/(?P<id>[a-zA-Z0-9_-]+)',
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [ $this, 'delete_minbar_bookmark' ],
				'permission_callback' => [ $this, 'can_manage_khutbah' ],
			]
		);
	}

	/**
	 * Shared args for shortcode preview widget endpoints.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	private function widget_preview_args(): array {
		return [
			'design'   => [
				'required'          => false,
				'sanitize_callback' => 'sanitize_key',
			],
			'language' => [
				'required'          => false,
				'sanitize_callback' => 'sanitize_key',
			],
			'qibla'    => [
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'meta'     => [
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'iqamah'   => [
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'limit'    => [
				'required'          => false,
				'validate_callback' => function( $param ) {
					return is_numeric( $param ) || empty( $param );
				},
				'sanitize_callback' => 'absint',
			],
			'title'    => [
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	/**
	 * Read a REST yes/no flag with a safe default.
	 */
	private function yes_no_param( WP_REST_Request $request, string $key, string $default ): string {
		$value = strtolower( sanitize_text_field( (string) $request->get_param( $key ) ) );
		return in_array( $value, [ 'yes', 'no' ], true ) ? $value : $default;
	}

	/**
	 * Read short optional text for preview shortcode attributes.
	 */
	private function short_text_param( WP_REST_Request $request, string $key ): string {
		$value = sanitize_text_field( (string) $request->get_param( $key ) );
		return wp_html_excerpt( $value, 120, '' );
	}

	public function can_read(): bool {
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		foreach ( ITMMS_Roles::CAPS as $cap ) {
			if ( current_user_can( $cap ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Capability check for mutating settings.
	 */
	public function can_manage_settings(): bool {
		return current_user_can( 'manage_options' ) || current_user_can( 'itmms_manage_settings' );
	}

	public function can_manage_announcements(): bool {
		return current_user_can( 'manage_options' ) || current_user_can( 'itmms_manage_announcements' );
	}

	public function can_manage_events(): bool {
		return current_user_can( 'manage_options' ) || current_user_can( 'itmms_manage_events' );
	}

	public function can_manage_khutbah(): bool {
		return current_user_can( 'manage_options' )
			|| current_user_can( 'itmms_manage_khutbah' )
			|| current_user_can( 'itmms_manage_announcements' )
			|| current_user_can( 'itmms_manage_prayers' );
	}

	public function can_manage_prayers(): bool {
		return current_user_can( 'manage_options' ) || current_user_can( 'itmms_manage_prayers' );
	}

	/**
	 * Return dashboard summary.
	 */
	public function get_dashboard(): WP_REST_Response {
		$settings = ITMMS_Settings::get_all();
		$prayer_times = ITMMS_Prayer_Times::today();
		$timezone_name = (string) ( $settings['timezone'] ?? wp_timezone_string() );

		try {
			$timezone = new DateTimeZone( $timezone_name );
		} catch ( Exception $e ) {
			$timezone = wp_timezone();
			$timezone_name = $timezone->getName();
		}

		$day = new DateTimeImmutable( 'now', $timezone );
		$upcoming_days = [];
		for ( $i = 0; $i < 7; $i++ ) {
			$date = 0 === $i ? $day : $day->modify( '+' . $i . ' days' );
			$row = ITMMS_Prayer_Times::for_date( $date, $settings, false );
			$indexed = [];
			foreach ( (array) ( $row['prayers'] ?? [] ) as $prayer ) {
				if ( ! empty( $prayer['key'] ) ) {
					$indexed[ (string) $prayer['key'] ] = $prayer;
				}
			}

			$upcoming_days[] = [
				'date'    => (string) ( $row['date'] ?? $date->format( 'Y-m-d' ) ),
				'label'   => date_i18n( 'D, M j', $date->getTimestamp() ),
				'hijri'   => (string) ( $row['hijri_date']['label'] ?? '' ),
				'is_today'=> 0 === $i,
				'prayers' => [
					'fajr'    => (string) ( $indexed['fajr']['time'] ?? '' ),
					'dhuhr'   => (string) ( $indexed['dhuhr']['time'] ?? '' ),
					'asr'     => (string) ( $indexed['asr']['time'] ?? '' ),
					'maghrib' => (string) ( $indexed['maghrib']['time'] ?? '' ),
					'isha'    => (string) ( $indexed['isha']['time'] ?? '' ),
				],
			];
		}

		$latitude  = (float) ( $settings['latitude'] ?? 0 );
		$longitude = (float) ( $settings['longitude'] ?? 0 );
		$coords_ok = abs( $latitude ) > 0.0001 || abs( $longitude ) > 0.0001;
		$timezone_ok = ! in_array( $timezone_name, [ '', 'UTC', '+00:00', '-00:00' ], true );
		$site_timezone = wp_timezone_string();

		$payload = [
			'settings'    => $settings,
			'stats'       => [
				'announcements' => ITMMS_Announcements::count_active(),
				'events'        => ITMMS_Events::count_active(),
			],
			'prayers'     => $prayer_times['prayers'],
			'next_prayer' => $prayer_times['next_prayer'],
			'prayer_meta' => $prayer_times['meta'],
			'hijri_date'  => ITMMS_Hijri::for_date( $day, (int) ( $settings['hijri_adjustment'] ?? 0 ), 'en' ),
			'announcements' => ITMMS_Announcements::active( 5 ),
			'events'        => ITMMS_Events::active( 5 ),
			'modules'     => ITMMS_Settings::module_definitions(),
			'upcoming_days' => $upcoming_days,
			'trust'       => [
				'source'           => (string) ( $prayer_times['meta']['prayer_source'] ?? $settings['prayer_source'] ?? 'local' ),
				'hijri_adjustment' => (int) ( $settings['hijri_adjustment'] ?? 0 ),
				'coordinates_ok'   => $coords_ok,
				'timezone_ok'      => $timezone_ok,
				'timezone_mismatch'=> $timezone_ok && $site_timezone && $timezone_name !== $site_timezone,
				'site_timezone'    => $site_timezone,
				'offsets'          => $settings['prayer_offsets'] ?? [],
			],
			'timetable'   => ITMMS_Prayer_Timetable::summary(),
			'pro'         => function_exists( 'masjidos_pro_localize' ) ? masjidos_pro_localize() : [ 'active' => false ],
		];

		/**
		 * Filter dashboard REST payload (Pro may append cards / stats).
		 *
		 * @param array<string,mixed> $payload Dashboard data.
		 */
		$payload = (array) apply_filters( 'masjidos_dashboard_data', $payload );

		return rest_ensure_response( $payload );
	}

	/**
	 * Dismiss the first-run Welcome screen.
	 */
	public function dismiss_welcome(): WP_REST_Response {
		update_option( 'itmms_show_welcome', 0, false );
		return rest_ensure_response(
			[
				'show_welcome' => false,
			]
		);
	}

	/**
	 * Return settings.
	 */
	public function get_settings(): WP_REST_Response {
		return rest_ensure_response(
			[
				'settings' => ITMMS_Settings::get_all(),
				'modules'  => ITMMS_Settings::module_definitions(),
			]
		);
	}

	/**
	 * Update settings.
	 */
	public function update_settings( WP_REST_Request $request ): WP_REST_Response {
		$settings = ITMMS_Settings::update( $request->get_json_params() ?: [] );
		ITMMS_Prayer_Times::flush_cache();

		return rest_ensure_response(
			[
				'settings' => $settings,
				'modules'  => ITMMS_Settings::module_definitions(),
			]
		);
	}

	/**
	 * Return today's calculated prayer times (public headless JSON).
	 */
	public function get_prayer_times_today(): WP_REST_Response {
		return rest_ensure_response( ITMMS_SalahAPI::headless_day( ITMMS_Prayer_Times::today() ) );
	}

	/**
	 * Return prayer times for a specific Gregorian date (public headless JSON).
	 */
	public function get_prayer_times_date( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$date_str = (string) $request->get_param( 'date' );
		$settings = ITMMS_Settings::get_all();
		try {
			$timezone = new DateTimeZone( (string) ( $settings['timezone'] ?? wp_timezone_string() ) );
		} catch ( Exception $e ) {
			$timezone = wp_timezone();
		}

		$date = DateTimeImmutable::createFromFormat( 'Y-m-d', $date_str, $timezone );
		if ( ! $date ) {
			return new WP_Error( 'itmms_invalid_date', __( 'Invalid date. Use YYYY-MM-DD.', 'masjidos' ), [ 'status' => 400 ] );
		}

		$day = ITMMS_Prayer_Times::for_date( $date->setTime( 0, 0, 0 ), $settings );
		return rest_ensure_response( ITMMS_SalahAPI::headless_day( $day ) );
	}

	/**
	 * Return a month of prayer times as JSON (not the HTML widget).
	 */
	public function get_prayer_times_month_json( WP_REST_Request $request ): WP_REST_Response {
		$settings = ITMMS_Settings::get_all();
		try {
			$timezone = new DateTimeZone( (string) ( $settings['timezone'] ?? wp_timezone_string() ) );
		} catch ( Exception $e ) {
			$timezone = wp_timezone();
		}
		$now = new DateTimeImmutable( 'now', $timezone );
		$year = (int) $request->get_param( 'year' );
		$month = (int) $request->get_param( 'month' );
		if ( $year < 1970 || $year > 2099 ) {
			$year = (int) $now->format( 'Y' );
		}
		if ( $month < 1 || $month > 12 ) {
			$month = (int) $now->format( 'n' );
		}

		$month_data = ITMMS_Prayer_Times::for_month( $year, $month, $settings );
		$days = [];
		foreach ( (array) ( $month_data['days'] ?? [] ) as $day ) {
			if ( is_array( $day ) ) {
				$days[] = ITMMS_SalahAPI::headless_day( $day );
			}
		}

		return rest_ensure_response(
			[
				'year'     => $year,
				'month'    => $month,
				'label'    => (string) ( $month_data['label'] ?? '' ),
				'timezone' => (string) ( $month_data['timezone'] ?? '' ),
				'days'     => $days,
				'meta'     => is_array( $month_data['meta'] ?? null ) ? $month_data['meta'] : [],
			]
		);
	}

	/**
	 * Public SalahAPI 1.0 document.
	 */
	public function get_salahapi_document(): WP_REST_Response {
		return rest_ensure_response( ITMMS_SalahAPI::document() );
	}

	/**
	 * Public SalahAPI CSV feed (fromDate / toDate query params).
	 */
	public function get_salahapi_csv( WP_REST_Request $request ) {
		$settings = ITMMS_Settings::get_all();
		try {
			$timezone = new DateTimeZone( (string) ( $settings['timezone'] ?? wp_timezone_string() ) );
		} catch ( Exception $e ) {
			$timezone = wp_timezone();
		}
		$now = new DateTimeImmutable( 'now', $timezone );

		$from = (string) $request->get_param( 'fromDate' );
		$to = (string) $request->get_param( 'toDate' );
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $from ) ) {
			$from = $now->format( 'Y-m-01' );
		}
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $to ) ) {
			$to = $now->modify( 'last day of this month' )->format( 'Y-m-d' );
		}

		$csv = ITMMS_SalahAPI::csv( $from, $to );
		$response = new WP_REST_Response( $csv );
		$response->header( 'Content-Type', 'text/csv; charset=utf-8' );
		$response->header( 'Content-Disposition', 'inline; filename="masjidos-salahapi.csv"' );
		return $response;
	}

	public function get_prayer_timetable(): WP_REST_Response {
		return rest_ensure_response(
			[
				'summary' => ITMMS_Prayer_Timetable::summary(),
			]
		);
	}

	public function import_prayer_timetable( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$params  = $request->get_json_params();
		$csv     = isset( $params['csv'] ) ? (string) $params['csv'] : '';
		$mode    = isset( $params['mode'] ) ? sanitize_key( (string) $params['mode'] ) : 'merge';
		$dry_run = ! empty( $params['dry_run'] );
		if ( ! in_array( $mode, [ 'merge', 'replace' ], true ) ) {
			$mode = 'merge';
		}

		if ( '' === trim( $csv ) ) {
			return new WP_Error( 'itmms_empty_csv', __( 'CSV content is required.', 'masjidos' ), [ 'status' => 400 ] );
		}

		if ( strlen( $csv ) > 2 * 1024 * 1024 ) {
			return new WP_Error(
				'itmms_csv_too_large',
				__( 'CSV file is too large. Import one year at a time (about 365 rows).', 'masjidos' ),
				[ 'status' => 413 ]
			);
		}

		$result = ITMMS_Prayer_Timetable::import_csv( $csv, $mode, $dry_run );
		if ( empty( $result['success'] ) ) {
			return new WP_Error(
				'itmms_csv_import_failed',
				$dry_run
					? __( 'CSV validation found no valid rows.', 'masjidos' )
					: __( 'CSV import could not be completed.', 'masjidos' ),
				[
					'status' => 400,
					'data'   => $result,
				]
			);
		}

		return rest_ensure_response( $result );
	}

	public function export_prayer_timetable( WP_REST_Request $request ) {
		$source = sanitize_key( (string) $request->get_param( 'source' ) );
		$year = (int) $request->get_param( 'year' );
		if ( 'calculated' === $source ) {
			if ( $year < 1970 || $year > 2099 ) {
				$year = (int) gmdate( 'Y' );
			}
			$csv = ITMMS_Prayer_Timetable::export_calculated_year_csv( $year );
			$filename = sprintf( 'masjidos-calculated-%d.csv', $year );
		} else {
			$filter_year = ( $year >= 1970 && $year <= 2099 ) ? $year : null;
			$csv         = ITMMS_Prayer_Timetable::export_csv( $filter_year );
			$filename    = $filter_year
				? sprintf( 'masjidos-prayer-timetable-%d.csv', $filter_year )
				: 'masjidos-prayer-timetable.csv';
		}

		$response = new WP_REST_Response( $csv );
		$response->header( 'Content-Type', 'text/csv; charset=utf-8' );
		$response->header( 'Content-Disposition', 'attachment; filename="' . $filename . '"' );
		return $response;
	}

	public function sample_prayer_timetable() {
		$csv = ITMMS_Prayer_Timetable::sample_csv();
		$response = new WP_REST_Response( $csv );
		$response->header( 'Content-Type', 'text/csv; charset=utf-8' );
		$response->header( 'Content-Disposition', 'attachment; filename="masjidos-prayer-timetable-sample.csv"' );
		return $response;
	}

	public function clear_prayer_timetable( WP_REST_Request $request ): WP_REST_Response {
		$year = (int) $request->get_param( 'year' );
		if ( $year >= 1970 && $year <= 2099 ) {
			return rest_ensure_response( ITMMS_Prayer_Timetable::clear_year( $year ) );
		}

		ITMMS_Prayer_Timetable::clear();
		return rest_ensure_response(
			[
				'success' => true,
				'summary' => ITMMS_Prayer_Timetable::summary(),
			]
		);
	}

	/**
	 * Return a public prayer times widget for the Features preview modal.
	 */
	public function get_prayer_widget( WP_REST_Request $request ): WP_REST_Response {
		$html = ITMMS_Public::get_instance()->render_prayer_times_shortcode(
			[
				'design'   => sanitize_key( (string) $request->get_param( 'design' ) ),
				'language' => sanitize_key( (string) $request->get_param( 'language' ) ),
				'qibla'    => $this->yes_no_param( $request, 'qibla', 'yes' ),
				'meta'     => $this->yes_no_param( $request, 'meta', 'yes' ),
				'iqamah'   => $this->yes_no_param( $request, 'iqamah', 'yes' ),
				'title'    => $this->short_text_param( $request, 'title' ),
			]
		);

		return rest_ensure_response( [ 'html' => $html ] );
	}

	/**
	 * Return a public monthly timetable widget for reload-free navigation.
	 */
	public function get_monthly_prayer_widget( WP_REST_Request $request ): WP_REST_Response {
		$month = absint( $request->get_param( 'month' ) ) ?: (int) wp_date( 'n' );
		$year = absint( $request->get_param( 'year' ) ) ?: (int) wp_date( 'Y' );
		$month = max( 1, min( 12, $month ) );
		$year = max( 1970, min( 2099, $year ) );

		$html = ITMMS_Public::get_instance()->render_monthly_prayer_times_shortcode(
			[
				'month'      => (string) $month,
				'year'       => (string) $year,
				'design'     => sanitize_key( (string) $request->get_param( 'design' ) ),
				'language'   => sanitize_key( (string) $request->get_param( 'language' ) ),
				'iqamah'     => 'yes' === strtolower( (string) $request->get_param( 'iqamah' ) ) ? 'yes' : 'no',
				'extras'     => 'yes' === strtolower( (string) $request->get_param( 'extras' ) ) ? 'yes' : 'no',
				'navigation' => 'yes',
				'title'      => wp_html_excerpt( sanitize_text_field( (string) $request->get_param( 'title' ) ), 120, '' ),
			]
		);

		return rest_ensure_response( [ 'html' => $html ] );
	}

	/**
	 * Return a public Jumuah widget for the Features preview modal.
	 */
	public function get_jumuah_widget( WP_REST_Request $request ): WP_REST_Response {
		$html = ITMMS_Public::get_instance()->render_jumuah_shortcode(
			[
				'design'   => sanitize_key( (string) $request->get_param( 'design' ) ),
				'language' => sanitize_key( (string) $request->get_param( 'language' ) ),
				'meta'     => $this->yes_no_param( $request, 'meta', 'yes' ),
				'title'    => $this->short_text_param( $request, 'title' ),
			]
		);

		return rest_ensure_response( [ 'html' => $html ] );
	}

	/**
	 * Return a public announcements widget for the Features preview modal.
	 */
	public function get_announcements_widget( WP_REST_Request $request ): WP_REST_Response {
		$html = ITMMS_Public::get_instance()->render_announcements_shortcode(
			[
				'design'   => sanitize_key( (string) $request->get_param( 'design' ) ),
				'language' => sanitize_key( (string) $request->get_param( 'language' ) ),
				'limit'    => (string) max( 1, min( 20, absint( $request->get_param( 'limit' ) ) ?: 5 ) ),
				'title'    => $this->short_text_param( $request, 'title' ),
			]
		);

		return rest_ensure_response( [ 'html' => $html ] );
	}

	/**
	 * Return a public events widget for the Features preview modal.
	 */
	public function get_events_widget( WP_REST_Request $request ): WP_REST_Response {
		$html = ITMMS_Public::get_instance()->render_events_shortcode(
			[
				'language' => sanitize_key( (string) $request->get_param( 'language' ) ),
				'limit'    => (string) max( 1, min( 20, absint( $request->get_param( 'limit' ) ) ?: 5 ) ),
				'title'    => $this->short_text_param( $request, 'title' ),
			]
		);

		return rest_ensure_response( [ 'html' => $html ] );
	}

	/**
	 * Return a public Duas & Azkar widget for the Features preview modal.
	 */
	public function get_duas_azkar_widget( WP_REST_Request $request ): WP_REST_Response {
		$html = ITMMS_Public::get_instance()->render_duas_azkar_shortcode(
			[
				'design'   => sanitize_key( (string) $request->get_param( 'design' ) ),
				'language' => sanitize_key( (string) $request->get_param( 'language' ) ),
				'category' => sanitize_key( (string) $request->get_param( 'category' ) ),
				'limit'    => (string) max( 1, min( 12, absint( $request->get_param( 'limit' ) ) ?: 4 ) ),
				'title'    => $this->short_text_param( $request, 'title' ),
				'counter'  => sanitize_key( (string) $request->get_param( 'counter' ) ) ?: 'yes',
				'share'    => sanitize_key( (string) $request->get_param( 'share' ) ) ?: 'yes',
				'audio'    => sanitize_key( (string) $request->get_param( 'audio' ) ) ?: 'yes',
			]
		);

		return rest_ensure_response( [ 'html' => $html ] );
	}

	/**
	 * Return a public calendar widget for reload-free navigation.
	 */
	public function get_calendar_widget( WP_REST_Request $request ): WP_REST_Response {
		$month = absint( $request->get_param( 'month' ) ) ?: (int) wp_date( 'n' );
		$year = absint( $request->get_param( 'year' ) ) ?: (int) wp_date( 'Y' );
		$month = max( 1, min( 12, $month ) );
		$year = max( 1970, min( 2099, $year ) );

		$html = ITMMS_Public::get_instance()->render_islamic_calendar_shortcode(
			[
				'month'    => $month,
				'year'     => $year,
				'language' => sanitize_key( $request->get_param( 'language' ) ?: 'en' ),
				'title'    => sanitize_text_field( $request->get_param( 'title' ) ?: '' ),
			]
		);

		return rest_ensure_response( [ 'html' => $html ] );
	}

	public function get_announcements(): WP_REST_Response {
		return rest_ensure_response( [ 'announcements' => ITMMS_Announcements::all() ] );
	}

	public function get_public_announcements( WP_REST_Request $request ): WP_REST_Response {
		$settings = ITMMS_Settings::get_all();
		if ( empty( $settings['modules']['announcements'] ) ) {
			return rest_ensure_response( [ 'announcements' => [] ] );
		}

		$limit = max( 1, min( 50, absint( $request->get_param( 'limit' ) ) ?: 10 ) );
		$type = sanitize_key( (string) $request->get_param( 'type' ) );
		$announcements = array_map(
			static function ( array $notice ): array {
				return [
					'id'                => (int) $notice['id'],
					'title'             => (string) $notice['title'],
					'content'           => (string) $notice['content'],
					'announcement_type' => (string) $notice['announcement_type'],
					'priority'          => (int) $notice['priority'],
					'start_date'        => (string) $notice['start_date'],
					'end_date'          => (string) $notice['end_date'],
				];
			},
			ITMMS_Announcements::active( $limit, $type )
		);

		return rest_ensure_response( [ 'announcements' => $announcements ] );
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_announcement( WP_REST_Request $request ) {
		$notice = ITMMS_Announcements::create( $request->get_json_params() ?: [] );
		return is_wp_error( $notice ) ? $notice : new WP_REST_Response( [ 'announcement' => $notice ], 201 );
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_announcement( WP_REST_Request $request ) {
		$notice = ITMMS_Announcements::update( absint( $request['id'] ), $request->get_json_params() ?: [] );
		return is_wp_error( $notice ) ? $notice : rest_ensure_response( [ 'announcement' => $notice ] );
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_announcement( WP_REST_Request $request ) {
		$id = absint( $request['id'] );
		if ( ! ITMMS_Announcements::find( $id ) ) {
			return new WP_Error( 'itmms_notice_missing', __( 'Notice not found.', 'masjidos' ), [ 'status' => 404 ] );
		}

		if ( ! ITMMS_Announcements::delete( $id ) ) {
			return new WP_Error( 'itmms_notice_delete', __( 'The notice could not be deleted.', 'masjidos' ), [ 'status' => 500 ] );
		}

		return rest_ensure_response( [ 'deleted' => true, 'id' => $id ] );
	}

	public function get_events(): WP_REST_Response {
		return rest_ensure_response( [ 'events' => ITMMS_Events::all() ] );
	}

	public function get_public_events( WP_REST_Request $request ): WP_REST_Response {
		$settings = ITMMS_Settings::get_all();
		if ( empty( $settings['modules']['events'] ) ) {
			return rest_ensure_response( [ 'events' => [] ] );
		}

		$limit = max( 1, min( 50, absint( $request->get_param( 'limit' ) ) ?: 10 ) );
		$events = array_map(
			static function ( array $event ): array {
				return [
					'id'          => (int) $event['id'],
					'title'       => (string) $event['title'],
					'description' => (string) $event['description'],
					'start_time'  => (string) $event['start_time'],
					'end_time'    => (string) $event['end_time'],
					'location'    => (string) $event['location'],
				];
			},
			ITMMS_Events::active( $limit )
		);

		return rest_ensure_response( [ 'events' => $events ] );
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_event( WP_REST_Request $request ) {
		$event = ITMMS_Events::create( $request->get_json_params() ?: [] );
		return is_wp_error( $event ) ? $event : new WP_REST_Response( [ 'event' => $event ], 201 );
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_event( WP_REST_Request $request ) {
		$event = ITMMS_Events::update( absint( $request['id'] ), $request->get_json_params() ?: [] );
		return is_wp_error( $event ) ? $event : rest_ensure_response( [ 'event' => $event ] );
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_event( WP_REST_Request $request ) {
		$id = absint( $request['id'] );
		if ( ! ITMMS_Events::find( $id ) ) {
			return new WP_Error( 'itmms_event_missing', __( 'Event not found.', 'masjidos' ), [ 'status' => 404 ] );
		}

		if ( ! ITMMS_Events::delete( $id ) ) {
			return new WP_Error( 'itmms_event_delete', __( 'The event could not be deleted.', 'masjidos' ), [ 'status' => 500 ] );
		}

		return rest_ensure_response( [ 'deleted' => true, 'id' => $id ] );
	}

	public function get_khutbahs(): WP_REST_Response {
		return rest_ensure_response(
			[
				'khutbahs'   => ITMMS_Khutbah::all(),
				'stats'      => ITMMS_Khutbah::stats(),
				'categories' => ITMMS_Khutbah::categories(),
			]
		);
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_khutbah( WP_REST_Request $request ) {
		$params = $request->get_json_params() ?: [];
		$item = ITMMS_Khutbah::create( $params );
		if ( is_wp_error( $item ) ) {
			return $item;
		}
		$similar = ITMMS_Khutbah::find_similar_topics( (string) ( $item['topic'] ?? '' ), 6, (int) ( $item['id'] ?? 0 ) );
		return new WP_REST_Response(
			[
				'khutbah'  => $item,
				'similar'  => $similar,
			],
			201
		);
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_khutbah( WP_REST_Request $request ) {
		$item = ITMMS_Khutbah::update( absint( $request['id'] ), $request->get_json_params() ?: [] );
		if ( is_wp_error( $item ) ) {
			return $item;
		}
		$similar = ITMMS_Khutbah::find_similar_topics( (string) ( $item['topic'] ?? '' ), 6, (int) ( $item['id'] ?? 0 ) );
		return rest_ensure_response(
			[
				'khutbah' => $item,
				'similar' => $similar,
			]
		);
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_khutbah( WP_REST_Request $request ) {
		$id = absint( $request['id'] );
		if ( ! ITMMS_Khutbah::find( $id ) ) {
			return new WP_Error( 'itmms_khutbah_missing', __( 'Khutbah not found.', 'masjidos' ), [ 'status' => 404 ] );
		}

		if ( ! ITMMS_Khutbah::delete( $id ) ) {
			return new WP_Error( 'itmms_khutbah_delete', __( 'The khutbah could not be deleted.', 'masjidos' ), [ 'status' => 500 ] );
		}

		return rest_ensure_response( [ 'deleted' => true, 'id' => $id ] );
	}

	public function get_minbar_dashboard(): WP_REST_Response {
		return rest_ensure_response( ITMMS_Minbar::dashboard() );
	}

	public function get_minbar_profiles(): WP_REST_Response {
		return rest_ensure_response( [ 'profiles' => ITMMS_Minbar::profiles_all() ] );
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_minbar_profile( WP_REST_Request $request ) {
		$item = ITMMS_Minbar::profile_create( $request->get_json_params() ?: [] );
		return is_wp_error( $item ) ? $item : new WP_REST_Response( [ 'profile' => $item ], 201 );
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_minbar_profile( WP_REST_Request $request ) {
		$item = ITMMS_Minbar::profile_update( absint( $request['id'] ), $request->get_json_params() ?: [] );
		return is_wp_error( $item ) ? $item : rest_ensure_response( [ 'profile' => $item ] );
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_minbar_profile( WP_REST_Request $request ) {
		$id = absint( $request['id'] );
		if ( ! ITMMS_Minbar::profile_find( $id ) ) {
			return new WP_Error( 'itmms_profile_missing', __( 'Khatib profile not found.', 'masjidos' ), [ 'status' => 404 ] );
		}
		if ( ! ITMMS_Minbar::profile_delete( $id ) ) {
			return new WP_Error( 'itmms_profile_delete', __( 'Could not delete profile.', 'masjidos' ), [ 'status' => 500 ] );
		}
		return rest_ensure_response( [ 'deleted' => true, 'id' => $id ] );
	}

	public function get_minbar_schedule(): WP_REST_Response {
		return rest_ensure_response(
			[
				'schedule' => ITMMS_Minbar::schedule_all(),
				'upcoming' => ITMMS_Minbar::schedule_upcoming( 12 ),
			]
		);
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_minbar_schedule( WP_REST_Request $request ) {
		$item = ITMMS_Minbar::schedule_create( $request->get_json_params() ?: [] );
		return is_wp_error( $item ) ? $item : new WP_REST_Response( [ 'entry' => $item ], 201 );
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_minbar_schedule( WP_REST_Request $request ) {
		$item = ITMMS_Minbar::schedule_update( absint( $request['id'] ), $request->get_json_params() ?: [] );
		return is_wp_error( $item ) ? $item : rest_ensure_response( [ 'entry' => $item ] );
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_minbar_schedule( WP_REST_Request $request ) {
		$id = absint( $request['id'] );
		if ( ! ITMMS_Minbar::schedule_find( $id ) ) {
			return new WP_Error( 'itmms_schedule_missing', __( 'Schedule entry not found.', 'masjidos' ), [ 'status' => 404 ] );
		}
		if ( ! ITMMS_Minbar::schedule_delete( $id ) ) {
			return new WP_Error( 'itmms_schedule_delete', __( 'Could not delete schedule entry.', 'masjidos' ), [ 'status' => 500 ] );
		}
		return rest_ensure_response( [ 'deleted' => true, 'id' => $id ] );
	}

	public function get_minbar_plans(): WP_REST_Response {
		return rest_ensure_response( [ 'plans' => ITMMS_Minbar::get_plans() ] );
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function save_minbar_plan( WP_REST_Request $request ) {
		$item = ITMMS_Minbar::plan_save( $request->get_json_params() ?: [] );
		return is_wp_error( $item ) ? $item : rest_ensure_response( [ 'plan' => $item, 'plans' => ITMMS_Minbar::get_plans() ] );
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_minbar_plan( WP_REST_Request $request ) {
		$id = sanitize_key( (string) $request['id'] );
		ITMMS_Minbar::plan_delete( $id );
		return rest_ensure_response( [ 'deleted' => true, 'id' => $id, 'plans' => ITMMS_Minbar::get_plans() ] );
	}

	public function search_minbar_references( WP_REST_Request $request ): WP_REST_Response {
		$q = sanitize_text_field( (string) $request->get_param( 'q' ) );
		$type = sanitize_key( (string) $request->get_param( 'type' ) );
		return rest_ensure_response(
			[
				'results'    => ITMMS_Minbar::search_references( $q, $type ?: 'all' ),
				'bookmarks'  => ITMMS_Minbar::get_bookmarks(),
			]
		);
	}

	public function get_minbar_bookmarks(): WP_REST_Response {
		return rest_ensure_response( [ 'bookmarks' => ITMMS_Minbar::get_bookmarks() ] );
	}

	public function add_minbar_bookmark( WP_REST_Request $request ): WP_REST_Response {
		$bookmarks = ITMMS_Minbar::bookmark_add( $request->get_json_params() ?: [] );
		return rest_ensure_response( [ 'bookmarks' => $bookmarks ] );
	}

	/**
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_minbar_bookmark( WP_REST_Request $request ) {
		$id = sanitize_key( (string) $request['id'] );
		$bookmarks = ITMMS_Minbar::bookmark_remove( $id );
		return rest_ensure_response( [ 'bookmarks' => $bookmarks, 'deleted' => true ] );
	}

}
