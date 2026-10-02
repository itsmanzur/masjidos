<?php
/**
 * Elementor integration orchestrator for MasjidOS.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ITMMS_Elementor
 */
final class ITMMS_Elementor {

	/** @var self|null Singleton instance. */
	private static ?self $instance = null;

	/**
	 * Get singleton instance.
	 */
	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Private constructor.
	 */
	private function __construct() {}

	/**
	 * Initialize Elementor hooks.
	 */
	public function init(): void {
		add_action( 'elementor/elements/categories_registered', [ $this, 'register_category' ] );
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
		add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'enqueue_editor_assets' ] );
		add_action( 'elementor/preview/enqueue_styles', [ $this, 'enqueue_editor_assets' ] );
	}

	/**
	 * Register MasjidOS category in Elementor.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager Elements manager instance.
	 */
	public function register_category( $elements_manager ): void {
		$elements_manager->add_category(
			'masjidos',
			[
				'title' => __( 'MasjidOS', 'masjidos' ),
				'icon'  => 'eicon-apps',
			]
		);
	}

	/**
	 * Register MasjidOS widgets.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Widgets manager instance.
	 */
	public function register_widgets( $widgets_manager ): void {
		require_once ITMMS_PLUGIN_DIR . 'includes/elementor/widgets/class-itmms-elementor-prayer-times.php';
		require_once ITMMS_PLUGIN_DIR . 'includes/elementor/widgets/class-itmms-elementor-monthly-timetable.php';
		require_once ITMMS_PLUGIN_DIR . 'includes/elementor/widgets/class-itmms-elementor-jumuah.php';
		require_once ITMMS_PLUGIN_DIR . 'includes/elementor/widgets/class-itmms-elementor-announcements.php';
		require_once ITMMS_PLUGIN_DIR . 'includes/elementor/widgets/class-itmms-elementor-islamic-calendar.php';
		require_once ITMMS_PLUGIN_DIR . 'includes/elementor/widgets/class-itmms-elementor-ask-imam.php';
		require_once ITMMS_PLUGIN_DIR . 'includes/elementor/widgets/class-itmms-elementor-education.php';

		$widgets_manager->register( new ITMMS_Elementor_Prayer_Times() );
		$widgets_manager->register( new ITMMS_Elementor_Monthly_Timetable() );
		$widgets_manager->register( new ITMMS_Elementor_Jumuah() );
		$widgets_manager->register( new ITMMS_Elementor_Announcements() );
		$widgets_manager->register( new ITMMS_Elementor_Islamic_Calendar() );
		$widgets_manager->register( new ITMMS_Elementor_Ask_Imam() );
		$widgets_manager->register( new ITMMS_Elementor_Education() );
	}

	/**
	 * Enqueue front-end CSS inside the Elementor editor and preview.
	 */
	public function enqueue_editor_assets(): void {
		wp_enqueue_style(
			'itmms-public-styles',
			ITMMS_PLUGIN_URL . 'public/assets/css/public.css',
			[],
			ITMMS_VERSION
		);
		wp_enqueue_script(
			'itmms-public-scripts',
			ITMMS_PLUGIN_URL . 'public/assets/js/public.js',
			[],
			ITMMS_VERSION,
			true
		);
	}
}
