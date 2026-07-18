<?php
/**
 * CSV prayer timetable import, export, and storage.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Stores masjid-provided daily Azan and Iqamah times from CSV uploads.
 */
final class ITMMS_Prayer_Timetable {

	public const OPTION_KEY = 'itmms_prayer_timetable';

	/** @var array<int,string> */
	private const PRAYER_KEYS = [ 'fajr', 'sunrise', 'dhuhr', 'asr', 'maghrib', 'isha' ];

	/** @var array<int,string> */
	private const IQAMAH_KEYS = [ 'fajr', 'dhuhr', 'asr', 'maghrib', 'isha' ];

	/**
	 * @return array<string,mixed>
	 */
	public static function get_store(): array {
		$stored = get_option( self::OPTION_KEY, [] );
		if ( ! is_array( $stored ) ) {
			return [ 'days' => [], 'updated_at' => '' ];
		}

		return [
			'days'       => is_array( $stored['days'] ?? null ) ? $stored['days'] : [],
			'updated_at' => (string) ( $stored['updated_at'] ?? '' ),
		];
	}

	/**
	 * @return array<string,mixed>|null
	 */
	public static function for_date( string $date ): ?array {
		$date = self::normalize_date_key( $date );
		if ( '' === $date ) {
			return null;
		}

		$store = self::get_store();
		$row = $store['days'][ $date ] ?? null;
		return is_array( $row ) ? $row : null;
	}

	/**
	 * @return array<string,mixed>
	 */
	public static function summary(): array {
		$store = self::get_store();
		$days = array_keys( $store['days'] );
		sort( $days );

		$years = [];
		foreach ( $days as $date ) {
			$year = (int) substr( (string) $date, 0, 4 );
			if ( $year >= 1970 ) {
				$years[ $year ] = ( $years[ $year ] ?? 0 ) + 1;
			}
		}
		ksort( $years );

		$count = count( $days );

		return [
			'count'      => $count,
			'start_date' => $days[0] ?? '',
			'end_date'   => '' !== ( $days[0] ?? '' ) ? ( $days[ count( $days ) - 1 ] ?? '' ) : '',
			'updated_at' => (string) ( $store['updated_at'] ?? '' ),
			'active'     => ! empty( $days ),
			'years'      => $years,
			'large'      => $count > 400,
		];
	}

	/**
	 * Validate CSV without writing to storage.
	 *
	 * @return array<string,mixed>
	 */
	public static function validate_csv( string $csv ): array {
		$parsed = self::parse_csv( $csv );
		$rows   = $parsed['rows'];
		$dates  = [];
		foreach ( $rows as $row ) {
			$date = (string) ( $row['date'] ?? '' );
			if ( '' !== $date ) {
				$dates[] = $date;
			}
		}
		sort( $dates );

		$preview = array_slice( $dates, 0, 5 );
		$valid   = count( $rows );

		return [
			'success'   => $valid > 0,
			'valid'     => $valid,
			'errors'    => $parsed['errors'],
			'error_count'=> count( $parsed['errors'] ),
			'preview'   => $preview,
			'start_date'=> $dates[0] ?? '',
			'end_date'  => '' !== ( $dates[0] ?? '' ) ? ( $dates[ count( $dates ) - 1 ] ?? '' ) : '',
			'summary'   => self::summary(),
		];
	}

	/**
	 * Import CSV text and merge rows by date.
	 *
	 * @return array<string,mixed>
	 */
	public static function import_csv( string $csv, string $mode = 'merge', bool $dry_run = false ): array {
		if ( $dry_run ) {
			$result = self::validate_csv( $csv );
			$result['imported'] = 0;
			$result['skipped']  = 0;
			$result['dry_run']  = true;
			return $result;
		}

		$parsed = self::parse_csv( $csv );
		if ( ! empty( $parsed['errors'] ) && empty( $parsed['rows'] ) ) {
			return [
				'success'  => false,
				'errors'   => $parsed['errors'],
				'imported' => 0,
				'skipped'  => 0,
				'summary'  => self::summary(),
			];
		}

		$store = self::get_store();
		if ( 'replace' === $mode ) {
			$store['days'] = [];
		}

		$imported = 0;
		$skipped  = 0;
		$errors   = $parsed['errors'];

		foreach ( $parsed['rows'] as $line => $row ) {
			$date = (string) ( $row['date'] ?? '' );
			if ( '' === $date ) {
				++$skipped;
				$errors[] = sprintf(
					/* translators: %d: CSV line number. */
					__( 'Line %d: missing or invalid date.', 'masjidos' ),
					$line
				);
				continue;
			}

			$store['days'][ $date ] = [
				'azan'   => $row['azan'],
				'iqamah' => $row['iqamah'],
			];
			++$imported;
		}

		$store['updated_at'] = gmdate( 'c' );
		update_option( self::OPTION_KEY, $store, false );
		ITMMS_Prayer_Times::flush_cache();

		return [
			'success'  => $imported > 0,
			'imported' => $imported,
			'skipped'  => $skipped,
			'errors'   => $errors,
			'summary'  => self::summary(),
		];
	}

	/**
	 * Export stored timetable as CSV.
	 *
	 * @param int|null $year Optional Gregorian year filter.
	 */
	public static function export_csv( ?int $year = null ): string {
		$store = self::get_store();
		$days  = array_keys( $store['days'] );
		sort( $days );

		if ( null !== $year && $year >= 1970 && $year <= 2099 ) {
			$prefix = sprintf( '%04d-', $year );
			$days   = array_values(
				array_filter(
					$days,
					static function ( $date ) use ( $prefix ): bool {
						return 0 === strpos( (string) $date, $prefix );
					}
				)
			);
		}

		$headers = [
			'date',
			'fajr',
			'sunrise',
			'dhuhr',
			'asr',
			'maghrib',
			'isha',
			'fajr_iqamah',
			'dhuhr_iqamah',
			'asr_iqamah',
			'maghrib_iqamah',
			'isha_iqamah',
		];

		$lines = [ implode( ',', $headers ) ];
		foreach ( $days as $date ) {
			$row = $store['days'][ $date ] ?? [];
			$azan = is_array( $row['azan'] ?? null ) ? $row['azan'] : [];
			$iqamah = is_array( $row['iqamah'] ?? null ) ? $row['iqamah'] : [];
			$cells = [
				$date,
				(string) ( $azan['fajr'] ?? '' ),
				(string) ( $azan['sunrise'] ?? '' ),
				(string) ( $azan['dhuhr'] ?? '' ),
				(string) ( $azan['asr'] ?? '' ),
				(string) ( $azan['maghrib'] ?? '' ),
				(string) ( $azan['isha'] ?? '' ),
				(string) ( $iqamah['fajr'] ?? '' ),
				(string) ( $iqamah['dhuhr'] ?? '' ),
				(string) ( $iqamah['asr'] ?? '' ),
				(string) ( $iqamah['maghrib'] ?? '' ),
				(string) ( $iqamah['isha'] ?? '' ),
			];
			$lines[] = implode( ',', array_map( [ self::class, 'escape_csv_cell' ], $cells ) );
		}

		return implode( "\n", $lines ) . ( count( $lines ) > 1 ? "\n" : '' );
	}

	/**
	 * Remove imported days for one Gregorian year.
	 *
	 * @return array<string,mixed>
	 */
	public static function clear_year( int $year ): array {
		$year = max( 1970, min( 2099, $year ) );
		$prefix = sprintf( '%04d-', $year );
		$store  = self::get_store();
		$removed = 0;

		foreach ( array_keys( $store['days'] ) as $date ) {
			if ( 0 === strpos( (string) $date, $prefix ) ) {
				unset( $store['days'][ $date ] );
				++$removed;
			}
		}

		if ( $removed > 0 ) {
			$store['updated_at'] = gmdate( 'c' );
			if ( empty( $store['days'] ) ) {
				delete_option( self::OPTION_KEY );
			} else {
				update_option( self::OPTION_KEY, $store, false );
			}
			ITMMS_Prayer_Times::flush_cache();
		}

		return [
			'success' => true,
			'removed' => $removed,
			'year'    => $year,
			'summary' => self::summary(),
		];
	}

	/**
	 * Export calculated prayer times for a Gregorian year.
	 */
	public static function export_calculated_year_csv( int $year ): string {
		$settings = ITMMS_Settings::get_all();
		$timezone = new DateTimeZone( (string) ( $settings['timezone'] ?? wp_timezone_string() ) );
		$year = max( 1970, min( 2099, $year ) );
		$start = new DateTimeImmutable( sprintf( '%04d-01-01 00:00:00', $year ), $timezone );
		$end = new DateTimeImmutable( sprintf( '%04d-12-31 00:00:00', $year ), $timezone );

		$headers = [
			'date',
			'fajr',
			'sunrise',
			'dhuhr',
			'asr',
			'maghrib',
			'isha',
			'fajr_iqamah',
			'dhuhr_iqamah',
			'asr_iqamah',
			'maghrib_iqamah',
			'isha_iqamah',
		];
		$lines = [ implode( ',', $headers ) ];

		for ( $date = $start; $date <= $end; $date = $date->modify( '+1 day' ) ) {
			$result = ITMMS_Prayer_Times::calculated_for_date( $date, $settings, false );
			$indexed = [];
			foreach ( (array) ( $result['prayers'] ?? [] ) as $prayer ) {
				if ( ! empty( $prayer['key'] ) ) {
					$indexed[ (string) $prayer['key'] ] = $prayer;
				}
			}

			$cells = [
				$date->format( 'Y-m-d' ),
				self::display_time_to_24h( (string) ( $indexed['fajr']['time'] ?? '' ) ),
				self::display_time_to_24h( (string) ( $indexed['sunrise']['time'] ?? '' ) ),
				self::display_time_to_24h( (string) ( $indexed['dhuhr']['time'] ?? '' ) ),
				self::display_time_to_24h( (string) ( $indexed['asr']['time'] ?? '' ) ),
				self::display_time_to_24h( (string) ( $indexed['maghrib']['time'] ?? '' ) ),
				self::display_time_to_24h( (string) ( $indexed['isha']['time'] ?? '' ) ),
				self::display_time_to_24h( (string) ( $indexed['fajr']['iqamah'] ?? '' ) ),
				self::display_time_to_24h( (string) ( $indexed['dhuhr']['iqamah'] ?? '' ) ),
				self::display_time_to_24h( (string) ( $indexed['asr']['iqamah'] ?? '' ) ),
				self::display_time_to_24h( (string) ( $indexed['maghrib']['iqamah'] ?? '' ) ),
				self::display_time_to_24h( (string) ( $indexed['isha']['iqamah'] ?? '' ) ),
			];
			$lines[] = implode( ',', array_map( [ self::class, 'escape_csv_cell' ], $cells ) );
		}

		return implode( "\n", $lines ) . "\n";
	}

	/**
	 * Sample CSV for mosque committees.
	 */
	public static function sample_csv(): string {
		return "date,fajr,sunrise,dhuhr,asr,maghrib,isha,fajr_iqamah,dhuhr_iqamah,asr_iqamah,maghrib_iqamah,isha_iqamah\n" .
			"2026-01-01,05:18,06:42,12:05,15:20,17:45,19:05,05:30,12:15,15:35,17:50,19:15\n" .
			"2026-01-02,05:19,06:42,12:05,15:21,17:46,19:06,05:31,12:15,15:36,17:51,19:16\n";
	}

	public static function clear(): void {
		delete_option( self::OPTION_KEY );
		ITMMS_Prayer_Times::flush_cache();
	}

	/**
	 * Parse flexible CSV headers and rows.
	 *
	 * @return array{rows:array<int,array<string,mixed>>,errors:array<int,string>}
	 */
	private static function parse_csv( string $csv ): array {
		$csv = trim( str_replace( [ "\r\n", "\r" ], "\n", $csv ) );
		if ( '' === $csv ) {
			return [
				'rows'   => [],
				'errors' => [ __( 'CSV file is empty.', 'masjidos' ) ],
			];
		}

		$lines = explode( "\n", $csv );
		$header_line = array_shift( $lines );
		if ( null === $header_line ) {
			return [
				'rows'   => [],
				'errors' => [ __( 'CSV header row is missing.', 'masjidos' ) ],
			];
		}

		$headers = self::parse_csv_line( $header_line );
		$map = self::map_headers( $headers );
		if ( ! isset( $map['date'] ) ) {
			return [
				'rows'   => [],
				'errors' => [ __( 'CSV must include a date column.', 'masjidos' ) ],
			];
		}

		$rows = [];
		$errors = [];
		$line_number = 1;

		foreach ( $lines as $line ) {
			++$line_number;
			$line = trim( $line );
			if ( '' === $line ) {
				continue;
			}

			$cells = self::parse_csv_line( $line );
			$date_raw = trim( (string) ( $cells[ $map['date'] ] ?? '' ) );
			$date = self::parse_date( $date_raw );
			if ( '' === $date ) {
				$errors[] = sprintf(
					/* translators: 1: CSV line number, 2: invalid date value. */
					__( 'Line %1$d: invalid date "%2$s".', 'masjidos' ),
					$line_number,
					$date_raw
				);
				continue;
			}

			$azan = [];
			foreach ( self::PRAYER_KEYS as $key ) {
				if ( ! isset( $map[ $key ] ) ) {
					continue;
				}
				$value = self::parse_time( (string) ( $cells[ $map[ $key ] ] ?? '' ) );
				if ( '' !== $value ) {
					$azan[ $key ] = $value;
				}
			}

			$iqamah = [];
			foreach ( self::IQAMAH_KEYS as $key ) {
				$column = $key . '_iqamah';
				if ( ! isset( $map[ $column ] ) ) {
					continue;
				}
				$value = self::parse_time( (string) ( $cells[ $map[ $column ] ] ?? '' ) );
				if ( '' !== $value ) {
					$iqamah[ $key ] = $value;
				}
			}

			if ( empty( $azan ) && empty( $iqamah ) ) {
				$errors[] = sprintf(
					/* translators: %d: CSV line number. */
					__( 'Line %d: no prayer or Iqamah times found.', 'masjidos' ),
					$line_number
				);
				continue;
			}

			$rows[ $line_number ] = [
				'date'   => $date,
				'azan'   => $azan,
				'iqamah' => $iqamah,
			];
		}

		return [
			'rows'   => $rows,
			'errors' => $errors,
		];
	}

	/**
	 * @param array<int,string> $headers
	 * @return array<string,int>
	 */
	private static function map_headers( array $headers ): array {
		$aliases = [
			'date'           => [ 'date', 'day', 'gregorian', 'gregorian_date' ],
			'fajr'           => [ 'fajr', 'fajr_azan', 'fajr_adhan', 'fajr_start' ],
			'sunrise'        => [ 'sunrise', 'sun', 'shuruq' ],
			'dhuhr'          => [ 'dhuhr', 'zuhr', 'zohar', 'zuhar', 'dhuhr_azan' ],
			'asr'            => [ 'asr', 'asr_azan' ],
			'maghrib'        => [ 'maghrib', 'maghrib_azan' ],
			'isha'           => [ 'isha', 'isha_azan' ],
			'fajr_iqamah'    => [ 'fajr_iqamah', 'fajr_iqama', 'fajr_jamaat', 'fajr_jamat', 'j_fajr', 'fajr_jamah' ],
			'dhuhr_iqamah'   => [ 'dhuhr_iqamah', 'dhuhr_iqama', 'zuhr_iqamah', 'dhuhr_jamaat', 'j_dhuhr', 'j_zuhr' ],
			'asr_iqamah'     => [ 'asr_iqamah', 'asr_iqama', 'asr_jamaat', 'j_asr' ],
			'maghrib_iqamah' => [ 'maghrib_iqamah', 'maghrib_iqama', 'maghrib_jamaat', 'j_maghrib' ],
			'isha_iqamah'    => [ 'isha_iqamah', 'isha_iqama', 'isha_jamaat', 'j_isha' ],
		];

		$map = [];
		foreach ( $headers as $index => $header ) {
			$normalized = self::normalize_header( $header );
			foreach ( $aliases as $field => $options ) {
				if ( in_array( $normalized, $options, true ) && ! isset( $map[ $field ] ) ) {
					$map[ $field ] = $index;
				}
			}
		}

		return $map;
	}

	private static function normalize_header( string $header ): string {
		$header = strtolower( trim( $header ) );
		$header = preg_replace( '/[^a-z0-9]+/', '_', $header );
		return trim( (string) $header, '_' );
	}

	/**
	 * @return array<int,string>
	 */
	private static function parse_csv_line( string $line ): array {
		return str_getcsv( $line );
	}

	private static function normalize_date_key( string $date ): string {
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			return $date;
		}
		return self::parse_date( $date );
	}

	private static function parse_date( string $value ): string {
		$value = trim( $value );
		if ( '' === $value ) {
			return '';
		}

		$formats = [ 'Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y', 'm-d-Y', 'd.m.Y', 'j/n/Y', 'n/j/Y' ];
		foreach ( $formats as $format ) {
			$date = DateTimeImmutable::createFromFormat( $format, $value );
			if ( $date instanceof DateTimeImmutable ) {
				return $date->format( 'Y-m-d' );
			}
		}

		$timestamp = strtotime( $value );
		if ( false !== $timestamp ) {
			return gmdate( 'Y-m-d', $timestamp );
		}

		return '';
	}

	/**
	 * Normalize a time string to 24-hour HH:MM.
	 */
	public static function parse_time( string $value ): string {
		$value = trim( preg_replace( '/\s+/', ' ', $value ) );
		if ( '' === $value ) {
			return '';
		}

		if ( preg_match( '/^(\d{1,2}):(\d{2})$/', $value, $matches ) ) {
			$hour = (int) $matches[1];
			$minute = (int) $matches[2];
			if ( $hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59 ) {
				return sprintf( '%02d:%02d', $hour, $minute );
			}
		}

		if ( preg_match( '/^(\d{1,2}):(\d{2})\s*([AP]M)$/i', $value, $matches ) ) {
			$hour = (int) $matches[1];
			$minute = (int) $matches[2];
			$meridiem = strtoupper( $matches[3] );
			if ( 'PM' === $meridiem && $hour < 12 ) {
				$hour += 12;
			}
			if ( 'AM' === $meridiem && 12 === $hour ) {
				$hour = 0;
			}
			if ( $hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59 ) {
				return sprintf( '%02d:%02d', $hour, $minute );
			}
		}

		return '';
	}

	/**
	 * Convert minutes after midnight to HH:MM.
	 */
	public static function minutes_to_time( float $minutes ): string {
		$minutes = (int) round( $minutes );
		$minutes = ( $minutes % 1440 + 1440 ) % 1440;
		return sprintf( '%02d:%02d', intdiv( $minutes, 60 ), $minutes % 60 );
	}

	/**
	 * Convert HH:MM to minutes after midnight.
	 */
	public static function time_to_minutes( string $time ): float {
		$time = self::parse_time( $time );
		if ( '' === $time ) {
			return 0.0;
		}
		[ $hour, $minute ] = array_map( 'intval', explode( ':', $time ) );
		return ( $hour * 60 ) + $minute;
	}

	private static function display_time_to_24h( string $display ): string {
		if ( '' === $display ) {
			return '';
		}
		return self::parse_time( $display );
	}

	private static function escape_csv_cell( string $value ): string {
		if ( false !== strpos( $value, ',' ) || false !== strpos( $value, '"' ) || false !== strpos( $value, "\n" ) ) {
			return '"' . str_replace( '"', '""', $value ) . '"';
		}
		return $value;
	}
}
