<?php

    /*
    Profile Builder - Multi-Step Forms Add-On
    License: GPL2

    == Copyright ==
    Copyright 2015 Cozmoslabs (www.cozmoslabs.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
    */

/**
 * Function that enqueues the necessary styles and scripts in backend
 *
 * @since   v.1.0.0
 */
function wppb_in_msf_scripts() {
    $screen = get_current_screen();

    if( defined( 'PROFILE_BUILDER_VERSION' ) && ( $screen->id == 'profile-builder_page_manage-fields' || $screen->id == 'wppb-rf-cpt' || $screen->id == 'wppb-epf-cpt' ) ) {
        wp_enqueue_style( 'wppb-msf-style', plugin_dir_url( __FILE__ ) . 'assets/css/multi-step-forms.css' );
        wp_enqueue_script( 'wppb-msf-script', plugin_dir_url( __FILE__ ) . 'assets/js/multi-step-forms.js', array( 'jquery' ) );

        $vars_array = array(
            'ajaxUrl'				    => admin_url( 'admin-ajax.php' ),
            'ajaxNonce'                 => wp_create_nonce( 'wppb_msf_backend_nonce' ),
            'tabTitle'		            => __( 'Step', 'profile-builder' ),
            'tabTitlePlaceholder'	    => __( 'Title for Tab', 'profile-builder' ),
            'tabsTitleDesc'		        => __( 'Add Break Points to edit tabs title.', 'profile-builder' ),
            'tabsTitleDescUnsavedForm'  => __( 'Publish the form to edit tabs title.', 'profile-builder' ),
            'alertUnsavedForm'          => __( 'Publish the form before adding Break Points!', 'profile-builder' ),
            'alertAjaxRequestInProcess' => __( 'Request in process, please wait a few seconds before a new one!', 'profile-builder' )
        );

        wp_localize_script( 'wppb-msf-script', 'wppb_msf_data', $vars_array );
    }
}
add_action( 'admin_enqueue_scripts', 'wppb_in_msf_scripts' );

/**
 * Function that enqueues the necessary scripts in frontend
 *
 * @since   v.1.0.0
 */
function wppb_in_msf_frontend_assets() {
    global $wppb_shortcode_on_front;

    if( $wppb_shortcode_on_front ) {
        wp_enqueue_script( 'wppb-msf-script-frontend', plugin_dir_url( __FILE__ ) . 'assets/js/frontend-multi-step-forms.js', array( 'jquery' ), PROFILE_BUILDER_VERSION );
        wp_enqueue_style( 'wppb-msf-style-frontend', plugin_dir_url( __FILE__ ) . 'assets/css/frontend-multi-step-forms.css', array(), PROFILE_BUILDER_VERSION );

        $vars_array = array(
            'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
            'ajaxNonce' => wp_create_nonce( 'wppb_msf_frontend_nonce' )
        );

        wp_localize_script( 'wppb-msf-script-frontend', 'wppb_msf_data_frontend', $vars_array );
    }
}
add_action( 'wp_footer', 'wppb_in_msf_frontend_assets' );

//Make sure scripts and styles are loaded when an Elementor popup is used
if ( did_action( 'elementor/loaded' ) ) {
    add_action( 'elementor/frontend/after_render', 'wppb_in_msf_frontend_assets' );
}

/**
 * Function that adds the Meta Boxes on each Manage Fields and Multiple Registration / Edit Profile Forms
 *
 * @since   v.1.0.0
 */
function wppb_in_msf_add_meta_boxes() {
    add_meta_box( 'wppb-msf-side', __( 'Multi-Step Forms', 'profile-builder' ), 'wppb_in_msf_meta_boxes_content', array( 'wppb-epf-cpt', 'wppb-rf-cpt', 'profile-builder_page_manage-fields' ), 'side', 'low' );
}
add_action( 'add_meta_boxes', 'wppb_in_msf_add_meta_boxes', 11 );

/**
 * Function that adds content to Meta Boxes on each Manage Fields and Multiple Registration / Edit-Profile Forms
 *
 * @param   WP_Post  $post   current post
 *
 * @since   v.1.0.0
 */
function wppb_in_msf_meta_boxes_content( $post ) {
    $screen = get_current_screen();

    if( $screen->id == 'profile-builder_page_manage-fields' ) {
        $wppb_msf_options = get_option( 'wppb_msf_options', 'not_found' );

        if( $wppb_msf_options == 'not_found' ) {
            $wppb_msf_options = array( 'pb-default-register' => 'yes', 'pb-default-edit-profile' => 'yes', 'msf-pagination' => 'no', 'msf-tabs' => 'no' );
            update_option( 'wppb_msf_options', $wppb_msf_options );
        }

        if( isset( $_POST['wppb_msf_save_options'] ) && wp_verify_nonce( sanitize_text_field( $_POST['wppb_msf_save_options'] ), 'wppb-msf-options-verify' ) ) {
            $wppb_msf_options = array(
                'pb-default-register'		=> isset( $_POST['pb-default-register'] ) ? sanitize_text_field( $_POST['pb-default-register'] ) : 'no',
                'pb-default-edit-profile'	=> isset( $_POST['pb-default-edit-profile'] ) ? sanitize_text_field( $_POST['pb-default-edit-profile'] ) : 'no',
                'msf-pagination'	        => isset( $_POST['msf-pagination'] ) ? sanitize_text_field( $_POST['msf-pagination'] ) : 'no',
                'msf-tabs'	                => isset( $_POST['msf-tabs'] ) ? sanitize_text_field( $_POST['msf-tabs'] ) : 'no'
            );

            if( isset( $_POST['msf-tab-title'] ) ) {
                foreach( $_POST['msf-tab-title'] as $key => $value ) { //phpcs:ignore
                    if( ! empty( $value ) ) {
                        $wppb_msf_tab_titles[$key] = sanitize_text_field( $value );
                    }
                }

                if( ! empty( $wppb_msf_tab_titles ) ) {
                    update_option( 'wppb_msf_tab_titles', $wppb_msf_tab_titles );
                }
            }

            update_option( 'wppb_msf_options', $wppb_msf_options );

            echo '<div class="notice notice-success is-dismissible"><p>'. esc_html__( 'Multi-Step Forms options updated.', 'profile-builder' ) .'</p></div>';
        }

        ?>
        <form id="wppb-msf-options" name="wppb-msf-options" method="POST" action="">
            <p><strong><?php esc_html_e( 'Enable on:', 'profile-builder' ); ?></strong></p>
            <p><label><input type="checkbox" id="pb-default-register" name="pb-default-register" value="yes" <?php echo $wppb_msf_options != 'not_found' && $wppb_msf_options['pb-default-register'] == 'yes' ? 'checked' : ''; ?>> <?php esc_html_e( 'PB Default Register Form', 'profile-builder' ); ?></label></p>
            <p><label><input type="checkbox" id="pb-default-edit-profile" name="pb-default-edit-profile" value="yes" <?php echo $wppb_msf_options != 'not_found' && $wppb_msf_options['pb-default-edit-profile'] == 'yes' ? 'checked' : ''; ?>> <?php esc_html_e( 'PB Default Edit Profile Form', 'profile-builder' ); ?></label></p>
            <p class="description"><?php esc_html_e( 'To enable it on Multiple Registration and Edit-Profile Forms you must add Break Points in each form page.', 'profile-builder' ); ?></p>
            <p><strong><?php esc_html_e( 'Pagination and Tabs:', 'profile-builder' ); ?></strong></p>
            <p><label><input type="checkbox" id="msf-pagination" name="msf-pagination" value="yes" <?php echo $wppb_msf_options != 'not_found' && isset( $wppb_msf_options['msf-pagination'] ) && $wppb_msf_options['msf-pagination'] == 'yes' ? 'checked' : ''; ?>> <?php esc_html_e( 'Enable Pagination', 'profile-builder' ); ?></label></p>
            <p><label><input type="checkbox" id="msf-tabs" name="msf-tabs" value="yes" <?php echo $wppb_msf_options != 'not_found' && isset( $wppb_msf_options['msf-tabs'] ) && $wppb_msf_options['msf-tabs'] == 'yes' ? 'checked' : ''; ?>> <?php esc_html_e( 'Enable Tabs', 'profile-builder' ); ?> <span class="wppb-msf-edit-tabs-title" style="display: none" >- <a id="wppb-msf-edit-tabs-title" href="#"><?php esc_html_e( 'Edit Tabs Title', 'profile-builder' ); ?></a></span></label></p>
            <div class="wppb-msf-tabs-title-container" style="display: none;"></div>
            <div id="major-publishing-actions"><input name="save" type="submit" class="button button-primary button-large" id="wppb_msf_submit" value="<?php esc_html_e( 'Update Multi-Step', 'profile-builder' ); ?>"></div>
            <?php wp_nonce_field( 'wppb-msf-options-verify', 'wppb_msf_save_options', false ); ?>
        </form>
        <?php
    } else {
        $wppb_msf_post_options = get_post_meta( $post->ID, 'wppb_msf_post_options', true );
        $wppb_msf_post_options = is_array( $wppb_msf_post_options ) ? $wppb_msf_post_options : 'not_found';

        if( $wppb_msf_post_options == 'not_found' ) {
            $wppb_msf_post_options = array( 'msf-pagination' => 'no', 'msf-tabs' => 'no' );
            update_post_meta( $post->ID, 'wppb_msf_post_options', $wppb_msf_post_options );
        }

        $wppb_msf_check_post_status = '';
        if( get_post_status( $post->ID ) !== 'publish' ) {
            $wppb_msf_check_post_status = '<p class="description wppb-msf-post-publish-error">'. esc_html__( 'Publish the form before adding Break Points!', 'profile-builder' ) .'</p>';
        }

        ?>
        <?php echo $wppb_msf_check_post_status; //phpcs:ignore ?>
        <p class="description"><?php esc_html_e( 'To enable MSF you must add Break Points.', 'profile-builder' ); ?></p>
        <p><strong><?php esc_html_e( 'Pagination and Tabs:', 'profile-builder' ); ?></strong></p>
        <p><label><input type="checkbox" id="msf-pagination" name="msf-pagination" value="yes" <?php echo $wppb_msf_post_options != 'not_found' && $wppb_msf_post_options['msf-pagination'] == 'yes' ? 'checked' : ''; ?>> <?php esc_html_e( 'Enable Pagination', 'profile-builder' ); ?></label></p>
        <p><label><input type="checkbox" id="msf-tabs" name="msf-tabs" value="yes" <?php echo $wppb_msf_post_options != 'not_found' && $wppb_msf_post_options['msf-tabs'] == 'yes' ? 'checked' : ''; ?>> <?php esc_html_e( 'Enable Tabs', 'profile-builder' ); ?> <span class="wppb-msf-edit-tabs-title" style="display: none" >- <a id="wppb-msf-edit-tabs-title" href="#"><?php esc_html_e( 'Edit Tabs Title', 'profile-builder' ); ?></a></span></label></p>
        <div class="wppb-msf-tabs-title-container" style="display: none;"></div>
        <?php
        wp_nonce_field( 'wppb-msf-post-'. $post->ID .'-options-verify', 'wppb_msf_save_post_options', false );
    }

    // WPML support for Multi Step Form - Next/Previous buttons text
    if( function_exists( 'wppb_icl_register_string' )) {
        wppb_icl_register_string('plugin profile-builder-pro', 'msf_next_button_text_translation', 'Next');
        wppb_icl_register_string('plugin profile-builder-pro', 'msf_previous_button_text_translation', 'Previous');
    }

}

/**
 * Function that save content from Meta Boxes on each Multiple Registration / Edit-Profile Forms
 *
 * @param   int     $post_id    Post ID
 *
 * @since   v.1.0.0
 */
function wppb_in_msf_save_metabox_content( $post_id ) {
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if( isset( $_POST['wppb_msf_save_post_options'] ) && wp_verify_nonce( sanitize_text_field( $_POST['wppb_msf_save_post_options'] ), 'wppb-msf-post-'. $post_id .'-options-verify' ) ) {
        $wppb_msf_post_options = array(
            'msf-pagination'	        => isset( $_POST['msf-pagination'] ) ? sanitize_text_field( $_POST['msf-pagination'] ) : 'no',
            'msf-tabs'	                => isset( $_POST['msf-tabs'] ) ? sanitize_text_field( $_POST['msf-tabs'] ) : 'no'
        );

        if( isset( $_POST['msf-tab-title'] ) ) {
            foreach( $_POST['msf-tab-title'] as $key => $value ) { //phpcs:ignore
                if( ! empty( $value ) ) {
                    $wppb_msf_tab_titles[$key] = sanitize_text_field( $value );

                    // WPML support for Multi Step Form - Tab titles
                    if( function_exists( 'wppb_icl_register_string' ) && isset( $_POST['post_type'] )) {
                        if( $_POST['post_type'] === 'wppb-epf-cpt' )
                            $form_type = 'edit_profile';
                        elseif( $_POST['post_type'] === 'wppb-rf-cpt' )
                            $form_type = 'register';
                        wppb_icl_register_string('plugin profile-builder-pro', 'msf_'. $form_type .'_step_' . $key . '_tab_title_translation', $value);
                    }

                    
                }
            }

            if( ! empty( $wppb_msf_tab_titles ) ) {
                update_post_meta( $post_id, 'wppb_msf_tab_titles', $wppb_msf_tab_titles );
            }
        }

        update_post_meta( $post_id, 'wppb_msf_post_options', $wppb_msf_post_options );
    }
}
add_action( 'save_post', 'wppb_in_msf_save_metabox_content' );

/**
 * Function that outputs code for steps before first form field
 *
 * @since   v.1.0.0
 *
 * @param   string      $output         code to output before first form field
 * @param   string      $form_id        ID of the form
 * @param   string      $form_type      type of the form
 *
 * @return  string
 */
function wppb_in_msf_output_before_first_form_field( $output, $form_id, $form_type, $form_fields, $called_from ) {
    if( ! is_null( $form_id ) ) {
        $break_points = get_post_meta( $form_id, 'wppb_msf_break_points', true );
        $break_points = is_array( $break_points ) ? $break_points : 'not_found';
    } else {
        $break_points = get_option( 'wppb_msf_break_points', 'not_found' );
        $wppb_msf_options = get_option( 'wppb_msf_options', 'not_found' );

        if( $wppb_msf_options != 'not_found' ) {
            if( ( $wppb_msf_options['pb-default-register'] == 'no' && $form_type == 'register' ) || ( $wppb_msf_options['pb-default-edit-profile'] == 'no' && $form_type == 'edit_profile' ) ) {
                return $output;
            }
        }
    }

    if( $break_points != 'not_found' && ! empty( $break_points ) && is_null( $called_from ) ) {
        $output .= '<li class="wppb-msf-step"><ul style="margin: 0;">';
    }

    return $output;
}
add_filter( 'wppb_output_before_first_form_field', 'wppb_in_msf_output_before_first_form_field', 10, 5 );

/**
 * Function that outputs code for steps after last form field
 *
 * @param   string  $output   code to output after last form field
 * @param   string  $form_id   ID of the form
 * @param   string  $form_type   type of the form
 *
 * @since   v.1.0.0
 *
 * @return  string
 */
function wppb_in_msf_output_after_last_form_field( $output, $form_id, $form_type, $called_from ) {
    if( ! is_null( $form_id ) ) {
        $break_points = get_post_meta( $form_id, 'wppb_msf_break_points', true );
        $break_points = is_array( $break_points ) ? $break_points : 'not_found';
    } else {
        $break_points = get_option( 'wppb_msf_break_points', 'not_found' );
        $wppb_msf_options = get_option( 'wppb_msf_options', 'not_found' );

        if( $wppb_msf_options != 'not_found' ) {
            if( ( $wppb_msf_options['pb-default-register'] == 'no' && $form_type == 'register' ) || ( $wppb_msf_options['pb-default-edit-profile'] == 'no' && $form_type == 'edit_profile' ) ) {
                return $output;
            }
        }
    }

    if( $break_points != 'not_found' && ! empty( $break_points ) && is_null( $called_from ) ) {
        $output .= '</ul></li>';
    }

    return $output;
}
add_filter( 'wppb_output_after_last_form_field', 'wppb_in_msf_output_after_last_form_field', 10, 4 );

/**
 * Function that outputs the end of a step in form
 *
 * @since   v.1.0.0
 *
 * @param   string  $field_output   code to output after field
 * @param   array   $field  array with the field details
 * @param   string  $form_id    ID of the form
 * @param   string  $form_type   type of the form
 *
 * @return  string  $field_output
 */
function wppb_in_msf_output_after_form_field( $field_output, $field, $form_id, $form_type, $called_from ) {
    if( ! is_null( $form_id ) ) {
        $break_points = get_post_meta( $form_id, 'wppb_msf_break_points', true );
        $break_points = is_array( $break_points ) ? $break_points : 'not_found';
    } else {
        $break_points = get_option( 'wppb_msf_break_points', 'not_found' );
        $wppb_msf_options = get_option( 'wppb_msf_options', 'not_found' );

        if( $wppb_msf_options != 'not_found' ) {
            if( ( $wppb_msf_options['pb-default-register'] == 'no' && $form_type == 'register' ) || ( $wppb_msf_options['pb-default-edit-profile'] == 'no' && $form_type == 'edit_profile' ) ) {
                return $field_output;
            }
        }
    }

    if( $break_points != 'not_found' && array_key_exists( $field['id'], $break_points ) && is_null( $called_from ) ) {
        $field_output .= '</ul></li><li class="wppb-msf-step" style="display: none;"><ul style="margin: 0;">';
    }

    return $field_output;
}
add_filter( 'wppb_output_after_form_field', 'wppb_in_msf_output_after_form_field', 10, 5 );

/**
 * Function that hides the send credentials checkbox when needed
 *
 * @param   string  $output   code to output after field
 * @param   string  $form_type   type of the form
 * @param   string  $form_id    ID of the form
 *
 * @since   v.1.0.0
 *
 * @return  string
 */
function wppb_in_msf_hide_send_credentials_checkbox( $output, $form_type, $form_id ) {
    if( ! is_null( $form_id ) ) {
        $break_points = get_post_meta( $form_id, 'wppb_msf_break_points', true );
    } else {
        $break_points = get_option( 'wppb_msf_break_points', 'not_found' );
        $wppb_msf_options = get_option( 'wppb_msf_options', 'not_found' );

        if( $wppb_msf_options != 'not_found' ) {
            if( ( $wppb_msf_options['pb-default-register'] == 'no' && $form_type == 'register' ) || ( $wppb_msf_options['pb-default-edit-profile'] == 'no' && $form_type == 'edit_profile' ) ) {
                return $output;
            }
        }
    }

    if( $break_points != 'not_found' && ! empty( $break_points ) ) {
        $output = '<ul style="display: none;">';
    }

    return $output;
}
add_filter( 'wppb_before_send_credentials_checkbox', 'wppb_in_msf_hide_send_credentials_checkbox', 10, 3 );

/**
 * Function that hides the Submit button when needed
 *
 * @param   string  $output   code to output after field
 * @param   string  $form_type   type of the form
 * @param   string  $form_id    ID of the form
 *
 * @since   v.1.0.0
 *
 * @return  string
 */
function wppb_in_msf_hide_form_submit( $output, $form_type, $form_id ) {
    if( ! is_null( $form_id ) ) {
        $break_points = get_post_meta( $form_id, 'wppb_msf_break_points', true );
    } else {
        $break_points = get_option( 'wppb_msf_break_points', 'not_found' );
        $wppb_msf_options = get_option( 'wppb_msf_options', 'not_found' );

        if( $wppb_msf_options != 'not_found' ) {
            if( ( $wppb_msf_options['pb-default-register'] == 'no' && $form_type == 'register' ) || ( $wppb_msf_options['pb-default-edit-profile'] == 'no' && $form_type == 'edit_profile' ) ) {
                return $output;
            }
        }
    }

    if( $break_points != 'not_found' && ! empty( $break_points ) ) {
        if( $form_type != 'edit_profile' ) {
            $output .= 'style="display: none;" data-wppb-msf="yes"';
        } else {
            $output .= 'data-wppb-msf-ep="yes"';
        }
    }

    return $output;
}
add_filter( 'wppb_form_submit_extra_attr', 'wppb_in_msf_hide_form_submit', 10, 3 );

/**
 * Function that adds buttons and pagination on form
 *
 * @param   string  $output   code to output before form fields
 * @param   string  $form_type   type of the form
 * @param   string  $form_id   ID of the form
 *
 * @since   v.1.0.0
 *
 * @return  string
 */
function wppb_in_msf_after_form_fields( $output, $form_type, $form_id ) {
    $next_button_label = apply_filters( 'wppb_msf_next_button_label', __( 'Next', 'profile-builder' ) );
    $previous_button_label = apply_filters( 'wppb_msf_previous_button_label', __( 'Previous', 'profile-builder' ) );

    // check for WPML translations
    $next_button_label = wppb_icl_t( 'plugin profile-builder-pro', 'msf_next_button_text_translation', $next_button_label );
    $previous_button_label = wppb_icl_t( 'plugin profile-builder-pro', 'msf_previous_button_text_translation', $previous_button_label );

    if( ! is_null( $form_id ) ) {
        $break_points = get_post_meta( $form_id, 'wppb_msf_break_points', true );
        $wppb_msf_options = get_post_meta( $form_id, 'wppb_msf_post_options', true );
        $wppb_msf_options = is_array( $wppb_msf_options ) ? $wppb_msf_options : 'not_found';
    } else {
        $break_points = get_option( 'wppb_msf_break_points', 'not_found' );
        $wppb_msf_options = get_option( 'wppb_msf_options', 'not_found' );

        if( $wppb_msf_options != 'not_found' ) {
            if( ( $wppb_msf_options['pb-default-register'] == 'no' && $form_type == 'register' ) || ( $wppb_msf_options['pb-default-edit-profile'] == 'no' && $form_type == 'edit_profile' ) ) {
                return $output;
            }
        }
    }

    if( $break_points != 'not_found' && ! empty( $break_points ) ) {
        $pagination_output = '';
        if( isset( $wppb_msf_options ) && $wppb_msf_options != 'not_found' && $wppb_msf_options['msf-pagination'] == 'yes' ) {
            for( $i = 0; $i <= count( $break_points ); $i++ ) {
                $pagination_output .= '<input type="button" id="wppb-msf-pagination-'. $i .'" data-msf-disabled-check="'. ( $i != 0 && $form_type != 'edit_profile' ? 'yes' : 'no' ) .'" data-msf-step="'. $i .'" class="wppb-msf-pagination '. ( $i == 0 ? 'wppb-msf-active' : '' ) .'" value="'. ( $i + 1 ) .'" '. ( $i != 0 && $form_type != 'edit_profile' ? 'disabled' : '' ) .'>';
            }
        }

        $output = '<li class="wppb-msf-step-commands"><input type="button" class="wppb-msf-button wppb-msf-prev" style="float: left; padding: 8px 15px;" value="'. $previous_button_label .'" disabled><input type="button" class="wppb-msf-button wppb-msf-next" style="float: right; padding: 8px 15px;" value="'. $next_button_label .'"><span id="wppb-msf-pagination">'. $pagination_output .'</span></li>'. $output;
    }

    return $output;
}
add_filter( 'wppb_after_form_fields', 'wppb_in_msf_after_form_fields', 10, 3 );

/**
 * Function that adds the spinner and tabs on form
 *
 * @param   string  $output   code to output before form fields
 * @param   string  $form_type   type of the form
 * @param   string  $form_id   ID of the form
 *
 * @since   v.1.0.0
 *
 * @return  string
 */
function wppb_in_msf_before_form_fields( $output, $form_type, $form_id ) {
    if( ! is_null( $form_id ) ) {
        $break_points        = get_post_meta( $form_id, 'wppb_msf_break_points', true );
        $wppb_msf_options    = get_post_meta( $form_id, 'wppb_msf_post_options', true );
        $wppb_msf_options    = is_array( $wppb_msf_options ) ? $wppb_msf_options : 'not_found';
        $wppb_msf_tab_titles = get_post_meta( $form_id, 'wppb_msf_tab_titles', true );
        $wppb_msf_tab_titles = is_array( $wppb_msf_tab_titles ) ? $wppb_msf_tab_titles : 'not_found';
    } else {
        $break_points        = get_option( 'wppb_msf_break_points', 'not_found' );
        $wppb_msf_options    = get_option( 'wppb_msf_options', 'not_found' );
        $wppb_msf_tab_titles = get_option( 'wppb_msf_tab_titles', 'not_found' );

        if( $wppb_msf_options != 'not_found' ) {
            if( ( $wppb_msf_options['pb-default-register'] == 'no' && $form_type == 'register' ) || ( $wppb_msf_options['pb-default-edit-profile'] == 'no' && $form_type == 'edit_profile' ) ) {
                return $output;
            }
        }
    }

    if( $break_points != 'not_found' && ! empty( $break_points ) ) {
        $tabs_output = '';
        if( isset( $wppb_msf_options ) && $wppb_msf_options != 'not_found' && $wppb_msf_options['msf-tabs'] == 'yes' ) {
            $tabs_output .= '<div id="wppb-msf-tabs">';
            for( $i = 0; $i <= count( $break_points ); $i++ ) {
                if( isset( $wppb_msf_tab_titles ) && $wppb_msf_tab_titles != 'not_found' && isset( $wppb_msf_tab_titles[$i] ) ) {
                    $tab_title = $wppb_msf_tab_titles[$i];

                    // check for WPML translations
                    $tab_title = wppb_icl_t( 'plugin profile-builder-pro', 'msf_'. $form_type .'_step_' . $i . '_tab_title_translation', $tab_title );

                } else {
                    $tab_title = __( 'Step', 'profile-builder' ) .' '. ( $i + 1 );
                }

                $tabs_output .= '<input type="button" id="wppb-msf-tabs-'. $i .'" data-msf-disabled-check="'. ( $i != 0 && $form_type != 'edit_profile' ? 'yes' : 'no' ) .'" data-msf-step="'. $i .'" class="wppb-msf-tabs '. ( $i == 0 ? 'wppb-msf-active' : '' ) .'" value="'. $tab_title .'" '. ( $i != 0 && $form_type != 'edit_profile' ? 'disabled' : '' ) .'>';
            }
            $tabs_output .= '</div>';
        }

        $output = $tabs_output .'<div class="wppb-msf-spinner-container"><span class="icon-wppb-msf-spinner wppb-msf-spin"></span></div>'. $output;
    }

    return $output;
}
add_filter( 'wppb_before_form_fields', 'wppb_in_msf_before_form_fields', 10, 3 );

/**
 * Function that adds Break Points column in back-end (Manage Fields, Multiple Registration and Edit-Profile Forms)
 *
 * @since   v.1.0.0
 *
 * @param   string  $content    columns content added by filter
 * @param   string  $list       table content
 * @param   string  $meta       post meta
 *
 * @return  string
 */
function wppb_in_msf_column_content( $content, $list, $meta ) {
    if( isset( $_GET['wppb_rpf_repeater_meta_name'] ) ) {
        return $content;
    }

    if( defined( 'PROFILE_BUILDER_VERSION' ) && ( $meta == 'wppb_manage_fields' || $meta == 'wppb_rf_fields' || $meta == 'wppb_epf_fields' ) ) {
        $content .= '<td class="wck-msf"><span title="Add form Break Point (for Multi-Step Forms)" class="wppb-msf-break" style="display: none;"><b class="wppb-msf-add-sign">+</b></span></td>';
    }

    return $content;
}
add_filter( 'wck_add_content_after_columns', 'wppb_in_msf_column_content', 10, 3 );

/**
 * Function that saves a break point on a field
 *
 * @since   v.1.0.0
 */
function wppb_in_msf_save_break_points() {
    check_ajax_referer( 'wppb_msf_backend_nonce', 'wppb_msf_ajax_nonce' );

    if( isset( $_POST['wppb_msf_form_id'] ) ) {
        if ($_POST['wppb_msf_form_id'] === 'manage-fields') {
            $break_points = get_option('wppb_msf_break_points', 'not_found');
        } else {
            $break_points = get_post_meta( sanitize_text_field( $_POST['wppb_msf_form_id'] ), 'wppb_msf_break_points', true);
            $break_points = is_array($break_points) ? $break_points : 'not_found';
        }
    }

    if( isset( $_POST['wppb_msf_action'] ) && isset( $_POST['wppb_msf_field_id'] ) ) {
        if ($_POST['wppb_msf_action'] === 'add') {
            if ($break_points == 'not_found') {
                $break_points = array(absint($_POST['wppb_msf_field_id']) => absint($_POST['wppb_msf_field_id']));
            } else {
                $break_points[absint($_POST['wppb_msf_field_id'])] = absint($_POST['wppb_msf_field_id']);
            }
        } elseif ($_POST['wppb_msf_action'] === 'remove' && $break_points != 'not_found') {
            unset($break_points[absint($_POST['wppb_msf_field_id'])]);
        }
    }

    if( isset( $_POST['wppb_msf_form_id'] ) ){
        if( $_POST['wppb_msf_form_id'] === 'manage-fields' ) {
            update_option( 'wppb_msf_break_points', $break_points );
        } else {
            update_post_meta( absint( $_POST['wppb_msf_form_id'] ), 'wppb_msf_break_points', $break_points );
        }
    }

    die( 'option_updated' );
}
add_action( 'wp_ajax_wppb_msf_save_break_points', 'wppb_in_msf_save_break_points' );

/**
 * Function that deletes break points attached to deleted fields
 *
 * @param   string  $post_meta
 * @param   int     $post_id
 * @param   int     $element_id
 *
 * @since   v.1.0.0
 */
function wppb_in_msf_delete_break_points( $post_meta, $post_id, $element_id = NULL ) {
    if( isset( $element_id ) ) {
        if( isset( $post_meta ) && $post_meta == 'wppb_manage_fields' ) {
            $wppb_manage_fields = get_option( 'wppb_manage_fields', 'not_found' );
            $break_points = get_option( 'wppb_msf_break_points', 'not_found' );

            if( $wppb_manage_fields != 'not_found' && $break_points != 'not_found' ) {
                unset( $break_points[$wppb_manage_fields[$element_id]['id']] );
                update_option( 'wppb_msf_break_points', $break_points );
            }
        } elseif( isset( $post_meta ) && isset( $post_id ) ) {
            $wppb_form_fields = get_post_meta( $post_id, $post_meta, true );
            $break_points = get_post_meta( $post_id, 'wppb_msf_break_points', true );
            $break_points = is_array( $break_points ) ? $break_points : 'not_found';

            if( $break_points != 'not_found' ) {
                unset( $break_points[$wppb_form_fields[$element_id]['id']] );
                update_post_meta( $post_id, 'wppb_msf_break_points', $break_points );
            }
        }
    } elseif( isset( $post_id ) ) {
        delete_post_meta( $post_id, 'wppb_msf_break_points' );
    }
}
add_action( 'wck_before_remove_meta', 'wppb_in_msf_delete_break_points', 10 , 3 );
add_action( 'wppb_before_remove_all_fields', 'wppb_in_msf_delete_break_points', 10 , 2 );

/**
 * Function that checks database for break points attached to fields
 *
 * @since   v.1.0.0
 */
function wppb_in_msf_check_break_points() {
    if( !isset( $_POST['wppb_msf_form_id'] ) )
        die();

    if( $_POST['wppb_msf_form_id'] === 'manage-fields' ) {
        $break_points = get_option( 'wppb_msf_break_points', 'not_found' );
    } else {
        $break_points = get_post_meta( sanitize_text_field( $_POST['wppb_msf_form_id'] ), 'wppb_msf_break_points', true );
        $break_points = is_array( $break_points ) ? $break_points : 'not_found';
    }

    if( $_POST['wppb_msf_form_id'] === NULL ) {
        die( 'NULL' );
    } elseif( $break_points == 'not_found' ) {
        die( $break_points ); //phpcs:ignore
    } else {
        $break_points = array_map( 'absint', $break_points );
        die( json_encode( $break_points ) );
    }
}
add_action( 'wp_ajax_wppb_msf_check_break_points', 'wppb_in_msf_check_break_points' );
add_action( 'wp_ajax_nopriv_wppb_msf_check_break_points', 'wppb_in_msf_check_break_points' );

/**
 * Function that checks database for break points attached to fields after fields reorder
 *
 * @param   int  $id   ID of the form
 *
 * @since   v.1.0.0
 */
function wppb_in_msf_after_reorder_check_break_points( $id ) {
    if( empty( $id ) ) {
        $break_points = get_option( 'wppb_msf_break_points', 'not_found' );
        $id = '\'manage-fields\'';
    } else {
        $break_points = get_post_meta( $id, 'wppb_msf_break_points', true );
    }

    if( $break_points != 'not_found' && ! empty( $break_points ) ) {
        $break_points = array_map( 'absint', $break_points );
        echo '<script>wppb_msf_add_break_points( '. json_encode( $break_points ) .' )</script>';
    }

    echo '<script>wppb_msf_break_points_buttons( '. $id .' )</script>'; // phpcs:ignore
}
add_action( 'wck_refresh_list_wppb_manage_fields', 'wppb_in_msf_after_reorder_check_break_points' );
add_action( 'wck_refresh_entry_wppb_manage_fields', 'wppb_in_msf_after_reorder_check_break_points' );
add_action( 'wck_refresh_list_wppb_rf_fields', 'wppb_in_msf_after_reorder_check_break_points' );
add_action( 'wck_refresh_entry_wppb_rf_fields', 'wppb_in_msf_after_reorder_check_break_points' );
add_action( 'wck_refresh_list_wppb_epf_fields', 'wppb_in_msf_after_reorder_check_break_points' );
add_action( 'wck_refresh_entry_wppb_epf_fields', 'wppb_in_msf_after_reorder_check_break_points' );

/**
 * Function that checks and returns tab titles
 *
 * @since   v.1.0.0
 */
function wppb_in_msf_check_tab_titles() {
    if( isset( $_POST['wppb_msf_form_id'] ) ) {
        if ($_POST['wppb_msf_form_id'] == 'manage-fields') {
            $tab_titles = get_option('wppb_msf_tab_titles', 'not_found');
        } else {
            $tab_titles = get_post_meta( sanitize_text_field( $_POST['wppb_msf_form_id'] ), 'wppb_msf_tab_titles', true);
            $tab_titles = is_array($tab_titles) ? $tab_titles : 'not_found';
        }
    }

    if( $tab_titles == 'not_found' ) {
        die( $tab_titles ); //phpcs:ignore
    } else {
        $tab_titles = array_map( 'esc_attr', $tab_titles );
        die( json_encode( $tab_titles ) );
    }
}
add_action( 'wp_ajax_wppb_msf_check_tab_titles', 'wppb_in_msf_check_tab_titles' );
add_action( 'wp_ajax_nopriv_wppb_msf_check_tab_titles', 'wppb_in_msf_check_tab_titles' );

/**
 * Function that checks and returns errors for fields on Next step link click
 *
 * @since   v.1.0.0
 */
function wppb_in_msf_check_required_fields() {
    check_ajax_referer( 'wppb_msf_frontend_nonce', 'wppb_msf_ajax_nonce' );

    include_once( WPPB_PLUGIN_DIR .'/front-end/default-fields/default-fields.php' );
    wppb_include_extra_fields_files();

    $wppb_manage_fields = get_option( 'wppb_manage_fields', 'not_found' );

    $output_field_errors = array();

    // check request data for correct format
    $request_data = array();
    foreach( $_POST['request_data'] as $key => $value ) { //phpcs:ignore
        $key = sanitize_text_field( $key );
        $request_data[wppb_handle_meta_name( $key )] = $value;
    }

    foreach( $_POST['wppb_msf_fields'] as $stepName => $fieldList ) {//phpcs:ignore
        foreach ($fieldList as $id => $value) {
            $field = array();

            // return field name from field class
            $field_name = explode(' ', $value['class']);
            $field_name = substr($field_name[1], 5);
            $field_name = esc_attr($field_name);

            // return field title by removing required sign *
            if (isset($value['title'])) {
                $field['field-title'] = str_replace('*', '', $value['title']);
                $field['field-title'] = sanitize_text_field($field['field-title']);
            }

            // return the id of the field from the field li (wppb-form-element-XX)
            if (isset($id)) {
                $field_id = intval(substr($id, 18));
            }

            // skip username validation on edit profile
            if ($field_name == 'default-username' && isset($_POST['form_type']) && $_POST['form_type'] == 'edit_profile')
                continue;

            // check for fields errors for woocommerce billing fields
            if ($field_name == 'woocommerce-customer-billing-address') {
                if (function_exists('wppb_in_woo_billing_fields_array') && function_exists('wppb_in_check_woo_individual_fields_val')) {
                    $field['field'] = 'WooCommerce Customer Billing Address';

                    $billing_fields = wppb_in_woo_billing_fields_array();

                    if (!empty($request_data['billing_country']) && class_exists('WC_Countries')) {
                        $WC_Countries_Obj = new WC_Countries();
                        $locale = $WC_Countries_Obj->get_country_locale();

                        if (isset($locale[$request_data['billing_country']]['state']['required']) && ($locale[$request_data['billing_country']]['state']['required'] == false)) {
                            if (is_array($billing_fields) && isset($billing_fields['billing_state'])) {
                                $billing_fields['billing_state']['required'] = 'No';
                            }
                        }
                    }

                    if (isset($value['fields'])) {
                        foreach ($value['fields'] as $key => $woo_field_label) {
                            $key = sanitize_text_field($key);

                            $woo_error_for_field = wppb_in_check_woo_individual_fields_val('', $billing_fields[$key], $key, $request_data, sanitize_text_field($_POST['form_type']));

                            if (!empty($woo_error_for_field)) {
                                $output_field_errors[$stepName][$key]['field'] = $key;
                                $output_field_errors[$stepName][$key]['error'] = '<span class="wppb-form-error">' . $woo_error_for_field . '</span>';
                                $output_field_errors[$stepName][$key]['type'] = 'woocommerce';
                            }
                        }
                    }
                }
            }

            // check for fields errors for woocommerce shipping fields
            if ($field_name == 'woocommerce-customer-shipping-address') {
                if (function_exists('wppb_in_woo_shipping_fields_array') && function_exists('wppb_in_check_woo_individual_fields_val')) {
                    $field['field'] = 'WooCommerce Customer Shipping Address';

                    $shipping_fields = wppb_in_woo_shipping_fields_array();

                    if (!empty($request_data['shipping_country']) && class_exists('WC_Countries')) {
                        $WC_Countries_Obj = new WC_Countries();
                        $locale = $WC_Countries_Obj->get_country_locale();

                        if (isset($locale[$request_data['shipping_country']]['state']['required']) && ($locale[$request_data['shipping_country']]['state']['required'] == false)) {
                            if (is_array($shipping_fields) && isset($shipping_fields['shipping_state'])) {
                                $shipping_fields['shipping_state']['required'] = 'No';
                            }
                        }
                    }

                    if (isset($value['fields'])) {
                        foreach ($value['fields'] as $key => $woo_field_label) {
                            $key = sanitize_text_field($key);

                            $woo_error_for_field = wppb_in_check_woo_individual_fields_val('', $shipping_fields[$key], $key, $request_data, sanitize_text_field($_POST['form_type']));

                            if (!empty($woo_error_for_field)) {
                                $output_field_errors[$stepName][$key]['field'] = $key;
                                $output_field_errors[$stepName][$key]['error'] = '<span class="wppb-form-error">' . $woo_error_for_field . '</span>';
                                $output_field_errors[$stepName][$key]['type'] = 'woocommerce';
                            }
                        }
                    }
                }
            }

            // add repeater fields to fields array
            if (isset($value['extra_groups_count'])) {
                $wppb_manage_fields = apply_filters('wppb_form_fields', $wppb_manage_fields, array('context' => 'multi_step_forms', 'extra_groups_count' => esc_attr($value['extra_groups_count']), 'global_request' => $request_data, 'form_type' => sanitize_text_field($_POST['form_type'])));
            }

            // search for fields in fields array by meta-name or id (if field does not have a mata-name)
            if (!empty($value['meta-name']) && $value['meta-name'] != 'passw1' && $value['meta-name'] != 'passw2' && wppb_in_msf_get_field_options($value['meta-name'], $wppb_manage_fields) !== false) {
                $field = wppb_in_msf_get_field_options($value['meta-name'], $wppb_manage_fields);
            } elseif (!empty($field_id) && wppb_in_msf_get_field_options($field_id, $wppb_manage_fields, 'id') !== false
                && $field_name != 'woocommerce-customer-billing-address' && $field_name != 'woocommerce-customer-shipping-address') {

                $field = wppb_in_msf_get_field_options($field_id, $wppb_manage_fields, 'id');
            }

            // check for fields errors
            if ($field_name != 'woocommerce-customer-billing-address' && $field_name != 'woocommerce-customer-shipping-address') {
                $error_for_field = apply_filters('wppb_check_form_field_' . $field_name, '', $field, $request_data, sanitize_text_field($_POST['form_type']));
            }

            // construct the array with fields errors
            if (!empty($value['meta-name']) && !empty($error_for_field)) {
                $output_field_errors[$stepName][esc_attr($value['meta-name'])]['field'] = $field_name;
                $output_field_errors[$stepName][esc_attr($value['meta-name'])]['error'] = '<span class="wppb-form-error">' . wp_kses_post($error_for_field) . '</span>';
            }
        }
    }

    $output_field_errors = apply_filters( 'wppb_output_field_errors_filter', $output_field_errors );

    if( ! empty( $output_field_errors ) ) {
        die( json_encode( $output_field_errors ) );
    } else {
        die( 'no_errors' );
    }
}
add_action( 'wp_ajax_wppb_msf_check_required_fields', 'wppb_in_msf_check_required_fields' );
add_action( 'wp_ajax_nopriv_wppb_msf_check_required_fields', 'wppb_in_msf_check_required_fields' );

/* Function that search in multidimensional arrays */
function wppb_in_msf_get_field_options( $needle, $haystack, $type = 'meta-name' ) {

    foreach( $haystack as $item ) {
        if( is_array( $item ) && isset( $item[$type] ) && $item[$type] == $needle ) {
            return $item;
        }
    }

    return false;

}
