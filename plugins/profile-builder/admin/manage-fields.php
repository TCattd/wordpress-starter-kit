<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
// Define the default values.
define( 'WPPB_DEFAULTS_MAP_LAT', 48 );
define( 'WPPB_DEFAULTS_MAP_LNG', 12 );
define( 'WPPB_DEFAULTS_MAP_ZOOM', 4 );

/**
 * Function that creates the Manage Fields submenu and populates it with a repeater field form
 *
 * @since v.2.0
 *
 * @return void
 */
function wppb_manage_fields_submenu(){
	// create a new sub_menu page which holds the data for the default + extra fields
	$args = array(
		'menu_title' => __('Form Fields', 'profile-builder'),
		'page_title' => __('Manage Form Fields', 'profile-builder'),
		'menu_slug' => 'manage-fields',
		'page_type' => 'submenu_page',
		'capability' => 'manage_options',
		'priority' => 5,
		'parent_slug' => 'profile-builder'
	);
	$manage_fields_page = new WCK_Page_Creator_PB($args);
}
add_action( 'admin_menu', 'wppb_manage_fields_submenu', 1 );

function wppb_populate_manage_fields(){

    $manage_field_types = array(
        'optgroups' => array(
            'default' =>
                array(
                    'label' 	=> __('Default', 'profile-builder'),
                    'options'	=> array(
                        'Default - Name (Heading)',
                        'Default - Contact Info (Heading)',
                        'Default - About Yourself (Heading)',
                        'Default - Username',
                        'Default - First Name',
                        'Default - Last Name',
                        'Default - Nickname',
                        'Default - E-mail',
                        'Default - Website',
                        'Default - Password',
                        'Default - Repeat Password',
                        'Default - Biographical Info',
                        'Default - Display name publicly as',
                    ),
            ),
            'standard' =>
                array(
                    'label'		=> __('Standard', 'profile-builder'),
                    'options'	=> array(),
                ),
            'advanced' =>
                array(
                    'label'		=> __('Advanced', 'profile-builder'),
                    'options'	=> array(),
                ),
            'other' =>
                array(
                    'label'		=> __('Other', 'profile-builder'),
                    'options'	=> array(
                        'GDPR Checkbox', // since 2.8.2
                        'GDPR Delete Button', // since 3.0.1
                    ),
                ),
        ),
    );

    // Default contact methods were removed in WP 3.6. A filter dictates contact methods.
    if ( apply_filters( 'wppb_remove_default_contact_methods', get_site_option( 'initial_db_version' ) < 23588 ) ){
        $manage_field_types['optgroups']['default']['options'][] = 'Default - AIM';
        $manage_field_types['optgroups']['default']['options'][] = 'Default - Yahoo IM';
        $manage_field_types['optgroups']['default']['options'][] = 'Default - Jabber / Google Talk';
    }

    if ( wppb_can_users_signup_blog() ) {
        $manage_field_types['optgroups']['advanced']['options'][] = 'Default - Blog Details';
    }

    if( PROFILE_BUILDER != 'Profile Builder Free' ) {
        $manage_field_types['optgroups']['standard']['options'][] = 'Heading';
        $manage_field_types['optgroups']['standard']['options'][] = 'Input';
        $manage_field_types['optgroups']['standard']['options'][] = 'Number';
        $manage_field_types['optgroups']['standard']['options'][] = 'Input (Hidden)';
        $manage_field_types['optgroups']['standard']['options'][] = 'Language';
        $manage_field_types['optgroups']['standard']['options'][] = 'Textarea';
        $manage_field_types['optgroups']['standard']['options'][] = 'WYSIWYG';
        $manage_field_types['optgroups']['standard']['options'][] = 'Select';
        $manage_field_types['optgroups']['standard']['options'][] = 'Select (Multiple)';
        $manage_field_types['optgroups']['standard']['options'][] = 'Checkbox';
        $manage_field_types['optgroups']['standard']['options'][] = 'Radio';
        $manage_field_types['optgroups']['standard']['options'][] = 'HTML';
        $manage_field_types['optgroups']['standard']['options'][] = 'Upload';
        $manage_field_types['optgroups']['standard']['options'][] = 'Avatar';

        $manage_field_types['optgroups']['advanced']['options'][] = 'Phone';
        $manage_field_types['optgroups']['advanced']['options'][] = 'Select (Country)';
        $manage_field_types['optgroups']['advanced']['options'][] = 'Select (Timezone)';
        $manage_field_types['optgroups']['advanced']['options'][] = 'Select (Currency)';
        $manage_field_types['optgroups']['advanced']['options'][] = 'Select (CPT)';
        $manage_field_types['optgroups']['advanced']['options'][] = 'Checkbox (Terms and Conditions)';
        $manage_field_types['optgroups']['advanced']['options'][] = 'Datepicker';
        $manage_field_types['optgroups']['advanced']['options'][] = 'Timepicker';
        $manage_field_types['optgroups']['advanced']['options'][] = 'Colorpicker';
        $manage_field_types['optgroups']['advanced']['options'][] = 'Validation';
        $manage_field_types['optgroups']['advanced']['options'][] = 'Map';

		$manage_field_types['optgroups']['other']['options'][] = 'Email';
		$manage_field_types['optgroups']['other']['options'][] = 'URL';

        $manage_field_types['optgroups']['other']['options'][] = 'Select2';
        $manage_field_types['optgroups']['other']['options'][] = 'Select2 (Multiple)';

        $manage_field_types['optgroups']['other']['options'][] = 'Honeypot';
    }

    $manage_field_types['optgroups']['other']['options'][] = 'Email Confirmation';

    /* added recaptcha and user role field since version 2.6.2 */
    $manage_field_types['optgroups']['advanced']['options'][] = 'reCAPTCHA';
    $manage_field_types['optgroups']['advanced']['options'][] = 'Select (User Role)';


    $manage_field_types['optgroups']['other']['options'] = apply_filters( 'wppb_manage_fields_types', $manage_field_types['optgroups']['other']['options'] );

    $manage_field_types = apply_filters( 'wppb_all_manage_fields_types', $manage_field_types );

	//Free to Pro call to action on Manage Fields page
	$field_description = __('Choose one of the supported field types','profile-builder');
	if( PROFILE_BUILDER == 'Profile Builder Free' ) {
		$field_description .= sprintf( __('. Extra Field Types are available in <a href="%s">Basic or PRO versions</a>.' , 'profile-builder'), esc_url( 'https://www.cozmoslabs.com/wordpress-profile-builder/?utm_source=wpbackend&utm_medium=clientsite&utm_content=manage-fields-link&utm_campaign=PBFree' ) );
	}

    //user roles
    global $wp_roles;

	// @TODO - The block below could use refactoring, see new function wppb_prepare_key_value_options.
    $user_roles = array();
    foreach( $wp_roles->roles as $user_role_slug => $user_role )
        if( $user_role_slug !== 'administrator' )
            array_push( $user_roles, '%' . wppb_prepare_wck_labels( $user_role['name'] ) . '%' . $user_role_slug );


	// @TODO - The block below could use refactoring, see new function wppb_prepare_key_value_options.
	// country select
	$default_country_array = wppb_country_select_options( 'back_end' );
	foreach( $default_country_array as $iso_country_code => $country_name ) {
		$default_country_options[] = '%'. wppb_prepare_wck_labels( $country_name ) .'%'.$iso_country_code;
	}

	// @TODO - The block below could use refactoring, see new function wppb_prepare_key_value_options.
    // currency select
    $default_currency_array = wppb_get_currencies( 'back_end' );
    array_unshift( $default_currency_array, '' );
    foreach( $default_currency_array as $iso_currency_code => $currency_name ) {
        $default_currency_options[]  = '%'. wppb_prepare_wck_labels( $currency_name ) .'%'.$iso_currency_code;
    }

	//cpt select
	$post_types = get_post_types( array( 'public'   => true ), 'names' );


	if( apply_filters( 'wppb_update_field_meta_key_in_db', false ) ) {
		$meta_key_description = __( 'Use this in conjunction with WordPress functions to display the value in the page of your choosing<br/>Auto-completed but in some cases editable (in which case it must be unique)<br/>Changing this might take long in case of a very big user-count', 'profile-builder' );
	}
	else{
		$meta_key_description = __( 'Use this in conjunction with WordPress functions to display the value in the page of your choosing<br/>Auto-completed but in some cases editable (in which case it must be unique)<br/>Changing this will only affect subsequent entries', 'profile-builder' );
	}

	// Initiate the list of available tags for the pin bubble.
    if( function_exists( 'wppb_generate_userlisting_merge_tags' ) ) {
        $bubble_meta_tags = wppb_generate_userlisting_merge_tags('meta');
        $bubble_meta_tags = wp_list_pluck($bubble_meta_tags, 'label', 'name');
    }
    else
        $bubble_meta_tags = array();

	// set up the fields array
	$fields = apply_filters( 'wppb_manage_fields', array(

        array( 'type' => 'text', 'slug' => 'field-title', 'title' => __( 'Field Title', 'profile-builder' ), 'description' => __( 'Title of the field', 'profile-builder' ) ),
        array( 'type' => 'select', 'slug' => 'field', 'title' => __( 'Field', 'profile-builder' ), 'options' => apply_filters( 'wppb_manage_fields_types_options', $manage_field_types ), 'default-option' => true, 'description' => $field_description ),
        array( 'type' => 'text', 'slug' => 'meta-name', 'title' => __( 'Meta-name', 'profile-builder' ), 'default' => wppb_get_meta_name(), 'description' => $meta_key_description ),
        array( 'type' => 'text', 'slug' => 'id', 'title' => __( 'ID', 'profile-builder' ), 'default' => wppb_get_unique_id(), 'description' => __( "A unique, auto-generated ID for this particular field<br/>You can use this in conjuction with filters to target this element if needed<br/>Can't be edited", 'profile-builder' ), 'readonly' => true ),
        array( 'type' => 'textarea', 'slug' => 'description', 'title' => __( 'Description', 'profile-builder' ), 'description' => __( 'Enter a (detailed) description of the option for end users to read<br/>Optional', 'profile-builder') ),
        array( 'type' => 'text', 'slug' => 'row-count', 'title' => __( 'Row Count', 'profile-builder' ), 'default' => 5, 'description' => __( "Specify the number of rows for a 'Textarea' field<br/>If not specified, defaults to 5", 'profile-builder' ) ),
        array( 'type' => 'text', 'slug' => 'allowed-image-extensions', 'title' => __( 'Allowed Image Extensions', 'profile-builder' ), 'default' => '.*', 'description' => __( 'Specify the extension(s) you want to limit to upload<br/>Example: .ext1,.ext2,.ext3<br/>If not specified, defaults to: .jpg,.jpeg,.gif,.png (.*)', 'profile-builder' ) ),
        array( 'type' => 'checkbox', 'slug' => 'simple-upload', 'title' => __( 'Use Simple Upload', 'profile-builder' ), 'options' => array( '%'.__('Yes','profile-builder').'%'.'yes' ), 'description' => __( 'Use a simple upload field instead of the WordPress upload', 'profile-builder' ) ),
        array( 'type' => 'text', 'slug' => 'allowed-upload-extensions', 'title' => __( 'Allowed Upload Extensions', 'profile-builder' ), 'default' => '.*', 'description' => __( 'Specify the extension(s) you want to limit to upload<br/>Example: .ext1,.ext2,.ext3<br/>If not specified, defaults to all WordPress allowed file extensions (.*)', 'profile-builder' ) ),
        array( 'type' => 'text', 'slug' => 'avatar-size', 'title' => __( 'Avatar Size', 'profile-builder' ), 'default' => 100, 'description' => __( "Enter a value (between 20 and 200) for the size of the 'Avatar'<br/>If not specified, defaults to 100", 'profile-builder' ) ),
        array( 'type' => 'text', 'slug' => 'date-format', 'title' => __( 'Date-format', 'profile-builder' ), 'default' => 'mm/dd/yy', 'description' => __( 'Specify the format of the date when using Datepicker<br/>Valid options: mm/dd/yy, mm/yy/dd, dd/yy/mm, dd/mm/yy, yy/dd/mm, yy/mm/dd, mm-dd-yy, yy-mm-dd, D, dd M yy, D, d M y, DD, dd-M-y, D, d M yy, mm/yy, mm/dd, dd/mm, @<br/>If not specified, defaults to mm/dd/yy<br/>ATTENTION: if you plan to use this field for sorting, please make sure to use year first, then month, and day last.', 'profile-builder' ) ),
        array( 'type' => 'textarea', 'slug' => 'terms-of-agreement', 'title' => __( 'Terms of Agreement', 'profile-builder' ), 'description' => __( 'Enter a detailed description of the terms of agreement for the user to read.<br/>Links can be inserted by using standard HTML syntax: &lt;a href="custom_url"&gt;custom_text&lt;/a&gt;', 'profile-builder' ) ),
        array( 'type' => 'text', 'slug' => 'options', 'title' => __( 'Options', 'profile-builder' ), 'description' => __( "Enter a comma separated list of values<br/>This can be anything, as it is hidden from the user, but should not contain special characters or apostrophes", 'profile-builder' ) ),
        array( 'type' => 'text', 'slug' => 'labels', 'title' => __( 'Labels', 'profile-builder' ), 'description' => __( "Enter a comma separated list of labels<br/>Visible for the user", 'profile-builder' ) ),
        array( 'type' => 'select', 'slug' => 'recaptcha-type', 'title' => __( 'reCAPTCHA Type', 'profile-builder' ), 'options' => array('%reCAPTCHA V2%v2', '%Invisible reCAPTCHA%invisible'), 'default' => 'v2', 'description' => __( 'Choose the <a href="https://developers.google.com/recaptcha/docs/versions" target="_blank">type of reCAPTCHA</a> you wish to add to this site.<br/>Please note that the Invisible reCAPTCHA is a type of reCAPTCHA v2.', 'profile-builder' ) ),
        array( 'type' => 'text', 'slug' => 'public-key', 'title' => __( 'Site Key', 'profile-builder' ), 'description' => __( 'The site key from Google, <a href="https://www.google.com/recaptcha/admin/create" target="_blank">https://www.google.com/recaptcha/admin/create</a>', 'profile-builder' ) ),
        array( 'type' => 'text', 'slug' => 'private-key', 'title' => __( 'Secret Key', 'profile-builder' ), 'description' => __( 'The secret key from Google, <a href="https://www.google.com/recaptcha/admin/create" target="_blank">https://www.google.com/recaptcha/admin/create</a>', 'profile-builder' ) ),
        array( 'type' => 'checkbox', 'slug' => 'captcha-pb-forms', 'title' => __( 'Display on PB forms', 'profile-builder' ), 'options' => array( '%'.__('PB Login','profile-builder').'%'.'pb_login', '%'.__('PB Register','profile-builder').'%'.'pb_register', '%'.__('PB Recover Password','profile-builder').'%'.'pb_recover_password' ), 'default' => 'pb_register', 'description' => __( "Select on which Profile Builder forms to display reCAPTCHA", 'profile-builder' ) ),
        array( 'type' => 'checkbox', 'slug' => 'captcha-wp-forms', 'title' => __( 'Display on default WP forms', 'profile-builder' ), 'options' => array( '%'.__('Default WP Login', 'profile-builder').'%'.'default_wp_login', '%'.__('Default WP Register', 'profile-builder').'%'.'default_wp_register', '%'.__('Default WP Recover Password', 'profile-builder').'%'.'default_wp_recover_password'), 'default' => 'default_wp_register', 'description' => __( "Select on which default WP forms to display reCAPTCHA", 'profile-builder' ) ),
        array( 'type' => 'checkbox', 'slug' => 'user-roles', 'title' => __( 'User Roles', 'profile-builder' ), 'options' => $user_roles, 'description' => __( "Select which user roles to show to the user ( drag and drop to re-order )", 'profile-builder' ) ),
        array( 'type' => 'checkbox', 'slug' => 'user-roles-on-edit-profile', 'title' => __( 'Display on Edit Profile', 'profile-builder' ), 'options' => array('%Yes%yes'), 'description' => __( "Check if you want the select user role field to appear on Edit Profile forms", 'profile-builder' ) ),
        array( 'type' => 'text', 'slug' => 'user-roles-sort-order', 'title' => __( 'User Roles Order', 'profile-builder' ), 'description' => __( "Save the user role order from the user roles checkboxes", 'profile-builder' ) ),
        array( 'type' => 'text', 'slug' => 'default-value', 'title' => __( 'Default Value', 'profile-builder' ), 'description' => __( "Default value of the field", 'profile-builder' ) ),
        array( 'type' => 'text', 'slug' => 'default-option', 'title' => __( 'Default Option', 'profile-builder' ), 'description' => __( "Specify the option which should be selected by default", 'profile-builder' ) ),
        array( 'type' => 'text', 'slug' => 'default-options', 'title' => __( 'Default Option(s)', 'profile-builder' ), 'description' => __( "Specify the option which should be checked by default<br/>If there are multiple values, separate them with a ',' (comma)", 'profile-builder' ) ),
		array( 'type' => 'select', 'slug' => 'default-option-country', 'title' => __( 'Default Option', 'profile-builder' ), 'options' => ( isset( $default_country_options ) ) ? $default_country_options : '', 'description' => __( "Default option of the field", 'profile-builder' ) ),
		array( 'type' => 'select', 'slug' => 'default-option-timezone', 'title' => __( 'Default Option', 'profile-builder' ), 'options' => wppb_timezone_select_options( 'back_end' ), 'description' => __( "Default option of the field", 'profile-builder' ) ),
        array( 'type' => 'select', 'slug' => 'default-option-currency', 'title' => __( 'Default Option', 'profile-builder' ), 'options' => ( isset( $default_currency_options ) ) ? $default_currency_options : '', 'description' => __( "Default option of the field", 'profile-builder' ) ),
        array( 'type' => 'select', 'slug' => 'show-currency-symbol', 'title' => __( 'Show Currency Symbol', 'profile-builder' ), 'options' => array( 'No', 'Yes' ), 'default' => 'No', 'description' => __( 'Whether the currency symbol should be displayed after the currency name in the select option.', 'profile-builder' ) ),
        array( 'type' => 'select', 'slug' => 'cpt', 'title' => __( 'Show Post Type', 'profile-builder' ), 'options' => $post_types, 'default' => 'post', 'description' => __( 'Posts from what post type will be displayed in the select.', 'profile-builder' ) ),
        array( 'type' => 'text', 'slug' => 'validation-possible-values', 'title' => __( 'Allowable Values', 'profile-builder' ), 'description' => __( "Enter a comma separated list of possible values. Upon registration if the value provided by the user does not match one of these values, the user will not be registered.", 'profile-builder' ) ),
        array( 'type' => 'text', 'slug' => 'custom-error-message', 'title' => __( 'Error Message', 'profile-builder' ), 'description' => __( "Set a custom error message that will be displayed to the user.", 'profile-builder' ) ),
        array( 'type' => 'select', 'slug' => 'time-format', 'title' => __( 'Time Format', 'profile-builder' ), 'options' => array( '%12 Hours%12', '%24 Hours%24' ), 'description' => __( 'Specify the time format.', 'profile-builder' ) ),
        array( 'type' => 'text', 'slug' => 'map-api-key', 'title' => __( 'Google Maps API Key', 'profile-builder' ), 'description' => __( 'Enter your Google Maps API key ( <a href="https://console.developers.google.com/flows/enableapi?apiid=maps_backend" target="_blank">Get your API key</a> ). If more than one map fields are added to a form the API key from the first map displayed will be used.', 'profile-builder' ) ),

		array(
			'type'        => 'text',
			'slug'        => 'map-default-lat',
			'title'       => __( 'Default Latitude', 'profile-builder' ),
			'description' => __( 'The latitude at which the map should be displayed when no pins are attached.', 'profile-builder' ),
			'default'     => WPPB_DEFAULTS_MAP_LAT,
		),
		array(
			'type'        => 'text',
			'slug'        => 'map-default-lng',
			'title'       => __( 'Default Longitude', 'profile-builder' ),
			'description' => __( 'The longitude at which the map should be displayed when no pins are attached.', 'profile-builder' ),
			'default'     => WPPB_DEFAULTS_MAP_LNG,
		),
		array(
			'type'        => 'text',
			'slug'        => 'map-default-zoom',
			'title'       => __( 'Default Zoom Level', 'profile-builder' ),
			'description' => __( 'Add a number from 0 to 19. The higher the number the higher the zoom.', 'profile-builder' ),
			'default'     => WPPB_DEFAULTS_MAP_ZOOM,
		),

        array( 'type' => 'text', 'slug' => 'map-height', 'title' => __( 'Map Height', 'profile-builder' ), 'description' => __( "The height of the map.", 'profile-builder' ), 'default' => 400 ),
		array( 'type' => 'textarea', 'slug' => 'default-content', 'title' => __( 'Default Content', 'profile-builder' ), 'description' => __( "Default value of the textarea", 'profile-builder' ) ),
		array( 'type' => 'textarea', 'slug' => 'html-content', 'title' => __( 'HTML Content', 'profile-builder' ), 'description' => __( "Add your HTML (or text) content", 'profile-builder' ) ),
		array( 'type' => 'text', 'slug' => 'phone-format', 'title' => __( 'Phone Format', 'profile-builder' ), 'default' => '(###) ###-####', 'description' => __( "You can use: # for numbers, parentheses ( ), - sign, + sign, dot . and spaces.", 'profile-builder' ) .'<br>'.  __( "Eg. (###) ###-####", 'profile-builder' ) .'<br>'. __( "Empty field won't check for correct phone number.", 'profile-builder' ) ),
		array( 'type' => 'select', 'slug' => 'heading-tag', 'title' => __( 'Heading Tag', 'profile-builder' ), 'options' => array( '%h1 - biggest size%h1', 'h2', 'h3', 'h4', 'h5', '%h6 - smallest size%h6' ), 'default' => 'h4', 'description' => __( 'Change heading field size on front-end forms', 'profile-builder' ) ),
		array( 'type' => 'text', 'slug' => 'min-number-value', 'title' => __( 'Min Number Value', 'profile-builder' ), 'description' => __( "Min allowed number value (0 to allow only positive numbers)", 'profile-builder' ) .'<br>'. __( "Leave it empty for no min value", 'profile-builder' ) ),
		array( 'type' => 'text', 'slug' => 'max-number-value', 'title' => __( 'Max Number Value', 'profile-builder' ), 'description' => __( "Max allowed number value (0 to allow only negative numbers)", 'profile-builder' ) .'<br>'. __( "Leave it empty for no max value", 'profile-builder' ) ),
		array( 'type' => 'text', 'slug' => 'number-step-value', 'title' => __( 'Number Step Value', 'profile-builder' ), 'description' => __( "Step value 1 to allow only integers, 0.1 to allow integers and numbers with 1 decimal", 'profile-builder' ) .'<br>'. __( "To allow multiple decimals use for eg. 0.01 (for 2 deciamls) and so on", 'profile-builder' ) .'<br>'. __( "You can also use step value to specify the legal number intervals (eg. step value 2 will allow only -4, -2, 0, 2 and so on)", 'profile-builder' ) .'<br>'. __( "Leave it empty for no restriction", 'profile-builder' ) ),
		array( 'type' => 'select', 'slug' => 'required', 'title' => __( 'Required', 'profile-builder' ), 'options' => array( 'No', 'Yes' ), 'default' => 'No', 'description' => __( 'Whether the field is required or not', 'profile-builder' ) ),
        array( 'type' => 'select', 'slug' => 'overwrite-existing', 'title' => __( 'Overwrite Existing', 'profile-builder' ), 'options' => array( 'No', 'Yes' ), 'default' => 'No', 'description' => __( "Selecting 'Yes' will add the field to the list, but will overwrite any other field in the database that has the same meta-name<br/>Use this at your own risk", 'profile-builder' ) ),

		// Added the new option for the map field type, that allows to customize the POIs load type.
		array(
			'type'        => 'select',
			'slug'        => 'map-pins-load-type',
			'title'       => __( 'POIs Load Type', 'profile-builder' ),
			'options'     => array(
				'%' . __( 'POIs of the listed users (as filtered & paginated)', 'profile-builder' ) . '%',
				'%' . __( 'POIs of all the users for the filter* (no pagination)', 'profile-builder' ) . '%all',
			),
			'default'     => '',
			'description' => __( 'This option allows you to load on a single map the POIs for all users, or just these for the listed ones (this will take into account the filters and the faceted menus). *Please use this feature wisely, it will impact the performance.', 'profile-builder' ),
		),

		// Added the new option for the map field type, that allows to customize the POI bubble content.
		array(
			'type'        => 'checkbox',
			'slug'        => 'map-bubble-fields',
			'title'       => __( 'POI Bubble Info', 'profile-builder' ),
			'options'     => wppb_prepare_key_value_options( apply_filters( 'wppb_map_bubble_fields', $bubble_meta_tags ) ),
			'default'     => 'avatar_or_gravatar, meta_display_name',
			'description' => __( 'Select the attributes to be listed inside the POI bubble.', 'profile-builder' ),
			'extra_attributes' => array(
				'dropdown_options' => true,
				'sortable_options' => true,
			),
		),

		// Added the new option for the map field type, that allows to customize the number of users per iteration.
		array(
			'type'        => 'text',
			'slug'        => 'map-pagination-number',
			'title'       => __( 'Number of Users per Map Iteration', 'profile-builder' ),
			'description' => __( 'When loading the map of all users with no pagination, the map script will iterate multiple times and will expose gradually POIs on the map, until all the POIs for the users that match the criteria will be added on the map (think of this as of pagination for the map POIs). The smaller the number of users per iteration, the fastest the iteration response will be, but for a large number of users, the map script will iterate multiple times. Setting a higher limit will decrease the performance, but might produce a smaller number of iterations. <br><br><b>Please adjust this value to your hosting capabilities, and make sure that the value you set is the best for performance.</b> We recommend a <b>maximum</b> value of 300.', 'profile-builder' ),
			'default'     => 50,
		),

        //add Select 2 attributes
        array( 'type' => 'text', 'slug' => 'select2-multiple-limit', 'title' => __( 'Maximum Selections', 'profile-builder' ), 'description' => __( "Select2 multi-value select boxes can set restrictions regarding the maximum number of options selected.", 'profile-builder' ) ),
        array( 'type' => 'checkbox', 'slug' => 'select2-multiple-tags', 'title' => __( 'User Inputted Options', 'profile-builder' ), 'options' => array( '%Enable user inputted options%yes' ), 'description' => __( "Check this to allow users to create their own options beside the pre-existing ones.", 'profile-builder' ) ),

) );

	// create the new submenu with the above options
	$args = array(
		'metabox_id' 	=> 'manage-fields',
		'metabox_title' => __( 'Form Field Properties', 'profile-builder' ),
		'post_type' 	=> 'manage-fields',
		'meta_name' 	=> 'wppb_manage_fields',
		'meta_array' 	=> $fields,
		'context'		=> 'option'
		);
	new Wordpress_Creation_Kit_PB( $args );

	/* this is redundant but it should have a very low impact and for comfort we leave it here as well  */
    wppb_prepopulate_fields();

    // create the info side meta-box
    $args = array(
        'metabox_id' 	=> 'manage-fields-info',
        'metabox_title' => __( 'Registration & Edit Profile Forms', 'profile-builder' ),
        'post_type' 	=> 'manage-fields',
        'meta_name' 	=> 'wppb_manage_fields_info',
        'meta_array' 	=> '',
        'context'		=> 'option',
        'mb_context'    => 'side'
    );
    new Wordpress_Creation_Kit_PB( $args );
}
add_action( 'admin_init', 'wppb_populate_manage_fields', 1 );

/**
 * Function that prepopulates the manage fields list with the default fields of WP
 *
 * @since v.2.0
 *
 * @return void
 */
function wppb_prepopulate_fields(){
	$prepopulated_fields[] = array( 'field' => 'Default - Name (Heading)', 'field-title' => __( 'Name', 'profile-builder' ), 'meta-name' => '',	'overwrite-existing' => 'No', 'id' => '1', 'description' => '', 'row-count' => '5', 'allowed-image-extensions' => '.*',	'allowed-upload-extensions' => '.*', 'avatar-size' => '100', 'date-format' => 'mm/dd/yy', 'terms-of-agreement' => '', 'options' => '', 'labels' => '', 'public-key' => '', 'private-key' => '', 'default-value' => '', 'default-option' => '', 'default-options' => '', 'default-content' => '', 'required' => 'No' );
	$prepopulated_fields[] = array( 'field' => 'Default - Username', 'field-title' => __( 'Username', 'profile-builder' ), 'meta-name' => '', 'overwrite-existing' => 'No', 'id' => '2', 'description' => __( 'Usernames cannot be changed.', 'profile-builder' ), 'row-count' => '5', 'allowed-image-extensions' => '.*', 'allowed-upload-extensions' => '.*', 'avatar-size' => '100', 'date-format' => 'mm/dd/yy', 'terms-of-agreement' => '', 'options' => '', 'labels' => '', 'public-key' => '', 'private-key' => '', 'default-value' => '', 'default-option' => '', 'default-options' => '', 'default-content' => '', 'required' => 'Yes' );
	$prepopulated_fields[] = array( 'field' => 'Default - First Name', 'field-title' => __( 'First Name', 'profile-builder' ), 'meta-name' => 'first_name', 'overwrite-existing' => 'No', 'id' => '3', 'description' => '', 'row-count' => '5', 'allowed-image-extensions' => '.*', 'allowed-upload-extensions' => '.*', 'avatar-size' => '100', 'date-format' => 'mm/dd/yy', 'terms-of-agreement' => '', 'options' => '', 'labels' => '', 'public-key' => '', 'private-key' => '', 'default-value' => '', 'default-option' => '', 'default-options' => '', 'default-content' => '', 'required' => 'No' );
	$prepopulated_fields[] = array( 'field' => 'Default - Last Name', 'field-title' => __( 'Last Name', 'profile-builder' ), 'meta-name' => 'last_name', 'overwrite-existing' => 'No', 'id' => '4', 'description' => '', 'row-count' => '5', 'allowed-image-extensions' => '.*', 'allowed-upload-extensions' => '.*', 'avatar-size' => '100', 'date-format' => 'mm/dd/yy', 'terms-of-agreement' => '', 'options' => '', 'labels' => '', 'public-key' => '', 'private-key' => '', 'default-value' => '', 'default-option' => '', 'default-options' => '', 'default-content' => '', 'required' => 'No' );
	$prepopulated_fields[] = array( 'field' => 'Default - Nickname', 'field-title' => __( 'Nickname', 'profile-builder' ), 'meta-name' => 'nickname', 'overwrite-existing' => 'No', 'id' => '5', 'description' => '', 'row-count' => '5', 'allowed-image-extensions' => '.*', 'allowed-upload-extensions' => '.*', 'avatar-size' => '100', 'date-format' => 'mm/dd/yy', 'terms-of-agreement' => '', 'options' => '', 'labels' => '', 'public-key' => '', 'private-key' => '', 'default-value' => '', 'default-option' => '', 'default-options' => '', 'default-content' => '', 'required' => 'Yes' );
	$prepopulated_fields[] = array( 'field' => 'Default - Display name publicly as', 'field-title' => __( 'Display name publicly as', 'profile-builder' ), 'meta-name' => '', 'overwrite-existing' => 'No', 'id' => '6', 'description' => '', 'row-count' => '5', 'allowed-image-extensions' => '.*', 'allowed-upload-extensions' => '.*', 'avatar-size' => '100', 'date-format' => 'mm/dd/yy', 'terms-of-agreement' => '', 'options' => '', 'labels' => '', 'public-key' => '', 'private-key' => '', 'default-value' => '', 'default-option' => '', 'default-options' => '', 'default-content' => '', 'required' => 'No' );
	$prepopulated_fields[] = array( 'field' => 'Default - Contact Info (Heading)', 'field-title' => __( 'Contact Info', 'profile-builder' ), 'meta-name' => '', 'overwrite-existing' => 'No', 'id' => '7', 'description' => '', 'row-count' => '5', 'allowed-image-extensions' => '.*', 'allowed-upload-extensions' => '.*', 'avatar-size' => '100', 'date-format' => 'mm/dd/yy', 'terms-of-agreement' => '', 'options' => '', 'labels' => '', 'public-key' => '', 'private-key' => '', 'default-value' => '', 'default-option' => '', 'default-options' => '', 'default-content' => '', 'required' => 'No' );
	$prepopulated_fields[] = array( 'field' => 'Default - E-mail', 'field-title' => __( 'E-mail', 'profile-builder' ), 'meta-name' => '', 'overwrite-existing' => 'No', 'id' => '8', 'description' => '', 'row-count' => '5', 'allowed-image-extensions' => '.*', 'allowed-upload-extensions' => '.*', 'avatar-size' => '100', 'date-format' => 'mm/dd/yy', 'terms-of-agreement' => '', 'options' => '', 'labels' => '', 'public-key' => '', 'private-key' => '', 'default-value' => '', 'default-option' => '', 'default-options' => '', 'default-content' => '', 'required' => 'Yes' );
	$prepopulated_fields[] = array( 'field' => 'Default - Website', 'field-title' => __( 'Website', 'profile-builder' ), 'meta-name' => '', 'overwrite-existing' => 'No', 'id' => '9', 'description' => '', 'row-count' => '5', 'allowed-image-extensions' => '.*', 'allowed-upload-extensions' => '.*', 'avatar-size' => '100', 'date-format' => 'mm/dd/yy', 'terms-of-agreement' => '', 'options' => '', 'labels' => '', 'public-key' => '', 'private-key' => '', 'default-value' => '', 'default-option' => '', 'default-options' => '', 'default-content' => '', 'required' => 'No' );

	// Default contact methods were removed in WP 3.6. A filter dictates contact methods.
	if ( apply_filters( 'wppb_remove_default_contact_methods', get_site_option( 'initial_db_version' ) < 23588 ) ){
		$prepopulated_fields[] = array( 'field' => 'Default - AIM', 'field-title' => __( 'AIM', 'profile-builder' ), 'meta-name' => 'aim', 'overwrite-existing' => 'No', 'id' => '10', 'description' => '', 'row-count' => '5', 'allowed-image-extensions' => '.*', 'allowed-upload-extensions' => '.*', 'avatar-size' => '100', 'date-format' => 'mm/dd/yy', 'terms-of-agreement' => '', 'options' => '', 'labels' => '', 'public-key' => '', 'private-key' => '', 'default-value' => '', 'default-option' => '', 'default-options' => '', 'default-content' => '', 'required' => 'No' );
		$prepopulated_fields[] = array( 'field' => 'Default - Yahoo IM', 'field-title' => __( 'Yahoo IM', 'profile-builder' ), 'meta-name' => 'yim', 'overwrite-existing' => 'No', 'id' => '11', 'description' => '', 'row-count' => '5', 'allowed-image-extensions' => '.*', 'allowed-upload-extensions' => '.*', 'avatar-size' => '100', 'date-format' => 'mm/dd/yy', 'terms-of-agreement' => '', 'options' => '', 'labels' => '', 'public-key' => '', 'private-key' => '', 'default-value' => '', 'default-option' => '', 'default-options' => '', 'default-content' => '', 'required' => 'No' );
		$prepopulated_fields[] = array( 'field' => 'Default - Jabber / Google Talk', 'field-title' => __( 'Jabber / Google Talk', 'profile-builder' ), 'meta-name' => 'jabber', 'overwrite-existing' => 'No', 'id' => '12', 'description' => '', 'row-count' => '5', 'allowed-image-extensions' => '.*', 'allowed-upload-extensions' => '.*', 'avatar-size' => '100', 'date-format' => 'mm/dd/yy', 'terms-of-agreement' => '', 'options' => '', 'labels' => '', 'public-key' => '', 'private-key' => '', 'default-value' => '', 'default-option' => '', 'default-options' => '', 'default-content' => '', 'required' => 'No' );
	}

	$prepopulated_fields[] = array( 'field' => 'Default - About Yourself (Heading)', 'field-title' => __( 'About Yourself', 'profile-builder' ), 'meta-name' => '', 'overwrite-existing' => 'No', 'id' => '13', 'description' => '', 'row-count' => '5', 'allowed-image-extensions' => '.*', 'allowed-upload-extensions' => '.*', 'avatar-size' => '100', 'date-format' => 'mm/dd/yy', 'terms-of-agreement' => '', 'options' => '', 'labels' => '', 'public-key' => '', 'private-key' => '', 'default-value' => '', 'default-option' => '', 'default-options' => '', 'default-content' => '', 'required' => 'No' );
	$prepopulated_fields[] = array( 'field' => 'Default - Biographical Info', 'field-title' => __( 'Biographical Info', 'profile-builder' ), 'meta-name' => 'description', 'overwrite-existing' => 'No', 'id' => '14', 'description' => __( 'Share a little biographical information to fill out your profile. This may be shown publicly.', 'profile-builder' ), 'row-count' => '5', 'allowed-image-extensions' => '.*', 'allowed-upload-extensions' => '.*', 'avatar-size' => '100', 'date-format' => 'mm/dd/yy', 'terms-of-agreement' => '', 'options' => '', 'labels' => '', 'public-key' => '', 'private-key' => '', 'default-value' => '', 'default-option' => '', 'default-options' => '', 'required' => 'No' );
	$prepopulated_fields[] = array( 'field' => 'Default - Password', 'field-title' => __( 'Password', 'profile-builder' ), 'meta-name' => '', 'overwrite-existing' => 'No', 'id' => '15', 'description' => __( 'Type your password.', 'profile-builder' ), 'row-count' => '5', 'allowed-image-extensions' => '.*', 'allowed-upload-extensions' => '.*', 'avatar-size' => '100', 'date-format' => 'mm/dd/yy', 'terms-of-agreement' => '', 'options' => '', 'labels' => '', 'public-key' => '', 'private-key' => '', 'default-value' => '', 'default-option' => '', 'default-options' => '', 'default-content' => '', 'required' => 'Yes' );
	$prepopulated_fields[] = array( 'field' => 'Default - Repeat Password', 'field-title' => __( 'Repeat Password', 'profile-builder' ), 'meta-name' => '', 'overwrite-existing' => 'No', 'id' => '16', 'description' => __( 'Type your password again. ', 'profile-builder' ), 'row-count' => '5', 'allowed-image-extensions' => '.*', 'allowed-upload-extensions' => '.*', 'avatar-size' => '100', 'date-format' => 'mm/dd/yy', 'terms-of-agreement' => '', 'options' => '', 'labels' => '', 'public-key' => '', 'private-key' => '', 'default-value' => '', 'default-option' => '', 'default-options' => '', 'default-content' => '', 'required' => 'Yes' );
	if ( wppb_can_users_signup_blog() ){
		$prepopulated_fields[] = array( 'field' => 'Default - Blog Details', 'field-title' => __( 'Blog Details', 'profile-builder' ), 'meta-name' => '', 'overwrite-existing' => 'No', 'id' => '17', 'description' => '', 'row-count' => '5', 'allowed-image-extensions' => '.*', 'allowed-upload-extensions' => '.*', 'avatar-size' => '100', 'date-format' => 'mm/dd/yy', 'terms-of-agreement' => '', 'options' => '', 'labels' => '', 'public-key' => '', 'private-key' => '', 'default-value' => '', 'default-option' => '', 'default-options' => '', 'default-content' => '', 'required' => 'No' );
	}

	add_option ( 'wppb_manage_fields', apply_filters ( 'wppb_prepopulated_fields', $prepopulated_fields ) );
}

/**
 * Function that returns a unique meta-name
 *
 * @since v.2.0
 *
 * @return string
 */
function wppb_get_meta_name( $option = 'wppb_manage_fields', $prefix = 'custom_field_' ){
	$id = 1;

	$wppb_manage_fields = get_option( $option, 'not_found' );

	if ( ( $wppb_manage_fields === 'not_found' ) || ( empty( $wppb_manage_fields ) ) ){
		return $prefix . $id;
	}
    else{
        $meta_names = array();
		foreach( $wppb_manage_fields as $value ){
			if ( strpos( $value['meta-name'], $prefix ) === 0 ){
                $meta_names[] = $value['meta-name'];
			}
		}

        if( !empty( $meta_names ) ){
            $meta_numbers = array();
            foreach( $meta_names as $meta_name ){
                $number = str_replace( $prefix, '', $meta_name );
                /* we should have an underscore present in custom_field_# so remove it */
                $number = str_replace( '_', '', $number );

                $meta_numbers[] = $number;
            }
            if( !empty( $meta_numbers ) ){
                rsort( $meta_numbers );
                $id = (int)$meta_numbers[0] + 1;
            }
        }

		return $prefix . $id;
	}
}


/**
 * Function that returns an array with countries
 *
 * @since v.2.0
 *
 * @return array
 */
function wppb_country_select_options( $form_location ) {
	$country_array = apply_filters( 'wppb_'.$form_location.'_country_select_array',
		array(
			''	 => __( 'Select a Country', 'profile-builder' ),
			'AF' => __( 'Afghanistan', 'profile-builder' ),
			'AX' => __( 'Aland Islands', 'profile-builder' ),
			'AL' => __( 'Albania', 'profile-builder' ),
			'DZ' => __( 'Algeria', 'profile-builder' ),
			'AS' => __( 'American Samoa', 'profile-builder' ),
			'AD' => __( 'Andorra', 'profile-builder' ),
			'AO' => __( 'Angola', 'profile-builder' ),
			'AI' => __( 'Anguilla', 'profile-builder' ),
			'AQ' => __( 'Antarctica', 'profile-builder' ),
			'AG' => __( 'Antigua and Barbuda', 'profile-builder' ),
			'AR' => __( 'Argentina', 'profile-builder' ),
			'AM' => __( 'Armenia', 'profile-builder' ),
			'AW' => __( 'Aruba', 'profile-builder' ),
			'AU' => __( 'Australia', 'profile-builder' ),
			'AT' => __( 'Austria', 'profile-builder' ),
			'AZ' => __( 'Azerbaijan', 'profile-builder' ),
			'BS' => __( 'Bahamas', 'profile-builder' ),
			'BH' => __( 'Bahrain', 'profile-builder' ),
			'BD' => __( 'Bangladesh', 'profile-builder' ),
			'BB' => __( 'Barbados', 'profile-builder' ),
			'BY' => __( 'Belarus', 'profile-builder' ),
			'BE' => __( 'Belgium', 'profile-builder' ),
			'BZ' => __( 'Belize', 'profile-builder' ),
			'BJ' => __( 'Benin', 'profile-builder' ),
			'BM' => __( 'Bermuda', 'profile-builder' ),
			'BT' => __( 'Bhutan', 'profile-builder' ),
			'BO' => __( 'Bolivia', 'profile-builder' ),
			'BQ' => __( 'Bonaire, Saint Eustatius and Saba', 'profile-builder' ),
			'BA' => __( 'Bosnia and Herzegovina', 'profile-builder' ),
			'BW' => __( 'Botswana', 'profile-builder' ),
			'BV' => __( 'Bouvet Island', 'profile-builder' ),
			'BR' => __( 'Brazil', 'profile-builder' ),
			'IO' => __( 'British Indian Ocean Territory', 'profile-builder' ),
			'VG' => __( 'British Virgin Islands', 'profile-builder' ),
			'BN' => __( 'Brunei', 'profile-builder' ),
			'BG' => __( 'Bulgaria', 'profile-builder' ),
			'BF' => __( 'Burkina Faso', 'profile-builder' ),
			'BI' => __( 'Burundi', 'profile-builder' ),
			'KH' => __( 'Cambodia', 'profile-builder' ),
			'CM' => __( 'Cameroon', 'profile-builder' ),
			'CA' => __( 'Canada', 'profile-builder' ),
			'CV' => __( 'Cape Verde', 'profile-builder' ),
			'KY' => __( 'Cayman Islands', 'profile-builder' ),
			'CF' => __( 'Central African Republic', 'profile-builder' ),
			'TD' => __( 'Chad', 'profile-builder' ),
			'CL' => __( 'Chile', 'profile-builder' ),
			'CN' => __( 'China', 'profile-builder' ),
			'CX' => __( 'Christmas Island', 'profile-builder' ),
			'CC' => __( 'Cocos Islands', 'profile-builder' ),
			'CO' => __( 'Colombia', 'profile-builder' ),
			'KM' => __( 'Comoros', 'profile-builder' ),
			'CK' => __( 'Cook Islands', 'profile-builder' ),
			'CR' => __( 'Costa Rica', 'profile-builder' ),
			'HR' => __( 'Croatia', 'profile-builder' ),
			'CU' => __( 'Cuba', 'profile-builder' ),
			'CW' => __( 'Curacao', 'profile-builder' ),
			'CY' => __( 'Cyprus', 'profile-builder' ),
			'CZ' => __( 'Czech Republic', 'profile-builder' ),
			'CD' => __( 'Democratic Republic of the Congo', 'profile-builder' ),
			'DK' => __( 'Denmark', 'profile-builder' ),
			'DJ' => __( 'Djibouti', 'profile-builder' ),
			'DM' => __( 'Dominica', 'profile-builder' ),
			'DO' => __( 'Dominican Republic', 'profile-builder' ),
			'TL' => __( 'East Timor', 'profile-builder' ),
			'EC' => __( 'Ecuador', 'profile-builder' ),
			'EG' => __( 'Egypt', 'profile-builder' ),
			'SV' => __( 'El Salvador', 'profile-builder' ),
			'GQ' => __( 'Equatorial Guinea', 'profile-builder' ),
			'ER' => __( 'Eritrea', 'profile-builder' ),
			'EE' => __( 'Estonia', 'profile-builder' ),
			'ET' => __( 'Ethiopia', 'profile-builder' ),
			'FK' => __( 'Falkland Islands', 'profile-builder' ),
			'FO' => __( 'Faroe Islands', 'profile-builder' ),
			'FJ' => __( 'Fiji', 'profile-builder' ),
			'FI' => __( 'Finland', 'profile-builder' ),
			'FR' => __( 'France', 'profile-builder' ),
			'GF' => __( 'French Guiana', 'profile-builder' ),
			'PF' => __( 'French Polynesia', 'profile-builder' ),
			'TF' => __( 'French Southern Territories', 'profile-builder' ),
			'GA' => __( 'Gabon', 'profile-builder' ),
			'GM' => __( 'Gambia', 'profile-builder' ),
			'GE' => __( 'Georgia', 'profile-builder' ),
			'DE' => __( 'Germany', 'profile-builder' ),
			'GH' => __( 'Ghana', 'profile-builder' ),
			'GI' => __( 'Gibraltar', 'profile-builder' ),
			'GR' => __( 'Greece', 'profile-builder' ),
			'GL' => __( 'Greenland', 'profile-builder' ),
			'GD' => __( 'Grenada', 'profile-builder' ),
			'GP' => __( 'Guadeloupe', 'profile-builder' ),
			'GU' => __( 'Guam', 'profile-builder' ),
			'GT' => __( 'Guatemala', 'profile-builder' ),
			'GG' => __( 'Guernsey', 'profile-builder' ),
			'GN' => __( 'Guinea', 'profile-builder' ),
			'GW' => __( 'Guinea-Bissau', 'profile-builder' ),
			'GY' => __( 'Guyana', 'profile-builder' ),
			'HT' => __( 'Haiti', 'profile-builder' ),
			'HM' => __( 'Heard Island and McDonald Islands', 'profile-builder' ),
			'HN' => __( 'Honduras', 'profile-builder' ),
			'HK' => __( 'Hong Kong', 'profile-builder' ),
			'HU' => __( 'Hungary', 'profile-builder' ),
			'IS' => __( 'Iceland', 'profile-builder' ),
			'IN' => __( 'India', 'profile-builder' ),
			'ID' => __( 'Indonesia', 'profile-builder' ),
			'IR' => __( 'Iran', 'profile-builder' ),
			'IQ' => __( 'Iraq', 'profile-builder' ),
			'IE' => __( 'Ireland', 'profile-builder' ),
			'IM' => __( 'Isle of Man', 'profile-builder' ),
			'IL' => __( 'Israel', 'profile-builder' ),
			'IT' => __( 'Italy', 'profile-builder' ),
			'CI' => __( 'Ivory Coast', 'profile-builder' ),
			'JM' => __( 'Jamaica', 'profile-builder' ),
			'JP' => __( 'Japan', 'profile-builder' ),
			'JE' => __( 'Jersey', 'profile-builder' ),
			'JO' => __( 'Jordan', 'profile-builder' ),
			'KZ' => __( 'Kazakhstan', 'profile-builder' ),
			'KE' => __( 'Kenya', 'profile-builder' ),
			'KI' => __( 'Kiribati', 'profile-builder' ),
			'XK' => __( 'Kosovo', 'profile-builder' ),
			'KW' => __( 'Kuwait', 'profile-builder' ),
			'KG' => __( 'Kyrgyzstan', 'profile-builder' ),
			'LA' => __( 'Laos', 'profile-builder' ),
			'LV' => __( 'Latvia', 'profile-builder' ),
			'LB' => __( 'Lebanon', 'profile-builder' ),
			'LS' => __( 'Lesotho', 'profile-builder' ),
			'LR' => __( 'Liberia', 'profile-builder' ),
			'LY' => __( 'Libya', 'profile-builder' ),
			'LI' => __( 'Liechtenstein', 'profile-builder' ),
			'LT' => __( 'Lithuania', 'profile-builder' ),
			'LU' => __( 'Luxembourg', 'profile-builder' ),
			'MO' => __( 'Macao', 'profile-builder' ),
			'MK' => __( 'Macedonia', 'profile-builder' ),
			'MG' => __( 'Madagascar', 'profile-builder' ),
			'MW' => __( 'Malawi', 'profile-builder' ),
			'MY' => __( 'Malaysia', 'profile-builder' ),
			'MV' => __( 'Maldives', 'profile-builder' ),
			'ML' => __( 'Mali', 'profile-builder' ),
			'MT' => __( 'Malta', 'profile-builder' ),
			'MH' => __( 'Marshall Islands', 'profile-builder' ),
			'MQ' => __( 'Martinique', 'profile-builder' ),
			'MR' => __( 'Mauritania', 'profile-builder' ),
			'MU' => __( 'Mauritius', 'profile-builder' ),
			'YT' => __( 'Mayotte', 'profile-builder' ),
			'MX' => __( 'Mexico', 'profile-builder' ),
			'FM' => __( 'Micronesia', 'profile-builder' ),
			'MD' => __( 'Moldova', 'profile-builder' ),
			'MC' => __( 'Monaco', 'profile-builder' ),
			'MN' => __( 'Mongolia', 'profile-builder' ),
			'ME' => __( 'Montenegro', 'profile-builder' ),
			'MS' => __( 'Montserrat', 'profile-builder' ),
			'MA' => __( 'Morocco', 'profile-builder' ),
			'MZ' => __( 'Mozambique', 'profile-builder' ),
			'MM' => __( 'Myanmar', 'profile-builder' ),
			'NA' => __( 'Namibia', 'profile-builder' ),
			'NR' => __( 'Nauru', 'profile-builder' ),
			'NP' => __( 'Nepal', 'profile-builder' ),
			'NL' => __( 'Netherlands', 'profile-builder' ),
			'NC' => __( 'New Caledonia', 'profile-builder' ),
			'NZ' => __( 'New Zealand', 'profile-builder' ),
			'NI' => __( 'Nicaragua', 'profile-builder' ),
			'NE' => __( 'Niger', 'profile-builder' ),
			'NG' => __( 'Nigeria', 'profile-builder' ),
			'NU' => __( 'Niue', 'profile-builder' ),
			'NF' => __( 'Norfolk Island', 'profile-builder' ),
			'KP' => __( 'North Korea', 'profile-builder' ),
			'MP' => __( 'Northern Mariana Islands', 'profile-builder' ),
			'NO' => __( 'Norway', 'profile-builder' ),
			'OM' => __( 'Oman', 'profile-builder' ),
			'PK' => __( 'Pakistan', 'profile-builder' ),
			'PW' => __( 'Palau', 'profile-builder' ),
			'PS' => __( 'Palestinian Territory', 'profile-builder' ),
			'PA' => __( 'Panama', 'profile-builder' ),
			'PG' => __( 'Papua New Guinea', 'profile-builder' ),
			'PY' => __( 'Paraguay', 'profile-builder' ),
			'PE' => __( 'Peru', 'profile-builder' ),
			'PH' => __( 'Philippines', 'profile-builder' ),
			'PN' => __( 'Pitcairn', 'profile-builder' ),
			'PL' => __( 'Poland', 'profile-builder' ),
			'PT' => __( 'Portugal', 'profile-builder' ),
			'PR' => __( 'Puerto Rico', 'profile-builder' ),
			'QA' => __( 'Qatar', 'profile-builder' ),
			'CG' => __( 'Republic of the Congo', 'profile-builder' ),
			'RE' => __( 'Reunion', 'profile-builder' ),
			'RO' => __( 'Romania', 'profile-builder' ),
			'RU' => __( 'Russia', 'profile-builder' ),
			'RW' => __( 'Rwanda', 'profile-builder' ),
			'BL' => __( 'Saint Barthelemy', 'profile-builder' ),
			'SH' => __( 'Saint Helena', 'profile-builder' ),
			'KN' => __( 'Saint Kitts and Nevis', 'profile-builder' ),
			'LC' => __( 'Saint Lucia', 'profile-builder' ),
			'MF' => __( 'Saint Martin', 'profile-builder' ),
			'PM' => __( 'Saint Pierre and Miquelon', 'profile-builder' ),
			'VC' => __( 'Saint Vincent and the Grenadines', 'profile-builder' ),
			'WS' => __( 'Samoa', 'profile-builder' ),
			'SM' => __( 'San Marino', 'profile-builder' ),
			'ST' => __( 'Sao Tome and Principe', 'profile-builder' ),
			'SA' => __( 'Saudi Arabia', 'profile-builder' ),
			'SN' => __( 'Senegal', 'profile-builder' ),
			'RS' => __( 'Serbia', 'profile-builder' ),
			'SC' => __( 'Seychelles', 'profile-builder' ),
			'SL' => __( 'Sierra Leone', 'profile-builder' ),
			'SG' => __( 'Singapore', 'profile-builder' ),
			'SX' => __( 'Sint Maarten', 'profile-builder' ),
			'SK' => __( 'Slovakia', 'profile-builder' ),
			'SI' => __( 'Slovenia', 'profile-builder' ),
			'SB' => __( 'Solomon Islands', 'profile-builder' ),
			'SO' => __( 'Somalia', 'profile-builder' ),
			'ZA' => __( 'South Africa', 'profile-builder' ),
			'GS' => __( 'South Georgia and the South Sandwich Islands', 'profile-builder' ),
			'KR' => __( 'South Korea', 'profile-builder' ),
			'SS' => __( 'South Sudan', 'profile-builder' ),
			'ES' => __( 'Spain', 'profile-builder' ),
			'LK' => __( 'Sri Lanka', 'profile-builder' ),
			'SD' => __( 'Sudan', 'profile-builder' ),
			'SR' => __( 'Suriname', 'profile-builder' ),
			'SJ' => __( 'Svalbard and Jan Mayen', 'profile-builder' ),
			'SZ' => __( 'Swaziland', 'profile-builder' ),
			'SE' => __( 'Sweden', 'profile-builder' ),
			'CH' => __( 'Switzerland', 'profile-builder' ),
			'SY' => __( 'Syria', 'profile-builder' ),
			'TW' => __( 'Taiwan', 'profile-builder' ),
			'TJ' => __( 'Tajikistan', 'profile-builder' ),
			'TZ' => __( 'Tanzania', 'profile-builder' ),
			'TH' => __( 'Thailand', 'profile-builder' ),
			'TG' => __( 'Togo', 'profile-builder' ),
			'TK' => __( 'Tokelau', 'profile-builder' ),
			'TO' => __( 'Tonga', 'profile-builder' ),
			'TT' => __( 'Trinidad and Tobago', 'profile-builder' ),
			'TN' => __( 'Tunisia', 'profile-builder' ),
			'TR' => __( 'Turkey', 'profile-builder' ),
			'TM' => __( 'Turkmenistan', 'profile-builder' ),
			'TC' => __( 'Turks and Caicos Islands', 'profile-builder' ),
			'TV' => __( 'Tuvalu', 'profile-builder' ),
			'VI' => __( 'U.S. Virgin Islands', 'profile-builder' ),
			'UG' => __( 'Uganda', 'profile-builder' ),
			'UA' => __( 'Ukraine', 'profile-builder' ),
			'AE' => __( 'United Arab Emirates', 'profile-builder' ),
			'GB' => __( 'United Kingdom', 'profile-builder' ),
			'US' => __( 'United States', 'profile-builder' ),
			'UM' => __( 'United States Minor Outlying Islands', 'profile-builder' ),
			'UY' => __( 'Uruguay', 'profile-builder' ),
			'UZ' => __( 'Uzbekistan', 'profile-builder' ),
			'VU' => __( 'Vanuatu', 'profile-builder' ),
			'VA' => __( 'Vatican', 'profile-builder' ),
			'VE' => __( 'Venezuela', 'profile-builder' ),
			'VN' => __( 'Vietnam', 'profile-builder' ),
			'WF' => __( 'Wallis and Futuna', 'profile-builder' ),
			'EH' => __( 'Western Sahara', 'profile-builder' ),
			'YE' => __( 'Yemen', 'profile-builder' ),
			'ZM' => __( 'Zambia', 'profile-builder' ),
			'ZW' => __( 'Zimbabwe', 'profile-builder' ),
		)
	);

	return $country_array;
}


/**
 * Function that returns an array with timezone options
 *
 * @since v.2.0
 *
 * @return array
 */
function wppb_timezone_select_options( $form_location ) {
	$timezone_array = apply_filters( 'wppb_'.$form_location.'_timezone_select_array', array ( '(GMT -12:00) Eniwetok, Kwajalein', '(GMT -11:00) Midway Island, Samoa', '(GMT -10:00) Hawaii', '(GMT -9:00) Alaska', '(GMT -8:00) Pacific Time (US & Canada)', '(GMT -7:00) Mountain Time (US & Canada)', '(GMT -6:00) Central Time (US & Canada), Mexico City', '(GMT -5:00) Eastern Time (US & Canada), Bogota, Lima', '(GMT -4:00) Atlantic Time (Canada), Caracas, La Paz', '(GMT -3:30) Newfoundland', '(GMT -3:00) Brazil, Buenos Aires, Georgetown', '(GMT -2:00) Mid-Atlantic', '(GMT -1:00) Azores, Cape Verde Islands', '(GMT) Western Europe Time, London, Lisbon, Casablanca', '(GMT +1:00) Brussels, Copenhagen, Madrid, Paris', '(GMT +2:00) Kaliningrad, South Africa', '(GMT +3:00) Baghdad, Riyadh, Moscow, St. Petersburg', '(GMT +3:30) Tehran', '(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi', '(GMT +4:30) Kabul', '(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent', '(GMT +5:30) Bombay, Calcutta, Madras, New Delhi', '(GMT +5:45) Kathmandu', '(GMT +6:00) Almaty, Dhaka, Colombo', '(GMT +7:00) Bangkok, Hanoi, Jakarta', '(GMT +8:00) Beijing, Perth, Singapore, Hong Kong', '(GMT +9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk', '(GMT +9:30) Adelaide, Darwin', '(GMT +10:00) Eastern Australia, Guam, Vladivostok', '(GMT +11:00) Magadan, Solomon Islands, New Caledonia', '(GMT +12:00) Auckland, Wellington, Fiji, Kamchatka' ) );

	return $timezone_array;
}


/*
 * Array with the currency ISO code and associated currency name
 *
 * @param string $form_location
 *
 * @return array
 *
 */
function wppb_get_currencies( $form_location = '' ) {

    $currencies = array(
        'ALL' => __( 'Albania Lek', 'profile-builder' ),
        'AFN' => __( 'Afghanistan Afghani', 'profile-builder' ),
        'ARS' => __( 'Argentina Peso', 'profile-builder' ),
        'AWG' => __( 'Aruba Guilder', 'profile-builder' ),
        'AUD' => __( 'Australia Dollar', 'profile-builder' ),
        'AZN' => __( 'Azerbaijan New Manat', 'profile-builder' ),
        'BSD' => __( 'Bahamas Dollar', 'profile-builder' ),
        'BBD' => __( 'Barbados Dollar','profile-builder' ),
        'BDT' => __( 'Bangladeshi taka','profile-builder' ),
        'BYR' => __( 'Belarus Ruble','profile-builder' ),
        'BZD' => __( 'Belize Dollar','profile-builder' ),
        'BMD' => __( 'Bermuda Dollar','profile-builder' ),
        'BOB' => __( 'Bolivia Boliviano','profile-builder' ),
        'BAM' => __( 'Bosnia and Herzegovina Convertible Marka','profile-builder' ),
        'BWP' => __( 'Botswana Pula','profile-builder' ),
        'BGN' => __( 'Bulgaria Lev','profile-builder' ),
        'BRL' => __( 'Brazil Real','profile-builder' ),
        'BND' => __( 'Brunei Darussalam Dollar','profile-builder' ),
        'KHR' => __( 'Cambodia Riel','profile-builder' ),
        'CAD' => __( 'Canada Dollar','profile-builder' ),
        'KYD' => __( 'Cayman Islands Dollar','profile-builder' ),
        'CLP' => __( 'Chile Peso','profile-builder' ),
        'CNY' => __( 'China Yuan Renminbi','profile-builder' ),
        'COP' => __( 'Colombia Peso','profile-builder' ),
        'CRC' => __( 'Costa Rica Colon','profile-builder' ),
        'HRK' => __( 'Croatia Kuna','profile-builder' ),
        'CUP' => __( 'Cuba Peso','profile-builder' ),
        'CZK' => __( 'Czech Republic Koruna','profile-builder' ),
        'DKK' => __( 'Denmark Krone','profile-builder' ),
        'DOP' => __( 'Dominican Republic Peso','profile-builder' ),
        'XCD' => __( 'East Caribbean Dollar','profile-builder' ),
        'EGP' => __( 'Egypt Pound','profile-builder' ),
        'SVC' => __( 'El Salvador Colon','profile-builder' ),
        'EEK' => __( 'Estonia Kroon','profile-builder' ),
        'EUR' => __( 'Euro','profile-builder' ),
        'FKP' => __( 'Falkland Islands (Malvinas) Pound','profile-builder' ),
        'FJD' => __( 'Fiji Dollar','profile-builder' ),
        'GHC' => __( 'Ghana Cedis','profile-builder' ),
        'GIP' => __( 'Gibraltar Pound','profile-builder' ),
        'GTQ' => __( 'Guatemala Quetzal','profile-builder' ),
        'GGP' => __( 'Guernsey Pound','profile-builder' ),
        'GYD' => __( 'Guyana Dollar','profile-builder' ),
        'HNL' => __( 'Honduras Lempira','profile-builder' ),
        'HKD' => __( 'Hong Kong Dollar','profile-builder' ),
        'HUF' => __( 'Hungary Forint','profile-builder' ),
        'ISK' => __( 'Iceland Krona','profile-builder' ),
        'INR' => __( 'India Rupee','profile-builder' ),
        'IDR' => __( 'Indonesia Rupiah','profile-builder' ),
        'IRR' => __( 'Iran Rial','profile-builder' ),
        'IMP' => __( 'Isle of Man Pound','profile-builder' ),
        'ILS' => __( 'Israel Shekel','profile-builder' ),
        'JMD' => __( 'Jamaica Dollar','profile-builder' ),
        'JPY' => __( 'Japan Yen','profile-builder' ),
        'JEP' => __( 'Jersey Pound','profile-builder' ),
        'KZT' => __( 'Kazakhstan Tenge','profile-builder' ),
        'KPW' => __( 'Korea (North) Won','profile-builder' ),
        'KRW' => __( 'Korea (South) Won','profile-builder' ),
        'KGS' => __( 'Kyrgyzstan Som','profile-builder' ),
        'LAK' => __( 'Laos Kip','profile-builder' ),
        'LVL' => __( 'Latvia Lat','profile-builder' ),
        'LBP' => __( 'Lebanon Pound','profile-builder' ),
        'LRD' => __( 'Liberia Dollar','profile-builder' ),
        'LTL' => __( 'Lithuania Litas','profile-builder' ),
        'MKD' => __( 'Macedonia Denar','profile-builder' ),
        'MYR' => __( 'Malaysia Ringgit','profile-builder' ),
        'MUR' => __( 'Mauritius Rupee','profile-builder' ),
        'MXN' => __( 'Mexico Peso','profile-builder' ),
        'MNT' => __( 'Mongolia Tughrik','profile-builder' ),
        'MZN' => __( 'Mozambique Metical','profile-builder' ),
        'NAD' => __( 'Namibia Dollar','profile-builder' ),
        'NPR' => __( 'Nepal Rupee','profile-builder' ),
        'ANG' => __( 'Netherlands Antilles Guilder','profile-builder' ),
        'NZD' => __( 'New Zealand Dollar','profile-builder' ),
        'NIO' => __( 'Nicaragua Cordoba','profile-builder' ),
        'NGN' => __( 'Nigeria Naira','profile-builder' ),
        'NOK' => __( 'Norway Krone','profile-builder' ),
        'OMR' => __( 'Oman Rial', 'profile-builder' ),
        'PKR' => __( 'Pakistan Rupee', 'profile-builder' ),
        'PAB' => __( 'Panama Balboa', 'profile-builder' ),
        'PYG' => __( 'Paraguay Guarani', 'profile-builder' ),
        'PEN' => __( 'Peru Nuevo Sol', 'profile-builder' ),
        'PHP' => __( 'Philippines Peso', 'profile-builder' ),
        'PLN' => __( 'Poland Zloty', 'profile-builder' ),
        'QAR' => __( 'Qatar Riyal', 'profile-builder' ),
        'RON' => __( 'Romania New Leu', 'profile-builder' ),
        'RUB' => __( 'Russia Ruble', 'profile-builder' ),
        'SHP' => __( 'Saint Helena Pound', 'profile-builder' ),
        'SAR' => __( 'Saudi Arabia Riyal', 'profile-builder' ),
        'RSD' => __( 'Serbia Dinar', 'profile-builder' ),
        'SCR' => __( 'Seychelles Rupee', 'profile-builder' ),
        'SGD' => __( 'Singapore Dollar', 'profile-builder' ),
        'SBD' => __( 'Solomon Islands Dollar', 'profile-builder' ),
        'SOS' => __( 'Somalia Shilling', 'profile-builder' ),
        'ZAR' => __( 'South Africa Rand', 'profile-builder' ),
        'LKR' => __( 'Sri Lanka Rupee', 'profile-builder' ),
        'SEK' => __( 'Sweden Krona', 'profile-builder' ),
        'CHF' => __( 'Switzerland Franc', 'profile-builder' ),
        'SRD' => __( 'Suriname Dollar', 'profile-builder' ),
        'SYP' => __( 'Syria Pound', 'profile-builder' ),
        'TWD' => __( 'Taiwan New Dollar', 'profile-builder' ),
        'THB' => __( 'Thailand Baht', 'profile-builder' ),
        'TTD' => __( 'Trinidad and Tobago Dollar', 'profile-builder' ),
        'TRY' => __( 'Turkey Lira', 'profile-builder' ),
        'TRL' => __( 'Turkey Lira', 'profile-builder' ),
        'TVD' => __( 'Tuvalu Dollar', 'profile-builder' ),
        'UAH' => __( 'Ukraine Hryvna', 'profile-builder' ),
        'GBP' => __( 'United Kingdom Pound', 'profile-builder' ),
        'UGX' => __( 'Uganda Shilling', 'profile-builder' ),
        'USD' => __( 'US Dollar', 'profile-builder' ),
        'UYU' => __( 'Uruguay Peso', 'profile-builder' ),
        'UZS' => __( 'Uzbekistan Som', 'profile-builder' ),
        'VEF' => __( 'Venezuela Bolivar', 'profile-builder' ),
        'VND' => __( 'Viet Nam Dong', 'profile-builder' ),
        'YER' => __( 'Yemen Rial', 'profile-builder' ),
        'ZWD' => __( 'Zimbabwe Dollar', 'profile-builder' )
    );

    $filter_name = ( empty( $form_location ) ? 'wppb_get_currencies' : 'wppb_get_currencies_' . $form_location );

    return apply_filters( $filter_name, $currencies );

}


/*
 * Returns the currency symbol for a given currency code
 *
 * @param string $currency_code
 *
 * @return string
 *
 */
function wppb_get_currency_symbol( $currency_code ) {

    $currency_symbols = array(
        'AED' => '&#1583;.&#1573;', // ?
        'AFN' => '&#65;&#102;',
        'ALL' => '&#76;&#101;&#107;',
        'AMD' => '',
        'ANG' => '&#402;',
        'AOA' => '&#75;&#122;', // ?
        'ARS' => '&#36;',
        'AUD' => '&#36;',
        'AWG' => '&#402;',
        'AZN' => '&#1084;&#1072;&#1085;',
        'BAM' => '&#75;&#77;',
        'BBD' => '&#36;',
        'BDT' => '&#2547;', // ?
        'BGN' => '&#1083;&#1074;',
        'BHD' => '.&#1583;.&#1576;', // ?
        'BIF' => '&#70;&#66;&#117;', // ?
        'BMD' => '&#36;',
        'BND' => '&#36;',
        'BOB' => '&#36;&#98;',
        'BRL' => '&#82;&#36;',
        'BSD' => '&#36;',
        'BTN' => '&#78;&#117;&#46;', // ?
        'BWP' => '&#80;',
        'BYR' => '&#112;&#46;',
        'BZD' => '&#66;&#90;&#36;',
        'CAD' => '&#36;',
        'CDF' => '&#70;&#67;',
        'CHF' => '&#67;&#72;&#70;',
        'CLF' => '', // ?
        'CLP' => '&#36;',
        'CNY' => '&#165;',
        'COP' => '&#36;',
        'CRC' => '&#8353;',
        'CUP' => '&#8396;',
        'CVE' => '&#36;', // ?
        'CZK' => '&#75;&#269;',
        'DJF' => '&#70;&#100;&#106;', // ?
        'DKK' => '&#107;&#114;',
        'DOP' => '&#82;&#68;&#36;',
        'DZD' => '&#1583;&#1580;', // ?
        'EGP' => '&#163;',
        'ETB' => '&#66;&#114;',
        'EUR' => '&#8364;',
        'FJD' => '&#36;',
        'FKP' => '&#163;',
        'GBP' => '&#163;',
        'GEL' => '&#4314;', // ?
        'GHS' => '&#162;',
        'GIP' => '&#163;',
        'GMD' => '&#68;', // ?
        'GNF' => '&#70;&#71;', // ?
        'GTQ' => '&#81;',
        'GYD' => '&#36;',
        'HKD' => '&#36;',
        'HNL' => '&#76;',
        'HRK' => '&#107;&#110;',
        'HTG' => '&#71;', // ?
        'HUF' => '&#70;&#116;',
        'IDR' => '&#82;&#112;',
        'ILS' => '&#8362;',
        'INR' => '&#8377;',
        'IQD' => '&#1593;.&#1583;', // ?
        'IRR' => '&#65020;',
        'ISK' => '&#107;&#114;',
        'JEP' => '&#163;',
        'JMD' => '&#74;&#36;',
        'JOD' => '&#74;&#68;', // ?
        'JPY' => '&#165;',
        'KES' => '&#75;&#83;&#104;', // ?
        'KGS' => '&#1083;&#1074;',
        'KHR' => '&#6107;',
        'KMF' => '&#67;&#70;', // ?
        'KPW' => '&#8361;',
        'KRW' => '&#8361;',
        'KWD' => '&#1583;.&#1603;', // ?
        'KYD' => '&#36;',
        'KZT' => '&#1083;&#1074;',
        'LAK' => '&#8365;',
        'LBP' => '&#163;',
        'LKR' => '&#8360;',
        'LRD' => '&#36;',
        'LSL' => '&#76;', // ?
        'LTL' => '&#76;&#116;',
        'LVL' => '&#76;&#115;',
        'LYD' => '&#1604;.&#1583;', // ?
        'MAD' => '&#1583;.&#1605;.', //?
        'MDL' => '&#76;',
        'MGA' => '&#65;&#114;', // ?
        'MKD' => '&#1076;&#1077;&#1085;',
        'MMK' => '&#75;',
        'MNT' => '&#8366;',
        'MOP' => '&#77;&#79;&#80;&#36;', // ?
        'MRO' => '&#85;&#77;', // ?
        'MUR' => '&#8360;', // ?
        'MVR' => '.&#1923;', // ?
        'MWK' => '&#77;&#75;',
        'MXN' => '&#36;',
        'MYR' => '&#82;&#77;',
        'MZN' => '&#77;&#84;',
        'NAD' => '&#36;',
        'NGN' => '&#8358;',
        'NIO' => '&#67;&#36;',
        'NOK' => '&#107;&#114;',
        'NPR' => '&#8360;',
        'NZD' => '&#36;',
        'OMR' => '&#65020;',
        'PAB' => '&#66;&#47;&#46;',
        'PEN' => '&#83;&#47;&#46;',
        'PGK' => '&#75;', // ?
        'PHP' => '&#8369;',
        'PKR' => '&#8360;',
        'PLN' => '&#122;&#322;',
        'PYG' => '&#71;&#115;',
        'QAR' => '&#65020;',
        'RON' => '&#108;&#101;&#105;',
        'RSD' => '&#1044;&#1080;&#1085;&#46;',
        'RUB' => '&#1088;&#1091;&#1073;',
        'RWF' => '&#1585;.&#1587;',
        'SAR' => '&#65020;',
        'SBD' => '&#36;',
        'SCR' => '&#8360;',
        'SDG' => '&#163;', // ?
        'SEK' => '&#107;&#114;',
        'SGD' => '&#36;',
        'SHP' => '&#163;',
        'SLL' => '&#76;&#101;', // ?
        'SOS' => '&#83;',
        'SRD' => '&#36;',
        'STD' => '&#68;&#98;', // ?
        'SVC' => '&#36;',
        'SYP' => '&#163;',
        'SZL' => '&#76;', // ?
        'THB' => '&#3647;',
        'TJS' => '&#84;&#74;&#83;', // ? TJS (guess)
        'TMT' => '&#109;',
        'TND' => '&#1583;.&#1578;',
        'TOP' => '&#84;&#36;',
        'TRY' => '&#8356;', // New Turkey Lira (old symbol used)
        'TTD' => '&#36;',
        'TWD' => '&#78;&#84;&#36;',
        'TZS' => '',
        'UAH' => '&#8372;',
        'UGX' => '&#85;&#83;&#104;',
        'USD' => '&#36;',
        'UYU' => '&#36;&#85;',
        'UZS' => '&#1083;&#1074;',
        'VEF' => '&#66;&#115;',
        'VND' => '&#8363;',
        'VUV' => '&#86;&#84;',
        'WST' => '&#87;&#83;&#36;',
        'XAF' => '&#70;&#67;&#70;&#65;',
        'XCD' => '&#36;',
        'XDR' => '',
        'XOF' => '',
        'XPF' => '&#70;',
        'YER' => '&#65020;',
        'ZAR' => '&#82;',
        'ZMK' => '&#90;&#75;', // ?
        'ZWL' => '&#90;&#36;',
    );

    if( !empty( $currency_symbols[$currency_code] ) )
        return $currency_symbols[$currency_code];
    else
        return '';

}


/**
 * Function that returns a unique, incremented ID
 *
 * @since v.2.0
 *
 * @return integer id
 */
function wppb_get_unique_id(){
    $id = 1;
	$wppb_manage_fields = get_option( 'wppb_manage_fields', 'not_found' );
    if ( ( $wppb_manage_fields === 'not_found' ) || ( empty( $wppb_manage_fields ) ) ){
        return $id;
    }
    else{
        $ids_array = array();
        foreach( $wppb_manage_fields as $value ){
            $ids_array[] = $value['id'];
        }
        if( !empty( $ids_array ) ){
            rsort( $ids_array );
            $id = $ids_array[0] + 1;
        }
    }
    return apply_filters( 'wppb_field_unique_id', $id, $ids_array, $wppb_manage_fields );
}

/**
 * Function that checks to see if the id is unique when saving the new field
 *
 * @param array $values
 *
 * @return array
 */
function wppb_check_unique_id_on_saving( $values ) {
    $wppb_manage_fields = get_option( 'wppb_manage_fields', 'not_found' );

    if( $wppb_manage_fields != 'not_found' ) {

        $ids_array = array();
        foreach( $wppb_manage_fields as $field ){
            $ids_array[] = $field['id'];
        }

        if( in_array( $values['id'], $ids_array ) ) {
            rsort( $ids_array );
            $values['id'] = $ids_array[0] + 1;
        }

    }
    return $values;
}
add_filter( 'wck_add_meta_filter_values_wppb_manage_fields', 'wppb_check_unique_id_on_saving' );


function wppb_return_unique_field_list( $only_default_fields = false ){

	$unique_field_list[] = 'Default - Name (Heading)';
	$unique_field_list[] = 'Default - Contact Info (Heading)';
	$unique_field_list[] = 'Default - About Yourself (Heading)';
	$unique_field_list[] = 'Default - Username';
	$unique_field_list[] = 'Default - First Name';
	$unique_field_list[] = 'Default - Last Name';
	$unique_field_list[] = 'Default - Nickname';
	$unique_field_list[] = 'Default - E-mail';
	$unique_field_list[] = 'Default - Website';

	// Default contact methods were removed in WP 3.6. A filter dictates contact methods.
	if ( apply_filters( 'wppb_remove_default_contact_methods', get_site_option( 'initial_db_version' ) < 23588 ) ){
		$unique_field_list[] = 'Default - AIM';
		$unique_field_list[] = 'Default - Yahoo IM';
		$unique_field_list[] = 'Default - Jabber / Google Talk';
	}

	$unique_field_list[] = 'Default - Password';
	$unique_field_list[] = 'Default - Repeat Password';
	$unique_field_list[] = 'Default - Biographical Info';
	$unique_field_list[] = 'Default - Display name publicly as';

    $unique_field_list[] = 'GDPR Checkbox';

    $unique_field_list[] = 'Email Confirmation';

	if ( wppb_can_users_signup_blog() ) {
		$unique_field_list[] = 'Default - Blog Details';
	}

    if( !$only_default_fields ){
	    $unique_field_list[] = 'Avatar';
	    $unique_field_list[] = 'reCAPTCHA';
        $unique_field_list[] = 'Select (User Role)';
        $unique_field_list[] = 'Map';
    }

	return 	apply_filters ( 'wppb_unique_field_list', $unique_field_list );
}


/**
 * Function that checks several things when adding/editing the fields
 *
 * @since v.2.0
 *
 * @param string $message - the message to be displayed
 * @param array $fields - the added fields
 * @param array $required_fields
 * @param string $meta - the meta-name of the option
 * @param string $values - The values entered for each option
 * @param integer $post_id
 * @return boolean
 */
function wppb_check_field_on_edit_add( $message, $fields, $required_fields, $meta_name, $posted_values, $post_id ){
	global $wpdb;

	if ( $meta_name == 'wppb_manage_fields' ){

		// check for a valid field-type (fallback)
		if ( $posted_values['field'] == '' )
			$message .= __( "You must select a field\n", 'profile-builder' );
		// END check for a valid field-type (fallback)

		$unique_field_list = wppb_return_unique_field_list();
		$all_fields = apply_filters( 'wppb_manage_fields_check_field_on_edit_add', get_option ( $meta_name, 'not_set' ), $posted_values );

		// check if the unique fields are only added once
		if( $all_fields != 'not_set' ){
			foreach( $all_fields as $field ){
				if ( ( in_array ( $posted_values['field'], $unique_field_list ) ) && ( $posted_values['field'] == $field['field'] ) && ( $posted_values['id'] != $field['id'] ) ){
					$message .= __( "Please choose a different field type as this one already exists in your form (must be unique)\n", 'profile-builder' );
					break;
				}
			}
		}
		// END check if the unique fields are only added once

		// check for avatar size
		if ( $posted_values['field'] == 'Avatar' ){
			if ( is_numeric( $posted_values['avatar-size'] ) ){
				if ( ( $posted_values['avatar-size'] < 20 ) || ( $posted_values['avatar-size'] > 200 ) )
					$message .= __( "The entered avatar size is not between 20 and 200\n", 'profile-builder' );

			}else
				$message .= __( "The entered avatar size is not numerical\n", 'profile-builder' );

		}
		// END check for avatar size

		// check for correct row value
		if ( ( $posted_values['field'] == 'Default - Biographical Info' ) || ( $posted_values['field'] == 'Textarea' ) ){
			if ( !is_numeric( $posted_values['row-count'] ) )
				$message .= __( "The entered row number is not numerical\n", 'profile-builder' );

			elseif ( trim( $posted_values['row-count'] ) == '' )
				$message .= __( "You must enter a value for the row number\n", 'profile-builder' );
		}
		// END check for correct row value


		// check for the public and private keys
		if ( $posted_values['field'] == 'reCAPTCHA'){
			if ( trim( $posted_values['public-key'] ) == '' )
				$message .= __( "You must enter the site key\n", 'profile-builder' );
			if ( trim( $posted_values['private-key'] ) == '' )
				$message .= __( "You must enter the secret key\n", 'profile-builder' );
		}
		// END check for the public and private keys

		// check for the correct the date-format
		if ( $posted_values['field'] == 'Datepicker' ){
            $date_format = trim( $posted_values['date-format'] );
            if( empty( $date_format ) )
                $message .= __( "You must enter a value for the date-format\n", 'profile-builder' );
            else {
                $date_format = preg_split("/(, | |\/|-|\.|,)/", $date_format );
                $valid_pieces = array('d', 'dd', 'D', 'DD', 'o', 'oo', 'm', 'mm', 'M', 'MM', 'y', 'yy', '@');
                if ($date_format) {
                    $invalid_format = false;
                    foreach ($date_format as $piece) {
                        if (!in_array($piece, $valid_pieces)) {
                            $invalid_format = true;
                            break;
                        }
                    }

                    if ($invalid_format)
                        $message .= __("The entered value for the Datepicker is not a valid date-format\n", 'profile-builder');
                }
            }

		}
		// END check for the correct the date-format

		//check for empty meta-name and duplicate meta-name
		if ( $posted_values['overwrite-existing'] == 'No' ){
            $skip_check_for_fields = wppb_return_unique_field_list(true);
            $skip_check_for_fields = apply_filters ( 'wppb_skip_check_for_fields', $skip_check_for_fields );

			if ( !in_array( trim( $posted_values['field'] ), $skip_check_for_fields ) ){
				$reserved_meta_name_list = wppb_get_reserved_meta_name_list( $all_fields, $posted_values );

                //check to see if meta-name is empty
                $skip_empty_check_for_fields = array( 'Heading', 'Select (User Role)', 'reCAPTCHA', 'HTML', 'GDPR Delete Button' );

                if( !in_array( $posted_values['field'], $skip_empty_check_for_fields ) && empty( $posted_values['meta-name'] ) ) {
                    $message .= __( "The meta-name cannot be empty\n", 'profile-builder' );
                }

                //check if the meta-name starts or ends with a space
                if ( strpos( $posted_values['meta-name'], " " ) === 0 ) {
                    $message .= __( "The meta-name cannot begin with a space\n", 'profile-builder' );
                }
                if ( strpos( $posted_values['meta-name'], " " ) === strlen( $posted_values['meta-name'] ) - 1 ) {
                    $message .= __( "The meta-name cannot end with a space\n", 'profile-builder' );
                }

                // meta names that are reserved and cannot be used as part of other meta names
                $reserved_meta_name_list_strict = apply_filters( 'wppb_unique_meta_name_list_strict', array( 'map' ) );
                // skip meta name check for these fields
                $skip_unique_meta_name_list_check = apply_filters( 'wppb_skip_unique_meta_name_list_check', array( 'Map', 'Honeypot' ) );

				// if the desired meta name is in the restricted list or if one of the meta names in the strictly
                // restricted list is part of it display an error message
                $is_reserved_meta_name = false;
                $trimmed_meta_name = trim( $posted_values['meta-name'] );
                if ( !in_array( $posted_values['field'], $skip_unique_meta_name_list_check ) ) {
                    foreach ($reserved_meta_name_list as $reserved_meta_name) {
                        if (in_array($trimmed_meta_name, $reserved_meta_name_list) ||
                            (strpos($trimmed_meta_name, $reserved_meta_name) !== false && in_array($reserved_meta_name, $reserved_meta_name_list_strict))) {
                            $is_reserved_meta_name = true;
                            break;
                        }
                    }
                }
				if ( $is_reserved_meta_name )
					$message .= __( "That meta-name can't be used, please choose another\n", 'profile-builder' );

				else{
					$found_in_custom_fields = false;

					if( $all_fields != 'not_set' )
						foreach( $all_fields as $field ){
							if ( $posted_values['meta-name'] != '' && ( $field['meta-name'] == $posted_values['meta-name'] ) && ( $field['id'] != $posted_values['id'] ) ){
								$message .= __( "That meta-name is already in use\n", 'profile-builder' );
								$found_in_custom_fields = true;

							}elseif ( ( $field['meta-name'] == $posted_values['meta-name'] ) && ( $field['id'] == $posted_values['id'] ) )
								$found_in_custom_fields = true;
						}

					if ( $found_in_custom_fields === false ){
                        if( $posted_values['meta-name'] != '' ) {
                            $found_meta_name = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->usermeta WHERE meta_key = %s", $posted_values['meta-name']));
                            if ($found_meta_name != null)
                                $message .= __("That meta-name is already in use\n", 'profile-builder');
                        }
					}
				}
			}
		}
		//END check duplicate meta-name

		// check for correct meta name on upload field
		if( $posted_values['field'] == 'Upload' ) {
			if( ! empty( $posted_values['meta-name'] ) && preg_match( '/([^a-z\d_-])/', $posted_values['meta-name'] ) ) {
				$message .= __( "The meta-name can only contain lowercase letters, numbers, _ , - and no spaces.\n", 'profile-builder' );
			}
		}
		// END check for correct meta name on upload field

		// check for valid default option (checkbox, select, radio)
		if ( ( $posted_values['field'] == 'Checkbox' ) || ( $posted_values['field'] == 'Select (Multiple)' ) ) {
			$options = array_map( 'trim', explode( ',', $posted_values['options'] ) );
			$default_options = ( ( trim( $posted_values['default-options'] ) == '' ) ? array() : array_map( 'trim', explode( ',', $posted_values['default-options'] ) ) );

			/* echo "<script>console.log(  Posted options: " . print_r($options, true) . "' );</script>";
			echo "<script>console.log(  Default options: " . print_r($default_options, true) . "' );</script>"; */

			$not_found = '';
			foreach ( $default_options as $key => $value ){
				if ( !in_array( $value, $options ) )
					$not_found .= $value . ', ';
			}

			if ( $not_found != '' )
				$message .= sprintf( __( "The following option(s) did not coincide with the ones in the options list: %s\n", 'profile-builder' ), trim( $not_found, ', ' ) );

		}elseif ( ( $posted_values['field'] == 'Radio' ) || ( $posted_values['field'] == 'Select' ) ){
			if ( ( trim( $posted_values['default-option'] ) != '' )  && ( !in_array( $posted_values['default-option'], array_map( 'trim', explode( ',', $posted_values['options'] ) ) ) ) )
				$message .= sprintf( __( "The following option did not coincide with the ones in the options list: %s\n", 'profile-builder' ), $posted_values['default-option'] );
		}
		// END check for valid default option (checkbox, select, radio)

        // check to see if any user role is selected (user-role field)
        if( $posted_values['field'] == 'Select (User Role)' ) {
            if( empty( $posted_values['user-roles'] ) ) {
                $message .= __( "Please select at least one user role\n", 'profile-builder' );
            }
        }
        // END check to see if Administrator user role has been selected (user-role field)

        $message = apply_filters( 'wppb_check_extra_manage_fields', $message, $posted_values );

	}elseif ( ( $meta_name == 'wppb_rf_fields' ) || ( $meta_name == 'wppb_epf_fields' ) ){
		if ( $posted_values['field'] == '' ){
			$message .= __( "You must select a field\n", 'profile-builder' );

		}else{
			$fields_so_far = get_post_meta ( $post_id, $meta_name, true );
            if ( !empty($fields_so_far) ){
                foreach ( $fields_so_far as $key => $value ){
                    if ( $value['id'] == $posted_values['id'] )
                        $message .= __( "That field is already added in this form\n", 'profile-builder' );
                }
            }
		}
	}
	return $message;
}
add_filter( 'wck_extra_message', 'wppb_check_field_on_edit_add', 10, 6 );


/**
 * Function that calls the wppb_hide_properties_for_already_added_fields and wppb_enable_select2 after a field-update
 *
 * @since v.2.0
 *
 * @param void
 *
 * @return string
 */
function wppb_manage_fields_after_refresh_list( $id ){
	echo "<script type=\"text/javascript\">wppb_hide_properties_for_already_added_fields( '#container_wppb_manage_fields' );</script>";
	echo "<script type=\"text/javascript\">wppb_enable_select2( '#wppb_manage_fields' );</script>";

}
add_action( "wck_refresh_list_wppb_manage_fields", "wppb_manage_fields_after_refresh_list" );
add_action( "wck_refresh_entry_wppb_manage_fields", "wppb_manage_fields_after_refresh_list" );

/**
 * Function that calls the wppb_hide_all and wppb_enable_select2
 *
 * @since v.2.0
 *
 * @param void
 *
 * @return string
 */
function wppb_manage_fields_after_ajax_add_field( $id ){
	echo "<script type=\"text/javascript\">wppb_hide_all( '#wppb_manage_fields' );</script>";
    echo "<script type=\"text/javascript\">wppb_enable_select2( '#wppb_manage_fields' );</script>";
}
add_action("wck_ajax_add_form_wppb_manage_fields", "wppb_manage_fields_after_ajax_add_field" );

/**
 * Function that modifies the table header in Manage Fields to add Field Name, Field Type, Meta Key, Required
 *
 * @since v.2.0
 *
 * @param $list, $id
 *
 * @return string
 */
function wppb_manage_fields_header( $list_header ){
	return '<thead><tr><th class="wck-number">#</th><th class="wck-content">'. __( '<pre>Title</pre><pre>Type</pre><pre>Meta Name</pre><pre class="wppb-mb-head-required">Required</pre>', 'profile-builder' ) .'</th><th class="wck-edit">'. __( 'Edit', 'profile-builder' ) .'</th><th class="wck-delete">'. __( 'Delete', 'profile-builder' ) .'</th></tr></thead>';
}
add_action( 'wck_metabox_content_header_wppb_manage_fields', 'wppb_manage_fields_header' );

/**
 * Add contextual help to the side of manage fields for the shortcodes
 *
 * @since v.2.0
 *
 * @param $hook
 *
 * @return string
 */
function wppb_add_content_before_manage_fields(){
?>
    <p><?php esc_html_e('Use these shortcodes on the pages you want the forms to be displayed:', 'profile-builder'); ?></p>
        <ul>
            <li><strong class="nowrap">[wppb-register]</strong></li>
            <li><strong class="nowrap">[wppb-edit-profile]</strong></li>
            <li><strong class="nowrap">[wppb-register role="author"]</strong></li>
        </ul>
    <p>
    <?php
    $versions = array( 'Profile Builder Pro', 'Profile Builder Elite', 'Profile Builder Unlimited', 'Profile Builder Dev' );

	if( in_array( PROFILE_BUILDER, $versions ) )
        esc_html_e("If you're interested in displaying different fields in the registration and edit profile forms, please use the Multiple Registration & Edit Profile Forms Add-ons.", 'profile-builder');
    else
        esc_html_e( "With Profile Builder Pro you can display different fields in the registration and edit profile forms, using the Multiple Registration & Edit Profile Forms add-on.", "profile-builder" )
    ?>
    </p>
<?php
}
add_action('wck_metabox_content_wppb_manage_fields_info', 'wppb_add_content_before_manage_fields');


/**
 * Function that calls the wppb_edit_form_properties
 *
 * @since v.2.0
 *
 * @param void
 *
 * @return string
 */
function wppb_remove_properties_from_added_form( $meta_name, $id, $element_id ){
    if ( ( $meta_name == 'wppb_epf_fields' ) || ( $meta_name == 'wppb_rf_fields' ) )
        echo "<script type=\"text/javascript\">wppb_disable_delete_on_default_mandatory_fields();</script>";

    if ( $meta_name == 'wppb_manage_fields' ) {
        echo "<script type=\"text/javascript\">wppb_edit_form_properties( '#container_wppb_manage_fields', 'update_container_wppb_manage_fields_" . esc_attr( $element_id ) . "' );</script>";
        echo "<script type=\"text/javascript\">wppb_enable_select2( '#container_wppb_manage_fields' );</script>";
    }
}
add_action("wck_after_adding_form", "wppb_remove_properties_from_added_form", 10, 3);

/*
 * WPML Support for dynamic strings in Profile Builder. Tags: WPML, fields, translate
 */
add_filter( 'update_option_wppb_manage_fields', 'wppb_wpml_compat_with_fields', 10, 2 );
function wppb_wpml_compat_with_fields( $oldvalue, $_newvalue ){
    $default_fields = 	array(
							'Default - Name (Heading)',
							'Default - Contact Info (Heading)',
							'Default - About Yourself (Heading)',
							'Default - Username',
							'Default - First Name',
							'Default - Last Name',
							'Default - Nickname',
							'Default - E-mail',
							'Default - Website',
							'Default - AIM',
							'Default - Yahoo IM',
							'Default - Jabber / Google Talk',
							'Default - Password',
							'Default - Repeat Password',
							'Default - Biographical Info',
							'Default - Blog Details',
							'Default - Display name publicly as'
	);

	if ( is_array( $_newvalue ) ){
        foreach ( $_newvalue as $field ){
			if ( in_array($field['field'], $default_fields) ){
				$prefix = 'default_field_';
			} else {
				$prefix = 'custom_field_';
			}
            if (function_exists('wppb_icl_register_string')){
                if( !empty( $field['field-title'] ) )
                    wppb_icl_register_string('plugin profile-builder-pro', $prefix . $field['id'].'_title_translation' , $field['field-title'] );
                if( !empty( $field['description'] ) )
                    wppb_icl_register_string('plugin profile-builder-pro', $prefix . $field['id'].'_description_translation', $field['description'] );
                if( !empty( $field['labels'] ) )
                    wppb_icl_register_string('plugin profile-builder-pro', $prefix . $field['id'].'_labels_translation', $field['labels'] );
                if( !empty( $field['default-value'] ) )
                    wppb_icl_register_string('plugin profile-builder-pro', $prefix . $field['id'].'_default_value_translation', $field['default-value'] );
                if( !empty( $field['default-content'] ) )
                    wppb_icl_register_string('plugin profile-builder-pro', $prefix . $field['id'].'_default_content_translation', $field['default-content'] );
                if( !empty( $field['html-content'] ) )
                    wppb_icl_register_string('plugin profile-builder-pro', $prefix . $field['id'].'_html_content_translation', $field['html-content'] );
            }
        }
    }
}


/*
 * Returns the HTML for a map given the field
 *
 */
function wppb_get_map_output( $field, $args ) {

    $defaults = array(
        'markers'     => array(),
        'editable'    => true,
        'show_search' => true,
        'extra_attr'  => ''
    );

    $args = wp_parse_args( $args, $defaults );

    $return = '';

	// Search box
	// The style:left=-99999px is set to hide the input from the viewport. It will be rewritten when the map gets initialised
	if ( $args['show_search'] ) {
		$return .= '<input style="left: -99999px" type="text" id="' . $field['meta-name'] . '-search-box" class="wppb-map-search-box" placeholder="' . __( 'Search Location', 'profile-builder' ) . '" />';
	}

	$lng  = ( ! empty( $field['map-default-lng'] ) ) ? (float) $field['map-default-lng'] : WPPB_DEFAULTS_MAP_LNG;
	$lat  = ( ! empty( $field['map-default-lat'] ) ) ? (float) $field['map-default-lat'] : WPPB_DEFAULTS_MAP_LAT;
	$zoom = ( ! empty( $field['map-default-zoom'] ) ) ? (int) $field['map-default-zoom'] : WPPB_DEFAULTS_MAP_ZOOM;

	// Get one time the settings.
	$settings = wppb_options_get_map_settings();
	$pin_info = ( ! empty( $args['user_id'] ) ) ? wppb_compute_simple_pin_content_for_user( $args['user_id'], $settings ) : '';

	// Map container.
	$return .= '<div id="' . $field['meta-name'] . '" class="wppb-map-container" style="height: ' . $field['map-height'] . 'px;" data-editable="' . ( $args['editable'] ? 1 : 0 ) . '" data-default-zoom="' . $zoom . '" data-default-lat="' . $lat . '" data-default-lng="' . $lng . '" ' . $args['extra_attr'] . '></div>';

	if ( ! empty( $args['markers'] ) ) {
		foreach ( $args['markers'] as $marker ) {
			if ( ! empty( $args['user_id'] ) ) {
				// Also compute the marker bubble content.
				$bubble = wppb_get_user_pin_bubble(
					$args['user_id'],
					strstr( $marker, ',', true ),
					ltrim( strstr( $marker, ',' ), ',' ),
					$pin_info,
					''
				);
				$return .= $bubble;
			}

			$return .= '<input name="' . $field['meta-name'] . '[]" type="hidden" class="wppb-map-marker" value="' . $marker . '" />';
		}
	}

	return $return;
}


/*
 * Returns all the saved markers for a map field for a particular user
 *
 */
function wppb_get_user_map_markers( $user_id, $meta_name ) {

    global $wpdb;

    $meta_name_underlined = $meta_name . '_';

    $results = $wpdb->get_results( "SELECT meta_value, meta_key FROM {$wpdb->usermeta} WHERE user_id={$user_id} AND meta_key LIKE '%{$meta_name_underlined}%'", ARRAY_N );

	$markers = array();
	$i = 0;

    foreach( $results as $key => $result ) {
		$pattern = '/^' . $meta_name . '_[0-9]+(_[0-9]+)*$/';
		preg_match( $pattern, $result[1], $matches );
		if ( count ($matches) > 0 ) {
			$markers[$i] = $result[0];
			$i++;
		}

	}
    return $markers;

}

/*
 * Deletes from the database all saved markers
 *
 */
function wppb_delete_user_map_markers( $user_id, $meta_name ) {

    global $wpdb;

    $delete = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->usermeta} WHERE user_id=%d AND meta_key REGEXP %s", $user_id, $meta_name . '_[0-9]' ) );

    wp_cache_delete( $user_id, 'user_meta' );

}


if ( ! function_exists( 'wppb_options_get_map_settings' ) ) {
    /**
     * Get all the map related attributes.
     *
     * @return string
     */
    function wppb_options_get_map_settings() {
        $manage_fields = apply_filters(
            'wppb_form_fields',
            get_option( 'wppb_manage_fields', 'not_set' ),
            array( 'context' => 'map_api_key' )
        );
        foreach ( $manage_fields as $field ) {
            if ( ! empty( $field['map-api-key'] ) ) {
                // Fail-fast. This is what we need.
                return $field;
            }
        }

        // Fallback.
        return null;
    }
}

if ( ! function_exists( 'wppb_get_user_pin_bubble' ) ) {
    /**
     * Returns the map usable marker info.
     *
     * @param  integer $id   The user ID.
     * @param  float   $lat  The latitude.
     * @param  float   $lng  The longitude.
     * @param  string  $info The bubble inner info.
     * @param  string  $icon The pin custom icon.
     * @return string
     */
    function wppb_get_user_pin_bubble( $id, $lat, $lng, $info, $icon = '' ) {
        return '<div class="marker marker-for-user-' . (int) $id . ' marker-content-info" data-lat="' . esc_attr( $lat ) . '" data-lng="' . esc_attr( $lng ) . '" data-icon="' . esc_attr( $icon ) . '"><div class="marker-content">' . wp_kses_post( $info ) . '</div></div>';
    }
}

if ( ! function_exists( 'wppb_compute_simple_pin_content_for_user' ) ) {
    /**
     * Compute simple pin content for user.
     *
     * @param  integer $id_user  User ID.
     * @param  array   $settings Settings array.
     * @return string
     */
    function wppb_compute_simple_pin_content_for_user( $id_user, $settings = array() ) {

        if ( empty( $id_user ) ) {
            // Fail-fast.
            return '';
        }
        if ( empty( $settings ) ) {
            // Get one time the settings.
            $settings = wppb_options_get_map_settings();
        }

        $args   = ( ! empty( $settings['map-bubble-fields'] ) ) ? explode( ', ', $settings['map-bubble-fields'] ) : array();
        $result = '';
        if ( ! empty( $args ) ) {
            foreach ( $args as $tag ) {
                // Go to souce.
                if ( 'avatar_or_gravatar' == $tag ) {
                    $val = apply_filters( 'mustache_variable_avatar_or_gravatar', '', $tag, array(), array(
                        'user_id' => $id_user,
                    ) );
                } else {
                    $val = apply_filters( 'mustache_variable_default_user_field', '', $tag, array(), array(
                        'user_id' => $id_user,
                    ) );
                }
                if ( ! empty( $val ) ) {
                    $result .= '<p class="marker-info-' . esc_attr( $tag ) . '">' . wp_kses_post( $val ) . '</p>';
                }
            }
        }

        return $result;
    }
}


/**
 * Disable the add button again after we added a field
 */
add_action( 'wck_ajax_add_form_wppb_manage_fields', 'wppb_redisable_the_add_button' );
function wppb_redisable_the_add_button(){
	?>
	<script>wppb_disable_add_entry_button ( '#wppb_manage_fields' );</script>
	<?php
}


/**
 * Function that updates the meta_key of a field in the usertmeta table when it was changed for a field. It is turned off by default
 */
add_action( 'wck_before_update_meta', 'wppb_change_field_meta_key', 10, 4 );
function wppb_change_field_meta_key( $meta, $id, $values, $element_id ){
	if( apply_filters( 'wppb_update_field_meta_key_in_db', false ) ) {
		if ($meta == 'wppb_manage_fields') {
			global $wpdb;
			$wppb_manage_fields = get_option('wppb_manage_fields');
			if (!empty($wppb_manage_fields)) {

				/*
				 * We need to be sure this is not a repeater.
				 * The way we're doing this is by checking if the $values['meta-name'] is found inside $wppb_manage_fields
				 * If it's not, it's probably inside a repeater.
				 *
				 * The reason we're doing this is because when we're updating a repeater field the $meta is still  'wppb_manage_fields' for some reason, while $element_id comes from the position of the repeater.
				 *
				 * This means we're updating last_name (if it's the 9'th position in wppb_manage_fields) instead of 'something_repeater' that's also in the 9'th position but inside the repeater group.
				 *
				 * Also, this code didn't account for 'something_repeater_1' etc, meaning it would have changed just 1 field, causing data loss anyway.
				 *
				 */
				$is_not_repeater = false;
				foreach ($wppb_manage_fields as $field){
					if ( $field['meta-name'] == $values['meta-name'] ){
						$is_not_repeater = true;
					}
				}

				if (!empty($values['meta-name']) && $wppb_manage_fields[$element_id]['meta-name'] != $values['meta-name'] && $is_not_repeater ) {
					$wpdb->update(
						$wpdb->usermeta,
						array('meta_key' => sanitize_text_field($values['meta-name'])),
						array('meta_key' => sanitize_text_field($wppb_manage_fields[$element_id]['meta-name']))
					);
				} else {
					return;
				}
			}
		}
	}
}


if ( ! function_exists( 'wppb_prepare_key_value_options' ) ) {
	/**
	 * Convert an array with keys and values into a usable option for the defined fields types.
	 *
	 * @param  arrays $array List of options.
	 * @return array         Usable options.
	 */
	function wppb_prepare_key_value_options( $array = array() ) {
		$result = array();
		if ( ! empty( $array ) ) {
			foreach ( $array as $name => $label ) {
				$result[] = '%' . wppb_prepare_wck_labels( $label ) . '%' . $name;
			}
		}
		return $result;
	}
}

if ( ! function_exists( 'wppb_filter_map_bubble_fields' ) ) {
	/**
	 * Filter the map POI's bubble content.
	 *
	 * @param  arrays $tags List of available tags.
	 * @return array        Filtered and sorted tags.
	 */
	function wppb_filter_map_bubble_fields( $tags ) {
		// Attempt to preseve one tag example.
		$maybe_avatar = ( ! empty( $tags['avatar_or_gravatar'] ) ) ? $tags['avatar_or_gravatar'] : false;

		// Remove some of the tags.
		unset( $tags['meta_user_name'] );
		unset( $tags['more_info_url'] );
		unset( $tags['avatar_or_gravatar'] );

		if ( ! empty( $maybe_avatar ) ) {
			// Perhaps preprend this to the list.
			$tags = array_merge( array( 'avatar_or_gravatar' => $maybe_avatar ), $tags );
		}

		return $tags;
	}
}
add_filter( 'wppb_map_bubble_fields', 'wppb_filter_map_bubble_fields' );
