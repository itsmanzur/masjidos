<?php
/**
 * ITMMS_REST_Widgets methods for ITMMS_REST.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * @package MasjidOS
 */
trait ITMMS_REST_Widgets {

	/**
	 * Public GET responses that may be cached briefly by browsers/CDNs.
	 *
	 * @param mixed $data Response data.
	 */
	private function public_cached_response( $data, int $max_age = 60 ): WP_REST_Response {
		$response = rest_ensure_response( $data );
		$response->header( 'Cache-Control', 'public, max-age=' . max( 0, $max_age ) );
		return $response;
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

		return $this->public_cached_response( [ 'html' => $html ], 60 );
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

		return $this->public_cached_response( [ 'html' => $html ], 60 );
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

		return $this->public_cached_response( [ 'html' => $html ], 60 );
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

		return $this->public_cached_response( [ 'html' => $html ], 30 );
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

		return $this->public_cached_response( [ 'html' => $html ], 60 );
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

		return $this->public_cached_response( [ 'html' => $html ], 120 );
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

		return $this->public_cached_response( [ 'html' => $html ], 60 );
	}

}
