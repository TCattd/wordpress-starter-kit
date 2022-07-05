<?php

/**
 * The code that runs during plugin activation.
 */

if( !function_exists( 'wppb_in_msf_check_break_points_on_activation' ) ){

    function wppb_in_msf_check_break_points_on_activation( $addon ) {

        if( $addon == 'multi-step-forms' ){

            $wppb_manage_fields = get_option( 'wppb_manage_fields', 'not_found' );
            $old_break_points   = get_option( 'wppb_msf_break_points', 'not_found' );

            if( $old_break_points != 'not_found' ) {
                $break_points = array();

                foreach( $wppb_manage_fields as $field ) {
                    if( array_search( intval( $field['id'] ), $old_break_points ) ) {
                        $break_points[intval( $field['id'] )] = intval( $field['id'] );
                    }
                }

                update_option( 'wppb_msf_break_points', $break_points );
            }

            $args = array(
                'numberposts' => 0,
                'post_type'   => array( 'wppb-rf-cpt', 'wppb-epf-cpt' )
            );

            $rf_epf_posts = get_posts( $args );

            if( isset( $rf_epf_posts ) ) {
                foreach( $rf_epf_posts as $post ) {
                    if( $post->post_type == 'wppb-rf-cpt' ) {
                        $post_fields_option = 'wppb_rf_fields';
                    } elseif ( $post->post_type == 'wppb-epf-cpt' ) {
                        $post_fields_option = 'wppb_epf_fields';
                    }

                    if( isset( $post_fields_option ) ) {
                        $forms_fields = get_post_meta( intval( $post->ID ), $post_fields_option, true );
                        $forms_fields = is_array( $forms_fields ) ? $forms_fields : 'not_found';
                        $old_forms_break_points = get_post_meta( intval( $post->ID ), 'wppb_msf_break_points', true );
                        $old_forms_break_points = is_array( $old_forms_break_points ) ? $old_forms_break_points : 'not_found';

                        if( $old_forms_break_points != 'not_found' && $forms_fields != 'not_found' ) {
                            $forms_break_points = array();

                            foreach( $forms_fields as $field ) {
                                if( array_search( intval( $field['id'] ), $old_break_points ) ) {
                                    $forms_break_points[intval( $field['id'] )] = intval( $field['id'] );
                                }
                            }

                            update_post_meta( intval( $post->ID ), 'wppb_msf_break_points', $forms_break_points );
                        }
                    }
                }
            }

        }

    }
    add_action( 'wppb_add_ons_activate', 'wppb_in_msf_check_break_points_on_activation', 10, 1);

}
