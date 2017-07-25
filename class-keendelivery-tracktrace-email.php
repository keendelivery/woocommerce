<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * @since 0.1
 * @extends \WC_Email
 */
class WC_KeenDelivery_TraceTrace_Email extends WC_Email {


	/**
	 * @since 0.1
	 */
	public function __construct() {

		// set ID, this simply needs to be a unique name
		$this->id = 'wc_keendelivery_tracktrace';

		// this is the title in WooCommerce Email settings
		$this->title = 'Track & Trace KeenDelivery';

		// this is the description in WooCommerce email settings
		$this->description = 'Dit is een e-mail die verstuurd wordt naar klanten met daarin alle verzendinformatie van de bestelling.';

		// these are the default heading and subject lines that can be overridden using the settings
		$this->heading = 'Track & Trace informatie voor bestelling {order_number}';
		$this->subject = 'Track & Trace informatie voor bestelling {order_number}';

		// these define the locations of the templates that this email should use, we'll just use the new order template since this email is similar
		$this->template_html  = 'emails/customer-keendelivery-tracktrace.php';
		$this->template_plain = 'emails/plain/customer-keendelivery-tracktrace.php';

		// Call parent constructor to load any other defaults not explicity defined here
		parent::__construct();

		// this sets the recipient to the settings defined below in init_form_fields()
		$this->recipient = $this->get_option( 'recipient' );

		// if none was entered, just use the WP admin email as a fallback
		if ( ! $this->recipient ) {
			$this->recipient = get_option( 'admin_email' );
		}
	}


	/**
	 * @since 0.1
	 *
	 * @param int $order_id
	 */
	public function trigger( $order_id ) {

		// return if no order ID is present
		if ( ! $order_id ) {
			return;
		}

		// setup order object
		$this->object = new WC_Order( $order_id );
		$this->recipient               = $this->object->billing_email;

		// replace variables in the subject/headings
		$this->find[]    = '{order_date}';
		$this->replace[] = date_i18n( wc_date_format(), strtotime( $this->object->order_date ) );

		$this->find[]    = '{order_number}';
		$this->replace[] = $this->object->get_order_number();

		if ( ! $this->is_enabled() ) {
			return;
		}

		// send the mail
		if ( $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() ) ) {
			update_post_meta( $order_id, '_keendelivery_jet_track_trace_mail_send', time() );
			echo '1';
			exit;

		} else {
			die( "Het versturen van de track & trace mail is mislukt" );
		}
	}


	/**
	 * get_content_html function.
	 *
	 * @since 0.1
	 * @return string
	 */
	public function get_content_html() {
		ob_start();

		wc_get_template( $this->template_html, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading()
		), '', plugin_dir_path( __FILE__ ) . 'templates/' );

		return ob_get_clean();
	}


	/**
	 * get_content_plain function.
	 *
	 * @since 0.1
	 * @return string
	 */
	public function get_content_plain() {
		ob_start();
		wc_get_template( $this->template_plain, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading()
		), '', plugin_dir_path( __FILE__ ) . 'templates/' );

		return ob_get_clean();
	}


	/**
	 * Initialize Settings Form Fields
	 *
	 * @since 2.0
	 */
	public function init_form_fields() {

		$this->form_fields = array(
			'enabled'    => array(
				'title'   => 'Enable/Disable',
				'type'    => 'checkbox',
				'label'   => 'Enable this email notification',
				'default' => 'yes'
			),
			'subject'    => array(
				'title'       => 'Subject',
				'type'        => 'text',
				'description' => sprintf( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', $this->subject ),
				'placeholder' => '',
				'default'     => ''
			),
			'heading'    => array(
				'title'       => 'Email Heading',
				'type'        => 'text',
				'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.' ),
					$this->heading ),
				'placeholder' => '',
				'default'     => ''
			),
			'email_type' => array(
				'title'       => 'Email type',
				'type'        => 'select',
				'description' => 'Choose which format of email to send.',
				'default'     => 'html',
				'class'       => 'email_type',
				'options'     => array(
					'plain'     => __( 'Plain text', 'woocommerce' ),
					'html'      => __( 'HTML', 'woocommerce' ),
					'multipart' => __( 'Multipart', 'woocommerce' ),
				)
			)
		);
	}


}
