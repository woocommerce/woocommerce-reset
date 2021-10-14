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
				'methods' => 'DELETE',
				'callback' => __NAMESPACE__ . '\\handle_delete_state_route',
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
				'methods' => 'DELETE',
				'callback' => __NAMESPACE__ . '\\truncate_note_tables',
				'permission_callback' => '__return_true',
			)
		);
		register_rest_route(
			'woocommerce-reset/v1',
			'/action-scheduler/run',
			array(
				'methods' => 'POST',
				'callback' => __NAMESPACE__ . '\\run_action_scheduler',
				'permission_callback' => '__return_true',
			)
		);
		register_rest_route(
			'woocommerce-reset/v1',
			'cron/list',
			array(
				'callback' => __NAMESPACE__ . '\\get_cron_list',
				'methods' => 'GET',
			)
		);
		register_rest_route(
			'woocommerce-reset/v1',
			'/cron/run',
			array(
				'methods' => 'POST',
				'callback' => __NAMESPACE__ . '\\run_cron_job',
				'args' => array(
					'hook' => array(
						'description' => 'Name of the cron that will be triggered.',
						'type' => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

	}
);

/**
 * Handle the DELETE woocommerce-reset/v1/state route.
 */
function handle_delete_state_route()
{
	/*
	 * Delete options, rather than reset them to another value. This allow their
	 * default value to be assigned when the option is next retrieved by the site.
	 */
	$options = delete_options(...WOOCOMMERCE_OPTIONS);
	$transients = delete_all_transients();
	$notes = truncate_note_tables();
	$general_settings = reset_settings('general');
	$products_settings = reset_settings('products');
	$tax_settings = reset_settings('tax');
	run_cron_job_by_hook('wc_admin_daily');

	return array(
		'options' => $options,
		'transients' => $transients,
		'notes' => $notes,
		'settings' => array(
			'general' => $general_settings,
			'products' => $products_settings,
			'tax' => $tax_settings
		)
	);
}


/**
 * Handle the DELETE woocommerce-reset/v1/notes route.
 */
function truncate_note_tables()
{
	global $wpdb;
	$note_tables = WOOCOMMERCE_ADMIN_NOTE_TABLES;
	$success = true;
	foreach ($note_tables as $note_table) {
		$table = $wpdb->prefix . $note_table;
		$result = $wpdb->query("TRUNCATE TABLE " . $table);
		if ($success && ! $result ) {
			$success = false;
		}
	}
	return $success;
}

/**
 * Delete WooCommerce options.
 *
 * @param string ...$option_names Names of options to delete.
 * @return boolean on success or failure.
 */
function delete_options(string ...$option_names)
{
	return array_walk($option_names, 'delete_option');
}

/**
 * Deletes all transients stored in the database.
 */
function delete_all_transients()
{
	global $wpdb;
	$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_%' ");
	wp_cache_flush(); // Manually flush the cache after direct database call.
	return $wpdb->rows_affected > 0;
}

/** 
 * Resets a particular settings_group.
*/
function reset_settings($settings_group)
{
	$request = new \WP_REST_Request('GET', '/wc/v3/settings/' . $settings_group);
	$response = rest_do_request($request);

	if (is_wp_error($response)) {
		return false;
	}

	$data = $response->get_data();
	$success = true;

	foreach ($data as $setting) {
		// The rest api doesn't allow selects to be set to ''.
		if (($setting['type'] == 'select' && '' === $setting['default']) || $setting['default'] === $setting['value']) {
			continue;
		}

		$request = new \WP_REST_Request('PUT', sprintf('/wc/v3/settings/%s/%s', $settings_group, $setting['id']));
		$request->set_body_params(
			array(
				'value' => $setting['default'],
			)
		);
		$response = rest_do_request($request);
		if ($success && 200 !== $response->status) {
			$success = false;
		}
	}
	return $success;
}

/** 
 * Runs the action scheduler.
 */
function run_cron() {
	do_action( 'action_scheduler_run_queue', 'Async Request' );
}
/*
 * Handle the POST woocommerce-reset/v1/action-scheduler/run route.
 */
function run_action_scheduler()
{
	if (class_exists('ActionScheduler')) {
		ActionScheduler::runner()->run();
	}
}

/**
 * Handle the GET woocommerce-reset/v1/cron/list route.
 */
function get_cron_list()
{
	$crons = _get_cron_array();
	$events = array();

	if (empty($crons)) {
		return array();
	}

	foreach ($crons as $cron) {
		foreach ($cron as $hook => $data) {
			$events[] = $hook;
		}
	}
	return $events;
}

/**
 * Handle the POST woocommerce-reset/v1/cron/run route.
 */
function run_cron_job($request)
{
	run_cron_job_by_hook($request->get_param('hook'));
}

function run_cron_job_by_hook($hook)
{
	if (!isset($hook)) {
		return;
	}

	$crons = _get_cron_array();
	foreach ($crons as $cron) {
		if (isset($cron[$hook])) {
			$cron_signature = current($cron[$hook]);
			$args = $cron_signature['args'];
			delete_transient('doing_cron');
			$scheduled = schedule_event($hook, $args);

			if (false === $scheduled) {
				return $scheduled;
			}

			add_filter('cron_request', function (array $cron_request) {
				$cron_request['url'] = add_query_arg('run-cron', 1, $cron_request['url']);
				return $cron_request;
			});

			spawn_cron();
			sleep(1);
			return true;
		}
	}
	return false;
}

function schedule_event($hook, $args = array())
{
	$event = (object)array(
		'hook' => $hook,
		'timestamp' => 1,
		'schedule' => false,
		'args' => $args,
	);
	$crons = (array)_get_cron_array();
	$key = md5(serialize($event->args));

	$crons[$event->timestamp][$event->hook][$key] = array(
		'schedule' => $event->schedule,
		'args' => $event->args,
	);
	uksort($crons, 'strnatcasecmp');
	return _set_cron_array($crons);
}
