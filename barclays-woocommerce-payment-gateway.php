<?php

/**
 * Plugin Name: Barclays WooCommerce Payment Gateway
 * Plugin URI: www.c-metric.com
 * Description: Barclays WooCommerce Payment Gateway to WooCommerce shop website.
 * Author: Rupesh Jorkar (RJ)
 * Author URI: 
 * Version: 1.1
 * License: 
 * License URI: 
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! defined( 'BarclaysWooCommerce_DB_VERSION' ) ) {
      define( 'BarclaysWooCommerce_DB_VERSION', '1.1' );
	define( 'BarclaysWooCommerce_PLUGIN_DIR', dirname( __FILE__ ) );
}  

//update plugin code start
require 'plugin-update-checker/plugin-update-checker.php';

$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'http://c-metric.net/plugins/barclaysWooCommercePaymentGateway/barclaysWooCommerce.json',
	__FILE__,
	'barclaysWooCommercePaymentGateway-c-metric'
);
$GLOBALS['wc_cybersource_barclays'] = new WC_Cybersource_Barclays();


class WC_Cybersource_Barclays
{


	const GATEWAY_CLASS_NAME = 'WC_Gateway_Cybersource_Barclays';

	const GATEWAY_ID = 'cybersource_barclays';	
	const TEXT_DOMAIN = 'wc-cybersource';	

	private $plugin_path;
	private $plugin_url;

	private $logger;
	private $response_url;

	/**
	 * Initialize the main plugin class
	 */
	function __construct()
	{

		// Load the gateway
		add_action( 'plugins_loaded', array( $this, 'load_classes' ) );
		
		// add a 'Configure' link to the plugin action links
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_manage_link' ), 10, 4 );    // remember, __FILE__ derefs symlinks :(

		add_action( 'init', array( $this, 'load_translation' ) );

		if ( is_admin() && ! defined( 'DOING_AJAX' ) )
		{

			//add_action( 'admin_notices', array( $this, 'check_ssl' ) );

			// order admin link to cybersource transaction
			add_action( 'woocommerce_order_actions',       array( $this, 'order_meta_box_transaction_link' ) );
			add_action( 'woocommerce_order_actions_start', array( $this, 'order_meta_box_transaction_link' ) );

		}

		
		// Unhook Woocommerce email notifications
		add_action( 'woocommerce_email', array( $this, 'unhook_email_notifications' ) );
	
		$this->response_url = add_query_arg( 'wc-api', 'wc_gateway_cybersource_barclays_response', home_url( '/' ) );

		if ( is_ssl() || 'yes' == get_option( 'woocommerce_force_ssl_checkout' ) )
		{
			$this->response_url = str_replace( 'http:', 'https:', $this->response_url );
		}
		add_action( 'wp_enqueue_scripts', array($this,'barclays_enqueue_scripts') );
			add_action( 'admin_enqueue_scripts', array($this,'admin_barclays_enqueue_scripts') );
		// Payment listener/API hook
		add_action( 'woocommerce_api_wc_gateway_cybersource_barclays_response', array( $this, 'cybersource_relay_response' ) );
	}


	public function load_classes()
	{
	    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	     if ( is_admin() && current_user_can( 'activate_plugins' ) && ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            add_action( 'admin_notices', array( $this,'woocommerce_barclays_activation_notice') );

            deactivate_plugins( BarclaysWooCommerce_PLUGIN_DIR , true);

            if ( isset( $_GET['activate'] ) ) {
              unset( $_GET['activate'] );
            }
             return false;
          }else{
                
                 /* automatic page create thank you page code start here */
                $new_page_title1 = 'Thank you';
                $new_page_content1 = '';
                $new_page_template1 = 'thankyou-response-template.php'; //ex. template-custom.php. Leave blank if you don't want a custom page template.
              
                        
                $page_check1 = get_page_by_title($new_page_title1);
                $new_page1 = array(
                    'post_type' => 'page',
                    'post_title' => $new_page_title1,
                    'post_content' => $new_page_content1,
                    'post_status' => 'publish',
                    'post_author' => 1,
                );
                if(!isset($page_check1->ID)){
                    $new_page_id1 = wp_insert_post($new_page1);
                    if(!empty($new_page_template1)){
                        update_post_meta($new_page_id1, '_wp_page_template', $new_page_template1);
                    }
                }
            /* automatic page create thank you page code end here */
            
                // CyberSource Barclays gateway
	        	require_once( 'includes/class-wc-gateway-barclays.php' );
                require_once('template/templateinit.php');
                  
	        	// Add class to WC Payment Methods
	        	add_filter( 'woocommerce_payment_gateways', array( $this, 'load_gateway' ) );
             	add_action('init',array('PageTemplater','get_instance'));
             	
             	
          }
       
		
	}

	/**
	 * Adds gateway to the list of available payment gateways
	 *
	 * @param array $gateways array of gateway names or objects
	 * @return array $gateways array of gateway names or objects
	 */
	public function load_gateway( $gateways )
	{

		$gateways[] = self::GATEWAY_CLASS_NAME;

		return $gateways;
	}
    public function woocommerce_barclays_activation_notice() {
	

	echo '<div class="error"><p>' . __( '<strong>Activation Error:</strong> You must have the <a href="https://wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a> plugin installed and activated for the BarclaysWooCommercePaymentGateway add-on to activate.',  self::TEXT_DOMAIN ) . '</p></div>';
    }
    
   

	/**
	 * Load the translation so that WPML is supported
	 */
	public function load_translation()
	{
		load_plugin_textdomain( self::TEXT_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}


	public	function barclays_enqueue_scripts(){

		wp_register_style( 'custom-barclays-style', $this->plugin_url() .'/includes/customstyle.css', array(  ) );
    
    wp_enqueue_style( 'custom-barclays-style' );
}
public	function admin_barclays_enqueue_scripts(){

		wp_register_style( 'admin-barclays-style', $this->plugin_url() .'/includes/adminstyle.css', array(  ) );
    
    wp_enqueue_style( 'admin-barclays-style' );
}
	/**
	 * Check if SSL is enabled and notify the admin user.  The gateway can technically still
	 * function without SSL, so this isn't a fatal dependency, not to mention users might
	 * not bother to configure SSL for their test server.
	 */
// 	public function check_ssl()
// 	{
// 		if ( 'yes' != get_option( 'woocommerce_force_ssl_checkout' ) )
// 		{
// 			echo '<div class="error"><p>WooCommerce IS NOT BEING FORCED OVER SSL; YOUR CUSTOMER\'S CREDIT CARD DATA IS AT RISK.</p></div>';
// 		}
// 	}


	/**
	 * Handle the API response call by instantiating the gateway object
	 * and handing off to it
	 */
	public function cybersource_relay_response()
	{
		$wc_gateway_cybersource_barclays = new WC_Gateway_Cybersource_Barclays();
		$wc_gateway_cybersource_barclays->cybersource_response();
	}
	

	
	/**
	 * Add a button to the order actions meta box to view the order in the
	 * CyberSource ebc
	 *
	 * @param int $post_id the order identifier
	 */
	public function order_meta_box_transaction_link( $post_id )
	{
		global $woocommerce, $wc_cybersource_barclays;
		// this action is overloaded
		if ( is_array( $post_id ) ) return $post_id;

		$order = wc_get_order( isset( $order_id ) );

		if ( self::GATEWAY_ID == $order->payment_method )
		{
			$wc_gateway_cybersource_barclays = new WC_Gateway_Cybersource_Barclays();
			$wc_gateway_cybersource_barclays->order_meta_box_transaction_link( $order );
		}
	}

	/**
	 * Logs $message using the woocommerce logging facility
	 *
	 * @param string $message the string to log
	 */
	public function log( $message )
	{
		global $woocommerce;
		
		if ( ! is_object( $this->logger ) )
		{

			$this->logger = new WC_Logger();

			$this->logger->add( self::GATEWAY_ID, $message );
		}
	}


	public function plugin_path()
	{
		if ( is_null( $this->plugin_path ) ) $this->plugin_path = plugin_dir_path( __FILE__ );

		return $this->plugin_path;
	}

	public function plugin_url()
	{
		if ( is_null( $this->plugin_url ) ) $this->plugin_url = untrailingslashit( plugins_url( '/', __FILE__ ) );

		return $this->plugin_url;
	}

	/**
	 * Return the plugin action links.  This will only be called if the plugin
	 * is active.
	 *
	 * @param array $actions associative array of action names to anchor tags
	 * @param string $plugin_file plugin file name, ie my-plugin/my-plugin.php
	 * @param array $plugin_data associative array of plugin data from the plugin file headers
	 * @param string $context plugin status context, ie 'all', 'active', 'inactive', 'recently_active'
	 *
	 * @return array associative array of plugin action links
	 */
	public function plugin_manage_link( $actions, $plugin_file, $plugin_data, $context )
	{
		// add a 'Configure' link to the front of the actions list for this plugin
		if ( version_compare( WOOCOMMERCE_VERSION, "2.1" ) <= 0 )
		{
			return array_merge( array( 'configure' => '<a href="' . admin_url( 'admin.php?page=woocommerce_settings&tab=payment_gateways&section=' . self::GATEWAY_CLASS_NAME ) . '">' . __( 'Configure', self::TEXT_DOMAIN ) . '</a>' ),
								$actions );
		}else{
			return array_merge( array( 'configure' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . self::GATEWAY_CLASS_NAME ) . '">' . __( 'Configure', self::TEXT_DOMAIN ) . '</a>' ),
								$actions );
		}
	}

	/**
	 * Gets the receipt response URL
	 *
	 * @return string receipt response URL
	 */
	public function get_response_url()
	{
		return $this->response_url;
	}
	
	
	/** Default Email Notification Override **********************************/
	
	public function unhook_email_notifications( $email_class )
	{
 
		/**
		 * Hooks for sending emails during store events
		 **/
	//	remove_action( 'woocommerce_low_stock_notification', array( $email_class, 'low_stock' ) );
	//	remove_action( 'woocommerce_no_stock_notification', array( $email_class, 'no_stock' ) );
	//	remove_action( 'woocommerce_product_on_backorder_notification', array( $email_class, 'backorder' ) );
		
		// New order emails
		remove_action( 'woocommerce_order_status_pending_to_processing_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
		remove_action( 'woocommerce_order_status_pending_to_completed_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
		remove_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
		remove_action( 'woocommerce_order_status_failed_to_processing_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
		remove_action( 'woocommerce_order_status_failed_to_completed_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
		remove_action( 'woocommerce_order_status_failed_to_on-hold_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
		
		// Processing order emails
		remove_action( 'woocommerce_order_status_pending_to_processing_notification', array( $email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger' ) );
		remove_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger' ) );
		
		// Completed order emails
		remove_action( 'woocommerce_order_status_completed_notification', array( $email_class->emails['WC_Email_Customer_Completed_Order'], 'trigger' ) );
			
		// Note emails
		remove_action( 'woocommerce_new_customer_note_notification', array( $email_class->emails['WC_Email_Customer_Note'], 'trigger' ) );
	}
}
