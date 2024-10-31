<?php
/*
Plugin Name: Recruitly Wordpress Plugin
Plugin URI: https://recruitly.io
Description: Recruitly job board integration.
Version: 2.0.16
Author: Recruitly
Author URI: https://recruitly.io
License: GNU GENERAL PUBLIC LICENSE
*/
define('RECRUITLY_PLUGIN_VERSION', '2.0.16');

defined('RECRUITLY_POST_TYPE') or define('RECRUITLY_POST_TYPE', 'current-vacancies');

register_activation_hook(__FILE__, 'activate_recruitly_wordpress_plugin');
register_deactivation_hook(__FILE__, 'deactivate_recruitly_wordpress_plugin');
register_uninstall_hook(__FILE__, 'uninstall_recruitly_wordpress_plugin');

define('RECRUITLY_PLUGIN_DIR', untrailingslashit(plugin_dir_path(__FILE__)));
define('RECRUITLY_PLUGIN_URL', untrailingslashit(plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__))));
define('RECRUITLY_CRON_ACTION', 'recruitly_cron');


/**
 * Include dependencies
 */
include(plugin_dir_path(__FILE__) . 'recruitly-wp-templates.php');
include(plugin_dir_path(__FILE__) . 'admin/includes/commons.php');
include(plugin_dir_path(__FILE__) . 'admin/includes/menus.php');
include(plugin_dir_path(__FILE__) . 'admin/includes/customposttypes.php');
include(plugin_dir_path(__FILE__) . 'admin/includes/filters.php');
include(plugin_dir_path(__FILE__) . 'admin/includes/shortcodes.php');
include(plugin_dir_path(__FILE__) . 'admin/includes/taxonomies.php');
include(plugin_dir_path(__FILE__) . 'admin/settings.php');
include(plugin_dir_path(__FILE__) . 'admin/dataloader.php');

function activate_recruitly_wordpress_plugin()
{
    recruitly_wordpress_truncate_post_type();

    delete_option('recruitly_sync_in_progress');

    if (!wp_next_scheduled('recruitly_add_every_ten_minutes_event')) {
        wp_schedule_event(time(), 'every_ten_minutes', 'recruitly_add_every_ten_minutes_event');
    }

}

function deactivate_recruitly_wordpress_plugin()
{
   uninstall_recruitly_wordpress_plugin();
}

function uninstall_recruitly_wordpress_plugin()
{
    try {
        wp_clear_scheduled_hook(RECRUITLY_CRON_ACTION);

        if (wp_next_scheduled('recruitly_add_every_ten_minutes_event')) {
            wp_clear_scheduled_hook('recruitly_add_every_ten_minutes_event');
        }
    } catch (Throwable $ex) {
    }

    try {
        delete_option('recruitly_sync_in_progress');
        delete_option('recruitly_apikey');
    } catch (Throwable $ex) {
    }

    try {
        recruitly_wordpress_truncate_post_type();
    } catch (Throwable $ex) {
    }

    try {
        recruitly_wordpress_delete_taxonomies();
    } catch (Throwable $ex) {
    }

    if (isset($wp_post_types[RECRUITLY_POST_TYPE])) {
        unset($wp_post_types[RECRUITLY_POST_TYPE]);
    }

}

function recruitly_scripts_to_header()
{
    wp_enqueue_script('jquery');
    wp_register_script('featherlight-js', 'https://cdnjs.cloudflare.com/ajax/libs/featherlight/1.7.13/featherlight.min.js', array('jquery'), '', true);
    wp_register_style('featherlight-css', 'https://cdnjs.cloudflare.com/ajax/libs/featherlight/1.7.13/featherlight.min.css', '', '', 'screen');
    wp_enqueue_script('featherlight-js');
    wp_enqueue_style('featherlight-css');
    try {
        wp_enqueue_style('wp-recruitly-form-css', plugins_url('public/css/forms-global.css', __FILE__));
    } catch (Throwable $e) {
    }
}

// Recruitly WP API endpoint to refresh Jobs
function recruitly_enable_jobreload_api()
{

    add_action('rest_api_init', function () {

        // https://example.com/wp-json/recruitly/v2/reloadjobs
        register_rest_route('recruitly/v2', '/reloadjobs', array(
            'methods' => 'GET',
            'callback' => 'recruitly_rest_reloadjobs',
            'permission_callback' => function (WP_REST_Request $request) {
                if (get_option('recruitly_refresh_key', 'HCh5j2X6eNEreM$@!uqCLbT44DPg2Spl') == $request->get_param('refreshkey')) {
                    return true;
                } else {
                    return false;
                }
            }
        ));

    });

}

function recruitly_rest_reloadjobs($request)
{

    update_option('recruitly_rest_jobs_refreshed', time());

    try {
        recruitly_wordpress_insert_post_type($request, false);
    } catch (Throwable $ex) {
        return new WP_REST_Response("ERROR " . $ex->getMessage(), 200);
    }

    return new WP_REST_Response("OK", 200);

}

function recruitly_add_every_ten_minutes($schedules)
{
    $schedules['every_ten_minutes'] = array(
        'interval' => 180,
        'display' => __('Every 10 Minutes', 'recruitly')
    );
    return $schedules;
}

add_action('init', 'recruitly_enable_jobreload_api');

add_action('wp_enqueue_scripts', 'recruitly_scripts_to_header');

add_filter('cron_schedules', 'recruitly_add_every_ten_minutes');

add_action('recruitly_add_every_ten_minutes_event', 'recruitly_wordpress_verify_and_resync');