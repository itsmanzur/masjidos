<?php
/**
 * Elementor Monthly Timetable Widget.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

/**
 * Class ITMMS_Elementor_Monthly_Timetable
 */
class ITMMS_Elementor_Monthly_Timetable extends ITMMS_Elementor_Widget_Base {

	/**
	 * Get widget name.
	 */
	public function get_name(): string {
		return 'masjidos_monthly_timetable';
	}

	/**
	 * Get widget title.
	 */
	public function get_title(): string {
		return __( 'Monthly Prayer Timetable', 'masjidos' );
	}

	/**
	 * Get widget icon.
	 */
	public function get_icon(): string {
		return 'eicon-table';
	}

	/**
	 * Get widget keywords.
	 */
	public function get_keywords(): array {
		return [ 'monthly', 'prayer', 'timetable', 'calendar', 'salah', 'namaz', 'masjid' ];
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls(): void {
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Timetable Settings', 'masjidos' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->register_quick_manage_notice(
			__( 'Edit Monthly Timetable & Overrides', 'masjidos' ),
			'admin.php?page=masjidos#prayer-times',
			'prayer_times'
		);

		$this->add_control(
			'title',
			[
				'label'       => __( 'Heading Title', 'masjidos' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Monthly Prayer Timetable', 'masjidos' ),
				'placeholder' => __( 'Monthly Prayer Timetable', 'masjidos' ),
			]
		);

		$design_options = [
			'table'   => __( 'Table Grid', 'masjidos' ),
			'compact' => __( 'Compact List', 'masjidos' ),
		];
		if ( function_exists( 'masjidos_pro_is_active' ) && masjidos_pro_is_active() ) {
			$design_options['premium-print']   = __( 'Pro: Premium Print', 'masjidos' );
			$design_options['mosque-board']    = __( 'Pro: Mosque Board', 'masjidos' );
			$design_options['ramadan-monthly'] = __( 'Pro: Ramadan Monthly', 'masjidos' );
		}

		$this->add_control(
			'design',
			[
				'label'   => __( 'Design Layout', 'masjidos' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'table',
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

		$this->add_control(
			'iqamah',
			[
				'label'        => __( 'Show Iqamah Times', 'masjidos' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'masjidos' ),
				'label_off'    => __( 'No', 'masjidos' ),
				'return_value' => 'yes',
				'default'      => 'no',
			]
		);

		$this->add_control(
			'navigation',
			[
				'label'        => __( 'Month/Year Navigation', 'masjidos' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'masjidos' ),
				'label_off'    => __( 'No', 'masjidos' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'extras',
			[
				'label'        => __( 'Show Ishraq / Zawal Times', 'masjidos' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'masjidos' ),
				'label_off'    => __( 'No', 'masjidos' ),
				'return_value' => 'yes',
				'default'      => 'no',
			]
		);

		$this->end_controls_section();

		$this->register_common_style_controls( '.itmms-public-monthly' );
	}

	/**
	 * Render widget output.
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();

		$atts = [
			'title'      => $settings['title'] ?? '',
			'design'     => $settings['design'] ?? 'table',
			'language'   => $settings['language'] ?? $this->get_default_language(),
			'month'      => (string) ( $settings['month'] ?? '' ),
			'year'       => (string) ( $settings['year'] ?? '' ),
			'iqamah'     => ( 'yes' === ( $settings['iqamah'] ?? '' ) ) ? 'yes' : 'no',
			'navigation' => ( 'yes' === ( $settings['navigation'] ?? '' ) ) ? 'yes' : 'no',
			'extras'     => ( 'yes' === ( $settings['extras'] ?? '' ) ) ? 'yes' : 'no',
		];

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized by ITMMS_Public::safe_kses() preserving select, form and button controls.
		echo ITMMS_Public::get_instance()->render_monthly_prayer_times_shortcode( $atts );
	}
}
