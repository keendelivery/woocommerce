<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // If accessed directly, then deny


/**
 * General function that allows you to retrieve the KeenDelivery track and trace info, wherever you are...
 *
 * @param $order_id
 *
 * @return bool|mixed
 */
function get_jet_track_and_trace_by_order_id( $order_id ) {
	$track_trace = get_post_meta( $order_id, '_keendelivery_jet_track_trace', true );
	if ( isset( $track_trace ) && is_array( $track_trace ) && count( $track_trace ) > 0 ) {
		return $track_trace;
	} else {
		return false;
	}

}

/**
 * Check if WooCommerce is active on WP Networking
 *
 * @return bool
 */
function is_network_woocommerce_active() {
	if ( ! is_multisite() ) {
		return false;
	}

	$plugins = get_site_option( 'active_sitewide_plugins' );
	if ( isset( $plugins['woocommerce/woocommerce.php'] ) ) {
		return true;
	}

	return false;
}


