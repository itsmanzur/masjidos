<?php
/**
 * Settings repository for MasjidOS.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Central settings API.
 */
final class ITMMS_Settings {

	public const OPTION_KEY = 'itmms_settings';

	/**
	 * Default plugin settings.
	 *
	 * @return array<string,mixed>
	 */
	public static function defaults(): array {
		return [
			'masjid_name'         => get_bloginfo( 'name' ),
			'city'                => 'Dhaka',
			'country'             => 'Bangladesh',
			'timezone'            => self::default_timezone(),
			'latitude'            => '23.8103',
			'longitude'           => '90.4125',
			'calculation_method'  => 'karachi',
			'asr_method'          => 'hanafi',
			'prayer_source'       => 'local',
			'hijri_adjustment'    => 0,
			'currency'            => 'BDT',
			'public_transparency' => true,
			'prayer_offsets'      => [
				'fajr'    => 0,
				'sunrise' => 0,
				'dhuhr'   => 0,
				'asr'     => 0,
				'maghrib' => 0,
				'isha'    => 0,
			],
			'iqamah_times'        => [
				'fajr'    => '',
				'dhuhr'   => '',
				'asr'     => '',
				'maghrib' => '',
				'isha'    => '',
			],
			'jumuah'              => [
				'enabled'      => true,
				'khutbah_time' => '13:00',
				'jamaat_time'  => '13:30',
				'topic'        => '',
				'language'     => '',
				'khatib'       => [
					'name'      => '',
					'image_url' => '',
					'bio'       => '',
				],
				'sessions'     => [
					[
						'label'       => 'First Jumuah',
						'khutbah_time' => '13:00',
						'jamaat_time'  => '13:30',
					],
					[
						'label'       => 'Second Jumuah',
						'khutbah_time' => '',
						'jamaat_time'  => '',
					],
				],
				'notice'       => '',
			],
			'modules'             => [
				'prayer_times'  => true,
				'announcements' => true,
				'events'        => false,
				'duas_azkar'    => true,
			],
			'tv_theme'              => 'dark',
			'tv_announcement_speed' => 7,
			'tv_logo_url'           => '',
			'tv_font_size'          => 'normal',
		];
	}

	/**
	 * Ensure default settings exist.
	 */
	public static function install_defaults(): void {
		if ( false === get_option( self::OPTION_KEY, false ) ) {
			add_option( self::OPTION_KEY, self::defaults(), '', false );
		}
	}

	/**
	 * Read all settings.
	 *
	 * @return array<string,mixed>
	 */
	public static function get_all(): array {
		$stored = get_option( self::OPTION_KEY, [] );
		return self::merge_defaults( is_array( $stored ) ? $stored : [] );
	}

	/**
	 * Update settings after sanitizing input.
	 *
	 * @param array<string,mixed> $input Raw request data.
	 * @return array<string,mixed>
	 */
	public static function update( array $input ): array {
		$current = self::get_all();

		$next = [
			'masjid_name'         => isset( $input['masjid_name'] ) ? sanitize_text_field( wp_unslash( $input['masjid_name'] ) ) : $current['masjid_name'],
			'city'                => isset( $input['city'] ) ? sanitize_text_field( wp_unslash( $input['city'] ) ) : $current['city'],
			'country'             => isset( $input['country'] ) ? sanitize_text_field( wp_unslash( $input['country'] ) ) : $current['country'],
			'timezone'            => isset( $input['timezone'] ) ? self::sanitize_timezone( $input['timezone'], $current['timezone'] ) : $current['timezone'],
			'latitude'            => isset( $input['latitude'] ) ? self::sanitize_decimal( $input['latitude'], -90, 90 ) : $current['latitude'],
			'longitude'           => isset( $input['longitude'] ) ? self::sanitize_decimal( $input['longitude'], -180, 180 ) : $current['longitude'],
			'calculation_method'  => isset( $input['calculation_method'] ) ? self::sanitize_choice( $input['calculation_method'], self::calculation_method_keys(), $current['calculation_method'] ) : $current['calculation_method'],
			'asr_method'          => isset( $input['asr_method'] ) ? self::sanitize_choice( $input['asr_method'], [ 'standard', 'hanafi' ], $current['asr_method'] ) : $current['asr_method'],
			'prayer_source'       => isset( $input['prayer_source'] ) ? self::sanitize_choice( $input['prayer_source'], [ 'local', 'aladhan' ], $current['prayer_source'] ?? 'local' ) : ( $current['prayer_source'] ?? 'local' ),
			'hijri_adjustment'    => isset( $input['hijri_adjustment'] ) ? self::sanitize_int_range( $input['hijri_adjustment'], -3, 3 ) : (int) $current['hijri_adjustment'],
			'currency'            => isset( $input['currency'] ) ? self::sanitize_choice( $input['currency'], [ 'BDT', 'USD', 'GBP', 'EUR', 'SAR' ], $current['currency'] ) : $current['currency'],
			'public_transparency' => isset( $input['public_transparency'] ) ? (bool) $input['public_transparency'] : (bool) $current['public_transparency'],
			'prayer_offsets'      => self::sanitize_offsets( isset( $input['prayer_offsets'] ) && is_array( $input['prayer_offsets'] ) ? $input['prayer_offsets'] : ( $current['prayer_offsets'] ?? [] ) ),
			'iqamah_times'        => self::sanitize_iqamah_times( isset( $input['iqamah_times'] ) && is_array( $input['iqamah_times'] ) ? $input['iqamah_times'] : ( $current['iqamah_times'] ?? [] ) ),
			'jumuah'              => self::sanitize_jumuah_settings( isset( $input['jumuah'] ) && is_array( $input['jumuah'] ) ? $input['jumuah'] : ( $current['jumuah'] ?? [] ) ),
			'modules'             => self::sanitize_modules( isset( $input['modules'] ) && is_array( $input['modules'] ) ? $input['modules'] : $current['modules'] ),
			'tv_theme'              => isset( $input['tv_theme'] ) ? self::sanitize_choice( $input['tv_theme'], [ 'dark', 'light', 'green' ], $current['tv_theme'] ?? 'dark' ) : ( $current['tv_theme'] ?? 'dark' ),
			'tv_announcement_speed' => isset( $input['tv_announcement_speed'] ) ? self::sanitize_int_range( $input['tv_announcement_speed'], 3, 30 ) : (int) ( $current['tv_announcement_speed'] ?? 7 ),
			'tv_logo_url'           => isset( $input['tv_logo_url'] ) ? esc_url_raw( wp_unslash( $input['tv_logo_url'] ) ) : ( $current['tv_logo_url'] ?? '' ),
			'tv_font_size'          => isset( $input['tv_font_size'] ) ? self::sanitize_choice( $input['tv_font_size'], [ 'small', 'normal', 'large', 'xlarge' ], $current['tv_font_size'] ?? 'normal' ) : ( $current['tv_font_size'] ?? 'normal' ),
		];

		update_option( self::OPTION_KEY, self::merge_defaults( $next ), false );
		return self::get_all();
	}

	/**
	 * Module definitions used by admin UI.
	 *
	 * @return array<int,array<string,string>>
	 */
	public static function module_definitions(): array {
		return [
			[ 'key' => 'prayer_times', 'name' => __( 'Prayer Times', 'masjidos' ), 'description' => __( 'Auto location, Azan countdown, Qibla', 'masjidos' ), 'icon' => 'clock', 'color' => 'teal' ],
			[ 'key' => 'announcements', 'name' => __( 'Announcements', 'masjidos' ), 'description' => __( 'Scheduled notices, public list, ticker', 'masjidos' ), 'icon' => 'megaphone', 'color' => 'orange' ],
			[ 'key' => 'events', 'name' => __( 'Events', 'masjidos' ), 'description' => __( 'Events calendar, timings, and location registration', 'masjidos' ), 'icon' => 'calendar', 'color' => 'blue' ],
			[ 'key' => 'duas_azkar', 'name' => __( 'Duas & Azkar', 'masjidos' ), 'description' => __( 'Daily duas, azkar counters, sharing, and audio-ready cards', 'masjidos' ), 'icon' => 'ledger', 'color' => 'teal' ],
		];
	}

	/**
	 * @param array<string,mixed> $settings Stored settings.
	 * @return array<string,mixed>
	 */
	private static function merge_defaults( array $settings ): array {
		$defaults = self::defaults();
		$stored_modules = isset( $settings['modules'] ) && is_array( $settings['modules'] )
			? array_intersect_key( $settings['modules'], $defaults['modules'] )
			: [];
		$settings['modules'] = array_merge(
			$defaults['modules'],
			$stored_modules
		);
		$settings['prayer_offsets'] = array_merge(
			$defaults['prayer_offsets'],
			isset( $settings['prayer_offsets'] ) && is_array( $settings['prayer_offsets'] ) ? $settings['prayer_offsets'] : []
		);
		$settings['iqamah_times'] = array_merge(
			$defaults['iqamah_times'],
			isset( $settings['iqamah_times'] ) && is_array( $settings['iqamah_times'] ) ? $settings['iqamah_times'] : []
		);
		$settings['jumuah'] = array_merge(
			$defaults['jumuah'],
			isset( $settings['jumuah'] ) && is_array( $settings['jumuah'] ) ? $settings['jumuah'] : []
		);
		$settings['jumuah']['khatib'] = self::normalize_jumuah_khatib( $settings['jumuah']['khatib'] ?? [] );
		$settings['jumuah']['sessions'] = self::normalize_jumuah_sessions( $settings['jumuah'] );

		$settings = array_merge( $defaults, $settings );
		$settings['timezone'] = self::normalize_timezone_for_location(
			(string) $settings['timezone'],
			(string) $settings['country'],
			(string) $settings['city']
		);

		return $settings;
	}

	private static function default_timezone(): string {
		$timezone = wp_timezone_string();
		if ( in_array( $timezone, [ '', 'UTC', '+00:00', '-00:00' ], true ) ) {
			return 'Asia/Dhaka';
		}

		return $timezone;
	}

	/**
	 * @return string[]
	 */
	private static function calculation_method_keys(): array {
		return [ 'karachi', 'mwl', 'isna', 'egypt', 'makkah', 'dubai', 'qatar', 'kuwait', 'singapore', 'tehran', 'jafari' ];
	}

	/**
	 * @param mixed $value Raw timezone.
	 */
	private static function sanitize_timezone( $value, string $fallback ): string {
		$timezone = sanitize_text_field( wp_unslash( $value ) );
		if ( '' === $timezone ) {
			return $fallback;
		}

		try {
			new DateTimeZone( $timezone );
			return $timezone;
		} catch ( Exception $e ) {
			return $fallback;
		}
	}

	private static function normalize_timezone_for_location( string $timezone, string $country, string $city ): string {
		$is_utc = in_array( $timezone, [ '', 'UTC', '+00:00', '-00:00' ], true );
		$is_bangladesh = false !== stripos( $country, 'bangladesh' ) || false !== stripos( $city, 'dhaka' );

		if ( $is_utc && $is_bangladesh ) {
			return 'Asia/Dhaka';
		}

		return $timezone;
	}

	/**
	 * @param mixed $value Raw value.
	 */
	private static function sanitize_decimal( $value, float $min, float $max ): string {
		$number = (float) sanitize_text_field( wp_unslash( $value ) );
		$number = max( $min, min( $max, $number ) );
		return (string) $number;
	}

	/**
	 * @param mixed $value Raw integer.
	 */
	private static function sanitize_int_range( $value, int $min, int $max ): int {
		$number = (int) sanitize_text_field( wp_unslash( $value ) );
		return max( $min, min( $max, $number ) );
	}

	/**
	 * @param mixed    $value Raw value.
	 * @param string[] $allowed Allowed choices.
	 */
	private static function sanitize_choice( $value, array $allowed, string $fallback ): string {
		$value = sanitize_text_field( wp_unslash( $value ) );
		return in_array( $value, $allowed, true ) ? $value : $fallback;
	}

	/**
	 * @param array<string,mixed> $modules Raw module flags.
	 * @return array<string,bool>
	 */
	private static function sanitize_modules( array $modules ): array {
		$clean = [];
		foreach ( self::defaults()['modules'] as $key => $default ) {
			$clean[ $key ] = array_key_exists( $key, $modules ) ? (bool) $modules[ $key ] : (bool) $default;
		}
		return $clean;
	}

	/**
	 * @param array<string,mixed> $offsets Raw minute offsets.
	 * @return array<string,int>
	 */
	private static function sanitize_offsets( array $offsets ): array {
		$clean = [];
		foreach ( self::defaults()['prayer_offsets'] as $key => $default ) {
			$value = array_key_exists( $key, $offsets ) ? (int) $offsets[ $key ] : (int) $default;
			$clean[ $key ] = max( -60, min( 60, $value ) );
		}

		return $clean;
	}

	/**
	 * @param array<string,mixed> $times Raw Iqamah time values.
	 * @return array<string,string>
	 */
	private static function sanitize_iqamah_times( array $times ): array {
		$clean = [];
		foreach ( self::defaults()['iqamah_times'] as $key => $default ) {
			$value = array_key_exists( $key, $times ) ? sanitize_text_field( wp_unslash( $times[ $key ] ) ) : $default;
			$clean[ $key ] = self::sanitize_time_value( $value, '' );
		}

		return $clean;
	}

	/**
	 * @param array<string,mixed> $input Raw Jumuah settings.
	 * @return array<string,mixed>
	 */
	private static function sanitize_jumuah_settings( array $input ): array {
		$defaults = self::defaults()['jumuah'];
		$khatib = isset( $input['khatib'] ) ? self::normalize_jumuah_khatib( $input['khatib'] ) : self::normalize_jumuah_khatib( $input['khatib_name'] ?? ( $input['khatib'] ?? [] ) );
		$sessions = self::normalize_jumuah_sessions( $input );

		return [
			'enabled'      => array_key_exists( 'enabled', $input ) ? (bool) $input['enabled'] : (bool) $defaults['enabled'],
			'khutbah_time' => self::sanitize_time_value( $input['khutbah_time'] ?? $defaults['khutbah_time'], (string) $defaults['khutbah_time'] ),
			'jamaat_time'  => self::sanitize_time_value( $input['jamaat_time'] ?? $defaults['jamaat_time'], (string) $defaults['jamaat_time'] ),
			'topic'        => isset( $input['topic'] ) ? sanitize_text_field( wp_unslash( $input['topic'] ) ) : '',
			'language'     => isset( $input['language'] ) ? sanitize_text_field( wp_unslash( $input['language'] ) ) : '',
			'khatib'       => $khatib,
			'sessions'     => $sessions,
			'notice'       => isset( $input['notice'] ) ? sanitize_textarea_field( wp_unslash( $input['notice'] ) ) : '',
		];
	}

	/**
	 * @param mixed $input Raw Khatib value or legacy string.
	 * @return array<string,string>
	 */
	private static function normalize_jumuah_khatib( $input ): array {
		if ( is_string( $input ) ) {
			$input = [ 'name' => $input ];
		}
		$input = is_array( $input ) ? $input : [];

		return [
			'name'      => isset( $input['name'] ) ? sanitize_text_field( wp_unslash( $input['name'] ) ) : '',
			'image_url' => isset( $input['image_url'] ) ? esc_url_raw( wp_unslash( $input['image_url'] ) ) : '',
			'bio'       => isset( $input['bio'] ) ? sanitize_textarea_field( wp_unslash( $input['bio'] ) ) : '',
		];
	}

	/**
	 * @param array<string,mixed> $input Raw Jumuah settings.
	 * @return array<int,array<string,string>>
	 */
	private static function normalize_jumuah_sessions( array $input ): array {
		$raw_sessions = isset( $input['sessions'] ) && is_array( $input['sessions'] ) ? $input['sessions'] : [];
		if ( empty( $raw_sessions ) ) {
			$raw_sessions = [
				[
					'label'       => 'First Jumuah',
					'khutbah_time' => $input['khutbah_time'] ?? '13:00',
					'jamaat_time'  => $input['jamaat_time'] ?? '13:30',
				],
				[
					'label'       => 'Second Jumuah',
					'khutbah_time' => '',
					'jamaat_time'  => '',
				],
			];
		}

		$clean = [];
		foreach ( array_slice( $raw_sessions, 0, 3 ) as $index => $session ) {
			$session = is_array( $session ) ? $session : [];
			$label = isset( $session['label'] ) ? sanitize_text_field( wp_unslash( $session['label'] ) ) : sprintf( 'Jumuah %d', $index + 1 );
			$khutbah = self::sanitize_time_value( $session['khutbah_time'] ?? '', '' );
			$jamaat = self::sanitize_time_value( $session['jamaat_time'] ?? '', '' );
			if ( '' === $khutbah && '' === $jamaat && $index > 0 ) {
				continue;
			}
			$clean[] = [
				'label'       => $label,
				'khutbah_time' => $khutbah,
				'jamaat_time'  => $jamaat,
			];
		}

		return $clean ?: [
			[
				'label'       => 'First Jumuah',
				'khutbah_time' => '13:00',
				'jamaat_time'  => '13:30',
			],
		];
	}

	/**
	 * @param mixed $value Raw HH:MM value.
	 */
	private static function sanitize_time_value( $value, string $fallback ): string {
		$value = sanitize_text_field( wp_unslash( $value ) );
		return preg_match( '/^(?:[01]\d|2[0-3]):[0-5]\d$/', $value ) ? $value : $fallback;
	}
}
