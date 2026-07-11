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
				'language' => 'en',
				'qibla' => 'yes',
				'meta'  => 'yes',
				'compact' => 'no',
				'iqamah' => 'yes',
			],
			$atts,
			'masjidos_prayer_times'
		);

		$this->enqueue_assets();

		$design = $this->normalize_design( (string) $atts['design'], 'yes' === strtolower( (string) $atts['compact'] ) );
		$designs = $this->get_designs();
		$language = $this->normalize_language( (string) $atts['language'] );
		$labels = $this->labels( $language );
		if ( ! $has_custom_title ) {
			$atts['title'] = $labels['title'];
		}

		$data = ITMMS_Prayer_Times::today();
		$meta = $data['meta'];
		$next = $data['next_prayer'];
		$next_name = isset( $next['key'] ) ? $this->prayer_label( (string) $next['key'], $language, (string) ( $next['name'] ?? '' ) ) : (string) ( $next['name'] ?? '' );
		$date_label = date_i18n( get_option( 'date_format' ), strtotime( (string) ( $data['date'] ?? 'now' ) ) );
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

		if ( empty( $designs[ $design ] ) || 'free' !== ( $designs[ $design ]['tier'] ?? 'free' ) ) {
			$rendered = apply_filters( 'masjidos_render_prayer_widget_design', '', $design, $data, $atts, $designs );
			if ( is_string( $rendered ) && '' !== $rendered ) {
				return wp_kses_post( $rendered );
			}

			return $this->render_locked_design_notice( $design, $designs[ $design ] ?? null );
		}

		ob_start();
		$template_path = ITMMS_PLUGIN_DIR . 'public/templates/prayer-times.php';
		if ( file_exists( $template_path ) ) {
			include $template_path;
		}
		return wp_kses_post( (string) ob_get_clean() );
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
				'language' => 'en',
				'iqamah'   => 'no',
				'design'   => 'table',
				'navigation' => 'yes',
			],
			$atts,
			'masjidos_monthly_prayer_times'
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
		$labels = $this->monthly_labels( $language );
		if ( ! $has_custom_title ) {
			$atts['title'] = $labels['title'];
		}
		$design = sanitize_key( (string) $atts['design'] ) ?: 'table';
		$designs = $this->get_monthly_designs();

		$data = ITMMS_Prayer_Times::for_month( $year, $month, $settings );
		$show_iqamah = 'yes' === strtolower( (string) $atts['iqamah'] );
		$show_navigation = 'no' !== strtolower( (string) $atts['navigation'] );
		$meta = $data['meta'] ?? [];
		$prayer_keys = [ 'fajr', 'sunrise', 'dhuhr', 'asr', 'maghrib', 'isha' ];
		$today = $now->format( 'Y-m-d' );

		if ( empty( $designs[ $design ] ) || 'free' !== ( $designs[ $design ]['tier'] ?? 'free' ) ) {
			$rendered = apply_filters( 'masjidos_render_monthly_prayer_widget_design', '', $design, $data, $atts, $designs );
			if ( is_string( $rendered ) && '' !== $rendered ) {
				return wp_kses_post( $rendered );
			}

			return $this->render_locked_monthly_design_notice( $design, $designs[ $design ] ?? null );
		}

		ob_start();
		$template_path = ITMMS_PLUGIN_DIR . 'public/templates/monthly-prayer-times.php';
		if ( file_exists( $template_path ) ) {
			include $template_path;
		}
		return wp_kses_post( (string) ob_get_clean() );
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
				'language'  => 'en',
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
				return wp_kses_post( $rendered );
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
		return wp_kses_post( (string) ob_get_clean() );
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
				'language'  => 'en',
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
			$atts['title'] = ( 'bn' === $language ) ? 'আসন্ন ইভেন্টসমূহ' : __( 'Upcoming Events', 'masjidos' );
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
		return wp_kses_post( (string) ob_get_clean() );
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
				'language' => 'en',
				'meta'     => 'yes',
			],
			$atts,
			'masjidos_jumuah'
		);

		$this->enqueue_assets();

		$settings = ITMMS_Settings::get_all();
		$jumuah = isset( $settings['jumuah'] ) && is_array( $settings['jumuah'] ) ? $settings['jumuah'] : [];
		if ( empty( $jumuah['enabled'] ) ) {
			return '';
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
				return wp_kses_post( $rendered );
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
		return wp_kses_post( (string) ob_get_clean() );
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
			],
		];

		return $labels[ $language ] ?? $labels['en'];
	}

	private function prayer_label( string $key, string $language, string $fallback ): string {
		$labels = [
			'en' => [
				'fajr'    => 'Fajr',
				'sunrise' => 'Sunrise',
				'dhuhr'   => 'Dhuhr',
				'asr'     => 'Asr',
				'maghrib' => 'Maghrib',
				'isha'    => 'Isha',
			],
			'bn' => [
				'fajr'    => 'ফজর',
				'sunrise' => 'সূর্যোদয়',
				'dhuhr'   => 'যোহর',
				'asr'     => 'আসর',
				'maghrib' => 'মাগরিব',
				'isha'    => 'এশা',
			],
			'ar' => [
				'fajr'    => 'الفجر',
				'sunrise' => 'الشروق',
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
				'iqamah' => __( 'Iqamah', 'masjidos' ),
				'navigation' => __( 'Timetable month navigation', 'masjidos' ),
				'previous' => __( 'Previous month', 'masjidos' ),
				'next' => __( 'Next month', 'masjidos' ),
				'month' => __( 'Month', 'masjidos' ),
				'year' => __( 'Year', 'masjidos' ),
				'error' => __( 'The timetable could not be loaded. Please try again.', 'masjidos' ),
				'current_month' => __( 'Current Month', 'masjidos' ),
				'print' => __( 'Print', 'masjidos' ),
			],
			'bn' => [
				'title'  => 'মাসিক নামাজের সময়সূচি',
				'date'   => 'তারিখ',
				'iqamah' => 'জামাত',
				'navigation' => 'সময়সূচির মাস নির্বাচন',
				'previous' => 'আগের মাস',
				'next' => 'পরের মাস',
				'month' => 'মাস',
				'year' => 'বছর',
				'error' => 'সময়সূচি লোড করা যায়নি। আবার চেষ্টা করুন।',
				'current_month' => 'বর্তমান মাস',
				'print' => 'প্রিন্ট',
			],
			'ar' => [
				'title'  => 'جدول الصلاة الشهري',
				'date'   => 'التاريخ',
				'iqamah' => 'الإقامة',
				'navigation' => 'التنقل بين أشهر الجدول',
				'previous' => 'الشهر السابق',
				'next' => 'الشهر التالي',
				'month' => 'الشهر',
				'year' => 'السنة',
				'error' => 'تعذر تحميل الجدول. يرجى المحاولة مرة أخرى.',
				'current_month' => 'الشهر الحالي',
				'print' => 'طباعة',
			],
		];

		return $labels[ $language ] ?? $labels['en'];
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

	private function format_time( string $time, string $timezone ): string {
		if ( ! preg_match( '/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time ) ) {
			return '';
		}

		try {
			$date = new DateTimeImmutable( 'today ' . $time, new DateTimeZone( $timezone ) );
			return $date->format( 'g:i A' );
		} catch ( Exception $e ) {
			return $time;
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
		$label = $definition['label'] ?? ucwords( str_replace( '-', ' ', $design ) );

		return '<div class="itmms-public-prayer-lock">' .
			'<strong>' . esc_html( $label ) . '</strong>' .
			'<p>' . esc_html__( 'This prayer widget design is available in MasjidOS Pro.', 'masjidos' ) . '</p>' .
			'<code>[masjidos_prayer_times design="' . esc_html( $design ) . '"]</code>' .
		'</div>';
	}

	/**
	 * @param array<string,string>|null $definition Design definition.
	 */
	private function render_locked_jumuah_design_notice( string $design, ?array $definition ): string {
		$label = $definition['label'] ?? ucwords( str_replace( '-', ' ', $design ) );

		return '<div class="itmms-public-prayer-lock">' .
			'<strong>' . esc_html( $label ) . '</strong>' .
			'<p>' . esc_html__( 'This Jumuah widget design is available in MasjidOS Pro.', 'masjidos' ) . '</p>' .
			'<code>[masjidos_jumuah design="' . esc_html( $design ) . '"]</code>' .
		'</div>';
	}

	/**
	 * @param array<string,string>|null $definition Design definition.
	 */
	private function render_locked_monthly_design_notice( string $design, ?array $definition ): string {
		$label = $definition['label'] ?? ucwords( str_replace( '-', ' ', $design ) );

		return '<div class="itmms-public-prayer-lock">' .
			'<strong>' . esc_html( $label ) . '</strong>' .
			'<p>' . esc_html__( 'This monthly timetable design is available in MasjidOS Pro.', 'masjidos' ) . '</p>' .
			'<code>[masjidos_monthly_prayer_times design="' . esc_html( $design ) . '"]</code>' .
		'</div>';
	}

	/**
	 * @param array<string,string>|null $definition Design definition.
	 */
	private function render_locked_announcement_design_notice( string $design, ?array $definition ): string {
		$label = $definition['label'] ?? ucwords( str_replace( '-', ' ', $design ) );

		return '<div class="itmms-public-prayer-lock">' .
			'<strong>' . esc_html( $label ) . '</strong>' .
			'<p>' . esc_html__( 'This announcement widget design is available in MasjidOS Pro.', 'masjidos' ) . '</p>' .
			'<code>[masjidos_announcements design="' . esc_html( $design ) . '"]</code>' .
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
			'itmms-public',
			ITMMS_PLUGIN_URL . 'public/assets/css/public.css',
			[],
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
}
