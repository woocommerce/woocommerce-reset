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
	 * Test deleting single option.
	 */
	public function test_delete_options_single() {
		update_option( 'foo', 'testing' );

		delete_options( 'foo' );

		$this->assertFalse( get_option( 'foo' ) );
	}

	/**
	 * Test deleting multiple options.
	 */
	public function test_delete_options_multi() {
		update_option( 'foo', 'testing' );
		update_option( 'bar', 'testing' );
		update_option( 'baz', 'testing' );

		delete_options( 'foo', 'bar', 'baz' );

		$this->assertFalse( get_option( 'foo' ) );
		$this->assertFalse( get_option( 'bar' ) );
		$this->assertFalse( get_option( 'baz' ) );
	}

}
