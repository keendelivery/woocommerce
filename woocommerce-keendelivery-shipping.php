<?php
/**
 * Plugin Name: KeenDelivery - WooCommerce Shipping plugin
 * Plugin URI: http://www.keendelivery.nl/
 * Description: Een plugin van KeenDelivery voor WooCommerce waarmee op eenvoudige wijze labels aan kunt maken voor uw zendingen.
 * Version: 1.0.7
 * Author: KeenDelivery BV
 * Author URI: https://www.keendelivery.com
 * Copyright: © KeenDelivery
 *
 * WC requires at least: 3.0
 * WC tested up to: 4.0
 */


/**
 * Security Note
 */
defined( 'ABSPATH' ) or die( "Sorry, no access..." );

define( 'KEENDELIVERY_VERSION', '1.0.7' );


// Load file for general KeenDelivery functions
require_once ('woocommerce-keendelivery-functions.php');

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || is_network_woocommerce_active() ) {

	/**
	 * Class KeenDelivery
	 */
	class KeenDelivery {

		/**
		 * KeenDelivery constructor.
		 */
		public function __construct() {
			$this->includes();
			add_filter( 'woocommerce_email_classes',array( $this, 'add_keendelivery_tracktrace_woocommerce_email' ) );


		}


		/**
		 * All include files
		 */
		private function includes() {
			include_once( 'class-keendelivery-orders.php' );
			include_once( 'class-keendelivery-settings.php' );

		}

		/**
		 * @param $email_classes
		 *
		 * @return mixed
		 */
		function add_keendelivery_tracktrace_woocommerce_email( $email_classes ) {

			// include our custom email class
			require_once( 'class-keendelivery-tracktrace-email.php' );

			// add the email class to the list of email classes that WooCommerce loads
			$email_classes['WC_KeenDelivery_TraceTrace_Email'] = new WC_KeenDelivery_TraceTrace_Email();

			return $email_classes;

		}



	}



	// Let's start the KeenDelivery plugin...
	$wpKeenDelivery = new KeenDelivery();



}

