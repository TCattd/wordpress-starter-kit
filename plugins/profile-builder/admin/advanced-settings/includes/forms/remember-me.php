<?php

add_action( 'wppb_login_form_bottom', 'wppb_toolbox_rememberme_checked', 99, 2 );
function wppb_toolbox_rememberme_checked( $form_part, $args ) {
	return $form_part.'<script>if ( document.getElementById("rememberme") ) document.getElementById("rememberme").checked = true;</script>';
}
