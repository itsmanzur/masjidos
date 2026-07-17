<?php
/**
 * SalahAPI 1.0 document + CSV builders.
 *
 * @package MasjidOS
 * @link https://github.com/salahapi/salahapi-specification
 */

defined( 'ABSPATH' ) || exit;

/**
 * Builds SalahAPI-compatible prayer metadata and CSV payloads.
 */
final class ITMMS_SalahAPI {

	public const VERSION = '1.0';

	/**
	 * Public SalahAPI document (metadata + CSV pointer).
	 *
	 * @return array<string,mixed>
	 */
	public static function document(): array {
		$settings = ITMMS_Settings::get_all();
		$method_key = (string) ( $settings['calculation_method'] ?? 'karachi' );
		$method = ITMMS_Prayer_Times::calculation_methods()[ $method_key ]
			?? ITMMS_Prayer_Times::calculation_methods()['karachi'];

		$csv_url = rest_url( 'masjidos/v1/salahapi/csv' );

		$doc = [
			'salahapi'           => self::VERSION,
			'info'               => [
				'title'       => (string) ( $settings['masjid_name'] ?? get_bloginfo( 'name' ) ),
				'description' => sprintf(
					/* translators: 1: city, 2: calculation method label */
					__( 'Prayer times for %1$s using %2$s', 'masjidos' ),
					trim( (string) ( $settings['city'] ?? '' ) . ', ' . (string) ( $settings['country'] ?? '' ), ' ,' ),
					(string) ( $method['label'] ?? $method_key )
				),
				'version'     => defined( 'ITMMS_VERSION' ) ? ITMMS_VERSION : '1.0.0',
				'contact'     => [
					'name'  => (string) ( $settings['masjid_name'] ?? get_bloginfo( 'name' ) ),
					'email' => (string) get_bloginfo( 'admin_email' ),
				],
			],
			'location'           => [
				'latitude'   => (float) ( $settings['latitude'] ?? 0 ),
				'longitude'  => (float) ( $settings['longitude'] ?? 0 ),
				'timezone'   => (string) ( $settings['timezone'] ?? wp_timezone_string() ),
				'city'       => (string) ( $settings['city'] ?? '' ),
				'country'    => (string) ( $settings['country'] ?? '' ),
				'dateFormat' => 'YYYY-MM-DD',
				'timeFormat' => 'HH:mm:ss',
			],
			'calculationMethod'  => self::calculation_method_object( $settings, $method_key, $method ),
			'dailyPrayerTimes'   => [
				'csvUrl'           => $csv_url,
				'csvUrlParameters' => [
					'fromDate' => [
						'in'     => 'query',
						'type'   => 'fromDate',
						'format' => 'YYYY-MM-DD',
					],
					'toDate'   => [
						'in'     => 'query',
						'type'   => 'toDate',
						'format' => 'YYYY-MM-DD',
					],
				],
				'dateFormat'       => 'YYYY-MM-DD',
				'timeFormat'       => 'HH:mm:ss',
			],
		];

		/**
		 * Filter the public SalahAPI document.
		 *
		 * @param array<string,mixed> $doc      Document.
		 * @param array<string,mixed> $settings Settings.
		 */
		return apply_filters( 'masjidos_salahapi_document', $doc, $settings );
	}

	/**
	 * Build SalahAPI CSV for an inclusive date range.
	 *
	 * @param string $from_date Y-m-d.
	 * @param string $to_date   Y-m-d.
	 */
	public static function csv( string $from_date, string $to_date ): string {
		$settings = ITMMS_Settings::get_all();
		$timezone = self::timezone( (string) ( $settings['timezone'] ?? '' ) );

		$from = DateTimeImmutable::createFromFormat( 'Y-m-d', $from_date, $timezone );
		$to = DateTimeImmutable::createFromFormat( 'Y-m-d', $to_date, $timezone );
		if ( ! $from || ! $to ) {
			return self::csv_header() . "\n";
		}

		$from = $from->setTime( 0, 0, 0 );
		$to = $to->setTime( 0, 0, 0 );
		if ( $from > $to ) {
			$tmp = $from;
			$from = $to;
			$to = $tmp;
		}

		// Cap range to keep requests bounded (max ~400 days).
		$max = $from->modify( '+399 days' );
		if ( $to > $max ) {
			$to = $max;
		}

		$lines = [ self::csv_header() ];
		$cursor = $from;
		while ( $cursor <= $to ) {
			$day = ITMMS_Prayer_Times::for_date( $cursor, $settings, false );
			$lines[] = self::csv_row( $cursor->format( 'Y-m-d' ), $day );
			$cursor = $cursor->modify( '+1 day' );
		}

		return implode( "\n", $lines ) . "\n";
	}

	/**
	 * Compact headless day payload for apps / kiosks.
	 *
	 * @param array<string,mixed> $day Prayer times result from ITMMS_Prayer_Times.
	 * @return array<string,mixed>
	 */
	public static function headless_day( array $day ): array {
		$prayers = [];
		foreach ( (array) ( $day['prayers'] ?? [] ) as $prayer ) {
			if ( ! is_array( $prayer ) ) {
				continue;
			}
			$key = (string) ( $prayer['key'] ?? '' );
			$prayers[] = [
				'key'       => $key,
				'name'      => (string) ( $prayer['name'] ?? '' ),
				'arabic'    => (string) ( $prayer['arabic'] ?? '' ),
				'kind'      => (string) ( $prayer['kind'] ?? 'fard' ),
				'athan'     => self::to_hhmmss_from_raw( (string) ( $prayer['raw'] ?? '' ) ),
				'iqama'     => self::display_time_to_hhmmss( (string) ( $prayer['iqamah'] ?? '' ), (string) ( $day['date'] ?? '' ), (string) ( $day['timezone'] ?? '' ) ),
				'time'      => (string) ( $prayer['time'] ?? '' ),
				'iqamah'    => (string) ( $prayer['iqamah'] ?? '' ),
				'base_time' => (string) ( $prayer['base_time'] ?? '' ),
				'offset'    => (int) ( $prayer['offset'] ?? 0 ),
				'raw'       => (string) ( $prayer['raw'] ?? '' ),
				'timestamp' => (int) ( $prayer['timestamp'] ?? 0 ),
				'current'   => ! empty( $prayer['current'] ),
			];
		}

		$next = is_array( $day['next_prayer'] ?? null ) ? $day['next_prayer'] : null;

		return [
			'date'        => (string) ( $day['date'] ?? '' ),
			'timezone'    => (string) ( $day['timezone'] ?? '' ),
			'hijri_date'  => (string) ( $day['hijri_date'] ?? '' ),
			'prayers'     => $prayers,
			'next_prayer' => $next,
			'meta'        => is_array( $day['meta'] ?? null ) ? $day['meta'] : [],
		];
	}

	/**
	 * @param array<string,mixed> $settings Plugin settings.
	 * @param array<string,mixed> $method   Method preset row.
	 * @return array<string,mixed>
	 */
	private static function calculation_method_object( array $settings, string $method_key, array $method ): array {
		$asr = (string) ( $settings['asr_method'] ?? 'hanafi' );
		$object = [
			'name'                   => sanitize_key( $method_key ),
			'fajrAngle'              => isset( $method['fajr_angle'] ) ? (float) $method['fajr_angle'] : null,
			'ishaAngle'              => isset( $method['isha_angle'] ) ? (float) $method['isha_angle'] : null,
			'asrCalculationMethod'   => 'hanafi' === $asr ? 'hanafi' : 'standard',
			'highLatitudeAdjustment' => 'middleOfTheNight',
			'iqamaCalculationRules'  => self::iqama_rules_object( $settings ),
			'jumuahRules'            => self::jumuah_rules( $settings ),
		];

		if ( null === $object['fajrAngle'] ) {
			unset( $object['fajrAngle'] );
		}
		if ( null === $object['ishaAngle'] ) {
			unset( $object['ishaAngle'] );
		}

		return $object;
	}

	/**
	 * @param array<string,mixed> $settings Plugin settings.
	 * @return array<string,mixed>
	 */
	private static function iqama_rules_object( array $settings ): array {
		$rules = ITMMS_Iqamah_Rules::normalized( $settings );
		$fixed = is_array( $settings['iqamah_times'] ?? null ) ? $settings['iqamah_times'] : [];
		$out = [ 'changeOn' => 'friday' ];

		foreach ( [ 'fajr', 'dhuhr', 'asr', 'maghrib', 'isha' ] as $key ) {
			$row = $rules[ $key ] ?? [];
			$mode = (string) ( $row['mode'] ?? 'fixed' );
			$minutes = (int) ( $row['minutes'] ?? 0 );
			$round = (int) ( $row['round'] ?? 0 );
			$rule = [];

			if ( 'none' === $mode ) {
				$rule['static'] = 'none';
			} elseif ( 'fixed' === $mode ) {
				$time = (string) ( $fixed[ $key ] ?? '' );
				if ( preg_match( '/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time ) ) {
					$rule['static'] = $time;
				} else {
					$rule['static'] = 'none';
				}
			} elseif ( 'before_sunrise' === $mode ) {
				$rule['change'] = 'daily';
				$rule['beforeEndMinutes'] = $minutes;
				if ( $round > 0 ) {
					$rule['roundMinutes'] = $round;
				}
			} else {
				$rule['change'] = 'daily';
				$rule['afterAthanMinutes'] = $minutes;
				if ( $round > 0 ) {
					$rule['roundMinutes'] = $round;
				}
			}

			$out[ $key ] = $rule;
		}

		return $out;
	}

	/**
	 * @param array<string,mixed> $settings Plugin settings.
	 * @return array<int,array<string,mixed>>
	 */
	private static function jumuah_rules( array $settings ): array {
		$jumuah = is_array( $settings['jumuah'] ?? null ) ? $settings['jumuah'] : [];
		if ( empty( $jumuah['enabled'] ) ) {
			return [];
		}

		$masjid = (string) ( $settings['masjid_name'] ?? get_bloginfo( 'name' ) );
		$address = trim( (string) ( $settings['city'] ?? '' ) . ', ' . (string) ( $settings['country'] ?? '' ), ' ,' );
		$sessions = isset( $jumuah['sessions'] ) && is_array( $jumuah['sessions'] ) ? $jumuah['sessions'] : [];
		$rules = [];

		foreach ( $sessions as $index => $session ) {
			if ( ! is_array( $session ) ) {
				continue;
			}
			$jamaat = (string) ( $session['jamaat_time'] ?? '' );
			$label = (string) ( $session['label'] ?? '' );
			if ( '' === $label ) {
				$label = sprintf(
					/* translators: %d: Jumuah session number */
					__( 'Jumuah %d', 'masjidos' ),
					(int) $index + 1
				);
			}

			$rules[] = [
				'name'     => $label,
				'time'     => [
					'static' => preg_match( '/^(?:[01]\d|2[0-3]):[0-5]\d$/', $jamaat ) ? $jamaat : 'none',
				],
				'location' => [
					'name'    => $masjid,
					'address' => $address,
				],
			];
		}

		if ( empty( $rules ) ) {
			$jamaat = (string) ( $jumuah['jamaat_time'] ?? '' );
			$rules[] = [
				'name'     => __( 'Jumuah', 'masjidos' ),
				'time'     => [
					'static' => preg_match( '/^(?:[01]\d|2[0-3]):[0-5]\d$/', $jamaat ) ? $jamaat : 'none',
				],
				'location' => [
					'name'    => $masjid,
					'address' => $address,
				],
			];
		}

		return $rules;
	}

	private static function csv_header(): string {
		return 'day,fajr_athan,fajr_iqama,sunrise,dhuhr_athan,dhuhr_iqama,asr_athan,asr_iqama,maghrib_athan,maghrib_iqama,isha_athan,isha_iqama';
	}

	/**
	 * @param array<string,mixed> $day Day result.
	 */
	private static function csv_row( string $day, array $day_result ): string {
		$indexed = [];
		foreach ( (array) ( $day_result['prayers'] ?? [] ) as $prayer ) {
			if ( ! is_array( $prayer ) || empty( $prayer['key'] ) ) {
				continue;
			}
			$indexed[ (string) $prayer['key'] ] = $prayer;
		}

		$tz = (string) ( $day_result['timezone'] ?? '' );
		$cells = [ $day ];
		foreach ( [ 'fajr', 'sunrise', 'dhuhr', 'asr', 'maghrib', 'isha' ] as $key ) {
			$row = $indexed[ $key ] ?? [];
			$athan = self::to_hhmmss_from_raw( (string) ( $row['raw'] ?? '' ) );
			$cells[] = $athan;
			if ( 'sunrise' !== $key ) {
				$cells[] = self::display_time_to_hhmmss( (string) ( $row['iqamah'] ?? '' ), $day, $tz );
			}
		}

		return self::csv_escape_row( $cells );
	}

	/**
	 * @param array<int,string> $cells Row cells.
	 */
	private static function csv_escape_row( array $cells ): string {
		$escaped = [];
		foreach ( $cells as $cell ) {
			$cell = (string) $cell;
			if ( strpbrk( $cell, ",\"\n\r" ) !== false ) {
				$cell = '"' . str_replace( '"', '""', $cell ) . '"';
			}
			$escaped[] = $cell;
		}
		return implode( ',', $escaped );
	}

	private static function to_hhmmss_from_raw( string $raw ): string {
		if ( '' === $raw ) {
			return '';
		}
		try {
			$dt = new DateTimeImmutable( $raw );
			return $dt->format( 'H:i:s' );
		} catch ( Exception $e ) {
			return '';
		}
	}

	private static function display_time_to_hhmmss( string $display, string $date, string $timezone ): string {
		$display = trim( $display );
		if ( '' === $display ) {
			return '';
		}

		if ( preg_match( '/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/', $display ) ) {
			$parts = explode( ':', $display );
			$h = (int) $parts[0];
			$m = (int) $parts[1];
			$s = isset( $parts[2] ) ? (int) $parts[2] : 0;
			return sprintf( '%02d:%02d:%02d', $h, $m, $s );
		}

		try {
			$tz = self::timezone( $timezone );
			$base = $date ? $date . ' ' . $display : $display;
			$dt = DateTimeImmutable::createFromFormat( 'Y-m-d g:i A', $base, $tz );
			if ( ! $dt ) {
				$dt = new DateTimeImmutable( $base, $tz );
			}
			return $dt->format( 'H:i:s' );
		} catch ( Exception $e ) {
			return '';
		}
	}

	private static function timezone( string $timezone ): DateTimeZone {
		try {
			return new DateTimeZone( $timezone ?: wp_timezone_string() );
		} catch ( Exception $e ) {
			return wp_timezone();
		}
	}
}
