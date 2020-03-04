<?php
/**
 * Uninstall
 *
 * Deletes the rates table
 */
if ( ! defined('WP_UNINSTALL_PLUGIN') ) exit();
/*
global $wpdb;

// Tables
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}woocommerce_devvn_district_shipping_rates");
delete_option( 'devvn_woo_district' );