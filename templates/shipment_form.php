<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // If accessed directly, then deny

?>
<form id="keendelivery_shipment_form" method="post">
    <ul class="keendelivery_shipment_form"
        style="<?php echo( ( isset( $is_jet_verzendt_active ) && $is_jet_verzendt_active ) ? '' : 'display: none' ); ?>">

        <li class="wide">

            <div class="message" id="jet_message" style="display: none"></div>

            <div id="shipment_form"></div>
        </li>

        <li class="wide send_actions ">
			<?php if ( isset( $is_batch_order_send ) && $is_batch_order_send ) : ?>
                <button class="button send_order button-primary" id="jet_send_submit"
                        onclick="keendelivery_start_send_orders(); return false">
                    Versturen naar KeenDelivery
                </button>
			<?php else : ?>
                <button class="button send_order button-primary" id="jet_send_submit"
                        onclick="keendelivery_send_order(); return false">
                    Versturen naar KeenDelivery
                </button>
			<?php endif; ?>
            <div class="clearfix"></div>

			<?php if ( ! isset( $is_batch_order_send ) ) : ?>
                <a href="javascript:void(0);" class="keendelivery_small grey" onclick="set_active_keendelivery(false);">Jet
                    Verzendt uitschakelen voor deze
                    bestelling</a>
			<?php endif; ?>
        </li>

        <script>
            jQuery(function () {

                generate_shipment_form('<?php
	                $postdata = get_option( 'jet_post_data_shipment_formdata' );
                    echo $postdata;
                    ?>');

            })
        </script>


    </ul>

    <ul class="keendelivery_no_shipment"
        style="<?php echo( ( isset( $is_jet_verzendt_active ) && $is_jet_verzendt_active ) ? 'display: none' : '' ); ?>">

        <li class="wide grey">

            KeenDelivery is niet actief voor deze bestelling.
            <a href="javascript:void(0);" onclick="set_active_keendelivery(true);" class="grey">KeenDelivery
                activeren</a>

        </li>

    </ul>
    <input type="hidden" id="jet_wp_nonce" value="<?php echo wp_create_nonce( 'jet_send_orders' ); ?>" name="wp_nonce"/>
    <input type="hidden" id="save_config_setting" value="1" name="save_config_setting"/>
    <input type="hidden" id="jet_order_id" name="order_id"
           value="<?php echo ( isset( $order_id ) && $order_id > 0 ) ? (int) $order_id : ''; ?>"/>
</form>
