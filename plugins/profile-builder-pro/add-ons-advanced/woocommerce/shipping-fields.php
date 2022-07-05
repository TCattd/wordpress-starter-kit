<?php
/* returns WooCommerce shipping fields */
function wppb_in_woo_get_shipping_fields(){
    return array(
        'shipping_country' => array( 'label' => __('Country','profile-builder'), 'required' => 'Yes'),
        'shipping_first_name' => array( 'label' => __('First Name','profile-builder'), 'required' => 'Yes'),
        'shipping_last_name'=> array( 'label' => __('Last Name','profile-builder'), 'required' => 'Yes'),
        'shipping_company' => array( 'label' => __('Company Name','profile-builder'), 'required' => 'No'),
        'shipping_address_1' => array( 'label' => __('Address','profile-builder'), 'required' => 'Yes'),
        'shipping_address_2' => array( 'label' => '', 'required' => 'No'),
        'shipping_city' => array( 'label' => __('Town / City','profile-builder'), 'required' => 'Yes'),
        'shipping_state' => array( 'label' => __('State / County','profile-builder'), 'required' => 'Yes'),
        'shipping_postcode' => array( 'label' => __('Postcode / Zip','profile-builder'), 'required' => 'Yes')
    );
}

/* returns WooCommerce shipping fields array for front-end display */
function wppb_in_woo_shipping_fields_array(){

    $shipping_fields_array = wppb_in_woo_get_shipping_fields();

    $field = wppb_in_woo_get_field('WooCommerce Customer Shipping Address');

    // Check if there are any saved values for how and which shipping fields to display
    if ( !empty($field['field']) && !empty($field['woo-shipping-fields']) ) {

        $keys =  array_map('trim', explode(',', $field['woo-shipping-fields']));

        // get individual field names edited by the user in the UI and put them into an associative array
        $fields_name_array = array();
        if ( !empty($field['woo-shipping-fields-name']) )
            $fields_name_array = json_decode( $field['woo-shipping-fields-name'], true);

        $selected_shipping_fields_array = array();

        foreach ( $keys as $field_meta ) {

            if ( array_key_exists($field_meta, $shipping_fields_array)) {

                        // Check if we don't have a different field name inserted by the user
                        if ( !empty($fields_name_array) && ( array_key_exists($field_meta, $fields_name_array) ) )
                            $selected_shipping_fields_array[$field_meta]['label'] = $fields_name_array[$field_meta];
                        else
                            $selected_shipping_fields_array[$field_meta]['label'] = $shipping_fields_array[$field_meta]['label'];

                        // We set required to "No" by default, will set the required fields below using the saved values
                        $selected_shipping_fields_array[$field_meta]['required'] = 'No';

                    }
                    else {

                        $meta = str_replace('required_', '',  $field_meta);
                        // mark as required only the fields checked to be displayed, ignore the rest
                        if ( in_array($meta, $keys) )
                            $selected_shipping_fields_array[$meta]['required'] = 'Yes';

                    }

        }

        $shipping_fields_array = $selected_shipping_fields_array;

        // Do not display "Address line 2" field name in the front-end if it hasn't been changed
        if ( isset($shipping_fields_array['shipping_address_2']) && $shipping_fields_array['shipping_address_2']['label'] == 'Address line 2' )
            $shipping_fields_array['shipping_address_2']['label'] = '';

    }

    return apply_filters('wppb_woo_shipping_fields', $shipping_fields_array);
}

function wppb_in_woo_shipping_state_not_required( $fields ){

    if ( !empty($_POST['shipping_country'] ) && class_exists('WC_Countries') ) {

        $WC_Countries_Obj = new WC_Countries();
        $locale = $WC_Countries_Obj->get_country_locale();

        if ( isset( $locale[$_POST['shipping_country']]['state']['required'] ) && $locale[$_POST['shipping_country']]['state']['required'] === false ) { //phpcs:ignore

            if (is_array($fields) && isset($fields['shipping_state'])) {

                $fields['shipping_state']['required'] = 'No';
                return $fields;

            }
        }

    }

    return $fields;
}
add_filter('wppb_woo_shipping_fields','wppb_in_woo_shipping_state_not_required');

/* handle field output */
function wppb_in_woo_shipping_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){
    if ( $field['field'] == 'WooCommerce Customer Shipping Address' ){
        $shipping_fields = wppb_in_woo_shipping_fields_array();

        $output = '<ul class="wppb-woo-shipping-fields">';

        // Add a header to WooCommerce shipping fields
        $shipping_heading = '<li class="wppb-form-field wppb_shipping_heading"><h4>'.wppb_icl_t('plugin profile-builder-pro', 'custom_field_'.$field['id'].'_title_translation', $field['field-title']).'</h4></li>';
        $output .= apply_filters('wppb_woo_shipping_fields_heading', $shipping_heading);

        // Get allowed countries & states
        if ( class_exists('WC_Countries') ) {

            $WC_Countries_Obj = new WC_Countries();
            $country_array = $WC_Countries_Obj->get_shipping_countries();
            $default_country = $WC_Countries_Obj->get_base_country();   //get Base Location from WooCommerce settings
            $states_array = $WC_Countries_Obj->get_shipping_country_states();
            $locale = $WC_Countries_Obj->get_country_locale();

            // Check if base location is in the allowed shipping countries array, if not make it empty
            if ( ( !empty($default_country) ) && ( !array_key_exists($default_country, $country_array) ) ) {
                $default_country = '';
            }

            // Set the country field before anything else, because sometimes it's used to display the available states for the State drop-down select field
            if ( ( array_key_exists('shipping_country', $shipping_fields) ) && ($form_location != 'register') )
                $shipping_country = get_user_meta($user_id, 'shipping_country', true);
            else
                $shipping_country = '';

            //Display each individual shipping field
            foreach ($shipping_fields as $field_key => $field_val) {

                // check for WPML translations
                $field_val['label'] = wppb_icl_t( 'plugin profile-builder-pro', 'woocommerce_' . $field_key . '_label_translation', $field_val['label'] );

                if ($form_location != 'register') {
                    $$field_key = get_user_meta($user_id, $field_key, true);
                } else {
                    $$field_key = '';
                }

                $$field_key = (isset($request_data[$field_key]) ? trim($request_data[$field_key]) : $$field_key);

                // For Shipping State check whether the field is required or not
                if ($field_key == 'shipping_state') {

                    $selected_country = (($shipping_country != '') ? $shipping_country : $default_country);
                    if ( isset( $locale[$selected_country]['state']['required'] ) && ( $locale[$selected_country]['state']['required'] == false ) )  {
                        $field_val['required'] = 'No';
                    }

                }

                // displaying error messages for each individual field
                $error_mark = (($field_val['required'] == 'Yes') ? '<span class="wppb-required" title="' . wppb_required_field_error($field_val["label"]) . '">*</span>' : '');
                $is_error = wppb_in_check_woo_individual_fields_val('', $field_val, $field_key, $request_data, $form_location);
                $error_class = '';

                //Do not display errors for Shipping Fields if "Ship to a different address?" checkbox is not checked
                if (isset($_POST['woo_different_shipping_address']) && ($_POST['woo_different_shipping_address'] == 'no')) $is_error = '';

                if ($is_error != '') {
                    $error_mark = '<img src="' . WPPB_PLUGIN_URL . 'assets/images/pencil_delete.png" title="' . wppb_required_field_error($field_val["label"]) . '"/>';
                    $error_class = ' wppb-field-error';
                }

                $extra_attribute = apply_filters('wppb_woo_extra_attribute', '', $field_val);


                if ($field_key == 'shipping_country') {

                    $output .= '<li class="wppb-form-field wppb_' . $field_key . $error_class . '">
                            <label for="' . $field_key . '">' . $field_val['label'] . $error_mark . '</label>
                            <select name="' . $field_key . '" id="' . $field_key . '" class="country_to_state custom_field_country_select">';
                    $output .= '<option value="" selected >' . esc_attr__('Select an option&hellip;', 'woocommerce') . '</option>'; //phpcs:ignore

                    foreach ($country_array as $country_key => $country_value) {

                        $output .= '<option value="' . $country_key . '"';
                        // On Register form select the Base Location set in WooCommerce settings for the Country Select field
                        if (($form_location == 'register') && empty($$field_key) && ($country_key == $default_country))
                            $output .= ' selected';

                        if ($$field_key === $country_key)
                            $output .= ' selected';

                        $output .= '>' . $country_value . '</option>';

                    }

                    $output .= '</select>' . $is_error . '</li>';

                } elseif ($field_key == 'shipping_state') {

                    $selected_country = (($shipping_country != '') ? $shipping_country : $default_country);

                    if ( isset($states_array[$selected_country]) ) {

                        $style = ( empty($states_array[$selected_country]) ) ? 'display:none' : '';

                        $output .= '<li style="'.$style.';" class="wppb-form-field wppb_' . $field_key . $error_class . '">
                                    <label for="' . $field_key . '">' . $field_val['label'] . $error_mark . '</label>';

                        // Display a select with the available States
                        $output .= '<select name="' . $field_key . '" id="' . $field_key . '" class="custom_field_state_select">';
                        $output .= '<option value="" selected >' . esc_attr__('Select an option&hellip;', 'woocommerce') . '</option>'; //phpcs:ignore

                        foreach ( $states_array[$selected_country] as $key => $value ) {
                            $output .= '<option value="' . $key . '"';

                            if ($$field_key === $key)
                                $output .= ' selected';

                            $output .= '>' . $value . '</option>';
                        }

                        $output .= '</select>' . $is_error . '</li>';

                    } else {
                        //display an input if selected country has no states
                        $output .= '<li class="wppb-form-field wppb_' . $field_key . $error_class . '">
                                   <label for="' . $field_key . '">' . $field_val['label'] . $error_mark . '</label>';
                        $output .= '<input class="extra_field_input" name="' . $field_key . '" type="text" id="' . $field_key . '" value="' . esc_attr(wp_unslash($$field_key)) . '" ' . $extra_attribute . '/> ' . $is_error . '</li>';
                    }

                } else {
                    // Output a standard input field (if it's not country or state select)
                    $output .= '<li class="wppb-form-field wppb_' . $field_key . $error_class . '">
                            <label for="' . $field_key . '">' . $field_val['label'] . $error_mark . '</label>
                            <input class="extra_field_input" name="' . $field_key . '" type="text" id="' . $field_key . '" value="' . esc_attr(wp_unslash($$field_key)) . '" ' . $extra_attribute . '/>' . $is_error . '</li>';
                }

            } // end foreach

        } //end if ( class_exists('WC_Countries') )

        $output .= '</ul>';

        return apply_filters( 'wppb_'.$form_location.'_input_custom_field_'.$field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data );
    }
}
add_filter( 'wppb_output_form_field_woocommerce-customer-shipping-address', 'wppb_in_woo_shipping_handler', 10, 6 );


/* handle field save */
function wppb_in_save_woo_shipping_value( $field, $user_id, $request_data, $form_location ){
    if( $field['field'] == 'WooCommerce Customer Shipping Address' ){
        $shipping_fields = wppb_in_woo_shipping_fields_array();

        foreach ($shipping_fields as $field_key => $field_val) {

        // If "Ship to a different address?" exists and not checked save values from Billing Fields in Shipping Fields as well.
        if ( isset($_POST['woo_different_shipping_address']) && ($_POST['woo_different_shipping_address'] == 'no') ) {
            $billing_key = str_replace('shipping', 'billing', $field_key);
            if ( isset($request_data[$billing_key]) )
                update_user_meta($user_id, $field_key, $request_data[$billing_key]);
        }
        elseif ( isset($request_data[$field_key]) )
            update_user_meta($user_id, $field_key, $request_data[$field_key]);

        }
    }
}
add_action( 'wppb_save_form_field', 'wppb_in_save_woo_shipping_value', 10, 4 );


/* handle field validation, to not save data in case some required fields are not filled in */
function wppb_in_check_woo_shipping_value( $message, $field, $request_data, $form_location ){
    if ( ( $field['field'] == 'WooCommerce Customer Shipping Address' ) && ( ($form_location == 'edit_profile') || ($form_location == 'register') ) ) {

        // if "Ship to different address" checkbox exists and it's not checked then don't display the required field errors for Shipping Fields
        if (isset($_POST['woo_different_shipping_address']) && ($_POST['woo_different_shipping_address'] == 'no')) return $message;

        $shipping_fields = wppb_in_woo_shipping_fields_array();
        foreach ($shipping_fields as $field_key => $field_val) {
            if ( ($field_val['required'] == 'Yes') &&  isset( $request_data[$field_key] ) && ( trim( $request_data[$field_key] ) == '' )   )
                return wppb_required_field_error($field_key);
        }
    }
    return $message;
}
add_filter( 'wppb_check_form_field_woocommerce-customer-shipping-address', 'wppb_in_check_woo_shipping_value', 10, 4 );


/* Add shipping information to wp_signups table (when Email Confirmation is active)*/
function wppb_in_woo_meta_activation_shipping( $posted_value, $field, $global_request ){
    $shipping_fields = wppb_in_woo_shipping_fields_array();
    $return_values = array();
    foreach ($shipping_fields as $field_key => $field_val) {

    // If "Ship to a different address?" exists and not checked then add values from corresponding Billing Field for each Shipping Field to wp_signups.
    if (isset($_POST['woo_different_shipping_address']) && ($_POST['woo_different_shipping_address'] == 'no')) {
        $billing_key = str_replace('shipping', 'billing', $field_key);
        if ( isset($global_request[$billing_key]) )
            $return_values[$field_key] = $global_request[$billing_key];
    }
    elseif ( isset($global_request[$field_key]) )    // save the shipping info entered in the shipping field
        $return_values[$field_key] = $global_request[$field_key];

    }
    return $return_values;
}
add_filter( 'wppb_add_to_user_signup_form_field_woocommerce-customer-shipping-address', 'wppb_in_woo_meta_activation_shipping',10, 3 );


/* Add Woo Shipping meta on user activation*/
function wppb_in_woo_adding_shipping_meta_on_activation($user_id, $password, $meta){
    $shipping_fields = wppb_in_woo_shipping_fields_array();
    if ( isset($meta['wppbwoo_shipping']) ) {
        foreach ($shipping_fields as $field_key => $field_val) {

            if ( !empty($meta['wppbwoo_shipping'][$field_key]) ) {
                update_user_meta( $user_id, $field_key, $meta['wppbwoo_shipping'][$field_key] );
            }

        }
    }
}
add_action( 'wppb_add_meta_on_user_activation_woocommerce-customer-shipping-address', 'wppb_in_woo_adding_shipping_meta_on_activation', 10, 3 );
