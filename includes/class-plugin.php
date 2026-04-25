<?php
/**
 * Core plugin class — singleton entry point.
 *
 * @package WPStarterPlugin\Includes
 */

namespace WPStarterPlugin\Includes;

defined( 'ABSPATH' ) || exit;

final class Plugin {

	/** @var Plugin|null */
	private static ?Plugin $instance = null;

	/** @var Loader */
	private Loader $loader;

	/** Private constructor enforces singleton. */
	private function __construct() {
		$this->loader = new Loader();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_api_hooks();
		$this->loader->run();
	}

	/** Returns (and creates) the single instance. */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/** Wire up i18n. */
	private function set_locale(): void {
		$i18n = new I18n();
		$this->loader->add_action( 'init', $i18n, 'load_plugin_textdomain' );
	}

	/** Admin-side hooks. */
	private function define_admin_hooks(): void {
		if ( ! is_admin() ) {
			return;
		}

		$admin = new \WPStarterPlugin\Admin\Admin();

		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu',            $admin, 'register_menu' );

		// Plugin row "Settings" link.
		$this->loader->add_filter(
			'plugin_action_links_' . WPSP_BASENAME,
			$admin,
			'plugin_action_links'
		);
	}

	/** Front-end hooks. */
	private function define_public_hooks(): void {
		$frontend = new \WPStarterPlugin\PublicFacing\Frontend();

		$this->loader->add_action( 'wp_enqueue_scripts', $frontend, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $frontend, 'enqueue_scripts' );
	}

	/** REST API hooks. */
	private function define_api_hooks(): void {
		$api = new \WPStarterPlugin\Api\RestApi();
		$this->loader->add_action( 'rest_api_init', $api, 'register_routes' );
	}

	/** Prevent cloning and unserialization. */
	public function __clone() {}
	public function __wakeup() {}
}
