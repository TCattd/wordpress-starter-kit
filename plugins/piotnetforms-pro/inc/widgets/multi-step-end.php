<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Piotnetforms_Multi_Step_End extends Base_Widget_Piotnetforms {
	protected $is_add_conditional_logic = false;

	public function get_type() {
		return 'multi-step-end';
	}

	public function get_class_name() {
		return 'Piotnetforms_Multi_Step_End';
	}

	public function get_title() {
		return 'Multi Step End';
	}

	public function get_icon() {
		return [
			'type'  => 'image',
			'value' => plugin_dir_url( __FILE__ ) . '../../assets/icons/w-multi-step-end.svg',
		];
	}

	public function get_categories() {
		return [ ];
	}

	public function get_keywords() {
		return [ 'text' ];
	}

	public function get_script() {
		return [
			'piotnetforms-multi-step-script',
		];
	}

	public static function get_button_sizes() {
		return [
			'xs' => __( 'Extra Small', 'piotnetforms' ),
			'sm' => __( 'Small', 'piotnetforms' ),
			'md' => __( 'Medium', 'piotnetforms' ),
			'lg' => __( 'Large', 'piotnetforms' ),
			'xl' => __( 'Extra Large', 'piotnetforms' ),
		];
	}

	public function register_controls() {
		$this->start_tab( 'settings', 'Settings' );

		$this->start_section( 'button_settings_section', 'Buttons' );
		$this->add_button_setting_controls();

		$this->start_section( 'other_options_section', 'Other Options' );
		$this->add_other_options_controls();

		$this->start_section( 'action_after_submit_settings_section', 'Actions After Submit' );
		$this->add_action_after_submit();

		$this->start_section(
			'email_settings_section',
			'Email',
			[
				'condition' => [
					'submit_actions' => 'email',
				],
			]
		);
		$this->add_email_setting_controls();

		$this->start_section(
			'email_2_settings_section',
			'Email2',
			[
				'condition' => [
					'submit_actions' => 'email2',
				],
			]
		);
		$this->add_email_2_setting_controls();

		$this->start_section( 'form_database_section', 'Form Database' );
		$this->form_database_controls();

		$this->start_section(
			'booking_settings_section',
			'Booking',
			[
				'condition' => [
					'submit_actions' => 'booking',
				],
			]
		);
		$this->add_booking_setting_controls();

		$this->start_section(
			'register_settings_section',
			'Register',
			[
				'condition' => [
					'submit_actions' => 'register',
				],
			]
		);
		$this->add_register_setting_controls();

		$this->start_section(
			'login_settings_section',
			'Login',
			[
				'condition' => [
					'submit_actions' => 'login',
				],
			]
		);
		$this->add_login_setting_controls();

		$this->start_section(
			'update_user_profile_settings_section',
			'Update User Profile',
			[
				'condition' => [
					'submit_actions' => 'update_user_profile',
				],
			]
		);
		$this->add_update_user_profile_setting_controls();

		$this->start_section(
			'submit_post_settings_section',
			'Submit Post',
			[
				'condition' => [
					'submit_actions' => 'submit_post',
				],
			]
		);
		$this->add_submit_post_setting_controls();
		$this->start_section( 'stripe_payment_settings_section', 'Stripe Payment' );
		$this->add_stripe_payment_setting_controls();
		$this->start_section( 'paypal_settings_section', 'Paypal Payment' );
		$this->add_paypal_setting_controls();
		$this->start_section( 'paypal_subscription_settings_section', 'Paypal Subscription' );
		$this->add_paypal_subscription_setting_controls();
		$this->start_section( 'mollie_settings_section', 'Mollie Payment' );
		$this->add_mollie_setting_controls();
		$this->start_section( 'recaptcha_settings_section', 'reCAPTCHA V3' );
		$this->add_recaptcha_setting_controls();

		$this->start_section(
			'redirect_settings_section',
			'Redirect',
			[
				'condition' => [
					'submit_actions' => 'redirect',
				],
			]
		);
		$this->add_redirect_setting_controls();
		if ( class_exists( 'WooCommerce' ) ) {
			$this->start_section(
				'woocommerce_add_to_cart_settings_section',
				'WooCommerce Add To Cart',
				[
					'condition' => [
						'submit_actions' => 'woocommerce_add_to_cart',
					],
				]
			);
			$this->add_woocommerce_add_to_cart_setting_controls();
		}
		$this->start_section(
			'webhook_settings_section',
			'Webhook',
			[
				'condition' => [
					'submit_actions' => 'webhook',
				],
			]
		);
		$this->add_webhook_setting_controls();
		$this->start_section(
			'remote_request_settings_section',
			'Remote Request',
			[
				'condition' => [
					'submit_actions' => 'remote_request',
				],
			]
		);
		$this->add_remote_request_setting_controls();

		$this->start_section(
			'mailchimp_v3_settings_section',
			'MailChimp V3',
			[
				'condition' => [
					'submit_actions' => 'mailchimp_v3',
				],
			]
		);
		$this->add_mailchimp_v3_setting_controls();
		// $this->start_section(
		// 	'mailerlite_settings_section',
		// 	'MailerLite',
		// 	[
		// 		'condition' => [
		// 			'submit_actions' => 'mailerlite',
		// 		],
		// 	]
		// );
		// $this->add_mailerlite_setting_controls();
		$this->start_section(
			'mailerlite_v2_settings_section',
			'MailerLite V2',
			[
				'condition' => [
					'submit_actions' => 'mailerlite_v2',
				],
			]
		);
		$this->add_mailerlite_v2_setting_controls();
		$this->start_section(
			'sendinblue_settings_section',
			'Sendinblue',
			[
				'condition' => [
					'submit_actions' => 'sendinblue',
				],
			]
		);
		$this->add_sendinblue_setting_controls();
		$this->start_section(
			'getresponse_settings_section',
			'Getresponse',
			[
				'condition' => [
					'submit_actions' => 'getresponse',
				],
			]
		);
		$this->add_getresponse_setting_controls();
		$this->start_section(
			'mailpoet_settings_section',
			'Mailpoet',
			[
				'condition' => [
					'submit_actions' => 'mailpoet',
				],
			]
		);
		$this->add_mailpoet_setting_controls();
		$this->start_section(
			'activecampaign_settings_section',
			'Activecampaign',
			[
				'condition' => [
					'submit_actions' => 'activecampaign',
				],
			]
		);
		$this->add_activecampaign_setting_controls();

		$this->start_section(
			'convertkit_settings_section',
			'Convertkit',
			[
				'condition' => [
					'submit_actions' => 'convertkit',
				],
			]
		);
		$this->add_convertkit_setting_controls();
		$this->start_section(
			'zohocrm_settings_section',
			'Zoho CRM',
			[
				'condition' => [
					'submit_actions' => 'zohocrm',
				],
			]
		);
		$this->add_zohocrm_setting_controls();

		$this->start_section(
			'google_calendar_settings_section',
			'Google Calendar',
			[
				'condition' => [
					'submit_actions' => 'google_calendar',
				],
			]
		);
		$this->google_calendar_controls();

		$this->start_section(
			'piotnetforms_hubspot_settings_section',
			'Hubspot',
			[
				'condition' => [
					'submit_actions' => 'hubspot',
				],
			]
		);
		$this->piotnetforms_hubspot_controls();

		$this->start_section(
			'webhook_slack_settings_section',
			'Webhook Slack',
			[
				'condition' => [
					'submit_actions' => 'webhook_slack',
				],
			]
		);

		$this->add_webhook_slack_setting_controls();

		$this->start_section(
			'twilio_sendgrid_settings_section',
			'Twilio SendGrid',
			[
				'condition' => [
					'submit_actions' => 'twilio_sendgrid',
				],
			]
		);

		$this->add_twilio_sendgrid_setting_controls();

		$this->start_section(
			'section_sendy',
			'Sendy',
			[
				'condition' => [
					'submit_actions' => 'sendy',
				],
			]
		);

		$this->add_sendy_setting_controls();

		$this->start_section(
			'twilio_whatsapp_settings_section',
			'Twilio Whatsapp',
			[
				'condition' => [
					'submit_actions' => 'twilio_whatsapp',
				],
			]
		);

		$this->add_whatsapp_setting_controls();

		$this->start_section(
			'twilio_sms_settings_section',
			'Twilio SMS',
			[
				'condition' => [
					'submit_actions' => 'twilio_sms',
				],
			]
		);

		$this->add_twilio_sms_setting_controls();

		$this->start_section(
			'sendfox_settings_section',
			'SendFox',
			[
				'condition' => [
					'submit_actions' => 'sendfox',
				],
			]
		);

		$this->add_sendfox_setting_controls();

		$this->start_section(
			'pdfgenerator_settings_section',
			'PDF Generator',
			[
				'condition' => [
					'submit_actions' => 'pdfgenerator',
				],
			]
		);

		$this->add_pdfgenerator_setting_controls();

		$this->start_section( 'form_options_settings_section', 'Form Messages' );
		$this->form_options_setting_controls();

		$this->start_section( 'abandonment_settings_section', 'Abandonment' );
		$this->abandonment_setting_controls();

		$this->start_section( 'google_sheets_controls', 'Google Sheets' );
		$this->google_sheets_controls();

		$this->start_section( 'conditional_logic_settings_section', 'Conditional Logic' );
		$this->conditional_logic_setting_controls();

		//Tab Style
		$this->start_tab( 'style', 'Style' );
		$this->start_section( 'button_style_section', 'Button' );
		$this->add_button_style_controls();
		$this->start_section( 'message_style_section', 'Messages' );
		$this->add_message_style_controls();

		// $this->start_tab( 'style', 'Style' );
		// $this->start_section( 'text_styles_section', 'Style' );
		// $this->add_style_controls();

		$this->add_advanced_tab();

		return $this->structure;
	}

	private function form_database_controls() {
		$this->add_control(
			'piotnetforms_database_disable',
			[
				'label' => __( 'Disable', 'piotnetforms' ),
				'type' => 'switch',
				'default' => '',
				'label_on' => 'Yes',
				'label_off' => 'No',
				'return_value' => 'yes',
			]
		);
		$this->add_control(
			'piotnetforms_database_hidden_field',
			[
				'label' => __( 'Hidden field (database)?', 'piotnetforms' ),
				'type' => 'switch',
				'default' => '',
				'label_on' => 'Yes',
				'label_off' => 'No',
				'return_value' => 'yes',
				'description' => 'When selected, the fields will be saved as ******.',
			]
		);
		//repeater
		$this->new_group_controls();

		$this->add_control(
			'piotnetforms_database_field_name_hideen',
			[
				'label'       => __( 'Field ID* (Required)', 'piotnetforms' ),
				'type'        => 'text',
				'description' => 'E.g news',
			]
		);
		$repeater_items = $this->get_group_controls();

		$this->new_group_controls();
		$this->add_control(
			'',
			[
				'type'           => 'repeater-item',
				'remove_label'   => __( 'Remove Item', 'piotnetforms' ),
				'controls'       => $repeater_items,
				'controls_query' => '.piotnet-control-repeater-field',
			]
		);
		$repeater_list = $this->get_group_controls();

		$this->add_control(
			'piotnetforms_database_list_field_hidden',
			[
				'type'           => 'repeater',
				'label'          => __( 'Fields List', 'piotnetforms' ),
				'value'          => '',
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
				'condition' => [
					'piotnetforms_database_hidden_field' => 'yes'
				]
			]
		);
		//end repeater
	}
	private function piotnetforms_hubspot_controls() {
		$this->add_control(
			'piotnetforms_hubspot_acceptance_field',
			[
				'label'        => __( 'Acceptance Field?', 'piotnetforms' ),
				'type'         => 'switch',
				'label_on'     => __( 'Yes', 'piotnetforms' ),
				'label_off'    => __( 'No', 'piotnetforms' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);
		$this->add_control(
			'piotnetforms_hubspot_acceptance_field_shortcode',
			[
				'label'       => __( 'Acceptance Field Shortcode', 'piotnetforms' ),
				'type'        => 'select',
				'get_fields'  => true,
				'value'       => __( '', 'piotnetforms' ),
				'placeholder' => __( 'Enter your shortcode here', 'piotnetforms' ),
				'condition'   => [
					'piotnetforms_hubspot_acceptance_field' => 'yes',
				],
			]
		);

		$this->add_control(
			'piotnetforms_hubspot_get_group',
			[
				'type'        => 'html',
				'label_block' => true,
				'raw'         => __( '<button data-piotnetforms-hubspot-get-group-list class="piotnetforms-admin-button-ajax piotnetforms-button piotnetforms-button-default" type="button">Get Group List<i class="fas fa-spinner fa-spin"></i></button><div class="piotnetforms-hubspot-group-list"></div>', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'piotnetforms_hubspot_group_key',
			[
				'label'     => __( 'Group Key', 'piotnetforms' ),
				'type'      => 'text',
				'label_block' => true,
				'placeholder' => __( 'Enter the group key here', 'piotnetforms' ),
			]
		);

		$this->add_control(
			'piotnetforms_hubspot_get_property',
			[
				'type'        => 'html',
				'label_block' => true,
				'raw'         => __( '<button data-piotnetforms-hubspot-get-property-list class="piotnetforms-admin-button-ajax piotnetforms-button piotnetforms-button-default" type="button">Get Property List<i class="fas fa-spinner fa-spin"></i></button><div class="piotnetforms-hubspot-property-list"></div>', 'piotnetforms' ),
			]
		);
		$this->new_group_controls();

		$this->add_control(
			'piotnetforms_hubspot_property_name',
			[
				'label'       => __( 'Property Name', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'placeholder' => __( 'E.g email, firstname, lastname', 'piotnetforms' ),
			]
		);

		$this->add_control(
			'piotnetforms_hubspot_field_shortcode',
			[
				'label'       => __( 'Field Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'placeholder' => __( 'E.g [field id="email"]', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'repeater_id',
			[
				'type' => 'hidden',
			],
			[
				'overwrite' => 'true',
			]
		);
		$repeater_items = $this->get_group_controls();

		$this->new_group_controls();
		$this->add_control(
			'',
			[
				'type'           => 'repeater-item',
				'remove_label'   => __( 'Remove Item', 'piotnetforms' ),
				'controls'       => $repeater_items,
				'controls_query' => '.piotnet-control-repeater-field',
			]
		);
		$repeater_list = $this->get_group_controls();

		$this->add_control(
			'piotnetforms_hubspot_list',
			[
				'type'           => 'repeater',
				'label'          => __( 'Field Mapping', 'piotnetforms' ),
				'value'          => '',
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
			]
		);
	}

	private function google_calendar_controls() {
		$this->add_control(
			'google_calendar_enable',
			[
				'type'         => 'switch',
				'label'        => __( 'Enable', 'piotnetforms' ),
				'value'        => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'google_calendar_date_type',
			[
				'type'    => 'select',
				'label'   => 'Date Type',
				'value'   => 'date',
				'options' => [
					'date' => 'Date',
					'date_time'   => 'Date Time',
				],
				'condition' => [
					'google_calendar_enable' => 'yes'
				]
			]
		);
		$this->add_control(
			'google_calendar_duration',
			[
				'type'         => 'text',
				'label'        => 'Duration* (Required)',
				'label_block'  => true,
				'placeholder' => '',
				'description' => __( 'The unit is minute. Eg:30,60,90,...', 'piotnetforms' ),
				'condition' => [
					'google_calendar_enable' => 'yes',
					'google_calendar_date_type' => 'date_time'
				]
			]
		);

		$this->add_control(
			'google_calendar_attendees_name',
			[
				'type'         => 'text',
				'label'        => 'Attendees Name* (Required)',
				'label_block'  => true,
				'dynamic'      => true,
				'get_fields'   => true,
				'condition' => [
					'google_calendar_enable' => 'yes'
				]
			]
		);

		$this->add_control(
			'google_calendar_attendees_email',
			[
				'type'         => 'text',
				'label'        => 'Attendees Email* (Required)',
				'label_block'  => true,
				'dynamic'      => true,
				'get_fields'   => true,
				'condition' => [
					'google_calendar_enable' => 'yes'
				]
			]
		);

		$this->add_control(
			'google_calendar_date_start',
			[
				'type'         => 'text',
				'label'        => 'Date Start* (Required)',
				'label_block'  => true,
				'dynamic'      => true,
				'get_fields'   => true,
				'condition' => [
					'google_calendar_enable' => 'yes'
				]
			]
		);

		$this->add_control(
			'google_calendar_date_end',
			[
				'type'         => 'text',
				'label'        => 'Date End* (Required)',
				'label_block'  => true,
				'dynamic'      => true,
				'get_fields'   => true,
				'condition' => [
					'google_calendar_enable' => 'yes'
				]
			]
		);


		$this->add_control(
			'google_calendar_summary',
			[
				'type'         => 'text',
				'label'        => 'Summary* (Required)',
				'label_block'  => true,
				'dynamic'      => true,
				'get_fields'   => true,
				'condition' => [
					'google_calendar_enable' => 'yes'
				]
			]
		);

		$this->add_control(
			'google_calendar_description',
			[
				'type'         => 'text',
				'label'        => 'Description',
				'label_block'  => true,
				'dynamic'      => true,
				'get_fields'   => true,
				'condition' => [
					'google_calendar_enable' => 'yes'
				]
			]
		);

		$this->add_control(
			'google_calendar_location',
			[
				'type'         => 'text',
				'label'        => 'Location',
				'label_block'  => true,
				'dynamic'      => true,
				'get_fields'   => true,
				'condition' => [
					'google_calendar_enable' => 'yes'
				]
			]
		);

		$this->add_control(
			'google_calendar_remind_method',
			[
				'type'         => 'select',
				'label'        => 'Remind Method',
				'label_block'  => true,
				'value'        => 'left',
				'options'      => [
					'email'   => __( 'Email', 'piotnetforms' ),
					'popup' => __( 'Popup', 'piotnetforms' ),
				],
				'condition' => [
					'google_calendar_enable' => 'yes'
				]
			]
		);
		$this->add_control(
			'google_calendar_remind_time',
			[
				'type'         => 'text',
				'label'        => 'Remind Time* (Required)',
				'label_block'  => true,
				'description' => __( 'The unit is minute. Eg:30,60,90,...', 'piotnetforms' ),
				'condition' => [
					'google_calendar_enable' => 'yes'
				]
			]
		);
	}

	private function google_sheets_controls() {
		$this->add_control(
			'piotnetforms_google_sheets_connector_enable',
			[
				'label' => __( 'Enable', 'piotnetforms' ),
				'type' => 'switch',
				'default' => '',
				'description' => __( 'This feature only works on the frontend.', 'piotnetforms' ),
				'label_on' => 'Yes',
				'label_off' => 'No',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'piotnetforms_google_sheets_connector_id',
			[
				'label' => __( 'Google Sheet ID', 'piotnetforms' ),
				'type' => 'text',
				'description' => __( 'ID is the value between the "/d/" and the "/edit" in the URL of your spreadsheet. For example: /spreadsheets/d/****/edit#gid=0', 'piotnetforms' ),
				'condition' => [
					'piotnetforms_google_sheets_connector_enable' => 'yes',
				],
			]
		);

		$this->add_control(
			'piotnetforms_google_sheets_connector_tab',
			[
				'label' => __( 'Tab Name', 'piotnetforms' ),
				'type' => 'text',
				'condition' => [
					'piotnetforms_google_sheets_connector_enable' => 'yes',
				],
			]
		);

		$this->new_group_controls();
		$this->add_control(
			'piotnetforms_google_sheets_connector_field_id',
			[
				'label' => __( 'Field Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
			]
		);
		$this->add_control(
			'piotnetforms_google_sheets_connector_field_column',
			[
				'label' => __( 'Column in Google Sheets', 'piotnetforms' ),
				'type' => 'text',
				'label_block' => true,
				'description' => 'E.g A,B,C,AA,AB,AC,AZ',
			]
		);
		$this->add_control(
			'repeater_id',
			[
				'type' => 'hidden',
			],
			[
				'overwrite' => 'true',
			]
		);
		$repeater_items = $this->get_group_controls();

		$this->new_group_controls();
		$this->add_control(
			'',
			[
				'type'           => 'repeater-item',
				'remove_label'   => __( 'Remove Item', 'piotnetforms' ),
				'controls'       => $repeater_items,
				'controls_query' => '.piotnet-control-repeater-field',
			]
		);
		$repeater_list = $this->get_group_controls();

		$this->add_control(
			'piotnetforms_google_sheets_connector_field_list',
			[
				'type'           => 'repeater',
				'label'          => __( 'Fields Mapping', 'piotnetforms' ),
				'value'          => '',
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
				'condition' => [
					'piotnetforms_google_sheets_connector_enable' => 'yes',
				],
			]
		);
	}

	private function add_button_setting_controls() {
		$this->add_control(
			'form_id',
			[
				'type'        => 'hidden',
				'description' => __( 'Enter the same form id for all fields in a form', 'piotnetforms' ),
				'label'       => __( 'Form ID* (Required)', 'piotnetforms' ),
			]
		);

		// $this->add_control(
		// 	'button_type',
		// 	[
		// 		'label'   => __( 'Type', 'piotnetforms' ),
		// 		'type'    => 'select',
		// 		'value'   => '',
		// 		'options' => [
		// 			''        => __( 'Default', 'piotnetforms' ),
		// 			'info'    => __( 'Info', 'piotnetforms' ),
		// 			'success' => __( 'Success', 'piotnetforms' ),
		// 			'warning' => __( 'Warning', 'piotnetforms' ),
		// 			'danger'  => __( 'Danger', 'piotnetforms' ),
		// 		],
		// 	]
		// );

		$this->add_control(
			'disable_buttons',
			[
				'label' => __( 'Disable Buttons', 'piotnetforms' ),
				'type' => 'switch',
				'default' => '',
				'label_on' => 'Yes',
				'label_off' => 'No',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'prev_button',
			[
				'label' => __( 'Previous Button', 'piotnetforms' ),
				'type' => 'switch',
				'default' => 'yes',
				'label_on' => 'Yes',
				'label_off' => 'No',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'prev_button_text',
			[
				'type'        => 'text',
				'label'       => __( 'Previous Button Text', 'piotnetforms' ),
				'label_block' => true,
				'value'       => __( 'Previous', 'piotnetforms' ),
				'condition'   => [
					'prev_button' => 'yes',
				],
			]
		);

		$this->add_control(
			'submit_button_text',
			[
				'label'       => __( 'Submit Button Text', 'piotnetforms' ),
				'type'        => 'text',
				'value'       => __( 'Submit', 'piotnetforms' ),
			]
		);

		$this->add_responsive_control(
			'buttons_justify_content',
			[
				'type'         => 'select',
				'label'        => __( 'Justify Content', 'piotnetforms' ),
				'label_block'  => true,
				'value'        => '',
				'options'      => [
					''       => __( 'Default', 'piotnetforms' ),
					'center' => __( 'Center', 'piotnetforms' ),
					'space-between' => __( 'Space Between', 'piotnetforms' ),
					'space-evenly' => __( 'Space Evenly', 'piotnetforms' ),
					'space-around' => __( 'Space Around', 'piotnetforms' ),
				],
				'selectors'    => [
					'{{WRAPPER}}' => 'justify-content: {{VALUE}}',
				],
			]
		);

		// $this->add_control(
		// 	'size',
		// 	[
		// 		'label'   => __( 'Size', 'piotnetforms' ),
		// 		'type'    => 'select',
		// 		'value'   => 'sm',
		// 		'options' => self::get_button_sizes(),
		// 	]
		// );
		$this->add_control(
			'icon',
			[
				'label'          => __( 'Submit Button Icon', 'piotnetforms' ),
				'type'           => 'icon',
				'label_block'    => true,
				'value'          => '',
				'options_source' => 'fontawesome',
			]
		);
		$this->add_control(
			'icon_align',
			[
				'label'     => __( 'Icon Position', 'piotnetforms' ),
				'type'      => 'select',
				'value'     => 'left',
				'options'   => [
					'left'  => __( 'Before', 'piotnetforms' ),
					'right' => __( 'After', 'piotnetforms' ),
				],
				'conditions' => [
					'terms' => [
						[
							'name'     => 'icon',
							'operator' => '!=',
							'value'    => '',
						],
					],
				],
			]
		);
		$this->add_control(
			'icon_indent',
			[
				'label'      => __( 'Icon Spacing', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [
					'px' => [
						'min'  => 1,
						'max'  => 200,
						'step' => 1,
					],
				],
				'value'      => [
					'unit' => 'px',
					'size' => '10',
				],
				'conditions' => [
					'terms' => [
						[
							'name'     => 'icon',
							'operator' => '!=',
							'value'    => '',
						],
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .piotnetforms-align-icon-right' => 'margin-left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .piotnetforms-align-icon-left' => 'margin-right: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'icon_size',
			[
				'label'      => __( 'Icon Size', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [
					'px' => [
						'min'  => 1,
						'max'  => 50,
						'step' => 1,
					],
				],
				'value'      => [
					'unit' => 'px',
					'size' => '12',
				],
				'conditions' => [
					'terms' => [
						[
							'name'     => 'icon',
							'operator' => '!=',
							'value'    => '',
						],
					],
				],
				'selectors'  => [
					'{{WRAPPER}} .piotnetforms-button-icon' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);
	}

	private function add_other_options_controls() {
		$this->add_control(
			'remove_empty_form_input_fields',
			[
				'type'         => 'switch',
				'label'        => __( 'Remove Empty Form Input Fields', 'piotnetforms' ),
				'value'        => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
			]
		);
		$this->add_control(
			'enter_submit_form',
			[
				'type'         => 'switch',
				'label'        => __( 'Press Enter To Submit Form', 'piotnetforms' ),
				'value'        => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'hide_button_after_submitting',
			[
				'type'         => 'switch',
				'label'        => __( 'Hide The Button After Submitting', 'piotnetforms' ),
				'value'        => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
			]
		);
	}

	private function add_action_after_submit() {
		$actions         = [
			[
				'name'  => 'email',
				'label' => 'Email',
			],
			[
				'name'  => 'email2',
				'label' => 'Email 2',
			],
			[
				'name'  => 'booking',
				'label' => 'Booking',
			],
			[
				'name'  => 'redirect',
				'label' => 'Redirect',
			],
			[
				'name'  => 'register',
				'label' => 'Register',
			],
			[
				'name'  => 'login',
				'label' => 'Login',
			],
			[
				'name'  => 'update_user_profile',
				'label' => 'Update User Profile',
			],
			[
				'name'  => 'webhook',
				'label' => 'Webhook',
			],
			[
				'name'  => 'remote_request',
				'label' => 'Remote Request',
			],
			// [
			// 	'name'  => 'popup',
			// 	'label' => 'Popup',
			// ],
			// [
			// 	'name'  => 'open_popup',
			// 	'label' => 'Open Popup',
			// ],
			// [
			// 	'name'  => 'close_popup',
			// 	'label' => 'Close Popup',
			// ],
			[
				'name'  => 'submit_post',
				'label' => 'Submit Post',
			],
			[
				'name'  => 'woocommerce_add_to_cart',
				'label' => 'Woocommerce Add To Cart',
			],
			[
				'name'  => 'mailchimp_v3',
				'label' => 'MailChimp',
			],
			// [
			// 	'name'  => 'mailerlite',
			// 	'label' => 'MailerLite',
			// ],
			[
				'name'  => 'mailerlite_v2',
				'label' => 'MailerLite',
			],
			[
				'name'  => 'activecampaign',
				'label' => 'ActiveCampaign',
			],
			[
				'name' => 'hubspot',
				'label' => 'Hubspot'
			],
			[
				'name'  => 'pdfgenerator',
				'label' => 'PDF Generator',
			],
			[
				'name'  => 'getresponse',
				'label' => 'Getresponse',
			],
			[
				'name'  => 'mailpoet',
				'label' => 'Mailpoet',
			],
			[
				'name'  => 'zohocrm',
				'label' => 'Zoho CRM',
			],
			[
				'name'  => 'google_calendar',
				'label' => 'Google Calendar',
			],
			[
				'name'  => 'webhook_slack',
				'label' => 'Webhook Slack',
			],
			[
				'name'  => 'sendy',
				'label' => 'Sendy',
			],
			[
				'name'  => 'twilio_whatsapp',
				'label' => 'Twilio Whatsapp',
			],
			[
				'name'  => 'twilio_sms',
				'label' => 'Twilio SMS',
			],
			[
				'name'  => 'sendfox',
				'label' => 'SendFox',
			],
			[
				'name'  => 'sendinblue',
				'label' => 'Sendinblue',
			],
			[
				'name'  => 'convertkit',
				'label' => 'Convertkit',
			],
			[
				'name'  => 'twilio_sendgrid',
				'label' => 'Twilio SendGrid',
			],
		];
		$actions_options = [];

		foreach ( $actions as $action ) {
			$actions_options[ $action['name'] ] = $action['label'];
		}
		$this->add_control(
			'submit_actions',
			[
				'label'       => __( 'Add Action', 'piotnetforms' ),
				'type'        => 'select2',
				'multiple'    => true,
				'options'     => $actions_options,
				'label_block' => true,
				'value'       => [
					'email',
				],
				'description' => __( 'Add actions that will be performed after a visitor submits the form (e.g. send an email notification). Choosing an action will add its setting below.', 'piotnetforms' ),
			]
		);

		$this->conditional_for_actions_controls();
	}
	private function add_booking_setting_controls() {
		$this->add_control(
			'booking_shortcode',
			[
				'label'       => __( 'Booking Shortcode', 'piotnetforms' ),
				'type'        => 'text',
				'placeholder' => __( '[field id="booking"]', 'piotnetforms' ),
				'label_block' => true,
			]
		);
	}
	private function add_register_setting_controls() {
		global $wp_roles;
		$roles       = $wp_roles->roles;
		$roles_array = [];
		foreach ( $roles as $key => $value ) {
			$roles_array[ $key ] = $value['name'];
		}
		$this->add_control(
			'register_role',
			[
				'label'       => __( 'Role', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'options'     => $roles_array,
				'value'       => 'subscriber',
			]
		);
		$this->add_control(
			'register_email',
			[
				'label'       => __( 'Email Field Shortcode* (Required)', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'description' => __( 'E.g [field id="email"]', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'register_username',
			[
				'label'       => __( 'Username Field Shortcode* (Required)', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'description' => __( 'E.g [field id="username"]', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'register_password',
			[
				'label'       => __( 'Password Field Shortcode* (Required)', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'description' => __( 'E.g [field id="password"]', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'register_password_confirm',
			[
				'label'       => __( 'Confirm Password Field Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'description' => __( 'E.g [field id="confirm_password"]', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'register_password_confirm_message',
			[
				'label'       => __( 'Wrong Password Message', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'value'       => __( 'Wrong Password', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'register_first_name',
			[
				'label'       => __( 'First Name Field Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'description' => __( 'E.g [field id="first_name"]', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'register_last_name',
			[
				'label'       => __( 'Last Name Field Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'description' => __( 'E.g [field id="last_name"]', 'piotnetforms' ),
			]
		);
		//repeater
		$this->new_group_controls();
		$this->add_control(
			'register_user_meta',
			[
				'label'       => __( 'User Meta', 'piotnetforms' ),
				'type'        => 'select',
				'options'     => [
					''             => __( 'Choose', 'piotnetforms' ),
					'meta'         => __( 'User Meta Key', 'piotnetforms' ),
					'acf'          => __( 'ACF Field', 'piotnetforms' ),
					'metabox'      => __( 'MetaBox Field', 'piotnetforms' ),
					'toolset'      => __( 'Toolset Field', 'piotnetforms' ),
				],
			]
		);
		$this->add_control(
			'register_user_meta_type',
			[
				'label'     => __( 'User Meta Type', 'piotnetforms' ),
				'type'      => 'select',
				'options'   => [
					'text'     => __( 'Text,Textarea,Number,Email,Url,Password', 'piotnetforms' ),
					'image'    => __( 'Image', 'piotnetforms' ),
					'gallery'  => __( 'Gallery', 'piotnetforms' ),
					'select'   => __( 'Select', 'piotnetforms' ),
					'radio'    => __( 'Radio', 'piotnetforms' ),
					'checkbox' => __( 'Checkbox', 'piotnetforms' ),
					'true_false' => __( 'True / False', 'piotnetforms' ),
					'date'     => __( 'Date', 'piotnetforms' ),
					'time'     => __( 'Time', 'piotnetforms' ),
				],
				'value'     => 'text',
				'condition' => [
					'register_user_meta' => 'acf',
				],
			]
		);
		$this->add_control(
			'register_user_meta_key',
			[
				'label'       => __( 'Meta Key', 'piotnetforms' ),
				'type'        => 'text',
				'description' => 'E.g description',
			]
		);
		$this->add_control(
			'register_user_meta_field_id',
			[
				'label'       => __( 'Field Shortcode', 'piotnetforms' ),
				'type'        => 'select',
				'get_fields'  => true,
				'description' => __( 'E.g [field id="description"]', 'piotnetforms' ),
			]
		);

		$this->add_control(
			'repeater_id',
			[
				'type' => 'hidden',
			],
			[
				'overwrite' => 'true',
			]
		);
		$repeater_items = $this->get_group_controls();

		$this->new_group_controls();
		$this->add_control(
			'',
			[
				'type'           => 'repeater-item',
				'remove_label'   => __( 'Remove Item', 'piotnetforms' ),
				'controls'       => $repeater_items,
				'controls_query' => '.piotnet-control-repeater-field',
			]
		);
		$repeater_list = $this->get_group_controls();

		$this->add_control(
			'register_user_meta_list',
			[
				'type'           => 'repeater',
				'label'          => __( 'User Meta List', 'piotnetforms' ),
				'value'          => '',
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
			]
		);
	}
	private function add_login_setting_controls() {
		$this->add_control(
			'login_username',
			[
				'label'       => __( 'Username or Email Field Shortcode* (Required)', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'description' => __( 'E.g [field id="username"]', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'login_password',
			[
				'label'       => __( 'Password Field Shortcode* (Required)', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'description' => __( 'E.g [field id="password"]', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'login_remember',
			[
				'label'       => __( 'Remember Field Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'description' => __( 'E.g [field id="remember"]', 'piotnetforms' ),
			]
		);
	}
	private function add_update_user_profile_setting_controls() {
		//repeater
		$this->new_group_controls();
		$this->add_control(
			'update_user_meta',
			[
				'label'       => __( 'User Meta', 'piotnetforms' ),
				'type'        => 'select',
				'options'     => [
					''             => __( 'Choose', 'piotnetforms' ),
					'display_name'     => __( 'Display Name', 'piotnetforms' ),
					'first_name'   => __( 'First Name', 'piotnetforms' ),
					'last_name'    => __( 'Last Name', 'piotnetforms' ),
					'description'  => __( 'Bio', 'piotnetforms' ),
					'email'        => __( 'Email', 'piotnetforms' ),
					'password'     => __( 'Password', 'piotnetforms' ),
					'url'          => __( 'Website', 'piotnetforms' ),
					'meta'         => __( 'User Meta Key', 'piotnetforms' ),
					'acf'          => __( 'ACF Field', 'piotnetforms' ),
					'metabox'      => __( 'MetaBox Field', 'piotnetforms' ),
					'toolset'      => __( 'Toolset Field', 'piotnetforms' ),
				],
				'description' => __( 'If you want to update user password, you have to create a password field and confirm password field', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'update_user_meta_type',
			[
				'label'     => __( 'User Meta Type', 'piotnetforms' ),
				'type'      => 'select',
				'options'   => [
					'text'     => __( 'Text,Textarea,Number,Email,Url,Password', 'piotnetforms' ),
					'image'    => __( 'Image', 'piotnetforms' ),
					'gallery'  => __( 'Gallery', 'piotnetforms' ),
					'select'   => __( 'Select', 'piotnetforms' ),
					'radio'    => __( 'Radio', 'piotnetforms' ),
					'checkbox' => __( 'Checkbox', 'piotnetforms' ),
					'true_false' => __( 'True / False', 'piotnetforms' ),
					'date'     => __( 'Date', 'piotnetforms' ),
					'time'     => __( 'Time', 'piotnetforms' ),
					// 'repeater' => __( 'ACF Repeater', 'piotnetforms' ),
					// 'google_map' => __( 'ACF Google Map', 'piotnetforms' ),
				],
				'value'     => 'text',
				'condition' => [
					'update_user_meta' => 'acf',
				],
			]
		);
		$this->add_control(
			'update_user_meta_key',
			[
				'label'       => __( 'User Meta Key', 'piotnetforms' ),
				'type'        => 'text',
				'description' => 'E.g description',
				'condition'   => [
					'update_user_meta' => [ 'meta', 'acf' ],
				],
			]
		);
		$this->add_control(
			'update_user_meta_field_shortcode',
			[
				'label'       => __( 'Field Shortcode', 'piotnetforms' ),
				'type'        => 'select',
				'get_fields'  => true,
				'description' => __( 'E.g [field id="description"]', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'update_user_meta_field_shortcode_confirm_password',
			[
				'label'       => __( 'Confirm Password Field Shortcode', 'piotnetforms' ),
				'type'        => 'select',
				'get_fields'  => true,
				'description' => __( 'E.g [field id="confirm_password"]', 'piotnetforms' ),
				'condition'   => [
					'update_user_meta' => 'password',
				],
			]
		);
		$this->add_control(
			'wrong_password_message',
			[
				'label'     => __( 'Wrong Password Message', 'piotnetforms' ),
				'type'      => 'text',
				'value'     => __( 'Wrong Password', 'piotnetforms' ),
				'condition' => [
					'update_user_meta' => 'password',
				],
			]
		);
		$this->add_control(
			'repeater_id',
			[
				'type' => 'hidden',
			],
			[
				'overwrite' => 'true',
			]
		);
		$repeater_items = $this->get_group_controls();

		$this->new_group_controls();
		$this->add_control(
			'',
			[
				'type'           => 'repeater-item',
				'remove_label'   => __( 'Remove Item', 'piotnetforms' ),
				'controls'       => $repeater_items,
				'controls_query' => '.piotnet-control-repeater-field',
			]
		);
		$repeater_list = $this->get_group_controls();

		$this->add_control(
			'update_user_meta_list',
			[
				'type'           => 'repeater',
				'label'          => __( 'User Meta List', 'piotnetforms' ),
				'value'          => '',
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
			]
		);
	}
	private function add_submit_post_setting_controls() {
		$post_types       = get_post_types( [], 'objects' );
		$post_types_array = [];
		$taxonomy         = [];
		foreach ( $post_types as $post_type ) {
			$post_types_array[ $post_type->name ] = $post_type->label;
			$taxonomy_of_post_type                = get_object_taxonomies( $post_type->name, 'names' );
			$post_type_name                       = $post_type->name;
			if ( ! empty( $taxonomy_of_post_type ) && $post_type_name != 'nav_menu_item' && $post_type_name != 'piotnetforms_library' && $post_type_name != 'piotnetforms_font' ) {
				if ( $post_type_name == 'post' ) {
					$taxonomy_of_post_type = array_diff( $taxonomy_of_post_type, [ 'post_format' ] );
				}
				$taxonomy[ $post_type_name ] = $taxonomy_of_post_type;
			}
		}

		$taxonomy_array = [];
		foreach ( $taxonomy as $key => $value ) {
			foreach ( $value as $key_item => $value_item ) {
				$taxonomy_array[ $value_item . '|' . $key ] = $value_item . ' - ' . $key;
			}
		}
		$this->add_control(
			'submit_post_type',
			[
				'label'   => __( 'Post Type', 'piotnetforms' ),
				'type'    => 'select',
				'options' => $post_types_array,
				'value'   => 'post',
			]
		);
		// $this->add_control(
		// 	'submit_post_taxonomy',
		// 	[
		// 		'label' => __( 'Taxonomy', 'piotnetforms' ),
		// 		'type'  => 'hidden',
		// 		'value' => 'category-post',
		// 	]
		// );
		// $this->add_control(
		// 	'submit_post_term_slug',
		// 	[
		// 		'label'       => __( 'Term slug', 'piotnetforms' ),
		// 		'type'        => 'hidden',
		// 		'description' => 'E.g news, [field id="term"]',
		// 	]
		// );
		// $this->add_control(
		// 	'submit_post_term',
		// 	[
		// 		'label'       => __( 'Term Field Shortcode', 'piotnetforms' ),
		// 		'type'        => 'hidden',
		// 		'description' => __( 'E.g [field id="term"]', 'piotnetforms' ),
		// 	]
		// );
		//repeater
		$this->new_group_controls();
		$this->add_control(
			'submit_post_taxonomy',
			[
				'label'   => __( 'Taxonomy', 'piotnetforms' ),
				'type'    => 'select',
				'options' => $taxonomy_array,
				'value'   => 'category-post',
			]
		);

		$this->add_control(
			'submit_post_terms_slug',
			[
				'label'       => __( 'Term slug', 'piotnetforms' ),
				'type'        => 'text',
				'description' => 'E.g news',
			]
		);
		$this->add_control(
			'submit_post_terms_field_id',
			[
				'label'       => __( 'Terms Select Field Shortcode', 'piotnetforms' ),
				'type'        => 'select',
				'get_fields'  => true,
				'description' => __( 'E.g [field id="term"]', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'repeater_id',
			[
				'type' => 'hidden',
			],
			[
				'overwrite' => 'true',
			]
		);
		$repeater_items = $this->get_group_controls();

		$this->new_group_controls();
		$this->add_control(
			'',
			[
				'type'           => 'repeater-item',
				'remove_label'   => __( 'Remove Item', 'piotnetforms' ),
				'controls'       => $repeater_items,
				'controls_query' => '.piotnet-control-repeater-field',
			]
		);
		$repeater_list = $this->get_group_controls();

		$this->add_control(
			'submit_post_terms_list',
			[
				'type'           => 'repeater',
				'label'          => __( 'Terms List', 'piotnetforms' ),
				'value'          => '',
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
			]
		);
		//end repeater
		$this->add_control(
			'submit_post_status',
			[
				'label'   => __( 'Post Status', 'piotnetforms' ),
				'type'    => 'select',
				'options' => [
					'publish' => __( 'Publish', 'piotnetforms' ),
					'pending' => __( 'Pending', 'piotnetforms' ),
				],
				'value'   => 'publish',
			]
		);
		$this->add_control(
			'submit_post_url_shortcode',
			[
				'label'   => __( 'Post URL shortcode', 'piotnetforms' ),
				'type'    => 'html',
				'classes' => 'forms-field-shortcode',
				'raw'     => '<input value="[post_url]" readonly />',
			]
		);
		$this->add_control(
			'submit_post_id_shortcode',
			[
				'label'   => __( 'Post ID shortcode', 'piotnetforms' ),
				'type'    => 'html',
				'classes' => 'forms-field-shortcode',
				'raw'     => '<input value="[post_id]" readonly />',
			]
		);
		$this->add_control(
			'submit_post_title',
			[
				'label'       => __( 'Title Field Shortcode', 'piotnetforms' ),
				'type'        => 'select',
				'get_fields'  => true,
				'description' => __( 'E.g [field id="title"]', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'submit_post_content',
			[
				'label'       => __( 'Content Field Shortcode', 'piotnetforms' ),
				'type'        => 'select',
				'get_fields'  => true,
				'description' => __( 'E.g [field id="content"]', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'submit_post_featured_image',
			[
				'label'       => __( 'Featured Image Field Shortcode', 'piotnetforms' ),
				'type'        => 'select',
				'get_fields'  => true,
				'description' => __( 'E.g [field id="featured_image_upload"]', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'submit_post_custom_field_source',
			[
				'label'   => __( 'Custom Fields', 'piotnetforms' ),
				'type'    => 'select',
				'options' => [
					'post_custom_field' => __( 'Post Custom Field', 'piotnetforms' ),
					'acf_field'         => __( 'ACF Field', 'piotnetforms' ),
					'toolset_field'     => __( 'Toolset Field', 'piotnetforms' ),
					'jet_engine_field'  => __( 'JetEngine Field', 'piotnetforms' ),
					'pods_field'  => __( 'Pods Field', 'piotnetforms' ),
					'metabox_field'  => __( 'Metabox Field', 'piotnetforms' ),
				],
				'value'   => 'post_custom_field',
			]
		);
		//repeater
		$this->new_group_controls();
		$this->add_control(
			'submit_post_custom_field',
			[
				'label'       => __( 'Custom Field Slug', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'description' => __( 'E.g custom_field_slug', 'piotnetforms' ),
				'placeholder' => __( 'Avoid using common words like "image" "date"', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'submit_post_custom_field_id',
			[
				'label'       => __( 'Field Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'description' => __( 'E.g [field id="addition"]', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'submit_post_custom_field_type',
			[
				'label'       => __( 'Custom Field Type if you use ACF, Toolset, JetEngine, Pods or MetaBox', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'options'     => [
					'text'       => __( 'Text,Textarea,Number,Email,Url,Password', 'piotnetforms' ),
					'image'      => __( 'Image', 'piotnetforms' ),
					'gallery'    => __( 'Gallery', 'piotnetforms' ),
					'select'     => __( 'Select', 'piotnetforms' ),
					'radio'      => __( 'Radio', 'piotnetforms' ),
					'checkbox'   => __( 'Checkbox', 'piotnetforms' ),
					'true_false' => __( 'True / False', 'piotnetforms' ),
					'date'       => __( 'Date', 'piotnetforms' ),
					'time'       => __( 'Time', 'piotnetforms' ),
					'repeater'   => __( 'ACF Repeater', 'piotnetforms' ),
					'google_map' => __( 'ACF Google Map', 'piotnetforms' ),
					'acf_relationship' => __( 'ACF Relationship', 'piotnetforms' ),
					'file' => __( 'ACF File', 'piotnetforms' ),
					'jet_engine_repeater' => __( 'JetEngine Repeater', 'piotnetforms' ),
					'meta_box_group' => __( 'MetaBox Group', 'piotnetforms' ),
					'metabox_google_map' => __( 'MetaBox Google Map', 'piotnetforms' ),
				],
				'value'       => 'text',
			]
		);
		$this->add_control(
			'repeater_id',
			[
				'type' => 'hidden',
			],
			[
				'overwrite' => 'true',
			]
		);
		$this->add_control(
			'repeater_item_title',
			[
				'type' => 'hidden',
				'default' => '{{{ submit_post_custom_field }}}',
			]
		);
		$repeater_items = $this->get_group_controls();

		$this->new_group_controls();
		$this->add_control(
			'',
			[
				'type'           => 'repeater-item',
				'remove_label'   => __( 'Remove Item', 'piotnetforms' ),
				'controls'       => $repeater_items,
				'controls_query' => '.piotnet-control-repeater-field',
			]
		);
		$repeater_list = $this->get_group_controls();

		$this->add_control(
			'submit_post_custom_fields_list',
			[
				'type'           => 'repeater',
				'label'          => __( 'Custom Fields List', 'piotnetforms' ),
				'value'          => '',
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
			]
		);
	}
	private function add_style_controls() {
		$this->add_control(
			'text_color',
			[
				'type'      => 'color',
				'label'     => 'Text Color',
				'selectors' => [
					'{{WRAPPER}}' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_text_typography_controls(
			'text_typography',
			[
				'selectors' => '{{WRAPPER}}',
			]
		);
	}
	private function add_stripe_payment_setting_controls() {
		$this->add_control(
			'piotnetforms_stripe_enable',
			[
				'label'        => __( 'Enable', 'piotnetforms' ),
				'type'         => 'switch',
				'default'      => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
			]
		);
		$this->add_control(
			'piotnetforms_stripe_currency',
			[
				'label'     => __( 'Currency', 'piotnetforms' ),
				'type'      => 'select',
				'options'   => [
					'USD' => 'USD',
					'AED' => 'AED',
					'AFN' => 'AFN',
					'ALL' => 'ALL',
					'AMD' => 'AMD',
					'ANG' => 'ANG',
					'AOA' => 'AOA',
					'ARS' => 'ARS',
					'AUD' => 'AUD',
					'AWG' => 'AWG',
					'AZN' => 'AZN',
					'BAM' => 'BAM',
					'BBD' => 'BBD',
					'BDT' => 'BDT',
					'BGN' => 'BGN',
					'BIF' => 'BIF',
					'BMD' => 'BMD',
					'BND' => 'BND',
					'BOB' => 'BOB',
					'BRL' => 'BRL',
					'BSD' => 'BSD',
					'BWP' => 'BWP',
					'BZD' => 'BZD',
					'CAD' => 'CAD',
					'CDF' => 'CDF',
					'CHF' => 'CHF',
					'CLP' => 'CLP',
					'CNY' => 'CNY',
					'COP' => 'COP',
					'CRC' => 'CRC',
					'CVE' => 'CVE',
					'CZK' => 'CZK',
					'DJF' => 'DJF',
					'DKK' => 'DKK',
					'DOP' => 'DOP',
					'DZD' => 'DZD',
					'EGP' => 'EGP',
					'ETB' => 'ETB',
					'EUR' => 'EUR',
					'FJD' => 'FJD',
					'FKP' => 'FKP',
					'GBP' => 'GBP',
					'GEL' => 'GEL',
					'GIP' => 'GIP',
					'GMD' => 'GMD',
					'GNF' => 'GNF',
					'GTQ' => 'GTQ',
					'GYD' => 'GYD',
					'HKD' => 'HKD',
					'HNL' => 'HNL',
					'HRK' => 'HRK',
					'HTG' => 'HTG',
					'HUF' => 'HUF',
					'IDR' => 'IDR',
					'ILS' => 'ILS',
					'INR' => 'INR',
					'ISK' => 'ISK',
					'JMD' => 'JMD',
					'JPY' => 'JPY',
					'KES' => 'KES',
					'KGS' => 'KGS',
					'KHR' => 'KHR',
					'KMF' => 'KMF',
					'KRW' => 'KRW',
					'KYD' => 'KYD',
					'KZT' => 'KZT',
					'LAK' => 'LAK',
					'LBP' => 'LBP',
					'LKR' => 'LKR',
					'LRD' => 'LRD',
					'LSL' => 'LSL',
					'MAD' => 'MAD',
					'MDL' => 'MDL',
					'MGA' => 'MGA',
					'MKD' => 'MKD',
					'MMK' => 'MMK',
					'MNT' => 'MNT',
					'MOP' => 'MOP',
					'MRO' => 'MRO',
					'MUR' => 'MUR',
					'MVR' => 'MVR',
					'MWK' => 'MWK',
					'MXN' => 'MXN',
					'MYR' => 'MYR',
					'MZN' => 'MZN',
					'NAD' => 'NAD',
					'NGN' => 'NGN',
					'NIO' => 'NIO',
					'NOK' => 'NOK',
					'NPR' => 'NPR',
					'NZD' => 'NZD',
					'PAB' => 'PAB',
					'PEN' => 'PEN',
					'PGK' => 'PGK',
					'PHP' => 'PHP',
					'PKR' => 'PKR',
					'PLN' => 'PLN',
					'PYG' => 'PYG',
					'QAR' => 'QAR',
					'RON' => 'RON',
					'RSD' => 'RSD',
					'RUB' => 'RUB',
					'RWF' => 'RWF',
					'SAR' => 'SAR',
					'SBD' => 'SBD',
					'SCR' => 'SCR',
					'SEK' => 'SEK',
					'SGD' => 'SGD',
					'SHP' => 'SHP',
					'SLL' => 'SLL',
					'SOS' => 'SOS',
					'SRD' => 'SRD',
					'STD' => 'STD',
					'SZL' => 'SZL',
					'THB' => 'THB',
					'TJS' => 'TJS',
					'TOP' => 'TOP',
					'TRY' => 'TRY',
					'TTD' => 'TTD',
					'TWD' => 'TWD',
					'TZS' => 'TZS',
					'UAH' => 'UAH',
					'UGX' => 'UGX',
					'UYU' => 'UYU',
					'UZS' => 'UZS',
					'VND' => 'VND',
					'VUV' => 'VUV',
					'WST' => 'WST',
					'XAF' => 'XAF',
					'XCD' => 'XCD',
					'XOF' => 'XOF',
					'XPF' => 'XPF',
					'YER' => 'YER',
					'ZAR' => 'ZAR',
					'ZMW' => 'ZMW',
				],
				'value'     => 'USD',
				'condition' => [
					'piotnetforms_stripe_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_subscriptions',
			[
				'label'        => __( 'Subscriptions', 'piotnetforms' ),
				'type'         => 'switch',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
				'description'  => __( 'E.g bills every day, 2 weeks, 3 months, 1 year', 'piotnetforms' ),
				'condition'    => [
					'piotnetforms_stripe_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_subscriptions_only_price_enable',
			[
				'label'        => __( 'Subscriptions use only price?', 'piotnetforms' ),
				'type'         => 'switch',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
				'description'  => __( 'E.g bills every day, 2 weeks, 3 months, 1 year', 'piotnetforms' ),
				'conditions'  => [
					[
						'name' => 'piotnetforms_stripe_enable',
						'operator' => '==',
						'value' => 'yes'
					],
					[
						'name' => 'piotnetforms_stripe_subscriptions',
						'operator' => '==',
						'value' => 'yes'
					]
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_subscriptions_price_id',
			[
				'label'       => __( 'Price ID', 'piotnetforms' ),
				'type'        => 'text',
				'placeholder' => __( 'E.g: price_1JLgKMBi8bDi9DwedUFZ66fv', 'piotnetforms' ),
				'conditions'  => [
					[
						'name' => 'piotnetforms_stripe_enable',
						'operator' => '==',
						'value' => 'yes'
					],
					[
						'name' => 'piotnetforms_stripe_subscriptions',
						'operator' => '==',
						'value' => 'yes'
					],
					[
						'name' => 'piotnetforms_stripe_subscriptions_only_price_enable',
						'operator' => '==',
						'value' => 'yes'
					]
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_subscriptions_product_name',
			[
				'label'       => __( 'Product Name* (Required)', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'value'       => 'Piotnet Forms',
				'conditions'  => [
					[
						'name' => 'piotnetforms_stripe_enable',
						'operator' => '==',
						'value' => 'yes'
					],
					[
						'name' => 'piotnetforms_stripe_subscriptions',
						'operator' => '==',
						'value' => 'yes'
					],
					[
						'name' => 'piotnetforms_stripe_subscriptions_only_price_enable',
						'operator' => '!=',
						'value' => 'yes'
					]
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_subscriptions_product_id',
			[
				'label'       => __( 'Product ID (Optional)', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'value'       => '',
				'conditions'  => [
					[
						'name' => 'piotnetforms_stripe_enable',
						'operator' => '==',
						'value' => 'yes'
					],
					[
						'name' => 'piotnetforms_stripe_subscriptions',
						'operator' => '==',
						'value' => 'yes'
					],
					[
						'name' => 'piotnetforms_stripe_subscriptions_only_price_enable',
						'operator' => '!=',
						'value' => 'yes'
					]
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_tax_rate_enable',
			[
				'label'        => __( 'Use Tax?', 'piotnetforms' ),
				'type'         => 'switch',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
				'conditions'  => [
					[
						'name' => 'piotnetforms_stripe_enable',
						'operator' => '==',
						'value' => 'yes'
					],
					[
						'name' => 'piotnetforms_stripe_subscriptions',
						'operator' => '==',
						'value' => 'yes'
					]
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_tax_rate',
			[
				'label'       => __( 'Tax ID', 'piotnetforms' ),
				'type'        => 'text',
				'placeholder' => __( 'E.g: txr_1JJsT9Bi8bDi9Dwe8vDZZOVJ', 'piotnetforms' ),
				'conditions'  => [
					[
						'name' => 'piotnetforms_stripe_enable',
						'operator' => '==',
						'value' => 'yes'
					],
					[
						'name' => 'piotnetforms_stripe_subscriptions',
						'operator' => '==',
						'value' => 'yes'
					],
					[
						'name' => 'piotnetforms_stripe_tax_rate_enable',
						'operator' => '==',
						'value' => 'yes'
					]
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_subscriptions_field_enable',
			[
				'label'        => __( 'Subscriptions Plan Select Field', 'piotnetforms' ),
				'type'         => 'switch',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
				'conditions'  => [
					[
						'name' => 'piotnetforms_stripe_enable',
						'operator' => '==',
						'value' => 'yes'
					],
					[
						'name' => 'piotnetforms_stripe_subscriptions',
						'operator' => '==',
						'value' => 'yes'
					],
					[
						'name' => 'piotnetforms_stripe_subscriptions_only_price_enable',
						'operator' => '!=',
						'value' => 'yes'
					]
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_subscriptions_field',
			[
				'label'       => __( 'Subscriptions Plan Select Field Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'description' => __( 'E.g [field id="plan_select"]', 'piotnetforms' ),
				'condition'   => [
					'piotnetforms_stripe_enable'        => 'yes',
					'piotnetforms_stripe_subscriptions' => 'yes',
				],
			]
		);
		//repeater
		$this->new_group_controls();
		$this->add_control(
			'piotnetforms_stripe_subscriptions_field_enable_repeater',
			[
				'label'        => __( 'Subscriptions Plan Select Field', 'piotnetforms' ),
				'type'         => 'switch',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
			]
		);
		$this->add_control(
			'piotnetforms_stripe_subscriptions_field_value',
			[
				'label'       => __( 'Subscriptions Plan Field Value', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'placeholder' => __( 'E.g Daily, Weekly, 3 Months, Yearly', 'piotnetforms' ),
				'condition'   => [
					'piotnetforms_stripe_subscriptions_field_enable_repeater' => 'yes',
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_subscriptions_interval',
			[
				'label'   => __( 'Interval* (Required)', 'piotnetforms' ),
				'type'    => 'select',
				'options' => [
					'day'   => 'day',
					'week'  => 'week',
					'month' => 'month',
					'year'  => 'year',
				],
				'value'   => 'year',
			]
		);
		$this->add_control(
			'piotnetforms_stripe_subscriptions_interval_count',
			[
				'label'       => __( 'Interval Count* (Required)', 'piotnetforms' ),
				'type'        => 'number',
				'value'       => 1,
				'description' => __( 'Interval "month", Interval Count "3" = Bills every 3 months', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'piotnetforms_stripe_subscriptions_amount',
			[
				'label'       => __( 'Amount', 'piotnetforms' ),
				'type'        => 'number',
				'description' => __( 'E.g 100, 1000', 'piotnetforms' ),
				'condition'   => [
					'piotnetforms_stripe_subscriptions_amount_field_enable!' => 'yes',
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_subscriptions_one_time_fee',
			[
				'label' => __( 'One-time Fee', 'piotnetforms' ),
				'type'  => 'number',
				'value' => 0,
			]
		);
		$this->add_control(
			'piotnetforms_stripe_subscriptions_amount_field_enable',
			[
				'label'        => __( 'Amount Field Enable', 'piotnetforms' ),
				'type'         => 'switch',
				'default'      => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
			]
		);
		$this->add_control(
			'piotnetforms_stripe_subscriptions_amount_field',
			[
				'label'       => __( 'Amount Field Shortcode', 'piotnetforms' ),
				'type'        => 'select',
				'get_fields'  => true,
				'description' => __( 'E.g [field id="amount_yearly"]', 'piotnetforms' ),
				'condition'   => [
					'piotnetforms_stripe_subscriptions_amount_field_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_subscriptions_cancel',
			[
				'label'        => __( 'Canceling Subscriptions', 'piotnetforms' ),
				'type'         => 'switch',
				'default'      => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
			]
		);
		$this->add_control(
			'piotnetforms_stripe_subscriptions_cancel_add',
			[
				'label'     => __( '+', 'piotnetforms' ),
				'type'      => 'number',
				'value'     => 0,
				'condition' => [
					'piotnetforms_stripe_subscriptions_cancel' => 'yes',
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_subscriptions_cancel_add_unit',
			[
				'label'     => __( 'Unit', 'piotnetforms' ),
				'type'      => 'select',
				'options'   => [
					'day'   => 'day',
					'month' => 'month',
					'year'  => 'year',
				],
				'value'     => 'day',
				'condition' => [
					'piotnetforms_stripe_subscriptions_cancel' => 'yes',
				],
			]
		);
		$this->add_control(
			'repeater_id',
			[
				'type' => 'hidden',
			],
			[
				'overwrite' => 'true',
			]
		);
		$repeater_items = $this->get_group_controls();

		$this->new_group_controls();
		$this->add_control(
			'',
			[
				'type'           => 'repeater-item',
				'remove_label'   => __( 'Remove Item', 'piotnetforms' ),
				'controls'       => $repeater_items,
				'controls_query' => '.piotnet-control-repeater-field',
			]
		);
		$repeater_list = $this->get_group_controls();

		$this->add_control(
			'piotnetforms_stripe_subscriptions_list',
			[
				'type'           => 'repeater',
				'label'          => __( 'Subscriptions Plan List', 'piotnetforms' ),
				'value'          => '',
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
				'condition'   => [
					'piotnetforms_stripe_enable'         => 'yes',
					'piotnetforms_stripe_subscriptions' => 'yes',
					'piotnetforms_stripe_subscriptions_field_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_amount',
			[
				'label'       => __( 'Amount', 'piotnetforms' ),
				'type'        => 'number',
				'description' => __( 'E.g 100, 1000', 'piotnetforms' ),
				'condition'   => [
					'piotnetforms_stripe_enable'         => 'yes',
					'piotnetforms_stripe_amount_field_enable!' => 'yes',
					'piotnetforms_stripe_subscriptions!' => 'yes',
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_amount_field_enable',
			[
				'label'        => __( 'Amount Field Enable', 'piotnetforms' ),
				'type'         => 'switch',
				'default'      => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
				'condition'    => [
					'piotnetforms_stripe_enable'         => 'yes',
					'piotnetforms_stripe_subscriptions!' => 'yes',
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_amount_field',
			[
				'label'       => __( 'Amount Field Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'description' => __( 'E.g [field id="amount"]', 'piotnetforms' ),
				'condition'   => [
					'piotnetforms_stripe_enable'         => 'yes',
					'piotnetforms_stripe_amount_field_enable' => 'yes',
					'piotnetforms_stripe_subscriptions!' => 'yes',
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_create_invoice',
			[
				'label'        => __( 'Create Invoice?', 'piotnetforms' ),
				'type'         => 'switch',
				'default'      => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
                'condition'   => [
					'piotnetforms_stripe_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'pafe_stripe_tax_invoice',
			[
				'label'       => __( 'Tax ID', 'piotnetforms' ),
				'type'        => 'text',
				'description' => __( 'E.g txr_1JJsT9Bi8bDi9Dwe8vDZZOVJ', 'piotnetforms' ),
				'condition'   => [
					'piotnetforms_stripe_create_invoice' => 'yes',
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_customer_description',
			[
				'label'       => __( 'Payment Description', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'dynamic'     => true,
				'get_fields'  => true,
				'description' => __( 'E.g [field id="description"]', 'piotnetforms' ),
				'condition'   => [
					'piotnetforms_stripe_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_customer_field_name',
			[
				'label'       => __( 'Customer Name Field', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'dynamic'     => true,
				'get_fields'  => true,
				'description' => __( 'E.g [field id="name"]', 'piotnetforms' ),
				'condition'   => [
					'piotnetforms_stripe_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_customer_field_email',
			[
				'label'       => __( 'Customer Email Field', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'dynamic'     => true,
				'get_fields'  => true,
				'description' => __( 'E.g [field id="email"]', 'piotnetforms' ),
				'condition'   => [
					'piotnetforms_stripe_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_customer_info_field',
			[
				'label'       => __( 'Customer Description Field', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'dynamic'     => true,
				'get_fields'  => true,
				'description' => __( 'E.g [field id="description"]', 'piotnetforms' ),
				'condition'   => [
					'piotnetforms_stripe_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_customer_field_phone',
			[
				'label'       => __( 'Customer Phone Field', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'dynamic'     => true,
				'get_fields'  => true,
				'description' => __( 'E.g [field id="phone"]', 'piotnetforms' ),
				'condition'   => [
					'piotnetforms_stripe_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_customer_field_address_line1',
			[
				'label'       => __( 'Customer Address Line 1 Field', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'dynamic'     => true,
				'get_fields'  => true,
				'description' => __( 'E.g [field id="address_line1"]', 'piotnetforms' ),
				'condition'   => [
					'piotnetforms_stripe_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_customer_field_address_city',
			[
				'label'       => __( 'Customer Address City Field', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'dynamic'     => true,
				'get_fields'  => true,
				'description' => __( 'E.g [field id="city"]', 'piotnetforms' ),
				'condition'   => [
					'piotnetforms_stripe_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_customer_field_address_country',
			[
				'label'       => __( 'Customer Address Country Field', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'dynamic'     => true,
				'get_fields'  => true,
				'description' => __( 'E.g [field id="country"]. You should create a select field, the country value is two-letter country code (https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2)', 'piotnetforms' ),
				'condition'   => [
					'piotnetforms_stripe_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_customer_field_address_line2',
			[
				'label'       => __( 'Customer Address Line 2 Field', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'dynamic'     => true,
				'get_fields'  => true,
				'description' => __( 'E.g [field id="address_line2"]', 'piotnetforms' ),
				'condition'   => [
					'piotnetforms_stripe_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_customer_field_address_postal_code',
			[
				'label'       => __( 'Customer Address Postal Code Field', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'dynamic'     => true,
				'get_fields'  => true,
				'description' => __( 'E.g [field id="postal_code"]', 'piotnetforms' ),
				'condition'   => [
					'piotnetforms_stripe_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_customer_field_address_state',
			[
				'label'       => __( 'Customer Address State Field', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'dynamic'     => true,
				'get_fields'  => true,
				'description' => __( 'E.g [field id="state"]', 'piotnetforms' ),
				'condition'   => [
					'piotnetforms_stripe_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_customer_receipt_email',
			[
				'label'       => __( 'Receipt Email', 'piotnetforms' ),
				'type'        => 'text',
				'dynamic'     => true,
				'get_fields'  => true,
				'description' => __( 'E.g [field id="email"]', 'piotnetforms' ),
				'condition'   => [
					'piotnetforms_stripe_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_status_succeeded',
			[
				'label'     => __( 'Succeeded Status', 'piotnetforms' ),
				'type'      => 'text',
				'value'     => __( 'succeeded', 'piotnetforms' ),
				'condition' => [
					'piotnetforms_stripe_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_status_pending',
			[
				'label'     => __( 'Pending Status', 'piotnetforms' ),
				'type'      => 'text',
				'value'     => __( 'pending', 'piotnetforms' ),
				'condition' => [
					'piotnetforms_stripe_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_status_failed',
			[
				'label'     => __( 'Failed Status', 'piotnetforms' ),
				'type'      => 'text',
				'value'     => __( 'failed', 'piotnetforms' ),
				'condition' => [
					'piotnetforms_stripe_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_message_succeeded',
			[
				'label'       => __( 'Succeeded Message', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'value'       => __( 'Payment success', 'piotnetforms' ),
				'condition'   => [
					'piotnetforms_stripe_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_message_pending',
			[
				'label'       => __( 'Pending Message', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'value'       => __( 'Payment pending', 'piotnetforms' ),
				'condition'   => [
					'piotnetforms_stripe_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'piotnetforms_stripe_message_failed',
			[
				'label'       => __( 'Failed Message', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'value'       => __( 'Payment failed', 'piotnetforms' ),
				'condition'   => [
					'piotnetforms_stripe_enable' => 'yes',
				],
			]
		);
	}
	private function add_paypal_subscription_setting_controls() {
		$this->add_control(
			'paypal_subscription_enable',
			[
				'label'        => __( 'Enable', 'piotnetforms' ),
				'type'         => 'switch',
				'default'      => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
				'description'  => 'This feature only works on the frontend'
			]
		);
		$this->add_control(
			'paypal_subscription_currency',
			[
				'label'     => __( 'Currency', 'piotnetforms' ),
				'type'      => 'select',
				'options'   => [
					'AUD' => 'AUD',
					'BRL' => 'BRL',
					'CAD' => 'CAD',
					'CZK' => 'CZK',
					'DKK' => 'DKK',
					'EUR' => 'EUR',
					'HKD' => 'HKD',
					'HUF' => 'HUF',
					'INR' => 'INR',
					'ILS' => 'ILS',
					'MYR' => 'MYR',
					'MXN' => 'MXN',
					'TWD' => 'TWD',
					'NZD' => 'NZD',
					'NOK' => 'NOK',
					'PHP' => 'PHP',
					'PLN' => 'PLN',
					'GBP' => 'GBP',
					'RUB' => 'RUB',
					'SGD' => 'SGD',
					'SEK' => 'SEK',
					'CHF' => 'CHF',
					'THB' => 'THB',
					'USD' => 'USD',
				],
				'value'     => 'USD',
				'condition' => [
					'paypal_subscription_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'paypal_subscription_locale',
			[
				'label'       => __( 'Locale', 'piotnetforms' ),
				'type'        => 'text',
				'description' => __( 'E.g "fr_FR". By default PayPal smartly detects the correct locale for the buyer based on their geolocation and browser preferences. Go to this url to get your locale value <a href="https://developer.paypal.com/docs/checkout/reference/customize-sdk/#locale" target="_blank">https://developer.paypal.com/docs/checkout/reference/customize-sdk/#locale</a>', 'piotnetforms' ),
				'condition'   => [
					'paypal_subscription_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'paypal_subscription_sandbox',
			[
				'label'     => __( 'Subscription Sandbox', 'piotnetforms' ),
				'type'      => 'select',
				'options'   => [
					'no' => 'No',
					'yes' => 'Yes',
				],
				'value'     => 'no',
				'condition' => [
					'paypal_subscription_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'paypal_get_plans',
			[
				'type'    => 'html',
				'raw'     => '<button class="piotnetforms-admin-button-ajax piotnetforms-button piotnetforms-button-default" data-piotnetforms-paypal-get-plan>Get Plans <i class="fas fa-spinner fa-spin"></i></button>',
				'condition' => [
					'paypal_subscription_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'paypal_result',
			[
				'type'    => 'html',
				'label_block' => true,
				'raw'     => '<div class="piotnetforms-paypal-plans-result"></div>',
				'condition' => [
					'paypal_subscription_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'paypal_plan',
			[
				'label'       => __( 'Plan ID', 'piotnetforms' ),
				'type'        => 'text',
				'description' => __( 'E.g 100, 1000, [field id="amount"]', 'piotnetforms' ),
				'condition' => [
					'paypal_subscription_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'paypal_subscription_message_succeeded',
			[
				'label'       => __( 'Succeeded Message', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'value'       => __( 'Payment success', 'piotnetforms' ),
				'condition'   => [
					'paypal_subscription_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'paypal_subscription_message_failed',
			[
				'label'       => __( 'Failed Message', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'value'       => __( 'Payment failed', 'piotnetforms' ),
				'condition'   => [
					'paypal_subscription_enable' => 'yes',
				],
			]
		);
	}
	private function add_paypal_setting_controls() {
		$this->add_control(
			'paypal_enable',
			[
				'label'        => __( 'Enable', 'piotnetforms' ),
				'type'         => 'switch',
				'default'      => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
				'description'  => 'This feature only works on the frontend'
			]
		);
		$this->add_control(
			'paypal_currency',
			[
				'label'     => __( 'Currency', 'piotnetforms' ),
				'type'      => 'select',
				'options'   => [
					'AUD' => 'AUD',
					'BRL' => 'BRL',
					'CAD' => 'CAD',
					'CZK' => 'CZK',
					'DKK' => 'DKK',
					'EUR' => 'EUR',
					'HKD' => 'HKD',
					'HUF' => 'HUF',
					'INR' => 'INR',
					'ILS' => 'ILS',
					'MYR' => 'MYR',
					'MXN' => 'MXN',
					'TWD' => 'TWD',
					'NZD' => 'NZD',
					'NOK' => 'NOK',
					'PHP' => 'PHP',
					'PLN' => 'PLN',
					'GBP' => 'GBP',
					'RUB' => 'RUB',
					'SGD' => 'SGD',
					'SEK' => 'SEK',
					'CHF' => 'CHF',
					'THB' => 'THB',
					'USD' => 'USD',
				],
				'value'     => 'USD',
				'condition' => [
					'paypal_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'paypal_amount',
			[
				'label'       => __( 'Amount', 'piotnetforms' ),
				'type'        => 'text',
				'description' => __( 'E.g 100, 1000, [field id="amount"]', 'piotnetforms' ),
				'condition'   => [
					'paypal_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'paypal_description',
			[
				'label'       => __( 'Description', 'piotnetforms' ),
				'type'        => 'text',
				'description' => __( 'E.g Piotnet Forms, [field id="description"]', 'piotnetforms' ),
				'condition'   => [
					'paypal_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'paypal_locale',
			[
				'label'       => __( 'Locale', 'piotnetforms' ),
				'type'        => 'text',
				'description' => __( 'E.g "fr_FR". By default PayPal smartly detects the correct locale for the buyer based on their geolocation and browser preferences. Go to this url to get your locale value <a href="https://developer.paypal.com/docs/checkout/reference/customize-sdk/#locale" target="_blank">https://developer.paypal.com/docs/checkout/reference/customize-sdk/#locale</a>', 'piotnetforms' ),
				'condition'   => [
					'paypal_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'paypal_message_succeeded',
			[
				'label'       => __( 'Succeeded Message', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'value'       => __( 'Payment success', 'piotnetforms' ),
				'condition'   => [
					'paypal_enable' => 'yes',
				],
			]
		);
		$this->add_control(
			'paypal_message_failed',
			[
				'label'       => __( 'Failed Message', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'value'       => __( 'Payment failed', 'piotnetforms' ),
				'condition'   => [
					'paypal_enable' => 'yes',
				],
			]
		);
	}
	private function add_mollie_setting_controls() {
		if ( empty( get_option( 'piotnetforms-mollie-api-key' ) ) ) {
			$this->add_control(
				'mollie_note',
				[
					'type'    => 'html',
					'raw'     => '<p>Please enter mollie payment API Key at Dashboard->Piotnet Forms->Settings->Mollie Payment</p>',
				]
			);
		} else {
			$this->add_control(
				'mollie_enable',
				[
					'label'        => __( 'Enable', 'piotnetforms' ),
					'type'         => 'switch',
					'default'      => '',
					'label_on'     => 'Yes',
					'label_off'    => 'No',
					'return_value' => 'yes',
					'description'  => 'This feature only works on the frontend'
				]
			);
			$this->add_control(
				'mollie_currency',
				[
					'label'     => __( 'Currency', 'piotnetforms' ),
					'type'      => 'select',
					'options'   => [
						'AUD' => 'AUD',
						'BRL' => 'BRL',
						'CAD' => 'CAD',
						'CZK' => 'CZK',
						'DKK' => 'DKK',
						'EUR' => 'EUR',
						'HKD' => 'HKD',
						'HUF' => 'HUF',
						'INR' => 'INR',
						'ILS' => 'ILS',
						'MYR' => 'MYR',
						'MXN' => 'MXN',
						'TWD' => 'TWD',
						'NZD' => 'NZD',
						'NOK' => 'NOK',
						'PHP' => 'PHP',
						'PLN' => 'PLN',
						'GBP' => 'GBP',
						'RUB' => 'RUB',
						'SGD' => 'SGD',
						'SEK' => 'SEK',
						'CHF' => 'CHF',
						'THB' => 'THB',
						'USD' => 'USD',
					],
					'value'     => 'USD',
					'condition' => [
						'mollie_enable' => 'yes',
					],
				]
			);
			$this->add_control(
				'mollie_amount',
				[
					'label'       => __( 'Amount', 'piotnetforms' ),
					'type'        => 'text',
					'description' => __( 'E.g 100, 1000, [field id="amount"]', 'piotnetforms' ),
					'condition'   => [
						'mollie_enable' => 'yes',
					],
				]
			);
			$this->add_control(
				'mollie_description',
				[
					'label'       => __( 'Description', 'piotnetforms' ),
					'type'        => 'text',
					'description' => __( 'E.g Piotnet Forms, [field id="description"]', 'piotnetforms' ),
					'condition'   => [
						'mollie_enable' => 'yes',
					],
				]
			);
			$this->add_control(
				'mollie_locale',
				[
					'label'       => __( 'Locale', 'piotnetforms' ),
					'type'        => 'select',
					'options' => [
						'en_US'  => __( 'en_US', 'piotnetforms' ),
						'nl_NL'  => __( 'nl_NL', 'piotnetforms' ),
						'nl_BE'  => __( 'nl_BE', 'piotnetforms' ),
						'fr_FR'  => __( 'fr_FR', 'piotnetforms' ),
						'fr_BE'  => __( 'fr_BE', 'piotnetforms' ),
						'de_DE'  => __( 'de_DE', 'piotnetforms' ),
						'de_AT'  => __( 'de_AT', 'piotnetforms' ),
						'de_CH'  => __( 'de_CH', 'piotnetforms' ),
						'es_ES'  => __( 'es_ES', 'piotnetforms' ),
						'ca_ES'  => __( 'ca_ES', 'piotnetforms' ),
						'pt_PT'  => __( 'pt_PT', 'piotnetforms' ),
						'it_IT'  => __( 'it_IT', 'piotnetforms' ),
						'nb_NO'  => __( 'nb_NO', 'piotnetforms' ),
						'sv_SE'  => __( 'sv_SE', 'piotnetforms' ),
						'fi_FI'  => __( 'fi_FI', 'piotnetforms' ),
						'da_DK'  => __( 'da_DK', 'piotnetforms' ),
						'is_IS'  => __( 'is_IS', 'piotnetforms' ),
						'hu_HU'  => __( 'hu_HU', 'piotnetforms' ),
						'pl_PL'  => __( 'pl_PL', 'piotnetforms' ),
						'lv_LV'  => __( 'lv_LV', 'piotnetforms' ),
						'lt_LT'  => __( 'lt_LT', 'piotnetforms' ),
					],
					'condition'   => [
						'mollie_enable' => 'yes',
					],
				]
			);
			$this->add_control(
				'mollie_message_status',
				[
					'label'       => __( 'Payment Status', 'piotnetforms' ),
					'type'        => 'text',
					'default' => __( '[payment_status]', 'piotnetforms' ),
					'condition'   => [
						'mollie_enable' => 'yes',
					],
				]
			);
			$this->add_control(
				'mollie_message_succeeded',
				[
					'label'       => __( 'Succeeded Message', 'piotnetforms' ),
					'type'        => 'text',
					'default' => __( 'Payment success', 'piotnetforms' ),
					'condition'   => [
						'mollie_enable' => 'yes',
					],
				]
			);
			$this->add_control(
				'mollie_message_pending',
				[
					'label'       => __( 'Pending Message', 'piotnetforms' ),
					'type'        => 'text',
					'default' => __( 'Payment pending', 'piotnetforms' ),
					'condition'   => [
						'mollie_enable' => 'yes',
					],
				]
			);
			$this->add_control(
				'mollie_message_failed',
				[
					'label'       => __( 'Failed Message', 'piotnetforms' ),
					'type'        => 'text',
					'default' => __( 'Payment failed', 'piotnetforms' ),
					'condition'   => [
						'mollie_enable' => 'yes',
					],
				]
			);
			$this->add_control(
				'mollie_message_open',
				[
					'label'       => __( 'Open Message', 'piotnetforms' ),
					'type'        => 'text',
					'default' => __( 'Payment open', 'piotnetforms' ),
					'condition'   => [
						'mollie_enable' => 'yes',
					],
				]
			);
			$this->add_control(
				'mollie_message_canceled',
				[
					'label'       => __( 'Canceled Message', 'piotnetforms' ),
					'type'        => 'text',
					'default' => __( 'Payment canceled', 'piotnetforms' ),
					'condition'   => [
						'mollie_enable' => 'yes',
					],
				]
			);
			$this->add_control(
				'mollie_message_authorized',
				[
					'label'       => __( 'Authorized Message', 'piotnetforms' ),
					'type'        => 'text',
					'default' => __( 'Payment authorized', 'piotnetforms' ),
					'condition'   => [
						'mollie_enable' => 'yes',
					],
				]
			);
			$this->add_control(
				'mollie_message_expired',
				[
					'label'       => __( 'Expired Message', 'piotnetforms' ),
					'type'        => 'text',
					'default' => __( 'Payment expired', 'piotnetforms' ),
					'condition'   => [
						'mollie_enable' => 'yes',
					],
				]
			);
		}
	}
	private function add_recaptcha_setting_controls() {
		$this->add_control(
			'piotnetforms_recaptcha_enable',
			[
				'label'        => __( 'Enable', 'piotnetforms' ),
				'type'         => 'switch',
				'description'  => __( 'To use reCAPTCHA, you need to add the Site Key and Secret Key in Dashboard > Piotnet Forms > reCAPTCHA.' ),
				'default'      => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
			]
		);
		$this->add_control(
			'piotnetforms_recaptcha_hide_badge',
			[
				'label'        => __( 'Hide the reCaptcha v3 badge', 'piotnetforms' ),
				'type'         => 'switch',
				'default'      => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
			]
		);
	}
	private function add_email_setting_controls() {
		$this->add_control(
			'email_to',
			[
				'label'       => __( 'To', 'piotnetforms' ),
				'type'        => 'text',
				'default'     => get_option( 'admin_email' ),
				'placeholder' => get_option( 'admin_email' ),
				'label_block' => true,
				'title'       => __( 'Separate emails with commas', 'piotnetforms' ),
				'render_type' => 'none',
				'dynamic'     => true,
				'get_fields'  => true,
			]
		);
		/* translators: %s: Site title. */
		$default_message = sprintf( __( 'New message from "%s"', 'piotnetforms' ), get_option( 'blogname' ) );
		$this->add_control(
			'email_subject',
			[
				'label'       => __( 'Subject', 'piotnetforms' ),
				'type'        => 'text',
				'value'       => $default_message,
				'placeholder' => $default_message,
				'dynamic'     => true,
				'get_fields'  => true,
				'get_metadata' => true,
				'label_block' => true,
				'render_type' => 'none',
			]
		);
		$this->add_control(
			'email_content',
			[
				'label'       => __( 'Message', 'piotnetforms' ),
				'type'        => 'textarea',
				'dynamic'     => true,
				'get_fields'  => true,
				'get_metadata' => true,
				'value'       => '[all-fields]',
				'placeholder' => '[all-fields]',
				'description' => __( 'By default, all form fields are sent via shortcode: <code>[all-fields]</code>. Want to customize sent fields? Copy the shortcode that appears inside the field and paste it above. Enter this if you want to customize sent fields and remove line if field empty [field id="your_field_id"][remove_line_if_field_empty]', 'piotnetforms' ),
				'label_block' => true,
				'render_type' => 'none',
			]
		);

		$site_domain = str_ireplace( 'www.', '', parse_url( home_url(), PHP_URL_HOST ) );
		$this->add_control(
			'email_from',
			[
				'label'       => __( 'From Email', 'piotnetforms' ),
				'type'        => 'text',
				'value'       => 'email@' . $site_domain,
				'render_type' => 'none',
			]
		);

		$this->add_control(
			'email_from_name',
			[
				'label'       => __( 'From Name', 'piotnetforms' ),
				'type'        => 'text',
				'value'       => get_bloginfo( 'name' ),
				'render_type' => 'none',
			]
		);
		$this->add_control(
			'email_reply_to',
			[
				'label'       => __( 'Reply-To', 'piotnetforms' ),
				'type'        => 'text',
				'options'     => [
					'' => '',
				],
				'render_type' => 'none',
			]
		);
		$this->add_control(
			'email_to_cc',
			[
				'label'       => __( 'Cc', 'piotnetforms' ),
				'type'        => 'text',
				'value'       => '',
				'title'       => __( 'Separate emails with commas', 'piotnetforms' ),
				'render_type' => 'none',
			]
		);
		$this->add_control(
			'email_to_bcc',
			[
				'label'       => __( 'Bcc', 'piotnetforms' ),
				'type'        => 'text',
				'value'       => '',
				'title'       => __( 'Separate emails with commas', 'piotnetforms' ),
				'render_type' => 'none',
			]
		);
		$this->add_control(
			'form_metadata',
			[
				'label'       => __( 'Meta Data', 'piotnetforms' ),
				'type'        => 'select2',
				'label_block' => true,
				'value'       => [
					'date',
					'time',
					'page_url',
					'user_agent',
					'remote_ip',
				],
				'options'     => [
					'date'       => __( 'Date', 'piotnetforms' ),
					'time'       => __( 'Time', 'piotnetforms' ),
					'page_url'   => __( 'Page URL', 'piotnetforms' ),
					'user_agent' => __( 'User Agent', 'piotnetforms' ),
					'remote_ip'  => __( 'Remote IP', 'piotnetforms' ),
				],
				'render_type' => 'none',
			]
		);

		$this->add_control(
			'email_content_type',
			[
				'label' => __( 'Send As', 'piotnetforms' ),
				'type' => 'select',
				'default' => 'plain',
				'render_type' => 'none',
				'options' => [
					'html' => __( 'HTML', 'piotnetforms' ),
					'plain' => __( 'Plain', 'piotnetforms' ),
				],
			]
		);
	}
	private function add_email_2_setting_controls() {
		$this->add_control(
			'submit_id_shortcode_2',
			[
				'label'   => __( 'Submit ID Shortcode', 'piotnetforms' ),
				'type'    => 'html',
				'classes' => 'forms-field-shortcode',
				'raw'     => '<input class="piotnetforms-field-submit-id" value="[submit_id]" readonly />',
			]
		);
		$this->add_control(
			'email_to_2',
			[
				'label'       => __( 'To', 'piotnetforms' ),
				'type'        => 'text',
				'value'       => get_option( 'admin_email' ),
				'placeholder' => get_option( 'admin_email' ),
				'label_block' => true,
				'title'       => __( 'Separate emails with commas', 'piotnetforms' ),
				'render_type' => 'none',
				'dynamic'     => true,
				'get_fields'  => true,
			]
		);
		/* translators: %s: Site title. */
		$default_message = sprintf( __( 'New message from "%s"', 'piotnetforms' ), get_option( 'blogname' ) );
		$this->add_control(
			'email_subject_2',
			[
				'label'       => __( 'Subject', 'piotnetforms' ),
				'type'        => 'text',
				'value'       => $default_message,
				'placeholder' => $default_message,
				'dynamic'     => true,
				'get_fields'  => true,
				'get_metadata' => true,
				'label_block' => true,
				'render_type' => 'none',
			]
		);
		$this->add_control(
			'email_content_2',
			[
				'label'       => __( 'Message', 'piotnetforms' ),
				'type'        => 'textarea',
				'value'       => '[all-fields]',
				'placeholder' => '[all-fields]',
				'description' => __( 'By default, all form fields are sent via shortcode: <code>[all-fields]</code>. Want to customize sent fields? Copy the shortcode that appears inside the field and paste it above. Enter this if you want to customize sent fields and remove line if field empty [field id="your_field_id"][remove_line_if_field_empty]', 'piotnetforms' ),
				'label_block' => true,
				'render_type' => 'none',
				'dynamic'     => true,
				'get_fields'  => true,
				'get_metadata' => true,
			]
		);
		$site_domain = str_ireplace( 'www.', '', parse_url( home_url(), PHP_URL_HOST ) );
		$this->add_control(
			'email_from_2',
			[
				'label'       => __( 'From Email', 'piotnetforms' ),
				'type'        => 'text',
				'value'       => 'email@' . $site_domain,
				'render_type' => 'none',
			]
		);
		$this->add_control(
			'email_from_name_2',
			[
				'label'       => __( 'From Name', 'piotnetforms' ),
				'type'        => 'text',
				'value'       => get_bloginfo( 'name' ),
				'render_type' => 'none',
			]
		);
		$this->add_control(
			'email_reply_to_2',
			[
				'label'       => __( 'Reply-To', 'piotnetforms' ),
				'type'        => 'text',
				'options'     => [
					'' => '',
				],
				'render_type' => 'none',
			]
		);
		$this->add_control(
			'email_to_cc_2',
			[
				'label'       => __( 'Cc', 'piotnetforms' ),
				'type'        => 'text',
				'value'       => '',
				'title'       => __( 'Separate emails with commas', 'piotnetforms' ),
				'render_type' => 'none',
			]
		);
		$this->add_control(
			'email_to_bcc_2',
			[
				'label'       => __( 'Bcc', 'piotnetforms' ),
				'type'        => 'text',
				'value'       => '',
				'title'       => __( 'Separate emails with commas', 'piotnetforms' ),
				'render_type' => 'none',
			]
		);

		$this->add_control(
			'form_metadata_2',
			[
				'label'       => __( 'Meta Data', 'piotnetforms' ),
				'type'        => 'select2',
				'label_block' => true,
				'value'       => [],
				'options'     => [
					'date'       => __( 'Date', 'piotnetforms' ),
					'time'       => __( 'Time', 'piotnetforms' ),
					'page_url'   => __( 'Page URL', 'piotnetforms' ),
					'user_agent' => __( 'User Agent', 'piotnetforms' ),
					'remote_ip'  => __( 'Remote IP', 'piotnetforms' ),
				],
				'render_type' => 'none',
			]
		);

		$this->add_control(
			'email_content_type_2',
			[
				'label' => __( 'Send As', 'piotnetforms' ),
				'type' => 'select',
				'default' => 'plain',
				'render_type' => 'none',
				'options' => [
					'html' => __( 'HTML', 'piotnetforms' ),
					'plain' => __( 'Plain', 'piotnetforms' ),
				],
			]
		);
	}
	private function add_redirect_setting_controls() {
		$this->add_control(
			'redirect_to',
			[
				'label'       => __( 'Redirect To', 'piotnetforms' ),
				'type'        => 'text',
				'placeholder' => __( 'https://your-link.com', 'piotnetforms' ),
				'label_block' => true,
				'classes'     => 'piotnetforms-control-direction-ltr',
			]
		);

		$this->add_control(
			'redirect_open_new_tab',
			[
				'label' => __( 'Open In New Tab', 'piotnetforms' ),
				'type' => 'switch',
				'default' => '',
				'label_on' => 'Yes',
				'label_off' => 'No',
				'return_value' => 'yes',
			]
		);
	}
	private function add_woocommerce_add_to_cart_setting_controls() {
		$this->add_control(
			'woocommerce_add_to_cart_product_id',
			[
				'label'     => __( 'Product ID', 'piotnetforms' ),
				'type'      => 'text',
				'dynamic'   => [
					'active' => true,
				],
				'condition' => [
					'submit_actions' => 'woocommerce_add_to_cart',
				],
			]
		);
		$this->add_control(
			'woocommerce_add_to_cart_price',
			[
				'label'       => __( 'Price Field Shortcode', 'piotnetforms' ),
				'type'        => 'select',
				'get_fields'  => true,
				'placeholder' => __( 'Field Shortcode. E.g [field id="total"]', 'piotnetforms' ),
				'label_block' => true,
				'condition'   => [
					'submit_actions' => 'woocommerce_add_to_cart',
				],
			]
		);
		$this->add_control(
			'woocommerce_add_to_cart_custom_order_item_meta_enable',
			[
				'label'        => __( 'Custom Order Item Meta', 'piotnetforms' ),
				'type'         => 'switch',
				'default'      => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
			]
		);
		//repeater
		$this->new_group_controls();
		$this->add_control(
			'woocommerce_add_to_cart_custom_order_item_field_shortcode',
			[
				'label'       => __( 'Field Shortcode, Repeater Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
			]
		);
		$this->add_control(
			'woocommerce_add_to_cart_custom_order_item_remove_if_field_empty',
			[
				'label'        => __( 'Remove If Field Empty', 'piotnetforms' ),
				'type'         => 'switch',
				'default'      => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
			]
		);
		$this->add_control(
			'repeater_id',
			[
				'type' => 'hidden',
			],
			[
				'overwrite' => 'true',
			]
		);
		$repeater_items = $this->get_group_controls();

		$this->new_group_controls();
		$this->add_control(
			'',
			[
				'type'           => 'repeater-item',
				'remove_label'   => __( 'Remove Item', 'piotnetforms' ),
				'controls'       => $repeater_items,
				'controls_query' => '.piotnet-control-repeater-field',
			]
		);
		$repeater_list = $this->get_group_controls();

		$this->add_control(
			'woocommerce_add_to_cart_custom_order_item_list',
			[
				'type'           => 'repeater',
				'label'          => __( 'Custom Order Item List', 'piotnetforms' ),
				'value'          => '',
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
				'condition'   => [
					'woocommerce_add_to_cart_custom_order_item_meta_enable' => 'yes',
				],
			]
		);
	}
	private function add_webhook_setting_controls() {
		$this->add_control(
			'webhooks',
			[
				'label'       => __( 'Webhook URL', 'piotnetforms' ),
				'type'        => 'text',
				'placeholder' => __( 'https://your-webhook-url.com', 'piotnetforms' ),
				'label_block' => true,
				'description' => __( 'Enter the integration URL (like Zapier) that will receive the form\'s submitted data.', 'piotnetforms' ),
				'render_type' => 'none',
			]
		);
		$this->add_control(
			'webhooks_advanced_data',
			[
				'label'       => __( 'Advanced Data', 'piotnetforms' ),
				'type'        => 'switch',
				'default'     => 'no',
				'render_type' => 'none',
			]
		);
	}
	private function add_remote_request_setting_controls() {
		$this->add_control(
			'remote_request_url',
			[
				'label'       => __( 'URL', 'piotnetforms' ),
				'type'        => 'text',
				'placeholder' => __( 'https://your-endpoint-url.com', 'piotnetforms' ),
				'label_block' => true,
				'render_type' => 'none',
			]
		);
		//repeater
		$this->new_group_controls();
		$this->add_control(
			'remote_request_arguments_parameter',
			[
				'label'       => __( 'Parameter', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'placeholder' => __( 'E.g method, timeout', 'piotnetforms' ),
			]
		);

		$this->add_control(
			'remote_request_arguments_value',
			[
				'label'       => __( 'Value', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'placeholder' => __( 'E.g POST, 30', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'repeater_id',
			[
				'type' => 'hidden',
			],
			[
				'overwrite' => 'true',
			]
		);
		$repeater_items = $this->get_group_controls();

		$this->new_group_controls();
		$this->add_control(
			'',
			[
				'type'           => 'repeater-item',
				'remove_label'   => __( 'Remove Item', 'piotnetforms' ),
				'controls'       => $repeater_items,
				'controls_query' => '.piotnet-control-repeater-field',
			]
		);
		$repeater_list = $this->get_group_controls();

		$this->add_control(
			'remote_request_arguments_list',
			[
				'type'           => 'repeater',
				'label'          => __( 'Request arguments. E.g method = POST, method = GET, timeout = 30', 'piotnetforms' ),
				'value'          => '',
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
			]
		);
		//repeater
		$this->new_group_controls();
		$this->add_control(
			'remote_request_header_parameter',
			[
				'label'       => __( 'Parameter', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'placeholder' => __( 'E.g content-type, x-powered-by', 'piotnetforms' ),
			]
		);

		$this->add_control(
			'remote_request_header_value',
			[
				'label'       => __( 'Value', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'placeholder' => __( 'E.g application/php, PHP/5.3.3', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'repeater_id',
			[
				'type' => 'hidden',
			],
			[
				'overwrite' => 'true',
			]
		);
		$repeater_items = $this->get_group_controls();

		$this->new_group_controls();
		$this->add_control(
			'',
			[
				'type'           => 'repeater-item',
				'remove_label'   => __( 'Remove Item', 'piotnetforms' ),
				'controls'       => $repeater_items,
				'controls_query' => '.piotnet-control-repeater-field',
			]
		);
		$repeater_list = $this->get_group_controls();

		$this->add_control(
			'remote_request_header_list',
			[
				'type'           => 'repeater',
				'label'          => __( 'Header arguments. E.g content-type = application/php, x-powered-by = PHP/5.3.3', 'piotnetforms' ),
				'value'          => '',
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
			]
		);
		//repeater
		$this->new_group_controls();
		$this->add_control(
			'remote_request_body_parameter',
			[
				'label'       => __( 'Parameter', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'placeholder' => __( 'E.g email', 'piotnetforms' ),
			]
		);

		$this->add_control(
			'remote_request_body_value',
			[
				'label'       => __( 'Value', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'placeholder' => __( 'E.g [field id="email"]', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'repeater_id',
			[
				'type' => 'hidden',
			],
			[
				'overwrite' => 'true',
			]
		);
		$repeater_items = $this->get_group_controls();

		$this->new_group_controls();
		$this->add_control(
			'',
			[
				'type'           => 'repeater-item',
				'remove_label'   => __( 'Remove Item', 'piotnetforms' ),
				'controls'       => $repeater_items,
				'controls_query' => '.piotnet-control-repeater-field',
			]
		);
		$repeater_list = $this->get_group_controls();

		$this->add_control(
			'remote_request_body_list',
			[
				'type'           => 'repeater',
				'label'          => __( 'Body arguments. E.g email = [field id="email"]', 'piotnetforms' ),
				'value'          => '',
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
			]
		);
	}
	private function add_mailchimp_setting_controls() {
		$this->add_control(
			'mailchimp_note',
			[
				'type'        => 'html',
				'classes'     => 'piotnetforms-descriptor',
				'label_block' => true,
				'raw'         => __( 'You are using MailChimp API Key set in WP Dashboard > Piotnet Forms > MailChimp Integration. You can also set a different MailChimp API Key by choosing "Custom".', 'piotnetforms' ),
				'condition'   => [
					'mailchimp_api_key_source' => 'default',
				],
			]
		);
		$this->add_control(
			'mailchimp_api_key_source',
			[
				'label'   => __( 'API Key', 'piotnetforms' ),
				'type'    => 'select',
				'options' => [
					'default' => __( 'Default', 'piotnetforms' ),
					'custom'  => __( 'Custom', 'piotnetforms' ),
				],
				'value'   => 'default',
			]
		);
		$this->add_control(
			'mailchimp_api_key',
			[
				'label'       => __( 'Custom API Key', 'piotnetforms' ),
				'type'        => 'text',
				'condition'   => [
					'mailchimp_api_key_source' => 'custom',
				],
				'description' => __( 'Use this field to set a custom API Key for the current form', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'mailchimp_audience_id',
			[
				'label'       => __( 'Audience ID', 'piotnetforms' ),
				'type'        => 'text',
				'placeholder' => __( 'E.g 82e5ab8640', 'piotnetforms' ),
				'render_type' => 'none',
			]
		);
		$this->add_control(
			'mailchimp_acceptance_field_shortcode',
			[
				'label'       => __( 'Acceptance Field Shortcode', 'piotnetforms' ),
				'type'        => 'select',
				'get_fields'  => true,
				'placeholder' => __( 'E.g [field id="acceptance"]', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'mailchimp_groups_id',
			[
				'label'       => __( 'Groups', 'piotnetforms' ),
				'type'        => 'select2',
				'options'     => [],
				'label_block' => true,
				'multiple'    => true,
				'render_type' => 'none',
				'condition'   => [
					'mailchimp_list!' => '',
				],
			]
		);
		$this->add_control(
			'mailchimp_tags',
			[
				'label'       => __( 'Tags', 'piotnetforms' ),
				'description' => __( 'Add comma separated tags', 'piotnetforms' ),
				'type'        => 'text',
				'render_type' => 'none',
				'condition'   => [
					'mailchimp_list!' => '',
				],
			]
		);
		//repeater
		$this->new_group_controls();
		$this->add_control(
			'mailchimp_field_mapping_address',
			[
				'label'        => __( 'Address Field', 'piotnetforms' ),
				'type'         => 'switch',
				'default'      => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'mailchimp_field_mapping_tag_name',
			[
				'label'       => __( 'Tag Name. E.g EMAIL, FNAME, LNAME, ADDRESS', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'placeholder' => __( 'E.g EMAIL, FNAME, LNAME, ADDRESS', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'mailchimp_field_mapping_field_shortcode',
			[
				'label'       => __( 'Field Shortcode E.g [field id="email"]', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'placeholder' => __( 'E.g [field id="email"]', 'piotnetforms' ),
				'condition'   => [
					'mailchimp_field_mapping_address' => '',
				],
			]
		);

		$this->add_control(
			'mailchimp_field_mapping_address_field_shortcode_address_1',
			[
				'label'       => __( 'Address 1 Field Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'condition'   => [
					'mailchimp_field_mapping_address' => 'yes',
				],
			]
		);

		$this->add_control(
			'mailchimp_field_mapping_address_field_shortcode_address_2',
			[
				'label'       => __( 'Address 2 Field Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'condition'   => [
					'mailchimp_field_mapping_address' => 'yes',
				],
			]
		);

		$this->add_control(
			'mailchimp_field_mapping_address_field_shortcode_city',
			[
				'label'       => __( 'City Field Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'condition'   => [
					'mailchimp_field_mapping_address' => 'yes',
				],
			]
		);

		$this->add_control(
			'mailchimp_field_mapping_address_field_shortcode_state',
			[
				'label'       => __( 'State Field Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'condition'   => [
					'mailchimp_field_mapping_address' => 'yes',
				],
			]
		);

		$this->add_control(
			'mailchimp_field_mapping_address_field_shortcode_zip',
			[
				'label'       => __( 'Zip Field Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'condition'   => [
					'mailchimp_field_mapping_address' => 'yes',
				],
			]
		);

		$this->add_control(
			'mailchimp_field_mapping_address_field_shortcode_country',
			[
				'label'       => __( 'Country Field Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'condition'   => [
					'mailchimp_field_mapping_address' => 'yes',
				],
			]
		);
		$this->add_control(
			'repeater_id',
			[
				'type' => 'hidden',
			],
			[
				'overwrite' => 'true',
			]
		);
		$repeater_items = $this->get_group_controls();

		$this->new_group_controls();
		$this->add_control(
			'',
			[
				'type'           => 'repeater-item',
				'remove_label'   => __( 'Remove Item', 'piotnetforms' ),
				'controls'       => $repeater_items,
				'controls_query' => '.piotnet-control-repeater-field',
			]
		);
		$repeater_list = $this->get_group_controls();

		$this->add_control(
			'mailchimp_field_mapping_list',
			[
				'type'           => 'repeater',
				'label'          => __( 'Field Mapping', 'piotnetforms' ),
				'value'          => '',
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
			]
		);
	}
	private function add_sendinblue_setting_controls() {
		$this->add_control(
			'sendinblue_note',
			[
				'type' => 'html',
				'classes' => 'elementor-descriptor',
				'raw' => __( 'You are using Sendinblue API Key set in WP Dashboard > Piotnet Forms > Sendinblue Integration. You can also set a different Sendinblue API Key by choosing "Custom".', 'piotnetforms' ),
				'condition' => [
					'sendinblue_api_key_source' => 'default',
				],
				'label_block' => true,
			]
		);
		$this->add_control(
			'sendinblue_api_key_source',
			[
				'label' => __( 'API Key', 'piotnetforms' ),
				'type' => 'select',
				'options' => [
					'default' => __( 'Default', 'piotnetforms' ),
					'custom' => __( 'Custom', 'piotnetforms' ),
				],
				'default' => 'default',
			]
		);
		$this->add_control(
			'sendinblue_api_key',
			[
				'label' => __( 'Custom API Key', 'piotnetforms' ),
				'type' => 'text',
				'condition' => [
					'sendinblue_api_key_source' => 'custom',
				],
				'description' => __( 'Use this field to set a custom API Key for the current form', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'sendinblue_api_acceptance_field',
			[
				'label' => __( 'Acceptance Field?', 'piotnetforms' ),
				'type' => 'switch',
				'label_on' => __( 'Yes', 'piotnetforms' ),
				'label_off' => __( 'No', 'piotnetforms' ),
				'return_value' => 'yes',
				'default' => '',
			]
		);
		$this->add_control(
			'sendinblue_api_acceptance_field_shortcode',
			[
				'label' => __( 'Acceptance Field Shortcode', 'piotnetforms' ),
				'type' => 'text',
				'placeholder' => __( 'E.g [field id="acceptance"]', 'piotnetforms' ),
				'condition' => [
					'sendinblue_api_acceptance_field' => 'yes'
				]
			]
		);
		$this->add_control(
			'sendinblue_list_ids',
			[
				'label' => __( 'List ID', 'piotnetforms' ),
				'type' => 'text',
			]
		);
		$this->add_control(
			'sendinblue_api_get_list',
			[
				'type' => 'html',
				'raw' => __( '<button data-piotnetforms-sendinblue-get-list class="piotnetforms-admin-button-ajax elementor-button elementor-button-default" type="button">Get Lists <i class="fas fa-spinner fa-spin"></i></button><br><div class="piotnetforms-sendinblue-group-result" data-piotnetforms-sendinblue-api-get-list-results></div>', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'sendinblue_api_get_attr',
			[
				'type' => 'html',
				'raw' => __( '<div class="piotnetforms-sendinblue-attribute-result" data-piotnetforms-sendinblue-api-get-attributes-result></div>', 'piotnetforms' ),
			]
		);
		//repeater
		$this->new_group_controls();
		$this->add_control(
			'sendinblue_tagname',
			[
				'label'       => __( 'Tag Name', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'placeholder' => __( 'E.g email, name, last_name', 'piotnetforms' ),
			]
		);

		$this->add_control(
			'sendinblue_shortcode',
			[
				'label'       => __( 'Field Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'placeholder' => __( 'E.g [field id="email"]', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'repeater_id',
			[
				'type' => 'hidden',
			],
			[
				'overwrite' => 'true',
			]
		);
		$repeater_items = $this->get_group_controls();

		$this->new_group_controls();
		$this->add_control(
			'',
			[
				'type'           => 'repeater-item',
				'remove_label'   => __( 'Remove Item', 'piotnetforms' ),
				'controls'       => $repeater_items,
				'controls_query' => '.piotnet-control-repeater-field',
			]
		);
		$repeater_list = $this->get_group_controls();

		$this->add_control(
			'sendinblue_fields_map',
			[
				'type'           => 'repeater',
				'label'          => __( 'Field Mapping', 'piotnetforms' ),
				'value'          => '',
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
			]
		);
	}
	private function add_mailchimp_v3_setting_controls() {
		$this->add_control(
			'mailchimp_note_v3',
			[
				'type'        => 'html',
				'label_block' => true,
				'classes'     => 'piotnetforms-descriptor',
				'raw'         => __( 'You are using MailChimp API Key set in WP Dashboard > Piotnet Forms > MailChimp Integration. You can also set a different MailChimp API Key by choosing "Custom".', 'piotnetforms' ),
				'condition'   => [
					'mailchimp_api_key_source_v3' => 'default',
				],
			]
		);
		$this->add_control(
			'mailchimp_api_key_source_v3',
			[
				'label'   => __( 'API Key', 'piotnetforms' ),
				'type'    => 'select',
				'options' => [
					'default' => __( 'Default', 'piotnetforms' ),
					'custom'  => __( 'Custom', 'piotnetforms' ),
				],
				'value'   => 'default',
			]
		);
		$this->add_control(
			'mailchimp_api_key_v3',
			[
				'label'       => __( 'Custom API Key', 'piotnetforms' ),
				'type'        => 'text',
				'condition'   => [
					'mailchimp_api_key_source_v3' => 'custom',
				],
				'description' => __( 'Use this field to set a custom API Key for the current form', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'mailchimp_confirm_email_v3',
			[
				'label'        => __( 'Send confirm email?', 'piotnetforms' ),
				'type'         => 'switch',
				'default'      => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
			]
		);
		$this->add_control(
			'mailchimp_acceptance_enable_v3',
			[
				'label'        => __( 'Acceptance Field?', 'piotnetforms' ),
				'type'         => 'switch',
				'default'      => 'yes',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
			]
		);
		$this->add_control(
			'mailchimp_acceptance_field_shortcode_v3',
			[
				'label'       => __( 'Acceptance Field Shortcode', 'piotnetforms' ),
				'type'        => 'select',
				'get_fields'  => true,
				'placeholder' => __( 'E.g [field id="acceptance"]', 'piotnetforms' ),
				'condition' => [
					'mailchimp_acceptance_enable_v3' => 'yes'
				]
			]
		);
		$this->add_control(
			'mailchimp_get_data_list',
			[
				'type'        => 'html',
				'label_block' => true,
				'raw'         => __( '<button data-piotnetforms-mailchimp-get-data-list class="piotnetforms-admin-button-ajax piotnetforms-button piotnetforms-button-default" type="button">Get List IDs&ensp;<i class="fas fa-spinner fa-spin"></i></button><br><div data-piotnetforms-mailchimp-get-data-list-results></div>', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'mailchimp_list_id',
			[
				'label'       => __( 'List ID (<i>required</i>)', 'piotnetforms' ),
				'type'        => 'text',
				'placeholder' => __( 'E.g 82e5ab8640', 'piotnetforms' ),
				'render_type' => 'none',
			]
		);
		$this->add_control(
			'mailchimp_get_group_and_fields',
			[
				'type'        => 'html',
				'label_block' => true,
				'raw'         => __( '<button data-piotnetforms-mailchimp-get-group-and-field class="piotnetforms-admin-button-ajax piotnetforms-button piotnetforms-button-default" type="button">Get Groups and Fields <i class="fas fa-spinner fa-spin"></i></button><br>', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'mailchimp_get_groups',
			[
				'type' => 'html',
				'raw'  => __( '<div data-piotnetforms-mailchimp-get-groups></div>', 'piotnetforms' ),
				'label_block' => true,
			]
		);
		$this->add_control(
			'mailchimp_group_id',
			[
				'label'       => __( 'Group IDs', 'piotnetforms' ),
				'type'        => 'text',
				'placeholder' => __( 'E.g ade42df840', 'piotnetforms' ),
				'description' => 'You can add multiple group ids separated by commas.',
				'render_type' => 'none',
			]
		);
		$this->add_control(
			'mailchimp_get_merge_fields',
			[
				'type' => 'html',
				'raw'  => __( '<div data-piotnetforms-mailchimp-get-data-merge-fields></div>', 'piotnetforms' ),
				'label_block' => true,
			]
		);
		//repeater
		$this->new_group_controls();
		$this->add_control(
			'mailchimp_field_mapping_address_v3',
			[
				'label'        => __( 'Address Field', 'piotnetforms' ),
				'type'         => 'switch',
				'default'      => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'mailchimp_field_mapping_tag_name_v3',
			[
				'label'       => __( 'Tag Name', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'placeholder' => __( 'E.g EMAIL, FNAME, LNAME, ADDRESS', 'piotnetforms' ),
			]
		);

		$this->add_control(
			'mailchimp_field_mapping_field_shortcode_v3',
			[
				'label'       => __( 'Field Shortcode E.g [field id="email"]', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'placeholder' => __( 'E.g [field id="email"]', 'piotnetforms' ),
				'condition'   => [
					'mailchimp_field_mapping_address_v3' => '',
				],
			]
		);

		$this->add_control(
			'mailchimp_v3_field_mapping_address_field_shortcode_address_1',
			[
				'label'       => __( 'Address 1 Field Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'condition'   => [
					'mailchimp_field_mapping_address_v3' => 'yes',
				],
			]
		);

		$this->add_control(
			'mailchimp_v3_field_mapping_address_field_shortcode_address_2',
			[
				'label'       => __( 'Address 2 Field Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'condition'   => [
					'mailchimp_field_mapping_address_v3' => 'yes',
				],
			]
		);

		$this->add_control(
			'mailchimp_v3_field_mapping_address_field_shortcode_city',
			[
				'label'       => __( 'City Field Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'condition'   => [
					'mailchimp_field_mapping_address_v3' => 'yes',
				],
			]
		);

		$this->add_control(
			'mailchimp_v3_field_mapping_address_field_shortcode_state',
			[
				'label'       => __( 'State Field Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'condition'   => [
					'mailchimp_field_mapping_address_v3' => 'yes',
				],
			]
		);

		$this->add_control(
			'mailchimp_v3_field_mapping_address_field_shortcode_zip',
			[
				'label'       => __( 'Zip Field Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'condition'   => [
					'mailchimp_field_mapping_address_v3' => 'yes',
				],
			]
		);

		$this->add_control(
			'mailchimp_v3_field_mapping_address_field_shortcode_country',
			[
				'label'       => __( 'Country Field Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'condition'   => [
					'mailchimp_field_mapping_address_v3' => 'yes',
				],
			]
		);
		$this->add_control(
			'repeater_id',
			[
				'type' => 'hidden',
			],
			[
				'overwrite' => 'true',
			]
		);
		$repeater_items = $this->get_group_controls();

		$this->new_group_controls();
		$this->add_control(
			'',
			[
				'type'           => 'repeater-item',
				'remove_label'   => __( 'Remove Item', 'piotnetforms' ),
				'controls'       => $repeater_items,
				'controls_query' => '.piotnet-control-repeater-field',
			]
		);
		$repeater_list = $this->get_group_controls();

		$this->add_control(
			'mailchimp_field_mapping_list_v3',
			[
				'type'           => 'repeater',
				'label'          => __( 'Field Mapping', 'piotnetforms' ),
				'value'          => '',
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
			]
		);
	}
	private function add_mailerlite_v2_setting_controls() {
		$this->add_control(
			'mailerlite_v2_note',
			[
				'type'      => 'html',
				'classes'   => 'piotnetforms-descriptor',
				'label_block' => true,
				'raw'       => __( 'You are using MailerLite V2 API Key set in WP Dashboard > Piotnet Forms > MailerLite V2 Integration. You can also set a different MailerLite API Key by choosing "Custom".', 'piotnetforms' ),
				'condition' => [
					'mailerlite_api_key_source' => 'default',
				],
			]
		);
		$this->add_control(
			'mailerlite_api_key_source_v2',
			[
				'label' => __( 'API Key', 'piotnetforms' ),
				'type' => 'select',
				'options' => [
					'default' => __( 'Default', 'piotnetforms' ),
					'custom' => __( 'Custom', 'piotnetforms' ),
				],
				'default' => 'default',
			]
		);
		$this->add_control(
			'mailerlite_api_key_v2',
			[
				'label' => __( 'Custom API Key', 'piotnetforms' ),
				'type' => 'text',
				'condition' => [
					'mailerlite_api_key_source_v2' => 'custom',
				],
				'description' => __( 'Use this field to set a custom API Key for the current form', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'mailerlite_api_acceptance_field',
			[
				'label' => __( 'Acceptance Field?', 'piotnetforms' ),
				'type' => 'switch',
				'label_on' => __( 'Yes', 'piotnetforms' ),
				'label_off' => __( 'No', 'piotnetforms' ),
				'return_value' => 'yes',
				'default' => '',
			]
		);
		$this->add_control(
			'mailerlite_api_acceptance_field_shortcode',
			[
				'label' => __( 'Acceptance Field Shortcode', 'piotnetforms' ),
				'type'        => 'select',
				'get_fields'  => true,
				'placeholder' => __( 'E.g [field id="acceptance"]', 'piotnetforms' ),
				'condition' => [
					'mailerlite_api_acceptance_field' => 'yes'
				]
			]
		);
		$this->add_control(
			'mailerlite_api_get_groups',
			[
				'type' => 'html',
				'label_block' => true,
				'raw' => __( '<button data-piotnetforms-mailerlite-api-get-groups class="piotnetforms-admin-button-ajax elementor-button elementor-button-default" type="button">Get Groups <i class="fas fa-spinner fa-spin"></i></button><br><div class="piotnetforms-mailerlite-group-result" data-piotnetforms-mailerlite-api-get-groups-results></div>', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'mailerlite_api_group',
			[
				'label' => __( 'Group ID', 'piotnetforms' ),
				'type' => 'text',
				'placeholder' => __( 'Type your group here', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'mailerlite_api_get_fields',
			[
				'type' => 'html',
				'label_block' => true,
				'raw' => __( '<div class="piotnetforms-mailerlite-fields-result" data-piotnetforms-mailerlite-api-get-fields-results></div>', 'piotnetforms' ),
			]
		);
		//repeater
		$this->new_group_controls();
		$this->add_control(
			'mailerlite_v2_field_mapping_tag_name',
			[
				'label'       => __( 'Tag Name', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'placeholder' => __( 'E.g email, name, last_name', 'piotnetforms' ),
			]
		);

		$this->add_control(
			'mailerlite_v2_field_mapping_field_shortcode',
			[
				'label'       => __( 'Field Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'placeholder' => __( 'E.g [field id="email"]', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'repeater_id',
			[
				'type' => 'hidden',
			],
			[
				'overwrite' => 'true',
			]
		);
		$repeater_items = $this->get_group_controls();

		$this->new_group_controls();
		$this->add_control(
			'',
			[
				'type'           => 'repeater-item',
				'remove_label'   => __( 'Remove Item', 'piotnetforms' ),
				'controls'       => $repeater_items,
				'controls_query' => '.piotnet-control-repeater-field',
			]
		);
		$repeater_list = $this->get_group_controls();

		$this->add_control(
			'mailerlite_v2_field_mapping_list',
			[
				'type'           => 'repeater',
				'label'          => __( 'Field Mapping', 'piotnetforms' ),
				'value'          => '',
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
			]
		);
	}
	private function add_mailerlite_setting_controls() {
		$this->add_control(
			'mailerlite_note',
			[
				'type'      => 'html',
				'classes'   => 'piotnetforms-descriptor',
				'label_block' => true,
				'raw'       => __( 'You are using MailerLite API Key set in WP Dashboard > Piotnet Forms > MailerLite Integration. You can also set a different MailerLite API Key by choosing "Custom".', 'piotnetforms' ),
				'condition' => [
					'mailerlite_api_key_source' => 'default',
				],
			]
		);
		$this->add_control(
			'mailerlite_api_key_source',
			[
				'label'   => __( 'API Key', 'piotnetforms' ),
				'type'    => 'select',
				'options' => [
					'default' => __( 'Default', 'piotnetforms' ),
					'custom'  => __( 'Custom', 'piotnetforms' ),
				],
				'value'   => 'default',
			]
		);
		$this->add_control(
			'mailerlite_api_key',
			[
				'label'       => __( 'Custom API Key', 'piotnetforms' ),
				'type'        => 'text',
				'condition'   => [
					'mailerlite_api_key_source' => 'custom',
				],
				'description' => __( 'Use this field to set a custom API Key for the current form', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'mailerlite_group_id',
			[
				'label'       => __( 'GroupID', 'piotnetforms' ),
				'type'        => 'text',
				'placeholder' => __( 'E.g 87562190', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'mailerlite_email_field_shortcode',
			[
				'label'       => __( 'Email Field Shortcode* (Required)', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'placeholder' => __( 'E.g [field id="email"]', 'piotnetforms' ),
			]
		);
		//repeater
		$this->new_group_controls();
		$this->add_control(
			'mailerlite_field_mapping_tag_name',
			[
				'label'       => __( 'Tag Name', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'placeholder' => __( 'E.g email, name, last_name', 'piotnetforms' ),
			]
		);

		$this->add_control(
			'mailerlite_field_mapping_field_shortcode',
			[
				'label'       => __( 'Field Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'placeholder' => __( 'E.g [field id="email"]', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'repeater_id',
			[
				'type' => 'hidden',
			],
			[
				'overwrite' => 'true',
			]
		);
		$repeater_items = $this->get_group_controls();

		$this->new_group_controls();
		$this->add_control(
			'',
			[
				'type'           => 'repeater-item',
				'remove_label'   => __( 'Remove Item', 'piotnetforms' ),
				'controls'       => $repeater_items,
				'controls_query' => '.piotnet-control-repeater-field',
			]
		);
		$repeater_list = $this->get_group_controls();

		$this->add_control(
			'mailerlite_field_mapping_list',
			[
				'type'           => 'repeater',
				'label'          => __( 'Field Mapping', 'piotnetforms' ),
				'value'          => '',
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
			]
		);
	}
	private function add_getresponse_setting_controls() {
		$this->add_control(
			'getresponse_api_key_source',
			[
				'label'   => __( 'API Key', 'piotnetforms' ),
				'type'    => 'select',
				'options' => [
					'default' => __( 'Default', 'piotnetforms' ),
					'custom'  => __( 'Custom', 'piotnetforms' ),
				],
				'value'   => 'default',
			]
		);
		$this->add_control(
			'getresponse_api_key',
			[
				'label'     => __( 'Custom API Key', 'piotnetforms' ),
				'type'      => 'text',
				'condition' => [
					'getresponse_api_key_source' => 'custom',
				],
			]
		);
		$this->add_control(
			'getresponse_get_data_list',
			[
				'type'        => 'html',
				'label_block' => true,
				'raw'         => __( '<button data-piotnetforms-getresponse-get-data-list class="piotnetforms-admin-button-ajax piotnetforms-button piotnetforms-button-default" type="button">Get List&ensp;<i class="fas fa-spinner fa-spin"></i></button><div id="piotnetforms-getresponse-list"></div>', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'getresponse_campaign_id',
			[
				'label' => __( 'Campaign ID', 'piotnetforms' ),
				'type'  => 'text',
			]
		);
		$this->add_control(
			'getresponse_day_of_cycle',
			[
				'label' => __( 'Day Of Cycle', 'piotnetforms' ),
				'type'  => 'number',
			]
		);
		$this->add_control(
			'getresponse_get_data_custom_fields',
			[
				'type'        => 'html',
				'label_block' => true,
				'raw'         => __( '<button data-piotnetforms-getresponse-get-data-custom-fields class="piotnetforms-admin-button-ajax piotnetforms-button piotnetforms-button-default" type="button">Get Custom Fields&ensp;<i class="fas fa-spinner fa-spin"></i></button><div id="piotnetforms-getresponse-custom-fields"></div>', 'piotnetforms' ),
			]
		);
		//repeater
		$this->new_group_controls();
		$this->add_control(
			'getresponse_field_mapping_multiple',
			[
				'label'        => __( 'Multiple Field?', 'piotnetforms' ),
				'type'         => 'switch',
				'label_on'     => __( 'Yes', 'piotnetforms' ),
				'label_off'    => __( 'No', 'piotnetforms' ),
				'return_value' => 'yes',
				'default'      => '',
			]
		);

		$this->add_control(
			'getresponse_field_mapping_tag_name',
			[
				'label'       => __( 'Tag Name', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'placeholder' => __( 'E.g email, name, last_name', 'piotnetforms' ),
			]
		);

		$this->add_control(
			'getresponse_field_mapping_field_shortcode',
			[
				'label'       => __( 'Field Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'placeholder' => __( 'E.g [field id="email"]', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'repeater_id',
			[
				'type' => 'hidden',
			],
			[
				'overwrite' => 'true',
			]
		);
		$repeater_items = $this->get_group_controls();

		$this->new_group_controls();
		$this->add_control(
			'',
			[
				'type'           => 'repeater-item',
				'remove_label'   => __( 'Remove Item', 'piotnetforms' ),
				'controls'       => $repeater_items,
				'controls_query' => '.piotnet-control-repeater-field',
			]
		);
		$repeater_list = $this->get_group_controls();

		$this->add_control(
			'getresponse_field_mapping_list',
			[
				'type'           => 'repeater',
				'label'          => __( 'Property', 'piotnetforms' ),
				'value'          => '',
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
			]
		);
	}
	private function add_mailpoet_setting_controls() {
		$this->add_control(
			'mailpoet_send_confirmation_email',
			[
				'label'        => __( 'Send Confirmation Email?', 'piotnetforms' ),
				'type'         => 'switch',
				'label_on'     => __( 'Yes', 'piotnetforms' ),
				'label_off'    => __( 'No', 'piotnetforms' ),
				'return_value' => 'yes',
				'default'      => '',
				'description'  => __( 'Send confirmation email to customer, if not send subscriber to be added as unconfirmed.', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'mailpoet_send_welcome_email',
			[
				'label'        => __( 'Send Welcome Email', 'piotnetforms' ),
				'type'         => 'switch',
				'label_on'     => __( 'Yes', 'piotnetforms' ),
				'label_off'    => __( 'No', 'piotnetforms' ),
				'return_value' => 'yes',
				'default'      => '',
			]
		);
		$this->add_control(
			'mailpoet_skip_subscriber_notification',
			[
				'label'        => __( 'Skip subscriber notification?', 'piotnetforms' ),
				'type'         => 'switch',
				'label_on'     => __( 'Yes', 'piotnetforms' ),
				'label_off'    => __( 'No', 'piotnetforms' ),
				'return_value' => 'yes',
				'default'      => '',
			]
		);
		$this->add_control(
			'mailpoet_acceptance_field',
			[
				'label'        => __( 'Acceptance Field?', 'piotnetforms' ),
				'type'         => 'switch',
				'label_on'     => __( 'Yes', 'piotnetforms' ),
				'label_off'    => __( 'No', 'piotnetforms' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);
		$this->add_control(
			'mailpoet_acceptance_field_shortcode',
			[
				'label'       => __( 'Acceptance Field Shortcode', 'piotnetforms' ),
				'type'        => 'select',
				'get_fields'  => true,
				'value'       => __( '', 'piotnetforms' ),
				'placeholder' => __( 'Enter your shortcode here', 'piotnetforms' ),
				'condition'   => [
					'mailpoet_acceptance_field' => 'yes',
				],
			]
		);
		$this->add_control(
			'mailpoet_select_list',
			[
				'label'       => __( 'Select Lists', 'piotnetforms' ),
				'type'        => 'select2',
				'options'     => $this->mailpoet_get_list(),
				'label_block' => true,
			]
		);
		$this->add_control(
			'mailpoet_get_custom_field',
			[
				'type'        => 'html',
				'label_block' => true,
				'raw'         => __( '<button class="piotnetforms-button piotnetforms-admin-button-ajax piotnetforms-button-default piotnet-button-mailpoet-get-fields" data-piotnet-mailpoet-get-custom-fields>GET CUSTOM FIELDS <i class="fa fa-spinner fa-spin"></i></button><div class="piotnet-mailpoet-custom-fiedls-result" data-piotnet-mailpoet-result-custom-field></div>', 'piotnetforms' ),
			]
		);
		//repeater
		$this->new_group_controls();
		$this->add_control(
			'mailpoet_field_mapping_tag_name',
			[
				'label'       => __( 'Tag Name', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'placeholder' => __( 'E.g email, name, last_name', 'piotnetforms' ),
			]
		);

		$this->add_control(
			'mailpoet_field_mapping_field_shortcode',
			[
				'label'       => __( 'Field Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'placeholder' => __( 'E.g [field id="email"]', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'repeater_id',
			[
				'type' => 'hidden',
			],
			[
				'overwrite' => 'true',
			]
		);
		$repeater_items = $this->get_group_controls();

		$this->new_group_controls();
		$this->add_control(
			'',
			[
				'type'           => 'repeater-item',
				'remove_label'   => __( 'Remove Item', 'piotnetforms' ),
				'controls'       => $repeater_items,
				'controls_query' => '.piotnet-control-repeater-field',
			]
		);
		$repeater_list = $this->get_group_controls();

		$this->add_control(
			'mailpoet_field_mapping_list',
			[
				'type'           => 'repeater',
				'label'          => __( 'Field Mapping', 'piotnetforms' ),
				'value'          => '',
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
			]
		);
	}
	private function add_activecampaign_setting_controls() {
		$this->add_control(
			'activecampaign_note',
			[
				'type'        => 'html',
				'classes'     => 'piotnetforms-descriptor',
				'label_block' => true,
				'raw'         => __( 'You are using ActiveCampaign API Key set in WP Dashboard > Piotnet Forms > ActiveCampaign Integration. You can also set a different ActiveCampaign API Key by choosing "Custom".', 'piotnetforms' ),
				'condition'   => [
					'activecampaign_api_key_source' => 'default',
				],
			]
		);
		$this->add_control(
			'activecampaign_api_key_source',
			[
				'label'   => __( 'API Credentials', 'piotnetforms' ),
				'type'    => 'select',
				'options' => [
					'default' => __( 'Default', 'piotnetforms' ),
					'custom'  => __( 'Custom', 'piotnetforms' ),
				],
				'default' => 'default',
			]
		);
		$this->add_control(
			'activecampaign_api_url',
			[
				'label'       => __( 'Custom API URL', 'piotnetforms' ),
				'type'        => 'text',
				'condition'   => [
					'activecampaign_api_key_source' => 'custom',
				],
				'description' => __( 'Use this field to set a custom API URL for the current form', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'activecampaign_api_key',
			[
				'label'       => __( 'Custom API Key', 'piotnetforms' ),
				'type'        => 'text',
				'condition'   => [
					'activecampaign_api_key_source' => 'custom',
				],
				'description' => __( 'Use this field to set a custom API Key for the current form', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'activecampaign_edit_contact',
			[
				'label'        => __( 'Edit contact?', 'piotnetforms' ),
				'type'         => 'switch',
				'label_on'     => __( 'Yes', 'piotnetforms' ),
				'label_off'    => __( 'No', 'piotnetforms' ),
				'return_value' => 'yes',
				'default'      => '',
			]
		);
		$this->add_control(
			'activecampaign_get_data_list',
			[
				'type'            => 'html',
				'label_block'     => true,
				'raw'             => __( '<button data-piotnetforms-campaign-get-data-list class="piotnetforms-admin-button-ajax piotnetforms-button piotnetforms-button-default" type="button">Click Here To Get List IDs&ensp;<i class="fas fa-spinner fa-spin"></i></button><br><br><div data-piotnetforms-campaign-get-data-list-results></div>', 'piotnetforms' ),
				'content_classes' => 'your-class',
			]
		);
		$this->add_control(
			'activecampaign_list',
			[
				'label' => __( 'List ID* (Required)', 'piotnetforms' ),
				'type'  => 'number',
				'value' => 1,
			]
		);
		$this->add_control(
			'activecampaign_get_flelds',
			[
				'type'            => 'html',
				'label_block'     => true,
				'raw'             => __( '<div data-piotnetforms-campaign-get-fields></div>', 'piotnetforms' ),
			]
		);
		//repeater
		$this->new_group_controls();
		$this->add_control(
			'activecampaign_field_mapping_tag_name',
			[
				'label'       => __( 'Tag Name', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'placeholder' => __( 'E.g email, name, last_name', 'piotnetforms' ),
			]
		);

		$this->add_control(
			'activecampaign_field_mapping_field_shortcode',
			[
				'label'       => __( 'Field Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'placeholder' => __( 'E.g [field id="email"]', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'repeater_id',
			[
				'type' => 'hidden',
			],
			[
				'overwrite' => 'true',
			]
		);
		$repeater_items = $this->get_group_controls();

		$this->new_group_controls();
		$this->add_control(
			'',
			[
				'type'           => 'repeater-item',
				'remove_label'   => __( 'Remove Item', 'piotnetforms' ),
				'controls'       => $repeater_items,
				'controls_query' => '.piotnet-control-repeater-field',
			]
		);
		$repeater_list = $this->get_group_controls();

		$this->add_control(
			'activecampaign_field_mapping_list',
			[
				'type'           => 'repeater',
				'label'          => __( 'Field Mapping', 'piotnetforms' ),
				'value'          => '',
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
			]
		);
	}
	private function add_convertkit_setting_controls() {
		$this->add_control(
			'convertkit_note',
			[
				'type'        => 'html',
				'classes'     => 'piotnetforms-descriptor',
				'label_block' => true,
				'raw'         => __( 'You are using Convertkit API Key set in WP Dashboard > Piotnet Forms > Convertkit Integration. You can also set a different Convertkit API Key by choosing "Custom".', 'piotnetforms' ),
				'condition'   => [
					'convertkit_api_key_source' => 'default',
				],
			]
		);
		$this->add_control(
			'convertkit_api_key_source',
			[
				'label'   => __( 'API Credentials', 'piotnetforms' ),
				'type'    => 'select',
				'options' => [
					'default' => __( 'Default', 'piotnetforms' ),
					'custom'  => __( 'Custom', 'piotnetforms' ),
				],
				'default' => 'default',
			]
		);
		$this->add_control(
			'convertkit_api_key',
			[
				'label'       => __( 'Custom API Key', 'piotnetforms' ),
				'type'        => 'text',
				'condition'   => [
					'convertkit_api_key_source' => 'custom',
				],
				'description' => __( 'Use this field to set a custom API Key for the current form', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'convertkit_acceptance_field',
			[
				'label'        => __( 'Acceptance Field?', 'piotnetforms' ),
				'type'         => 'switch',
				'label_on'     => __( 'Yes', 'piotnetforms' ),
				'label_off'    => __( 'No', 'piotnetforms' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);
		$this->add_control(
			'convertkit_acceptance_field_shortcode',
			[
				'label'       => __( 'Acceptance Field Shortcode', 'piotnetforms' ),
				'type'        => 'select',
				'get_fields'  => true,
				'value'       => __( '', 'piotnetforms' ),
				'placeholder' => __( 'Enter your shortcode here', 'piotnetforms' ),
				'condition'   => [
					'convertkit_acceptance_field' => 'yes',
				],
			]
		);
		$this->add_control(
			'convertkit_form_id',
			[
				'label'       => __( 'Form ID', 'piotnetforms' ),
				'type'        => 'text',
			]
		);
		$this->add_control(
			'convertkit_get_data_list',
			[
				'type'            => 'html',
				'label_block'     => true,
				'raw'             => __( '<button data-piotnetforms-convertkit-get-data class="piotnetforms-admin-button-ajax piotnetforms-button piotnetforms-button-default" type="button">Click Here To Get Form IDs&ensp;<i class="fas fa-spinner fa-spin"></i></button><br><br><div data-piotnetforms-convertkit-get-data-results></div>', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'convertkit_get_data_fields',
			[
				'type'            => 'html',
				'label_block'     => true,
				'raw'             => __( '<div data-piotnetforms-convertkit-fields></div>', 'piotnetforms' ),
			]
		);
		//repeater
		$this->new_group_controls();
		$this->add_control(
			'convertkit_tag_name',
			[
				'label'       => __( 'Tag Name', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'placeholder' => __( 'E.g email, name, last_name', 'piotnetforms' ),
			]
		);

		$this->add_control(
			'convertkit_shortcode',
			[
				'label'       => __( 'Field Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'placeholder' => __( 'E.g [field id="email"]', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'repeater_id',
			[
				'type' => 'hidden',
			],
			[
				'overwrite' => 'true',
			]
		);
		$repeater_items = $this->get_group_controls();

		$this->new_group_controls();
		$this->add_control(
			'',
			[
				'type'           => 'repeater-item',
				'remove_label'   => __( 'Remove Item', 'piotnetforms' ),
				'controls'       => $repeater_items,
				'controls_query' => '.piotnet-control-repeater-field',
			]
		);
		$repeater_list = $this->get_group_controls();

		$this->add_control(
			'convertkit_field_mapping_list',
			[
				'type'           => 'repeater',
				'label'          => __( 'Field Mapping', 'piotnetforms' ),
				'value'          => '',
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
			]
		);
	}
	private function add_zohocrm_setting_controls() {
		$zoho_token = get_option( 'piotnetforms_zoho_access_token' );
		if ( empty( $zoho_token ) ) {
			$this->add_control(
				'zohocrm_note',
				[
					'type' => 'html',
					'raw'  => __( 'Please get the Zoho CRM token in admin page.', 'piotnetforms' ),
				]
			);
		} else {
			$this->add_control(
				'zohocrm_module',
				[
					'label'   => __( 'Zoho Module', 'piotnetforms' ),
					'type'    => 'select',
					'default' => 'Leads',
					'options' => [
						'Leads'          => __( 'Leads', 'piotnetforms' ),
						'Accounts'       => __( 'Accounts', 'piotnetforms' ),
						'Contacts'       => __( 'Contacts', 'piotnetforms' ),
						'campaigns'      => __( 'Campaigns', 'piotnetforms' ),
						'deals'          => __( 'Deals', 'piotnetforms' ),
						'tasks'          => __( 'Tasks', 'piotnetforms' ),
						'cases'          => __( 'Cases', 'piotnetforms' ),
						'events'         => __( 'Events', 'piotnetforms' ),
						'calls'          => __( 'Calls', 'piotnetforms' ),
						'solutions'      => __( 'Solutions', 'piotnetforms' ),
						'products'       => __( 'Products', 'piotnetforms' ),
						'vendors'        => __( 'Vendors', 'piotnetforms' ),
						'pricebooks'     => __( 'Pricebooks', 'piotnetforms' ),
						'quotes'         => __( 'Quotes', 'piotnetforms' ),
						'salesorders'    => __( 'Salesorders', 'piotnetforms' ),
						'purchaseorders' => __( 'Purchaseorders', 'piotnetforms' ),
						'invoices'       => __( 'Invoices', 'piotnetforms' ),
						'custom'         => __( 'Custom', 'piotnetforms' ),
						'notes'          => __( 'Notes', 'piotnetforms' ),
					],
				]
			);
			$this->add_control(
				'zohocrm_custom_module',
				[
					'label'       => __( 'Module API Name', 'piotnetforms' ),
					'type'        => 'text',
					'conditions' => [
						[
							'name' => 'zohocrm_module',
							'operator' => '==',
							'value' => 'custom'
						]
					],
				]
			);
			$this->add_control(
				'zohocrm_get_field_mapping',
				[
					'type' => 'html',
					'label_block' => true,
					'raw'  => __( '<button data-piotnetforms-zohocrm-get-tag-name class="piotnetforms-admin-button-ajax piotnetforms-button piotnetforms-button-default" type="button">Get Tag Name&ensp;<i class="fas fa-spinner fa-spin"></i></button><div id="piotnetforms-zohocrm-tag-name"></div>', 'piotnetforms' ),
				]
			);
			$this->add_control(
				'zoho_acceptance_field',
				[
					'label'        => __( 'Acceptance Field?', 'piotnetforms' ),
					'type'         => 'switch',
					'label_on'     => __( 'Yes', 'piotnetforms' ),
					'label_off'    => __( 'No', 'piotnetforms' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				]
			);
			$this->add_control(
				'zoho_acceptance_field_shortcode',
				[
					'label'       => __( 'Acceptance Field Shortcode', 'piotnetforms' ),
					'type'        => 'select',
					'get_fields'  => true,
					'value'       => __( '', 'piotnetforms' ),
					'placeholder' => __( 'Enter your shortcode here', 'piotnetforms' ),
					'condition'   => [
						'zoho_acceptance_field' => 'yes',
					],
				]
			);
			//repeater
			$this->new_group_controls();
			$this->add_control(
				'zohocrm_tagname',
				[
					'label'       => __( 'Tag Name', 'piotnetforms' ),
					'type'        => 'text',
					'label_block' => true,
				]
			);
			$this->add_control(
				'zohocrm_shortcode',
				[
					'label'       => __( 'Field Shortcode', 'piotnetforms' ),
					'type'        => 'select',
					'get_fields'  => true,
					'label_block' => true,
				]
			);
			$this->add_control(
				'repeater_id',
				[
					'type' => 'hidden',
				],
				[
					'overwrite' => 'true',
				]
			);
			$repeater_items = $this->get_group_controls();

			$this->new_group_controls();
			$this->add_control(
				'',
				[
					'type'           => 'repeater-item',
					'remove_label'   => __( 'Remove Item', 'piotnetforms' ),
					'controls'       => $repeater_items,
					'controls_query' => '.piotnet-control-repeater-field',
				]
			);
			$repeater_list = $this->get_group_controls();

			$this->add_control(
				'zohocrm_fields_map',
				[
					'type'           => 'repeater',
					'label'          => __( 'Fields Mapping', 'piotnetforms' ),
					'value'          => '',
					'label_block'    => true,
					'add_label'      => __( 'Add Item', 'piotnetforms' ),
					'controls'       => $repeater_list,
					'controls_query' => '.piotnet-control-repeater-list',
				]
			);
		}
	}
	private function add_pdfgenerator_setting_controls() {
		$this->add_control(
			'pdfgenerator_set_custom',
			[
				'label'        => __( 'Custom Layout', 'piotnetforms' ),
				'type'         => 'switch',
				'label_on'     => __( 'Yes', 'piotnetforms' ),
				'label_off'    => __( 'No', 'piotnetforms' ),
				'return_value' => 'yes',
				'default'      => '',
			]
		);
		$this->add_control(
			'pdfgenerator_import_template',
			[
				'label'        => __( 'Import Template', 'piotnetforms' ),
				'type'         => 'switch',
				'label_on'     => __( 'Yes', 'piotnetforms' ),
				'label_off'    => __( 'No', 'piotnetforms' ),
				'return_value' => 'yes',
				'default'      => '',
				'conditions' => [
					[
						'name' => 'pdfgenerator_set_custom',
						'operator' => '!=',
						'value' => ''
					]
				],
			]
		);
		$this->add_control(
			'pdfgenerator_template_url',
			[
				'label'       => __( 'PDF Template File URL', 'piotnetforms' ),
				'type'        => 'text',
				'placeholder' => __( 'Enter your title here', 'piotnetforms' ),
				'description' => 'Go to WP Dashboard > Media > Library > Upload PDF Template File > Get File URL',
				'conditions'  => [
					[
						'name' => 'pdfgenerator_set_custom',
						'operator' => '!=',
						'value' => ''
					],
				],
			]
		);
		$this->add_control(
			'pdfgenerator_size',
			[
				'label'     => __( 'PDF Size', 'piotnetforms' ),
				'type'      => 'select',
				'value'     => 'a4',
				'options' => [
					'a3'  => __( 'A3 (297*420)', 'piotnetforms' ),
					'a4' => __( 'A4 (210*297)', 'piotnetforms' ),
					'a5' => __( 'A5 (148*210)', 'piotnetforms' ),
					'letter' => __( 'Letter (215.9*279.4)', 'piotnetforms' ),
					'legal' => __( 'Legal (215.9*355.6)', 'piotnetforms' ),
				],
			]
		);
		$this->add_control(
			'pdfgenerator_font_family',
			[
				'label'     => __( 'Font Family', 'piotnetforms' ),
				'type'      => 'select',
				'value'     => 'a4',
				'options' => $this->piotnetforms_get_pdf_fonts()
			]
		);
		$this->add_control(
			'pdfgenerator_save_file',
			[
				'label'        => __( 'Save file in core coding.', 'piotnetforms' ),
				'type'         => 'switch',
				'description' 	=> 'This PDF file will be stored in: "wp-content\uploads\piotnetforms\files"',
				'label_on'     => __( 'Yes', 'piotnetforms' ),
				'label_off'    => __( 'Hide', 'piotnetforms' ),
				'return_value' => 'yes',
				'default'      => '',
			]
		);
		$this->add_control(
			'pdfgenerator_custom_file_name',
			[
				'label'        => __( 'Custom Export File Name', 'piotnetforms' ),
				'type'         => 'switch',
				'label_on'     => __( 'Yes', 'piotnetforms' ),
				'label_off'    => __( 'Hide', 'piotnetforms' ),
				'return_value' => 'yes',
				'default'      => '',
			]
		);
		$this->add_control(
			'pdfgenerator_export_file_name',
			[
				'label'       => __( 'File Name', 'piotnetforms' ),
				'type'        => 'text',
				'placeholder' => __( 'Enter your file name here', 'piotnetforms' ),
				'condition' => [
					'pdfgenerator_custom_file_name' => 'yes'
				],
				'render_type' => 'none'
			]
		);
		$this->add_control(
			'pdfgenerator_title',
			[
				'label'       => __( 'Title', 'piotnetforms' ),
				'type'        => 'text',
				'placeholder' => __( 'Enter your title here', 'piotnetforms' ),
				'conditions'   => [
					[
						'name' => 'pdfgenerator_set_custom',
						'operator' => '!=',
						'value' => 'yes'
					]
				],
			]
		);
		$this->add_control(
			'pdfgenerator_title_text_align',
			[
				'label'     => __( 'Title Align', 'piotnetforms' ),
				'type'      => 'select',
				'value'     => 'left',
				'options'   => [
					'left'   => __( 'Left', 'piotnetforms' ),
					'center' => __( 'Center', 'piotnetforms' ),
					'right'  => __( 'Right', 'piotnetforms' ),
				],
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-pdf-generator-preview__title' => 'text-align: {{VALUE}};',
				],
				'conditions'   => [
					[
						'name' => 'pdfgenerator_set_custom',
						'operator' => '!=',
						'value' => 'yes'
					]
				],
			]
		);
		$this->add_control(
			'pdfgenerator_title_font_size',
			[
				'label'      => __( 'Title Font Size', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [
					'px' => [
						'min'  => 0,
						'max'  => 50,
						'step' => 1,
					],
				],
				'value'      => [
					'unit' => 'px',
					'size' => 10,
				],
				'selectors'  => [
					'{{WRAPPER}} .piotnetforms-pdf-generator-preview__title' => 'font-size: {{SIZE}}{{UNIT}};',
				],
				'conditions'   => [
					[
						'name' => 'pdfgenerator_set_custom',
						'operator' => '!=',
						'value' => 'yes'
					]
				],
			]
		);
		$this->add_control(
			'pdfgenerator_font_size',
			[
				'label'      => __( 'Content Font Size', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [
					'px' => [
						'min'  => 0,
						'max'  => 50,
						'step' => 1,
					],
				],
				'value'      => [
					'unit' => 'px',
					'size' => 10,
				],
				'selectors'  => [
					'{{WRAPPER}} .piotnetforms-pdf-generator-preview__item' => 'font-size: {{SIZE}}{{UNIT}};',
				],
				'conditions'   => [
					[
						'name' => 'pdfgenerator_set_custom',
						'operator' => '==',
						'value' => 'yes'
					]
				],
			]
		);
		$this->add_control(
			'pdfgenerator_color',
			[
				'label'     => __( 'Title Color', 'piotnetforms' ),
				'type'      => 'color',
				'value'     => '#000',
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-pdf-generator-preview__item' => 'color: {{VALUE}}',
				],
				'conditions'   => [
					[
						'name' => 'pdfgenerator_set_custom',
						'operator' => '!=',
						'value' => 'yes'
					]
				],
			]
		);
		$this->add_control(
			'pdfgenerator_background_image_enable',
			[
				'label'        => __( 'Image Background', 'piotnetforms' ),
				'type'         => 'switch',
				'label_on'     => __( 'Yes', 'piotnetforms' ),
				'label_off'    => __( 'Hide', 'piotnetforms' ),
				'return_value' => 'yes',
				'default'      => 'yes',
				'conditions'   => [
					[
						'name' => 'pdfgenerator_import_template',
						'operator' => '!=',
						'value' => 'yes'
					]
				],
			]
		);
		$this->add_control(
			'pdfgenerator_background_image',
			[
				'label'       => __( 'Choose Image', 'piotnetforms' ),
				'type'        => 'media',
				'label_block' => true,
				'value'       => '',
				'description' => 'Only access image fomat jpg.',
				'conditions'   => [
					[
						'name' => 'pdfgenerator_background_image_enable',
						'operator' => '==',
						'value' => 'yes'
					]
				],
			]
		);
		$this->add_control(
			'pdfgenerator_heading_field_mapping_show_label',
			[
				'label'        => __( 'Show Label', 'piotnetforms' ),
				'type'         => 'switch',
				'label_on'     => __( 'Yes', 'piotnetforms' ),
				'label_off'    => __( 'No', 'piotnetforms' ),
				'return_value' => 'yes',
				'default'      => '',
				'conditions'   => [
					[
						'name' => 'pdfgenerator_set_custom',
						'operator' => '!=',
						'value' => 'yes'
					]
				],
			]
		);
		$this->add_control(
			'pdfgenerator_heading_field_mapping_font_size',
			[
				'label'      => __( 'Font Size', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [
					'px' => [
						'min'  => 0,
						'max'  => 50,
						'step' => 1,
					],
				],
				'value'      => [
					'unit' => 'px',
					'size' => 12,
				],
				'selectors'  => [
					'{{WRAPPER}} .piotnetforms-field-mapping__preview' => 'font-size: {{SIZE}}{{UNIT}};',
				],
				'conditions'   => [
					[
						'name' => 'pdfgenerator_set_custom',
						'operator' => '!=',
						'value' => 'yes'
					]
				],
			]
		);
		$this->add_control(
			'pdfgenerator_heading_field_mapping_color',
			[
				'label'     => __( 'Text Color', 'piotnetforms' ),
				'type'      => 'color',
				'value'     => '#000',
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-field-mapping__preview' => 'color: {{VALUE}}',
				],
				'conditions'   => [
					[
						'name' => 'pdfgenerator_set_custom',
						'operator' => '!=',
						'value' => 'yes'
					]
				],
			]
		);
		$this->add_control(
			'pdfgenerator_heading_field_mapping_text_align',
			[
				'label'     => __( 'Text Align', 'piotnetforms' ),
				'type'      => 'select',
				'default'   => 'left',
				'options'   => [
					'left'   => __( 'Left', 'piotnetforms' ),
					'center' => __( 'Center', 'piotnetforms' ),
					'right'  => __( 'Right', 'piotnetforms' ),
				],
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-field-mapping__preview' => 'text-align: {{VALUE}};',
				],
				'conditions'   => [
					[
						'name' => 'pdfgenerator_set_custom',
						'operator' => '!=',
						'value' => 'yes'
					]
				],
			]
		);
		//repeater
		$this->new_group_controls();
		$this->add_control(
			'pdfgenerator_field_shortcode',
			[
				'label'       => __( 'Field shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'placeholder' => __( 'E.g [field id="email"]', 'piotnetforms' ),
			]
		);

		$this->add_control(
			'pdfgenerator_field_type',
			[
				'label'   => __( 'Type', 'piotnetforms' ),
				'type'    => 'select',
				'value'   => 'default',
				'options' => [
					'default'      => __( 'Default', 'piotnetforms' ),
					'image'        => __( 'Image', 'piotnetforms' ),
					'image-upload' => __( 'Image upload', 'piotnetforms' ),
				],
			]
		);

		$this->add_control(
			'pdfgenerator_image_field',
			[
				'label'     => __( 'Choose Image', 'piotnetforms' ),
				'type'      => 'media',
				'value'     => '',
				'conditions' => [
					[
						'name' => 'pdfgenerator_field_type',
						'operator' => 'in',
						'value' => ['image-upload']
					]
				],
			]
		);

		$this->add_control(
			'custom_font',
			[
				'label'        => __( 'Custom Font', 'piotnetforms' ),
				'type'         => 'switch',
				'label_on'     => __( 'Yes', 'piotnetforms' ),
				'label_off'    => __( 'No', 'piotnetforms' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'conditions'    => [
					[
						'name' => 'pdfgenerator_field_type',
						'operator' => '==',
						'value' => 'default'
					]
				],
			]
		);
		$this->add_control(
			'auto_position',
			[
				'label'        => __( 'Auto Position?', 'piotnetforms' ),
				'type'         => 'switch',
				'label_on'     => __( 'Yes', 'piotnetforms' ),
				'label_off'    => __( 'No', 'piotnetforms' ),
				'return_value' => 'yes',
				'default'      => '',
				'conditions'    => [
					[
						'name' => 'pdfgenerator_field_type',
						'operator' => '==',
						'value' => 'default'
					]
				],
			]
		);
		$this->add_control(
			'font_weight',
			[
				'label'   => __( 'Font Style', 'piotnetforms' ),
				'type'    => 'select',
				'value'   => 'default',
				'options' => $this->piotnetforms_get_pdf_fonts_style(),
				'conditions'  => [
					[
						'name' => 'pdfgenerator_field_type',
						'operator' => '==',
						'value' => 'default'
					],
					[
						'name' => 'custom_font',
						'operator' => '==',
						'value' => 'yes'
					]
				],
			]
		);
		$this->add_control(
			'font_size',
			[
				'label'      => __( 'Font Size', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [
					'px' => [
						'min'  => 0,
						'max'  => 60,
						'step' => 1,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'value'      => [
					'unit' => 'px',
					'size' => 14,
				],
				'selectors'  => [
					'{{WRAPPER}} {{CURRENT_ITEM}}' => 'font-size: {{SIZE}}{{UNIT}};',
				],
				'conditions'  => [
					[
						'name' => 'pdfgenerator_field_type',
						'operator' => '==',
						'value' => 'default'
					],
					[
						'name' => 'custom_font',
						'operator' => '==',
						'value' => 'yes'
					]
				],
			]
		);
		// $this->add_control(
		// 	'font_weight',
		// 	[
		// 		'label'       => __( 'Font Style', 'piotnetforms' ),
		// 		'type'        => 'select',
		// 		'label_block' => true,
		// 		'options' => [
		// 			'N'  => __( 'Normal', 'piotnetforms' ),
		// 			'I'  => __( 'Italic', 'piotnetforms' ),
		// 			'B' => __( 'Bold', 'piotnetforms' ),
		// 			'BI' => __( 'Bold Italic', 'piotnetforms' ),
		// 		],
		// 		'conditions'  => [
		// 			[
		// 				'name' => 'pdfgenerator_field_type',
		// 				'operator' => '==',
		// 				'value' => 'default'
		// 			],
		// 			[
		// 				'name' => 'custom_font',
		// 				'operator' => '==',
		// 				'value' => 'yes'
		// 			]
		// 		],
		// 	]
		// );
		$this->add_control(
			'color',
			[
				'label'     => __( 'Text Color', 'piotnetforms' ),
				'type'      => 'color',
				'value'     => '#000',
				'selectors' => [
					'{{WRAPPER}} {{CURRENT_ITEM}}' => 'color: {{VALUE}}',
				],
				'conditions' => [
					[
						'name' => 'pdfgenerator_field_type',
						'operator' => '==',
						'value' => 'default'
					],
					[
						'name' => 'custom_font',
						'operator' => '==',
						'value' => 'yes'
					]
				],
			]
		);
		$this->add_control(
			'pdfgenerator_width',
			[
				'label'       => __( 'Width', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'slider',
				'size_units'  => [
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'value'       => [
					'unit' => '%',
					'size' => '90',
				],
				'selectors'   => [
					'{{WRAPPER}} {{CURRENT_ITEM}}'     => 'width: {{SIZE}}%;',
					'{{WRAPPER}} {{CURRENT_ITEM}} img' => 'width: {{SIZE}}%;',
				],
				'conditions'   => [
					[
						'name' => 'pdfgenerator_field_type',
						'operator' => 'in',
						'value' => [ 'default', 'image-upload', 'image' ]
					]
				],
			]
		);
		$this->add_control(
			'pdfgenerator_height',
			[
				'label'       => __( 'Height', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'slider',
				'size_units'  => [
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'value'       => [
					'unit' => '%',
					'size' => '90',
				],
				'selectors'   => [
					'{{WRAPPER}} {{CURRENT_ITEM}}'     => 'height: {{SIZE}}%;',
					'{{WRAPPER}} {{CURRENT_ITEM}} img' => 'height: {{SIZE}}%;',
				],
				'conditions'   => [
					[
						'name' => 'pdfgenerator_field_type',
						'operator' => 'in',
						'value' => ['image' ]
					]
				],
			]
		);
		$this->add_control(
			'pdfgenerator_set_x',
			[
				'label'       => __( 'Set X (mm)', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'slider',
				'size_units'  => [
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'value'       => [
					'unit' => '%',
					'size' => 0,
				],
				'description' => 'This feature only works while custom layout enabled.',
				'selectors'   => [
					'{{WRAPPER}} {{CURRENT_ITEM}}' => 'left: {{SIZE}}%;',
				],
				'conditions' => [
					[
						'name' => 'pdfgenerator_field_type',
						'operator' => '==',
						'value' => 'default'
					]
				]
			]
		);

		$this->add_control(
			'pdfgenerator_set_y',
			[
				'label'       => __( 'Set Y (mm)', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'slider',
				'size_units'  => [
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'value'       => [
					'unit' => '%',
					'size' => '0',
				],
				'description' => 'This feature only works while custom layout enabled.',
				'selectors'   => [
					'{{WRAPPER}} {{CURRENT_ITEM}}' => 'top: {{SIZE}}%;',
				],
				'conditions' => [
					[
						'name' => 'pdfgenerator_field_type',
						'operator' => '==',
						'value' => 'default'
					]
				]
			]
		);
		//Image
		$this->add_control(
			'pdfgenerator_image_set_x',
			[
				'label'       => __( 'Set X (mm)', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'slider',
				'size_units'  => [
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'value'       => [
					'unit' => '%',
					'size' => '30',
				],
				'description' => 'This feature only works while custom layout enabled.',
				'selectors'   => [
					'{{WRAPPER}} {{CURRENT_ITEM}} img' => 'left: {{SIZE}}%;',
				],
				'conditions' => [
					[
						'name' => 'pdfgenerator_field_type',
						'operator' => 'in',
						'value' => ['image', 'image-upload']
					]
				]
			]
		);
		$this->add_control(
			'pdfgenerator_image_set_y',
			[
				'label'       => __( 'Set Y (mm)', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'slider',
				'size_units'  => [
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'value'       => [
					'unit' => '%',
					'size' => '30',
				],
				'description' => 'This feature only works while custom layout enabled.',
				'selectors'   => [
					'{{WRAPPER}} {{CURRENT_ITEM}} img' => 'top: {{SIZE}}%;',
				],
				'conditions' => [
					[
						'name' => 'pdfgenerator_field_type',
						'operator' => 'in',
						'value' => ['image', 'image-upload']
					]
				]
			]
		);

		// $this->add_control(
		// 	'pdfgenerator_image_set_y',
		// 	[
		// 		'label' => __( 'Set Y (mm)', 'piotnetforms' ),
		// 		'label_block' => true,
		// 		'type' => 'slider',
		// 		'size_units'  => [
		// 			'%' => [
		// 				'min' => 0,
		// 				'max' => 100,
		// 			],
		// 		],
		// 		'description' => 'This feature only works while custom layout enabled.',
		// 		// 'selectors' => [
		// 		// 	'{{WRAPPER}} {{CURRENT_ITEM}} img' => 'top: {{SIZE}}%;',
		// 		// ],
		// 		// 'condition'=>[
		// 		// 	'pdfgenerator_field_type' => ['image', 'image-upload']
		// 		// ],
		// 	]
		// );
		$this->add_control(
			'repeater_id',
			[
				'type' => 'hidden',
			],
			[
				'overwrite' => 'true',
			]
		);
		$repeater_items = $this->get_group_controls();

		$this->new_group_controls();
		$this->add_control(
			'',
			[
				'type'           => 'repeater-item',
				'remove_label'   => __( 'Remove Item', 'piotnetforms' ),
				'controls'       => $repeater_items,
				'controls_query' => '.piotnet-control-repeater-field',
			]
		);
		$repeater_list = $this->get_group_controls();

		$this->add_control(
			'pdfgenerator_field_mapping_list',
			[
				'type'           => 'repeater',
				'label'          => __( 'Field Mapping', 'piotnetforms' ),
				'value'          => '',
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
				'conditions' => [
					[
						'name' => 'pdfgenerator_set_custom',
						'operator' => '==',
						'value' => 'yes'
					]
				]
			]
		);
	}

	//Webhook Slack
	private function add_webhook_slack_setting_controls() {
		$this->add_control(
			'slack_webhook_url',
			[
				'label'       => __( 'Webhook URL', 'piotnetforms' ),
				'type'        => 'text',
				'description' => __( "Enter the webhook URL that will receive the form's submitted data. <a href='https://slack.com/apps/A0F7XDUAZ-incoming-webhooks/' target='_blank'>Click here for instructions</a>", 'piotnetforms' ),
				'label_block' => true,
			]
		);

		$this->add_control(
			'slack_icon_url',
			[
				'label'       => __( 'Icon URL', 'piotnetforms' ),
				'type'        => 'text',
				'label_block' => true,
			]
		);

		$this->add_control(
			'slack_channel',
			[
				'label'       => __( 'Channel', 'piotnetforms' ),
				'type'        => 'text',
				'description' => 'Enter the channel ID / channel name'
			]
		);

		$this->add_control(
			'slack_username',
			[
				'label'       => __( 'Username', 'piotnetforms' ),
				'type'        => 'text',
			]
		);

		$this->add_control(
			'slack_pre_text',
			[
				'label'       => __( 'Pre Text', 'piotnetforms' ),
				'type'        => 'text',
			]
		);

		$this->add_control(
			'slack_title',
			[
				'label'       => __( 'Title', 'piotnetforms' ),
				'type'        => 'text',
			]
		);

		$this->add_control(
			'slack_message',
			[
				'label'       => __( 'Message', 'piotnetforms' ),
				'type'        => 'textarea',
				'value'       => '[all-fields]',
				'placeholder' => '[all-fields]',
				'description' => __( 'By default, all form fields are sent via shortcode: <code>[all-fields]</code>. Want to customize sent fields? Copy the shortcode that appears inside the field and paste it above. Enter this if you want to customize sent fields and remove line if field empty [field id="your_field_id"][remove_line_if_field_empty]', 'piotnetforms' ),
				'label_block' => true,
				'render_type' => 'none',
			]
		);

		$this->add_control(
			'slack_color',
			[
				'label'       => __( 'Color', 'piotnetforms' ),
				'type'        => 'color',
				'value'       => '#2eb886',
			]
		);

		$this->add_control(
			'slack_timestamp',
			[
				'label' => __( 'Timestamp', 'piotnetforms' ),
				'type' => 'switch',
				'default' => '',
				'label_on' => 'Yes',
				'label_off' => 'No',
				'return_value' => 'yes',
			]
		);
	}

	//SendGrid
	private function add_twilio_sendgrid_setting_controls() {
		$this->add_control(
			'twilio_sendgrid_api_key',
			[
				'label' => __( 'API Key', 'piotnetforms' ),
				'type' => 'text',
				'label_block' => true,
			]
		);

		$this->add_control(
			'twilio_sendgrid_get_data_list',
			[
				'type' => 'html',
				  'label_block' => true,
				'raw' => __( '<button data-piotnetforms-twilio-sendgrid-get-data-list class="piotnetforms-admin-button-ajax elementor-button elementor-button-default" type="button">Get List IDs&ensp;<i class="fas fa-spinner fa-spin"></i></button><br><div data-piotnetforms-twilio-sendgrid-get-data-list-results></div>', 'piotnetforms' ),
			]
		);

		$this->add_control(
			'twilio_sendgrid_list_ids',
			[
				'label' => __( 'List IDs', 'piotnetforms' ),
				'type' => 'text',
				'label_block' => true,
				'title' => __( 'Separate IDs with commas', 'piotnetforms' ),
				'render_type' => 'none',
			]
		);

		$this->add_control(
			'twilio_sendgrid_email_field_shortcode',
			[
				'label'       => __( 'Email Field Shortcode* (Required)', 'piotnetforms' ),
				'type' => 'select',
				'get_fields'  => true,
				'placeholder' => __( 'E.g [field id="email"]', 'piotnetforms' ),
				'label_block' => true,
			]
		);

		//repeater
		$this->new_group_controls();
		$this->add_control(
			'twilio_sendgrid_field_mapping_tag_name',
			[
				'label' => __( 'Tag Name', 'piotnetforms' ),
				'label_block' => true,
				'type' => 'text',
				'placeholder' => __( 'E.g first_name, last_name, phone_number', 'piotnetforms' ),
			]
		);

		$this->add_control(
			'twilio_sendgrid_field_mapping_field_shortcode',
			[
				'label' => __( 'Field Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type' => 'select',
				'get_fields'  => true,
				'placeholder' => __( 'E.g [field id="first_name"]', 'piotnetforms' ),
			]
		);

		$this->add_control(
			'repeater_id',
			[
				'type' => 'hidden',
			],
			[
				'overwrite' => 'true',
			]
		);
		$repeater_items = $this->get_group_controls();

		$this->new_group_controls();
		$this->add_control(
			'',
			[
				'type'           => 'repeater-item',
				'remove_label'   => __( 'Remove Item', 'piotnetforms' ),
				'controls'       => $repeater_items,
				'controls_query' => '.piotnet-control-repeater-field',
			]
		);
		$repeater_list = $this->get_group_controls();

		$this->add_control(
			'twilio_sendgrid_field_mapping_list',
			[
				'type'           => 'repeater',
				'label'          => __( 'Field Mapping', 'piotnetforms' ),
				'value'          => '',
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
			]
		);
	} //End SendGrid

	private function add_sendy_setting_controls() {
		$this->add_control(
			'sendy_url',
			[
				'label' => __( 'Sendy URL', 'piotnetforms' ),
				'type' => 'text',
				'placeholder' => 'http://your_sendy_installation/',
				'label_block' => true,
				'description' => __( 'Enter the URL where you have Sendy installed, including a trailing /', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'sendy_api_key',
			[
				'label' => __( 'API key', 'piotnetforms' ),
				'type' => 'text',
				'description' => __( 'To find it go to Settings (top right corner) -> Your API Key.', 'piotnetforms' ),
			]
		);
		$this->add_control(
			'sendy_list_id',
			[
				'label' => __( 'Sendy List ID', 'piotnetforms' ),
				'type' =>'text',
				'description' => __( 'The list id you want to subscribe a user to.', 'piotnetforms' ),
			]
		);

		$this->add_control(
			'sendy_name_field_shortcode',
			[
				'label' => __( 'Name Field Shortcode', 'piotnetforms' ),
				'type' =>'select',
				'description' => __( 'E.g [field id="name"]', 'piotnetforms' ),
				'get_fields'  => true,
			]
		);

		$this->add_control(
			'sendy_email_field_shortcode',
			[
				'label' => __( 'Email Field Shortcode', 'piotnetforms' ),
				'type' => 'select',
				'description' => __( 'E.g [field id="email"]', 'piotnetforms' ),
				'get_fields'  => true,
			]
		);

		$this->add_control(
			'sendy_gdpr_shortcode',
			[
				'label' => __( 'GDPR/CCPA Compliant Shortcode', 'piotnetforms' ),
				'type' => 'text',
				'placeholder' => __( 'E.g [field id="acceptance"]', 'piotnetforms' ),
			]
		);

		$this->new_group_controls();

		$this->add_control(
			'custom_field_name',
			[
				'label' => __( 'Sendy Custom Field Name', 'piotnetforms' ),
				'type' => 'text',
				'description' => __( 'Place the Name of the Sendy Custom Field', 'piotnetforms' ),
				'label_block' => true,
			]
		);
		$this->add_control(
			'custom_field_shortcode',
			[
				'label' => __( 'Custom Field Shortcode', 'piotnetforms' ),
				'type' =>'select',
				'description' => __( 'E.g [field id="phone"]', 'piotnetforms' ),
				'label_block' => true,
				'get_fields'  => true,
			]
		);
		$this->add_control(
			'repeater_id',
			[
				'type' => 'hidden',
			],
			[
				'overwrite' => 'true',
			]
		);
		$repeater_items = $this->get_group_controls();

		$this->new_group_controls();
		$this->add_control(
			'',
			[
				'type'           => 'repeater-item',
				'remove_label'   => __( 'Remove Item', 'piotnetforms' ),
				'controls'       => $repeater_items,
				'controls_query' => '.piotnet-control-repeater-field',
			]
		);
		$repeater_list = $this->get_group_controls();

		$this->add_control(
			'sendy_custom_fields',
			[
				'type'           => 'repeater',
				'label'          => __( 'Custom Fields', 'piotnetforms' ),
				'value'          => '',
				'label_block'    => true,
				'add_label'      => __( 'Add Custom Fields', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
			]
		);
	}
	// whatsapp
	private function add_whatsapp_setting_controls() {
		$this->add_control(
			'whatsapp_to',
			[
				'label' => __( 'Whatsapp To', 'piotnetforms' ),
				'type' => 'text',
				'description' => __( 'Phone with country code, like: +14155238886', 'piotnetforms' ),
			]
		);

		$this->add_control(
			'whatsapp_form',
			[
				'label' => __( 'Whatsapp Form', 'piotnetforms' ),
				'type' => 'text',
				'description' => __( 'Phone with country code, like: +14155238886', 'piotnetforms' ),
			]
		);

		$this->add_control(
			'whatsapp_message',
			[
				'label'       => __( 'Message', 'piotnetforms' ),
				'type'        => 'textarea',
				'value'       => '[all-fields]',
				'placeholder' => '[all-fields]',
				'description' => __( 'By default, all form fields are sent via shortcode: <code>[all-fields]</code>. Want to customize sent fields? Copy the shortcode that appears inside the field and paste it above. Enter this if you want to customize sent fields and remove line if field empty [field id="your_field_id"][remove_line_if_field_empty]', 'piotnetforms' ),
				'label_block' => true,
				'render_type' => 'none',
			]
		);
	}
	// end whatsapp

	private function add_twilio_sms_setting_controls() {
		$this->add_control(
			'twilio_sms_to',
			[
				'label' => __( 'To', 'piotnetforms' ),
				'type' =>'text',
				'description' => __( 'Phone with country code, like: +14155238886', 'piotnetforms' ),
			]
		);

		$this->add_control(
			'twilio_sms_messaging_service_id',
			[
				'label' => __( 'Messaging ServiceS ID', 'piotnetforms' ),
				'type' =>'text',
			]
		);

		$this->add_control(
			'twilio_sms_message',
			[
				'label' => __( 'Message', 'piotnetforms' ),
				'type' =>'textarea',
				'default' => '[all-fields]',
				'placeholder' => '[all-fields]',
				'description' => __( 'By default, all form fields are sent via shortcode: <code>[all-fields]</code>. Want to customize sent fields? Copy the shortcode that appears inside the field and paste it above. Enter this if you want to customize sent fields and remove line if field empty [field id="your_field_id"][remove_line_if_field_empty]', 'piotnetforms' ),
				'label_block' => true,
				'render_type' => 'none',
			]
		);
	}

	private function add_sendfox_setting_controls() {
		$this->add_control(
			'sendfox_list_id',
			[
				'label' => __( 'SendFox List ID', 'piotnetforms' ),
				'type' =>'text',
			]
		);

		$this->add_control(
			'sendfox_email_field_shortcode',
			[
				'label' => __( 'Email Field Shortcode', 'piotnetforms' ),
				'type' => 'select',
				'description' => __( 'E.g [field id="email"]', 'piotnetforms' ),
				'get_fields'  => true,
			]
		);
		$this->add_control(
			'sendfox_first_name_field_shortcode',
			[
				'label' => __( 'First Name Field Shortcode', 'piotnetforms' ),
				'type' => 'select',
				'description' => __( 'E.g [field id="first_name"]', 'piotnetforms' ),
				'get_fields'  => true,
			]
		);
		$this->add_control(
			'sendfox_last_name_field_shortcode',
			[
				'label' => __( 'Last Name Field Shortcode', 'piotnetforms' ),
				'type' => 'select',
				'description' => __( 'E.g [field id="last_name"]', 'piotnetforms' ),
				'get_fields'  => true,
			]
		);
	}

	private function form_options_setting_controls() {
		$this->add_control(
			'success_message',
			[
				'label'       => __( 'Success Message', 'piotnetforms' ),
				'type'        => 'text',
				'value'       => __( 'The form was sent successfully.', 'piotnetforms' ),
				'placeholder' => __( 'The form was sent successfully.', 'piotnetforms' ),
				'label_block' => true,
				'render_type' => 'none',
			]
		);

		$this->add_control(
			'error_message',
			[
				'label'       => __( 'Error Message', 'piotnetforms' ),
				'type'        => 'text',
				'default'     => __( 'An error occured.', 'piotnetforms' ),
				'placeholder' => __( 'An error occured.', 'piotnetforms' ),
				'label_block' => true,
				'render_type' => 'none',
			]
		);
		$this->add_control(
			'required_field_message',
			[
				'label'       => __( 'Required Message', 'piotnetforms' ),
				'type'        => 'text',
				'value'       => __( 'This field is required.', 'piotnetforms' ),
				'placeholder' => __( 'This field is required.', 'piotnetforms' ),
				'label_block' => true,
				'render_type' => 'none',
			]
		);
		$this->add_control(
			'invalid_message',
			[
				'label'       => __( 'Invalid Message', 'piotnetforms' ),
				'type'        => 'text',
				'value'       => __( "There's something wrong. The form is invalid.", 'piotnetforms' ),
				'placeholder' => __( "There's something wrong. The form is invalid.", 'piotnetforms' ),
				'label_block' => true,
				'render_type' => 'none',
			]
		);
		$this->add_control(
			'hidden_messages',
			[
				'label' => __( 'Hidden Messages', 'piotnetforms' ),
				'type' => 'switch',
				'default' => '',
				'label_on' => 'Yes',
				'label_off' => 'No',
				'return_value' => 'yes',
			]
		);
	}

	private function abandonment_setting_controls() {
		$this->add_control(
			'form_abandonment_enable',
			[
				'label' => __( 'Enable', 'piotnetforms' ),
				'type' => 'switch',
				'default' => '',
				'description' => __( 'This feature only works on the frontend.', 'piotnetforms' ),
				'label_on' => 'Yes',
				'label_off' => 'No',
				'return_value' => 'yes',
			]
		);
		$this->add_control(
			'form_abandonment_webhook_enable',
			[
				'label' => __( 'Enable Webhook', 'piotnetforms' ),
				'type' => 'switch',
				'default' => '',
				'description' => __( 'This feature only works on the frontend.', 'piotnetforms' ),
				'label_on' => 'Yes',
				'label_off' => 'No',
				'return_value' => 'yes',
				'condition' => [
					'form_abandonment_enable' => 'yes'
				]
			]
		);
		$this->add_control(
			'form_abandonment_webhook_url',
			[
				'label'       => __( 'Webhook url', 'piotnetforms' ),
				'type'        => 'text',
				'label_block' => true,
				'condition'   => [
					'form_abandonment_enable' => 'yes',
					'form_abandonment_webhook_enable' => 'yes'
				],
			]
		);
	}

	private function conditional_logic_setting_controls() {
		$this->add_control(
			'piotnetforms_conditional_logic_form_enable',
			[
				'label'        => __( 'Enable', 'piotnetforms' ),
				'type'         => 'switch',
				'default'      => '',
				'description'  => __( 'This feature only works on the frontend.', 'piotnetforms' ),
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'piotnetforms_conditional_logic_form_speed',
			[
				'label'       => __( 'Speed', 'piotnetforms' ),
				'type'        => 'text',
				'description' => __( 'E.g 100, 1000, slow, fast' ),
				'value'       => 400,
				'condition'   => [
					'piotnetforms_conditional_logic_form_enable' => 'yes',
				],
			]
		);

		$this->add_control(
			'piotnetforms_conditional_logic_form_easing',
			[
				'label'       => __( 'Easing', 'piotnetforms' ),
				'type'        => 'text',
				'description' => __( 'E.g swing, linear' ),
				'default'     => 'swing',
				'condition'   => [
					'piotnetforms_conditional_logic_form_enable' => 'yes',
				],
			]
		);
		//repeater
		$this->new_group_controls();
		$this->add_control(
			'piotnetforms_conditional_logic_form_if',
			[
				'label'       => __( 'Show this submit If', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'placeholder' => __( 'Field Shortcode', 'piotnetforms' ),
			]
		);

		$this->add_control(
			'piotnetforms_conditional_logic_form_comparison_operators',
			[
				'label'       => __( 'Comparison Operators', 'piotnetforms' ),
				'type'        => 'select',
				'label_block' => true,
				'options'     => [
					'not-empty' => __( 'not empty', 'piotnetforms' ),
					'empty'     => __( 'empty', 'piotnetforms' ),
					'='         => __( 'equals', 'piotnetforms' ),
					'!='        => __( 'not equals', 'piotnetforms' ),
					'>'         => __( '>', 'piotnetforms' ),
					'>='        => __( '>=', 'piotnetforms' ),
					'<'         => __( '<', 'piotnetforms' ),
					'<='        => __( '<=', 'piotnetforms' ),
					'checked'   => __( 'checked', 'piotnetforms' ),
					'unchecked' => __( 'unchecked', 'piotnetforms' ),
					'contains'  => __( 'contains', 'piotnetforms' ),
				],
			]
		);

		$this->add_control(
			'piotnetforms_conditional_logic_form_type',
			[
				'label'       => __( 'Type Value', 'piotnetforms' ),
				'type'        => 'select',
				'label_block' => true,
				'options'     => [
					'string' => __( 'String', 'piotnetforms' ),
					'number' => __( 'Number', 'piotnetforms' ),
				],
				'value'       => 'string',
				'condition'   => [
					'piotnetforms_conditional_logic_form_comparison_operators' => [ '=', '!=', '>', '>=', '<', '<=' ],
				],
			]
		);

		$this->add_control(
			'piotnetforms_conditional_logic_form_value',
			[
				'label'       => __( 'Value', 'piotnetforms' ),
				'type'        => 'text',
				'label_block' => true,
				'placeholder' => __( '50', 'piotnetforms' ),
				'condition'   => [
					'piotnetforms_conditional_logic_form_comparison_operators' => [ '=', '!=', '>', '>=', '<', '<=', 'contains' ],
				],
			]
		);

		$this->add_control(
			'piotnetforms_conditional_logic_form_and_or_operators',
			[
				'label'       => __( 'OR, AND Operators', 'piotnetforms' ),
				'type'        => 'select',
				'label_block' => true,
				'options'     => [
					'or'  => __( 'OR', 'piotnetforms' ),
					'and' => __( 'AND', 'piotnetforms' ),
				],
				'value'       => 'or',
			]
		);
		$this->add_control(
			'repeater_id',
			[
				'type' => 'hidden',
			],
			[
				'overwrite' => 'true',
			]
		);
		$repeater_items = $this->get_group_controls();

		$this->new_group_controls();
		$this->add_control(
			'',
			[
				'type'           => 'repeater-item',
				'remove_label'   => __( 'Remove Item', 'piotnetforms' ),
				'controls'       => $repeater_items,
				'controls_query' => '.piotnet-control-repeater-field',
			]
		);
		$repeater_list = $this->get_group_controls();

		$this->add_control(
			'piotnetforms_conditional_logic_form_list',
			[
				'type'           => 'repeater',
				'label'          => __( 'Conditional List', 'piotnetforms' ),
				'value'          => '',
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
				'condition'   => [
					'piotnetforms_conditional_logic_form_enable' => 'yes',
				],
			]
		);
	}

	private function conditional_for_actions_controls() {
		$this->add_control(
			'conditional_for_actions_enable',
			[
				'label'        => __( 'Conditional For Actions', 'piotnetforms' ),
				'type'         => 'switch',
				'default'      => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
			]
		);

		$actions         = [
			[
				'name'  => 'email',
				'label' => 'Email',
			],
			[
				'name'  => 'email2',
				'label' => 'Email 2',
			],
			[
				'name'  => 'booking',
				'label' => 'Booking',
			],
			[
				'name'  => 'redirect',
				'label' => 'Redirect',
			],
			[
				'name'  => 'register',
				'label' => 'Register',
			],
			[
				'name'  => 'login',
				'label' => 'Login',
			],
			[
				'name'  => 'update_user_profile',
				'label' => 'Update User Profile',
			],
			[
				'name'  => 'webhook',
				'label' => 'Webhook',
			],
			[
				'name'  => 'remote_request',
				'label' => 'Remote Request',
			],
			// [
			// 	'name'  => 'popup',
			// 	'label' => 'Popup',
			// ],
			// [
			// 	'name'  => 'open_popup',
			// 	'label' => 'Open Popup',
			// ],
			// [
			// 	'name'  => 'close_popup',
			// 	'label' => 'Close Popup',
			// ],
			[
				'name'  => 'submit_post',
				'label' => 'Submit Post',
			],
			[
				'name'  => 'woocommerce_add_to_cart',
				'label' => 'Woocommerce Add To Cart',
			],
			[
				'name'  => 'mailchimp_v3',
				'label' => 'MailChimp',
			],
			// [
			// 	'name'  => 'mailerlite',
			// 	'label' => 'MailerLite',
			// ],
			[
				'name'  => 'mailerlite_v2',
				'label' => 'MailerLite',
			],
			[
				'name'  => 'activecampaign',
				'label' => 'ActiveCampaign',
			],
			[
				'name'  => 'pdfgenerator',
				'label' => 'PDF Generator',
			],
			[
				'name'  => 'getresponse',
				'label' => 'Getresponse',
			],
			[
				'name'  => 'mailpoet',
				'label' => 'Mailpoet',
			],
			[
				'name'  => 'zohocrm',
				'label' => 'Zoho CRM',
			],
			[
				'name'  => 'google_calendar',
				'label' => 'Google Calendar',
			],
			[
				'name'  => 'webhook_slack',
				'label' => 'Webhook Slack',
			],
			[
				'name'  => 'sendy',
				'label' => 'Sendy',
			],
			[
				'name'  => 'sendfox',
				'label' => 'SendFox',
			],
			[
				'name'  => 'twilio_whatsapp',
				'label' => 'Twilio Whatsapp',
			],
			[
				'name'  => 'twilio_sms',
				'label' => 'Twilio SMS',
			],
			[
				'name'  => 'twilio_sendgrid',
				'label' => 'Twilio SendGrid',
			],
		];
		$actions_options = [];

		foreach ( $actions as $action ) {
			$actions_options[ $action['name'] ] = $action['label'];
		}

		//repeater
		$this->new_group_controls();

		$this->add_control(
			'conditional_for_actions_action',
			[
				'label'       => __( 'Action', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'options'	  => $actions_options,
			]
		);

		$this->add_control(
			'conditional_for_actions_if',
			[
				'label'       => __( 'Do this action If', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'placeholder' => __( 'Field Shortcode', 'piotnetforms' ),
			]
		);

		$this->add_control(
			'conditional_for_actions_comparison_operators',
			[
				'label'       => __( 'Comparison Operators', 'piotnetforms' ),
				'type'        => 'select',
				'label_block' => true,
				'options'     => [
					'not-empty' => __( 'not empty', 'piotnetforms' ),
					'empty'     => __( 'empty', 'piotnetforms' ),
					'='         => __( 'equals', 'piotnetforms' ),
					'!='        => __( 'not equals', 'piotnetforms' ),
					'>'         => __( '>', 'piotnetforms' ),
					'>='        => __( '>=', 'piotnetforms' ),
					'<'         => __( '<', 'piotnetforms' ),
					'<='        => __( '<=', 'piotnetforms' ),
					'checked'   => __( 'checked', 'piotnetforms' ),
					'unchecked' => __( 'unchecked', 'piotnetforms' ),
					'contains'  => __( 'contains', 'piotnetforms' ),
				],
			]
		);

		$this->add_control(
			'conditional_for_actions_type',
			[
				'label'       => __( 'Type Value', 'piotnetforms' ),
				'type'        => 'select',
				'label_block' => true,
				'options'     => [
					'string' => __( 'String', 'piotnetforms' ),
					'number' => __( 'Number', 'piotnetforms' ),
				],
				'value'       => 'string',
				'condition'   => [
					'conditional_for_actions_comparison_operators' => [ '=', '!=', '>', '>=', '<', '<=' ],
				],
			]
		);

		$this->add_control(
			'conditional_for_actions_value',
			[
				'label'       => __( 'Value', 'piotnetforms' ),
				'type'        => 'text',
				'label_block' => true,
				'placeholder' => __( '50', 'piotnetforms' ),
				'condition'   => [
					'conditional_for_actions_comparison_operators' => [ '=', '!=', '>', '>=', '<', '<=', 'contains' ],
				],
			]
		);

		$this->add_control(
			'conditional_for_actions_and_or_operators',
			[
				'label'       => __( 'OR, AND Operators', 'piotnetforms' ),
				'type'        => 'select',
				'label_block' => true,
				'options'     => [
					'or'  => __( 'OR', 'piotnetforms' ),
					'and' => __( 'AND', 'piotnetforms' ),
				],
				'value'       => 'or',
			]
		);
		$this->add_control(
			'repeater_id',
			[
				'type' => 'hidden',
			],
			[
				'overwrite' => 'true',
			]
		);
		$repeater_items = $this->get_group_controls();

		$this->new_group_controls();
		$this->add_control(
			'',
			[
				'type'           => 'repeater-item',
				'remove_label'   => __( 'Remove Item', 'piotnetforms' ),
				'controls'       => $repeater_items,
				'controls_query' => '.piotnet-control-repeater-field',
			]
		);
		$repeater_list = $this->get_group_controls();

		$this->add_control(
			'conditional_for_actions_list',
			[
				'type'           => 'repeater',
				'label'          => __( 'Conditional List', 'piotnetforms' ),
				'value'          => '',
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
				'condition'   => [
					'conditional_for_actions_enable' => 'yes',
				],
			]
		);
	}

	private function add_button_style_controls() {
		$this->add_text_typography_controls(
			'typography',
			[
				'selectors' => '{{WRAPPER}} a.piotnetforms-button, {{WRAPPER}} .piotnetforms-button',
			]
		);
		$this->add_control(
			'',
			[
				'type' => 'heading-tab',
				'tabs' => [
					[
						'name'   => 'submit_button_style_normal_tab',
						'title'  => __( 'NORMAL', 'piotnetforms' ),
						'active' => true,
					],
					[
						'name'  => 'submit_button_style_hover_tab',
						'title' => __( 'HOVER', 'piotnetforms' ),
					],
				],
			]
		);

		$normal_controls = $this->tab_button_style_controls(
			'style_normal',
			[
				'selectors' => '{{WRAPPER}} a.piotnetforms-button, {{WRAPPER}} .piotnetforms-button',
			]
		);
		$this->add_control(
			'submit_button_style_normal_tab',
			[
				'type'           => 'content-tab',
				'label'          => __( 'Normal', 'piotnetforms' ),
				'value'          => '',
				'active'         => true,
				'controls'       => $normal_controls,
				'controls_query' => '.piotnet-start-controls-tab',
			]
		);

		$hover_controls = $this->tab_button_style_controls(
			'style_hover',
			[
				'selectors' => '{{WRAPPER}} a.piotnetforms-button:hover, {{WRAPPER}} .piotnetforms-button:hover, {{WRAPPER}} a.piotnetforms-button:focus, {{WRAPPER}} .piotnetforms-button:focus',
			]
		);
		$this->add_control(
			'submit_button_style_hover_tab',
			[
				'type'           => 'content-tab',
				'label'          => __( 'Hover', 'piotnetforms' ),
				'value'          => '',
				'controls'       => $hover_controls,
				'controls_query' => '.piotnet-start-controls-tab',
			]
		);
	}
	private function tab_button_style_controls( string $name, $args = [] ) {
		$wrapper = isset( $args['selectors'] ) ? $args['selectors'] : '{{WRAPPER}}';
		$this->new_group_controls();
		$this->add_control(
			$name . 'button_text_color',
			[
				'label'     => __( 'Text Color', 'piotnetforms' ),
				'type'      => 'color',
				'value'     => '',
				'selectors' => [
					$wrapper => 'color: {{VALUE}};',
				],
			]
		);
		$this->add_control(
			$name . 'background_color',
			[
				'label'     => __( 'Background Color', 'piotnetforms' ),
				'type'      => 'color',
				'selectors' => [
					$wrapper => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			$name.'_button_border_type',
			[
				'type'      => 'select',
				'label'     => __( 'Border Type', 'piotnetforms' ),
				'value'     => '',
				'options'   => [
					''       => 'None',
					'solid'  => 'Solid',
					'double' => 'Double',
					'dotted' => 'Dotted',
					'dashed' => 'Dashed',
					'groove' => 'Groove',
				],
				'selectors' => [
					$wrapper => 'border-style:{{VALUE}};',
				],
			]
		);
		$this->add_control(
			$name.'_button_border_color',
			[
				'type'        => 'color',
				'label'       => __( 'Border Color', 'piotnetforms' ),
				'value'       => '',
				'label_block' => true,
				'selectors'   => [
					$wrapper => 'border-color: {{VALUE}};',
				],
				'conditions'  => [
					[
						'name'     => $name.'_button_border_type',
						'operator' => '!=',
						'value'    => '',
					],
				],
			]
		);
		$this->add_responsive_control(
			$name.'_button_border_width',
			[
				'type'        => 'dimensions',
				'label'       => __( 'Border Width', 'piotnetforms' ),
				'value'       => [
					'unit'   => 'px',
					'top'    => '',
					'right'  => '',
					'bottom' => '',
					'left'   => '',
				],
				'label_block' => true,
				'size_units'  => [ 'px', '%', 'em' ],
				'selectors'   => [
					$wrapper => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'conditions'  => [
					[
						'name'     => $name.'_button_border_type',
						'operator' => '!=',
						'value'    => '',
					],
				],
			]
		);

		$this->add_control(
			$name . 'border_radius',
			[
				'label'       => __( 'Border Radius', 'piotnetforms' ),
				'type'        => 'dimensions',
				'value'       => [
					'unit'   => 'px',
					'top'    => '',
					'right'  => '',
					'bottom' => '',
					'left'   => '',
				],
				'label_block' => true,
				'size_units'  => [ 'px', '%' ],
				'selectors'   => [
					$wrapper => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_control(
			$name . 'button_box_shadow',
			[
				'type'        => 'box-shadow',
				'label'       => __( 'Box Shadow', 'piotnetforms' ),
				'value'       => '',
				'label_block' => false,
				'render_type' => 'none',
				'selectors'   => [
					$wrapper => 'box-shadow: {{VALUE}};',
				],
			]
		);
		$this->add_responsive_control(
			$name . 'text_padding',
			[
				'label'       => __( 'Padding', 'piotnetforms' ),
				'type'        => 'dimensions',
				'label_block' => false,
				'value'       => [
					'unit'   => 'px',
					'top'    => '',
					'right'  => '',
					'bottom' => '',
					'left'   => '',
				],
				'size_units'  => [ 'px', 'em', '%' ],
				'selectors'   => [
					$wrapper => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		return $this->get_group_controls();
	}
	private function add_message_style_controls() {
		$this->add_text_typography_controls(
			'message_typography',
			[
				'selectors' => '{{WRAPPER}} .piotnetforms-message',
			]
		);
		$this->add_control(
			'success_message_color',
			[
				'label'     => __( 'Success Message Color', 'piotnetforms' ),
				'type'      => 'color',
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-message.piotnetforms-message-success' => 'color: {{VALUE}};',
				],
			]
		);
		$this->add_control(
			'error_message_color',
			[
				'label'     => __( 'Error Message Color', 'piotnetforms' ),
				'type'      => 'color',
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-message.piotnetforms-message-danger' => 'color: {{VALUE}};',
				],
			]
		);
		$this->add_control(
			'inline_message_color',
			[
				'label'     => __( 'Inline Message Color', 'piotnetforms' ),
				'type'      => 'color',
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-message.piotnetforms-help-inline' => 'color: {{VALUE}};',
				],
			]
		);
	}
	public function render() {
		$settings = $this->settings;
		$settings['submit_actions'] = !empty( $settings['submit_actions'] ) ? $settings['submit_actions'] : [];
		$editor = ( isset( $_GET['action'] ) && $_GET['action'] == 'piotnetforms' ) ? true : false;
		$form_post_id = $this->post_id;
		$form_version = empty( get_post_meta( $form_post_id, '_piotnetforms_version', true ) ) ? 1 : get_post_meta( $form_post_id, '_piotnetforms_version', true );
		$form_id = $form_version == 1 ? get_post_meta( $form_post_id, '_piotnetforms_form_id', true ) : $form_post_id;

		$this->add_render_attribute( 'wrapper', 'class', 'piotnetforms-submit' );
		$this->add_render_attribute( 'wrapper', 'class', 'piotnetforms-button-wrapper' );
		$this->add_render_attribute( 'wrapper', 'class', 'piotnetforms-multi-step-form__content-item-buttons' );

		if ( ! empty( $settings['link']['url'] ) ) {
			$this->add_render_attribute( 'button', 'href', $settings['link']['url'] );
			$this->add_render_attribute( 'button', 'class', 'piotnetforms-button-link' );

			if ( $settings['link']['is_external'] ) {
				$this->add_render_attribute( 'button', 'target', '_blank' );
			}

			if ( $settings['link']['nofollow'] ) {
				$this->add_render_attribute( 'button', 'rel', 'nofollow' );
			}
		}

		if ( ! empty( $settings['align_responsive_desktop'] ) ) {
			if ( $settings['align_responsive_desktop'] == 'justify' ) {
				$this->add_render_attribute( 'button', 'class', 'piotnetforms-button--justify' );
			}
		}

		$this->add_render_attribute( 'button', 'class', 'piotnetforms-button' );
		$this->add_render_attribute( 'button', 'role', 'button' );

		$this->add_render_attribute( 'button-prev', 'class', 'piotnetforms-button' );
		$this->add_render_attribute( 'button-prev', 'role', 'button' );

		$this->add_render_attribute( 'button', 'data-piotnetforms-required-text', $settings['required_field_message'] );

		if ( ! empty( $settings['button_css_id'] ) ) {
			$this->add_render_attribute( 'button', 'id', $settings['button_css_id'] );
		}

		if ( ! empty( $settings['size'] ) ) {
			$this->add_render_attribute( 'button', 'class', 'piotnetforms-size-' . $settings['size'] );
		}

		if ( $form_id ) {
			$submit_keyboard = !empty( $settings['enter_submit_form'] ) ? 'true' : 'false';
			$submit_hide = !empty( $settings['hide_button_after_submitting'] ) ? 'true' : 'false';
			$this->add_render_attribute( 'button', 'data-piotnetforms-submit-form-id', $form_id );
			$this->add_render_attribute( 'button', 'data-pafe-submit-keyboard', $submit_keyboard );
			$this->add_render_attribute( 'button', 'data-piotnetforms-submit-hide', $submit_hide );
		}

		if ( !empty( get_option( 'piotnetforms-recaptcha-site-key' ) ) && !empty( get_option( 'piotnetforms-recaptcha-secret-key' ) ) && !empty( $settings['piotnetforms_recaptcha_enable'] ) ) {
			$this->add_render_attribute( 'button', 'data-piotnetforms-submit-recaptcha', esc_attr( get_option( 'piotnetforms-recaptcha-site-key' ) ) );
		}

		if ( !empty( $settings['piotnetforms_conditional_logic_form_list'] ) ) {
			$list_conditional = $settings['piotnetforms_conditional_logic_form_list'];
			if ( !empty( $settings['piotnetforms_conditional_logic_form_enable'] ) && !empty( $list_conditional[0]['piotnetforms_conditional_logic_form_if'] ) && !empty( $list_conditional[0]['piotnetforms_conditional_logic_form_comparison_operators'] ) ) {
				$this->add_render_attribute( 'button', [
					'data-piotnetforms-conditional-logic' => str_replace( '\"]', '', str_replace( '[field id=\"', '', json_encode( $list_conditional ) ) ),
					'data-piotnetforms-conditional-logic-speed' => $settings['piotnetforms_conditional_logic_form_speed'],
					'data-piotnetforms-conditional-logic-easing' => $settings['piotnetforms_conditional_logic_form_easing'],
					'data-piotnetforms-conditional-logic-not-field' => '',
					'data-piotnetforms-conditional-logic-not-field-form-id' => $form_id,
				] );

				wp_enqueue_script( $this->slug . '-advanced-script' );
			}
		}

		if ( !empty( $settings['form_abandonment_enable'] ) ) {
			$this->add_render_attribute( 'wrapper', [
				'data-piotnetforms-abandonment' => '',
			] );
			if ( !empty( $settings['form_abandonment_webhook_enable'] ) && !empty( $settings['form_abandonment_webhook_url'] ) ) {
				$this->add_render_attribute( 'wrapper', [
					'data-piotnetforms-abandonment-webhook' => $settings['form_abandonment_webhook_url'],
				] );
			}
			wp_enqueue_script( $this->slug . '-abandonment-script' );
		} ?>

		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			<?php if ( empty( $settings['disable_buttons'] ) ) : ?>
			<?php if ( !empty( $settings['prev_button'] ) ) : ?>
				<div class="piotnetforms-multi-step-form__content-item-button">
					<button <?php echo $this->get_render_attribute_string( 'button-prev' ); ?> data-piotnetforms-nav="prev">
						<span class="piotnetforms-button-content-wrapper">
							<span class="piotnetforms-button-text"><?php echo $settings['prev_button_text']; ?></span>
						</span>
					</button>
				</div>
			<?php endif; ?>
			<div class="piotnetforms-multi-step-form__content-item-button">
		<?php

		if ( in_array( 'update_user_profile', $settings['submit_actions'] ) ) {
			if ( is_user_logged_in() ) {
				if ( !empty( $settings['update_user_meta_list'] ) ) {
					$update_user_profile = [];
					$user_id = get_current_user_id();

					foreach ( $settings['update_user_meta_list'] as $user_meta ) {
						if ( !empty( $user_meta['update_user_meta'] ) && !empty( $user_meta['update_user_meta_field_shortcode'] ) ) {
							$user_meta_key = $user_meta['update_user_meta'];
							$user_meta_value = '';

							if ( $user_meta['update_user_meta'] == 'meta' || $user_meta['update_user_meta'] == 'acf' ) {
								if ( !empty( $user_meta['update_user_meta_key'] ) ) {
									$user_meta_key = $user_meta['update_user_meta_key'];

									if ( $user_meta['update_user_meta'] == 'meta' ) {
										$user_meta_value = get_user_meta( $user_id, $user_meta_key, true );
									} else {
										$user_meta_value = get_field( $user_meta_key, 'user_' . $user_id );
									}
								}
							} elseif ( $user_meta['update_user_meta'] == 'email' ) {
								$user_meta_value = get_the_author_meta( 'user_email', $user_id );
							} else {
								$user_meta_value = get_user_meta( $user_id, $user_meta_key, true );
							}

							if ( $user_meta['update_user_meta'] == 'acf' ) {
								$meta_type = $user_meta['update_user_meta_type'];

								if ( $meta_type == 'image' ) {
									if ( !empty( $user_meta_value ) ) {
										$user_meta_value = $user_meta_value['url'];
									}
								}

								if ( $meta_type == 'gallery' ) {
									if ( is_array( $user_meta_value ) ) {
										$images = '';
										foreach ( $user_meta_value as $item ) {
											if ( is_array( $item ) ) {
												if ( isset( $item['url'] ) ) {
													$images .= $item['url'] . ',';
												}
											}
										}
										$user_meta_value = rtrim( $images, ',' );
									}
								}

								if ( $meta_type == 'select' || $meta_type == 'checkbox' ) {
									if ( is_array( $user_meta_value ) ) {
										$value_string = '';
										foreach ( $user_meta_value as $item ) {
											$value_string .= $item . ',';
										}
										$user_meta_value = rtrim( $value_string, ',' );
									}
								}
							}

							if ( $user_meta_key != 'password' ) {
								$update_user_profile[] = [
									'user_meta_key' => $user_meta_key,
									'user_meta_value' => $user_meta_value,
									'field_id' => $user_meta['update_user_meta_field_shortcode'],
								];
							}
						}
					}

					$this->add_render_attribute( 'button', [
						'data-piotnetforms-submit-update-user-profile' => str_replace( '\"]', '', str_replace( '[field id=\"', '', json_encode( $update_user_profile ) ) ),
					] );

					wp_enqueue_script( $this->slug . '-advanced-script' );
				}
			}
		}

		if ( !empty( $settings['mollie_enable'] ) ) {
			$this->add_render_attribute( 'button', [
				'data-piotnetforms-mollie-payment' => $form_id
			] );
		}

		if ( !empty( $settings['paypal_enable'] ) && isset( $form_id ) || !empty( $settings['paypal_subscription_enable'] ) ) {
			$this->add_render_attribute( 'button', [
				'data-piotnetforms-paypal-submit' => '',
				'data-piotnetforms-paypal-submit-enable' => '',
			] );
		}

		if ( !empty( $settings['piotnetforms_stripe_enable'] ) ) {
			$this->add_render_attribute( 'button', [
				'data-piotnetforms-stripe-submit' => '',
			] );

			if ( !empty( $settings['piotnetforms_stripe_amount'] ) ) {
				$this->add_render_attribute( 'button', [
					'data-piotnetforms-stripe-amount' => $settings['piotnetforms_stripe_amount'],
				] );
			}

			if ( !empty( $settings['piotnetforms_stripe_currency'] ) ) {
				$this->add_render_attribute( 'button', [
					'data-piotnetforms-stripe-currency' => $settings['piotnetforms_stripe_currency'],
				] );
			}

			if ( !empty( $settings['piotnetforms_stripe_amount_field_enable'] ) && !empty( $settings['piotnetforms_stripe_amount_field'] ) ) {
				$this->add_render_attribute( 'button', [
					'data-piotnetforms-stripe-amount-field' => $settings['piotnetforms_stripe_amount_field'],
				] );
			}

			if ( !empty( $settings['piotnetforms_stripe_customer_info_field'] ) ) {
				$this->add_render_attribute( 'button', [
					'data-piotnetforms-stripe-customer-info-field' => $settings['piotnetforms_stripe_customer_info_field'],
				] );
			}
		}

		if ( !empty( $settings['woocommerce_add_to_cart_product_id'] ) ) {
			$this->add_render_attribute( 'button', [
				'data-piotnetforms-woocommerce-product-id' => $settings['woocommerce_add_to_cart_product_id'],
			] );
		}

		if ( !empty( $_GET['edit'] ) ) {
			$post_id = intval( $_GET['edit'] );
			if ( is_user_logged_in() && get_post( $post_id ) != null ) {
				if ( current_user_can( 'edit_others_posts' ) || get_current_user_id() == get_post( $post_id )->post_author ) {
					$sp_post_id = get_post_meta( $post_id, '_submit_post_id', true );
					$sp_button_id = get_post_meta( $post_id, '_submit_button_id', true );

					if ( !empty( $_GET['smpid'] ) ) {
						$sp_post_id = sanitize_text_field( $_GET['smpid'] );
					}

					if ( !empty( $_GET['sm'] ) ) {
						$sp_button_id = sanitize_text_field( $_GET['sm'] );
					}

					$form = [];

					$data     = json_decode( get_post_meta( $sp_post_id, '_piotnetforms_data', true ), true );
					$form['settings'] = $data['widgets'][ $sp_button_id ]['settings'];

					if ( !empty( $form ) ) {
						$this->add_render_attribute( 'button', [
							'data-piotnetforms-submit-post-edit' => intval( $post_id ),
						] );

						$submit_post_id = $post_id;

						if ( isset( $form['settings']['submit_post_custom_fields_list'] ) ) {
							$sp_custom_fields = $form['settings']['submit_post_custom_fields_list'];

							if ( is_array( $sp_custom_fields ) ) {
								foreach ( $sp_custom_fields as $sp_custom_field ) {
									if ( !empty( $sp_custom_field['submit_post_custom_field'] ) ) {
										$custom_field_value = '';
										$meta_type = $sp_custom_field['submit_post_custom_field_type'];

										if ( $meta_type == 'repeater' && function_exists( 'update_field' ) && $form['settings']['submit_post_custom_field_source'] == 'acf_field' ) {
											$custom_field_value = get_field( $sp_custom_field['submit_post_custom_field'], $submit_post_id );
											if ( !empty( $custom_field_value ) ) {
												array_walk( $custom_field_value, function ( & $item, $custom_field_value_key, $submit_post_id_value ) {
													foreach ( $item as $key => $value ) {
														$field_object = get_field_object( $this->acf_get_field_key( $key, $submit_post_id_value ) );
														if ( !empty( $field_object ) ) {
															$field_type = $field_object['type'];

															$item_value = $value;

															if ( $field_type == 'repeater' ) {
																foreach ( $item_value as $item_value_key => $item_value_element ) {
																	foreach ( $field_object['sub_fields'] as $item_sub_field ) {
																		foreach ( $item_value_element as $item_value_element_key => $item_value_element_value ) {
																			if ( $item_sub_field['name'] == $item_value_element_key ) {
																				if ( $item_sub_field['type'] == 'image' ) {
																					if ( !empty( $item_value_element_value['url'] ) ) {
																						$item_value[$item_value_key][$item_value_element_key] = $item_value_element_value['url'];
																					}
																				}
																			}
																		}
																	}
																}
															}

															if ( $field_type == 'image' ) {
																if ( !empty( $item_value['url'] ) ) {
																	$item_value = $item_value['url'];
																}
															}

															if ( $field_type == 'gallery' ) {
																if ( is_array( $item_value ) ) {
																	$images = '';
																	foreach ( $item_value as $itemx ) {
																		if ( is_array( $itemx ) ) {
																			$images .= $itemx['url'] . ',';
																		}
																	}
																	$item_value = rtrim( $images, ',' );
																}
															}

															if ( $field_type == 'select' || $field_type == 'checkbox' ) {
																if ( is_array( $item_value ) ) {
																	$value_string = '';
																	foreach ( $item_value as $itemx ) {
																		$value_string .= $itemx . ',';
																	}
																	$item_value = rtrim( $value_string, ',' );
																}
															}

															if ( $field_type == 'date_picker' ) {
																$time = strtotime( $item_value );
																$item_value = date( get_option( 'date_format' ), $time );
															}

															$item[$key] = $item_value;
														}
													}
												}, $_GET['edit'] ); ?>
													<div data-piotnetforms-repeater-value='<?php echo json_encode( $custom_field_value ); ?>' data-piotnetforms-repeater-value-id="<?php echo $sp_custom_field['submit_post_custom_field']; ?>" data-piotnetforms-repeater-value-form-id="<?php echo $form_id; ?>" style="display: none;">
														<?php echo json_encode( $custom_field_value ); ?>
													</div>
												<?php
											}
										}

										if ( $meta_type == 'jet_engine_repeater' && $form['settings']['submit_post_custom_field_source'] == 'jet_engine_field' ) {
											$custom_field_value = get_post_meta( $submit_post_id, $sp_custom_field['submit_post_custom_field'], true );
											if ( !empty( $custom_field_value ) ) {
												foreach ( $custom_field_value as $item_key => $custom_field_item ) {
													foreach ( $custom_field_item as $key => $value ) {
														$field_object = $this->jetengine_repeater_get_field_object( $key, $sp_custom_field['submit_post_custom_field'] );
														if ( !empty( $field_object ) ) {
															$field_type = $field_object['type'];
															$item_value = $value;

															if ( $field_type == 'media' ) {
																$image = get_the_guid( $value );
																if ( !empty( $image ) ) {
																	$item_value = $image;
																}
															}

															if ( $field_type == 'gallery' ) {
																$images_array = explode( ',', $item_value );
																if ( is_array( $images_array ) ) {
																	$images = '';
																	foreach ( $images_array as $images_item ) {
																		if ( !empty( $images_item ) ) {
																			$images .= get_the_guid( $images_item ) . ',';
																		}
																	}
																	if ( !empty( $images ) ) {
																		$item_value = rtrim( $images, ',' );
																	}
																}
															}

															if ( $field_type == 'checkbox' ) {
																if ( is_array( $item_value ) ) {
																	$value_string = '';
																	foreach ( $item_value as $itemx => $itemx_value ) {
																		if ( $itemx_value == 'true' ) {
																			$value_string .= $itemx . ',';
																		}
																	}
																	$item_value = rtrim( $value_string, ',' );
																}
															}

															if ( $field_type == 'date' ) {
																$time = strtotime( $item_value );
																if ( empty( $item_value ) ) {
																	$item_value = '';
																} else {
																	$item_value = date( 'Y-m-d', $time );
																}
															}

															if ( $field_type == 'time' ) {
																$time = strtotime( $item_value );
																$item_value = date( 'H:i', $time );
															}

															$custom_field_item[$key] = $item_value;
														}
													}

													if ( is_string( $item_key ) ) {
														unset( $custom_field_value[$item_key] );
														$custom_field_value[] = $custom_field_item;
													} else {
														$custom_field_value[$item_key] = $custom_field_item;
													}
												} ?>
                                                    <div data-piotnetforms-repeater-value='<?php echo json_encode( $custom_field_value ); ?>' data-piotnetforms-repeater-value-id="<?php echo $sp_custom_field['submit_post_custom_field']; ?>" data-piotnetforms-repeater-value-form-id="<?php echo $form_id; ?>" style="display: none;">
                                                        <?php echo json_encode( $custom_field_value ); ?>
                                                    </div>
                                                <?php
											}
										}
									}
								}
							}
						}
					}
				}
			}
		} ?>
		<input type="hidden" name="post_id" value="<?php echo $this->post_id; ?>" data-piotnetforms-hidden-form-id="<?php if ( $form_id ) {
			echo $form_id;
		} ?>"/>
		<input type="hidden" name="form_id" value="<?php echo $this->get_id(); ?>" data-piotnetforms-hidden-form-id="<?php if ( $form_id ) {
			echo $form_id;
		} ?>"/>
		<input type="hidden" name="remote_ip" value="<?php echo $this->get_client_ip(); ?>" data-piotnetforms-hidden-form-id="<?php if ( $form_id ) {
			echo $form_id;
		} ?>"/>

		<?php if ( in_array( 'redirect', $settings['submit_actions'] ) ) : ?>
			<input type="hidden" name="redirect" value="<?php echo $settings['redirect_to']; ?>" data-piotnetforms-hidden-form-id="<?php if ( $form_id ) {
				echo $form_id;
			} ?>" data-piotnetforms-open-new-tab="<?php echo $settings['redirect_open_new_tab']; ?>"/>
		<?php endif; ?>

		<?php if ( in_array( 'popup', $settings['submit_actions'] ) ) : ?>
			<?php if ( !empty( $settings['popup_action'] ) && !empty( $settings['popup_action_popup_id'] ) ) : ?>
				<a href="<?php echo $this->create_popup_url( $settings['popup_action_popup_id'], $settings['popup_action'] ); ?>" data-piotnetforms-popup data-piotnetforms-hidden-form-id="<?php if ( $form_id ) {
					echo $form_id;
				} ?>" style="display: none;"></a>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( in_array( 'open_popup', $settings['submit_actions'] ) ) : ?>
			<?php if ( !empty( $settings['popup_action_popup_id_open'] ) ) : ?>
				<a href="<?php echo $this->create_popup_url( $settings['popup_action_popup_id_open'], 'open' ); ?>" data-piotnetforms-popup-open data-piotnetforms-hidden-form-id="<?php if ( $form_id ) {
					echo $form_id;
				} ?>" style="display: none;"></a>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( in_array( 'close_popup', $settings['submit_actions'] ) ) : ?>
			<?php if ( !empty( $settings['popup_action_popup_id_close'] ) ) : ?>
				<a href="<?php echo $this->create_popup_url( $settings['popup_action_popup_id_close'], 'close' ); ?>" data-piotnetforms-popup-close data-piotnetforms-hidden-form-id="<?php if ( $form_id ) {
					echo $form_id;
				} ?>" style="display: none;"></a>
			<?php endif; ?>
		<?php endif; ?>

		<button <?php echo $this->get_render_attribute_string( 'button' ); ?>>
			<?php $this->render_text(); ?>
		</button>

		<?php if ( in_array( 'submit_post', $settings['submit_actions'] ) ) : ?>
			<?php if ( $editor ) :
				echo '<div style="margin-top: 20px;">' . __( 'Edit Post URL Shortcode', 'piotnetforms' ) . '</div><input class="piotnetforms-field-shortcode" style="min-width: 300px; padding: 10px;" value="[piotnetforms_edit_post edit_text='. "'Edit Post'" . ' sm=' . "'" . $this->get_id() . "'" . ' smpid=' . "'" . get_the_ID() . "'" .']' . get_the_permalink() . '[/piotnetforms_edit_post]" readonly /><div class="piotnetforms-control-field-description">' . __( 'Add this shortcode to your single template.', 'piotnetforms' ) . ' The shortcode will be changed if you edit this form so you have to refresh piotnetforms Editor Page and then copy the shortcode. ' . __( 'Replace', 'piotnetforms' ) . ' "' . get_the_permalink() . '" ' . __( 'by your Page URL contains your Submit Post Form.', 'piotnetforms' ) . '</div>';
				echo '<div style="margin-top: 20px;">' . __( 'Delete Post URL Shortcode', 'piotnetforms' ) . '</div><input class="piotnetforms-field-shortcode" style="min-width: 300px; padding: 10px;" value="[piotnetforms_delete_post force_delete='. "'0'". ' delete_text='. "'Delete Post'" . ' sm=' . "'" . $this->get_id() . "'" . ' smpid=' . "'" . get_the_ID() . "'" . ' redirect='."'http://YOUR-DOMAIN'".']'.'[/piotnetforms_delete_post]" readonly /><div class="piotnetforms-control-field-description">' . __( 'Add this shortcode to your single template.', 'piotnetforms' ) . ' The shortcode will be changed if you edit this form so you have to refresh piotnetforms Editor Page and then copy the shortcode. ' . __( 'Replace', 'piotnetforms' ) . ' "http://YOUR-DOMAIN" ' . __( 'by your Page URL', 'piotnetforms' ) . '</div>'; ?>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( !empty( $settings['paypal_enable'] ) && isset( $form_id ) || !empty( $settings['paypal_subscription_enable'] ) && isset( $form_id ) ) : ?>
			<div class="piotnetforms-paypal">
				<!-- Set up a container element for the button -->
			    <div id="piotnetforms-paypal-button-container-<?php echo str_replace( ' ', '', $form_id ); ?>"></div>
		    </div>

		    <?php
						$paypal_sdk_url = 'https://www.paypal.com/sdk/js?client-id='.get_option( 'piotnetforms-paypal-client-id' );
			$paypal_currency = !empty( $settings['paypal_enable'] ) ? $settings['paypal_currency'] : $settings['paypal_subscription_currency'];
			$paypal_locale = !empty( $settings['paypal_enable'] ) ? $settings['paypal_locale'] : $settings['paypal_subscription_locale'];
			if ( !empty( $paypal_currency ) ) {
				$paypal_sdk_url .= '&currency='.$paypal_currency;
			}
			if ( !empty( $paypal_locale ) ) {
				$paypal_sdk_url .= '&locale='.$paypal_locale;
			}
			if ( !empty( $settings['paypal_subscription_enable'] ) && !empty( $settings['paypal_plan'] ) ) {
				$paypal_sdk_url .= '&vault=true';
			} ?>
		    <!-- Include the PayPal JavaScript SDK -->
		    <script src="<?php echo $paypal_sdk_url; ?>"></script>

		    <script>
		    	function getFieldValue(fieldId) {
		    		var fieldName = 'form_fields[' + fieldId + ']',
		    			$field = jQuery(document).find('[name="' + fieldName + '"]'),
		    			fieldType = $field.attr('type'),
						formID = $field.attr('data-piotnetforms-id');

					if (fieldType == 'radio' || fieldType == 'checkbox') {
						var fieldValue = $field.closest('.piotnetforms-element').find('input:checked').val();
						fieldValue = fieldValue ? fieldValue : $field.closest('.piotnetforms-field-container').find('input:checked').val();
			        } else {
			        	var fieldValue = $field.val();
			        }

			        if (fieldValue == '') {
			        	var fieldValue = 0;
			        }

			        return fieldValue;
		    	}

		    	function piotnetformsValidateForm<?php echo str_replace( ' ', '', $form_id ); ?>() {
		    		var formID = '<?php echo $form_id; ?>',
		    			$ = jQuery,
			    		$fields = $(document).find('[data-piotnetforms-id="'+ formID +'"]'),
			    		$submit = $(document).find('[data-piotnetforms-submit-form-id="'+ formID +'"]'),
			    		requiredText = $submit.data('piotnetforms-required-text'),
			    		error = 0;

					var $parent = $submit.closest('.piotnetforms-element');

					$fields.each(function(){
						if ( $(this).data('piotnetforms-stripe') == undefined && $(this).data('piotnetforms-html') == undefined ) {
							var $checkboxRequired = $(this).closest('.piotnetforms-field-type-checkbox.piotnetforms-field-required');
							var checked = 0;
							if ($checkboxRequired.length > 0) {
								checked = $checkboxRequired.find("input[type=checkbox]:checked").length;
							} 

							if ($(this).attr('oninvalid') != undefined) {
								requiredText = $(this).attr('oninvalid').replace("this.setCustomValidity('","").replace("')","");
							}

                            var isValid = $(this)[0].checkValidity();
                            var next_ele = $($(this)[0]).next()[0];
                            if ($(next_ele).hasClass('flatpickr-mobile')) {
                                isValid = next_ele.checkValidity();
                            }

							if ( !isValid && $(this).closest('.piotnetforms-fields-wrapper').css('display') != 'none' && $(this).closest('[data-piotnetforms-conditional-logic]').css('display') != 'none' && $(this).data('piotnetforms-honeypot') == undefined &&  $(this).closest('[data-piotnetforms-signature]').length == 0 || checked == 0 && $checkboxRequired.length > 0 && $(this).closest('.piotnetforms-element').css('display') != 'none') {
								if ($(this).css('display') == 'none' || $(this).closest('div').css('display') == 'none' || $(this).data('piotnetforms-image-select') != undefined || $checkboxRequired.length > 0) {
									$(this).closest('.piotnetforms-field-group').find('[data-piotnetforms-required]').html(requiredText);
									
								} else {
									if ($(this).data('piotnetforms-image-select') == undefined) {
										$(this)[0].reportValidity();
									} 
								}

								error++;
							} else {

								$(this).closest('.piotnetforms-field-group').find('[data-piotnetforms-required]').html('');

								if ($(this).closest('[data-piotnetforms-signature]').length > 0) {
									var $piotnetformsSingature = $(this).closest('[data-piotnetforms-signature]'),
										$exportButton = $piotnetformsSingature.find('[data-piotnetforms-signature-export]');

									$exportButton.trigger('click');

									if ($(this).val() == '' && $(this).closest('.piotnetforms-widget').css('display') != 'none' && $(this).attr('required') != undefined) {
										$(this).closest('.piotnetforms-field-group').find('[data-piotnetforms-required]').html(requiredText);
										error++;
									} 
								}
							}
						}
					});

					if (error == 0) {
						return true;
					} else {
						return false;
					}
		    	}
				var isFirefox = typeof InstallTrigger !== 'undefined';
		    	// fix alert ]
		        // Render the PayPal button into #paypal-button-container
				var paypalPlanID = '<?php echo $settings['paypal_plan'] ?>';
		        paypal.Buttons({


	                onClick :  function(data, actions){
	                    if(!piotnetformsValidateForm<?php echo str_replace( ' ', '', $form_id ); ?>()){
							if(isFirefox){
								setTimeout(() => {
									piotnetformsValidateForm<?php echo str_replace( ' ', '', $form_id ); ?>()
								}, 300)
							}
	                        return false;
	                    }else {
	                        return true;
	                    }
	                },
					<?php if ( !empty( $settings['paypal_subscription_enable'] ) && !empty( $settings['paypal_plan'] ) ) { ?>
						createSubscription: function(data, actions) {
							return actions.subscription.create({
							/* Creates the subscription */
							plan_id: paypalPlanID
							});
						},
						onApprove: function(data, actions) {
							var $submit = jQuery(document).find('[data-piotnetforms-submit-form-id="<?php echo $settings['form_id']; ?>"]');
		                    $submit.attr('data-piotnetforms-paypal-submit-transaction-id', data.subscriptionID);
		                    $submit.trigger('click');
						},
						style: {
							label: 'subscribe'
						}
					<?php } else { ?>
		            // Set up the transaction
		            createOrder: function(data, actions) {
		                return actions.order.create({
		                    purchase_units: [{
		                        amount: {
		                        	<?php if ( strpos( $settings['paypal_amount'], 'field id="' ) !== false ) : ?>
			                            value: getFieldValue('<?php echo str_replace( '[field id="', '', str_replace( '"]', '', $settings['paypal_amount'] ) ); ?>'),
		                            <?php else : ?>
		                            	value: '<?php echo $settings['paypal_amount']; ?>',
			                        <?php endif; ?>
		                        },
		                        <?php if ( strpos( $settings['paypal_description'], '[field id="' ) !== false ) : ?>
		                            description: getFieldValue('<?php echo str_replace( '[field id="', '', str_replace( '"]', '', $settings['paypal_description'] ) ); ?>'),
	                            <?php else : ?>
	                            	description: '<?php echo $settings['paypal_description']; ?>',
		                        <?php endif; ?>
		                    }]
		                });
		            },

		            onApprove: function(data, actions) {
		                return actions.order.capture().then(function(details) {
		                    // Show a success message to the buyer
		                    // alert('Transaction completed by ' + details.payer.name.given_name + '!');
		                    var $submit = jQuery(document).find('[data-piotnetforms-submit-form-id="<?php echo str_replace( ' ', '', $form_id ); ?>"]'),
		                    	$parent = $submit.closest('.piotnetforms-submit');

		                    $submit.attr('data-piotnetforms-paypal-submit-transaction-id', details.id);
		                    $submit.trigger('click');
		                    $parent.find('.piotnetforms-message').removeClass('visible');
        					$parent.find('.piotnetforms-alert--paypal .piotnetforms-message-success').addClass('visible');
		                });
		            },
		            onError: function (err) {
		            	var $submit = jQuery(document).find('[data-piotnetforms-submit-form-id="<?php echo str_replace( ' ', '', $form_id ); ?>"]'),
            				$parent = $submit.closest('.piotnetforms-submit');

					    $parent.find('.piotnetforms-message').removeClass('visible');
						$parent.find('.piotnetforms-alert--paypal .piotnetforms-message-danger').addClass('visible');
					}
					<?php } ?>

		        }).render('#piotnetforms-paypal-button-container-<?php echo str_replace( ' ', '', $form_id ); ?>');
		    </script>
	    <?php endif; ?>
		
		<?php if ( in_array( 'pdfgenerator', $settings['submit_actions'] ) ): ?>
		<?php if ( $settings['pdfgenerator_background_image_enable'] == 'yes' ) {
			if ( isset( $settings['pdfgenerator_background_image']['url'] ) ) {
				$pdf_generator_image = $settings['pdfgenerator_background_image']['url'];
			}
		} ?>
		<!-- Import Template -->
		<?php if ( $settings['pdfgenerator_import_template'] == 'yes' && !empty( $settings['pdfgenerator_template_url'] ) ): ?>
		<div class="pafe-button-load-pdf-template" style="text-align:center">
			<button data-pafe-load-pdf-template="<?php echo $settings['pdfgenerator_template_url']; ?>" id="piotnetforms-load-pdf-template">Load PDF Template</button>
		</div>
		<?php endif; ?>
		<?php
			$import_class = !empty( $settings['pdfgenerator_import_template'] ) ? ' pdf_is_import' : ''; ?>
		<div class="piotnetforms-pdf-generator-preview<?php if ( empty( $settings['pdfgenerator_set_custom'] ) ) {
			echo ' piotnetforms-pdf-generator-preview--not-custom';
		} ?> <?php echo $settings['pdfgenerator_size'] ?> <?php echo $import_class; ?>" style="border: 1px solid #000; margin: 0 auto; position: relative; <?php if ( isset( $pdf_generator_image ) ) {
			echo "background-image:url('".$pdf_generator_image."'); background-size: contain; background-position: left top; background-repeat: no-repeat;";
		} ?>">

		<?php if ( in_array( 'pdfgenerator', $settings['submit_actions'] ) ): ?>
		<?php if ( $settings['pdfgenerator_import_template'] == 'yes' && !empty( $settings['pdfgenerator_template_url'] ) ): ?>
		<canvas id="piotnetforms-canvas-pdf"></canvas>
		<?php endif; ?>
		<script src="<?php echo plugin_dir_url( __FILE__ ).'../../assets/js/minify/pdf.min.js' ?>"></script>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				<?php if ( $settings['pdfgenerator_import_template'] == 'yes' && !empty( $settings['pdfgenerator_template_url'] ) ): ?>
					piotnetformtLoadPdfPreview('<?php echo $settings['pdfgenerator_template_url'] ?>')
				<?php endif; ?>
				jQuery(document).on('click', '#piotnetforms-load-pdf-template', function(){
					var url = jQuery(this).attr('data-pafe-load-pdf-template');
					if(url){
						piotnetformtLoadPdfPreview(url);
					}else{return}
				});
				function piotnetformtLoadPdfPreview(url){
					var pdfjsLib = window['pdfjs-dist/build/pdf'];

					pdfjsLib.GlobalWorkerOptions.workerSrc = '<?php echo plugin_dir_url( __FILE__ ).'../../assets/js/minify/pdf.worker.min.js' ?>';
					var loadingTask = pdfjsLib.getDocument(url);
					loadingTask.promise.then(function(pdf) {

					var pageNumber = 1;
					pdf.getPage(pageNumber).then(function(page) {

						var scale = 1.43;
						var viewport = page.getViewport({scale: scale});

						var canvas = document.getElementById('piotnetforms-canvas-pdf');
						var context = canvas.getContext('2d');
						canvas.height = 1123;//viewport.height;
						canvas.width = 794;//viewport.width;

						var renderContext = {
						canvasContext: context,
						viewport: viewport
						};
						var renderTask = page.render(renderContext);
						renderTask.promise.then(function () {
							console.log('Page rendered');
						});
					});
					}, function (reason) {
					// PDF loading error
						console.error(reason);
					});
				}
			});
		</script>
		<?php endif; ?>
		<div id="piotnetforms-pdf-preview-template"></div>
		<?php if ( !empty( $settings['pdfgenerator_title'] ) && $settings['pdfgenerator_set_custom'] != 'yes' ): ?>
		<div class="piotnetforms-pdf-generator-preview__title" style="margin-top: 20px; margin-left: 20px;"><?php echo $settings['pdfgenerator_title'] ?></div>
		<?php endif; ?>
			<?php if ( $settings['pdfgenerator_set_custom'] == 'yes' ) { ?>
			<?php foreach ( $settings['pdfgenerator_field_mapping_list'] as $item ): ?>
				<?php if ( $item['pdfgenerator_field_type'] == 'default' ) { ?>
					<?php
				$pdf_font_weight = !empty( $item['font_weight'] ) ? $item['font_weight'] : '';
					?>
					<?php if ( !empty( $item['auto_position'] ) ) { ?>
						<div class="piotnetforms-pdf-generator-preview__item <?php echo $pdf_font_weight; ?> piotnetforms-repeater-item-<?php echo esc_attr( $item['repeater_id'] ); ?>" style="background: #dedede;line-height: 1; margin-left:15px;margin-top:15px;">
							<?php echo $item['pdfgenerator_field_shortcode']; ?>
						</div>
					<?php } else { ?>
						<div class="piotnetforms-pdf-generator-preview__item <?php echo $pdf_font_weight; ?> piotnetforms-repeater-item-<?php echo esc_attr( $item['repeater_id'] ); ?>" style="position: absolute; background: #dedede;line-height: 1;">
							<?php echo $item['pdfgenerator_field_shortcode']; ?>
						</div>
					<?php } ?>
				<?php } elseif ( $item['pdfgenerator_field_type'] == 'image' ) { ?>
				<div class="piotnetforms-pdf-generator-preview__item-image  piotnetforms-repeater-item-<?php echo esc_attr( $item['repeater_id'] ); ?>">
					<img src="<?php echo plugins_url().'/piotnetforms-pro/assets/images/signature.png'; ?>" style="position: absolute;">
					<?php //echo 'Type image in form';?>
				</div>
				<?php } else { ?>
				<?php
					$pdf_image_preview_url = !empty( $item['pdfgenerator_image_field']['url'] ) ? $item['pdfgenerator_image_field']['url'] : plugins_url().'/piotnetforms-pro/assets/images/signature.png';
					?>
				<div class="piotnetforms-pdf-generator-preview__item-image  piotnetforms-repeater-item-<?php echo esc_attr( $item['repeater_id'] ); ?>">
					<img src="<?php echo $pdf_image_preview_url; ?>" style="position: absolute;">
				</div>
			<?php } endforeach;
			} else { ?>
			<div class="piotnetforms-field-mapping__preview">
				<?php if ( $settings['pdfgenerator_heading_field_mapping_show_label'] == 'yes' ) {
					echo 'Label: Your Field Value';
				} else {
					echo 'Your Field Value';
				} ?>
			</div>
			<?php } ?>
		</div>
		<?php endif; ?>
		</div>
		<?php if ( !empty( $settings['mollie_enable'] ) ) : ?>
			<?php
					$mollie_msg_success = $settings['mollie_message_succeeded'] ? $settings['mollie_message_succeeded'] : 'Payment success';
			$mollie_msg_failed = $settings['mollie_message_failed'] ? $settings['mollie_message_failed'] : 'Payment failed';
			$mollie_msg_pending = $settings['mollie_message_pending'] ? $settings['mollie_message_pending'] : 'Payment pending';
			wp_enqueue_script( $this->slug . '-mollie-script' ); ?>
			<div class="piotnetforms-alert piotnetforms-alert--mollie">
				<div class="piotnetforms-message piotnetforms-message-success" role="alert"><?php echo $mollie_msg_success; ?></div>
				<div class="piotnetforms-message piotnetforms-message-danger" role="alert"><?php echo $mollie_msg_pending; ?></div>
				<div class="piotnetforms-message piotnetforms-help-inline" role="alert"><?php echo $mollie_msg_failed; ?></div>
			</div>
		<?php endif; ?>

		<?php if ( !empty( $settings['piotnetforms_stripe_enable'] ) ) : ?>
			<div class="piotnetforms-alert piotnetforms-alert--stripe">
				<div class="piotnetforms-message piotnetforms-message-success" role="alert"><?php echo $settings['piotnetforms_stripe_message_succeeded']; ?></div>
				<div class="piotnetforms-message piotnetforms-message-danger" role="alert"><?php echo $settings['piotnetforms_stripe_message_failed']; ?></div>
				<div class="piotnetforms-message piotnetforms-help-inline" role="alert"><?php echo $settings['piotnetforms_stripe_message_pending']; ?></div>
			</div>
		<?php endif; ?>

		<?php if ( !empty( $settings['paypal_enable'] ) || !empty( $settings['paypal_subscription_enable'] ) ) : ?>
			<?php
						if ( !empty( $settings['paypal_enable'] ) ) {
							$paypal_msg_success = $settings['paypal_message_succeeded'] ? $settings['paypal_message_succeeded'] : 'Payment success';
							$paypal_msg_failed = $settings['paypal_message_failed'] ? $settings['paypal_message_failed'] : 'Payment failed';
						}
			if ( !empty( $settings['paypal_subscription_enable'] ) ) {
				$paypal_msg_success = $settings['paypal_subscription_message_succeeded'] ? $settings['paypal_subscription_message_succeeded'] : 'Payment success';
				$paypal_msg_failed = $settings['paypal_subscription_message_failed'] ? $settings['paypal_subscription_message_failed'] : 'Payment failed';
			} ?>
			<div class="piotnetforms-alert piotnetforms-alert--paypal">
				<div class="piotnetforms-message piotnetforms-message-success" role="alert"><?php echo $paypal_msg_success; ?></div>
				<div class="piotnetforms-message piotnetforms-message-danger" role="alert"><?php echo $paypal_msg_failed; ?></div>
			</div>
		<?php endif; ?>

		<?php if ( !empty( get_option( 'piotnetforms-recaptcha-site-key' ) ) && !empty( get_option( 'piotnetforms-recaptcha-secret-key' ) ) && !empty( $settings['piotnetforms_recaptcha_enable'] ) ) : ?>
		<script src="https://www.google.com/recaptcha/api.js?render=<?php echo esc_attr( get_option( 'piotnetforms-recaptcha-site-key' ) ); ?>"></script>
		<?php if ( !empty( $settings['piotnetforms_recaptcha_hide_badge'] ) ) : ?>
			<style type="text/css">
				.grecaptcha-badge {
					opacity:0 !important;
					visibility: collapse !important;
				}
			</style>
		<?php endif; ?>
		<?php endif; ?>
		<div id="piotnetforms-trigger-success-<?php if ( $form_id ) {
			echo $form_id;
		} ?>" data-piotnetforms-trigger-success="<?php if ( $form_id ) {
			echo $form_id;
		} ?>" style="display: none"></div>
		<div id="piotnetforms-trigger-failed-<?php if ( $form_id ) {
			echo $form_id;
		} ?>" data-piotnetforms-trigger-failed="<?php if ( $form_id ) {
			echo $form_id;
		} ?>" style="display: none"></div>
		<div class="piotnetforms-alert piotnetforms-alert--mail<?php if ( !empty( $settings['hidden_messages'] ) ) {
			echo ' piotnetforms-alert--hidden';
		} ?>">
			<div class="piotnetforms-message piotnetforms-message-success" role="alert" data-piotnetforms-message="<?php echo $settings['success_message']; ?>"><?php echo $settings['success_message']; ?></div>
			<div class="piotnetforms-message piotnetforms-message-danger" role="alert" data-piotnetforms-message="<?php echo $settings['error_message']; ?>"><?php echo $settings['error_message']; ?></div>
			<!-- <div class="piotnetforms-message piotnetforms-help-inline" role="alert">Server error. Form not sent.</div> -->
		</div>
		<?php endif; // End disable buttons?>
		</div>
		<?php if ( !$editor ) : ?>
		</div>
		</div>
		</div>
		<?php endif; ?>
		<?php
	}

	public function live_preview() {
		?>
		<%	
			var s = data.widget_settings;
			var formId = s.form_id ? s.form_id : '';

			view.add_attribute( 'wrapper', 'class', 'piotnetforms-submit' );
			view.add_attribute( 'wrapper', 'class', 'piotnetforms-button-wrapper' );

			view.add_attribute( 'button', 'class', 'piotnetforms-button' );
			view.add_attribute( 'button', 'role', 'button' );
			view.add_attribute( 'button', 'data-piotnetforms-required-text', s['required_field_message'] );
			view.add_attribute( 'button', 'data-piotnetforms-submit-form-id', formId );

			if ( s['align_responsive_desktop'] ) {
				if ( s['align_responsive_desktop'] == 'justify') {
					view.add_attribute( 'button', 'class', 'piotnetforms-button--justify' );
				}
			}

			view.add_multi_attribute({
				'content-wrapper': {
					class: 'piotnetforms-button-content-wrapper',
				},
				'icon-align': {
					class: [
						'piotnetforms-button-icon',
						'piotnetforms-align-icon-' + s['icon_align'],
					],
				},
				text: {
					class: 'piotnetforms-button-text'
				}
			});
		%>
		<div <%= view.render_attributes('wrapper') %>>
			<button <%= view.render_attributes('button') %>>
				<span <%= view.render_attributes('content-wrapper') %>>
					<span class="piotnetforms-button-text piotnetforms-spinner"><span class="icon-spinner-of-dots"></span></span>
					<% if ( s['icon'] ) { %>
					<span <%= view.render_attributes('icon-align') %>>
						<i class="<%= s['icon'] %>" aria-hidden="true"></i>
					</span>
					<% } %>
					<span <%= view.render_attributes('text') %>><%= s['submit_button_text'] %></span>
				</span>
			</button>
			<% if (s['submit_actions'] !== undefined) { %>
			<% if (s['submit_actions'].includes('submit_post')) { %>
				<div style="margin-top: 20px;">
					<?php echo __( 'Edit Post URL Shortcode', 'piotnetforms' ) ?>
				</div>
				<input class="piotnetforms-field-shortcode" style="min-width: 300px; padding: 10px;" value="[edit_post edit_text='Edit Post' sm='<%= data.widget_id %>' smpid='<%= view.post_id %>']<%= view.post_url %>[/edit_post]" readonly /><div class="piotnetforms-control-field-description">Add this shortcode to your single template. The shortcode will be changed if you edit this form so you have to refresh piotnetforms Editor Page and then copy the shortcode. Replace "<%= view.post_url %>" by your Page URL contains your Submit Post Form.</div>
			    <div style="margin-top: 20px;">
					<?php echo __( 'Delete Post URL Shortcode', 'piotnetforms' ) ?>
				</div>
				<input class="piotnetforms-field-shortcode" style="min-width: 300px; padding: 10px;" value="[piotnetforms_delete_post force_delete='0' delete_text='Delete Post' sm='<%= data.widget_id %>' smpid='<%= view.post_id %>' redirect='http://YOUR-DOMAIN.piotnet.com' ][/piotnetforms_delete_post]" readonly /><div class="piotnetforms-control-field-description">Add this shortcode to your single template. The shortcode will be changed if you edit this form so you have to refresh piotnetforms Editor Page and then copy the shortcode. Replace "http://YOUR-DOMAIN.piotnet.com" by your Page URL.</div>
			<% } %>
			<% } %>
			<!-- PDF generator -->
			<% if (s['submit_actions'] !== undefined) { %>
			<% if(s.submit_actions.includes("pdfgenerator")){ %>
			<%
				var setCustom = !s.pdfgenerator_set_custom ? 'piotnetforms-pdf-generator-preview--not-custom' : '';
			%>
			<% if(s.pdfgenerator_background_image_enable == 'yes'){
				if(s.pdfgenerator_background_image){
					if(s.pdfgenerator_background_image.url){
						var PdfGeneratorImage = s.pdfgenerator_background_image.url
					}
				}
			 } %>
			<!-- Import Template -->
			<% if(s.pdfgenerator_set_custom == 'yes' && s.pdfgenerator_import_template == 'yes' && s.pdfgenerator_template_url != ''){ %>
			<div class="pafe-button-load-pdf-template" style="text-align:center">
				<button data-pafe-load-pdf-template="<%= s.pdfgenerator_template_url %>" id="piotnetforms-load-pdf-template">Load PDF Template</button>
			</div>
			<% } %>
			<% var importClass = s.pdfgenerator_import_template ? 'pdf_is_import' : 'pdf_not_import'; %>
			<div class="piotnetforms-pdf-generator-preview <%= setCustom %> <%= s.pdfgenerator_size %> <%= importClass %>" style="border: 1px solid #000;margin: 0 auto; position: relative; <% if(PdfGeneratorImage){ %><%= 'background-image:url('+PdfGeneratorImage+');background-size: contain; background-position: left top; background-repeat: no-repeat;' %><% } %>">
			<% if(s.pdfgenerator_set_custom == 'yes' && s.pdfgenerator_import_template == 'yes' && s.pdfgenerator_template_url != ''){ %>
			<canvas id="piotnetforms-canvas-pdf"></canvas>
			<% } %>
			<% if(s.pdfgenerator_title && s.pdfgenerator_set_custom != 'yes'){ %>
			<div class="piotnetforms-pdf-generator-preview__title" style="margin-top: 20px; margin-left: 20px;"><%= s.pdfgenerator_title %></div>
			<% } %>
				<% if(s.pdfgenerator_set_custom == 'yes'){ %>
				<%  _.each(s.pdfgenerator_field_mapping_list, function(item, index){ %>
				<%
					item.pdfgenerator_field_type = item.pdfgenerator_field_type ? item.pdfgenerator_field_type : 'default' 
				%>
					<% if(item.pdfgenerator_field_type == 'default'){ %>
						<%
							var pdf_font_weight = item.font_weight ? item.font_weight : '';
						%>
						<% if(item.auto_position == 'yes'){ %>
							<div class="piotnetforms-pdf-generator-preview__item <%= pdf_font_weight %> piotnetforms-repeater-item-<%= item.repeater_id %>" style="background: #dedede;line-height: 1; margin-left:15px;margin-top:15px;">
								<%= item.pdfgenerator_field_shortcode %>
							</div>
						<% }else{ %>
							<div class="piotnetforms-pdf-generator-preview__item <%= pdf_font_weight %> piotnetforms-repeater-item-<%= item.repeater_id %>" style="position: absolute; background: #dedede;line-height: 1;">
								<%= item.pdfgenerator_field_shortcode %>
							</div>
						<% } %>
					<% }else if(item.pdfgenerator_field_type == 'image'){ %>
					<div class="piotnetforms-pdf-generator-preview__item-image  piotnetforms-repeater-item-<%= item.repeater_id %>">
						<img src="<?php echo plugins_url().'/piotnetforms-pro/assets/images/signature.png'; ?>" style="position: absolute;">
					</div>
					<!-- <div class="piotnetforms-pdf-generator-preview__item-image  piotnetforms-repeater-item-<%= item.repeater_id %>">
						<img src="<?php //echo plugins_url().'/piotnetforms-pro/assets/images/signature.png';?>" style="position: absolute;">
					</div> -->
					<% }else{ %>
					<%
						var PdfImagePreviewUrl = item.pdfgenerator_image_field ? item.pdfgenerator_image_field.url : '<?php echo plugins_url().'/piotnetforms-pro/assets/images/signature.png' ?>';
					%>
					<div class="piotnetforms-pdf-generator-preview__item-image  piotnetforms-repeater-item-<%= item.repeater_id %>">
						<img src="<%= PdfImagePreviewUrl %>" style="position: absolute;">
					</div>
				<% }});}else{ %>
				<div class="piotnetforms-field-mapping__preview">
					<% if(s.pdfgenerator_heading_field_mapping_show_label){ %>
						<%= 'Label: Your Field Value' %>
					<% }else{ %>
						<%= 'Your Field Value' %>
					<% } %>
				</div>
				<% } %>
			</div>
			<% } %>
			<% } %>
		</div>
		<?php
	}
	public function mailpoet_get_list() {
		$data = [];
		if ( class_exists( \MailPoet\API\API::class ) ) {
			$mailpoet_api = \MailPoet\API\API::MP( 'v1' );
			$lists        = $mailpoet_api->getLists();
			foreach ( $lists as $item ) {
				$data[ $item['id'] ] = $item['name'];
			}
		}
		return $data;
	}
	protected function get_client_ip() {
		$ipaddress = '';
		if ( getenv( 'HTTP_CLIENT_IP' ) ) {
			$ipaddress = getenv( 'HTTP_CLIENT_IP' );
		} elseif ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
			$ipaddress = getenv( 'HTTP_X_FORWARDED_FOR' );
		} elseif ( getenv( 'HTTP_X_FORWARDED' ) ) {
			$ipaddress = getenv( 'HTTP_X_FORWARDED' );
		} elseif ( getenv( 'HTTP_FORWARDED_FOR' ) ) {
			$ipaddress = getenv( 'HTTP_FORWARDED_FOR' );
		} elseif ( getenv( 'HTTP_FORWARDED' ) ) {
			$ipaddress = getenv( 'HTTP_FORWARDED' );
		} elseif ( getenv( 'REMOTE_ADDR' ) ) {
			$ipaddress = getenv( 'REMOTE_ADDR' );
		} else {
			$ipaddress = 'UNKNOWN';
		}
		return $ipaddress;
	}

	/**
	 * Render button text.
	 *
	 * Render button widget text.
	 *
	 * @since 1.5.0
	 * @access protected
	 */
	protected function render_text() {
		$settings = $this->settings;

		$this->add_render_attribute(
			[
				'content-wrapper' => [
					'class' => 'piotnetforms-button-content-wrapper',
				],
				'icon-align'      => [
					'class' => [
						'piotnetforms-button-icon',
						'piotnetforms-align-icon-' . $settings['icon_align'],
					],
				],
				'text'            => [
					'class' => 'piotnetforms-button-text',
				],
			]
		); ?>
		<span <?php echo $this->get_render_attribute_string( 'content-wrapper' ); ?>>
			<span class="piotnetforms-button-text piotnetforms-spinner"><span class="icon-spinner-of-dots"></span></span>
			<?php if ( ! empty( $settings['icon'] ) ) : wp_enqueue_style( $this->slug . '-fontawesome-style' ); ?>
			<span <?php echo $this->get_render_attribute_string( 'icon-align' ); ?>>
				<i class="<?php echo esc_attr( $settings['icon'] ); ?>" aria-hidden="true"></i>
			</span>
			<?php endif; ?>
			<span <?php echo $this->get_render_attribute_string( 'text' ); ?>><?php echo $settings['submit_button_text']; ?></span>
		</span>
		<?php
	}

	protected function create_list_exist( $repeater ) {
		$settings = $this->get_settings_for_display();

		// $repeater_terms = $repeater->get_controls();

		// if (!empty($settings['submit_post_term_slug']) && empty($repeater_terms)) {
		// 	$repeater_terms[0] = $settings['submit_post_term_slug'];
		// 	$repeater_terms[1] = $settings['submit_post_term'];
		// }

		return $settings;
	}

	public function add_wpml_support() {
		add_filter( 'wpml_piotnetforms_widgets_to_translate', [ $this, 'wpml_widgets_to_translate_filter' ] );
	}

	public function wpml_widgets_to_translate_filter( $widgets ) {
		$widgets[ $this->get_name() ] = [
			'conditions' => [ 'widgetType' => $this->get_name() ],
			'fields'     => [
				[
					'field'       => 'text',
					'type'        => __( 'Button Text', 'piotnetforms' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'email_to',
					'type'        => __( 'Email To', 'piotnetforms' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'email_subject',
					'type'        => __( 'Email Subject', 'piotnetforms' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'email_content',
					'type'        => __( 'Email Content', 'piotnetforms' ),
					'editor_type' => 'AREA',
				],
				[
					'field'       => 'email_from',
					'type'        => __( 'Email From', 'piotnetforms' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'email_from_name',
					'type'        => __( 'Email From Name', 'piotnetforms' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'email_reply_to',
					'type'        => __( 'Email Reply To', 'piotnetforms' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'email_to_cc',
					'type'        => __( 'Cc', 'piotnetforms' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'email_to_bcc',
					'type'        => __( 'Bcc', 'piotnetforms' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'email_to_2',
					'type'        => __( 'Email To 2', 'piotnetforms' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'email_subject_2',
					'type'        => __( 'Email Subject 2', 'piotnetforms' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'email_content_2',
					'type'        => __( 'Email Content 2', 'piotnetforms' ),
					'editor_type' => 'AREA',
				],
				[
					'field'       => 'email_from_2',
					'type'        => __( 'Email From 2', 'piotnetforms' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'email_from_name_2',
					'type'        => __( 'Email From Name 2', 'piotnetforms' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'email_reply_to_2',
					'type'        => __( 'Email Reply To 2', 'piotnetforms' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'email_to_cc_2',
					'type'        => __( 'Cc 2', 'piotnetforms' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'email_to_bcc_2',
					'type'        => __( 'Bcc 2', 'piotnetforms' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'success_message',
					'type'        => __( 'Success Message', 'piotnetforms' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'error_message',
					'type'        => __( 'Error Message', 'piotnetforms' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'required_field_message',
					'type'        => __( 'Required Message', 'piotnetforms' ),
					'editor_type' => 'LINE',
				],
				[
					'field'       => 'invalid_message',
					'type'        => __( 'Invalid Message', 'piotnetforms' ),
					'editor_type' => 'LINE',
				],
			],
		];

		return $widgets;
	}
	public function piotnetforms_get_pdf_fonts() {
		$pdf_fonts = [];
		$pdf_fonts['default'] = 'Default';
		$args = [
			'post_type' => 'piotnetforms-fonts',
			'post_status' => 'publish',
		];
		$fonts = new WP_Query( $args );
		if ( !empty( $fonts->posts ) ) {
			foreach ( $fonts->posts as $key => $font ) {
				$font_key = get_post_meta( $font->ID, '_piotnetforms_pdf_font', true );
				$font_key = substr( $font_key, strpos( $font_key, 'uploads/' )+8 );
				$pdf_fonts[$font_key] = $font->post_title;
			}
		}
		return $pdf_fonts;
	}
	public function piotnetforms_get_pdf_fonts_style() {
		$pdf_fonts_style = [];
		$pdf_fonts_style['N'] = 'Normal';
		$pdf_fonts_style['I'] = 'Italic';
		$pdf_fonts_style['B'] = 'Bold';
		$pdf_fonts_style['BI'] = 'Bold Italic';
		$args = [
			'post_type' => 'piotnetforms-fonts',
			'post_status' => 'publish',
		];
		$fonts = new WP_Query( $args );
		if ( !empty( $fonts->posts ) ) {
			foreach ( $fonts->posts as $key => $font ) {
				$font_key = get_post_meta( $font->ID, '_piotnetforms_pdf_font', true );
				$font_key = substr( $font_key, strpos( $font_key, 'uploads/' )+8 );
				$pdf_fonts_style[$font_key] = $font->post_title;
			}
		}
		return $pdf_fonts_style;
	}
}
