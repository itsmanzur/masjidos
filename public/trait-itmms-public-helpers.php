<?php
/**
 * ITMMS_Public_Helpers methods for ITMMS_Public.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * @package MasjidOS
 */
trait ITMMS_Public_Helpers {

	private function normalize_language( string $language ): string {
		$language = strtolower( sanitize_key( $language ) );
		$aliases = [
			'bangla'  => 'bn',
			'bengali' => 'bn',
			'bn_bd'   => 'bn',
			'arabic'  => 'ar',
			'ar_sa'   => 'ar',
			'english' => 'en',
			'en_us'   => 'en',
		];
		$language = $aliases[ $language ] ?? $language;

		return in_array( $language, [ 'en', 'bn', 'ar' ], true ) ? $language : 'en';
	}

	/**
	 * Translate an English msgid for a public widget language without changing the global locale.
	 */
	private function translate_for_language( string $text, string $language ): string {
		$language = $this->normalize_language( $language );
		if ( 'en' === $language || '' === $text ) {
			return $text;
		}

		$locale = ( 'bn' === $language ) ? 'bn_BD' : 'ar';
		static $catalogs = [];

		if ( ! array_key_exists( $locale, $catalogs ) ) {
			$catalogs[ $locale ] = null;
			$mofile               = ITMMS_PLUGIN_DIR . 'languages/masjidos-' . $locale . '.mo';
			if ( is_readable( $mofile ) ) {
				if ( ! class_exists( 'MO', false ) ) {
					require_once ABSPATH . WPINC . '/pomo/mo.php';
				}
				$mo = new MO();
				if ( $mo->import_from_file( $mofile ) ) {
					$catalogs[ $locale ] = $mo;
				}
			}
		}

		if ( $catalogs[ $locale ] instanceof MO ) {
			$translated = $catalogs[ $locale ]->translate( $text );
			if ( is_string( $translated ) && '' !== $translated ) {
				return $translated;
			}
		}

		return $text;
	}

	/**
	 * Empty-state notice localized to the widget language (en|bn|ar).
	 *
	 * @param string $title   English msgid.
	 * @param string $message English msgid.
	 */
	private function render_localized_empty_state( string $title, string $message, string $language ): string {
		return $this->render_announcement_empty_state(
			$this->translate_for_language( $title, $language ),
			$this->translate_for_language( $message, $language )
		);
	}

	/**
	 * Keep empty-state msgids visible to gettext scanners (not called at runtime).
	 *
	 * @return array<int,string>
	 */
	private function empty_state_msgids_for_i18n(): array {
		return [
			__( 'Prayer Times is disabled', 'masjidos' ),
			__( 'Enable the Prayer Times module before using this shortcode.', 'masjidos' ),
			__( 'No prayer times available', 'masjidos' ),
			__( 'Check Prayer Setup: timezone, coordinates, and calculation method.', 'masjidos' ),
			__( 'No monthly timetable available', 'masjidos' ),
			__( 'Check Prayer Setup and try again.', 'masjidos' ),
			__( 'No duas found', 'masjidos' ),
			__( 'Try another category or increase the limit.', 'masjidos' ),
			__( 'No verse available', 'masjidos' ),
			__( 'Check back later for today’s Quran verse.', 'masjidos' ),
			__( 'No hadith available', 'masjidos' ),
			__( 'Check back later for today’s hadith.', 'masjidos' ),
			__( 'No names available', 'masjidos' ),
			__( 'The 99 Names collection could not be loaded.', 'masjidos' ),
			__( 'No surahs available', 'masjidos' ),
			__( 'The Audio Quran list could not be loaded.', 'masjidos' ),
		];
	}

	/**
	 * @return array<string,string>
	 */
	private function labels( string $language ): array {
		$labels = [
			'en' => [
				'title'       => __( 'Prayer Times', 'masjidos' ),
				'next_prayer' => __( 'Next Prayer', 'masjidos' ),
				'iqamah'      => __( 'Iqamah', 'masjidos' ),
				'now'         => __( 'Now', 'masjidos' ),
				'location'    => __( 'Location', 'masjidos' ),
				'method'      => __( 'Method', 'masjidos' ),
				'asr'         => __( 'Asr', 'masjidos' ),
				'timezone'    => __( 'Timezone', 'masjidos' ),
				'qibla'       => __( 'Qibla', 'masjidos' ),
				'source'      => __( 'Source', 'masjidos' ),
				'local_calculation' => __( 'Local calculation', 'masjidos' ),
				'auto_api'    => __( 'Auto API', 'masjidos' ),
				'csv_timetable' => __( 'Masjid CSV Timetable', 'masjidos' ),
				'hijri_adjustment' => __( 'Hijri adjustment', 'masjidos' ),
				'offsets'     => __( 'Offsets', 'masjidos' ),
				'coordinates' => __( 'Coordinates', 'masjidos' ),
				'moon_note'   => __( 'Hijri dates may differ by one day based on local moon sighting.', 'masjidos' ),
				'qibla_prompt' => __( 'Tap to point live', 'masjidos' ),
			],
			'bn' => [
				'title'       => 'নামাজের সময়সূচি',
				'next_prayer' => 'পরবর্তী ওয়াক্ত',
				'iqamah'      => 'জামাত',
				'now'         => 'এখন',
				'location'    => 'এলাকা',
				'method'      => 'পদ্ধতি',
				'asr'         => 'আসর',
				'timezone'    => 'টাইমজোন',
				'qibla'       => 'কিবলা',
				'source'      => 'উৎস',
				'local_calculation' => 'লোকাল হিসাব',
				'auto_api'    => 'অটো API',
				'csv_timetable' => 'মসজিদ CSV সময়সূচি',
				'hijri_adjustment' => 'হিজরি সমন্বয়',
				'offsets'     => 'সমন্বয়',
				'coordinates' => 'কোঅর্ডিনেট',
				'moon_note'   => 'স্থানীয় চাঁদ দেখার কারণে হিজরি তারিখ একদিন এদিক-ওদিক হতে পারে।',
				'qibla_prompt' => 'লাইভ কিবলার জন্য ট্যাপ করুন',
			],
			'ar' => [
				'title'       => 'مواقيت الصلاة',
				'next_prayer' => 'الصلاة التالية',
				'iqamah'      => 'الإقامة',
				'now'         => 'الآن',
				'location'    => 'الموقع',
				'method'      => 'الطريقة',
				'asr'         => 'العصر',
				'timezone'    => 'المنطقة الزمنية',
				'qibla'       => 'القبلة',
				'source'      => 'المصدر',
				'local_calculation' => 'حساب محلي',
				'auto_api'    => 'واجهة API تلقائية',
				'csv_timetable' => 'جدول CSV للمسجد',
				'hijri_adjustment' => 'تعديل هجري',
				'offsets'     => 'التعديلات',
				'coordinates' => 'الإحداثيات',
				'moon_note'   => 'قد تختلف التواريخ الهجرية يوماً واحداً حسب الرؤية المحلية للهلال.',
				'qibla_prompt' => 'اضغط لتوجيه القبلة مباشرة',
			],
		];

		return $labels[ $language ] ?? $labels['en'];
	}

	private function prayer_label( string $key, string $language, string $fallback ): string {
		$labels = [
			'en' => [
				'fajr'    => 'Fajr',
				'sunrise' => 'Sunrise',
				'ishraq'  => 'Ishraq',
				'zawal'   => 'Zawal',
				'dhuhr'   => 'Dhuhr',
				'asr'     => 'Asr',
				'maghrib' => 'Maghrib',
				'isha'    => 'Isha',
			],
			'bn' => [
				'fajr'    => 'ফজর',
				'sunrise' => 'সূর্যোদয়',
				'ishraq'  => 'ইশরাক',
				'zawal'   => 'যাওয়াল',
				'dhuhr'   => 'যোহর',
				'asr'     => 'আসর',
				'maghrib' => 'মাগরিব',
				'isha'    => 'এশা',
			],
			'ar' => [
				'fajr'    => 'الفجر',
				'sunrise' => 'الشروق',
				'ishraq'  => 'الإشراق',
				'zawal'   => 'الزوال',
				'dhuhr'   => 'الظهر',
				'asr'     => 'العصر',
				'maghrib' => 'المغرب',
				'isha'    => 'العشاء',
			],
		];

		return $labels[ $language ][ $key ] ?? $fallback;
	}

	/**
	 * @return array<string,string>
	 */
	private function jumuah_labels( string $language ): array {
		$labels = [
			'en' => [
				'title'    => __( 'Jumuah Prayer', 'masjidos' ),
				'friday'   => __( 'Friday', 'masjidos' ),
				'khutbah'  => __( 'Khutbah', 'masjidos' ),
				'jamaat'   => __( 'Jamaat', 'masjidos' ),
				'first_jumuah' => __( 'First Jumuah', 'masjidos' ),
				'second_jumuah' => __( 'Second Jumuah', 'masjidos' ),
				'khatib'   => __( 'Khatib', 'masjidos' ),
				'topic'    => __( 'Topic', 'masjidos' ),
				'language' => __( 'Language', 'masjidos' ),
				'location' => __( 'Location', 'masjidos' ),
			],
			'bn' => [
				'title'    => 'জুমার নামাজ',
				'friday'   => 'শুক্রবার',
				'khutbah'  => 'খুতবা',
				'jamaat'   => 'জামাত',
				'first_jumuah' => 'প্রথম জুমা',
				'second_jumuah' => 'দ্বিতীয় জুমা',
				'khatib'   => 'খতিব',
				'topic'    => 'বিষয়',
				'language' => 'ভাষা',
				'location' => 'এলাকা',
			],
			'ar' => [
				'title'    => 'صلاة الجمعة',
				'friday'   => 'الجمعة',
				'khutbah'  => 'الخطبة',
				'jamaat'   => 'الجماعة',
				'first_jumuah' => 'الجمعة الأولى',
				'second_jumuah' => 'الجمعة الثانية',
				'khatib'   => 'الخطيب',
				'topic'    => 'الموضوع',
				'language' => 'اللغة',
				'location' => 'الموقع',
			],
		];

		return $labels[ $language ] ?? $labels['en'];
	}

	private function jumuah_session_label( string $label, string $language, int $index ): string {
		$normalized = strtolower( trim( $label ) );
		$labels = $this->jumuah_labels( $language );

		if ( '' === $normalized || in_array( $normalized, [ 'first jumuah', 'jumuah 1', 'first jamaat' ], true ) ) {
			return 0 === $index ? $labels['first_jumuah'] : $labels['second_jumuah'];
		}

		if ( in_array( $normalized, [ 'second jumuah', 'jumuah 2', 'second jamaat' ], true ) ) {
			return $labels['second_jumuah'];
		}

		return $label;
	}

	/**
	 * @return array<string,string>
	 */
	private function monthly_labels( string $language ): array {
		$labels = [
			'en' => [
				'title'  => __( 'Monthly Prayer Timetable', 'masjidos' ),
				'date'   => __( 'Date', 'masjidos' ),
				'today'  => __( 'Today', 'masjidos' ),
				'iqamah' => __( 'Iqamah', 'masjidos' ),
				'navigation' => __( 'Timetable month navigation', 'masjidos' ),
				'previous' => __( 'Previous month', 'masjidos' ),
				'next' => __( 'Next month', 'masjidos' ),
				'month' => __( 'Month', 'masjidos' ),
				'year' => __( 'Year', 'masjidos' ),
				'error' => __( 'The timetable could not be loaded. Please try again.', 'masjidos' ),
				'current_month' => __( 'Current Month', 'masjidos' ),
				'print' => __( 'Print', 'masjidos' ),
				'source' => __( 'Source', 'masjidos' ),
				'local_calculation' => __( 'Local calculation', 'masjidos' ),
				'auto_api' => __( 'Auto API', 'masjidos' ),
				'csv_timetable' => __( 'Masjid CSV Timetable', 'masjidos' ),
				'method' => __( 'Method', 'masjidos' ),
				'asr' => __( 'Asr', 'masjidos' ),
				'hijri_adjustment' => __( 'Hijri adjustment', 'masjidos' ),
				'offsets' => __( 'Offsets', 'masjidos' ),
				'coordinates' => __( 'Coordinates', 'masjidos' ),
				/* translators: 1: calculation source label, 2: location string (city/country). */
				'generated_using' => __( 'Generated using %1$s for %2$s. Hijri dates may differ by local moon sighting.', 'masjidos' ),
			],
			'bn' => [
				'title'  => 'মাসিক নামাজের সময়সূচি',
				'date'   => 'তারিখ',
				'today'  => 'আজ',
				'iqamah' => 'জামাত',
				'navigation' => 'সময়সূচির মাস নির্বাচন',
				'previous' => 'আগের মাস',
				'next' => 'পরের মাস',
				'month' => 'মাস',
				'year' => 'বছর',
				'error' => 'সময়সূচি লোড করা যায়নি। আবার চেষ্টা করুন।',
				'current_month' => 'বর্তমান মাস',
				'print' => 'প্রিন্ট',
				'source' => 'উৎস',
				'local_calculation' => 'লোকাল হিসাব',
				'auto_api' => 'অটো API',
				'csv_timetable' => 'মসজিদ CSV সময়সূচি',
				'method' => 'পদ্ধতি',
				'asr' => 'আসর',
				'hijri_adjustment' => 'হিজরি সমন্বয়',
				'offsets' => 'সমন্বয়',
				'coordinates' => 'কোঅর্ডিনেট',
				'generated_using' => '%2$s এর জন্য %1$s দিয়ে তৈরি। স্থানীয় চাঁদ দেখার কারণে হিজরি তারিখ ভিন্ন হতে পারে।',
			],
			'ar' => [
				'title'  => 'جدول الصلاة الشهري',
				'date'   => 'التاريخ',
				'today'  => 'اليوم',
				'iqamah' => 'الإقامة',
				'navigation' => 'التنقل بين أشهر الجدول',
				'previous' => 'الشهر السابق',
				'next' => 'الشهر التالي',
				'month' => 'الشهر',
				'year' => 'السنة',
				'error' => 'تعذر تحميل الجدول. يرجى المحاولة مرة أخرى.',
				'current_month' => 'الشهر الحالي',
				'print' => 'طباعة',
				'source' => 'المصدر',
				'local_calculation' => 'حساب محلي',
				'auto_api' => 'واجهة API تلقائية',
				'csv_timetable' => 'جدول CSV للمسجد',
				'method' => 'الطريقة',
				'asr' => 'العصر',
				'hijri_adjustment' => 'تعديل هجري',
				'offsets' => 'التعديلات',
				'coordinates' => 'الإحداثيات',
				'generated_using' => 'تم الإنشاء باستخدام %1$s لـ %2$s. قد تختلف التواريخ الهجرية حسب الرؤية المحلية.',
			],
		];

		return $labels[ $language ] ?? $labels['en'];
	}

	/**
	 * Build compact trust chips for prayer / monthly widgets.
	 *
	 * @param array<string,mixed>  $settings Plugin settings.
	 * @param array<string,mixed>  $meta Prayer result meta.
	 * @param array<string,string> $labels Localized labels.
	 * @param string               $language Widget language.
	 * @return array<int,array{0:string,1:string}>
	 */
	private function build_prayer_trust_items( array $settings, array $meta, array $labels, string $language = 'en' ): array {
		$source_label = $this->prayer_source_label( $settings, $meta, $labels );

		$hijri_adjustment = (int) ( $settings['hijri_adjustment'] ?? $meta['hijri_adjustment'] ?? 0 );
		$hijri_adjustment_label = 0 === $hijri_adjustment ? '0' : ( ( $hijri_adjustment > 0 ? '+' : '' ) . (string) $hijri_adjustment );
		$hijri_adjustment_label = ITMMS_Hijri::number( $hijri_adjustment_label, $language );

		$latitude  = (float) ( $meta['latitude'] ?? $settings['latitude'] ?? 0 );
		$longitude = (float) ( $meta['longitude'] ?? $settings['longitude'] ?? 0 );
		$coords    = ( abs( $latitude ) > 0.0001 || abs( $longitude ) > 0.0001 )
			? ITMMS_Hijri::number( number_format( $latitude, 4, '.', '' ), $language ) . ', ' . ITMMS_Hijri::number( number_format( $longitude, 4, '.', '' ), $language )
			: '';

		$offsets = is_array( $meta['offsets'] ?? null ) ? $meta['offsets'] : ( $settings['prayer_offsets'] ?? [] );
		$offset_parts = [];
		if ( is_array( $offsets ) ) {
			foreach ( [ 'fajr', 'sunrise', 'dhuhr', 'asr', 'maghrib', 'isha' ] as $key ) {
				$value = isset( $offsets[ $key ] ) ? (int) $offsets[ $key ] : 0;
				if ( 0 === $value ) {
					continue;
				}
				$offset_parts[] = ucfirst( $key ) . ' ' . ITMMS_Hijri::number( ( $value > 0 ? '+' : '' ) . $value . 'm', $language );
			}
		}

		$items = [
			[ (string) ( $labels['source'] ?? __( 'Source', 'masjidos' ) ), $source_label ],
			[ (string) ( $labels['method'] ?? __( 'Method', 'masjidos' ) ), (string) ( $meta['calculation_method'] ?? '' ) ],
			[ (string) ( $labels['asr'] ?? __( 'Asr', 'masjidos' ) ), (string) ( $meta['asr_method'] ?? '' ) ],
			[ (string) ( $labels['hijri_adjustment'] ?? __( 'Hijri adjustment', 'masjidos' ) ), $hijri_adjustment_label ],
		];

		if ( '' !== $coords ) {
			$items[] = [ (string) ( $labels['coordinates'] ?? __( 'Coordinates', 'masjidos' ) ), $coords ];
		}

		if ( ! empty( $offset_parts ) ) {
			$items[] = [ (string) ( $labels['offsets'] ?? __( 'Offsets', 'masjidos' ) ), implode( ', ', $offset_parts ) ];
		}

		return $items;
	}

	/**
	 * @param array<string,mixed>  $settings Plugin settings.
	 * @param array<string,mixed>  $meta Prayer result meta.
	 * @param array<string,string> $labels Localized labels.
	 */
	private function prayer_source_label( array $settings, array $meta, array $labels ): string {
		if ( 'csv' === (string) ( $meta['prayer_source'] ?? '' ) ) {
			return (string) ( $labels['csv_timetable'] ?? __( 'Masjid CSV Timetable', 'masjidos' ) );
		}

		return 'aladhan' === (string) ( $settings['prayer_source'] ?? 'local' )
			? (string) ( $labels['auto_api'] ?? __( 'Auto API', 'masjidos' ) )
			: (string) ( $labels['local_calculation'] ?? __( 'Local calculation', 'masjidos' ) );
	}

	/**
	 * @param array<string,mixed> $settings Plugin settings.
	 */
	private function hijri_label_for_date( string $date, array $settings, string $language ): string {
		try {
			$timezone = new DateTimeZone( (string) ( $settings['timezone'] ?? wp_timezone_string() ) );
			$day = new DateTimeImmutable( $date . ' 00:00:00', $timezone );
			$hijri = ITMMS_Hijri::for_date( $day, (int) ( $settings['hijri_adjustment'] ?? 0 ), $language );
			return (string) ( $hijri['label'] ?? '' );
		} catch ( Exception $e ) {
			return '';
		}
	}

	/**
	 * @return array<string,string>
	 */
	private function announcement_labels( string $language ): array {
		$labels = [
			'en' => [
				'title'        => __( 'Masjid Notices', 'masjidos' ),
				'notice'       => __( 'Notice', 'masjidos' ),
				'pause'        => __( 'Pause', 'masjidos' ),
				'play'         => __( 'Play', 'masjidos' ),
				'active_one'   => __( '1 active notice', 'masjidos' ),
				/* translators: %d: Number of currently active notices. */
				'active_count' => __( '%d active notices', 'masjidos' ),
				'general'      => __( 'General', 'masjidos' ),
				'urgent'       => __( 'Urgent', 'masjidos' ),
				'jumuah'       => __( 'Jumuah', 'masjidos' ),
			],
			'bn' => [
				'title'        => 'মসজিদের নোটিশ',
				'notice'       => 'নোটিশ',
				'pause'        => 'থামান',
				'play'         => 'চালান',
				'active_one'   => '১টি সক্রিয় নোটিশ',
				'active_count' => '%dটি সক্রিয় নোটিশ',
				'general'      => 'সাধারণ',
				'urgent'       => 'জরুরি',
				'jumuah'       => 'জুমা',
			],
			'ar' => [
				'title'        => 'إعلانات المسجد',
				'notice'       => 'إعلان',
				'pause'        => 'إيقاف',
				'play'         => 'تشغيل',
				'active_one'   => 'إعلان نشط واحد',
				'active_count' => '%d إعلانات نشطة',
				'general'      => 'عام',
				'urgent'       => 'عاجل',
				'jumuah'       => 'الجمعة',
			],
		];

		return $labels[ $language ] ?? $labels['en'];
	}

	/**
	 * @return array<int,string>
	 */
	private function month_names( string $language ): array {
		$months = [
			'en' => [ 1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ],
			'bn' => [ 1 => 'জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর' ],
			'ar' => [ 1 => 'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر' ],
		];

		return $months[ $language ] ?? $months['en'];
	}

	/**
	 * Localized weekday label for monthly timetable cells.
	 *
	 * @param string $date Y-m-d date.
	 * @param string $language Widget language.
	 * @param bool   $short Short weekday label.
	 */
	private function weekday_label( string $date, string $language, bool $short = false ): string {
		try {
			$dt = new DateTimeImmutable( $date, wp_timezone() );
		} catch ( Exception $e ) {
			return '';
		}

		// DateTime format('w'): 0 = Sunday … 6 = Saturday.
		$index = (int) $dt->format( 'w' );
		$full = [
			'en' => [ 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ],
			'bn' => [ 'রবিবার', 'সোমবার', 'মঙ্গলবার', 'বুধবার', 'বৃহস্পতিবার', 'শুক্রবার', 'শনিবার' ],
			'ar' => [ 'الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت' ],
		];
		$abbr = [
			'en' => [ 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' ],
			'bn' => [ 'রবি', 'সোম', 'মঙ্গল', 'বুধ', 'বৃহঃ', 'শুক্র', 'শনি' ],
			'ar' => [ 'أحد', 'إثن', 'ثلا', 'أرب', 'خمي', 'جمع', 'سبت' ],
		];

		$map = $short ? ( $abbr[ $language ] ?? $abbr['en'] ) : ( $full[ $language ] ?? $full['en'] );
		return $map[ $index ] ?? '';
	}

	/**
	 * @param array<int,array<string,mixed>> $prayers Prayer rows.
	 * @return array<string,array<string,mixed>>
	 */
	private function indexed_prayers( array $prayers ): array {
		$indexed = [];
		foreach ( $prayers as $prayer ) {
			if ( isset( $prayer['key'] ) ) {
				$indexed[ (string) $prayer['key'] ] = $prayer;
			}
		}

		return $indexed;
	}

	private function format_time( string $time, string $timezone, string $language = 'en' ): string {
		if ( ! preg_match( '/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time ) ) {
			return '';
		}

		try {
			$date = new DateTimeImmutable( 'today ' . $time, new DateTimeZone( $timezone ) );
			return ITMMS_Hijri::format_clock( $date->format( 'g:i A' ), $language );
		} catch ( Exception $e ) {
			return ITMMS_Hijri::format_clock( $time, $language );
		}
	}

	private function initials( string $name ): string {
		$parts = preg_split( '/\s+/', trim( $name ) );
		$initials = '';
		foreach ( array_slice( is_array( $parts ) ? $parts : [], 0, 2 ) as $part ) {
			$initials .= function_exists( 'mb_substr' ) ? mb_substr( $part, 0, 1 ) : substr( $part, 0, 1 );
		}

		return $initials ?: 'J';
	}

	private function render_announcement_empty_state( string $title, string $message ): string {
		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'itmms_manage_announcements' ) ) {
			return '';
		}

		return '<div class="itmms-public-announcement-empty">' .
			'<strong>' . esc_html( $title ) . '</strong>' .
			'<p>' . esc_html( $message ) . '</p>' .
			'<code>[masjidos_announcements]</code>' .
		'</div>';
	}

	/**
	 * Enqueue public assets only when shortcode renders.
	 */
	private function enqueue_assets(): void {
		wp_enqueue_style(
			'itmms-fonts',
			ITMMS_PLUGIN_URL . 'public/assets/css/tv-fonts.css',
			[],
			ITMMS_VERSION
		);

		wp_enqueue_style(
			'itmms-public',
			ITMMS_PLUGIN_URL . 'public/assets/css/public.css',
			[ 'itmms-fonts' ],
			ITMMS_VERSION
		);

		wp_enqueue_script(
			'itmms-public',
			ITMMS_PLUGIN_URL . 'public/assets/js/public.js',
			[],
			ITMMS_VERSION,
			true
		);
	}

	/**
	 * Safe KSES wrapper that preserves select, option, and button tags for interactive widgets.
	 *
	 * @param string $html Raw HTML content.
	 * @return string Sanitized HTML content.
	 */
	private function safe_kses( string $html ): string {
		$allowed = wp_kses_allowed_html( 'post' );

		$allowed['select'] = [
			'class'                      => true,
			'id'                         => true,
			'name'                       => true,
			'style'                      => true,
			'data-itmms-monthly-month'   => true,
			'data-itmms-monthly-year'    => true,
			'data-itmms-calendar-month'  => true,
			'data-itmms-calendar-year'   => true,
			'data-itmms-quran-surah'     => true,
			'aria-label'                 => true,
		];

		$allowed['form'] = [
			'action' => true,
			'class'  => true,
			'method' => true,
			'role'   => true,
		];

		$allowed['input'] = [
			'aria-label'  => true,
			'class'       => true,
			'id'          => true,
			'name'        => true,
			'placeholder' => true,
			'type'        => true,
			'value'       => true,
		];

		$allowed['label'] = [
			'class' => true,
			'for'   => true,
			'style' => true,
		];

		$allowed['option'] = [
			'value'    => true,
			'selected' => true,
			'style'    => true,
		];

		$allowed['button'] = [
			'class'                        => true,
			'id'                           => true,
			'type'                         => true,
			'style'                        => true,
			'disabled'                     => true,
			'data-itmms-monthly-step'      => true,
			'data-itmms-monthly-current'   => true,
			'data-itmms-monthly-print'     => true,
			'data-itmms-ticker-toggle'     => true,
			'data-play-label'              => true,
			'data-pause-label'             => true,
			'data-itmms-calendar-step'     => true,
			'data-itmms-calendar-current'  => true,
			'data-itmms-dua-count'         => true,
			'data-itmms-dua-reset'         => true,
			'data-itmms-dua-audio'         => true,
			'data-itmms-dua-share'         => true,
			'data-itmms-education-share'   => true,
			'data-itmms-popup-close'       => true,
			'data-itmms-share-success'     => true,
			'data-itmms-share-text'        => true,
			'data-itmms-cal-toggle'        => true,
			'aria-label'                   => true,
			'aria-pressed'                 => true,
			'aria-expanded'                => true,
			'aria-controls'                => true,
			'title'                        => true,
		];

		$allowed['div'] = array_merge(
			$allowed['div'] ?? [],
			[
				'class'                    => true,
				'id'                       => true,
				'hidden'                   => true,
				'data-itmms-monthly'       => true,
				'data-itmms-calendar'      => true,
				'data-endpoint'            => true,
				'data-month'               => true,
				'data-year'                => true,
				'data-current-month'       => true,
				'data-current-year'        => true,
				'data-design'              => true,
				'data-language'            => true,
				'data-iqamah'              => true,
				'data-extras'              => true,
				'data-title'               => true,
				'data-error'               => true,
				'data-next-prayer'         => true,
				'data-itmms-public-qibla'  => true,
				'data-itmms-popup-id'      => true,
				'data-gregorian-date'      => true,
				'data-hijri-date-label'    => true,
				'data-itmms-calendar-drawer' => true,
				'data-itmms-calendar-drawer-title' => true,
				'data-itmms-calendar-drawer-list' => true,
				'style'                    => true,
				'role'                     => true,
				'tabindex'                 => true,
				'title'                    => true,
				'dir'                      => true,
				'aria-label'               => true,
				'aria-busy'                => true,
				'aria-hidden'              => true,
			]
		);

		$allowed['span'] = array_merge(
			$allowed['span'] ?? [],
			[
				'class'            => true,
				'style'            => true,
				'title'            => true,
				'aria-hidden'      => true,
				'data-today-label' => true,
			]
		);

		$allowed['section'] = array_merge(
			$allowed['section'] ?? [],
			[
				'class'                    => true,
				'dir'                      => true,
				'data-itmms-share-success' => true,
			]
		);

		$allowed['article'] = array_merge(
			$allowed['article'] ?? [],
			[
				'class'                  => true,
				'data-itmms-dua-key'     => true,
				'data-itmms-dua-text'    => true,
				'data-itmms-dua-target'  => true,
			]
		);

		$allowed['audio'] = [
			'class'                   => true,
			'controls'                => true,
			'id'                      => true,
			'preload'                 => true,
			'src'                     => true,
			'data-itmms-quran-player' => true,
		];

		$allowed['time'] = [
			'class'    => true,
			'datetime' => true,
		];

		$allowed['strong'] = array_merge(
			$allowed['strong'] ?? [],
			[
				'class'            => true,
				'data-today-label' => true,
			]
		);

		$allowed['b'] = array_merge(
			$allowed['b'] ?? [],
			[
				'class'                       => true,
				'data-itmms-public-countdown' => true,
				'data-itmms-dua-count-value'  => true,
			]
		);

		$allowed['h4'] = array_merge(
			$allowed['h4'] ?? [],
			[
				'class' => true,
				'id'    => true,
				'data-itmms-calendar-drawer-title' => true,
			]
		);

		return wp_kses( $html, $allowed );
	}

}
