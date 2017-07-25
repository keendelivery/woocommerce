<?php
/*
	Plugin Name: KeenDelivery - WooCommerce Shipping plugin
	Plugin URI: http://www.keendelivery.nl/
	Description: Een plugin van KeenDelivery voor WooCommerce waarmee op handige wijze gecommuniceerd kan worden met de KeenPortal.
	Version: 1.0
	Author: KeenDelivery
	Author URI: http://www.keendelivery.nl

	Copyright: © KeenDelivery
*/


/**
 * Security Note
 */
defined( 'ABSPATH' ) or die( "Sorry, no access..." );

define( 'KEENDELIVERY_VERSION', '1.0' );


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

