<?php
 /* @param string $meta Meta name.	 
 * @param array $details Contains the details for the field.	 
 * @param string $value Contains input value;
 * @param string $context Context where the function is used. Depending on it some actions are preformed.;
 * @return string $element input element html string. */

if( !empty( $details['options'] ) ){
	$element .= '<div class="wck-radiobuttons">';
	foreach( $details['options'] as $option ){
		$found = false;
		
		$values = explode( ', ', $value );						
		if( strpos( $option, '%' ) === false  ){
			$label = $option;
			$value_attr = $option;
			if ( in_array( $option, $values ) ) 
				$found = true;
		}
		else{
			$option_parts = explode( '%', $option );
			if( !empty( $option_parts ) ){
				if( empty( $option_parts[0] ) && count( $option_parts ) == 3 ){
					$label = $option_parts[1];
					$value_attr = $option_parts[2];
					if ( in_array( $option_parts[2], $values ) ) 
						$found = true;
				}
			}
		}
				
		$element .= '<div><label><input type="radio" name="'. $single_prefix . esc_attr( Wordpress_Creation_Kit_PB::wck_generate_slug( $details['title'], $details ) ) .'" id="';
		if( !empty( $frontend_prefix ) )
			$element .=	$frontend_prefix;

        /* since the slug below is generated from the value as well we need to determine here if we have a slug or not and not let the wck_generate_slug() function do that */
        if( !empty( $details['slug'] ) )
            $slug_from = $details['slug'];
        else
            $slug_from = $details['title'];
		$element .= esc_attr( Wordpress_Creation_Kit_PB::wck_generate_slug( $slug_from . '_' . $value_attr ) ) .'" value="'. esc_attr( $value_attr ) .'"  '. checked( $found, true, false ) .'class="mb-radio mb-field" />'. esc_html( $label ) .'</label></div>';
	}
	$element .= '</div>';
}
?>