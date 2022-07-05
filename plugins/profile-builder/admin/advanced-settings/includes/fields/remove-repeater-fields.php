<?php

function wppb_toolbox_remove_repeater_from_admin() {
    remove_filter('wppb_admin_output_form_field_repeater', 'wppb_repeater_handler', 10);
}
add_action( 'admin_init', 'wppb_toolbox_remove_repeater_from_admin' );
