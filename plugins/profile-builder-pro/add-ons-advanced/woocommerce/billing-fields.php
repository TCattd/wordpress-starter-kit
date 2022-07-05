<?php
/* returns WooCommerce billing fields*/
function wppb_in_woo_get_billing_fields(){
    return array(
        'billing_country' => array('label' => __('Country', 'profile-builder'), 'required' => 'Yes'),
        'billing_first_name' => array('label' => __('First Name', 'profile-builder'), 'required' => 'Yes'),
        'billing_last_name' => array('label' => __('Last Name', 'profile-builder'), 'required' => 'Yes'),
        'billing_company' => array('label' => __('Company Name', 'profile-builder'), 'required' => 'No'),
        'billing_address_1' => array('label' => __('Address', 'profile-builder'), 'required' => 'Yes'),
        'billing_address_2' => array('label' => '', 'required' => 'No'),
        'billing_city' => array('label' => __('Town / City', 'profile-builder'), 'required' => 'Yes'),
        'billing_state' => array('label' => __('State / County', 'profile-builder'), 'required' => 'Yes'),
        'billing_postcode' => array('label' => __('Postcode / Zip', 'profile-builder'), 'required' => 'Yes'),
        'billing_email' => array('label' => __('Email Address', 'profile-builder'), 'required' => 'Yes'),
        'billing_phone' => array('label' => __('Phone', 'profile-builder'), 'required' => 'Yes')
    );
}


/* returns WooCommerce billing fields array for front-end display */
function wppb_in_woo_billing_fields_array() {

    $billing_fields_array = wppb_in_woo_get_billing_fields();

    $field = wppb_in_woo_get_field('WooCommerce Customer Billing Address');

    // Check if there are any saved values for which individual billing fields to display
    if ( !empty($field) && !empty($field['woo-billing-fields']) ) {

        $keys =  array_map('trim', explode(',', $field['woo-billing-fields']));

        // get individual field names edited by the user in the UI and put them into an associative array
        $fields_name_array = array();
        if ( !empty($field['woo-billing-fields-name']) )
            $fields_name_array = json_decode( $field['woo-billing-fields-name'], true);

        $selected_billing_fields_array = array();

        foreach ( $keys as $field_meta ) {

            if ( array_key_exists($field_meta, $billing_fields_array)) {

                        // Check if we don't have a different field name inserted by the user
                        if ( !empty($fields_name_array) && ( array_key_exists($field_meta, $fields_name_array) ) )
                            $selected_billing_fields_array[$field_meta]['label'] = $fields_name_array[$field_meta];
                        else
                            $selected_billing_fields_array[$field_meta]['label'] = $billing_fields_array[$field_meta]['label'];

                        // We set required to "No" by default, will set the required fields below using the saved values
                        $selected_billing_fields_array[$field_meta]['required'] = 'No';

                    }
                    else {

                        $meta = str_replace('required_', '',  $field_meta);
                        if ( in_array($meta, $keys) )
                            $selected_billing_fields_array[$meta]['required'] = 'Yes';

                    }

        }

        $billing_fields_array = $selected_billing_fields_array;

        // Do not display "Address line 2" field name in the front-end if it hasn't been changed
        if ( isset($billing_fields_array['billing_address_2']) && $billing_fields_array['billing_address_2']['label'] == 'Address line 2' )
                $billing_fields_array['billing_address_2']['label'] = '';
    }

    return apply_filters('wppb_woo_billing_fields', $billing_fields_array);
}

function wppb_in_woo_billing_state_not_required( $fields ){

    if ( !empty($_POST['billing_country'] ) && class_exists('WC_Countries') ) {

        $WC_Countries_Obj = new WC_Countries();
        $locale = $WC_Countries_Obj->get_country_locale();

        if ( isset( $locale[$_POST['billing_country']]['state']['required'] ) && $locale[$_POST['billing_country']]['state']['required'] === false  ) { //phpcs:ignore

            if (is_array($fields) && isset($fields['billing_state'])) {

                $fields['billing_state']['required'] = 'No';
                return $fields;

            }
        }

    }

    return $fields;
}
add_filter('wppb_woo_billing_fields','wppb_in_woo_billing_state_not_required');

/* handle field output */
function wppb_in_woo_billing_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){
    if ( $field['field'] == 'WooCommerce Customer Billing Address' ){
        $billing_fields = wppb_in_woo_billing_fields_array();

        $output = '<ul class="wppb-woo-billing-fields">';

        // Add a header to WooCommerce billing fields
        $billing_heading = '<li class="wppb-form-field wppb_billing_heading"><h4>'.wppb_icl_t('plugin profile-builder-pro', 'custom_field_'.$field['id'].'_title_translation', $field['field-title']).'</h4></li>';
        $output .= apply_filters('wppb_woo_billing_fields_heading', $billing_heading);

        // Get allowed countries & states
        if ( class_exists('WC_Countries') ) {

            $WC_Countries_Obj = new WC_Countries();
            $country_array = $WC_Countries_Obj->get_allowed_countries();
            $default_country = $WC_Countries_Obj->get_base_country();
            $states_array = $WC_Countries_Obj->get_allowed_country_states();
            $locale = $WC_Countries_Obj->get_country_locale();

            // Check if base location is in the allowed countries array, if not make it empty
            if ( ( !empty($default_country) ) && ( !array_key_exists($default_country, $country_array) ) ) {
                $default_country = '';
            }

            // Set the country field before anything else, because sometimes it's used to display the available states for the State drop-down select field
            if ( ( array_key_exists('billing_country', $billing_fields) ) && ($form_location != 'register') )
                $billing_country = get_user_meta($user_id, 'billing_country', true);
            else
                $billing_country = '';

            //Display each individual billing field
            foreach ($billing_fields as $field_key => $field_val) {

                // check for WPML translations
                $field_val['label'] = wppb_icl_t( 'plugin profile-builder-pro', 'woocommerce_' . $field_key . '_label_translation', $field_val['label'] );

                if ($form_location != 'register') {
                    $$field_key = get_user_meta($user_id, $field_key, true);
                } else {
                    $$field_key = '';
                }

                $$field_key = (isset($request_data[$field_key]) ? trim($request_data[$field_key]) : $$field_key);

                // For Billing State check whether the field is required or not
                if ($field_key == 'billing_state') {

                    $selected_country = (($billing_country != '') ? $billing_country : $default_country);
                    if ( isset( $locale[$selected_country]['state']['required'] ) && ( $locale[$selected_country]['state']['required'] == false ) )  {
                        $field_val['required'] = 'No';
                    }

                }

                // displaying error messages for each individual field
                $error_mark = (($field_val['required'] == 'Yes') ? '<span class="wppb-required" title="' . wppb_required_field_error($field_val["label"]) . '">*</span>' : '');
                $is_error = wppb_in_check_woo_individual_fields_val('', $field_val, $field_key, $request_data, $form_location);
                $error_class = '';

                if ($is_error != '') {
                    $error_mark = '<img src="' . WPPB_PLUGIN_URL . 'assets/images/pencil_delete.png" title="' . wppb_required_field_error($field_val["label"]) . '"/>';
                    $error_class = ' wppb-field-error';
                }

                $extra_attribute = apply_filters('wppb_woo_extra_attribute', '', $field_val);


                if ($field_key == 'billing_country') {

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

                } elseif ($field_key == 'billing_state') {

                    $selected_country = (($billing_country != '') ? $billing_country : $default_country);


                    if ( isset($states_array[$selected_country]) ) {

                            $style = ( empty($states_array[$selected_country]) ) ? 'display:none' : '';

                            $output .= '<li style="'.$style.';" class="wppb-form-field wppb_' . $field_key . $error_class . '">
                                       <label for="' . $field_key . '">' . $field_val['label'] . $error_mark . '</label>';

                            // Display a select with the available States
                            $output .= '<select name="' . $field_key . '" id="' . $field_key . '" class="custom_field_state_select">';
                            $output .= '<option value="" selected >' . esc_attr__('Select an option&hellip;', 'woocommerce') . '</option>'; //phpcs:ignore

                            foreach ($states_array[$selected_country] as $key => $value) {
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

        /*
        *
        * Add "Ship to a different address?" checkbox on Register form at the end of Billing Fields (if not checked we won't display the Shipping Fields)
        * */

        //Check if Woo Shipping Address is added in Manage Fields
        $in_manage_fields = false;
        if (get_option('wppb_manage_fields')) {
            $wppb_manage_fields = get_option('wppb_manage_fields');
            foreach ($wppb_manage_fields as $key => $value) {
                if ($value['field'] == 'WooCommerce Customer Shipping Address') {
                    $in_manage_fields = true;
                    break;
                }
            }
        }

        // Display "Ship to a different address" checkbox if we're on the PB Register form and Woo Shipping Address is added under Manage Fields
        if ( ($form_location == 'register') && $in_manage_fields ) {
            ?><script type="text/javascript">
                jQuery(document).ready(function(){
                    wppb_in_woo_toggle_required_attrbute();
                    jQuery("#woo_different_shipping_address").on( 'click', function(){
                        wppb_in_woo_toggle_required_attrbute();
                        jQuery(".wppb-woocommerce-customer-shipping-address").toggle();
                    });
                    function wppb_in_woo_toggle_required_attrbute(){
                        // Trigger a custom event that will remove the HTML attribute -required- for hidden fields. This is necessary for browsers to allow form submission.
                        if(document.getElementById('woo_different_shipping_address').checked) {
                            jQuery(".wppb-woocommerce-customer-shipping-address input" ).trigger( "wppbAddRequiredAttributeEvent" );
                        } else {
                            jQuery(".wppb-woocommerce-customer-shipping-address input" ).trigger( "wppbRemoveRequiredAttributeEvent" );
                        }
                    }
                });
            </script> <?php
            $checked = 'checked';
            if ( isset($_POST['woo_different_shipping_address']) && ($_POST['woo_different_shipping_address'] == 'no') ) {
                $checked = '';
                echo '<style> .wppb-woocommerce-customer-shipping-address {display:none;}  </style>';
            }
            $different_address_checkbox = '
                    <ul>
                        <li class=" wppb-form-field wppb-shipping-different-address ">
                        <label for="woo_different_shipping_address">
                        <input type="hidden" name="woo_different_shipping_address" value="no" />
                        <input id="woo_different_shipping_address" type="checkbox" name="woo_different_shipping_address" value="yes" '.$checked.'>
                        <strong>'. __('Ship to a different address?','profile-builder').'</strong> </label>
                        </li>
                    </ul>';
            $output .= apply_filters('wppb_woo_shipping_fields_ship_to_different_address', $different_address_checkbox);
        }


        return apply_filters( 'wppb_'.$form_location.'_input_custom_field_'.$field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data );
    }
}
add_filter( 'wppb_output_form_field_woocommerce-customer-billing-address', 'wppb_in_woo_billing_handler', 10, 6 );


/* handle field save */
function wppb_in_save_woo_billing_value( $field, $user_id, $request_data, $form_location ){
    if( $field['field'] == 'WooCommerce Customer Billing Address' ) {
        $billing_fields = wppb_in_woo_billing_fields_array();
        foreach ($billing_fields as $field_key => $field_val) {
            if (isset($request_data[$field_key])) {
                update_user_meta($user_id, $field_key, $request_data[$field_key]);
            }
        }
    }
}
add_action( 'wppb_save_form_field', 'wppb_in_save_woo_billing_value', 10, 4 );


/* handle field validation, to not save data in case some required fields are not filled in */
function wppb_in_check_woo_billing_value( $message, $field, $request_data, $form_location ){
    if (($field['field'] == 'WooCommerce Customer Billing Address' )&& (($form_location == 'edit_profile')||($form_location == 'register')))  {
        $billing_fields = wppb_in_woo_billing_fields_array();
        foreach ($billing_fields as $field_key => $field_val) {
            if ( ($field_val['required'] == 'Yes') &&  isset( $request_data[$field_key] ) && ( trim( $request_data[$field_key] ) == '' )   )
                return wppb_required_field_error($field_key);
        }
    }
    return $message;
}
add_filter( 'wppb_check_form_field_woocommerce-customer-billing-address', 'wppb_in_check_woo_billing_value', 10, 4 );


/* Add billing information to wp_signups table (when Email Confirmation is active) */
function wppb_in_woo_meta_activation_billing( $posted_value, $field, $global_request ){
    $billing_fields = wppb_in_woo_billing_fields_array();
    $return_values = array();
    foreach ($billing_fields as $field_key => $field_val) {
        $return_values[$field_key] =  $global_request[$field_key];
    }
    return $return_values;
}
add_filter( 'wppb_add_to_user_signup_form_field_woocommerce-customer-billing-address', 'wppb_in_woo_meta_activation_billing',10, 3 );


/* Add Woo Billing meta on user activation*/
function wppb_in_woo_adding_billing_meta_on_activation($user_id, $password, $meta){
    $billing_fields = wppb_in_woo_billing_fields_array();
    if ( isset($meta['wppbwoo_billing']) ) {
        foreach ($billing_fields as $field_key => $field_val) {
            update_user_meta( $user_id, $field_key, $meta['wppbwoo_billing'][$field_key] );
        }
    }
}
add_action( 'wppb_add_meta_on_user_activation_woocommerce-customer-billing-address', 'wppb_in_woo_adding_billing_meta_on_activation', 10, 3 );

