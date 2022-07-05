<?php 
 /* @param string $meta Meta name.	 
 * @param array $details Contains the details for the field.	 
 * @param string $value Contains input value;
 * @param string $context Context where the function is used. Depending on it some actions are preformed.;
 * @return string $element input element html string. */
 
$element .= '<textarea name="'. $single_prefix . esc_attr( Wordpress_Creation_Kit_PB::wck_generate_slug( $details['title'], $details ) ) .'" style="vertical-align:top;width:400px;height:200px" class="mb-textarea mb-field '. esc_attr( Wordpress_Creation_Kit_PB::wck_generate_slug( $details['title'], $details ) ) .'">'. esc_html( $value ) .'</textarea>';
$element .= '<script type="text/javascript">jQuery( function(){ if ( typeof wckInitTinyMCE == "function" ) wckInitTinyMCE("'. Wordpress_Creation_Kit_PB::wck_generate_slug( $details['title'], $details ) .'")});</script>';
?>