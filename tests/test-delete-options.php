<?php
/**
 * Class DeleteOptionsTest
 *
 * @package WooCommerce_Reset
 */

namespace Automattic\WooCommerce\Reset;

/**
 * Test case for delete_options.
 */
class DeleteOptionsTest extends \WP_UnitTestCase {

	/**
	 * Test test_delete_options with a data provider.
	 *
	 * @dataProvider get_woocommerce_admin_option_names
	 * @param string $option_name Name of option from data provider.
	 */
	public function test_delete_options( string $option_name ) {
		delete_options();

		$this->assertFalse( get_option( $option_name ) );
	}

	/**
	 * Return array of options managed by WooCommerce Admin.
	 */
	public function get_woocommerce_admin_option_names() : array {
		return array(
			array( 'woocommerce_allow_tracking' ),
			array( 'woocommerce_onboarding_profile' ),
			array( 'woocommerce_task_list_welcome_modal_dismissed' ),
			array( 'woocommerce_task_list_tracked_completed_tasks' ),
			array( 'woocommerce_ces_tracks_queue' ),
			array( 'woocommerce_clear_ces_tracks_queue_for_page' ),
			array( 'wc_remote_inbox_notifications_stored_state' ),
			array( 'wc_remote_inbox_notifications_specs' ),
			array( 'wc_remote_inbox_notifications_wca_updated' ),
			array( 'woocommerce_admin_install_timestamp' ),
		);
	}
}
