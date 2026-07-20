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

	use ITMMS_REST_Permissions;
	use ITMMS_REST_Response;
	use ITMMS_REST_Dashboard;
	use ITMMS_REST_Prayer;
	use ITMMS_REST_Widgets;
	use ITMMS_REST_Content;
	use ITMMS_REST_Minbar;

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
}
