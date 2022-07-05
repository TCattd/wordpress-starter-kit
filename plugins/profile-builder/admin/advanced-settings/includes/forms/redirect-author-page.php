<?php

function wppb_toolbox_redirect_author_page_if_not_approved() {
	if (!is_author()) return;

	$author = (get_query_var('author_name')) ? get_user_by('slug', get_query_var('author_name')) : get_userdata(get_query_var('author'));

	if ( wp_get_object_terms( $author->ID, 'user_status' ) ){
		wp_redirect( home_url() );
		die();
	} else
		return;
}
add_action( 'template_redirect', 'wppb_toolbox_redirect_author_page_if_not_approved' );
