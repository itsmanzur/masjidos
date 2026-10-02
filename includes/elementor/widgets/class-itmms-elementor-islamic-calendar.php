<?php
/**
 * Elementor Islamic Calendar Widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;

/**
 * Class ITMMS_Elementor_Islamic_Calendar
 */
class ITMMS_Elementor_Islamic_Calendar extends Widget_Base {

	/**
	 * Get widget name.
	 */
	public function get_name(): string {
		return 'masjidos_islamic_calendar';
	}

	/**
	 * Get widget title.
	 */
	public function get_title(): string {
		return __( 'Islamic Dual Calendar', 'masjidos' );
	}

	/**
	 * Get widget icon.
	 */
	public function get_icon(): string {
		return 'eicon-calendar';
	}

	/**
	 * Get widget categories.
	 */
	public function get_categories(): array {
		return [ 'masjidos' ];
	}

	/**
	 * Get widget keywords.
	 */
	public function get_keywords(): array {
		return [ 'islamic', 'hijri', 'calendar', 'dates', 'ramadan', 'events', 'masjid' ];
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls(): void {
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Calendar Settings', 'masjidos' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'title',
			[
				'label'       => __( 'Heading Title', 'masjidos' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Islamic Calendar', 'masjidos' ),
				'placeholder' => __( 'Islamic Calendar', 'masjidos' ),
			]
		);

		$this->add_control(
			'language',
			[
				'label'   => __( 'Language', 'masjidos' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'en',
				'options' => [
					'en' => __( 'English', 'masjidos' ),
					'bn' => __( 'Bangla (বাংলা)', 'masjidos' ),
					'ar' => __( 'Arabic (العربية)', 'masjidos' ),
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output.
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();

		$atts = [
			'title'    => $settings['title'] ?? '',
			'language' => $settings['language'] ?? 'en',
		];

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by ITMMS_Public::safe_kses() preserving select, form and button controls.
		echo ITMMS_Public::get_instance()->render_islamic_calendar_shortcode( $atts );
	}
}
