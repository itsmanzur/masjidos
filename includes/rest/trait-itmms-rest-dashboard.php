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
	 * Compute the 5-step onboarding status & progress for MasjidOS Dashboard and WP Admin widget.
	 *
	 * @param array<string,mixed>|null $settings Optional settings array.
	 * @return array<string,mixed>
	 */
	public static function get_onboarding_status( ?array $settings = null ): array {
		if ( null === $settings ) {
			$settings = ITMMS_Settings::get_all();
		}

		$latitude      = (float) ( $settings['latitude'] ?? 0 );
		$longitude     = (float) ( $settings['longitude'] ?? 0 );
		$timezone_name = (string) ( $settings['timezone'] ?? wp_timezone_string() );
		$coords_ok     = abs( $latitude ) > 0.0001 || abs( $longitude ) > 0.0001;
		$timezone_ok   = ! in_array( $timezone_name, [ '', 'UTC', '+00:00', '-00:00' ], true );
		$location_done = $coords_ok && $timezone_ok;

		// Step 2: Iqamah & Jumuah times configured.
		$iqamah_done = (int) get_option( 'itmms_onboarding_iqamah_done', 0 ) === 1;
		if ( ! $iqamah_done ) {
			$iqamah_times = (array) ( $settings['iqamah_times'] ?? [] );
			foreach ( $iqamah_times as $val ) {
				if ( '' !== trim( (string) $val ) ) {
					$iqamah_done = true;
					break;
				}
			}
		}
		if ( ! $iqamah_done ) {
			$tt_summary  = ITMMS_Prayer_Timetable::summary();
			$iqamah_done = ! empty( $tt_summary['count'] );
		}

		// Step 3: Starter prayer page published.
		$page_id   = absint( get_option( 'itmms_starter_page_id', 0 ) );
		$page_url  = '';
		$edit_url  = '';
		$page_done = false;

		if ( $page_id > 0 ) {
			$post = get_post( $page_id );
			if ( $post && 'page' === $post->post_type && 'publish' === $post->post_status ) {
				$page_done = true;
				$page_url  = (string) get_permalink( $page_id );
				$edit_url  = (string) get_edit_post_link( $page_id, 'raw' );
			}
		}

		if ( ! $page_done ) {
			global $wpdb;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$found_id = (int) $wpdb->get_var(
				"SELECT ID FROM {$wpdb->posts}
				WHERE post_type = 'page' AND post_status = 'publish'
				AND (post_content LIKE '%[masjidos_prayer_times%' OR post_content LIKE '%wp:masjidos/prayer-times%')
				LIMIT 1"
			);
			if ( $found_id > 0 ) {
				$page_id   = $found_id;
				$page_done = true;
				$page_url  = (string) get_permalink( $page_id );
				$edit_url  = (string) get_edit_post_link( $page_id, 'raw' );
				update_option( 'itmms_starter_page_id', $page_id, false );
			}
		}

		// Step 4: First notice or khutbah published.
		$notices_count  = ITMMS_Announcements::count_active();
		$khutbahs_count = count( ITMMS_Khutbah::all( 1 ) );
		$content_done   = ( $notices_count > 0 || $khutbahs_count > 0 );

		// Step 5: Mosque TV display previewed.
		$tv_done = (int) get_option( 'itmms_tv_previewed', 0 ) === 1;

		$steps = [
			[
				'key'  => 'location',
				'done' => $location_done,
			],
			[
				'key'  => 'iqamah_jumuah',
				'done' => $iqamah_done,
			],
			[
				'key'  => 'starter_page',
				'done' => $page_done,
			],
			[
				'key'  => 'first_content',
				'done' => $content_done,
			],
			[
				'key'  => 'tv_preview',
				'done' => $tv_done,
			],
		];

		$completed = 0;
		foreach ( $steps as $step ) {
			if ( ! empty( $step['done'] ) ) {
				++$completed;
			}
		}
		$total   = count( $steps );
		$percent = $total > 0 ? (int) round( ( $completed / $total ) * 100 ) : 0;

		return [
			'completed'         => $completed,
			'total'             => $total,
			'percent'           => $percent,
			'dismissed'         => (int) get_option( 'itmms_onboarding_dismissed', 0 ) === 1,
			'starter_page_id'   => $page_id,
			'starter_page_url'  => $page_url,
			'starter_page_edit' => $edit_url,
			'tv_url'            => home_url( '/masjidos-display/' ),
			'steps'             => $steps,
		];
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
		$ui_language = ITMMS_Settings::ui_language();
		$hijri_adj   = (int) ( $settings['hijri_adjustment'] ?? 0 );
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

			$upcoming_hijri = ITMMS_Hijri::for_date( $date, $hijri_adj, $ui_language );
			$upcoming_days[] = [
				'date'    => (string) ( $row['date'] ?? $date->format( 'Y-m-d' ) ),
				'label'   => 'en' === $ui_language ? $date->format( 'D, M j' ) : date_i18n( 'D, M j', $date->getTimestamp() ),
				'hijri'   => (string) ( $upcoming_hijri['label'] ?? '' ),
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
			'settings'      => $settings,
			'stats'         => [
				'announcements' => ITMMS_Announcements::count_active(),
				'events'        => ITMMS_Events::count_active(),
			],
			'prayers'       => $prayer_times['prayers'],
			'next_prayer'   => $prayer_times['next_prayer'],
			'prayer_meta'   => $prayer_times['meta'],
			'hijri_date'    => ITMMS_Hijri::for_date( $day, $hijri_adj, $ui_language ),
			'announcements' => ITMMS_Announcements::active( 5 ),
			'events'        => ITMMS_Events::active( 5 ),
			'modules'       => ITMMS_Settings::module_definitions(),
			'upcoming_days' => $upcoming_days,
			'trust'         => [
				'source'            => (string) ( $prayer_times['meta']['prayer_source'] ?? $settings['prayer_source'] ?? 'local' ),
				'hijri_adjustment'  => (int) ( $settings['hijri_adjustment'] ?? 0 ),
				'coordinates_ok'    => $coords_ok,
				'timezone_ok'       => $timezone_ok,
				'timezone_mismatch' => $timezone_ok && $site_timezone && $timezone_name !== $site_timezone,
				'site_timezone'     => $site_timezone,
				'offsets'           => $settings['prayer_offsets'] ?? [],
			],
			'timetable'     => ITMMS_Prayer_Timetable::summary(),
			'onboarding'    => self::get_onboarding_status( $settings ),
			'pro'           => function_exists( 'masjidos_pro_localize' ) ? masjidos_pro_localize() : [ 'active' => false ],
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
	 * Create or return the one-click Starter Prayer Times page.
	 */
	public function create_starter_page(): WP_REST_Response {
		$status = self::get_onboarding_status();
		if ( ! empty( $status['starter_page_id'] ) && ! empty( $status['starter_page_url'] ) ) {
			return rest_ensure_response(
				[
					'created'    => false,
					'page_id'    => (int) $status['starter_page_id'],
					'page_url'   => (string) $status['starter_page_url'],
					'edit_url'   => (string) $status['starter_page_edit'],
					'onboarding' => $status,
				]
			);
		}

		$settings    = ITMMS_Settings::get_all();
		$masjid_name = (string) ( $settings['masjid_name'] ?? get_bloginfo( 'name' ) );
		$title       = sprintf(
			/* translators: %s: masjid name */
			__( '%s — Prayer Times & Masjid Board', 'masjidos' ),
			$masjid_name ?: 'MasjidOS'
		);

		$content = implode(
			"\n\n",
			[
				'<!-- wp:shortcode -->',
				'[masjidos_prayer_times iqamah="yes"]',
				'<!-- /wp:shortcode -->',
				'<!-- wp:shortcode -->',
				'[masjidos_jumuah]',
				'<!-- /wp:shortcode -->',
				'<!-- wp:shortcode -->',
				'[masjidos_announcements limit="5"]',
				'<!-- /wp:shortcode -->',
				'<!-- wp:shortcode -->',
				'[masjidos_monthly_prayer_times iqamah="yes"]',
				'<!-- /wp:shortcode -->',
			]
		);

		$page_id = wp_insert_post(
			[
				'post_title'   => $title,
				'post_name'    => 'prayer-times',
				'post_content' => $content,
				'post_status'  => 'publish',
				'post_type'    => 'page',
			],
			true
		);

		if ( is_wp_error( $page_id ) ) {
			return new WP_REST_Response(
				[
					'message' => $page_id->get_error_message(),
				],
				500
			);
		}

		update_option( 'itmms_starter_page_id', (int) $page_id, false );
		$updated = self::get_onboarding_status( $settings );

		return rest_ensure_response(
			[
				'created'    => true,
				'page_id'    => (int) $page_id,
				'page_url'   => (string) get_permalink( (int) $page_id ),
				'edit_url'   => (string) get_edit_post_link( (int) $page_id, 'raw' ),
				'onboarding' => $updated,
			]
		);
	}

	/**
	 * Dismiss or reopen the dashboard onboarding checklist widget.
	 */
	public function dismiss_onboarding( WP_REST_Request $request ): WP_REST_Response {
		$params    = $request->get_json_params() ?: [];
		$dismissed = isset( $params['dismissed'] ) ? (bool) $params['dismissed'] : true;
		update_option( 'itmms_onboarding_dismissed', $dismissed ? 1 : 0, false );

		return rest_ensure_response(
			[
				'onboarding' => self::get_onboarding_status(),
			]
		);
	}

	/**
	 * Mark TV display as previewed from admin quick action.
	 */
	public function mark_tv_previewed(): WP_REST_Response {
		update_option( 'itmms_tv_previewed', 1, false );

		return rest_ensure_response(
			[
				'onboarding' => self::get_onboarding_status(),
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
		$raw = $request->get_json_params() ?: [];
		if ( isset( $raw['iqamah_rules'] ) || isset( $raw['iqamah_times'] ) || isset( $raw['jumuah'] ) ) {
			update_option( 'itmms_onboarding_iqamah_done', 1, false );
		}
		$settings = ITMMS_Settings::update( $raw );
		ITMMS_Prayer_Times::flush_cache();

		return rest_ensure_response(
			[
				'settings'   => $settings,
				'modules'    => ITMMS_Settings::module_definitions(),
				'onboarding' => self::get_onboarding_status( $settings ),
			]
		);
	}

}
