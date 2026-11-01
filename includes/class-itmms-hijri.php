<?php
/**
 * Hijri date helper for MasjidOS.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Provides lightweight local Hijri date formatting.
 */
final class ITMMS_Hijri {

	/**
	 * Return formatted Hijri date details for a Gregorian date.
	 *
	 * @param DateTimeImmutable $date Gregorian date in the masjid timezone.
	 * @param int               $adjustment Day adjustment, usually -1, 0, or +1.
	 * @param string            $language Display language: en, bn, or ar.
	 * @return array<string,mixed>
	 */
	public static function for_date( DateTimeImmutable $date, int $adjustment = 0, string $language = 'en' ): array {
		$adjustment = max( -3, min( 3, $adjustment ) );
		$adjusted = $date->modify( sprintf( '%+d days', $adjustment ) );
		$parts = self::gregorian_to_hijri(
			(int) $adjusted->format( 'Y' ),
			(int) $adjusted->format( 'n' ),
			(int) $adjusted->format( 'j' )
		);

		$label = self::format_parts( $parts, $language );
		if ( 0 !== $adjustment ) {
			if ( 'bn' === $language ) {
				$label = sprintf( '%s সমন্বিত', $label );
			} elseif ( 'ar' === $language ) {
				$label = sprintf( '%s معدّل', $label );
			} else {
				$label = sprintf( '%s adjusted', $label );
			}
		}

		return [
			'year'       => $parts['year'],
			'month'      => $parts['month'],
			'day'        => $parts['day'],
			'month_name' => self::month_name( $parts['month'], $language ),
			'label'      => $label,
			'adjustment' => $adjustment,
		];
	}

	/**
	 * Return a short Hijri range label for a Gregorian month.
	 */
	public static function range_label( DateTimeImmutable $start, DateTimeImmutable $end, int $adjustment = 0, string $language = 'en' ): string {
		$first = self::for_date( $start, $adjustment, $language );
		$last = self::for_date( $end, $adjustment, $language );

		if ( $first['year'] === $last['year'] && $first['month'] === $last['month'] ) {
			return sprintf(
				'%1$s-%2$s %3$s %4$s AH',
				self::number( (string) $first['day'], $language ),
				self::number( (string) $last['day'], $language ),
				(string) $first['month_name'],
				self::number( (string) $first['year'], $language )
			);
		}

		return sprintf( '%1$s - %2$s', (string) $first['label'], (string) $last['label'] );
	}

	/**
	 * @return array{year:int,month:int,day:int}
	 */
	private static function gregorian_to_hijri( int $year, int $month, int $day ): array {
		$jd = self::gregorian_to_jdn( $year, $month, $day );
		$hijri_year = (int) floor( ( ( 30 * ( $jd - 1948440 ) ) + 10646 ) / 10631 );
		$hijri_month = (int) min( 12, ceil( ( $jd - ( 29 + self::hijri_to_jdn( $hijri_year, 1, 1 ) ) ) / 29.5 ) + 1 );
		$hijri_month = max( 1, min( 12, $hijri_month ) );
		$hijri_day = (int) ( $jd - self::hijri_to_jdn( $hijri_year, $hijri_month, 1 ) + 1 );

		if ( $hijri_day < 1 ) {
			$hijri_month--;
			if ( $hijri_month < 1 ) {
				$hijri_month = 12;
				$hijri_year--;
			}
			$hijri_day = (int) ( $jd - self::hijri_to_jdn( $hijri_year, $hijri_month, 1 ) + 1 );
		}

		return [
			'year'  => $hijri_year,
			'month' => $hijri_month,
			'day'   => $hijri_day,
		];
	}

	private static function gregorian_to_jdn( int $year, int $month, int $day ): int {
		$a = intdiv( 14 - $month, 12 );
		$y = $year + 4800 - $a;
		$m = $month + ( 12 * $a ) - 3;

		return $day + intdiv( ( 153 * $m ) + 2, 5 ) + ( 365 * $y ) + intdiv( $y, 4 ) - intdiv( $y, 100 ) + intdiv( $y, 400 ) - 32045;
	}

	private static function hijri_to_jdn( int $year, int $month, int $day ): int {
		return (int) ( $day + ceil( 29.5 * ( $month - 1 ) ) + ( ( $year - 1 ) * 354 ) + floor( ( ( 3 + ( 11 * $year ) ) / 30 ) ) + 1948439 );
	}

	/**
	 * @param array{year:int,month:int,day:int} $parts Hijri parts.
	 */
	private static function format_parts( array $parts, string $language ): string {
		$era = 'AH';
		if ( 'bn' === $language ) {
			$era = 'হিজরি';
		} elseif ( 'ar' === $language ) {
			$era = 'هـ';
		}

		return sprintf(
			'%1$s %2$s %3$s %4$s',
			self::number( (string) $parts['day'], $language ),
			self::month_name( $parts['month'], $language ),
			self::number( (string) $parts['year'], $language ),
			$era
		);
	}

	private static function month_name( int $month, string $language ): string {
		$months = [
			'en' => [
				1  => 'Muharram',
				2  => 'Safar',
				3  => 'Rabi al-awwal',
				4  => 'Rabi al-thani',
				5  => 'Jumada al-awwal',
				6  => 'Jumada al-thani',
				7  => 'Rajab',
				8  => 'Shaban',
				9  => 'Ramadan',
				10 => 'Shawwal',
				11 => 'Dhu al-Qadah',
				12 => 'Dhu al-Hijjah',
			],
			'bn' => [
				1  => 'মুহাররম',
				2  => 'সফর',
				3  => 'রবিউল আউয়াল',
				4  => 'রবিউস সানি',
				5  => 'জমাদিউল আউয়াল',
				6  => 'জমাদিউস সানি',
				7  => 'রজব',
				8  => 'শাবান',
				9  => 'রমজান',
				10 => 'শাওয়াল',
				11 => 'জিলকদ',
				12 => 'জিলহজ',
			],
			'ar' => [
				1  => 'محرم',
				2  => 'صفر',
				3  => 'ربيع الأول',
				4  => 'ربيع الآخر',
				5  => 'جمادى الأولى',
				6  => 'جمادى الآخرة',
				7  => 'رجب',
				8  => 'شعبان',
				9  => 'رمضان',
				10 => 'شوال',
				11 => 'ذو القعدة',
				12 => 'ذو الحجة',
			],
		];

		return $months[ $language ][ $month ] ?? $months['en'][ $month ] ?? '';
	}

	public static function number( string $value, string $language ): string {
		if ( ! in_array( $language, [ 'bn', 'ar' ], true ) ) {
			return $value;
		}

		$digits = 'bn' === $language
			? [ '0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯' ]
			: [ '0' => '٠', '1' => '١', '2' => '٢', '3' => '٣', '4' => '٤', '5' => '٥', '6' => '٦', '7' => '٧', '8' => '٨', '9' => '٩' ];

		return strtr( $value, $digits );
	}

	/**
	 * Localize a 12-hour clock string (e.g. "5:30 AM") for bn/ar display.
	 */
	public static function format_clock( string $time, string $language ): string {
		$time = trim( $time );
		if ( '' === $time || ! in_array( $language, [ 'bn', 'ar' ], true ) ) {
			return $time;
		}

		$meridiem = [
			'bn' => [ 'AM' => 'পূর্বাহ্ন', 'PM' => 'অপরাহ্ন' ],
			'ar' => [ 'AM' => 'ص', 'PM' => 'م' ],
		];

		$replaced = str_ireplace(
			[ 'AM', 'PM' ],
			[ $meridiem[ $language ]['AM'], $meridiem[ $language ]['PM'] ],
			$time
		);

		return self::number( $replaced, $language );
	}

	/**
	 * Localize digits inside a Gregorian date label for bn/ar.
	 */
	public static function format_label( string $label, string $language ): string {
		return self::number( $label, $language );
	}

	/**
	 * Convert Hijri date to Gregorian parts.
	 */
	public static function hijri_to_gregorian( int $year, int $month, int $day ): array {
		$jdn = self::hijri_to_jdn( $year, $month, $day );
		$l = $jdn + 68569;
		$n = intdiv( 4 * $l, 146097 );
		$l = $l - intdiv( 146097 * $n + 3, 4 );
		$i = intdiv( 4000 * ( $l + 1 ), 1461001 );
		$l = $l - intdiv( 1461 * $i, 4 ) + 31;
		$j = intdiv( 80 * $l, 2447 );
		$day_g = $l - intdiv( 2447 * $j, 80 );
		$l = intdiv( $j, 11 );
		$month_g = $j + 2 - ( 12 * $l );
		$year_g = 100 * ( $n - 49 ) + $i + $l;

		return [ 'year' => $year_g, 'month' => $month_g, 'day' => $day_g ];
	}
}
