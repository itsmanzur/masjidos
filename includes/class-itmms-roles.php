<?php
/**
 * Registers custom roles and capabilities.
 *
 * @package MasjidOS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class ITMMS_Roles
 */
class ITMMS_Roles {

	/**
	 * All custom capabilities used by MasjidOS.
	 *
	 * @var string[]
	 */
	public const CAPS = [
		'itmms_manage_prayers',
		'itmms_manage_events',
		'itmms_manage_announcements',
		'itmms_manage_khutbah',
		'itmms_view_reports',
		'itmms_manage_settings',
	];

	/**
	 * Role-specific capability map.
	 *
	 * @var array<string,array<string,bool>>
	 */
	private const ROLE_CAPS = [
		'itmms_imam'    => [
			'read'                       => true,
			'itmms_manage_prayers'       => true,
			'itmms_manage_events'        => true,
			'itmms_manage_announcements' => true,
			'itmms_manage_khutbah'       => true,
			'itmms_view_reports'         => true,
		],
		'itmms_muazzin' => [
			'read'                 => true,
			'itmms_manage_prayers' => true,
			'itmms_view_reports'   => true,
		],
	];

	/**
	 * Called on plugin activation and upgrade.
	 * Adds custom roles and grants all caps to administrators.
	 */
	public static function setup_roles(): void {
		add_role(
			'itmms_imam',
			__( 'Imam', 'masjidos' ),
			self::ROLE_CAPS['itmms_imam']
		);

		add_role(
			'itmms_muazzin',
			__( 'Muazzin', 'masjidos' ),
			self::ROLE_CAPS['itmms_muazzin']
		);

		foreach ( self::ROLE_CAPS as $role_key => $capabilities ) {
			self::grant_caps_to_role( $role_key, $capabilities );
		}

		$admin = get_role( 'administrator' );
		if ( $admin instanceof WP_Role ) {
			foreach ( self::CAPS as $cap ) {
				$admin->add_cap( $cap );
			}
		}
	}

	/**
	 * Remove roles and capabilities added by MasjidOS.
	 * Call this only on plugin uninstall, not on deactivation.
	 */
	public static function remove_roles(): void {
		remove_role( 'itmms_imam' );
		remove_role( 'itmms_muazzin' );

		$admin = get_role( 'administrator' );
		if ( $admin instanceof WP_Role ) {
			foreach ( self::CAPS as $cap ) {
				$admin->remove_cap( $cap );
			}
		}
	}

	/**
	 * Add capabilities to an existing role.
	 *
	 * WordPress does not update capabilities when add_role() is called for a
	 * role that already exists, so activation and upgrades both need this pass.
	 *
	 * @param string             $role_key Role slug.
	 * @param array<string,bool> $capabilities Capabilities to grant.
	 */
	private static function grant_caps_to_role( string $role_key, array $capabilities ): void {
		$role = get_role( $role_key );
		if ( ! $role instanceof WP_Role ) {
			return;
		}

		foreach ( $capabilities as $capability => $grant ) {
			if ( $grant ) {
				$role->add_cap( $capability );
			}
		}
	}
}
