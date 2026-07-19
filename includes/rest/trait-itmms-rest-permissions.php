<?php
/**
 * ITMMS_REST_Permissions methods for ITMMS_REST.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * @package MasjidOS
 */
trait ITMMS_REST_Permissions {

	public function can_read(): bool {
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		foreach ( ITMMS_Roles::CAPS as $cap ) {
			if ( current_user_can( $cap ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Capability check for mutating settings.
	 */
	public function can_manage_settings(): bool {
		return current_user_can( 'manage_options' ) || current_user_can( 'itmms_manage_settings' );
	}

	public function can_manage_announcements(): bool {
		return current_user_can( 'manage_options' ) || current_user_can( 'itmms_manage_announcements' );
	}

	public function can_manage_events(): bool {
		return current_user_can( 'manage_options' ) || current_user_can( 'itmms_manage_events' );
	}

	public function can_manage_khutbah(): bool {
		return current_user_can( 'manage_options' ) || current_user_can( 'itmms_manage_khutbah' );
	}

	public function can_manage_prayers(): bool {
		return current_user_can( 'manage_options' ) || current_user_can( 'itmms_manage_prayers' );
	}

}
