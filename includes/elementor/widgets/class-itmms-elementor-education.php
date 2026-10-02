<?php
/**
 * Elementor Islamic Education & Content Widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;

/**
 * Class ITMMS_Elementor_Education
 */
class ITMMS_Elementor_Education extends Widget_Base {

	/**
	 * Get widget name.
	 */
	public function get_name(): string {
		return 'masjidos_education';
	}

	/**
	 * Get widget title.
	 */
	public function get_title(): string {
		return __( 'Islamic Learning & Content', 'masjidos' );
	}

	/**
	 * Get widget icon.
	 */
	public function get_icon(): string {
		return 'eicon-document-file';
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
		return [ 'quran', 'hadith', 'verse', 'allah', 'names', 'audio', 'articles', 'islamic' ];
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls(): void {
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Content Settings', 'masjidos' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'content_type',
			[
				'label'   => __( 'Select Content Module', 'masjidos' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'quran_verse',
				'options' => [
					'quran_verse'  => __( 'Quran Verse of the Day', 'masjidos' ),
					'hadith'       => __( 'Hadith of the Day', 'masjidos' ),
					'allah_names'  => __( '99 Names of Allah (Asma ul Husna)', 'masjidos' ),
					'audio_quran'  => __( 'Audio Quran Player', 'masjidos' ),
					'articles'     => __( 'Islamic Articles Grid', 'masjidos' ),
					'duas_azkar'   => __( 'Duas & Daily Azkar', 'masjidos' ),
				],
			]
		);

		$this->add_control(
			'title',
			[
				'label'       => __( 'Custom Heading Title', 'masjidos' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => __( 'Leave empty for default module title', 'masjidos' ),
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
				'label'     => __( 'Number of Items (for Articles / Duas)', 'masjidos' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 30,
				'step'      => 1,
				'default'   => 6,
				'condition' => [
					'content_type' => [ 'articles', 'duas_azkar' ],
				],
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output.
	 */
	protected function render(): void {
		$settings     = $this->get_settings_for_display();
		$content_type = $settings['content_type'] ?? 'quran_verse';
		$public       = ITMMS_Public::get_instance();

		$atts = [
			'title'    => $settings['title'] ?? '',
			'language' => $settings['language'] ?? 'en',
			'limit'    => (string) ( $settings['limit'] ?? '6' ),
		];

		switch ( $content_type ) {
			case 'hadith':
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by ITMMS_Public::safe_kses().
				echo $public->render_hadith_shortcode( $atts );
				break;
			case 'allah_names':
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by ITMMS_Public::safe_kses().
				echo $public->render_allah_names_shortcode( $atts );
				break;
			case 'audio_quran':
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by ITMMS_Public::safe_kses().
				echo $public->render_audio_quran_shortcode( $atts );
				break;
			case 'articles':
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by ITMMS_Public::safe_kses().
				echo $public->render_articles_shortcode( $atts );
				break;
			case 'duas_azkar':
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by ITMMS_Public::safe_kses().
				echo $public->render_duas_azkar_shortcode( $atts );
				break;
			case 'quran_verse':
			default:
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by ITMMS_Public::safe_kses().
				echo $public->render_quran_verse_shortcode( $atts );
				break;
		}
	}
}
