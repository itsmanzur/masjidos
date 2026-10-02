<?php
/**
 * Elementor Ask the Imam Widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;

/**
 * Class ITMMS_Elementor_Ask_Imam
 */
class ITMMS_Elementor_Ask_Imam extends Widget_Base {

	/**
	 * Get widget name.
	 */
	public function get_name(): string {
		return 'masjidos_ask_imam';
	}

	/**
	 * Get widget title.
	 */
	public function get_title(): string {
		return __( 'Ask the Imam (Q&A)', 'masjidos' );
	}

	/**
	 * Get widget icon.
	 */
	public function get_icon(): string {
		return 'eicon-comments';
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
		return [ 'ask', 'imam', 'qa', 'fatwa', 'question', 'answer', 'masjid', 'scholar' ];
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls(): void {
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Q&A Settings', 'masjidos' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'mode',
			[
				'label'   => __( 'Display Mode', 'masjidos' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'form',
				'options' => [
					'form'    => __( 'Question Submission Form', 'masjidos' ),
					'answers' => __( 'Answered Questions Library', 'masjidos' ),
				],
			]
		);

		$this->add_control(
			'title',
			[
				'label'       => __( 'Heading Title', 'masjidos' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => __( 'Leave empty for default title', 'masjidos' ),
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
			'limit',
			[
				'label'     => __( 'Number of Answers to Show', 'masjidos' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 50,
				'step'      => 1,
				'default'   => 10,
				'condition' => [
					'mode' => 'answers',
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
		$mode     = $settings['mode'] ?? 'form';

		$atts = [
			'title'    => $settings['title'] ?? '',
			'language' => $settings['language'] ?? 'en',
		];

		if ( 'answers' === $mode ) {
			$atts['limit'] = (string) ( $settings['limit'] ?? '10' );
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is escaped in ITMMS_Ask_Imam.
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by ITMMS_Ask_Imam with custom form/select tags.
			echo ITMMS_Ask_Imam::get_instance()->render_answers_shortcode( $atts );
		} else {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Output is escaped in ITMMS_Ask_Imam.
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by ITMMS_Ask_Imam with custom form/select tags.
			echo ITMMS_Ask_Imam::get_instance()->render_form_shortcode( $atts );
		}
	}
}
