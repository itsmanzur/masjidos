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

	use ITMMS_Public_Helpers;
	use ITMMS_Public_Designs;
	use ITMMS_Public_Blocks;
	use ITMMS_Public_Display;

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
			return $this->render_localized_empty_state(
				'Prayer Times is disabled',
				'Enable the Prayer Times module before using this shortcode.',
				$language
			);
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
			return $this->render_localized_empty_state(
				'No prayer times available',
				'Check Prayer Setup: timezone, coordinates, and calculation method.',
				$language
			);
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
			return $this->render_localized_empty_state(
				'Prayer Times is disabled',
				'Enable the Prayer Times module before using this shortcode.',
				$language
			);
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
			return $this->render_localized_empty_state(
				'No monthly timetable available',
				'Check Prayer Setup and try again.',
				$language
			);
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
			return $this->render_localized_empty_state(
				'No duas found',
				'Try another category or increase the limit.',
				$language
			);
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
			return $this->render_localized_empty_state(
				'No verse available',
				'Check back later for today’s Quran verse.',
				$language
			);
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
			return $this->render_localized_empty_state(
				'No hadith available',
				'Check back later for today’s hadith.',
				$language
			);
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
			return $this->render_localized_empty_state(
				'No names available',
				'The 99 Names collection could not be loaded.',
				$language
			);
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
			return $this->render_localized_empty_state(
				'No surahs available',
				'The Audio Quran list could not be loaded.',
				$language
			);
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
