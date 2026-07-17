<?php
/**
 * Minbar module helpers: profiles, schedule, planner, references.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Minbar (Khatib Tools) domain helpers.
 */
final class ITMMS_Minbar {

	public const PLANS_OPTION = 'itmms_khutbah_plans';
	public const BOOKMARKS_OPTION = 'itmms_minbar_bookmarks';

	/**
	 * Dashboard payload for admin SPA.
	 *
	 * @return array<string,mixed>
	 */
	public static function dashboard(): array {
		$settings = ITMMS_Settings::get_all();
		$stats    = ITMMS_Khutbah::stats();
		$plans    = self::get_plans();
		$profiles = self::profiles_all();
		$upcoming = self::schedule_upcoming( 8 );
		$holy     = self::upcoming_islamic_days( 6 );

		$user = wp_get_current_user();
		$hijri = ITMMS_Hijri::for_date(
			new DateTimeImmutable( 'now', wp_timezone() ),
			(int) ( $settings['hijri_adjustment'] ?? 0 ),
			'bn'
		);

		$cta = null;
		if ( ! empty( $holy[0] ) ) {
			$cta = [
				'title'   => (string) ( $holy[0]['title'] ?? '' ),
				'days'    => (int) ( $holy[0]['days'] ?? 0 ),
				'message' => sprintf(
					/* translators: 1: holy day name, 2: days remaining */
					__( '%1$s is in %2$d days. Plan a related khutbah in the Planner.', 'masjidos' ),
					(string) ( $holy[0]['title'] ?? '' ),
					(int) ( $holy[0]['days'] ?? 0 )
				),
			];
		}

		return [
			'greeting_name' => $user->display_name ?: __( 'Khatib', 'masjidos' ),
			'hijri'         => $hijri,
			'stats'         => [
				'archive'    => $stats['total'],
				'this_month' => $stats['this_month'],
				'planned'    => count( $plans ),
				'references' => count( self::get_bookmarks() ),
				'khatibs'    => count(
					array_filter(
						$profiles,
						static function ( $row ): bool {
							return ! empty( $row['is_active'] );
						}
					)
				),
			],
			'recent'        => array_slice( ITMMS_Khutbah::all( 5 ), 0, 5 ),
			'holy_days'     => $holy,
			'cta'           => $cta,
			'schedule'      => $upcoming,
			'categories'    => ITMMS_Khutbah::categories(),
			'ai_available'  => (bool) apply_filters( 'masjidos_minbar_ai_available', false ),
		];
	}

	/**
	 * Upcoming Islamic holy days with day countdowns.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function upcoming_islamic_days( int $limit = 6 ): array {
		$year = (int) gmdate( 'Y' );
		$events = array_merge(
			ITMMS_Events::get_islamic_events( $year ),
			ITMMS_Events::get_islamic_events( $year + 1 )
		);
		$now = time();
		$out = [];

		foreach ( $events as $event ) {
			$start = (string) ( $event['start_time'] ?? '' );
			$ts    = strtotime( $start );
			if ( ! $ts || $ts < $now ) {
				continue;
			}
			$days = (int) ceil( ( $ts - $now ) / DAY_IN_SECONDS );
			$out[] = [
				'title' => (string) ( $event['title'] ?? '' ),
				'date'  => gmdate( 'Y-m-d', $ts ),
				'days'  => max( 0, $days ),
			];
		}

		usort(
			$out,
			static function ( $a, $b ): int {
				return ( $a['days'] ?? 0 ) <=> ( $b['days'] ?? 0 );
			}
		);

		return array_slice( $out, 0, max( 1, min( 12, $limit ) ) );
	}

	// ── Profiles ──────────────────────────────────────────────────────

	/**
	 * @return array<int,array<string,mixed>>
	 */
	public static function profiles_all(): array {
		global $wpdb;
		$table = $wpdb->prefix . 'itmms_khatib_profiles';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$rows = $wpdb->get_results(
			$wpdb->prepare( 'SELECT * FROM %i ORDER BY is_active DESC, name ASC', $table ),
			ARRAY_A
		);
		return array_map( [ self::class, 'normalize_profile' ], is_array( $rows ) ? $rows : [] );
	}

	/**
	 * @param array<string,mixed> $input Raw.
	 * @return array<string,mixed>|WP_Error
	 */
	public static function profile_create( array $input ) {
		global $wpdb;
		$data = self::sanitize_profile( $input );
		if ( is_wp_error( $data ) ) {
			return $data;
		}
		$formats = array_fill( 0, count( $data ), '%s' );
		$formats[0] = '%d'; // user_id
		$keys = array_keys( $data );
		$active_i = array_search( 'is_active', $keys, true );
		if ( false !== $active_i ) {
			$formats[ $active_i ] = '%d';
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$ok = $wpdb->insert(
			$wpdb->prefix . 'itmms_khatib_profiles',
			$data,
			$formats
		);
		if ( false === $ok ) {
			return new WP_Error( 'itmms_profile_create', __( 'Could not create khatib profile.', 'masjidos' ), [ 'status' => 500 ] );
		}
		return self::profile_find( (int) $wpdb->insert_id );
	}

	/**
	 * @param array<string,mixed> $input Raw.
	 * @return array<string,mixed>|WP_Error
	 */
	public static function profile_update( int $id, array $input ) {
		global $wpdb;
		if ( ! self::profile_find( $id ) ) {
			return new WP_Error( 'itmms_profile_missing', __( 'Khatib profile not found.', 'masjidos' ), [ 'status' => 404 ] );
		}
		$data = self::sanitize_profile( $input );
		if ( is_wp_error( $data ) ) {
			return $data;
		}
		$formats = array_fill( 0, count( $data ), '%s' );
		$formats[0] = '%d';
		$keys = array_keys( $data );
		$active_i = array_search( 'is_active', $keys, true );
		if ( false !== $active_i ) {
			$formats[ $active_i ] = '%d';
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$ok = $wpdb->update(
			$wpdb->prefix . 'itmms_khatib_profiles',
			$data,
			[ 'id' => $id ],
			$formats,
			[ '%d' ]
		);
		if ( false === $ok ) {
			return new WP_Error( 'itmms_profile_update', __( 'Could not update khatib profile.', 'masjidos' ), [ 'status' => 500 ] );
		}
		return self::profile_find( $id );
	}

	public static function profile_delete( int $id ): bool {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return false !== $wpdb->delete( $wpdb->prefix . 'itmms_khatib_profiles', [ 'id' => $id ], [ '%d' ] );
	}

	/**
	 * @return array<string,mixed>|null
	 */
	public static function profile_find( int $id ): ?array {
		global $wpdb;
		$table = $wpdb->prefix . 'itmms_khatib_profiles';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$row = $wpdb->get_row(
			$wpdb->prepare( 'SELECT * FROM %i WHERE id = %d', $table, $id ),
			ARRAY_A
		);
		return is_array( $row ) ? self::normalize_profile( $row ) : null;
	}

	/**
	 * @param array<string,mixed> $input Raw.
	 * @return array<string,mixed>
	 */
	private static function sanitize_profile( array $input ) {
		$name = sanitize_text_field( wp_unslash( (string) ( $input['name'] ?? '' ) ) );
		if ( '' === $name ) {
			$name = __( 'Untitled khatib', 'masjidos' );
		}

		$email_raw = sanitize_text_field( wp_unslash( (string) ( $input['email'] ?? '' ) ) );
		$email     = '' !== $email_raw ? sanitize_email( $email_raw ) : '';
		if ( '' === $email && '' !== $email_raw ) {
			// Keep typed value even if not a perfect email so the profile still saves.
			$email = $email_raw;
		}

		return [
			'user_id'        => isset( $input['user_id'] ) ? absint( $input['user_id'] ) : 0,
			'name'           => $name,
			'title'          => sanitize_text_field( wp_unslash( (string) ( $input['title'] ?? '' ) ) ),
			'phone'          => sanitize_text_field( wp_unslash( (string) ( $input['phone'] ?? '' ) ) ),
			'email'          => $email,
			'photo_url'      => self::sanitize_link( $input['photo_url'] ?? '' ),
			'expertise'      => sanitize_text_field( wp_unslash( (string) ( $input['expertise'] ?? '' ) ) ),
			'languages'      => sanitize_text_field( wp_unslash( (string) ( $input['languages'] ?? '' ) ) ),
			'location'       => sanitize_text_field( wp_unslash( (string) ( $input['location'] ?? '' ) ) ),
			'website'        => self::sanitize_link( $input['website'] ?? '' ),
			'facebook_url'   => self::sanitize_link( $input['facebook_url'] ?? '' ),
			'youtube_url'    => self::sanitize_link( $input['youtube_url'] ?? '' ),
			'instagram_url'  => self::sanitize_link( $input['instagram_url'] ?? '' ),
			'linkedin_url'   => self::sanitize_link( $input['linkedin_url'] ?? '' ),
			'x_url'          => self::sanitize_link( $input['x_url'] ?? '' ),
			'tiktok_url'     => self::sanitize_link( $input['tiktok_url'] ?? '' ),
			'bio'            => sanitize_textarea_field( wp_unslash( (string) ( $input['bio'] ?? '' ) ) ),
			'is_active'      => array_key_exists( 'is_active', $input ) ? (int) ( ! empty( $input['is_active'] ) ) : 1,
		];
	}

	/**
	 * Soft link sanitizer: keeps "#", hash links, and domains without scheme.
	 *
	 * @param mixed $value Raw link.
	 */
	private static function sanitize_link( $value ): string {
		$value = trim( wp_unslash( (string) $value ) );
		if ( '' === $value ) {
			return '';
		}

		if ( '#' === $value || 0 === strpos( $value, '#' ) ) {
			return sanitize_text_field( $value );
		}

		// Bare scheme is not a usable URL (avoids broken photo previews).
		if ( preg_match( '#^https?://$#i', $value ) ) {
			return '';
		}

		if ( ! preg_match( '#^https?://#i', $value ) && false !== strpos( $value, '.' ) ) {
			$value = 'https://' . ltrim( $value, '/' );
		}

		$url = esc_url_raw( $value );
		if ( '' !== $url ) {
			return $url;
		}

		return sanitize_text_field( $value );
	}

	/**
	 * @param array<string,mixed> $row DB row.
	 * @return array<string,mixed>
	 */
	private static function normalize_profile( array $row ): array {
		return [
			'id'             => (int) ( $row['id'] ?? 0 ),
			'user_id'        => (int) ( $row['user_id'] ?? 0 ),
			'name'           => (string) ( $row['name'] ?? '' ),
			'title'          => (string) ( $row['title'] ?? '' ),
			'phone'          => (string) ( $row['phone'] ?? '' ),
			'email'          => (string) ( $row['email'] ?? '' ),
			'photo_url'      => (string) ( $row['photo_url'] ?? '' ),
			'expertise'      => (string) ( $row['expertise'] ?? '' ),
			'languages'      => (string) ( $row['languages'] ?? '' ),
			'location'       => (string) ( $row['location'] ?? '' ),
			'website'        => (string) ( $row['website'] ?? '' ),
			'facebook_url'   => (string) ( $row['facebook_url'] ?? '' ),
			'youtube_url'    => (string) ( $row['youtube_url'] ?? '' ),
			'instagram_url'  => (string) ( $row['instagram_url'] ?? '' ),
			'linkedin_url'   => (string) ( $row['linkedin_url'] ?? '' ),
			'x_url'          => (string) ( $row['x_url'] ?? '' ),
			'tiktok_url'     => (string) ( $row['tiktok_url'] ?? '' ),
			'bio'            => (string) ( $row['bio'] ?? '' ),
			'is_active'      => ! empty( $row['is_active'] ),
		];
	}

	// ── Schedule ──────────────────────────────────────────────────────

	/**
	 * @return array<int,array<string,mixed>>
	 */
	public static function schedule_all( int $limit = 50 ): array {
		global $wpdb;
		$limit = max( 1, min( 200, $limit ) );
		$s = esc_sql( $wpdb->prefix . 'itmms_khatib_schedule' );
		$p = esc_sql( $wpdb->prefix . 'itmms_khatib_profiles' );
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table names from $wpdb->prefix, escaped with esc_sql().
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT s.*, p.name AS khatib_name, p.photo_url AS khatib_photo FROM {$s} s LEFT JOIN {$p} p ON p.id = s.khatib_id ORDER BY s.scheduled_date DESC, s.id DESC LIMIT %d",
				$limit
			),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return array_map( [ self::class, 'normalize_schedule' ], is_array( $rows ) ? $rows : [] );
	}

	/**
	 * @return array<int,array<string,mixed>>
	 */
	public static function schedule_upcoming( int $limit = 10 ): array {
		global $wpdb;
		$limit = max( 1, min( 50, $limit ) );
		$today = ( new DateTimeImmutable( 'now', wp_timezone() ) )->format( 'Y-m-d' );
		$s = esc_sql( $wpdb->prefix . 'itmms_khatib_schedule' );
		$p = esc_sql( $wpdb->prefix . 'itmms_khatib_profiles' );
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table names from $wpdb->prefix, escaped with esc_sql().
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT s.*, p.name AS khatib_name, p.photo_url AS khatib_photo FROM {$s} s LEFT JOIN {$p} p ON p.id = s.khatib_id WHERE s.scheduled_date >= %s ORDER BY s.scheduled_date ASC, s.id ASC LIMIT %d",
				$today,
				$limit
			),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return array_map( [ self::class, 'normalize_schedule' ], is_array( $rows ) ? $rows : [] );
	}

	/**
	 * This week's Jummah schedule row (next Friday or today if Friday).
	 *
	 * @return array<string,mixed>|null
	 */
	public static function this_week(): ?array {
		$tz = wp_timezone();
		$now = new DateTimeImmutable( 'now', $tz );
		$dow = (int) $now->format( 'N' ); // 1=Mon … 5=Fri
		$friday = 5 === $dow ? $now : $now->modify( 'next friday' );
		$date = $friday->format( 'Y-m-d' );

		global $wpdb;
		$s = esc_sql( $wpdb->prefix . 'itmms_khatib_schedule' );
		$p = esc_sql( $wpdb->prefix . 'itmms_khatib_profiles' );
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table names from $wpdb->prefix, escaped with esc_sql().
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT s.*, p.name AS khatib_name, p.photo_url AS khatib_photo FROM {$s} s LEFT JOIN {$p} p ON p.id = s.khatib_id WHERE s.scheduled_date = %s ORDER BY s.id DESC LIMIT 1",
				$date
			),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return is_array( $row ) ? self::normalize_schedule( $row ) : null;
	}

	/**
	 * @param array<string,mixed> $input Raw.
	 * @return array<string,mixed>|WP_Error
	 */
	public static function schedule_create( array $input ) {
		global $wpdb;
		$data = self::sanitize_schedule( $input );
		if ( is_wp_error( $data ) ) {
			return $data;
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$ok = $wpdb->insert(
			$wpdb->prefix . 'itmms_khatib_schedule',
			$data,
			[ '%d', '%s', '%s', '%s', '%s', '%s' ]
		);
		if ( false === $ok ) {
			return new WP_Error( 'itmms_schedule_create', __( 'Could not create schedule entry.', 'masjidos' ), [ 'status' => 500 ] );
		}
		return self::schedule_find( (int) $wpdb->insert_id );
	}

	/**
	 * @param array<string,mixed> $input Raw.
	 * @return array<string,mixed>|WP_Error
	 */
	public static function schedule_update( int $id, array $input ) {
		global $wpdb;
		if ( ! self::schedule_find( $id ) ) {
			return new WP_Error( 'itmms_schedule_missing', __( 'Schedule entry not found.', 'masjidos' ), [ 'status' => 404 ] );
		}
		$data = self::sanitize_schedule( $input );
		if ( is_wp_error( $data ) ) {
			return $data;
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$ok = $wpdb->update(
			$wpdb->prefix . 'itmms_khatib_schedule',
			$data,
			[ 'id' => $id ],
			[ '%d', '%s', '%s', '%s', '%s', '%s' ],
			[ '%d' ]
		);
		if ( false === $ok ) {
			return new WP_Error( 'itmms_schedule_update', __( 'Could not update schedule entry.', 'masjidos' ), [ 'status' => 500 ] );
		}
		return self::schedule_find( $id );
	}

	public static function schedule_delete( int $id ): bool {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return false !== $wpdb->delete( $wpdb->prefix . 'itmms_khatib_schedule', [ 'id' => $id ], [ '%d' ] );
	}

	/**
	 * @return array<string,mixed>|null
	 */
	public static function schedule_find( int $id ): ?array {
		global $wpdb;
		$s = esc_sql( $wpdb->prefix . 'itmms_khatib_schedule' );
		$p = esc_sql( $wpdb->prefix . 'itmms_khatib_profiles' );
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table names from $wpdb->prefix, escaped with esc_sql().
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT s.*, p.name AS khatib_name, p.photo_url AS khatib_photo FROM {$s} s LEFT JOIN {$p} p ON p.id = s.khatib_id WHERE s.id = %d",
				$id
			),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return is_array( $row ) ? self::normalize_schedule( $row ) : null;
	}

	/**
	 * @param array<string,mixed> $input Raw.
	 * @return array<string,mixed>|WP_Error
	 */
	private static function sanitize_schedule( array $input ) {
		$khatib_id = absint( $input['khatib_id'] ?? 0 );
		$date = sanitize_text_field( (string) ( $input['scheduled_date'] ?? '' ) );
		$date = substr( $date, 0, 10 );
		$dt = DateTimeImmutable::createFromFormat( 'Y-m-d', $date );
		if ( $khatib_id < 1 || ! self::profile_find( $khatib_id ) ) {
			return new WP_Error( 'itmms_schedule_khatib', __( 'Valid khatib is required.', 'masjidos' ), [ 'status' => 400 ] );
		}
		if ( ! $dt ) {
			return new WP_Error( 'itmms_schedule_date', __( 'Valid schedule date is required.', 'masjidos' ), [ 'status' => 400 ] );
		}
		$status = sanitize_key( (string) ( $input['status'] ?? 'confirmed' ) );
		if ( ! in_array( $status, [ 'confirmed', 'guest', 'pending' ], true ) ) {
			$status = 'confirmed';
		}
		$type = sanitize_key( (string) ( $input['type'] ?? 'jumuah' ) );
		if ( '' === $type ) {
			$type = 'jumuah';
		}
		return [
			'khatib_id'      => $khatib_id,
			'scheduled_date' => $dt->format( 'Y-m-d' ),
			'type'           => $type,
			'topic'          => sanitize_text_field( wp_unslash( (string) ( $input['topic'] ?? '' ) ) ),
			'status'         => $status,
			'notes'          => sanitize_textarea_field( wp_unslash( (string) ( $input['notes'] ?? '' ) ) ),
		];
	}

	/**
	 * @param array<string,mixed> $row DB row.
	 * @return array<string,mixed>
	 */
	private static function normalize_schedule( array $row ): array {
		return [
			'id'             => (int) ( $row['id'] ?? 0 ),
			'khatib_id'      => (int) ( $row['khatib_id'] ?? 0 ),
			'khatib_name'    => (string) ( $row['khatib_name'] ?? '' ),
			'khatib_photo'   => (string) ( $row['khatib_photo'] ?? '' ),
			'scheduled_date' => (string) ( $row['scheduled_date'] ?? '' ),
			'type'           => (string) ( $row['type'] ?? 'jumuah' ),
			'topic'          => (string) ( $row['topic'] ?? '' ),
			'status'         => (string) ( $row['status'] ?? 'confirmed' ),
			'notes'          => (string) ( $row['notes'] ?? '' ),
		];
	}

	// ── Planner (option JSON) ─────────────────────────────────────────

	/**
	 * @return array<int,array<string,mixed>>
	 */
	public static function get_plans(): array {
		$stored = get_option( self::PLANS_OPTION, [] );
		if ( ! is_array( $stored ) ) {
			return [];
		}
		$out = [];
		foreach ( $stored as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$out[] = self::normalize_plan( $row );
		}
		usort(
			$out,
			static function ( $a, $b ): int {
				return strcmp( (string) ( $a['date'] ?? '' ), (string) ( $b['date'] ?? '' ) );
			}
		);
		return $out;
	}

	/**
	 * @param array<string,mixed> $input Raw.
	 * @return array<string,mixed>|WP_Error
	 */
	public static function plan_save( array $input ) {
		$plans = self::get_plans();
		$id = sanitize_key( (string) ( $input['id'] ?? '' ) );
		$date = substr( sanitize_text_field( (string) ( $input['date'] ?? '' ) ), 0, 10 );
		$topic = sanitize_text_field( wp_unslash( (string) ( $input['topic'] ?? '' ) ) );
		if ( '' === $topic || ! DateTimeImmutable::createFromFormat( 'Y-m-d', $date ) ) {
			return new WP_Error( 'itmms_plan_invalid', __( 'Plan date and topic are required.', 'masjidos' ), [ 'status' => 400 ] );
		}

		$duplicates = ITMMS_Khutbah::find_similar_topics( $topic, 6 );
		$row = [
			'id'         => $id ?: 'plan_' . wp_generate_password( 8, false, false ),
			'date'       => $date,
			'topic'      => $topic,
			'category'   => sanitize_key( (string) ( $input['category'] ?? '' ) ),
			'notes'      => sanitize_textarea_field( wp_unslash( (string) ( $input['notes'] ?? '' ) ) ),
			'duplicates' => array_map(
				static function ( $d ) {
					return [
						'id'    => (int) ( $d['id'] ?? 0 ),
						'date'  => (string) ( $d['date'] ?? '' ),
						'topic' => (string) ( $d['topic'] ?? '' ),
					];
				},
				$duplicates
			),
		];

		$found = false;
		foreach ( $plans as $i => $plan ) {
			if ( ( $plan['id'] ?? '' ) === $row['id'] ) {
				$plans[ $i ] = $row;
				$found = true;
				break;
			}
		}
		if ( ! $found ) {
			$plans[] = $row;
		}

		update_option( self::PLANS_OPTION, $plans, false );
		return $row;
	}

	public static function plan_delete( string $id ): bool {
		$id = sanitize_key( $id );
		$plans = array_values(
			array_filter(
				self::get_plans(),
				static function ( $row ) use ( $id ): bool {
					return ( $row['id'] ?? '' ) !== $id;
				}
			)
		);
		update_option( self::PLANS_OPTION, $plans, false );
		return true;
	}

	/**
	 * @param array<string,mixed> $row Plan row.
	 * @return array<string,mixed>
	 */
	private static function normalize_plan( array $row ): array {
		return [
			'id'         => (string) ( $row['id'] ?? '' ),
			'date'       => (string) ( $row['date'] ?? '' ),
			'topic'      => (string) ( $row['topic'] ?? '' ),
			'category'   => (string) ( $row['category'] ?? '' ),
			'notes'      => (string) ( $row['notes'] ?? '' ),
			'duplicates' => is_array( $row['duplicates'] ?? null ) ? $row['duplicates'] : [],
		];
	}

	// ── References / bookmarks ────────────────────────────────────────

	/**
	 * Search built-in education packs + duas.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function search_references( string $query, string $type = 'all' ): array {
		$query = strtolower( trim( $query ) );
		$type  = sanitize_key( $type );
		$verses = [];
		$hadiths = [];
		$duas = [];

		if ( in_array( $type, [ 'all', 'quran' ], true ) ) {
			foreach ( self::verse_pack() as $verse ) {
				$hay = strtolower( ( $verse['en'] ?? '' ) . ' ' . ( $verse['bn'] ?? '' ) . ' ' . ( $verse['ref'] ?? '' ) );
				if ( '' === $query || false !== strpos( $hay, $query ) ) {
					$verse['type'] = 'quran';
					$verses[] = $verse;
				}
			}
		}

		if ( in_array( $type, [ 'all', 'hadith' ], true ) ) {
			foreach ( self::hadith_pack() as $hadith ) {
				$hay = strtolower( ( $hadith['en'] ?? '' ) . ' ' . ( $hadith['bn'] ?? '' ) . ' ' . ( $hadith['ref'] ?? '' ) );
				if ( '' === $query || false !== strpos( $hay, $query ) ) {
					$hadith['type'] = 'hadith';
					$hadiths[] = $hadith;
				}
			}
		}

		if ( in_array( $type, [ 'all', 'dua' ], true ) && class_exists( 'ITMMS_Duas_Azkar' ) ) {
			$catalog = ITMMS_Duas_Azkar::items( 'en', 'all', 100 );
			foreach ( $catalog as $dua ) {
				if ( ! is_array( $dua ) ) {
					continue;
				}
				$title = (string) ( $dua['title'] ?? '' );
				$hay = strtolower( $title . ' ' . ( $dua['latin'] ?? '' ) . ' ' . ( $dua['meaning'] ?? '' ) . ' ' . ( $dua['source'] ?? '' ) );
				if ( '' === $query || false !== strpos( $hay, $query ) ) {
					$duas[] = [
						'id'   => (string) ( $dua['key'] ?? md5( $title ) ),
						'ar'   => (string) ( $dua['arabic'] ?? '' ),
						'en'   => (string) ( $dua['meaning'] ?? $title ),
						'bn'   => '',
						'ref'  => (string) ( $dua['source'] ?? 'Dua' ),
						'type' => 'dua',
					];
				}
			}
		}

		return array_slice( array_merge( $verses, $hadiths, $duas ), 0, 40 );
	}

	/**
	 * @return array<int,array<string,mixed>>
	 */
	public static function get_bookmarks(): array {
		$stored = get_option( self::BOOKMARKS_OPTION, [] );
		return is_array( $stored ) ? array_values( $stored ) : [];
	}

	/**
	 * @param array<string,mixed> $item Bookmark.
	 * @return array<int,array<string,mixed>>
	 */
	public static function bookmark_add( array $item ): array {
		$bookmarks = self::get_bookmarks();
		$row = [
			'id'     => sanitize_key( (string) ( $item['id'] ?? wp_generate_password( 8, false, false ) ) ),
			'type'   => sanitize_key( (string) ( $item['type'] ?? 'quran' ) ),
			'ar'     => sanitize_textarea_field( (string) ( $item['ar'] ?? '' ) ),
			'en'     => sanitize_textarea_field( (string) ( $item['en'] ?? '' ) ),
			'bn'     => sanitize_textarea_field( (string) ( $item['bn'] ?? '' ) ),
			'ref'    => sanitize_text_field( (string) ( $item['ref'] ?? '' ) ),
		];
		foreach ( $bookmarks as $existing ) {
			if ( ( $existing['id'] ?? '' ) === $row['id'] && ( $existing['type'] ?? '' ) === $row['type'] ) {
				return $bookmarks;
			}
		}
		$bookmarks[] = $row;
		update_option( self::BOOKMARKS_OPTION, $bookmarks, false );
		return $bookmarks;
	}

	public static function bookmark_remove( string $id ): array {
		$id = sanitize_key( $id );
		$bookmarks = array_values(
			array_filter(
				self::get_bookmarks(),
				static function ( $row ) use ( $id ): bool {
					return ( $row['id'] ?? '' ) !== $id;
				}
			)
		);
		update_option( self::BOOKMARKS_OPTION, $bookmarks, false );
		return $bookmarks;
	}

	/**
	 * Expanded verse pack for Minbar search (includes Education daily verses).
	 *
	 * @return array<int,array<string,string>>
	 */
	private static function verse_pack(): array {
		$base = [];
		if ( method_exists( 'ITMMS_Education', 'get_verse_of_day' ) ) {
			// Pull static list via reflection of daily rotation source — use a local expanded set.
		}
		return [
			[ 'id' => 'v1', 'type' => 'quran', 'ar' => 'اللَّهُ لَا إِلَٰهَ إِلَّا هُوَ الْحَيُّ الْقَيُّومُ', 'en' => 'Allah - there is no deity except Him, the Ever-Living, the Sustainer of all existence.', 'bn' => 'আল্লাহ, তিনি ছাড়া কোনো উপাস্য নেই; তিনি চিরঞ্জীব, সবকিছুর ধারক।', 'ref' => '2:255' ],
			[ 'id' => 'v2', 'type' => 'quran', 'ar' => 'إِنَّ مَعَ الْعُسْرِ يُسْرًا', 'en' => 'Indeed, with hardship comes ease.', 'bn' => 'নিশ্চয়ই কষ্টের সাথে স্বস্তি রয়েছে।', 'ref' => '94:6' ],
			[ 'id' => 'v3', 'type' => 'quran', 'ar' => 'وَقُلْ رَبِّ زِدْنِي عِلْمًا', 'en' => 'And say, "My Lord, increase me in knowledge."', 'bn' => 'বলুন, হে আমার রব, আমার জ্ঞান বৃদ্ধি করুন।', 'ref' => '20:114' ],
			[ 'id' => 'v4', 'type' => 'quran', 'ar' => 'ادْعُونِي أَسْتَجِبْ لَكُمْ', 'en' => 'Call upon Me; I will respond to you.', 'bn' => 'তোমরা আমাকে ডাকো, আমি তোমাদের ডাকে সাড়া দেব।', 'ref' => '40:60' ],
			[ 'id' => 'v5', 'type' => 'quran', 'ar' => 'إِنَّ اللَّهَ مَعَ الصَّابِرِينَ', 'en' => 'Indeed, Allah is with the patient.', 'bn' => 'নিশ্চয়ই আল্লাহ ধৈর্যশীলদের সাথে আছেন।', 'ref' => '2:153' ],
			[ 'id' => 'v6', 'type' => 'quran', 'ar' => 'وَمَن يَتَّقِ اللَّهَ يَجْعَل لَّهُ مَخْرَجًا', 'en' => 'And whoever fears Allah - He will make for him a way out.', 'bn' => 'যে আল্লাহকে ভয় করে, তিনি তার জন্য নিষ্কৃতির পথ করে দেন।', 'ref' => '65:2' ],
			[ 'id' => 'v7', 'type' => 'quran', 'ar' => 'فَاذْكُرُونِي أَذْكُرْكُمْ', 'en' => 'So remember Me; I will remember you.', 'bn' => 'অতএব তোমরা আমাকে স্মরণ করো, আমিও তোমাদের স্মরণ করব।', 'ref' => '2:152' ],
			[ 'id' => 'v8', 'type' => 'quran', 'ar' => 'وَبَشِّرِ الصَّابِرِينَ', 'en' => 'And give good tidings to the patient.', 'bn' => 'এবং ধৈর্যশীলদের সুসংবাদ দাও।', 'ref' => '2:155' ],
			[ 'id' => 'v9', 'type' => 'quran', 'ar' => 'إِنَّ الصَّلَاةَ تَنْهَىٰ عَنِ الْفَحْشَاءِ وَالْمُنكَرِ', 'en' => 'Indeed, prayer prohibits immorality and wrongdoing.', 'bn' => 'নিশ্চয়ই সালাত অশ্লীল ও অসৎ কাজ থেকে বিরত রাখে।', 'ref' => '29:45' ],
			[ 'id' => 'v10', 'type' => 'quran', 'ar' => 'وَتَعَاوَنُوا عَلَى الْبِرِّ وَالتَّقْوَىٰ', 'en' => 'And cooperate in righteousness and piety.', 'bn' => 'সৎকর্ম ও তাকওয়ার কাজে পরস্পরকে সহযোগিতা করো।', 'ref' => '5:2' ],
		];
	}

	/**
	 * @return array<int,array<string,string>>
	 */
	private static function hadith_pack(): array {
		return [
			[ 'id' => 'h1', 'type' => 'hadith', 'ar' => 'إِنَّمَا الأَعْمَالُ بِالنِّيَّاتِ', 'en' => 'Actions are judged by intentions.', 'bn' => 'সব কাজ নিয়তের উপর নির্ভরশীল।', 'ref' => 'Bukhari 1' ],
			[ 'id' => 'h2', 'type' => 'hadith', 'ar' => 'الدِّينُ النَّصِيحَةُ', 'en' => 'The religion is sincere advice.', 'bn' => 'দ্বীন হলো কল্যাণ কামনা করা।', 'ref' => 'Muslim 55' ],
			[ 'id' => 'h3', 'type' => 'hadith', 'ar' => 'لَا يُؤْمِنُ أَحَدُكُمْ حَتَّى يُحِبَّ لِأَخِيهِ مَا يُحِبُّ لِنَفْسِهِ', 'en' => 'None of you truly believes until he loves for his brother what he loves for himself.', 'bn' => 'তোমাদের কেউ পূর্ণ মুমিন হবে না, যতক্ষণ না সে নিজের জন্য যা ভালোবাসে তার ভাইয়ের জন্যও তা ভালোবাসে।', 'ref' => 'Bukhari 13' ],
			[ 'id' => 'h4', 'type' => 'hadith', 'ar' => 'مَنْ كَانَ يُؤْمِنُ بِاللَّهِ وَالْيَوْمِ الآخِرِ فَلْيَقُلْ خَيْرًا أَوْ لِيَصْمُتْ', 'en' => 'Whoever believes in Allah and the Last Day should speak good or remain silent.', 'bn' => 'যে আল্লাহ ও শেষ দিনের প্রতি ঈমান রাখে, সে যেন ভালো কথা বলে অথবা চুপ থাকে।', 'ref' => 'Bukhari 6018' ],
			[ 'id' => 'h5', 'type' => 'hadith', 'ar' => 'الْمُسْلِمُ مَنْ سَلِمَ الْمُسْلِمُونَ مِنْ لِسَانِهِ وَيَدِهِ', 'en' => 'A Muslim is the one from whose tongue and hand other Muslims are safe.', 'bn' => 'প্রকৃত মুসলিম সে, যার জিহ্বা ও হাত থেকে অন্য মুসলমান নিরাপদ থাকে।', 'ref' => 'Bukhari 10' ],
			[ 'id' => 'h6', 'type' => 'hadith', 'ar' => 'خَيْرُكُمْ مَنْ تَعَلَّمَ الْقُرْآنَ وَعَلَّمَهُ', 'en' => 'The best among you are those who learn the Quran and teach it.', 'bn' => 'তোমাদের মধ্যে সর্বোত্তম সে, যে কুরআন শেখে এবং অন্যকে শেখায়।', 'ref' => 'Bukhari 5027' ],
			[ 'id' => 'h7', 'type' => 'hadith', 'ar' => 'الطُّهُورُ شَطْرُ الإِيمَانِ', 'en' => 'Cleanliness is half of faith.', 'bn' => 'পবিত্রতা ঈমানের অর্ধেক।', 'ref' => 'Muslim 223' ],
			[ 'id' => 'h8', 'type' => 'hadith', 'ar' => 'مَنْ سَلَكَ طَرِيقًا يَلْتَمِسُ فِيهِ عِلْمًا سَهَّلَ اللَّهُ لَهُ بِهِ طَرِيقًا إِلَى الْجَنَّةِ', 'en' => 'Whoever travels a path in search of knowledge, Allah makes easy for him a path to Paradise.', 'bn' => 'যে জ্ঞান অর্জনের পথে চলে, আল্লাহ তার জন্য জান্নাতের পথ সহজ করে দেন।', 'ref' => 'Muslim 2699' ],
		];
	}
}
