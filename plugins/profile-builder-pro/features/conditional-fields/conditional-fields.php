<?php
/*
Description: Conditional Fields
*/

Class PB_Conditional_Fields {

    /*
     * The id of the last deleted field
     *
     */
    public $deleted_element_id = null;
    public $wppb_manage_fields = null;

    /*
     * Constructor
     *
     */
	public function __construct() {

		/* initialize the property with what we store in the 'wppb_manage_fields' option */
		$this->wppb_manage_fields = get_option( 'wppb_manage_fields' );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		add_filter( 'wppb_manage_fields', array( $this, 'add_conditional_fields' ) );


		add_action( 'wck_ajax_add_form_wppb_manage_fields', array( $this, 'set_js_wppb_manage_fields' ) );
		add_action( 'wck_refresh_list_wppb_manage_fields', array( $this, 'set_js_wppb_manage_fields' ) );
		add_action( 'wck_refresh_entry_wppb_manage_fields', array( $this, 'set_js_wppb_manage_fields' ) );
		add_filter( 'wck_metabox_content_wppb_manage_fields', array( $this, 'set_js_wppb_manage_fields' ) );

		add_action( 'wck_after_adding_form_wppb_manage_fields', array( $this, 'init_conditional_logic_option' ) );


		/* frontend handling */
		add_action( 'wp_footer', array( $this, 'frontend_conditional_handle' ), 99 );
		/**
		 * add required check filters for every field
		 */
		if( !empty( $this->wppb_manage_fields ) && !is_admin() ) {
			foreach( $this->wppb_manage_fields as $field ) {
				/* add it on a later priority */
				add_filter( 'wppb_check_form_field_' . Wordpress_Creation_Kit_PB::wck_generate_slug($field['field']), array( $this, 'bypass_required_if_needed' ), 20, 4 );
			}
		}

        add_action( 'wck_before_remove_meta', array( $this, 'cache_deleted_element_id' ), 10, 3 );
        add_filter( 'pre_update_option', array( $this, 'remove_field_from_rules' ), 10, 3 );

        add_filter( 'wck_pre_displayed_value_wppb_manage_fields_element_conditional-logic-enabled', array( $this, 'conditional_logic_icon' ) );

        $wppb_toolbox_forms_settings = get_option( 'wppb_toolbox_forms_settings' );
        if( isset( $wppb_toolbox_forms_settings['ajax-conditional-logic'] ) && $wppb_toolbox_forms_settings['ajax-conditional-logic'] === 'yes'){
            add_action("wp_ajax_wppb_conditional_logic", array( &$this, 'wppb_conditional_logic_ajax') );
            add_action("wp_ajax_nopriv_wppb_conditional_logic", array( &$this, 'wppb_conditional_logic_ajax') );
        }

	}

	/*
     * Enqueue scripts in the admin area
     *
     */
	public function enqueue_admin_scripts() {

		if( !empty( $_GET['page'] ) && $_GET['page'] === 'manage-fields' && defined( 'PROFILE_BUILDER_VERSION' ) ) {
			wp_enqueue_script( 'pb-conditional-fields', plugin_dir_url( __FILE__ ) . 'assets/js/conditional-fields.js', array( 'jquery', 'wppb-manage-fields-live-change' ) );
			wp_enqueue_style( 'pb-conditional-fields', plugin_dir_url( __FILE__ ) . 'assets/css/conditional-fields.css' );
		}

	}


	/*
     * Adds the field options for the conditional logic enable/disable checkbox and for the conditional logic rules
     *
     * @param array $fields - fields in manage fields
     *
     */
	public function add_conditional_fields( $fields ) {

		array_push( $fields, array( 'type' => 'checkbox', 'slug' => 'conditional-logic-enabled', 'title' => __( 'Conditional Logic', 'profile-builder' ), 'options' => array( '%Enable conditional logic%yes' ) ) );
		array_push( $fields, array( 'type' => 'text', 'slug' => 'conditional-logic', 'title' => __( 'Conditional Rules', 'profile-builder' ) ) );

		return $fields;

	}


	/*
     * Gets fields saved in the manage_fields option and adds them to the JS conditional logic object
     *
     */
	public function set_js_wppb_manage_fields( $fields_or_id ) {

		$wppb_manage_fields = apply_filters( 'wppb_cf_form_fields', get_option( apply_filters( 'wppb_cf_option_meta', 'wppb_manage_fields' ), array() ) );

        // Don't allow all fields to be available
        $fields_not_allowed = apply_filters( 'wppb_conditional_fields_not_allowed', array(
            'Default - Name (Heading)',
            'Default - Contact Info (Heading)',
            'Default - About Yourself (Heading)',
            'Default - Password',
            'Default - Repeat Password',
            'Heading',
            'WYSIWYG',
            'Checkbox (Terms and Conditions)',
            'Upload',
            'Avatar',
            'reCAPTCHA',
            'HTML',
            'Map'
        ), $wppb_manage_fields );

        if( !empty( $wppb_manage_fields ) ) {
            foreach( $wppb_manage_fields as $key => $field ) {
                if( in_array( $field['field'], $fields_not_allowed ) ) {
                    unset($wppb_manage_fields[$key]);
                }

                // Add the actual names for the Select (User Role) field
                if( $field['field'] === 'Select (User Role)' ) {
                    global $wp_roles;
                    $user_role_names = array();
                    foreach ( explode( ', ', $field['user-roles'] ) as $user_role_slug ) {
                        if (isset($wp_roles->roles[$user_role_slug]['name'])) {
                            $user_role_names[] = $wp_roles->roles[$user_role_slug]['name'];
                        } else {
                            $user_role_names[] = $user_role_slug;
                        }
                    }
                    $wppb_manage_fields[$key]['user-role-names'] = implode( ',', $user_role_names );
                }
            }
        }

		// Set the manage fields in JS
		$return = '<script type="text/javascript">wppb_conditional_logic.set_manage_fields(' . json_encode( $wppb_manage_fields ) . ');</script>';

		if( $fields_or_id === 0 )
			echo $return;//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		else
			return $fields_or_id . $return;

	}


	/*
     * Initializes the conditional logic options in JS for a field when the edit form of the field gets rendered
     *
     */
	public function init_conditional_logic_option( $id ) {

		echo '<script type="text/javascript"> jQuery("#container_wppb_manage_fields input[name=conditional-logic-enabled]").each( function() { wppb_conditional_logic.show_conditional_option( jQuery(this) ) });</script>';
	}

	/* function that handles the js on the front end for fields */
	function frontend_conditional_handle(){
        global $wppb_register_edit_profile_shortcode_on_front;
		if( !empty( $wppb_register_edit_profile_shortcode_on_front ) && $wppb_register_edit_profile_shortcode_on_front === true ){

            $wppb_toolbox_forms_settings = get_option('wppb_toolbox_forms_settings');
            if (isset($wppb_toolbox_forms_settings['ajax-conditional-logic']) && $wppb_toolbox_forms_settings['ajax-conditional-logic'] === 'yes' && defined('WPPB_PAID_PLUGIN_URL') ) {
                wp_enqueue_script('wppb-conditional-logic', WPPB_PAID_PLUGIN_URL . 'features/conditional-fields/assets/js/script-conditional-logic.js', array('jquery'), PROFILE_BUILDER_VERSION, true);
                wp_localize_script('wppb-conditional-logic', 'wppb_conditional_ajax', array('ajaxUrl' => admin_url('admin-ajax.php')));
                wp_print_scripts('wppb-conditional-logic');
            }
		    else {
                if (!empty($this->wppb_manage_fields)) {

                    /* output the js rules */
                    echo '<script type="text/javascript">
						function wppbHideActions( liElement ){
							jQuery( "input[type=\'text\'], input[type=\'email\'], input[type=\'number\'], input[type=\'hidden\'], textarea, select option, input[type=\'checkbox\'], input[type=\'radio\'], input[type=\'url\']", jQuery( liElement ) ).each( function(){
								/* do this for cascading conditional fields meaning if a field is hidden and another field is dependent on it than hide it as well */
								if( jQuery( this )[0].hasAttribute("value") ){
								    /* keep the value for the repeater field hidden count so we do not loose the values when we hide the repeater fields and show them again */
								    if( !( jQuery( this )[0].hasAttribute("name") && jQuery( this ).attr("name").substring(0, 20) == "wppb_repeater_field_" ) ){
                                        jQuery( this ).attr( "conditional-value", jQuery( this ).attr("value") );
                                        jQuery( this ).removeAttr("value");
                                        jQuery( this ).trigger("change");
									}
								}
								else{
									jQuery( this ).attr( "conditional-value", jQuery( this ).text() );
									jQuery( this ).trigger("change");
								}

								/* we do this so we do not send them in $_POST so we do not change their value */
								/* the repeater field hidden count needs to be in the $_POST */
								// for select the name attribute is on the parent not the option tag
								if( jQuery( this ).is("option") )
									current = jQuery( this ).parent();
								else
									current = jQuery( this );
								if( typeof current[0] != "undefined" && current[0].hasAttribute("name") && current.attr("name").substring(0, 20) != "wppb_repeater_field_" ){
									current.attr( "conditional-name", current.attr("name") );
									current.removeAttr("name");
								}

								// Trigger a custom event that will remove the HTML attribute -required- for hidden fields. This is necessary for browsers to allow form submission.
								jQuery( this ).trigger( "wppbRemoveRequiredAttributeEvent" );
							} )
						}
						function wppbShowActions( liElement ){
							jQuery( "input[type=\'text\'], input[type=\'email\'], input[type=\'number\'], input[type=\'hidden\'], textarea, select option, input[type=\'checkbox\'], input[type=\'radio\'], input[type=\'url\']", jQuery( liElement ) ).each( function(){
								if( jQuery( this )[0].hasAttribute("conditional-value") ){
									jQuery( this ).attr( "value", jQuery( this ).attr("conditional-value") );
									jQuery( this ).removeAttr("conditional-value");
									jQuery( this ).trigger("change");

									// Trigger a custom event that will add the HTML attribute -required- back again, for previously hidden fields.
									jQuery( this ).trigger( "wppbAddRequiredAttributeEvent" );
								}

								// for select the name attribute is on the parent not the option tag
								if( jQuery( this ).is("option") )
									current = jQuery( this ).parent();
								else
									current = jQuery( this );
								if( current[0].hasAttribute("conditional-name") ){
									current.attr( "name", current.attr("conditional-name") );
									current.removeAttr("conditional-name");
								}

                                // for subscription plans field, we want to select the default selected plan from settings; since all the fields use the same name, by default, only the last one will be selected
                                // for future reference: when multiple fields show, its fine that only one of them will have the default plan selected ( you can only buy 1 plan at time )
                                if( jQuery(liElement).hasClass("wppb-subscription-plans") ){

                                    jQuery( liElement ).find( "input[name=\'subscription_plans\']" ).each( function( index, item ){

                                        if( typeof jQuery(item).data("default-selected") != "undefined" && jQuery(item).data("default-selected") == true ){
                                            jQuery(item).prop("checked","checked")
                                            jQuery(item).trigger("click")
                                        }

                                    })

                                }

							} )
						}
					 </script>';

                    foreach ($this->wppb_manage_fields as $field) {
                        if (!empty($field["conditional-logic-enabled"]) && !empty($field["conditional-logic"]) && $field["conditional-logic-enabled"] === 'yes') {
                            $field_conditional_logic = json_decode($field["conditional-logic"], true);

                            $check_fields_in_DOM = array();
                            $check_fields_change = array();
                            $check_fields_conditions = array();

                            if (!empty($field_conditional_logic['rules'])) {
                                foreach ($field_conditional_logic['rules'] as $rule) {
                                    if ($rule['operator'] == 'is')
                                        $operator = '!=';
                                    else if ($rule['operator'] == 'is not')
                                        $operator = '==';

                                    /* check if field in DOM */
                                    $check_fields_in_DOM[] = 'jQuery( "#wppb-form-element-' . $rule['field'] . '" ).length != 0';
                                    /* js string for field change */
                                    $check_fields_change[] = '#wppb-form-element-' . $rule['field'] . ' input, #wppb-form-element-' . $rule['field'] . ' textarea, #wppb-form-element-' . $rule['field'] . ' select';
                                    /* js string to check if the condition is met */
                                    $check_fields_conditions[] = 'jQuery.inArray( "' . $rule['value'] . '", jQuery( "#wppb-form-element-' . $rule['field'] . ' input[type=\"text\"], #wppb-form-element-' . $rule['field'] . ' input[type=\"email\"], #wppb-form-element-' . $rule['field'] . ' input[type=\"number\"], #wppb-form-element-' . $rule['field'] . ' input[type=\"hidden\"], #wppb-form-element-' . $rule['field'] . ' textarea, #wppb-form-element-' . $rule['field'] . ' select option:selected, #wppb-form-element-' . $rule['field'] . ' input[type=\"checkbox\"]:checked, #wppb-form-element-' . $rule['field'] . ' input[type=\"radio\"]:checked, #wppb-form-element-' . $rule['field'] . ' input[type=\"url\"]" ).map(function(){return jQuery(this).val(); }).get() ) ' . $operator . ' -1';
                                }
                            }

                            /* combine all the rules together */
                            if ($field_conditional_logic['logic_type'] == 'all') {
                                $check_fields_in_DOM_all = implode(' && ', $check_fields_in_DOM);
                                $check_fields_conditions_all = implode(' && ', $check_fields_conditions);
                            } else if ($field_conditional_logic['logic_type'] == 'any') {
                                $check_fields_in_DOM_all = implode(' || ', $check_fields_in_DOM);
                                $check_fields_conditions_all = implode(' || ', $check_fields_conditions);
                            }

                            $check_fields_change_all = implode(', ', $check_fields_change);
                            // phpcs:disable
                            /* output the js rules */
                            $wppb_toolbox_forms_settings = get_option('wppb_toolbox_forms_settings');
                            if (!isset( $wppb_toolbox_forms_settings['ajax-conditional-logic'] ) || $wppb_toolbox_forms_settings['ajax-conditional-logic'] === 'no'):
                                echo '<script type="text/javascript">
								if( jQuery( "#wppb-form-element-' . $field['id'] . '" ).length != 0 ){

									if( ' . $check_fields_in_DOM_all . ' ){

										if( "show" == "' . $field_conditional_logic['action_type'] . '" ){
											jQuery( "#wppb-form-element-' . $field['id'] . '" ).hide( 0, function(){ wppbHideActions(this) });
										}

										if( ' . $check_fields_conditions_all . ' ){
											if( "show" == "' . $field_conditional_logic['action_type'] . '" ){
												jQuery( "#wppb-form-element-' . $field['id'] . '" ).show( 0, function(){ wppbShowActions(this) });
											}
											else if( "hide" == "' . $field_conditional_logic['action_type'] . '" ){
												jQuery( "#wppb-form-element-' . $field['id'] . '" ).hide( 0, function(){ wppbHideActions(this) });
											}
										}

										jQuery( "' . $check_fields_change_all . '" ).on( "change keyup", function(){
											if( ' . $check_fields_conditions_all . ' ){
												if( "show" == "' . $field_conditional_logic['action_type'] . '" ){
													jQuery( "#wppb-form-element-' . $field['id'] . '" ).show( 0, function(){ wppbShowActions(this) });
												}
												else if( "hide" == "' . $field_conditional_logic['action_type'] . '" ){
													jQuery( "#wppb-form-element-' . $field['id'] . '" ).hide( 0, function(){ wppbHideActions(this) });
												}
											}
											else{
												if( "show" == "' . $field_conditional_logic['action_type'] . '" ){
													if( jQuery( "#wppb-form-element-' . $field['id'] . '" ).css("display") != "none" )
														jQuery( "#wppb-form-element-' . $field['id'] . '" ).hide( 0, function(){ wppbHideActions(this) });
												}
												else if( "hide" == "' . $field_conditional_logic['action_type'] . '" ){
													if( jQuery( "#wppb-form-element-' . $field['id'] . '" ).css("display") == "none" )
														jQuery( "#wppb-form-element-' . $field['id'] . '" ).show( 0, function(){ wppbShowActions(this) });
												}
											}
										});
									}

								}
							  </script>';
                            endif;
                            // phpcs:enable
                        }
                    }
                }
            }
		}
	}

    function wppb_conditional_logic_ajax(){

        $args = array(
            'form_type' 			=> isset( $_POST['formType'] ) ? sanitize_text_field( $_POST['formType'] ) : '',
            'form_fields' 			=> array(),
            'form_name' 			=> isset( $_POST['formName'] ) ? sanitize_text_field( $_POST['formName'] ): '',
            'role' 					=> '', //used only for the register-form settings
            'redirect_url'          => '',
            'logout_redirect_url'   => '', //used only for the register-form settings
            'redirect_priority'		=> 'normal',
            'ID'                    => isset( $_POST['formID'] ) ? sanitize_text_field( $_POST['formID'] ): ''
        );
        $args['form_fields'] = apply_filters( 'wppb_change_form_fields', get_option( 'wppb_manage_fields' ), $args );

        $form_fields = apply_filters( 'wppb_form_fields', $args['form_fields'], array( 'global_request' => array(), 'context' => 'validate_frontend', 'form_type' => $args['form_type'] , 'role' => $args['role'], 'user_id' => '' ) );
        $ret = array();
        $fields = isset( $_POST['data'] ) ? json_decode(stripslashes($_POST['data']), true): array();//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

        if( !empty($form_fields) ) {
            foreach ($form_fields as $form_field) {
                if (isset($form_field['conditional-logic-enabled']) && $form_field['conditional-logic-enabled'] === 'yes') {
                    $logic = json_decode($form_field['conditional-logic']);
                    if ($logic->logic_type == 'all') {
                        $flag = true;
                        foreach ($logic->rules as $rule) {
                            //the field in the rule might not be present in the form (maybe cascading rules) but we still need to check the conditions
                            if( !array_key_exists($rule->field, $fields) )
                                $fields[$rule->field] = null;

                            if (isset($rule->operator) && isset($rule->value)) {
                                //$fields[$rule->field] can be an array for Select Multiple
                                if ( $rule->operator === 'is' && ( ( !is_array($fields[$rule->field]) && $fields[$rule->field] !== $rule->value ) || ( is_array($fields[$rule->field]) && !in_array( $rule->value, $fields[$rule->field] ) ) ) ) {
                                    $flag = false;
                                    break;
                                }
                                if ($rule->operator === 'is not' && ( ( !is_array($fields[$rule->field]) && $fields[$rule->field] === $rule->value ) || ( is_array($fields[$rule->field]) && in_array( $rule->value, $fields[$rule->field] ) ) ) ) {
                                    $flag = false;
                                    break;
                                }
                            }
                        }
                    } else if ($logic->logic_type == 'any') {
                        $flag = false;
                        foreach ($logic->rules as $rule) {
                            //the field in the rule might not be present in the form (maybe cascading rules) but we still need to check the conditions
                            if( !array_key_exists($rule->field, $fields) )
                                $fields[$rule->field] = null;

                            if ( isset($rule->operator) && isset($rule->value) ) {
                                if ($rule->operator === 'is' && ( ( !is_array($fields[$rule->field]) && $fields[$rule->field] === $rule->value ) || ( is_array($fields[$rule->field]) && in_array( $rule->value, $fields[$rule->field] ) ) ) ) {
                                    $flag = true;
                                    break;
                                }
                                if ($rule->operator == 'is not' && ( ( !is_array($fields[$rule->field]) && $fields[$rule->field] !== $rule->value ) || ( is_array($fields[$rule->field]) && !in_array( $rule->value, $fields[$rule->field] ) ) ) ) {
                                    $flag = true;
                                    break;
                                }
                            }
                        }
                    }


                    if (($logic->action_type == 'show' && $flag === true) || ($logic->action_type == 'hide' && $flag !== true)) {
                        $ret[$form_field['id']]['type'] = 'show';
                        $ret[$form_field['id']]['html'] = apply_filters('wppb_output_form_field_' . Wordpress_Creation_Kit_PB::wck_generate_slug($form_field['field']), '', $args['form_type'], $form_field, get_current_user_id(), array(), array(), $args['role']);
                    }
                    if (($logic->action_type == 'hide' && $flag === true) || ($logic->action_type == 'show' && $flag !== true)) {
                        $ret[$form_field['id']]['type'] = 'hide';
                    }
                }
            }
        }

        echo json_encode($ret);
        exit();
    }

	/**
	 * if the field is hidden by conditional fields when submitted then we don't test the error check on it (required)
	 */
	function bypass_required_if_needed( $message, $field, $request_data, $form_location ){

		/* if we are not on the default form then get the fields that are present in the form */
		if( !empty( $request_data['form_name'] ) && $request_data['form_name'] != 'unspecified' ){
			$form_id = Profile_Builder_Form_Creator::wppb_get_form_id_from_form_name( $request_data['form_name'], $form_location );
			if( !empty( $form_id ) ){
				if( $form_location == 'register' )
					$meta = 'wppb_rf_fields';
				else if ( $form_location == 'edit_profile' )
					$meta = 'wppb_epf_fields';

				$form_fields = get_post_meta( $form_id, $meta, true );
			}
		}


		/* go ahead if we have conditional enabled on the field */
		if( !empty( $field["conditional-logic-enabled"] ) && !empty( $field["conditional-logic"] ) && $field["conditional-logic-enabled"] === 'yes' ) {
			$field_conditional_logic = json_decode($field["conditional-logic"], true);

			/* set initial conditions based on action_type. if action type is show then it means that at the begging it is hidden */
			if( $field_conditional_logic['action_type'] == 'show' )
				$hide_field = true;
			else if( $field_conditional_logic['action_type'] == 'hide' )
				$hide_field = false;

			/* if all the rules must be met then we start with a true value and pass through all of them and if one is not true then we invalidate them */
			if ($field_conditional_logic['logic_type'] == 'all') {
				$all_conditions = true;
			}

			if (!empty($field_conditional_logic['rules'])) {
				foreach ($field_conditional_logic['rules'] as $rule) {

					$request_name = $this->get_field_meta_name_from_id($rule['field']);

					/* we are in multi forms case so see if the field in the rule is present in the form */
					if( !empty( $form_fields ) && $field_conditional_logic['logic_type'] == 'all' ){
						$found = false;
						foreach( $form_fields as $form_field ){
							if( $form_field['id'] == $rule['field'] ){
								$found = true;
								break;
							}
						}
						if( !$found  )
							return $message;
					}
					elseif( !empty( $form_fields ) && $field_conditional_logic['logic_type'] == 'any' ){
						/* if none of the fields from the rules are present in the form we should return the original message */
						foreach ($field_conditional_logic['rules'] as $any_rule){
							$rule_fields_ids[] = $any_rule['field'];
						}
						foreach( $form_fields as $any_form_field ){
							$form_fields_ids[] = $any_form_field['id'];
						}

						if( !empty( $rule_fields_ids ) && !empty( $form_fields_ids ) ){
							$common_fields = array_intersect( $rule_fields_ids, $form_fields_ids );
							if( empty( $common_fields ) )
								return $message;
						}
					}

					if (!empty($request_name)) {
						/* if the field in the rule is not present in the form don't do anything and return the initial message */
						if (isset($request_data[wppb_handle_meta_name($request_name)])) {

							if( !is_array( $request_data[wppb_handle_meta_name($request_name)] ) )
								$request_value = array( $request_data[wppb_handle_meta_name($request_name)] );
							else
								$request_value = $request_data[wppb_handle_meta_name($request_name)];

							if ($rule['operator'] == 'is' && in_array( $rule['value'], $request_value ) ) {
								if ($field_conditional_logic['logic_type'] == 'any') {
									$hide_field = !$hide_field;
									break;
								}
							}
							else if ($rule['operator'] == 'is' && !in_array( $rule['value'], $request_value ) ) {
								if ($field_conditional_logic['logic_type'] == 'all') {
									$all_conditions = false;
									break;
								}
							}


							if( $rule['operator'] == 'is not' && !in_array( $rule['value'], $request_value ) ){
								if ($field_conditional_logic['logic_type'] == 'any') {
									$hide_field = !$hide_field;
									break;
								}
							}
							else if( $rule['operator'] == 'is not' && in_array( $rule['value'], $request_value ) ){
								if ($field_conditional_logic['logic_type'] == 'all') {
									$all_conditions = false;
									break;
								}
							}
						}
						else{
							if ($field_conditional_logic['logic_type'] == 'all') {
								$all_conditions = false;
							}
						}
					}

				}
			}

			/* if all the rules must be valid and the boolean is still true then the inital state of the field changed */
			if( $field_conditional_logic['logic_type'] == 'all' && $all_conditions )
				$hide_field = !$hide_field;

			/* after checking all the rules and the field is hidden then don't test it */
			if( $hide_field )
				return '';
		}

		return $message;
	}

	/**
	 * function that returns the field meta name based on the field id
	 */
	function get_field_meta_name_from_id( $id ){
		if( !empty( $this->wppb_manage_fields )  ) {
			foreach ($this->wppb_manage_fields as $field) {
				if( $field['id'] == $id ) {
					if( !empty(  $field['meta-name'] ) )
						return $field['meta-name'];
					else{
						switch ( Wordpress_Creation_Kit_PB::wck_generate_slug( $field['field'] ) ){
							case 'default-username':
								return 'username';
								break;
							case 'default-display-name-publicly-as':
								return "display_name";
								break;
							case 'default-e-mail':
								return 'email';
								break;
							case 'default-website':
								return 'website';
								break;
							case 'select-user-role':
								return 'custom_field_user_role';
								break;
							case 'subscription-plans':
								return 'subscription_plans';
								break;
						}
					}
				}
			}
		}

		return '';
	}


    /*
     * Displays an icon in the manage fields list for fields that have conditional logic enabled
     *
     * @param string $display_value
     *
     * @return string
     *
     */
    function conditional_logic_icon( $display_value ) {

        if( strpos( $display_value, 'yes' ) !== false )
            return '<span title="' . __( 'This field has conditional logic enabled.', 'profile-builder' ) . '" class="wppb-manage-fields-dashicon dashicons dashicons-randomize"></span>';

        return $display_value;
    }


    /*
     * Hooks in WCK to cache the element_id of the row that is being removed by the admin
     *
     * @param string $meta      - the name of the option that is being handled
     * @param int $id           - the post id
     * @param int $element_id   - the id of the element that is being removed
     *
     */
    function cache_deleted_element_id( $meta, $id, $element_id ) {

        // If the meta is not manage fields, exit
        if( $meta != 'wppb_manage_fields' )
            return;

        // Go on only if we have an element id passed
        if( empty( $element_id ) )
            return;

        // We know we want the option, not post_meta, so we'll exit if a post id is provided
        if( !empty( $id ) )
            return;

        $this->deleted_element_id = $element_id;

    }


    /*
     * Remove the field that is being deleted from all the conditional rules set
     *
     * @param array $value      - the value that will be saved in the DB
     * @param string $option    - the name of the option being handled
     * @param array $old_value  - the value that is currently saved in the DB
     *
     * @return array
     *
     */
    public function remove_field_from_rules( $value, $option, $old_value ) {

        if( $option != 'wppb_manage_fields' )
            return $value;

        if( $this->deleted_element_id == null )
            return $value;

        // Grab the field that will be deleted
        $deleted_field = $old_value[ $this->deleted_element_id ];

        // Return the value to its initial state
        $this->deleted_element_id = null;


        if( !empty( $value ) ) {
            foreach( $value as $field_key => $field ) {

                // Jump to the next field if this element doesn't have any conditional logic saved
                if( empty( $field['conditional-logic'] ) )
                    continue;

                // Decode the conditional logic
                $cl = json_decode( $field['conditional-logic'], ARRAY_A );

                // As we want to remove all rules that have in them the field that is being deleted,
                // we'll just skip fields that don't have any rules set
                if( empty( $cl['rules'] ) )
                    continue;

                // Go through each rule and remove the ones where the deleted field appears
                foreach( $cl['rules'] as $key => $rule ) {
                    if( $rule['field'] == $deleted_field['id'] )
                        unset( $cl['rules'][$key] );
                }

                // Reset the keys
                $cl['rules'] = array_values( $cl['rules'] );

                // Encode the conditional logic as it was
                $value[$field_key]['conditional-logic'] = json_encode( $cl );

            }
        }

        return $value;

    }

}

// Let's get this party started
new PB_Conditional_Fields;
