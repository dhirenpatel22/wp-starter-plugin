<?php
/**
 * Fires when the plugin is deleted from the Plugins screen.
 * Only runs if "Delete" is clicked — NOT on deactivation.
 *
 * @package WPStarterPlugin
 */

// Safety checks: must be called by WordPress and only when uninstalling.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Remove all plugin options.
$option_keys = [
	'wpsp_version',
	'wpsp_enable_feature_x',
	'wpsp_items_per_page',
	'wpsp_api_key',
];

foreach ( $option_keys as $key ) {
	delete_option( $key );
}

// Multisite: clean up each site's options.
if ( is_multisite() ) {
	$sites = get_sites( [ 'fields' => 'ids', 'number' => 0 ] );
	foreach ( $sites as $site_id ) {
		switch_to_blog( $site_id );
		foreach ( $option_keys as $key ) {
			delete_option( $key );
		}
		restore_current_blog();
	}
}

// Drop custom table.
// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wpsp_items" );

// Clear any scheduled events.
wp_clear_scheduled_hook( 'wpsp_daily_event' );
