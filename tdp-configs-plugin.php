<?php

/**
 * Plugin Name: tdp-configs
 * Version: 1.0
 */

require_once dirname(__FILE__) . '/tdp-common-configs.php';

date_default_timezone_set('Europe/Copenhagen');

// Remove dashicons in frontend for unauthenticated users
add_action('wp_enqueue_scripts', 'bs_dequeue_dashicons');
function bs_dequeue_dashicons()
{
    if (!is_user_logged_in()) {
        wp_deregister_style('dashicons');
    }
}


function modify_archive_query($query)
{
    // Check if we are on the front end and if the main query is being modified
    if (!is_admin() && $query->is_main_query()) {
        // Target a specific archive page, e.g., a custom post type archive
        if ($query->is_post_type_archive('gd_place')) {
            // Extract the geolocation ID from the URL
            $geolocation_id = extract_geolocation_id_via_url_seo_text_configs();
            global $wp;
            $current_url = add_query_arg(array(), $wp->request);
            // $special_location = get_post_meta($current_geolocation_id, 'special_location', true);
            if ($current_url == "lokation") {
                $geolocation_id = 29783; //set geolocation id to Denmark
            }

            // Assume this function returns an array of post IDs
            $gd_place_list_combined = get_post_meta($geolocation_id, 'archive_gd_place_list', false);
            if (!empty($gd_place_list_combined)) {
                //get the ids from the gd_place_list_combined array
                $gd_place_list_combined = array_map(function ($post) {
                    return $post['ID'];
                }, $gd_place_list_combined);
            }

            // Set the post__in parameter for the main query
            if (!empty($gd_place_list_combined)) {
                $query->set('post__in', array_values($gd_place_list_combined));
                $query->set('orderby', 'post__in');
            } else {
                $query->set('post__in', array(0));
            }
        }
    }
}

add_action('pre_get_posts', 'modify_archive_query', 1);


//create custom query for "depotrum" page
add_action('elementor/query/depotrum_page_query', function ($query) {
    $geolocation_id = 17921; //set geolocation id to Denmark (tjekdepot.local)
    $geolocation_id = 29783; //set geolocation id to Denmark

    $gd_place_list_combined = get_post_meta($geolocation_id, 'archive_gd_place_list', false);
    if (!empty($gd_place_list_combined)) {
        // extract the ids from the gd_place_list_combined array
        $gd_place_list_combined = array_map(function ($post) {
            return $post['ID'];
        }, $gd_place_list_combined);

        $gd_place_list_combined = array_map('intval', $gd_place_list_combined);
    }

    $query->set('post_type', 'gd_place');
    $query->set('post__in', $gd_place_list_combined);
    $query->set('orderby', 'post__in');
    $query->set('posts_per_page', 6);
});

function handle_javascript_error()
{
    $_POST['error_message'] = $_POST['error_message'];

    if (!isset($_POST['error_message'])) {
        // Handle the case where the first name is not set (redirect or display an error message)
        trigger_error('Javascript Error Handler: Error message is not set.', E_USER_ERROR);
    } else if ($_POST['error_message'] == "no_booking_link") {
        trigger_error('Javascript Error Handler: No booking link found, unit_id: ' . $_POST['unit_id'], E_USER_ERROR);
    } else if ($_POST['error_message'] == "undefined_booking_error") {
        trigger_error('Javascript Error Handler: Undefined booking error, unit_id: ' . $_POST['unit_id'], E_USER_ERROR);
    }
    exit();
}

add_action('wp_ajax_nopriv_javascript_error_action', 'handle_javascript_error');
add_action('wp_ajax_javascript_error_action', 'handle_javascript_error');
