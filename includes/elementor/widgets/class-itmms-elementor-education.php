<?php
/**
 * Elementor Islamic Education & Content Widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Class ITMMS_Elementor_Education
 */
class ITMMS_Elementor_Education extends ITMMS_Elementor_Widget_Base {

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
	 * Get widget keywords.
	 */
	public function get_keywords(): array {
		return [ 'quran', 'hadith', 'verse', 'allah', 'names', 'audio', 'articles', 'duas', 'azkar', 'islamic' ];
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

		$this->register_quick_manage_notice(
			__( 'Manage Islamic Articles', 'masjidos' ),
			'edit.php?post_type=itmms_article'
		);

		$this->add_control(
			'content_type',
			[
				'label'   => __( 'Select Content Module', 'masjidos' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'quran_verse',
				'options' => [
					'quran_verse' => __( 'Quran Verse of the Day', 'masjidos' ),
					'hadith'      => __( 'Hadith of the Day', 'masjidos' ),
					'allah_names' => __( '99 Names of Allah (Asma ul Husna)', 'masjidos' ),
					'audio_quran' => __( 'Audio Quran Player', 'masjidos' ),
					'articles'    => __( 'Islamic Articles Grid', 'masjidos' ),
					'duas_azkar'  => __( 'Duas & Daily Azkar', 'masjidos' ),
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
				'default' => $this->get_default_language(),
				'options' => $this->get_language_options(),
			]
		);

		// Design controls per content type.
		$this->add_control(
			'design_classic_compact',
			[
				'label'     => __( 'Design Layout', 'masjidos' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'classic',
				'options'   => [
					'classic' => __( 'Classic', 'masjidos' ),
					'compact' => __( 'Compact', 'masjidos' ),
				],
				'condition' => [
					'content_type' => [ 'quran_verse', 'hadith', 'audio_quran' ],
				],
			]
		);

		$this->add_control(
			'design_names',
			[
				'label'     => __( 'Design Layout', 'masjidos' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'grid',
				'options'   => [
					'grid'    => __( 'Grid', 'masjidos' ),
					'compact' => __( 'Compact', 'masjidos' ),
				],
				'condition' => [
					'content_type' => 'allah_names',
				],
			]
		);

		$this->add_control(
			'design_articles',
			[
				'label'     => __( 'Design Layout', 'masjidos' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'grid',
				'options'   => [
					'grid' => __( 'Grid', 'masjidos' ),
					'list' => __( 'List', 'masjidos' ),
				],
				'condition' => [
					'content_type' => 'articles',
				],
			]
		);

		$this->add_control(
			'design_duas',
			[
				'label'     => __( 'Design Layout', 'masjidos' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'cards',
				'options'   => [
					'cards'   => __( 'Cards', 'masjidos' ),
					'compact' => __( 'Compact', 'masjidos' ),
				],
				'condition' => [
					'content_type' => 'duas_azkar',
				],
			]
		);

		// Category filter for Articles.
		$article_categories = [ '' => __( 'All Categories', 'masjidos' ) ];
		if ( class_exists( 'ITMMS_Education' ) && taxonomy_exists( ITMMS_Education::TAXONOMY ) ) {
			$terms = get_terms(
				[
					'taxonomy'   => ITMMS_Education::TAXONOMY,
					'hide_empty' => false,
				]
			);
			if ( is_array( $terms ) ) {
				foreach ( $terms as $term ) {
					if ( $term instanceof WP_Term ) {
						$article_categories[ $term->slug ] = $term->name;
					}
				}
			}
		}

		$this->add_control(
			'article_category',
			[
				'label'     => __( 'Article Category', 'masjidos' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => $article_categories,
				'condition' => [
					'content_type' => 'articles',
				],
			]
		);

		// Category filter for Duas & Azkar.
		$this->add_control(
			'dua_category',
			[
				'label'     => __( 'Dua & Azkar Category', 'masjidos' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'all',
				'options'   => [
					'all'         => __( 'All Categories', 'masjidos' ),
					'daily'       => __( 'Daily', 'masjidos' ),
					'morning'     => __( 'Morning', 'masjidos' ),
					'evening'     => __( 'Evening', 'masjidos' ),
					'food'        => __( 'Food & Drink', 'masjidos' ),
					'sleep'       => __( 'Sleep & Waking', 'masjidos' ),
					'home'        => __( 'Home', 'masjidos' ),
					'masjid'      => __( 'Masjid & Prayer', 'masjidos' ),
					'travel'      => __( 'Travel', 'masjidos' ),
					'rain'        => __( 'Rain & Weather', 'masjidos' ),
					'forgiveness' => __( 'Forgiveness', 'masjidos' ),
					'quran'       => __( 'Quranic Duas', 'masjidos' ),
					'protection'  => __( 'Protection', 'masjidos' ),
				],
				'condition' => [
					'content_type' => 'duas_azkar',
				],
			]
		);

		$this->add_control(
			'limit',
			[
				'label'     => __( 'Number of Items', 'masjidos' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 99,
				'step'      => 1,
				'default'   => 6,
				'condition' => [
					'content_type' => [ 'articles', 'duas_azkar', 'allah_names' ],
				],
			]
		);

		$this->add_control(
			'tafsir',
			[
				'label'        => __( 'Show Tafsir Link', 'masjidos' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'masjidos' ),
				'label_off'    => __( 'No', 'masjidos' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => [
					'content_type' => 'quran_verse',
				],
			]
		);

		$this->add_control(
			'share',
			[
				'label'        => __( 'Show Share Button', 'masjidos' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'masjidos' ),
				'label_off'    => __( 'No', 'masjidos' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => [
					'content_type' => [ 'quran_verse', 'hadith', 'duas_azkar' ],
				],
			]
		);

		$this->add_control(
			'excerpt',
			[
				'label'        => __( 'Show Article Excerpt', 'masjidos' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'masjidos' ),
				'label_off'    => __( 'No', 'masjidos' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => [
					'content_type' => 'articles',
				],
			]
		);

		$this->add_control(
			'source',
			[
				'label'        => __( 'Show Reference Source', 'masjidos' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'masjidos' ),
				'label_off'    => __( 'No', 'masjidos' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => [
					'content_type' => 'duas_azkar',
				],
			]
		);

		$this->add_control(
			'counter',
			[
				'label'        => __( 'Show Tasbih Counter', 'masjidos' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'masjidos' ),
				'label_off'    => __( 'No', 'masjidos' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => [
					'content_type' => 'duas_azkar',
				],
			]
		);

		$this->add_control(
			'audio',
			[
				'label'        => __( 'Show Audio Player', 'masjidos' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'masjidos' ),
				'label_off'    => __( 'No', 'masjidos' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'condition'    => [
					'content_type' => 'duas_azkar',
				],
			]
		);

		$this->end_controls_section();

		$this->register_common_style_controls();
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
			'language' => $settings['language'] ?? $this->get_default_language(),
			'limit'    => (string) ( $settings['limit'] ?? '6' ),
		];

		switch ( $content_type ) {
			case 'hadith':
				$atts['design'] = $settings['design_classic_compact'] ?? 'classic';
				$atts['share']  = ( 'yes' === ( $settings['share'] ?? 'yes' ) ) ? 'yes' : 'no';
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by ITMMS_Public::safe_kses().
				echo $public->render_hadith_shortcode( $atts );
				break;

			case 'allah_names':
				$atts['design'] = $settings['design_names'] ?? 'grid';
				$atts['limit']  = (string) ( $settings['limit'] ?? '99' );
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by ITMMS_Public::safe_kses().
				echo $public->render_allah_names_shortcode( $atts );
				break;

			case 'audio_quran':
				$atts['design'] = $settings['design_classic_compact'] ?? 'classic';
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by ITMMS_Public::safe_kses().
				echo $public->render_audio_quran_shortcode( $atts );
				break;

			case 'articles':
				$atts['design']   = $settings['design_articles'] ?? 'grid';
				$atts['category'] = (string) ( $settings['article_category'] ?? '' );
				$atts['excerpt']  = ( 'yes' === ( $settings['excerpt'] ?? 'yes' ) ) ? 'yes' : 'no';
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by ITMMS_Public::safe_kses().
				echo $public->render_articles_shortcode( $atts );
				break;

			case 'duas_azkar':
				$atts['design']   = $settings['design_duas'] ?? 'cards';
				$atts['category'] = (string) ( $settings['dua_category'] ?? 'all' );
				$atts['source']   = ( 'yes' === ( $settings['source'] ?? 'yes' ) ) ? 'yes' : 'no';
				$atts['counter']  = ( 'yes' === ( $settings['counter'] ?? 'yes' ) ) ? 'yes' : 'no';
				$atts['share']    = ( 'yes' === ( $settings['share'] ?? 'yes' ) ) ? 'yes' : 'no';
				$atts['audio']    = ( 'yes' === ( $settings['audio'] ?? 'yes' ) ) ? 'yes' : 'no';
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by ITMMS_Public::safe_kses().
				echo $public->render_duas_azkar_shortcode( $atts );
				break;

			case 'quran_verse':
			default:
				$atts['design'] = $settings['design_classic_compact'] ?? 'classic';
				$atts['tafsir'] = ( 'yes' === ( $settings['tafsir'] ?? 'yes' ) ) ? 'yes' : 'no';
				$atts['share']  = ( 'yes' === ( $settings['share'] ?? 'yes' ) ) ? 'yes' : 'no';
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by ITMMS_Public::safe_kses().
				echo $public->render_quran_verse_shortcode( $atts );
				break;
		}
	}
}
