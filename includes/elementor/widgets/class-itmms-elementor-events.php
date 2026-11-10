<?php
/**
 * Elementor Events Widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Class ITMMS_Elementor_Events
 */
class ITMMS_Elementor_Events extends ITMMS_Elementor_Widget_Base {

	/**
	 * Get widget name.
	 */
	public function get_name(): string {
		return 'masjidos_events';
	}

	/**
	 * Get widget title.
	 */
	public function get_title(): string {
		return __( 'Upcoming Events', 'masjidos' );
	}

	/**
	 * Get widget icon.
	 */
	public function get_icon(): string {
		return 'eicon-calendar';
	}

	/**
	 * Get widget keywords.
	 */
	public function get_keywords(): array {
		return [ 'events', 'upcoming', 'programs', 'halaqah', 'lectures', 'masjid', 'mosque' ];
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls(): void {
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Events Settings', 'masjidos' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->register_quick_manage_notice(
			__( 'Manage Events in MasjidOS', 'masjidos' ),
			'admin.php?page=masjidos#events',
			'events'
		);

		$this->add_control(
			'title',
			[
				'label'       => __( 'Heading Title', 'masjidos' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Upcoming Events', 'masjidos' ),
				'placeholder' => __( 'Upcoming Events', 'masjidos' ),
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
			'limit',
			[
				'label'   => __( 'Number of Events', 'masjidos' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 20,
				'step'    => 1,
				'default' => 5,
			]
		);

		$this->end_controls_section();

		$this->register_common_style_controls( '.itmms-public-events', '.itmms-public-events__item' );
	}

	/**
	 * Render widget output.
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();

		$atts = [
			'title'    => $settings['title'] ?? '',
			'language' => $settings['language'] ?? $this->get_default_language(),
			'limit'    => (string) ( $settings['limit'] ?? '5' ),
		];

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by ITMMS_Public::safe_kses().
		echo ITMMS_Public::get_instance()->render_events_shortcode( $atts );
	}
}
