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

	/**
	 * Return dashboard summary.
	 */
	public function get_dashboard(): WP_REST_Response {
		$settings = ITMMS_Settings::get_all();
		$prayer_times = ITMMS_Prayer_Times::today();

		return rest_ensure_response(
			[
				'settings'    => $settings,
				'stats'       => [
					'announcements' => ITMMS_Announcements::count_active(),
					'events'        => ITMMS_Events::count_active(),
				],
				'prayers'     => $prayer_times['prayers'],
				'next_prayer' => $prayer_times['next_prayer'],
				'prayer_meta' => $prayer_times['meta'],
				'hijri_date'  => ITMMS_Hijri::for_date( new DateTimeImmutable( 'now', new DateTimeZone( (string) ( $settings['timezone'] ?? wp_timezone_string() ) ) ), (int) ( $settings['hijri_adjustment'] ?? 0 ), 'en' ),
				'announcements' => ITMMS_Announcements::active( 5 ),
				'events'        => ITMMS_Events::active( 5 ),
				'modules'     => ITMMS_Settings::module_definitions(),
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
		return rest_ensure_response(
			[
				'settings' => ITMMS_Settings::update( $request->get_json_params() ?: [] ),
				'modules'  => ITMMS_Settings::module_definitions(),
			]
		);
	}

	/**
	 * Return today's calculated prayer times.
	 */
	public function get_prayer_times_today(): WP_REST_Response {
		return rest_ensure_response( ITMMS_Prayer_Times::today() );
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

}
