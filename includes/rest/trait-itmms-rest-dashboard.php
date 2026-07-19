<?php
/**
 * ITMMS_REST_Dashboard methods for ITMMS_REST.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * @package MasjidOS
 */
trait ITMMS_REST_Dashboard {

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

}
