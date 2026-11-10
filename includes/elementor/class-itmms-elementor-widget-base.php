<?php
/**
 * Base Elementor Widget for MasjidOS.
 *
 * Provides shared language options, quick-manage admin links, module status
 * notices, and unified Style Tab controls across all MasjidOS Elementor widgets.
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
 * Abstract Class ITMMS_Elementor_Widget_Base
 */
abstract class ITMMS_Elementor_Widget_Base extends Widget_Base {

	/**
	 * Get widget categories.
	 *
	 * @return array<int,string>
	 */
	public function get_categories(): array {
		return [ 'masjidos' ];
	}

	/**
	 * Get default UI language from MasjidOS settings.
	 */
	protected function get_default_language(): string {
		return class_exists( 'ITMMS_Settings' ) ? ITMMS_Settings::ui_language() : 'en';
	}

	/**
	 * Get standard language select options.
	 *
	 * @return array<string,string>
	 */
	protected function get_language_options(): array {
		return [
			'en' => __( 'English', 'masjidos' ),
			'bn' => __( 'Bangla (বাংলা)', 'masjidos' ),
			'ar' => __( 'Arabic (العربية)', 'masjidos' ),
		];
	}

	/**
	 * Check whether a MasjidOS module is currently enabled.
	 */
	protected function is_module_enabled( string $module_key ): bool {
		if ( ! class_exists( 'ITMMS_Settings' ) ) {
			return true;
		}
		$settings = ITMMS_Settings::get_all();
		if ( 'jumuah' === $module_key ) {
			return ! empty( $settings['jumuah']['enabled'] );
		}
		return ! isset( $settings['modules'][ $module_key ] ) || ! empty( $settings['modules'][ $module_key ] );
	}

	/**
	 * Register a quick-manage notice and module status helper inside the Content tab.
	 *
	 * @param string      $manage_label Button/link text to manage data in MasjidOS admin.
	 * @param string      $admin_path   Relative wp-admin path (e.g. 'admin.php?page=masjidos#announcements').
	 * @param string|null $module_key   Optional module key ('prayer_times', 'announcements', 'events', 'duas_azkar', 'jumuah').
	 */
	protected function register_quick_manage_notice( string $manage_label, string $admin_path, ?string $module_key = null ): void {
		$admin_url = admin_url( $admin_path );
		$enabled   = null === $module_key ? true : $this->is_module_enabled( $module_key );

		$html = '<div style="padding:10px 12px;border-radius:6px;background:#f0f7f5;border:1px solid #b7dfd5;font-size:12px;line-height:1.5;color:#154c3f;">';
		if ( ! $enabled ) {
			$features_url = admin_url( 'admin.php?page=masjidos#features' );
			$html        .= '<div style="margin-bottom:8px;padding:6px 8px;border-radius:4px;background:#fff4e5;border:1px solid #f5c26b;color:#7a4500;">'
				. '<strong>' . esc_html__( 'Module Disabled:', 'masjidos' ) . '</strong> '
				. esc_html__( 'This module is currently turned off in MasjidOS settings.', 'masjidos' )
				. ' <a href="' . esc_url( $features_url ) . '" target="_blank" rel="noopener noreferrer" style="color:#7a4500;text-decoration:underline;font-weight:600;">'
				. esc_html__( 'Enable Module →', 'masjidos' ) . '</a></div>';
		}
		$html .= '<div style="display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;">'
			. '<span>' . esc_html__( 'Need to edit module data?', 'masjidos' ) . '</span>'
			. '<a href="' . esc_url( $admin_url ) . '" target="_blank" rel="noopener noreferrer" style="color:#0f6b56;font-weight:600;text-decoration:underline;">'
			. esc_html( $manage_label ) . ' &rarr;</a>'
			. '</div></div>';

		$this->add_control(
			'itmms_quick_manage_notice',
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => $html,
				'content_classes' => 'itmms-elementor-manage-notice',
			]
		);
	}

	/**
	 * Register unified Style Tab controls for container, header/typography, and inner items.
	 *
	 * @param string $root_selector CSS selector relative to {{WRAPPER}} for the main widget wrapper.
	 * @param string $item_selector Optional CSS selector relative to {{WRAPPER}} for inner cards/rows.
	 */
	protected function register_common_style_controls(
		string $root_selector = '[class^="itmms-public-"]',
		string $item_selector = ''
	): void {
		$wrapper_root = '{{WRAPPER}} ' . $root_selector;

		$this->start_controls_section(
			'section_style_container',
			[
				'label' => __( 'Container & Card Style', 'masjidos' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'container_bg_color',
			[
				'label'     => __( 'Background Color', 'masjidos' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					$wrapper_root => 'background: {{VALUE}}; background-color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'container_padding',
			[
				'label'      => __( 'Padding', 'masjidos' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					$wrapper_root => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'container_border',
				'selector' => $wrapper_root,
			]
		);

		$this->add_control(
			'container_border_radius',
			[
				'label'      => __( 'Border Radius', 'masjidos' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em' ],
				'selectors'  => [
					$wrapper_root => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'container_box_shadow',
				'selector' => $wrapper_root,
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
			'eyebrow_color',
			[
				'label'     => __( 'Eyebrow / Subtitle Color', 'masjidos' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} [class*="__eyebrow"]' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'heading_color',
			[
				'label'     => __( 'Heading Color', 'masjidos' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					$wrapper_root . ' h2, ' . $wrapper_root . ' h3' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'heading_typography',
				'selector' => $wrapper_root . ' h2',
			]
		);

		$this->add_control(
			'text_color',
			[
				'label'     => __( 'Body Text Color', 'masjidos' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					$wrapper_root . ' p, ' . $wrapper_root . ' li, ' . $wrapper_root . ' td, ' . $wrapper_root . ' span' => 'color: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		if ( '' !== $item_selector ) {
			$wrapper_item = '{{WRAPPER}} ' . $item_selector;

			$this->start_controls_section(
				'section_style_items',
				[
					'label' => __( 'Inner Cards / Rows', 'masjidos' ),
					'tab'   => Controls_Manager::TAB_STYLE,
				]
			);

			$this->add_control(
				'item_bg_color',
				[
					'label'     => __( 'Item Background Color', 'masjidos' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [
						$wrapper_item => 'background: {{VALUE}}; background-color: {{VALUE}};',
					],
				]
			);

			$this->add_group_control(
				Group_Control_Border::get_type(),
				[
					'name'     => 'item_border',
					'selector' => $wrapper_item,
				]
			);

			$this->add_control(
				'item_border_radius',
				[
					'label'      => __( 'Item Border Radius', 'masjidos' ),
					'type'       => Controls_Manager::DIMENSIONS,
					'size_units' => [ 'px', '%', 'em' ],
					'selectors'  => [
						$wrapper_item => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					],
				]
			);

			$this->end_controls_section();
		}
	}
}
