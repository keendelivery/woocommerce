<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // If accessed directly, then deny

/**
 * Class KeenDelivery_Settings
 */
class KeenDelivery_Settings {

	/**
	 * @var
	 */
	private $options;


	/**
	 * KeenDelivery_Settings constructor.
	 */
	public function __construct() {

		// add admin setting page
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );

	}


	/**
	 * Add KeenDelivery options page
	 */
	public function add_plugin_page() {
		// This page will be under "Settings"
		add_options_page(
			'KeenDelivery instellingen',
			'KeenDelivery',
			'manage_options',
			'keendelivery-setting-admin',
			array( $this, 'create_admin_page' )
		);
	}


	/**
	 * Options page callback
	 */
	public function create_admin_page() {

		$this->options = get_option( 'keendelivery_option_name' );
		?>
		<div class="wrap">
			<h2>Instellingen KeenDelivery</h2>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'keendelivery_option_group' );
				do_settings_sections( 'keendelivery-setting-admin' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}


	/**
	 *
	 */
	public function admin_init() {

		register_setting(
			'keendelivery_option_group', // Option group
			'keendelivery_option_name', // Option name
			array( $this, 'sanitize' ) // Sanitize
		);

		add_settings_section(
			'keendelivery_setting_section_authorisation', // ID
			'API-authorisatiegegevens', // Title
			array( $this, 'print_section_authorisation_info' ), // Callback
			'keendelivery-setting-admin' // Page
		);

		add_settings_section(
			'keendelivery_setting_section_printer_methods', // ID
			'Printerinstellingen', // Title
			array( $this, 'print_section_printer_methods_info' ), // Callback
			'keendelivery-setting-admin' // Page
		);

		add_settings_section(
			'keendelivery_setting_section_printer_label', // ID
			'Labelformaat', // Title
			array( $this, 'print_section_printer_label_info' ), // Callback
			'keendelivery-setting-admin' // Page
		);


		add_settings_section(
			'keendelivery_setting_section_shipping_methods', // ID
			'Verzendmethodes', // Title
			array( $this, 'print_section_shipping_methods_info' ), // Callback
			'keendelivery-setting-admin' // Page
		);

		add_settings_section(
			'keendelivery_setting_section_auto_tracktrace', // ID
			'Track&Trace-mail', // Title
			array( $this, 'print_section_shipping_methods_info' ), // Callback
			'keendelivery-setting-admin' // Page
		);


		add_settings_section(
			'keendelivery_setting_section_status', // ID
			'API-status', // Title
			array( $this, 'print_section_status_info' ), // Callback
			'keendelivery-setting-admin' // Page
		);



		add_settings_field(
			'jet_token',
			'API-sleutel',
			array( $this, 'jet_token_callback' ),
			'keendelivery-setting-admin',
			'keendelivery_setting_section_authorisation'
		);

		add_settings_field(
			'jet_printer_methods',
			'Welke type labelprinter moet standaard ingesteld staan?',
			array( $this, 'jet_printer_methods_callback' ),
			'keendelivery-setting-admin',
			'keendelivery_setting_section_printer_methods'
		);

		add_settings_field(
			'jet_printer_methods',
			'Geef het labelformaat op:',
			array( $this, 'jet_printer_label_callback' ),
			'keendelivery-setting-admin',
			'keendelivery_setting_section_printer_label'
		);

		add_settings_field(
			'jet_shipping_methods',
			'Welke verzendmethodes moeten naar KeenDelivery verstuurd worden?',
			array( $this, 'jet_shipping_methods_callback' ),
			'keendelivery-setting-admin',
			'keendelivery_setting_section_shipping_methods'
		);

		add_settings_field(
			'jet_shipping_methods',
			'Automatisch track&trace-mail versturen',
			array( $this, 'jet_shipping_tracktrace_callback' ),
			'keendelivery-setting-admin',
			'keendelivery_setting_section_auto_tracktrace'
		);

		add_settings_field(
			'jet_status',
			'Status',
			array( $this, 'jet_status_callback' ),
			'keendelivery-setting-admin',
			'keendelivery_setting_section_status'
		);
	}


	/**
	 * @param $input
	 *
	 * @return array
	 */
	public function sanitize( $input ) {
		$new_input = array();

		if ( isset( $input['jet_token'] ) ) {
			$postfix = '';
			if ( substr( $input['jet_token'], - 1 ) == ' ' ) {
				$postfix = ' ';
			}
			$new_input['jet_token'] = sanitize_text_field( $input['jet_token'] ) . $postfix;
		}

		$new_input['jet_printer_methods'] = ( isset( $input['jet_printer_methods'] ) ) ? sanitize_text_field( $input['jet_printer_methods'] ) : 'pdf';

		$new_input['jet_printer_label'] = ( isset( $input['jet_printer_label'] ) ) ? sanitize_text_field( $input['jet_printer_label'] ) : 'default';

		$new_input['jet_shipping_methods'] = isset( $input['jet_shipping_methods'] ) ? $input['jet_shipping_methods'] : array();

		$new_input['jet_auto_tracktrace'] = ( isset( $input['jet_auto_tracktrace'] ) && $input['jet_auto_tracktrace'] == 1 ) ? 1 : 0;

		$new_input['jet_status'] = ( isset( $input['jet_status'] ) && $input['jet_status'] == 'live' ) ? 'live' : 'test';

		return $new_input;
	}


	/**
	 *
	 */
	public function print_section_authorisation_info() {
		//
	}


	/**
	 *
	 */
	public function print_section_printer_methods_info() {
		//
	}


	/**
	 *
	 */
	public function print_section_printer_label_info() {
		//
	}


	/**
	 *
	 */
	public function print_section_shipping_methods_info() {
		//
	}

	public function print_section_shipping_auto_tracktrace() {
		//
	}

	/**
	 *
	 */
	public function keendelivery_setting_section_printer_label() {

	}

	/**
	 *
	 */
	public function print_section_status_info() {
		//
	}


	/**
	 *
	 */
	public function jet_token_callback() {
		printf(
			'<input type="text" id="jet_token" name="keendelivery_option_name[jet_token]" value="%s" style="width:100%%">',
			isset( $this->options['jet_token'] ) ? $this->options['jet_token'] : ''
		);
	}


	/**
	 *
	 */
	public function jet_printer_methods_callback() {


		echo '<select multiple name="keendelivery_option_name[jet_printer_methods]" id="jet_printer_methods" style="width:100%">';

		echo '  <option value="pdf" ' . ( ( isset( $this->options['jet_printer_methods'] ) && $this->options['jet_printer_methods'] == 'pdf' ) ? 'selected="selected"' : '' ) . '>PDF</option>';
		echo '  <option value="bat" ' . ( ( isset( $this->options['jet_printer_methods'] ) && $this->options['jet_printer_methods'] == 'bat' ) ? 'selected="selected"' : '' ) . '>BAT (werkt alleen met DHL)</option>';
		echo '  <option value="zpl" ' . ( ( isset( $this->options['jet_printer_methods'] ) && $this->options['jet_printer_methods'] == 'zpl' ) ? 'selected="selected"' : '' ) . '>ZPL (werkt alleen met DHL)</option>';

		echo '</select>';
	}


	/**
	 *
	 */
	public function jet_printer_label_callback() {


		echo '<select multiple name="keendelivery_option_name[jet_printer_label]" id="jet_printer_label" style="width:100%">';

		echo '  <option value="DEFAULT" ' . ( ( isset( $this->options['jet_printer_label'] ) && $this->options['jet_printer_label'] == 'DEFAULT' ) ? 'selected="selected"' : '' ) . '>1x A6 label</option>';
		echo '  <option value="4XA6" ' . ( ( isset( $this->options['jet_printer_label'] ) && $this->options['jet_printer_label'] == '4XA6' ) ? 'selected="selected"' : '' ) . '>4x A6 labels per A4 pagina</option>';
		echo '  <option value="A5" ' . ( ( isset( $this->options['jet_printer_label'] ) && $this->options['jet_printer_label'] == 'A5' ) ? 'selected="selected"' : '' ) . '>1x A5 label (alleen Fadello/PostNL)</option>';
		echo '  <option value="2XA5" ' . ( ( isset( $this->options['jet_printer_label'] ) && $this->options['jet_printer_label'] == '2XA5' ) ? 'selected="selected"' : '' ) . '>2x A5 labels per A4 pagina (alleen Fadello/PostNL)</option>';

		echo '</select>';
	}


	/**
	 *
	 */
	public function jet_shipping_methods_callback() {

		$shipping_methods = WC()->shipping->load_shipping_methods();
		$active_methods   = array();
		foreach ( $shipping_methods as $id => $shipping_method ) {

			if ( isset( $shipping_method->enabled ) && 'yes' === $shipping_method->enabled ) {


				$method = array( 'title' => $shipping_method->method_title, 'id' => $shipping_method->id );
				array_push( $active_methods, $method );
			}
		}

		if ( count( $active_methods ) > 0 ) {
			echo '<select multiple name="keendelivery_option_name[jet_shipping_methods][]" id="jet_shipping_methods" style="width:100%">';
			foreach ( $active_methods as $method ) {
				echo '<option value="' . esc_attr( $method['id'] ) . '" ' . ( isset( $this->options['jet_shipping_methods'] ) && in_array( $method['id'], $this->options['jet_shipping_methods'] ) ? 'selected="selected"' : '' ) . '>' . esc_html( $method['title'] ) . '</option>';
			}
			echo '</select>';


		} else {

			echo 'Geen actieve verzend methodes gevonden';
		}


	}


	/**
	 *
	 */
	public function jet_shipping_tracktrace_callback() {
		$status = ( isset( $this->options['jet_auto_tracktrace'] ) && $this->options['jet_auto_tracktrace'] == 1 ) ? 1 : 0;

		print ( '<select name="keendelivery_option_name[jet_auto_tracktrace]" id="jet_auto_tracktrace" style="width:100%">
				  <option value="1" ' . ( $status == 1 ? ' selected' : '' ) . '>Ja, automatisch</option>
				  <option value="0" ' . ( $status == 0 ? ' selected' : '' ) . '>Nee, handmatig</option>
				</select>' );
	}


	/**
	 *
	 */
	public function jet_status_callback() {
		$status = ( isset( $this->options['jet_status'] ) && $this->options['jet_status'] == 'live' ) ? 'live' : 'test';

		print ( '<select name="keendelivery_option_name[jet_status]" id="jet_status" style="width:100%">
				  <option value="test" ' . ( $status == 'test' ? ' selected' : '' ) . '>Test-status</option>
				  <option value="live" ' . ( $status == 'live' ? ' selected' : '' ) . '>Live-status</option>
				</select>' );
	}

}


if ( is_admin() ) {
	$keendelivery_settings = new KeenDelivery_Settings();
}
