<?php
/**
 * Khutbah archive repository (Minbar Archive).
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Database access for Friday khutbah archive entries.
 */
final class ITMMS_Khutbah {

	private const CACHE_GROUP = 'masjidos_khutbah';

	/**
	 * @return array<int,string>
	 */
	public static function categories(): array {
		return [
			'aqeedah'  => __( 'Aqeedah', 'masjidos' ),
			'akhlaq'   => __( 'Akhlaq', 'masjidos' ),
			'fiqh'     => __( 'Fiqh', 'masjidos' ),
			'seerah'   => __( 'Seerah', 'masjidos' ),
			'tazkiyah' => __( 'Tazkiyah', 'masjidos' ),
			'ramadan'  => __( 'Ramadan', 'masjidos' ),
			'other'    => __( 'Other', 'masjidos' ),
		];
	}

	/**
	 * @return array<int,array<string,mixed>>
	 */
	public static function all( int $limit = 100 ): array {
		global $wpdb;

		$limit     = max( 1, min( 500, $limit ) );
		$cache_key = self::cache_key( 'all:' . $limit );
		$found     = false;
		$cached    = wp_cache_get( $cache_key, self::CACHE_GROUP, false, $found );
		if ( $found && is_array( $cached ) ) {
			return $cached;
		}

		$table = esc_sql( self::table() );
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} ORDER BY date DESC, id DESC LIMIT %d",
				$limit
			),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$items = array_map( [ self::class, 'normalize' ], is_array( $rows ) ? $rows : [] );
		wp_cache_set( $cache_key, $items, self::CACHE_GROUP, MINUTE_IN_SECONDS );

		return $items;
	}

	/**
	 * Public / admin archive query with filters.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function query( int $limit = 12, string $search = '', string $date = '', string $category = '', bool $public_only = false ): array {
		global $wpdb;

		$limit    = max( 1, min( 100, $limit ) );
		$search   = sanitize_text_field( $search );
		$date     = self::sanitize_date( $date, '' );
		$category = sanitize_key( $category );

		$cache_key = self::cache_key( 'query:' . $limit . ':' . md5( $search . '|' . $date . '|' . $category . '|' . ( $public_only ? '1' : '0' ) ) );
		$found     = false;
		$cached    = wp_cache_get( $cache_key, self::CACHE_GROUP, false, $found );
		if ( $found && is_array( $cached ) ) {
			return $cached;
		}

		$table  = esc_sql( self::table() );
		$where  = [ '1=1' ];
		$params = [];

		if ( $public_only ) {
			$where[] = 'is_public = 1';
		}

		if ( '' !== $search ) {
			$where[]  = '(topic LIKE %s OR khatib LIKE %s OR summary LIKE %s OR tags LIKE %s)';
			$like     = '%' . $wpdb->esc_like( $search ) . '%';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}

		if ( '' !== $date ) {
			$where[]  = 'date = %s';
			$params[] = $date;
		}

		if ( '' !== $category && isset( self::categories()[ $category ] ) ) {
			$where[]  = 'category = %s';
			$params[] = $category;
		}

		$params[] = $limit;
		$sql      = "SELECT * FROM {$table} WHERE " . implode( ' AND ', $where ) . ' ORDER BY date DESC, id DESC LIMIT %d';

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $table is esc_sql()'d; placeholders bound via $wpdb->prepare().
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter

		$items = array_map( [ self::class, 'normalize' ], is_array( $rows ) ? $rows : [] );
		wp_cache_set( $cache_key, $items, self::CACHE_GROUP, MINUTE_IN_SECONDS );

		return $items;
	}

	/**
	 * @return array{total:int,this_month:int,with_audio:int,with_doc:int,planned_hint:int}
	 */
	public static function stats(): array {
		global $wpdb;

		$table       = self::table();
		$month_start = gmdate( 'Y-m-01' );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$total = (int) $wpdb->get_var(
			$wpdb->prepare( 'SELECT COUNT(*) FROM %i', $table )
		);
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$this_month = (int) $wpdb->get_var(
			$wpdb->prepare( 'SELECT COUNT(*) FROM %i WHERE date >= %s', $table, $month_start )
		);
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$with_audio = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM %i WHERE audio_url IS NOT NULL AND audio_url <> ''", $table )
		);
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$with_doc = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM %i WHERE doc_url IS NOT NULL AND doc_url <> ''", $table )
		);

		return [
			'total'      => $total,
			'this_month' => $this_month,
			'with_audio' => $with_audio,
			'with_doc'   => $with_doc,
		];
	}

	/**
	 * Detect similar topics in the last N months.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function find_similar_topics( string $topic, int $months = 6, int $exclude_id = 0 ): array {
		global $wpdb;

		$topic = sanitize_text_field( $topic );
		if ( '' === $topic ) {
			return [];
		}

		$months = max( 1, min( 24, $months ) );
		$since  = ( new DateTimeImmutable( 'now', wp_timezone() ) )->modify( '-' . $months . ' months' )->format( 'Y-m-d' );
		$table  = esc_sql( self::table() );
		$like   = '%' . $wpdb->esc_like( $topic ) . '%';

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, date, topic, khatib FROM {$table} WHERE date >= %s AND topic LIKE %s AND id <> %d ORDER BY date DESC LIMIT 5",
				$since,
				$like,
				$exclude_id
			),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return array_map( [ self::class, 'normalize' ], is_array( $rows ) ? $rows : [] );
	}

	/**
	 * @return array<string,mixed>|null
	 */
	public static function find( int $id ): ?array {
		global $wpdb;

		$cache_key = self::cache_key( 'find:' . $id );
		$found     = false;
		$cached    = wp_cache_get( $cache_key, self::CACHE_GROUP, false, $found );
		if ( $found ) {
			return is_array( $cached ) ? $cached : null;
		}

		$table = esc_sql( self::table() );
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$item = is_array( $row ) ? self::normalize( $row ) : null;
		wp_cache_set( $cache_key, $item, self::CACHE_GROUP, MINUTE_IN_SECONDS );

		return $item;
	}

	/**
	 * @param array<string,mixed> $input Raw values.
	 * @return array<string,mixed>|WP_Error
	 */
	public static function create( array $input ) {
		global $wpdb;

		$data = self::sanitize( $input );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$formats = self::formats_for( $data );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$inserted = $wpdb->insert( self::table(), $data, $formats );

		if ( false === $inserted ) {
			return new WP_Error( 'itmms_khutbah_create', __( 'The khutbah could not be created.', 'masjidos' ), [ 'status' => 500 ] );
		}

		self::invalidate_cache();
		return self::find( (int) $wpdb->insert_id );
	}

	/**
	 * @param array<string,mixed> $input Raw values.
	 * @return array<string,mixed>|WP_Error
	 */
	public static function update( int $id, array $input ) {
		global $wpdb;

		$existing = self::find( $id );
		if ( ! $existing ) {
			return new WP_Error( 'itmms_khutbah_missing', __( 'Khutbah not found.', 'masjidos' ), [ 'status' => 404 ] );
		}

		// Merge partial updates (e.g. outline-only from Sermon Builder).
		$merged = array_merge(
			[
				'date'             => $existing['date'] ?? '',
				'topic'            => $existing['topic'] ?? '',
				'khatib'           => $existing['khatib'] ?? '',
				'language'         => $existing['language'] ?? '',
				'summary'          => $existing['summary'] ?? '',
				'audio_url'        => $existing['audio_url'] ?? '',
				'category'         => $existing['category'] ?? '',
				'tags'             => $existing['tags'] ?? '',
				'quran_refs'       => $existing['quran_refs'] ?? [],
				'hadith_refs'      => $existing['hadith_refs'] ?? [],
				'doc_url'          => $existing['doc_url'] ?? '',
				'is_public'        => ! empty( $existing['is_public'] ) ? 1 : 0,
				'duration_minutes' => $existing['duration_minutes'] ?? 0,
				'outline'          => $existing['outline'] ?? '',
			],
			$input
		);

		$data = self::sanitize( $merged );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$formats = self::formats_for( $data );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$updated = $wpdb->update( self::table(), $data, [ 'id' => $id ], $formats, [ '%d' ] );

		if ( false === $updated ) {
			return new WP_Error( 'itmms_khutbah_update', __( 'The khutbah could not be updated.', 'masjidos' ), [ 'status' => 500 ] );
		}

		self::invalidate_cache();
		return self::find( $id );
	}

	public static function delete( int $id ): bool {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$deleted = $wpdb->delete( self::table(), [ 'id' => $id ], [ '%d' ] );
		if ( false !== $deleted ) {
			self::invalidate_cache();
		}

		return false !== $deleted;
	}

	private static function table(): string {
		global $wpdb;
		return $wpdb->prefix . 'itmms_khutbah_archive';
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
	 * @param array<string,mixed> $data Sanitized row.
	 * @return array<int,string>
	 */
	private static function formats_for( array $data ): array {
		$formats = [];
		foreach ( array_keys( $data ) as $key ) {
			if ( 'is_public' === $key || 'duration_minutes' === $key ) {
				$formats[] = '%d';
			} else {
				$formats[] = '%s';
			}
		}
		return $formats;
	}

	/**
	 * @param array<string,mixed> $input Raw values.
	 * @return array<string,mixed>|WP_Error
	 */
	private static function sanitize( array $input ) {
		$topic  = sanitize_text_field( wp_unslash( (string) ( $input['topic'] ?? '' ) ) );
		$khatib = sanitize_text_field( wp_unslash( (string) ( $input['khatib'] ?? '' ) ) );
		$date   = self::sanitize_date( $input['date'] ?? '', '' );

		if ( '' === $topic ) {
			return new WP_Error( 'itmms_khutbah_topic', __( 'Khutbah topic is required.', 'masjidos' ), [ 'status' => 400 ] );
		}
		if ( '' === $khatib ) {
			return new WP_Error( 'itmms_khutbah_khatib', __( 'Khatib name is required.', 'masjidos' ), [ 'status' => 400 ] );
		}
		if ( '' === $date ) {
			return new WP_Error( 'itmms_khutbah_date', __( 'Khutbah date is required.', 'masjidos' ), [ 'status' => 400 ] );
		}

		$category = sanitize_key( (string) ( $input['category'] ?? '' ) );
		if ( '' !== $category && ! isset( self::categories()[ $category ] ) ) {
			$category = 'other';
		}

		$duration = isset( $input['duration_minutes'] ) ? absint( $input['duration_minutes'] ) : 0;
		if ( $duration > 180 ) {
			$duration = 180;
		}

		$is_public = array_key_exists( 'is_public', $input ) ? (int) ( ! empty( $input['is_public'] ) ) : 1;

		return [
			'date'              => $date,
			'topic'             => $topic,
			'khatib'            => $khatib,
			'language'          => sanitize_text_field( wp_unslash( (string) ( $input['language'] ?? '' ) ) ),
			'summary'           => sanitize_textarea_field( wp_unslash( (string) ( $input['summary'] ?? '' ) ) ),
			'audio_url'         => esc_url_raw( wp_unslash( (string) ( $input['audio_url'] ?? '' ) ) ),
			'category'          => $category,
			'tags'              => sanitize_text_field( wp_unslash( (string) ( $input['tags'] ?? '' ) ) ),
			'quran_refs'        => self::sanitize_json_list( $input['quran_refs'] ?? [] ),
			'hadith_refs'       => self::sanitize_json_list( $input['hadith_refs'] ?? [] ),
			'doc_url'           => esc_url_raw( wp_unslash( (string) ( $input['doc_url'] ?? '' ) ) ),
			'is_public'         => $is_public,
			'duration_minutes'  => $duration,
			'outline'           => self::sanitize_outline( $input['outline'] ?? '' ),
		];
	}

	/**
	 * @param mixed $value Raw list or JSON.
	 */
	private static function sanitize_json_list( $value ): string {
		if ( is_string( $value ) ) {
			$raw = trim( wp_unslash( $value ) );
			if ( '' === $raw ) {
				return '[]';
			}
			$decoded = json_decode( $raw, true );
			if ( is_array( $decoded ) ) {
				$value = $decoded;
			} else {
				$value = array_map( 'trim', explode( ',', $raw ) );
			}
		}
		if ( ! is_array( $value ) ) {
			return '[]';
		}

		$clean = [];
		foreach ( $value as $item ) {
			if ( is_string( $item ) ) {
				$item = trim( sanitize_text_field( $item ) );
				if ( '' !== $item ) {
					$clean[] = $item;
				}
				continue;
			}
			if ( ! is_array( $item ) ) {
				continue;
			}
			$row = [
				'text'   => sanitize_textarea_field( (string) ( $item['text'] ?? $item['ref'] ?? '' ) ),
				'source' => sanitize_text_field( (string) ( $item['source'] ?? '' ) ),
			];
			if ( '' !== $row['text'] ) {
				$clean[] = $row;
			}
		}

		$json = wp_json_encode( $clean );
		return is_string( $json ) ? $json : '[]';
	}

	/**
	 * @param mixed $value Outline string or array.
	 */
	private static function sanitize_outline( $value ): string {
		if ( is_array( $value ) ) {
			$clean = [];
			foreach ( $value as $key => $part ) {
				$clean[ sanitize_key( (string) $key ) ] = sanitize_textarea_field( wp_unslash( (string) $part ) );
			}
			$json = wp_json_encode( $clean );
			return is_string( $json ) ? $json : '';
		}
		return sanitize_textarea_field( wp_unslash( (string) $value ) );
	}

	/**
	 * @param mixed $value Raw date.
	 */
	private static function sanitize_date( $value, string $fallback ): string {
		$value = sanitize_text_field( wp_unslash( (string) $value ) );
		if ( '' === $value ) {
			return $fallback;
		}

		$value = substr( $value, 0, 10 );
		$date  = DateTimeImmutable::createFromFormat( 'Y-m-d', $value );
		return $date instanceof DateTimeImmutable ? $date->format( 'Y-m-d' ) : $fallback;
	}

	/**
	 * @param array<string,mixed> $row Database row.
	 * @return array<string,mixed>
	 */
	private static function normalize( array $row ): array {
		$quran = json_decode( (string) ( $row['quran_refs'] ?? '[]' ), true );
		$hadith = json_decode( (string) ( $row['hadith_refs'] ?? '[]' ), true );
		$outline_raw = (string) ( $row['outline'] ?? '' );
		$outline_json = json_decode( $outline_raw, true );

		return [
			'id'               => (int) ( $row['id'] ?? 0 ),
			'date'             => (string) ( $row['date'] ?? '' ),
			'topic'            => (string) ( $row['topic'] ?? '' ),
			'khatib'           => (string) ( $row['khatib'] ?? '' ),
			'language'         => (string) ( $row['language'] ?? '' ),
			'summary'          => (string) ( $row['summary'] ?? '' ),
			'audio_url'        => (string) ( $row['audio_url'] ?? '' ),
			'category'         => (string) ( $row['category'] ?? '' ),
			'tags'             => (string) ( $row['tags'] ?? '' ),
			'quran_refs'       => is_array( $quran ) ? $quran : [],
			'hadith_refs'      => is_array( $hadith ) ? $hadith : [],
			'doc_url'          => (string) ( $row['doc_url'] ?? '' ),
			'is_public'        => ! array_key_exists( 'is_public', $row ) || ! empty( $row['is_public'] ),
			'duration_minutes' => isset( $row['duration_minutes'] ) && '' !== (string) $row['duration_minutes'] ? (int) $row['duration_minutes'] : null,
			'outline'          => is_array( $outline_json ) ? $outline_json : $outline_raw,
		];
	}
}
