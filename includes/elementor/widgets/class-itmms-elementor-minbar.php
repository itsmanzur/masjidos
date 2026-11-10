<?php
/**
 * Elementor Minbar & Khutbah Suite Widget.
 *
 * Covers [masjidos_khutbah_archive], [masjidos_khatib_this_week],
 * [masjidos_upcoming_khutbah], and [masjidos_khutbah_search].
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Class ITMMS_Elementor_Minbar
 */
class ITMMS_Elementor_Minbar extends ITMMS_Elementor_Widget_Base {

	/**
	 * Get widget name.
	 */
	public function get_name(): string {
		return 'masjidos_minbar';
	}

	/**
	 * Get widget title.
	 */
	public function get_title(): string {
		return __( 'Minbar & Khutbah Archive', 'masjidos' );
	}

	/**
	 * Get widget icon.
	 */
	public function get_icon(): string {
		return 'eicon-archive-posts';
	}

	/**
	 * Get widget keywords.
	 */
	public function get_keywords(): array {
		return [ 'khutbah', 'minbar', 'khatib', 'archive', 'sermon', 'jumuah', 'friday', 'masjid' ];
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls(): void {
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Minbar & Khutbah Settings', 'masjidos' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->register_quick_manage_notice(
			__( 'Manage Minbar & Khutbahs', 'masjidos' ),
			'admin.php?page=masjidos#minbar'
		);

		$this->add_control(
			'mode',
			[
				'label'   => __( 'Widget Mode', 'masjidos' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'archive',
				'options' => [
					'archive'          => __( 'Jumuah Khutbah Archive', 'masjidos' ),
					'khatib_this_week' => __( 'This Week’s Khatib', 'masjidos' ),
					'upcoming'         => __( 'Upcoming Khutbahs', 'masjidos' ),
					'search'           => __( 'Khutbah Archive Search', 'masjidos' ),
				],
			]
		);

		$this->add_control(
			'title',
			[
				'label'       => __( 'Custom Heading Title', 'masjidos' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => __( 'Leave empty for default localized title', 'masjidos' ),
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

		$categories = [ '' => __( 'All Categories', 'masjidos' ) ];
		if ( class_exists( 'ITMMS_Khutbah' ) ) {
			$categories = array_merge( $categories, ITMMS_Khutbah::categories() );
		}

		$this->add_control(
			'category',
			[
				'label'     => __( 'Filter by Category', 'masjidos' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => $categories,
				'condition' => [
					'mode' => 'archive',
				],
			]
		);

		$this->add_control(
			'limit',
			[
				'label'     => __( 'Items Limit', 'masjidos' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 100,
				'step'      => 1,
				'default'   => 12,
				'condition' => [
					'mode' => [ 'archive', 'upcoming', 'search' ],
				],
			]
		);

		$this->end_controls_section();

		$this->register_common_style_controls(
			'.itmms-public-archive, {{WRAPPER}} .itmms-public-minbar',
			'.itmms-public-archive__card, {{WRAPPER}} .itmms-public-minbar__card, {{WRAPPER}} .itmms-public-minbar__list-item'
		);
	}

	/**
	 * Render widget output.
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$mode     = $settings['mode'] ?? 'archive';
		$public   = ITMMS_Public::get_instance();

		$atts = [
			'title'    => $settings['title'] ?? '',
			'language' => $settings['language'] ?? $this->get_default_language(),
		];

		switch ( $mode ) {
			case 'khatib_this_week':
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by ITMMS_Public::safe_kses().
				echo $public->render_khatib_this_week_shortcode( $atts );
				break;

			case 'upcoming':
				$atts['limit'] = (string) ( $settings['limit'] ?? '5' );
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by ITMMS_Public::safe_kses().
				echo $public->render_upcoming_khutbah_shortcode( $atts );
				break;

			case 'search':
				$atts['limit'] = (string) ( $settings['limit'] ?? '6' );
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by ITMMS_Public::safe_kses().
				echo $public->render_khutbah_search_shortcode( $atts );
				break;

			case 'archive':
			default:
				$atts['limit']    = (string) ( $settings['limit'] ?? '12' );
				$atts['category'] = (string) ( $settings['category'] ?? '' );
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by ITMMS_Public::safe_kses().
				echo $public->render_khutbah_archive_shortcode( $atts );
				break;
		}
	}
}
