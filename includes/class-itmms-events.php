<?php
/**
 * Event repository and query helper.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles database CRUD operations for MasjidOS events.
 */
final class ITMMS_Events {

	private const CACHE_GROUP = 'masjidos_events';

	/**
	 * Return all events.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function all( int $limit = 100 ): array {
		global $wpdb;

		$limit = max( 1, min( 200, $limit ) );
		$cache_key = self::cache_key( 'all:' . $limit );
		$found = false;
		$cached = wp_cache_get( $cache_key, self::CACHE_GROUP, false, $found );
		if ( $found && is_array( $cached ) ) {
			return $cached;
		}

		$table = esc_sql( self::table() );
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- WordPress 6.0 lacks %i; the internal table name is escaped above.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Cached query against the plugin's own table.
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} ORDER BY start_time DESC, id DESC LIMIT %d",
				$limit
			),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$events = array_map( [ self::class, 'normalize' ], is_array( $rows ) ? $rows : [] );

		// Merge Islamic auto-events
		$current_year = (int) gmdate( 'Y' );
		$islamic = self::get_islamic_events( $current_year );
		$islamic = array_merge( $islamic, self::get_islamic_events( $current_year + 1 ) );
		$islamic = array_merge( $islamic, self::get_islamic_events( $current_year - 1 ) );

		$now = self::now();
		foreach ( $islamic as $ie ) {
			if ( $ie['start_time'] > $now ) {
				$ie['status'] = 'upcoming';
			} elseif ( $ie['end_time'] >= $now && $ie['start_time'] <= $now ) {
				$ie['status'] = 'ongoing';
			} else {
				$ie['status'] = 'past';
			}
			$events[] = $ie;
		}

		// Merge Jumuah recurring events
		$jumuahs = self::get_recurring_jumuah_events( $current_year );
		$jumuahs = array_merge( $jumuahs, self::get_recurring_jumuah_events( $current_year + 1 ) );
		$jumuahs = array_merge( $jumuahs, self::get_recurring_jumuah_events( $current_year - 1 ) );

		foreach ( $jumuahs as $je ) {
			if ( $je['start_time'] > $now ) {
				$je['status'] = 'upcoming';
			} elseif ( $je['end_time'] >= $now && $je['start_time'] <= $now ) {
				$je['status'] = 'ongoing';
			} else {
				$je['status'] = 'past';
			}
			$events[] = $je;
		}

		// Sort all by start_time DESC
		usort( $events, static function( $a, $b ) {
			return strcmp( $b['start_time'], $a['start_time'] );
		} );

		$events = array_slice( $events, 0, $limit );
		wp_cache_set( $cache_key, $events, self::CACHE_GROUP, MINUTE_IN_SECONDS );

		return $events;
	}

	/**
	 * Return upcoming and ongoing events.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function active( int $limit = 10 ): array {
		global $wpdb;

		$limit = max( 1, min( 50, $limit ) );
		$cache_key = self::cache_key( 'active:' . $limit );
		$found = false;
		$cached = wp_cache_get( $cache_key, self::CACHE_GROUP, false, $found );
		if ( $found && is_array( $cached ) ) {
			return $cached;
		}

		$table = esc_sql( self::table() );
		$now = self::now();

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- WordPress 6.0 lacks %i; the internal table name is escaped.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Cached query against the plugin's own table.
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE start_time >= %s OR (end_time IS NOT NULL AND end_time >= %s) ORDER BY start_time ASC LIMIT %d",
				$now,
				$now,
				$limit
			),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$events = array_map( [ self::class, 'normalize' ], is_array( $rows ) ? $rows : [] );

		// Merge Islamic auto-events
		$current_year = (int) gmdate( 'Y' );
		$islamic = self::get_islamic_events( $current_year );
		$islamic = array_merge( $islamic, self::get_islamic_events( $current_year + 1 ) );

		foreach ( $islamic as $ie ) {
			if ( $ie['start_time'] > $now ) {
				$ie['status'] = 'upcoming';
			} elseif ( $ie['end_time'] >= $now && $ie['start_time'] <= $now ) {
				$ie['status'] = 'ongoing';
			} else {
				continue;
			}
			$events[] = $ie;
		}

		// Merge Jumuah recurring events
		$jumuahs = self::get_recurring_jumuah_events( $current_year );
		$jumuahs = array_merge( $jumuahs, self::get_recurring_jumuah_events( $current_year + 1 ) );

		foreach ( $jumuahs as $je ) {
			if ( $je['start_time'] > $now ) {
				$je['status'] = 'upcoming';
			} elseif ( $je['end_time'] >= $now && $je['start_time'] <= $now ) {
				$je['status'] = 'ongoing';
			} else {
				continue;
			}
			$events[] = $je;
		}

		// Sort by start_time ASC
		usort( $events, static function( $a, $b ) {
			return strcmp( $a['start_time'], $b['start_time'] );
		} );

		$events = array_slice( $events, 0, $limit );
		wp_cache_set( $cache_key, $events, self::CACHE_GROUP, MINUTE_IN_SECONDS );

		return $events;
	}

	/**
	 * Return the count of upcoming and ongoing events.
	 */
	public static function count_active(): int {
		global $wpdb;

		$cache_key = self::cache_key( 'count-active' );
		$found = false;
		$cached = wp_cache_get( $cache_key, self::CACHE_GROUP, false, $found );
		if ( $found ) {
			return (int) $cached;
		}

		$now = self::now();
		$table = esc_sql( self::table() );
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- WordPress 6.0 lacks %i; the internal table name is escaped.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Cached query against the plugin's own table.
		$count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE start_time >= %s OR (end_time IS NOT NULL AND end_time >= %s)",
				$now,
				$now
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		wp_cache_set( $cache_key, $count, self::CACHE_GROUP, MINUTE_IN_SECONDS );

		return $count;
	}

	/**
	 * Find an event by ID.
	 *
	 * @return array<string,mixed>|null
	 */
	public static function find( int $id ): ?array {
		global $wpdb;

		$cache_key = self::cache_key( 'find:' . $id );
		$found = false;
		$cached = wp_cache_get( $cache_key, self::CACHE_GROUP, false, $found );
		if ( $found ) {
			return is_array( $cached ) ? $cached : null;
		}

		$table = esc_sql( self::table() );
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- WordPress 6.0 lacks %i; the internal table name is escaped.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Query against the plugin's own table.
		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$event = is_array( $row ) ? self::normalize( $row ) : null;
		wp_cache_set( $cache_key, $event, self::CACHE_GROUP, MINUTE_IN_SECONDS );

		return $event;
	}

	/**
	 * Create a new event.
	 *
	 * @param array<string,mixed> $input Raw input values.
	 * @return array<string,mixed>|WP_Error
	 */
	public static function create( array $input ) {
		global $wpdb;

		$data = self::sanitize( $input );
		if ( '' === $data['title'] ) {
			return new WP_Error( 'itmms_event_title', __( 'Event title is required.', 'masjidos' ), [ 'status' => 400 ] );
		}
		if ( empty( $data['start_time'] ) ) {
			return new WP_Error( 'itmms_event_start_time', __( 'Event start time is required.', 'masjidos' ), [ 'status' => 400 ] );
		}
		if ( ! empty( $data['end_time'] ) && $data['end_time'] < $data['start_time'] ) {
			return new WP_Error( 'itmms_event_dates', __( 'The event end time must be after its start time.', 'masjidos' ), [ 'status' => 400 ] );
		}

		$data['created_by'] = get_current_user_id();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table write.
		$inserted = $wpdb->insert(
			self::table(),
			$data,
			[ '%s', '%s', '%s', '%s', '%s', '%s', '%d' ]
		);

		if ( false === $inserted ) {
			return new WP_Error( 'itmms_event_create', __( 'The event could not be created.', 'masjidos' ), [ 'status' => 500 ] );
		}

		self::invalidate_cache();
		return self::find( (int) $wpdb->insert_id );
	}

	/**
	 * Update an existing event.
	 *
	 * @param array<string,mixed> $input Raw input values.
	 * @return array<string,mixed>|WP_Error
	 */
	public static function update( int $id, array $input ) {
		global $wpdb;

		if ( ! self::find( $id ) ) {
			return new WP_Error( 'itmms_event_missing', __( 'Event not found.', 'masjidos' ), [ 'status' => 404 ] );
		}

		$data = self::sanitize( $input );
		if ( '' === $data['title'] ) {
			return new WP_Error( 'itmms_event_title', __( 'Event title is required.', 'masjidos' ), [ 'status' => 400 ] );
		}
		if ( empty( $data['start_time'] ) ) {
			return new WP_Error( 'itmms_event_start_time', __( 'Event start time is required.', 'masjidos' ), [ 'status' => 400 ] );
		}
		if ( ! empty( $data['end_time'] ) && $data['end_time'] < $data['start_time'] ) {
			return new WP_Error( 'itmms_event_dates', __( 'The event end time must be after its start time.', 'masjidos' ), [ 'status' => 400 ] );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table write.
		$updated = $wpdb->update(
			self::table(),
			$data,
			[ 'id' => $id ],
			[ '%s', '%s', '%s', '%s', '%s', '%s' ],
			[ '%d' ]
		);

		if ( false === $updated ) {
			return new WP_Error( 'itmms_event_update', __( 'The event could not be updated.', 'masjidos' ), [ 'status' => 500 ] );
		}

		self::invalidate_cache();
		return self::find( $id );
	}

	/**
	 * Delete an event.
	 */
	public static function delete( int $id ): bool {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table write.
		$deleted = $wpdb->delete( self::table(), [ 'id' => $id ], [ '%d' ] );
		if ( false !== $deleted ) {
			self::invalidate_cache();
		}

		return false !== $deleted;
	}

	private static function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'itmms_events';
	}

	private static function cache_key( string $key ): string {
		$last_changed = wp_cache_get( 'last_changed', self::CACHE_GROUP );
		if ( false === $last_changed ) {
			$last_changed = microtime();
			wp_cache_set( 'last_changed', $last_changed, self::CACHE_GROUP );
		}

		return $key . ':' . $last_changed;
	}

	private static function invalidate_cache(): void {
		wp_cache_set( 'last_changed', microtime(), self::CACHE_GROUP );
	}

	/**
	 * Sanitize event input fields.
	 *
	 * @param array<string,mixed> $input Raw input.
	 * @return array<string,mixed>
	 */
	private static function sanitize( array $input ): array {
		$start = self::sanitize_datetime( $input['start_time'] ?? '', self::now() );
		$end = self::sanitize_datetime( $input['end_time'] ?? '', '' );

		return [
			'title'       => sanitize_text_field( wp_unslash( (string) ( $input['title'] ?? '' ) ) ),
			'description' => sanitize_textarea_field( wp_unslash( (string) ( $input['description'] ?? '' ) ) ),
			'start_time'  => $start,
			'end_time'    => '' === $end ? null : $end,
			'location'    => sanitize_text_field( wp_unslash( (string) ( $input['location'] ?? '' ) ) ),
			'image_url'   => esc_url_raw( wp_unslash( (string) ( $input['image_url'] ?? '' ) ) ),
		];
	}

	private static function sanitize_datetime( $value, string $fallback ): string {
		$value = sanitize_text_field( wp_unslash( (string) $value ) );
		if ( '' === $value ) {
			return $fallback;
		}

		$value = str_replace( 'T', ' ', $value );
		$date = DateTimeImmutable::createFromFormat( 'Y-m-d H:i', substr( $value, 0, 16 ), self::timezone() );
		return $date instanceof DateTimeImmutable ? $date->format( 'Y-m-d H:i:s' ) : $fallback;
	}

	/**
	 * Normalize event row fields.
	 *
	 * @param array<string,mixed> $row Raw database row.
	 * @return array<string,mixed>
	 */
	private static function normalize( array $row ): array {
		$now = self::now();
		$status = 'past';

		if ( (string) $row['start_time'] > $now ) {
			$status = 'upcoming';
		} elseif ( ! empty( $row['end_time'] ) && (string) $row['start_time'] <= $now && (string) $row['end_time'] >= $now ) {
			$status = 'ongoing';
		} elseif ( empty( $row['end_time'] ) && substr( (string) $row['start_time'], 0, 10 ) === substr( $now, 0, 10 ) ) {
			$status = 'ongoing';
		}

		return [
			'id'          => (int) $row['id'],
			'title'       => (string) $row['title'],
			'description' => (string) ( $row['description'] ?? '' ),
			'start_time'  => (string) $row['start_time'],
			'end_time'    => empty( $row['end_time'] ) ? '' : (string) $row['end_time'],
			'location'    => (string) ( $row['location'] ?? '' ),
			'image_url'   => (string) ( $row['image_url'] ?? '' ),
			'status'      => $status,
			'created_by'  => (int) ( $row['created_by'] ?? 0 ),
			'created_at'  => (string) ( $row['created_at'] ?? '' ),
		];
	}

	private static function now(): string {
		return ( new DateTimeImmutable( 'now', self::timezone() ) )->format( 'Y-m-d H:i:s' );
	}

	private static function timezone(): DateTimeZone {
		$settings = ITMMS_Settings::get_all();
		try {
			return new DateTimeZone( (string) ( $settings['timezone'] ?? wp_timezone_string() ) );
		} catch ( Exception $e ) {
			return wp_timezone();
		}
	}

	/**
	 * Get Islamic holy days for a specific Gregorian year.
	 *
	 * @param int $gregorian_year Year to fetch events for.
	 * @return array<int,array<string,mixed>>
	 */
	public static function get_islamic_events( int $gregorian_year ): array {
		$events = [];

		$start_hijri = 1440;
		$end_hijri = 1460;
		if ( class_exists( 'ITMMS_Hijri' ) ) {
			try {
				$settings = ITMMS_Settings::get_all();
				$adj = isset( $settings['hijri_adjustment'] ) ? (int) $settings['hijri_adjustment'] : 0;
				$tz = self::timezone();

				$jan_1 = new DateTimeImmutable( sprintf( '%d-01-01 00:00:00', $gregorian_year ), $tz );
				$dec_31 = new DateTimeImmutable( sprintf( '%d-12-31 23:59:59', $gregorian_year ), $tz );

				$start_hijri_data = ITMMS_Hijri::for_date( $jan_1, $adj );
				$end_hijri_data = ITMMS_Hijri::for_date( $dec_31, $adj );

				$start_hijri = (int) $start_hijri_data['year'];
				$end_hijri = (int) $end_hijri_data['year'];
			} catch ( Exception $e ) {
				$start_hijri = $gregorian_year - 579;
				$end_hijri = $start_hijri + 1;
			}
		}

		$holy_days = [
			[ 1, 10, 'Ashura (10 Muharram)', 'আশুরা (১০ মুহাররম)', 'عاشوراء (١٠ محرم)' ],
			[ 3, 12, 'Mawlid an-Nabi ﷺ (12 Rabi al-awwal)', 'ঈদে মিলাদুন্নবী ﷺ (১২ রবিউল আউয়াল)', 'المولد النبوي ﷺ (١٢ ربيع الأول)' ],
			[ 8, 15, 'Shab-e-Barat (15 Shaban)', 'শবে বরাত (১৫ শাবান)', 'ليلة البراءة (١٥ شعبان)' ],
			[ 9, 27, 'Laylat al-Qadr (27 Ramadan)', 'শবে কদর (২৭ রমজান)', 'ليلة القدر (٢٧ رمضان)' ],
			[ 10, 1, 'Eid al-Fitr (1 Shawwal)', 'ঈদুল ফিতর (১ শাওয়াল)', 'عيد الفطر (١ شوال)' ],
			[ 12, 10, 'Eid al-Adha (10 Dhu al-Hijjah)', 'ঈদুল আযহা (১০ জিলহজ)', 'عيد الأضحى (١٠ ذو الحجة)' ],
		];

		$id_counter = 90000;
		$lang       = ITMMS_Settings::ui_language();
		if ( ! in_array( $lang, [ 'en', 'bn', 'ar' ], true ) ) {
			$lang = 'en';
		}

		for ( $hy = $start_hijri - 1; $hy <= $end_hijri + 1; $hy++ ) {
			foreach ( $holy_days as $day_data ) {
				$h_month = $day_data[0];
				$h_day = $day_data[1];

				$g_parts = ITMMS_Hijri::hijri_to_gregorian( $hy, $h_month, $h_day );

				if ( (int) $g_parts['year'] === $gregorian_year ) {
					$title = $day_data[2];
					if ( 'bn' === $lang ) {
						$title = $day_data[3];
					} elseif ( 'ar' === $lang ) {
						$title = $day_data[4];
					}

					$date_str = sprintf( '%04d-%02d-%02d', $g_parts['year'], $g_parts['month'], $g_parts['day'] );

					$events[] = [
						'id'          => $id_counter++,
						'title'       => $title,
						'description' => __( 'Islamic holy day automatically calculated from the Hijri calendar.', 'masjidos' ),
						'start_time'  => $date_str . ' 00:00:00',
						'end_time'    => $date_str . ' 23:59:59',
						'location'    => __( 'Mosque', 'masjidos' ),
						'image_url'   => '',
						'status'      => 'upcoming',
						'created_by'  => 0,
						'created_at'  => $date_str . ' 00:00:00',
						'is_islamic'  => true,
					];
				}
			}
		}

		return $events;
	}

	/**
	 * Get recurring Friday Jumuah events for a specific year.
	 *
	 * @param int $year Gregorian year.
	 * @return array<int,array<string,mixed>>
	 */
	public static function get_recurring_jumuah_events( int $year ): array {
		$events = [];
		$settings = ITMMS_Settings::get_all();

		if ( empty( $settings['jumuah']['enabled'] ) ) {
			return $events;
		}

		$khutbah_time = isset( $settings['jumuah']['khutbah_time'] ) ? $settings['jumuah']['khutbah_time'] : '13:00';
		$jamaat_time = isset( $settings['jumuah']['jamaat_time'] ) ? $settings['jumuah']['jamaat_time'] : '13:30';
		$topic = isset( $settings['jumuah']['topic'] ) ? $settings['jumuah']['topic'] : '';
		$khatib_name = $settings['jumuah']['khatib']['name'] ?? '';

		$tz = self::timezone();
		$start_date = new DateTime( sprintf( '%d-01-01', $year ), $tz );
		if ( '5' !== $start_date->format( 'w' ) ) {
			$start_date->modify( 'next friday' );
		}

		$id_counter = 80000;

		while ( (int) $start_date->format( 'Y' ) === $year ) {
			$date_str = $start_date->format( 'Y-m-d' );
			/* translators: %s: Khatib name */
			$title = $khatib_name ? sprintf( __( 'Jumuah Khutbah by %s', 'masjidos' ), $khatib_name ) : __( 'Friday Jumuah Prayer', 'masjidos' );
			if ( $topic ) {
				$title .= ': ' . $topic;
			}

			$events[] = [
				'id'          => $id_counter++,
				'title'       => $title,
				'description' => __( 'Weekly Friday congregational prayer and khutbah.', 'masjidos' ),
				'start_time'  => $date_str . ' ' . $khutbah_time . ':00',
				'end_time'    => $date_str . ' ' . $jamaat_time . ':00',
				'location'    => __( 'Mosque Main Hall', 'masjidos' ),
				'image_url'   => '',
				'status'      => 'upcoming',
				'created_by'  => 0,
				'created_at'  => $date_str . ' 00:00:00',
				'is_jumuah'   => true,
			];

			$start_date->modify( '+7 days' );
		}

		return $events;
	}
}
