<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // If accessed directly, then deny
?>
<div class="wrap">
    <h1>Verstuur bestellingen</h1>
    <h2>Weet u zeker dat u deze bestellingen naar KeenDelivery wilt versturen?</h2>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div style="position: relative;" id="post-body-content">
                <table class="widefat">
                    <thead>
                    <tr>
                        <th width="70">Order #</th>
                        <th>Naam</th>
                        <th>Adres</th>
                        <th>Plaats</th>
                        <th width="300">Status KeenDelivery</th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <th>Order #</th>
                        <th>Naam</th>
                        <th>Adres</th>
                        <th>Plaats</th>
                        <th>Status</th>
                    </tr>
                    </tfoot>
                    <tbody>
					<?php
					foreach ( $order_ids as $id ) :
						$order = new WC_Order( $id );
						?>
						<?php if ( $this->can_ship_order( $id ) ) : ?>
                        <tr>
                            <td><?php echo $id; ?> <input type="hidden" class="order_ids" value="<?php echo $id; ?>"/></td>
                            <td><?php echo esc_html( $order->shipping_first_name . ' ' . $order->shipping_last_name ); ?></td>
                            <td><?php echo esc_html( $order->shipping_address_1 . ' ' . $order->shipping_address_2 ); ?></td>
                            <td><?php echo esc_html( $order->shipping_city ) ?></td>
                            <td class="jet_order_status" id="jet_order_status_<?php echo $id; ?>"></td>
                        </tr>
					<?php else : ?>
                        <tr>
                            <td><?php echo $id; ?></td>
                            <td colspan="4"><span class="grey">Deze order is niet verzendbaar en wordt overgeslagen.</span></td>
                        </tr>
					<?php endif; ?>
					<?php endforeach; ?>
                    </tbody>
                </table>

            </div>

            <div id="postbox-container-1" class="postbox-container">
                <div class="postbox">
                    <h2 class="hndle ui-sortable-handle"><span>Instellingen</span></h2>
                    <div class="inside">
                        <div id="keendelivery_order_info">
							<?php
							$is_jet_verzendt_active = true;
							$is_batch_order_send    = true;
							$post_data              = get_option( 'jet_post_data_shipment_formdata' );
							$order_id               = null;
							require_once( dirname( __FILE__ ) . '/shipment_form.php' );
							?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <br class="clear">
    </div>
</div>
