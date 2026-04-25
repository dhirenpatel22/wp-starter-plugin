<?php
/**
 * Front-end (public) functionality.
 *
 * @package WPStarterPlugin\PublicFacing
 */

namespace WPStarterPlugin\PublicFacing;

defined( 'ABSPATH' ) || exit;

class Frontend {

	/** Register front-end stylesheet. */
	public function enqueue_styles(): void {
		wp_enqueue_style(
			'wpsp-public',
			WPSP_URL . 'public/css/public.css',
			[],
			WPSP_VERSION
		);
	}

	/** Register front-end JavaScript. */
	public function enqueue_scripts(): void {
		wp_enqueue_script(
			'wpsp-public',
			WPSP_URL . 'public/js/public.js',
			[],
			WPSP_VERSION,
			true
		);

		wp_localize_script(
			'wpsp-public',
			'wpspPublic',
			[
				'restUrl' => esc_url_raw( rest_url( 'wpsp/v1/' ) ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
			]
		);
	}
}
