<?php
/**
 * Core bootstrap class.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Loads all sub-systems and wires them into WordPress.
 */
final class ITMMS_Core {

	/** @var ITMMS_Core|null Singleton instance. */
	private static ?ITMMS_Core $instance = null;

	/**
	 * Return or create the singleton instance.
	 */
	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Private constructor. Use get_instance().
	 */
	private function __construct() {
		$this->init();
	}

	/** Prevent cloning. */
	private function __clone() {}

	/**
	 * Hook sub-systems into WordPress.
	 */
	private function init(): void {
		ITMMS_Installer::maybe_upgrade();

		if ( is_admin() ) {
			ITMMS_Admin::get_instance()->init();
		}

		ITMMS_Public::get_instance()->init();
		( new ITMMS_REST() )->init();
	}
}
