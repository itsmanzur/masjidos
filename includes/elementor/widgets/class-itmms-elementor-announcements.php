<?php
/**
 * Elementor Announcements Widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Class ITMMS_Elementor_Announcements
 */
class ITMMS_Elementor_Announcements extends ITMMS_Elementor_Widget_Base {

	/**
	 * Get widget name.
	 */
	public function get_name(): string {
		return 'masjidos_announcements';
	}

	/**
	 * Get widget title.
	 */
	public function get_title(): string {
		return __( 'Masjid Notices & News', 'masjidos' );
	}

	/**
	 * Get widget icon.
	 */
	public function get_icon(): string {
		return 'eicon-bullet-list';
	}

	/**
	 * Get widget keywords.
	 */
	public function get_keywords(): array {
		return [ 'announcements', 'notices', 'news', 'ticker', 'board', 'masjid', 'mosque' ];
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls(): void {
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Notice Settings', 'masjidos' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->register_quick_manage_notice(
			__( 'Manage Notices in MasjidOS', 'masjidos' ),
			'admin.php?page=masjidos#announcements',
			'announcements'
		);

		$this->add_control(
			'title',
			[
				'label'       => __( 'Heading Title', 'masjidos' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Masjid Notices', 'masjidos' ),
				'placeholder' => __( 'Masjid Notices', 'masjidos' ),
			]
		);

		$design_options = [
			'list'   => __( 'List', 'masjidos' ),
			'cards'  => __( 'Cards', 'masjidos' ),
			'ticker' => __( 'Live Ticker (Marquee)', 'masjidos' ),
			'banner' => __( 'Slim Banner', 'masjidos' ),
			'popup'  => __( 'Popup Modal', 'masjidos' ),
		];
		if ( function_exists( 'masjidos_pro_is_active' ) && masjidos_pro_is_active() ) {
			$design_options['digital-board']  = __( 'Pro: Digital Board', 'masjidos' );
			$design_options['ramadan-banner'] = __( 'Pro: Ramadan Banner', 'masjidos' );
		}

		$this->add_control(
			'design',
			[
				'label'   => __( 'Design Layout', 'masjidos' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'list',
				'options' => $design_options,
			]
		);

		$this->add_control(
			'language',
			[
				'label'   => __( 'Language', 'masjidos' ),
				'type'    => Controls_Manager::SELECT,
				'default' => $this->get_default_language(),
				'options' => $this->get_language_options(),
			]
		);

		$this->add_control(
			'type',
			[
				'label'   => __( 'Filter by Notice Type', 'masjidos' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'all',
				'options' => [
					'all'     => __( 'All Types', 'masjidos' ),
					'general' => __( 'General', 'masjidos' ),
					'urgent'  => __( 'Urgent / Important', 'masjidos' ),
					'jumuah'  => __( 'Jumuah', 'masjidos' ),
				],
			]
		);

		$this->add_control(
			'limit',
			[
				'label'   => __( 'Number of Notices', 'masjidos' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 20,
				'step'    => 1,
				'default' => 5,
			]
		);

		$this->add_control(
			'show_date',
			[
				'label'        => __( 'Show Date Badge', 'masjidos' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'masjidos' ),
				'label_off'    => __( 'No', 'masjidos' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->end_controls_section();

		$this->register_common_style_controls( '.itmms-public-announcements', '.itmms-public-announcements__item' );
	}

	/**
	 * Render widget output.
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();

		$atts = [
			'title'     => $settings['title'] ?? '',
			'design'    => $settings['design'] ?? 'list',
			'language'  => $settings['language'] ?? $this->get_default_language(),
			'type'      => $settings['type'] ?? 'all',
			'limit'     => (string) ( $settings['limit'] ?? '5' ),
			'show_date' => ( 'yes' === ( $settings['show_date'] ?? '' ) ) ? 'yes' : 'no',
		];

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by ITMMS_Public::safe_kses() preserving select, form and button controls.
		echo ITMMS_Public::get_instance()->render_announcements_shortcode( $atts );
	}
}
