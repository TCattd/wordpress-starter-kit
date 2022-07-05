<?php
/**
 * Profile Builder - WooCommerce Sync Add-on
 * WC requires at least: 2.5.0
 * WC tested up to: 6.1
 */
/*  Copyright 2017 Cozmoslabs (www.cozmoslabs.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
/*
* Define plugin path
*/
define('WPPBWOO_IN_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . dirname(plugin_basename(__FILE__)));


/*
 * Include the file for creating the WooCommerce Sync subpage
 */
if (file_exists(WPPBWOO_IN_PLUGIN_DIR . '/woosync-page.php'))
    include_once(WPPBWOO_IN_PLUGIN_DIR . '/woosync-page.php');

// Makes sure the plugin is defined before trying to use it
if ( ! function_exists( 'is_plugin_active_for_network' ) )
	require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

// Check if WooCommerce is active
if ( ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) || (is_plugin_active_for_network('woocommerce/woocommerce.php')) )  {

    /* Allow PB to manage Billing fields from WooCommerce*/
    if (file_exists(WPPBWOO_IN_PLUGIN_DIR . '/billing-fields.php'))
        include_once(WPPBWOO_IN_PLUGIN_DIR . '/billing-fields.php');

    /* Allow PB to manage Shipping fields from WooCommerce*/
    if (file_exists(WPPBWOO_IN_PLUGIN_DIR . '/shipping-fields.php'))
        include_once(WPPBWOO_IN_PLUGIN_DIR . '/shipping-fields.php');

    /* Add support for custom fields created with PB to be displayed on WooCommerce Checkout page  */
    if (file_exists(WPPBWOO_IN_PLUGIN_DIR . '/woo-checkout-field-support.php'))
        include_once(WPPBWOO_IN_PLUGIN_DIR . '/woo-checkout-field-support.php');

    //Clear woocommerce cart session after saving billing and shipping fields.
    function wppb_in_woo_clear_session_on_save( $request, $form_name, $user_id ){
        if (class_exists('WC_Session_Handler')) {
            $wc_session = new WC_Session_Handler;

            if ( $wc_session->has_session() ){

                $wc_session_parts = $wc_session->get_session_cookie();

                if ( $wc_session_parts !== false ) {
                    $customer_id = $wc_session_parts[0];

                    $wc_session->delete_session( $customer_id );
                }

            }
        }
    }
    add_action( 'wppb_edit_profile_success', 'wppb_in_woo_clear_session_on_save', 20, 4 );



    //Add Country Select script
    function wppb_in_woo_country_select_scripts(){
        wp_register_script( 'woo-country-select', plugin_dir_url(__FILE__) . 'assets/js/country-select.js' , array('jquery'));

        if ( class_exists('WC_Countries') ) {
            $WC_Countries_Obj = new WC_Countries();
            $locale = $WC_Countries_Obj->get_country_locale();
        }
        else $locale = array();

        // Localize the script with new data
        $translation_array = array(
            'countries'              => json_encode( WC()->countries ? array_merge(WC()->countries->get_allowed_country_states(), WC()->countries->get_shipping_country_states()) : array() ),
            'i18n_select_state_text' => esc_attr__( 'Select an option&hellip;', 'woocommerce' ), //phpcs:ignore
            'locale'                 => json_encode( $locale )
        );
        wp_localize_script( 'woo-country-select', 'wc_country_select_params', $translation_array );
        wp_enqueue_script( 'woo-country-select' );
    }
    add_action('wp_enqueue_scripts','wppb_in_woo_country_select_scripts');


    // Function that enqueues the necessary admin scripts
    function wppb_in_woo_sync_scripts( $hook ){
		if ( $hook == 'profile-builder_page_manage-fields' ) {
			wp_enqueue_script( 'wppb-woo-sync', plugin_dir_url( __FILE__ ) . 'assets/js/main.js', array( 'jquery', 'wppb-manage-fields-live-change' ), PROFILE_BUILDER_VERSION, true );
		}
    }
    add_action('admin_enqueue_scripts', 'wppb_in_woo_sync_scripts');


    //Add Billing and Shipping fields in the backend fields drop-down select
    add_filter('wppb_manage_fields_types', 'wppb_in_add_woo_billing_shipping_fields');

    // Add Woo Shipping and Billing fields to the unique fields list + skip check for empty meta
    add_filter('wppb_unique_field_list', 'wppb_in_add_woo_billing_shipping_fields');

    function wppb_in_add_woo_billing_shipping_fields($fields){
        $fields[] = 'WooCommerce Customer Billing Address';
        $fields[] = 'WooCommerce Customer Shipping Address';
        return $fields;
    }

    /* Function that returns the fields array with their new names (the ones inserted by the user ) */
    function wppb_in_woo_get_fields_edited_names( $field_name ){

        $field = wppb_in_woo_get_field( $field_name );

        //get default field names array
        switch ( $field_name ) {

            case 'WooCommerce Customer Billing Address':
                $fields_array = wppb_in_woo_get_billing_fields();
                $meta = 'woo-billing-fields-name';
                break;

            case 'WooCommerce Customer Shipping Address':
                $fields_array = wppb_in_woo_get_shipping_fields();
                $meta = 'woo-shipping-fields-name';
                break;

            default:
                $fields_array = array();
        }

        if ( !empty($fields_array) && !empty($field) && !empty($field[$meta]) ) {

            // get individual field names edited by the user in the UI and put them into an associative array
            $fields_name_array = json_decode($field[$meta], true);

            if ( !empty($fields_name_array) ) {

                foreach ($fields_name_array as $key => $value) {
                    $fields_array[$key]['label'] = $value;
                }

            }

        }

        return $fields_array;

    }

    /* Function that returns the field value for a given field_name ; empty if not found */
    function wppb_in_woo_get_field ( $field_name ){
        $manage_fields = get_option('wppb_manage_fields', 'not_found');

        if ($manage_fields != 'not_found') {

            foreach ($manage_fields as $field_key => $field_value) {

                if ( ($field_value['field'] == $field_name) )
                    return $field_value;
            }
        }

        return ''; // return empty if not found
    }

    // WPML support for WooCommerce Billing/Shipping form labels
    add_action( 'wck_before_update_meta', 'wppb_in_woo_billing_shipping_wpml_support', 10, 3 );
    function wppb_in_woo_billing_shipping_wpml_support( $meta, $id, $values ) {
        if( $meta === 'wppb_manage_fields' && function_exists( 'wppb_icl_register_string' )) {

            if ( $values['meta-name'] === 'wppbwoo_billing' )
                $register_strings_array = json_decode( stripslashes( $values['woo-billing-fields-name'] ), true);
            elseif ( $values['meta-name'] === 'wppbwoo_shipping' )
                $register_strings_array = json_decode( stripslashes( $values['woo-shipping-fields-name'] ), true);

            if ( !empty( $register_strings_array )) {
                foreach ($register_strings_array as $reg_key => $reg_value) {
                    wppb_icl_register_string('plugin profile-builder-pro', 'woocommerce_' . $reg_key . '_label_translation', $reg_value);
                }
            }

        }
    }

    // Add support for selecting which individual WooCommerce Shipping and Billing fields to display
    add_filter('wppb_manage_fields', 'wppb_in_woo_add_support_individual_billing_shipping_fields');
    function wppb_in_woo_add_support_individual_billing_shipping_fields( $fields ){

        $billing_fields_array = wppb_in_woo_get_fields_edited_names('WooCommerce Customer Billing Address');
        $default_billing_fields = array();

        $billing_fields = array();
        foreach( $billing_fields_array as $key => $value ) {
                if ( ($key == 'billing_address_2') && ($value['label'] == '') ) $value['label'] = __('Address line 2','profile-builder');
                array_push( $billing_fields, '%' . $value['label'] . '%' . $key );
                array_push( $billing_fields, '%' . '' . '%' . 'required_' . $key );
                // set default billing fields and corresponding 'required' values
                array_push( $default_billing_fields, $key);
                if ($value['required'] == 'Yes') array_push( $default_billing_fields, 'required_' . $key );
        }

        $fields[] = array( 'type' => 'woocheckbox', 'slug' => 'woo-billing-fields', 'title' => __( 'Billing Fields', 'profile-builder' ), 'options' => $billing_fields, 'default-options' => $default_billing_fields, 'description' => __( "Select which WooCommerce Billing fields to display to the user ( drag and drop to re-order ) and which should be required", 'profile-builder' ) );
        $fields[] = array( 'type' => 'text', 'slug' => 'woo-billing-fields-sort-order', 'title' => __( 'Billing Fields Order', 'profile-builder' ), 'description' => __( "Save the billing fields order from the billing fields checkboxes", 'profile-builder' ) );
        $fields[] = array( 'type' => 'text', 'slug' => 'woo-billing-fields-name', 'title' => __( 'Billing Fields Name', 'profile-builder' ), 'description' => __( "Save the billing fields names", 'profile-builder' ) );


        $shipping_fields_array = wppb_in_woo_get_fields_edited_names('WooCommerce Customer Shipping Address');
        $default_shipping_fields = array();


        $shipping_fields = array();
        foreach( $shipping_fields_array as $key => $value ) {
            if ($key == 'shipping_address_2') $value['label'] = __('Address line 2','profile-builder');
            array_push( $shipping_fields, '%' . $value['label'] . '%' . $key );
            array_push( $shipping_fields, '%' . '' . '%' . 'required_' . $key );
            // set default shipping fields and corresponding 'required' values
            array_push( $default_shipping_fields, $key);
            if ($value['required'] == 'Yes') array_push( $default_shipping_fields, 'required_' . $key );
        }

        $fields[] = array( 'type' => 'woocheckbox', 'slug' => 'woo-shipping-fields', 'title' => __( 'Shipping Fields', 'profile-builder' ), 'options' => $shipping_fields, 'default-options' => $default_shipping_fields, 'description' => __( "Select which WooCommerce Shipping fields to display to the user ( drag and drop to re-order ) and which should be required", 'profile-builder' ) );
        $fields[] = array( 'type' => 'text', 'slug' => 'woo-shipping-fields-sort-order', 'title' => __( 'Shipping Fields Order', 'profile-builder' ), 'description' => __( "Save the shipping fields order from the shipping fields checkboxes", 'profile-builder' ) );
        $fields[] = array( 'type' => 'text', 'slug' => 'woo-shipping-fields-name', 'title' => __( 'Shipping Fields Name', 'profile-builder' ), 'description' => __( "Save the shipping fields names", 'profile-builder' ) );

        return $fields;
    }

    /**
     * Function that calls the wppb_edit_form_properties (to initialize field sorting on edit)
     */
    function wppb_in_woo_initialize_sorting_on_edit( $meta_name, $id, $element_id ){

        if ( $meta_name == 'wppb_manage_fields' ) {
            echo "<script type=\"text/javascript\">wppb_handle_woosync_billing_shipping_field ( '#container_wppb_manage_fields', 'billing' );</script>";
            echo "<script type=\"text/javascript\">wppb_handle_woosync_billing_shipping_field ( '#container_wppb_manage_fields', 'shipping' );</script>";
        }
    }
    add_action('wck_after_adding_form', 'wppb_in_woo_initialize_sorting_on_edit', 20, 3);


    // Returns the html needed for displaying the custom 'woocheckbox' field type
    add_filter('wck_output_form_field_customtype_woocheckbox', 'wppb_in_woo_woocheckbox_form_field', 10, 4);
    function wppb_in_woo_woocheckbox_form_field($element, $value, $details, $single_prefix){

        if( !empty( $details['options'] ) ){

            // we need these to set placeholders for field name inputs based on the default names
            $billing_array = wppb_in_woo_get_billing_fields();
            $shipping_array = wppb_in_woo_get_shipping_fields();

            $element .= '<span class="wck-woocheckboxes-heading">'.__('Field Name', 'profile-builder').'</span>';
            $element .= '<span class="wck-woocheckboxes-heading">'.__('Required', 'profile-builder').'</span>';

            $element .= '<div class="wck-checkboxes wck-woocheckboxes">';

            $count = 0; // used to add each 2 consecutive checkboxes in a single div

            foreach( $details['options'] as $option ){
                $found = false;
                $count++;

                // if there aren't any previously saved values, use the default options
                if ( ( empty($value) ) && ( !empty($details['default-options']) ) )
                    $value = $details['default-options'];

                if( !is_array( $value ) )
                    $values = explode( ', ', $value );
                else
                    $values = $value;

                if( strpos( $option, '%' ) === false  ){
                    $label = $option;
                    $value_attr = $option;
                    if ( in_array( $option, $values ) )
                        $found = true;
                }
                else{
                    $option_parts = explode( '%', $option );
                    if( !empty( $option_parts ) ){
                        if( empty( $option_parts[0] ) && count( $option_parts ) == 3 ){
                            $label = $option_parts[1];
                            $value_attr = $option_parts[2];
                            if ( in_array( $option_parts[2], $values ) )
                                $found = true;
                        }
                    }
                }

                if ( ($count % 2 ) != 0 ) $element .= '<div>';

                $element .= '<label><input type="checkbox" name="'. $single_prefix . esc_attr( Wordpress_Creation_Kit_PB::wck_generate_slug( $details['title'], $details ) );

                $element .= '" id="';

                /* since the slug below is generated from the value as well we need to determine here if we have a slug or not and not let the wck_generate_slug() function do that */
                if( !empty( $details['slug'] ) )
                    $slug_from = $details['slug'];
                else
                    $slug_from = $details['title'];

                $element .= esc_attr( Wordpress_Creation_Kit_PB::wck_generate_slug( $slug_from . '_' . $value_attr ) ) .'" value="'. esc_attr( $value_attr ) .'"  '. checked( $found, true, false ) .'class="mb-checkbox mb-field" />';

                // Add the placeholder input only for Field Name, do not add it for Required checkbox
                if ( ($count % 2) != 0 ) {

                    // set value for the placeholder -> default field name
                    $placeholder = '';

                    if ( array_key_exists($value_attr , $billing_array ) )
                        $placeholder = ( $billing_array[$value_attr]['label'] != '') ? $billing_array[$value_attr]['label'] : __('Address line 2','profile-builder');

                    elseif ( array_key_exists($value_attr , $shipping_array ) )
                        $placeholder = ( $shipping_array[$value_attr]['label'] != '') ? $shipping_array[$value_attr]['label'] : __('Address line 2','profile-builder');

                    $element .= '<input type="text" value="'. esc_html( $label ) .'" placeholder="'. esc_html( $placeholder ) .'" title="'.__('Click to edit ', 'profile-builder'). esc_html( $placeholder ) .'" class="wck-woocheckbox-field-label" />';
                    $element .= '<span class="dashicons dashicons-edit"></span>';
                }

                $element .= '</label>';

                if ( ($count % 2) == 0 ) $element .= '</div>';
            }
            $element .= '</div>';
        }

        return $element;
    }




    // Handle field validation for each individual Billing and Shipping field
    function wppb_in_check_woo_individual_fields_val( $message, $field_val, $field_key, $request_data, $form_location ){
        if ( ($field_val['required'] == 'Yes') &&  isset( $request_data[$field_key] ) && ( trim( $request_data[$field_key] ) == '' )   ) {
            return '<span class="wppb-form-error">'.wppb_required_field_error($field_key).'</span>';
        }
        //For Billing Email field check if it's a valid email
        if ( ($field_key == 'billing_email') && ($field_val['required'] == 'Yes') && isset( $request_data[$field_key]) && !is_email( trim( $request_data['billing_email'] ) ) ) {
            return '<span class="wppb-form-error">'.__('The email you entered is not a valid email address.', 'profile-builder').'</span>';
        }
        return $message;
    }


    // Add extra styling for WooCommerce form fields
    function wppb_in_woo_add_plugin_stylesheet($hook) {

        if  ( file_exists( plugin_dir_path(__FILE__).'/assets/css/style-fields.css' ) )  {
            // Add style only on the Manage Fields page in backend
            if ( (!empty($hook)) && ($hook == 'profile-builder_page_manage-fields') ) {
                wp_register_style( 'wppb_woo_stylesheet', plugin_dir_url(__FILE__) . 'assets/css/style-fields.css');
                wp_enqueue_style( 'wppb_woo_stylesheet' );
            }
        }

    }
    add_action('admin_enqueue_scripts' , 'wppb_in_woo_add_plugin_stylesheet');


	// Add style to front-end
	function wppb_in_woo_add_scripts_front_end() {
		wp_register_style( 'wppb_woo_stylesheet', plugin_dir_url(__FILE__) . 'assets/css/style-fields.css', array('wppb_stylesheet'));
		wp_enqueue_style( 'wppb_woo_stylesheet' );
        if ( is_checkout() || is_account_page()) {
            wp_enqueue_script( 'wppb_woo_checkout', plugin_dir_url(__FILE__) . 'assets/js/checkout.js');
        }

	}
	add_action('wp_enqueue_scripts' , 'wppb_in_woo_add_scripts_front_end');

    //Remove Woo Billing and Shipping fields from UserListing & Email Confirmation merge tags (available Meta and Sort Variables list)
    function wppb_in_woo_remove_shipping_billing_from_userlisting_ec($manage_fields){
        foreach ($manage_fields as $key => $value){
            if (($value['field'] == 'WooCommerce Customer Billing Address') || ($value['field'] == 'WooCommerce Customer Shipping Address')) unset($manage_fields[$key]);
        }
        return array_values($manage_fields);
    }
    add_filter('wppb_userlisting_merge_tags', 'wppb_in_woo_remove_shipping_billing_from_userlisting_ec');
    add_filter('wppb_email_customizer_get_fields', 'wppb_in_woo_remove_shipping_billing_from_userlisting_ec');


    /*
     *  Add individual WooCommerce Billing and Shipping fields tags to Userlisting (both sorting and meta)
     */
    add_filter('wppb_userlisting_get_merge_tags', 'wppb_in_woo_add_userlisting_tags', 10, 2);  //add tags to User Listing
    function wppb_in_woo_add_userlisting_tags( $merge_tags, $type = '' ){

        // $type can be 'meta' or 'sort'
        $user_meta = 'user_meta';
        $unescaped = false;
        if ($type == 'sort') {
            $user_meta = 'sort_tag';
            $unescaped = true;
        }

        // add billing fields tags
        $billing_fields_array = wppb_in_woo_get_billing_fields();
        foreach ( $billing_fields_array as $key => $value ) {
            // remove 'billing_address_2' from sorting variables
            if ( ( $key == 'billing_address_2' ) && ( $type == 'sort' ) )
                continue;
            $merge_tags[] = array( 'name' => $type.'_'.$key, 'type' => $user_meta, 'unescaped' => $unescaped, 'label' => $value['label'] );
        }

        // add shipping fields tags
        $shipping_fields_array = wppb_in_woo_get_billing_fields();
        foreach ( $shipping_fields_array as $key => $value ) {
            // remove 'shipping_address_2' from sorting variables
            if ( ( $key == 'shipping_address_2' ) && ( $type == 'sort' ) )
                continue;
            $merge_tags[] = array( 'name' => $type.'_'.$key, 'type' => $user_meta, 'unescaped' => $unescaped, 'label' => $value['label'] );
        }

        return $merge_tags;

    }

    /*
     *  Add sorting support for Woo Billing and Shipping fields tags to Userlisting
     */
    add_filter('mustache_variable_sort_tag', 'wppb_in_woo_userlisting_sort_tags', 11, 4);
    function wppb_in_woo_userlisting_sort_tags( $value, $name, $children, $extra_info ){

        $billing_fields_array = wppb_in_woo_get_billing_fields();

        $shipping_fields_array = wppb_in_woo_get_shipping_fields();

        $i = 1000;

        foreach( $billing_fields_array as $key => $field_value ){

            if ( ( $name == 'sort_'.$key ) && function_exists( 'wppb_get_new_url' ) ){

                $i++;

                return '<a href="'.wppb_get_new_url( $key, $extra_info ).'" class="sortLink" id="sortLink'.$i.'">'.$field_value['label'].'</a>';

            }
        }

        foreach( $shipping_fields_array as $key => $field_value ){

            if ( ( $name == 'sort_'.$key ) && function_exists( 'wppb_get_new_url' ) ){

                $i++;

                return '<a href="'.wppb_get_new_url( $key, $extra_info ).'" class="sortLink" id="sortLink'.$i.'">'.$field_value['label'].'</a>';

            }
        }



        return $value;

    }

    /*
     *  Display (Billing & Shipping) Country and State full name (not just codes) in Userlisting
     */
    function wppb_in_woo_get_billing_shipping_country_state_full_name($value, $name, $children, $extra_values){

        if ( ( $name == 'meta_billing_country' ) || ( $name == 'meta_shipping_country' ) || ( $name == 'meta_billing_state' ) || ( $name == 'meta_shipping_state' ) ) {

            // Get allowed countries
            if ( class_exists('WC_Countries') ) {

                $WC_Countries_Object = new WC_Countries();

                // Country array from WooCommerce
                $country_array = $WC_Countries_Object->get_allowed_countries();

                // States array from WooCommerce
                $states_array = $WC_Countries_Object->get_allowed_country_states();

                $user_id = ( !empty( $extra_values['user_id'] ) ? $extra_values['user_id'] : get_query_var( 'author' ) );

                // check if it's billing or shipping meta
                if ( strpos($name, 'billing') !== false )
                    $meta_key = 'billing';
                else
                    $meta_key = 'shipping';

                $country_code = get_user_meta($user_id, $meta_key.'_country', true);

                if ( ( $name == 'meta_billing_country' ) || ( $name == 'meta_shipping_country') ) {

                    if ( !empty($country_code) && isset($country_array[$country_code])) return $country_array[$country_code];
                }

                if ( ( $name == 'meta_billing_state' ) || ( $name == 'meta_shipping_state' ) ) {

                    $states_code = get_user_meta($user_id, $meta_key.'_state', true);

                    if ( !empty($states_code) && !empty($states_array[$country_code]) && isset($states_array[$country_code][$states_code]) ) return $states_array[$country_code][$states_code];
                }

            }
        }

        return $value;
    }
    add_filter('mustache_variable_user_meta', 'wppb_in_woo_get_billing_shipping_country_state_full_name', 12, 4); //give it a lower priority to be executed last


    /*
    * Display (Billing & Shipping) Country and State full name (not just codes) in Facet filters
    */
    function wppb_in_woo_billing_shipping_country_state_name( $returned_value, $meta_value, $faceted_filter_options, $wppb_manage_fields ){

        if ( ($faceted_filter_options['facet-meta'] == 'billing_country') || ($faceted_filter_options['facet-meta'] == 'shipping_country') ) {

            if ( class_exists('WC_Countries') ) {

                $WC_Countries_Object = new WC_Countries();

                // Country array from WooCommerce
                $country_array = $WC_Countries_Object->get_allowed_countries();

                if  ( !empty($country_array[$meta_value]) )
                    $returned_value = $country_array[$meta_value];

            }

        }

        if ( ($faceted_filter_options['facet-meta'] == 'billing_state') || ($faceted_filter_options['facet-meta'] == 'shipping_state') ) {

            if ( class_exists('WC_Countries') ) {

                $WC_Countries_Object = new WC_Countries();

                $country_code = '';
                if (isset($_GET['ul_filter_billing_country']))
                    $country_code = sanitize_text_field( $_GET['ul_filter_billing_country'] );
                else if (isset($_GET['ul_filter_shipping_country']))
                    $country_code = sanitize_text_field( $_GET['ul_filter_shipping_country'] );

                if ( empty($country_code) )
                    $country_code = $WC_Countries_Object->get_base_country();

                $states_array = $WC_Countries_Object->get_allowed_country_states();

                if ( isset($states_array[$country_code]) && !empty($states_array[$country_code][$meta_value]) )
                    $returned_value = $states_array[$country_code][$meta_value];


            }

        }


        return $returned_value;
    }
    add_filter('wppb_ul_facet_value_or_label', 'wppb_in_woo_billing_shipping_country_state_name', 10, 4);


    /*
    * Function that returns an empty results array for Billing/Shipping State facet if none or multiple Billing/Shipping countries were selected (we need a single country selected to display the proper state names)
    */
    function wppb_in_woo_empty_results_if_no_country_selected($results, $facet_meta, $faceted_filters, $wppb_manage_fields){

        if ( ($facet_meta == 'billing_state') || ($facet_meta == 'shipping_state') ) {

            // check if there is a billing or shipping country facet set
            foreach ($faceted_filters as $filter) {

                if ( ( $filter['facet-meta'] == 'billing_country' ) || ( $filter['facet-meta'] == 'shipping_country' ) ) {

                    $country_code = '';
                    if ( isset($_GET['ul_filter_billing_country']) )
                        $country_code = sanitize_text_field( $_GET['ul_filter_billing_country'] );
                    else if ( isset($_GET['ul_filter_shipping_country']) )
                        $country_code = sanitize_text_field( $_GET['ul_filter_shipping_country'] );

                    // If no country was selected in the facet or if multiple countries were selected, display no states (we can only show state names for one country)
                    if ( empty($country_code) || ( strlen($country_code) > 2) )
                        $results = array();
                }

            }

        }

        return $results;
    }
    add_filter('wppb_get_all_values_for_user_meta' , 'wppb_in_woo_empty_results_if_no_country_selected', 10, 4);


    /*
     * Function to display a custom "No options available" message for Billing/Shipping State facet, in case no country was selected
     */
    function wppb_in_woo_custom_no_options_message_billing_shipping_state( $message, $faceted_filter_options ){

        if ( !empty($faceted_filter_options['facet-meta']) && ( ( $faceted_filter_options['facet-meta'] == 'billing_state' ) || ( $faceted_filter_options['facet-meta'] == 'shipping-state' ) ) ){

            $message = __( 'No options available. Please select one country.', 'profile-builder' );
        }

        return $message;

    }
    add_filter('wppb_facet_no_options_message', 'wppb_in_woo_custom_no_options_message_billing_shipping_state', 10, 2);


    /*
     * Include individual Woo Shipping Address & Billing Address fields in Search Fields and Faceted Menus (as facet meta)
     */
    function wppb_in_woo_include_billing_shipping_in_search_all_fields_and_faceted_menus( $fields ){

        $billing_fields_array = wppb_in_woo_get_billing_fields();

        foreach ( $billing_fields_array as $key => $field_value ) {
            // remove billing address line 2 from search, it's not used so often
            if ( $key != 'billing_address_2' )
                $fields[] = '%' . __('Billing ','profile-builder') . $field_value['label'] . '%' . $key;
        }

        $shipping_fields_array = wppb_in_woo_get_billing_fields();

        foreach ( $shipping_fields_array as $key => $field_value ) {
            // remove shipping address line 2 from search, it's not used so often
            if ( $key != 'shipping_address_2' )
                $fields[] = '%' . __('Shipping ','profile-builder') . $field_value['label'] . '%' . $key;
        }

        return $fields;
    }
    add_filter( 'wppb_userlisting_search_all_fields', 'wppb_in_woo_include_billing_shipping_in_search_all_fields_and_faceted_menus' );
    add_filter( 'wppb_userlisting_facet_meta', 'wppb_in_woo_include_billing_shipping_in_search_all_fields_and_faceted_menus' );


    /*
     * Populate "Default Sorting Criteria" dropdown with individual Woo Shipping & Billing Address fields
     */
    function wppb_in_woo_include_billing_shipping_fields_in_default_sorting_criteria ($sorting_criteria) {
        $billing_fields_array = wppb_in_woo_get_billing_fields();

        foreach ( $billing_fields_array as $key => $field_value ) {
            if ($key != 'billing_address_2')
                $sorting_criteria[] = '%' .  __('Billing ','profile-builder') . $field_value['label'] . '%' . $key;
        }

        $shipping_fields_array = wppb_in_woo_get_shipping_fields();

        foreach ( $shipping_fields_array as $key => $field_value ) {
            if ($key != 'shipping_address_2')
                $sorting_criteria[] = '%' .  __('Shipping ','profile-builder') . $field_value['label'] . '%' . $key;
        }

        return $sorting_criteria;

    }
    add_filter('wppb_default_sorting_criteria', 'wppb_in_woo_include_billing_shipping_fields_in_default_sorting_criteria');


    /*
     * We also need to include individual Woo Billing & Shipping meta in user_meta_keys array
     */
    function wppb_in_woo_include_billing_shipping_in_search( $user_meta_keys ){

        $billing_fields_array = wppb_in_woo_get_billing_fields();

        foreach ( $billing_fields_array as $key => $field_value )
            $user_meta_keys[] = $key;

        $shipping_fields_array = wppb_in_woo_get_shipping_fields();

        foreach ( $shipping_fields_array as $key => $field_value )
            $user_meta_keys[] = $key;

        return $user_meta_keys;
    }

    add_filter( 'wppb_userlisting_search_in_user_meta_keys', 'wppb_in_woo_include_billing_shipping_in_search' );


    /*
     * Add the Woo Shipping Address & Billing Address merge tags we need in Email Customizer
     */
    add_filter( 'wppb_email_customizer_get_merge_tags', 'wppb_in_woo_add_tags_in_ec' );
    function wppb_in_woo_add_tags_in_ec( $merge_tags ){
        /* unescaped because they might contain html */
        $merge_tags[] = array( 'name' => 'wppbwoo_billing', 'type' => 'wppbwoo_billing', 'unescaped' => true, 'label' => __( 'Billing Address', 'profile-builder' ) );
        $merge_tags[] = array( 'name' => 'wppbwoo_shipping', 'type' => 'wppbwoo_shipping', 'unescaped' => true, 'label' => __( 'Shipping Address', 'profile-builder' ) );
        return $merge_tags;
    }


    /* Display content in Email Customizer for WooCommerce Billing merge tag */
    add_filter( 'mustache_variable_wppbwoo_billing', 'wppb_in_woo_handle_merge_tag_wppbwoo_billing', 10, 4 );
    function wppb_in_woo_handle_merge_tag_wppbwoo_billing( $value, $name, $children, $extra_values){

        $user_id = ( !empty( $extra_values['user_id'] ) ? $extra_values['user_id'] : get_query_var( 'username' ) );
        $billing_fields = wppb_in_woo_billing_fields_array();

        if( !empty($user_id) && !empty($billing_fields) ) {
           return wppb_in_woo_ec_output_wppbwoo_billing_shipping($user_id, $billing_fields);
        }
    }

    /* Display content in Email Customizer for WooCommerce Shipping merge tag */
    add_filter( 'mustache_variable_wppbwoo_shipping', 'wppb_in_woo_handle_merge_tag_wppbwoo_shipping', 10, 4 );
    function wppb_in_woo_handle_merge_tag_wppbwoo_shipping( $value, $name, $children, $extra_values){

        $user_id = ( !empty( $extra_values['user_id'] ) ? $extra_values['user_id'] : get_query_var( 'username' ) );
        $shipping_fields = wppb_in_woo_shipping_fields_array();

        if( !empty($user_id) && !empty($shipping_fields) ) {
            return wppb_in_woo_ec_output_wppbwoo_billing_shipping($user_id, $shipping_fields);
        }
    }

    /* Output Billing and Shipping fields content in Email Customizer*/
    function wppb_in_woo_ec_output_wppbwoo_billing_shipping($user_id, $fields){

            $user_meta = get_user_meta($user_id);

            // Used to get country name based on country code
            if (class_exists('WC_Countries')) {
                $WC_Countries_Obj = new WC_Countries();
                $country_array = $WC_Countries_Obj->get_allowed_countries();
                $states_array =  $WC_Countries_Obj->get_allowed_country_states();
            }

            $output = '<table>';
            foreach ($fields as $field_key => $field_val) {

                // display only address fields which aren't empty
                if (!empty($user_meta[$field_key][0])) {
                    $country_code = $user_meta[$field_key][0];

                    if  ( ( ($field_key == 'billing_country') || ($field_key == 'shipping_country') ) && (class_exists('WC_Countries')) ) {
                        $output .= '<tr><td>' . $field_val['label'] . ' : ' . $country_array[$country_code] . '</td></tr>';
                    }
                    elseif ( ( ($field_key == 'billing_state') || ($field_key == 'shipping_state') ) && (class_exists('WC_Countries'))  && !empty($states_array[$country_code][$user_meta[$field_key][0]]) ) {
                            $output .= '<tr><td>' . $field_val['label'] . ' : ' . $states_array[$country_code][$user_meta[$field_key][0]] . '</td></tr>';
                    }
                    else {
                        if ( ($field_key == 'billing_address_2') || ($field_key == 'shipping_address_2') ) $field_val['label'] = __('Address line 2', 'profile-builder');
                        $output .= '<tr><td>' . $field_val['label'] . ' : ' . $user_meta[$field_key][0] . '</td></tr>';
                    }
                }
            }
            $output .= '</table>';

            return $output;
    }


    /*
     * Replace WooCommerce MyAccount "form-login.php" and "edit account" templates with "myaccount-login-register.php" and "myaccount-edit-profile.php" templates added by our WooSync add-on
     */
    function wppb_in_woo_replace_myaccount_login_register_edit_account_templates($located, $template_name, $args='', $template_path='', $default_path=''){
        $wppb_woosync_settings = get_option( 'wppb_woosync_settings');

        // make sure Profile Builder is active
        if ( class_exists('Wordpress_Creation_Kit_PB') ) {

            $current_theme = wp_get_theme();

            if ( !empty($wppb_woosync_settings['RegisterForm']) && ($template_name == 'myaccount/form-login.php') && (!is_user_logged_in()) ) {

                // verify if we have a custom template to load from the current theme templates folder
                if( !empty( $current_theme->stylesheet ) && file_exists( get_theme_root() . '/' . $current_theme->stylesheet . '/templates/myaccount-login-register.php' ) ) {
                    $located = get_theme_root() . '/' . $current_theme->stylesheet . '/templates/myaccount-login-register.php';
                }
                else {
                    $located = WPPBWOO_IN_PLUGIN_DIR . '/templates/myaccount-login-register.php';
                }

            }

            if ( !empty($wppb_woosync_settings['EditProfileForm']) && ($template_name == 'myaccount/form-edit-account.php') && (is_user_logged_in()) ) {

                // verify if we have a custom edit profile template to load from the current theme templates folder
                if ( !empty( $current_theme->stylesheet ) && file_exists( get_theme_root() . '/' . $current_theme->stylesheet . '/templates/myaccount-edit-profile.php' ) ) {
                    $located = get_theme_root() . '/' . $current_theme->stylesheet . '/templates/myaccount-edit-profile.php';;
                }
                else {
                    $located = WPPBWOO_IN_PLUGIN_DIR . '/templates/myaccount-edit-profile.php';
                }

            }

        }

        return $located;
    }
    add_filter('wc_get_template','wppb_in_woo_replace_myaccount_login_register_edit_account_templates', 10, 5);


}
else {
    /*
     * Display notice if WooCommerce is not active
     */
    function wppb_in_woo_admin_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php esc_html_e( 'WooCommerce needs to be installed and activated for Profile Builder - WooCommerce Sync Add-on to work!', 'profile-builder' ); ?> </p>
        </div>
    <?php
    }
    add_action( 'admin_notices', 'wppb_in_woo_admin_notice' );
}
