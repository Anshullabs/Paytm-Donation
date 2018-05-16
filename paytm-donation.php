<?php
/*
 * Plugin Name: Paytm Donation
 * Plugin URI: #
 * Description: Use [paytm-donation] Shortcode to show donation form. 
 * Author: Anshul G
 * Author URI: http://anshullabs.xyz
 * Network: True
 * Version: 1.0
 */

/**
 * @package : Paytm Donation
 * @author  : Anshul G.
 * @version : 1.0
 */

/*
|---------------------------------------------------------------------------------------------------
| Plugin Activation Hook
|---------------------------------------------------------------------------------------------------
*/
register_activation_hook(__FILE__, 'pd_plugin_activation');
function pd_plugin_activation() {
	global $wpdb;
	$settings = pd_paytm_settings_list();
		foreach ($settings as $setting) {
		add_option($setting['name'], $setting['value']);
	}
	
	$table_name = $wpdb->prefix . "paytm_donations";
    $sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `order_id` varchar(255) CHARACTER SET utf8 NOT NULL,
        `name` varchar(255) CHARACTER SET utf8 NOT NULL,
        `phone` varchar(255) NOT NULL,
        `email` varchar(255) NOT NULL,
		`address` varchar(255) CHARACTER SET utf8 NOT NULL,
        `city` varchar(255) CHARACTER SET utf8 NOT NULL,
        `state` varchar(255) CHARACTER SET utf8 NOT NULL,
        `zip` varchar(255) CHARACTER SET utf8 NOT NULL,
        `country` varchar(255) CHARACTER SET utf8 NOT NULL,
        `amount` varchar(255) NOT NULL,
        `pan_no` varchar(255) NOT NULL,
        `post_id` text NOT NULL,
        `payment_status` varchar(255) NOT NULL,
        `payment_method` varchar(255) NOT NULL,
        `date` datetime NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `id` (`id`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta($sql);
}


/*
|---------------------------------------------------------------------------------------------------
| Plugin Deactivation Hook
|---------------------------------------------------------------------------------------------------
*/
register_deactivation_hook(__FILE__, 'pd_plugin_deactivation');
function pd_plugin_deactivation() {	
	$settings = pd_paytm_settings_list();
	foreach ($settings as $setting) {
		delete_option($setting['name']);
	}
}


/*
|---------------------------------------------------------------------------------------------------
| Include Classes Files
|---------------------------------------------------------------------------------------------------
*/
require_once dirname( __FILE__ ) . '/inc/encdec_paytm.php';
require_once dirname( __FILE__ ) . '/inc/donation-checkout.php';
require_once dirname( __FILE__ ) . '/inc/check-donation-response.php';
require_once dirname( __FILE__ ) . '/shortcode/donateform-shortcode.php';
require_once dirname( __FILE__ ) . '/admin/paytm-donation-listing-class.php';



/*
|---------------------------------------------------------------------------------------------------
| Plugin enqueue  admin style and script
|---------------------------------------------------------------------------------------------------
*/
add_action('admin_enqueue_scripts', 'pd_enqueue_scripts_callback');
add_action('wp_enqueue_scripts', 'pd_enqueue_scripts_callback');
function pd_enqueue_scripts_callback() {
	wp_enqueue_style( 'pd-bootstrap', plugins_url('css/pd-bootstrap.css',__FILE__));
}


/*
|---------------------------------------------------------------------------------------------------
| Add Admin Menu
|---------------------------------------------------------------------------------------------------
*/
add_action( 'admin_menu', 'pd_add_adminMenu' );
function pd_add_adminMenu(){
	add_menu_page(
				'Paytm Donation Listings', // page_title
				'Paytm Donation', // menu_title
				'manage_options', // capability
				'paytm-donation-listing', // menu_slug
				'pd_paytm_donation_list_page', // function
				'dashicons-vault', // icon_url
				80 // position
			);
	add_submenu_page(
				'paytm-donation-listing', // parent menu slug
				'Paytm Donation Settings', // page_title
				'Paytm Settings', // menu_title
				'manage_options', // capability
				'paytm-donation-settings', // menu_slug
				'pd_paytm_donation_setting_page' // function				
			);
}


/*
|---------------------------------------------------------------------------------------------------
| Admin Menu Pages Callback
|---------------------------------------------------------------------------------------------------
*/
function pd_paytm_donation_list_page(){
	require_once dirname( __FILE__ ) . '/admin/paytm-donation-listings.php';
}

function pd_paytm_donation_setting_page(){
	require_once dirname( __FILE__ ) . '/admin/admin-settings.php';
}


/*
|---------------------------------------------------------------------------------------------------
| Register Setting Page Firlds
|---------------------------------------------------------------------------------------------------
*/
add_action( 'admin_init', 'pd_paytm_register_settings' );
function pd_paytm_register_settings() {
	$settings = pd_paytm_settings_list();
	foreach ($settings as $setting) {
		register_setting($setting['name'], $setting['value']);
	}
}


/*
|---------------------------------------------------------------------------------------------------
| Plugin Setting Page Firlds
|---------------------------------------------------------------------------------------------------
*/
if ( !function_exists('pd_paytm_settings_list')) {
	
	function pd_paytm_settings_list(){
		$settings = array(
			array(
				'display' => 'Merchant ID',
				'name'    => 'paytm_merchant_id',
				'value'   => '',
				'type'    => 'text',
	    		'hint'    => 'Merchant ID'
			),
			array(
				'display' => 'Merchant Key',
				'name'    => 'paytm_merchant_key',
				'value'   => '',
				'type'    => 'text',
				'hint'    => 'Merchant key'
			),
			array(
				'display' => 'Website',
				'name'    => 'paytm_website',
				'value'   => '',
				'type'    => 'text',
	    		'hint'    => 'Website Provided by Paytm'
			),
			array(
				'display' => 'Industry Type',
				'name'    => 'paytm_industry_type_id',
				'value'   => '',
				'type'    => 'text',
	    		'hint'    => 'Industry Type ID'
			),
			array(
				'display' => 'Channel ID',
				'name'    => 'paytm_channel_id',
				'value'   => '',
				'type'    => 'text',
	    		'hint'    => 'Channel ID e.g. WEB/WAP'
			),
			array(
				'display' => 'Mode',
				'name'    => 'paytm_mode',
				'value'   => 'TEST',
				'values'  => array('TEST'=>'TEST','LIVE'=>'LIVE'),
				'type'    => 'select',
	    		'hint'    => 'Change the mode of the payments'
			),			
			array(
				'display' => 'Default Amount',
				'name'    => 'paytm_amount',
				'value'   => '100',
				'type'    => 'number',
	     		'hint'    => 'the default donation amount, WITHOUT currency signs -- ie. 100'
			),
			array(
				'display' => 'Default Button/Link Text',
				'name'    => 'paytm_content',
				'value'   => 'Donate By Paytm',
				'type'    => 'text',
	    		'hint'    => 'the default text to be used for buttons or links if none is provided'
			),
			array(
				'display' => 'Set CallBack URL',	
				'name'    => 'paytm_callback',
				'value'   => 'YES',
				'values'  => array('YES'=>'YES','NO'=>'NO'),
				'type'    => 'select',
				'hint'    => 'Select No to disable CallBack URL'
			)
		);
		return $settings;
	}
}


