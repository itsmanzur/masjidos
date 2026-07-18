<?php
/**
 * Public shortcodes and assets.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Public-facing bootstrap.
 */
final class ITMMS_Public {

	/** @var ITMMS_Public|null */
	private static ?ITMMS_Public $instance = null;

	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {}
	private function __clone() {}

	/**
	 * Register public hooks.
	 */
	public function init(): void {
		add_shortcode( 'masjidos_prayer_times', [ $this, 'render_prayer_times_shortcode' ] );
		add_shortcode( 'masjidos_jumuah', [ $this, 'render_jumuah_shortcode' ] );
		add_shortcode( 'masjidos_monthly_prayer_times', [ $this, 'render_monthly_prayer_times_shortcode' ] );
		add_shortcode( 'masjidos_announcements', [ $this, 'render_announcements_shortcode' ] );
		add_shortcode( 'masjidos_events', [ $this, 'render_events_shortcode' ] );
		add_shortcode( 'masjidos_islamic_calendar', [ $this, 'render_islamic_calendar_shortcode' ] );
		add_shortcode( 'itmms_calendar', [ $this, 'render_islamic_calendar_shortcode' ] );
		add_shortcode( 'masjidos_duas_azkar', [ $this, 'render_duas_azkar_shortcode' ] );
		add_shortcode( 'masjidos_khutbah_archive', [ $this, 'render_khutbah_archive_shortcode' ] );
		add_shortcode( 'masjidos_khatib_this_week', [ $this, 'render_khatib_this_week_shortcode' ] );
		add_shortcode( 'masjidos_upcoming_khutbah', [ $this, 'render_upcoming_khutbah_shortcode' ] );
		add_shortcode( 'masjidos_khutbah_search', [ $this, 'render_khutbah_search_shortcode' ] );
		add_shortcode( 'masjidos_quran_verse', [ $this, 'render_quran_verse_shortcode' ] );
		add_shortcode( 'masjidos_hadith', [ $this, 'render_hadith_shortcode' ] );
		add_shortcode( 'masjidos_allah_names', [ $this, 'render_allah_names_shortcode' ] );
		add_shortcode( 'masjidos_audio_quran', [ $this, 'render_audio_quran_shortcode' ] );
		add_shortcode( 'masjidos_articles', [ $this, 'render_articles_shortcode' ] );

		// Register Gutenberg blocks.
		add_action( 'init', [ $this, 'register_blocks' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets' ] );

		// Register TV Display rewrites and redirects.
		add_action( 'init', [ $this, 'register_display_rewrites' ] );
		add_filter( 'query_vars', [ $this, 'register_display_query_vars' ] );
		add_action( 'template_redirect', [ $this, 'handle_display_template_redirect' ] );
		add_action( 'template_redirect', [ $this, 'handle_ical_export_redirect' ] );
	}

	/**
	 * Render the public prayer times widget.
	 *
	 * @param array<string,mixed>|string $atts Shortcode attributes.
	 */
	public function render_prayer_times_shortcode( $atts = [] ): string {
		$atts = is_array( $atts ) ? $atts : [];
		$has_custom_title = isset( $atts['title'] ) && '' !== trim( (string) $atts['title'] );

		$atts = shortcode_atts(
			[
				'title' => __( 'Prayer Times', 'masjidos' ),
				'design' => 'classic',
				'language' => ITMMS_Settings::ui_language(),
				'qibla' => 'yes',
				'meta'  => 'yes',
				'compact' => 'no',
				'iqamah' => 'yes',
				'hijri' => 'yes',
			],
			$atts,
			'masjidos_prayer_times'
		);

		$this->enqueue_assets();

		$settings = ITMMS_Settings::get_all();
		if ( empty( $settings['modules']['prayer_times'] ) ) {
			$language = $this->normalize_language( (string) $atts['language'] );
			$empty_title = __( 'Prayer Times is disabled', 'masjidos' );
			$empty_message = __( 'Enable the Prayer Times module before using this shortcode.', 'masjidos' );
			if ( 'bn' === $language ) {
				$empty_title = 'নামাজের সময় বন্ধ আছে';
				$empty_message = 'এই শর্টকোড ব্যবহারের আগে Prayer Times মডিউল চালু করুন।';
			} elseif ( 'ar' === $language ) {
				$empty_title = 'أوقات الصلاة معطلة';
				$empty_message = 'فعّل وحدة أوقات الصلاة قبل استخدام هذا الشورت كود.';
			}
			return $this->render_announcement_empty_state( $empty_title, $empty_message );
		}

		$design = $this->normalize_design( (string) $atts['design'], 'yes' === strtolower( (string) $atts['compact'] ) );
		$designs = $this->get_designs();
		$language = $this->normalize_language( (string) $atts['language'] );
		$labels = $this->labels( $language );
		if ( ! $has_custom_title ) {
			$atts['title'] = $labels['title'];
		}

		$data = ITMMS_Prayer_Times::today();
		if ( empty( $data['prayers'] ) || ! is_array( $data['prayers'] ) ) {
			$empty_title = __( 'No prayer times available', 'masjidos' );
			$empty_message = __( 'Check Prayer Setup: timezone, coordinates, and calculation method.', 'masjidos' );
			if ( 'bn' === $language ) {
				$empty_title = 'নামাজের সময় পাওয়া যায়নি';
				$empty_message = 'Prayer Setup-এ টাইমজোন, কোঅর্ডিনেট ও ক্যালকুলেশন মেথড চেক করুন।';
			} elseif ( 'ar' === $language ) {
				$empty_title = 'لا تتوفر أوقات الصلاة';
				$empty_message = 'تحقق من المنطقة الزمنية والإحداثيات وطريقة الحساب.';
			}
			return $this->render_announcement_empty_state( $empty_title, $empty_message );
		}

		$meta = $data['meta'];
		$next = $data['next_prayer'];
		$next_name = isset( $next['key'] ) ? $this->prayer_label( (string) $next['key'], $language, (string) ( $next['name'] ?? '' ) ) : (string) ( $next['name'] ?? '' );
		$date_label = ITMMS_Hijri::format_label(
			date_i18n( get_option( 'date_format' ), strtotime( (string) ( $data['date'] ?? 'now' ) ) ),
			$language
		);
		$show_hijri = 'no' !== strtolower( (string) $atts['hijri'] );
		$hijri_label = $show_hijri ? $this->hijri_label_for_date( (string) ( $data['date'] ?? 'now' ), $settings, $language ) : '';
		$show_qibla = 'yes' === strtolower( (string) $atts['qibla'] );
		$show_meta = 'yes' === strtolower( (string) $atts['meta'] );
		$is_compact = 'compact' === $design;
		$show_iqamah = 'yes' === strtolower( (string) $atts['iqamah'] );
		$has_iqamah = ! empty(
			array_filter(
				$data['prayers'],
				static function ( $prayer ): bool {
					return ! empty( $prayer['iqamah'] );
				}
			)
		);
		$show_iqamah_column = $show_iqamah && $has_iqamah;
		$source_label = $this->prayer_source_label( $settings, $meta, $labels );
		$trust_items = $this->build_prayer_trust_items( $settings, $meta, $labels, $language );
		$trust_note = (string) ( $labels['moon_note'] ?? '' );

		if ( empty( $designs[ $design ] ) || 'free' !== ( $designs[ $design ]['tier'] ?? 'free' ) ) {
			$rendered = apply_filters( 'masjidos_render_prayer_widget_design', '', $design, $data, $atts, $designs );
			if ( is_string( $rendered ) && '' !== $rendered ) {
				return $this->safe_kses( $rendered );
			}

			return $this->render_locked_design_notice( $design, $designs[ $design ] ?? null );
		}

		ob_start();
		$template_path = ITMMS_PLUGIN_DIR . 'public/templates/prayer-times.php';
		if ( file_exists( $template_path ) ) {
			include $template_path;
		}
		return $this->safe_kses( (string) ob_get_clean() );
	}

	/**
	 * Render a monthly prayer timetable.
	 *
	 * @param array<string,mixed>|string $atts Shortcode attributes.
	 */
	public function render_monthly_prayer_times_shortcode( $atts = [] ): string {
		$atts = is_array( $atts ) ? $atts : [];
		$has_custom_title = isset( $atts['title'] ) && '' !== trim( (string) $atts['title'] );
		$atts = shortcode_atts(
			[
				'title'    => __( 'Monthly Prayer Timetable', 'masjidos' ),
				'month'    => '',
				'year'     => '',
				'language' => ITMMS_Settings::ui_language(),
				'iqamah'   => 'no',
				'design'   => 'table',
				'navigation' => 'yes',
				// Ishraq/Zawal clutter monthly boards; opt in with extras="yes".
				'extras'   => 'no',
			],
			$atts,
			'masjidos_monthly_prayer_times'
		);

		$this->enqueue_assets();

		$settings = ITMMS_Settings::get_all();
		$language = $this->normalize_language( (string) $atts['language'] );
		if ( empty( $settings['modules']['prayer_times'] ) ) {
			$empty_title = __( 'Prayer Times is disabled', 'masjidos' );
			$empty_message = __( 'Enable the Prayer Times module before using this shortcode.', 'masjidos' );
			if ( 'bn' === $language ) {
				$empty_title = 'নামাজের সময় বন্ধ আছে';
				$empty_message = 'এই শর্টকোড ব্যবহারের আগে Prayer Times মডিউল চালু করুন।';
			} elseif ( 'ar' === $language ) {
				$empty_title = 'أوقات الصلاة معطلة';
				$empty_message = 'فعّل وحدة أوقات الصلاة قبل استخدام هذا الشورت كود.';
			}
			return $this->render_announcement_empty_state( $empty_title, $empty_message );
		}

		$timezone = new DateTimeZone( (string) ( $settings['timezone'] ?? wp_timezone_string() ) );
		$now = new DateTimeImmutable( 'now', $timezone );
		$year = '' === (string) $atts['year'] ? (int) $now->format( 'Y' ) : (int) $atts['year'];
		$month = '' === (string) $atts['month'] ? (int) $now->format( 'n' ) : (int) $atts['month'];
		$year = max( 1970, min( 2099, $year ) );
		$month = max( 1, min( 12, $month ) );
		$labels = $this->monthly_labels( $language );
		if ( ! $has_custom_title ) {
			$atts['title'] = $labels['title'];
		}
		$design = sanitize_key( (string) $atts['design'] ) ?: 'table';
		$designs = $this->get_monthly_designs();

		$data = ITMMS_Prayer_Times::for_month( $year, $month, $settings );
		if ( empty( $data['days'] ) || ! is_array( $data['days'] ) ) {
			$empty_title = __( 'No monthly timetable available', 'masjidos' );
			$empty_message = __( 'Check Prayer Setup and try again.', 'masjidos' );
			if ( 'bn' === $language ) {
				$empty_title = 'মাসিক সময়সূচি নেই';
				$empty_message = 'Prayer Setup চেক করে আবার চেষ্টা করুন।';
			} elseif ( 'ar' === $language ) {
				$empty_title = 'لا يتوفر جدول شهري';
				$empty_message = 'تحقق من إعدادات الصلاة ثم أعد المحاولة.';
			}
			return $this->render_announcement_empty_state( $empty_title, $empty_message );
		}

		$show_iqamah = 'yes' === strtolower( (string) $atts['iqamah'] );
		$show_navigation = 'no' !== strtolower( (string) $atts['navigation'] );
		$show_extras = 'yes' === strtolower( (string) $atts['extras'] );
		$meta = $data['meta'] ?? [];
		$prayer_keys = [ 'fajr', 'sunrise', 'dhuhr', 'asr', 'maghrib', 'isha' ];
		if ( $show_extras && ( ! array_key_exists( 'show_ishraq', $settings ) || ! empty( $settings['show_ishraq'] ) ) ) {
			array_splice( $prayer_keys, 2, 0, [ 'ishraq' ] );
		}
		if ( $show_extras && ( ! array_key_exists( 'show_zawal', $settings ) || ! empty( $settings['show_zawal'] ) ) ) {
			$dhuhr_index = array_search( 'dhuhr', $prayer_keys, true );
			if ( false !== $dhuhr_index ) {
				array_splice( $prayer_keys, (int) $dhuhr_index, 0, [ 'zawal' ] );
			}
		}
		$today = $now->format( 'Y-m-d' );
		$month_start = new DateTimeImmutable( sprintf( '%04d-%02d-01 00:00:00', $year, $month ), $timezone );
		$month_end = $month_start->modify( 'last day of this month' );
		$hijri_range_label = ITMMS_Hijri::range_label( $month_start, $month_end, (int) ( $settings['hijri_adjustment'] ?? 0 ), $language );
		$source_label = $this->prayer_source_label( $settings, $meta, $labels );
		$trust_items = $this->build_prayer_trust_items( $settings, $meta, $labels, $language );
		$trust_note = (string) ( $labels['generated_using'] ?? '' );
		if ( '' !== $trust_note ) {
			$trust_note = sprintf(
				/* translators: 1: source label, 2: location */
				$trust_note,
				$source_label,
				(string) ( $meta['location'] ?? '' )
			);
		}

		if ( empty( $designs[ $design ] ) || 'free' !== ( $designs[ $design ]['tier'] ?? 'free' ) ) {
			$rendered = apply_filters( 'masjidos_render_monthly_prayer_widget_design', '', $design, $data, $atts, $designs );
			if ( is_string( $rendered ) && '' !== $rendered ) {
				return $this->safe_kses( $rendered );
			}

			return $this->render_locked_monthly_design_notice( $design, $designs[ $design ] ?? null );
		}

		ob_start();
		$template_path = ITMMS_PLUGIN_DIR . 'public/templates/monthly-prayer-times.php';
		if ( file_exists( $template_path ) ) {
			include $template_path;
		}
		return $this->safe_kses( (string) ob_get_clean() );
	}

	/**
	 * Render the public Islamic Calendar.
	 *
	 * @param array<string,mixed>|string $atts Shortcode attributes.
	 * @return string Rendered calendar HTML.
	 */
	public function render_islamic_calendar_shortcode( $atts = [] ): string {
		$atts = is_array( $atts ) ? $atts : [];
		$has_custom_title = isset( $atts['title'] ) && '' !== trim( (string) $atts['title'] );
		$atts = shortcode_atts(
			[
				'title'      => __( 'Islamic Calendar', 'masjidos' ),
				'month'      => '',
				'year'       => '',
				'language'   => ITMMS_Settings::ui_language(),
				'navigation' => 'yes',
			],
			$atts,
			'masjidos_islamic_calendar'
		);

		$this->enqueue_assets();

		$settings = ITMMS_Settings::get_all();
		$timezone = new DateTimeZone( (string) ( $settings['timezone'] ?? wp_timezone_string() ) );
		$now = new DateTimeImmutable( 'now', $timezone );
		$year = '' === (string) $atts['year'] ? (int) $now->format( 'Y' ) : (int) $atts['year'];
		$month = '' === (string) $atts['month'] ? (int) $now->format( 'n' ) : (int) $atts['month'];
		$year = max( 1970, min( 2099, $year ) );
		$month = max( 1, min( 12, $month ) );
		$language = $this->normalize_language( (string) $atts['language'] );

		$labels = [
			'en' => [
				'title'         => 'Islamic Calendar',
				'navigation'    => 'Calendar Navigation',
				'previous'      => 'Previous Month',
				'next'          => 'Next Month',
				'month'         => 'Month',
				'year'          => 'Year',
				'current_month' => 'This month',
				'today'         => 'Today',
				'events'        => 'Mosque events',
				'no_events'     => 'No events scheduled',
				'holy_day'      => 'Holy day',
				'legend_today'  => 'Today',
				'legend_holy'   => 'Holy day',
				'legend_event'  => 'Mosque event',
				'more_events'   => '+%d more',
			],
			'bn' => [
				'title'         => 'ইসলামিক ক্যালেন্ডার',
				'navigation'    => 'ক্যালেন্ডার নেভিগেশন',
				'previous'      => 'পূর্ববর্তী মাস',
				'next'          => 'পরবর্তী মাস',
				'month'         => 'মাস',
				'year'          => 'বছর',
				'current_month' => 'চলতি মাস',
				'today'         => 'আজ',
				'events'        => 'মসজিদের ইভেন্ট',
				'no_events'     => 'কোনো ইভেন্ট নেই',
				'holy_day'      => 'পবিত্র দিবস',
				'legend_today'  => 'আজ',
				'legend_holy'   => 'পবিত্র দিবস',
				'legend_event'  => 'মসজিদের ইভেন্ট',
				'more_events'   => '+আরও %d',
			],
			'ar' => [
				'title'         => 'التقويم الإسلامي',
				'navigation'    => 'تصفح التقويم',
				'previous'      => 'الشهر السابق',
				'next'          => 'الشهر التالي',
				'month'         => 'الشهر',
				'year'          => 'السنة',
				'current_month' => 'هذا الشهر',
				'today'         => 'اليوم',
				'events'        => 'فعاليات المسجد',
				'no_events'     => 'لا توجد فعاليات',
				'holy_day'      => 'يوم مقدس',
				'legend_today'  => 'اليوم',
				'legend_holy'   => 'يوم مقدس',
				'legend_event'  => 'فعالية المسجد',
				'more_events'   => '+%d إضافية',
			],
		];

		$active_labels = $labels[ $language ] ?? $labels['en'];
		if ( ! $has_custom_title ) {
			$atts['title'] = $active_labels['title'];
		}

		$show_navigation = 'no' !== strtolower( (string) $atts['navigation'] );
		$today = $now->format( 'Y-m-d' );

		ob_start();
		$template_path = ITMMS_PLUGIN_DIR . 'public/templates/islamic-calendar.php';
		if ( file_exists( $template_path ) ) {
			include $template_path;
		}
		return $this->safe_kses( (string) ob_get_clean() );
	}

	/**
	 * Render a lightweight public Duas & Azkar widget.
	 *
	 * @param array<string,mixed>|string $atts Shortcode attributes.
	 * @return string Rendered widget HTML.
	 */
	public function render_duas_azkar_shortcode( $atts = [] ): string {
		$atts = is_array( $atts ) ? $atts : [];
		$has_custom_title = isset( $atts['title'] ) && '' !== trim( (string) $atts['title'] );
		$atts = shortcode_atts(
			[
				'title'    => __( 'Duas & Azkar', 'masjidos' ),
				'category' => 'all',
				'language' => ITMMS_Settings::ui_language(),
				'limit'    => '4',
				'design'   => 'cards',
				'source'   => 'yes',
				'counter'  => 'yes',
				'share'    => 'yes',
				'audio'    => 'yes',
			],
			$atts,
			'masjidos_duas_azkar'
		);

		$settings = ITMMS_Settings::get_all();
		if ( empty( $settings['modules']['duas_azkar'] ) ) {
			return $this->render_announcement_empty_state(
				__( 'Duas & Azkar is disabled', 'masjidos' ),
				__( 'Enable the Duas & Azkar module before using this shortcode.', 'masjidos' )
			);
		}

		$this->enqueue_assets();

		$language = $this->normalize_language( (string) $atts['language'] );
		$labels = ITMMS_Duas_Azkar::labels( $language );
		if ( ! $has_custom_title ) {
			$atts['title'] = $labels['title'];
		}

		$design = sanitize_key( (string) $atts['design'] ) ?: 'cards';
		if ( ! in_array( $design, [ 'cards', 'compact' ], true ) ) {
			$design = 'cards';
		}

		$category = sanitize_key( (string) $atts['category'] ) ?: 'all';
		$limit = max( 1, min( 12, absint( $atts['limit'] ) ?: 4 ) );
		$show_source = 'no' !== strtolower( (string) $atts['source'] );
		$show_counter = 'no' !== strtolower( (string) $atts['counter'] );
		$show_share = 'no' !== strtolower( (string) $atts['share'] );
		$show_audio = 'no' !== strtolower( (string) $atts['audio'] );
		$items = ITMMS_Duas_Azkar::items( $language, $category, $limit );
		$category_labels = ITMMS_Duas_Azkar::category_labels( $language );
		$category_label = $category_labels[ $category ] ?? ucwords( str_replace( '-', ' ', $category ) );

		if ( empty( $items ) ) {
			$empty_title = __( 'No duas found', 'masjidos' );
			$empty_message = __( 'Try another category or increase the limit.', 'masjidos' );
			if ( 'bn' === $language ) {
				$empty_title = 'কোনো দোয়া পাওয়া যায়নি';
				$empty_message = 'অন্য ক্যাটাগরি চেষ্টা করুন অথবা লিমিট বাড়ান।';
			} elseif ( 'ar' === $language ) {
				$empty_title = 'لا توجد أدعية';
				$empty_message = 'جرّب فئة أخرى أو زد الحد.';
			}
			return $this->render_announcement_empty_state( $empty_title, $empty_message );
		}

		ob_start();
		$template_path = ITMMS_PLUGIN_DIR . 'public/templates/duas-azkar.php';
		if ( file_exists( $template_path ) ) {
			include $template_path;
		}
		return $this->safe_kses( (string) ob_get_clean() );
	}

	/**
	 * Render active public announcements.
	 *
	 * @param array<string,mixed>|string $atts Shortcode attributes.
	 */
	public function render_announcements_shortcode( $atts = [] ): string {
		$atts = is_array( $atts ) ? $atts : [];
		$has_custom_title = isset( $atts['title'] ) && '' !== trim( (string) $atts['title'] );
		$atts = shortcode_atts(
			[
				'title'     => __( 'Masjid Notices', 'masjidos' ),
				'design'    => 'list',
				'language'  => ITMMS_Settings::ui_language(),
				'type'      => 'all',
				'limit'     => '5',
				'show_date' => 'yes',
			],
			$atts,
			'masjidos_announcements'
		);

		$settings = ITMMS_Settings::get_all();
		if ( empty( $settings['modules']['announcements'] ) ) {
			return $this->render_announcement_empty_state(
				__( 'Announcements are disabled', 'masjidos' ),
				__( 'Enable the Announcements module before using this shortcode.', 'masjidos' )
			);
		}

		$this->enqueue_assets();
		$design = sanitize_key( (string) $atts['design'] ) ?: 'list';
		$language = $this->normalize_language( (string) $atts['language'] );
		$labels = $this->announcement_labels( $language );
		if ( ! $has_custom_title ) {
			$atts['title'] = $labels['title'];
		}
		$type = sanitize_key( (string) $atts['type'] );
		$limit = max( 1, min( 20, absint( $atts['limit'] ) ?: 5 ) );
		$show_date = 'no' !== strtolower( (string) $atts['show_date'] );
		$designs = $this->get_announcement_designs();
		$notices = ITMMS_Announcements::active( $limit, $type );

		if ( empty( $designs[ $design ] ) || 'free' !== ( $designs[ $design ]['tier'] ?? 'free' ) ) {
			$rendered = apply_filters( 'masjidos_render_announcement_widget_design', '', $design, $notices, $settings, $atts, $designs );
			if ( is_string( $rendered ) && '' !== $rendered ) {
				return $this->safe_kses( $rendered );
			}

			return $this->render_locked_announcement_design_notice( $design, $designs[ $design ] ?? null );
		}

		if ( empty( $notices ) ) {
			return $this->render_announcement_empty_state(
				__( 'No active notices', 'masjidos' ),
				__( 'Create a published notice whose start date has arrived and end date has not passed.', 'masjidos' )
			);
		}

		$location = implode( ', ', array_filter( [ $settings['city'] ?? '', $settings['country'] ?? '' ] ) );
		ob_start();
		$template_path = ITMMS_PLUGIN_DIR . 'public/templates/announcements.php';
		if ( file_exists( $template_path ) ) {
			include $template_path;
		}
		return $this->safe_kses( (string) ob_get_clean() );
	}

	/**
	 * Render active public events.
	 *
	 * @param array<string,mixed>|string $atts Shortcode attributes.
	 */
	public function render_events_shortcode( $atts = [] ): string {
		$atts = is_array( $atts ) ? $atts : [];
		$has_custom_title = isset( $atts['title'] ) && '' !== trim( (string) $atts['title'] );
		$atts = shortcode_atts(
			[
				'title'     => __( 'Upcoming Events', 'masjidos' ),
				'design'    => 'list',
				'language'  => ITMMS_Settings::ui_language(),
				'limit'     => '5',
			],
			$atts,
			'masjidos_events'
		);

		$settings = ITMMS_Settings::get_all();
		if ( empty( $settings['modules']['events'] ) ) {
			return $this->render_announcement_empty_state(
				__( 'Events are disabled', 'masjidos' ),
				__( 'Enable the Events module before using this shortcode.', 'masjidos' )
			);
		}

		$this->enqueue_assets();
		$design = sanitize_key( (string) $atts['design'] ) ?: 'list';
		$language = $this->normalize_language( (string) $atts['language'] );
		if ( ! $has_custom_title ) {
			if ( 'bn' === $language ) {
				$atts['title'] = 'আসন্ন ইভেন্টসমূহ';
			} elseif ( 'ar' === $language ) {
				$atts['title'] = 'الفعاليات القادمة';
			} else {
				$atts['title'] = __( 'Upcoming Events', 'masjidos' );
			}
		}
		$limit = max( 1, min( 20, absint( $atts['limit'] ) ?: 5 ) );
		$events = ITMMS_Events::active( $limit );

		if ( empty( $events ) ) {
			return $this->render_announcement_empty_state(
				__( 'No upcoming events', 'masjidos' ),
				__( 'Check back later for community lectures, prayers, and charity events.', 'masjidos' )
			);
		}

		$location = implode( ', ', array_filter( [ $settings['city'] ?? '', $settings['country'] ?? '' ] ) );
		ob_start();
		$template_path = ITMMS_PLUGIN_DIR . 'public/templates/events.php';
		if ( file_exists( $template_path ) ) {
			include $template_path;
		}
		return $this->safe_kses( (string) ob_get_clean() );
	}

	/**
	 * Render the public Jumuah widget.
	 *
	 * @param array<string,mixed>|string $atts Shortcode attributes.
	 */
	public function render_jumuah_shortcode( $atts = [] ): string {
		$atts = is_array( $atts ) ? $atts : [];
		$has_custom_title = isset( $atts['title'] ) && '' !== trim( (string) $atts['title'] );

		$atts = shortcode_atts(
			[
				'title'    => __( 'Jumuah Prayer', 'masjidos' ),
				'design'   => 'classic',
				'language' => ITMMS_Settings::ui_language(),
				'meta'     => 'yes',
			],
			$atts,
			'masjidos_jumuah'
		);

		$this->enqueue_assets();

		$settings = ITMMS_Settings::get_all();
		$jumuah = isset( $settings['jumuah'] ) && is_array( $settings['jumuah'] ) ? $settings['jumuah'] : [];
		if ( empty( $jumuah['enabled'] ) ) {
			if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'itmms_manage_prayers' ) ) {
				return '';
			}

			return '<div class="itmms-public-announcement-empty">' .
				'<strong>' . esc_html__( 'Jumuah widget is disabled', 'masjidos' ) . '</strong>' .
				'<p>' . esc_html__( 'Enable Jumuah in Prayer Setup → Jumuah, then publish this block or shortcode.', 'masjidos' ) . '</p>' .
				'<code>[masjidos_jumuah]</code>' .
			'</div>';
		}

		$design = $this->normalize_design( (string) $atts['design'], false );
		$designs = $this->get_jumuah_designs();
		$language = $this->normalize_language( (string) $atts['language'] );
		$labels = $this->jumuah_labels( $language );
		$show_meta = 'yes' === strtolower( (string) $atts['meta'] );
		$is_compact = 'compact' === $design;
		if ( ! $has_custom_title ) {
			$atts['title'] = $labels['title'];
		}

		if ( empty( $designs[ $design ] ) || 'free' !== ( $designs[ $design ]['tier'] ?? 'free' ) ) {
			$rendered = apply_filters( 'masjidos_render_jumuah_widget_design', '', $design, $jumuah, $settings, $atts, $designs );
			if ( is_string( $rendered ) && '' !== $rendered ) {
				return $this->safe_kses( $rendered );
			}

			return $this->render_locked_jumuah_design_notice( $design, $designs[ $design ] ?? null );
		}

		$location = implode( ', ', array_filter( [ $settings['city'] ?? '', $settings['country'] ?? '' ] ) );
		$timezone = (string) ( $settings['timezone'] ?? 'UTC' );
		$sessions = isset( $jumuah['sessions'] ) && is_array( $jumuah['sessions'] ) ? $jumuah['sessions'] : [];
		if ( empty( $sessions ) ) {
			$sessions = [
				[
					'label'       => $labels['first_jumuah'],
					'khutbah_time' => $jumuah['khutbah_time'] ?? '',
					'jamaat_time'  => $jumuah['jamaat_time'] ?? '',
				],
			];
		}
		$khatib = isset( $jumuah['khatib'] ) && is_array( $jumuah['khatib'] ) ? $jumuah['khatib'] : [ 'name' => (string) ( $jumuah['khatib'] ?? '' ) ];
		$khatib_name = (string) ( $khatib['name'] ?? '' );
		$khatib_image = (string) ( $khatib['image_url'] ?? '' );
		$khatib_bio = (string) ( $khatib['bio'] ?? '' );
		$topic = (string) ( $jumuah['topic'] ?? '' );
		$jumuah_language = (string) ( $jumuah['language'] ?? '' );
		$notice = (string) ( $jumuah['notice'] ?? '' );
		$has_khatib_profile = $khatib_name || $khatib_image || $khatib_bio;
		$has_details = $topic || $jumuah_language || $location;
		$has_meta = $show_meta && ( $has_khatib_profile || $has_details );
		$meta_lite = $has_meta && ! $has_khatib_profile;

		ob_start();
		$template_path = ITMMS_PLUGIN_DIR . 'public/templates/jumuah.php';
		if ( file_exists( $template_path ) ) {
			include $template_path;
		}
		return $this->safe_kses( (string) ob_get_clean() );
	}

	/**
	 * Available public widget designs. Pro can add designs with this filter,
	 * but free plugin only ships free design renderers.
	 *
	 * @return array<string,array<string,string>>
	 */
	private function get_designs(): array {
		$designs = [
			'classic' => [
				'label'       => __( 'Classic', 'masjidos' ),
				'tier'        => 'free',
				'description' => __( 'Balanced prayer times card with Qibla and meta panel.', 'masjidos' ),
			],
			'compact' => [
				'label'       => __( 'Compact', 'masjidos' ),
				'tier'        => 'free',
				'description' => __( 'Narrow prayer table for sidebars and smaller sections.', 'masjidos' ),
			],
			'premium-card' => [
				'label'       => __( 'Premium Card', 'masjidos' ),
				'tier'        => 'pro',
				'description' => __( 'Available in MasjidOS Pro.', 'masjidos' ),
			],
			'mosque-display' => [
				'label'       => __( 'Mosque Display', 'masjidos' ),
				'tier'        => 'pro',
				'description' => __( 'Available in MasjidOS Pro.', 'masjidos' ),
			],
			'ramadan-special' => [
				'label'       => __( 'Ramadan Special', 'masjidos' ),
				'tier'        => 'pro',
				'description' => __( 'Available in MasjidOS Pro.', 'masjidos' ),
			],
		];

		/**
		 * Filter public prayer widget design registry.
		 *
		 * Pro plugins may add their own design keys and render them through
		 * masjidos_render_prayer_widget_design.
		 *
		 * @param array<string,array<string,string>> $designs Design registry.
		 */
		$filtered = apply_filters( 'masjidos_prayer_widget_designs', $designs );
		return is_array( $filtered ) ? $filtered : $designs;
	}

	/**
	 * @return array<string,array<string,string>>
	 */
	private function get_jumuah_designs(): array {
		$designs = [
			'classic' => [
				'label'       => __( 'Classic', 'masjidos' ),
				'tier'        => 'free',
				'description' => __( 'Wide Jumuah card with khutbah, jamaat, and meta details.', 'masjidos' ),
			],
			'compact' => [
				'label'       => __( 'Compact', 'masjidos' ),
				'tier'        => 'free',
				'description' => __( 'Narrow Jumuah card for sidebars and mobile-first sections.', 'masjidos' ),
			],
			'premium-sermon' => [
				'label'       => __( 'Premium Sermon', 'masjidos' ),
				'tier'        => 'pro',
				'description' => __( 'Available in MasjidOS Pro.', 'masjidos' ),
			],
			'mosque-notice' => [
				'label'       => __( 'Mosque Notice', 'masjidos' ),
				'tier'        => 'pro',
				'description' => __( 'Available in MasjidOS Pro.', 'masjidos' ),
			],
		];

		/**
		 * Filter public Jumuah widget design registry.
		 *
		 * Pro plugins may add their own design keys and render them through
		 * masjidos_render_jumuah_widget_design.
		 *
		 * @param array<string,array<string,string>> $designs Design registry.
		 */
		$filtered = apply_filters( 'masjidos_jumuah_widget_designs', $designs );
		return is_array( $filtered ) ? $filtered : $designs;
	}

	/**
	 * @return array<string,array<string,string>>
	 */
	private function get_monthly_designs(): array {
		$designs = [
			'table' => [
				'label'       => __( 'Table', 'masjidos' ),
				'tier'        => 'free',
				'description' => __( 'Clean monthly prayer timetable table.', 'masjidos' ),
			],
			'compact' => [
				'label'       => __( 'Compact', 'masjidos' ),
				'tier'        => 'free',
				'description' => __( 'Card-style monthly timetable for narrower sections.', 'masjidos' ),
			],
			'premium-print' => [
				'label'       => __( 'Premium Print', 'masjidos' ),
				'tier'        => 'pro',
				'description' => __( 'Available in MasjidOS Pro.', 'masjidos' ),
			],
			'mosque-board' => [
				'label'       => __( 'Mosque Board', 'masjidos' ),
				'tier'        => 'pro',
				'description' => __( 'Available in MasjidOS Pro.', 'masjidos' ),
			],
			'ramadan-monthly' => [
				'label'       => __( 'Ramadan Monthly', 'masjidos' ),
				'tier'        => 'pro',
				'description' => __( 'Available in MasjidOS Pro.', 'masjidos' ),
			],
		];

		/**
		 * Filter public monthly timetable design registry.
		 *
		 * Pro plugins may add their own design keys and render them through
		 * masjidos_render_monthly_prayer_widget_design.
		 *
		 * @param array<string,array<string,string>> $designs Design registry.
		 */
		$filtered = apply_filters( 'masjidos_monthly_prayer_widget_designs', $designs );
		return is_array( $filtered ) ? $filtered : $designs;
	}

	/**
	 * @return array<string,array<string,string>>
	 */
	private function get_announcement_designs(): array {
		$designs = [
			'list' => [
				'label'       => __( 'Notice List', 'masjidos' ),
				'tier'        => 'free',
				'description' => __( 'Readable public notice board.', 'masjidos' ),
			],
			'ticker' => [
				'label'       => __( 'Notice Ticker', 'masjidos' ),
				'tier'        => 'free',
				'description' => __( 'Compact scrolling announcement strip.', 'masjidos' ),
			],
			'banner' => [
				'label'       => __( 'Notice Banner', 'masjidos' ),
				'tier'        => 'free',
				'description' => __( 'Slim top banner for the highest-priority notice.', 'masjidos' ),
			],
			'popup' => [
				'label'       => __( 'Popup Modal', 'masjidos' ),
				'tier'        => 'free',
				'description' => __( 'Dismissible modal for urgent announcements.', 'masjidos' ),
			],
			'digital-board' => [
				'label'       => __( 'Digital Board', 'masjidos' ),
				'tier'        => 'pro',
				'description' => __( 'Available in MasjidOS Pro.', 'masjidos' ),
			],
			'ramadan-banner' => [
				'label'       => __( 'Ramadan Banner', 'masjidos' ),
				'tier'        => 'pro',
				'description' => __( 'Available in MasjidOS Pro.', 'masjidos' ),
			],
		];

		$filtered = apply_filters( 'masjidos_announcement_widget_designs', $designs );
		return is_array( $filtered ) ? $filtered : $designs;
	}

	private function normalize_design( string $design, bool $legacy_compact ): string {
		$design = sanitize_key( $design );
		if ( $legacy_compact ) {
			return 'compact';
		}

		return $design ?: 'classic';
	}

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

	/**
	 * @param array<string,string>|null $definition Design definition.
	 */
	private function render_locked_design_notice( string $design, ?array $definition ): string {
		return $this->render_pro_lock_notice(
			$definition['label'] ?? ucwords( str_replace( '-', ' ', $design ) ),
			__( 'This prayer widget design is available in MasjidOS Pro.', 'masjidos' ),
			'[masjidos_prayer_times design="' . $design . '"]'
		);
	}

	/**
	 * @param array<string,string>|null $definition Design definition.
	 */
	private function render_locked_jumuah_design_notice( string $design, ?array $definition ): string {
		return $this->render_pro_lock_notice(
			$definition['label'] ?? ucwords( str_replace( '-', ' ', $design ) ),
			__( 'This Jumuah widget design is available in MasjidOS Pro.', 'masjidos' ),
			'[masjidos_jumuah design="' . $design . '"]'
		);
	}

	/**
	 * @param array<string,string>|null $definition Design definition.
	 */
	private function render_locked_monthly_design_notice( string $design, ?array $definition ): string {
		return $this->render_pro_lock_notice(
			$definition['label'] ?? ucwords( str_replace( '-', ' ', $design ) ),
			__( 'This monthly timetable design is available in MasjidOS Pro.', 'masjidos' ),
			'[masjidos_monthly_prayer_times design="' . $design . '"]'
		);
	}

	/**
	 * @param array<string,string>|null $definition Design definition.
	 */
	private function render_locked_announcement_design_notice( string $design, ?array $definition ): string {
		return $this->render_pro_lock_notice(
			$definition['label'] ?? ucwords( str_replace( '-', ' ', $design ) ),
			__( 'This announcement widget design is available in MasjidOS Pro.', 'masjidos' ),
			'[masjidos_announcements design="' . $design . '"]'
		);
	}

	/**
	 * Shared Pro lock notice with marketing CTA (no Pro code).
	 */
	private function render_pro_lock_notice( string $label, string $message, string $shortcode ): string {
		$pro_url = function_exists( 'masjidos_pro_url' ) ? masjidos_pro_url() : '';
		$cta     = '';
		if ( $pro_url && ! masjidos_pro_is_active() ) {
			$cta = '<p class="itmms-public-prayer-lock__cta"><a href="' . esc_url( $pro_url ) . '" target="_blank" rel="noopener noreferrer">' .
				esc_html__( 'Learn about MasjidOS Pro', 'masjidos' ) .
			'</a></p>';
		}

		return '<div class="itmms-public-prayer-lock">' .
			'<strong>' . esc_html( $label ) . '</strong>' .
			'<p>' . esc_html( $message ) . '</p>' .
			'<code>' . esc_html( $shortcode ) . '</code>' .
			$cta .
		'</div>';
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
	 * Register Gutenberg blocks.
	 */
	public function register_blocks(): void {
		$language_attr = [
			'type'    => 'string',
			'default' => 'en',
		];
		$title_attr = static function ( string $default ): array {
			return [
				'type'    => 'string',
				'default' => $default,
			];
		};

		register_block_type(
			'masjidos/prayer-times',
			[
				'render_callback' => [ $this, 'render_prayer_times_block' ],
				'attributes'      => [
					'title'    => $title_attr( __( 'Prayer Times', 'masjidos' ) ),
					'design'   => [ 'type' => 'string', 'default' => 'classic' ],
					'language' => $language_attr,
					'qibla'    => [ 'type' => 'string', 'default' => 'yes' ],
					'meta'     => [ 'type' => 'string', 'default' => 'yes' ],
					'iqamah'   => [ 'type' => 'string', 'default' => 'yes' ],
					'hijri'    => [ 'type' => 'string', 'default' => 'yes' ],
				],
			]
		);

		register_block_type(
			'masjidos/islamic-calendar',
			[
				'render_callback' => [ $this, 'render_islamic_calendar_block' ],
				'attributes'      => [
					'title'    => $title_attr( __( 'Islamic Calendar', 'masjidos' ) ),
					'language' => $language_attr,
				],
			]
		);

		register_block_type(
			'masjidos/monthly-prayer-times',
			[
				'render_callback' => [ $this, 'render_monthly_prayer_times_block' ],
				'attributes'      => [
					'title'      => $title_attr( __( 'Monthly Prayer Timetable', 'masjidos' ) ),
					'language'   => $language_attr,
					'design'     => [ 'type' => 'string', 'default' => 'table' ],
					'iqamah'     => [ 'type' => 'string', 'default' => 'no' ],
					'navigation' => [ 'type' => 'string', 'default' => 'yes' ],
					'extras'     => [ 'type' => 'string', 'default' => 'no' ],
				],
			]
		);

		register_block_type(
			'masjidos/jumuah',
			[
				'render_callback' => [ $this, 'render_jumuah_block' ],
				'attributes'      => [
					'title'    => $title_attr( __( 'Jumuah Prayer', 'masjidos' ) ),
					'language' => $language_attr,
					'design'   => [ 'type' => 'string', 'default' => 'classic' ],
					'meta'     => [ 'type' => 'string', 'default' => 'yes' ],
				],
			]
		);

		register_block_type(
			'masjidos/announcements',
			[
				'render_callback' => [ $this, 'render_announcements_block' ],
				'attributes'      => [
					'title'     => $title_attr( __( 'Masjid Notices', 'masjidos' ) ),
					'language'  => $language_attr,
					'design'    => [ 'type' => 'string', 'default' => 'list' ],
					'type'      => [ 'type' => 'string', 'default' => 'all' ],
					'limit'     => [ 'type' => 'string', 'default' => '5' ],
					'show_date' => [ 'type' => 'string', 'default' => 'yes' ],
				],
			]
		);

		register_block_type(
			'masjidos/events',
			[
				'render_callback' => [ $this, 'render_events_block' ],
				'attributes'      => [
					'title'    => $title_attr( __( 'Upcoming Events', 'masjidos' ) ),
					'language' => $language_attr,
					'limit'    => [ 'type' => 'string', 'default' => '5' ],
				],
			]
		);

		register_block_type(
			'masjidos/duas-azkar',
			[
				'render_callback' => [ $this, 'render_duas_azkar_block' ],
				'attributes'      => [
					'title'    => $title_attr( __( 'Duas & Azkar', 'masjidos' ) ),
					'language' => $language_attr,
					'category' => [ 'type' => 'string', 'default' => 'all' ],
					'limit'    => [ 'type' => 'string', 'default' => '4' ],
					'design'   => [ 'type' => 'string', 'default' => 'cards' ],
					'source'   => [ 'type' => 'string', 'default' => 'yes' ],
					'counter'  => [ 'type' => 'string', 'default' => 'yes' ],
					'share'    => [ 'type' => 'string', 'default' => 'yes' ],
					'audio'    => [ 'type' => 'string', 'default' => 'yes' ],
				],
			]
		);

		register_block_type(
			'masjidos/khutbah-archive',
			[
				'render_callback' => [ $this, 'render_khutbah_archive_block' ],
				'attributes'      => [
					'title'    => $title_attr( __( 'Jumuah Khutbah Archive', 'masjidos' ) ),
					'language' => $language_attr,
					'limit'    => [ 'type' => 'string', 'default' => '12' ],
					'category' => [ 'type' => 'string', 'default' => '' ],
				],
			]
		);

		register_block_type(
			'masjidos/khatib-this-week',
			[
				'render_callback' => [ $this, 'render_khatib_this_week_block' ],
				'attributes'      => [
					'title'    => $title_attr( __( 'This Week\'s Khatib', 'masjidos' ) ),
					'language' => $language_attr,
				],
			]
		);

		register_block_type(
			'masjidos/upcoming-khutbah',
			[
				'render_callback' => [ $this, 'render_upcoming_khutbah_block' ],
				'attributes'      => [
					'title'    => $title_attr( __( 'Upcoming Khutbahs', 'masjidos' ) ),
					'language' => $language_attr,
					'limit'    => [ 'type' => 'string', 'default' => '5' ],
				],
			]
		);

		register_block_type(
			'masjidos/khutbah-search',
			[
				'render_callback' => [ $this, 'render_khutbah_search_block' ],
				'attributes'      => [
					'title'    => $title_attr( __( 'Search Khutbah Archive', 'masjidos' ) ),
					'language' => $language_attr,
					'limit'    => [ 'type' => 'string', 'default' => '6' ],
				],
			]
		);

		register_block_type(
			'masjidos/quran-verse',
			[
				'render_callback' => [ $this, 'render_quran_verse_block' ],
				'attributes'      => [
					'title'    => $title_attr( __( 'Quran Verse of the Day', 'masjidos' ) ),
					'language' => $language_attr,
					'design'   => [ 'type' => 'string', 'default' => 'classic' ],
					'share'    => [ 'type' => 'string', 'default' => 'yes' ],
					'tafsir'   => [ 'type' => 'string', 'default' => 'yes' ],
				],
			]
		);

		register_block_type(
			'masjidos/hadith',
			[
				'render_callback' => [ $this, 'render_hadith_block' ],
				'attributes'      => [
					'title'    => $title_attr( __( 'Hadith of the Day', 'masjidos' ) ),
					'language' => $language_attr,
					'design'   => [ 'type' => 'string', 'default' => 'classic' ],
					'share'    => [ 'type' => 'string', 'default' => 'yes' ],
				],
			]
		);

		register_block_type(
			'masjidos/allah-names',
			[
				'render_callback' => [ $this, 'render_allah_names_block' ],
				'attributes'      => [
					'title'    => $title_attr( __( '99 Names of Allah', 'masjidos' ) ),
					'language' => $language_attr,
					'design'   => [ 'type' => 'string', 'default' => 'grid' ],
					'limit'    => [ 'type' => 'string', 'default' => '99' ],
				],
			]
		);

		register_block_type(
			'masjidos/audio-quran',
			[
				'render_callback' => [ $this, 'render_audio_quran_block' ],
				'attributes'      => [
					'title'    => $title_attr( __( 'Audio Quran Player', 'masjidos' ) ),
					'language' => $language_attr,
					'design'   => [ 'type' => 'string', 'default' => 'classic' ],
				],
			]
		);

		register_block_type(
			'masjidos/articles',
			[
				'render_callback' => [ $this, 'render_articles_block' ],
				'attributes'      => [
					'title'    => $title_attr( __( 'Islamic Articles', 'masjidos' ) ),
					'language' => $language_attr,
					'category' => [ 'type' => 'string', 'default' => '' ],
					'limit'    => [ 'type' => 'string', 'default' => '6' ],
					'excerpt'  => [ 'type' => 'string', 'default' => 'yes' ],
					'design'   => [ 'type' => 'string', 'default' => 'grid' ],
				],
			]
		);
	}

	public function render_prayer_times_block( array $attributes ): string {
		return $this->render_prayer_times_shortcode( $attributes );
	}

	public function render_islamic_calendar_block( array $attributes ): string {
		return $this->render_islamic_calendar_shortcode( $attributes );
	}

	public function render_monthly_prayer_times_block( array $attributes ): string {
		return $this->render_monthly_prayer_times_shortcode( $attributes );
	}

	public function render_jumuah_block( array $attributes ): string {
		return $this->render_jumuah_shortcode( $attributes );
	}

	public function render_announcements_block( array $attributes ): string {
		return $this->render_announcements_shortcode( $attributes );
	}

	public function render_events_block( array $attributes ): string {
		return $this->render_events_shortcode( $attributes );
	}

	public function render_duas_azkar_block( array $attributes ): string {
		return $this->render_duas_azkar_shortcode( $attributes );
	}

	public function render_khutbah_archive_block( array $attributes ): string {
		return $this->render_khutbah_archive_shortcode( $attributes );
	}

	public function render_khatib_this_week_block( array $attributes ): string {
		return $this->render_khatib_this_week_shortcode( $attributes );
	}

	public function render_upcoming_khutbah_block( array $attributes ): string {
		return $this->render_upcoming_khutbah_shortcode( $attributes );
	}

	public function render_khutbah_search_block( array $attributes ): string {
		return $this->render_khutbah_search_shortcode( $attributes );
	}

	public function render_quran_verse_block( array $attributes ): string {
		return $this->render_quran_verse_shortcode( $attributes );
	}

	public function render_hadith_block( array $attributes ): string {
		return $this->render_hadith_shortcode( $attributes );
	}

	public function render_allah_names_block( array $attributes ): string {
		return $this->render_allah_names_shortcode( $attributes );
	}

	public function render_audio_quran_block( array $attributes ): string {
		return $this->render_audio_quran_shortcode( $attributes );
	}

	public function render_articles_block( array $attributes ): string {
		return $this->render_articles_shortcode( $attributes );
	}

	/**
	 * Enqueue Gutenberg block editor assets.
	 */
	public function enqueue_block_editor_assets(): void {
		wp_enqueue_script(
			'itmms-block-editor',
			ITMMS_PLUGIN_URL . 'admin/assets/js/block-editor.js',
			[ 'wp-blocks', 'wp-components', 'wp-block-editor', 'wp-element', 'wp-i18n' ],
			ITMMS_VERSION,
			true
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'itmms-block-editor', 'masjidos', ITMMS_PLUGIN_DIR . 'languages' );
		}

		wp_localize_script(
			'itmms-block-editor',
			'itmmsBlockData',
			[
				'defaultLanguage' => ITMMS_Settings::ui_language(),
			]
		);
	}

	/**
	 * Register TV Display rewrite rules.
	 */
	public function register_display_rewrites(): void {
		add_rewrite_rule( '^masjidos-display/?$', 'index.php?masjidos_display=1', 'top' );
	}

	/**
	 * Register TV Display query vars.
	 *
	 * @param array<int,string> $vars Query variables.
	 * @return array<int,string>
	 */
	public function register_display_query_vars( array $vars ): array {
		$vars[] = 'masjidos_display';
		return $vars;
	}

	public function handle_display_template_redirect(): void {
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$is_pretty_display = preg_match( '#/masjidos-display/?(\?.*)?$#', $request_uri );
		$is_plain_display  = (int) get_query_var( 'masjidos_display' ) === 1;

		if ( $is_pretty_display || $is_plain_display ) {
			// Disable browser cache to keep prayer times accurate
			nocache_headers();

			$settings = ITMMS_Settings::get_all();
			$template_path = ITMMS_PLUGIN_DIR . 'public/templates/tv-display.php';
			if ( file_exists( $template_path ) ) {
				include $template_path;
				exit;
			}
		}
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

	/**
	 * Build Google / Outlook / ICS links for an event.
	 *
	 * @param array<string,mixed> $event Event row.
	 * @return array{google:string,outlook:string,ics:string}
	 */
	private function event_calendar_links( array $event ): array {
		$id = absint( $event['id'] ?? 0 );
		$title = html_entity_decode( (string) ( $event['title'] ?? '' ), ENT_QUOTES, 'UTF-8' );
		$description = html_entity_decode( wp_strip_all_tags( (string) ( $event['description'] ?? '' ) ), ENT_QUOTES, 'UTF-8' );
		$location = html_entity_decode( (string) ( $event['location'] ?? '' ), ENT_QUOTES, 'UTF-8' );
		$tz = wp_timezone();

		try {
			$start = new DateTimeImmutable( (string) ( $event['start_time'] ?? 'now' ), $tz );
		} catch ( Exception $e ) {
			$start = new DateTimeImmutable( 'now', $tz );
		}

		try {
			$end = ! empty( $event['end_time'] )
				? new DateTimeImmutable( (string) $event['end_time'], $tz )
				: $start->modify( '+1 hour' );
		} catch ( Exception $e ) {
			$end = $start->modify( '+1 hour' );
		}

		$start_utc = $start->setTimezone( new DateTimeZone( 'UTC' ) );
		$end_utc = $end->setTimezone( new DateTimeZone( 'UTC' ) );
		$google_dates = $start_utc->format( 'Ymd\THis\Z' ) . '/' . $end_utc->format( 'Ymd\THis\Z' );

		$google = add_query_arg(
			[
				'action'   => 'TEMPLATE',
				'text'     => $title,
				'dates'    => $google_dates,
				'details'  => $description,
				'location' => $location,
			],
			'https://calendar.google.com/calendar/render'
		);

		$outlook = add_query_arg(
			[
				'path'    => '/calendar/action/compose',
				'rru'     => 'addevent',
				'subject' => $title,
				'body'    => $description,
				'location'=> $location,
				'startdt' => $start->format( 'Y-m-d\TH:i:s' ),
				'enddt'   => $end->format( 'Y-m-d\TH:i:s' ),
			],
			'https://outlook.live.com/calendar/0/deeplink/compose'
		);

		return [
			'google'  => $google,
			'outlook' => $outlook,
			'ics'     => add_query_arg( 'masjidos_ical', $id, home_url( '/' ) ),
		];
	}

	/**
	 * Handle iCal export template redirect when 'masjidos_ical' URL parameter is present.
	 */
	public function handle_ical_export_redirect(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['masjidos_ical'] ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$id = absint( $_GET['masjidos_ical'] );
		$event = null;

		if ( $id >= 90000 ) {
			$current_year = (int) gmdate( 'Y' );
			$islamic = ITMMS_Events::get_islamic_events( $current_year );
			$islamic = array_merge( $islamic, ITMMS_Events::get_islamic_events( $current_year + 1 ) );
			$islamic = array_merge( $islamic, ITMMS_Events::get_islamic_events( $current_year - 1 ) );
			foreach ( $islamic as $ie ) {
				if ( (int) $ie['id'] === $id ) {
					$event = $ie;
					break;
				}
			}
		} else {
			$event = ITMMS_Events::find( $id );
		}

		if ( ! $event ) {
			wp_die( esc_html__( 'Event not found.', 'masjidos' ), esc_html__( 'Error', 'masjidos' ), [ 'response' => 404 ] );
		}

		$dt_start = new DateTime( $event['start_time'] );
		$dt_end = ! empty( $event['end_time'] ) ? new DateTime( $event['end_time'] ) : ( clone $dt_start )->modify( '+1 hour' );

		$dt_start_utc = $dt_start->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Ymd\THis\Z' );
		$dt_end_utc = $dt_end->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Ymd\THis\Z' );
		$dt_stamp = ( new DateTime( 'now', new DateTimeZone( 'UTC' ) ) )->format( 'Ymd\THis\Z' );

		$title = html_entity_decode( $event['title'], ENT_QUOTES, 'UTF-8' );
		$description = html_entity_decode( wp_strip_all_tags( $event['description'] ), ENT_QUOTES, 'UTF-8' );
		$location = html_entity_decode( $event['location'], ENT_QUOTES, 'UTF-8' );

		header( 'Content-Type: text/calendar; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="event-' . $id . '.ics"' );

		echo "BEGIN:VCALENDAR\r\n";
		echo "VERSION:2.0\r\n";
		echo "PRODID:-//MasjidOS//WordPress Plugin//EN\r\n";
		echo "CALSCALE:GREGORIAN\r\n";
		echo "BEGIN:VEVENT\r\n";
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Calendar files require unescaped plain text formatting.
		echo "UID:itmms-event-" . $id . "@" . wp_parse_url( home_url(), PHP_URL_HOST ) . "\r\n";
		echo "DTSTAMP:" . $dt_stamp . "\r\n";
		echo "DTSTART:" . $dt_start_utc . "\r\n";
		echo "DTEND:" . $dt_end_utc . "\r\n";
		echo "SUMMARY:" . str_replace( [ "\r", "\n" ], ' ', $title ) . "\r\n";
		echo "DESCRIPTION:" . str_replace( [ "\r", "\n" ], ' ', $description ) . "\r\n";
		echo "LOCATION:" . str_replace( [ "\r", "\n" ], ' ', $location ) . "\r\n";
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo "END:VEVENT\r\n";
		echo "END:VCALENDAR\r\n";
		exit;
	}

	/**
	 * Render public Jumuah Khutbah Archive widget.
	 */
	public function render_khutbah_archive_shortcode( $atts = [] ): string {
		$atts = is_array( $atts ) ? $atts : [];
		$has_custom_title = isset( $atts['title'] ) && '' !== trim( (string) $atts['title'] );
		$atts = shortcode_atts(
			[
				'title'    => __( 'Jumuah Khutbah Archive', 'masjidos' ),
				'language' => ITMMS_Settings::ui_language(),
				'limit'    => 12,
				'category' => '',
			],
			$atts,
			'masjidos_khutbah_archive'
		);

		$this->enqueue_assets();
		$language = $this->normalize_language( (string) $atts['language'] );
		if ( ! $has_custom_title ) {
			$atts['title'] = ( 'bn' === $language )
				? 'জুমার খুতবা আর্কাইভ'
				: ( ( 'ar' === $language ) ? 'أرشيف خطب الجمعة' : __( 'Jumuah Khutbah Archive', 'masjidos' ) );
		}
		$limit    = max( 1, min( 100, (int) $atts['limit'] ) );
		$category = sanitize_key( (string) $atts['category'] );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$search = isset( $_GET['itmms_khutbah_search'] ) ? sanitize_text_field( wp_unslash( $_GET['itmms_khutbah_search'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$date_filter = isset( $_GET['itmms_khutbah_date'] ) ? sanitize_text_field( wp_unslash( $_GET['itmms_khutbah_date'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['itmms_khutbah_category'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$category = sanitize_key( wp_unslash( (string) $_GET['itmms_khutbah_category'] ) );
		}

		$khutbahs = ITMMS_Khutbah::query( $limit, $search, $date_filter, $category, true );
		$categories = ITMMS_Khutbah::categories();

		ob_start();
		$template_path = ITMMS_PLUGIN_DIR . 'public/templates/khutbah-archive.php';
		if ( file_exists( $template_path ) ) {
			include $template_path;
		}
		$html = ob_get_clean();

		return $this->safe_kses( $html );
	}

	/**
	 * This week's scheduled khatib + topic.
	 *
	 * @param array<string,mixed>|string $atts Shortcode attributes.
	 */
	public function render_khatib_this_week_shortcode( $atts = [] ): string {
		$atts = is_array( $atts ) ? $atts : [];
		$has_custom_title = isset( $atts['title'] ) && '' !== trim( (string) $atts['title'] );
		$atts = shortcode_atts(
			[
				'title'    => __( 'This Week\'s Khatib', 'masjidos' ),
				'language' => ITMMS_Settings::ui_language(),
			],
			$atts,
			'masjidos_khatib_this_week'
		);

		$this->enqueue_assets();
		$language = $this->normalize_language( (string) $atts['language'] );
		if ( ! $has_custom_title ) {
			$atts['title'] = ( 'bn' === $language )
				? 'এই সপ্তাহের খতিব'
				: ( ( 'ar' === $language ) ? 'خطيب هذا الأسبوع' : __( 'This Week\'s Khatib', 'masjidos' ) );
		}
		$entry = ITMMS_Minbar::this_week();

		ob_start();
		$template_path = ITMMS_PLUGIN_DIR . 'public/templates/khatib-this-week.php';
		if ( file_exists( $template_path ) ) {
			include $template_path;
		}
		return $this->safe_kses( (string) ob_get_clean() );
	}

	/**
	 * Upcoming planned / scheduled khutbah topics.
	 *
	 * @param array<string,mixed>|string $atts Shortcode attributes.
	 */
	public function render_upcoming_khutbah_shortcode( $atts = [] ): string {
		$atts = is_array( $atts ) ? $atts : [];
		$has_custom_title = isset( $atts['title'] ) && '' !== trim( (string) $atts['title'] );
		$atts = shortcode_atts(
			[
				'title'    => __( 'Upcoming Khutbahs', 'masjidos' ),
				'language' => ITMMS_Settings::ui_language(),
				'limit'    => 5,
			],
			$atts,
			'masjidos_upcoming_khutbah'
		);

		$this->enqueue_assets();
		$language = $this->normalize_language( (string) $atts['language'] );
		if ( ! $has_custom_title ) {
			$atts['title'] = ( 'bn' === $language )
				? 'আসন্ন খুতবা'
				: ( ( 'ar' === $language ) ? 'الخطب القادمة' : __( 'Upcoming Khutbahs', 'masjidos' ) );
		}
		$limit = max( 1, min( 20, (int) $atts['limit'] ) );
		$items = ITMMS_Minbar::schedule_upcoming( $limit );
		if ( count( $items ) < $limit ) {
			$plans = ITMMS_Minbar::get_plans();
			$today = ( new DateTimeImmutable( 'now', wp_timezone() ) )->format( 'Y-m-d' );
			foreach ( $plans as $plan ) {
				if ( ( $plan['date'] ?? '' ) < $today ) {
					continue;
				}
				$items[] = [
					'scheduled_date' => (string) ( $plan['date'] ?? '' ),
					'topic'          => (string) ( $plan['topic'] ?? '' ),
					'khatib_name'    => '',
					'status'         => 'planned',
				];
				if ( count( $items ) >= $limit ) {
					break;
				}
			}
		}

		ob_start();
		$template_path = ITMMS_PLUGIN_DIR . 'public/templates/upcoming-khutbah.php';
		if ( file_exists( $template_path ) ) {
			include $template_path;
		}
		return $this->safe_kses( (string) ob_get_clean() );
	}

	/**
	 * Compact public search widget for the archive.
	 *
	 * @param array<string,mixed>|string $atts Shortcode attributes.
	 */
	public function render_khutbah_search_shortcode( $atts = [] ): string {
		$atts = is_array( $atts ) ? $atts : [];
		$has_custom_title = isset( $atts['title'] ) && '' !== trim( (string) $atts['title'] );
		$atts = shortcode_atts(
			[
				'title'    => __( 'Search Khutbah Archive', 'masjidos' ),
				'language' => ITMMS_Settings::ui_language(),
				'limit'    => 6,
			],
			$atts,
			'masjidos_khutbah_search'
		);

		$this->enqueue_assets();
		$language = $this->normalize_language( (string) $atts['language'] );
		if ( ! $has_custom_title ) {
			$atts['title'] = ( 'bn' === $language )
				? 'খুতবা আর্কাইভ খুঁজুন'
				: ( ( 'ar' === $language ) ? 'البحث في أرشيف الخطب' : __( 'Search Khutbah Archive', 'masjidos' ) );
		}
		$limit = max( 1, min( 50, (int) $atts['limit'] ) );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$search = isset( $_GET['itmms_khutbah_search'] ) ? sanitize_text_field( wp_unslash( $_GET['itmms_khutbah_search'] ) ) : '';
		$khutbahs = '' !== $search ? ITMMS_Khutbah::query( $limit, $search, '', '', true ) : [];

		ob_start();
		$template_path = ITMMS_PLUGIN_DIR . 'public/templates/khutbah-search.php';
		if ( file_exists( $template_path ) ) {
			include $template_path;
		}
		return $this->safe_kses( (string) ob_get_clean() );
	}

	/**
	 * Render public Quran Verse shortcode.
	 *
	 * @param array<string,mixed>|string $atts Shortcode attributes.
	 */
	public function render_quran_verse_shortcode( $atts = [] ): string {
		$atts = is_array( $atts ) ? $atts : [];
		$has_custom_title = isset( $atts['title'] ) && '' !== trim( (string) $atts['title'] );
		$atts = shortcode_atts(
			[
				'title'    => __( 'Quran Verse of the Day', 'masjidos' ),
				'language' => ITMMS_Settings::ui_language(),
				'design'   => 'classic',
				'share'    => 'yes',
				'tafsir'   => 'yes',
			],
			$atts,
			'masjidos_quran_verse'
		);

		$this->enqueue_assets();
		$language = $this->normalize_language( (string) $atts['language'] );
		if ( ! $has_custom_title ) {
			if ( 'bn' === $language ) {
				$atts['title'] = 'আজকের আয়াত';
			} elseif ( 'ar' === $language ) {
				$atts['title'] = 'آية اليوم';
			}
		}

		$design = sanitize_key( (string) $atts['design'] ) ?: 'classic';
		if ( ! in_array( $design, [ 'classic', 'compact' ], true ) ) {
			$design = 'classic';
		}
		$show_share = 'no' !== strtolower( (string) $atts['share'] );
		$show_tafsir = 'no' !== strtolower( (string) $atts['tafsir'] );
		$verse = ITMMS_Education::get_verse_of_day();

		if ( empty( $verse ) || empty( $verse['ar'] ) ) {
			$empty_title = __( 'No verse available', 'masjidos' );
			$empty_message = __( 'Check back later for today’s Quran verse.', 'masjidos' );
			if ( 'bn' === $language ) {
				$empty_title = 'কোনো আয়াত নেই';
				$empty_message = 'আজকের আয়াত পরে আবার দেখুন।';
			} elseif ( 'ar' === $language ) {
				$empty_title = 'لا توجد آية';
				$empty_message = 'عد لاحقاً لآية اليوم.';
			}
			return $this->render_announcement_empty_state( $empty_title, $empty_message );
		}

		ob_start();
		$template_path = ITMMS_PLUGIN_DIR . 'public/templates/quran-verse.php';
		if ( file_exists( $template_path ) ) {
			include $template_path;
		}
		return $this->safe_kses( (string) ob_get_clean() );
	}

	/**
	 * Render public Hadith shortcode.
	 *
	 * @param array<string,mixed>|string $atts Shortcode attributes.
	 */
	public function render_hadith_shortcode( $atts = [] ): string {
		$atts = is_array( $atts ) ? $atts : [];
		$has_custom_title = isset( $atts['title'] ) && '' !== trim( (string) $atts['title'] );
		$atts = shortcode_atts(
			[
				'title'    => __( 'Hadith of the Day', 'masjidos' ),
				'language' => ITMMS_Settings::ui_language(),
				'design'   => 'classic',
				'share'    => 'yes',
			],
			$atts,
			'masjidos_hadith'
		);

		$this->enqueue_assets();
		$language = $this->normalize_language( (string) $atts['language'] );
		if ( ! $has_custom_title ) {
			if ( 'bn' === $language ) {
				$atts['title'] = 'আজকের হাদিস';
			} elseif ( 'ar' === $language ) {
				$atts['title'] = 'حديث اليوم';
			}
		}

		$design = sanitize_key( (string) $atts['design'] ) ?: 'classic';
		if ( ! in_array( $design, [ 'classic', 'compact' ], true ) ) {
			$design = 'classic';
		}
		$show_share = 'no' !== strtolower( (string) $atts['share'] );
		$hadith = ITMMS_Education::get_hadith_of_day();

		if ( empty( $hadith ) || empty( $hadith['ar'] ) ) {
			$empty_title = __( 'No hadith available', 'masjidos' );
			$empty_message = __( 'Check back later for today’s hadith.', 'masjidos' );
			if ( 'bn' === $language ) {
				$empty_title = 'কোনো হাদিস নেই';
				$empty_message = 'আজকের হাদিস পরে আবার দেখুন।';
			} elseif ( 'ar' === $language ) {
				$empty_title = 'لا يوجد حديث';
				$empty_message = 'عد لاحقاً لحديث اليوم.';
			}
			return $this->render_announcement_empty_state( $empty_title, $empty_message );
		}

		ob_start();
		$template_path = ITMMS_PLUGIN_DIR . 'public/templates/hadith.php';
		if ( file_exists( $template_path ) ) {
			include $template_path;
		}
		return $this->safe_kses( (string) ob_get_clean() );
	}

	/**
	 * Render public Names of Allah shortcode.
	 *
	 * @param array<string,mixed>|string $atts Shortcode attributes.
	 */
	public function render_allah_names_shortcode( $atts = [] ): string {
		$atts = is_array( $atts ) ? $atts : [];
		$has_custom_title = isset( $atts['title'] ) && '' !== trim( (string) $atts['title'] );
		$atts = shortcode_atts(
			[
				'title'    => __( '99 Names of Allah', 'masjidos' ),
				'language' => ITMMS_Settings::ui_language(),
				'design'   => 'grid',
				'limit'    => '99',
			],
			$atts,
			'masjidos_allah_names'
		);

		$this->enqueue_assets();
		$language = $this->normalize_language( (string) $atts['language'] );
		if ( ! $has_custom_title ) {
			if ( 'bn' === $language ) {
				$atts['title'] = 'আল্লাহর ৯৯ নাম';
			} elseif ( 'ar' === $language ) {
				$atts['title'] = 'أسماء الله الحسنى';
			}
		}

		$design = sanitize_key( (string) $atts['design'] ) ?: 'grid';
		if ( ! in_array( $design, [ 'grid', 'compact' ], true ) ) {
			$design = 'grid';
		}
		$limit = max( 1, min( 99, absint( $atts['limit'] ) ?: 99 ) );
		$names = array_slice( ITMMS_Education::get_allah_names(), 0, $limit );

		if ( empty( $names ) ) {
			$empty_title = __( 'No names available', 'masjidos' );
			$empty_message = __( 'The 99 Names collection could not be loaded.', 'masjidos' );
			if ( 'bn' === $language ) {
				$empty_title = 'নাম পাওয়া যায়নি';
				$empty_message = '৯৯ নামের তালিকা লোড করা যায়নি।';
			}
			return $this->render_announcement_empty_state( $empty_title, $empty_message );
		}

		ob_start();
		$template_path = ITMMS_PLUGIN_DIR . 'public/templates/allah-names.php';
		if ( file_exists( $template_path ) ) {
			include $template_path;
		}
		return $this->safe_kses( (string) ob_get_clean() );
	}

	/**
	 * Render public Audio Quran Embed shortcode.
	 *
	 * @param array<string,mixed>|string $atts Shortcode attributes.
	 */
	public function render_audio_quran_shortcode( $atts = [] ): string {
		$atts = is_array( $atts ) ? $atts : [];
		$has_custom_title = isset( $atts['title'] ) && '' !== trim( (string) $atts['title'] );
		$atts = shortcode_atts(
			[
				'title'    => __( 'Audio Quran Player', 'masjidos' ),
				'language' => ITMMS_Settings::ui_language(),
				'design'   => 'classic',
			],
			$atts,
			'masjidos_audio_quran'
		);

		$this->enqueue_assets();
		$language = $this->normalize_language( (string) $atts['language'] );
		if ( ! $has_custom_title ) {
			if ( 'bn' === $language ) {
				$atts['title'] = 'অডিও কুরআন';
			} elseif ( 'ar' === $language ) {
				$atts['title'] = 'القرآن الصوتي';
			}
		}

		$design = sanitize_key( (string) $atts['design'] ) ?: 'classic';
		if ( ! in_array( $design, [ 'classic', 'compact' ], true ) ) {
			$design = 'classic';
		}
		$surahs = ITMMS_Education::get_surahs();

		if ( empty( $surahs ) ) {
			$empty_title = __( 'No surahs available', 'masjidos' );
			$empty_message = __( 'The Audio Quran list could not be loaded.', 'masjidos' );
			if ( 'bn' === $language ) {
				$empty_title = 'কোনো সূরা নেই';
				$empty_message = 'অডিও কুরআনের তালিকা লোড করা যায়নি।';
			}
			return $this->render_announcement_empty_state( $empty_title, $empty_message );
		}

		ob_start();
		$template_path = ITMMS_PLUGIN_DIR . 'public/templates/audio-quran.php';
		if ( file_exists( $template_path ) ) {
			include $template_path;
		}
		return $this->safe_kses( (string) ob_get_clean() );
	}

	/**
	 * Render Islamic Articles list shortcode.
	 *
	 * @param array<string,mixed>|string $atts Shortcode attributes.
	 */
	public function render_articles_shortcode( $atts = [] ): string {
		$atts = is_array( $atts ) ? $atts : [];
		$atts = shortcode_atts(
			[
				'title'    => __( 'Islamic Articles', 'masjidos' ),
				'language' => ITMMS_Settings::ui_language(),
				'category' => '',
				'limit'    => '6',
				'excerpt'  => 'yes',
				'design'   => 'grid',
			],
			$atts,
			'masjidos_articles'
		);

		$this->enqueue_assets();
		$language = $this->normalize_language( (string) $atts['language'] );
		$limit = max( 1, min( 24, absint( $atts['limit'] ) ?: 6 ) );
		$show_excerpt = 'no' !== strtolower( (string) $atts['excerpt'] );
		$category = sanitize_title( (string) $atts['category'] );
		$design = sanitize_key( (string) $atts['design'] ) ?: 'grid';
		if ( ! in_array( $design, [ 'grid', 'list' ], true ) ) {
			$design = 'grid';
		}
		$articles = ITMMS_Education::get_articles( $limit, $category );

		$labels = [
			'title'   => ( 'bn' === $language ) ? 'ইসলামিক আর্টিকেল' : ( ( 'ar' === $language ) ? 'مقالات إسلامية' : __( 'Islamic Articles', 'masjidos' ) ),
			'empty'   => ( 'bn' === $language ) ? 'এখনও কোনো আর্টিকেল নেই।' : ( ( 'ar' === $language ) ? 'لا توجد مقالات بعد.' : __( 'No articles published yet.', 'masjidos' ) ),
			'read'    => ( 'bn' === $language ) ? 'পড়ুন' : ( ( 'ar' === $language ) ? 'اقرأ' : __( 'Read', 'masjidos' ) ),
			'uncat'   => ( 'bn' === $language ) ? 'সাধারণ' : ( ( 'ar' === $language ) ? 'عام' : __( 'General', 'masjidos' ) ),
			'source'  => ( 'bn' === $language ) ? 'সূত্র' : ( ( 'ar' === $language ) ? 'المصدر' : __( 'Source', 'masjidos' ) ),
			'lang_en' => ( 'bn' === $language ) ? 'ইংরেজি' : ( ( 'ar' === $language ) ? 'الإنجليزية' : __( 'English', 'masjidos' ) ),
			'lang_bn' => ( 'bn' === $language ) ? 'বাংলা' : ( ( 'ar' === $language ) ? 'البنغالية' : __( 'Bangla', 'masjidos' ) ),
			'lang_ar' => ( 'bn' === $language ) ? 'আরবি' : ( ( 'ar' === $language ) ? 'العربية' : __( 'Arabic', 'masjidos' ) ),
		];
		if ( '' === trim( (string) $atts['title'] ) || __( 'Islamic Articles', 'masjidos' ) === (string) $atts['title'] ) {
			$atts['title'] = $labels['title'];
		}

		ob_start();
		$template_path = ITMMS_PLUGIN_DIR . 'public/templates/articles.php';
		if ( file_exists( $template_path ) ) {
			include $template_path;
		}
		return $this->safe_kses( (string) ob_get_clean() );
	}
}
