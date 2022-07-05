<?php 
 /* @param string $meta Meta name.	 
 * @param array $details Contains the details for the field.	 
 * @param string $value Contains input value;
 * @param string $context Context where the function is used. Depending on it some actions are preformed.;
 * @return string $element input element html string. */

$element .=  sprintf(  __( 'You can add the information for the %s after you add an entry', 'profile-builder' ), $details['title'] );
?>