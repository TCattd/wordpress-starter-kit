<?php

    class WPPB_IN_Campaign_Monitor_Widget extends WP_Widget {

        /*
         * Register widget with WordPress.
         *
         * @since v.1.0.0
         *
         */
        function __construct() {
            parent::__construct(
                'wppb_campaign_monitor_widget',
                __( 'Profile Builder Campaign Monitor Widget', 'profile-builder' ),
                array( 'description' => __( 'Adds a basic subscribe form so that your users can subscribe to your Campaign Monitor lists', 'profile-builder' ), )
            );

            add_action( 'wp_ajax_display_list_fields', array( $this, 'display_list_fields' ) );
        }


        /*
         * Function that returns the saved values for a instance
         *
         * @since v.1.0.0
         *
         * @param int $widget_number    - The instance number
         *
         */
        public function get_instance( $widget_number = '' ) {

            if( empty( $widget_number ) )
                $widget_number = $this->number;

            $option = get_option( $this->option_name );
            $instance = $option[ $widget_number ];

            return $instance;
        }


        /*
         * Function that displays the widget in the front-end
         *
         * @since v.1.0.0
         *
         * @param array $args     Widget arguments.
         * @param array $instance Saved values from database.
         *
         */
        public function widget( $args, $instance )
        {

            $validate_response = $this->validate_front_end_form();

            // Get settings
            $wppb_cmi_settings = get_option('wppb_cmi_settings', array());

            // If the key is invalid or missing display an error message to the admins
            if (!wppb_in_cmi_get_api_key_status($wppb_cmi_settings) && current_user_can('manage_options')) {

                echo $args['before_widget']; //phpcs:ignore

                if (!empty($instance['title'])) {
                    echo $args['before_title'] . apply_filters('wpp_cmi_widget_title', $instance['title']) . $args['after_title']; //phpcs:ignore
                }

                echo esc_html__('Something went wrong. Either the Campaign Monitor API key is missing or it is invalid.', 'profile-builder');

                echo $args['after_widget']; //phpcs:ignore

            // If API key is okay display the form
            } else {

                echo $args['before_widget']; //phpcs:ignore

                if (!empty($instance['title'])) {
                    echo $args['before_title'] . apply_filters('wpp_cmi_widget_title', $instance['title']) . $args['after_title']; //phpcs:ignore
                }


                if ($validate_response['success'] == 1) {

                    // Add subscriber to the list
                    $auth = array('api_key' => $wppb_cmi_settings['api_key']);
                    $wrap = new WPPB_CS_REST_Subscribers($validate_response['subscribe']['list_id'], $auth);

                    $result = $wrap->add($validate_response['subscribe']['args']);

                    // Update the user meta
                    if ($result->was_successful()) {
                        echo '<div class="wppb-cmi-widget-subscribe-success-wrapper">';
                            echo '<p class="wppb-cmi-widget-subscribe-success-message">' . sprintf(__('%s', 'profile-builder'), apply_filters('wppb_cmi_widget_subscribe_new_success', 'You have been subscribed to our newsletter')) . '</p>'; //phpcs:ignore
                        echo '</div>';

                        // Update user data if user is logged in
                        $user = get_user_by('email', $validate_response['subscribe']['args']['EmailAddress']);

                        if ($user)
                            update_user_meta($user->ID, 'wppb_cmi_subscribe_status', 'active');
                    }

                } else {

                    if (!empty($instance['cmi_list_id']) && !empty($instance['cmi_list_fields'])) {
                        echo '<form enctype="multipart/form-data" method="POST" action="' . apply_filters('wppb_cmi_widget_subscribe_form_action', '') . '">'; //phpcs:ignore

                        // Add hidden inputs with the list ids
                        echo '<input type="hidden" name="wppb_cmi_list_id" value="' . esc_attr( $instance['cmi_list_id'] ) . '" />';

                        foreach ($instance['cmi_list_fields'] as $wpp_cmi_list_field) {

                            if (isset($wpp_cmi_list_field['tag']) && !empty($wpp_cmi_list_field['tag'])) {

                                // Asign error class to variable if we have errors
                                $error_class = '';
                                if (isset($validate_response['success']) && $validate_response['success'] == 0 && isset($validate_response['fields'][$wpp_cmi_list_field['tag']]) && isset($validate_response['fields'][$wpp_cmi_list_field['tag']]['errors'])) {
                                    $error_class = 'wppb-cmi-widget-field-error';
                                }

                                // Field wrapper
                                echo '<div class="wppb-cmi-widget-field-wrapper ' . $error_class . '">'; //phpcs:ignore


                                // Display field label
                                echo '<label for="wppb_cmi_widget_request_' . esc_attr( $wpp_cmi_list_field['tag'] ) . '">' . esc_html( $wpp_cmi_list_field['name'] ) . '</label>';


                                // Display required span if field is required
                                if (isset($wpp_cmi_list_field['req']))
                                    echo '<span class="wppb-cmi-widget-field-required">' . apply_filters('wppb_cmi_widget_field_required', '*') . '</span>'; //phpcs:ignore


                                // If there was an error get the values that were sent
                                $field_value = '';
                                if (isset($validate_response['success']) && $validate_response['success'] == 0 && isset($validate_response['fields'][$wpp_cmi_list_field['tag']]) && isset($validate_response['fields'][$wpp_cmi_list_field['tag']]['value'])) {
                                    $field_value = $validate_response['fields'][$wpp_cmi_list_field['tag']]['value'];
                                }

                                // Display the actual input
                                echo '<input type="text" id="wppb_cmi_widget_request_' . esc_attr( $wpp_cmi_list_field['tag'] ) . '" name="wppb_cmi_widget_request_' . esc_attr( $wpp_cmi_list_field['tag'] ) . '" value="' . esc_attr( $field_value ) . '" />';

                                // Display field errors under the input if there are any
                                if (isset($validate_response['success']) && $validate_response['success'] == 0 && isset($validate_response['fields'][$wpp_cmi_list_field['tag']]) && isset($validate_response['fields'][$wpp_cmi_list_field['tag']]['errors'])) {
                                    foreach ($validate_response['fields'][$wpp_cmi_list_field['tag']]['errors'] as $error) {
                                        echo '<div class="wppb-cmi-widget-field-error-message-wrapper"><span class="wppb-cmi-widget-field-error-message">' . wp_kses_post( $error ) . '</span></div>';
                                    }
                                }

                                echo '</div>';

                            }

                        }

                        // Display the submit button
                        echo '<input type="submit" value="' . esc_attr( apply_filters('wppb_cmi_widget_submit_button_text', __('Submit', 'profile-builder') ) ) . '" />';

                        echo '</form>';
                    }

                }

                echo $args['after_widget']; //phpcs:ignore

            }
        }

        /*
         * Function that displays the widget in the back-end
         *
         * @since v.1.0.0
         *
         * @param array $instance Previously saved values from database.
         *
         */
        public function form( $instance ) {

            $wppb_cmi_settings = get_option( 'wppb_cmi_settings', array() );

            if( wppb_in_cmi_get_api_key_status( $wppb_cmi_settings ) ) {
                $title = !empty($instance['title']) ? $instance['title'] : __('Subscribe to Newsletter', 'profile-builder');
                $cmi_list_id = !empty($instance['cmi_list_id']) ? $instance['cmi_list_id'] : '';
                $cmi_list_field_fullname = isset($instance['cmi_list_fields']['fullname']['tag']) ? $instance['cmi_list_fields']['fullname']['tag'] : '';
                $cmi_list_resubscribe = isset($instance['cmi_list_resubscribe']) ? $instance['cmi_list_resubscribe'] : '';

                ?>

                <div class="wppb-cmi-widget-wrapper">

                    <label class="wppb-cmi-widget-label" for="<?php echo esc_attr( $this->get_field_id('title') ); ?>"><?php esc_html_e('Title', 'profile-builder'); ?></label>

                    <div class="wppb-cmi-widget-input-wrapper">
                        <input class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" type="text" value="<?php echo esc_attr($title); ?>">
                    </div>

                    <label class="wppb-cmi-widget-label" for="<?php echo esc_attr( $this->get_field_id('cmi_list_id') ); ?>"><?php esc_html_e( 'Select the list your new subscriber will be added to', 'profile-builder' ); ?></label>

                    <div class="wppb-cmi-widget-input-wrapper">
                        <select id="<?php echo esc_attr( $this->get_field_id('cmi_list_id') ); ?>" name="<?php echo esc_attr( $this->get_field_name('cmi_list_id') ) ?>" class="widefat">
                            <?php if( !empty($wppb_cmi_settings['client']['lists']) ): ?>
                                <option value=""><?php echo esc_html__( 'Select list...', 'profile-builder' ); ?></option>

                                <?php foreach( $wppb_cmi_settings['client']['lists'] as $wppb_cmi_list_id => $wppb_cmi_list ): ?>
                                    <option <?php selected( $cmi_list_id, $wppb_cmi_list_id, true); ?> value="<?php echo esc_attr( $wppb_cmi_list_id ); ?>"><?php echo esc_html( $wppb_cmi_list['name'] ); ?></option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value=""><?php echo esc_html__( 'No lists found', 'profile-builder' ); ?></option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <label class="wppb-cmi-widget-label"><?php esc_html_e( 'Select which fields to show', 'profile-builder' ); ?></label>

                    <div class="wppb-cmi-widget-input-wrapper">
                        <label class="wppb-cmi-widget-checkbox-label">
                            <input type="hidden" value="email" name="<?php echo esc_attr( $this->get_field_name('cmi_list_fields') ); ?>[email][tag]" />
                            <input type="checkbox" value="" disabled="disabled" checked="checked" />
                            <?php echo esc_html__( 'Email address', 'profile-builder' ); ?>
                        </label>
                        <input type="hidden" value="<?php echo esc_html__( 'Email address', 'profile-builder' ); ?>" name="<?php echo esc_attr( $this->get_field_name('cmi_list_fields') ); ?>[email][name]" />
                        <input type="hidden" value="1" name="<?php echo esc_attr( $this->get_field_name('cmi_list_fields') ); ?>[email][req]" />

                        <label class="wppb-cmi-widget-checkbox-label">
                            <input type="checkbox" value="<?php echo esc_html__( 'fullname', 'profile-builder' ); ?>" name="<?php echo esc_attr( $this->get_field_name('cmi_list_fields') ); ?>[fullname][tag]" <?php checked( $cmi_list_field_fullname, 'fullname', true); ?> />
                            <?php echo esc_html__( 'Name', 'profile-builder' ); ?>
                        </label>
                        <input type="hidden" value="<?php echo esc_html__( 'Name', 'profile-builder' ); ?>" name="<?php echo esc_attr( $this->get_field_name('cmi_list_fields') ); ?>[fullname][name]" />
                    </div>

                    <label class="wppb-cmi-widget-label"><?php esc_html_e( 'Extra options', 'profile-builder' ); ?></label>

                    <div class="wppb-cmi-widget-input-wrapper">
                        <label class="wppb-cmi-widget-checkbox-label">
                            <input type="checkbox" name="<?php echo esc_attr( $this->get_field_name('cmi_list_resubscribe') ); ?>" <?php checked( $cmi_list_resubscribe, 'on', true); ?> />
                            <?php echo esc_html__( 'Resubscribe', 'profile-builder' ); ?>
                        </label>

                        <p class="description"><?php echo esc_html__( 'If the subscriber is in an inactive state or has previously been unsubscribed and you check the Resubscribe option, they will be re-added to the list. Therefore, this method should be used with caution and only where suitable.', 'profile-builder' ); ?></p>
                    </div>
                </div>

            <?php
            } else {
                echo '<p>' . esc_html__( 'The Campaign Monitor API key is either missing or is invalid.', 'profile-builder' ) . '</p>';
            }
        }


        /*
         * Function that sanitizes the widget form values as they are saved
         *
         *
         * @param array $new_instance Values just sent to be saved.
         * @param array $old_instance Previously saved values from database.
         *
         * @return array Updated safe values to be saved.
         */
        public function update( $new_instance, $old_instance ) {

            $instance = array();

            // Update the title field
            $instance['title'] = isset( $new_instance['title'] ) ? strip_tags( $new_instance['title'] ) : '';

            // Update the lists ids
            $instance['cmi_list_id'] = isset( $new_instance['cmi_list_id'] ) ? strip_tags( $new_instance['cmi_list_id'] ) : '';

            // Update the list fields
            $instance['cmi_list_fields'] = isset( $new_instance['cmi_list_fields'] ) ? $new_instance['cmi_list_fields'] : '';

            // Update the list resubscribe
            $instance['cmi_list_resubscribe'] = isset( $new_instance['cmi_list_resubscribe'] ) ? strip_tags( $new_instance['cmi_list_resubscribe'] ) : '';

            return $instance;

        }


        /*
         * Function that validates the front-end field
         *
         * @since v.1.0.0
         *
         * @return array
         *
         */
        public function validate_front_end_form() {

            $response = array( 'success' => 1 );
            $response_error = array( 'success' => 0 );
            $error = 0;

            // Validate list id
            if( isset( $_POST['wppb_cmi_list_id'] ) && !empty( $_POST['wppb_cmi_list_id'] ) )
                $response['subscribe']['list_id'] = htmlspecialchars(trim($_POST['wppb_cmi_list_id']));//phpcs:ignore
            else
                return false;

            // Validate fields
            $instance = $this->get_instance();

            foreach( $instance['cmi_list_fields'] as $wpp_cmi_list_field ) {

                if( isset( $wpp_cmi_list_field['tag'] ) && isset( $_POST['wppb_cmi_widget_request_' . $wpp_cmi_list_field['tag']] ) ) {

                    $field_value = htmlspecialchars(trim( $_POST['wppb_cmi_widget_request_' . $wpp_cmi_list_field['tag']] ) ); //phpcs:ignore

                    if( !empty( $field_value ) ) {

                        // E-mail field
                        if( $wpp_cmi_list_field['tag'] == 'email' && !is_email( $field_value ) ) {
                            $response_error['fields'][ $wpp_cmi_list_field['tag'] ]['errors'][] = apply_filters( 'wppb_cmi_widget_field_error_email_not_valid', 'The email is not valid' );
                            $error = 1;
                        } elseif( is_email( $field_value ) ) {
                            $response['subscribe']['args']['EmailAddress'] = $field_value;
                        }

                        // Name field
                        if( $wpp_cmi_list_field['tag'] == 'fullname' ) {
                            $response['subscribe']['args']['Name'] = $field_value;
                        }

                    } elseif( isset( $wpp_cmi_list_field['req'] ) ) {
                        $response_error['fields'][ $wpp_cmi_list_field['tag'] ]['errors'][] = apply_filters( 'wppb_cmi_widget_field_error_required', 'This field is required' , $wpp_cmi_list_field['tag'] );
                        $error = 1;
                    }

                    // Return field value on error
                    if( !empty( $field_value ) )
                        $response_error['fields'][ $wpp_cmi_list_field['tag'] ]['value'] = $field_value;
                }

            }

            // Add list resubscribe
            if( isset( $instance['cmi_list_resubscribe'] ) && !empty( $instance['cmi_list_resubscribe'] ) )
                $response['subscribe']['args']['Resubscribe'] = true;

            // Return results
            if( $error == 1 )
                return $response_error;
            else
                return $response;

        }

    }

    add_action( 'widgets_init', function(){
        register_widget( 'WPPB_IN_Campaign_Monitor_Widget' );
    });