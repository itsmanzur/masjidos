<?php
/**
 * Remove MasjidOS data when the plugin is deleted from WordPress.
 *
 * @package MasjidOS
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

/**
 * Clean one site's MasjidOS data.
 */
function itmms_uninstall_site(): void {
	global $wpdb;

	delete_option( 'itmms_settings' );
	delete_option( 'itmms_prayer_timetable' );
	delete_option( 'itmms_khutbah_plans' );
	delete_option( 'itmms_minbar_bookmarks' );
	delete_option( 'itmms_db_version' );
	delete_option( 'itmms_show_welcome' );

	$tables = [
		$wpdb->prefix . 'itmms_announcements',
		$wpdb->prefix . 'itmms_events',
		$wpdb->prefix . 'itmms_khutbah_archive',
		$wpdb->prefix . 'itmms_khatib_profiles',
		$wpdb->prefix . 'itmms_khatib_schedule',
	];

	foreach ( $tables as $table ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Uninstall must remove the plugin's internally named custom tables.
		$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Uninstall must remove all prayer cache transients, including expired rows.
	$wpdb->query(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_itmms_prayers_%' OR option_name LIKE '_transient_timeout_itmms_prayers_%'"
	);

	remove_role( 'itmms_imam' );
	remove_role( 'itmms_muazzin' );


	$admin = get_role( 'administrator' );
	if ( $admin instanceof WP_Role ) {
		$capabilities = [
			'itmms_manage_prayers',
			'itmms_manage_events',
			'itmms_manage_announcements',
			'itmms_manage_khutbah',
			'itmms_view_reports',
			'itmms_manage_settings',
		];
		foreach ( $capabilities as $capability ) {
			$admin->remove_cap( $capability );
		}
	}
}

if ( is_multisite() ) {
	$itmms_site_ids = get_sites( [ 'fields' => 'ids', 'number' => 0 ] );
	foreach ( $itmms_site_ids as $itmms_site_id ) {
		switch_to_blog( (int) $itmms_site_id );
		itmms_uninstall_site();
		restore_current_blog();
	}
} else {
	itmms_uninstall_site();
}
