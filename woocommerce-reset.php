<?php
/**
 * Plugin Name:     WooCommerce Reset
 * Plugin URI:      https://github.com/woocommerce/woocommerce-reset
 * Description:     A developer tool for WooCommerce provding REST API endpoints to reset WooCommerce data.
 * Author:          WooCommerce
 * Author URI:      https://woocommerce.com/
 * Text Domain:     woocommerce-reset
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         WooCommerce_Reset
 */

namespace Automattic\WooCommerce\Reset;

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'woocommerce-reset/v1',
			'/reset',
			array(
				'methods'             => 'POST',
				'callback'            => __NAMESPACE__ . '\\handle_reset_route',
				'permission_callback' => '__return_true',
			)
		);
	}
);

/**
 * Handle the POST woocommerce-reset/v1/reset route.
 */
function handle_reset_route() {
	/*
	 * Delete options, rather than reset them to another value. This allow their
	 * default value to be assigned when the option is next retrieved by the site.
	 */
	delete_options();
}

/**
 * Delete WooCommerce options.
 *
 * Presently, this is limited to options manged by WC Admin.
 */
function delete_options() {
	$options = array(
		'woocommerce_allow_tracking',
		'woocommerce_onboarding_profile',
		'woocommerce_task_list_welcome_modal_dismissed',
		'woocommerce_task_list_tracked_completed_tasks',
		'woocommerce_ces_tracks_queue',
		'woocommerce_clear_ces_tracks_queue_for_page',
		'wc_remote_inbox_notifications_stored_state',
		'wc_remote_inbox_notifications_specs',
		'wc_remote_inbox_notifications_wca_updated',
		'woocommerce_admin_install_timestamp',
	);

	array_walk(
		$options,
		function( $name ) {
			delete_option( $name );
		}
	);
}
