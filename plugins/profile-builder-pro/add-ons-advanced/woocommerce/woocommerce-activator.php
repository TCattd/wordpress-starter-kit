<?php

/**
 * The code that runs during plugin activation.
 */

if( !function_exists( 'wppb_in_prepopulate_woo_billing_shipping_fields' ) ){

    function wppb_in_prepopulate_woo_billing_shipping_fields( $addon ) {

        if( $addon == 'woocommerce' ){

            $wppb_manage_fields = get_option('wppb_manage_fields');

            if ( !empty( $wppb_manage_fields ) ) {

                /* check to see if we already have the fields */
                foreach( $wppb_manage_fields as $wppb_manage_field ){
                    if( $wppb_manage_field['meta-name'] == 'wppbwoo_billing' || $wppb_manage_field['meta-name'] == 'wppbwoo_shipping' ){
                        return;
                    }
                }

                if (function_exists('wppb_get_unique_id')) {
                    //Add Billing fields
                    $wppb_manage_fields[] = array('field' => 'WooCommerce Customer Billing Address', 'field-title' => __('Billing Address', 'profile-builder'), 'meta-name' => 'wppbwoo_billing', 'overwrite-existing' => 'No', 'id' => wppb_get_unique_id(), 'description' => __('Displays customer billing fields in front-end. ', 'profile-builder'), 'row-count' => '5', 'allowed-image-extensions' => '.*', 'allowed-upload-extensions' => '.*', 'avatar-size' => '100', 'date-format' => 'mm/dd/yy', 'terms-of-agreement' => '', 'options' => '', 'labels' => '', 'public-key' => '', 'private-key' => '', 'default-value' => '', 'default-option' => '', 'default-options' => '', 'default-content' => '', 'required' => 'No');
                    update_option('wppb_manage_fields', $wppb_manage_fields);

                    //Add Shipping fields
                    $wppb_manage_fields[] = array('field' => 'WooCommerce Customer Shipping Address', 'field-title' => __('Shipping Address', 'profile-builder'), 'meta-name' => 'wppbwoo_shipping', 'overwrite-existing' => 'No', 'id' => wppb_get_unique_id(), 'description' => __('Displays customer shipping fields in front-end. ', 'profile-builder'), 'row-count' => '5', 'allowed-image-extensions' => '.*', 'allowed-upload-extensions' => '.*', 'avatar-size' => '100', 'date-format' => 'mm/dd/yy', 'terms-of-agreement' => '', 'options' => '', 'labels' => '', 'public-key' => '', 'private-key' => '', 'default-value' => '', 'default-option' => '', 'default-options' => '', 'default-content' => '', 'required' => 'No');
                    update_option('wppb_manage_fields', $wppb_manage_fields);
                }

            }

        }

    }
    add_action( 'wppb_add_ons_activate', 'wppb_in_prepopulate_woo_billing_shipping_fields', 10, 1);

}
