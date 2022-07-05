<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/* handle field output */
function wppb_user_role_handler( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){
    if ( $field['field'] == 'Select (User Role)' ){

        $roles_editor_active = false;
        $wppb_generalSettings = get_option( 'wppb_general_settings', 'not_found' );
        if( $wppb_generalSettings != 'not_found' ) {
            if( ! empty( $wppb_generalSettings['rolesEditor'] ) && ( $wppb_generalSettings['rolesEditor'] == 'yes' ) && $form_location == 'edit_profile' && current_user_can('manage_options') ) {
                $roles_editor_active = true;
            }
        }

        $user_role = '';
        $user_roles = '';
        $user_can_manage_options = false;

        if( $form_location == 'edit_profile' && isset($field['user-roles-on-edit-profile']) && $field['user-roles-on-edit-profile'] === 'yes' )
            $show_user_role_on_edit_profile = true;
        else
            $show_user_role_on_edit_profile = false;

        // Get user data, set user's role and check to see if user can manage options
        if( $user_id != 0 ) {
            $user_data = get_userdata( $user_id );

            if( ! empty( $user_data->roles ) ) {
                $user_role = reset( $user_data->roles );
                $user_roles = $user_data->roles;
            }

            if( isset( $user_data->allcaps['manage_options'] ) && $user_data->allcaps['manage_options'] == 1 ) {
                $user_can_manage_options = true;
            }
        }

        $input_value = isset( $request_data['custom_field_user_role'] ) ? $request_data['custom_field_user_role'] : $user_role;
        $input_value_multiple = isset( $request_data['custom_field_user_role'] ) ? $request_data['custom_field_user_role'] : $user_roles;

        $item_title = apply_filters( 'wppb_'.$form_location.'_user_role_custom_field_'.$field['id'].'_item_title', wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_title_translation', $field['field-title'], true ) );
        $item_description = wppb_icl_t( 'plugin profile-builder-pro', 'custom_field_'.$field['id'].'_description_translation', $field['description'], true );

        //get user roles
        if( !empty( $field['user-roles'] ) ) {
            global $wp_roles;

            $available_user_roles = explode( ', ', $field['user-roles'] );

            foreach( $available_user_roles as $key => $role_slug ) {
                if( isset( $wp_roles->roles[$role_slug]['name'] ) ) {
                    $available_user_roles[$key] = array(
                        'slug' => $role_slug,
                        'name' => $wp_roles->roles[$role_slug]['name']
                    );
                } else {
                    unset( $available_user_roles[$key] );
                }
            }
        }

		$extra_attr = apply_filters( 'wppb_extra_attribute', '', $field, $form_location );

        if( $form_location == 'register' || ( $form_location == 'edit_profile' && apply_filters( 'wppb_user_role_select_field_capability', current_user_can('manage_options' )) && $user_can_manage_options == false ) || ( $show_user_role_on_edit_profile && !current_user_can('manage_options') ) ) {
            $error_mark = ( ( $field['required'] == 'Yes' ) ? '<span class="wppb-required" title="'.wppb_required_field_error($field["field-title"]).'">*</span>' : '' );

            if ( array_key_exists( $field['id'], $field_check_errors ) )
                $error_mark = '<img src="'.WPPB_PLUGIN_URL.'assets/images/pencil_delete.png" title="'.wppb_required_field_error($field["field-title"]).'"/>';

            $output = '
				<label for="custom_field_user_role">'.$item_title.$error_mark.'</label>
				<select name="custom_field_user_role'. ( $roles_editor_active ? '[]' : '' ) .'" id="'.$field['meta-name'].'" class="custom_field_user_role '. apply_filters( 'wppb_fields_extra_css_class', '', $field ) .'" '. $extra_attr . ( $roles_editor_active ? ' multiple="multiple"' : '' ) .'>';

				$extra_select_option = apply_filters( 'wppb_extra_select_option', '', $field, $item_title );
				if( ! empty( $extra_select_option ) ) {
					$output .= $extra_select_option;
				}

                if( ! empty( $available_user_roles ) ) {
                    foreach( $available_user_roles as $user_role ) {
                        $output .= '<option value="'. $user_role['slug'] .'"';

                        if( $roles_editor_active && is_array( $input_value_multiple ) ) {
                            if( in_array( $user_role['slug'], $input_value_multiple ) ) {
                                $output .= ' selected="selected" ';
                            }
                        } else {
                            $output .= selected( $input_value, $user_role['slug'], false );
                        }

                        $output .= '>'. translate_user_role( $user_role['name'] ) .'</option>';
                    }
                }

				$output .= '</select>';

            if( $form_location == 'edit_profile' && !$show_user_role_on_edit_profile && !apply_filters( 'wppb_user_role_select_field_capability', current_user_can('manage_options' )) )
                $output .= '<span class="wppb-description-delimiter">'. __( 'Only administrators can see this field on edit profile forms.', 'profile-builder' ) .'</span>';

            if( !empty( $item_description ) )
                $output .= '<span class="wppb-description-delimiter">'.$item_description.'</span>';

        } elseif( $form_location == 'edit_profile' && current_user_can('manage_options') && $user_can_manage_options == true ) {

            $output = '
				<label for="custom_field_user_role">'.$item_title.'</label>
				<p>' . __( 'As an administrator you cannot change your role.', 'profile-builder' ) . '</p>';

            $output .= '</select>';

            if( !$show_user_role_on_edit_profile )
                $output .= '<span class="wppb-description-delimiter">'. __( 'Only administrators can see this field on edit profile forms.', 'profile-builder' ) .'</span>';

            if( !empty( $item_description ) )
                $output .= '<span class="wppb-description-delimiter">'.$item_description.'</span>';

        }
        else{
            if( !empty( $input_value_multiple ) ){
                foreach( $input_value_multiple as $input_value_multi ){
                    $output .= '<input type="hidden" disabled="disabled" readonly="readonly" value="'.$input_value_multi.'">';
                }
            }
            else {
                $output .= '<input type="hidden" disabled="disabled" readonly="readonly" value="' . $input_value . '">';
            }
        }

        return apply_filters( 'wppb_'.$form_location.'_user_role_custom_field_'.$field['id'], $output, $form_location, $field, $user_id, $field_check_errors, $request_data, $input_value, $input_value_multiple );
    }
}
add_filter( 'wppb_output_form_field_select-user-role', 'wppb_user_role_handler', 10, 6 );


/* handle field validation */
function wppb_check_user_role_value( $message, $field, $request_data, $form_location ) {

    if( $form_location == 'edit_profile' && isset($field['user-roles-on-edit-profile']) && $field['user-roles-on-edit-profile'] === 'yes' )
        $show_user_role_on_edit_profile = true;
    else
        $show_user_role_on_edit_profile = false;

    $field['meta-name'] = 'custom_field_user_role';

    if( $form_location == 'back_end' )
        return $message;

    if( $form_location == 'edit_profile' && ( ( !current_user_can( 'manage_options' ) && ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ) ) && !$show_user_role_on_edit_profile ) )
        return __( 'You cannot register this user role', 'profile-builder');

    $roles_editor_active = false;
    $wppb_generalSettings = get_option( 'wppb_general_settings', 'not_found' );
    if( $wppb_generalSettings != 'not_found' ) {
        if( ! empty( $wppb_generalSettings['rolesEditor'] ) && ( $wppb_generalSettings['rolesEditor'] == 'yes' ) && $form_location == 'edit_profile' && current_user_can('manage_options') ) {
            $roles_editor_active = true;
        }
    }

    if( $field['field'] == 'Select (User Role)' ){

        if( ( $form_location == 'register' || $show_user_role_on_edit_profile ) && $field['required'] == 'Yes' && current_user_can( 'manage_options' ) === false ) {
            if( ( isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) && ( trim( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) == '' ) ) || !isset( $request_data[wppb_handle_meta_name( $field['meta-name'] )] ) ){
                return wppb_required_field_error($field["field-title"]);
            }
        }

        if( isset( $field['user-roles'] ) && isset( $request_data['custom_field_user_role'] ) ) {
            $available_user_roles = explode(', ', $field['user-roles'] );

            if( $roles_editor_active && is_array( $request_data['custom_field_user_role'] ) ) {
                foreach( $request_data['custom_field_user_role'] as $key => $value ) {
                    if( ! in_array( $value, $available_user_roles ) ) {
                        return __( 'You cannot register this user role', 'profile-builder');
                    }
                }
            } else {
                if( ! in_array( $request_data['custom_field_user_role'], $available_user_roles ) ) {
                    return __( 'You cannot register this user role', 'profile-builder');
                }
            }
        }

    }

    return $message;
}
add_filter( 'wppb_check_form_field_select-user-role', 'wppb_check_user_role_value', 10, 4 );


/* handle field save */
function wppb_userdata_add_user_role( $userdata, $global_request, $form_args ){

    if( wppb_field_exists_in_form( 'Select (User Role)', $form_args ) ) {

        $roles_editor_active = false;
        $wppb_generalSettings = get_option('wppb_general_settings', 'not_found');
        if ($wppb_generalSettings != 'not_found') {
            if (!empty($wppb_generalSettings['rolesEditor']) && $wppb_generalSettings['rolesEditor'] === 'yes' && current_user_can('manage_options')) {
                $roles_editor_active = true;
            }
        }

        if (isset($global_request['custom_field_user_role'])) {
            if ($roles_editor_active && is_array($global_request['custom_field_user_role'])) {
                $user_roles = array_map('trim', $global_request['custom_field_user_role']);
                $user_roles = array_map('sanitize_text_field', $user_roles);

                //don't allow administrator value. it should never be here but just in case make a hard check
                if (($key = array_search("administrator", $user_roles)) !== false) {
                    unset($user_roles[$key]);
                }

                $userdata['role'] = $user_roles;
            } else {
                $role = sanitize_text_field(trim($global_request['custom_field_user_role']));
                if( $role !== 'administrator' ) {//don't allow administrator value. it should never be here but just in case make a hard check
                    $userdata['role'] = $role;
                }
            }
        }
    }

    return $userdata;
}
add_filter( 'wppb_build_userdata', 'wppb_userdata_add_user_role', 10, 3 );
