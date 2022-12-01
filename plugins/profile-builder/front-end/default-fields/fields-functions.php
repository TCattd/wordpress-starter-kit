<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// the function to display the custom fields in the back-end
function wppb_display_fields_in_admin( $user ){
	$admin_fields = '';
	?>
	<script type="text/javascript">
		var form = document.getElementById('your-profile');
		form.encoding = "multipart/form-data"; //IE5.5
		form.setAttribute('enctype', 'multipart/form-data'); //required for IE6 (is interpreted into "encType")

		jQuery(function(){
			//hover states on the static widgets
            jQuery('#dialog_link, ul#icons li').on('mouseenter', function() { jQuery(this).addClass('ui-state-hover'); });
            jQuery('#dialog_link, ul#icons li').on('mouseleave', function() { jQuery(this).removeClass('ui-state-hover'); });
		});
	</script>
	<?php

	$all_data = get_option( 'wppb_manage_fields' );
	if ( is_array( $all_data ) ){
		foreach ( $all_data as $value ) {

            $display_field = apply_filters( 'wppb_output_display_form_field', true, $value, 'back_end', 'all', $user->ID );

            if( $display_field == false )
                continue;

            $admin_fields .= apply_filters( 'wppb_admin_output_form_field_'.Wordpress_Creation_Kit_PB::wck_generate_slug( $value['field'] ), '', 'back_end', $value, $user->ID, '', $_REQUEST );
        }

	}

	echo $admin_fields; //phpcs:ignore  WordPress.Security.EscapeOutput.OutputNotEscaped
}

// the function to save the values from the custom fields in the back-end
function wppb_save_fields_in_admin( $user_id ){
    $global_request = $_REQUEST;
	$all_data = apply_filters( 'wppb_form_fields', get_option( 'wppb_manage_fields' ), array( 'context' => 'validate_backend' ) );
	if ( is_array( $all_data ) ){
		foreach ( $all_data as $field ){
            /* check to see if we have any error for the field. if we do don't save it */
            $error_for_field = apply_filters( 'wppb_check_form_field_'.Wordpress_Creation_Kit_PB::wck_generate_slug( $field['field'] ), '', $field, $global_request, 'back_end', '', $user_id );
			if( empty( $error_for_field ) )
                do_action( 'wppb_backend_save_form_field',  $field, $user_id, $global_request, 'backend-form' );
        }
	}
}

/* the function that checks for field error in the backend */
function wppb_validate_fields_in_admin( &$errors, $update, &$user ){

    $all_data = apply_filters( 'wppb_form_fields', get_option( 'wppb_manage_fields' ), array( 'context' => 'validate_backend' ) );
    $global_request = $_REQUEST;
    if ( is_array( $all_data ) ){
        foreach ( $all_data as $field ){
            $error_for_field = apply_filters( 'wppb_check_form_field_'.Wordpress_Creation_Kit_PB::wck_generate_slug( $field['field'] ), '', $field, $global_request, 'back_end', '', get_current_user_id() );

            if( !empty( $error_for_field ) ){
                $errors->add( $field['id'], '<strong>'. __( 'ERROR', 'profile-builder' ).'</strong> '.$field['field-title'].':'.$error_for_field);
            }
        }
    }
}
