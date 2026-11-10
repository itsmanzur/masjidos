<?php
/**
 * Elementor Islamic Calendar Widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Class ITMMS_Elementor_Islamic_Calendar
 */
class ITMMS_Elementor_Islamic_Calendar extends ITMMS_Elementor_Widget_Base {

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

		$this->register_quick_manage_notice(
			__( 'Configure Hijri Adjustment & Events', 'masjidos' ),
			'admin.php?page=masjidos#prayer-times'
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
				'default' => $this->get_default_language(),
				'options' => $this->get_language_options(),
			]
		);

		$this->add_control(
			'navigation',
			[
				'label'        => __( 'Show Month/Year Navigation', 'masjidos' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'masjidos' ),
				'label_off'    => __( 'No', 'masjidos' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'month',
			[
				'label'   => __( 'Initial Month', 'masjidos' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => [
					''   => __( 'Current Month (Auto)', 'masjidos' ),
					'1'  => __( 'January', 'masjidos' ),
					'2'  => __( 'February', 'masjidos' ),
					'3'  => __( 'March', 'masjidos' ),
					'4'  => __( 'April', 'masjidos' ),
					'5'  => __( 'May', 'masjidos' ),
					'6'  => __( 'June', 'masjidos' ),
					'7'  => __( 'July', 'masjidos' ),
					'8'  => __( 'August', 'masjidos' ),
					'9'  => __( 'September', 'masjidos' ),
					'10' => __( 'October', 'masjidos' ),
					'11' => __( 'November', 'masjidos' ),
					'12' => __( 'December', 'masjidos' ),
				],
			]
		);

		$this->add_control(
			'year',
			[
				'label'       => __( 'Initial Year (Optional)', 'masjidos' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => __( 'Current Year (Auto)', 'masjidos' ),
			]
		);

		$this->end_controls_section();

		$this->register_common_style_controls( '.itmms-public-calendar', '.itmms-public-calendar__cell' );
	}

	/**
	 * Render widget output.
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();

		$atts = [
			'title'      => $settings['title'] ?? '',
			'language'   => $settings['language'] ?? $this->get_default_language(),
			'navigation' => ( 'yes' === ( $settings['navigation'] ?? 'yes' ) ) ? 'yes' : 'no',
			'month'      => (string) ( $settings['month'] ?? '' ),
			'year'       => (string) ( $settings['year'] ?? '' ),
		];

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by ITMMS_Public::safe_kses() preserving select, form and button controls.
		echo ITMMS_Public::get_instance()->render_islamic_calendar_shortcode( $atts );
	}
}
