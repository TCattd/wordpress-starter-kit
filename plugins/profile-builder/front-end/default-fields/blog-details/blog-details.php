<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/* handle field output */
function wppb_blog_details_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){

    // Display "Yes, I'd like to create a new site" checkbox if we're on the PB Register form and we are on Multisite with Network setting "Both sites and user accounts can be registered".
    if  ( ( $form_location != 'register' ) ||  ( ! wppb_can_users_signup_blog() ) ){
        return $output;
    }

    //Check if Blog Details field is added in Manage Fields
    $in_manage_fields = false;
    $wppb_manage_fields = get_option( 'wppb_manage_fields', 'not_set' );
    if ( $wppb_manage_fields != 'not_set' ){
        foreach ( $wppb_manage_fields as $field ) {
            if ( $field['field'] == 'Default - Blog Details' ) {
                $in_manage_fields = true;
                break;
            }
        }
    }
    if ( ! $in_manage_fields ) {
        return $output;
    }


    $output = '<ul>';

    $item_description = wppb_icl_t( 'plugin profile-builder-pro', 'default_field_'.$field['id'].'_description_translation', $field['description'], true );
    $heading = '<li class="wppb-form-field wppb-blog-details-heading"><h4>'.wppb_icl_t('plugin profile-builder-pro', 'default_field_'.$field['id'].'_title_translation', $field['field-title'], true).'</h4><span class="wppb-description-delimiter">'.$item_description.'</span></li>';
    $output .= apply_filters( 'wppb_blog_details_heading', $heading );


    ?><script type="text/javascript">
        jQuery(document).ready(function(){

            wppb_toggle_required_attrbute_for_blog_details();
            jQuery("#wppb_create_new_site_checkbox").on("click",function(){

                wppb_toggle_required_attrbute_for_blog_details();
                jQuery(".wppb-blog-details-fields").toggle();
            });
            function wppb_toggle_required_attrbute_for_blog_details(){

                // Trigger a custom event that will remove the HTML attribute -required- for hidden fields. This is necessary for browsers to allow form submission.
                if(document.getElementById('wppb_create_new_site_checkbox').checked) {
                    jQuery(".wppb-blog-details-fields input" ).trigger( "wppbAddRequiredAttributeEvent" );
                } else {
                    jQuery(".wppb-blog-details-fields input" ).trigger( "wppbRemoveRequiredAttributeEvent" );
                }
            }
        });
    </script> <?php
    $checked = '';
    if ( isset( $request_data['wppb_create_new_site_checkbox']) && ( $request_data['wppb_create_new_site_checkbox'] == 'yes') ) {
        $checked = 'checked';
    }else{
        echo '<style> .wppb-blog-details-fields {display:none;}  </style>';
    }
    $create_new_site_checkbox = '
                    <li class=" wppb-form-field wppb-create-new-site " id="wppb-create-new-site">
                    <label for="wppb_create_new_site_checkbox">
                    <input id="wppb_create_new_site_checkbox" type="checkbox" name="wppb_create_new_site_checkbox" value="yes" '.$checked.' autocomplete="off">
                    <strong>'. __('Yes, I\'d like to create a new site','profile-builder').'</strong> </label>
                    </li>';
    $output .= apply_filters( 'wppb_blog_details_checkbox', $create_new_site_checkbox );

    $output .= '<ul class="wppb-blog-details-fields">';

    // Site URL
    $item_description = __( 'Your site url will look like this:<br>', 'profile-builder' );
    if ( is_subdomain_install() ) {
        global $current_site;
        $subdomain_base = apply_filters( 'wppb_blogs_subdomain_base', preg_replace( '|^www\.|', '', $current_site->domain ) . $current_site->path );
        $domain = '"http://'. esc_attr( '<your-slug>.' ) . $subdomain_base;
    } else {
        $domain = '"' . esc_url( home_url( '/' ) )  . esc_attr( '<your-slug>' ) . '"';
    }
    $blog_url_input_value = '';
    $blog_url_input_value = ( isset( $request_data['wppb_blog_url'] ) ? trim( $request_data['wppb_blog_url'] ) : $blog_url_input_value );
    $error_mark = '<span class="wppb-required" title="'.wppb_required_field_error($field["field-title"]).'">*</span>';

    $extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );

    $error_class = '';
    $is_error = wppb_check_individual_blog_fields( 'wppb_blog_url', $request_data, $form_location );
    if ($is_error != '') {
        $error_mark = '<img src="' . WPPB_PLUGIN_URL . 'assets/images/pencil_delete.png" title="' . wppb_required_field_error('') . '"/>';
        $error_class = ' wppb-field-error';
    }

    $output .= '
        <li class=" wppb-form-field wppb-blog-url ' . $error_class . '">
        <label for="blog-url">' .  __( 'Site URL slug', 'profile-builder' ) . $error_mark.'</label>
        <input class="text-input default_field_blog_url" name="wppb_blog_url" maxlength="'. apply_filters( 'wppb_maximum_character_length', 70, $field ) .'" type="text" id="wppb_blog_url" value="'. esc_attr( wp_unslash( $blog_url_input_value ) ) .'" '. $extra_attr .' />';
    $output .= '<span class="wppb-description-delimiter">'. $item_description . $domain . '</span>';
    $output .= $is_error .'</li>';



    // Site title
    $blog_title_input_value = '';
    $blog_title_input_value = ( isset( $request_data['wppb_blog_title'] ) ? trim( $request_data['wppb_blog_title'] ) : $blog_title_input_value );
    $error_mark = '<span class="wppb-required" title="'.wppb_required_field_error($field["field-title"]).'">*</span>';

    $extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );

    $error_class = '';
    $is_error = wppb_check_individual_blog_fields( 'wppb_blog_title', $request_data, $form_location );
    if ($is_error != '') {
        $error_mark = '<img src="' . WPPB_PLUGIN_URL . 'assets/images/pencil_delete.png" title="' . wppb_required_field_error('') . '"/>';
        $error_class = ' wppb-field-error';
    }

    $output .= '
        <li class=" wppb-form-field wppb-blog-title ' . $error_class . '">
        <label for="blog-title">' .  __( 'Site Title', 'profile-builder' ) . $error_mark.'</label>
        <input class="text-input default_field_blog_title" name="wppb_blog_title" maxlength="'. apply_filters( 'wppb_maximum_character_length', 70, $field ) .'" type="text" id="wppb_blog_title" value="'. esc_attr( wp_unslash( $blog_title_input_value ) ) .'" '. $extra_attr .' />' .
        $is_error . '</li>';



    // Privacy
    $blog_privacy_input_value = 'Yes';
    $blog_privacy_input_value = ( isset( $request_data['wppb_blog_privacy'] ) ? trim( $request_data['wppb_blog_privacy'] ) : $blog_privacy_input_value );
    $error_mark = '<span class="wppb-required" title="'.wppb_required_field_error($field["field-title"]).'">*</span>';

    $radio_values = array( 'Yes', 'No' );

    $error_class = '';
    $is_error = wppb_check_individual_blog_fields( 'wppb_blog_privacy', $request_data, $form_location );
    if ($is_error != '') {
        $error_mark = '<img src="' . WPPB_PLUGIN_URL . 'assets/images/pencil_delete.png" title="' . wppb_required_field_error('') . '"/>';
        $error_class = ' wppb-field-error';
    }

    $output .= '
        <li class=" wppb-form-field wppb-blog-privacy ' . $error_class . ' ">
        <label for="blog-privacy">'. __( 'Privacy: I would like my site to appear in search engines, and in public listings around this network.', 'profile-builder' ) . $error_mark.'</label>';
    $output .= '<ul class="wppb-radios">';
    foreach( $radio_values as $key => $value){
        $output .= '<li><input value="'.esc_attr( trim( $value ) ).'" class="blog_privacy_radio '. apply_filters( 'wppb_fields_extra_css_class', '', $field ) .'" id="'.Wordpress_Creation_Kit_PB::wck_generate_slug( trim( $value ) ).'_'.$field['id'].'" name="wppb_blog_privacy" type="radio" '. $extra_attr .' ';

        if ( $blog_privacy_input_value === trim( $value ) )
            $output .= ' checked';

        $output .= ' /><label for="'.Wordpress_Creation_Kit_PB::wck_generate_slug( trim( $value ) ).'_'.$field['id'].'" class="wppb-rc-value">'. trim( $radio_values[$key] ) .'</label></li>';
    }
    $output .= '</ul>' . $is_error . '</li>';

    // end wppb-blog-details-fields
    $output .= '</ul>';

    $output .= '</ul>';

    return apply_filters( 'wppb_blog_details_output', $output, $form_location, $field, $user_id, $field_check_errors, $request_data );

}
add_filter( 'wppb_output_form_field_default-blog-details', 'wppb_blog_details_handler', 10, 6 );

/* handle field save */
function wppb_create_blog_on_registration( $field, $user_id, $request_data, $form_location ){
    if( $form_location == 'register' && $field['field'] == 'Default - Blog Details' && isset( $request_data['wppb_create_new_site_checkbox'] ) && $request_data['wppb_create_new_site_checkbox'] == 'yes' ) {
        $blog_url = $request_data['wppb_blog_url'];
        $blog_title = $request_data['wppb_blog_title'];

        $usermeta['public'] = ( isset( $request_data['wppb_blog_privacy'] ) && 'Yes' == $request_data['wppb_blog_privacy'] ) ? true : false;
        $blog_details = wpmu_validate_blog_signup( $blog_url, $blog_title );
        if ( empty($blog_details['errors']->errors['blogname']) && empty($blog_details['errors']->errors['blog_title'])) {
            wpmu_create_blog( $blog_details['domain'], $blog_details['path'], $blog_details['blog_title'], $user_id, $usermeta );
        }
    }
}
add_action( 'wppb_save_form_field', 'wppb_create_blog_on_registration', 10, 4 );

/* handle field validation */
function wppb_check_blog_details_values( $message, $field, $request_data, $form_location ){
    if ( isset( $request_data['wppb_create_new_site_checkbox'] ) && $request_data['wppb_create_new_site_checkbox'] == 'yes' ){
        $blog_fields_array = wppb_blog_details_fields_array();
        foreach ( $blog_fields_array as $blog_field ){
            if( ( isset( $request_data[$blog_field] ) && ( trim( $request_data[$blog_field] ) == '' ) ) || !isset( $request_data[$blog_field] ) ){
                return wppb_required_field_error($blog_field);
            }
        }
    }
    return $message;
}
add_filter( 'wppb_check_form_field_default-blog-details', 'wppb_check_blog_details_values', 10, 4 );

/* Add blog details information to wp_signups table (when Email Confirmation is active) */
function wppb_add_blog_details_to_signup_table( $meta, $global_request, $role ){
    if ( isset( $global_request['wppb_create_new_site_checkbox'] ) && $global_request['wppb_create_new_site_checkbox'] == 'yes' ) {
        $blog_details_fields_array = wppb_blog_details_fields_array();

        foreach ($blog_details_fields_array as $blog_field) {
            $meta[$blog_field] = $global_request[$blog_field];
        }
    }
    return $meta;
}
add_filter( 'wppb_add_to_user_signup_form_meta', 'wppb_add_blog_details_to_signup_table',10, 3 );



function wppb_blog_details_fields_array(){
    return array(
        'wppb_blog_title',
        'wppb_blog_url',
        'wppb_blog_privacy',
        'wppb_create_new_site_checkbox'
    );
}

function wppb_check_individual_blog_fields( $field_key, $request_data, $form_location ){
    if ( isset( $request_data['wppb_create_new_site_checkbox'] ) && $request_data['wppb_create_new_site_checkbox'] == 'yes' ) {
        if ( $field_key == 'wppb_blog_privacy' && ( ! isset( $request_data[$field_key] ) || ( isset( $request_data[$field_key] ) && ( trim( $request_data[$field_key] ) == '' ) ) ) ) {
                return '<span class="wppb-form-error">' . wppb_required_field_error($field_key) . '</span>';
        }

        $wp_error = wpmu_validate_blog_signup($request_data['wppb_blog_url'], $request_data['wppb_blog_title']);

        if ( $field_key == 'wppb_blog_url' && !empty($wp_error['errors']->errors['blogname'])){
            return '<span class="wppb-form-error">' . $wp_error['errors']->errors['blogname'][0] . '</span>';
        }
        if ( $field_key == 'wppb_blog_title' && !empty($wp_error['errors']->errors['blog_title'])){
            return '<span class="wppb-form-error">' . $wp_error['errors']->errors['blog_title'][0] . '</span>';
        }

    }
    return '';
}

