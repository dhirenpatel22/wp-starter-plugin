<?php
/**
 * Fired on plugin activation.
 *
 * @package WPStarterPlugin\Includes
 */

namespace WPStarterPlugin\Includes;

defined( 'ABSPATH' ) || exit;

class Activator {

	public static function activate(): void {
		// Minimum requirements check.
		if ( version_compare( PHP_VERSION, '8.1', '<' ) ) {
			deactivate_plugins( WPSP_BASENAME );
			wp_die(
				esc_html__( 'WP Starter Plugin requires PHP 8.1 or higher.', 'wp-starter-plugin' ),
				esc_html__( 'Plugin Activation Error', 'wp-starter-plugin' ),
				[ 'back_link' => true ]
			);
		}

		if ( version_compare( get_bloginfo( 'version' ), '6.0', '<' ) ) {
			deactivate_plugins( WPSP_BASENAME );
			wp_die(
				esc_html__( 'WP Starter Plugin requires WordPress 6.0 or higher.', 'wp-starter-plugin' ),
				esc_html__( 'Plugin Activation Error', 'wp-starter-plugin' ),
				[ 'back_link' => true ]
			);
		}

		self::create_tables();
		self::set_default_options();

		// Store activation version for future upgrade routines.
		update_option( 'wpsp_version', WPSP_VERSION );

		// Schedule a recurring event (example).
		if ( ! wp_next_scheduled( 'wpsp_daily_event' ) ) {
			wp_schedule_event( time(), 'daily', 'wpsp_daily_event' );
		}

		// Flush rewrite rules after CPT/taxonomy registration.
		flush_rewrite_rules();
	}

	/** Create custom DB tables if needed. */
	private static function create_tables(): void {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . 'wpsp_items';

		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id            BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id       BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			title         VARCHAR(200)        NOT NULL DEFAULT '',
			content       LONGTEXT,
			status        VARCHAR(20)         NOT NULL DEFAULT 'active',
			created_at    DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at    DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY   (id),
			KEY user_id   (user_id),
			KEY status    (status)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/** Set default plugin options. */
	private static function set_default_options(): void {
		$defaults = [
			'enable_feature_x' => true,
			'items_per_page'   => 10,
			'api_key'          => '',
		];

		foreach ( $defaults as $key => $value ) {
			// add_option silently skips if the option already exists.
			add_option( "wpsp_{$key}", $value );
		}
	}
}
