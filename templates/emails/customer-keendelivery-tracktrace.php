<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<?php do_action( 'woocommerce_email_header', $email_heading ); ?>
<p>Uw bestelling wordt verzonden. U kunt uw levering volgen via uw persoonlijke Track & Trace link. Als de link niet werkt, kunt u het adres kopiëren en plaatsen in de adresbalk van uw internet browser: </p>

<p><?php
	$track_trace_info = get_jet_track_and_trace_by_order_id( $order->id );
	if ( $track_trace_info ) :
		foreach ( $track_trace_info as $key => $url ) :
			echo '<a href="' . esc_url( $url ) . '" target="_blank">' . esc_html( $url ) . '</a><br/>';
		endforeach;
	endif;
	?>
</p>

<?php do_action( 'woocommerce_email_footer' ); ?>
