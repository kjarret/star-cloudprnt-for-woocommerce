<?php
	/**
	 * Plugin Name: Star CloudPRNT for WooCommerce
	 * Plugin URI: http://www.star-emea.com
	 * Description: Star CloudPRNT for WooCommerce enables cloud printing technology with your Star Receipt printer.
	 * Version: 2.0.0 beta
	 * Author: lawrenceowen, athompson1, gcubero, fmahmood
	 * Author URI: http://www.star-emea.com/support
	 * Requires at least: 5.0.0
	 * Tested up to: 5.6.0
	 */
	 
	// Block direct access to this script
	if (!defined( 'ABSPATH' )) exit;
	
	// Include printer files
	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') include_once(plugin_dir_path(__FILE__).'cloudprnt\\printer.inc.php');
	else include_once(plugin_dir_path(__FILE__).'cloudprnt/printer.inc.php');
	include_once(plugin_dir_path(__FILE__).star_cloudprnt_get_os_path('cloudprnt/printer_star_line.inc.php'));
	include_once(plugin_dir_path(__FILE__).star_cloudprnt_get_os_path('cloudprnt/printer_text_plain.inc.php'));
	include_once(plugin_dir_path(__FILE__).star_cloudprnt_get_os_path('cloudprnt/printer_star_prnt.inc.php'));
	
	// Include plugin page settings and woo commerce hooks
	include_once(plugin_dir_path(__FILE__).star_cloudprnt_get_os_path('create-settings.php'));
	include_once(plugin_dir_path(__FILE__).star_cloudprnt_get_os_path('order-handler.php'));
	
	// Run page setup and woo commerce hooks
	star_cloudprnt_create_settings_page();
	star_cloudprnt_setup_order_handler();
?>