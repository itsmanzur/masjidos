<?php
/**
 * Elementor Prayer Times Widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

/**
 * Class ITMMS_Elementor_Prayer_Times
 */
class ITMMS_Elementor_Prayer_Times extends Widget_Base {

	/**
	 * Get widget name.
	 */
	public function get_name(): string {
		return 'masjidos_prayer_times';
	}

	/**
	 * Get widget title.
	 */
	public function get_title(): string {
		return __( 'Prayer Times', 'masjidos' );
	}

	/**
	 * Get widget icon.
	 */
	public function get_icon(): string {
		return 'eicon-clock-o';
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
		return [ 'prayer', 'salah', 'namaz', 'iqamah', 'times', 'azan', 'masjid', 'mosque' ];
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls(): void {
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Prayer Settings', 'masjidos' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'title',
			[
				'label'       => __( 'Heading Title', 'masjidos' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Today’s Prayer Times', 'masjidos' ),
				'placeholder' => __( 'Today’s Prayer Times', 'masjidos' ),
			]
		);

		$this->add_control(
			'design',
			[
				'label'   => __( 'Design Layout', 'masjidos' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'classic',
				'options' => [
					'classic' => __( 'Classic (Cards Grid)', 'masjidos' ),
					'compact' => __( 'Compact (List)', 'masjidos' ),
				],
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

		$this->add_control(
			'iqamah',
			[
				'label'        => __( 'Show Iqamah Times', 'masjidos' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'masjidos' ),
				'label_off'    => __( 'No', 'masjidos' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'qibla',
			[
				'label'        => __( 'Show Qibla Direction', 'masjidos' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'masjidos' ),
				'label_off'    => __( 'No', 'masjidos' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'hijri',
			[
				'label'        => __( 'Show Hijri Date', 'masjidos' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'masjidos' ),
				'label_off'    => __( 'No', 'masjidos' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'meta',
			[
				'label'        => __( 'Show Sunrise / Next Prayer Info', 'masjidos' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'masjidos' ),
				'label_off'    => __( 'No', 'masjidos' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->end_controls_section();

		// Style Section.
		$this->start_controls_section(
			'section_style_card',
			[
				'label' => __( 'Card & Container Style', 'masjidos' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'card_bg_color',
			[
				'label'     => __( 'Background Color', 'masjidos' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .itmms-prayer-widget' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'card_border',
				'selector' => '{{WRAPPER}} .itmms-prayer-widget',
			]
		);

		$this->add_control(
			'card_border_radius',
			[
				'label'      => __( 'Border Radius', 'masjidos' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					'{{WRAPPER}} .itmms-prayer-widget' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'card_box_shadow',
				'selector' => '{{WRAPPER}} .itmms-prayer-widget',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_typography',
			[
				'label' => __( 'Typography & Colors', 'masjidos' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'heading_color',
			[
				'label'     => __( 'Heading Color', 'masjidos' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .itmms-prayer-widget__heading' => 'color: {{VALUE}};',
					'{{WRAPPER}} .itmms-prayer-widget h3'       => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'heading_typography',
				'selector' => '{{WRAPPER}} .itmms-prayer-widget__heading, {{WRAPPER}} .itmms-prayer-widget h3',
			]
		);

		$this->add_control(
			'prayer_name_color',
			[
				'label'     => __( 'Prayer Names Color', 'masjidos' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .itmms-prayer-card__name' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'prayer_time_color',
			[
				'label'     => __( 'Prayer Time Digits Color', 'masjidos' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .itmms-prayer-card__time' => 'color: {{VALUE}};',
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
			'design'   => $settings['design'] ?? 'classic',
			'language' => $settings['language'] ?? 'en',
			'iqamah'   => ( 'yes' === ( $settings['iqamah'] ?? '' ) ) ? 'yes' : 'no',
			'qibla'    => ( 'yes' === ( $settings['qibla'] ?? '' ) ) ? 'yes' : 'no',
			'hijri'    => ( 'yes' === ( $settings['hijri'] ?? '' ) ) ? 'yes' : 'no',
			'meta'     => ( 'yes' === ( $settings['meta'] ?? '' ) ) ? 'yes' : 'no',
		];

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by ITMMS_Public::safe_kses() preserving select, form and button controls.
		echo ITMMS_Public::get_instance()->render_prayer_times_shortcode( $atts );
	}
}
