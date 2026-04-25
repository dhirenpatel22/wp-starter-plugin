<?php
/**
 * Fired on plugin deactivation.
 *
 * @package WPStarterPlugin\Includes
 */

namespace WPStarterPlugin\Includes;

defined( 'ABSPATH' ) || exit;

class Deactivator {

	public static function deactivate(): void {
		// Clear scheduled events.
		$timestamp = wp_next_scheduled( 'wpsp_daily_event' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'wpsp_daily_event' );
		}

		// Flush rewrite rules (removes any CPT slugs).
		flush_rewrite_rules();
	}
}
