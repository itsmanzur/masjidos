<?php
/**
 * Elementor Custom Duas widget (duas from MasjidOS > Duas Library).
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Class ITMMS_Elementor_Custom_Duas
 */
class ITMMS_Elementor_Custom_Duas extends ITMMS_Elementor_Widget_Base {

	/**
	 * Get widget name.
	 */
	public function get_name(): string {
		return 'masjidos_custom_duas';
	}

	/**
	 * Get widget title.
	 */
	public function get_title(): string {
		return __( 'MasjidOS Custom Duas', 'masjidos' );
	}

	/**
	 * Get widget icon.
	 */
	public function get_icon(): string {
		return 'eicon-text-area';
	}

	/**
	 * Get widget keywords.
	 */
	public function get_keywords(): array {
		return [ 'dua', 'duas', 'azkar', 'library', 'custom', 'masjid', 'mosque' ];
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
			__( 'Manage Duas Library', 'masjidos' ),
			'edit.php?post_type=itmms_dua',
			'duas_azkar'
		);

		$this->add_control(
			'title',
			[
				'label'       => __( 'Heading Title', 'masjidos' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Duas & Azkar', 'masjidos' ),
				'placeholder' => __( 'Duas & Azkar', 'masjidos' ),
			]
		);

		$categories = [ 'all' => __( 'All', 'masjidos' ) ];
		if ( class_exists( 'ITMMS_Duas_Library' ) ) {
			$categories = array_merge( $categories, ITMMS_Duas_Library::category_options() );
		}

		$this->add_control(
			'category',
			[
				'label'       => __( 'Dua Library category', 'masjidos' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'all',
				'options'     => $categories,
				'description' => __( 'Categories come from MasjidOS > Duas Library.', 'masjidos' ),
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
			'design',
			[
				'label'   => __( 'Design', 'masjidos' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'cards',
				'options' => [
					'cards'   => __( 'Cards', 'masjidos' ),
					'compact' => __( 'Compact', 'masjidos' ),
				],
			]
		);

		$this->add_control(
			'limit',
			[
				'label'   => __( 'Limit', 'masjidos' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 50,
				'step'    => 1,
				'default' => 12,
			]
		);

		foreach (
			[
				'source'  => __( 'Show Source', 'masjidos' ),
				'counter' => __( 'Show Counter', 'masjidos' ),
				'share'   => __( 'Show Share', 'masjidos' ),
				'audio'   => __( 'Show Audio', 'masjidos' ),
			] as $key => $label
		) {
			$this->add_control(
				$key,
				[
					'label'        => $label,
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => __( 'Yes', 'masjidos' ),
					'label_off'    => __( 'No', 'masjidos' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				]
			);
		}

		$this->end_controls_section();

		$this->register_common_style_controls( '.itmms-public-duas', '.itmms-public-duas__item' );
	}

	/**
	 * Render widget output.
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();

		$atts = [
			'title'    => $settings['title'] ?? '',
			'category' => $settings['category'] ?? 'all',
			'language' => $settings['language'] ?? $this->get_default_language(),
			'design'   => $settings['design'] ?? 'cards',
			'limit'    => (string) ( $settings['limit'] ?? '12' ),
			'source'   => ( 'yes' === ( $settings['source'] ?? 'yes' ) ) ? 'yes' : 'no',
			'counter'  => ( 'yes' === ( $settings['counter'] ?? 'yes' ) ) ? 'yes' : 'no',
			'share'    => ( 'yes' === ( $settings['share'] ?? 'yes' ) ) ? 'yes' : 'no',
			'audio'    => ( 'yes' === ( $settings['audio'] ?? 'yes' ) ) ? 'yes' : 'no',
		];

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by ITMMS_Public::safe_kses().
		echo ITMMS_Public::get_instance()->render_custom_duas_shortcode( $atts );
	}
}
