<?php
/**
 * Admin-facing functionality.
 *
 * @package WPStarterPlugin\Admin
 */

namespace WPStarterPlugin\Admin;

defined( 'ABSPATH' ) || exit;

class Admin {

	/** Register admin stylesheet. */
	public function enqueue_styles( string $hook_suffix ): void {
		// Only load on plugin pages.
		if ( ! $this->is_plugin_page( $hook_suffix ) ) {
			return;
		}

		wp_enqueue_style(
			'wpsp-admin',
			WPSP_URL . 'admin/css/admin.css',
			[],
			WPSP_VERSION
		);
	}

	/** Register admin JavaScript. */
	public function enqueue_scripts( string $hook_suffix ): void {
		if ( ! $this->is_plugin_page( $hook_suffix ) ) {
			return;
		}

		wp_enqueue_script(
			'wpsp-admin',
			WPSP_URL . 'admin/js/admin.js',
			[ 'jquery' ],
			WPSP_VERSION,
			true   // load in footer
		);

		// Pass data to JS.
		wp_localize_script(
			'wpsp-admin',
			'wpspAdmin',
			[
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'wpsp_admin_nonce' ),
				'i18n'    => [
					'saving' => __( 'Saving…', 'wp-starter-plugin' ),
					'saved'  => __( 'Saved!',  'wp-starter-plugin' ),
					'error'  => __( 'An error occurred.', 'wp-starter-plugin' ),
				],
			]
		);
	}

	/** Register top-level menu and sub-pages. */
	public function register_menu(): void {
		add_menu_page(
			__( 'WP Starter Plugin', 'wp-starter-plugin' ),
			__( 'Starter Plugin',    'wp-starter-plugin' ),
			'manage_options',
			'wp-starter-plugin',
			[ $this, 'render_dashboard_page' ],
			'dashicons-admin-plugins',
			80
		);

		add_submenu_page(
			'wp-starter-plugin',
			__( 'Dashboard', 'wp-starter-plugin' ),
			__( 'Dashboard', 'wp-starter-plugin' ),
			'manage_options',
			'wp-starter-plugin',
			[ $this, 'render_dashboard_page' ]
		);

		add_submenu_page(
			'wp-starter-plugin',
			__( 'Settings', 'wp-starter-plugin' ),
			__( 'Settings', 'wp-starter-plugin' ),
			'manage_options',
			'wp-starter-plugin-settings',
			[ $this, 'render_settings_page' ]
		);
	}

	/** Render the dashboard page. */
	public function render_dashboard_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'wp-starter-plugin' ) );
		}
		require_once WPSP_PATH . 'admin/partials/dashboard.php';
	}

	/** Render the settings page with the Settings API. */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'wp-starter-plugin' ) );
		}

		// Handle Settings API form save.
		if ( isset( $_POST['wpsp_settings_nonce'] ) ) {
			$this->save_settings();
		}

		require_once WPSP_PATH . 'admin/partials/settings.php';
	}

	/** Save settings with nonce and capability check. */
	private function save_settings(): void {
		if ( ! check_admin_referer( 'wpsp_save_settings', 'wpsp_settings_nonce' ) ) {
			wp_die( esc_html__( 'Nonce verification failed.', 'wp-starter-plugin' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'wp-starter-plugin' ) );
		}

		update_option( 'wpsp_enable_feature_x', isset( $_POST['enable_feature_x'] ) ? true : false );
		update_option( 'wpsp_items_per_page',   absint( $_POST['items_per_page'] ?? 10 ) );
		update_option( 'wpsp_api_key',          sanitize_text_field( $_POST['api_key'] ?? '' ) );

		add_settings_error(
			'wpsp_settings',
			'wpsp_settings_updated',
			__( 'Settings saved.', 'wp-starter-plugin' ),
			'updated'
		);
	}

	/** Add "Settings" link on Plugins list screen. */
	public function plugin_action_links( array $links ): array {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			admin_url( 'admin.php?page=wp-starter-plugin-settings' ),
			__( 'Settings', 'wp-starter-plugin' )
		);
		array_unshift( $links, $settings_link );
		return $links;
	}

	/** True when the current screen belongs to this plugin. */
	private function is_plugin_page( string $hook_suffix ): bool {
		return str_contains( $hook_suffix, 'wp-starter-plugin' );
	}
}
