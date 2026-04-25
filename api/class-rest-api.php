<?php
/**
 * REST API endpoints.
 *
 * Namespace : wpsp/v1
 * Endpoints :
 *   GET  /items         — list items (authenticated)
 *   POST /items         — create item (authenticated)
 *   GET  /items/{id}    — get single item (authenticated)
 *   PUT  /items/{id}    — update item (authenticated)
 *   DELETE /items/{id}  — delete item (authenticated)
 *
 * @package WPStarterPlugin\Api
 */

namespace WPStarterPlugin\Api;

defined( 'ABSPATH' ) || exit;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class RestApi {

	private const NAMESPACE = 'wpsp/v1';
	private const ROUTE     = '/items';

	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			self::ROUTE,
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_items' ],
					'permission_callback' => [ $this, 'check_read_permission' ],
					'args'                => $this->collection_params(),
				],
				[
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_item' ],
					'permission_callback' => [ $this, 'check_write_permission' ],
					'args'                => $this->item_schema(),
				],
			]
		);

		register_rest_route(
			self::NAMESPACE,
			self::ROUTE . '/(?P<id>[\d]+)',
			[
				[
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_item' ],
					'permission_callback' => [ $this, 'check_read_permission' ],
					'args'                => [ 'id' => [ 'validate_callback' => 'is_numeric' ] ],
				],
				[
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_item' ],
					'permission_callback' => [ $this, 'check_write_permission' ],
					'args'                => $this->item_schema(),
				],
				[
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_item' ],
					'permission_callback' => [ $this, 'check_write_permission' ],
				],
			]
		);
	}

	// -------------------------------------------------------------------------
	// Callbacks
	// -------------------------------------------------------------------------

	public function get_items( WP_REST_Request $request ): WP_REST_Response {
		global $wpdb;

		$table  = $wpdb->prefix . 'wpsp_items';
		$page   = max( 1, (int) $request->get_param( 'page' ) );
		$limit  = min( 100, max( 1, (int) ( $request->get_param( 'per_page' ) ?? 10 ) ) );
		$offset = ( $page - 1 ) * $limit;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$items = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE status = %s ORDER BY created_at DESC LIMIT %d OFFSET %d",
				'active',
				$limit,
				$offset
			),
			ARRAY_A
		);

		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE status = 'active'" );

		$response = new WP_REST_Response( $items, 200 );
		$response->header( 'X-WP-Total',      $total );
		$response->header( 'X-WP-TotalPages', (int) ceil( $total / $limit ) );

		return $response;
	}

	public function get_item( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		global $wpdb;

		$id    = absint( $request->get_param( 'id' ) );
		$table = $wpdb->prefix . 'wpsp_items';
		$item  = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ),
			ARRAY_A
		);

		if ( ! $item ) {
			return new WP_Error( 'wpsp_not_found', __( 'Item not found.', 'wp-starter-plugin' ), [ 'status' => 404 ] );
		}

		return new WP_REST_Response( $item, 200 );
	}

	public function create_item( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		global $wpdb;

		$data = [
			'user_id' => get_current_user_id(),
			'title'   => sanitize_text_field( $request->get_param( 'title' ) ),
			'content' => wp_kses_post( $request->get_param( 'content' ) ?? '' ),
			'status'  => 'active',
		];

		$wpdb->insert( $wpdb->prefix . 'wpsp_items', $data );

		if ( ! $wpdb->insert_id ) {
			return new WP_Error( 'wpsp_db_error', __( 'Could not create item.', 'wp-starter-plugin' ), [ 'status' => 500 ] );
		}

		return $this->get_item( new WP_REST_Request( 'GET', "/wpsp/v1/items/{$wpdb->insert_id}" ) );
	}

	public function update_item( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		global $wpdb;

		$id    = absint( $request->get_param( 'id' ) );
		$table = $wpdb->prefix . 'wpsp_items';

		$existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE id = %d", $id ) );
		if ( ! $existing ) {
			return new WP_Error( 'wpsp_not_found', __( 'Item not found.', 'wp-starter-plugin' ), [ 'status' => 404 ] );
		}

		$data = array_filter( [
			'title'   => $request->get_param( 'title' )   ? sanitize_text_field( $request->get_param( 'title' ) )   : null,
			'content' => $request->get_param( 'content' ) ? wp_kses_post( $request->get_param( 'content' ) )         : null,
		] );

		$wpdb->update( $table, $data, [ 'id' => $id ] );

		return $this->get_item( $request );
	}

	public function delete_item( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		global $wpdb;

		$id     = absint( $request->get_param( 'id' ) );
		$table  = $wpdb->prefix . 'wpsp_items';
		$result = $wpdb->delete( $table, [ 'id' => $id ], [ '%d' ] );

		if ( ! $result ) {
			return new WP_Error( 'wpsp_not_found', __( 'Item not found.', 'wp-starter-plugin' ), [ 'status' => 404 ] );
		}

		return new WP_REST_Response( [ 'deleted' => true, 'id' => $id ], 200 );
	}

	// -------------------------------------------------------------------------
	// Permission callbacks
	// -------------------------------------------------------------------------

	public function check_read_permission(): bool {
		return is_user_logged_in();
	}

	public function check_write_permission(): bool {
		return current_user_can( 'edit_posts' );
	}

	// -------------------------------------------------------------------------
	// Schema / args
	// -------------------------------------------------------------------------

	private function collection_params(): array {
		return [
			'page'     => [
				'default'           => 1,
				'sanitize_callback' => 'absint',
			],
			'per_page' => [
				'default'           => 10,
				'sanitize_callback' => 'absint',
			],
		];
	}

	private function item_schema(): array {
		return [
			'title' => [
				'required'          => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'content' => [
				'required'          => false,
				'type'              => 'string',
				'sanitize_callback' => 'wp_kses_post',
			],
		];
	}
}
