<?php
/* @param string $meta Meta name.
 * @param array $details Contains the details for the field.
 * @param string $value Contains input value;
 * @param string $context Context where the function is used. Depending on it some actions are preformed.;
 * @return string $element input element html string. */

/* since the slug below is generated dinamically from other elements we need to determine here if we have a slug or not and not let the wck_generate_slug() function do that */
if( !empty( $details['slug'] ) )
    $slug_from = $details['slug'];
else
    $slug_from = $details['title'];

/* define id's for input and info div */
$upload_input_id = str_replace( '-', '_', Wordpress_Creation_Kit_PB::wck_generate_slug( $meta . $slug_from ) );
$upload_info_div_id = str_replace( '-', '_', Wordpress_Creation_Kit_PB::wck_generate_slug( $meta .'_info_container_'. $slug_from ) );

/* hidden input that will hold the attachment id */
$element.= '<input id="'. esc_attr( $upload_input_id ) .'" type="hidden" size="36" name="'. $single_prefix . esc_attr( Wordpress_Creation_Kit_PB::wck_generate_slug( $details['title'], $details ) ) .'" value="'. $value .'" class="mb-text-input mb-field"/>';

/* container for the image preview (or file ico) and name and file type */
if( !empty ( $value ) ){
    /* it can hold multiple attachments separated by comma */
    $values = explode( ',', $value );
    foreach( $values as $value ){
        $file_src = wp_get_attachment_url($value);
        $thumbnail = wp_get_attachment_image( $value, array( 80, 80 ), true );
        $file_name = get_the_title( $value );
        $file_type = get_post_mime_type( $value );
        $attachment_url = admin_url( "post.php?post={$value}&action=edit" );

        $element.= '<div id="'.esc_attr( $upload_info_div_id ).'_info_container" class="upload-field-details" data-attachment_id="'. $value .'">';
        $element.= '<div class="file-thumb">';
        $element.= "<a href='{$attachment_url}' target='_blank' class='wck-attachment-link'>" . $thumbnail . "</a>";
        $element.= '</div>';

        $element.= '<p><span class="file-name">';
        $element.= $file_name;
        $element.= '</span><span class="file-type">';
        $element.= $file_type;
        $element.= '</span>';
        if( !empty ( $value ) )
            $element.= '<span class="wck-remove-upload">'.__( 'Remove', 'profile-builder' ).'</span>';
        $element.= '</p></div>';
    }
}

$element.= '<a href="#" class="button wck_upload_button" id="upload_'. esc_attr(Wordpress_Creation_Kit_PB::wck_generate_slug( $details['title'], $details ) ) .'_button" data-uploader_title="'. $details['title'] .'" data-uploader_button_text="Select Files" data-upload_input="'.esc_attr( $upload_input_id ).'" ';
if( is_user_logged_in() )
    $element.= 'data-uploader_logged_in="true"';

if( !empty( $post_id ) )
    $element.= ' data-post_id="'. $post_id .'"';

if( !empty( $details['multiple_upload'] ) ){
    if( $details['multiple_upload'] == 'true' )
        $element.= ' data-multiple_upload="true"';
    else
        $element.= ' data-multiple_upload="false"';
}

if( !empty( $details['attach_to_post'] ) ){
    if( $details['attach_to_post'] == true )
        $element.= ' data-attach_to_post="true"';
}

if( $context != 'fep' )
    $element.= ' data-upload_in_backend="true"';
else
    $element.= ' data-upload_in_backend="false"';

if( !empty( $details['allowed_types'] ) )
    $element.= ' data-allowed_types="'. $details['allowed_types'] .'"';

$element.= '>'. __( 'Upload ', 'profile-builder' ) . $details['title'] .'</a>';
?>