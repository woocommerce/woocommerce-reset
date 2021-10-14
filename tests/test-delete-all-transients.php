<?php
/**
 * Class DeleteAllTransientsTest
 *
 * @package WooCommerce_Reset
 */

namespace Automattic\WooCommerce\Reset;

/**
 * Test case for delete_options.
 */
class DeleteAllTransientsTest extends \WP_UnitTestCase {

	/**
	 * Test deleting all transients.
	 */
	public function test_delete_all_transients() {
		set_transient( 'foo', 'foo testing', 60 );
		set_transient( 'bar', 'bar testing', 60 );

		delete_all_transients();

		$this->assertFalse( get_transient( 'foo' ) );
		$this->assertFalse( get_transient( 'bar' ) );
	}

}
