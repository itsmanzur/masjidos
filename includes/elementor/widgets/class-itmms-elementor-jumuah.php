<?php
/**
 * Elementor Jumuah Widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Class ITMMS_Elementor_Jumuah
 */
class ITMMS_Elementor_Jumuah extends ITMMS_Elementor_Widget_Base {

	/**
	 * Get widget name.
	 */
	public function get_name(): string {
		return 'masjidos_jumuah';
	}

	/**
	 * Get widget title.
	 */
	public function get_title(): string {
		return __( 'Jumuah & Khatib', 'masjidos' );
	}

	/**
	 * Get widget icon.
	 */
	public function get_icon(): string {
		return 'eicon-user-circle-o';
	}

	/**
	 * Get widget keywords.
	 */
	public function get_keywords(): array {
		return [ 'jumuah', 'friday', 'khatib', 'khutbah', 'imam', 'masjid', 'sermon' ];
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls(): void {
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Jumuah Settings', 'masjidos' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->register_quick_manage_notice(
			__( 'Configure Jumuah Sessions & Khatib', 'masjidos' ),
			'admin.php?page=masjidos#prayer-times',
			'jumuah'
		);

		$this->add_control(
			'title',
			[
				'label'       => __( 'Heading Title', 'masjidos' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Friday Jumuah Prayer', 'masjidos' ),
				'placeholder' => __( 'Friday Jumuah Prayer', 'masjidos' ),
			]
		);

		$design_options = [
			'classic' => __( 'Classic Card', 'masjidos' ),
			'compact' => __( 'Compact', 'masjidos' ),
		];
		if ( function_exists( 'masjidos_pro_is_active' ) && masjidos_pro_is_active() ) {
			$design_options['premium-sermon'] = __( 'Pro: Premium Sermon', 'masjidos' );
			$design_options['mosque-notice']  = __( 'Pro: Mosque Notice', 'masjidos' );
		}

		$this->add_control(
			'design',
			[
				'label'   => __( 'Design Layout', 'masjidos' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'classic',
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
			'meta',
			[
				'label'        => __( 'Show Khatib Profile & Topic', 'masjidos' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'masjidos' ),
				'label_off'    => __( 'No', 'masjidos' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->end_controls_section();

		$this->register_common_style_controls( '.itmms-public-jumuah', '.itmms-public-jumuah__session' );
	}

	/**
	 * Render widget output.
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();

		$atts = [
			'title'    => $settings['title'] ?? '',
			'design'   => $settings['design'] ?? 'classic',
			'language' => $settings['language'] ?? $this->get_default_language(),
			'meta'     => ( 'yes' === ( $settings['meta'] ?? '' ) ) ? 'yes' : 'no',
		];

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by ITMMS_Public::safe_kses() preserving select, form and button controls.
		echo ITMMS_Public::get_instance()->render_jumuah_shortcode( $atts );
	}
}
