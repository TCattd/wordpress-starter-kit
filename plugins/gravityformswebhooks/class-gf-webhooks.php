<?php

defined( 'ABSPATH' ) || die();

// Load Feed Add-On Framework.
GFForms::include_feed_addon_framework();

/**
 * Webhooks integration using the Add-On Framework.
 *
 * @see GFFeedAddOn
 */
class GF_Webhooks extends GFFeedAddOn {

	/**
	 * Defines the version of the Webhooks Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_version Contains the version, defined in webhooks.php
	 */
	protected $_version = GF_WEBHOOKS_VERSION;

	/**
	 * Defines the minimum Gravity Forms version required.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_min_gravityforms_version The minimum version required.
	 */
	protected $_min_gravityforms_version = '2.2';

	/**
	 * Defines the plugin slug.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_slug The slug used for this plugin.
	 */
	protected $_slug = 'gravityformswebhooks';

	/**
	 * Defines the main plugin file.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_path The path to the main plugin file, relative to the plugins folder.
	 */
	protected $_path = 'gravityformswebhooks/webhooks.php';

	/**
	 * Defines the full path to this class file.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_full_path The full path.
	 */
	protected $_full_path = __FILE__;

	/**
	 * Defines the URL where this Add-On can be found.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string
	 */
	protected $_url = 'http://www.gravityforms.com';

	/**
	 * Defines the title of this Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_title The title of the Add-On.
	 */
	protected $_title = 'Gravity Forms Webhooks Add-On';

	/**
	 * Defines the short title of this Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_title The short title of the Add-On.
	 */
	protected $_short_title = 'Webhooks';

	/**
	 * Contains an instance of this class, if available.
	 *
	 * @since  1.0
	 * @access private
	 * @var    object $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;

	/**
	 * Defines if Add-On should use Gravity Forms servers for update data.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    bool
	 */
	protected $_enable_rg_autoupgrade = true;

	/**
	 * Defines if feeds can be processed asynchronously.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    bool
	 */
	protected $_async_feed_processing = true;

	/**
	 * Defines the capability needed to access the Add-On settings page.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_capabilities_settings_page The capability needed to access the Add-On settings page.
	 */
	protected $_capabilities_settings_page = 'gravityforms_webhooks';

	/**
	 * Defines the capability needed to access the Add-On form settings page.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_capabilities_form_settings The capability needed to access the Add-On form settings page.
	 */
	protected $_capabilities_form_settings = 'gravityforms_webhooks';

	/**
	 * Defines the capability needed to uninstall the Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_capabilities_uninstall The capability needed to uninstall the Add-On.
	 */
	protected $_capabilities_uninstall = 'gravityforms_webhooks_uninstall';

	/**
	 * Defines the capabilities to add to roles by the Members plugin.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    array $_capabilities Capabilities to add to roles by the Members plugin.
	 */
	protected $_capabilities = array( 'gravityforms_webhooks', 'gravityforms_webhooks_uninstall' );

	/**
	 * Get instance of this class.
	 *
	 * @since  1.0
	 * @access public
	 * @static
	 *
	 * @return $_instance
	 */
	public static function get_instance() {

		if ( null === self::$_instance ) {
			self::$_instance = new self;
		}

		return self::$_instance;

	}

	/**
	 * Enqueue needed stylesheets.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return array
	 */
	public function styles() {

		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';

		$styles = array(
			array(
				'handle'  => $this->_slug . '_form_settings',
				'src'     => $this->get_base_url() . "/css/form_settings{$min}.css",
				'version' => $this->_version,
				'enqueue' => array( array( 'admin_page' => array( 'form_settings' ) ) ),
			),
		);

		return array_merge( parent::styles(), $styles );

	}

	/**
	 * Return the scripts which should be enqueued.
	 *
	 * @since 1.5
	 *
	 * @return array
	 */
	public function scripts() {
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';

		$scripts = array(
			array(
				'handle'  => 'gform_webhooks_merge_tags',
				'src'     => $this->get_base_url() . "/js/gfwebhooks_merge_tags{$min}.js",
				'version' => $this->_version,
				'deps'    => array( 'jquery' ),
				'enqueue' => array(
					array( 'admin_page' => array( 'form_settings' ) ),
				),
				'strings' => array(
					'ajax_url_label'     => esc_html__( 'Admin Ajax URL', 'gravityformswebhooks' ),
					'rest_api_url_label' => esc_html__( 'REST API URL', 'gravityformswebhooks' ),
				),
			),
		);

		return array_merge( parent::scripts(), $scripts );
	}

	/**
	 * Plugin starting point. Handles hooks and loading of language files.
	 *
	 * @since 1.1.6 Added PayPal delay support.
	 * @since 1.1.5 Added support for the {admin_ajax_url} and {rest_api_url} merge tags.
	 */
	public function init() {
		parent::init();

		if ( $this->is_gravityforms_supported() ) {
			add_filter( 'gform_pre_replace_merge_tags', array( $this, 'replace_merge_tags' ), 10, 7 );

			$this->add_delayed_payment_support(
				array(
					'option_label' => esc_html__( 'Send webhook only when payment is received.', 'gravityformswebhooks' ),
				)
			);
		}
	}

	/**
	 * Return the plugin's icon for the plugin/form settings menu.
	 *
	 * @since 1.3
	 *
	 * @return string
	 */
	public function get_menu_icon() {

		return $this->is_gravityforms_supported( '2.5-beta-4' ) ? 'gform-icon--webhooks' : 'dashicons-admin-generic';

	}





	// # FEED SETTINGS -------------------------------------------------------------------------------------------------

	/**
	 * Setup fields for feed settings.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @uses GF_Webhooks::get_header_choices()
	 *
	 * @return array
	 */
	public function feed_settings_fields() {

		// Prepare dependency for Request Body.
		if ( version_compare( GFForms::$version, '2.5-dev-1', '<' ) ) {
			$body_deps = array( 'field' => 'requestBodyType', 'values' => array( 'select_fields' ) );
		} else {
			$body_deps = array(
				'live'   => true,
				'fields' => array(
					array(
						'field'  => 'requestBodyType',
						'values' => array( 'select_fields' ),
					),
				),
			);
		}

		return array(
			array(
				'fields' => array(
					array(
						'label'          => esc_html__( 'Name', 'gravityformswebhooks' ),
						'name'           => 'feedName',
						'type'           => 'text',
						'class'          => 'medium',
						'required'       => true,
						'tooltip'        => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Name', 'gravityformswebhooks' ),
							esc_html__( 'Enter a feed name to uniquely identify this setup.', 'gravityformswebhooks' )
						),
					),
				),
			),
			array(
				'fields' => array(
					array(
						'label'          => esc_html__( 'Request URL', 'gravityformswebhooks' ),
						'name'           => 'requestURL',
						'type'           => 'text',
						'class'          => 'large merge-tag-support mt-position-right mt-hide_all_fields',
						'required'       => true,
						'tooltip'        => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Request URL', 'gravityformswebhooks' ),
							esc_html__( 'Enter the URL to be used in the webhook request.', 'gravityformswebhooks' )
						),
						'validation_callback' => array( $this, 'validate_request_url' ),
					),
					array(
						'label'          => esc_html__( 'Request Method', 'gravityformswebhooks' ),
						'name'           => 'requestMethod',
						'type'           => 'select',
						'default_value'  => 'POST',
						'required'       => true,
						'tooltip'        => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Request Method', 'gravityformswebhooks' ),
							esc_html__( 'Select the HTTP method used for the webhook request.', 'gravityformswebhooks' )
						),
						'choices'        => array(
							array(
								'label' => 'GET',
								'value' => 'GET',
							),
							array(
								'label' => 'POST',
								'value' => 'POST',
							),
							array(
								'label' => 'PUT',
								'value' => 'PUT',
							),
							array(
								'label' => 'PATCH',
								'value' => 'PATCH',
							),
							array(
								'label' => 'DELETE',
								'value' => 'DELETE',
							),
						),
					),
					array(
						'label'          => esc_html__( 'Request Format', 'gravityformswebhooks' ),
						'name'           => 'requestFormat',
						'type'           => 'select',
						'default_value'  => 'json',
						'required'       => true,
						'tooltip'        => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Request Format', 'gravityformswebhooks' ),
							esc_html__( 'Select the format for the webhook request.', 'gravityformswebhooks' )
						),
						'choices'        => array(
							array(
								'label' => esc_html__( 'JSON', 'gravityformswebhooks' ),
								'value' => 'json',
							),
							array(
								'label' => esc_html__( 'FORM', 'gravityformswebhooks' ),
								'value' => 'form',
							),
						),
					),
				),
			),
			array(
				'fields' => array(
					array(
						'label'          => esc_html__( 'Request Headers', 'gravityformswebhooks' ),
						'name'           => 'requestHeaders',
						'type'           => 'generic_map',
						'required'       => false,
						'merge_tags'     => true,
						'tooltip'        => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Request Headers', 'gravityformswebhooks' ),
							esc_html__( 'Setup the HTTP headers to be sent with the webhook request.', 'gravityformswebhooks' )
						),
						'key_field'      => array(
							'choices'      => $this->get_header_choices(),
							'custom_value' => true,
							'title'        => esc_html__( 'Name', 'gravityformswebhooks' ),
						),
						'value_field'    => array(
							'choices'      => 'form_fields',
							'custom_value' => true,
						),
					),
				),
			),
			array(
				'fields' => array(
					array(
						'label'          => esc_html__( 'Request Body', 'gravityformswebhooks' ),
						'name'           => 'requestBodyType',
						'type'           => 'radio',
						'default_value'  => 'all_fields',
						'horizontal'     => true,
						'required'       => true,
						'onchange'       => version_compare( GFForms::$version, '2.5-dev-1', '<' ) ? "jQuery(this).closest('form').submit();" : null,
						'tooltip'        => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Request Body', 'gravityformswebhooks' ),
							esc_html__( 'Select if all fields or select fields should be sent with the webhook request.', 'gravityformswebhooks' )
						),
						'choices'        => array(
							array(
								'label' => esc_html__( 'All Fields', 'gravityformswebhooks' ),
								'value' => 'all_fields',
							),
							array(
								'label' => esc_html__( 'Select Fields', 'gravityformswebhooks' ),
								'value' => 'select_fields',
							),
						),
					),
					array(
						'label'          => esc_html__( 'Field Values', 'gravityformswebhooks' ),
						'name'           => 'fieldValues',
						'type'           => 'generic_map',
						'required'       => true,
						'merge_tags'     => true,
						'dependency'     => $body_deps,
						'tooltip'        => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Field Values', 'gravityformswebhooks' ),
							esc_html__( 'Setup the fields to be sent in the webhook request.', 'gravityformswebhooks' )
						),
						'value_field'    => array(
							'choices'      => 'form_fields',
							'custom_value' => true,
						),
					),
				),
			),
			array(
				'fields' => array(
					array(
						'name'           => 'feedCondition',
						'type'           => 'feed_condition',
						'label'          => esc_html__( 'Webhook Condition', 'gravityformswebhooks' ),
						'checkbox_label' => esc_html__( 'Enable Condition', 'gravityformswebhooks' ),
						'instructions'   => esc_html__( 'Execute Webhook if', 'gravityformswebhooks' ),
						'tooltip'        => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Conditional Logic', 'gravityforms' ),
							esc_html__( 'When conditions are enabled, the Webhook will only be executed when the conditions are met. When disabled, the Webhook will be executed for every form submission.', 'gravityforms' )
						),
					),
				),
			),
		);

	}

	/**
	 * Setup columns for feed list table.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return array
	 */
	public function feed_list_columns() {

		return array(
			'feedName'   => esc_html__( 'Name', 'gravityformswebhooks' ),
			'requestURL' => esc_html__( 'Request URL', 'gravityformswebhooks' ),
		);

	}

	/**
	 * Prepares common HTTP header names as choices.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return array
	 */
	public function get_header_choices() {

		return array(
			array(
				'label' => esc_html__( 'Select a Name', 'gravityformswebhooks' ),
				'value' => '',
			),
			array(
				'label' => 'Accept',
				'value' => 'Accept',
			),
			array(
				'label' => 'Accept-Charset',
				'value' => 'Accept-Charset',
			),
			array(
				'label' => 'Accept-Encoding',
				'value' => 'Accept-Encoding',
			),
			array(
				'label' => 'Accept-Language',
				'value' => 'Accept-Language',
			),
			array(
				'label' => 'Accept-Datetime',
				'value' => 'Accept-Datetime',
			),
			array(
				'label' => 'Authorization',
				'value' => 'Authorization',
			),
			array(
				'label' => 'Cache-Control',
				'value' => 'Cache-Control',
			),
			array(
				'label' => 'Connection',
				'value' => 'Connection',
			),
			array(
				'label' => 'Cookie',
				'value' => 'Cookie',
			),
			array(
				'label' => 'Content-Length',
				'value' => 'Content-Length',
			),
			array(
				'label' => 'Content-Type',
				'value' => 'Content-Type',
			),
			array(
				'label' => 'Date',
				'value' => 'Date',
			),
			array(
				'label' => 'Expect',
				'value' => 'Expect',
			),
			array(
				'label' => 'Forwarded',
				'value' => 'Forwarded',
			),
			array(
				'label' => 'From',
				'value' => 'From',
			),
			array(
				'label' => 'Host',
				'value' => 'Host',
			),
			array(
				'label' => 'If-Match',
				'value' => 'If-Match',
			),
			array(
				'label' => 'If-Modified-Since',
				'value' => 'If-Modified-Since',
			),
			array(
				'label' => 'If-None-Match',
				'value' => 'If-None-Match',
			),
			array(
				'label' => 'If-Range',
				'value' => 'If-Range',
			),
			array(
				'label' => 'If-Unmodified-Since',
				'value' => 'If-Unmodified-Since',
			),
			array(
				'label' => 'Max-Forwards',
				'value' => 'Max-Forwards',
			),
			array(
				'label' => 'Origin',
				'value' => 'Origin',
			),
			array(
				'label' => 'Pragma',
				'value' => 'Pragma',
			),
			array(
				'label' => 'Proxy-Authorization',
				'value' => 'Proxy-Authorization',
			),
			array(
				'label' => 'Range',
				'value' => 'Range',
			),
			array(
				'label' => 'Referer',
				'value' => 'Referer',
			),
			array(
				'label' => 'TE',
				'value' => 'TE',
			),
			array(
				'label' => 'User-Agent',
				'value' => 'User-Agent',
			),
			array(
				'label' => 'Upgrade',
				'value' => 'Upgrade',
			),
			array(
				'label' => 'Via',
				'value' => 'Via',
			),
			array(
				'label' => 'Warning',
				'value' => 'Warning',
			),
		);

	}

	/**
	 * Enable feed duplication.
	 *
	 * @since  1.1.3
	 * @access public
	 *
	 * @param int|array $id The ID of the feed to be duplicated or the feed object when duplicating a form.
	 *
	 * @return bool
	 */
	public function can_duplicate_feed( $id ) {

		return true;

	}



	// # FEED PROCESSING -----------------------------------------------------------------------------------------------

	/**
	 * Determines if feed processing should happen asynchronously.
	 *
	 * @since 1.1.6 Added PayPal delay support.
	 * @since 1.0
	 *
	 * @param array $feed  The feed currently being processed.
	 * @param array $entry The entry currently being processed.
	 * @param array $form  The form currently being processed.
	 *
	 * @return bool
	 */
	public function is_asynchronous( $feed, $entry, $form ) {
		if ( $this->_bypass_feed_delay ) {
			return false;
		}

		return parent::is_asynchronous( $feed, $entry, $form );
	}

	/**
	 * Send webhook request.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param array $feed  The current Feed object.
	 * @param array $entry The current Entry object.
	 * @param array $form  The current Form object.
	 *
	 * @uses GFAddOn::log_debug()
	 * @uses GFAddOn::log_error()
	 * @uses GFCommon::replace_variables()
	 * @uses GFFeedAddOn::add_feed_error()
	 * @uses GF_Webhooks::get_request_data()
	 */
	public function process_feed( $feed, $entry, $form ) {

		// Get request method.
		$request_method = rgars( $feed, 'meta/requestMethod' );

		/**
		 * Modify the webhook HTTP request method.
		 *
		 * @since 1.0
		 * @param string $request_method HTTP request method.
		 * @param array  $feed           The current Feed object.
		 * @param array  $entry          The current Entry object.
		 * @param array  $form           The current Form object.
		 */
		$request_method = gf_apply_filters( array( 'gform_webhooks_request_method', $form['id'] ), $request_method, $feed, $entry, $form );

		// Convert request method to uppercase.
		$request_method = strtoupper( $request_method );

		// Get request headers.
		$request_headers = $this->get_generic_map_fields( $feed, 'requestHeaders', $form, $entry );

		// Remove request headers with undefined name.
		unset( $request_headers[ null ] );

		/**
		 * Modify the webhook HTTP request headers.
		 *
		 * @since 1.0
		 * @param array $request_headers HTTP request headers.
		 * @param array $feed           The current Feed object.
		 * @param array $entry          The current Entry object.
		 * @param array $form           The current Form object.
		 */
		$request_headers = gf_apply_filters( array( 'gform_webhooks_request_headers', $form['id'] ), $request_headers, $feed, $entry, $form );

		// Get request data.
		$request_data = $this->get_request_data( $feed, $entry, $form );

		// Get request URL and replace merge tags.
		$request_url = rgars( $feed, 'meta/requestURL' );
		$request_url = GFCommon::replace_variables( $request_url, $form, $entry, false, true, false, 'text' );

		// If this is a GET or DELETE request, add request data to request URL.
		if ( in_array( $request_method, array( 'GET', 'DELETE' ) ) && ! empty( $request_data ) ) {
			$request_url = add_query_arg( urlencode_deep( $request_data ), $request_url );
		}

		// If this is a PUT or POST request, format request data.
		if ( in_array( $request_method, array( 'POST', 'PUT' ) ) && 'json' === $feed['meta']['requestFormat'] ) {

			// Add content type header.
			$request_headers['Content-Type'] = 'application/json';

			// Encode request data.
			$request_data = json_encode( $request_data );

		}

		/**
		 * Modify the webhook HTTP request URL.
		 *
		 * @since 1.0
		 * @param string $request_data HTTP request URL.
		 * @param array  $feed         The current Feed object.
		 * @param array  $entry        The current Entry object.
		 * @param array  $form         The current Form object.
		 */
		$request_url = apply_filters( 'gform_webhooks_request_url', $request_url, $feed, $entry, $form );

		// If feed URL is empty, log error and exit.
		if ( rgblank( $request_url ) ) {
			$this->add_feed_error( esc_html__( 'Webhook was not processed because request URL was empty.', 'gravityformswebhooks' ), $feed, $entry, $form );
			return;
		}

		// Prepare request arguments.
		$request_args = array(
			'body'      => ! in_array( $request_method, array( 'GET', 'DELETE' ) ) ? $request_data : null,
			'method'    => $request_method,
			'headers'   => $request_headers,
			'sslverify' => apply_filters( 'https_local_ssl_verify', true, $request_url ),
		);

		/**
		 * Modify the webhook HTTP request arguments.
		 *
		 * @since 1.0
		 * @param array $request_args HTTP request arguments.
		 * @param array $feed         The current Feed object.
		 * @param array $entry        The current Entry object.
		 * @param array $form         The current Form object.
		 */
		$request_args = apply_filters( 'gform_webhooks_request_args', $request_args, $feed, $entry, $form );

		// Log request we are about to run.
		$this->log_debug( __METHOD__ . '(): Sending webhook request to ' . $request_url . '; ' . print_r( $request_args, true ) );

		// Execute request.
		$response = wp_remote_request( $request_url, $request_args );

		// Log error or success based on response.
		if ( is_wp_error( $response ) ) {
			$this->add_feed_error( sprintf( esc_html__( 'Webhook was not successfully executed. %s (%d)', 'gravityformswebhooks' ), $response->get_error_message(), $response->get_error_code() ), $feed, $entry, $form );
		} else {
			$this->log_debug( sprintf( '%s(): Webhook successfully executed. code: %s; body: %s', __METHOD__, wp_remote_retrieve_response_code( $response ), wp_remote_retrieve_body( $response ) ) );
		}

		/**
		 * Fired after a Webhooks request has been executed.
		 *
		 * @since 1.1.2
		 *
		 * @param WP_Error|array $response The response or WP_Error on failure.
		 * @param array          $feed     The current Feed object.
		 * @param array          $entry    The current Entry object.
		 * @param array          $form     The current Form object.
		 */
		gf_do_action( array( 'gform_webhooks_post_request', $form['id'], $feed['id'] ), $response, $feed, $entry, $form );

	}

	/**
	 * Get data for webhook request.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param array $feed  The current Feed object.
	 * @param array $entry The current Entry object.
	 * @param array $form  The current Form object.
	 *
	 * @uses GFAddOn::get_generic_map_fields()
	 *
	 * @return array
	 */
	public function get_request_data( $feed, $entry, $form ) {

		// Get request data by body type.
		if ( 'all_fields' === $feed['meta']['requestBodyType'] ) {
			$request_data = $entry;
		} else {
			$request_data = $this->get_generic_map_fields( $feed, 'fieldValues', $form, $entry );
		}

		/**
		 * Modify the webhook HTTP request data.
		 *
		 * @since 1.0
		 * @param array $request_data HTTP request data.
		 * @param array $feed         The current Feed object.
		 * @param array $entry        The current Entry object.
		 * @param array $form         The current Form object.
		 */
		return gf_apply_filters( array( 'gform_webhooks_request_data', $form['id'] ), $request_data, $feed, $entry, $form );

	}

	// # MERGE TAGS ----------------------------------------------------------------------------------------------------

	/**
	 * Include the merge tags in the merge tag drop downs in the form settings area.
	 *
	 * @deprecated 1.5 No replacement.
	 *
	 * @param array $form The current form object.
	 *
	 * @since 1.1.5
	 *
	 * @return array
	 */
	public function add_merge_tags( $form ) {
		_deprecated_function( __METHOD__, '1.5' );
		return $form;
	}

	/**
	 * Replace the merge tags.
	 *
	 * @param string $text       The current text in which merge tags are being replaced.
	 * @param array  $form       The current form object.
	 * @param array  $entry      The current entry object.
	 * @param bool   $url_encode Whether or not to encode any URLs found in the replaced value.
	 * @param bool   $esc_html   Whether or not to encode HTML found in the replaced value.
	 * @param bool   $nl2br      Whether or not to convert newlines to break tags.
	 * @param string $format     The format requested for the location the merge is being used. Possible values: html, text or url.
	 *
	 * @since 1.1.5
	 *
	 * @return string
	 */
	public function replace_merge_tags( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {
		if ( empty( $entry ) || empty( $form ) ) {
			return $text;
		}

		$admin_ajax_url_merge_tag = '{admin_ajax_url}';
		if ( strpos( $text, $admin_ajax_url_merge_tag ) !== false ) {
			$admin_ajax_url = GFCommon::format_variable_value( admin_url( 'admin-ajax.php' ), $url_encode, $esc_html, $format, $nl2br );
			$text           = str_replace( $admin_ajax_url_merge_tag, $admin_ajax_url, $text );
		}

		$rest_api_url_merge_tag = '{rest_api_url}';
		if ( strpos( $text, $rest_api_url_merge_tag ) !== false ) {
			$rest_api_url = GFCommon::format_variable_value( rest_url(), $url_encode, $esc_html, $format, $nl2br );
			$text         = str_replace( $rest_api_url_merge_tag, $rest_api_url, $text );
		}

		return $text;
	}

	/**
	 * Validation callback for the requestURL field of the feed settings page.
	 *
	 * @since 1.5
	 *
	 * @param GF_Field $field         The field object.
	 * @param string   $setting_value The requestURL field setting.
	 */
	public function validate_request_url( $field, $setting_value ) {
		if ( rgar( $field, 'required' ) && rgblank( $setting_value ) ) {
			$this->set_field_error( $field, rgar( $field, 'error_message' ) );
			return;
		}

		$sanitized_setting_value = $this->get_sanitized_request_url( $setting_value );

		if ( $this->is_valid_request_url( $setting_value, $sanitized_setting_value ) ) {
			return;
		}

		$this->set_field_error(
			$field,
			$this->get_validation_error_message( $field, $setting_value, $sanitized_setting_value )
		);
	}

	/**
	 * Returns a sanitized request URL that allows merge tags.
	 *
	 * @since 1.5
	 *
	 * @param string $request_url The value of the requestURL field.
	 *
	 * @return string
	 */
	private function get_sanitized_request_url( $request_url ) {
		if ( ! GFCommon::has_merge_tag( $request_url ) ) {
			return esc_url_raw( $request_url );
		}

		$sanitized_value = $this->esc_url_with_merge_tags( $this->encode_spaces_in_url( $request_url ) );

		return $this->is_url_without_scheme( $request_url ) ? "http://{$sanitized_value}" : $sanitized_value;
	}

	/**
	 * Get the validation error message that displays when an invalid value is entered.
	 *
	 * @since 1.5
	 *
	 * @param GF_Field $field                   The requestURL settings field.
	 * @param string   $request_url             The string of the original requestURL.
	 * @param string   $sanitized_field_setting The string of sanitized text.
	 *
	 * @return string
	 */
	private function get_validation_error_message( $field, $request_url, $sanitized_field_setting ) {
		$script = sprintf(
			'jQuery("input[name=\"%s\"]").val(jQuery(this).data("safe"));',
			( $this->is_gravityforms_supported( '2.5' ) ? '_gform_setting_' : '_gaddon_setting_' ) . rgar( $field, 'name' )
		);

		$message = $this->is_url_without_scheme( $request_url )
			? esc_html__( 'The URL you have entered is malformed.', 'gravityformswebhooks' )
			: esc_html__( 'The URL you have entered is not valid. For security reasons, some characters are not allowed.', 'gravityformswebhooks' );

		return $message
			. sprintf(
				" <a href='javascript:void(0);' onclick='%s' data-safe='%s'>%s</a>",
				htmlspecialchars( $script, ENT_QUOTES ),
				htmlspecialchars( htmlspecialchars( $sanitized_field_setting, ENT_QUOTES ), ENT_QUOTES ),
				esc_html__( 'Fix it', 'gravityformswebhooks' )
			);
	}

	/**
	 * Modified version of the pattern from esc_url() which will retain the merge tag brackets.
	 *
	 * @since 1.5
	 *
	 * @param string $request_url The request URL.
	 *
	 * @return string
	 */
	private function esc_url_with_merge_tags( $request_url ) {
		return preg_replace( '|[^a-z0-9-~+_.?#=!&;,/{:}%@$\|*\'()\[\]\\x80-\\xff]|i', '', $request_url );
	}

	/**
	 * Replaces empty spaces in the request URL with an encoded value.
	 *
	 * @since 1.5
	 *
	 * @param string $request_url The request URL.
	 *
	 * @return string
	 */
	private function encode_spaces_in_url( $request_url ) {
		return str_replace( ' ', '%20', wp_strip_all_tags( $request_url, true ) );
	}

	/**
	 * Check whether the entered request URL is valid.
	 *
	 * @since 1.5
	 *
	 * @param string $request_url           The requestURL feed setting.
	 * @param string $sanitized_request_url The result of the requestURL santization.
	 *
	 * @return bool
	 */
	private function is_valid_request_url( $request_url, $sanitized_request_url ) {
		if ( $this->is_url_with_host_and_scheme( $request_url ) ) {
			return true;
		}

		if ( $this->is_url_without_scheme( $request_url ) ) {
			return false;
		}

		return $this->encode_spaces_in_url( $request_url ) === $sanitized_request_url;
	}

	/**
	 * Determines whether the submitted requestURL is a url with a host and a scheme.
	 *
	 * @since 1.5
	 *
	 * @param string $request_url The submitted requestURL.
	 *
	 * @return bool
	 */
	private function is_url_with_host_and_scheme( $request_url ) {
		$parsed_url = wp_parse_url( $request_url );

		if ( ! rgar( $parsed_url, 'scheme' ) ) {
			return false;
		}

		return rgar( $parsed_url, 'host' ) ? true : false !== filter_var( gethostbyname( rgar( $parsed_url, 'path' ) ), FILTER_VALIDATE_IP );
	}

	/**
	 * Determines whether the submitted requestURL is formatted as a url but which lacks a scheme.
	 *
	 * @since 1.5
	 *
	 * @param string $request_url The submitted requestURL.
	 *
	 * @return bool
	 */
	private function is_url_without_scheme( $request_url ) {
		$parsed_url = wp_parse_url( $request_url );

		if ( rgar( $parsed_url, 'host' ) ) {
			return true;
		}

		$host = explode( '/', rgar( $parsed_url, 'path' ) );

		return (
			false !== filter_var( gethostbyname( $host[0] ), FILTER_VALIDATE_IP )
			&& ! rgar( $parsed_url, 'scheme' )
		);
	}
}
