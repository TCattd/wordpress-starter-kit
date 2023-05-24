( function ( GF_Google_Analytics_Admin, $ ) {
	jQuery( document ).ready( function() {
		// Main form wrapper.
		var $form = $( '#gform-settings' );

		// Handle disconnects from the admin.
		var $disconnect = $( '.gfga-disconnect' );
		if ( $disconnect.length > 0 ) {
			$disconnect.on( 'click', function( e ) {
				e.preventDefault();
				$disconnect.html( google_analytics_admin_strings.disconnect );

				var url_params = wpAjax.unserialize( e.target.href );
				var nonce_value = url_params.nonce;

				// Perform Ajax request.
				$.post(
					ajaxurl,
					{
						action: 'disconnect_account',
						nonce: nonce_value,
					},
					function( response ) {
						window.location.href = window.location.href;
					}
				);
			} );
		}

		// Disable submit button and only reenable if required fields are filled out. Necessary to work with new loader.
		var $action = $form.find( 'input[name="gfgaaction"]');
		if ( $action.length !== 0 ) {
			var $submit_button = $form.find( ':submit' );
			$submit_button.prop( 'disabled', true );
		}
		$( '#gform-settings-section-google-analytics-settings' ).on( 'change', ( 'input, select' ), function( event ) {
			var account_id = $form.find( 'select[name="gaproperty"]' ).find(':selected').val();
			var view = $( '#ga-views select :selected' ).val();
			var manual = $( 'input[name=_gform_setting_gamanual]' ).val();
			var ga_code = $( '#ga_ua_code' ).val();
			var manual_account_id = $( '#ga_account_id' ).val();
			var manual_view = $( '#ga_view' ).val();
			var gtm_container = $form.find( 'select[name="gtmproperty"]' ).find( ':selected' ).val();
			if ( ! gtm_container ) {
				var gtm_container = $form.find( '#container' ).val();
			}
			var workspace = $form.find( 'select[name="gaworkspace"]' ).val();
			if ( ! workspace ) {
				var workspace = $form.find( '#workspace' ).val();
			}
			var $submit_button = $form.find( ':submit' );
			if ( 'ga' === $action.val() ) {
				if ( 'self_config' == account_id && ga_code && manual_account_id && manual_view ) {
					$submit_button.prop( 'disabled', false );
				} else if ( 'manual' == account_id ) {
					$submit_button.prop( 'disabled', false );
				} else if ( account_id && view ) {
					$submit_button.prop( 'disabled', false );
				} else {
					$submit_button.prop( 'disabled', true );
				}
			}
			if ( 'gtm' === $action.val() ) {
				if ( account_id === 'manual' && gtm_container && workspace ) {
					$submit_button.prop( 'disabled', false );
				} else if ( account_id && view && gtm_container && workspace ) {
					$submit_button.prop( 'disabled', false );
				} else {
					$submit_button.prop( 'disabled', true );
				}
			}
		})

		// Handle form submission on connect screen
		$form.on( 'submit', function( e ) {

			// Determining if we're just connecting for the first time.
			if ( $form.find( 'input[value="google_analytics_setup"]' ).length == 1 ) {
				// is_postback
				e.preventDefault();

				// Set l18n.
				var $save_button = $form.find( '#gform-settings-save' );
				var $mode = $form.find( '[name="_gform_setting_mode"]:checked').val();
				$save_button.prop( 'value', google_analytics_admin_strings.redirect ).prop( 'disabled', 'disabled' );

				// Get nonce.
				var nonce_value = $form.find( 'input[name="gfganonce"]').val();

				// Perform Ajax request.
				$.post(
					ajaxurl,
					{
						action: 'redirect_to_api',
						nonce: nonce_value,
						mode: $mode,
					},
					function( response ) {
						if ( ! response.data.errors ) {
							window.location.href = response.data.redirect;
						}
					},
					'json'
				);

				return;
			}

			// Determine if we're executing in the correct form
			var $action = $form.find( 'input[name="gfgaaction"]');
			if( $action.length == 0 ) {
				return;
			}

			// Get the nonce
			var nonce = $form.find( 'input[name="gfganonce"]' ).val();
			var token = $form.find('input[name="gfga_token"]' ).val();
			var refresh = $form.find('input[name="gfga_refresh"]' ).val();
			if ( 'ga' === $action.val() ) {
				e.preventDefault();

				// Update the submit button text to show connecting
				var $submit_button = $form.find( ':submit' );
				$submit_button.val( google_analytics_admin_strings.connecting ).prop( 'disabled', 'disabled' );

				// We're in Google Analytics mode
				var $select = $form.find( 'select[name="gaproperty"]' );
				if ( $select.val() == 'self_config' ) {
					var ga_code = $( '#ga_ua_code' ).val();
					var account_id = $( '#ga_account_id' ).val();
					var account_name = $( '#ga_account_name' ).val();
					var view = $( '#ga_view' ).val();
					var view_name = $( '#ga_view_name' ).val();
				} else {
					var ga_code = $select.val();
					var account_id = $select.find(':selected').data( 'account-id' );
					var account_name = $select.find(':selected').data('account-name');

					// Get the View
					var view = $( '#ga-views select :selected' ).val();
					var view_name = $( '#ga-views select :selected' ).data( 'view-name' );
				}

				$.post(
					ajaxurl,
					{
						action: 'save_google_analytics_data',
						nonce: nonce,
						token: token,
						refresh: refresh,
						account_id: account_id,
						account_name: account_name,
						ga_code: ga_code,
						view: view,
						view_name: view_name
					},
					function( response ) {
						window.location.href = response;
					}
				);
			}
			if ( 'gtm' === $action.val() ) {
				// We're in Google Tag Manager mode
				e.preventDefault();

				// Update the submit button text to show connecting
				var $submit_button = $form.find( ':submit' );
				$submit_button.val( google_analytics_admin_strings.connecting ).prop( 'disabled', 'disabled' );

				// Get Google Analytics data
				var $select = $form.find( 'select[name="gaproperty"]' );
				var ga_code = $select.val();
				var selected_account = $select.find( ':selected' ).val();
				var account_id = $select.find( ':selected' ).data( 'account-id' );
				var account_name = $select.find( ':selected' ).data( 'account-name' );

				// Get the View
				var view = $( '#ga-views select :selected' ).val();
				var view_name = $( '#ga-views select :selected' ).data( 'view-name' );

				// Get GTM Data
				// GTM account ID
				var $gtm_select = $form.find( 'select[name="gtmproperty"]' );
				var gtm_account_id = $gtm_select.find(':selected').data( 'account-id' );

				// GTM container
				var $gtm_container_select = $form.find( 'select[name="gacontainer"]' );
				var gtm_path = $gtm_container_select.find( ':selected' ).data( 'path' );
				var gtm_container = $gtm_container_select.find( ':selected' ).val();
				if ( ! gtm_container ) {
					var gtm_container = $form.find( '#container' ).val();
				}
				if ( ! gtm_path ) {
					var container_id = $form.find( '#container_id' ).val();
					var gtm_path = 'accounts/' + gtm_account_id + '/containers/' + container_id;
				}

				// Get Google Workspace data
				var $select = $form.find( 'select[name="gaworkspace"]' );
				var workspace = $select.val();
				if ( ! workspace ) {
					var workspace = $form.find( '#workspace' ).val();
				}


				// Get GTM Opt-out Data
				var gtm_auto_create = $form.find( 'input[name="_gform_setting_gtm_auto_create"]' );
				if ( gtm_auto_create.val() == 1 ) {
					gtm_auto_create = 'on';
				} else {
					gtm_auto_create = gtm_auto_create.val();
				}

				$.post(
					ajaxurl,
					{
						action: 'save_google_tag_manager_data',
						nonce: nonce,
						token: token,
						refresh: refresh,
						account_id: account_id,
						account_name: account_name,
						ga_code: ga_code,
						gtm_account_id: gtm_account_id,
						gtm_path: gtm_path,
						gtm_container: gtm_container,
						gtm_auto_create: gtm_auto_create,
						gtm_workspace: workspace,
						view: view,
						view_name: view_name
					},
					function( response ) {
						if ( false === response.success ) {
							$( '#gform-page-loader-mask' ).hide();
							$( '#gform-settings' ).before( '<div class="alert gforms_note_error" role="alert">' + response.data[0].message + '</div>' );
							$( '.alert' ).get(0).scrollIntoView();
							$( '#gform_setting_container_id, #gform_setting_workspace, #gform_setting_container' ).find( '.gform-settings-input__container' ).addClass( 'gform-settings-input__container--invalid' );
						} else {
							window.location.href = response;
						}
					}
				);
			}
		} );

		// Get views for selected UA account.
		$( '#gaproperty' ).on( 'change', function( e ) {
			var $option = $( this ).find( ':selected' );
			if ( $option.val() === 'self_config' ) {
				$( '#gform_setting_ga_ua_code, #gform_setting_ga_account_id, #gform_setting_ga_account_name, #gform_setting_ga_view, #gform_setting_ga_view_name, #gform_setting_manual_instructions' ).show();
			} else {
				$( '#gform_setting_ga_ua_code, #gform_setting_ga_account_id, #gform_setting_ga_account_name, #gform_setting_ga_view, #gform_setting_ga_view_name, #gform_setting_manual_instructions' ).hide();
			}
			if ( $option.val() === 'manual' ) {
				$( '#ga-views' ).html('');
				return;
			}
			var ua_code = $option.data( 'ua-code' );
			var profile_id = $option.data( 'account-id' );
			var token = $option.data( 'token' );
			var nonce = $( 'body' ).find( 'input[name="gfganonce"]' ).val();
			$( '#ga-views' ).html( '<br /><img src="' + google_analytics_admin_strings.spinner + '" />' );
			$.post(
				ajaxurl,
				{
					action: 'get_ga_views',
					account_id: profile_id,
					ga_code: ua_code,
					nonce: nonce,
					token: token,
				},
				function( response ) {
					$( '#ga-views' ).html( response );
				}
			);
		} );
		// Get containers for selected GTM account.
		$( '#gtmproperty' ).on( 'change', function( e ) {
			var $option = $( this ).find( ':selected' );
			var accountId = $option.data( 'accountId' );
			var path = $option.data( 'path' );
			var token = $option.data( 'token' );
			var nonce = $( 'body' ).find( 'input[name="gfganonce"]' ).val();
			$( '#gtm-containers' ).html( '<br /><img src="' + google_analytics_admin_strings.spinner + '" />' );
			$.post(
				ajaxurl,
				{
					action: 'get_gtm_containers',
					accountId: accountId,
					path: path,
					nonce: nonce,
					token: token,
				},
				function( response ) {
					if( response.success ) {
						$( '#gtm-containers' ).html( response.body );
					} else {
						$( '#gform_setting_container' ).show();
						$( '#gform_setting_container_id' ).show();
						$( '#gform_setting_workspace' ).show();
						$( '#gtm-containers' ).hide();
					}
				}
			);
		} );
		// Get views for selected UA account.
		$( document ).on( 'change', '#gacontainer', function( e ) {
			var $option = $( this ).find( ':selected' );
			var path = $option.data( 'path' );
			var token = $option.data( 'token' );
			var nonce = $( 'body' ).find( 'input[name="gfganonce"]' ).val();
			$( '#gtm-workspaces' ).html( '<br /><img src="' + google_analytics_admin_strings.spinner + '" />' );
			$.post(
				ajaxurl,
				{
					action: 'get_gtm_workspaces',
					path: path,
					nonce: nonce,
					token: token,
				},
				function( response ) {
					if( response.success ) {
						$( '#gtm-workspaces' ).html( response.body );
					} else {
						$( '#gform_setting_workspace' ).show();
						$( '#gtm-workspaces' ).hide();
					}
				}
			);
		} );
	} );
}( window.GF_Google_Analytics_Admin = window.GF_Google_Analytics_Admin || {}, jQuery ) );
