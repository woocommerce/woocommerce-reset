<?php
/**
 * Class RestTest
 *
 * @package WooCommerce_Reset
 */

namespace Automattic\WooCommerce\Reset;

/**
 * REST test case.
 */
class RestTest extends \WP_UnitTestCase {

	/**
	 * Inits the REST server.
	 */
	public function setUp() {
		parent::setUp();

		global $wp_rest_server;
		$wp_rest_server = new \WP_REST_Server();
		do_action( 'rest_api_init' );
	}

	/**
	 * Test the reset endpoint.
	 */
	public function test_reset_endpoint() {
		global $wp_rest_server;

		$routes = $wp_rest_server->get_routes();

		wp_set_current_user( 0 );

		$request = new \WP_REST_Request( 'POST', '/woocommerce-reset/v1/reset' );

		$response = $wp_rest_server->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );
	}
}
