<?php
/*
Description: Extends the functionality of Profile Builder by adding the possibility to have custom css classes on fields
*/

/*
 * Function that enqueues the necessary scripts
 *
 * @since v.1.0.0
 */
function wppb_ccc_scripts( $hook ) {
    if ( $hook == 'profile-builder_page_manage-fields' ) {
        wp_enqueue_script('wppb-custom-css-class-field', plugin_dir_url(__FILE__) . 'assets/js/main.js', array('jquery', 'wppb-manage-fields-live-change'));
    }
}
add_action( 'admin_enqueue_scripts', 'wppb_ccc_scripts' );

/*
 * Function that adds the numbers only checkbox on an input field.
 *
 * @since v.1.0.0
 *
 * @param array $fields - The current field properties
 *
 * @return array        - The field properties that now include the numbers only checkbox
 */
function wppb_ccc_field( $fields ) {
    $class = array(
        'type' => 'text',
        'slug' => 'class-field',
        'title' => __( 'CSS Class', 'profile-builder' ),
        'description' => __( "Add a class to a field. Should not contain dots(.) and for multiple classes separate by space.", 'profile-builder' )
    );
    array_push( $fields, $class );
    return $fields;
}
add_filter( 'wppb_manage_fields', 'wppb_ccc_field' );

function wppb_ccc_class( $class, $field, $error_var ){
    if( !empty( $field['class-field'] ) ){
        $class .= ' '. esc_attr( $field['class-field'] );
    }
    return $class;
}
add_filter( 'wppb_field_css_class', 'wppb_ccc_class', 10, 3 );