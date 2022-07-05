<?php
/**
 * Add support for custom fields created with Profile Builder to be displayed on WooCommerce Checkout page
 *
 */

//Display custom fields on WooCommerce checkout for logged in users as well (by default WooCommerce allows them only for non-logged in users)
function wppb_in_woo_display_checkout_fields_for_logged_in_users( $checkout ){
    if ( is_user_logged_in() ) {
        do_action( 'woocommerce_before_checkout_registration_form', $checkout );
        if (!empty($checkout->checkout_fields['account'])) {
            echo '<div class="create-account">';
            foreach ($checkout->checkout_fields['account'] as $key => $field) {
                //do not display username and password for logged in users
                if ( ($key == 'account_password') || ($key == 'account_username') )
                    continue;
                woocommerce_form_field($key, $field, $checkout->get_value($key));
            }
            echo '<div class="clear"></div>';
            echo '</div>';
        }
        do_action( 'woocommerce_after_checkout_registration_form', $checkout );
    }
}
add_action('woocommerce_after_checkout_billing_form', 'wppb_in_woo_display_checkout_fields_for_logged_in_users', 10 , 1 );

//Save custom fields information added on WooCommerce Checkout page
function wppb_in_woo_save_checkout_extra_fields( $user_id, $request_data ){
    // sometimes this doesn't get loaded.
    if( defined( 'WPPB_PAID_PLUGIN_DIR' ) && file_exists ( WPPB_PAID_PLUGIN_DIR .'/front-end/extra-fields/extra-fields.php'))
        include_once( WPPB_PAID_PLUGIN_DIR .'/front-end/extra-fields/extra-fields.php');

    $wppb_manage_fields = get_option( 'wppb_manage_fields', 'not_found' );

    // default form data. Need this for the wppb_build_userdata filter.
    $form_data = array(
        'form_type' 			=> 'woocommerce_checkout',
        'form_fields' 			=> array(),
        'form_name' 			=> '',
        'role' 					=> '', //used only for the register-form settings
        'redirect_url'          => '',
        'logout_redirect_url'   => '', //used only for the register-form settings
        'redirect_priority'		=> 'normal',
        'ID'                    => null
    );

    if( $wppb_manage_fields != 'not_found' ) {
        foreach ($wppb_manage_fields as $field){
            if ( isset($field['woocommerce-checkout-field']) && ( $field['woocommerce-checkout-field'] == "Yes" ) ){
                do_action( 'wppb_save_form_field', $field, $user_id, $_REQUEST, 'edit_profile' );
                $form_data[ 'form_fields' ][] = $field;
            }
        }
    }

    $userdata = apply_filters( 'wppb_build_userdata', array(), $_REQUEST, $form_data );

    if( isset( $wppb_general_settings['loginWith'] ) && ( $wppb_general_settings['loginWith'] == 'email' ) ){
        $user_info = get_userdata( $user_id );
        $userdata['user_login'] = $user_info->user_login;
    }

    $userdata['ID'] = $user_id;
    $userdata = wp_unslash( $userdata );

    if( current_user_can( 'manage_options' ) && isset( $userdata['role'] ) && is_array( $userdata['role'] ) ) {
        $user_data = get_userdata( $user_id );
        $user_data->remove_all_caps();

        foreach( $userdata['role'] as $role ) {
            $user_data->add_role( $role );
        }

        unset( $userdata['role'] );
    }

    wp_update_user( $userdata );
}
add_action( 'woocommerce_checkout_update_user_meta', 'wppb_in_woo_save_checkout_extra_fields', 10, 2 );

// Add "WooCommerce Checkout Field" checkbox to the field properties in Manage Fields page
function wppb_in_woo_checkout_field_to_manage_fields( $fields ) {
    $woo_checkout_manage_field = array( 'type' => 'select', 'slug' => 'woocommerce-checkout-field', 'title' => __( 'Display on WooCommerce Checkout', 'profile-builder' ), 'options' => array( 'No', 'Yes' ), 'default' => 'No', 'description' => __( 'Whether the field should be added to the WooCommerce checkout form or not', 'profile-builder' ) );
    array_push( $fields, $woo_checkout_manage_field );
    return $fields;
}
add_filter( 'wppb_manage_fields', 'wppb_in_woo_checkout_field_to_manage_fields');

function wppb_in_woo_add_checkout_errors(){
    if ( !class_exists( 'Profile_Builder_Form_Creator' ) && file_exists ( WPPB_PLUGIN_DIR .'/front-end/class-formbuilder.php' ) )
        include_once( WPPB_PLUGIN_DIR .'/front-end/class-formbuilder.php');

    // need to check if user is logged in or "createaccount" is enabled. Otherwise don't validate shit.
    $args = array();
    $fields = get_option( 'wppb_manage_fields', array() );
    if(is_user_logged_in()){
        $args['form_type'] = 'edit_profile';
    } else {
        $args['form_type'] = 'register';
    }

    foreach ( $fields as  $key => $field ) {
        if ( isset($field['woocommerce-checkout-field']) && ( $field['woocommerce-checkout-field'] == "Yes" ) ){
            $args['form_fields'][] = $field;

        }
    }
    //woocommerce-checkout-field
    if( ( isset($_REQUEST['createaccount']) && $_REQUEST['createaccount'] == 1 ) || is_user_logged_in() || wppb_in_is_woo_registration_required()){
        $pb_form = new Profile_Builder_Form_Creator( $args );
        $field_check_errors = $pb_form->wppb_test_required_form_values( $_REQUEST );

        //foreach error, go through each field and if the ID exists, throw an woo notice
        if ( isset( $args['form_fields'] ) && is_array( $args['form_fields'] ) ) {
            foreach ( $args['form_fields'] as $key => $field ) {
                $specific_message = ((array_key_exists($field['id'], $field_check_errors)) ? $field_check_errors[$field['id']] : '');
                if ($specific_message) {
                    wc_add_notice('<span class="wppb-err"><strong class="wppb-form-element-' . $field['id'] . '">' . $field['field-title'] . '</strong></span> ' . $specific_message, 'error');
                }
            }
        }
    }

}
add_action('woocommerce_checkout_process', 'wppb_in_woo_add_checkout_errors', 20);

// Woo alternate way of adding fields to the user account
function wppb_in_woo_add_checkout_fields($checkout){
    if ( !class_exists( 'Profile_Builder_Form_Creator' ) && file_exists ( WPPB_PLUGIN_DIR .'/front-end/class-formbuilder.php' ) )
        include_once( WPPB_PLUGIN_DIR .'/front-end/class-formbuilder.php');

    $args = array();
    $fields = get_option( 'wppb_manage_fields' );

    $args['form_fields'] = array();
    if(is_user_logged_in()){
        $args['form_type'] = 'edit_profile';
    } else {
        $args['form_type'] = 'register';
    }

    foreach ( $fields as  $key => $field ) {
        if ( isset($field['woocommerce-checkout-field']) && ( $field['woocommerce-checkout-field'] == "Yes" ) ){
            $args['form_fields'][] = $field;
        }
    }

    if ( count($args['form_fields']) == 0 ){
        return;
    }

    $pb_form = new Profile_Builder_Form_Creator( $args );
    add_filter('wppb_field_css_class', 'wppb_in_woo_change_field_class', 10, 3);
    echo '<div class="create-account wppb-user-forms"><ul class="wppb-woo-checkout-fields">';
    echo $pb_form->wppb_output_form_fields( $_REQUEST, array(), $pb_form->args['form_fields'] ); //phpcs:ignore
    echo '</ul></div>';

}
add_action('woocommerce_after_checkout_registration_form','wppb_in_woo_add_checkout_fields');

function wppb_in_woo_change_field_class($class, $field, $error_var){
    return $class . ' form-row form-row-wide ';
}

/**
 * Is registration required to checkout?
 *
 * @since  1.5.2
 * @return boolean
 */
function wppb_in_is_woo_registration_required() {
    return apply_filters( 'woocommerce_checkout_registration_required', 'yes' !== get_option( 'woocommerce_enable_guest_checkout' ) );
}
