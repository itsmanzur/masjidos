<?php
/**
 * Iqamah (Jamaat) rules engine.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Resolves daily Iqamah times from fixed clocks or dynamic rules.
 */
final class ITMMS_Iqamah_Rules {

	/** @var array<int,string> */
	private const PRAYER_KEYS = [ 'fajr', 'dhuhr', 'asr', 'maghrib', 'isha' ];

	/**
	 * Default per-prayer rule settings.
	 *
	 * @return array<string,array<string,int|string>>
	 */
	public static function defaults(): array {
		$defaults = [];
		foreach ( self::PRAYER_KEYS as $key ) {
			$defaults[ $key ] = [
				'mode'    => 'fixed',
				'minutes' => 0,
				'round'   => 0,
			];
		}

		return $defaults;
	}

	/**
	 * @param array<string,mixed> $settings Plugin settings.
	 * @return array<string,array<string,int|string>>
	 */
	public static function normalized( array $settings ): array {
		$defaults = self::defaults();
		$stored = isset( $settings['iqamah_rules'] ) && is_array( $settings['iqamah_rules'] )
			? $settings['iqamah_rules']
			: [];

		$rules = [];
		foreach ( self::PRAYER_KEYS as $key ) {
			$row = isset( $stored[ $key ] ) && is_array( $stored[ $key ] ) ? $stored[ $key ] : [];
			$mode = isset( $row['mode'] ) ? sanitize_key( (string) $row['mode'] ) : 'fixed';
			if ( ! in_array( $mode, [ 'fixed', 'after_azan', 'before_sunrise', 'none' ], true ) ) {
				$mode = 'fixed';
			}
			if ( 'before_sunrise' === $mode && 'fajr' !== $key ) {
				$mode = 'fixed';
			}

			$minutes = isset( $row['minutes'] ) ? (int) $row['minutes'] : 0;
			$minutes = max( 0, min( 180, $minutes ) );
			$round = isset( $row['round'] ) ? (int) $row['round'] : 0;
			if ( ! in_array( $round, [ 0, 5, 10, 15 ], true ) ) {
				$round = 0;
			}

			$rules[ $key ] = [
				'mode'    => $mode,
				'minutes' => $minutes,
				'round'   => $round,
			];
		}

		return $rules;
	}

	/**
	 * @param array<string,mixed> $input Raw settings input.
	 * @return array<string,array<string,int|string>>
	 */
	public static function sanitize( array $input ): array {
		return self::normalized( [ 'iqamah_rules' => $input ] );
	}

	/**
	 * Resolve Iqamah clock values for each prayer.
	 *
	 * @param array<string,float> $azan_minutes Prayer times in minutes after midnight.
	 * @param array<string,mixed> $settings Plugin settings.
	 * @return array<string,string> HH:MM values keyed by prayer.
	 */
	public static function resolve_all( DateTimeImmutable $date, array $azan_minutes, array $settings ): array {
		$rules = self::normalized( $settings );
		$fixed = self::fixed_times( $settings['iqamah_times'] ?? [] );
		$resolved = [];

		foreach ( self::PRAYER_KEYS as $key ) {
			$resolved[ $key ] = self::resolve_prayer(
				$key,
				$rules[ $key ],
				$azan_minutes,
				$fixed[ $key ] ?? '',
				$date
			);
		}

		return $resolved;
	}

	/**
	 * @param array<string,mixed>              $rule Prayer rule row.
	 * @param array<string,float|int>            $azan_minutes Azan minutes keyed by prayer.
	 * @param array<string,array<string,int|string>> $all_rules All prayer rules.
	 */
	public static function resolve_prayer(
		string $key,
		array $rule,
		array $azan_minutes,
		string $fixed_time,
		DateTimeImmutable $date
	): string {
		$mode = (string) ( $rule['mode'] ?? 'fixed' );
		$minutes = (int) ( $rule['minutes'] ?? 0 );
		$round = (int) ( $rule['round'] ?? 0 );

		if ( 'none' === $mode ) {
			return '';
		}

		if ( 'fixed' === $mode ) {
			return preg_match( '/^(?:[01]\d|2[0-3]):[0-5]\d$/', $fixed_time ) ? $fixed_time : '';
		}

		if ( 'before_sunrise' === $key || 'before_sunrise' === $mode ) {
			if ( ! isset( $azan_minutes['sunrise'] ) ) {
				return '';
			}
			$target = (float) $azan_minutes['sunrise'] - $minutes;
			if ( isset( $azan_minutes['fajr'] ) && $target <= (float) $azan_minutes['fajr'] ) {
				$target = (float) $azan_minutes['fajr'] + 5;
			}
			return self::minutes_to_time( $target );
		}

		if ( ! isset( $azan_minutes[ $key ] ) ) {
			return '';
		}

		$base = (float) $azan_minutes[ $key ];
		if ( $round > 0 ) {
			$base = ceil( $base / $round ) * $round;
		}

		return self::minutes_to_time( $base + $minutes );
	}

	/**
	 * @param mixed $times Raw fixed Iqamah times.
	 * @return array<string,string>
	 */
	private static function fixed_times( $times ): array {
		$defaults = array_fill_keys( self::PRAYER_KEYS, '' );
		if ( ! is_array( $times ) ) {
			return $defaults;
		}

		foreach ( self::PRAYER_KEYS as $key ) {
			$value = isset( $times[ $key ] ) ? (string) $times[ $key ] : '';
			$defaults[ $key ] = preg_match( '/^(?:[01]\d|2[0-3]):[0-5]\d$/', $value ) ? $value : '';
		}

		return $defaults;
	}

	private static function minutes_to_time( float $minutes ): string {
		$minutes = (int) round( $minutes );
		$minutes = ( $minutes % 1440 + 1440 ) % 1440;
		return sprintf( '%02d:%02d', intdiv( $minutes, 60 ), $minutes % 60 );
	}
}
