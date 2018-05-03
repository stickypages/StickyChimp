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

add_action('admin_menu', 'sticky_chimp_admin_actions');
function sticky_chimp_admin_actions() {
	add_options_page("Sticky", "Sticky", 1, "sticky", "sticky_options_page_func" );
}
function sticky_options_page_func() {
	global $wpdb;

	include 'sticky-options-admin.php';
}