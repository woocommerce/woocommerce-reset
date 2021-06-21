<?php
/**
 * PHPUnit bootstrap file
 *
 * @package WooCommerce_Reset
 */

// Composer autoloader must be loaded before WP_PHPUNIT__DIR will be available.
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Give access to tests_add_filter() function.
require_once getenv( 'WP_PHPUNIT__DIR' ) . '/includes/functions.php';

// Activate plugins.
tests_add_filter(
	'muplugins_loaded',
	function() {
		require dirname( __DIR__ ) . '/woocommerce-reset.php';
		require dirname( __DIR__ ) . '/../woocommerce/woocommerce.php';
	}
);

// Start up the WP testing environment.
require getenv( 'WP_PHPUNIT__DIR' ) . '/includes/bootstrap.php';
