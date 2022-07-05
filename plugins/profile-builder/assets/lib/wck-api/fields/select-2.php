<?php
/* @param string $meta Meta name.
 * @param array $details Contains the details for the field.
 * @param string $value Contains input value;
 * @param string $context Context where the function is used. Depending on it some actions are preformed.;
 * @return string $element input element html string. */

$id = esc_attr( Wordpress_Creation_Kit_PB::wck_generate_slug( $details['title'], $details ) );
if( !empty( $frontend_prefix ) )
    $id = $frontend_prefix.$id;

$element .= '<select name="'. $single_prefix . esc_attr( Wordpress_Creation_Kit_PB::wck_generate_slug( $details['title'], $details ) ) .'"  id="'.$id.'"';
$element .= '" class="mb-select-2 mb-field" >';

if( !empty( $details['default-option'] ) && $details['default-option'] )
    $element .= '<option value="">'. __('Select or type in an option', 'profile-builder') .'</option>';

$field_name = Wordpress_Creation_Kit_PB::wck_generate_slug( $details['title'], $details );

// we're passing this further to a function. Need to make sure it exists so we don't get a notice.
if( empty( $details['value'] ) ){
    $details['value'] = false;
}

$options = '';
if( !empty( $details['options'] ) ){

    $new_nonexisting_value = true;

    $i = 0;
    foreach( $details['options'] as $option ){
        $optionOutput = Wordpress_Creation_Kit_PB::wck_generate_select_option($option, $details['value'], $i, $value);
        $options .= apply_filters("wck_select_{$meta}_{$field_name}_option_{$i}", $optionOutput, $i);

        $i++;

        if( !empty( $value ) && ( $option === $value || strpos( $option, '%'.$value, -(strlen($value) + 1) ) !== false ) )//it is not a custom value because it is present in the options
            $new_nonexisting_value = false;
    }

    //display the custom value that was inserted with select 2 that was not present in options
    if( $new_nonexisting_value )
        $options .= '<option value="'. esc_attr( $value ) .'"  '. selected( $value, $value, false ) .' >'. esc_html( $value ) .'</option>';
}

$element .= apply_filters( "wck_select_{$meta}_{$field_name}_options", $options );
$element .= '</select>';
$element .= '<script type="text/javascript">
                if(typeof jQuery.fn.select2 === "undefined"){jQuery( function() {jQuery( ".mb-select-2").select2({ tags: true, placeholder: "Select or type in an option" });});}
                else{jQuery( ".mb-select-2").select2({ tags: true, placeholder: "Select or type in an option" });}
             </script>';





