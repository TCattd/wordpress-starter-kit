<?php
/**
 * Displays the content of the dialog box when the user clicks on the "Deactivate" link on the plugin settings page
 *
 * @package BestWebSoft
 * @since 2.1.3
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Displays a confirmation and feedback dialog box when the user clicks on the "Deactivate" link on the plugins
 * page.
 *
 * @since  2.1.3
 */
if ( ! function_exists( 'bws_add_deactivation_feedback_dialog_box' ) ) {
	function bws_add_deactivation_feedback_dialog_box() {
		global $bstwbsftwppdtplgns_active_plugins;
		if ( empty( $bstwbsftwppdtplgns_active_plugins ) ) {
			return;
		}

		$contact_support_template = __( 'Need help? We are ready to answer your questions.', 'bestwebsoft' ) . ' <a href="https://support.bestwebsoft.com/hc/en-us/requests/new" target="_blank">' . __( 'Contact Support', 'bestwebsoft' ) . '</a>';

		$reasons = array(
			array(
				'id'                => 'NOT_WORKING',
				'text'              => __( 'The plugin is not working', 'bestwebsoft' ),
				'input_type'        => 'textarea',
				'input_placeholder' => __( "Kindly share what didn't work so we can fix it in future updates...", 'bestwebsoft' ),
			),
			array(
				'id'                => 'DIDNT_WORK_AS_EXPECTED',
				'text'              => __( "The plugin didn't work as expected", 'bestwebsoft' ),
				'input_type'        => 'textarea',
				'input_placeholder' => __( 'What did you expect?', 'bestwebsoft' ),
			),
			array(
				'id'                => 'SUDDENLY_STOPPED_WORKING',
				'text'              => __( 'The plugin suddenly stopped working', 'bestwebsoft' ),
				'input_type'        => '',
				'input_placeholder' => '',
				'internal_message'  => $contact_support_template,
			),
			array(
				'id'                => 'BROKE_MY_SITE',
				'text'              => __( 'The plugin broke my site', 'bestwebsoft' ),
				'input_type'        => '',
				'input_placeholder' => '',
				'internal_message'  => $contact_support_template,
			),
			array(
				'id'                => 'COULDNT_MAKE_IT_WORK',
				'text'              => __( "I couldn't understand how to get it work", 'bestwebsoft' ),
				'input_type'        => '',
				'input_placeholder' => '',
				'internal_message'  => $contact_support_template,
			),
			array(
				'id'                => 'FOUND_A_BETTER_PLUGIN',
				'text'              => __( 'I found a better plugin', 'bestwebsoft' ),
				'input_type'        => 'textfield',
				'input_placeholder' => __( "What's the plugin name?", 'bestwebsoft' ),
			),
			array(
				'id'                => 'GREAT_BUT_NEED_SPECIFIC_FEATURE',
				'text'              => __( "The plugin is great, but I need specific feature that you don't support", 'bestwebsoft' ),
				'input_type'        => 'textarea',
				'input_placeholder' => __( 'What feature?', 'bestwebsoft' ),
			),
			array(
				'id'                => 'NO_LONGER_NEEDED',
				'text'              => __( 'I no longer need the plugin', 'bestwebsoft' ),
				'input_type'        => '',
				'input_placeholder' => '',
			),
			array(
				'id'                => 'TEMPORARY_DEACTIVATION',
				'text'              => __( "It's a temporary deactivation, I'm just debugging an issue", 'bestwebsoft' ),
				'input_type'        => '',
				'input_placeholder' => '',
			),
			array(
				'id'                => 'OTHER',
				'text'              => __( 'Other', 'bestwebsoft' ),
				'input_type'        => 'textfield',
				'input_placeholder' => '',
			),
		);

		$modal_html = '<div class="bws-modal bws-modal-deactivation-feedback">
	    	<div class="bws-modal-dialog">
	    		<div class="bws-modal-body">
	    			<h2>' . __( 'Quick Feedback', 'bestwebsoft' ) . '</h2>
	    			<div class="bws-modal-panel active">
	    				<p>' . __( 'If you have a moment, please let us know why you are deactivating', 'bestwebsoft' ) . ':</p><ul>';

		foreach ( $reasons as $reason ) {
			$list_item_classes = 'bws-modal-reason' . ( ! empty( $reason['input_type'] ) ? ' has-input' : '' );

			if ( ! empty( $reason['internal_message'] ) ) {
				$list_item_classes      .= ' has-internal-message';
				$reason_internal_message = $reason['internal_message'];
			} else {
				$reason_internal_message = '';
			}

			$modal_html .= '<li class="' . $list_item_classes . '" data-input-type="' . $reason['input_type'] . '" data-input-placeholder="' . $reason['input_placeholder'] . '">
				<label>
					<span>
						<input type="radio" name="selected-reason" value="' . $reason['id'] . '"/>
					</span>
					<span>' . $reason['text'] . '</span>
				</label>
				<div class="bws-modal-internal-message">' . $reason_internal_message . '</div>
			</li>';
		}
		$modal_html .= '</ul>
		    				<label class="bws-modal-anonymous-label">
			    				<input type="checkbox" />' .
								__( 'Send website data and allow to contact me back', 'bestwebsoft' ) .
							'</label>
						</div>
					</div>
					<div class="bws-modal-footer">
						<a href="#" class="button button-primary bws-modal-button-deactivate disabled">' . __( 'Submit and Deactivate', 'bestwebsoft' ) . '</a>
						<a href="#" class="bws-modal-skip-link">' . __( 'Skip and Deactivate', 'bestwebsoft' ) . '</a>
						<span class="bws-modal-processing hidden">' . __( 'Processing', 'bestwebsoft' ) . '...</span>
						<div class="clear"></div>
					</div>
				</div>
			</div>';

		$script = '';

		foreach ( $bstwbsftwppdtplgns_active_plugins as $basename => $plugin_data ) {

			$slug      = dirname( $basename );
			$plugin_id = sanitize_title( $plugin_data['Name'] );

			$script .= "(function($) {
					var modalHtml = " . json_encode( esc_html( $modal_html ) ) . ",
					    \$modal                = $( modalHtml ),
					    \$deactivateLink       = $( '#the-list .active[data-plugin=\"" . $basename . "\"] .deactivate a' ),
						\$anonymousFeedback    = \$modal.find( '.bws-modal-anonymous-label' ),
						selectedReasonID      = false;

					/* WP added data-plugin attr after 4.5 version/ In prev version was id attr */
					if ( 0 == \$deactivateLink.length )
						\$deactivateLink = $( '#the-list .active#" . $plugin_id . " .deactivate a' );

					\$modal.appendTo( $( 'body' ) );

					BwsModalRegisterEventHandlers();
					
					function BwsModalRegisterEventHandlers() {
						\$deactivateLink.click( function( evt ) {
							evt.preventDefault();

							/* Display the dialog box.*/
							BwsModalReset();
							\$modal.addClass( 'active' );
							$( 'body' ).addClass( 'has-bws-modal' );
						});

						\$modal.on( 'input propertychange', '.bws-modal-reason-input input', function() {
							if ( ! BwsModalIsReasonSelected( 'OTHER' ) ) {
								return;
							}

							var reason = $( this ).val().trim();

							/* If reason is not empty, remove the error-message class of the message container to change the message color back to default. */
							if ( reason.length > 0 ) {
								\$modal.find( '.message' ).removeClass( 'error-message' );
								BwsModalEnableDeactivateButton();
							}
						});

						\$modal.on( 'blur', '.bws-modal-reason-input input', function() {
							var \$userReason = $( this );

							setTimeout( function() {
								if ( ! BwsModalIsReasonSelected( 'OTHER' ) ) {
									return;
								}
							}, 150 );
						});

						\$modal.on( 'click', '.bws-modal-footer .bws-modal-skip-link', function( evt ) {
							evt.preventDefault();
							
							/* If no selected reason, just deactivate the plugin. */
							window.location.href = \$deactivateLink.attr( 'href' );
							return;
						});

						\$modal.on( 'click', '.bws-modal-footer .button', function( evt ) {
							evt.preventDefault();

							if ( $( this ).hasClass( 'disabled' ) ) {
								return;
							}

							var _parent = $( this ).parents( '.bws-modal:first' ),
								_this =  $( this );

							var \$radio = \$modal.find( 'input[type=\"radio\"]:checked' );

							if ( 0 === \$radio.length ) {
								/* If no selected reason */
								BwsModalDisableDeactivateButton();
								return;
							}

							var \$selected_reason = \$radio.parents( 'li:first' ),
							    \$input = \$selected_reason.find( 'textarea, input[type=\"text\"]' ),
							    userReason = ( 0 !== \$input.length ) ? \$input.val().trim() : '';

							var is_anonymous = ( \$anonymousFeedback.find( 'input' ).is( ':checked' ) ) ? 0 : 1;

							$.ajax({
								url       : ajaxurl,
								method    : 'POST',
								data      : {
									'action'			: 'bws_submit_uninstall_reason_action',
									'plugin'			: '" . $basename . "',
									'reason_id'			: \$radio.val(),
									'reason_info'		: userReason,
									'is_anonymous'		: is_anonymous,
									'bws_ajax_nonce'	: '" . wp_create_nonce( 'bws_ajax_nonce' ) . "'
								},
								beforeSend: function() {
									_parent.find( '.bws-modal-footer .button' ).hide();
									_parent.find( '.bws-modal-footer .bws-modal-processing' ).show();
								},
								complete  : function( message ) {
									/* Do not show the dialog box, deactivate the plugin. */
									window.location.href = \$deactivateLink.attr( 'href' );
								}
							});
						});

						\$modal.on( 'click', 'input[type=\"radio\"]', function() {
							var \$selectedReasonOption = $( this );

							/* If the selection has not changed, do not proceed. */
							if ( selectedReasonID === \$selectedReasonOption.val() )
								return;

							selectedReasonID = \$selectedReasonOption.val();

							\$anonymousFeedback.show();

							var _parent = $( this ).parents( 'li:first' );

							\$modal.find( '.bws-modal-reason-input' ).remove();
							\$modal.find( '.bws-modal-internal-message' ).hide();

							BwsModalEnableDeactivateButton();

							if ( _parent.hasClass( 'has-internal-message' ) ) {
								_parent.find( '.bws-modal-internal-message' ).show();
							}

							if (_parent.hasClass('has-input')) {
								var reasonInputHtml = '<div class=\"bws-modal-reason-input\"><span class=\"message\"></span>' + ( ( 'textfield' === _parent.data( 'input-type' ) ) ? '<input type=\"text\" />' : '<textarea rows=\"5\" maxlength=\"200\"></textarea>' ) + '</div>';

								_parent.append( $( reasonInputHtml ) );
								_parent.find( 'input, textarea' ).attr( 'placeholder', _parent.data( 'input-placeholder' ) ).focus();

								if ( BwsModalIsReasonSelected( 'OTHER' ) ) {
									\$modal.find( '.message' ).text( '" . esc_html__( 'Please tell us the reason so we can improve it.', 'bestwebsoft' ) . "' ).show();
								}
							}
						});

						/* If the user has clicked outside the window, cancel it. */
						\$modal.on( 'click', function( evt ) {
							var \$target = $( evt.target );

							/* If the user has clicked anywhere in the modal dialog, just return. */
							if ( \$target.hasClass( 'bws-modal-body' ) || \$target.hasClass( 'bws-modal-footer' ) ) {
								return;
							}

							/* If the user has not clicked the close button and the clicked element is inside the modal dialog, just return. */
							if ( ! \$target.hasClass( 'bws-modal-button-close' ) && ( \$target.parents( '.bws-modal-body' ).length > 0 || \$target.parents( '.bws-modal-footer' ).length > 0 ) ) {
								return;
							}

							/* Close the modal dialog */
							\$modal.removeClass( 'active' );
							$( 'body' ).removeClass( 'has-bws-modal' );

							return false;
						});
					}

					function BwsModalIsReasonSelected( reasonID ) {
						/* Get the selected radio input element.*/
						return ( reasonID == \$modal.find('input[type=\"radio\"]:checked').val() );
					}

					function BwsModalReset() {
						selectedReasonID = false;						

						/* Uncheck all radio buttons.*/
						\$modal.find( 'input[type=\"radio\"]' ).prop( 'checked', false );

						/* Remove all input fields ( textfield, textarea ).*/
						\$modal.find( '.bws-modal-reason-input' ).remove();

						\$modal.find( '.message' ).hide();

						/* Hide, since by default there is no selected reason.*/
						\$anonymousFeedback.hide();

						BwsModalDisableDeactivateButton();

						BwsModalShowPanel();
					}

					function BwsModalEnableDeactivateButton() {
						\$modal.find( '.bws-modal-button-deactivate' ).removeClass( 'disabled' ).show();
						\$modal.find( '.bws-modal-processing' ).hide();
					}

					function BwsModalDisableDeactivateButton() {
						\$modal.find( '.bws-modal-button-deactivate' ).addClass( 'disabled' );
					}

					function BwsModalShowPanel() {
						\$modal.find( '.bws-modal-panel' ).addClass( 'active' );
					}
				})(jQuery);";
		}

		/* add script in FOOTER */
		wp_register_script( 'bws-deactivation-feedback-dialog-boxes', '', array( 'jquery' ), false, true );
		wp_enqueue_script( 'bws-deactivation-feedback-dialog-boxes' );
		wp_add_inline_script( 'bws-deactivation-feedback-dialog-boxes', $script );
	}
}

/**
 * Called after the user has submitted his reason for deactivating the plugin.
 *
 * @since  2.1.3
 */
if ( ! function_exists( 'bws_submit_uninstall_reason_action' ) ) {
	function bws_submit_uninstall_reason_action() {
		global $bstwbsftwppdtplgns_options, $wp_version, $bstwbsftwppdtplgns_active_plugins, $current_user;

		if ( isset( $_REQUEST['bws_ajax_nonce'] ) ) {

			wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['bws_ajax_nonce'] ) ), 'bws_ajax_nonce' );

			$reason_id = isset( $_REQUEST['reason_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['reason_id'] ) ) : '';
			$basename  = isset( $_REQUEST['plugin'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['plugin'] ) ) : '';

			if ( empty( $reason_id ) || empty( $basename ) ) {
				exit;
			}

			$reason_info = isset( $_REQUEST['reason_info'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['reason_info'] ) ) : '';
			if ( ! empty( $reason_info ) ) {
				$reason_info = substr( $reason_info, 0, 255 );
			}
			$is_anonymous = isset( $_REQUEST['is_anonymous'] ) && 1 === intval( $_REQUEST['is_anonymous'] );

			$options = array(
				'product'     => $basename,
				'reason_id'   => $reason_id,
				'reason_info' => $reason_info,
			);

			if ( ! $is_anonymous ) {
				if ( ! isset( $bstwbsftwppdtplgns_options ) ) {
					$bstwbsftwppdtplgns_options = ( is_multisite() ) ? get_site_option( 'bstwbsftwppdtplgns_options' ) : get_option( 'bstwbsftwppdtplgns_options' );
				}

				if ( ! empty( $bstwbsftwppdtplgns_options['track_usage']['usage_id'] ) ) {
					$options['usage_id'] = $bstwbsftwppdtplgns_options['track_usage']['usage_id'];
				} else {
					$options['usage_id']   = false;
					$options['url']        = get_bloginfo( 'url' );
					$options['wp_version'] = $wp_version;
					$options['is_active']  = false;
					$options['version']    = $bstwbsftwppdtplgns_active_plugins[ $basename ]['Version'];
				}

				$options['email'] = $current_user->data->user_email;
			}

			/* send data */
			$raw_response = wp_remote_post(
				'https://bestwebsoft.com/wp-content/plugins/products-statistics/deactivation-feedback/',
				array(
					'method'  => 'POST',
					'body'    => $options,
					'timeout' => 15,
				)
			);

			if ( ! is_wp_error( $raw_response ) && 200 === intval( wp_remote_retrieve_response_code( $raw_response ) ) ) {
				if ( ! $is_anonymous ) {
					$response = maybe_unserialize( wp_remote_retrieve_body( $raw_response ) );

					if ( is_array( $response ) && ! empty( $response['usage_id'] ) && $response['usage_id'] !== $options['usage_id'] ) {
						$bstwbsftwppdtplgns_options['track_usage']['usage_id'] = $response['usage_id'];

						if ( is_multisite() ) {
							update_site_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
						} else {
							update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
						}
					}
				}

				echo 'done';
			} else {
				echo wp_kses_data( $response->get_error_code() ) . ': ' . wp_kses_data( $response->get_error_message() );
			}
		}
		exit;
	}
}

add_action( 'wp_ajax_bws_submit_uninstall_reason_action', 'bws_submit_uninstall_reason_action' );
