<?php
/**
 * Settings page partial.
 *
 * @package WPStarterPlugin\Admin
 */

defined( 'ABSPATH' ) || exit;

$enable_feature = (bool) get_option( 'wpsp_enable_feature_x', true );
$items_per_page = (int)  get_option( 'wpsp_items_per_page', 10 );
$api_key        =        get_option( 'wpsp_api_key', '' );
?>
<div class="wrap wpsp-settings">
	<h1><?php esc_html_e( 'WP Starter Plugin — Settings', 'wp-starter-plugin' ); ?></h1>

	<?php settings_errors( 'wpsp_settings' ); ?>

	<form method="post" action="">
		<?php wp_nonce_field( 'wpsp_save_settings', 'wpsp_settings_nonce' ); ?>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="enable_feature_x">
						<?php esc_html_e( 'Enable Feature X', 'wp-starter-plugin' ); ?>
					</label>
				</th>
				<td>
					<input
						type="checkbox"
						id="enable_feature_x"
						name="enable_feature_x"
						value="1"
						<?php checked( $enable_feature ); ?>
					/>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="items_per_page">
						<?php esc_html_e( 'Items Per Page', 'wp-starter-plugin' ); ?>
					</label>
				</th>
				<td>
					<input
						type="number"
						id="items_per_page"
						name="items_per_page"
						value="<?php echo esc_attr( $items_per_page ); ?>"
						min="1"
						max="100"
						class="small-text"
					/>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="api_key">
						<?php esc_html_e( 'API Key', 'wp-starter-plugin' ); ?>
					</label>
				</th>
				<td>
					<input
						type="password"
						id="api_key"
						name="api_key"
						value="<?php echo esc_attr( $api_key ); ?>"
						class="regular-text"
						autocomplete="new-password"
					/>
					<p class="description">
						<?php esc_html_e( 'Enter your API key here.', 'wp-starter-plugin' ); ?>
					</p>
				</td>
			</tr>
		</table>

		<?php submit_button( __( 'Save Settings', 'wp-starter-plugin' ) ); ?>
	</form>
</div>
