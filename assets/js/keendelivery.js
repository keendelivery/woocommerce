var jet_shipping_methods;

function keendelivery_get_order_info(order_id, wp_nonce) {

    var data = {
        'action': 'get_order_info',
        'order_id': order_id,
        'wp_nonce': wp_nonce
    };

    jQuery.post(ajaxurl, data, function (response) {
        jQuery('#keendelivery_order_info').html(response);
    });


}

function keendelivery_send_track_trace(order_id, wp_nonce) {

    var c = confirm("Weet u zeker dat u deze e-mail wilt versturen?");
    if (c) {

        jQuery('#jet_send_track_trace').prop('disabled', true); // disable button when loading

        var data = {
            'action': 'send_track_trace',
            'order_id': order_id,
            'wp_nonce': wp_nonce
        };

        jQuery.post(ajaxurl, data, function (response) {

            if (isNaN(response) || response != 1) { // an error

                jQuery('#jet_message').show();
                jQuery('#jet_message').html(response);
                jQuery('#jet_send_track_trace').prop('disabled', false);

            } else { // success
                keendelivery_get_order_info(order_id, wp_nonce);

            }

        });


    }
}


function keendelivery_send_order() {

    jQuery('#jet_send_submit').prop('disabled', true); // disable button when loading

    var data = {
        'action': 'send_order',
        'post': jQuery('#keendelivery_shipment_form').serialize()
    };

    jQuery.post(ajaxurl, data, function (response) {

        if (isNaN(response) || response <= 0) { // an error

            jQuery('#jet_message').show();
            jQuery('#jet_message').html(response);
            jQuery('#jet_send_submit').prop('disabled', false);

        } else { // success
            var order_id = jQuery('#jet_order_id').val();
            var wp_nonce = jQuery('#jet_wp_nonce').val();
            keendelivery_get_order_info(order_id, wp_nonce);

        }

    });
}


function keendelivery_start_send_orders() {
    jet_send_status = 0;
    jet_count_orders = 0;
    jet_errors = 0;

    jQuery('#jet_send_submit').prop('disabled', true); // disable button when loading

    jQuery(".jet_order_status").each(function () {
        jet_count_orders++;
        jQuery(this).html('<div class="jet_grey">Bezig met versturen...</div>');
    });

    if (jet_count_orders > 0) {
        var i = 0;
        var order_ids = jQuery('.order_ids').each(function () {
            keendelivery_start_send_order_item(jQuery(this).val(), i);
            i++;
        });
    } else {
        alert('Er zijn geen orders om te versturen');
    }

}

function keendelivery_start_send_order_item(order_id, i) {
    if (i == 0) {
        jQuery('#save_config_setting').val(1);
    } else {
        jQuery('#save_config_setting').val(0);
    }
    jQuery('#jet_order_id').val(order_id);


    var data = {
        'action': 'send_order',
        'post': jQuery('#keendelivery_shipment_form').serialize()
    };

    jQuery.post(ajaxurl, data, function (response) {
        if (response == 1) {

            jQuery('#jet_order_status_' + order_id).html('<span class="jet_green">Order verzonden</span>');
        } else {
            jet_errors++;
            jQuery('#jet_order_status_' + order_id).html('<span class="jet_red">' + response + '</span>');
        }
        jet_send_status++;

        if (jet_send_status == jet_count_orders) {
            if (jet_errors > 0) {
                if (jet_errors == 1) {
                    alert('Het verzenden is afgerond. Er is ' + jet_errors + ' order mislukt.');
                } else {
                    alert('Het verzenden is afgerond. Er zijn ' + jet_errors + ' orders mislukt.');
                }
            } else {
                alert('Verzenden is succesvol afgerond.');
            }

            jQuery('#jet_send_submit').prop('disabled', false);
        }


    });
}


function set_active_keendelivery(status) {
    if (status) {
        jQuery('.keendelivery_no_shipment').hide();
        jQuery('.keendelivery_shipment_form').show();
    } else {
        jQuery('.keendelivery_no_shipment').show();
        jQuery('.keendelivery_shipment_form').hide();
    }

}


function generate_shipment_form(postdata) {

    jQuery.post(ajaxurl, {
        'action': 'get_shipment_methods'
    }, function (response) {
        jet_shipping_methods = jQuery.parseJSON(response);

        var form = '';

        form += generate_product_dropdown();

        form += generate_amount_field();

        form += generate_reference_field();

        form += generate_service_field();

        form += generate_service_options();

        jQuery('#shipment_form').html(form);

        set_keen_services();

        set_keen_prefered_data(postdata);

    });


}

function generate_product_dropdown() {
    result = '<div class="form-group">';

    result += '<h4>Vervoerder</h4>';

    result += '<select id="keen_product" name="product" onchange="set_keen_services()">';

    if (Object.keys(jet_shipping_methods).length > 0) {
        for (var k in jet_shipping_methods) {
            if (typeof jet_shipping_methods[k] !== 'function') {
                result += '<option value="' + jet_shipping_methods[k]['value'] + '">' + jet_shipping_methods[k]['text'] + '</option>';
                //console.log(jet_shipping_methods[k]);

            }
        }
    }

    result += '</select>';
    result += '</div>';

    return result;

}

function generate_amount_field() {
    result = '<div class="form-group">';
    result += '<h4>Aantal pakketten</h4>';
    result += '<input type="text" name="amount" id="keen_amount" />';
    result += '</div>';

    return result;
}

function generate_reference_field() {
    result = '<div class="form-group">';
    result += '<h4>Referentie</h4>';
    result += '<input type="text" name="reference" id="keen_reference" />';
    result += '</div>';

    return result;
}

function generate_service_field() {
    result = '<div class="form-group">';
    result += '<h4>Service</h4>';
    result += '<select id="keen_service" name="service" onchange="set_keen_service_options()">';
    result += '</select>';
    result += '</div>';

    return result;
}


function generate_service_options() {
    result = '<div id="keen_service_options"></div>';
    return result;
}

function set_keen_services() {
    var current_product = jQuery('#keen_product').val();
    result = '';

    if (Object.keys(jet_shipping_methods).length > 0) {
        for (var k in jet_shipping_methods) {
            if (typeof jet_shipping_methods[k] !== 'function') {
                if (jet_shipping_methods[k].value == current_product) {

                    if (Object.keys(jet_shipping_methods[k]['services']).length > 0) {
                        for (var i in jet_shipping_methods[k]['services']) {
                            if (typeof jet_shipping_methods[k]['services'][i] !== 'function') {
                                result += '<option value="' + jet_shipping_methods[k]['services'][i]['value'] + '">' + jet_shipping_methods[k]['services'][i]['text'] + '</option>';
                            }
                        }

                    }
                }
            }
        }
    }
    jQuery('#keen_service').html('');
    jQuery('#keen_service').html(result);


    set_keen_service_options();

}

function set_keen_service_options() {
    var current_product = jQuery('#keen_product').val();
    var keen_service = jQuery('#keen_service').val();
    result = '';

    if (Object.keys(jet_shipping_methods).length > 0) {
        for (var k in jet_shipping_methods) {
            if (typeof jet_shipping_methods[k] !== 'function') {
                if (jet_shipping_methods[k].value == current_product) {

                    if (Object.keys(jet_shipping_methods[k]['services']).length > 0) {
                        for (var i in jet_shipping_methods[k]['services']) {
                            if (jet_shipping_methods[k]['services'][i].value == keen_service) {

                                if (Object.keys(jet_shipping_methods[k]['services'][i]['options']).length > 0) {
                                    for (var j in jet_shipping_methods[k]['services'][i]['options']) {

                                        if (typeof jet_shipping_methods[k]['services'][i]['options'] !== 'function') {
                                            type = jet_shipping_methods[k]['services'][i]['options'][j]['type'];

                                            if (type != 'hidden') {
                                                result += '<div class="form-group">';
                                                result += '<h4>' + jet_shipping_methods[k]['services'][i]['options'][j]['text'] + '</h4>';

                                            }


                                            if (type == 'selectbox') {
                                                result += '<select ';
                                                if (jet_shipping_methods[k]['services'][i]['options'][j]['mandatory'] == 1) {
                                                    result += ' required ';
                                                }
                                                result += ' name="' + jet_shipping_methods[k]['services'][i]['options'][j]['field'] + '" id="keen_' + jet_shipping_methods[k]['services'][i]['options'][j]['field'] + '">';

                                                if (jet_shipping_methods[k]['services'][i]['options'][j]['mandatory'] == 0) {
                                                    result += '<option value="">Kies evt. een optie</option>';
                                                }

                                                if (Object.keys(jet_shipping_methods[k]['services'][i]['options'][j]['choices']).length > 0) {
                                                    for (var l in jet_shipping_methods[k]['services'][i]['options'][j]['choices']) {
                                                        if (typeof jet_shipping_methods[k]['services'][i]['options'][j]['choices'] !== 'function') {
                                                            result += '<option value="' + jet_shipping_methods[k]['services'][i]['options'][j]['choices'][l]['value'] + '">' + jet_shipping_methods[k]['services'][i]['options'][j]['choices'][l]['text'] + '</option>';
                                                        }
                                                    }
                                                }
                                                result += '</select>';
                                            }


                                            if (type == 'radio') {

                                                if (Object.keys(jet_shipping_methods[k]['services'][i]['options'][j]['choices']).length > 0) {
                                                    for (var l in jet_shipping_methods[k]['services'][i]['options'][j]['choices']) {
                                                        if (typeof jet_shipping_methods[k]['services'][i]['options'][j]['choices'] !== 'function') {
                                                            result += '<label><input type="radio" ';
                                                            result += ' name="' + jet_shipping_methods[k]['services'][i]['options'][j]['field'] + '" ';
                                                            if (jet_shipping_methods[k]['services'][i]['options'][j]['mandatory'] == 1) {
                                                                result += ' required ';
                                                            }
                                                            result += ' value="' + jet_shipping_methods[k]['services'][i]['options'][j]['choices'][l]['value'] + '" />' + jet_shipping_methods[k]['services'][i]['options'][j]['choices'][l]['text'] + '</label>';
                                                        }
                                                    }
                                                }

                                            }


                                            if (type == 'checkbox') {

                                                result += '<input type="checkbox" ';
                                                if (jet_shipping_methods[k]['services'][i]['options'][j]['mandatory'] == 1) {
                                                    result += ' required ';
                                                }

                                                result += ' name="' + (jet_shipping_methods[k]['services'][i]['options'][j]['field']) + '" id="keen_' + jet_shipping_methods[k]['services'][i]['options'][j]['field'] + '" />';

                                            }


                                            if (type == 'textbox' || type == 'date' || type == 'email' || type == 'hidden') {

                                                result += '<input ';

                                                if (type == 'textbox') {
                                                    result += ' type="text" ';
                                                } else if (type == 'email') {
                                                    result += ' type="email" ';
                                                } else if (type == 'hidden') {
                                                    result += ' type="hidden" value="' + jet_shipping_methods[k]['services'][i]['options'][j]['choices']['value'] + '" ';
                                                } else if (type == 'date') {
                                                    result += ' type="date" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" ';


                                                }


                                                if (jet_shipping_methods[k]['services'][i]['options'][j]['mandatory'] == 1) {
                                                    result += ' required ';
                                                }

                                                result += ' name="' + (jet_shipping_methods[k]['services'][i]['options'][j]['field']) + '" id="keen_' + jet_shipping_methods[k]['services'][i]['options'][j]['field'] + '" />';

                                            }

                                            if (type != 'hidden') {
                                                result += '</div>';
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    jQuery('#keen_service_options').html('');
    jQuery('#keen_service_options').html(result);

    jQuery('[type=date]').datepicker({dateFormat: 'dd-mm-yy'});
}

function set_keen_prefered_data(prefered_data) {


        var arrayOfStrings = prefered_data.split('&');
        for (i = 0; i < arrayOfStrings.length; i++) {
            value = arrayOfStrings[i];
            arrValue = value.split('=');

            element = jQuery('[name="' + arrValue[0] + '"]');
            if (element) {
                if (arrValue[0] == 'product') {
                    element.val(arrValue[1]);
                    set_keen_services();

                } else if (arrValue[0] == 'service') {
                    element.val(arrValue[1]);
                    set_keen_service_options();

                } else {

                    if (element.is(':checkbox'))  {
                        element.prop( "checked", true );
                    } else {
                        element.val(arrValue[1]);
                    }

                }

            }
        }
}