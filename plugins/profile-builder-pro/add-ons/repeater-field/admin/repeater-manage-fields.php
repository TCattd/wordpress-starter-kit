<?php

/**
 * Enqueues the necessary scripts in the admin area
 *
 * @param $hook
 */
function wppb_rpf_scripts_and_styles_admin( $hook ) {
    if ( 'profile-builder_page_manage-fields' != $hook ){
        return;
    }
    wp_enqueue_script( 'wppb-repeater-field-script', WPPB_PAID_PLUGIN_URL . 'add-ons/repeater-field/assets/js/wppb-rpf-admin.js', array( 'jquery' ), PROFILE_BUILDER_VERSION, true );
    wp_enqueue_style( 'wppb-repeater-field-ui', WPPB_PAID_PLUGIN_URL . 'add-ons/repeater-field/assets/css/wppb-rpf-admin.css', false, PROFILE_BUILDER_VERSION );
}
add_action( 'admin_enqueue_scripts', 'wppb_rpf_scripts_and_styles_admin' );


/**
 * Change the option meta where the repeater group gets saved
 *
 * @param $meta_name     Option meta name
 * @return $meta_name    Repeater field meta-name
 */
function wppb_rpf_option_meta( $meta_name ){
    if ( current_user_can( 'manage_options'  ) && ! empty( $_GET['wppb_rpf_repeater_meta_name'] ) ) {
        return sanitize_text_field( $_GET['wppb_rpf_repeater_meta_name'] );
    }
    return $meta_name;
}
add_filter( 'wck_option_meta' , 'wppb_rpf_option_meta' );

/*
 * Conditional Fields option meta. Helps display the correct fields in internal Repeater Fields' Conditional Logic.
 * It is not used at this point because Conditional Logic is not supported within a Repeater Field
 */
add_filter( 'wppb_cf_option_meta' , 'wppb_rpf_option_meta' );


/**
 * Keep the get parameter when doing ajax calls in Repeater Manage fields
 *
 * @param $ajax_url
 * @return string
 */
function wppb_rpf_add_get_parameters( $ajax_url ){
    if ( current_user_can( 'manage_options'  ) && ! empty( $_GET['wppb_rpf_repeater_meta_name'] ) ) {
        return $ajax_url . '?wppb_rpf_repeater_meta_name=' . sanitize_text_field( $_GET['wppb_rpf_repeater_meta_name'] );
    }
    return $ajax_url;
}
add_filter( 'wck_ajax_url', 'wppb_rpf_add_get_parameters' );


/**
 * Take into account repeater fields IDs when generating a unique ID for a field
 *
 * @param $unique_id
 * @param $ids_array
 * @param $manage_fields
 * @return mixed
 */
function wppb_rpf_correct_unique_id( $unique_id, $ids_array, $manage_fields ) {
    $ids_array = array_merge ( $ids_array, wppb_rpf_get_ids_array_of_all_rpf($manage_fields));

    if( !empty( $ids_array ) ){
        rsort( $ids_array );
        $unique_id = $ids_array[0] + 1;
    }

    return $unique_id;
}
add_filter( 'wppb_field_unique_id', 'wppb_rpf_correct_unique_id', 10, 3 );


/**
 * Take into account repeater fields IDs when adding a field
 *
 * @param $values
 * @return array
 */
function wppb_rpf_check_unique_id_on_save( $values ){

    // get manage fields ids_array
    $wppb_manage_fields = get_option( 'wppb_manage_fields', 'not_found' );

    if( $wppb_manage_fields != 'not_found' ) {

        $ids_array = array();
        foreach( $wppb_manage_fields as $field ){
            $ids_array[] = $field['id'];
        }
        $ids_array = array_merge ( $ids_array, wppb_rpf_get_ids_array_of_all_rpf($wppb_manage_fields));

        if( in_array( $values['id'], $ids_array ) ) {
            rsort( $ids_array );
            $values['id'] = $ids_array[0] + 1;
        }

    }
    return $values;
}
add_filter( 'wck_add_meta_filter_values_wppb_manage_fields', 'wppb_rpf_check_unique_id_on_save', 20 );


/**
 * Get IDs for every field of every Repeater group
 *
 * @param $manage_fields
 * @return array
 */
function wppb_rpf_get_ids_array_of_all_rpf($manage_fields){
    $ids_array = array();
    if (  is_array ( $manage_fields ) ){
        foreach ( $manage_fields as $field ) {
            if ( $field['field'] == 'Repeater' ){
                $repeater_group = get_option( $field['meta-name'], 'not_set' );
                if ( $repeater_group == 'not_set' ) {
                    continue;
                }
                foreach( $repeater_group as $value ){
                    $ids_array[] = $value['id'];
                }
            }
        }
    }
    return $ids_array;
}

function wppb_rpf_manage_fields_get_excluded_fields(){
    return apply_filters('wppb_rpf_manage_fields_get_excluded_fields',  array(
        'Default - Name (Heading)',
        'Default - Contact Info (Heading)',
        'Default - About Yourself (Heading)',
        'Default - Username',
        'Default - First Name',
        'Default - Last Name',
        'Default - Nickname',
        'Default - E-mail',
        'Default - Website',
        'Default - AIM',
        'Default - Yahoo IM',
        'Default - Jabber / Google Talk',
        'Default - Password',
        'Default - Repeat Password',
        'Default - Biographical Info',
        'Default - Display name publicly as',
        'Default - Blog Details',
        'Select (User Role)',
        'WYSIWYG',
        'Avatar',
        'reCAPTCHA',
        'MailChimp Subscribe',
        'MailPoet Subscribe',
        'Campaign Monitor Subscribe',
        'Email Confirmation',
        'WooCommerce Customer Billing Address',
        'WooCommerce Customer Shipping Address',
        'Subscription Plans',
        'Subscription Plans',
        'Repeater',
        'GDPR Checkbox',
        'GDPR Delete Button',
        'Checkbox (Terms and Conditions)'
    ));
}

/**
 * Function that adds the new Repeater field to the fields list
 * and also the list of fields that skip the meta-name check
 *
 * @param array $fields     - The names of all the fields
 * @return array
 */
function wppb_rpf_skip_manage_field_types( $fields ) {
    $fields[] = 'Repeater';

    if ( ! empty( $_GET['wppb_rpf_repeater_meta_name'] ) ) {
        $exclude_fields = wppb_rpf_manage_fields_get_excluded_fields();
        $fields = array_diff( $fields, $exclude_fields );
    }

    return $fields;
}
add_filter( 'wppb_skip_check_for_fields', 'wppb_rpf_manage_field_types', 100 );

function wppb_rpf_manage_field_types( $fields ) {
    $fields['optgroups']['other']['options'][] = 'Repeater';

    if ( ! empty( $_GET['wppb_rpf_repeater_meta_name'] ) ) {
        $exclude_fields = wppb_rpf_manage_fields_get_excluded_fields();
        unset ($fields['optgroups']['default']);
        foreach ($fields['optgroups'] as $key_optgroup => $optgroup ){
            $fields['optgroups'][$key_optgroup]['options'] = array_diff( $optgroup['options'], $exclude_fields );
        }
    }

    return $fields;
}
add_filter( 'wppb_all_manage_fields_types', 'wppb_rpf_manage_field_types', 100 );


/**
 * Function adds the Repeater field options in the field property from Manage Fields
 *
 * @param array $fields - The current field properties
 *
 * @return array        - The field properties that now include the MailPoet properties
 */
function wppb_rpf_manage_fields( $fields ) {
    $fields[] = array( 'type' => 'checkbox', 'slug' => 'rpf-enable-limit', 'title' => __( 'Limit', 'profile-builder' ), 'description' => __( 'Enable limit to the number of fields to be generated by users in front end forms ', 'profile-builder' ), 'options' => array( '%Enable limit%yes' ) );
    $fields[] = array( 'type' => 'text', 'slug' => 'rpf-limit', 'title' => __( 'General Limit', 'profile-builder' ), 'default' => 0, 'description' => __(  'Default limit for this repeater group. <br>Leave 0 for unlimited.', 'profile-builder' ) );
    $fields[] = array( 'type' => 'textarea', 'slug' => 'rpf-limit-reached-message', 'title' => __( 'Limit reached message', 'profile-builder' ), 'default' => __( "The maximum number of fields has been reached.", 'profile-builder' ), 'description' => __(  'The popup message to display when the limit of repeater groups is reached.', 'profile-builder' ) );
    $fields[] = array( 'type' => 'text', 'slug' => 'rpf-role-limit', 'title' => __( 'Limit per Role', 'profile-builder' ), 'description' => __(  'Leave 0 for unlimited.', 'profile-builder' )  );
    $fields[] = array( 'type' => 'rpf-button', 'slug' => 'rpf-button', 'title' => __( 'Repeated field group', 'profile-builder' ), 'description' => __(  'Manage field or group of fields that will be repeatable.', 'profile-builder' ) );

    if ( !empty ( $_GET['wppb_rpf_repeater_meta_name'] ) && !empty ( $_GET['wppb_field_metaname_prefix'] ) ) {

        foreach ( $fields as $key => $value){
            if ( $fields[$key]['slug'] == 'meta-name' ){
                $fields[$key]['default'] = wppb_get_meta_name( sanitize_text_field( $_GET['wppb_rpf_repeater_meta_name'] ), sanitize_text_field( $_GET['wppb_field_metaname_prefix'] ) . '_' ) ;
            }
        }
    }

    return $fields;
}
add_filter( 'wppb_manage_fields', 'wppb_rpf_manage_fields', 9 );


/**
 * Remove Conditional Logic options from fields that are part of Repeaters
 *
 * @param array $fields - The current field properties
 *
 * @return array        - The field properties that now include the MailPoet properties
 */
function wppb_rpf_manage_fields_remove_conditional_logic( $fields ) {
    if ( !empty ( $_GET['wppb_rpf_repeater_meta_name'] ) ) {

        foreach ( $fields as $key => $value) {
            if ( ( $fields[$key]['slug'] == 'conditional-logic-enabled' ) || ( $fields[$key]['slug'] == 'conditional-logic' ) || ( $fields[$key]['slug'] == 'woocommerce-checkout-field' ) ){
                unset( $fields[$key] );
            }
        }
    }

    return $fields;
}
add_filter( 'wppb_manage_fields', 'wppb_rpf_manage_fields_remove_conditional_logic', 20 );


/**
 * Get list of roles
 *
 * @return array        Json Encoded array of roles
 */
function wppb_rpf_get_roles(){
    //user roles
    global $wp_roles;

    $user_roles = array();
    foreach( $wp_roles->roles as $user_role_slug => $user_role )
        $user_roles[$user_role_slug] = stripslashes($user_role['name']) ;
    return json_encode($user_roles);
}


/**
 * Add new type of field option for the Edit Field Group button
 *
 * @param $output
 * @param $value
 * @param $details
 * @param $single_prefix
 * @return string
 */
function wppb_rpf_add_customtype_button( $output, $value, $details, $single_prefix ){
    $output = '';
    $output.= '<button type="button" class="button-primary" id="wppb_rpf_edit_field_group_button" onclick="wppb_manage_repeater_field.wppb_rpf_open_repeater_fields_iframe(this)">' .  __('Edit field group', 'profile-builder') . '</button>';
    $output.= '<span id="wppb_fields_saved" class="wppb-fields-saved">' . __('Repeatable fields saved!', 'profile-builder') . '</span>';
    return $output;
}
add_filter( 'wck_output_form_field_customtype_rpf-button', 'wppb_rpf_add_customtype_button', 10, 4 );


/**
 * Output the error if field not unique when saving Repeater field in Manage fields
 *
 * @param $message
 * @param $posted_values
 * @return string
 */
function wppb_rpf_check_manage_field_title( $message, $posted_values ) {

    if( $posted_values['field'] == 'Repeater' ) {
        if ( wppb_rpf_unique_field_title( $posted_values['field-title'], $posted_values['meta-name'] ) === false ) {
            $message .= __("Please enter a unique field title.", 'profile-builder')."\n";
        }
    }
    return $message;
}
add_filter( 'wppb_check_extra_manage_fields', 'wppb_rpf_check_manage_field_title', 10, 2 );


/**
 * Function that checks if the field title is unique among other Repeater fields.
 * Returns slug if unique and false otherwise
 *
 * @param $title
 * @param string $meta_name
 * @return bool|string
 */
function wppb_rpf_unique_field_title( $title, $meta_name = '' ){
    $slug = Wordpress_Creation_Kit_PB::wck_generate_slug( $title );
    if ( empty( $slug ) ){
        return false;
    }
    $manage_fields = get_option ('wppb_manage_fields', 'not_found');
    if ( $manage_fields != 'not_found' ) {
        foreach ( $manage_fields as $field ){
            if ( ( $field['field'] == "Repeater" ) && ( $field['meta-name'] != $meta_name ) && ( Wordpress_Creation_Kit_PB::wck_generate_slug( $field['field-title'] ) == $slug ) ) {
                return false;
            }
        }
    }
    return $slug;
}


/**
 * AJAX request response for unique field
 *
 * @return string
 */
function wppb_rpf_check_repeater_unique_title() {

    $title = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
    $meta_name = isset( $_POST['meta_name'] ) ? sanitize_text_field( $_POST['meta_name'] ) : '';

    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        $unique_title = wppb_rpf_unique_field_title( $title, $meta_name );
        if ( $unique_title !== false ){
            $response['is_unique'] = true;
            $response['title_slug'] = $unique_title;
        }else{
            $response['is_unique'] = false;
            $response['error_message'] = __("Please enter a unique field title.\n", 'profile-builder');
        }
        echo json_encode($response);
    }
    die();
}
add_action( 'wp_ajax_nopriv_wppb_rpf_check_repeater_unique_title', 'wppb_rpf_check_repeater_unique_title' );
add_action( 'wp_ajax_wppb_rpf_check_repeater_unique_title', 'wppb_rpf_check_repeater_unique_title' );


/**
 * Check for meta-name conflicts.
 *
 * Replaces in Manage fields the Repeater fields with the repeater group of fields.
 * Also, if there is any possibility that the indexed meta-name of a repeater field is in conflict with another meta-name, it adds a duplicate for that field so that the further check fails.
 *
 * @param $manage_fields
 * @param $posted_values
 * @return array
 */
function wppb_rpf_check_unique_field( $manage_fields, $posted_values ){
    // add all the repeater groups in manage fields
    $repeater_groups = array();
    foreach ( $manage_fields as $field ){
        if ( $field['field'] == "Repeater" ){
            $repeater_group = get_option( $field['meta-name'], 'not_set' );
            if ( $repeater_group != 'not_set' ) {
                $manage_fields = array_merge( $manage_fields, $repeater_group );
                if ( empty ( $_GET['wppb_rpf_repeater_meta_name'] ) ) {
                    $repeater_groups = array_merge($repeater_groups, $repeater_group);
                }
            }
        }
    }

    if ( !empty ( $_GET['wppb_rpf_repeater_meta_name'] ) ) {
        //we are adding a field inside a repeater group

        // check for conflicts between all fields (including the repeater fields) and the indexed version of the new field meta-name being inserted
        foreach ($manage_fields as $field) {
            if ( wppb_rpf_possible_meta_name_conflict($field['meta-name'], $posted_values['meta-name']) == true ) {
                $field['meta-name'] = $posted_values['meta-name'];

                // conflict detected -> add duplicate field so that it fails further checks
                $manage_fields = array_merge( $manage_fields, array($field) );
                break;
            }
        }
    }else{
        if ( count ( $repeater_groups ) > 0 ) {
            // check for conflicts between all the indexed repeater fields and the new field meta-name being inserted
            foreach ( $repeater_groups as $rpf_field ) {
                if ( wppb_rpf_possible_meta_name_conflict($posted_values['meta-name'], $rpf_field['meta-name'] ) == true  ) {
                    $rpf_field['meta-name'] = $posted_values['meta-name'];

                    // conflict detected -> add duplicate field so that it fails further checks
                    $manage_fields = array_merge( $manage_fields, array($rpf_field) );
                    break;
                }
            }
        }
    }

    return $manage_fields;
}
add_filter( 'wppb_manage_fields_check_field_on_edit_add', 'wppb_rpf_check_unique_field', 10, 3 );


/**
 * Regex match meta_name with indexed indexable_meta_name
 *
 * @param $meta_name
 * @param $indexable_meta_name
 * @return bool
 */
function wppb_rpf_possible_meta_name_conflict( $meta_name, $indexable_meta_name ){
    $pattern = '/^' . $indexable_meta_name . '_[0-9]+$/';
    preg_match( $pattern, $meta_name, $matches );
    if ( count ($matches) > 0 ){
        return true;
    }else{
        return false;
    }
}


/**
 * Add meta-name to a Repeater field if missing.
 *
 * Meta-names are added only if there are no repeatable fields added for the Repeater field
 *
 * @param $values
 * @return mixed
 */
function wppb_rpf_add_missing_meta_name( $values ){
    if ( $values['field'] == 'Repeater' && empty($values['meta-name']) ){
        $values['meta-name'] = 'wppb_repeater_field_' . Wordpress_Creation_Kit_PB::wck_generate_slug( $values['field-title'] );
    }
    return $values;
}
add_filter( 'wck_add_meta_filter_values_wppb_manage_fields', 'wppb_rpf_add_missing_meta_name' );


/**
 * Delete Repeater field option when deleting Repeater field from Manage fields
 *
 * @param $meta
 * @param $id
 * @param $element_id
 */
function wppb_rpf_remove_repeater_field_option( $meta, $id, $element_id ){
    
    $manage_fields = get_option( $meta );

    if( isset( $manage_fields[ $element_id ] ) ){
        $field = $manage_fields[$element_id];

        if( !empty( $field['meta-name'] ) )
            delete_option( $field['meta-name'] );
    }

}
add_action( 'wck_before_remove_meta', 'wppb_rpf_remove_repeater_field_option', 10, 3 );


/**
 * Output list of roles into DOM. Used for Limit per Role option
 *
 * @param $return
 * @return array Unmodified filter parameter.
 */
function wppb_rpf_set_js_roles_list( $return ){

    $return .= '<script type="text/javascript"> var wppb_rpf_roles_list =' . wppb_rpf_get_roles() . '</script>';

    return $return;
}
add_action( 'wck_metabox_content_wppb_manage_fields', 'wppb_rpf_set_js_roles_list' );


/**
 * Initializes the Limit per Role options in JS for a field when the edit form of the field gets rendered
 *
 * @param $id
 */
function wppb_rpf_init_enable_limit_option( $id ) {

    echo '<script type="text/javascript"> jQuery("#container_wppb_manage_fields input[name=rpf-enable-limit]").each( function() { wppb_manage_repeater_field.show_rpf_limit_option( jQuery(this) ) });</script>';
}
add_action( 'wck_after_adding_form_wppb_manage_fields', 'wppb_rpf_init_enable_limit_option');


/**
 * Exclude Repeater fields from Conditional Field options
 *
 * @param array $fields_not_allowed
 * @return array
 */
function wppb_rpf_do_not_allow_repeater( $fields_not_allowed ){
    $fields_not_allowed [] = 'Repeater';
    return $fields_not_allowed;
}
add_filter( 'wppb_conditional_fields_not_allowed' , 'wppb_rpf_do_not_allow_repeater' );


/**
 * WPML Support for dynamic strings in Profile Builder for fields inside Repeater. Tags: WPML, fields, translate
 */
if ( ! empty( $_GET['wppb_rpf_repeater_meta_name'] ) ) {
    add_filter( 'update_option_' . sanitize_text_field( $_GET['wppb_rpf_repeater_meta_name'] ), 'wppb_wpml_compat_with_fields', 10, 2 );
    add_filter( 'add_option_' . sanitize_text_field( $_GET['wppb_rpf_repeater_meta_name'] ), 'wppb_wpml_compat_with_fields', 10, 2 );
}
