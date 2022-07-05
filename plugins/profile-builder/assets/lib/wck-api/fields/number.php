<?php

$extra_attr = apply_filters( 'wck_extra_field_attributes', '', $details, $meta );

$element .= '<input type="number" name="'. $single_prefix . esc_attr( Wordpress_Creation_Kit_PB::wck_generate_slug( $details['title'], $details ) ) .'" id="';
if( !empty( $frontend_prefix ) )
    $element .=	$frontend_prefix;
$element .=	esc_attr( Wordpress_Creation_Kit_PB::wck_generate_slug( $details['title'], $details ) ) .'"';
if( !empty( $details['readonly'] ) && $details['readonly'] )
    $element .=	'readonly="readonly"';
$element .=	' step="'. ( ! empty( $details['number-step-value'] ) ? $details['number-step-value'] : 'any' ) .'" type="number" min="'. ( isset( $details['min-number-value'] ) ? $details['min-number-value'] : '' ) .'" max="'. ( isset( $details['max-number-value'] ) ? $details['max-number-value'] : '' ) .'"';
$element .=	' value="'. esc_attr( $value ) .'" class="mb-number-input mb-field" '.$extra_attr.'/>';
