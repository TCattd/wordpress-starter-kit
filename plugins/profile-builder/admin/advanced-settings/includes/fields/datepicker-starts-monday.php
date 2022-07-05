<?php

function wppb_toolbox_datepicker_starts_on_monday( $output, $form_location, $field, $user_id, $field_check_errors, $request_data ){
	$field_id = $field['meta-name'];

	$script = '<script>
	jQuery(function(){
	jQuery("#'. $field_id . '").each( function(){
		var currentDatepicker = this;
		jQuery( currentDatepicker ).datepicker("destroy");
		jQuery( currentDatepicker ).datepicker({
	            inline: true,
	            changeYear: true,
				changeMonth: true,
				dateFormat: jQuery( currentDatepicker ).data("dateformat"),
				firstDay: 1,
				yearRange: "c-100:c+30"
			});
		});
	});
	</script>';

	return $output . $script;
}
add_filter( 'wppb_output_form_field_datepicker', 'wppb_toolbox_datepicker_starts_on_monday', 20, 6 );
add_filter( 'wppb_admin_output_form_field_datepicker', 'wppb_toolbox_datepicker_starts_on_monday', 20, 6 );
