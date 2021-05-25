<?php
/**
 * Class RestTest
 *
 * @package WooCommerce_Reset
 */

namespace Automattic\WooCommerce\Reset;

/**
 * Integration test for reset endpoint.
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
	 * Test the reset endpoint deletes all woocommerce options.
	 */
	public function test_reset_endpoint_deletes_woocommerce_options() {
		global $wp_rest_server;

		$routes = $wp_rest_server->get_routes();

		wp_set_current_user( 0 );

		$request = new \WP_REST_Request( 'POST', '/woocommerce-reset/v1/reset' );

		$response = $wp_rest_server->dispatch( $request );

		$this->assertEquals( 200, $response->get_status() );

		$this->assertFalse( get_option( 'woocommerce_allow_tracking' ) );
		$this->assertFalse( get_option( 'woocommerce_onboarding_profile' ) );
		$this->assertFalse( get_option( 'woocommerce_task_list_welcome_modal_dismissed' ) );
		$this->assertFalse( get_option( 'woocommerce_task_list_tracked_completed_tasks' ) );
		$this->assertFalse( get_option( 'woocommerce_ces_tracks_queue' ) );
		$this->assertFalse( get_option( 'woocommerce_clear_ces_tracks_queue_for_page' ) );
		$this->assertFalse( get_option( 'wc_remote_inbox_notifications_stored_state' ) );
		$this->assertFalse( get_option( 'wc_remote_inbox_notifications_specs' ) );
		$this->assertFalse( get_option( 'wc_remote_inbox_notifications_wca_updated' ) );
		$this->assertFalse( get_option( 'woocommerce_admin_install_timestamp' ) );
	}
}
