<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Piotnetforms_Field extends Base_Widget_Piotnetforms {
	protected $is_add_conditional_logic = false;

	public function get_type() {
		return 'field';
	}

	public function get_class_name() {
		return 'Piotnetforms_Field';
	}

	public function get_title() {
		return 'Field';
	}

	public function get_icon() {
		return [
			'type' => 'image',
			'value' => plugin_dir_url( __FILE__ ) . '../../assets/icons/w-field.svg',
		];
	}

	public function get_categories() {
		return [ 'form' ];
	}

	public function get_keywords() {
		return [ 'field', 'input' ];
	}

	public function register_controls() {
		$this->start_tab( 'settings', 'Settings' );

		$this->start_section( 'section_settings_general', 'General' );
		$this->add_settings_controls();

		$this->start_section( 'section_settings_advanced', 'Other Options' );
		$this->add_settings_advanced_controls();

		$this->start_section( 'section_icon', 'Icon' );
		$this->add_icon_controls();

		$this->start_section(
			'input_mask_section',
			'Input Mask',
			[
				'conditions' => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => '!in',
							'value'    => [
								'checkbox',
								'acceptance',
								'radio',
							],
						],
					],
				],
			]
		);
		$this->add_input_mask_controls();

		$this->start_section( 'section_conditional_logic', 'Conditional Logic' );
		$this->add_conditional_logic_controls();

		$this->start_tab( 'style', 'Style' );
		$this->start_section(
			'text_styles_section',
			'Checkbox / Radio / Acceptance',
			[
				'conditions' => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'checkbox',
								'acceptance',
								'radio',
							],
						],
					],
				],
			]
		);
		$this->add_checkbox_style_controls();

		$this->start_section(
			'section_style_spiner',
			'(-/+) Button',
			[
				'condition' => [
					'field_type'     => 'number',
					'number_spiner!' => '',
				],
			]
		);
		$this->add_number_style_controls();

		$this->start_section(
			'section_style_image_select',
			'Image Select',
			[
				'condition' => [
					'field_type' => 'image_select',
				],
			]
		);
		$this->add_image_select_style_controls();

		$this->start_section(
			'section_style_piotnet_form_calculated_fields',
			'Calculated Fields',
			[
				'condition' => [
					'field_type' => 'calculated_fields',
				],
			]
		);
		$this->add_calculated_fields_style_controls();

		$this->start_section(
			'section_style_piotnet_form_label',
			'Label',
			[
				'condition' => [
					'modern_upload_field_style!' => 'true',
				],
			]
		);
		$this->add_label_style_controls();

		$this->start_section(
			'section_style_piotnet_form_modern_upload_style',
			'Modern Upload Style',
			[
				'condition' => [
					'modern_upload_field_style' => 'true',
				],
			]
		);
		$this->add_modern_upload_field_style_controls();

		$this->start_section( 'section_style_piotnet_form_field', 'Field' );
		$this->add_field_style_controls();

		$this->start_section(
			'section_style_field_description',
			'Field Description',
			[
				'conditions'   => [
					'terms' => [
						[
							'name'     => 'field_description',
							'operator' => '!=',
							'value'    => ''
						]
					],
				],
			]
		);
		$this->add_field_description_style_controls();

		$this->start_section(
			'section_style_piotnet_form_password',
			'Password Preview',
			[
				'conditions' => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'password',
							],
						],
					],
				],
			]
		);
		$this->add_password_compare_style_controls();

		$this->add_advanced_tab();

		return $this->structure;
	}

	private function add_settings_controls() {
		$field_types = [
			'text'              => __( 'Text', 'piotnetforms' ),
			'email'             => __( 'Email', 'piotnetforms' ),
			'textarea'          => __( 'Textarea', 'piotnetforms' ),
			'url'               => __( 'URL', 'piotnetforms' ),
			'tel'               => __( 'Tel', 'piotnetforms' ),
			'radio'             => __( 'Radio', 'piotnetforms' ),
			'select'            => __( 'Select', 'piotnetforms' ),
			'terms_select'      => __( 'Terms Select', 'piotnetforms' ),
			'image_select'      => __( 'Image Select', 'piotnetforms' ),
			'checkbox'          => __( 'Checkbox', 'piotnetforms' ),
			'acceptance'        => __( 'Acceptance', 'piotnetforms' ),
			'number'            => __( 'Number', 'piotnetforms' ),
			'date'              => __( 'Date', 'piotnetforms' ),
			'time'              => __( 'Time', 'piotnetforms' ),
			'image_upload'      => __( 'Image Upload', 'piotnetforms' ),
			'upload'            => __( 'File Upload', 'piotnetforms' ),
			'password'          => __( 'Password', 'piotnetforms' ),
			'html'              => __( 'HTML', 'piotnetforms' ),
			'hidden'            => __( 'Hidden', 'piotnetforms' ),
			'range_slider'      => __( 'Range Slider', 'piotnetforms' ),
			'coupon_code'       => __( 'Coupon Code', 'piotnetforms' ),
			'calculated_fields' => __( 'Calculated Fields', 'piotnetforms' ),
			'stripe_payment'    => __( 'Stripe Payment', 'piotnetforms' ),
			'honeypot'          => __( 'Honeypot', 'piotnetforms' ),
			'color'             => __( 'Color Picker', 'piotnetforms' ),
			'iban'             => __( 'Iban', 'piotnetforms' ),
			'confirm' 			=> __( 'Confirm', 'piotnetforms' ),
		];

		if ( get_option( 'piotnetforms-features-submit-post', 2 ) == 2 || get_option( 'piotnetforms-features-submit-post', 2 ) == 1 ) {
			$field_types['tinymce'] = __( 'TinyMCE', 'piotnetforms' );
		}

		if ( get_option( 'piotnetforms-features-select-autocomplete-field', 2 ) == 2 || get_option( 'piotnetforms-features-select-autocomplete-field', 2 ) == 1 ) {
			$field_types['select_autocomplete'] = __( 'Select Autocomplete', 'piotnetforms' );
		}

		if ( get_option( 'piotnetforms-features-address-autocomplete-field', 2 ) == 2 || get_option( 'piotnetforms-features-address-autocomplete-field', 2 ) == 1 ) {
			$field_types['address_autocomplete'] = __( 'Address Autocomplete', 'piotnetforms' );
		}

		if ( get_option( 'piotnetforms-features-signature-field', 2 ) == 2 || get_option( 'piotnetforms-features-signature-field', 2 ) == 1 ) {
			$field_types['signature'] = __( 'Signature', 'piotnetforms' );
		}

		$this->add_control(
			'field_type',
			[
				'label'       => __( 'Type', 'piotnetforms' ),
				'type'        => 'select',
				'options'     => $field_types,
				'default'     => 'text',
				'description' => 'TinyMCE, Range Slider Field only works on the frontend.',
			]
		);

		$this->add_control(
			'confirm_type',
			[
				'label'   => __( 'Confirm Type', 'piotnetforms' ),
				'type'    => 'select',
				'options' => [
					'text' => __( 'Text', 'piotnetforms' ),
					'email' => __( 'Email', 'piotnetforms' ),
					'url' => __( 'url', 'piotnetforms' ),
					'tel' => __( 'Tel', 'piotnetforms' ),
				],
				'default' => 'text',
				'condition'   => [
					'field_type' => 'confirm'
				]

			]
		);

		$this->add_control(
			'confirm_field_name',
			[
				'label'       => __( 'Confirm Field ID*', 'piotnetforms' ),
				'type'        => 'text',
				'condition' => [
					'field_type' => 'confirm'
				]
			]
		);

		$this->add_control(
			'confirm_error_msg',
			[
				'label'       => __( 'Confirm error messenger', 'piotnetforms' ),
				'type'        => 'text',
				'default' => "Field don't match",
				'condition' => [
					'field_type' => 'confirm'
				]
			]
		);

		$this->add_control(
			'field_id',
			[
				'label'       => __( 'Field ID*', 'piotnetforms' ),
				'type'        => 'text',
				'description' => __( 'Field ID have to be unique in a form, with latin character and no space, no number. Please do not enter Field ID = product. E.g your_field_id', 'piotnetforms' ),
			]
		);

		$this->add_control(
			'field_label',
			[
				'label'   => __( 'Label', 'piotnetforms' ),
				'label_block' => true,
				'type'    => 'text',
				'default' => '',
			]
		);

		// $this->add_control(
		//     'field_label_placement',
		//     [
		//         'label'       => __( 'Label Placement', 'piotnetforms' ),
		//         'label_block' => true,
		//         'type'        => 'select',
		//         'options'     => [
		//             '' => __( 'Default', 'piotnetforms' ),
		//             'top' => __( 'Top', 'piotnetforms' ),
		//             'right' => __( 'Right', 'piotnetforms' ),
		//             'bottom' => __( 'Bottom', 'piotnetforms' ),
		//             'left' => __( 'Left', 'piotnetforms' ),
		//             'hide' => __( 'Hide', 'piotnetforms' ),
		//         ],
		//     ]
		// );

		$this->add_control(
			'form_id',
			[
				'label'       => __( 'Form ID* (Required)', 'piotnetforms' ),
				'type'        => 'hidden',
				'description' => __( 'Enter the same form id for all fields in a form', 'piotnetforms' ),
			]
		);

		$this->add_control(
			'show_icon_password_options',
			[
				'label'       => __( 'Show/Hidden Password Icon?', 'piotnetforms' ),
				'type'        => 'switch',
				'label_on'    => __( 'Yes', 'piotnetforms' ),
				'label_off'   => __( 'No', 'piotnetforms' ),
				'default'     => '',
				'conditions'  => [
					[
						'name'     => 'field_type',
						'operator' => '==',
						'value'    => 'password',
					],
				],
			]
		);
		$this->add_control(
			'is_repassword_field',
			[
				'label'       => __( 'Is Repassword Field?', 'piotnetforms' ),
				'type'        => 'switch',
				'label_on'    => __( 'Yes', 'piotnetforms' ),
				'label_off'   => __( 'No', 'piotnetforms' ),
				'default'     => '',
				'conditions'  => [
					[
						'name'     => 'field_type',
						'operator' => '==',
						'value'    => 'password',
					],
				],
			]
		);
		$this->add_control(
			'field_password_id',
			[
				'label'   => __( 'Field Password ID', 'piotnetforms' ),
				'type'    => 'text',
				'description' => __( 'Enter your password field ID to compare', 'piotnetforms' ),
				'conditions'  => [
					[
						'name'     => 'field_type',
						'operator' => '==',
						'value'    => 'password',
					],
					// [
					// 	'name'     => 'is_repassword_field',
					// 	'operator' => '==',
					// 	'value'    => '',
					// ],
				],
			]
		);
		$this->add_control(
			'msg_repassword_err',
			[
				'label'   => __( "Msg Password Don't Match", 'piotnetforms' ),
				'type'    => 'text',
				'default' => __( "Password don't match", 'piotnetforms' ),
				'conditions'  => [
					[
						'name'     => 'field_type',
						'operator' => '==',
						'value'    => 'password',
					],
					// [
					// 	'name'     => 'is_repassword_field',
					// 	'operator' => '==',
					// 	'value'    => '',
					// ],
				],
			]
		);
		$this->add_control(
			'google_maps',
			[
				'label'       => __( 'Google Maps', 'piotnetforms' ),
				'type'        => 'switch',
				'description' => __( 'This feature only works on the frontend.', 'piotnetforms' ),
				'label_on'    => __( 'Show', 'piotnetforms' ),
				'label_off'   => __( 'Hide', 'piotnetforms' ),
				'default'     => '',
				'conditions'  => [
					[
						'name'     => 'field_type',
						'operator' => '==',
						'value'    => 'address_autocomplete',
					],
				],
			]
		);

		$this->add_control(
			'country',
			[
				'label'       => __( 'Country', 'piotnetforms' ),
				'type'        => 'select',
				'description' => __( 'Choose your country.', 'piotnetforms' ),
				'default'     => 'All',
				'options'     => [
					'All' => __( 'All', 'piotnetforms' ),
					'AF'  => 'Afghanistan',
					'AX'  => 'Åland Islands',
					'AL'  => 'Albania',
					'DZ'  => 'Algeria',
					'AS'  => 'American Samoa',
					'AD'  => 'Andorra',
					'AO'  => 'Angola',
					'AI'  => 'Anguilla',
					'AQ'  => 'Antarctica',
					'AG'  => 'Antigua and Barbuda',
					'AR'  => 'Argentina',
					'AM'  => 'Armenia',
					'AW'  => 'Aruba',
					'AU'  => 'Australia',
					'AT'  => 'Austria',
					'AZ'  => 'Azerbaijan',
					'BS'  => 'Bahamas',
					'BH'  => 'Bahrain',
					'BD'  => 'Bangladesh',
					'BB'  => 'Barbados',
					'BY'  => 'Belarus',
					'BE'  => 'Belgium',
					'BZ'  => 'Belize',
					'BJ'  => 'Benin',
					'BM'  => 'Bermuda',
					'BT'  => 'Bhutan',
					'BO'  => 'Bolivia, Plurinational State of',
					'BQ'  => 'Bonaire, Sint Eustatius and Saba',
					'BA'  => 'Bosnia and Herzegovina',
					'BW'  => 'Botswana',
					'BV'  => 'Bouvet Island',
					'BR'  => 'Brazil',
					'IO'  => 'British Indian Ocean Territory',
					'BN'  => 'Brunei Darussalam',
					'BG'  => 'Bulgaria',
					'BF'  => 'Burkina Faso',
					'BI'  => 'Burundi',
					'KH'  => 'Cambodia',
					'CM'  => 'Cameroon',
					'CA'  => 'Canada',
					'CV'  => 'Cape Verde',
					'KY'  => 'Cayman Islands',
					'CF'  => 'Central African Republic',
					'TD'  => 'Chad',
					'CL'  => 'Chile',
					'CN'  => 'China',
					'CX'  => 'Christmas Island',
					'CC'  => 'Cocos (Keeling) Islands',
					'CO'  => 'Colombia',
					'KM'  => 'Comoros',
					'CG'  => 'Congo',
					'CD'  => 'Congo, the Democratic Republic of the',
					'CK'  => 'Cook Islands',
					'CR'  => 'Costa Rica',
					'CI'  => "Côte d'Ivoire",
					'HR'  => 'Croatia',
					'CU'  => 'Cuba',
					'CW'  => 'Curaçao',
					'CY'  => 'Cyprus',
					'CZ'  => 'Czech Republic',
					'DK'  => 'Denmark',
					'DJ'  => 'Djibouti',
					'DM'  => 'Dominica',
					'DO'  => 'Dominican Republic',
					'EC'  => 'Ecuador',
					'EG'  => 'Egypt',
					'SV'  => 'El Salvador',
					'GQ'  => 'Equatorial Guinea',
					'ER'  => 'Eritrea',
					'EE'  => 'Estonia',
					'ET'  => 'Ethiopia',
					'FK'  => 'Falkland Islands (Malvinas)',
					'FO'  => 'Faroe Islands',
					'FJ'  => 'Fiji',
					'FI'  => 'Finland',
					'FR'  => 'France',
					'GF'  => 'French Guiana',
					'PF'  => 'French Polynesia',
					'TF'  => 'French Southern Territories',
					'GA'  => 'Gabon',
					'GM'  => 'Gambia',
					'GE'  => 'Georgia',
					'DE'  => 'Germany',
					'GH'  => 'Ghana',
					'GI'  => 'Gibraltar',
					'GR'  => 'Greece',
					'GL'  => 'Greenland',
					'GD'  => 'Grenada',
					'GP'  => 'Guadeloupe',
					'GU'  => 'Guam',
					'GT'  => 'Guatemala',
					'GG'  => 'Guernsey',
					'GN'  => 'Guinea',
					'GW'  => 'Guinea-Bissau',
					'GY'  => 'Guyana',
					'HT'  => 'Haiti',
					'HM'  => 'Heard Island and McDonald Islands',
					'VA'  => 'Holy See (Vatican City State)',
					'HN'  => 'Honduras',
					'HK'  => 'Hong Kong',
					'HU'  => 'Hungary',
					'IS'  => 'Iceland',
					'IN'  => 'India',
					'ID'  => 'Indonesia',
					'IR'  => 'Iran, Islamic Republic of',
					'IQ'  => 'Iraq',
					'IE'  => 'Ireland',
					'IM'  => 'Isle of Man',
					'IL'  => 'Israel',
					'IT'  => 'Italy',
					'JM'  => 'Jamaica',
					'JP'  => 'Japan',
					'JE'  => 'Jersey',
					'JO'  => 'Jordan',
					'KZ'  => 'Kazakhstan',
					'KE'  => 'Kenya',
					'KI'  => 'Kiribati',
					'KP'  => "Korea, Democratic People's Republic of",
					'KR'  => 'Korea, Republic of',
					'KW'  => 'Kuwait',
					'KG'  => 'Kyrgyzstan',
					'LA'  => "Lao People's Democratic Republic",
					'LV'  => 'Latvia',
					'LB'  => 'Lebanon',
					'LS'  => 'Lesotho',
					'LR'  => 'Liberia',
					'LY'  => 'Libya',
					'LI'  => 'Liechtenstein',
					'LT'  => 'Lithuania',
					'LU'  => 'Luxembourg',
					'MO'  => 'Macao',
					'MK'  => 'Macedonia, the former Yugoslav Republic of',
					'MG'  => 'Madagascar',
					'MW'  => 'Malawi',
					'MY'  => 'Malaysia',
					'MV'  => 'Maldives',
					'ML'  => 'Mali',
					'MT'  => 'Malta',
					'MH'  => 'Marshall Islands',
					'MQ'  => 'Martinique',
					'MR'  => 'Mauritania',
					'MU'  => 'Mauritius',
					'YT'  => 'Mayotte',
					'MX'  => 'Mexico',
					'FM'  => 'Micronesia, Federated States of',
					'MD'  => 'Moldova, Republic of',
					'MC'  => 'Monaco',
					'MN'  => 'Mongolia',
					'ME'  => 'Montenegro',
					'MS'  => 'Montserrat',
					'MA'  => 'Morocco',
					'MZ'  => 'Mozambique',
					'MM'  => 'Myanmar',
					'NA'  => 'Namibia',
					'NR'  => 'Nauru',
					'NP'  => 'Nepal',
					'NL'  => 'Netherlands',
					'NC'  => 'New Caledonia',
					'NZ'  => 'New Zealand',
					'NI'  => 'Nicaragua',
					'NE'  => 'Niger',
					'NG'  => 'Nigeria',
					'NU'  => 'Niue',
					'NF'  => 'Norfolk Island',
					'MP'  => 'Northern Mariana Islands',
					'NO'  => 'Norway',
					'OM'  => 'Oman',
					'PK'  => 'Pakistan',
					'PW'  => 'Palau',
					'PS'  => 'Palestinian Territory, Occupied',
					'PA'  => 'Panama',
					'PG'  => 'Papua New Guinea',
					'PY'  => 'Paraguay',
					'PE'  => 'Peru',
					'PH'  => 'Philippines',
					'PN'  => 'Pitcairn',
					'PL'  => 'Poland',
					'PT'  => 'Portugal',
					'PR'  => 'Puerto Rico',
					'QA'  => 'Qatar',
					'RE'  => 'Réunion',
					'RO'  => 'Romania',
					'RU'  => 'Russian Federation',
					'RW'  => 'Rwanda',
					'BL'  => 'Saint Barthélemy',
					'SH'  => 'Saint Helena, Ascension and Tristan da Cunha',
					'KN'  => 'Saint Kitts and Nevis',
					'LC'  => 'Saint Lucia',
					'MF'  => 'Saint Martin (French part)',
					'PM'  => 'Saint Pierre and Miquelon',
					'VC'  => 'Saint Vincent and the Grenadines',
					'WS'  => 'Samoa',
					'SM'  => 'San Marino',
					'ST'  => 'Sao Tome and Principe',
					'SA'  => 'Saudi Arabia',
					'SN'  => 'Senegal',
					'RS'  => 'Serbia',
					'SC'  => 'Seychelles',
					'SL'  => 'Sierra Leone',
					'SG'  => 'Singapore',
					'SX'  => 'Sint Maarten (Dutch part)',
					'SK'  => 'Slovakia',
					'SI'  => 'Slovenia',
					'SB'  => 'Solomon Islands',
					'SO'  => 'Somalia',
					'ZA'  => 'South Africa',
					'GS'  => 'South Georgia and the South Sandwich Islands',
					'SS'  => 'South Sudan',
					'ES'  => 'Spain',
					'LK'  => 'Sri Lanka',
					'SD'  => 'Sudan',
					'SR'  => 'Suriname',
					'SJ'  => 'Svalbard and Jan Mayen',
					'SZ'  => 'Swaziland',
					'SE'  => 'Sweden',
					'CH'  => 'Switzerland',
					'SY'  => 'Syrian Arab Republic',
					'TW'  => 'Taiwan, Province of China',
					'TJ'  => 'Tajikistan',
					'TZ'  => 'Tanzania, United Republic of',
					'TH'  => 'Thailand',
					'TL'  => 'Timor-Leste',
					'TG'  => 'Togo',
					'TK'  => 'Tokelau',
					'TO'  => 'Tonga',
					'TT'  => 'Trinidad and Tobago',
					'TN'  => 'Tunisia',
					'TR'  => 'Turkey',
					'TM'  => 'Turkmenistan',
					'TC'  => 'Turks and Caicos Islands',
					'TV'  => 'Tuvalu',
					'UG'  => 'Uganda',
					'UA'  => 'Ukraine',
					'AE'  => 'United Arab Emirates',
					'GB'  => 'United Kingdom',
					'US'  => 'United States',
					'UM'  => 'United States Minor Outlying Islands',
					'UY'  => 'Uruguay',
					'UZ'  => 'Uzbekistan',
					'VU'  => 'Vanuatu',
					'VE'  => 'Venezuela, Bolivarian Republic of',
					'VN'  => 'Viet Nam',
					'VG'  => 'Virgin Islands, British',
					'VI'  => 'Virgin Islands, U.S.',
					'WF'  => 'Wallis and Futuna',
					'EH'  => 'Western Sahara',
					'YE'  => 'Yemen',
					'ZM'  => 'Zambia',
					'ZW'  => 'Zimbabwe',
				],
				'conditions'  => [
					[
						'name'     => 'field_type',
						'operator' => '==',
						'value'    => 'address_autocomplete',
					],
				],
			]
		);

		$this->add_control(
			'google_maps_lat',
			[
				'label'       => __( 'Latitude', 'piotnetforms' ),
				'type'        => 'text',
				'placeholder' => '21.028511',
				'description' => __( 'Latitude and Longitude Finder https://www.latlong.net/', 'piotnetforms' ),
				'default'     => '21.028511',
				'condition'   => [
					'field_type'   => 'address_autocomplete',
					'google_maps!' => '',
				],

			]
		);

		$this->add_control(
			'google_maps_lng',
			[
				'label'       => __( 'Longitude', 'piotnetforms' ),
				'type'        => 'text',
				'placeholder' => '105.804817',
				'description' => __( 'Latitude and Longitude Finder https://www.latlong.net/', 'piotnetforms' ),
				'default'     => '105.804817',
				'separator'   => true,
				'condition'   => [
					'field_type'   => 'address_autocomplete',
					'google_maps!' => '',
				],

			]
		);

		$this->add_control(
			'google_maps_zoom',
			[
				'label'     => __( 'Zoom', 'piotnetforms' ),
				'type'      => 'slider',
				'default'   => [
					'size' => 15,
					'unit' => 'px',
				],
				'range'     => [
					'px' => [
						'min' => 1,
						'max' => 25,
					],
				],
				'condition' => [
					'field_type'   => 'address_autocomplete',
					'google_maps!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'google_maps_height',
			[
				'label'      => __( 'Height', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 1000,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 200,
				],
				'selectors'  => [
					'{{WRAPPER}} .piotnetforms-address-autocomplete-map' => 'height:{{SIZE}}{{UNIT}}',
				],
				'condition'  => [
					'field_type'   => 'address_autocomplete',
					'google_maps!' => '',
				],
			]
		);

		$this->add_control(
			'signature_clear_text',
			[
				'label'     => __( 'Clear Text', 'piotnetforms' ),
				'type'      => 'text',
				'default'   => __( 'Clear', 'piotnetforms' ),
				'condition' => [
					'field_type' => 'signature',
				],
			]
		);

		$this->add_responsive_control(
			'signature_max_width',
			[
				'label'      => __( 'Max Width', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [
					'px' => [
						'min' => 0,
						'max' => 2000,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 400,
				],
				'selectors'  => [
					'{{WRAPPER}} canvas' => 'max-width:{{SIZE}}{{UNIT}}',
				],
				'condition'  => [
					'field_type' => 'signature',
				],
			]
		);

		$this->add_responsive_control(
			'signature_height',
			[
				'label'      => __( 'Height', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 200,
				],
				'selectors'  => [
					'{{WRAPPER}} canvas' => 'height:{{SIZE}}{{UNIT}}',
				],
				'condition'  => [
					'field_type' => 'signature',
				],
			]
		);



		$this->add_control(
			'field_label_show',
			[
				'label'        => __( 'Show Label', 'piotnetforms' ),
				'type'         => 'switch',
				'label_on'     => __( 'Show', 'piotnetforms' ),
				'label_off'    => __( 'Hide', 'piotnetforms' ),
				'return_value' => 'true',
				'default'      => 'true',
				'condition'    => [
					'field_type!' => 'html',
				],
			]
		);
		$this->add_control(
			'field_label_inline',
			[
				'label'        => __( 'Inline Label', 'piotnetforms' ),
				'type'         => 'switch',
				'label_on'     => __( 'Yes', 'piotnetforms' ),
				'label_off'    => __( 'No', 'piotnetforms' ),
				'return_value' => 'true',
				'default'      => '',
			]
		);
		$this->add_responsive_control(
			'field_label_inline_width',
			[
				'label'      => __( 'Label Width', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [ '%' ],
				'range'      => [
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default'    => [
					'unit' => '%',
					'size' => '10',
				],
				'selectors'  => [
					'{{WRAPPER}} .piotnetforms-label-inline' => 'width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .piotnetforms-field-inline' => 'width: calc(100% - {{SIZE}}%)',
				],
				'condition'  => [
					'field_label_inline' => 'true',
				],
			]
		);
		$this->add_control(
			'image_upload_field_input_width',
			[
				'label'      => __( 'Image Upload Input Width', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [ '%' ],
				'range'      => [
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default'    => [
					'unit' => '%',
				],
				'selectors'  => [
					'{{WRAPPER}} .piotnetforms-field-group .piotnetforms-image-upload-placeholder' => 'width: {{SIZE}}{{UNIT}}!important;',
				],
				'condition'  => [
					'field_type' => 'image_upload',
				],
			]
		);
		$this->add_control(
			'field_placeholder',
			[
				'label'      => __( 'Placeholder', 'piotnetforms' ),
				'label_block' => true,
				'type'       => 'text',
				'default'    => '',
				'conditions' => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'tel',
								'text',
								'email',
								'textarea',
								'number',
								'url',
								'password',
								'select_autocomplete',
								'address_autocomplete',
								'date',
								'time',
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'field_required',
			[
				'label'        => __( 'Required', 'piotnetforms' ),
				'type'         => 'switch',
				'return_value' => 'true',
				'default'      => '',
				'conditions'   => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => '!in',
							'value'    => [
								'recaptcha',
								'hidden',
								'html',
								'honeypot',
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'mark_required',
			[
				'label'     => __( 'Required Mark', 'piotnetforms' ),
				'type'      => 'switch',
				'label_on'  => __( 'Show', 'piotnetforms' ),
				'label_off' => __( 'Hide', 'piotnetforms' ),
				'default'   => '',
				'conditions'   => [
					'terms' => [
						[
							'name'     => 'field_label',
							'operator' => '!=',
							'value'    => '',
						],
						[
							'name'     => 'field_required',
							'operator' => '!=',
							'value'    => '',
						],
					],
				],
			]
		);

		// $this->add_control(
		// 	'checkbox_style_button_with_icon',
		// 	[
		// 		'label'        => __( 'Style Button With Icon', 'piotnetforms' ),
		// 		'type'         => 'switch',
		// 		'return_value' => 'yes',
		// 		'conditions'   => [
		// 			'terms' => [
		// 				[
		// 					'name'     => 'field_type',
		// 					'operator' => 'in',
		// 					'value'    => [
		// 						'checkbox',
		// 						'radio',
		// 					],
		// 				],
		// 			],
		// 		],
		// 	]
		// );

		$this->add_control(
			'piotnetforms_style_checkbox_type',
			[
				'label'   => __( 'Style', 'piotnetforms' ),
				'label_block' => true,
				'type'    => 'select',
				'options' => [
					'native' => __( 'Native / Custom Checkbox, Radio Button', 'piotnetforms' ),
					'button' => __( 'Button / Button With Icon', 'piotnetforms' ),
					'square' => __( 'Square', 'piotnetforms' ),
				],
				'default' => 'native',
				'conditions'   => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'checkbox',
								'radio',
								'acceptance',
							],
						],
					],
				],
			]
		);

		$this->new_group_controls();
		$this->add_control(
			'label',
			[
				'label' => __( 'Label', 'piotnetforms' ),
				'type'  => 'text',
			]
		);
		$this->add_control(
			'value',
			[
				'label' => __( 'Value', 'piotnetforms' ),
				'type'  => 'text',
			]
		);
		$this->add_control(
			'icon_type',
			[
				'label' => __( 'Icon Type', 'piotnetforms' ),
				'type'  => 'select',
				'options'   => [
					'font_awesome' => __( 'Font Awesome', 'piotnetforms' ),
					'image'        => __( 'Image', 'piotnetforms' ),
				],
				'default'   => 'font_awesome',
			]
		);
		$this->add_control(
			'icon_font_awesome',
			[
				'label' => __( 'Icon Uncheck', 'piotnetforms' ),
				'type'  => 'icon',
				'options_source' => 'fontawesome',
				'condition'      => [
					'icon_type' => 'font_awesome',
				],
			]
		);
		$this->add_control(
			'icon_font_awesome_checked',
			[
				'label' => __( 'Icon Checked', 'piotnetforms' ),
				'type'  => 'icon',
				'options_source' => 'fontawesome',
				'condition'      => [
					'icon_type' => 'font_awesome',
				],
			]
		);
		$this->add_control(
			'icon_image',
			[
				'label' => __( 'Icon Image Uncheck', 'piotnetforms' ),
				'type'  => 'media',
				'condition'      => [
					'icon_type' => 'image',
				],
			]
		);
		$this->add_control(
			'icon_image_checked',
			[
				'label' => __( 'Icon Image Checked', 'piotnetforms' ),
				'type'  => 'media',
				'condition'      => [
					'icon_type' => 'image',
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
			'checkbox_style_button_with_icon_list',
			[
				'type'           => 'repeater',
				'label'          => __( 'Options', 'piotnetforms' ),
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
				'condition'      => [
					'piotnetforms_style_checkbox_type' => 'button',
				],
			]
		);

		$this->add_control(
			'field_options',
			[
				'label'       => __( 'Options', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'textarea',
				'default'     => '',
				'dynamic'     => [
					'active' => true,
				],
				'description' => __( 'Enter each option in a separate line. To differentiate between label and value, separate them with a pipe char ("|"). For example: First Name|f_name.<br>Select option group:<br>[optgroup label="Swedish Cars"]<br>Volvo|volvo<br>Saab|saab<br>[/optgroup]<br>[optgroup label="German Cars"]<br>Mercedes|mercedes<br>Audi|audi<br>[/optgroup]<br><br>The get posts shortcode for ACF Relationship Field [piotnetforms_get_posts post_type="post" value="id"]', 'piotnetforms' ),
				'conditions'  => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'select',
								'select_autocomplete',
								'image_select',
								'checkbox',
								'radio',
							],
						],
						[
							'name'     => 'piotnetforms_style_checkbox_type',
							'operator' => '!=',
							'value'    => 'button',
						],
					],
				],
			]
		);

		$this->add_control(
			'piotnetforms_image_select_field_gallery',
			[
				'label'     => __( 'Add Images', 'piotnetforms' ),
				'type'      => 'gallery',
				'default'   => [],
				'condition' => [
					'field_type' => 'image_select',
				],
			]
		);

		$this->add_control(
			'allow_multiple',
			[
				'label'        => __( 'Multiple Selection', 'piotnetforms' ),
				'type'         => 'switch',
				'return_value' => 'true',
				'conditions'   => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'select',
								'image_select',
								'terms_select',
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'inline_list',
			[
				'label'        => __( 'Inline List', 'piotnetforms' ),
				'type'         => 'switch',
				'return_value' => 'piotnetforms-subgroup-inline',
				'default'      => '',
				'conditions'   => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'checkbox',
								'radio',
								'terms_select',
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'send_data_by_label',
			[
				'label'        => __( 'Send data by Label', 'piotnetforms' ),
				'type'         => 'switch',
				'return_value' => 'true',
				'conditions'   => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'select',
								'image_select',
								'terms_select',
								'checkbox',
								'radio',
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'payment_methods_select_field_enable',
			[
				'label'        => __( 'Payment Methods Select Field', 'piotnetforms' ),
				'type'         => 'switch',
				'default'      => '',
				'description'  => __( 'If you have multiple payment methods', 'piotnetforms' ),
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
				'conditions'   => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'select',
								'image_select',
								'checkbox',
								'radio',
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'payment_methods_select_field_value_for_stripe',
			[
				'label'       => __( 'Payment Methods Field Value For Stripe', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'description' => 'E.g Stripe',
				'conditions'  => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'select',
								'image_select',
								'terms_select',
								'checkbox',
								'radio',
							],
						],
						[
							'name'     => 'payment_methods_select_field_enable',
							'operator' => '==',
							'value'    => 'yes',
						],
					],
				],
			]
		);

		$this->add_control(
			'payment_methods_select_field_value_for_paypal',
			[
				'label'       => __( 'Payment Methods Field Value For Paypal', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'description' => 'E.g Paypal',
				'conditions'  => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'select',
								'image_select',
								'terms_select',
								'checkbox',
								'radio',
							],
						],
						[
							'name'     => 'payment_methods_select_field_enable',
							'operator' => '=',
							'value'    => 'yes',
						],
					],
				],
			]
		);

		$this->add_control(
			'stripe_icon_color',
			[
				'type'        => 'color',
				'label'       => __( 'Icon Color', 'piotnetforms' ),
				'value'       => '',
				'conditions'   => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => '==',
							'value'    => 'stripe_payment'
						],
						[
							'name'     => 'stripe_custom_style_option',
							'operator' => '!=',
							'value'    => 'yes'
						]
					],
				],
			]
		);
		$this->add_control(
			'stripe_background_color',
			[
				'type'        => 'color',
				'label'       => __( 'Background Color', 'piotnetforms' ),
				'value'       => '',
				'conditions'   => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => '==',
							'value'    => 'stripe_payment'
						],
						[
							'name'     => 'stripe_custom_style_option',
							'operator' => '!=',
							'value'    => 'yes'
						]
					],
				],
			]
		);
		$this->add_control(
			'stripe_color',
			[
				'type'        => 'color',
				'label'       => __( 'Color', 'piotnetforms' ),
				'value'       => '',
				'conditions'   => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => '==',
							'value'    => 'stripe_payment'
						],
						[
							'name'     => 'stripe_custom_style_option',
							'operator' => '!=',
							'value'    => 'yes'
						]
					],
				],
			]
		);
		$this->add_control(
			'stripe_placeholder_color',
			[
				'type'        => 'color',
				'label'       => __( 'Placeholder Color', 'piotnetforms' ),
				'value'       => '',
				'conditions'   => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => '==',
							'value'    => 'stripe_payment'
						],
						[
							'name'     => 'stripe_custom_style_option',
							'operator' => '!=',
							'value'    => 'yes'
						]
					],
				],
			]
		);
		$this->add_control(
			'stripe_font_size',
			[
				'label'      => __( 'Font Size', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 5,
						'max'  => 100,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 16,
				],
				'conditions'   => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => '==',
							'value'    => 'stripe_payment'
						],
						[
							'name'     => 'stripe_custom_style_option',
							'operator' => '!=',
							'value'    => 'yes'
						]
					],
				],
			]
		);
		$this->add_control(
			'stripe_custom_style_option',
			[
				'label'        	=> __( 'Custom Style?', 'piotnetforms' ),
				'type'         	=> 'switch',
				'return_value' 	=> 'yes',
				'condition' 	=> [
					'field_type' => 'stripe_payment'
				]
			]
		);
		$this->add_control(
			'stripe_custom_font_family',
			[
				'label'       => __( 'URL Font', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'conditions'   => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => '==',
							'value'    => 'stripe_payment'
						],
						[
							'name'     => 'stripe_custom_style_option',
							'operator' => '==',
							'value'    => 'yes'
						]
					],
				],
			]
		);
		$this->add_control(
			'stripe_custom_style',
			[
				'label'     	=> __( 'Custon:', 'piotnetforms' ),
				'type'      	=> 'textarea',
				'default'      	=> '{"base":{"color":"#303238","fontSize":"16px","fontFamily":"\"Open Sans\", sans-serif","fontSmoothing":"antialiased","::placeholder":{"color":"#CFD7DF"}},"invalid":{"color":"#e5424d",":focus":{"color":"#303238"}}}',
				'description'	=> __( 'View options at <a target="_blank" href="https://stripe.com/docs/js/appendix/style">stripe style</a>', 'piotnetforms' ),
				'label_block'	=> true,
				'conditions'   => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => '==',
							'value'    => 'stripe_payment'
						],
						[
							'name'     => 'stripe_custom_style_option',
							'operator' => '==',
							'value'    => 'yes'
						]
					],
				],
			]
		);
		$this->add_control(
			'field_taxonomy_slug',
			[
				'label'       => __( 'Taxonomy Slug', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'default'     => __( 'category', 'piotnetforms' ),
				'description' => __( 'E.g: category, post_tag', 'piotnetforms' ),
				'condition'   => [
					'field_type' => 'terms_select',
				],
			]
		);

		$this->add_control(
			'terms_select_type',
			[
				'label'       => __( 'Terms Select Type', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'default'     => 'select',
				'options'     => [
					'select'   => __( 'Select', 'piotnetforms' ),
					'select2'  => __( 'Select 2', 'piotnetforms' ),
					'checkbox' => __( 'Checkbox', 'piotnetforms' ),
					'radio'    => __( 'Radio', 'piotnetforms' ),
				],
				'condition'   => [
					'field_type' => 'terms_select',
				],
			]
		);

		$this->add_control(
			'limit_multiple',
			[
				'label'     => __( 'Limit Multiple Selects', 'piotnetforms' ),
				'type'      => 'number',
				'default'   => 0,
				'condition' => [
					'field_type'     => 'image_select',
					'allow_multiple' => 'true',
				],
			]
		);

		$this->add_control(
			'checkbox_limit_multiple',
			[
				'label'     => __( 'Limit Multiple Selects', 'piotnetforms' ),
				'type'      => 'number',
				'default'   => 0,
				'condition' => [
					'field_type'     => 'checkbox',
				],
			]
		);

		$this->add_control(
			'select_size',
			[
				'label'      => __( 'Rows', 'piotnetforms' ),
				'type'       => 'number',
				'min'        => 2,
				'step'       => 1,
				'conditions' => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'select',
								'select_autocomplete',
								'terms_select',
								'image_select',
							],
						],
						[
							'name'  => 'allow_multiple',
							'value' => 'true',
						],
					],
				],
			]
		);

		$this->add_control(
			'file_sizes',
			[
				'label'       => __( 'Max. File Size', 'piotnetforms' ),
				'type'        => 'select',
				'condition'   => [
					'field_type' => 'upload',
				],
				'options'     => $this->get_upload_file_size_options(),
				'description' => __( 'If you need to increase max upload size please contact your hosting.', 'piotnetforms' ),
			]
		);

		$this->add_control(
			'file_sizes_message',
			[
				'label'       => __( 'Max. File Size Error Message', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'default'     => __( 'File size must be less than 1MB', 'piotnetforms' ),
				'condition'   => [
					'field_type' => 'upload',
				],
			]
		);

		$this->add_control(
			'file_types',
			[
				'label'       => __( 'Allowed File Types', 'piotnetforms' ),
				'type'        => 'text',
				'condition'   => [
					'field_type' => 'upload',
					'field_type!' => 'image_upload',
				],
				'description' => __( 'Enter the allowed file types, separated by a comma (jpg, gif, pdf, etc).', 'piotnetforms' ),
			]
		);

		$this->add_control(
			'file_types_message',
			[
				'label'       => __( 'Allowed File Types Error Message', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'default'     => __( 'Please enter a value with a valid mimetype.', 'piotnetforms' ),
				'condition'   => [
					'field_type' => 'upload',
				],
			]
		);

		$this->add_control(
			'modern_upload_field_style',
			[
				'label'        => __( 'Modern Style', 'piotnetforms' ),
				'type'         => 'switch',
				'label_on'     => __( 'Show', 'piotnetforms' ),
				'label_off'    => __( 'Hide', 'piotnetforms' ),
				'return_value' => 'true',
				'default'      => '',
				'condition'    => [
					'field_type' => 'upload',
				],
			]
		);

		$this->add_control(
			'modern_upload_field_text',
			[
				'label'       => __( 'Upload File Custom Text', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'default'     => __( 'Upload File', 'piotnetforms' ),
				'conditions'   => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => '=',
							'value'    => 'upload',
						],
						[
							'name'     => 'modern_upload_field_style',
							'operator' => '=',
							'value'    => 'true',
						],
					],
				],
			]
		);

		$this->add_control(
			'allow_multiple_upload',
			[
				'label'        => __( 'Multiple Files', 'piotnetforms' ),
				'type'         => 'switch',
				'return_value' => 'true',
				'conditions'   => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'upload',
								'image_upload',
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'max_files',
			[
				'label'     => __( 'Max Files', 'piotnetforms' ),
				'type'      => 'number',
				'condition' => [
					'field_type'            => 'image_upload',
					'allow_multiple_upload' => 'true',
				],
			]
		);

		// $this->add_control(
		// 	'max_files' => [
		// 		'label' => __( 'Max. Files', 'piotnetforms' ),
		// 		'type' => 'number',
		// 		'condition' => [
		// 			'field_type' => 'upload',
		// 			'allow_multiple_upload' => 'yes',
		// 		],
		// 		'tab' => 'content',
		// 		'inner_tab' => 'form_fields_content_tab',
		// 		'tabs_wrapper' => 'form_fields_tabs',
		// 	],
		// );

		$this->add_control(
			'attach_files',
			[
				'label'     => __( 'Attach files to email, do not upload to upload folder', 'piotnetforms' ),
				'type'      => 'switch',
				'condition' => [
					'field_type' => ['upload', 'image_upload']
				],
			]
		);

		$this->add_control(
			'field_html',
			[
				'label'      => __( 'HTML', 'piotnetforms' ),
				'type'       => 'textarea',
				'dynamic'    => [
					'active' => true,
				],
				'conditions' => [
					'terms' => [
						[
							'name'  => 'field_type',
							'value' => 'html',
						],
					],
				],
			]
		);

        $this->add_control(
			'alignment_html',
			[
				'label'      => __( 'Alignment', 'piotnetforms' ),
				'type'       => 'select',
				'default'    => '',
				'options'    => [
                    'flex-start'      => __( 'Default', 'piotnetforms' ),
					'center'  => __( 'Center', 'piotnetforms' ),
                    'flex-end' => __( 'Right', 'piotnetforms' ),
				],
				'conditions' => [
					'terms' => [
						[
							'name'  => 'field_type',
							'value' => 'html',
						],
					],
				],
                'selectors'   => [
					'{{WRAPPER}} .piotnetforms-field-container' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'rows',
			[
				'label'      => __( 'Rows', 'piotnetforms' ),
				'type'       => 'number',
				'default'    => 4,
				'conditions' => [
					'terms' => [
						[
							'name'  => 'field_type',
							'value' => 'textarea',
						],
					],
				],
			]
		);

		$this->add_control(
			'min_select',
			[
				'label'     => __( 'Min Select', 'piotnetforms' ),
				'type'      => 'number',
				'default'   => 0,
				'condition' => [
					'field_type'     => 'image_select',
					'allow_multiple' => 'true',
				],
			]
		);

		$this->add_control(
			'min_select_required_message',
			[
				'label'       => __( 'Required Message', 'piotnetforms' ),
				'type'        => 'text',
				'value'       => __( 'Please select the minimum number of images.', 'piotnetforms' ),
				'placeholder' => __( 'Please select the minimum number of images.', 'piotnetforms' ),
				'label_block' => true,
				'render_type' => 'none',
				'condition' => [
					'field_type'     => 'image_select',
					'allow_multiple' => 'true',
				],
			]
		);

		$this->add_control(
			'recaptcha_size',
			[
				'label'      => __( 'Size', 'piotnetforms' ),
				'type'       => 'select',
				'default'    => 'normal',
				'options'    => [
					'normal'  => __( 'Normal', 'piotnetforms' ),
					'compact' => __( 'Compact', 'piotnetforms' ),
				],
				'conditions' => [
					'terms' => [
						[
							'name'  => 'field_type',
							'value' => 'recaptcha',
						],
					],
				],
			]
		);

		$this->add_control(
			'recaptcha_style',
			[
				'label'      => __( 'Style', 'piotnetforms' ),
				'type'       => 'select',
				'default'    => 'light',
				'options'    => [
					'light' => __( 'Light', 'piotnetforms' ),
					'dark'  => __( 'Dark', 'piotnetforms' ),
				],
				'conditions' => [
					'terms' => [
						[
							'name'  => 'field_type',
							'value' => 'recaptcha',
						],
					],
				],
			]
		);

		// $this->add_control(
		// 	'css_classes',
		// 	[
		// 		'label' => __( 'CSS Classes', 'piotnetforms' ),
		// 		'type' => \Elementor\Controls_Manager::HIDDEN,
		// 		'default' => '',
		// 		'title' => __( 'Add your custom class WITHOUT the dot. e.g: my-class', 'piotnetforms' ),
		// 	]
		// );

		$this->add_control(
			'field_value',
			[
				'label'      => __( 'Default Value', 'piotnetforms' ),
				'label_block' => true,
				'type'       => 'text',
				'default'    => '',
				'dynamic'    => [
					'active' => true,
				],
				'conditions' => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'text',
								'email',
								'textarea',
								'url',
								'tel',
								'radio',
								'checkbox',
								'select',
								'select_autocomplete',
								'terms_select',
								'image_select',
								'number',
								'date',
								'time',
								'hidden',
								'address_autocomplete',
								'color',
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'field_value_color_note',
			[
				'type'       => 'html',
				'class'      => 'piotnetforms-control-field-description',
				'raw'        => __( 'E.g: #000000. The value must be in seven-character hexadecimal notation.', 'piotnetforms' ),
				'conditions' => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'color',
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'field_min',
			[
				'name'       => 'field_min',
				'label'      => __( 'Min. Value', 'piotnetforms' ),
				'type'       => 'number',
				'conditions' => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'number',
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'field_max',
			[
				'label'      => __( 'Max. Value', 'piotnetforms' ),
				'type'       => 'number',
				'conditions' => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'number',
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'number_spiner',
			[
				'label'      => __( 'Add (-/+) button', 'piotnetforms' ),
				'type'       => 'switch',
				'default'    => '',
				'label_on'   => 'Yes',
				'label_off'  => 'No',
				'conditions' => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'number',
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'acceptance_text',
			[
				'label'      => __( 'Acceptance Text', 'piotnetforms' ),
				'type'       => 'textarea',
				'conditions' => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'acceptance',
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'checked_by_default',
			[
				'label'      => __( 'Checked by Default', 'piotnetforms' ),
				'type'       => 'switch',
				'conditions' => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'acceptance',
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'flatpickr_custom_options_enable',
			[
				'label'        => __( 'Flatpickr Custom Options', 'piotnetforms' ),
				'type'         => 'switch',
				'label_on'     => __( 'Yes', 'piotnetforms' ),
				'label_off'    => __( 'No', 'piotnetforms' ),
				'return_value' => 'yes',
				'description'  => 'https://flatpickr.js.org/examples/',
				'default'      => '',
				'conditions'   => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'date',
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'flatpickr_custom_options',
			[
				'label'      => __( 'Flatpickr Options', 'piotnetforms' ),
				'type'       => 'textarea',
				'conditions' => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'date',
							],
						],
						[
							'name'     => 'flatpickr_custom_options_enable',
							'operator' => '==',
							'value'    => 'yes',
						],
					],
				],
			]
		);

		$this->add_control(
			'date_range',
			[
				'label'        => __( 'Date Range', 'piotnetforms' ),
				'type'         => 'switch',
				'label_on'     => __( 'Yes', 'piotnetforms' ),
				'label_off'    => __( 'No', 'piotnetforms' ),
				'return_value' => 'true',
				'default'      => '',
				'conditions'   => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'date',
							],
						],
						[
							'name'     => 'flatpickr_custom_options_enable',
							'operator' => '==',
							'value'    => '',
						],
					],
				],
			]
		);

		$this->add_control(
			'min_date',
			[
				'label'          => __( 'Min. Date', 'piotnetforms' ),
				'type'           => 'date',
				'label_block'    => false,
				'picker_options' => [
					'enableTime' => false,
				],
				'conditions'     => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'date',
							],
						],
						[
							'name'     => 'flatpickr_custom_options_enable',
							'operator' => '==',
							'value'    => '',
						],
					],
				],
			]
		);

		$this->add_control(
			'min_date_current',
			[
				'label'        => __( 'Set Current Date for Min. Date', 'piotnetforms' ),
				'type'         => 'switch',
				'default'      => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
				'conditions'   => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'date',
							],
						],
						[
							'name'     => 'flatpickr_custom_options_enable',
							'operator' => '==',
							'value'    => '',
						],
					],
				],
			]
		);

		$this->add_control(
			'max_date',
			[
				'name'           => 'max_date',
				'label'          => __( 'Max. Date', 'piotnetforms' ),
				'type'           => 'date',
				'label_block'    => false,
				'picker_options' => [
					'enableTime' => false,
				],
				'conditions'     => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'date',
							],
						],
						[
							'name'     => 'flatpickr_custom_options_enable',
							'operator' => '==',
							'value'    => '',
						],
					],
				],
			]
		);

		$this->add_control(
			'max_date_current',
			[
				'label'        => __( 'Set Current Date for Max. Date', 'piotnetforms' ),
				'type'         => 'switch',
				'default'      => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
				'conditions'   => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'date',
							],
						],
						[
							'name'     => 'flatpickr_custom_options_enable',
							'operator' => '==',
							'value'    => '',
						],
					],
				],
			]
		);

		$date_format = esc_attr( get_option( 'date_format' ) );

		$this->add_control(
			'date_format',
			[
				'label'       => __( 'Date Format', 'piotnetforms' ),
				'type'        => 'text',
				'label_block' => false,
				'default'     => $date_format,
				'dynamic'     => [
					'active' => true,
				],
				'conditions'  => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'date',
							],
						],
						[
							'name'     => 'flatpickr_custom_options_enable',
							'operator' => '==',
							'value'    => '',
						],
					],
				],
			]
		);

		$this->add_control(
			'date_language',
			[
				'label'       => __( 'Date Language', 'piotnetforms' ),
				'type'        => 'select',
				'label_block' => false,
				'description' => __( 'This feature only works on the frontend.', 'piotnetforms' ),
				'options'     => [
					'ar'      => 'Arabic',
					'at'      => 'Austria',
					'az'      => 'Azerbaijan',
					'be'      => 'Belarusian',
					'bg'      => 'Bulgarian',
					'bn'      => 'Bangla',
					'bs'      => 'Bosnian',
					'cat'     => 'Catalan',
					'cs'      => 'Czech',
					'cy'      => 'Welsh',
					'da'      => 'Danish',
					'de'      => 'German',
					'english' => 'English',
					'eo'      => 'Esperanto',
					'es'      => 'Spanish',
					'et'      => 'Estonian',
					'fa'      => 'Persian',
					'fi'      => 'Finnish',
					'fo'      => 'Faroese',
					'fr'      => 'French',
					'ga'      => 'Irish',
					'gr'      => 'Greek',
					'he'      => 'Hebrew',
					'hi'      => 'Hindi',
					'hr'      => 'Croatian',
					'hu'      => 'Hungarian',
					'id'      => 'Indonesian',
					'is'      => 'Icelandic',
					'it'      => 'Italian',
					'ja'      => 'Japanese',
					'ka'      => 'Georgian',
					'km'      => 'Khmer',
					'ko'      => 'Korean',
					'kz'      => 'Kazakh',
					'lt'      => 'Lithuanian',
					'lv'      => 'Latvian',
					'mk'      => 'Macedonian',
					'mn'      => 'Mongolian',
					'ms'      => 'Malaysian',
					'my'      => 'Burmese',
					'nl'      => 'Dutch',
					'no'      => 'Norwegian',
					'pa'      => 'Punjabi',
					'pl'      => 'Polish',
					'pt'      => 'Portuguese',
					'ro'      => 'Romanian',
					'ru'      => 'Russian',
					'si'      => 'Sinhala',
					'sk'      => 'Slovak',
					'sl'      => 'Slovenian',
					'sq'      => 'Albanian',
					'sr-cyr'  => 'SerbianCyrillic',
					'sr'      => 'Serbian',
					'sv'      => 'Swedish',
					'th'      => 'Thai',
					'tr'      => 'Turkish',
					'uk'      => 'Ukrainian',
					'vn'      => 'Vietnamese',
					'zh-tw'   => 'MandarinTraditional',
					'zh'      => 'Mandarin',
				],
				'default'     => 'english',
				'conditions'  => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'date',
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'use_native_date',
			[
				'label'      => __( 'Native HTML5', 'piotnetforms' ),
				'type'       => 'switch',
				'conditions' => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'date',
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'time_format',
			[
				'label'       => __( 'Time Format', 'piotnetforms' ),
				'type'        => 'text',
				'label_block' => false,
				'default'     => 'h:i K',
				'dynamic'     => [
					'active' => true,
				],
				'conditions'  => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'time',
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'time_minute_increment',
			[
				'name'       => 'time_minute_increment',
				'label'      => __( 'Minute Increment', 'piotnetforms' ),
				'type'       => 'number',
				'min'       => 5,
				'max'       => 60,
				'default'   => 5,
				'conditions' => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'time',
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'time_24hr',
			[
				'label'        => __( '24 hour', 'piotnetforms' ),
				'type'         => 'switch',
				'default'      => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
				'conditions'   => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'time',
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'use_native_time',
			[
				'label'      => __( 'Native HTML5', 'piotnetforms' ),
				'type'       => 'switch',
				'conditions' => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'time',
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'piotnetforms_range_slider_field_options',
			[
				'label'       => __( 'Range Slider Options', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'textarea',
				'default'     => 'skin: "round", type: "double", grid: true, min: 0, max: 1000, from: 200, to: 800, prefix: "$"',
				'description' => 'Demo: <a href="http://ionden.com/a/plugins/ion.rangeSlider/demo.html" target="_blank">http://ionden.com/a/plugins/ion.rangeSlider/demo.html</a>',
				'condition'   => [
					'field_type' => 'range_slider',
				],
			]
		);

		// $element->add_group_control(
		// 	\Elementor\Group_Control_Typography::get_type(),
		// 	[
		// 		'name' => 'piotnetforms_calculated_fields_form_typography',
		// 		'label' => __( 'Typography', 'piotnetforms' ),
		// 		'scheme' => \Elementor\Scheme_Typography::TYPOGRAPHY_1,
		// 		'selector' => '{{WRAPPER}} .piotnetforms-calculated-fields-form',
		// 	]
		// );

		$this->new_group_controls();
		$this->add_control(
			'piotnetforms_coupon_code',
			[
				'label' => __( 'Coupon Code', 'piotnetforms' ),
				'type'  => 'text',
			]
		);
		$this->add_control(
			'piotnetforms_coupon_code_discount_type',
			[
				'label'   => __( 'Discount Type', 'piotnetforms' ),
				'type'    => 'select',
				'options' => [
					'percentage'  => __( 'Percentage', 'piotnetforms' ),
					'flat_amount' => __( 'Flat Amount', 'piotnetforms' ),
				],
				'default' => 'percentage',
			]
		);
		$this->add_control(
			'piotnetforms_coupon_code_coupon_amount',
			[
				'label' => __( 'Coupon Amount', 'piotnetforms' ),
				'type'  => 'text',
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
			'piotnetforms_coupon_code_list',
			[
				'type'           => 'repeater',
				'label'          => __( 'Coupon Code', 'piotnetforms' ),
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
				'condition'      => [
					'field_type' => 'coupon_code',
				],
			]
		);

		$this->add_control(
			'piotnetforms_calculated_fields_form_calculation',
			[
				'label'       => __( 'Calculation', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'textarea',
				'dynamic'     => true,
				'get_fields'  => true,
				'description' => __( 'E.g [field id="quantity"]*[field id="price"]+10', 'piotnetforms' ),
				'condition'   => [
					'field_type' => 'calculated_fields',
					'piotnetforms_calculated_fields_form_distance_calculation!' => 'yes',
				],
			]
		);

		$this->add_control(
			'piotnetforms_calculated_fields_form_coupon_code',
			[
				'label'       => __( 'Coupon Code Field Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'description' => __( 'E.g [field id="coupon_code"]', 'piotnetforms' ),
				'condition'   => [
					'field_type' => 'calculated_fields',
				],
			]
		);

		$this->add_control(
			'piotnetforms_calculated_fields_form_distance_calculation',
			[
				'label'        => __( 'Distance Calculation', 'piotnetforms' ),
				'type'         => 'switch',
				'default'      => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
				'condition'    => [
					'field_type' => 'calculated_fields',
				],
			]
		);

		$this->add_control(
			'piotnetforms_calculated_fields_form_distance_calculation_from_specific_location_enable',
			[
				'label'        => __( 'From Specific Location', 'piotnetforms' ),
				'type'         => 'switch',
				'default'      => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
				'condition'   => [
					'field_type' => 'calculated_fields',
					'piotnetforms_calculated_fields_form_distance_calculation' => 'yes',
				],
			]
		);

		$this->add_control(
			'piotnetforms_calculated_fields_form_distance_calculation_from_specific_location',
			[
				'label'       => __( 'From Location', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'description' => __( 'Please go to https://www.google.com/maps and type your address to get exactly location', 'piotnetforms' ),
				'condition'   => [
					'field_type' => 'calculated_fields',
					'piotnetforms_calculated_fields_form_distance_calculation' => 'yes',
					'piotnetforms_calculated_fields_form_distance_calculation_from_specific_location_enable' => 'yes',
				],
			]
		);

		$this->add_control(
			'piotnetforms_calculated_fields_form_distance_calculation_from_field_shortcode',
			[
				'label'       => __( 'From Field Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'description' => __( 'E.g [field id="from"]', 'piotnetforms' ),
				'condition'   => [
					'field_type' => 'calculated_fields',
					'piotnetforms_calculated_fields_form_distance_calculation' => 'yes',
					'piotnetforms_calculated_fields_form_distance_calculation_from_specific_location_enable!' => 'yes',
				],
			]
		);

		$this->add_control(
			'piotnetforms_calculated_fields_form_distance_calculation_to_specific_location_enable',
			[
				'label'        => __( 'To Specific Location', 'piotnetforms' ),
				'type'         => 'switch',
				'default'      => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
				'condition'   => [
					'field_type' => 'calculated_fields',
					'piotnetforms_calculated_fields_form_distance_calculation' => 'yes',
				],
			]
		);

		$this->add_control(
			'piotnetforms_calculated_fields_form_distance_calculation_to_specific_location',
			[
				'label'       => __( 'To Location', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'description' => __( 'Please go to https://www.google.com/maps and type your address to get exactly location', 'piotnetforms' ),
				'condition'   => [
					'field_type' => 'calculated_fields',
					'piotnetforms_calculated_fields_form_distance_calculation' => 'yes',
					'piotnetforms_calculated_fields_form_distance_calculation_to_specific_location_enable' => 'yes',
				],
			]
		);

		$this->add_control(
			'piotnetforms_calculated_fields_form_distance_calculation_to_field_shortcode',
			[
				'label'       => __( 'To Field Shortcode', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'description' => __( 'E.g [field id="to"]', 'piotnetforms' ),
				'condition'   => [
					'field_type' => 'calculated_fields',
					'piotnetforms_calculated_fields_form_distance_calculation' => 'yes',
					'piotnetforms_calculated_fields_form_distance_calculation_to_specific_location_enable!' => 'yes',
				],
			]
		);

		$this->add_control(
			'piotnetforms_calculated_fields_form_distance_calculation_unit',
			[
				'label'       => __( 'Distance Unit', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'options'     => [
					'km'   => 'Kilometer',
					'mile' => 'Mile',
				],
				'default'     => 'km',
				'condition'   => [
					'field_type' => 'calculated_fields',
					'piotnetforms_calculated_fields_form_distance_calculation' => 'yes',
				],
			]
		);

		$this->add_control(
			'piotnetforms_calculated_fields_form_calculation_rounding_decimals',
			[
				'label'     => __( 'Rounding Decimals', 'piotnetforms' ),
				'type'      => 'number',
				'default'   => 2,
				'condition' => [
					'field_type' => 'calculated_fields',
				],
			]
		);

		$this->add_control(
			'piotnetforms_calculated_fields_form_calculation_rounding_decimals_show',
			[
				'label'        => __( 'Always show decimal places', 'piotnetforms' ),
				'type'         => 'switch',
				'default'      => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
				'condition'    => [
					'field_type' => 'calculated_fields',
				],
			]
		);

		$this->add_control(
			'piotnetforms_calculated_fields_form_calculation_rounding_decimals_decimals_symbol',
			[
				'label'     => __( 'Decimal point character', 'piotnetforms' ),
				'type'      => 'text',
				'default'   => '.',
				'condition' => [
					'field_type' => 'calculated_fields',
				],
			]
		);

		$this->add_control(
			'piotnetforms_calculated_fields_form_calculation_rounding_decimals_seperators_symbol',
			[
				'label'     => __( 'Separator character', 'piotnetforms' ),
				'type'      => 'text',
				'default'   => ',',
				'condition' => [
					'field_type' => 'calculated_fields',
				],
			]
		);

		$this->add_control(
			'piotnetforms_calculated_fields_form_before',
			[
				'label'       => __( 'Before Content', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'description' => __( 'E.g $', 'piotnetforms' ),
				'condition'   => [
					'field_type' => 'calculated_fields',
				],
			]
		);

		$this->add_control(
			'piotnetforms_calculated_fields_form_after',
			[
				'label'       => __( 'After Content', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'text',
				'description' => __( 'E.g $', 'piotnetforms' ),
				'condition'   => [
					'field_type' => 'calculated_fields',
				],
			]
		);
	}

	public function checkbox_button_style_controls( string $name, $args = [] ) {
		$wrapper = isset( $args['selectors'] ) ? $args['selectors'] : '{{WRAPPER}}';
		$previous_controls = $this->new_group_controls();
		$this->add_control(
			$name.'_color',
			[
				'type'        => 'color',
				'label'       => __( 'Text Color', 'piotnetforms' ),
				'label_block' => true,
				'selectors'   => [
					$wrapper => 'color: {{VALUE}};',
				],
			]
		);

		// pf-f-o-i = piotnetforms-field-option-icon

		$this->add_control(
			$name.'_icon_color',
			[
				'type'        => 'color',
				'label'       => __( 'Icon Color', 'piotnetforms' ),
				'label_block' => true,
				'selectors'   => [
					$wrapper . ' .pf-f-o-i-font-awesome' => 'color: {{VALUE}};',
				],
			]
		);
		$this->add_text_typography_controls(
			$name.'_text_typography',
			[
				'selectors' => $wrapper,
			]
		);
		$this->add_control(
			$name.'_background_color',
			[
				'type'        => 'color',
				'label'       => __( 'Button Background Color', 'piotnetforms' ),
				'value'       => '',
				'label_block' => true,
				'selectors'   => [
					$wrapper => 'background-color: {{VALUE}};',
				],
				'condition'  => [
					'piotnetforms_style_checkbox_type' => ['button'],
				],
			]
		);
		$this->add_control(
			$name.'_button_border_type',
			[
				'type'      => 'select',
				'label'     => __( 'Button Border Type', 'piotnetforms' ),
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
				'condition'  => [
					'piotnetforms_style_checkbox_type' => ['button'],
				],
			]
		);
		$this->add_control(
			$name.'_button_border_color',
			[
				'type'        => 'color',
				'label'       => __( 'Button Border Color', 'piotnetforms' ),
				'value'       => '',
				'label_block' => true,
				'selectors'   => [
					$wrapper => 'border-color: {{VALUE}};',
				],
				'conditions'  => [
					[
						'name'     => 'piotnetforms_style_checkbox_type',
						'operator' => '==',
						'value'    => 'button',
					],
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
				'label'       => __( 'Button Border Width', 'piotnetforms' ),
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
						'name'     => 'piotnetforms_style_checkbox_type',
						'operator' => '==',
						'value'    => 'button',
					],
					[
						'name'     => $name.'_button_border_type',
						'operator' => '!=',
						'value'    => '',
					],
				],
			]
		);
		return $this->get_group_controls( $previous_controls );
	}

	private function add_settings_advanced_controls() {
		$this->add_control(
			'field_description',
			[
				'label'      => __( 'Description', 'piotnetforms' ),
				'label_block' => true,
				'type'       => 'text',
			]
		);

		$this->add_control(
			'field_pattern',
			[
				'label'      => __( 'Pattern', 'piotnetforms' ),
				'label_block' => true,
				'type'       => 'text',
				'default'    => '[0-9()#&+*-=.]+',
				'conditions' => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'tel',
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'field_pattern_not_tel',
			[
				'label'      => __( 'Pattern', 'piotnetforms' ),
				'label_block' => true,
				'type'       => 'text',
				'conditions' => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'text',
								'email',
								'textarea',
								'url',
								'number',
								'password',
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'field_dial_code',
			[
				'label'        => __( 'International Telephone Input', 'piotnetforms' ),
				'type'         => 'switch',
				'label_on'     => __( 'On', 'piotnetforms' ),
				'label_off'    => __( 'Off', 'piotnetforms' ),
				'return_value' => 'true',
				'default'      => '',
				'conditions' => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'tel',
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'invalid_message',
			[
				'label'      => __( 'Invalid Message', 'piotnetforms' ),
				'label_block' => true,
				'type'       => 'text',
				'conditions' => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => '!in',
							'value'    => [
								'recaptcha',
								'hidden',
								'html',
								'honeypot',
								'iban'
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'iban_invalid_message',
			[
				'label'      => __( 'Invalid Message', 'piotnetforms' ),
				'type'       => 'text',
				'default'	 => 'This IBAN is invalid.',
				'condition' => [
					'field_type' => 'iban'
				],
			]
		);

		$this->add_control(
			'field_autocomplete',
			[
				'label'        => __( 'Autocomplete', 'piotnetforms' ),
				'type'         => 'switch',
				'label_on'     => __( 'On', 'piotnetforms' ),
				'label_off'    => __( 'Off', 'piotnetforms' ),
				'return_value' => 'true',
				'default'      => 'true',
				'condition'    => [
					'field_type!' => 'html',
				],
			]
		);

		$this->add_control(
			'max_length',
			[
				'label'      => __( 'Max Length', 'piotnetforms' ),
				'type'       => 'number',
				'conditions' => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'text',
								'email',
								'textarea',
								'url',
								'tel',
								'number',
								'password',
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'min_length',
			[
				'label'      => __( 'Min Length', 'piotnetforms' ),
				'type'       => 'number',
				'conditions' => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'tel',
								'textarea',
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'remove_this_field_from_repeater',
			[
				'label'        => __( 'Remove this field from the Repeater in the email', 'piotnetforms' ),
				'type'         => 'switch',
				'return_value' => 'true',
			]
		);

		$this->add_control(
			'field_remove_option_value',
			[
				'label'      => __( 'Remove this field from email message', 'piotnetforms' ),
				'type'       => 'switch',
				'default'    => '',
				'label_on'   => 'Yes',
				'label_off'  => 'No',
			]
		);

		$this->add_control(
			'field_value_remove',
			[
				'label'      => __( 'If Field Value is equal', 'piotnetforms' ),
				'type'       => 'text',
				'default'    => '',
				'dynamic'    => [
					'active' => true,
				],
				'conditions' => [
					[
						'name' => 'field_remove_option_value',
						'operator' => '==',
						'value' => 'true'
					]
				]
			]
		);

		$this->add_control(
			'multi_step_form_autonext',
			[
				'label'        => __( 'Automatically move to the next step after selecting - Multi Step Form', 'piotnetforms' ),
				'type'         => 'switch',
				'default'      => '',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
				'conditions'   => [
					'terms' => [
						[
							'name'     => 'field_type',
							'operator' => 'in',
							'value'    => [
								'select',
								'select_autocomplete',
								'image_select',
								'checkbox',
								'radio',
							],
						],
					],
				],
			]
		);

		$this->add_control(
			'shortcode',
			[
				'label'   => __( 'Shortcode', 'piotnetforms' ),
				'type'    => 'text',
				'classes' => 'piotnetforms-field-shortcode',
				'attr'    => 'readonly',
				'copy'    => true,
			]
		);

		$this->add_control(
			'live_preview_code',
			[
				'label'   => __( 'Live Preview Code', 'piotnetforms' ),
				'type'    => 'text',
				'classes' => 'piotnetforms-live-preview-code',
				'attr'    => 'readonly',
				'description' => __( 'Paste this code to anywhere to live preview this field value', 'piotnetforms' ),
				'condition' => [
					'field_type!' => 'image_upload'
				]
			]
		);

		$this->add_control(
			'live_preview_image',
			[
				'label'   => __( 'Live Preview Code', 'piotnetforms' ),
				'type'    => 'text',
				'classes' => 'piotnetforms-live-preview-code',
				'attr'    => 'readonly',
				'description' => __( 'Paste this code to anywhere to live preview this field value', 'piotnetforms' ),
				'condition' => [
					'field_type' => 'image_upload'
				]
			]
		);

		$this->add_control(
			'live_preview_image_width',
			[
				'label'     => __( 'Width', 'piotnetforms' ),
				'type'      => 'number',
				'default'	=> 150,
				'condition' => [
					'field_type' => 'image_upload'
				]
			]
		);

		$this->add_control(
			'live_preview_image_height',
			[
				'label'     => __( 'Height', 'piotnetforms' ),
				'type'      => 'number',
				'default'	=> 150,
				'condition' => [
					'field_type' => 'image_upload'
				]
			]
		);

		$this->add_control(
			'live_preview_show_label',
			[
				'label'       => __( 'Show label preview', 'piotnetforms' ),
				'type'        => 'switch',
				'label_on'    => __( 'Yes', 'piotnetforms' ),
				'label_off'   => __( 'No', 'piotnetforms' ),
				'default'     => '',
				'condition'  => [
					'field_type' => ['select', 'checkbox', 'radio']
				]
			]
		);
	}

	public function add_input_mask_controls() {
		$this->add_control(
			'input_mask_enable',
			[
				'label'        => __( 'Enable', 'piotnetforms' ),
				'type'         => 'switch',
				'label_on'     => 'Yes',
				'label_off'    => 'No',
				'return_value' => 'yes',
			]
		);

		$this->add_control(
			'input_mask',
			[
				'label'       => __( 'Mask', 'piotnetforms' ),
				'type'        => 'text',
				'description' => __( 'E.g (00) 0000-0000.<br>Documents:<br>https://igorescobar.github.io/jQuery-Mask-Plugin/docs.html', 'piotnetforms' ),
				'condition'   => [
					'input_mask_enable!' => '',
				],
			]
		);

		$this->add_control(
			'input_mask_reverse',
			[
				'label'        => __( 'Reverse', 'piotnetforms' ),
				'type'         => 'switch',
				'label_on'     => 'True',
				'label_off'    => 'False',
				'return_value' => 'yes',
				'default'      => '',
				'condition'    => [
					'input_mask_enable!' => '',
				],
			]
		);
	}

	private function add_icon_controls() {
		$this->add_control(
			'field_icon_enable',
			[
				'label'        => __( 'Enable', 'piotnetforms' ),
				'type'         => 'switch',
				'label_on'     => __( 'On', 'piotnetforms' ),
				'label_off'    => __( 'Off', 'piotnetforms' ),
				'return_value' => 'true',
				'default'      => '',
			]
		);

		$this->add_control(
			'field_icon_type',
			[
				'label'     => __( 'Icon Type', 'piotnetforms' ),
				'type'      => 'select',
				'options'   => [
					'font_awesome' => __( 'Font Awesome', 'piotnetforms' ),
					'image'        => __( 'Image', 'piotnetforms' ),
				],
				'default'   => 'font_awesome',
				'condition' => [
					'field_icon_enable!' => '',
				],
			]
		);

		$this->add_control(
			'field_icon_font_awesome',
			[
				'label'          => __( 'Icon', 'piotnetforms' ),
				'type'           => 'icon',
				'options_source' => 'fontawesome',
				'condition'      => [
					'field_icon_enable!' => '',
					'field_icon_type'    => 'font_awesome',
				],
			]
		);

		$this->add_control(
			'field_icon_image',
			[
				'label'     => __( 'Icon Image', 'piotnetforms' ),
				'type'      => 'media',
				'condition' => [
					'field_icon_enable!' => '',
					'field_icon_type'    => 'image',
				],
			]
		);

		$this->add_control(
			'field_icon_image_focus',
			[
				'label'     => __( 'Icon Image Focus', 'piotnetforms' ),
				'type'      => 'media',
				'condition' => [
					'field_icon_enable!' => '',
					'field_icon_type'    => 'image',
				],
			]
		);

		$this->add_responsive_control(
			'field_icon_width',
			[
				'label'      => __( 'Icon Width', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => '',
				],
				'selectors'  => [
					'{{WRAPPER}} .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field:not(.piotnetforms-select-wrapper)' => 'padding-left: {{SIZE}}{{UNIT}} !important;',
					'{{WRAPPER}} .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field .piotnetforms-field-textual' => 'padding-left: {{SIZE}}{{UNIT}} !important;',
					'{{WRAPPER}} .piotnetforms-field-icon' => 'width: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'field_icon_enable!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'field_icon_size',
			[
				'label'      => __( 'Icon Size', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => '',
				],
				'selectors'  => [
					'{{WRAPPER}} .piotnetforms-field-icon' => 'font-size: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'field_icon_enable!' => '',
					'field_icon_type'    => 'font_awesome',
				],
			]
		);

		$this->add_responsive_control(
			'field_icon_image_width',
			[
				'label'      => __( 'Icon Size', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default'    => [
					'unit' => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} .piotnetforms-field-icon img' => 'width: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'field_icon_enable!' => '',
					'field_icon_type'    => 'image',
				],
			]
		);

		$this->add_responsive_control(
			'field_icon_x',
			[
				'label'      => __( 'Icon Position X', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => '',
				],
				'selectors'  => [
					'{{WRAPPER}} .piotnetforms-field-icon, {{WRAPPER}} .piotnetforms-field-icon' => 'left: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'field_icon_enable!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'field_icon_x_right',
			[
				'label'      => __( 'Icon Position X from right', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => '',
				],
				'selectors'  => [
					'{{WRAPPER}} .piotnetforms-field-icon, {{WRAPPER}} .piotnetforms-field-icon' => 'right: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'field_icon_enable!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'field_icon_y',
			[
				'label'      => __( 'Icon Position Y', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [
						'min' => -100,
						'max' => 100,
					],
					'%' => [
						'min' => -100,
						'max' => 100,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => '',
				],
				'selectors'  => [
					'{{WRAPPER}} .piotnetforms-field-icon, {{WRAPPER}} .piotnetforms-field-icon' => 'bottom: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'field_icon_enable!' => '',
				],
			]
		);

		$this->add_control(
			'field_icon_color',
			[
				'label'     => __( 'Icon Color', 'piotnetforms' ),
				'type'      => 'color',
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-field-icon i' => 'color: {{VALUE}};',
				],
				'condition' => [
					'field_icon_enable!' => '',
					'field_icon_type'    => 'font_awesome',
				],
			]
		);

		$this->add_control(
			'field_icon_color_focus',
			[
				'label'     => __( 'Icon Color Focus', 'piotnetforms' ),
				'type'      => 'color',
				'selectors' => [
					'{{WRAPPER}}.piotnetforms-field-focus .piotnetforms-field-icon i' => 'color: {{VALUE}};',
				],
				'condition' => [
					'field_icon_enable!' => '',
					'field_icon_type'    => 'font_awesome',
				],
			]
		);
	}

	private function add_checkbox_style_controls() {
		$checkbox_horizontal_spacing = is_rtl() ? 'margin-left: {{SIZE}}{{UNIT}};' : 'margin-right: {{SIZE}}{{UNIT}};';

		$this->add_control(
			'piotnetforms_style_checkbox_horizontal_spacing',
			[
				'label'      => __( 'Checkbox/Radio Button Horizontal Spacing', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => '',
				],
				'selectors'  => [
					'{{WRAPPER}} input' => $checkbox_horizontal_spacing,
					'{{WRAPPER}} .pf-f-o-i' => $checkbox_horizontal_spacing,
				],
				'condition'  => [
					'piotnetforms_style_checkbox_type' => ['button','native'],
				],
			]
		);

		$this->add_responsive_control(
			'piotnetforms_style_checkbox_button_padding',
			[
				'label'      => __( 'Button Padding', 'piotnetforms' ),
				'type'       => 'dimensions',
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .piotnetforms-field-subgroup label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [
					'piotnetforms_style_checkbox_type' => ['button'],
				],
			]
		);

		$this->add_responsive_control(
			'piotnetforms_style_checkbox_button_text_align',
			[
				'type'         => 'select',
				'label'        => __( 'Button Alignment', 'piotnetforms' ),
				'label_block'  => true,
				'value'        => '',
				'options'      => [
					''       => __( 'Default', 'piotnetforms' ),
					'flex-start'   => __( 'Left', 'piotnetforms' ),
					'center' => __( 'Center', 'piotnetforms' ),
					'flex-end'  => __( 'Right', 'piotnetforms' ),
				],
				'selectors'    => [
					'{{WRAPPER}} .piotnetforms-field-subgroup--button label' => 'justify-content: {{VALUE}}',
				],
				'condition'  => [
					'piotnetforms_style_checkbox_type' => ['button'],
				],
			]
		);

		$this->add_control(
			'piotnetforms_style_checkbox_button_icon_on_top',
			[
				'label'   => __( 'Button Icon On Top', 'piotnetforms' ),
				'type'    => 'switch',
				'return_value' => 'yes',
				'default' => '',
				'condition'  => [
					'piotnetforms_style_checkbox_type' => ['button'],
				],
			]
		);

		$this->add_control(
			'piotnetforms_style_checkbox_button_icon_on_top_spacing',
			[
				'label'      => __( 'Icon On Top Spacing', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => '',
				],
				'selectors'  => [
					'{{WRAPPER}} .pf-f-o-i' => 'margin-bottom: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'piotnetforms_style_checkbox_type' => ['button'],
					'piotnetforms_style_checkbox_button_icon_on_top' => 'yes',
				],
			]
		);

		$this->add_control(
			'piotnetforms_style_checkbox_button_icon_position',
			[
				'label'   => __( 'Button Icon Position', 'piotnetforms' ),
				'type'    => 'select',
				'options' => [
					'relative' => __( 'Relative', 'piotnetforms' ),
					'absolute' => __( 'Absolute', 'piotnetforms' ),
				],
				'selectors'    => [
					'{{WRAPPER}} .pf-f-o-i' => 'position: {{VALUE}}',
				],
				'condition'  => [
					'piotnetforms_style_checkbox_type' => ['button'],
				],
			]
		);

		$this->add_responsive_control(
			'piotnetforms_style_checkbox_button_icon_position_absolute_top',
			[
				'label'   => __( 'Top (auto, 5px, -5px)', 'piotnetforms' ),
				'label_block' => true,
				'type'    => 'text',
				'default' => 'auto',
				'selectors'    => [
					'{{WRAPPER}} .pf-f-o-i' => 'top: {{VALUE}}',
				],
				'condition'  => [
					'piotnetforms_style_checkbox_type' => ['button'],
					'piotnetforms_style_checkbox_button_icon_position' => 'absolute',
				],
			]
		);

		$this->add_responsive_control(
			'piotnetforms_style_checkbox_button_icon_position_absolute_right',
			[
				'label'   => __( 'Right (auto, 5px, -5px)', 'piotnetforms' ),
				'label_block' => true,
				'type'    => 'text',
				'default' => 'auto',
				'selectors'    => [
					'{{WRAPPER}} .pf-f-o-i' => 'right: {{VALUE}}',
				],
				'condition'  => [
					'piotnetforms_style_checkbox_type' => ['button'],
					'piotnetforms_style_checkbox_button_icon_position' => 'absolute',
				],
			]
		);

		$this->add_responsive_control(
			'piotnetforms_style_checkbox_button_icon_position_absolute_bottom',
			[
				'label'   => __( 'Bottom (auto, 5px, -5px)', 'piotnetforms' ),
				'label_block' => true,
				'type'    => 'text',
				'default' => 'auto',
				'selectors'    => [
					'{{WRAPPER}} .pf-f-o-i' => 'bottom: {{VALUE}}',
				],
				'condition'  => [
					'piotnetforms_style_checkbox_type' => ['button'],
					'piotnetforms_style_checkbox_button_icon_position' => 'absolute',
				],
			]
		);

		$this->add_responsive_control(
			'piotnetforms_style_checkbox_button_icon_position_absolute_left',
			[
				'label'   => __( 'Left (auto, 5px, -5px)', 'piotnetforms' ),
				'label_block' => true,
				'type'    => 'text',
				'default' => 'auto',
				'selectors'    => [
					'{{WRAPPER}} .pf-f-o-i' => 'left: {{VALUE}}',
				],
				'condition'  => [
					'piotnetforms_style_checkbox_type' => ['button'],
					'piotnetforms_style_checkbox_button_icon_position' => 'absolute',
				],
			]
		);

		$this->add_control(
			'style_check_heading_tab',
			[
				'type' => 'heading-tab',
				'tabs' => [
					[
						'name'   => 'style_uncheck_tab',
						'title'  => __( 'UNCHECK', 'piotnetforms' ),
						'active' => true,
					],
					[
						'name'  => 'style_checked_tab',
						'title' => __( 'CHECKED', 'piotnetforms' ),
					],
				],
				'condition'  => [
					'field_type' => ['checkbox', 'radio'],
				],
			]
		);

		$style_uncheck_controls = $this->checkbox_button_style_controls(
			'style_uncheck',
			[
				'selectors' => '{{WRAPPER}} .piotnetforms-field-subgroup label',
			]
		);
		$this->add_control(
			'style_uncheck_tab',
			[
				'type'           => 'content-tab',
				'label'          => __( 'Uncheck', 'piotnetforms' ),
				'value'          => '',
				'active'         => true,
				'controls'       => $style_uncheck_controls,
				'controls_query' => '.piotnet-start-controls-tab',
				'condition'  => [
					'field_type' => ['checkbox', 'radio'],
				],
			]
		);

		$style_checked_controls = $this->checkbox_button_style_controls(
			'style_checked',
			[
				'selectors' => '{{WRAPPER}} .piotnetforms-field-subgroup input:checked ~ label',
			]
		);
		$this->add_control(
			'style_checked_tab',
			[
				'type'           => 'content-tab',
				'label'          => __( 'Checked', 'piotnetforms' ),
				'value'          => '',
				'controls'       => $style_checked_controls,
				'controls_query' => '.piotnet-start-controls-tab',
				'condition'  => [
					'field_type' => ['checkbox', 'radio'],
				],
			]
		);

		$this->add_control(
			'piotnetforms_style_checkbox_item_width_type',
			[
				'label'   => __( 'Item Width Type', 'piotnetforms' ),
				'type'    => 'select',
				'separator'  => 'before',
				'options' => [
					'auto' => __( 'Auto', 'piotnetforms' ),
					'custom' => __( 'Custom', 'piotnetforms' ),
				],
			]
		);

		$this->add_responsive_control(
			'piotnetforms_style_checkbox_item_width',
			[
				'label'   => __( 'Item Width', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
					'%' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => '',
				],
				'selectors'  => [
					'{{WRAPPER}} .piotnetforms-field-option' => 'width: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'piotnetforms_style_checkbox_item_width_type' => 'custom',
				],
			]
		);

		$this->add_responsive_control(
			'piotnetforms_style_checkbox_item_vertical_spacing',
			[
				'label'      => __( 'Item Vertical Spacing', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => '',
				],
				'selectors'  => [
					'{{WRAPPER}} .piotnetforms-field-option' => 'margin-bottom: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .piotnetforms-field-option:last-child' => 'margin-bottom: 0;',
				],
				'condition'  => [
					'piotnetforms_style_checkbox_type' => ['native', 'button', 'square'],
				],
			]
		);

		$this->add_responsive_control(
			'piotnetforms_style_checkbox_square_item_horizontal_spacing',
			[
				'label'      => __( 'Item Horizontal Spacing', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => '',
				],
				'selectors'  => [
					'{{WRAPPER}} .piotnetforms-field-option' => 'margin-right: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .piotnetforms-field-option:last-child' => 'margin-right: 0;',
				],
				'condition'  => [
					'piotnetforms_style_checkbox_type' => ['square'],
				],
			]
		);

		$this->add_responsive_control(
			'piotnetforms_style_checkbox_item_horizontal_spacing',
			[
				'label'      => __( 'Item Horizontal Spacing', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => '',
				],
				'selectors'  => [
					'{{WRAPPER}} .piotnetforms-field-option' => 'padding: 0 {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .piotnetforms-field-subgroup' => 'margin: 0 -{{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'piotnetforms_style_checkbox_type' => ['native', 'button'],
				],
			]
		);

		$this->add_control(
			'piotnetforms_style_checkbox_replacement',
			[
				'label'   => __( 'Checkbox / Radio Button Replacement', 'piotnetforms' ),
				'type'    => 'switch',
				'return_value' => 'yes',
				'default' => '',
				'condition'  => [
					'piotnetforms_style_checkbox_type' => ['native'],
				],
			]
		);

		$this->add_control(
			'piotnetforms_style_checkbox_replacement_icon_type',
			[
				'label' => __( 'Replacement Type', 'piotnetforms' ),
				'type'  => 'select',
				'options'   => [
					'font_awesome' => __( 'Font Awesome', 'piotnetforms' ),
					'image'        => __( 'Image', 'piotnetforms' ),
				],
				'default'   => 'font_awesome',
				'condition'  => [
					'piotnetforms_style_checkbox_replacement' => 'yes',
				],
			]
		);
		$this->add_control(
			'piotnetforms_style_checkbox_replacement_icon_font_awesome',
			[
				'label' => __( 'Icon Uncheck', 'piotnetforms' ),
				'type'  => 'icon',
				'options_source' => 'fontawesome',
				'condition'      => [
					'piotnetforms_style_checkbox_replacement' => 'yes',
					'piotnetforms_style_checkbox_replacement_icon_type' => 'font_awesome',
				],
			]
		);
		$this->add_control(
			'piotnetforms_style_checkbox_replacement_icon_font_awesome_checked',
			[
				'label' => __( 'Icon Checked', 'piotnetforms' ),
				'type'  => 'icon',
				'options_source' => 'fontawesome',
				'condition'      => [
					'piotnetforms_style_checkbox_replacement' => 'yes',
					'piotnetforms_style_checkbox_replacement_icon_type' => 'font_awesome',
				],
			]
		);
		$this->add_control(
			'piotnetforms_style_checkbox_replacement_icon_image',
			[
				'label' => __( 'Icon Image Uncheck', 'piotnetforms' ),
				'type'  => 'media',
				'condition'      => [
					'piotnetforms_style_checkbox_replacement' => 'yes',
					'piotnetforms_style_checkbox_replacement_icon_type' => 'image',
				],
			]
		);
		$this->add_control(
			'piotnetforms_style_checkbox_replacement_icon_image_checked',
			[
				'label' => __( 'Icon Image Checked', 'piotnetforms' ),
				'type'  => 'media',
				'condition'      => [
					'piotnetforms_style_checkbox_replacement' => 'yes',
					'piotnetforms_style_checkbox_replacement_icon_type' => 'image',
				],
			]
		);

		$this->add_responsive_control(
			'piotnetforms_style_checkbox_icon_width',
			[
				'label'   => __( 'Icon Width', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => '',
				],
				'selectors'  => [
					'{{WRAPPER}} .pf-f-o-i' => 'width: {{SIZE}}{{UNIT}}; font-size: {{SIZE}}{{UNIT}}',
				],
				'condition'  => [
					'piotnetforms_style_checkbox_type' => ['button', 'native'],
				],
			]
		);

		$this->add_responsive_control(
			'piotnetforms_style_checkbox_square_size',
			[
				'label'      => __( 'Size', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 18,
				],
				'selectors'  => [
					'{{WRAPPER}} span.piotnetforms-field-option' => 'position: relative;',
					'{{WRAPPER}} span.piotnetforms-field-option input' => 'position: absolute; top: 50%; left: 0px; transform: translateY(-50%); opacity: 0; z-index: 9;',
					'{{WRAPPER}} span.piotnetforms-field-option label' => 'display: block !important; cursor: pointer; margin: 0 auto; padding: 0px 0px 0px 30px;',
					'{{WRAPPER}} span.piotnetforms-field-option label:before' => 'content: ""; display: block; width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; position: absolute; top: 50%; left: 0px; transform: translateY(-50%); background: #fff; border-style: solid; border-width: 1px;',
				],
				'condition'  => [
					'piotnetforms_style_checkbox_type' => 'square',
				],
			]
		);

		$this->add_control(
			'piotnetforms_style_checkbox_square_border_width',
			[
				'label'      => __( 'Border Width', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 10,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 1,
				],
				'selectors'  => [
					'{{WRAPPER}} span.piotnetforms-field-option label:before' => 'border-width: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'piotnetforms_style_checkbox_type' => 'square',
				],
			]
		);

		$this->add_control(
			'piotnetforms_style_checkbox_square_border_color',
			[
				'label'     => __( 'Border Color', 'piotnetforms' ),
				'type'      => 'color',
				'default'   => '#000000',
				'selectors' => [
					'{{WRAPPER}} span.piotnetforms-field-option label:before' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'piotnetforms_style_checkbox_type' => 'square',
				],
			]
		);

		$this->add_control(
			'piotnetforms_style_checkbox_square_background_color',
			[
				'label'     => __( 'Checked Background Color', 'piotnetforms' ),
				'type'      => 'color',
				'default'   => '#000000',
				'selectors' => [
					'{{WRAPPER}} span.piotnetforms-field-option input:checked ~ label:before' => 'background: {{VALUE}};',
				],
				'condition' => [
					'piotnetforms_style_checkbox_type' => 'square',
				],
			]
		);

		$this->add_control(
			'piotnetforms_style_checkbox_square_border_radius',
			[
				'label'      => __( 'Border Radius', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => 'px',
				],
				'selectors'  => [
					'{{WRAPPER}} span.piotnetforms-field-option label:before' => 'border-radius: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'piotnetforms_style_checkbox_type' => 'square',
				],
			]
		);

		$this->add_responsive_control(
			'piotnetforms_style_checkbox_square_spacing',
			[
				'label'      => __( 'Spacing', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 30,
				],
				'selectors'  => [
					'{{WRAPPER}} span.piotnetforms-field-option label' => 'padding-left: {{SIZE}}{{UNIT}};',
				],
				'condition'  => [
					'piotnetforms_style_checkbox_type' => 'square',
				],
			]
		);
	}

	private function add_number_style_controls() {
		$this->add_responsive_control(
			'piotnetforms_style_spiner_width',
			[
				'label'      => __( 'Width', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 32,
				],
				'selectors'  => [
					'{{WRAPPER}} [data-piotnetforms-spiner] button' => 'width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'piotnetforms_style_spiner_height',
			[
				'label'      => __( 'Height', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 32,
				],
				'selectors'  => [
					'{{WRAPPER}} [data-piotnetforms-spiner] button' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'piotnetforms_style_spiner_input_width',
			[
				'label'      => __( 'Input Width', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [ 'px' ],
				'range'      => [
					'px' => [
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 20,
				],
				'selectors'  => [
					'{{WRAPPER}} [data-piotnetforms-spiner] .nice-number input' => 'width: {{SIZE}}{{UNIT}}!important;',
				],
			]
		);

		$this->add_responsive_control(
			'piotnetforms_style_spiner_border_radius',
			[
				'label'      => __( 'Border Radius', 'piotnetforms' ),
				'type'       => 'dimensions',
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} [data-piotnetforms-spiner] button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_text_typography_controls(
			'piotnetforms_style_spiner_typography',
			[
				'selector' => '{{WRAPPER}} [data-piotnetforms-spiner] button',
			]
		);

		$this->add_control(
			'piotnetforms_style_spiner_tabs',
			[
				'type' => 'heading-tab',
				'tabs' => [
					[
						'name'   => 'piotnetforms_style_spiner_normal',
						'title'  => __( 'NORMAL', 'piotnetforms' ),
						'active' => true,
					],
					[
						'name'  => 'piotnetforms_style_spiner_hover',
						'title' => __( 'HOVER', 'piotnetforms' ),
					],
				],
			]
		);

		$normal_controls = $this->add_style_spiner_controls(
			'normal',
			[
				'selectors' => '{{WRAPPER}} [data-piotnetforms-spiner] button',
			]
		);
		$this->add_control(
			'piotnetforms_style_spiner_normal',
			[
				'type'           => 'content-tab',
				'label'          => __( 'Normal', 'piotnetforms' ),
				'value'          => '',
				'active'         => true,
				'controls'       => $normal_controls,
				'controls_query' => '.piotnet-start-controls-tab',
			]
		);

		$hover_controls = $this->add_style_spiner_controls(
			'hover',
			[
				'selectors' => '{{WRAPPER}} [data-piotnetforms-spiner] button:hover',
			]
		);
		$this->add_control(
			'piotnetforms_style_spiner_hover',
			[
				'type'           => 'content-tab',
				'label'          => __( 'Hover', 'piotnetforms' ),
				'value'          => '',
				'controls'       => $hover_controls,
				'controls_query' => '.piotnet-start-controls-tab',
			]
		);
	}

	private function add_style_spiner_controls( string $name, $args = [] ) {
		$wrapper           = isset( $args['selectors'] ) ? $args['selectors'] : '{{WRAPPER}}';
		$previous_controls = $this->new_group_controls();

		$this->add_control(
			'piotnetforms_style_spiner_color_' . $name,
			[
				'label'     => __( 'Color', 'piotnetforms' ),
				'type'      => 'color',
				'selectors' => [
					$wrapper => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'piotnetforms_style_spiner_color_bg_' . $name,
			[
				'label'     => __( 'Background Color', 'piotnetforms' ),
				'type'      => 'color',
				'selectors' => [
					$wrapper => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'piotnetforms_style_spiner_border_' . $name,
			[
				'label'     => __( 'Border Type', 'piotnetforms' ),
				'type'      => 'select',
				'options'   => [
					''       => __( 'None', 'piotnetforms' ),
					'solid'  => _x( 'Solid', 'Border Control', 'piotnetforms' ),
					'double' => _x( 'Double', 'Border Control', 'piotnetforms' ),
					'dotted' => _x( 'Dotted', 'Border Control', 'piotnetforms' ),
					'dashed' => _x( 'Dashed', 'Border Control', 'piotnetforms' ),
					'groove' => _x( 'Groove', 'Border Control', 'piotnetforms' ),
				],
				'selectors' => [
					$wrapper => 'border-style: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'piotnetforms_style_spiner_border_width_' . $name,
			[
				'label'      => __( 'Border Width', 'piotnetforms' ),
				'type'       => 'dimensions',
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					$wrapper => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'piotnetforms_style_spiner_border_color_' . $name,
			[
				'label'     => __( 'Border Color', 'piotnetforms' ),
				'type'      => 'color',
				'default'   => '',
				'selectors' => [
					$wrapper => 'border-color: {{VALUE}};',
				],
			]
		);

		return $this->get_group_controls( $previous_controls );
	}

	private function add_image_select_style_controls() {
		$this->add_text_typography_controls(
			'piotnetforms_image_select_field_typography',
			[
				'selectors' => '{{WRAPPER}} .image_picker_selector .thumbnail p',
			]
		);

		$this->add_responsive_control(
			'piotnetforms_image_select_field_image_alignment',
			[
				'type'        => 'select',
				'label'       => __( 'Image Align', 'piotnetforms' ),
				'label_block' => true,
				'value'       => '',
				'options'     => [
					''       => __( 'Default', 'piotnetforms' ),
					'left'   => __( 'Left', 'piotnetforms' ),
					'center' => __( 'Center', 'piotnetforms' ),
					'right'  => __( 'Right', 'piotnetforms' ),
				],
				'selectors'   => [
					'{{WRAPPER}} .image_picker_selector .thumbnail' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'piotnetforms_image_select_field_text_align',
			[
				'type'        => 'select',
				'label'       => __( 'Text Align', 'piotnetforms' ),
				'label_block' => true,
				'value'       => '',
				'options'     => [
					''       => __( 'Default', 'piotnetforms' ),
					'left'   => __( 'Left', 'piotnetforms' ),
					'center' => __( 'Center', 'piotnetforms' ),
					'right'  => __( 'Right', 'piotnetforms' ),
				],
				'default'   => 'left',
				'selectors'   => [
					'{{WRAPPER}} .image_picker_selector .thumbnail p' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'piotnetforms_image_select_field_item_width',
			[
				'label'     => __( 'Item Width (%)', 'piotnetforms' ),
				'type'      => 'number',
				'default'   => 25,
				'min'       => 1,
				'max'       => 100,
				'selectors' => [
					'{{WRAPPER}} ul.thumbnails.image_picker_selector li' => 'width: {{VALUE}}% !important;',
				],
			]
		);

		$columns_margin  = is_rtl() ? '-{{SIZE}}{{UNIT}} -{{SIZE}}{{UNIT}} -{{SIZE}}{{UNIT}} -{{SIZE}}{{UNIT}};' : '-{{SIZE}}{{UNIT}} -{{SIZE}}{{UNIT}} -{{SIZE}}{{UNIT}} -{{SIZE}}{{UNIT}};';
		$columns_padding = is_rtl() ? '{{SIZE}}{{UNIT}} !important;' : '{{SIZE}}{{UNIT}} !important;';

		$this->add_responsive_control(
			'piotnetforms_image_select_field_image_align',
			[
				'type'        => 'select',
				'label'       => __( 'Item Align', 'piotnetforms' ),
				'label_block' => true,
				'value'       => '',
				'options'     => [
					''       => __( 'Default', 'piotnetforms' ),
					'flex-start'   => __( 'Left', 'piotnetforms' ),
					'center' => __( 'Center', 'piotnetforms' ),
					'flex-end'  => __( 'Right', 'piotnetforms' ),
				],
				'selectors'   => [
					'{{WRAPPER}} .piotnetforms-image-select-field .image_picker_selector' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'piotnetforms_image_select_field_item_spacing',
			[
				'label'      => __( 'Item Spacing', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [ 'px', '%' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default'    => [
					'unit' => 'px',
					'size' => 10,
				],
				'selectors'  => [
					'{{WRAPPER}} ul.thumbnails.image_picker_selector li' => 'padding:' . $columns_padding,
					'{{WRAPPER}} ul.thumbnails.image_picker_selector' => 'margin: ' . $columns_margin,
				],
			]
		);

		$this->add_responsive_control(
			'piotnetforms_image_select_field_item_border_radius',
			[
				'label'      => __( 'Item Border Radius', 'piotnetforms' ),
				'type'       => 'dimensions',
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} ul.thumbnails.image_picker_selector .thumbnail' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'piotnetforms_image_select_field_image_border_radius',
			[
				'label'      => __( 'Image Border Radius', 'piotnetforms' ),
				'type'       => 'dimensions',
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					'{{WRAPPER}} ul.thumbnails.image_picker_selector .image_picker_image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'piotnetforms_image_select_field_image_padding',
			[
				'label'      => __( 'Input Padding', 'piotnetforms' ),
				'type'       => 'dimensions',
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .image_picker_image' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'piotnetforms_image_select_field_label_padding',
			[
				'label'      => __( 'Input Padding', 'piotnetforms' ),
				'type'       => 'dimensions',
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} ul.thumbnails p' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'piotnetforms_image_select_field_normal_active',
			[
				'type' => 'heading-tab',
				'tabs' => [
					[
						'name'   => 'piotnetforms_image_select_field_normal',
						'title'  => __( 'NORMAL', 'piotnetforms' ),
						'active' => true,
					],
					[
						'name'  => 'piotnetforms_image_select_field_active',
						'title' => __( 'ACTIVE', 'piotnetforms' ),
					],
				],
			]
		);

		$normal_controls = $this->add_image_select_thumbnail_style(
			'normal',
			[
				'selectors' => '{{WRAPPER}} ul.thumbnails.image_picker_selector .thumbnail',
			]
		);
		$this->add_control(
			'piotnetforms_image_select_field_normal',
			[
				'type'           => 'content-tab',
				'label'          => __( 'Normal', 'piotnetforms' ),
				'value'          => '',
				'active'         => true,
				'controls'       => $normal_controls,
				'controls_query' => '.piotnet-start-controls-tab',
			]
		);

		$active_controls = $this->add_image_select_thumbnail_style(
			'active',
			[
				'selectors' => '{{WRAPPER}} ul.thumbnails.image_picker_selector .thumbnail.selected',
			]
		);
		$this->add_control(
			'piotnetforms_image_select_field_active',
			[
				'type'           => 'content-tab',
				'label'          => __( 'Active', 'piotnetforms' ),
				'value'          => '',
				'controls'       => $active_controls,
				'controls_query' => '.piotnet-start-controls-tab',
			]
		);
	}

	private function add_image_select_thumbnail_style( string $name, $args = [] ) {
		$wrapper           = isset( $args['selectors'] ) ? $args['selectors'] : '{{WRAPPER}}';
		$previous_controls = $this->new_group_controls();

		$this->add_control(
			'piotnetforms_image_select_field_border_' . $name,
			[
				'label'     => __( 'Item Border Type', 'piotnetforms' ),
				'type'      => 'select',
				'options'   => [
					''       => __( 'None', 'piotnetforms' ),
					'solid'  => _x( 'Solid', 'Border Control', 'piotnetforms' ),
					'double' => _x( 'Double', 'Border Control', 'piotnetforms' ),
					'dotted' => _x( 'Dotted', 'Border Control', 'piotnetforms' ),
					'dashed' => _x( 'Dashed', 'Border Control', 'piotnetforms' ),
					'groove' => _x( 'Groove', 'Border Control', 'piotnetforms' ),
				],
				'selectors' => [
					$wrapper => 'border-style: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'piotnetforms_image_select_field_border_width_' . $name,
			[
				'label'     => __( 'Item Border Width', 'piotnetforms' ),
				'type'      => 'dimensions',
				'selectors' => [
					$wrapper => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'piotnetforms_image_select_field_border_normal!' => '',
				],
			]
		);

		$this->add_control(
			'piotnetforms_image_select_field_border_color_' . $name,
			[
				'label'     => __( 'Item Border Color', 'piotnetforms' ),
				'type'      => 'color',
				'default'   => '',
				'selectors' => [
					$wrapper => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'piotnetforms_image_select_field_border_normal!' => '',
				],
			]
		);

		$this->add_control(
			'piotnetforms_image_select_field_background_color_' . $name,
			[
				'label'     => __( 'Background Color', 'piotnetforms' ),
				'type'      => 'color',
				'default'   => '',
				'selectors' => [
					$wrapper => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'piotnetforms_image_select_field_text_color_' . $name,
			[
				'label'     => __( 'Text Color', 'piotnetforms' ),
				'type'      => 'color',
				'default'   => '',
				'selectors' => [
					$wrapper . ' p' => 'color: {{VALUE}};',
				],
			]
		);

		return $this->get_group_controls( $previous_controls );
	}

	private function add_conditional_logic_controls() {
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
				'default'     => 400,
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

		$this->new_group_controls();
		$this->add_control(
			'piotnetforms_conditional_logic_form_action',
			[
				'label'       => __( 'Action', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select2',
				'multiple'    => true,
				'options'     => [
					'show'      => 'Show this field',
					'set_value' => 'Set Value',
				],
				'default'     => [
					'show',
				],
			]
		);

		$this->add_control(
			'piotnetforms_conditional_logic_form_set_value',
			[
				'label'       => __( 'Value', 'piotnetforms' ),
				'type'        => 'text',
				'description' => __( 'E.g 10, John, unchecked, checked', 'piotnetforms' ),
				'condition'   => [
					'piotnetforms_conditional_logic_form_action' => 'set_value',
				],
			]
		);

		$this->add_control(
			'piotnetforms_conditional_logic_form_set_value_for',
			[
				'label'       => __( 'Set Value For', 'piotnetforms' ),
				'label_block' => true,
				'type'        => 'select',
				'get_fields'  => true,
				'get_fields_include_itself'  => true,
				'placeholder' => __( 'Field Shortcode', 'piotnetforms' ),
				'condition'   => [
					'piotnetforms_conditional_logic_form_action' => 'set_value',
				],
			]
		);

		$this->add_control(
			'piotnetforms_conditional_logic_form_if',
			[
				'label'       => __( 'If', 'piotnetforms' ),
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
				'default'     => 'string',
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
				'default'     => 'or',
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
				'default' => '{{{ piotnetforms_conditional_logic_form_if }}} {{{ piotnetforms_conditional_logic_form_comparison_operators }}} {{{ piotnetforms_conditional_logic_form_value }}}',
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
				'label_block'    => true,
				'add_label'      => __( 'Add Item', 'piotnetforms' ),
				'controls'       => $repeater_list,
				'controls_query' => '.piotnet-control-repeater-list',
				'condition' => [
					'piotnetforms_conditional_logic_form_enable!' => '',
				],
			]
		);
	}

	private function add_calculated_fields_style_controls() {
		$this->add_control(
			'calculated_fields_color',
			[
				'label'     => __( 'Text Color', 'piotnetforms' ),
				'type'      => 'color',
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-calculated-fields-form' => 'color: {{VALUE}};',
				],
				'condition' => [
					'field_type' => 'calculated_fields',
				],
			]
		);

		$this->add_text_typography_controls(
			'calculated_fields_typography',
			[
				'selectors' => '{{WRAPPER}} .piotnetforms-calculated-fields-form',
			]
		);
	}

	private function add_label_style_controls() {
		$this->add_control(
			'label_spacing',
			[
				'label'     => __( 'Spacing', 'piotnetforms' ),
				'type'      => 'slider',
				'default'   => [
					'size' => '',
					'unit' => 'px',
				],
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 60,
					],
				],
				'selectors' => [
					'body.rtl {{WRAPPER}} .piotnetforms-labels-inline .piotnetforms-field-group > label' => 'padding-left: {{SIZE}}{{UNIT}};',
					// for the label position = inline option
					'body:not(.rtl) {{WRAPPER}} .piotnetforms-labels-inline .piotnetforms-field-group > label' => 'padding-right: {{SIZE}}{{UNIT}};',
					// for the label position = inline option
					'body {{WRAPPER}} .piotnetforms-field-group > label' => 'padding-bottom: {{SIZE}}{{UNIT}};',
					// for the label position = above option
				],
			]
		);

		$this->add_responsive_control(
			'label_text_align',
			[
				'type'        => 'select',
				'label'       => __( 'Text Align', 'piotnetforms' ),
				'label_block' => true,
				'value'       => '',
				'options'     => [
					''       => __( 'Default', 'piotnetforms' ),
					'left'   => __( 'Left', 'piotnetforms' ),
					'center' => __( 'Center', 'piotnetforms' ),
					'right'  => __( 'Right', 'piotnetforms' ),
				],
				'selectors'   => [
					'{{WRAPPER}} .piotnetforms-field-label' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'label_animation',
			[
				'label'       => __( 'Label Animation', 'piotnetforms' ),
				'type'        => 'switch',
				'label_on'    => __( 'Yes', 'piotnetforms' ),
				'label_off'   => __( 'No', 'piotnetforms' ),
				'return_value' => 'yes',
				'default'     => '',
			]
		);

		$this->add_responsive_control(
			'label_animation_focus_left',
			[
				'label'     => __( 'Label Animation Left', 'piotnetforms' ),
				'type'      => 'slider',
				'default'   => [
					'size' => '',
					'unit' => 'px',
				],
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'conditions'   => [
					[
						'name' => 'label_animation',
						'operator' => '==',
						'value' => 'yes'
					]
				],
				'selectors' => [
					'{{WRAPPER}}.piotnetforms-label-animation label' => 'left: {{SIZE}}{{UNIT}};',
				]
			]
		);

		$this->add_responsive_control(
			'label_animation_focus_spacing',
			[
				'label'     => __( 'Label Animation Focus Spacing', 'piotnetforms' ),
				'type'      => 'slider',
				'default'   => [
					'size' => 0,
					'unit' => 'px',
				],
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'conditions'   => [
					[
						'name' => 'label_animation',
						'operator' => '==',
						'value' => 'yes'
					]
				],
				'selectors' => [
					'{{WRAPPER}}.piotnetforms-label-animation.piotnetforms-label-animated label' => 'transform: translateX(0);',
					'{{WRAPPER}}.piotnetforms-label-animation.piotnetforms-label-animated label' => 'top:  {{SIZE}}{{UNIT}};',
				]
			]
		);

		$this->add_control(
			'',
			[
				'type' => 'heading-tab',
				'tabs' => [
					[
						'name'   => 'label_normal_tab',
						'title'  => __( 'NORMAL', 'piotnetforms' ),
						'active' => true,
					],
					[
						'name'  => 'label_focus_tab',
						'title' => __( 'FOCUS', 'piotnetforms' ),
					],
				],
			]
		);

		$normal_controls = $this->label_style_tab_controls(
			'',
			[
				'wrapper' => '{{WRAPPER}}',
			]
		);
		$this->add_control(
			'label_normal_tab',
			[
				'type'           => 'content-tab',
				'label'          => __( 'Normal', 'piotnetforms' ),
				'value'          => '',
				'active'         => true,
				'controls'       => $normal_controls,
				'controls_query' => '.piotnet-start-controls-tab',
			]
		);

		$focus_controls = $this->label_style_tab_controls(
			'focus',
			[
				'wrapper' => '{{WRAPPER}}.piotnetforms-field-focus',
			]
		);
		$this->add_control(
			'label_focus_tab',
			[
				'type'           => 'content-tab',
				'label'          => __( 'Focus', 'piotnetforms' ),
				'value'          => '',
				'controls'       => $focus_controls,
				'controls_query' => '.piotnet-start-controls-tab',
			]
		);
	}
	private function add_modern_upload_field_style_controls() {
		$this->add_responsive_control(
			'modern_upload_field_text_padding',
			[
				'label'      => __( 'Padding', 'piotnetforms' ),
				'type'       => 'dimensions',
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .piotnetforms-upload-field-modern-text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'modern_upload_field_text_align',
			[
				'type'        => 'select',
				'label'       => __( 'Text Align', 'piotnetforms' ),
				'label_block' => true,
				'value'       => '',
				'options'     => [
					''       => __( 'Default', 'piotnetforms' ),
					'left'   => __( 'Left', 'piotnetforms' ),
					'center' => __( 'Center', 'piotnetforms' ),
					'right'  => __( 'Right', 'piotnetforms' ),
				],
				'selectors'   => [
					'{{WRAPPER}} .piotnetforms-upload-field-modern-text' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'',
			[
				'type' => 'heading-tab',
				'tabs' => [
					[
						'name'   => 'modern_upload_field_normal_tab',
						'title'  => __( 'NORMAL', 'piotnetforms' ),
						'active' => true,
					],
					[
						'name'  => 'modern_upload_field_hover_tab',
						'title' => __( 'HOVER', 'piotnetforms' ),
					],
				],
			]
		);

		$normal_controls = $this->modern_upload_field_style_tab_controls(
			'',
			[
				'wrapper' => '{{WRAPPER}} .piotnetforms-upload-field-modern-text',
			]
		);
		$this->add_control(
			'modern_upload_field_normal_tab',
			[
				'type'           => 'content-tab',
				'label'          => __( 'Normal', 'piotnetforms' ),
				'value'          => '',
				'active'         => true,
				'controls'       => $normal_controls,
				'controls_query' => '.piotnet-start-controls-tab',
			]
		);

		$hover_controls = $this->modern_upload_field_style_tab_controls(
			'hover',
			[
				'wrapper' => '{{WRAPPER}} .piotnetforms-upload-field-modern-text:hover',
			]
		);
		$this->add_control(
			'modern_upload_field_hover_tab',
			[
				'type'           => 'content-tab',
				'label'          => __( 'Hover', 'piotnetforms' ),
				'value'          => '',
				'controls'       => $hover_controls,
				'controls_query' => '.piotnet-start-controls-tab',
			]
		);
	}

	private function modern_upload_field_style_tab_controls( string $name, $args = [] ) {
		$wrapper = isset( $args['wrapper'] ) ? $args['wrapper'] : '{{WRAPPER}}';
		$name = !empty( $name ) ? '_' . $name : '';
		$previous_controls = $this->new_group_controls();

		$this->add_control(
			'modern_upload_field_color' . $name,
			[
				'label'     => __( 'Texts Color', 'piotnetforms' ),
				'type'      => 'color',
				'selectors' => [
					$wrapper => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'modern_upload_field_background_color' . $name,
			[
				'label'     => __( 'Background Color', 'piotnetforms' ),
				'type'      => 'color',
				'selectors' => [
					$wrapper => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_text_typography_controls(
			'modern_upload_field_typography' . $name,
			[
				'selectors' => $wrapper,
			]
		);

		return $this->get_group_controls( $previous_controls );
	}

	private function label_style_tab_controls( string $name, $args = [] ) {
		$wrapper = isset( $args['wrapper'] ) ? $args['wrapper'] : '{{WRAPPER}}';
		$name = !empty( $name ) ? '_' . $name : '';
		$previous_controls = $this->new_group_controls();

		$this->add_control(
			'label_color' . $name,
			[
				'label'     => __( 'Text Color', 'piotnetforms' ),
				'type'      => 'color',
				'selectors' => [
					$wrapper . ' .piotnetforms-field-group > label, {{WRAPPER}} .piotnetforms-field-subgroup label' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'mark_required_color' . $name,
			[
				'label'     => __( 'Mark Color', 'piotnetforms' ),
				'type'      => 'color',
				'default'   => '',
				'selectors' => [
					$wrapper . ' .piotnetforms-mark-required .piotnetforms-field-label:after' => 'color: {{VALUE}};',
				],
				'condition' => [
					'mark_required' => 'true',
				],
			]
		);

		$this->add_text_typography_controls(
			'label_typography' . $name,
			[
				'selectors' => $wrapper . ' .piotnetforms-field-group > label',
			]
		);

		return $this->get_group_controls( $previous_controls );
	}

	private function field_style_controls( string $name, $args = [] ) {
		$wrapper = isset( $args['wrapper'] ) ? $args['wrapper'] : '{{WRAPPER}}';
		$name = !empty( $name ) ? '_' . $name : '';
		$previous_controls = $this->new_group_controls();
		$this->add_responsive_control(
			'field_text_align' . $name,
			[
				'type'        => 'select',
				'label'       => __( 'Text Align', 'piotnetforms' ),
				'label_block' => true,
				'value'       => '',
				'options'     => [
					''       => __( 'Default', 'piotnetforms' ),
					'left'   => __( 'Left', 'piotnetforms' ),
					'center' => __( 'Center', 'piotnetforms' ),
					'right'  => __( 'Right', 'piotnetforms' ),
				],
				'selectors'   => [
					$wrapper . ' .piotnetforms-field-group .piotnetforms-field' => 'text-align: {{VALUE}};',
					$wrapper . ' .piotnetforms-field-group .piotnetforms-field-subgroup' => 'justify-content: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'field_text_color' . $name,
			[
				'label'     => __( 'Text Color', 'piotnetforms' ),
				'type'      => 'color',
				'selectors' => [
					$wrapper . ' .piotnetforms-field-group .piotnetforms-field .piotnetforms-field-textual option' => 'color: {{VALUE}};',
					$wrapper . ' .piotnetforms-field-group .piotnetforms-field .selectize-control .selectize-dropdown .selectize-dropdown-content' => 'color: {{VALUE}};',
					$wrapper . ' .piotnetforms-field-group .piotnetforms-field .selectize-control .selectize-input' => 'color: {{VALUE}};',
					$wrapper . ' .piotnetforms-field-group .piotnetforms-field' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_text_typography_controls(
			'field_typography' . $name,
			[
				'selectors' => $wrapper . ' .piotnetforms-field-group .piotnetforms-field, ' .$wrapper . ' .piotnetforms-field-group .piotnetforms-field .piotnetforms-field-textual, ' . $wrapper . ' .piotnetforms-field-subgroup label, ' . $wrapper . ' .piotnetforms-field-group .piotnetforms-select-wrapper select::placeholder, ' . $wrapper . ' .piotnetforms-field-group .piotnetforms-field .selectize-control .selectize-dropdown .selectize-dropdown-content, ' . $wrapper . ' .piotnetforms-field-group .piotnetforms-field .selectize-control .selectize-input input::placeholder, ' . $wrapper . ' .piotnetforms-field-group .piotnetforms-field .selectize-control .selectize-input input, ' . $wrapper . ' .piotnetforms-field-group .piotnetforms-field .selectize-control .selectize-input .item',
			]
		);

		$this->add_control(
			'field_background_color' . $name,
			[
				'label'     => __( 'Background Color', 'piotnetforms' ),
				'label_block' => true,
				'type'      => 'color',
				'selectors' => [
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field:not(.piotnetforms-select-wrapper)' => 'background: {{VALUE}}!important;',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field .piotnetforms-field-textual .selectize-input' => 'background: {{VALUE}}!important;',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field .selectize-control .selectize-dropdown .selectize-dropdown-content' => 'background: {{VALUE}}!important;',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field .selectize-control .selectize-dropdown' => 'background: {{VALUE}}!important;',
					$wrapper . ' .piotnetforms-field-group .piotnetforms-select-wrapper select' => 'background: {{VALUE}}!important;',
				],
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'input_max_width' . $name,
			[
				'label'      => __( 'Input Max Width', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [ 'px', 'em', '%' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 1500,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default'    => [
					'unit' => 'px',
				],
				'selectors'  => [
					$wrapper . ' .piotnetforms-field-group .piotnetforms-field:not(.piotnetforms-select-wrapper)' => 'max-width: {{SIZE}}{{UNIT}}!important;',
					$wrapper . ' .piotnetforms-field-group .piotnetforms-field .piotnetforms-field-textual' => 'max-width: {{SIZE}}{{UNIT}}!important;',
					$wrapper . ' .piotnetforms-field-group .piotnetforms-field.piotnetforms-select-drop-down' => 'max-width: {{SIZE}}{{UNIT}}!important;',
					$wrapper . ' .piotnetforms-field-group .piotnetforms-field select.piotnetforms-field-textual' => 'max-width: unset !important;',
				],
			]
		);

		$this->add_responsive_control(
			'input_height' . $name,
			[
				'label'      => __( 'Input Height', 'piotnetforms' ),
				'type'       => 'slider',
				'size_units' => [ 'px', 'em', '%' ],
				'range'      => [
					'px' => [
						'min' => 0,
						'max' => 1000,
					],
					'%'  => [
						'min' => 0,
						'max' => 100,
					],
				],
				'default'    => [
					'unit' => 'px',
				],
				'selectors'  => [
					$wrapper . ' .piotnetforms-field-group .piotnetforms-field-container .mce-tinymce iframe' => 'height: {{SIZE}}{{UNIT}}!important;',
					$wrapper . ' .piotnetforms-field-group .piotnetforms-field-textual' => 'height: {{SIZE}}{{UNIT}}!important;',
				],
				'condition'  => [
					'field_type' => 'tinymce',
				],
			]
		);

		$this->add_responsive_control(
			'input_padding' . $name,
			[
				'label'      => __( 'Input Padding', 'piotnetforms' ),
				'type'       => 'dimensions',
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field:not(.piotnetforms-select-wrapper)' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field select' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field .piotnetforms-field-textual .selectize-input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field .selectize-control .selectize-dropdown .selectize-dropdown-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition'  => [
					'field_type!' => 'checkbox',
				],
			]
		);

		$this->add_control(
			'input_placeholder_color' . $name,
			[
				'label'     => __( 'Input Placeholder Color', 'piotnetforms' ),
				'label_block' => true,
				'type'      => 'color',
				'default'   => '',
				'selectors' => [
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field:not(.piotnetforms-select-wrapper)::placeholder' => 'color: {{VALUE}}; opacity: 1;',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field:not(.piotnetforms-select-wrapper)::-webkit-input-placeholder' => 'color: {{VALUE}}; opacity: 1;',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field:not(.piotnetforms-select-wrapper)::-moz-placeholder' => 'color: {{VALUE}}; opacity: 1;',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field:not(.piotnetforms-select-wrapper):-ms-input-placeholder' => 'color: {{VALUE}}; opacity: 1;',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field:not(.piotnetforms-select-wrapper):-moz-placeholder' => 'color: {{VALUE}}; opacity: 1;',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field .piotnetforms-field-textual' => 'color: {{VALUE}}; opacity: 1;',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field .selectize-control .selectize-input input::placeholder' => 'color: {{VALUE}}; opacity: 1;',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field .selectize-control .input-active input' => 'color: {{VALUE}}; opacity: 1;',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field.piotnetforms-field-textual::-webkit-input-placeholder' => 'color: {{VALUE}}; opacity: 1;',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field.piotnetforms-field-textual::-moz-placeholder' => 'color: {{VALUE}}; opacity: 1;',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field.piotnetforms-field-textual:-ms-input-placeholder' => 'color: {{VALUE}}; opacity: 1;',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field.piotnetforms-field-textual:-moz-placeholder' => 'color: {{VALUE}}; opacity: 1;',
					$wrapper . ' .flatpickr-mobile:before' => 'color: {{VALUE}}; opacity: 1;',
				],
			]
		);

		$this->add_control(
			'field_border_type' . $name,
			[
				'label'     => _x( 'Border Type', 'Border Control', 'piotnetforms' ),
				'type'      => 'select',
				'options'   => [
					''       => __( 'None', 'piotnetforms' ),
					'solid'  => _x( 'Solid', 'Border Control', 'piotnetforms' ),
					'double' => _x( 'Double', 'Border Control', 'piotnetforms' ),
					'dotted' => _x( 'Dotted', 'Border Control', 'piotnetforms' ),
					'dashed' => _x( 'Dashed', 'Border Control', 'piotnetforms' ),
					'groove' => _x( 'Groove', 'Border Control', 'piotnetforms' ),
				],
				'selectors' => [
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field:not(.piotnetforms-select-wrapper)' => 'border-style: {{VALUE}};',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field .piotnetforms-field-textual' => 'border-style: {{VALUE}};',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field .piotnetforms-field-textual .selectize-input' => 'border-style: {{VALUE}};',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field .selectize-control .selectize-dropdown' => 'border-style: {{VALUE}};',
					$wrapper . ' .piotnetforms-signature canvas' => 'border-style: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'field_border_width' . $name,
			[
				'label'     => _x( 'Width', 'Border Control', 'piotnetforms' ),
				'type'      => 'dimensions',
				'selectors' => [
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field:not(.piotnetforms-select-wrapper)' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field .piotnetforms-field-textual' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field .piotnetforms-field-textual .selectize-input' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field .selectize-control .selectize-dropdown' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					$wrapper . ' .piotnetforms-signature canvas' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'field_border_type!' => '',
				],
			]
		);

		$this->add_control(
			'field_border_color' . $name,
			[
				'label'     => _x( 'Color', 'Border Control', 'piotnetforms' ),
				'type'      => 'color',
				'default'   => '',
				'selectors' => [
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field:not(.piotnetforms-select-wrapper)' => 'border-color: {{VALUE}};',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field .piotnetforms-field-textual' => 'border-color: {{VALUE}};',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field .piotnetforms-field-textual .selectize-input' => 'border-color: {{VALUE}};',
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field .selectize-control .selectize-dropdown' => 'border-color: {{VALUE}};',
					$wrapper . ' .piotnetforms-signature canvas' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'field_border_type!' => '',
				],
			]
		);

		$this->add_responsive_control(
			'field_border_radius' . $name,
			[
				'label'      => __( 'Border Radius', 'piotnetforms' ),
				'type'       => 'dimensions',
				'size_units' => [ 'px', '%' ],
				'selectors'  => [
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field:not(.piotnetforms-select-wrapper)' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					$wrapper . ' .piotnetforms-field-group .piotnetforms-select-wrapper select' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					$wrapper . ' .piotnetforms-field-group .piotnetforms-select-wrapper .piotnetforms-field-textual' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					$wrapper . ' .piotnetforms-field-group .piotnetforms-select-wrapper .piotnetforms-field-textual .selectize-input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					$wrapper . ' .piotnetforms-field-group .piotnetforms-select-wrapper .selectize-control .selectize-dropdown .selectize-dropdown-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					$wrapper . ' .piotnetforms-signature canvas' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'field_box_shadow' . $name,
			[
				'type'        => 'box-shadow',
				'label'       => __( 'Box Shadow', 'piotnetforms' ),
				'value'       => '',
				'label_block' => false,
				'render_type' => 'none',
				'selectors'   => [
					$wrapper . ' .piotnetforms-field-group:not(.piotnetforms-field-type-upload) .piotnetforms-field' => 'box-shadow: {{VALUE}};',
				],
			]
		);
		return $this->get_group_controls( $previous_controls );
	}
	private function add_password_compare_style_controls() {
		$this->add_control(
			'icon_password_size',
			[
				'label'     => __( 'Icon Size', 'piotnetforms' ),
				'type'      => 'slider',
				'default'   => [
					'size' => '',
					'unit' => 'px',
				],
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 60,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-show-password-icon > i' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);
		$this->add_control(
			'icon_password_color',
			[
				'label'     => __( 'Icon Color', 'piotnetforms' ),
				'type'      => 'color',
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-show-password-icon > i' => 'color: {{VALUE}};',
				],
			]
		);
		$this->add_responsive_control(
			'piotnetforms_style_password_compare_padding',
			[
				'label'      => __( 'Button Padding', 'piotnetforms' ),
				'type'       => 'dimensions',
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .piotnetforms-show-password-icon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
	}
	private function add_field_style_controls() {
		$this->add_control(
			'',
			[
				'type' => 'heading-tab',
				'tabs' => [
					[
						'name'   => 'field_normal_tab',
						'title'  => __( 'NORMAL', 'piotnetforms' ),
						'active' => true,
					],
					[
						'name'  => 'field_focus_tab',
						'title' => __( 'FOCUS', 'piotnetforms' ),
					],
				],
			]
		);

		$normal_controls = $this->field_style_controls(
			'',
			[
				'wrapper' => '{{WRAPPER}}',
			]
		);
		$this->add_control(
			'field_normal_tab',
			[
				'type'           => 'content-tab',
				'label'          => __( 'Normal', 'piotnetforms' ),
				'value'          => '',
				'active'         => true,
				'controls'       => $normal_controls,
				'controls_query' => '.piotnet-start-controls-tab',
			]
		);

		$focus_controls = $this->field_style_controls(
			'focus',
			[
				'wrapper' => '{{WRAPPER}}.piotnetforms-field-focus',
			]
		);
		$this->add_control(
			'field_focus_tab',
			[
				'type'           => 'content-tab',
				'label'          => __( 'Focus', 'piotnetforms' ),
				'value'          => '',
				'controls'       => $focus_controls,
				'controls_query' => '.piotnet-start-controls-tab',
			]
		);
	}

	private function add_field_description_style_controls() {
		$this->add_control(
			'field_description_margin_top',
			[
				'label'     => __( 'Margin Top', 'piotnetforms' ),
				'type'      => 'slider',
				'default'   => [
					'size' => '',
					'unit' => 'px',
				],
				'range'     => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .piotnetforms-field-description' => 'margin-top: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'field_description_color',
			[
				'type'        => 'color',
				'label'       => __( 'Color', 'piotnetforms' ),
				'selectors'  => [
					'{{WRAPPER}} .piotnetforms-field-description' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_responsive_control(
			'field_description_align',
			[
				'type'        => 'select',
				'label'       => __( 'Align', 'piotnetforms' ),
				'label_block' => true,
				'options'     => [
					'' => 'Default',
					'left' => 'Left',
					'right' => 'Right',
					'center' => 'Center',
				],
				'selectors'  => [
					'{{WRAPPER}} .piotnetforms-field-description' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_text_typography_controls(
			'field_description_typography',
			[
				'selectors' => '{{WRAPPER}} .piotnetforms-field-description',
			]
		);
	}

	protected function make_textarea_field( $item, $item_index, $form_id, $tinymce = false, $i=0 ) {
		$this->add_render_attribute(
			'textarea' . $item_index,
			[
				'class' => [
					'piotnetforms-field-textual',
					'piotnetforms-field',
					'piotnetforms-size-' . $item['input_size'],
				],
				'name'  => $this->get_attribute_name( $item ),
				'id'    => $this->get_attribute_id( $item ),
				'rows'  => $item['rows'],
			]
		);

		if ( ! empty( $item['field_placeholder'] ) ) {
			$this->add_render_attribute( 'textarea' . $item_index, 'placeholder', $item['field_placeholder'] );
		}

		if ( $tinymce ) {
			$rtl = is_rtl() ? 'rtl' : 'ltr';
			$this->add_render_attribute( 'textarea' . $item_index, 'data-piotnetforms-tinymce' );
			$this->add_render_attribute( 'textarea' . $item_index, 'data-piotnetforms-tinymce-rtl', $rtl );
		}

		if ( !empty( $item['field_required'] ) ) {
			$this->add_required_attribute( 'textarea' . $item_index );
		}

		if ( ! empty( $item['invalid_message'] ) ) {
			$this->add_render_attribute( 'textarea' . $i, 'oninvalid', "this.setCustomValidity('" . $item['invalid_message'] . "')" );
			$this->add_render_attribute( 'textarea' . $i, 'onchange', "this.setCustomValidity('')" );
		}

		if ( ! empty( $item['max_length'] ) ) {
			$this->add_render_attribute( 'textarea' . $i, 'maxlength', $item['max_length'] );
		}

		if ( ! empty( $item['min_length'] ) ) {
			$this->add_render_attribute( 'textarea' . $i, 'minlength', $item['min_length'] );
		}

		if ( ! empty( $item['field_pattern_not_tel'] ) ) {
			$this->add_render_attribute( 'textarea' . $i, 'pattern', $item['field_pattern_not_tel'] );
		}

		if ( ! empty( $item['remove_this_field_from_repeater'] ) ) {
			$this->add_render_attribute( 'textarea' . $item_index, 'data-piotnetforms-remove-this-field-from-repeater' );
		}

		$name  = $this->get_field_name_shortcode( $this->get_attribute_name( $item ) );
		$value = $this->get_value_edit_post( $name );

		if ( empty( $value ) ) {
			$value = isset( $item['field_value'] ) ? $item['field_value'] : '';
			$this->add_render_attribute( 'textarea' . $item_index, 'data-piotnetforms-default-value', $value );
		}

		// if ( ! empty( $value ) ) {
		// 	$this->add_render_attribute( 'input' . $i, 'value', $value );
		// }
		// $value = empty( $item['field_value'] ) ? '' : $item['field_value'];

		$this->add_render_attribute( 'textarea' . $item_index, 'data-piotnetforms-id', $form_id );
		return '<textarea ' . $this->get_render_attribute_string( 'textarea' . $item_index ) . '>' . $value . '</textarea>';
	}

	protected function make_select_field( $item, $i, $form_id, $image_select = false, $terms_select = false, $select_autocomplete = false, $select2 = false ) {
		$preview_class = !empty( $item['live_preview_label'] ) ? 'piotnetforms-preview-label' : '';
		$multiple_class = !empty( $item['allow_multiple'] ) ? ' piotnetforms-select-multiple' : '';
		$select_class = $item['field_type'] == 'select' ? ' piotnetforms-select-drop-down' : '';
		$this->add_render_attribute(
			[
				'select-wrapper' . $i => [
					'class' => [
						'piotnetforms-field',
						'piotnetforms-select-wrapper' . $multiple_class . $select_class,
					],
				],
				'select' . $i         => [
					'name'  => $this->get_attribute_name( $item ) . ( ! empty( $item['allow_multiple'] ) ? '[]' : '' ),
					'id'    => $this->get_attribute_id( $item ),
					'class' => [
						'piotnetforms-field-textual' . $preview_class,
						'piotnetforms-size-' . $item['input_size'],
					],
					'data-piotnetforms-type' => 'select'
				],
			]
		);

		if ( $select2 ) {
			$this->add_render_attribute(
				['select' . $i => [
						'class' => [
							'piotnetforms-type-select2',
						],
					],
				]
			);
		}

		if ( $image_select ) {
			$list           = $item['piotnetforms_image_select_field_gallery'];
			$limit_multiple = $item['limit_multiple'];
			$min_select     = $item['min_select'];
			$min_select_message = $item['min_select_required_message'];
			if ( ! empty( $list ) ) {
				$this->add_render_attribute(
					[
						'select' . $i => [
							'data-piotnetforms-image-select' => json_encode( $list ),
						],
					]
				);

				if ( ! empty( $limit_multiple ) ) {
					$this->add_render_attribute(
						[
							'select' . $i => [
								'data-piotnetforms-image-select-limit-multiple' => $limit_multiple,
							],
						]
					);
					wp_enqueue_script( $this->slug . '-advanced2-script' );
				}

				if ( ! empty( $min_select ) ) {
					$this->add_render_attribute(
						[
							'select' . $i => [
								'data-piotnetforms-image-select-min-select' => $min_select,
							],
						]
					);
					$this->add_render_attribute(
						[
							'select' . $i => [
								'data-piotnetforms-image-select-min-select-message' => $min_select_message,
							],
						]
					);
				}
			}
		}

		if ( $item['field_required'] ) {
			$this->add_required_attribute( 'select' . $i );
		}

		if ( $item['allow_multiple'] ) {
			$this->add_render_attribute( 'select' . $i, 'multiple' );
			if ( ! empty( $item['select_size'] ) ) {
				$this->add_render_attribute( 'select' . $i, 'size', $item['select_size'] );
			}
		}

		if ( $item['send_data_by_label'] ) {
			$this->add_render_attribute( 'select' . $i, 'data-piotnetforms-send-data-by-label' );
		}

		if ( ! empty( $item['invalid_message'] ) ) {
			$this->add_render_attribute( 'select' . $i, 'oninvalid', "this.setCustomValidity('" . $item['invalid_message'] . "')" );
			$this->add_render_attribute( 'select' . $i, 'onchange', "this.setCustomValidity('')" );
		}

		if ( ! empty( $item['remove_this_field_from_repeater'] ) ) {
			$this->add_render_attribute( 'select' . $i, 'data-piotnetforms-remove-this-field-from-repeater' );
		}

		if ( ! empty( $item['multi_step_form_autonext'] ) ) {
			$this->add_render_attribute( 'select' . $i, 'data-piotnetforms-multi-step-form-autonext' );
		}

		if ( ! empty( $item['payment_methods_select_field_enable'] ) ) {
			$this->add_render_attribute( 'select' . $i, 'data-piotnetforms-payment-methods-select-field', '' );
			$this->add_render_attribute( 'select' . $i, 'data-piotnetforms-payment-methods-select-field-value-for-stripe', $item['payment_methods_select_field_value_for_stripe'] );
			$this->add_render_attribute( 'select' . $i, 'data-piotnetforms-payment-methods-select-field-value-for-paypal', $item['payment_methods_select_field_value_for_paypal'] );
		}

		$options = preg_split( '/\\r\\n|\\r|\\n/', $item['field_options'] );

		if ( $terms_select ) {
			if ( ! empty( $item['field_taxonomy_slug'] ) ) {
				$terms = get_terms(
					[
						'taxonomy'   => $item['field_taxonomy_slug'],
						'hide_empty' => false,
					]
				);

				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					$options = [];
					foreach ( $terms as $term ) {
						$options[] = $term->name . '|' . $term->slug;
					}
				}
			}
		}

		if ( ! $options ) {
			return '';
		}

		if ( $select_autocomplete ) {
			$this->add_render_attribute(
				[
					'select' . $i => [
						'data-piotnetforms-select-autocomplete' => '',
					],
				]
			);

			wp_enqueue_script( $this->slug . '-advanced2-script' );
		}

		ob_start();
		$this->add_render_attribute( 'select' . $i, 'data-piotnetforms-id', $form_id );

		$name  = $this->get_field_name_shortcode( $this->get_attribute_name( $item ) );
		$value = $this->get_value_edit_post( $name );

		if ( empty( $value ) ) {
			$this->add_render_attribute( 'select' . $i, 'data-piotnetforms-default-value', $item['field_value'] );
		} ?>
		<div <?php echo $this->get_render_attribute_string( 'select-wrapper' . $i ); ?>>
			<select <?php echo $this->get_render_attribute_string( 'select' . $i ); ?> data-options='<?php echo json_encode( $options ); ?>'>
				<?php

				if ( $select_autocomplete && ! empty( $item['field_placeholder'] ) ) {
					array_unshift( $options, $item['field_placeholder'] . '|' . '' );
				}

		foreach ( $options as $key => $option ) {
			$option_id    = $key;
			$option_value = esc_attr( $option );
			$option_label = esc_html( $option );

			if ( false !== strpos( $option, '|' ) ) {
				list( $label, $value ) = explode( '|', $option );
				$option_value          = esc_attr( $value );
				$option_label          = esc_html( $label );
			}

			$this->add_render_attribute( $option_id, 'value', $option_value );

			$name  = $this->get_field_name_shortcode( $this->get_attribute_name( $item ) );
			$value = $this->get_value_edit_post( $name );

			if ( empty( $value ) ) {
				$value = $item['field_value'];
			}

			if ( ! empty( $value ) && $option_value === $value ) {
				$this->add_render_attribute( $option_id, 'selected', 'selected' );
			}

			if ( ! $select_autocomplete ) {
				$values = explode( ',', $value );

				foreach ( $values as $value_item ) {
					if ( $option_value === $value_item ) {
						$this->add_render_attribute( $option_id, 'selected', 'selected' );
					}
				}
			}

			if ( $item['send_data_by_label'] ) {
				$this->add_render_attribute( $option_id, 'data-piotnetforms-send-data-by-label', $option_label );
			}

			if ( ! empty( $item['remove_this_field_from_repeater'] ) ) {
				$this->add_render_attribute( $option_id, 'data-piotnetforms-remove-this-field-from-repeater', $option_label );
			}

			if ( $key == ( count( $options ) - 1 ) && trim( $option_value ) == '' ) {
				# code...
			} else {
				if ( false !== strpos( $option_value, '[optgroup' ) ) {
					$optgroup = str_replace( '&quot;', '', str_replace( ']', '', str_replace( '[optgroup label=', '', $option_value ) ) ); // fix alert ]
					echo '<optgroup label="' . esc_attr( $optgroup ) . '">';
				} elseif ( false !== strpos( $option_value, '[/optgroup]' ) ) {
					echo '</optgroup>';
				} else {
					echo '<option ' . $this->get_render_attribute_string( $option_id ) . '>' . $option_label . '</option>';
				}
			}
		} ?>
			</select>
		</div>
		<?php

		$select = ob_get_clean();
		return $select;
	}

	protected function make_radio_checkbox_field( $item, $item_index, $type, $form_id, $terms_select = false ) {
		if ( !empty( $item['field_options'] ) ) {
			$options = preg_split( '/\\r\\n|\\r|\\n/', $item['field_options'] );
		}

		if ( $item['piotnetforms_style_checkbox_type'] ) {
			if ( $item['piotnetforms_style_checkbox_type'] == 'button' ) {
				if ( $item['checkbox_style_button_with_icon_list'] ) {
					if ( count( $item['checkbox_style_button_with_icon_list'] ) > 0 ) {
						$options = [];
						$options_list = $item['checkbox_style_button_with_icon_list'];
						for ( $i = 0; $i < count( $options_list ); $i++ ) {
							if ( !empty( $options_list[$i]['value'] ) && !empty( $options_list[$i]['label'] ) ) {
								$options[] = $options_list[$i]['label'] . '|' . $options_list[$i]['value'];
							} else {
								if ( $options_list[$i]['label'] ) {
									$options[] = $options_list[$i]['label'];
								} else {
									$options[] = $options_list[$i]['value'];
								}
							}
						}
					}
				}
			}
		}

		if ( $terms_select ) {
			if ( ! empty( $item['field_taxonomy_slug'] ) ) {
				$terms = get_terms(
					[
						'taxonomy'   => $item['field_taxonomy_slug'],
						'hide_empty' => false,
					]
				);

				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					$options = [];
					foreach ( $terms as $term ) {
						$options[] = $term->name . '|' . $term->slug;
					}
				}
			}
		}

		$html = '';
		if ( $options ) {
			$checkbox_replacement_class = $item['piotnetforms_style_checkbox_replacement'] && $item['piotnetforms_style_checkbox_type'] == 'native' ? ' piotnetforms-field-subgroup--checkbox-replacement' : '';
			// $html .= '<form>';
			$html .= '<div class="piotnetforms-field-subgroup piotnetforms-field-subgroup--' . $item['piotnetforms_style_checkbox_type'] . ' ' . $checkbox_replacement_class . ' ' . $item['inline_list'] . '">';
			$index = 0;
			foreach ( $options as $key => $option ) {
				$index++;
				$element_id   = $item['field_id'] . $key;
				$html_id      = $this->get_attribute_id( $item ) . '-' . $key;
				$option_label = $option;
				$option_value = $option;
				if ( false !== strpos( $option, '|' ) ) {
					list( $option_label, $option_value ) = explode( '|', $option );
				}

				$this->add_render_attribute(
					$element_id,
					[
						'type'       => $type,
						'value'      => $option_value,
						'data-value' => $option_value,
						'id'         => $html_id,
						'name'       => $this->get_attribute_name( $item ) . ( ( 'checkbox' === $type && count( $options ) > 1 ) ? '[]' : '' ),
					]
				);

				$name  = $this->get_field_name_shortcode( $this->get_attribute_name( $item ) );
				$value = $this->get_value_edit_post( $name );

				if ( ! empty( $item['checkbox_limit_multiple'] ) ) {
					$this->add_render_attribute( $element_id, 'data-piotnetforms-checkbox-limit-multiple', $item['checkbox_limit_multiple'] );
				}

				if ( empty( $value ) ) {
					$value = $item['field_value'];
					$this->add_render_attribute( $element_id, 'data-piotnetforms-default-value', $item['field_value'] );
				}
				if ( !empty( $item['live_preview_show_label'] ) ) {
					$this->add_render_attribute( $element_id, 'class', 'piotnetforms-preview-label' );
				}
				if ( ! empty( $item['invalid_message'] ) ) {
					// if ( $index == 1 ) {
					// 	$this->add_render_attribute( $element_id, 'oninvalid', "this.setCustomValidity('" . $item['invalid_message'] . "')" );
					// 	$this->add_render_attribute( $element_id, 'onchange', "this.setCustomValidity('')" );
					// } else {
					$this->add_render_attribute( $element_id, 'onclick', 'clearValidity(this)' );
					$this->add_render_attribute( $element_id, 'oninvalid', "this.setCustomValidity('" . $item['invalid_message'] . "')" );
					$this->add_render_attribute( $element_id, 'onchange', "this.setCustomValidity('')" );
					// }
					wp_enqueue_script( $this->slug . '-advanced2-script' );
				}

				if ( ! empty( $item['payment_methods_select_field_enable'] ) ) {
					$this->add_render_attribute( $element_id, 'data-piotnetforms-payment-methods-select-field', '' );
					$this->add_render_attribute( $element_id, 'data-piotnetforms-payment-methods-select-field-value-for-stripe', $item['payment_methods_select_field_value_for_stripe'] );
					$this->add_render_attribute( $element_id, 'data-piotnetforms-payment-methods-select-field-value-for-paypal', $item['payment_methods_select_field_value_for_paypal'] );
					wp_enqueue_script( $this->slug . '-advanced-script' );
				}

				if ( ! empty( $value ) && $option_value === $value ) {
					$this->add_render_attribute( $element_id, 'checked', 'checked' );
					$this->add_render_attribute( $element_id, 'data-checked', 'checked' );
				}

				$values = explode( ',', $value );
				foreach ( $values as $value_item ) {
					if ( $option_value === $value_item ) {
						$this->add_render_attribute( $element_id, 'checked', 'checked' );
						$this->add_render_attribute( $element_id, 'data-checked', 'checked' );
					}
				}

				if ( $item['send_data_by_label'] ) {
					$this->add_render_attribute( $element_id, 'data-piotnetforms-send-data-by-label', $option_label );
				}

				if ( ! empty( $item['remove_this_field_from_repeater'] ) ) {
					$this->add_render_attribute( $element_id, 'data-piotnetforms-remove-this-field-from-repeater', $option_label );
				}

				if ( $item['field_required'] && 'radio' === $type ) {
					$this->add_required_attribute( $element_id );
				}

				if ( ! empty( $item['multi_step_form_autonext'] ) && 'radio' === $type ) {
					$this->add_render_attribute( $element_id, 'data-piotnetforms-multi-step-form-autonext' );
				}

				$this->add_render_attribute( $element_id, 'data-piotnetforms-id', $form_id );


				$icon_html = '';

				if ( $item['piotnetforms_style_checkbox_replacement'] && $item['piotnetforms_style_checkbox_type'] == 'native' ) {
					if ( $item['piotnetforms_style_checkbox_replacement_icon_type'] == 'font_awesome' ) {
						if ( $item['piotnetforms_style_checkbox_replacement_icon_font_awesome'] || $item['piotnetforms_style_checkbox_replacement_icon_font_awesome_checked'] ) {
							wp_enqueue_style( $this->slug . '-fontawesome-style' );
							$icon_html_wrapper_class = $item['piotnetforms_style_checkbox_replacement_icon_font_awesome_checked'] ? 'pf-f-o-i pf-f-o-i-has-checked' : 'pf-f-o-i';

							if ( $item['piotnetforms_style_checkbox_replacement_icon_font_awesome_checked'] && !$item['piotnetforms_style_checkbox_replacement_icon_font_awesome'] ) {
								$icon_html_wrapper_class .= ' pf-f-o-i--only-checked';
							}

							$icon_html = '<span class="' . $icon_html_wrapper_class . '">';

							if ( $item['piotnetforms_style_checkbox_replacement_icon_font_awesome'] ) {
								$icon_html .= '<i class="pf-f-o-i-font-awesome pf-f-o-i-font-awesome--normal ' . $item['piotnetforms_style_checkbox_replacement_icon_font_awesome'] . '"></i>';
							}

							if ( $item['piotnetforms_style_checkbox_replacement_icon_font_awesome_checked'] ) {
								$icon_html .= '<i class="pf-f-o-i-font-awesome pf-f-o-i-font-awesome--checked ' . $item['piotnetforms_style_checkbox_replacement_icon_font_awesome_checked'] . '"></i>';
							}

							$icon_html .= '</span>';
						}
					} else {
						if ( $item['piotnetforms_style_checkbox_replacement_icon_image'] || $item['piotnetforms_style_checkbox_replacement_icon_image_checked'] ) {
							$icon_html_wrapper_class = $item['piotnetforms_style_checkbox_replacement_icon_image_checked'] ? 'pf-f-o-i pf-f-o-i-has-checked' : 'pf-f-o-i';

							if ( $item['piotnetforms_style_checkbox_replacement_icon_image_checked'] && !$item['piotnetforms_style_checkbox_replacement_icon_image'] ) {
								$icon_html_wrapper_class .= ' pf-f-o-i--only-checked';
							}

							$icon_html = '<span class="' . $icon_html_wrapper_class . '">';

							if ( $item['piotnetforms_style_checkbox_replacement_icon_image'] ) {
								$icon_html .= '<img class="pf-f-o-i-image pf-f-o-i-image--normal" src="' . $item['piotnetforms_style_checkbox_replacement_icon_image']['url'] . '">';
							}

							if ( $item['piotnetforms_style_checkbox_replacement_icon_image_checked'] ) {
								$icon_html .= '<img class="pf-f-o-i-image pf-f-o-i-image--checked" src="' . $item['piotnetforms_style_checkbox_replacement_icon_image_checked']['url'] . '">';
							}

							$icon_html .= '</span>';
						}
					}
				}

				if ( $item['checkbox_style_button_with_icon_list'] && $item['piotnetforms_style_checkbox_type'] == 'button' ) {
					$icon_list = $item['checkbox_style_button_with_icon_list'];
					if ( count( $icon_list ) > 0 ) {
						if ( $icon_list[$key]['icon_type'] == 'font_awesome' ) {
							if ( $icon_list[$key]['icon_font_awesome'] || $icon_list[$key]['icon_font_awesome_checked'] ) {
								wp_enqueue_style( $this->slug . '-fontawesome-style' );
								$icon_html_wrapper_class = $icon_list[$key]['icon_font_awesome_checked'] ? 'pf-f-o-i pf-f-o-i-has-checked' : 'pf-f-o-i';

								if ( $icon_list[$key]['icon_font_awesome_checked'] && !$icon_list[$key]['icon_font_awesome'] ) {
									$icon_html_wrapper_class .= ' pf-f-o-i--only-checked';
								}

								$icon_html = '<span class="' . $icon_html_wrapper_class . '">';

								if ( $icon_list[$key]['icon_font_awesome'] ) {
									$icon_html .= '<i class="pf-f-o-i-font-awesome pf-f-o-i-font-awesome--normal ' . $icon_list[$key]['icon_font_awesome'] . '"></i>';
								}

								if ( $icon_list[$key]['icon_font_awesome_checked'] ) {
									$icon_html .= '<i class="pf-f-o-i-font-awesome pf-f-o-i-font-awesome--checked ' . $icon_list[$key]['icon_font_awesome_checked'] . '"></i>';
								}

								$icon_html .= '</span>';
							}
						} else {
							if ( $icon_list[$key]['icon_image'] || $icon_list[$key]['icon_image_checked'] ) {
								$icon_html_wrapper_class = $icon_list[$key]['icon_image_checked'] ? 'pf-f-o-i pf-f-o-i-has-checked' : 'pf-f-o-i';

								if ( $icon_list[$key]['icon_image_checked'] && !$icon_list[$key]['icon_image'] ) {
									$icon_html_wrapper_class .= ' pf-f-o-i--only-checked';
								}

								$icon_html = '<span class="' . $icon_html_wrapper_class . '">';

								if ( $icon_list[$key]['icon_image'] ) {
									$icon_html .= '<img class="pf-f-o-i-image pf-f-o-i-image--normal" src="' . $icon_list[$key]['icon_image']['url'] . '">';
								}

								if ( $icon_list[$key]['icon_image_checked'] ) {
									$icon_html .= '<img class="pf-f-o-i-image pf-f-o-i-image--checked" src="' . $icon_list[$key]['icon_image_checked']['url'] . '">';
								}

								$icon_html .= '</span>';
							}
						}
					}
				}

				$icon_on_top_class = $item['piotnetforms_style_checkbox_button_icon_on_top'] ? 'piotnetforms-field-icon-on-top' : '';

				$html .= '<span class="piotnetforms-field-option"><input ' . $this->get_render_attribute_string( $element_id ) . '> <label for="' . esc_attr( $html_id ) . '" class="' . esc_attr( $icon_on_top_class ) . '">' . $icon_html . $option_label . '</label></span>';
			}

			$html .= '</div>';
			// $html .= '</form>';
		}

		return $html;
	}

	protected function form_fields_render_attributes( $i, $instance, $item ) {
		$label_inline = !empty( $item['field_label_inline'] ) ? ' piotnetforms-label-inline' : '';
		$box_flex = !empty( $item['field_label_inline'] ) ? 'piotnetforms-column-flex' : '';

		if(!empty($item['piotnetforms_range_slider_field_options'])){
			if($this->piotnetforms_is_json($item['piotnetforms_range_slider_field_options'])){
				$rage_setting_encode = $item['piotnetforms_range_slider_field_options'];
			}else{
				$range_slider_set = explode(',', $item['piotnetforms_range_slider_field_options']);
				$range_slider_options = [];
				foreach($range_slider_set as $val){
					$slider_item = explode(':', $val);
					$range_slider_options[str_replace(['"', ' '], '', $slider_item[0])] = str_replace(['"'," "], '', $slider_item[1]);
				}
				$rage_setting_encode = wp_json_encode($range_slider_options);
			}
		}else{
			$rage_setting_encode = '';
		}

		$this->add_render_attribute(
			[
				'field-group' . $i       => [
					'class' => [
						'piotnetforms-field-type-' . $item['field_type'],
						'piotnetforms-field-group',
						'piotnetforms-column',
						$box_flex,
						'piotnetforms-field-group-' . $item['field_id'],
					],
				],
				'input' . $i             => [
					'class' => [
						'piotnetforms-field',
						'piotnetforms-size-' . $item['input_size'],
					],
				],
				'range_slider' . $i      => [
					'type'                           => 'text',
					'name'                           => $this->get_attribute_name( $item ),
					'id'                             => $this->get_attribute_id( $item ),
					'class'                          => [
						'piotnetforms-field',
						'piotnetforms-size-' . $item['input_size'],
					],
					'data-piotnetforms-range-slider' => $rage_setting_encode,
				],
				'calculated_fields' . $i => [
					'type'                                => 'text',
					'name'                                => $this->get_attribute_name( $item ),
					'id'                                  => $this->get_attribute_id( $item ),
					'class'                               => [
						'piotnetforms-field',
						'piotnetforms-size-' . $item['input_size'],
					],
					'data-piotnetforms-calculated-fields' => isset( $item['piotnetforms_calculated_fields_form_calculation'] ) ? $item['piotnetforms_calculated_fields_form_calculation'] : '',
					'data-piotnetforms-calculated-fields-before' => isset( $item['piotnetforms_calculated_fields_form_before'] ) ? $item['piotnetforms_calculated_fields_form_before'] : '',
					'data-piotnetforms-calculated-fields-after' => isset( $item['piotnetforms_calculated_fields_form_after'] ) ? $item['piotnetforms_calculated_fields_form_after'] : '',
					'data-piotnetforms-calculated-fields-rounding-decimals' => $item['piotnetforms_calculated_fields_form_calculation_rounding_decimals'],
					'data-piotnetforms-calculated-fields-rounding-decimals-decimals-symbol' => $item['piotnetforms_calculated_fields_form_calculation_rounding_decimals_decimals_symbol'],
					'data-piotnetforms-calculated-fields-rounding-decimals-seperators-symbol' => $item['piotnetforms_calculated_fields_form_calculation_rounding_decimals_seperators_symbol'],
					'data-piotnetforms-calculated-fields-rounding-decimals-show' => isset( $item['piotnetforms_calculated_fields_form_calculation_rounding_decimals_show'] ) ? $item['piotnetforms_calculated_fields_form_calculation_rounding_decimals_show'] : '',
				],
				'label' . $i             => [
					'for'   => $this->get_attribute_id( $item ),
					'class' => 'piotnetforms-field-label'.$label_inline,
				],
			]
		);

		if ( $item['field_type'] == 'address_autocomplete' || $item['field_type'] == 'iban' ) {
			$this->add_render_attribute(
				[
					'input' . $i => [
						'type' => 'text',
						'name' => $this->get_attribute_name( $item ),
						'id'   => $this->get_attribute_id( $item ),
					],
				]
			);
		} else {
			$this->add_render_attribute(
				[
					'input' . $i => [
						'type' => $item['field_type'] != 'confirm' ? $item['field_type'] : $item['confirm_type'],
						'name' => $this->get_attribute_name( $item ),
						'id'   => $this->get_attribute_id( $item ),
					],
				]
			);
		}

		if ( empty( $item['width'] ) ) {
			$item['width'] = '100';
		}

		$this->add_render_attribute( 'field-group' . $i, 'class', 'piotnetforms-col-' . $item['width'] );

		if ( ! empty( $item['width_tablet'] ) ) {
			$this->add_render_attribute( 'field-group' . $i, 'class', 'piotnetforms-md-' . $item['width_tablet'] );
		}

		if ( !empty( $item['allow_multiple'] ) && $item['allow_multiple'] ) {
			$this->add_render_attribute( 'field-group' . $i, 'class', 'piotnetforms-field-type-' . $item['field_type'] . '-multiple' );
		}

		if ( ! empty( $item['width_mobile'] ) ) {
			$this->add_render_attribute( 'field-group' . $i, 'class', 'piotnetforms-sm-' . $item['width_mobile'] );
		}

		if ( ! empty( $item['field_placeholder'] ) ) {
			$this->add_render_attribute( 'input' . $i, 'placeholder', $item['field_placeholder'] );
		}

		if ( ! empty( $item['max_length'] ) ) {
			$this->add_render_attribute( 'input' . $i, 'maxlength', $item['max_length'] );
		}

		if ( ! empty( $item['min_length'] ) ) {
			$this->add_render_attribute( 'input' . $i, 'minlength', $item['min_length'] );
		}

		if ( ! empty( $item['field_pattern_not_tel'] ) && $item['field_type'] != 'tel' ) {
			$this->add_render_attribute( 'input' . $i, 'pattern', $item['field_pattern_not_tel'] );
		}

		if ( ! empty( $item['input_mask_enable'] ) ) {
			wp_enqueue_script( $this->slug . '-jquery-mask-script' );
			if ( ! empty( $item['input_mask'] ) ) {
				$this->add_render_attribute( 'input' . $i, 'data-mask', $item['input_mask'] );
			}
			if ( ! empty( $item['input_mask_reverse'] ) ) {
				$this->add_render_attribute( 'input' . $i, 'data-mask-reverse', 'true' );
			}
		}

		if ( ! empty( $item['invalid_message'] ) ) {
			$this->add_render_attribute( 'input' . $i, 'oninvalid', "this.setCustomValidity('" . $item['invalid_message'] . "')" );
			$this->add_render_attribute( 'input' . $i, 'onchange', "this.setCustomValidity('')" );
		}

		if ( ! empty( $item['field_autocomplete'] ) ) {
			$this->add_render_attribute( 'input' . $i, 'autocomplete', 'on' );
		} else {
			$this->add_render_attribute( 'input' . $i, 'autocomplete', 'off' );
		}

		$name  = $this->get_field_name_shortcode( $this->get_attribute_name( $item ) );
		$value = $this->get_value_edit_post( $name );

		if ( empty( $value ) ) {
			$value = isset( $item['field_value'] ) ? $item['field_value'] : '';
			$this->add_render_attribute( 'input' . $i, 'data-piotnetforms-default-value', $value );
		}

		if ( ! empty( $value ) || $value == 0 ) {
			$this->add_render_attribute( 'input' . $i, 'value', $value );
			$this->add_render_attribute( 'range_slider' . $i, 'value', $value );
			$this->add_render_attribute( 'input' . $i, 'data-piotnetforms-value', $value );
		}

		if ( ! empty( $item['field_required'] ) ) {
			$class = 'piotnetforms-field-required';
			if ( ! empty( $item['mark_required'] ) ) {
				$class .= ' piotnetforms-mark-required';
			}
			$this->add_render_attribute( 'field-group' . $i, 'class', $class );
			$this->add_required_attribute( 'input' . $i );
		}

		if ( ! empty( $item['allow_multiple_upload'] ) ) {
			$this->add_render_attribute( 'input' . $i, 'multiple', 'multiple' );
			//$this->add_render_attribute( 'input' . $i, 'name', $this->get_attribute_name( $item ) . '[]', true );
		}

		if ( $item['field_type'] == 'image_upload' ) {
			if ( ! empty( $item['attach_files'] ) ) {
				$this->add_render_attribute( 'input' . $i, 'data-attach-files', 'true', true );
			}
		}
		if ( $item['field_type'] == 'upload' ) {
			wp_enqueue_script( $this->slug . '-jquery-validation-script' );
			$this->add_render_attribute( 'input' . $i, 'name', 'upload_field', true );
			if ( ! empty( $item['attach_files'] ) ) {
				$this->add_render_attribute( 'input' . $i, 'data-attach-files', '', true );
			}

			if ( ! empty( $item['file_sizes'] ) ) {
				$this->add_render_attribute(
					'input' . $i,
					[
						'data-maxsize'         => $item['file_sizes'],  //MB
						'data-maxsize-message' => $item['file_sizes_message'],
					]
				);
			}

			if ( ! empty( $item['file_types'] ) ) {
				$file_types   = explode( ',', $item['file_types'] );
				$file_accepts = [ 'jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'ppt', 'pptx', 'odt', 'avi', 'ogg', 'm4a', 'mov', 'mp3', 'mp4', 'mpg', 'wav', 'wmv', 'zip', 'xls', 'xlsx' ];

				if ( is_array( $file_types ) ) {
					$file_types_output = '';
					foreach ( $file_types as $file_type ) {
						$file_type = trim( $file_type );
						// if (in_array($file_type, $file_accepts)) {
						// 	$file_types_output .= '.' . $file_type . ',';
						// }
						$file_types_output .= '.' . $file_type . ',';
					}

					//$this->add_render_attribute( 'input' . $i, 'accept', rtrim($file_types_output,',') );
					$this->add_render_attribute( 'input' . $i, 'data-accept', str_replace( '.', '', rtrim( $file_types_output, ',' ) ) );
				}

				$this->add_render_attribute(
					'input' . $i,
					[
						'data-types-message' => $item['file_types_message'],
					]
				);
			}
		}

		if ( ! empty( $item['remove_this_field_from_repeater'] ) ) {
			$this->add_render_attribute( 'input' . $i, 'data-piotnetforms-remove-this-field-from-repeater', '', true );
			$this->add_render_attribute( 'range_slider' . $i, 'data-piotnetforms-remove-this-field-from-repeater', '', true );
			$this->add_render_attribute( 'calculated_fields' . $i, 'data-piotnetforms-remove-this-field-from-repeater', '', true );
		}
	}

	public function get_field_name_shortcode( $content ) {
		$field_name = str_replace( '[field id=', '', $content );
		$field_name = str_replace( ']', '', $field_name );
		$field_name = str_replace( '"', '', $field_name );
		$field_name = str_replace( 'form_fields[', '', $field_name );
		//fix alert ]
		return trim( $field_name );
	}

	public function get_value_edit_post( $name ) {
		$value = '';
		if ( ! empty( $_GET['edit'] ) ) {
			$post_id = intval( $_GET['edit'] );
			if ( is_user_logged_in() && get_post( $post_id ) != null ) {
				if ( current_user_can( 'edit_others_posts' ) || get_current_user_id() == get_post( $post_id )->post_author ) {
					$sp_post_id = get_post_meta( $post_id, '_submit_post_id', true );
					$form_id    = get_post_meta( $post_id, '_submit_button_id', true );

					if ( ! empty( $_GET['smpid'] ) ) {
						$sp_post_id = sanitize_text_field( $_GET['smpid'] );
					}

					if ( ! empty( $_GET['sm'] ) ) {
						$form_id = sanitize_text_field( $_GET['sm'] );
					}

					$form = [];

					$data     = json_decode( get_post_meta( $sp_post_id, '_piotnetforms_data', true ), true );
					$form['settings'] = $data['widgets'][ $form_id ]['settings'];

					if ( ! empty( $form ) ) {
						if ( ! empty( $form['settings'] ) ) {
							$sp_post_taxonomy  = isset( $form['settings']['submit_post_taxonomy'] ) ? $form['settings']['submit_post_taxonomy'] : 'category-post';
							$sp_title          = $this->get_field_name_shortcode( $form['settings']['submit_post_title'] );
							$sp_content        = $this->get_field_name_shortcode( $form['settings']['submit_post_content'] );
							$sp_terms          = isset( $form['settings']['submit_post_terms_list'] ) ? $form['settings']['submit_post_terms_list'] : [];
							$sp_term           = isset( $form['settings']['submit_post_term'] ) ? $this->get_field_name_shortcode( $form['settings']['submit_post_term'] ) : '';
							$sp_featured_image = $this->get_field_name_shortcode( $form['settings']['submit_post_featured_image'] );
							$sp_custom_fields  = isset( $form['settings']['submit_post_custom_fields_list'] ) ? $form['settings']['submit_post_custom_fields_list'] : [];
							$sp_post_type = $form['settings']['submit_post_type'];

							if ( $name == $sp_title ) {
								$value = get_the_title( $post_id );
							}

							if ( $name == $sp_content ) {
								$value = get_the_content( null, false, $post_id );
							}

							if ( $name == $sp_term ) {
								if ( ! empty( $sp_post_taxonomy ) ) {
									$sp_post_taxonomy = explode( '|', $sp_post_taxonomy );
									$sp_post_taxonomy = $sp_post_taxonomy[0];
									$terms            = get_the_terms( $post_id, $sp_post_taxonomy );
									if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
										$value = $terms[0]->slug;
									}
								}
							}

							if ( ! empty( $sp_terms ) ) {
								foreach ( $sp_terms as $sp_terms_item ) {
									$sp_post_taxonomy = explode( '|', $sp_terms_item['submit_post_taxonomy'] );
									$sp_post_taxonomy = $sp_post_taxonomy[0];
									$sp_term_slug     = $sp_terms_item['submit_post_terms_slug'];
									$sp_term          = $this->get_field_name_shortcode( $sp_terms_item['submit_post_terms_field_id'] );

									if ( $name == $sp_term ) {
										$terms = get_the_terms( $post_id, $sp_post_taxonomy );
										if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
											foreach ( $terms as $term ) {
												$value .= $term->slug . ',';
											}
										}
									}
								}

								$value = rtrim( $value, ',' );
							}

							if ( $name == $sp_featured_image ) {
								$value = get_the_post_thumbnail_url( $post_id, 'full' );
							}

							foreach ( $sp_custom_fields as $sp_custom_field ) {
								if ( ! empty( $sp_custom_field['submit_post_custom_field'] ) ) {
									if ( $name == $this->get_field_name_shortcode( $sp_custom_field['submit_post_custom_field_id'] ) ) {
										$meta_type = $sp_custom_field['submit_post_custom_field_type'];

										if ( function_exists( 'get_field' ) && $form['settings']['submit_post_custom_field_source'] == 'acf_field' ) {
											$value = get_field( $sp_custom_field['submit_post_custom_field'], $post_id );

											if ( $meta_type == 'image' ) {
												if ( is_array( $value ) ) {
													$value = $value['url'];
												}
											}

											if ( $meta_type == 'gallery' ) {
												if ( is_array( $value ) ) {
													$images = '';
													foreach ( $value as $item ) {
														if ( is_array( $item ) ) {
															$images .= $item['url'] . ',';
														}
													}
													$value = rtrim( $images, ',' );
												}
											}

											if ( $meta_type == 'select' || $meta_type == 'checkbox' || $meta_type == 'acf_relationship' ) {
												if ( is_array( $value ) ) {
													$value_string = '';
													foreach ( $value as $item ) {
														$value_string .= $item . ',';
													}
													$value = rtrim( $value_string, ',' );
												}
											}

											if ( $meta_type == 'date' ) {
												$value = get_post_meta( $post_id, $sp_custom_field['submit_post_custom_field'], true );
												$time  = strtotime( $value );
												$value = date( get_option( 'date_format' ), $time );
											}

											if ( $meta_type == 'file' ) {
												if ( !empty( $value ) ) {
													$value = get_field( $sp_custom_field['submit_post_custom_field'], $post_id, false );
												}
											}
										} elseif ( $form['settings']['submit_post_custom_field_source'] == 'toolset_field' ) {
											$meta_key = 'wpcf-' . $sp_custom_field['submit_post_custom_field'];

											$value = get_post_meta( $post_id, $meta_key, false );

											if ( $meta_type == 'gallery' ) {
												if ( ! empty( $value ) ) {
													$images = '';
													foreach ( $value as $item ) {
														$images .= $item . ',';
													}
													$value = rtrim( $images, ',' );
												}
											} elseif ( $meta_type == 'checkbox' ) {
												if ( is_array( $value ) ) {
													$value_string = '';
													foreach ( $value as $item ) {
														foreach ( $item as $item_item ) {
															$value_string .= $item_item[0] . ',';
														}
													}
													$value = rtrim( $value_string, ',' );
												}
											} elseif ( $meta_type == 'date' ) {
												$value = date( get_option( 'date_format' ), $value[0] );
											} else {
												$value = $value[0];
											}
										} elseif ( $form['settings']['submit_post_custom_field_source'] == 'jet_engine_field' ) {
											$value = get_post_meta( $post_id, $sp_custom_field['submit_post_custom_field'], true );

											if ( $meta_type == 'image' ) {
												if ( ! empty( $value ) ) {
													$value = wp_get_attachment_url( $value );
												}
											}

											if ( $meta_type == 'gallery' ) {
												if ( ! empty( $value ) ) {
													$images    = '';
													$images_id = explode( ',', $value );
													foreach ( $images_id as $item ) {
														$images .= wp_get_attachment_url( $item ) . ',';
													}
													$value = rtrim( $images, ',' );
												}
											}

											if ( $meta_type == 'select' ) {
												if ( is_array( $value ) ) {
													$value_string = '';
													foreach ( $value as $item ) {
														$value_string .= $item . ',';
													}
													$value = rtrim( $value_string, ',' );
												}
											}

											if ( $meta_type == 'checkbox' ) {
												if ( is_array( $value ) ) {
													$value_string = '';
													foreach ( $value as $key => $item ) {
														if ( $item == 'true' ) {
															$value_string .= $key . ',';
														}
													}
													$value = rtrim( $value_string, ',' );
												}
											}

											if ( $meta_type == 'date' ) {
												$value = get_post_meta( $post_id, $sp_custom_field['submit_post_custom_field'], true );
												$time  = strtotime( $value );
												$value = date( get_option( 'date_format' ), $time );
											}
										} elseif ( function_exists( 'pods_field' ) && $form['settings']['submit_post_custom_field_source'] == 'pods_field' ) {
											$value = pods_field( $sp_post_type, $post_id, $sp_custom_field['submit_post_custom_field'], true );

											if ( $meta_type == 'image' ) {
												if ( is_array( $value ) ) {
													$value = $value['guid'];
												}
											}

											if ( $meta_type == 'gallery' ) {
												if ( is_array( $value ) ) {
													$images = '';
													foreach ( $value as $item ) {
														if ( is_array( $item ) ) {
															$images .= $item['guid'] . ',';
														}
													}
													$value = rtrim( $images, ',' );
												}
											}

											// if ( $meta_type == 'select' || $meta_type == 'checkbox' ) {
											// 	if ( is_array( $value ) ) {
											// 		$value_string = '';
											// 		foreach ( $value as $item ) {
											// 			$value_string .= $item . ',';
											// 		}
											// 		$value = rtrim( $value_string, ',' );
											// 	}
											// } PODS DOESN'T SUPPORT

											if ( $meta_type == 'date' ) {
												$value = get_post_meta( $post_id, $sp_custom_field['submit_post_custom_field'], true );
												$time  = strtotime( $value );
												$value = date( get_option( 'date_format' ), $time );
											}
										} elseif ( function_exists( 'rwmb_get_value' ) && $form['settings']['submit_post_custom_field_source'] == 'metabox_field' ) {
											$value = rwmb_get_value( $sp_custom_field['submit_post_custom_field'], [], $post_id );

											if ( $meta_type == 'image' ) {
												$images = rwmb_get_value( $sp_custom_field['submit_post_custom_field'], [ 'limit' => 1, 'size' => 'large' ], $post_id );
												if ( is_array( $value ) ) {
													$value = $images['url'];
												}
											}

											if ( $meta_type == 'gallery' ) {
												$value = rwmb_get_value( $sp_custom_field['submit_post_custom_field'], [ 'size' => 'large' ], $post_id );
												if ( is_array( $value ) ) {
													$images = '';
													foreach ( $value as $item ) {
														if ( is_array( $item ) ) {
															$images .= $item['url'] . ',';
														}
													}
													$value = rtrim( $images, ',' );
												}
											}

											if ( $meta_type == 'select' || $meta_type == 'checkbox' ) {
												if ( is_array( $value ) ) {
													$value_string = '';
													foreach ( $value as $item ) {
														$value_string .= $item . ',';
													}
													$value = rtrim( $value_string, ',' );
												};
											}

											if ( $meta_type == 'date' ) {
												$value = get_post_meta( $post_id, $sp_custom_field['submit_post_custom_field'], true );
												$time  = strtotime( $value );
												$value = date( get_option( 'date_format' ), $time );
											}
										} else {
											$value = get_post_meta( $post_id, $sp_custom_field['submit_post_custom_field'], true );
										}
									}
								}
							}
						}
					}
				}
			}
		}

		return $value;
	}

	public function render_plain_content() {
	}

	public function get_attribute_name( $item ) {
		return 'form_fields[' . trim( $item['field_id'] ) . ']';
	}

	public function get_attribute_id( $item ) {
		return 'form-field-' . trim( $item['field_id'] );
	}

	private function add_required_attribute( $element ) {
		$this->add_render_attribute( $element, 'required', 'required' );
		$this->add_render_attribute( $element, 'aria-required', 'true' );
	}

	private function get_upload_file_size_options() {
		$max_file_size = wp_max_upload_size() / pow( 1024, 2 ); //MB

		$sizes = [];

		for ( $file_size = 1; $file_size <= $max_file_size; $file_size++ ) {
			$sizes[ $file_size ] = $file_size . 'MB';
		}

		return $sizes;
	}

	// public function render() {
	// 	$settings = $this->settings;

	// }

	public function render() {
		$settings = $this->settings;
		$item_index = 0;
		$item = $settings;
		$field_type = $settings['field_type'];
		$field_id = $settings['field_id'];
		$form_post_id = $this->post_id;
		$form_version = empty( get_post_meta( $form_post_id, '_piotnetforms_version', true ) ) ? 1 : get_post_meta( $form_post_id, '_piotnetforms_version', true );
		$form_id = $form_version == 1 ? $settings['form_id'] : $form_post_id;
		$country = !( empty( $settings['country'] ) ) ? $settings['country'] : '';
		$latitude = !( empty( $settings['google_maps_lat'] ) ) ? $settings['google_maps_lat'] : '';
		$longitude = !( empty( $settings['google_maps_lng'] ) ) ? $settings['google_maps_lng'] : '';
		$zoom = !( empty( $settings['google_maps_zoom'] ) ) ? $settings['google_maps_zoom']['size'] : '';
		$field_placeholder = !( empty( $settings['field_placeholder'] ) ) ? $settings['field_placeholder'] : '';
		$field_value = isset( $settings['field_value'] ) ? $settings['field_value'] : '';
		$field_required = !( empty( $settings['field_required'] ) ) ? ' required="required" ' : '';

		if ( !empty( $item['field_value'] ) ) {
			$item['field_value'] = piotnetforms_dynamic_tags( $item['field_value'] );
		}

		if ( !empty( $item['field_options'] ) ) {
			$item['field_options'] = piotnetforms_dynamic_tags( $item['field_options'] );
		}

		$item['input_size'] = '';
		$this->form_fields_render_attributes( $item_index, '', $item );

		if ( !empty( $settings['piotnetforms_conditional_logic_form_list'] ) ) {
			$list_conditional = $settings['piotnetforms_conditional_logic_form_list'];
			if ( !empty( $settings['piotnetforms_conditional_logic_form_enable'] ) && !empty( $list_conditional[0]['piotnetforms_conditional_logic_form_if'] ) && !empty( $list_conditional[0]['piotnetforms_conditional_logic_form_comparison_operators'] ) ) {
				//$this->add_render_attribute( 'field-group' . $item_index, 'data-piotnetforms-conditional-logic', json_encode($list_conditional) );
				$this->add_render_attribute( 'field-group' . $item_index, [
					'data-piotnetforms-conditional-logic' => str_replace( '\"]', '', str_replace( '[field id=\"', '', json_encode( $list_conditional ) ) ),
					'data-piotnetforms-conditional-logic-speed' => $settings['piotnetforms_conditional_logic_form_speed'],
					'data-piotnetforms-conditional-logic-easing' => $settings['piotnetforms_conditional_logic_form_easing'],
				] );

				wp_enqueue_script( $this->slug . '-advanced-script' );
			}
		}

		if ( !empty( $item['number_spiner'] ) && $item['field_type'] == 'number' ) {
			$this->add_render_attribute( 'field-group' . $item_index, [
				'data-piotnetforms-spiner' => '',
			] );
			wp_enqueue_script( $this->slug . '-nice-number-script' );
		}

		$this->add_render_attribute( 'wrapper', 'class', 'piotnetforms-fields-wrapper piotnetforms-labels-above' );

		if ( !empty( $settings['label_animation'] ) ) {
			$this->add_render_attribute( 'wrapper', 'class', 'piotnetforms-label-animation' );
			wp_enqueue_script( $this->slug . '-advanced2-script' );
		}

		if ( !empty( $item['modern_upload_field_style'] ) ) {
			$this->add_render_attribute( 'field-group' . $item_index, [
				'class' => 'piotnetforms-field-type-upload-mordern',
			] );
		} ?>
		
		<div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
			<div <?php echo $this->get_render_attribute_string( 'field-group' . $item_index ); ?>>
				<?php
				if ( !empty( $item['field_label'] ) && 'html' !== $item['field_type'] ) {
					echo '<label ';
					if ( empty( $item['field_label_show'] ) ) {
						echo 'style="display:none" ';
					}
					echo $this->get_render_attribute_string( 'label' . $item_index );
					if ( 'honeypot' == $item['field_type'] ) {
						echo ' data-piotnetforms-honeypot';
					}
					echo '>'. $item['field_label'] .'</label>';
				}
		if ( empty( $settings['field_label_inline'] ) ) {
			echo '<div data-piotnetforms-required></div>';
			$field_inline = '';
		} else {
			$field_inline = ' piotnetforms-field-inline';
		}

		echo '<div class="piotnetforms-field-container'.$field_inline.'">';

		if ( ! empty( $item['field_icon_enable'] ) ) {
			echo '<div class="piotnetforms-field-icon">';
			if ( $item['field_icon_type'] == 'font_awesome' ) {
				if ( ! empty( $item['field_icon_font_awesome'] ) ) {
					wp_enqueue_style( $this->slug . '-fontawesome-style' );
					echo '<i class="' . $item['field_icon_font_awesome'] . '"></i>';
				}
			} else {
				if ( ! empty( $item['field_icon_image'] ) ) {
					echo '<img class="piotnetforms-field-icon-image--normal" src="' . $item['field_icon_image']['url'] . '">';
				}
				if ( ! empty( $item['field_icon_image_focus'] ) ) {
					echo '<img class="piotnetforms-field-icon-image--focus" src="' . $item['field_icon_image_focus']['url'] . '">';
				}
			}
			echo '</div>';
		}

		switch ( $item['field_type'] ) :
			case 'html':
				echo '<div class="piotnetforms-field piotnetforms-size- " data-piotnetforms-html data-piotnetforms-id="' . $item['form_id'] . '" ' . 'id="form-field-' . $item['field_id'] . '" name="form_fields[' .  $item['field_id'] . ']">' . $item['field_html'] . '</div>';
				break;
			case 'textarea':
				echo $this->make_textarea_field( $item, $item_index, $form_id );
				break;

			case 'tinymce':
				wp_enqueue_script( $this->slug . '-tinymce-script' );
				wp_enqueue_script( $this->slug . '-advanced2-script' );
				echo $this->make_textarea_field( $item, $item_index, $form_id, true );
				break;

			case 'select':
				echo $this->make_select_field( $item, $item_index, $form_id );
				break;

			case 'confirm':
				$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-id', $form_id );
				$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-confirm-field', $item['confirm_field_name'] );
				$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-confirm-msg', $item['confirm_error_msg'] );
				echo '<input size="1" ' . $this->get_render_attribute_string( 'input' . $item_index ) . '>';
				break;

			case 'select_autocomplete':
				wp_enqueue_script( $this->slug . '-selectize-script' );
				wp_enqueue_style( $this->slug . '-selectize-style' );
				wp_enqueue_script( $this->slug . '-advanced2-script' );
				echo $this->make_select_field( $item, $item_index, $form_id, false, false, true );
				break;

			case 'image_select':
				wp_enqueue_script( $this->slug . '-image-picker-script' );
				wp_enqueue_style( $this->slug . '-image-picker-style' );
				echo '<div data-piotnetforms-image_select_min_select_check></div>';
				echo $this->make_select_field( $item, $item_index, $form_id, true );
				break;

			case 'terms_select':
				if ( $item['terms_select_type'] == 'select' ) {
					echo $this->make_select_field( $item, $item_index, $form_id, false, true );
				} elseif ( $item['terms_select_type'] == 'select2' ) {
					wp_enqueue_script( $this->slug . '-select2-script' );
					wp_enqueue_script( $this->slug . '-advanced2-script' );
					wp_enqueue_style( $this->slug . '-select2-style' );
					echo $this->make_select_field( $item, $item_index, $form_id, false, true, false, true );
				} else {
					echo $this->make_radio_checkbox_field( $item, $item_index, $item['terms_select_type'], $form_id, true );
				}

				break;

			case 'radio':
			case 'checkbox':
				echo $this->make_radio_checkbox_field( $item, $item_index, $field_type, $form_id );
				break;
			case 'text':
			case 'email':
			case 'url':
			case 'password':
			case 'hidden':
			case 'color':
			case 'iban':
				if ( isset( $item['is_repassword_field'] ) && !empty( $item['field_password_id'] ) ) {
					$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-is-repassword', $item['field_password_id'] );
					$msg_pwd_err = !empty( $item['msg_repassword_err'] ) ? $item['msg_repassword_err'] : "Password don't math";
					$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-repassword-msg', $msg_pwd_err );
				}
				if ( $item['field_type'] == 'iban' ) {
					$iban_mesg = !empty( $settings['iban_invalid_message'] ) ? $settings['iban_invalid_message'] : 'This IBAN is invalid.';
					$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-iban-field' );
					$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-iban-msg', $iban_mesg );
					wp_enqueue_script( $this->slug . '-iban-script' );
				}
				$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-id', $form_id );
				echo '<input size="1" ' . $this->get_render_attribute_string( 'input' . $item_index ) . '>';
				if ( $item['field_type'] == 'password' && !empty( $item['show_icon_password_options'] ) ) {
					echo '<label for="form-field-'.$item['field_id'].'" class="piotnetforms-show-password-icon" data-piotnetforms-show-password-icon="true" data-piotnetforms-field-name="'.$item['field_id'].'"><i id="eye-icon-'.$item['field_id'].'" class="fa fa-eye"></i></label>';
					wp_enqueue_script( $this->slug . '-advanced2-script' );
				}
				break;
			case 'coupon_code':
				$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-id', $form_id );
				$this->remove_render_attribute( 'input' . $item_index, 'type' );
				$this->add_render_attribute( 'input' . $item_index, 'type', 'text' );
				if ( !empty( $item['piotnetforms_coupon_code_list'] ) ) {
					$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-coupon-code-list', json_encode( $item['piotnetforms_coupon_code_list'] ) );
				}
				echo '<input size="1" ' . $this->get_render_attribute_string( 'input' . $item_index ) . '>';
				break;
			case 'honeypot':
				$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-id', $form_id );
				echo '<input size="1" ' . $this->get_render_attribute_string( 'input' . $item_index ) . ' style="display:none !important;">';
				break;
			case 'address_autocomplete':
				$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-id', $form_id );
				$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-address-autocomplete', $form_id );
				$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-address-autocomplete-country', $country );

				$name = $this->get_field_name_shortcode( $this->get_attribute_name( $item ) );
				$value = $this->get_value_edit_post( $name );

				if ( !empty( $value ) ) {
					$this->remove_render_attribute( 'input' . $item_index, 'value' );
					$this->remove_render_attribute( 'input' . $item_index, 'data-piotnetforms-value' );
					$this->add_render_attribute( 'input' . $item_index, 'value', $value['address'] );
					$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-value', $value['address'] );
					$latitude = $value['lat'];
					$longitude = $value['lng'];
					$zoom = $value['zoom'];
				}

				$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-google-maps-lat', $latitude );
				$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-google-maps-lng', $longitude );
				$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-google-maps-formatted-address', '' );
				$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-google-maps-zoom', $zoom );

				echo '<input size="1" ' . $this->get_render_attribute_string( 'input' . $item_index ) . '>';
				if ( ! empty( $item['google_maps'] ) ) {
					echo '<div class="piotnetforms-address-autocomplete-map" style="width: 100%;" data-piotnetforms-address-autocomplete-map></div><div class="infowindow-content"><img src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" width="16" height="16" id="place-icon"><span id="place-name"  class="title"></span><br><span id="place-address"></span></div>';
				}
				if ( empty( esc_attr( get_option( 'piotnetforms-google-maps-api-key' ) ) ) ) {
					echo __( 'Please go to Dashboard > Piotnet Forms > Google Maps Integration > Enter Google Maps API Key > Save Settings', 'piotnetforms' );
				} else {
					wp_enqueue_script( $this->slug . '-google-maps-init-script' );
					wp_enqueue_script( $this->slug . '-google-maps-script' );
				}

				break;
			case 'image_upload':
				$name = $this->get_field_name_shortcode( $this->get_attribute_name( $item ) );
				$value = $this->get_value_edit_post( $name );

				if ( !empty( $value ) ) {
					$images = explode( ',', $value );
					foreach ( $images as $image ) {
						echo '<div class="piotnetforms-image-upload-placeholder piotnetforms-image-upload-uploaded" style="background-image:url('.$image.')" data-piotnetforms-image-upload-placeholder=""><input type="text" style="display:none;" data-piotnetforms-image-upload-item value="'.$image.'"><span class="piotnetforms-image-upload-button piotnetforms-image-upload-button--remove" data-piotnetforms-image-upload-button-remove><i class="fa fa-times" aria-hidden="true"></i></span><span class="piotnetforms-image-upload-button piotnetforms-image-upload-button--uploading" data-piotnetforms-image-upload-button-uploading><i class="fa fa-spinner fa-spin"></i></span></div>';
					}
				}

				echo '<label style="width: 25%" data-piotnetforms-image-upload-label ';
				if ( ! empty( $item['allow_multiple_upload'] ) ) {
					echo 'multiple="multiple"';
				} else {
					if ( !empty( $value ) ) {
						echo ' class="piotnetforms-image-upload-label-hidden" ';
					}
				}

				if ( ! empty( $item['max_files'] ) ) {
					echo 'data-piotnetforms-image-upload-max-files="' . $item['max_files'] . '" ';
				}

				echo '>';
				echo '<input type="file" accept="image/*" name="upload" style="display:none;"';
				if ( ! empty( $item['allow_multiple_upload'] ) ) {
					echo 'multiple="multiple"';
				}
				echo ' data-piotnetforms-image-upload>';
				echo '<div class="piotnetforms-image-upload-placeholder">';
				echo '<span class="piotnetforms-image-upload-button piotnetforms-image-upload-button--add" data-piotnetforms-image-upload-button-add><i class="fa fa-plus" aria-hidden="true"></i></span>';
				echo '<span class="piotnetforms-image-upload-button piotnetforms-image-upload-button--remove" data-piotnetforms-image-upload-button-remove><i class="fa fa-times" aria-hidden="true"></i></span>';
				echo '<span class="piotnetforms-image-upload-button piotnetforms-image-upload-button--uploading" data-piotnetforms-image-upload-button-uploading><i class="fa fa-spinner fa-spin"></i></span>';
				echo '</div>';
				echo '</label>';
				$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-id', $form_id );
				$this->add_render_attribute( 'input' . $item_index, 'data-pafe-field-type', 'image_upload' );
				echo '<div style="display: none">';
				echo '<input type="text" ' . $item_index . ' ' . $this->get_render_attribute_string( 'input' . $item_index ) . '>';
				echo '</div>';

				wp_enqueue_script( $this->slug . '-image-upload-script' );

				break;
			case 'upload':
				//echo "<form action='#' class='piotnetforms-upload' data-piotnetforms-upload enctype='multipart/form-data'>";
				$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-id', $form_id );

				$name = $this->get_field_name_shortcode( $this->get_attribute_name( $item ) );
				$value = $this->get_value_edit_post( $name );

				if ( !empty( $value ) ) {
					wp_enqueue_style( $this->slug . '-fontawesome-style' );
					$attached_file = get_attached_file( $value );
					echo '<div class="piotnetforms-file-uploaded">' . basename( $attached_file ) . ' <i class="fas fa-times" data-piotnetforms-file-uploaded-remove></i></div>';
					$this->add_render_attribute( 'input' . $item_index, 'class', 'piotnetforms-hidden' );
				}

				if ( !empty( $item['modern_upload_field_style'] ) ) {
					$this->add_render_attribute( 'input' . $item_index, 'class', 'piotnetforms-upload-field-modern-style' );
					echo '<div class="piotnetforms-upload-field-modern-text"><i class="fa fa-upload" aria-hidden="true"></i>'.'<span style="padding-left: 8px;">'. $item['modern_upload_field_text'].'</span>' .'</div>';
				}

				$this->remove_render_attribute( 'input' . $item_index, 'value' );
				echo '<input type="file" ' . $this->get_render_attribute_string( 'input' . $item_index ) . '>';
				//echo "</form>";
				wp_enqueue_script( $this->slug . '-advanced2-script' );
				break;
			case 'stripe_payment':
				if ( !empty( $settings['stripe_custom_style_option'] ) && !empty( $settings['stripe_custom_style'] ) ) {
					$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-stripe-custom-style', $foo = preg_replace( '/\s+/', '', $settings['stripe_custom_style'] ) );
				} else {
					$stripe_style = [
						'backgroundColor' => !empty( $settings['stripe_background_color'] ) ? $settings['stripe_background_color'] : '#fff',
						'color' => !empty( $settings['stripe_color'] ) ? $settings['stripe_color'] : '#303238',
						'placeholderColor' => !empty( $settings['stripe_placeholder_color'] ) ? $settings['stripe_placeholder_color'] : '#aab7c4',
						'fontSize' => !empty( $settings['stripe_font_size'] ) ? $settings['stripe_font_size']['size'].'px' : '16px',
						'iconColor' => !empty( $settings['stripe_icon_color'] ) ? $settings['stripe_icon_color'] : ''
					];
					$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-stripe-style', json_encode( $stripe_style ) );
				}
				$stripe_font = !empty( $settings['stripe_custom_font_family'] ) ? $settings['stripe_custom_font_family'] : '';
				$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-form-builder-stripe-font-family', $stripe_font );
				$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-id', $form_id );
				$this->add_render_attribute( 'input' . $item_index, 'class', 'piotnetforms-stripe' );
				$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-stripe', '' );
				echo '<div ' . $this->get_render_attribute_string( 'input' . $item_index ) . '></div><div class="card-errors"></div>';
				echo '<script src="https://js.stripe.com/v3/"></script>';
				wp_enqueue_script( $this->slug . '-stripe-script' );
				break;
			case 'range_slider':
				wp_enqueue_script( $this->slug . '-ion-rangeslider-script' );
				wp_enqueue_style( $this->slug . '-rangeslider-style' );
				$this->add_render_attribute( 'range_slider' . $item_index, 'data-piotnetforms-id', $form_id );
				echo '<input size="1" ' . $this->get_render_attribute_string( 'range_slider' . $item_index ) . '>'; ?>
						<script>
							(function ($) {
								var WidgetpiotnetformsFormBuilderHandlerRangeSlider<?php echo str_replace( '-', '_', $item['field_id'] ); ?> = function ($scope, $) {

								    var $elements = $scope.find('[data-piotnetforms-range-slider]');

									if (!$elements.length) {
										return;
									}

                                    $.each($elements, function (i, $element) {
										let rangerOptions = $(this).attr('data-piotnetforms-range-slider');
										if ($($element).siblings('.irs').length == 0) {
                                            <?php if($this->piotnetforms_is_json($item['piotnetforms_range_slider_field_options'])){ ?>
											    $('#form-field-<?php echo $item['field_id']; ?>').ionRangeSlider(JSON.parse(rangerOptions));
                                            <?php }else{ ?>
                                                $('#form-field-<?php echo $item['field_id']; ?>').ionRangeSlider({
                                                    <?php echo $item['piotnetforms_range_slider_field_options']; ?>
                                                });
                                            <?php } ?>
										}

										$($element).change();
									});

								};

								$(window).on('elementor/frontend/init', function () {
							        elementorFrontend.hooks.addAction('frontend/element_ready/piotnetforms-field.default', WidgetpiotnetformsFormBuilderHandlerRangeSlider<?php echo $item['field_id']; ?>);
							    });

							    $(document).on('piotnet-widget-init-Piotnetforms_Field', '[data-piotnet-editor-widgets-item-root]', function(){
									WidgetpiotnetformsFormBuilderHandlerRangeSlider<?php echo $item['field_id']; ?>($(this), $);
								});

								$(window).on('load', function(){
									WidgetpiotnetformsFormBuilderHandlerRangeSlider<?php echo $item['field_id']; ?>($(document), $);
								});

							}(jQuery)); 
						</script>
					<?php
										break;
			case 'calculated_fields':
				echo '<div class="piotnetforms-calculated-fields-form" style="width: 100%">' . $item['piotnetforms_calculated_fields_form_before'] . '<span class="piotnetforms-calculated-fields-form__value"></span>' . $item['piotnetforms_calculated_fields_form_after'] . '</div>';
				$this->add_render_attribute( 'calculated_fields' . $item_index, 'data-piotnetforms-id', $form_id );

				if ( !empty( $item['piotnetforms_calculated_fields_form_coupon_code'] ) ) {
					$this->add_render_attribute( 'calculated_fields' . $item_index, 'data-piotnetforms-calculated-fields-coupon-code', $item['piotnetforms_calculated_fields_form_coupon_code'] );
				}

				if ( !empty( $item['piotnetforms_calculated_fields_form_distance_calculation'] ) ) {
					$this->add_render_attribute( 'calculated_fields' . $item_index, 'data-piotnetforms-calculated-fields-distance-calculation', '' );
					$this->add_render_attribute( 'calculated_fields' . $item_index, 'data-piotnetforms-calculated-fields-distance-calculation-from-field-shortcode', $item['piotnetforms_calculated_fields_form_distance_calculation_from_field_shortcode'] );
					$this->add_render_attribute( 'calculated_fields' . $item_index, 'data-piotnetforms-calculated-fields-distance-calculation-to-field-shortcode', $item['piotnetforms_calculated_fields_form_distance_calculation_to_field_shortcode'] );
					$this->add_render_attribute( 'calculated_fields' . $item_index, 'data-piotnetforms-calculated-fields-distance-calculation-unit', $item['piotnetforms_calculated_fields_form_distance_calculation_unit'] );

					if ( !empty( $item['piotnetforms_calculated_fields_form_distance_calculation_from_specific_location'] ) ) {
						$this->add_render_attribute( 'calculated_fields' . $item_index, 'data-piotnetforms-calculated-fields-distance-calculation-from', $item['piotnetforms_calculated_fields_form_distance_calculation_from_specific_location'] );
					}

					if ( !empty( $item['piotnetforms_calculated_fields_form_distance_calculation_to_specific_location'] ) ) {
						$this->add_render_attribute( 'calculated_fields' . $item_index, 'data-piotnetforms-calculated-fields-distance-calculation-to', $item['piotnetforms_calculated_fields_form_distance_calculation_to_specific_location'] );
					}
				}

				echo '<input style="display:none!important;" size="1" ' . $this->get_render_attribute_string( 'calculated_fields' . $item_index ) . '>';

				wp_enqueue_script( $this->slug . '-advanced-script' );

				break;
			case 'tel':

				$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-id', $form_id );
				$this->add_render_attribute( 'input' . $item_index, 'pattern', esc_attr( $item['field_pattern'] ) );
				$this->add_render_attribute( 'input' . $item_index, 'title', __( 'Only numbers and phone characters (#, -, *, etc) are accepted.', 'piotnetforms-pro' ) );
				if ( !empty( $settings['field_dial_code'] ) ) {
					$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-tel-field' );
					wp_enqueue_script( $this->slug . '-intl-tel-script' );
					wp_enqueue_script( $this->slug . '-advanced2-script' );
					wp_enqueue_style( $this->slug . '-intl-tel-input-style' );
				}
				echo '<input size="1" '. $this->get_render_attribute_string( 'input' . $item_index ) . '>';

				break;
			case 'number':
				if ( !empty( $settings['field_value_remove'] ) || $settings['field_value_remove'] == '0' ) {
					$remove_value = $settings['field_value_remove'];
				} else {
					$remove_value = 'false';
				}
				$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-id', $form_id );
				$this->add_render_attribute( 'input' . $item_index, 'class', 'piotnetforms-field-textual' );
				$this->add_render_attribute( 'input' . $item_index, 'step', 'any' );
				$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-remove-value', $remove_value );

				if ( ( !empty( $item['field_min'] ) || $item['field_min'] == 0 ) && ( $item['field_min'] != '' ) ) {
					$this->add_render_attribute( 'input' . $item_index, 'min', esc_attr( $item['field_min'] ) );
				}

				if ( ( !empty( $item['field_max'] ) || $item['field_max'] == 0 ) && ( $item['field_max'] != '' ) ) {
					$this->add_render_attribute( 'input' . $item_index, 'max', esc_attr( $item['field_max'] ) );
				}

				echo '<input ' . $this->get_render_attribute_string( 'input' . $item_index ) . '>';
				wp_enqueue_script( $this->slug . '-advanced2-script' );
				break;
			case 'acceptance':
				$label = '';
				$this->add_render_attribute( 'input' . $item_index, 'class', 'piotnetforms-acceptance-field' );
				$this->add_render_attribute( 'input' . $item_index, 'type', 'checkbox', true );
				$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-id', $form_id );
				$this->add_render_attribute( 'input' . $item_index, 'value', 'on' );

				if ( ! empty( $item['acceptance_text'] ) ) {
					$label = '<label for="' . $this->get_attribute_id( $item ) . '">' . $item['acceptance_text'] . '</label>';
				}

				if ( ! empty( $item['checked_by_default'] ) ) {
					$this->add_render_attribute( 'input' . $item_index, 'checked', 'checked' );
				}

				echo '<div class="piotnetforms-field-subgroup"><span class="piotnetforms-field-option"><input ' . $this->get_render_attribute_string( 'input' . $item_index ) . '> ' . $label . '</span></div>';
				break;
			case 'date':
				wp_enqueue_style( $this->slug . '-flatpickr-style' );
				wp_enqueue_script( $this->slug . '-date-time-script' );
				$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-id', $form_id );
				$this->add_render_attribute( 'input' . $item_index, 'class', 'piotnetforms-field-textual piotnetforms-date-field' );

				//$this->add_render_attribute( 'input' . $item_index, 'pattern', '[0-9]{4}-[0-9]{2}-[0-9]{2}' );

				if ( isset( $item['use_native_date'] ) && 'yes' === $item['use_native_date'] ) {
					$this->add_render_attribute( 'input' . $item_index, 'class', 'piotnetforms-use-native' );
				}

				wp_enqueue_script( $this->slug . '-flatpickr-script' );
				if ( $item['date_language'] != 'english' ) {
					// echo "<script src='". plugin_dir_url( __DIR__ ) . 'languages/date/' . $item['date_language'] . ".js'></script>";
					wp_enqueue_script( $this->slug . '-flatpickr-language-' . $item['date_language'], plugin_dir_url( __DIR__ ) . 'languages/date/' . $item['date_language'] . '.js', [ $this->slug . '-flatpickr-script' ], null );
				}

				if ( empty( $item['flatpickr_custom_options_enable'] ) ) {
					if ( ! empty( $item['min_date'] ) && empty( $item['min_date_current'] ) ) {
						$this->add_render_attribute( 'input' . $item_index, 'min', esc_attr( $item['min_date'] ) );
					}

					if ( ! empty( $item['min_date_current'] ) ) {
						$this->add_render_attribute( 'input' . $item_index, 'min', esc_attr( wp_date( 'Y-m-d' ) ) );
					}

					if ( ! empty( $item['max_date'] )  && empty( $item['max_date_current'] ) ) {
						$this->add_render_attribute( 'input' . $item_index, 'max', esc_attr( $item['max_date'] ) );
					}

					if ( ! empty( $item['max_date_current'] ) ) {
						$this->add_render_attribute( 'input' . $item_index, 'max', esc_attr( wp_date( 'Y-m-d' ) ) );
					}

					if ( ! empty( $item['date_range'] ) ) {
						$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-date-range', '' );
						$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-date-range-days', '' );
					} else {
						$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-date-calculate', '' );
					}

					$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-date-language', esc_attr( $item['date_language'] ) );

					$this->add_render_attribute( 'input' . $item_index, 'data-date-format', esc_attr( $item['date_format'] ) );
				} else {
					$this->add_render_attribute( 'input' . $item_index, 'class', 'flatpickr-custom-options' );
					$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-flatpickr-custom-options', esc_attr( $item['flatpickr_custom_options'] ) );
				}

				echo '<input ' . $this->get_render_attribute_string( 'input' . $item_index ) . '>';
				if ( !empty( $item['flatpickr_custom_options_enable'] ) && !empty( $item['flatpickr_custom_options'] ) ) :
					?>
								<script>
									(function ($) {
										
										function piotnetformsDate<?php echo str_replace( '-', '_', $item['field_id'] ); ?>($scope, $) {

										    var $elements = $scope.find('.piotnetforms-date-field');

											if (!$elements.length) {
												return;
											}

											var $elements = $scope.find('#form-field-<?php echo $item['field_id']; ?>');

											if (!$elements.length) {
												return;
											}

											$.each($elements, function (i, $element) {
												$element.flatpickr(<?php echo $item['flatpickr_custom_options']; ?>);
											});

										};

										function waitFlatpickrReady<?php echo str_replace( '-', '_', $item['field_id'] ); ?>() {
										    if (typeof flatpickr === 'undefined') {
										    	setTimeout(function() { waitFlatpickrReady<?php echo str_replace( '-', '_', $item['field_id'] ); ?>() }, 50);
										    } else {
										        piotnetformsDate<?php echo str_replace( '-', '_', $item['field_id'] ); ?>($('.piotnetforms-field-type-date'),$);
										    }
										}
                                        //console.log(1);
										waitFlatpickrReady<?php echo str_replace( '-', '_', $item['field_id'] ); ?>();
									}(jQuery)); 
								</script>
						<?php
				endif;
				break;
			case 'time':
				wp_enqueue_script( $this->slug . '-flatpickr-script' );
				wp_enqueue_style( $this->slug . '-flatpickr-style' );
				wp_enqueue_script( $this->slug . '-date-time-script' );
				$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-id', $form_id );
				$this->add_render_attribute( 'input' . $item_index, 'class', 'piotnetforms-field-textual piotnetforms-time-field' );
				if ( isset( $item['use_native_time'] ) && 'yes' === $item['use_native_time'] ) {
					$this->add_render_attribute( 'input' . $item_index, 'class', 'piotnetforms-use-native' );
				}
				$this->add_render_attribute( 'input' . $item_index, 'data-time-format', esc_attr( $item['time_format'] ) );

				$this->add_render_attribute( 'input' . $item_index, 'data-time-minute-increment', esc_attr( $item['time_minute_increment'] ) );
				if ( ! empty( $item['time_24hr'] ) ) {
					$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-time-24hr', '' );
				}
				echo '<input ' . $this->get_render_attribute_string( 'input' . $item_index ) . '>';
				break;
			case 'signature':
				wp_enqueue_script( $this->slug . '-signature-pad-script' );
				echo '<div class="piotnetforms-signature" data-piotnetforms-signature><canvas class="not-resize"';
				if ( !empty( $item['signature_max_width_responsive_desktop'] ) ) : echo ' width="' . $item['signature_max_width_responsive_desktop']['size'] . '"';
				endif;
				if ( !empty( $item['signature_height_responsive_desktop'] ) ) : echo ' height="' . $item['signature_height_responsive_desktop']['size'] . '"';
				endif;
				echo '></canvas>';
				$this->add_render_attribute( 'input' . $item_index, 'data-piotnetforms-id', $form_id );
				echo '<input ' . $this->get_render_attribute_string( 'input' . $item_index ) . '>';
				echo '<div>';
				echo '<button type="button" class="piotnetforms-signature-clear" data-piotnetforms-signature-clear>' . $item['signature_clear_text'] . '</button>';
				echo '<button type="button" class="piotnetforms-signature-export" data-piotnetforms-signature-export style="display:none"></button>';
				echo '</div>';
				echo '</div>';
				break;
			default:
				$field_type = $item['field_type'];
		endswitch;

		echo '</div>';

		if ( !empty( $settings['field_description'] ) ) {
			echo '<div class="piotnetforms-field-description">' . $settings['field_description'] . '</div>';
		}

		if ( !empty( $settings['field_label_inline'] ) ) {
			echo '<div data-piotnetforms-required></div>';
			$label_inline_width = '';
		} ?>
			</div>
		</div>
	<?php
	}

	public function piotnetforms_is_json($string){
		json_decode($string);
		return json_last_error() === JSON_ERROR_NONE;
	}

	public function live_preview() {
		?>
			<%
				view.add_attribute('wrapper', 'class', 'piotnetforms-fields-wrapper piotnetforms-labels-above');
				var s = data.widget_settings;
				var country = s['country'] ? s['country']: '';
				var latitude = s['google_maps_lat'] ? s['google_maps_lat'] : '';
		        var longitude = s['google_maps_lng'] ? s['google_maps_lng'] : '';
				var zoom = s['google_maps_zoom'] ? s['google_maps_zoom']['size'] : '';

				function replaceAll(str, find, replace) {
			    	return str.replace(new RegExp(find, 'g'), replace);
			    }

				function get_attribute_name( s ) {
					return 'form_fields[' + s.field_id + ']';
				}

				function get_attribute_id( s ) {
					return 'form-field-' + s.field_id;
				}

				function add_required_attribute( element, view ) {
					view.add_attribute( element, 'required', 'required' );
					view.add_attribute( element, 'aria-required', 'true' );
				}

				function form_fields_render_attributes( view, s ) {
					var box_flex = s.field_label_inline ? 'piotnetforms-column-flex' : '';
					view.add_attribute('field-group', 'class', [
						'piotnetforms-field-type-' + s.field_type,
						'piotnetforms-field-group',
						'piotnetforms-column',
						box_flex,
						'piotnetforms-field-group-' + s.field_id,
					]);

					view.add_attribute('input', 'class', [
						 'piotnetforms-field'
                    ]);

					view.add_multi_attribute({
						range_slider: {
							type: "text",
							id: get_attribute_id( s ),
							name: get_attribute_name( s ),
							class: [
								'piotnetforms-field',
							],
							"data-piotnetforms-range-slider": s.piotnetforms_range_slider_field_options,
						}
					});

					view.add_multi_attribute({
						calculated_fields: {
							type: "text",
							id: get_attribute_id( s ),
							name: get_attribute_name( s ),
							class: [
								'piotnetforms-field',
							],
							'data-piotnetforms-calculated-fields': s['piotnetforms_calculated_fields_form_calculation'],
							'data-piotnetforms-calculated-fields-before': s['piotnetforms_calculated_fields_form_before'],
							'data-piotnetforms-calculated-fields-after': s['piotnetforms_calculated_fields_form_after'],
							'data-piotnetforms-calculated-fields-rounding-decimals': s['piotnetforms_calculated_fields_form_calculation_rounding_decimals'],
							'data-piotnetforms-calculated-fields-rounding-decimals-decimals-symbol': s['piotnetforms_calculated_fields_form_calculation_rounding_decimals_decimals_symbol'],
							'data-piotnetforms-calculated-fields-rounding-decimals-seperators-symbol': s['piotnetforms_calculated_fields_form_calculation_rounding_decimals_seperators_symbol'],
							'data-piotnetforms-calculated-fields-rounding-decimals-show': s['piotnetforms_calculated_fields_form_calculation_rounding_decimals_show'],
						},
						'label': {
							'for': get_attribute_id( s ),
							'class': 'piotnetforms-field-label',
						}
					});


					if ( s['field_type'] === 'honeypot' ) {
						view.add_multi_attribute({
							input: {
								'type': 'text',
								'name': 'form_fields[honeypot]',
								'id': 'form-field-honeypot',
							}
						});
					} else if ( s['field_type'] == 'address_autocomplete' ) {
						view.add_multi_attribute({
							input: {
								type: 'text',
								name: get_attribute_name( s ),
								id: get_attribute_id( s ),
							}
						});
					} else {
						view.add_multi_attribute({
							input: {
								type: s['field_type'] != 'confirm' ? s['field_type'] : s['confirm_type'],
								name: get_attribute_name( s ),
								id: get_attribute_id( s ),
							}
						});
					}

					if ( !s['width'] ) {
						s['width'] = '100';
					}

					view.add_attribute('field-group', 'class', 'piotnetforms-col-' + s['width']);

					if ( s['width_tablet'] ) {
						view.add_attribute( 'field-group', 'class', 'piotnetforms-md-' + s['width_tablet'] );
					}

					if ( s['allow_multiple'] ) {
						view.add_attribute( 'field-group', 'class', 'piotnetforms-field-type-' + s['field_type'] + '-multiple' );
					}

					if ( s['width_mobile'] ) {
						view.add_attribute( 'field-group', 'class', 'piotnetforms-sm-' + s['width_mobile'] );
					}

					if ( s['field_placeholder'] ) {
						view.add_attribute( 'input', 'placeholder', s['field_placeholder'] );
					}

					if ( s['max_length'] ) {
						view.add_attribute( 'input', 'maxlength', s['max_length'] );
					}

					if ( s['min_length'] ) {
						view.add_attribute( 'input', 'minlength', s['min_length'] );
					}

					if ( s['field_pattern_not_tel'] && s['field_type'] !== 'tel' ) {
						view.add_attribute( 'input', 'pattern', s['field_pattern_not_tel'] );
					}

					if ( s['input_mask_enable'] ) {
						if ( s['input_mask'] ) {
							view.add_attribute( 'input', 'data-mask', s['input_mask'] );
						}
						if ( s['input_mask_reverse'] ) {
							view.add_attribute( 'input', 'data-mask-reverse', 'true' );
						}
					}

					if ( s['invalid_message'] ) {
						view.add_attribute( 'input', 'oninvalid', "this.setCustomValidity('" + s['invalid_message'] + "')" );
						view.add_attribute( 'input', 'onchange', "this.setCustomValidity('')" );
					}

					if ( s['field_autocomplete'] ) {
						view.add_attribute( 'input', 'autocomplete', 'on' );
					} else {
						view.add_attribute( 'input', 'autocomplete', 'off' );
					}

					var value = '';

					if ( !value ) {
						value = s['field_value'];
						view.add_attribute( 'input', 'data-piotnetforms-default-value', s['field_value'] );
					}

					if ( value || value === 0 ) {
						view.add_attribute( 'input', 'value', value );
						view.add_attribute( 'range_slider', 'value', value );
						view.add_attribute( 'input', 'data-piotnetforms-value', value );
					}

					if ( s['field_required'] ) {
						var field_class = 'piotnetforms-field-required';
						if ( s['mark_required'] ) {
							field_class += ' piotnetforms-mark-required';
						}
						view.add_attribute( 'field-group', 'class', field_class );
						add_required_attribute( 'input', view );
					}

					if ( s['allow_multiple_upload'] ) {
						view.add_attribute( 'input', 'multiple', 'multiple' );
					}

					if ( s['field_type'] === 'upload' ) {
						view.add_attribute( 'input', 'name', 'upload_field', true );
						if ( s['attach_files'] ) {
							view.add_attribute( 'input', 'data-attach-files', '', true );
						}

						if ( s['file_sizes'] ) {

							view.add_multi_attribute({
								input: {
									'data-maxsize': s['file_sizes'],
									'data-maxsize-message': s['file_sizes_message'],
								}
							});
						}

						if ( s['file_types'] ) {
							var file_types   = s['file_types'].split(',');
							var file_accepts = [ 'jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'ppt', 'pptx', 'odt', 'avi', 'ogg', 'm4a', 'mov', 'mp3', 'mp4', 'mpg', 'wav', 'wmv', 'zip', 'xls', 'xlsx' ];

							if ( Array.isArray(file_types) ) {
								var file_types_output = '';

								for ( i=0; i < file_types.length; i++ ) {
									file_types_output += '.' + file_types[i];
									if( i < (file_types.length - 1)) {
										file_types_output += ',';
									} 
								}

								view.add_attribute( 'input', 'data-accept', file_types_output.replace(/\./g, "") );
							}

							view.add_attribute( 'input', 'data-types-message', s['file_types_message'] );
						}
					}

					if( s['number_spiner'] && s['field_type'] === 'number' ) {
						view.add_attribute( 'field-group', 'data-piotnetforms-spiner', '' );
					}

					if ( s['remove_this_field_from_repeater'] ) {
						view.add_attribute( 'input', 'data-piotnetforms-remove-this-field-from-repeater', '', true );
						view.add_attribute( 'range_slider', 'data-piotnetforms-remove-this-field-from-repeater', '', true );
						view.add_attribute( 'calculated_fields', 'data-piotnetforms-remove-this-field-from-repeater', '', true );
					}

					return view;

				}

				function make_textarea_field( s, tinymce ) {

					view.add_multi_attribute({
						textarea: {
							class: [
								'piotnetforms-field-textual',
								'piotnetforms-field',
							],
							name: get_attribute_name( s ),
							id: get_attribute_id( s ),
							rows: s['rows'],
						}
					});

					if ( s['field_placeholder'] ) {
						view.add_attribute( 'textarea', 'placeholder', s['field_placeholder'] );
					}

					if ( tinymce ) {
						view.add_attribute( 'textarea', 'data-piotnetforms-tinymce', '' );
						view.add_attribute( 'textarea', 'placeholder', 'This feature only works on the frontend' );
					}

					if ( s['field_required'] ) {
						add_required_attribute( 'textarea', view );
					}

					if ( s['max_length'] ) {
						view.add_attribute( 'textarea', 'maxlength', s['max_length'] );
					}
					
					if ( s['min_length'] ) {
						view.add_attribute( 'textarea', 'minlength', s['min_length'] );
					}

					if ( s['field_pattern_not_tel'] ) {
						view.add_attribute( 'textarea', 'pattern', s['field_pattern_not_tel'] );
					}

					if ( s['remove_this_field_from_repeater'] ) {
						view.add_attribute( 'textarea', 'data-piotnetforms-remove-this-field-from-repeater', '' );
					}

					var value = '';

					if ( !value ) {
						value = s['field_value'];
						view.add_attribute( 'textarea', 'data-piotnetforms-default-value', s['field_value'] );
					}

					view.add_attribute( 'textarea', 'data-piotnetforms-id', s['form_id'] );

					return {view: view, value: value};
				}

				view = form_fields_render_attributes( view, s );

				function make_select_field( view, s, image_select, terms_select, select_autocomplete ) {

					var name = get_attribute_name( s );

					if (s['allow_multiple']) {
						name += '[]';
					}

					view.add_multi_attribute({
						'select-wrapper': {
							'class' : [
								'piotnetforms-field',
								'piotnetforms-select-wrapper',
							],
						},
						select: {
							'class' : [
								'piotnetforms-field-textual',
							],
							name: name,
							id: get_attribute_id( s ),
						}
					});

					if ( image_select ) {
						var list = s['piotnetforms_image_select_field_gallery'];
						var limit_multiple = s['limit_multiple'];
						if ( list ) {
							view.add_multi_attribute({
								select: {
									'data-piotnetforms-image-select': JSON.stringify(list),
								}
							});

							if ( limit_multiple ) {
								view.add_multi_attribute({
									select: {
										'data-piotnetforms-image-select-limit-multiple': limit_multiple,
									}
								});
							}
						}
					}

					if ( s['field_required'] ) {
						add_required_attribute( 'select', view );
					}

					if ( s['allow_multiple'] ) {
						view.add_attribute( 'select', 'multiple', '' );
						if ( s['select_size'] ) {
							view.add_attribute( 'select', 'size', s['select_size'] );
						}
					}

					if ( s['send_data_by_label'] ) {
						view.add_attribute( 'select', 'data-piotnetforms-send-data-by-label', '' );
					}

					if ( s['invalid_message'] ) {
						view.add_attribute( 'select', 'oninvalid', "this.setCustomValidity('" + s['invalid_message'] + "')" );
						view.add_attribute( 'select', 'onchange', "this.setCustomValidity('')" );
					}

					if ( s['remove_this_field_from_repeater'] ) {
						view.add_attribute( 'select', 'data-piotnetforms-remove-this-field-from-repeater', '' );
					}

					if ( s['multi_step_form_autonext'] ) {
						view.add_attribute( 'select', 'data-piotnetforms-multi-step-form-autonext', '' );
					}

					if ( s['payment_methods_select_field_enable'] ) {
						view.add_attribute( 'select', 'data-piotnetforms-payment-methods-select-field', '' );
						view.add_attribute( 'select', 'data-piotnetforms-payment-methods-select-field-value-for-stripe', s['payment_methods_select_field_value_for_stripe'] );
						view.add_attribute( 'select', 'data-piotnetforms-payment-methods-select-field-value-for-paypal', s['payment_methods_select_field_value_for_paypal'] );
					}

					var options = s['field_options'] ? s['field_options'].split('\n') : [];

					if ( terms_select ) {
						if ( s['field_taxonomy_slug'] ) {
							options = [];
							var terms_select_option = 'This feature only work on the frontend|This feature only work on the frontend';
							options.push(terms_select_option);
						}
					}

					if ( select_autocomplete ) {
						view.add_attribute( 'select', 'data-piotnetforms-select-autocomplete', '' );
					}

					view.add_attribute( 'select', 'data-piotnetforms-id', s['form_id'] );
					view.add_attribute( 'select', 'data-piotnetforms-default-value', s['field_value'] );
					
					return {view: view, options: options};
				}

				if(s['label_animation']) {
					view.add_attribute( 'wrapper', 'class', 'piotnetforms-label-animation' );
				}
				var label_inline = s.field_label_inline != undefined ? ' piotnetforms-label-inline' : '';
			%>
			
			<div <%= view.render_attributes('wrapper') %>>
				<div <%= view.render_attributes('field-group') %>>
					<% if(s.field_label && s.field_type !== 'html') { %>
						<label <% if(!s.field_label_show) { %>style="display:none" <% } %> class="piotnetforms-field-label<%= label_inline %>"<% if(s.field_type == 'honeypot') { %> data-piotnetforms-honeypot<% } %>><%= s.field_label %></label>
					<% } %>
					<% if(!s.field_label_inline){ %>
					<div data-piotnetforms-required></div>
						<%
							var field_inline = '';
						%>
					<% }else{
						var field_inline = ' piotnetforms-field-inline';
					} %>
					<div class="piotnetforms-field-container<%= field_inline %>">
						<% if(s.field_icon_enable) { %>
							<div class="piotnetforms-field-icon">
							<% if(s.field_icon_type === 'font_awesome') { %>
								<% if(s.field_icon_font_awesome) { %>
									<i class="<%= s.field_icon_font_awesome %>"></i>
								<% } %>
							<% } else { %>
								<% if(s.field_icon_image) { %>
									<img class="piotnetforms-field-icon-image--normal" src="<%= s.field_icon_image.url %>">
								<% } %>
								<% if(s.field_icon_image_focus) { %>
									<img class="piotnetforms-field-icon-image--focus" src="<%= s.field_icon_image_focus.url %>">
								<% } %>
							<% } %>
							</div>
						<% } %>
						<%
							switch ( s['field_type'] ) {
								case 'html':
						%>
								<div class="piotnetforms-field piotnetforms-size- " data-piotnetforms-html data-piotnetforms-id="<%= s['form_id'] %>" id="form-field-<%= s['field_id'] %>" name="form_fields[<%= s['field_id'] %>]"><%= s['field_html'] %></div>
						<%
									break;
								case 'textarea':
									var textarea = make_textarea_field( s, false );
									view = textarea.view;
						%>
									<textarea <%= view.render_attributes('textarea') %>><%= textarea.value %></textarea>
						<%			
									break;
								case 'tinymce':
									var tinymce = make_textarea_field( s, true );
									view = tinymce.view;
						%>			
									<textarea <%= view.render_attributes('textarea') %>><%= tinymce.value %></textarea>
						<%
									break;
								case 'confirm':
									view.add_attribute( 'input', 'data-piotnetforms-id', s.form_id );
						%>
								<input size="1" <%= view.render_attributes('input') %>>
						<%
									break;
								case 'select':
								case 'select_autocomplete':
								case 'image_select':
								case 'terms_select':

									var image_select = s['field_type'] === 'image_select' ? true : false,
										terms_select = s['field_type'] === 'terms_select' ? true : false,
										select_autocomplete = s['field_type'] === 'select_autocomplete' ? true : false;

									var select = make_select_field( view, s, image_select, terms_select, select_autocomplete );
									var options = select.options;
									view = select.view;

									if ( s['field-type'] === 'terms_select' && s['terms_select_type'] !== 'select') {
										break;
									}
						%>
									<div <%= view.render_attributes('select-wrapper') %>>
										<select <%= view.render_attributes('select') %> data-options='<%= JSON.stringify(options) %>'>
											<%
												if ( select_autocomplete && s['field_placeholder'] ) {
													options.unshift( s['field_placeholder'] + '|' + '' );
												}

												for ( i = 0; i < options.length; i++ ) {
													var option = options[i],
														option_id = 'option_' + i,
														option_value = option,
														option_label = option;

													if ( option.includes("|") ) {
														var option_array = option.split('|'),
															option_value = option_array[1].trim(),
															option_label = option_array[0].trim();
													}

													view.add_attribute( option_id, 'value', option_value );

													value = s['field_value'];

													if ( value && option_value === value ) {
														view.add_attribute( option_id, 'selected', 'selected' );
													}

													if ( ! select_autocomplete && value !== undefined ) {
														var values = value.split(',');
														if (values) {
															for ( j = 0; j < options.length; j++ ) {
																if ( option_value === options[j] ) {
																	view.add_attribute( option_id, 'selected', 'selected' );
																}
															}
														}
													}

													if ( s['send_data_by_label'] ) {
														view.add_attribute( option_id, 'data-piotnetforms-send-data-by-label', option_label );
													}

													if ( s['remove_this_field_from_repeater'] ) {
														view.add_attribute( option_id, 'data-piotnetforms-remove-this-field-from-repeater', option_label );
													}

													if ( i == ( options.length - 1 ) && option_value === '' ) {
														
													} else {
														if ( option_value.includes('[optgroup') ) {
															var optgroup = replaceAll(option_value, '\\]', '');
															optgroup = replaceAll(optgroup, '\\[', '');
															optgroup = replaceAll(optgroup, 'optgroup label=', '');
															optgroup = replaceAll(optgroup, '&quot;', '');
															console.log(optgroup);
															%>
																<optgroup label=<%= optgroup %>>
															<%
														} else if ( option_value.includes('[/optgroup]') ) {
															%>
																</optgroup>
															<%
														} else {
															%>
																<option <%= view.render_attributes(option_id) %>><%= option_label %></option>
															<%
														}
													}
												}
											%>
										</select>
									</div>
						<%
									break;
								case 'radio':
								case 'checkbox':

									var options = s['field_options'] ? s['field_options'].split('\n') : [];

									if ( s['piotnetforms_style_checkbox_type'] === 'button' ) {
										if ( s['checkbox_style_button_with_icon_list'] ) {
											if ( s['checkbox_style_button_with_icon_list'].length > 0 ) {
												var options = [];
												var options_list = s['checkbox_style_button_with_icon_list'];
												for ( i = 0; i < options_list.length; i++ ) {
													if (options_list[i]['value'] && options_list[i]['label']) {
														options.push(options_list[i]['label'] + '|' + options_list[i]['value']); 
													} else {
														if (options_list[i]['label']) {
															options.push(options_list[i]['label']);
														} else {
															options.push(options_list[i]['value']);
														}
													}
												}
											}
										}
									}

									if ( terms_select ) {
										if ( s['field_taxonomy_slug'] ) {
											options = [];
											var terms_select_option = 'This feature only work on the frontend|This feature only work on the frontend';
											options.push(terms_select_option);
										}
									}

									if ( options ) {
										var checkbox_replacement_class = s['piotnetforms_style_checkbox_replacement'] && s['piotnetforms_style_checkbox_type'] == 'native' ? ' piotnetforms-field-subgroup--checkbox-replacement' : '';
								%>
									<form>
										<div class="piotnetforms-field-subgroup piotnetforms-field-subgroup--<%= s['piotnetforms_style_checkbox_type'] %><%= checkbox_replacement_class %> <%= s['inline_list'] %>">
										<%
										for ( i = 0; i < options.length; i++ ) {
											if ( options[i] ) {
											view.remove_group_attribute( 'element_id' );
											var option = options[i];
											var element_id = s['field_id'] + i;
											var html_id = get_attribute_id( s ) + '-' + i;
											var option_label = option;
											var option_value = option;

											if ( option.includes("|") ) {
												var option_array = option.split('|'),
													option_value = option_array[1],
													option_label = option_array[0];
											}

											var name = get_attribute_name( s );
											if ( s['field_type'] === 'checkbox' && options.length > 1 ) {
												name += '[]';
											} 

											view.add_multi_attribute({
												element_id: {
													'type'       : s['field_type'],
													'value'      : option_value,
													'data-value' : option_value,
													'id'         : html_id,
													'name'       : name,
												}
											});

											value = s['field_value'];
											view.add_attribute( element_id, 'data-piotnetforms-default-value', value );

											if ( s['invalid_message'] ) {
												if ( i === 1 ) {
													view.add_attribute( element_id, 'oninvalid', "this.setCustomValidity('" + s['invalid_message'] + "')" );
													view.add_attribute( element_id, 'onchange', "this.setCustomValidity('')" );
												} else {
													view.add_attribute( element_id, 'onclick', 'clearValidity(this)' );
													view.add_attribute( element_id, 'oninvalid', "this.setCustomValidity('" + s['invalid_message'] + "')" );
													view.add_attribute( element_id, 'onchange', "this.setCustomValidity('')" );
												}
											}

											if ( s['payment_methods_select_field_enable'] ) {
												view.add_attribute( element_id, 'data-piotnetforms-payment-methods-select-field', '' );
												view.add_attribute( element_id, 'data-piotnetforms-payment-methods-select-field-value-for-stripe', s['payment_methods_select_field_value_for_stripe'] );
												view.add_attribute( element_id, 'data-piotnetforms-payment-methods-select-field-value-for-paypal', s['payment_methods_select_field_value_for_paypal'] );
											}

											if ( value && option_value === value ) {
												view.add_attribute( element_id, 'checked', 'checked' );
												view.add_attribute( element_id, 'data-checked', 'checked' );
											}

											var values = value ? value.split(',') : [];
											for ( j = 0; j < values.length; j++ ) {
												if ( option_value === values[j] ) {
													view.add_attribute( element_id, 'checked', 'checked' );
													view.add_attribute( element_id, 'data-checked', 'checked' );
												}
											}

											if ( s['send_data_by_label'] ) {
												view.add_attribute( element_id, 'data-piotnetforms-send-data-by-label', option_label );
											}

											if ( s['remove_this_field_from_repeater'] ) {
												view.add_attribute( element_id, 'data-piotnetforms-remove-this-field-from-repeater', option_label );
											}

											if ( s['field_required'] && 'radio' === s['field_type'] ) {
												add_required_attribute( element_id, view );
											}

											if ( s['multi_step_form_autonext'] && 'radio' === s['field_type'] ) {
												view.add_attribute( element_id, 'data-piotnetforms-multi-step-form-autonext', '' );
											}

											view.add_attribute( element_id, 'data-piotnetforms-id', s['form_id'] );

											var icon_html = '';

											if ( s['piotnetforms_style_checkbox_replacement'] && s['piotnetforms_style_checkbox_type'] == 'native' ) {
												if ( s['piotnetforms_style_checkbox_replacement_icon_type'] == 'font_awesome' ) {
													if (s['piotnetforms_style_checkbox_replacement_icon_font_awesome'] || s['piotnetforms_style_checkbox_replacement_icon_font_awesome_checked']) {
														var icon_html_wrapper_class = s['piotnetforms_style_checkbox_replacement_icon_font_awesome_checked'] ? 'pf-f-o-i pf-f-o-i-has-checked' : 'pf-f-o-i';

														if (s['piotnetforms_style_checkbox_replacement_icon_font_awesome_checked'] && !s['piotnetforms_style_checkbox_replacement_icon_font_awesome']) {
															icon_html_wrapper_class += ' pf-f-o-i--only-checked';
														}

														var icon_html = '<span class="' + icon_html_wrapper_class + '">';

														if (s['piotnetforms_style_checkbox_replacement_icon_font_awesome']) {
															icon_html += '<i class="pf-f-o-i-font-awesome pf-f-o-i-font-awesome--normal ' + s['piotnetforms_style_checkbox_replacement_icon_font_awesome'] + '"></i>';
														}

														if (s['piotnetforms_style_checkbox_replacement_icon_font_awesome_checked']) {
															icon_html += '<i class="pf-f-o-i-font-awesome pf-f-o-i-font-awesome--checked ' + s['piotnetforms_style_checkbox_replacement_icon_font_awesome_checked'] + '"></i>';
														}

														icon_html += '</span>';
													}
												} else {
													if (s['piotnetforms_style_checkbox_replacement_icon_image'] || s['piotnetforms_style_checkbox_replacement_icon_image_checked']) {
														var icon_html_wrapper_class = s['piotnetforms_style_checkbox_replacement_icon_image_checked'] ? 'pf-f-o-i pf-f-o-i-has-checked' : 'pf-f-o-i';

														if (s['piotnetforms_style_checkbox_replacement_icon_image_checked'] && !s['piotnetforms_style_checkbox_replacement_icon_image']) {
															icon_html_wrapper_class += ' pf-f-o-i--only-checked';
														}

														var icon_html = '<span class="' + icon_html_wrapper_class + '">';

														if (s['piotnetforms_style_checkbox_replacement_icon_image']) {
															icon_html += '<img class="pf-f-o-i-image pf-f-o-i-image--normal" src="' + s['piotnetforms_style_checkbox_replacement_icon_image']['url'] + '">';
														}

														if (s['piotnetforms_style_checkbox_replacement_icon_image_checked']) {
															icon_html += '<img class="pf-f-o-i-image pf-f-o-i-image--checked" src="' + s['piotnetforms_style_checkbox_replacement_icon_image_checked']['url'] + '">';
														}

														icon_html += '</span>';
													}
												}
											}

											if ( s['checkbox_style_button_with_icon_list'] && s['piotnetforms_style_checkbox_type'] == 'button' ) {
												var icon_list = s['checkbox_style_button_with_icon_list'];
												if ( icon_list.length > 0 ) {
													if ( icon_list[i]['icon_type'] == 'font_awesome' ) {
														if (icon_list[i]['icon_font_awesome'] || icon_list[i]['icon_font_awesome_checked']) {
															var icon_html_wrapper_class = icon_list[i]['icon_font_awesome_checked'] ? 'pf-f-o-i pf-f-o-i-has-checked' : 'pf-f-o-i';

															if (icon_list[i]['icon_font_awesome_checked'] && !icon_list[i]['icon_font_awesome']) {
																icon_html_wrapper_class += ' pf-f-o-i--only-checked';
															}

															var icon_html = '<span class="' + icon_html_wrapper_class + '">';

															if (icon_list[i]['icon_font_awesome']) {
																icon_html += '<i class="pf-f-o-i-font-awesome pf-f-o-i-font-awesome--normal ' + icon_list[i]['icon_font_awesome'] + '"></i>';
															}

															if (icon_list[i]['icon_font_awesome_checked']) {
																icon_html += '<i class="pf-f-o-i-font-awesome pf-f-o-i-font-awesome--checked ' + icon_list[i]['icon_font_awesome_checked'] + '"></i>';
															}

															icon_html += '</span>';
														}
													} else {
														if (icon_list[i]['icon_image'] || icon_list[i]['icon_image_checked']) {
															var icon_html_wrapper_class = icon_list[i]['icon_image_checked'] ? 'pf-f-o-i pf-f-o-i-has-checked' : 'pf-f-o-i';

															if (icon_list[i]['icon_image_checked'] && !icon_list[i]['icon_image']) {
																icon_html_wrapper_class += ' pf-f-o-i--only-checked';
															}

															var icon_html = '<span class="' + icon_html_wrapper_class + '">';

															if (icon_list[i]['icon_image']) {
																icon_html += '<img class="pf-f-o-i-image pf-f-o-i-image--normal" src="' + icon_list[i]['icon_image']['url'] + '">';
															}

															if (icon_list[i]['icon_image_checked']) {
																icon_html += '<img class="pf-f-o-i-image pf-f-o-i-image--checked" src="' + icon_list[i]['icon_image_checked']['url'] + '">';
															}

															icon_html += '</span>';
														}
													}
												}
											}

											var icon_on_top_class = s['piotnetforms_style_checkbox_button_icon_on_top'] ? 'piotnetforms-field-icon-on-top' : '';
										%>
											<span class="piotnetforms-field-option <%= icon_on_top_class %>">
												<input <%= view.render_attributes('element_id') %>> <label for="<%= html_id %>" class="<%= icon_on_top_class %>"><%= icon_html %><%= option_label %></label>
											</span>
										<%
										} }
										%>
											</div>
										</form>
										<%
									}
									break;
								case 'text':
								case 'email':
								case 'url':
								case 'password':
								case 'hidden':
								case 'color':
									view.add_attribute( 'input', 'data-piotnetforms-id', s.form_id );
						%>
							<input size="1" <%= view.render_attributes('input') %>>
						<%
									break;
								case 'coupon_code':
									view.add_attribute( 'input', 'data-piotnetforms-id', s.form_id );
									view.remove_attribute( 'input', 'type' );
									view.add_attribute( 'input', 'type', 'text' );

									if ( s['piotnetforms_coupon_code_list'] ) {
										view.add_attribute( 'input', 'data-piotnetforms-coupon-code-list', JSON.stringify(s['piotnetforms_coupon_code_list']) );
									}
								%>
									<input size="1" <%= view.render_attributes('input') %>>
								<%
									break;
								case 'honeypot':
									view.add_attribute( 'input', 'data-piotnetforms-id', s.form_id );
								%>
									<input size="1" <%= view.render_attributes('input') %> style="display:none !important;">
								<%
									break;
								case 'address_autocomplete':
									view.add_attribute( 'input', 'data-piotnetforms-id', s.form_id );
									view.add_attribute( 'input', 'data-piotnetforms-address-autocomplete', s.form_id );
									view.add_attribute( 'input', 'data-piotnetforms-address-autocomplete-country', country );
									view.add_attribute( 'input', 'data-piotnetforms-google-maps-lat', latitude );
									view.add_attribute( 'input', 'data-piotnetforms-google-maps-lng', longitude );
									view.add_attribute( 'input', 'data-piotnetforms-google-maps-formatted-address', '' );
									view.add_attribute( 'input', 'data-piotnetforms-google-maps-zoom', zoom );
									view.add_attribute( 'input', 'placeholder', 'This feature only works on the frontend' );
								%>
									<input size="1" <%= view.render_attributes('input') %>>
								<%
									if ( s['google_maps'] ) {
								%>
									<div class="piotnetforms-address-autocomplete-map" style="width: 100%;" data-piotnetforms-address-autocomplete-map></div><div class="infowindow-content"><img src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" width="16" height="16" id="place-icon"><span id="place-name"  class="title"></span><br><span id="place-address"></span></div>
								<%
									}
								%>
								<?php
									if ( empty( esc_attr( get_option( 'piotnetforms-google-maps-api-key' ) ) ) ) {
										echo __( 'Please go to Dashboard > Piotnet Forms > Google Maps Integration > Enter Google Maps API Key > Save Settings', 'piotnetforms' );
									} ?>
								<%
									break;
								case 'image_upload':
									view.add_attribute( 'input', 'data-piotnetforms-id', s.form_id );
								%>
									<label style="width: 25%" data-piotnetforms-image-upload-label <% if ( s['allow_multiple_upload'] ) { %>multiple="multiple"<% } if ( s['max_files'] ) { %> data-piotnetforms-image-upload-max-files="<%= s['max_files'] %>"<% } %>>
									<input type="file" accept="image/*" name="upload" style="display:none;" <% if ( s['allow_multiple_upload'] ) { %>multiple="multiple"<% } %> data-piotnetforms-image-upload>
									<div class="piotnetforms-image-upload-placeholder">
									<span class="piotnetforms-image-upload-button piotnetforms-image-upload-button--add" data-piotnetforms-image-upload-button-add><i class="fa fa-plus" aria-hidden="true"></i></span>
									<span class="piotnetforms-image-upload-button piotnetforms-image-upload-button--remove" data-piotnetforms-image-upload-button-remove><i class="fa fa-times" aria-hidden="true"></i></span>
									<span class="piotnetforms-image-upload-button piotnetforms-image-upload-button--uploading" data-piotnetforms-image-upload-button-uploading><i class="fa fa-spinner fa-spin"></i></span>
									</div>
									</label>
									<div style="display: none">
										<input type="text" <%= view.render_attributes('input') %>>
									</div>
								<%
									break;
								case 'upload':
								%>
									<form action='#' class='piotnetforms-upload' data-piotnetforms-upload enctype='multipart/form-data'>
								<%	
									view.add_attribute( 'input', 'data-piotnetforms-id', s.form_id );
                                    if ( s['modern_upload_field_style'] ) {
                                        view.add_attribute( 'input', 'class', 'piotnetforms-upload-field-modern-style' );
                                %>
                                        <div class="piotnetforms-upload-field-modern-text"><i class="fa fa-upload" aria-hidden="true"><span style="padding-left: 8px;"><%= s['modern_upload_field_text'] %></span></i></div>
                                <%
                                    }
								%>
									<input type="file" <%= view.render_attributes('input') %>>
									</form>
								<%
									break;
								case 'stripe_payment':
									view.add_attribute( 'input', 'data-piotnetforms-id', s.form_id );
									view.add_attribute( 'input', 'class', 'piotnetforms-stripe' );
									view.add_attribute( 'input', 'data-piotnetforms-stripe', '' );
								%>
									<div <%= view.render_attributes('input') %>></div><div class="card-errors"></div>
								<%	
									break;
								case 'range_slider':
									view.add_attribute( 'range_slider', 'data-piotnetforms-id', s.form_id );
								%>
									<input size="1" <%= view.render_attributes('range_slider') %>>	
								<%
									break;
								case 'calculated_fields':
								%>
									<div class="piotnetforms-calculated-fields-form" style="width: 100%"><%= s['piotnetforms_calculated_fields_form_before'] %><span class="piotnetforms-calculated-fields-form__value"></span><%= s['piotnetforms_calculated_fields_form_after'] %></div>
								<%
									view.add_attribute( 'calculated_fields', 'data-piotnetforms-id', s.form_id );
									
									if ( s['piotnetforms_calculated_fields_form_coupon_code'] ) {
										view.add_attribute( 'calculated_fields', 'data-piotnetforms-calculated-fields-coupon-code', s['piotnetforms_calculated_fields_form_coupon_code'] );
									}

									if ( s['piotnetforms_calculated_fields_form_distance_calculation'] ) {
										view.add_attribute( 'calculated_fields', 'data-piotnetforms-calculated-fields-distance-calculation', '' );

										view.add_attribute( 'calculated_fields', 'data-piotnetforms-calculated-fields-distance-calculation-from-field-shortcode', s['piotnetforms_calculated_fields_form_distance_calculation_from_field_shortcode'] );

										view.add_attribute( 'calculated_fields', 'data-piotnetforms-calculated-fields-distance-calculation-to-field-shortcode', s['piotnetforms_calculated_fields_form_distance_calculation_to_field_shortcode'] );

										view.add_attribute( 'calculated_fields', 'data-piotnetforms-calculated-fields-distance-calculation-unit', s['piotnetforms_calculated_fields_form_distance_calculation_unit'] );

										if ( s['piotnetforms_calculated_fields_form_distance_calculation_from_specific_location'] ) {
											view.add_attribute( 'calculated_fields', 'data-piotnetforms-calculated-fields-distance-calculation-from', s['piotnetforms_calculated_fields_form_distance_calculation_from_specific_location'] );
										}

										if ( s['piotnetforms_calculated_fields_form_distance_calculation_to_specific_location'] ) {
											view.add_attribute( 'calculated_fields', 'data-piotnetforms-calculated-fields-distance-calculation-to', s['piotnetforms_calculated_fields_form_distance_calculation_to_specific_location'] );
										}
									}

									%>
										<input style="display:none!important;" size="1" <%= view.render_attributes('calculated_fields') %>>
									<%
									break;
								case 'tel':
									view.add_attribute( 'input', 'data-piotnetforms-id', s.form_id );
									view.add_attribute( 'input', 'pattern', s['field_pattern'] );
									view.add_attribute( 'input', 'title', '<?php echo __( 'Only numbers and phone characters (#, -, *, etc) are accepted.', 'piotnetforms' ); ?>' );
								%>
									<input size="1" <%= view.render_attributes('input') %>>
								<%	
									break;
								case 'number':
									view.add_attribute( 'input', 'data-piotnetforms-id', s.form_id );
									view.add_attribute( 'input', 'class', 'piotnetforms-field-textual' );
									view.add_attribute( 'input', 'step', 'any' );

									if ( s['field_min'] || s['field_min'] === 0 ) {
										view.add_attribute( 'input', 'min', s['field_min'] );
									}

									if ( s['field_max'] || s['field_max'] === 0 ) {
										view.add_attribute( 'input', 'max', s['field_max'] );
									}
								%>
									<input <%= view.render_attributes('input') %>>
								<%	
									break;
								case 'acceptance':
									var label = '';
									view.add_attribute( 'input', 'class', 'piotnetforms-acceptance-field' );
									view.remove_attribute( 'input', 'type' );
									view.add_attribute( 'input', 'type', 'checkbox' );
									view.add_attribute( 'input', 'data-piotnetforms-id', s.form_id );
									view.add_attribute( 'input', 'value', 'on' );

									if ( s['checked_by_default'] ) {
										view.add_attribute( 'input', 'checked', 'checked' );
									}
								%>
									<div class="piotnetforms-field-subgroup"><span class="piotnetforms-field-option"><input <%= view.render_attributes('input') %>><% if ( s['acceptance_text'] ) { %><label for="<%= get_attribute_id( s ) %>"><%= s['acceptance_text'] %></label><% } %></span></div>
								<%
									break;
								case 'date':
									view.add_attribute( 'input', 'data-piotnetforms-id', s.form_id );
									view.add_attribute( 'input', 'class', 'piotnetforms-field-textual piotnetforms-date-field' );
									view.remove_attribute( 'input', 'type' );
									view.add_attribute( 'input', 'type', 'text' );

									if ( s['use_native_date'] && 'yes' === s['use_native_date'] ) {
										view.add_attribute( 'input', 'class', 'piotnetforms-use-native' );
									}

									if ( !s['flatpickr_custom_options_enable'] ) {

										if ( s['min_date'] && !s['min_date_current'] ) {
											view.add_attribute( 'input', 'min', s['min_date'] );
										}

										if ( s['min_date_current'] ) {
											view.add_attribute( 'input', 'min', '<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>' );
										}

										if ( s['max_date'] && !s['max_date_current'] ) {
											view.add_attribute( 'input', 'max', s['max_date'] );
										}

										if ( s['max_date_current'] ) {
											view.add_attribute( 'input', 'max', '<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>' );
										}

										if ( s['date_range'] ) {
											view.add_attribute( 'input', 'data-piotnetforms-date-range', '' );
											view.add_attribute( 'input', 'data-piotnetforms-date-range-days', '' );
										} else {
											view.add_attribute( 'input', 'data-piotnetforms-date-calculate', '' );
										}

										view.add_attribute( 'input', 'data-piotnetforms-date-language', s['date_language'] );

										view.add_attribute( 'input', 'data-date-format', s['date_format'] );

									} else {
										view.add_attribute( 'input', 'class', 'flatpickr-custom-options' );
									}
								%>
									<input <%= view.render_attributes('input') %>>
								<%
									break;
								case 'time':
									view.add_attribute( 'input', 'data-piotnetforms-id', s.form_id );
									view.add_attribute( 'input', 'class', 'piotnetforms-field-textual piotnetforms-time-field' );
									view.remove_attribute( 'input', 'type' );
									view.add_attribute( 'input', 'type', 'text' );

									if ( s['use_native_time'] && 'yes' === s['use_native_time'] ) {
										view.add_attribute( 'input', 'class', 'piotnetforms-use-native' );
									}
									view.add_attribute( 'input', 'data-time-format', s['time_format'] );

									if ( s['time_24hr'] ) {
										view.add_attribute( 'input', 'data-piotnetforms-time-24hr', '' );
									}
								%>
									<input <%= view.render_attributes('input') %>>
								<%
									break;
								case 'signature':
									view.add_attribute( 'input', 'data-piotnetforms-id', s.form_id );
								%>
									<div class="piotnetforms-signature" data-piotnetforms-signature><canvas class="not-resize"<% if (s['signature_max_width_responsive_desktop']) { %> width="<%= s['signature_max_width_responsive_desktop']['size'] %>"<% } %><% if (s['signature_height_responsive_desktop']) { %> height="<%= s['signature_height_responsive_desktop']['size'] %>"<% } %>></canvas>
									<input <%= view.render_attributes('input') %>>
									<div>
									<button type="button" class="piotnetforms-signature-clear" data-piotnetforms-signature-clear><%= s['signature_clear_text'] %></button>
									<button type="button" class="piotnetforms-signature-export" data-piotnetforms-signature-export style="display:none"></button>
									</div>
									</div>
								<%
									break;

								default:
							}
						%>
					</div>
					<% if(s.field_description){ %>
						<div class="piotnetforms-field-description"><%= s.field_description %></div>
					<% } %>
					<% if(s.field_label_inline){ %>
						<div data-piotnetforms-required></div>
					<% } %>
				</div>
			</div>
		<?php
	}
}
