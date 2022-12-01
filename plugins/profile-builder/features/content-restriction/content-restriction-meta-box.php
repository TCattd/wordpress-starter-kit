<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function wppb_content_restriction_initialize_meta_box() {

    add_action( 'add_meta_boxes', 'wppb_content_restriction_add_meta_box' );

}
add_action( 'init', 'wppb_content_restriction_initialize_meta_box', 999 );

function wppb_content_restriction_add_meta_box() {

    $post_types = get_post_types( array( 'public' => true ) );

    if( ! empty( $post_types ) ) {
        foreach( $post_types as $post_type ) {
            add_meta_box(
                'wppb_post_content_restriction',
                __( 'Profile Builder Content Restriction', 'profile-builder' ),
                'wppb_content_restriction_meta_box_output',
                $post_type,
                'normal'
            );
        }
    }

}

function wppb_content_restriction_meta_box_output( $post ) {

    ?>
    <div class="wppb-meta-box-fields-wrapper">
        <h4><?php echo esc_html__( 'Display Options', 'profile-builder' ); ?></h4>

        <div class="wppb-meta-box-field-wrapper">
            <?php
            $wppb_content_restrict_types = apply_filters( 'wppb_single_post_content_restrict_types', array( 'message' => __( 'Message', 'profile-builder' ), 'redirect' => __( 'Redirect', 'profile-builder' ) ) );

            $wppb_content_restrict_type = get_post_meta( $post->ID, 'wppb-content-restrict-type', true );
            ?>

            <label class="wppb-meta-box-field-label"><?php esc_html_e( 'Type of Restriction', 'profile-builder' ); ?></label>

            <label class="wppb-meta-box-checkbox-label" for="wppb-content-restrict-type-default">
                <input type="radio" id="wppb-content-restrict-type-default" value="default" <?php if( empty( $wppb_content_restrict_type ) || $wppb_content_restrict_type == 'default' ) echo 'checked="checked"'; ?> name="wppb-content-restrict-type">
                <?php echo esc_html__( 'Settings Default', 'profile-builder' ); ?>
            </label>

            <?php foreach( $wppb_content_restrict_types as $type_slug => $type_label ) { ?>
                <label class="wppb-meta-box-checkbox-label" for="wppb-content-restrict-type-<?php echo esc_attr( $type_slug ); ?>">
                    <input type="radio" id="wppb-content-restrict-type-<?php echo esc_attr( $type_slug ); ?>" value="<?php echo esc_attr( $type_slug ); ?>" <?php if( $wppb_content_restrict_type == $type_slug ) echo 'checked="checked"'; ?> name="wppb-content-restrict-type">
                    <?php echo esc_html( $type_label ); ?>
                </label>
            <?php } ?>
        </div>

        <div class="wppb-meta-box-field-wrapper">
            <label class="wppb-meta-box-field-label"><?php esc_html_e( 'Display For', 'profile-builder' ); ?></label>

            <?php
            $wppb_roles = get_editable_roles();
            $wppb_user_status = get_post_meta( $post->ID, 'wppb-content-restrict-user-status', true );
            $wppb_selected_roles = get_post_meta( $post->ID, 'wppb-content-restrict-user-role' );
            ?>

            <label class="wppb-meta-box-checkbox-label" for="wppb-content-restrict-user-status">
                <input type="checkbox" value="loggedin" <?php if( ! empty( $wppb_user_status ) && $wppb_user_status == 'loggedin' ) echo 'checked="checked"'; ?> name="wppb-content-restrict-user-status" id="wppb-content-restrict-user-status">
                <?php echo esc_html__( 'Logged In Users', 'profile-builder' ); ?>
            </label>

            <?php
            if( ! empty( $wppb_roles ) ) {
                foreach( $wppb_roles as $wppb_role => $wppb_role_details ) {
                    ?>
                    <label class="wppb-meta-box-checkbox-label" for="wppb-content-restrict-user-role-<?php echo esc_attr( $wppb_role ) ?>">
                        <input type="checkbox" value="<?php echo esc_attr( $wppb_role ); ?>" <?php if( in_array( $wppb_role, $wppb_selected_roles ) ) echo 'checked="checked"'; ?> name="wppb-content-restrict-user-role[]" id="wppb-content-restrict-user-role-<?php echo esc_attr( $wppb_role ) ?>">
                        <?php echo !empty( $wppb_role_details['name'] ) ? esc_html( $wppb_role_details['name'] ) : esc_html( $wppb_role ); ?>
                    </label>
                <?php } ?>

                <p class="description" style="margin-top: 10px;">
                    <?php echo esc_html( sprintf( __( 'Checking only "Logged In Users" will show this %s to all logged in users, regardless of user role.', 'profile-builder' ), $post->post_type) ); ?>
                </p>

                <p class="description">
                    <?php echo esc_html( sprintf( __( 'Checking any user role will show this %s only to users that have one of those user roles assigned.', 'profile-builder' ), $post->post_type) ); ?>
                </p>
            <?php } ?>
        </div>
    </div>

    <?php if( isset( $post->post_type ) && $post->post_type == 'product' ) : ?>
        <div class="wppb-meta-box-fields-wrapper">
            <h4><?php esc_html_e( 'Purchase Options', 'profile-builder' ); ?></h4>

            <?php
            $wppb_purchase_user_status    = get_post_meta( $post->ID, 'wppb-purchase-restrict-user-status', true );
            $wppb_selected_purchase_roles = get_post_meta( $post->ID, 'wppb-purchase-restrict-user-role' );
            ?>

            <!-- Who Can Purchase? options -->
            <div class="wppb-meta-box-field-wrapper">
                <label class="wppb-meta-box-field-label"><?php esc_html_e( 'Who can purchase?', 'profile-builder' ); ?></label>

                <label class="wppb-meta-box-checkbox-label" for="wppb-purchase-restrict-user-status">
                    <input type="checkbox" value="loggedin" <?php if( ! empty( $wppb_purchase_user_status ) ) checked($wppb_purchase_user_status, 'loggedin' ); ?> name="wppb-purchase-restrict-user-status" id="wppb-purchase-restrict-user-status">
                    <?php esc_html_e( 'Logged In Users', 'profile-builder' ); ?>
                </label>

                <?php if( !empty( $wppb_roles ) ): foreach( $wppb_roles as $wppb_role => $wppb_role_details ): ?>

                    <label class="wppb-meta-box-checkbox-label" for="wppb-purchase-restrict-user-role-<?php echo esc_attr( $wppb_role ) ?>">
                        <input type="checkbox" value="<?php echo esc_attr( $wppb_role ); ?>" <?php if( in_array( $wppb_role, $wppb_selected_purchase_roles ) ) echo 'checked="checked"'; ?> name="wppb-purchase-restrict-user-role[]" id="wppb-purchase-restrict-user-role-<?php echo esc_attr( $wppb_role ) ?>">
                        <?php echo !empty( $wppb_role_details['name'] ) ? esc_html( $wppb_role_details['name'] ) : esc_html( $wppb_role ); ?>
                    </label>

                <?php endforeach; ?>

                    <p class="description" style="margin-top: 10px;">
                        <?php esc_html_e( 'Select who can purchase this product.', 'profile-builder' ); ?>
                    </p>

                <?php endif; ?>

            </div>
        </div>
    <?php endif; ?>

    <div id="wppb-meta-box-fields-wrapper-restriction-redirect-url" class="wppb-meta-box-fields-wrapper <?php echo ( $wppb_content_restrict_type == 'redirect' ? 'wppb-content-restriction-enabled' : '' ); ?>">
        <h4><?php echo esc_html__( 'Restriction Redirect URL', 'profile-builder' ); ?></h4>

        <div class="wppb-meta-box-field-wrapper">

            <?php $wppb_custom_redirect_url_enabled = get_post_meta( $post->ID, 'wppb-content-restrict-custom-redirect-url-enabled', true ); ?>

            <label class="wppb-meta-box-field-label"><?php esc_html_e( 'Enable Custom Redirect URL', 'profile-builder' ); ?></label>

            <label class="wppb-meta-box-checkbox-label" for="wppb-content-restrict-custom-redirect-url-enabled">
                <input type="checkbox" value="yes" <?php echo ( ! empty( $wppb_custom_redirect_url_enabled ) ? 'checked="checked"' : '' ); ?> name="wppb-content-restrict-custom-redirect-url-enabled" id="wppb-content-restrict-custom-redirect-url-enabled">
                <span class="description"><?php echo esc_html( sprintf( __( 'Check if you wish to add a custom redirect URL for this %s.', 'profile-builder' ), $post->post_type ) ); ?></span>
            </label>
        </div>

        <div class="wppb-meta-box-field-wrapper wppb-meta-box-field-wrapper-custom-redirect-url <?php echo ( ! empty( $wppb_custom_redirect_url_enabled ) ? 'wppb-content-restriction-enabled' : '' ); ?>">
            <?php $wppb_custom_redirect_url = get_post_meta( $post->ID, 'wppb-content-restrict-custom-redirect-url', true ); ?>

            <label class="wppb-meta-box-field-label" for="wppb-content-restrict-custom-redirect-url"><?php esc_html_e( 'Custom Redirect URL', 'profile-builder' ); ?></label>

            <label class="wppb-meta-box-checkbox-label">
                <input type="text" value="<?php echo ( ! empty( $wppb_custom_redirect_url ) ? esc_url( $wppb_custom_redirect_url ) : '' ); ?>" name="wppb-content-restrict-custom-redirect-url" id="wppb-content-restrict-custom-redirect-url" class="widefat">
                <p class="description"><?php echo esc_html( sprintf( __( 'Add a URL where you wish to redirect users that do not have access to this %s and try to access it directly.', 'profile-builder' ), $post->post_type ) ); ?></p>
            </label>
        </div>
    </div>

    <div id="wppb-meta-box-fields-wrapper-restriction-custom-messages" class="wppb-meta-box-fields-wrapper">
        <h4><?php echo esc_html__( 'Restriction Messages', 'profile-builder' ); ?></h4>

        <div class="wppb-meta-box-field-wrapper">
            <?php
            $wppb_custom_messages_enabled = get_post_meta( $post->ID, 'wppb-content-restrict-messages-enabled', true );
            ?>
            <label class="wppb-meta-box-field-label"><?php esc_html_e( 'Enable Custom Messages', 'profile-builder' ); ?></label>

            <label class="wppb-meta-box-checkbox-label" for="wppb-content-restrict-messages-enabled">
                <input type="checkbox" value="yes" <?php echo ( ! empty( $wppb_custom_messages_enabled ) ? 'checked="checked"' : '' ); ?> name="wppb-content-restrict-messages-enabled" id="wppb-content-restrict-messages-enabled">
                <span class="description"><?php echo esc_html( sprintf( __( 'Check if you wish to add custom messages for this %s.', 'profile-builder' ), $post->post_type ) ); ?></span>
            </label>
        </div>

        <div class="wppb-meta-box-field-wrapper-custom-messages <?php echo ( ! empty( $wppb_custom_messages_enabled ) ? 'wppb-content-restriction-enabled' : '' ); ?>">
            <?php do_action( 'wppb_view_meta_box_content_restrict_restriction_messages_top', $post->ID ); ?>

            <p><strong><?php esc_html_e( 'Messages for logged-out users', 'profile-builder' ); ?></strong></p>
            <?php wp_editor( get_post_meta( $post->ID, 'wppb-content-restrict-message-logged_out', true ), 'wppb-content-restrict-message-logged_out', array( 'textarea_name' => 'wppb-content-restrict-message-logged_out', 'editor_height' => 200 ) ); ?>

            <p><strong><?php esc_html_e( 'Messages for logged-in users', 'profile-builder' ); ?></strong></p>
            <?php wp_editor( get_post_meta( $post->ID, 'wppb-content-restrict-message-logged_in', true ), 'wppb-content-restrict-message-logged_in', array( 'textarea_name' => 'wppb-content-restrict-message-logged_in', 'editor_height' => 200 ) ); ?>

            <?php if( isset( $post->post_type ) && $post->post_type == 'product' ) : ?>
                
                <p><strong><?php esc_html_e( 'Messages for restricted product purchase', 'profile-builder' ); ?></strong></p>
                <?php wp_editor( wp_kses_post( get_post_meta( $post->ID, 'wppb-content-restrict-message-purchasing_restricted', true ) ), 'wppb-content-restrict-message-purchasing_restricted', array( 'textarea_name' => 'wppb-content-restrict-message-purchasing_restricted', 'editor_height' => 200 ) ); ?>

            <?php endif; ?>
        </div>
    </div>

    <?php
    wp_nonce_field( 'wppb_meta_box_single_content_restriction_nonce', 'wppb_content_restriction_token', false );

}

function wppb_content_restriction_save_data( $post_id ) {

    if( empty( $_POST['wppb_content_restriction_token'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['wppb_content_restriction_token'] ), 'wppb_meta_box_single_content_restriction_nonce' ) ) {
        return;
    }

    // Handle restriction rules
    delete_post_meta( $post_id, 'wppb-content-restrict-type' );

    if( ! empty( $_POST['wppb-content-restrict-type'] ) ) {
        update_post_meta( $post_id, 'wppb-content-restrict-type', sanitize_text_field( $_POST['wppb-content-restrict-type'] ) );
    }

    if( isset( $_POST['wppb-content-restrict-user-status'] ) && $_POST['wppb-content-restrict-user-status'] === 'loggedin' ) {
        delete_post_meta( $post_id, 'wppb-content-restrict-user-role' );

        if( isset( $_POST['wppb-content-restrict-user-role'] ) && is_array( $_POST['wppb-content-restrict-user-role'] ) ) {
            $user_role_ids = array_map( 'sanitize_text_field', $_POST['wppb-content-restrict-user-role'] );
            foreach( $user_role_ids as $user_role_id ) {
                if( ! empty( $user_role_id ) ) {
                    add_post_meta( $post_id, 'wppb-content-restrict-user-role',  $user_role_id );
                }
            }
        }
    }

    if( isset( $_POST['wppb-content-restrict-user-status'] ) && $_POST['wppb-content-restrict-user-status'] == 'loggedin' ) {
        update_post_meta( $post_id, 'wppb-content-restrict-user-status', 'loggedin' );
    } else {
        delete_post_meta( $post_id, 'wppb-content-restrict-user-status' );
    }

    // Handle custom redirect URL
    delete_post_meta( $post_id, 'wppb-content-restrict-custom-redirect-url-enabled' );

    if( isset( $_POST['wppb-content-restrict-custom-redirect-url-enabled'] ) ) {
        update_post_meta( $post_id, 'wppb-content-restrict-custom-redirect-url-enabled', 'yes' );
    }

    update_post_meta( $post_id, 'wppb-content-restrict-custom-redirect-url', ( ! empty( $_POST['wppb-content-restrict-custom-redirect-url'] ) ? esc_url_raw( $_POST['wppb-content-restrict-custom-redirect-url'] ) : '' ) );

    // Handle custom messages
    delete_post_meta( $post_id, 'wppb-content-restrict-messages-enabled' );

    if( isset( $_POST['wppb-content-restrict-messages-enabled'] ) ) {
        update_post_meta( $post_id, 'wppb-content-restrict-messages-enabled', 'yes' );
    }

    update_post_meta( $post_id, 'wppb-content-restrict-message-logged_out', ( ! empty( $_POST['wppb-content-restrict-message-logged_out'] ) ? wp_kses_post( $_POST['wppb-content-restrict-message-logged_out'] ) : '' ) );
    update_post_meta( $post_id, 'wppb-content-restrict-message-logged_in', ( ! empty( $_POST['wppb-content-restrict-message-logged_in'] ) ? wp_kses_post( $_POST['wppb-content-restrict-message-logged_in'] ) : '' ) );

    // Handle WooCommerce purchase settings
    delete_post_meta( $post_id, 'wppb-purchase-restrict-user-role' );
    if( isset( $_POST['wppb-purchase-restrict-user-role'] ) && is_array( $_POST['wppb-purchase-restrict-user-role'] ) ) {
        $restrict_roles = array_map( 'sanitize_text_field', $_POST['wppb-purchase-restrict-user-role'] );
        foreach ( $restrict_roles as $role ) {
            if( !empty( $role ) )
                add_post_meta($post_id, 'wppb-purchase-restrict-user-role', sanitize_text_field( $role ) );
        }

    }

    if( isset( $_POST['wppb-purchase-restrict-user-status'] ) && $_POST['wppb-purchase-restrict-user-status'] === 'loggedin' )
        update_post_meta( $post_id, 'wppb-purchase-restrict-user-status', 'loggedin' );
    else
        delete_post_meta( $post_id, 'wppb-purchase-restrict-user-status' );

    // save custom "product purchase restricted" message
    update_post_meta( $post_id, 'wppb-content-restrict-message-purchasing_restricted', ( ! empty( $_POST['wppb-content-restrict-message-purchasing_restricted'] ) ? wp_kses_post($_POST['wppb-content-restrict-message-purchasing_restricted']) : '' ) );

}
add_action( 'save_post', 'wppb_content_restriction_save_data' );
add_action( 'edit_attachment', 'wppb_content_restriction_save_data' );
