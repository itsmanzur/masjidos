<?php
/**
 * Announcement repository and status rules.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Database access for notices shown in admin and public widgets.
 */
final class ITMMS_Announcements {

	private const CACHE_GROUP = 'masjidos_announcements';

	/**
	 * Return notices for the admin screen.
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
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- WordPress 6.0 lacks the %i identifier placeholder; the internal table name is escaped above.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Cached query against the plugin's own table.
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} ORDER BY is_active DESC, priority DESC, start_date DESC, id DESC LIMIT %d",
				$limit
			),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$notices = array_map( [ self::class, 'normalize' ], is_array( $rows ) ? $rows : [] );
		wp_cache_set( $cache_key, $notices, self::CACHE_GROUP, MINUTE_IN_SECONDS );

		return $notices;
	}

	/**
	 * Return notices currently visible to the public.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function active( int $limit = 10, string $type = 'all' ): array {
		global $wpdb;

		$limit = max( 1, min( 50, $limit ) );
		$type = sanitize_key( $type );
		$cache_key = self::cache_key( 'active:' . $limit . ':' . $type );
		$found = false;
		$cached = wp_cache_get( $cache_key, self::CACHE_GROUP, false, $found );
		if ( $found && is_array( $cached ) ) {
			return $cached;
		}

		$table = esc_sql( self::table() );
		$now = self::now();

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- WordPress 6.0 lacks %i; the internal table name is escaped above.
		if ( in_array( $type, self::types(), true ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Cached query against the plugin's own table.
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$table} WHERE is_active = 1 AND start_date <= %s AND (end_date IS NULL OR end_date >= %s) AND announcement_type = %s ORDER BY priority DESC, start_date DESC, id DESC LIMIT %d",
					$now,
					$now,
					$type,
					$limit
				),
				ARRAY_A
			);
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Cached query against the plugin's own table.
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$table} WHERE is_active = 1 AND start_date <= %s AND (end_date IS NULL OR end_date >= %s) ORDER BY priority DESC, start_date DESC, id DESC LIMIT %d",
					$now,
					$now,
					$limit
				),
				ARRAY_A
			);
		}
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$notices = array_map( [ self::class, 'normalize' ], is_array( $rows ) ? $rows : [] );
		wp_cache_set( $cache_key, $notices, self::CACHE_GROUP, MINUTE_IN_SECONDS );

		return $notices;
	}

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
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- WordPress 6.0 lacks the %i identifier placeholder; the internal table name is escaped above.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Cached query against the plugin's own table.
		$count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE is_active = 1 AND start_date <= %s AND (end_date IS NULL OR end_date >= %s)",
				$now,
				$now
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		wp_cache_set( $cache_key, $count, self::CACHE_GROUP, MINUTE_IN_SECONDS );

		return $count;
	}

	/**
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
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- WordPress 6.0 lacks the %i identifier placeholder; the internal table name is escaped above.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Cached query against the plugin's own table.
		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$notice = is_array( $row ) ? self::normalize( $row ) : null;
		wp_cache_set( $cache_key, $notice, self::CACHE_GROUP, MINUTE_IN_SECONDS );

		return $notice;
	}

	/**
	 * @param array<string,mixed> $input Raw notice values.
	 * @return array<string,mixed>|WP_Error
	 */
	public static function create( array $input ) {
		global $wpdb;

		$data = self::sanitize( $input );
		if ( '' === $data['title'] ) {
			return new WP_Error( 'itmms_notice_title', __( 'Notice title is required.', 'masjidos' ), [ 'status' => 400 ] );
		}
		if ( ! empty( $data['end_date'] ) && $data['end_date'] < $data['start_date'] ) {
			return new WP_Error( 'itmms_notice_dates', __( 'The notice end date must be after its start date.', 'masjidos' ), [ 'status' => 400 ] );
		}

		$data['created_by'] = get_current_user_id();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Write to the plugin's own table; cache is invalidated below.
		$inserted = $wpdb->insert(
			self::table(),
			$data,
			[ '%s', '%s', '%s', '%d', '%s', '%s', '%d', '%d' ]
		);

		if ( false === $inserted ) {
			return new WP_Error( 'itmms_notice_create', __( 'The notice could not be created.', 'masjidos' ), [ 'status' => 500 ] );
		}

		self::invalidate_cache();
		return self::find( (int) $wpdb->insert_id );
	}

	/**
	 * @param array<string,mixed> $input Raw notice values.
	 * @return array<string,mixed>|WP_Error
	 */
	public static function update( int $id, array $input ) {
		global $wpdb;

		if ( ! self::find( $id ) ) {
			return new WP_Error( 'itmms_notice_missing', __( 'Notice not found.', 'masjidos' ), [ 'status' => 404 ] );
		}

		$data = self::sanitize( $input );
		if ( '' === $data['title'] ) {
			return new WP_Error( 'itmms_notice_title', __( 'Notice title is required.', 'masjidos' ), [ 'status' => 400 ] );
		}
		if ( ! empty( $data['end_date'] ) && $data['end_date'] < $data['start_date'] ) {
			return new WP_Error( 'itmms_notice_dates', __( 'The notice end date must be after its start date.', 'masjidos' ), [ 'status' => 400 ] );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Write to the plugin's own table; cache is invalidated below.
		$updated = $wpdb->update(
			self::table(),
			$data,
			[ 'id' => $id ],
			[ '%s', '%s', '%s', '%d', '%s', '%s', '%d' ],
			[ '%d' ]
		);

		if ( false === $updated ) {
			return new WP_Error( 'itmms_notice_update', __( 'The notice could not be updated.', 'masjidos' ), [ 'status' => 500 ] );
		}

		self::invalidate_cache();
		return self::find( $id );
	}

	public static function delete( int $id ): bool {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Write to the plugin's own table; cache is invalidated on success.
		$deleted = $wpdb->delete( self::table(), [ 'id' => $id ], [ '%d' ] );
		if ( false !== $deleted ) {
			self::invalidate_cache();
		}

		return false !== $deleted;
	}

	/**
	 * @return string[]
	 */
	public static function types(): array {
		return [ 'general', 'urgent', 'jumuah' ];
	}

	private static function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'itmms_announcements';
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
	 * @param array<string,mixed> $input Raw values.
	 * @return array<string,mixed>
	 */
	private static function sanitize( array $input ): array {
		$type = sanitize_key( (string) ( $input['announcement_type'] ?? 'general' ) );
		$start = self::sanitize_datetime( $input['start_date'] ?? '', self::now() );
		$end = self::sanitize_datetime( $input['end_date'] ?? '', '' );

		return [
			'title'             => sanitize_text_field( wp_unslash( (string) ( $input['title'] ?? '' ) ) ),
			'content'           => sanitize_textarea_field( wp_unslash( (string) ( $input['content'] ?? '' ) ) ),
			'announcement_type' => in_array( $type, self::types(), true ) ? $type : 'general',
			'priority'          => max( 0, min( 10, (int) ( $input['priority'] ?? 0 ) ) ),
			'start_date'        => $start,
			'end_date'          => '' === $end ? null : $end,
			'is_active'         => empty( $input['is_active'] ) ? 0 : 1,
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
	 * @param array<string,mixed> $row Database row.
	 * @return array<string,mixed>
	 */
	private static function normalize( array $row ): array {
		$now = self::now();
		$status = 'active';
		if ( empty( $row['is_active'] ) ) {
			$status = 'inactive';
		} elseif ( (string) $row['start_date'] > $now ) {
			$status = 'scheduled';
		} elseif ( ! empty( $row['end_date'] ) && (string) $row['end_date'] < $now ) {
			$status = 'expired';
		}

		return [
			'id'                => (int) $row['id'],
			'title'             => (string) $row['title'],
			'content'           => (string) ( $row['content'] ?? '' ),
			'announcement_type' => (string) ( $row['announcement_type'] ?? 'general' ),
			'priority'          => (int) ( $row['priority'] ?? 0 ),
			'start_date'        => (string) $row['start_date'],
			'end_date'          => empty( $row['end_date'] ) ? '' : (string) $row['end_date'],
			'is_active'         => ! empty( $row['is_active'] ),
			'status'            => $status,
			'created_by'        => (int) ( $row['created_by'] ?? 0 ),
			'created_at'        => (string) ( $row['created_at'] ?? '' ),
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
}
