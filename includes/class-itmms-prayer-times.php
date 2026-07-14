<?php
/**
 * Local prayer time calculation engine.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Calculates daily prayer times without an external API.
 */
final class ITMMS_Prayer_Times {

	/**
	 * Get today's prayer times using saved settings.
	 *
	 * @return array<string,mixed>
	 */
	public static function today(): array {
		$settings = ITMMS_Settings::get_all();
		$timezone = self::timezone( $settings['timezone'] ?? '' );
		$date = new DateTimeImmutable( 'now', $timezone );

		return self::for_date( $date, $settings );
	}

	/**
	 * Calculate prayer times for a date.
	 *
	 * @param DateTimeImmutable  $date Date in masjid timezone.
	 * @param array<string,mixed> $settings Plugin settings.
	 * @return array<string,mixed>
	 */
	public static function for_date( DateTimeImmutable $date, array $settings, bool $refresh_dynamic = true ): array {
		$timezone = self::timezone( $settings['timezone'] ?? '' );
		$date = $date->setTimezone( $timezone );

		$source = (string) ( $settings['prayer_source'] ?? 'local' );
		$cache_key = 'itmms_prayers_' . md5(
			$date->format( 'Y-m-d' ) . '|' .
			wp_json_encode(
				[
					$settings['latitude'] ?? '',
					$settings['longitude'] ?? '',
					$settings['timezone'] ?? '',
					$settings['calculation_method'] ?? '',
					$settings['asr_method'] ?? '',
					$settings['prayer_offsets'] ?? [],
					$settings['iqamah_times'] ?? [],
					$settings['hijri_adjustment'] ?? 0,
					$source,
					$settings['city'] ?? '',
					$settings['country'] ?? '',
					'calculation-registry-v3',
				]
			)
		);

		$now = new DateTimeImmutable( 'now', $timezone );
		$cacheable = abs( $date->getTimestamp() - $now->getTimestamp() ) <= ( 400 * DAY_IN_SECONDS );
		if ( $cacheable ) {
			$cached = get_transient( $cache_key );
			if ( is_array( $cached ) ) {
				return $refresh_dynamic ? self::refresh_dynamic_state( $cached, $date ) : $cached;
			}
		}

		$offsets = self::offsets( $settings['prayer_offsets'] ?? [] );
		$iqamah_times = self::iqamah_times( $settings['iqamah_times'] ?? [] );
		$method_key = (string) ( $settings['calculation_method'] ?? 'karachi' );
		$method = self::method( $method_key );

		// Use Aladhan API if enabled
		if ( 'aladhan' === $source ) {
			$year = (int) $date->format( 'Y' );
			$month = (int) $date->format( 'n' );
			$aladhan_month = self::fetch_aladhan_month( $year, $month, $settings );

			if ( is_array( $aladhan_month ) ) {
				$day_index = (int) $date->format( 'j' ) - 1;
				if ( isset( $aladhan_month[ $day_index ]['timings'] ) ) {
					$timings = $aladhan_month[ $day_index ]['timings'];
					$base_times = [
						'fajr'    => self::time_to_minutes( (string) $timings['Fajr'] ),
						'sunrise' => self::time_to_minutes( (string) $timings['Sunrise'] ),
						'dhuhr'   => self::time_to_minutes( (string) $timings['Dhuhr'] ),
						'asr'     => self::time_to_minutes( (string) $timings['Asr'] ),
						'maghrib' => self::time_to_minutes( (string) $timings['Maghrib'] ),
						'isha'    => self::time_to_minutes( (string) $timings['Isha'] ),
					];

					$times = $base_times;
					foreach ( $times as $key => $minutes ) {
						$times[ $key ] = $minutes + ( $offsets[ $key ] ?? 0 );
					}

					$result = self::format_result( $date, $times, $base_times, $offsets, $iqamah_times, $settings, $method, $method_key );
					$result['meta']['calculation_method'] = 'Auto API (' . self::method_label( $method_key ) . ')';

					if ( $cacheable ) {
						set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
					}
					return $refresh_dynamic ? self::refresh_dynamic_state( $result, $date ) : $result;
				}
			}
		}

		// Fallback/Default local calculation
		$latitude = (float) ( $settings['latitude'] ?? 23.8103 );
		$longitude = (float) ( $settings['longitude'] ?? 90.4125 );
		$asr_factor = ( 'hanafi' === ( $settings['asr_method'] ?? 'hanafi' ) ) ? 2.0 : 1.0;

		$day = (int) $date->format( 'z' ) + 1;
		$tz_offset = $timezone->getOffset( $date ) / HOUR_IN_SECONDS;
		$solar = self::solar_position( $day );
		$solar_noon = 720 - ( $solar['equation'] + ( 4 * $longitude ) - ( 60 * $tz_offset ) );

		$maghrib_zenith = isset( $method['maghrib_angle'] ) ? 90 + (float) $method['maghrib_angle'] : 90.833;
		$maghrib_minutes = $solar_noon + self::hour_angle_minutes( $latitude, $solar['declination'], $maghrib_zenith );
		$base_times = [
			'fajr'    => $solar_noon - self::hour_angle_minutes( $latitude, $solar['declination'], 90 + $method['fajr_angle'] ),
			'sunrise' => $solar_noon - self::hour_angle_minutes( $latitude, $solar['declination'], 90.833 ),
			'dhuhr'   => $solar_noon,
			'asr'     => $solar_noon + self::asr_minutes( $latitude, $solar['declination'], $asr_factor ),
			'maghrib' => $maghrib_minutes,
			'isha'    => isset( $method['isha_interval'] )
				? $maghrib_minutes + $method['isha_interval']
				: $solar_noon + self::hour_angle_minutes( $latitude, $solar['declination'], 90 + $method['isha_angle'] ),
		];
		$times = $base_times;

		foreach ( $times as $key => $minutes ) {
			$times[ $key ] = $minutes + ( $offsets[ $key ] ?? 0 );
		}

		$result = self::format_result( $date, $times, $base_times, $offsets, $iqamah_times, $settings, $method, $method_key );
		if ( $cacheable ) {
			set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
		}

		return $refresh_dynamic ? self::refresh_dynamic_state( $result, $date ) : $result;
	}

	/**
	 * Calculate prayer times for every day in a month.
	 *
	 * @param int                 $year Gregorian year.
	 * @param int                 $month Gregorian month number.
	 * @param array<string,mixed> $settings Plugin settings.
	 * @return array<string,mixed>
	 */
	public static function for_month( int $year, int $month, array $settings ): array {
		$timezone = self::timezone( $settings['timezone'] ?? '' );
		$year = max( 1970, min( 2099, $year ) );
		$month = max( 1, min( 12, $month ) );
		$start = new DateTimeImmutable( sprintf( '%04d-%02d-01 00:00:00', $year, $month ), $timezone );
		$days = (int) $start->format( 't' );
		$rows = [];

		for ( $day = 1; $day <= $days; $day++ ) {
			$date = $start->setDate( $year, $month, $day );
			$rows[] = self::for_date( $date, $settings, false );
		}

		$first = $rows[0] ?? self::for_date( $start, $settings, false );
		return [
			'year'     => $year,
			'month'    => $month,
			'label'    => $start->format( 'F Y' ),
			'timezone' => $timezone->getName(),
			'days'     => $rows,
			'meta'     => $first['meta'] ?? [],
		];
	}

	/**
	 * Calculate Qibla direction in degrees clockwise from true north.
	 */
	public static function qibla_direction( float $latitude, float $longitude ): float {
		$kaaba_lat = deg2rad( 21.422487 );
		$kaaba_lng = deg2rad( 39.826206 );
		$lat = deg2rad( $latitude );
		$lng = deg2rad( $longitude );
		$delta_lng = $kaaba_lng - $lng;

		$y = sin( $delta_lng );
		$x = ( cos( $lat ) * tan( $kaaba_lat ) ) - ( sin( $lat ) * cos( $delta_lng ) );
		$bearing = rad2deg( atan2( $y, $x ) );

		return round( fmod( $bearing + 360, 360 ), 1 );
	}

	/**
	 * @return array<string,float>
	 */
	private static function solar_position( int $day_of_year ): array {
		$gamma = ( 2 * M_PI / 365 ) * ( $day_of_year - 1 );

		$equation = 229.18 * (
			0.000075 +
			0.001868 * cos( $gamma ) -
			0.032077 * sin( $gamma ) -
			0.014615 * cos( 2 * $gamma ) -
			0.040849 * sin( 2 * $gamma )
		);

		$declination = 0.006918 -
			0.399912 * cos( $gamma ) +
			0.070257 * sin( $gamma ) -
			0.006758 * cos( 2 * $gamma ) +
			0.000907 * sin( 2 * $gamma ) -
			0.002697 * cos( 3 * $gamma ) +
			0.00148 * sin( 3 * $gamma );

		return [
			'equation'    => $equation,
			'declination' => rad2deg( $declination ),
		];
	}

	private static function hour_angle_minutes( float $latitude, float $declination, float $zenith ): float {
		$lat = deg2rad( $latitude );
		$dec = deg2rad( $declination );
		$zen = deg2rad( $zenith );

		$cos_ha = ( cos( $zen ) - ( sin( $lat ) * sin( $dec ) ) ) / ( cos( $lat ) * cos( $dec ) );
		$cos_ha = max( -1, min( 1, $cos_ha ) );

		return rad2deg( acos( $cos_ha ) ) * 4;
	}

	private static function asr_minutes( float $latitude, float $declination, float $factor ): float {
		$shadow_angle = rad2deg( atan( 1 / ( $factor + tan( abs( deg2rad( $latitude - $declination ) ) ) ) ) );
		$zenith = 90 - $shadow_angle;

		return self::hour_angle_minutes( $latitude, $declination, $zenith );
	}

	/**
	 * @param array<string,float> $times Calculated minutes after midnight.
	 * @param array<string,mixed> $settings Plugin settings.
	 * @param array<string,mixed> $method Method settings.
	 * @return array<string,mixed>
	 */
	private static function format_result( DateTimeImmutable $date, array $times, array $base_times, array $offsets, array $iqamah_times, array $settings, array $method, string $method_key ): array {
		$labels = [
			'fajr'    => [ 'name' => 'Fajr', 'arabic' => '&#1601;&#1580;&#1585;' ],
			'sunrise' => [ 'name' => 'Sunrise', 'arabic' => '&#1588;&#1585;&#1608;&#1602;' ],
			'dhuhr'   => [ 'name' => 'Dhuhr', 'arabic' => '&#1592;&#1607;&#1585;' ],
			'asr'     => [ 'name' => 'Asr', 'arabic' => '&#1593;&#1589;&#1585;' ],
			'maghrib' => [ 'name' => 'Maghrib', 'arabic' => '&#1605;&#1594;&#1585;&#1576;' ],
			'isha'    => [ 'name' => 'Isha', 'arabic' => '&#1593;&#1588;&#1575;&#1569;' ],
		];

		$now = new DateTimeImmutable( 'now', $date->getTimezone() );
		$rows = [];
		$next = null;
		$previous_key = null;

		foreach ( $labels as $key => $label ) {
			$time = self::date_with_minutes( $date, $times[ $key ] );
			$rows[ $key ] = [
				'key'       => $key,
				'name'      => $label['name'],
				'arabic'    => html_entity_decode( $label['arabic'], ENT_QUOTES, 'UTF-8' ),
				'time'      => $time->format( 'g:i A' ),
				'iqamah'    => self::format_iqamah_time( $iqamah_times[ $key ] ?? '', $date ),
				'base_time' => self::date_with_minutes( $date, $base_times[ $key ] )->format( 'g:i A' ),
				'offset'    => $offsets[ $key ] ?? 0,
				'raw'       => $time->format( DATE_ATOM ),
				'timestamp' => $time->getTimestamp(),
				'current'   => false,
			];

			if ( null === $next && $time->getTimestamp() > $now->getTimestamp() ) {
				$next = $rows[ $key ];
			}

			if ( $time->getTimestamp() <= $now->getTimestamp() ) {
				$previous_key = $key;
			}
		}

		if ( null === $next ) {
			$tomorrow = $date->modify( '+1 day' );
			$fajr = self::date_with_minutes( $tomorrow, $times['fajr'] );
			$next = [
				'key'       => 'fajr',
				'name'      => 'Fajr',
				'arabic'    => html_entity_decode( '&#1601;&#1580;&#1585;', ENT_QUOTES, 'UTF-8' ),
				'time'      => $fajr->format( 'g:i A' ),
				'raw'       => $fajr->format( DATE_ATOM ),
				'timestamp' => $fajr->getTimestamp(),
				'current'   => false,
			];
		}

		if ( $previous_key && isset( $rows[ $previous_key ] ) ) {
			$rows[ $previous_key ]['current'] = true;
		}

		$latitude = (float) ( $settings['latitude'] ?? 0 );
		$longitude = (float) ( $settings['longitude'] ?? 0 );
		$hijri_adjustment = isset( $settings['hijri_adjustment'] ) ? (int) $settings['hijri_adjustment'] : 0;

		return [
			'date'        => $date->format( 'Y-m-d' ),
			'timezone'    => $date->getTimezone()->getName(),
			'hijri_date'  => ITMMS_Hijri::for_date( $date, $hijri_adjustment, 'en' ),
			'prayers'     => array_values( $rows ),
			'next_prayer' => $next,
			'meta'        => [
				'location'           => trim( (string) ( $settings['city'] ?? '' ) . ', ' . (string) ( $settings['country'] ?? '' ), ' ,' ),
				'latitude'           => $latitude,
				'longitude'          => $longitude,
				'timezone'           => $date->getTimezone()->getName(),
				'calculation_method' => self::method_label( $method_key ),
				'asr_method'         => ucfirst( (string) ( $settings['asr_method'] ?? 'hanafi' ) ),
				'fajr_angle'         => $method['fajr_angle'],
				'isha_angle'         => $method['isha_angle'] ?? null,
				'isha_interval'      => $method['isha_interval'] ?? null,
				'maghrib_angle'      => $method['maghrib_angle'] ?? null,
				'qibla_direction'    => self::qibla_direction( $latitude, $longitude ),
			],
		];
	}

	private static function date_with_minutes( DateTimeImmutable $date, float $minutes ): DateTimeImmutable {
		$minutes = (int) round( $minutes );
		$minutes = ( $minutes % 1440 + 1440 ) % 1440;
		$hours = intdiv( $minutes, 60 );
		$mins = $minutes % 60;

		return $date->setTime( $hours, $mins, 0 );
	}

	/**
	 * Keep cached calculated times, but refresh current and next prayer per request.
	 *
	 * @param array<string,mixed> $result Cached calculation result.
	 * @return array<string,mixed>
	 */
	private static function refresh_dynamic_state( array $result, DateTimeImmutable $date ): array {
		if ( empty( $result['prayers'] ) || ! is_array( $result['prayers'] ) ) {
			return $result;
		}

		$now = new DateTimeImmutable( 'now', $date->getTimezone() );
		$next = null;
		$previous_index = null;

		foreach ( $result['prayers'] as $index => $prayer ) {
			$result['prayers'][ $index ]['current'] = false;
			$timestamp = isset( $prayer['timestamp'] ) ? (int) $prayer['timestamp'] : 0;

			if ( null === $next && $timestamp > $now->getTimestamp() ) {
				$next = $result['prayers'][ $index ];
			}

			if ( $timestamp <= $now->getTimestamp() ) {
				$previous_index = $index;
			}
		}

		if ( null !== $previous_index && isset( $result['prayers'][ $previous_index ] ) ) {
			$result['prayers'][ $previous_index ]['current'] = true;
		}

		if ( null === $next && isset( $result['prayers'][0] ) && is_array( $result['prayers'][0] ) ) {
			$first = $result['prayers'][0];
			$raw = new DateTimeImmutable( (string) ( $first['raw'] ?? 'now' ) );
			$raw = $raw->setTimezone( $date->getTimezone() )->modify( '+1 day' );
			$first['raw'] = $raw->format( DATE_ATOM );
			$first['timestamp'] = $raw->getTimestamp();
			$next = $first;
		}

		$result['next_prayer'] = $next;
		return $result;
	}

	/**
	 * Available local calculation presets.
	 *
	 * These presets intentionally contain only data. Pro or site-specific code can
	 * filter the UI later without changing the local calculation engine.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function calculation_methods(): array {
		return [
			'karachi'   => [
				'label'      => __( 'Karachi', 'masjidos' ),
				'fajr_angle' => 18.0,
				'isha_angle' => 18.0,
			],
			'mwl'       => [
				'label'      => __( 'Muslim World League', 'masjidos' ),
				'fajr_angle' => 18.0,
				'isha_angle' => 17.0,
			],
			'isna'      => [
				'label'      => __( 'ISNA', 'masjidos' ),
				'fajr_angle' => 15.0,
				'isha_angle' => 15.0,
			],
			'egypt'     => [
				'label'      => __( 'Egyptian Authority', 'masjidos' ),
				'fajr_angle' => 19.5,
				'isha_angle' => 17.5,
			],
			'makkah'    => [
				'label'         => __( 'Umm al-Qura, Makkah', 'masjidos' ),
				'fajr_angle'    => 18.5,
				'isha_interval' => 90,
			],
			'dubai'     => [
				'label'      => __( 'Dubai', 'masjidos' ),
				'fajr_angle' => 18.2,
				'isha_angle' => 18.2,
			],
			'qatar'     => [
				'label'         => __( 'Qatar', 'masjidos' ),
				'fajr_angle'    => 18.0,
				'isha_interval' => 90,
			],
			'kuwait'    => [
				'label'      => __( 'Kuwait', 'masjidos' ),
				'fajr_angle' => 18.0,
				'isha_angle' => 17.5,
			],
			'singapore' => [
				'label'      => __( 'Singapore', 'masjidos' ),
				'fajr_angle' => 20.0,
				'isha_angle' => 18.0,
			],
			'tehran'    => [
				'label'         => __( 'Tehran', 'masjidos' ),
				'fajr_angle'    => 17.7,
				'maghrib_angle' => 4.5,
				'isha_angle'    => 14.0,
			],
			'jafari'    => [
				'label'         => __( 'Jafari', 'masjidos' ),
				'fajr_angle'    => 16.0,
				'maghrib_angle' => 4.0,
				'isha_angle'    => 14.0,
			],
		];
	}

	/**
	 * @return array<int,string>
	 */
	public static function calculation_method_keys(): array {
		return array_keys( self::calculation_methods() );
	}

	/**
	 * @return array<string,mixed>
	 */
	private static function method( string $method ): array {
		$methods = self::calculation_methods();
		return $methods[ $method ] ?? $methods['karachi'];
	}

	private static function method_label( string $method ): string {
		$methods = self::calculation_methods();
		return (string) ( $methods[ $method ]['label'] ?? $methods['karachi']['label'] );
	}

	private static function timezone( string $timezone ): DateTimeZone {
		try {
			return new DateTimeZone( $timezone ?: wp_timezone_string() );
		} catch ( Exception $e ) {
			return wp_timezone();
		}
	}

	/**
	 * @param mixed $offsets Raw offsets.
	 * @return array<string,int>
	 */
	private static function offsets( $offsets ): array {
		$defaults = [
			'fajr'    => 0,
			'sunrise' => 0,
			'dhuhr'   => 0,
			'asr'     => 0,
			'maghrib' => 0,
			'isha'    => 0,
		];

		if ( ! is_array( $offsets ) ) {
			return $defaults;
		}

		foreach ( $defaults as $key => $value ) {
			$defaults[ $key ] = isset( $offsets[ $key ] ) ? max( -60, min( 60, (int) $offsets[ $key ] ) ) : $value;
		}

		return $defaults;
	}

	/**
	 * @param mixed $times Raw Iqamah times.
	 * @return array<string,string>
	 */
	private static function iqamah_times( $times ): array {
		$defaults = [
			'fajr'    => '',
			'dhuhr'   => '',
			'asr'     => '',
			'maghrib' => '',
			'isha'    => '',
		];

		if ( ! is_array( $times ) ) {
			return $defaults;
		}

		foreach ( $defaults as $key => $value ) {
			$defaults[ $key ] = isset( $times[ $key ] ) && preg_match( '/^(?:[01]\d|2[0-3]):[0-5]\d$/', (string) $times[ $key ] ) ? (string) $times[ $key ] : $value;
		}

		return $defaults;
	}

	private static function format_iqamah_time( string $time, DateTimeImmutable $date ): string {
		if ( '' === $time ) {
			return '';
		}

		[ $hour, $minute ] = array_map( 'intval', explode( ':', $time ) );
		return $date->setTime( $hour, $minute, 0 )->format( 'g:i A' );
	}

	/**
	 * Fetch monthly calendar from Aladhan API.
	 *
	 * External service: https://aladhan.com — only called when prayer_source === 'aladhan'.
	 *
	 * @param int $year Gregorian year.
	 * @param int $month Gregorian month.
	 * @param array<string,mixed> $settings Plugin settings.
	 * @return array<int,array<string,mixed>>|null
	 */
	public static function fetch_aladhan_month( int $year, int $month, array $settings ): ?array {
		$city = trim( (string) ( $settings['city'] ?? 'Dhaka' ) );
		$country = trim( (string) ( $settings['country'] ?? 'Bangladesh' ) );
		$method_key = (string) ( $settings['calculation_method'] ?? 'karachi' );
		$method_id = self::map_to_aladhan_method( $method_key );
		$school = ( 'hanafi' === ( $settings['asr_method'] ?? 'hanafi' ) ) ? 1 : 0;

		$cache_key = 'itmms_aladhan_' . md5( sprintf( '%d-%d-%s-%s-%d-%d', $year, $month, strtolower( $city ), strtolower( $country ), $method_id, $school ) );
		$cached = get_transient( $cache_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$url = sprintf(
			'https://api.aladhan.com/v1/calendarByCity/%d/%d?city=%s&country=%s&method=%d&school=%d',
			$year,
			$month,
			rawurlencode( $city ),
			rawurlencode( $country ),
			$method_id,
			$school
		);

		$response = wp_remote_get( $url, [ 'timeout' => 10 ] );
		if ( is_wp_error( $response ) ) {
			return null;
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $code ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		if ( empty( $data['data'] ) || ! is_array( $data['data'] ) ) {
			return null;
		}

		set_transient( $cache_key, $data['data'], 30 * DAY_IN_SECONDS );
		return $data['data'];
	}

	/**
	 * Map local calculation method keys to Aladhan method IDs.
	 */
	public static function map_to_aladhan_method( string $method ): int {
		$map = [
			'karachi'   => 1,
			'mwl'       => 3,
			'isna'      => 2,
			'egypt'     => 5,
			'makkah'    => 4,
			'dubai'     => 8,
			'qatar'     => 10,
			'kuwait'    => 9,
			'singapore' => 11,
			'tehran'    => 7,
			'jafari'    => 16,
		];
		return $map[ $method ] ?? 1;
	}

	/**
	 * Clean and convert time strings (e.g. "04:18 (EEST)" or "04:18") to minutes after midnight.
	 */
	private static function time_to_minutes( string $time_str ): float {
		$time_str = preg_replace( '/\s*\(.*?\)/', '', $time_str );
		$parts = explode( ':', $time_str );
		if ( count( $parts ) < 2 ) {
			return 0.0;
		}
		return ( (int) $parts[0] ) * 60 + ( (int) $parts[1] );
	}
}
