<?php

if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Generate the Form Design Selector to PB -> General Settings
 *
 */
function wppb_render_forms_design_selector() {

    $form_designs_data = wppb_get_form_designs_data();

    $output = '<div id="wppb-forms-design-browser">';

    foreach ( $form_designs_data as $form_design ) {

        if ($form_design['status'] == 'active') {
            $status = ' active';
            $title = '<strong>Active: </strong> ' . $form_design['name'];
        } else {
            $status = '';
            $title = $form_design['name'];
        }

        if ( $form_design['id'] != 'form-style-default' )
            $preview_button = '<div class="wppb-forms-design-preview" id="'. $form_design['id'] .'-info">Preview</div>';
        else $preview_button = '';

        $output .= '
                <div class="wppb-forms-design'. $status .'" id="'. $form_design['id'] .'">
                   <div class="wppb-forms-design-screenshot">
                      <img src="' . $form_design['images']['main'] . '" alt="Form Design">
                      '. $preview_button .'
                   </div>
                   <div class="wppb-forms-design-details">
                      <div class="wppb-forms-design-title">
                         <h2>'. $title .'</h2>
                      </div>
                      <div class="wppb-forms-design-activate">
                         <button 
                            type="button" 
                            class="button activate button-small" 
                            id="activate-'. $form_design['id'] .'"  
                            data-theme-id="'. $form_design['id'] .'">
                         Select
                         </button>
                      </div>
                   </div>
                </div>
        ';

        $img_count = 0;
        $image_list = '';
        foreach ( $form_design['images'] as $image ) {
            $img_count++;
            $active_img = ( $img_count == 1 ) ? ' active' : '';
            $image_list .= '<img class="wppb-forms-design-preview-image'. $active_img .'" src="'. $image .'">';
        }

        if ( $img_count > 1 ) {
            $previous_button = '<div class="wppb-slideshow-button wppb-forms-design-sildeshow-previous disabled" data-theme-id="'. $form_design['id'] .'" data-slideshow-direction="previous"> < </div>';
            $next_button = '<div class="wppb-slideshow-button wppb-forms-design-sildeshow-next" data-theme-id="'. $form_design['id'] .'" data-slideshow-direction="next"> > </div>';
            $justify_content = 'space-between';
        }
        else {
            $previous_button = $next_button = '';
            $justify_content = 'center';
        }

        $output .= '<div id="modal-'. $form_design['id'] .'" class="wppb-forms-design-modal" title="'. $form_design['name'] .'">
                        <div class="wppb-forms-design-modal-slideshow" style="justify-content: '. $justify_content .'">
                            '. $previous_button .'
                            <div class="wppb-forms-design-modal-images">
                                '. $image_list .'
                            </div>
                            '. $next_button .'
                        </div>
                    </div>';

    }

    $output .= '</div>';
    $output .= '<p class="wppb-form-desig-description description">'. sprintf( esc_html__( 'To display the %1$sForm Labels%2$s as depicted in the Preview images, activate the "Enable Placeholder Labels" Option in %3$sAdvanced Settings%4$s.', 'profile-builder' ),'<strong>', '</strong>', '<a href="'. get_site_url() .'/wp-admin/admin.php?page=profile-builder-toolbox-settings">', '</a>' ) .'</p>';

    return $output;
}


/**
 * Function that returns the Form Designs Data
 *
 */
function wppb_get_form_designs_data() {
    $active_design = wppb_get_active_form_design();

    $form_designs = array(
        array(
            'id' => 'form-style-default',
            'name' => 'Default',
            'status' => $active_design == 'form-style-default' ? 'active' : '',
            'images' => array(
                'main' => WPPB_PLUGIN_URL.'assets/images/pb-default-forms.jpg',
            ),
        ),
        array(
            'id' => 'form-style-1',
            'name' => 'Style 1',
            'status' => $active_design == 'form-style-1' ? 'active' : '',
            'images' => array(
                'main' => WPPB_PLUGIN_URL.'assets/images/style1-slide1.jpg',
                'slide1' => WPPB_PLUGIN_URL.'assets/images/style1-slide2.jpg',
                'slide2' => WPPB_PLUGIN_URL.'assets/images/style1-slide3.jpg',
            ),
        ),
        array(
            'id' => 'form-style-2',
            'name' => 'Style 2',
            'status' => $active_design == 'form-style-2' ? 'active' : '',
            'images' => array(
                'main' => WPPB_PLUGIN_URL.'assets/images/style2-slide1.jpg',
                'slide1' => WPPB_PLUGIN_URL.'assets/images/style2-slide2.jpg',
                'slide2' => WPPB_PLUGIN_URL.'assets/images/style2-slide3.jpg',
            ),
        ),
        array(
            'id' => 'form-style-3',
            'name' => 'Style 3',
            'status' => $active_design == 'form-style-3' ? 'active' : '',
            'images' => array(
                'main' => WPPB_PLUGIN_URL.'assets/images/style3-slide1.jpg',
                'slide1' => WPPB_PLUGIN_URL.'assets/images/style3-slide2.jpg',
                'slide2' => WPPB_PLUGIN_URL.'assets/images/style3-slide3.jpg',
            ),
        )
    );

    return $form_designs;
}


/**
 * Add Form Design CSS classes for easier styling
 *
 * --> Profile Builder & WooCommerce Fields
 */
function wppb_add_form_design_classes( $classes, $field ){
    $text_fields = array( 'Default - Username', 'Default - First Name', 'Default - Last Name', 'Default - Nickname', 'Default - E-mail', 'Default - Website', 'Default - Password', 'Default - Repeat Password', 'Default - Biographical Info',
        'Input', 'TextArea', 'Textarea', 'Number', 'Phone', 'Datepicker', 'Colorpicker', 'Validation', 'Email', 'Email Confirmation', 'URL', 'Language',
        // Login & Recover fields
        'user_login', 'user_pass', 'username_email', 'passw1', 'passw2',
        // Two-Factor Authentication fields
        'wppb_auth_description', 'wppb_auth_secret', 'wppb_auth_passw',
        // WooCommerce fields
        'billing_first_name', 'billing_last_name', 'billing_company', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_postcode', 'billing_email', 'billing_phone',
        'shipping_first_name', 'shipping_last_name', 'shipping_company', 'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_postcode');

    $select_fields = array( 'Select', 'Select2', 'Select (Country)', 'Select (Timezone)', 'Select (Currency)', 'Select (CPT)', 'Select (User Role)', 'Select2 (Multiple)', 'Default - Display name publicly as',
        // WooCommerce fields
        'billing_country', 'billing_state', 'shipping_country', 'shipping_state' );

    $forms_settings = get_option( 'wppb_toolbox_forms_settings' );

    if ( $forms_settings['placeholder-labels'] == 'yes' )
        $label_position_class = ' label-inside';
    else $label_position_class = ' label-outside';

    $needle = is_array( $field ) ? $field['field'] : $field;

    if ( in_array( $needle, $text_fields ) )
        $classes .= ' wppb-form-text-field' . $label_position_class;
    elseif ( in_array( $needle, $select_fields ) )
        $classes .= ' wppb-form-select-field' . $label_position_class;
    elseif ( $needle == 'Subscription Plans' )
        $classes .= $label_position_class;


    return $classes;
}
// only add Filters if the active Form Design is different from the Default
if ( wppb_get_active_form_design() != 'form-style-default'  ) {
    add_filter( 'wppb_field_css_class', 'wppb_add_form_design_classes', 20, 2);
    add_filter( 'wppb_woo_field_extra_css_class', 'wppb_add_form_design_classes', 20, 2);
    add_filter( 'wppb_login_field_extra_css_class', 'wppb_add_form_design_classes', 20, 2);
    add_filter( 'wppb_recover_field_extra_css_class', 'wppb_add_form_design_classes', 20, 2);
    add_filter( 'wppb_2fa_field_extra_css_class', 'wppb_add_form_design_classes', 20, 2);
}


/**
 * Change WooCommerce Billing/Shipping Fields Label
 *
 */
function wppb_change_woo_field_label( $woo_fields ) {
    if ( isset( $woo_fields['billing_address_2'] ) ) {
        if  ( empty( $woo_fields['billing_address_2']['label'] ) )
            $woo_fields['billing_address_2']['label'] = esc_html__('Address Line 2', 'profile-builder');

        if  ( empty( $woo_fields['billing_address_1']['label'] ) || $woo_fields['billing_address_1']['label'] == "Address" )
            $woo_fields['billing_address_1']['label'] = esc_html__('Address Line 1', 'profile-builder');
    }

    if ( isset( $woo_fields['shipping_address_2'] ) ) {
        if  ( empty( $woo_fields['shipping_address_2']['label'] ) )
            $woo_fields['shipping_address_2']['label'] = esc_html__('Address Line 2', 'profile-builder');

        if  ( empty( $woo_fields['shipping_address_1']['label'] ) || $woo_fields['shipping_address_1']['label'] == "Address" )
            $woo_fields['shipping_address_1']['label'] = esc_html__('Address Line 1', 'profile-builder');
    }

    return $woo_fields;

}
// only add Filters if active Form Design is different from the Default
if ( wppb_get_active_form_design() != 'form-style-default'  ) {
    add_filter('wppb_woo_billing_fields', 'wppb_change_woo_field_label');
    add_filter('wppb_woo_shipping_fields', 'wppb_change_woo_field_label');
}


/**
 * Add Form Design CSS classes for easier styling
 *
 * --> Paid Member Subscriptions Fields
 */
function wppb_pms_add_form_design_classes ( $fields ) {
    $forms_settings = get_option( 'wppb_toolbox_forms_settings' );

    if ( $forms_settings['placeholder-labels'] == 'yes' )
        $label_position_class = ' label-inside';
    else $label_position_class = ' label-outside';

    foreach ( $fields as $field ) {
        if ( $field['type'] == 'text' )
            $fields[$field['name']]['wrapper_class'] .= ' wppb-form-text-field' . $label_position_class;
        elseif ( $field['type'] == 'select' )
            $fields[$field['name']]['wrapper_class'] .= ' wppb-form-select-field' . $label_position_class;
        elseif ( $field['type'] == 'select_state' )
            $fields[$field['name']]['wrapper_class'] .= ' wppb-form-select-field' . $label_position_class;

    }

    return $fields;
}
// only add Filters if the active Form Design is different from the Default
if ( wppb_get_active_form_design() != 'form-style-default'  ) {
    add_filter('pms_inv_get_invoice_fields', 'wppb_pms_add_form_design_classes' );
    add_filter('pms_get_tax_extra_fields', 'wppb_pms_add_form_design_classes' );
}


/**
 * Function returns the Form Designs active Style
 *
 */
function wppb_get_active_form_design() {
    $wppb_generalSettings = get_option( 'wppb_general_settings' );

    if ( empty( $wppb_generalSettings['formsDesign'] ) || $wppb_generalSettings['formsDesign'] == 'form_style_default' || $wppb_generalSettings['formsDesign'] == 'forms_style_default')
        $active_design = 'form-style-default';
    else $active_design = $wppb_generalSettings['formsDesign'];

    return $active_design;
}


/**
 * Function adds Form Notifications custom Icon and Title
 *
 */
function wppb_add_form_alert_title ( $form_content, $message_container, $icon_type, $text, $active_design ) {
    if ( $active_design == 'form-style-3' )
        $icon = '<span class="wppb-alert-icon-container"><img src="'. WPPB_PAID_PLUGIN_URL.'features/form-designs/icons/form-design-'. $icon_type .'-icon.png' .'" alt="'. $icon_type .'"></span>';
    else $icon = '<img src="'. WPPB_PAID_PLUGIN_URL.'features/form-designs/icons/form-design-'. $icon_type .'-icon.png' .'" alt="'. $icon_type .'">';

    $message_title = '<span class="wppb-alert-title">'. $icon . $text .'</span>';

    return str_replace( $message_container, $message_container . $message_title, $form_content );
}


/**
 * Function that adds Form Design Styles from corresponding files
 *
 */
function wppb_add_form_styling( $form_content, $style_file, $form_title, $form_type ) {

    $active_design = wppb_get_active_form_design();

    if ( $active_design == 'form-style-default'  )
        return $form_content;

    // maybe add Form Notification Styles
    $form_content = wppb_form_notification_styling( $form_content );

    $file_path = WPPB_PAID_PLUGIN_DIR . '/features/form-designs/css/'. $active_design .'/'. $style_file;

    if ( $form_type == 'user_login' && strpos( $form_content, '<a class="login-register"' ) !== false ) {
        $extra_login_form_text = '<span class="wppb-register-message">' . __('Don’t have an account?', 'profile-builder') . '</span>';
        $form_content = str_replace( '<a class="login-register"', $extra_login_form_text . '<a class="login-register"', $form_content );
    }

    if ( $active_design == 'form-style-2' && $form_type == 'user_login' ) {
        $welcome_message = esc_html__('WELCOME BACK', 'profile-builder');
        $form_subtitle =  esc_html__('Please enter your details.', 'profile-builder');
    }

    if ( $active_design == 'form-style-2' && $form_type == 'edit_profile' ) {
        $welcome_message = esc_html__('Manage your profile information', 'profile-builder');
    }

    $edited_content = '<div id="wppb-'. $active_design .'-wrapper">'; // the wrapper helps when overwriting form styles (more specific targeting)
    $edited_content .= !empty( $welcome_message ) ? '<p class="wppb-form-welcome">' . $welcome_message . '</p>' : '';
    $edited_content .= '<h2 class="wppb-form-title">'. $form_title .'</h2>';
    $edited_content .= !empty( $form_subtitle ) ? '<p class="wppb-form-subtitle">' . $form_subtitle . '</p>' : '';
    $edited_content .= $form_content;
    $edited_content .= '<style>';
    $edited_content .= ( file_exists( $file_path ) ) ? file_get_contents( $file_path ) : '';
    $edited_content .= '</style>';
    $edited_content .= '</div>';

    return $edited_content;
}


/**
 * Function that adds Form Notification Styles
 *
 */
function wppb_form_notification_styling( $content ) {
    $active_design = wppb_get_active_form_design();

    if ( $active_design == 'form-style-default'  )
        return $content;

    $possible_classes_and_text = array(
        'wppb-error'        => esc_html__( 'ERROR!', 'profile-builder' ),
        'error'             => esc_html__( 'ERROR!', 'profile-builder' ),
        'wppb-success'      => esc_html__( 'SUCCESS!', 'profile-builder' ),
        'alert'             => esc_html__( 'NOTE!', 'profile-builder' ),
        'wppb-alert'        => esc_html__( 'NOTE!', 'profile-builder' ),
        'warning'           => esc_html__( 'WARNING!', 'profile-builder' ),
        'wppb-warning'      => esc_html__( 'WARNING!', 'profile-builder' ),
        'wppb-epaa-warning' => esc_html__( 'WARNING!', 'profile-builder' )
    );

    foreach ( $possible_classes_and_text as $message_class => $text ) {

        if ( $message_class == 'wppb-epaa-warning' )
            $icon_type = 'warning';
        else $icon_type = str_replace( 'wppb-','', $message_class );

        if ( strpos( $content, $message_class ) !== false ) {

            if ( strpos( $content, '<p id="wppb_form_general_message" class="'. $message_class .'">' ) !== false ) {
                $message_container = '<p id="wppb_form_general_message" class="' . $message_class . '">';
                $content = wppb_add_form_alert_title( $content, $message_container, $icon_type, $text, $active_design  );
            }

            if ( $message_class == 'wppb-success' && strpos( $content, '<p class="alert wppb-success" id="wppb_form_general_message">' ) !== false && strpos( $content, '<span class="wppb-alert-title">' ) === false ) {
                $message_container = '<p class="alert wppb-success" id="wppb_form_general_message">';
                $content = wppb_add_form_alert_title( $content, $message_container, $icon_type, $text, $active_design  );
            }

            if ( $message_class == 'alert' && strpos( $content, '<p class="alert" id="wppb_register_pre_form_message">' ) !== false && strpos( $content, '<span class="wppb-alert-title">' ) === false ) {
                $message_container = '<p class="alert" id="wppb_register_pre_form_message">';
                $content = wppb_add_form_alert_title( $content, $message_container, $icon_type, $text, $active_design  );
            }

            if ( $message_class == 'warning' && strpos( $content, '<p class="warning" id="wppb_edit_profile_user_not_logged_in_message">' ) !== false && strpos( $content, '<span class="wppb-alert-title">' ) === false ) {
                $message_container = '<p class="warning" id="wppb_edit_profile_user_not_logged_in_message">';
                $content = wppb_add_form_alert_title( $content, $message_container, $icon_type, $text, $active_design  );
            }

            if ( strpos( $content, '<p class="pms-email-confirmation-payment-message wppb-success">' ) !== false ) {
                $content = str_replace( '<p class="pms-email-confirmation-payment-message wppb-success">','<p class="pms-email-confirmation-payment-message wppb-warning">', $content );
                $message_container = '<p class="pms-email-confirmation-payment-message wppb-warning">';
                $custom_icon_type = 'warning';
                $custom_text = $possible_classes_and_text['wppb-warning'];
                $content = wppb_add_form_alert_title( $content, $message_container, $custom_icon_type, $custom_text, $active_design  );
            }

            if ( strpos( $content, '<p class="' . $message_class . '">' ) !== false ) {
                $message_container = '<p class="' . $message_class . '">';
                $content = wppb_add_form_alert_title( $content, $message_container, $icon_type, $text, $active_design  );
            }

        }

        if ( $message_class == 'wppb-warning' && strpos( $content, '<div class="pms-warning-message-wrapper">' ) !== false ) {
            $message_container = '<div class="pms-warning-message-wrapper">';
            $content = wppb_add_form_alert_title( $content, $message_container, $icon_type, $text, $active_design  );
        }


        if ( $message_class == 'wppb-alert' && strpos( $content, '<p class="wppb-front-end-logout">' ) !== false ) {
            $message_container = '<p class="wppb-front-end-logout">';
            $content = wppb_add_form_alert_title($content, $message_container, $icon_type, $text, $active_design);
        }

    }

    return $content;
}
add_filter('wppb_success_email_confirmation', 'wppb_form_notification_styling', 100 );
add_filter('wppb_email_confirmation_with_admin_approval', 'wppb_form_notification_styling', 100 );
add_filter('wppb_register_activate_user_error_message1', 'wppb_form_notification_styling', 100 );
add_filter('wppb_register_activate_user_error_message2', 'wppb_form_notification_styling', 100 );
add_filter('wppb_register_activate_user_error_message4', 'wppb_form_notification_styling', 100 );
add_filter('wppb_register_activate_user_error_message5', 'wppb_form_notification_styling', 100 );
add_filter('wppb_form_message_tpl_start', 'wppb_form_notification_styling', 100 );
add_filter('pms_pb_subscription_plans_field_payment_attention_message', 'wppb_form_notification_styling', 100 );
add_filter('wppb_register_pre_form_message', 'wppb_form_notification_styling', 100 );
add_filter('wppb_login_message', 'wppb_form_notification_styling', 100 );
add_filter('wppb_logout_message', 'wppb_form_notification_styling', 100 );
add_filter('wppb_edit_profile_user_not_logged_in_message', 'wppb_form_notification_styling', 100 );


function wppb_form_text_notification_styling( $content )
{
    $active_design = wppb_get_active_form_design();

    if ( $active_design == 'form-style-default' || empty( $content )  )
        return $content;

    $text = esc_html__( 'NOTE!', 'profile-builder' );
    $icon = '<span class="wppb-alert-icon-container"><img src="'. WPPB_PAID_PLUGIN_URL.'features/form-designs/icons/form-design-alert-icon.png' .'" alt="warning"></span>';
    $message_title = '<span class="wppb-alert-title">'. $icon . $text .'</span>';

    $edited_content = '<p class="wppb-alert">';
    $edited_content .= $message_title;
    $edited_content .= $content;
    $edited_content .= '</p>';

    return $edited_content;
}

add_filter('wppb_recover_password_already_logged_in', 'wppb_form_text_notification_styling', 100 );


/**
 * Maybe add styling for the Forms if any Form Design is selected
 *
 */

// Login Form
function wppb_login_form_styling( $form_content, $args ) {
    $form_title = esc_html__( 'Log in', 'profile-builder' );
    $form_content = wppb_add_form_styling( $form_content, 'login-form-style.css', $form_title, 'user_login' );

    return $form_content;
}
add_filter( 'wppb_login_form_before_content_output', 'wppb_login_form_styling', 10, 2 );

// Recover Password Form
function wppb_recover_password_form_styling( $form_content ) {
    $form_title = esc_html__( 'Recover Password', 'profile-builder' );
    $form_content = wppb_add_form_styling( $form_content, 'recover-password-form-style.css', $form_title, 'recover_password' );

    return $form_content;
}
add_filter( 'wppb_recover_password_before_content_output', 'wppb_recover_password_form_styling' );

// Register Form
function wppb_register_form_styling( $form_content ) {
    $form_title = esc_html__( 'Create a new account', 'profile-builder' );
    $form_content = wppb_add_form_styling( $form_content, 'register-form-style.css', $form_title, 'register_account' );

    return $form_content;
}
add_filter( 'wppb_register_form_content', 'wppb_register_form_styling' );

// Edit Profile Form
function wppb_edit_profile_form_styling( $form_content ) {
    $form_title = esc_html__( 'Edit account', 'profile-builder' );
    $form_content =  wppb_add_form_styling( $form_content, 'edit-profile-form-style.css', $form_title, 'edit_profile' );

    return $form_content;
}
add_filter( 'wppb_edit_profile_form_content', 'wppb_edit_profile_form_styling' );

// PMS Email Confirmation Register Form
function wppb_pms_register_form_styling( $form_content ) {
    if ( strpos( $form_content, 'pms-ec-register-form' ) !== false ) {

        if ( strpos( $form_content, '<div id="pms-subscription-plans-discount">' ) !== false ) {
            $forms_settings = get_option( 'wppb_toolbox_forms_settings' );
            if ( $forms_settings['placeholder-labels'] == 'yes' )
                $label_position_class = 'label-inside';
            else $label_position_class = 'label-outside';

            $element = '<div id="pms-subscription-plans-discount">';
            $edited_element = '<div id="pms-subscription-plans-discount" class="'. $label_position_class .'">';

            $form_content = str_replace( $element, $edited_element, $form_content );
        }

        $form_title = esc_html__( 'Subscription Payment', 'profile-builder' );
        $form_content =  wppb_add_form_styling( $form_content, 'pms-register-form-style.css', $form_title, 'pms_register_account' );
    }

    return $form_content;
}
add_filter( 'wppb_register_activate_user_error_message2', 'wppb_pms_register_form_styling', 90 );


/**
 * Load Form Design Feature Scripts and Styles
 *
 */

function wppb_enqueue_form_design_styles() {
    $active_design = wppb_get_active_form_design();

    if ( $active_design == 'form-style-default'  )
        return;

    wp_register_style( 'wppb_form_designs_general_style', WPPB_PAID_PLUGIN_URL.'features/form-designs/css/'. $active_design .'/form-design-general-style.css', array(),PROFILE_BUILDER_VERSION );
    wp_enqueue_style( 'wppb_form_designs_general_style' );

    wp_register_style( 'wppb_register_success_notification_style', WPPB_PAID_PLUGIN_URL.'features/form-designs/css/'. $active_design .'/extra-form-notifications-style.css', array(),PROFILE_BUILDER_VERSION );
    wp_enqueue_style( 'wppb_register_success_notification_style' );
}
add_action('wp_enqueue_scripts' , 'wppb_enqueue_form_design_styles');
add_action('elementor/editor/after_enqueue_styles' , 'wppb_enqueue_form_design_styles');

function wppb_enqueue_form_design_scripts() {
    $active_design = wppb_get_active_form_design();

    if ( $active_design == 'form-style-default'  )
        return;

    if ( $active_design == 'form-style-3' )
        wp_enqueue_script( 'wppb_form_designs_style3_js', WPPB_PAID_PLUGIN_URL.'/features/form-designs/js/form-designs-s3-front-end.js', array( 'jquery' ), PROFILE_BUILDER_VERSION );
    else wp_enqueue_script( 'wppb_form_designs_js', WPPB_PAID_PLUGIN_URL.'/features/form-designs/js/form-designs-front-end.js', array( 'jquery' ), PROFILE_BUILDER_VERSION );
}
add_action('wp_enqueue_scripts' , 'wppb_enqueue_form_design_scripts');
add_action('elementor/editor/after_enqueue_scripts' , 'wppb_enqueue_form_design_scripts');