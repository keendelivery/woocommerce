<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // If accessed directly, then deny

/**
 * Class KeenDelivery_Settings
 */
class KeenDelivery_Orders {


	/**
	 * KeenDelivery_Orders constructor.
	 */
	public function __construct() {
		$options = get_option( 'keendelivery_option_name' );

		if ( $options ) {
			// add metabox on order detail page
			add_action( 'add_meta_boxes', array( &$this, 'add_meta_box' ) );

			// add admin css + js
			add_action( 'admin_enqueue_scripts', array( &$this, 'admin_styles' ) );
			add_action( 'admin_enqueue_scripts', array( &$this, 'admin_scripts' ) );

			// processing ajax requests
			add_action( 'wp_ajax_get_order_info', array( &$this, 'get_order_info' ) );
			add_action( 'wp_ajax_send_order', array( &$this, 'send_order' ) );
			add_action( 'wp_ajax_send_track_trace', array( &$this, 'send_track_trace_email' ) );
			add_action( 'wp_ajax_get_shipment_methods', array( &$this, 'get_shipment_methods' ) );

			// add columns on order grid
			add_filter( 'manage_edit-shop_order_columns', array( &$this, 'add_column' ) );
			add_action( 'manage_shop_order_posts_custom_column', array( &$this, 'add_column_content' ), 2 );
			add_filter( 'manage_edit-shop_order_sortable_columns', array( &$this, 'add_column_filter' ) );

			// label print options
			add_action( 'admin_footer-edit.php', array( &$this, 'add_print_action_menu' ) );
			add_action( 'load-edit.php', array( &$this, 'print_labels_action' ) );

			// batch order send
			add_action( 'admin_menu', array( &$this, 'add_batch_order_page' ) );
			add_action( 'load-edit.php', array( &$this, 'send_keendelivery_orders' ) );

			// manual order update
			add_action( 'admin_init', array( &$this, 'update_orders' ) );
		}
	}


	/**
	 * Add css file
	 */
	public function admin_styles() {
		wp_enqueue_style( 'keendelivery_styles',
			plugins_url( basename( dirname( __FILE__ ) ) )
			. '/assets/css/admin.css', array(), KEENDELIVERY_VERSION );
		wp_enqueue_style( 'jquery-ui-css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
	}

	/**
	 * Add js file
	 */
	public function admin_scripts() {
		wp_enqueue_script( 'keendelivery_scripts',
			plugins_url( basename( dirname( __FILE__ ) ) )
			. '/assets/js/keendelivery.js', array(), KEENDELIVERY_VERSION );
		wp_enqueue_script( 'jquery-ui-datepicker' );
	}

	/**
	 * Build the meta box for the order view page
	 */
	public function add_meta_box() {
		add_meta_box( 'woocommerce-keendelivery',
			__( 'KeenDelivery', 'wc_keendelivery' ), array(
				&$this,
				'meta_box_content'
			), 'shop_order', 'side', 'high' );
	}


	/**
	 * Content for the meta box
	 */
	public function meta_box_content() {
		global $post;
		if ( $post->ID > 0 ) {
			echo '<div id="keendelivery_order_info"><img class="loading" src="'
			     . plugins_url( 'assets/img/loading.gif', __FILE__ )
			     . '" /></div>';
			echo '<script>
			jQuery(document).ready(function() {
				keendelivery_get_order_info(' . $post->ID . ', \'' . wp_create_nonce( 'jet_send_orders' ) . '\');
			});
		</script>';
		}

	}


	/**
	 * Show order info for ajax request
	 */
	public function get_order_info() {

		$order_id = ( isset( $_POST['order_id'] ) ) ? (int) $_POST['order_id'] : exit;
		if ( isset( $_POST['wp_nonce'] ) && wp_verify_nonce( $_POST['wp_nonce'], 'jet_send_orders' ) ) {

			if ( current_user_can( 'edit_shop_order', $order_id ) ) {

				$shipment_id = get_post_meta( $order_id, '_keendelivery_jet_shipment_id', true );
				if ( $shipment_id && $shipment_id > 0 ) {
					$this->show_keendelivery_shipment_status( $order_id );

				} else {
					$this->shipment_order_send_form( $order_id );

				}

			} else {
				echo 'Je hebt onvoldoende gebruikersrechten om deze order te kunnen bekijken.';

			}

		} else {
			die( "Ongeldige invoer" );
		}
		exit;

	}


	/**
	 * Create the KeenDelivery shipment form for the order view/edit page for new orders
	 *
	 * @param $order_id
	 */
	public function shipment_order_send_form( $order_id ) {

		$is_jet_verzendt_active = ( $this->is_default_keendelivery_shipment_method( $order_id ) ) ? true : false;

		include_once( dirname( __FILE__ ) . '/templates/shipment_form.php' );

	}


	/**
	 * View the KeenDelivery shipment details when an orders is send to KeenDelivery
	 *
	 * @param $order_id
	 */
	public function show_keendelivery_shipment_status( $order_id ) {




		$track_trace           = get_post_meta( $order_id, '_keendelivery_jet_track_trace', true );
		$track_trace_mail_send = get_post_meta( $order_id, '_keendelivery_jet_track_trace_mail_send', true );

		echo '<ul class="keendelivery_shipment_form">';
		echo '	<li class="wide">';

		echo '  <h4>Verzending is aangemeld</h4>';








		echo '  <h4>Labels</h4>';
		echo '  <a href="edit.php?post_type=shop_order&action=print_keendelivery_labels&post[]=' . $order_id . '">Print labels</a>';

		echo '  <h4>Track & trace</h4>';


		if ( isset( $track_trace ) && ! empty( $track_trace ) ) {

			foreach ( $track_trace as $key => $url ) {
				echo '<a href="' . esc_url( $url ) . '" target="_blank">' . esc_html( $key ) . '</a><br/>';

			}

			$options = get_option( 'keendelivery_option_name' );
			if ( isset( $shipper ) && $shipper == 'DHL' && isset( $options['jet_auto_tracktrace'] ) && $options['jet_auto_tracktrace'] == 1 ) {
				echo 'De track&trace mail wordt automatisch verzonden.';

			} elseif ( isset( $track_trace_mail_send ) && ! empty( $track_trace_mail_send ) ) {
				echo '  <div class="jet_track_trace_info">Track&trace informatie is verstuurd. <a onclick="keendelivery_send_track_trace('
				     . esc_attr( $order_id ) . ', \'' . wp_create_nonce( 'jet_send_orders' )
				     . '\'); return false" href="javascript:void(0);">Opnieuw versturen?</a></div>';

			} else {
				echo '<button class="button button-primary send_tracktrace" id="jet_send_track_trace" onclick="keendelivery_send_track_trace('
				     . esc_attr( $order_id ) . ', \'' . wp_create_nonce( 'jet_send_orders' ) . '\'); return false">Verstuur Trace&trace</button>';

			}
			echo '	<div class="clearfix"></div>';

		}


		echo '	</li>';
		echo '</ul>';

	}


	/**
	 *
	 * Check if an order is assigned to KeenDelivery
	 *
	 * @param $order_id
	 *
	 * @return bool
	 */
	public function is_default_keendelivery_shipment_method( $order_id ) {
		$options = get_option( 'keendelivery_option_name' );

		$order         = new WC_Order( $order_id );
		$shipment_info = $order->get_items( 'shipping' );
		if ( isset( $shipment_info ) && count( $shipment_info ) > 0
		     && isset( $options['jet_shipping_methods'] )
		     && count( $options['jet_shipping_methods'] ) > 0
		) {
			foreach ( $shipment_info as $info ) {
				if ( in_array( $info['method_id'], $options['jet_shipping_methods'] ) ) {
					return true;
				}
			}
		}

		return false;

	}


	/**
	 * Send an order to KeenDelivery portal
	 */
	public function send_order() {
		if ( isset( $_POST['post'] ) ) {
			parse_str( $_POST['post'], $post );

			$order_id = ( isset( $post['order_id'] ) && $post['order_id'] > 0 ) ? (int) $post['order_id'] : die( "Geen order invoer" );

			if ( isset( $post['wp_nonce'] ) && wp_verify_nonce( $post['wp_nonce'], 'jet_send_orders' ) ) {

				// save post in custom config
				if ( isset( $post['save_config_setting'] ) && $post['save_config_setting'] == 1 ) {
					$post_data_settings = $post;
					unset($post_data_settings['wp_nonce']);
					unset($post_data_settings['reference']);
					unset($post_data_settings['order_id']);
					unset($post_data_settings['save_config_setting']);

				    update_option( 'jet_post_data_shipment_formdata', http_build_query( $post_data_settings ) );
				}

				if ( current_user_can( 'edit_shop_order', $order_id ) && $this->can_ship_order( $order_id ) ) {

					// get jet verzendt options
					$options = get_option( 'keendelivery_option_name' );

					$auto_tracktrace = ( isset( $options['jet_auto_tracktrace'] ) && $options['jet_auto_tracktrace'] == 1 ) ? 1 : 0;

					$api_token = ( isset( $options['jet_token'] ) ) ? $options['jet_token'] : false;
					if ( ! $api_token ) {
						echo "Ongeldige API-sleutel KeenDelivery";
						exit;
					}

					$order = new WC_Order( $order_id );


					// format streetline data
					if ( empty( $order->shipping_address_2 ) ) { // if no/empty street 1, try to split street 0 field.
						$streetArr = explode( ' ', $order->shipping_address_1 );
						if ( count( $streetArr ) >= 2 ) {
							$order->shipping_address_2 = end( $streetArr );
							$order->shipping_address_1 = implode( ' ',
								array_slice( $streetArr, 0, - 1 ) );
						}
					}


					$shipment_data                   = array();
					$shipment_data['company_name']   = $order->shipping_company;
					$shipment_data['street_line_1']  = $order->shipping_address_1;
					$shipment_data['number_line_1']  = $order->shipping_address_2;
					$shipment_data['zip_code']       = $order->shipping_postcode;
					$shipment_data['city']           = $order->shipping_city;
					$shipment_data['country']        = $order->shipping_country;
					$shipment_data['contact_person'] = $order->shipping_first_name . ' ' . $order->shipping_last_name;
					$shipment_data['phone']          = $order->billing_phone;
					$shipment_data['email']          = $order->billing_email;

					if ( isset( $post ) && is_array( $post ) ) {
						$shipment_data = array_merge( $shipment_data, $post );
					}

                    if (isset($shipment_data['reference']) == false || empty($shipment_data['reference'])) {
                        $shipment_data['reference'] = 'Bestelling #' . $order_id;
                    }


                    $shipment_data['send_track_and_trace_email'] = $auto_tracktrace;
					$shipment_data['input_source']               = 'woocommerce';

					// check current api status: live or test
					$api_status = ( isset( $options['jet_status'] ) ) ? $options['jet_status'] : false;
					if ( $api_status == 'live' ) {
						$ch = curl_init( 'https://portal.keendelivery.com/api/v2/shipment?api_token=' . $api_token );
					} else {
						$ch = curl_init( 'http://testportal.keendelivery.com/api/v2/shipment?api_token=' . $api_token );
						$api_token = '6NsAWORewR5qg5PBpDZcaw4ctQu678urOBA7cTiu';
						$ch = curl_init( 'jetverzendt.app/api/v2/shipment?api_token=' . $api_token );
					}

					curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
					curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "POST" );
					curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $shipment_data ) );
					curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
					curl_setopt(
						$ch, CURLOPT_HTTPHEADER, array(
							'Content-Type: application/json',
							'Accept: application/json',
							'Content-Length: ' . strlen( json_encode( $shipment_data ) )
						)
					);

					$result = json_decode( curl_exec( $ch ) );

					if ( ! $result ) { // no server connection
						echo 'Fout bij verbinding maken met KeenPortal. Controleer uw instellingen.';

					} else if ( isset( $result->shipment_id ) && (int) $result->shipment_id > 0 ) { // request successful

						// if there are no errors, update post meta data
						update_post_meta( $order_id, '_keendelivery_label_printed', '' );
						update_post_meta( $order_id, '_keendelivery_jet_shipment_id', $result->shipment_id );
						update_post_meta( $order_id, '_keendelivery_jet_updated_at', date_i18n( "Y-m-d H:i:s" ) );

						//echo $result->shipment_id;
						echo "1";
						exit;

					} else { // jet error

						$result = iterator_to_array( new RecursiveIteratorIterator( new RecursiveArrayIterator( (array) $result ) ), 0 );
						if ( is_array( $result ) ) {
							echo implode( '<br/>', $result );
						}

						exit;

					}
				} else {
					die( "Deze bestelling is niet verzendbaar" );
				}


			}

		}

		exit;
	}

	public function get_shipment_methods() {
		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			wp_die( 'Helaas heeft u onvoldoende rechten om deze bewerking uit te voeren.' );
		}

		$shipping_methods = '';
		$keendelivery_shipping_methods_date = get_option('_keendelivery_shipping_methods_date');
		$keendelivery_shipping_methods = get_option('_keendelivery_shipping_methods');
		if ( $keendelivery_shipping_methods_date && $keendelivery_shipping_methods && $keendelivery_shipping_methods_date == date_i18n('Ymd')) {
		    $shipping_methods = $keendelivery_shipping_methods;

		} else {
			$options = get_option( 'keendelivery_option_name' );
			$api_token = ( isset( $options['jet_token'] ) ) ? $options['jet_token'] : false;
			if ( $api_token ) {

				$api_status = ( isset( $options['jet_status'] ) ) ? $options['jet_status'] : false;
				if ( $api_status == 'live' ) {
					$url = 'https://portal.keendelivery.com/api/v2/shipping_methods?api_token=' . $api_token . '&source=woocommerce';
				} else {
					$url = 'http://testportal.keendelivery.com/api/v2/shipping_methods?api_token=' . $api_token . '&source=woocommerce';
				}

				$ch = curl_init( $url );
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
				curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
				curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
				curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
				curl_setopt(
					$ch, CURLOPT_HTTPHEADER, [
						'Content-Type: application/json',
						'Accept: application/json'
					]
				);

				$result           = curl_exec( $ch );
				$shipping_methods = json_decode( $result );
				if ( is_object( $shipping_methods ) && isset( $shipping_methods->shipping_methods ) ) {
					$shipping_methods = (array) $shipping_methods->shipping_methods;
					$shipping_methods = json_encode( $shipping_methods );

					update_option( '_keendelivery_shipping_methods_date', date_i18n('Ymd') );
					update_option( '_keendelivery_shipping_methods', $shipping_methods );

				}

			}
		}

		echo $shipping_methods;
		exit;


	}


	/**
	 * Update script for a manual update function
	 */
	public function update_orders() {
		global $wpdb;

		if ( isset( $_GET['jet_update_orders'] ) ) {
			if ( ! current_user_can( 'edit_shop_orders' ) ) {
				wp_die( 'Helaas heeft u onvoldoende rechten om deze bewerking uit te voeren.' );
			}

			/*
			 * update only orders with no label OR no track & trace number
			 */

			$orders = $wpdb->get_results( "
		        SELECT p.ID,
		         	" . $wpdb->prefix . "postmeta_0.meta_value as jet_shipment_id,
		        	" . $wpdb->prefix . "postmeta_1.meta_value as jet_updated_at,
		            " . $wpdb->prefix . "postmeta_2.meta_value as label_printed,
		            " . $wpdb->prefix . "postmeta_3.meta_value as jet_track_trace
		        FROM " . $wpdb->prefix . "posts as p
		            INNER JOIN " . $wpdb->prefix . "postmeta AS " . $wpdb->prefix . "postmeta_0 ON ( p.ID = " . $wpdb->prefix . "postmeta_0.post_id )
		            INNER JOIN " . $wpdb->prefix . "postmeta AS " . $wpdb->prefix . "postmeta_1 ON ( p.ID = " . $wpdb->prefix . "postmeta_1.post_id )
		            INNER JOIN " . $wpdb->prefix . "postmeta AS " . $wpdb->prefix . "postmeta_2 ON ( p.ID = " . $wpdb->prefix . "postmeta_2.post_id )
		            INNER JOIN " . $wpdb->prefix . "postmeta AS " . $wpdb->prefix . "postmeta_3 ON ( p.ID = " . $wpdb->prefix . "postmeta_3.post_id )
		        WHERE p.post_type = 'shop_order'
		            AND p.post_date >= (NOW() - INTERVAL 365 DAY)
		            AND " . $wpdb->prefix . "postmeta_0 . meta_key = '_keendelivery_jet_shipment_id'
		            AND " . $wpdb->prefix . "postmeta_1 . meta_key = '_keendelivery_jet_updated_at'
		            AND " . $wpdb->prefix . "postmeta_2 . meta_key = '_keendelivery_label_printed'
		            AND " . $wpdb->prefix . "postmeta_3 . meta_key = '_keendelivery_jet_track_trace'
		            AND ( " . $wpdb->prefix . "postmeta_1 . meta_value = '' OR " . $wpdb->prefix . "postmeta_2 . meta_value = '' OR " . $wpdb->prefix . "postmeta_3 . meta_value = '' )
				GROUP BY p.ID " );

			echo 'Er moeten ' . count( $orders ) . ' onvolledige orders bijgewerkt worden.<br/><br/>';

			if ( $orders ) {

				// get jet verzendt options
				$options   = get_option( 'keendelivery_option_name' );
				$api_token = ( isset( $options['jet_token'] ) ) ? $options['jet_token'] : false;

				foreach ( $orders as $order ) {

					$errors = array();

					if ( ! $api_token ) {
						$errors[] = "Fout met verbinden: ongeldige API-sleutel KeenDelivery";
						exit;
					}

					// check current api status: live or test
					$api_status = ( isset( $options['jet_status'] ) ) ? $options['jet_status'] : false;
					if ( $api_status == 'live' ) {
						$ch = curl_init( 'https://portal.keendelivery.com/api/v2/shipment/' . $order->jet_shipment_id . '?api_token=' . $api_token );
					} else {
						$ch = curl_init( 'http://testportal.keendelivery.com/api/v2/shipment/' . $order->jet_shipment_id . '?api_token=' . $api_token );
					}
					curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
					curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
					curl_setopt(
						$ch, CURLOPT_HTTPHEADER, array(
							'Content-Type: application/json',
							'Accept: application/json'
						)
					);

					$result = json_decode( curl_exec( $ch ) );

					if ( ! $result ) { // no server connection
						echo 'Fout bij order ID: ' . $order->ID . '<br/>';
						//wp_die( 'Fout bij verbinding maken met KeenDelivery portal. We proberen zo opnieuw...' );

					} elseif ( $result->track_and_trace && $result->shipment_method ) {


						update_post_meta( $order->ID, '_keendelivery_jet_updated_at', $result->updated_at );
						update_post_meta( $order->ID, '_keendelivery_label_printed', ( ( $result->status == 5 ) ? 1 : '' ) );
						update_post_meta( $order->ID, '_keendelivery_jet_track_trace',
							get_object_vars( $result->track_and_trace ) ); // convert object to array

						echo 'Bijgewerkt: ' . $order->ID . '<br/>';
					}


				}


			}
			exit;
		}


	}


	/**
	 *
	 */
	public function send_track_trace_email() {

		$order_id = ( isset( $_POST['order_id'] ) ) ? (int) $_POST['order_id'] : exit;

		// check wp nonce
		if ( isset( $_POST['wp_nonce'] ) && wp_verify_nonce( $_POST['wp_nonce'], 'jet_send_orders' ) ) {

			// check user access
			if ( current_user_can( 'edit_shop_order', $order_id ) ) {

				$mailer = WC()->mailer();
				$mails  = $mailer->get_emails();
				if ( ! empty( $mails ) ) {
					foreach ( $mails as $mail ) {
						if ( $mail->id == 'wc_keendelivery_tracktrace' ) {
							$mail->trigger( $order_id );
						}
					}
				}
				exit;

			}

		} else {
			die( "Ongeldige invoer" );

		}


	}


	/**
	 * Add KeenDelivery columns to the order grid
	 *
	 * @param $columns
	 *
	 * @return array
	 */
	public function add_column( $columns ) {
		$new_columns = ( is_array( $columns ) ) ? $columns : array();
		unset( $new_columns['order_actions'] );

		$new_columns['labels_printed'] = 'Labels geprint';
		$new_columns['track_trace']    = 'Track & trace';

		$new_columns['order_actions'] = $columns['order_actions'];

		return $new_columns;
	}

	/**
	 * Add content to the KeenDelivery order grid
	 *
	 * @param $column
	 */
	public function add_column_content( $column ) {
		global $post;

		$jet_shipment_id = get_post_meta( $post->ID, '_keendelivery_jet_shipment_id', true );
		$label_printed   = get_post_meta( $post->ID, '_keendelivery_label_printed', true );
		$track_trace     = get_post_meta( $post->ID, '_keendelivery_jet_track_trace', true );

		if ( $jet_shipment_id && $jet_shipment_id > 0 ) {

			if ( $column == 'labels_printed' ) {
				echo ( isset( $label_printed ) && $label_printed == 1 ) ? '<div class="jet_label_printed yes">ja</div>'
					: '<div class="jet_label_printed no">nee</div>';
			}
			if ( $column == 'track_trace' ) {
				if ( $track_trace ) {
					foreach ( $track_trace as $key => $url ) {
						echo '<a href="' . esc_url( $url ) . '" target="_blank">' . esc_html( $key ) . '</a><br/>';

					}
				}
			}

		}
	}

	/**
	 * Add a filter option to the custom KeenDelivery column(s)
	 *
	 * @param $columns
	 *
	 * @return array
	 */
	public function add_column_filter( $columns ) {
		$custom = array(
			'labels_printed' => 'labels_printed'
		);

		return wp_parse_args( $custom, $columns );
	}


	/**
	 * Add a new dropdown item for printing KeenDelivery labels
	 */
	public function add_print_action_menu() {

		global $post_type;

		if ( $post_type == 'shop_order' ) {
			?>
            <script type="text/javascript">

                jQuery(document).ready(function () {
                    jQuery('<option>').val('send_keendelivery_orders').text('Versturen naar KeenDelivery').appendTo("select[name='action']");
                    jQuery('<option>').val('send_keendelivery_orders').text('Versturen naar KeenDelivery').appendTo("select[name='action2']");

                    jQuery('<option>').val('print_keendelivery_labels').text('Print labels KeenDelivery').appendTo("select[name='action']");
                    jQuery('<option>').val('print_keendelivery_labels').text('Print labels KeenDelivery').appendTo("select[name='action2']");
                });
            </script>
			<?php
		}
	}


	/**
	 * Send KeenDelivery orders
	 */
	public function send_keendelivery_orders() {
		if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'shop_order' && isset( $_GET['action'] ) && $_GET['action'] == 'send_keendelivery_orders'
		     && isset( $_GET['post'] )
		     && is_array( $_GET['post'] )
		) {
			$url_var = '&wp_nonce=' . wp_create_nonce( 'keendelivery_batch' );
			foreach ( $_GET['post'] as $item ) {

				$url_var .= '&ids[]=' . (int) $item;
			}

			wp_redirect( 'admin.php?page=batch_order_page' . $url_var );
			exit;
		}


	}

	/**
	 * Print KeenDelivery labels
	 */
	public function print_labels_action() {
		if ( isset( $_GET['post_type'] ) && $_GET['post_type'] == 'shop_order' && isset( $_GET['action'] ) && $_GET['action'] == 'print_keendelivery_labels'
		     && isset( $_GET['post'] )
		     && is_array( $_GET['post'] )
		) {
			$post_ids = array();
			$jet_ids  = array();

			// check user rights per order
			foreach ( $_GET['post'] as $order_id ) {
				if ( current_user_can( 'edit_shop_order', $order_id ) ) {
					$shipment_id = get_post_meta( $order_id, '_keendelivery_jet_shipment_id', true );
					if ( $shipment_id && (int) $shipment_id > 0 ) {
						$jet_ids[]  = (int) $shipment_id;
						$post_ids[] = (int) $order_id;
					}

				}
			}

			// get labels
			if ( ! empty( $jet_ids ) ) {
				// get jet verzendt options
				$options   = get_option( 'keendelivery_option_name' );
				$api_token = ( isset( $options['jet_token'] ) ) ? $options['jet_token'] : false;
				if ( ! $api_token ) {
					$errors[] = "Fout met verbinden: ongeldige API-sleutel KeenDelivery";
					exit;
				}


				// get printer settings
				$printer_method = $options['jet_printer_methods'];
				$printer_label  = $options['jet_printer_label'];
				if ( $printer_method == 'bat' ) {
					$file_name    = 'Verzendlabels KeenDelivery.bat';
					$content_type = 'Content-type:application/txt';
				} elseif ( $printer_method == 'zpl' ) {
					$file_name    = 'Verzendlabels KeenDelivery.zpl';
					$content_type = 'Content-type:application/txt';
				} else {
					$file_name    = 'Verzendlabels KeenDelivery.pdf';
					$content_type = 'Content-type:application/pdf';
				}


				// check current api status: live or test
				$api_status = ( isset( $options['jet_status'] ) ) ? $options['jet_status'] : false;
				if ( $api_status == 'live' ) {
					$ch = curl_init( 'https://portal.keendelivery.com/api/v2/label?api_token=' . $api_token );
				} else {
					$ch = curl_init( 'http://testportal.keendelivery.com/api/v2/label?api_token=' . $api_token );
				}


				$label_data = json_encode(
					array(
						'shipments' => $jet_ids,
						'type'      => strtoupper( $printer_method ),
						'options'   => array(
							'DHL'     => array( 'size' => strtoupper( $printer_label ) ),
							'DPD'     => array( 'size' => strtoupper( $printer_label ) ),
							'Fadello' => array( 'size' => strtoupper( $printer_label ) )
						)
					)
				);


				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
				curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "POST" );
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $label_data );
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
				curl_setopt(
					$ch, CURLOPT_HTTPHEADER, array(
						'Content-Type: application/json',
						'Accept: application/json',
						'Content-Length: ' . strlen( $label_data )
					)
				);

				$result = json_decode( curl_exec( $ch ) );

				if ( isset( $result->labels ) ) {
					header( $content_type );
					header( 'Content-Disposition: attachment; filename="' . $file_name . '"' );
					echo base64_decode( $result->labels );

					foreach ( $post_ids as $ids ) {
						update_post_meta( $ids, '_keendelivery_label_printed', 1 );
					}
					exit;
				} else {
					wp_die( "Helaas, het ophalen van labels is mislukt. Controleer uw instellingen..." );
				}
			} else {
				wp_die( "De labels voor deze bestelling(en) kunnen niet worden opgehaald" );
			}
			exit;
		}

	}

	/**
	 * Check if an order can be shipped
	 *
	 * @param $order_id
	 *
	 * @return bool
	 */
	public function can_ship_order( $order_id ) {
		$shipment_id = get_post_meta( $order_id, '_keendelivery_jet_shipment_id', true );
		if ( empty( $shipment_id ) && current_user_can( 'edit_shop_order', $order_id ) ) {
			return true;
		}

		return false;
	}


	/**
	 * Add admin page without menu button
	 */
	public function add_batch_order_page() {
		add_submenu_page( null, __( "Orders versturen naar KeenDelivery" ), __( "Orders versturen naar KeenDelivery" ), 'edit_shop_orders',
			"batch_order_page",
			array( &$this, 'batch_order_page' ) );
	}

	/**
	 * Show batch order send page
	 */
	public function batch_order_page() {
		$ids = ( isset( $_GET['ids'] ) && is_array( $_GET['ids'] ) && count( $_GET['ids'] ) > 0 ) ? $_GET['ids'] : die( 'Fout bij ophalen gegevens' );
		if ( ! wp_verify_nonce( $_GET['wp_nonce'], 'keendelivery_batch' ) ) {
			wp_die( 'Er is een fout opgetreden, probeer opnieuw' );
		}

		// check user rights per order
		$order_ids = array();
		foreach ( $ids as $order_id ) {
			if ( current_user_can( 'edit_shop_order', $order_id ) ) {
				$order_ids[] = (int) $order_id;
			}
		}

		// load template
		include_once( dirname( __FILE__ ) . '/templates/shipment_batch_order_send.php' );

	}


}


$keendelivery_orders = new KeenDelivery_Orders();
