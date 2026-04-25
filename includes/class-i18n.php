<?php
/**
 * Internationalisation.
 *
 * @package WPStarterPlugin\Includes
 */

namespace WPStarterPlugin\Includes;

defined( 'ABSPATH' ) || exit;

class I18n {

	public function load_plugin_textdomain(): void {
		load_plugin_textdomain(
			'wp-starter-plugin',
			false,
			dirname( WPSP_BASENAME ) . '/languages/'
		);
	}
}
