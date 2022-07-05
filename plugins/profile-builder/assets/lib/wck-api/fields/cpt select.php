<?php
/* @param string $meta Meta name.	 
 * @param array $details Contains the details for the field.	 
 * @param string $value Contains input value;
 * @param string $context Context where the function is used. Depending on it some actions are preformed.;
 * @return string $element input element html string. */
 
$args = apply_filters( 'wck-cpt-select-args', array( 'post_type' => $details['cpt'], 'orderby' => 'menu_order title', 'order' => 'ASC', 'posts_per_page' => '200', 'post_status' => 'publish' ), $details );			

$cpt_query = new WP_Query($args);

if( !empty( $cpt_query->posts ) ){
	$element .= '<select name="'. $single_prefix . esc_attr( Wordpress_Creation_Kit_PB::wck_generate_slug( $details['title'], $details ) ) .'"  id="';
	if( !empty( $frontend_prefix ) )
		$element .=	$frontend_prefix;  
	$element .= esc_attr( Wordpress_Creation_Kit_PB::wck_generate_slug( $details['title'], $details ) ) .'" class="mb-user-select mb-field" >';
	$element .= '<option value="">'. __('...Choose', 'profile-builder') .'</option>';
	foreach( $cpt_query->posts as $cpt ){
		if ( $cpt->post_title == '' )
			$cpt->post_title = 'No title. ID: ' . $cpt->ID;
		
		$element .= '<option value="'. esc_attr( $cpt->ID ) .'"  '. selected( $cpt->ID, $value, false ) .' >'. esc_html( $cpt->post_title ) .'</option>';					
	}				
	$element .= '</select>';
}
?>