<?php
/*
 * Plugin Name: Sticky Chimp
 * Plugin URI: https://stickytheme.com
 * Author: StickyPages
 * Author URI: https://stickypages.ca
 * Version: 1.0
 * Description: A director class for MailChimp connection/sync
 *
 */

include_once 'inc/helpers.php';
if($sticky_chimp_api_key = get_option("sticky_chimp_api_key")) {
	include_once 'inc/classes/StickyChimp.php';
	$StickyChimp = new StickyChimp($sticky_chimp_api_key);
}
include_once 'inc/action_filters.php';




/**
 * Create Admin Page!
 */
add_action('admin_menu', function() {
	add_options_page("StickyChimp", "StickyChimp", 1, "sticky-chimp", "sticky_options_page_func" );
});
function sticky_options_page_func() {
	global $wpdb;

	include_once 'inc/helpers.php';
	include_once 'inc/action_filters.php';
	include 'views/sticky-options-admin.php';
}