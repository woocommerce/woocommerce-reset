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

/**
 * Array of WP Options names used by WooCommerce.
 *
 * Presently, this is limited to options manged by WC Admin.
 */
const WOOCOMMERCE_OPTIONS = array(
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

const WOOCOMMERCE_ADMIN_NOTE_TABLES = array(
    'wc_admin_notes',
    'wc_admin_note_actions',
);

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'woocommerce-reset/v1',
			'/state',
			array(
				'methods'             => 'DELETE',
				'callback'            => __NAMESPACE__ . '\\handle_delete_state_route',
				'permission_callback' => '__return_true',
			)
		);
		register_rest_route(
			'woocommerce-reset/v1',
			'cron/run',
			array(
				'callback' => __NAMESPACE__ . '\\run_cron',
				'methods'  => 'POST',
			)
		);
        register_rest_route(
			'woocommerce-reset/v1',
			'/notes',
			array(
				'methods'             => 'DELETE',
				'callback'            => __NAMESPACE__ . '\\truncate_note_tables',
				'permission_callback' => '__return_true',
			)
		);

	}
);

/**
 * Handle the DELETE woocommerce-reset/v1/state route.
 */
function handle_delete_state_route() {
	/*
	 * Delete options, rather than reset them to another value. This allow their
	 * default value to be assigned when the option is next retrieved by the site.
	 */
	delete_options( ...WOOCOMMERCE_OPTIONS );
	delete_all_transients();
}


/**
 * Handle the DELETE woocommerce-reset/v1/notes route.
 */
function truncate_note_tables() {
    global $wpdb;
    foreach ( WOOCOMMERCE_ADMIN_NOTE_TABLES as noteTable ) {
      $table  = $wpdb->prefix . noteTable;
      $delete = $wpdb->query("TRUNCATE TABLE " . $table );
    }
}

/**
 * Delete WooCommerce options.
 *
 * @param string ...$option_names Names of options to delete.
 */
function delete_options( string ...$option_names ) {
	array_walk( $option_names, 'delete_option' );
}

/**
 * Deletes all transients stored in the database.
 */
function delete_all_transients() {
	global $wpdb;
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_%' " );
	wp_cache_flush(); // Manually flush the cache after direct database call.
}

/** 
 * Runs the action scheduler.
 */
function run_cron() {
	do_action( 'action_scheduler_run_queue', 'Async Request' );
}