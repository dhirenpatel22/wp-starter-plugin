<?php
/**
 * Plugin Name:       WP Starter Plugin
 * Plugin URI:        https://github.com/dhirenpatel22/wp-starter-plugin
 * Description:       A boilerplate to kickstart custom WordPress plugin development.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.1
 * Author:            Dhiren Patel
 * Author URI:        https://www.dhirenpatel.me/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-starter-plugin
 * Domain Path:       /languages
 *
 * @package WPStarterPlugin
 */

defined( 'ABSPATH' ) || exit;

// Plugin constants.
define( 'WPSP_VERSION',     '1.0.0' );
define( 'WPSP_FILE',        __FILE__ );
define( 'WPSP_PATH',        plugin_dir_path( __FILE__ ) );
define( 'WPSP_URL',         plugin_dir_url( __FILE__ ) );
define( 'WPSP_BASENAME',    plugin_basename( __FILE__ ) );

// Activation / deactivation hooks — fired before the core class loads.
register_activation_hook(   WPSP_FILE, [ 'WPStarterPlugin\Includes\Activator',   'activate'   ] );
register_deactivation_hook( WPSP_FILE, [ 'WPStarterPlugin\Includes\Deactivator', 'deactivate' ] );

/**
 * Autoloader — PSR-4 style, maps WPStarterPlugin\ → plugin root.
 */
spl_autoload_register( function ( string $class ): void {
	$prefix   = 'WPStarterPlugin\\';
	$base_dir = WPSP_PATH;

	if ( strncmp( $prefix, $class, strlen( $prefix ) ) !== 0 ) {
		return;
	}

	$relative = substr( $class, strlen( $prefix ) );
	$parts    = explode( '\\', $relative );
	$class_name = array_pop( $parts );

	// Convert namespace segments to lowercase folder names.
	$sub_dir   = strtolower( implode( '/', $parts ) );
	$file_name = 'class-' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';
	$file      = $base_dir . ( $sub_dir ? $sub_dir . '/' : '' ) . $file_name;

	if ( file_exists( $file ) ) {
		require_once $file;
	}
} );

/**
 * Returns the single plugin instance.
 *
 * @return WPStarterPlugin\Includes\Plugin
 */
function wpsp(): WPStarterPlugin\Includes\Plugin {
	return WPStarterPlugin\Includes\Plugin::instance();
}

// Kick everything off after all plugins are loaded.
add_action( 'plugins_loaded', 'wpsp' );
