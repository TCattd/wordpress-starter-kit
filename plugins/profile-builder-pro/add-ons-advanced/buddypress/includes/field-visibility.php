<?php

/**
 * Add Field Visibility options for each field
 *
 * @since v.1.0.0
 *
 * @param $fields
 *
 * @return mixed
 */
function wppb_in_bdp_add_visibility_options( $fields ) {

    $visibility_levels = wppb_in_bdp_get_visibility_levels_array();
    $visibility_options = array();
    foreach ( $visibility_levels as $level ){
        $visibility_options[] = '%' . $level['label'] . '%' .  $level['id'];
    }

    $allow_custom_visibility_options = array( '%' . __( 'Allow members to override', 'profile-builder' ) . '%allowed', '%' . __( 'Enforce field visibility', 'profile-builder' ) . '%disabled' );

    array_push( $fields, array( 'type' => 'select', 'slug' => 'bdp-default-visibility', 'title' => __( 'BuddyPress Default Field Visibility', 'profile-builder' ), 'options' => $visibility_options ) );
    array_push( $fields, array( 'type' => 'select', 'slug' => 'bdp-allow-custom-visibility', 'title' => __( 'BuddyPress Allow Custom Visibility', 'profile-builder' ), 'options' => $allow_custom_visibility_options ) );

    return $fields;

}
add_filter( 'wppb_manage_fields', 'wppb_in_bdp_add_visibility_options' );


/**
 * Output visibility disabled field list into DOM.
 *
 * Used for deciding whether to show BuddyPress Visibility option.
 *
 * @since v.1.0.0
 *
 * @param $return
 *
 * @return array Unmodified filter parameter.
 */
function wppb_in_bdp_set_js_disabled_field_list( $return ){

    echo '<script type="text/javascript"> var wppb_bdp_visibility_disabled_field_list = \'' . json_encode(wppb_in_bdp_get_visibility_disabled_fields()) . '\'; </script>';

    return $return;
}
add_action( 'wck_metabox_content_wppb_manage_fields', 'wppb_in_bdp_set_js_disabled_field_list' );


/**
 * Return Array of fields that will not support Visibility settings
 *
 * @since v.1.0.0
 *
 * @return array
 */
function wppb_in_bdp_get_visibility_disabled_fields() {
    return apply_filters( 'wppb_bdp_disable_visibility_options_for_fields',
        array(
            'Default - Username',
            'Default - Password',
            'Default - Repeat Password',
            'Default - Name (Heading)',
            'Default - Contact Info (Heading)',
            'Default - About Yourself (Heading)',
            'Default - Display name publicly as',
            'Heading',
            'Input (Hidden)',
            'Checkbox (Terms and Conditions)',
            'Avatar',
            'reCAPTCHA',
            'Validation',
            'HTML',
            'MailChimp Subscribe',
            'MailPoet Subscribe',
            'Campaign Monitor Subscribe',
            'Email Confirmation',
        ));
}


/**
 * Add Front-end visibility settings for users on registration and edit profile forms
 *
 * @since v.1.0.0
 *
 * @param $output
 * @param $field
 * @param $form_id
 * @param $form_type
 * @param $called_from
 *
 * @return string Html Output for visibility settings
 */
function wppb_in_bdp_frontend_field_visibility_settings( $output, $field, $form_id, $form_type, $called_from ) {
    $user_id = wppb_in_bdp_get_desired_user_id($form_type);
    $disabled_fields = wppb_in_bdp_get_visibility_disabled_fields();
    if ( in_array( $field['field'], $disabled_fields ) || !empty( $field['wppb-rpf-meta-data'] ) ) {
        return $output;
    }

    if ( wppb_in_bdp_allow_custom_visibility( $field ) ){
        $visibility_settings = '<p class="wppb-field-visibility-settings-toggle" id="wppb-field-visibility-settings-toggle-' . esc_attr( $field['id'] ) . '">';
        $visibility_settings .= __( 'This field can be seen by: ', 'profile-builder' ) . '<span class="wppb-current-visibility-level">' . wppb_in_bdp_get_visibility_level( 'label', $field, $user_id ) . '</span><span class="wppb-visibility-toggle-link">' . __( 'Change', 'profile-builder' ) . '</span>';
        $visibility_settings .= '</p>';

        $visibility_settings .= '<div class="wppb-field-visibility-settings" style="display: none;" id="wppb-field-visibility-settings-' . esc_attr( $field['id'] ) . '">';
        $visibility_settings .= '<fieldset>';
        $visibility_settings .= '<legend>' . __( 'Who can see this field?', 'profile-builder' ) . '</legend>';

        $visibility_settings .= wppb_in_bdp_get_frontend_visibility_buttons( $field, $user_id );

        $visibility_settings .= '</fieldset>';
        $visibility_settings .= '<span class="wppb-visibility-toggle-link wppb-visibility-close">' . __( 'Close', 'profile-builder' ) . '</span>';
        $visibility_settings .= '</div>';
    }else{
        $visibility_settings = '<p class="wppb-field-visibility-settings-notoggle" id="wppb-field-visibility-settings-toggle-' . esc_attr( $field['id'] ) .'">';
        $visibility_settings .= __( 'This field can be seen by: ', 'profile-builder' ) . '<span class="wppb-current-visibility-level">' . wppb_in_bdp_get_visibility_level( 'label', $field, $user_id ) . '</span>';
    }

    return '<div class="wppb_bdp_visibility_settings">' . $visibility_settings . '</div>' . $output;
}
add_filter( 'wppb_output_after_form_field', 'wppb_in_bdp_frontend_field_visibility_settings', 10, 5 );


/**
 * Function that returns the id for the current logged in user or for edit profile forms for administrator it can return the id of a selected user'
 *
 * @since v.1.0.0
 *
 * @param $form_type
 *
 * @return int
 */
function wppb_in_bdp_get_desired_user_id( $form_type ){
    if( $form_type == 'edit_profile' ){
        //only admins
        if( ( !is_multisite() && current_user_can( 'edit_users' ) ) || ( is_multisite() && current_user_can( 'manage_network' ) ) ) {
            if( ! empty( $_GET['edit_user'] ) && is_numeric( $_GET[ 'edit_user' ] ) && absint( $_GET[ 'edit_user' ] ) == $_GET[ 'edit_user' ] ){
                return sanitize_text_field( $_GET['edit_user'] );
            }
        }
    }
    return get_current_user_id();
}


/**
 * Return true if Custom Vsibility is allowed, false otherwise
 *
 * @since 1.0.0
 *
 * @param $field
 *
 * @return bool
 */
function wppb_in_bdp_allow_custom_visibility( $field ){
    if ( !empty( $field['bdp-allow-custom-visibility'] ) ){
        if ( $field['bdp-allow-custom-visibility'] == 'disabled' )
            return false;
    }
    return true;
}


/**
 * Returns Visibility level for a specific user's field.
 *
 * Returns label or id based on first parameter
 *
 * @since 1.0.0
 *
 * @param $label_or_id
 * @param $field
 * @param $user_id
 *
 * @return string
 */
function wppb_in_bdp_get_visibility_level( $label_or_id, $field, $user_id ){
    $visibility_id = '';
    if ( !empty( $user_id) ){
        $visibility_option = get_user_meta( $user_id, WPPB_IN_BDP_VISIBILITY_OPTION_NAME, true );
        if ( !empty( $visibility_option[$field['id']] ) ) {
            $visibility_id = $visibility_option[$field['id']];
        }
    }
    if ( empty( $visibility_id ) ) {
        if ( empty( $field['bdp-default-visibility'] ) )
            $visibility_id = 'public';
        else
            $visibility_id = $field['bdp-default-visibility'];
    }

    // force default visibility
    if ( !empty( $field['bdp-allow-custom-visibility'] ) && $field['bdp-allow-custom-visibility'] == 'disabled' )
        $visibility_id = $field['bdp-default-visibility'];

    $visibility_label = wppb_in_bdp_get_visibility_levels_array();
    return $visibility_label[$visibility_id][$label_or_id];
}


/**
 * Returns array of visibility levels id and labels
 *
 * @since v.1.0.0
 *
 * @return array
 */
 function wppb_in_bdp_get_visibility_levels_array(){
     $visibility_levels = apply_filters( 'wppb_bdp_visibility_levels',
         array(
             'public'    => array(
                                 'id'    => 'public',
                                 'label' => __( 'Everyone', 'profile-builder' )
                             ),
             'adminsonly'=> array(
                                 'id'    => 'adminsonly',
                                 'label' => __( 'Only Me', 'profile-builder' )
                             ),
             'loggedin'  => array(
                                 'id'    => 'loggedin',
                                 'label' => __( 'All Members', 'profile-builder' )
                             ),
             'friends'   => array(
                                 'id'    => 'friends',
                                 'label' => __( 'My Friends', 'profile-builder' )
                             ),
         ));

     return $visibility_levels;
 }


/**
 * Return HTML output for visibility options
 *
 * @since 1.0.0
 *
 * @param $field
 * @param $user_id
 *
 * @return string
 */
function wppb_in_bdp_get_frontend_visibility_buttons( $field, $user_id ){
    $output = '';
    foreach (wppb_in_bdp_get_visibility_levels_array() as $level) {
        $output .= '<label for="' . esc_attr('see-field_' . $field['id'] . '_' . $level['id']) . '">';
        $output .= '<input type="radio" id="' . esc_attr('see-field_' . $field['id'] . '_' . $level['id']) . '" name="' . esc_attr('wppb_bdp_field_' . $field['id'] . '_visibility') . '" value="' . esc_attr($level['id']) . '" ' . checked( $level['id'], wppb_in_bdp_get_visibility_level( 'id', $field, $user_id ), false ) . '/>';
        $output .= '<span class="field-visibility-text">' . esc_html($level['label']) . '</span>';
        $output .= '</label>';
    }
    return $output;
}


/**
 * Updates visibility options for each field of a user
 *
 * @since 1.0.0
 *
 * @param $request
 * @param $form_name
 * @param $user_id
 *
 * @return null
 */
function wppb_in_bdp_save_visibility_options( $request, $form_name, $user_id ){
    $visibility_option = wppb_in_bdp_build_visibility_levels_option( $request, $user_id );

    update_user_meta( $user_id, WPPB_IN_BDP_VISIBILITY_OPTION_NAME, $visibility_option );
}
add_action( 'wppb_register_success', 'wppb_in_bdp_save_visibility_options', 5, 3 );
add_action( 'wppb_edit_profile_success', 'wppb_in_bdp_save_visibility_options', 5, 3 );


/**
 * Store user's field visibility options on User sign up
 *
 * @since 1.0.0
 *
 * @param $meta
 * @param $global_request
 * @param $role
 *
 * @return array
 */
function wppb_in_bdp_add_visibility_levels_to_user_signup( $meta, $global_request, $role ){
    $visibility_option = wppb_in_bdp_build_visibility_levels_option( $global_request );
    $meta[WPPB_IN_BDP_VISIBILITY_OPTION_NAME] = $visibility_option;

    return $meta;
}
add_filter( 'wppb_add_to_user_signup_form_meta', 'wppb_in_bdp_add_visibility_levels_to_user_signup', 10, 3 );


/**
 * Update user's field visibility options on User sign up
 *
 * @since 1.0.0
 *
 * @param $user_id
 * @param $meta
 *
 * @return null
 */
function wppb_in_bdp_add_visibility_levels_on_user_activation( $user_id, $meta ){
    if ( !empty ( $meta[WPPB_IN_BDP_VISIBILITY_OPTION_NAME] ) ){
        update_user_meta( $user_id, WPPB_IN_BDP_VISIBILITY_OPTION_NAME, $meta[WPPB_IN_BDP_VISIBILITY_OPTION_NAME] );
    }
}
add_action( 'wppb_add_other_meta_on_user_activation', 'wppb_in_bdp_add_visibility_levels_on_user_activation', 10, 2 );


/**
 * Build Visibility levels option for a particular user
 *
 * @since 1.0.0
 *
 * @param $request
 * @param string $user_id
 *
 * @return array|void
 */
function wppb_in_bdp_build_visibility_levels_option( $request, $user_id = 'not_set' ){
    $manage_fields = get_option('wppb_manage_fields', 'not_set');
    if ($manage_fields == 'not_set') {
        return;
    }

    $visibility_option = get_user_meta( $user_id, WPPB_IN_BDP_VISIBILITY_OPTION_NAME, true );
    if ( empty ($visibility_option )) {
        $visibility_option = array();
    }
    $visibility_levels = wppb_in_bdp_get_visibility_levels_array();
    foreach ($manage_fields as $field) {
        if ( !empty( $request['wppb_bdp_field_' . $field['id'] . '_visibility'] ) && isset( $visibility_levels[ $request[ 'wppb_bdp_field_' . $field['id'] . '_visibility' ] ] ) ){
            $visibility_option[$field['id']] = $request[ 'wppb_bdp_field_' . $field['id'] . '_visibility' ];
        }
    }

    return $visibility_option;
}



/*
 * Userlisting features
 */

/**
 * Removes html element based on visibility setting
 *
 * Uses bp_visibility and user_id attributes of each html element.
 *
 * @since 1.0.0
 *
 * @param $ul_template
 * @param int $userID
 * @return bool|wppb_bdp_simple_html_dom
 */
function wppb_in_bdp_userlisting_adjust_field_visibility( $ul_template, $userID = '' ){
    $current_user_id = get_current_user_id();

    // Create a DOM object from a string
    $html = ProfileBuilder\BuddyPressAddon\str_get_html($ul_template);

    // Find all elements with attribute [bp_visibility]
    if ( is_object( $html ) ) {
        $visibility_affected_elements = $html->find('[bp_visibility]');
        foreach ($visibility_affected_elements as $element) {
            if (empty($element->user_id)) {
                $displayed_user_id = $userID;
            } else {
                $displayed_user_id = $element->user_id;
            }
            if (!empty ($element->bp_visibility) && !empty($displayed_user_id)) {

                if (!wppb_in_bdp_display_this_field($element->bp_visibility, $displayed_user_id, $current_user_id)) {

                    // do not display field so remove this element from DOM
                    $element->outertext = '';
                }
            }
        }
        $ul_template = $html;
    }
    return $ul_template ;
}
add_filter( 'wppb_single_userlisting_template', 'wppb_in_bdp_userlisting_adjust_field_visibility', 10, 2 );
add_filter( 'wppb_all_userlisting_template', 'wppb_in_bdp_userlisting_adjust_field_visibility', 10, 1 );


/**
 * Cover function for wppb_bdp_display_this_field_call
 *
 * Used for filtering
 *
 * @since 1.0.0
 *
 * @param $field_meta_name
 * @param $displayed_user_id
 * @param $current_user_id
 *
 * @return bool
 */
function wppb_in_bdp_display_this_field( $field_meta_name, $displayed_user_id, $current_user_id ){
    return apply_filters( 'wppb_bdp_display_field', wppb_in_bdp_display_this_field_call( $field_meta_name, $displayed_user_id, $current_user_id ) );
}


/**
 * Returns true if field can be displayed based on user and field visibility options. False otherwise.
 *
 * @since 1.0.0
 *
 * @param $field_meta_name
 * @param $displayed_user_id
 * @param $current_user_id
 *
 * @return bool
 */
function wppb_in_bdp_display_this_field_call( $field_meta_name, $displayed_user_id, $current_user_id ){
    if ( ! function_exists( 'wppb_get_field_by_id_or_meta' )){
        return true;
    }
    $field = wppb_get_field_by_id_or_meta( $field_meta_name );

    if ( empty ( $field ) ){
        $default_field_title = apply_filters( 'wppb_bdp_visibility_field_titles', array( 'display_name' => 'Default - Display name publicly as', 'website' => 'Default - Website', 'email' => 'Default - E-mail', 'description' => 'Default - Biographical Info', 'biographical_info' => 'Default - Biographical Info' ) );
        if ( !empty ( $default_field_title[$field_meta_name]) ) {
            $manage_fields = get_option('wppb_manage_fields');
            foreach ($manage_fields as $field) {
                if ($field['field'] == $default_field_title[$field_meta_name])
                    break;
            }
        }
    }

    $field = apply_filters( 'wppb_bdp_field_for_visibility', $field, $field_meta_name, $displayed_user_id, $current_user_id );
    if ( empty ( $field ) ){
        return true;
    }

    $visibility_level = wppb_in_bdp_get_visibility_level( 'id', $field, $displayed_user_id );

    switch ($visibility_level) {
        case 'public' :
            return true;
        case 'adminsonly' :
            if ( ($displayed_user_id == $current_user_id) || current_user_can( apply_filters( 'wppb_bdp_visibile_to_adminsonly_capability', 'bp_moderate') ) ) {
                return true;
            } else {
                return false;
            }
        case 'loggedin' :
            if ($current_user_id == 0) {
                return false;
            } else {
                return true;
            }
        case 'friends' :
            if ( function_exists( 'bp_is_active' ) && function_exists( 'friends_check_friendship' ) ) {
                if ( ( bp_is_active('friends') && friends_check_friendship( $displayed_user_id, $current_user_id ) ) || current_user_can( apply_filters( 'wppb_bdp_visibile_to_adminsonly_capability', 'bp_moderate' ) ) )
                    return true;
            }
            return false;
    }

    return true; // display field
}


/**
 * Functions used for Fool proofing visibility on User meta
 *
 * Return null instead of value if field should not be displayed.
 *
 * @since 1.0.0
 *
 * @param $value
 * @param $field_meta_name
 * @param $displayed_user_id
 *
 * @return string|null
 */
function wppb_in_bdp_enforce_desired_visibility_directly_on_tags_for_user_meta( $value, $field_meta_name, $displayed_user_id ){
    $current_user_id = get_current_user_id();
    if ( ! wppb_in_bdp_display_this_field( $field_meta_name, $displayed_user_id, $current_user_id ) ) {
        return '';
    }
    return $value;
}
add_filter( 'wppb_userlisting_user_meta_value', 'wppb_in_bdp_enforce_desired_visibility_directly_on_tags_for_user_meta', 20, 3 );

function wppb_in_bdp_enforce_desired_visibility_directly_on_tags_for_user_meta_labels( $value, $field_meta_name, $show_values, $displayed_user_id ){
    $current_user_id = get_current_user_id();
    if ( ! wppb_in_bdp_display_this_field( $field_meta_name, $displayed_user_id, $current_user_id ) ) {
        return '';
    }
    return $value;
}
add_filter( 'wppb_userlisting_user_meta_value_label', 'wppb_in_bdp_enforce_desired_visibility_directly_on_tags_for_user_meta_labels', 20, 4 );

function wppb_in_bdp_enforce_desired_visibility_directly_on_tags_for_default_user_fields( $value, $name, $displayed_user_id ){
    $field_meta_name = preg_replace('/meta_/', '', $name, 1);
    $current_user_id = get_current_user_id();
    if ( ! wppb_in_bdp_display_this_field( $field_meta_name, $displayed_user_id, $current_user_id ) ) {
        return '';
    }
    return $value;
}
add_filter( 'wppb_userlisting_default_user_field_value', 'wppb_in_bdp_enforce_desired_visibility_directly_on_tags_for_default_user_fields', 20, 3 );

function wppb_in_bdp_enforce_desired_visibility_directly_on_tags_for_user_meta_map( $value, $name, $children, $extra_info ){
    $displayed_user_id = ( !empty( $extra_info['user_id'] ) && !empty( $extra_info['single'] ) )  ? $extra_info['user_id'] : wppb_get_query_var( 'username' ) ;
    $field_meta_name = preg_replace('/meta_/', '', $name, 1);
    $current_user_id = get_current_user_id();
    if ( ! wppb_in_bdp_display_this_field( $field_meta_name, $displayed_user_id, $current_user_id ) ) {
        return '';
    }
    return $value;
}
add_filter( 'mustache_variable_user_meta_map', 'wppb_in_bdp_enforce_desired_visibility_directly_on_tags_for_user_meta_map', 20, 4 );
