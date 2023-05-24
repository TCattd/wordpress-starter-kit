( function ( GF_Google_Analytics_Feed, $ ) {
	jQuery( document ).ready( function() {
		var urlParams = new URLSearchParams( window.location.search );
		var settingsType = urlParams.get( 'settingstype' );

		function gforms_google_analytics_create_goal() {
			// Handle visibility
			$( '#ga_event_goal_id, #ga_event_goal_thickbox, #ga_event_action_thickbox, #ga_event_label_thickbox, #ga_event_value_thickbox, #ga_event_category_thickbox' ).removeAttr( 'disabled' );
			$( '#goal_select_edit, .gforms_goal_edit_heading, #use_goal, #edit_goal' ).hide();
			$( '#goal_labels, .gforms_goal_create_heading, #create_goal, #cancel_goal' ).show();

			// Set default data for thickbox fields
			var prefix = settingsType === 'form' ? 'pagination_' : '';
			$( '#ga_event_category_thickbox' ).prop( 'value', gforms_google_analytics_feed_strings[prefix + 'category'] );
			$( '#ga_event_action_thickbox' ).prop( 'value', gforms_google_analytics_feed_strings[prefix + 'action'] );
			$( '#ga_event_label_thickbox' ).prop( 'value', gforms_google_analytics_feed_strings[prefix + 'label'] );
			$( '#ga_event_goal_thickbox' ).prop( 'value', gforms_google_analytics_feed_strings[prefix + 'goal'] ).focus();
		}

		function gforms_google_analytics_select() {
			var $selectedGoal = $( '#goal_select option:selected' );
			if ( $( '.gform-addon-gtm-manual-config').length !== 0 ) {
				$( '#ga_event_category_thickbox, #ga_event_action_thickbox, #ga_event_label_thickbox, #ga_event_goal_thickbox' ).prop( 'disabled', 'disabled' );
				$( '#goal_labels, .gforms_goal_create_heading, #create_goal' ).hide();
				$( '#use_goal, #edit_goal, #goal_select_edit' ).show();
				$( '#gform_setting_feed_event_category, #gform_setting_feed_event_action, #gform_setting_feed_event_label' ).show();
				return;
			}
			if ( 0 === $selectedGoal.length ) {
				gforms_google_analytics_create_goal();
			} else {
				// Handle visibility
				$( '#ga_event_category_thickbox, #ga_event_action_thickbox, #ga_event_label_thickbox, #ga_event_goal_thickbox' ).prop( 'disabled', 'disabled' );
				$( '#goal_labels, .gforms_goal_create_heading, #create_goal' ).hide();
				$( '#use_goal, #edit_goal, #goal_select_edit' ).show();
				$( '#gform_setting_feed_event_category, #gform_setting_feed_event_action, #gform_setting_feed_event_label' ).show();

				// Set up goal data
				var eventCategory = $selectedGoal.attr('data-category');
				var eventAction = $selectedGoal.attr('data-action');
				var eventLabel = $.trim( $selectedGoal.attr('data-label') );
				var goalId = $selectedGoal.attr('data-goal-id');

				// Set data for thickbox fields
				$( '#gaeventgoalid_thickbox' ).prop( 'value', goalId );
				$( '#ga_event_goal_thickbox' ).prop( 'value', $selectedGoal.html() );
				$( '#ga_event_category_thickbox' ).prop( 'value', eventCategory );
				$( '#ga_event_action_thickbox' ).prop( 'value', eventAction );
				$( '#ga_event_label_thickbox' ).prop( 'value', eventLabel );
			}
		}

		function remove_error_message() {
			$( '.gform-addon-conversion-tracking-modal .gform-alert' ).remove();
			$( '.gform-addon-conversion-tracking-modal [required="required"]')
				.removeAttr( 'aria-describedby' )
				.closest( '.gform-settings-input__container' )
				.removeClass( 'gform-settings-input__container--invalid' );
			$( '.gform-addon-conversion-tracking-modal .gform-settings-validation__error' ).remove();
		}

		function add_error_message( response ) {
			// Add error alert
			if ( ! $( '.gform-addon-conversion-tracking-modal .gform-alert' ).length ) {
				var errorDiv = '<div class="gform-alert gform-alert--error gform-alert--inline">';
				errorDiv += '<span class="gform-alert__icon gform-icon gform-icon--circle-close" aria-hidden="true"></span>';
				errorDiv += '<div class="gform-alert__message-wrap"><span class="gform-alert__message">' + response.data[0].message + '</span></div>';
				errorDiv += '</div>';
				$( '#goal_labels' ).prepend( errorDiv );
			}

			// If the error response received was specifically for
			// missing required values, let's add inline error helpers and handle a11y
			if ( response.data[ 0 ].code === 'missing_values' ) {
				var $requiredInputs = $( '#goal_labels').find( '[required="required"]' );
				if ( $requiredInputs ) {
					$requiredInputs.each( function() {
						var $inputWrap = $( this ).closest( '.gform-settings-input__container' );
						if ( $inputWrap.hasClass( 'gform-settings-input__container--invalid' ) ) {
							return;
						}
						if ( this.value.trim() === '' ) {
							$inputWrap.addClass( 'gform-settings-input__container--invalid' );
							$inputWrap.append( '<div class="gform-settings-validation__error" id="error-' + this.id + '">' + gforms_google_analytics_feed_strings.required + '</div>' );
							this.setAttribute( 'aria-describedby', 'error-' + this.id );
						}
					} );
				}
			}

			// Scroll back up to top of the modal and focus the firsts required input
			$( '#TB_ajaxContent' ).animate({
				scrollTop: 0,
			}, 300, function() {
				if ( $requiredInputs ) {
					$requiredInputs.get(0).focus();
				}
			} );
		}

		// Populate variables if select box is changed.
		$( '#goal_select' ).on( 'change', gforms_google_analytics_select );

		// Open up Thickbox manually
		$( '#select_goal_popup' ).on( 'click', function( e ) {
			e.preventDefault();
			tb_show( gforms_google_analytics_feed_strings.goalcreation, "#TB_inline?inlineId=thickbox_goal_select" );
		} );

		// Open manual configuration modal
		$( '#set_values_popup' ).on( 'click', function( e ) {
			e.preventDefault();
			tb_show( gforms_google_analytics_feed_strings.goalcreation, "#TB_inline?inlineId=thickbox_set_values" );
			$( '#ga_event_goal_id, #ga_event_goal_thickbox, #ga_event_action_thickbox, #ga_event_label_thickbox, #ga_event_value_thickbox, #ga_event_category_thickbox' ).removeAttr( 'disabled' );
			$( '#goal_labels, #cancel_goal' ).show();
		} );

		// Use goal and close thickbox
		$( '#use_goal' ).on( 'click', function( e ) {
			e.preventDefault();
			var $selectedGoal = $( '#goal_select option:selected' );
			$( '#select_goal_popup' ).html( gforms_google_analytics_feed_strings.edit );
			$( '#selected_goal' ).html( $selectedGoal.html() );

			// Set up goal data
			var eventCategory = $selectedGoal.attr( 'data-category' );
			var eventAction = $selectedGoal.attr( 'data-action' );
			var eventLabel = $.trim( $selectedGoal.attr( 'data-label' ) );
			var goalId = $selectedGoal.attr( 'data-goal-id' );

			// Populate data for main form fields
			var prefix = settingsType === 'form' ? 'pagination_' : '';
			$( '#' + prefix + 'gaeventgoal' ).prop( 'value', $selectedGoal.html() );
			$( '#' + prefix + 'gaeventcategory' ).prop( 'value', eventCategory );
			$( '#' + prefix + 'gaeventaction' ).prop( 'value', eventAction );
			$( '#' + prefix + 'gaeventlabel' ).prop( 'value', eventLabel );
			$( '#' + prefix + 'gaeventgoalid' ).prop( 'value', goalId );

			// Populate data preview for main form and show it
			$( '#gform_setting_feed_event_category p' ).html( eventCategory );
			$( '#gform_setting_feed_event_action p' ).html( eventAction );
			$( '#gform_setting_feed_event_label p' ).html( eventLabel );
			$( '#gform_setting_feed_event_category, #gform_setting_feed_event_action, #gform_setting_feed_event_label' ).show();

			// Close Thickbox
			self.parent.tb_remove();
		} );

		// Create Goal Interface
		$( '.goal-creation' ).on( 'click', 'button', function( e ) {
			e.preventDefault();
			gforms_google_analytics_create_goal();
		} );

		// Edit Goal Interface
		$( '#edit_goal' ).on( 'click', function( e ) {
			e.preventDefault();

			// Update visibility
			$( '#use_goal, #edit_goal, #goal_select, #goal_select_edit, .gforms_goal_create_heading' ).hide();
			$( '#ga_event_category_thickbox, #ga_event_action_thickbox, #ga_event_label_thickbox, #ga_event_goal_thickbox' ).removeAttr( 'disabled' );
			$( '#goal_labels, .gforms_goal_edit_heading, #update_goal, #cancel_goal' ).show();
			$( '#ga_event_goal_thickbox' ).focus();
		} );

		// Cancel Goal Interface
		$( '#cancel_goal' ).on( 'click', function( e ) {
			e.preventDefault();

			// Update visibility
			$( '#use_goal, #edit_goal, #goal_select, #goal_select_edit' ).show();
			$( '#goal_labels, #update_goal, #cancel_goal, #create_goal' ).hide();
			$( '#ga_event_category_thickbox, #ga_event_action_thickbox, #ga_event_label_thickbox, #ga_event_goal_thickbox' ).prop( 'disabled', 'disabled' );
		} );

		$( '#close_manual_config' ).on( 'click', function( e ) {
			e.preventDefault();

			// Close Thickbox
			self.parent.tb_remove();
		} );

		$( '#update_goal' ).on( 'click', function( e ) {
			e.preventDefault();
			var $button = $( this );

			// Update visibility
			$( '#cancel_goal' ).hide();
			$( '#update_goal' )
				.prop('disabled', 'disabled' )
				.prop( 'value', gforms_google_analytics_feed_strings.saving );

			// Get thickbox field values
			var goalName = $.trim( $( '#ga_event_goal_thickbox' ).val() );
			var eventCategory = $.trim( $( '#ga_event_category_thickbox' ).val() );
			var eventAction = $.trim( $( '#ga_event_action_thickbox' ).val() );
			var eventLabel = $.trim( $( '#ga_event_label_thickbox' ).val() );
			var goalId = $.trim( $( '#gaeventgoalid_thickbox' ).val() );
			var nonce = $( '#create_ga_goal' ).val();

			// Pass our data to update on to Google
			$.post(
				ajaxurl,
				{
					action: 'update_analytics_goal',
					nonce: nonce,
					eventcategory: eventCategory,
					eventaction: eventAction,
					eventlabel: eventLabel,
					goal: goalName,
					goalId: goalId,
				},
				function( response ) {
					if ( response.success === true ) {
						remove_error_message();

						$( '#selected_goal').html( response.data.goal_name );

						// Populate data for main form fields
						var prefix = settingsType === 'form' ? 'pagination_' : '';
						$( '#' + prefix + 'gaeventgoal' ).prop( 'value', response.data.goal_name );
						$( '#' + prefix + 'gaeventcategory' ).prop( 'value', response.data.event_category );
						$( '#' + prefix + 'gaeventaction' ).prop( 'value', response.data.event_action );
						$( '#' + prefix + 'gaeventlabel' ).prop( 'value', response.data.event_label );

						// Populate data preview for main form
						$( '#gform_setting_feed_event_category p' ).html( response.data.event_category );
						$( '#gform_setting_feed_event_action p' ).html( response.data.event_action );
						$( '#gform_setting_feed_event_label p' ).html( response.data.event_label );
						$( '#select_goal_popup' ).html( gforms_google_analytics_feed_strings[ prefix + 'edit' ] );
						$button.val( gforms_google_analytics_feed_strings[ prefix + 'savinganduse' ] ).removeAttr( 'disabled' );

						// Set data for goal selection thickbox field
						$( '#goal_select option' ).each( function() {
							if ( $( this ).attr( 'data-goal-id' ) == response.data.goal_id ) {
								$( this ).html( response.data.goal_name );
								$( this ).attr( 'data-action', response.data.event_action );
								$( this ).attr( 'data-label', response.data.event_label );
								$( this ).attr( 'data-category', response.data.event_category );
								$( this ).attr( 'selected', 'selected' );;
							}
						} );

						// Update visibility
						$( '#use_goal, #edit_goal, #goal_select, #goal_select_edit' ).show();
						$( '#goal_labels, #update_goal, #cancel_goal' ).hide();
						$( '#ga_event_category_thickbox, #ga_event_action_thickbox, #ga_event_label_thickbox, #ga_event_goal_thickbox' ).prop( 'disabled', 'disabled' );
					} else {
						add_error_message( response );
						$button.val( gforms_google_analytics_feed_strings.pagination_savegoal ).removeAttr( 'disabled' );
					}
				},
				'json'
			);
		} );

		// Create the event goal
		$( '#create_goal' ).on( 'click', function( e ) {
			e.preventDefault();
			var $button = $( this );

			$button.val( gforms_google_analytics_feed_strings.creating ).attr( 'disabled', 'disabled' );

			// Get thickbox field values
			var goalName = $.trim( $( '#ga_event_goal_thickbox' ).val() );
			var eventCategory = $.trim( $( '#ga_event_category_thickbox' ).val() );
			var eventAction = $.trim( $( '#ga_event_action_thickbox' ).val() );
			var eventLabel = $.trim( $( '#ga_event_label_thickbox' ).val() );
			var nonce = $( '#create_ga_goal' ).val();

			// Pass our data to create a new goal on to Google
			$.post(
				ajaxurl,
				{
					action: 'create_analytics_goal',
					nonce: nonce,
					eventcategory: eventCategory,
					eventaction: eventAction,
					eventlabel: eventLabel,
					goal: goalName,
				},
				function( response ) {
					if ( response.success === true ) {
						remove_error_message();

						// Populate data for main form fields
						var prefix = settingsType === 'form' ? 'pagination_' : '';
						$( '#' + prefix + 'gaeventgoalid' ).prop( 'value', response.data.goal_id );
						$( '#' + prefix + 'gaeventgoal' ).prop( 'value', goalName );
						$( '#' + prefix + 'gaeventcategory' ).prop( 'value', eventCategory );
						$( '#' + prefix + 'gaeventaction' ).prop( 'value', eventAction );
						$( '#' + prefix + 'gaeventlabel' ).prop( 'value', eventLabel );

						// Populate data preview for main form
						$( '#gform_setting_feed_event_category p' ).html( eventCategory );
						$( '#gform_setting_feed_event_action p' ).html( eventAction );
						$( '#gform_setting_feed_event_label p' ).html( eventLabel );
						$( '#ga_goals' ).append( response.data.option );

						// Refresh data for goal selection thickbox field
						gforms_google_analytics_select();
						$( '#select_goal_popup' ).html( gforms_google_analytics_feed_strings.edit );
						$( '#selected_goal' ).html( goalName );

						// Update visibility
						$( '#cancel_goal' ).hide();
						$button.val( gforms_google_analytics_feed_strings.goalcreated ).fadeOut( 'slow', function() {
							$button.val( gforms_google_analytics_feed_strings.creategoal ).removeAttr( 'disabled' );
						} );
					} else {
						add_error_message( response );
						$button.val( gforms_google_analytics_feed_strings.pagination_creategoal ).removeAttr( 'disabled' );
					}
				},
				'json'
			);
		} );

		// Set the values when manually configuring
		$( '#set_event_values' ).on( 'click', function( e ) {
			e.preventDefault();
			var $button = $( this );

			// Get thickbox field values
			var goalName = $.trim( $( '#ga_event_goal_thickbox' ).val() );
			var eventCategory = $.trim( $( '#ga_event_category_thickbox' ).val() );
			var eventAction = $.trim( $( '#ga_event_action_thickbox' ).val() );
			var eventLabel = $.trim( $( '#ga_event_label_thickbox' ).val() );
			var nonce = $( '#create_ga_goal' ).val();

			var prefix = settingsType === 'form' ? 'pagination_' : '';
			$( '#' + prefix + 'gaeventgoalid' ).prop( 'value', null );
			$( '#' + prefix + 'gaeventgoal' ).prop( 'value', goalName );
			$( '#' + prefix + 'gaeventcategory' ).prop( 'value', eventCategory );
			$( '#' + prefix + 'gaeventaction' ).prop( 'value', eventAction );
			$( '#' + prefix + 'gaeventlabel' ).prop( 'value', eventLabel );

			// Populate data preview for main form and show it
			$( '#selected_goal' ).html( goalName );
			$( '#gform_setting_feed_event_category p' ).html( eventCategory );
			$( '#gform_setting_feed_event_action p' ).html( eventAction );
			$( '#gform_setting_feed_event_label p' ).html( eventLabel );
			$( '#gform_setting_feed_event_category, #gform_setting_feed_event_action, #gform_setting_feed_event_label' ).show();

			// Close Thickbox
			self.parent.tb_remove();
		} );

		// Get the selected option and populate variables.
		gforms_google_analytics_select();

	} );

}( window.GF_Google_Analytics_Feed = window.GF_Google_Analytics_Feed || {}, jQuery ) );
