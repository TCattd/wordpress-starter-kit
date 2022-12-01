<?php
use FuerteWpDep\Carbon_Fields\Container;
use FuerteWpDep\Carbon_Fields\Field;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://actitud.xyz
 * @since      1.0.0
 *
 * @package    Fuerte_Wp
 * @subpackage Fuerte_Wp/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Fuerte_Wp
 * @subpackage Fuerte_Wp/admin
 * @author     Esteban Cuevas <esteban@attitude.cl>
 */
class Fuerte_Wp_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Fuerte_Wp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Fuerte_Wp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		//wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/fuerte-wp-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Fuerte_Wp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Fuerte_Wp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/fuerte-wp-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function fuertewp_plugin_options() {
		global $fuertewp;

		/**
		 * No admin options if main config file exists physically
		 */
		if ( file_exists( ABSPATH . 'wp-config-fuerte.php' ) && is_array( $fuertewp ) && ! empty ( $fuertewp ) ) {
			return;
		}

		$domain = $_SERVER['SERVER_NAME'];

		// Carbon Fields Custom Datastore
		//require_once FUERTEWP_PATH . 'includes/class-fuerte-wp-carbon-fields-datastore.php';
		//$FTWPDatastore = new Serialized_Theme_Options_Datastore();

		Container::make( 'theme_options', __( 'Fuerte-WP', 'fuerte-wp' ) )
			->set_page_parent( 'options-general.php' )

			->add_tab( __('Main Options', 'fuerte-wp'), array(
				Field::make( 'checkbox', 'fuertewp_status', __( 'Enable Fuerte-WP.', 'fuerte-wp' ) )
					->set_default_value( '' )
					->set_option_value( 'enabled' )
					->set_help_text( __( 'Check the option to enable Fuerte-WP.', 'fuerte-wp' ) ),

				Field::make( 'multiselect', 'fuertewp_super_users', __( 'Super Administrators.', 'fuerte-wp' ) )
					->add_options( 'fuertewp_get_admin_users' )
					->set_help_text( __( 'Users that will not be affected by Fuerte-WP rules. Only administrators emails are listed here.', 'fuerte-wp' ) ),

				Field::make( 'separator', 'fuertewp_separator_general', __( 'General', 'fuerte-wp' ) ),

				Field::make( 'text', 'fuertewp_access_denied_message', __( 'Access denied message.', 'fuerte-wp' ) )
					->set_default_value( 'Access denied.' )
					->set_help_text( __( 'General access denied message shown to non super users.', 'fuerte-wp' ) ),

				Field::make( 'text', 'fuertewp_recovery_email', __( 'Recovery email.', 'fuerte-wp' ) )
					->set_default_value( '' )
					->set_attribute( 'type', 'email' )
					/* translators: %s: site domain */
					->set_help_text( sprintf( __('Admin recovery email. If empty, dev@%s will be used.<br/>This email will receive fatal errors from WP, and not the administration email in the General Settings. Check <a href="https://make.wordpress.org/core/2019/04/16/fatal-error-recovery-mode-in-5-2/" target="_blank">fatal error recovery mode</a>.', 'fuerte-wp' ), $domain ) ),

				Field::make( 'checkbox', 'fuertewp_sender_email_enable', __( 'Use a different sender email.', 'fuerte-wp' ) )
					->set_default_value( 'yes' )
					->set_option_value( 'yes' )
					->set_help_text( sprintf( __( 'Use a different email (than the <a href="%s">administrator one</a>) for all emails that WordPress sends.', 'fuerte-wp' ), admin_url('options-general.php') ), ),

				Field::make( 'text', 'fuertewp_sender_email', __( 'Sender email.', 'fuerte-wp' ) )
					->set_conditional_logic(
						[
							'relation' => 'AND',
								[
								'field' => 'fuertewp_sender_email_enable',
								'value' => true,
								'compare' => '=',
							],
						]
					)
					->set_default_value( '' )
					->set_attribute( 'type', 'email' )
					/* translators: %s: site domain */
					->set_help_text( sprintf( __( 'Default site sender email. If empty, no-reply@%1$s will be used.<br/>Emails sent by WP will use this email address. Make sure to check your <a href="https://mxtoolbox.com/SPFRecordGenerator.aspx?domain=%1$s&prefill=true" target="_blank">SPF Records</a> to avoid WP emails going to spam.', 'fuerte-wp' ), $domain ) ),

				Field::make( 'separator', 'fuertewp_separator_updates', __( 'Updates', 'fuerte-wp' ) ),

				Field::make( 'checkbox', 'fuertewp_autoupdate_core', __( 'Auto-update WordPress core.', 'fuerte-wp' ) )
					->set_default_value( 'yes' )
					->set_option_value( 'yes' )
					->set_help_text( __( 'Auto-update WordPress to the latest stable version.', 'fuerte-wp' ) ),

				Field::make( 'checkbox', 'fuertewp_autoupdate_plugins', __( 'Auto-update Plugins.', 'fuerte-wp' ) )
					->set_default_value( 'yes' )
					->set_option_value( 'yes' )
					->set_help_text( __( 'Auto-update Plugins to their latest stable version.', 'fuerte-wp' ) ),

				Field::make( 'checkbox', 'fuertewp_autoupdate_themes', __( 'Auto-update Themes.', 'fuerte-wp' ) )
					->set_default_value( 'yes' )
					->set_option_value( 'yes' )
					->set_help_text( __( 'Auto-update Themes to their latest stable version.', 'fuerte-wp' ) ),

				Field::make( 'checkbox', 'fuertewp_autoupdate_translations', __( 'Auto-update Translations.', 'fuerte-wp' ) )
					->set_default_value( 'yes' )
					->set_option_value( 'yes' )
					->set_help_text( __( 'Auto-update Translations to their latest stable version.', 'fuerte-wp' ) ),

				Field::make( 'separator', 'fuertewp_separator_tweaks', __( 'Tweaks', 'fuerte-wp' ) ),

				Field::make( 'checkbox', 'fuertewp_tweaks_use_site_logo_login', __( 'Use site logo at login.', 'fuerte-wp' ) )
					->set_default_value( 'yes' )
					->set_option_value( 'yes' )
					/* translators: %s: customizer URL */
					->set_help_text( sprintf( __( 'Use your site logo, uploaded via <a href="%s" target="_blank">Customizer > Site Identity</a>, for WordPress login page.', 'fuerte-wp' ), admin_url( 'customize.php?return=%2Fwp-admin%2Foptions-general.php%3Fpage%3Dcrb_carbon_fields_container_fuerte-wp.php' ) ) ),
			) )

			->add_tab( __('E-mails', 'fuerte-wp'), array(
				Field::make( 'html', 'fuertewp_emails_header', __( 'Note:' ) )
					->set_html( __( '<p>Here you can enable or disable several WordPress built in emails. <strong>Mark</strong> the ones you want to be <strong>enabled</strong>.</p><p><a href="https://github.com/johnbillion/wp_mail" target="_blank">Check here</a> for full documentation of all automated emails WordPress sends.', 'fuerte-wp' ) ),

				Field::make( 'checkbox', 'fuertewp_emails_fatal_error', __( 'Fatal Error.', 'fuerte-wp' ) )
					->set_default_value( 'yes' )
					->set_option_value( 'yes' )
					->set_help_text( __( 'Receipt: site admin or recovery email address (main options).', 'fuerte-wp' ) ),

				Field::make( 'checkbox', 'fuertewp_emails_automatic_updates', __( 'Automatic updates.', 'fuerte-wp' ) )
					->set_default_value( '' )
					->set_option_value( 'yes' )
					->set_help_text( __( 'Receipt: site admin.', 'fuerte-wp' ) ),

				Field::make( 'checkbox', 'fuertewp_emails_comment_awaiting_moderation', __( 'Comment awaiting moderation.', 'fuerte-wp' ) )
					->set_default_value( '' )
					->set_option_value( 'yes' )
					->set_help_text( __( 'Receipt: site admin.', 'fuerte-wp' ) ),

				Field::make( 'checkbox', 'fuertewp_emails_comment_has_been_published', __( 'Comment has been published.', 'fuerte-wp' ) )
					->set_default_value( '' )
					->set_option_value( 'yes' )
					->set_help_text( __( 'Receipt: post author.', 'fuerte-wp' ) ),

				Field::make( 'checkbox', 'fuertewp_emails_user_reset_their_password', __( 'User reset their password.', 'fuerte-wp' ) )
					->set_default_value( '' )
					->set_option_value( 'yes' )
					->set_help_text( __( 'Receipt: site admin.', 'fuerte-wp' ) ),

				Field::make( 'checkbox', 'fuertewp_emails_user_confirm_personal_data_export_request', __( 'User confirm personal data export request.', 'fuerte-wp' ) )
					->set_default_value( '' )
					->set_option_value( 'yes' )
					->set_help_text( __( 'Receipt: site admin.', 'fuerte-wp' ) ),

				Field::make( 'checkbox', 'fuertewp_emails_new_user_created', __( 'New user created.', 'fuerte-wp' ) )
					->set_default_value( 'yes' )
					->set_option_value( 'yes' )
					->set_help_text( __( 'Receipt: site admin.', 'fuerte-wp' ) ),

				Field::make( 'checkbox', 'fuertewp_emails_network_new_site_created', __( 'Network: new site created.', 'fuerte-wp' ) )
					->set_default_value( '' )
					->set_option_value( 'yes' )
					->set_help_text( __( 'Receipt: network admin.', 'fuerte-wp' ) ),

				Field::make( 'checkbox', 'fuertewp_emails_network_new_user_site_registered', __( 'Network: new user site registered.', 'fuerte-wp' ) )
					->set_default_value( '' )
					->set_option_value( 'yes' )
					->set_help_text( __( 'Receipt: network admin.', 'fuerte-wp' ) ),

				Field::make( 'checkbox', 'fuertewp_emails_network_new_site_activated', __( 'Network: new site activated.', 'fuerte-wp' ) )
					->set_default_value( '' )
					->set_option_value( 'yes' )
					->set_help_text( __( 'Receipt: network admin.', 'fuerte-wp' ) ),
			) )

			->add_tab( __('REST API', 'fuerte-wp'), [
				Field::make( 'html', 'fuertewp_restapi_restrictions_header', __( 'Note:' ) )
					->set_html( __( '<p>REST API restrictions.</p>', 'fuerte-wp' ) ),

				Field::make( 'checkbox', 'fuertewp_restrictions_restapi_loggedin_only', __( 'Restrict REST API usage to logged in users only.', 'fuerte-wp' ) )
					->set_default_value( '' )
					->set_option_value( 'yes' )
					->set_help_text( __( 'Modern WordPress depends on his REST API. The entire new editor, Gutenberg, uses it. And many more usage instances are common the WP core. You should not disable the REST API entirely, or WordPress will brake. This is the second best option: limit his usage to only logged in users. <a href="https://developer.wordpress.org/rest-api/frequently-asked-questions/" target="_blank">Learn more</a>.', 'fuerte-wp' ) ),

				Field::make( 'checkbox', 'fuertewp_restrictions_restapi_disable_app_passwords', __( 'Disable app passwords.', 'fuerte-wp' ) )
					->set_default_value( 'yes' )
					->set_option_value( 'yes' )
					->set_help_text( __( 'Disable generation of App Passwords, used for the REST API. <a href="https://make.wordpress.org/core/2020/11/05/application-passwords-integration-guide/" target="_blank">Check here</a> for more info.', 'fuerte-wp' ) ),

			] )

			->add_tab( __('Restrictions', 'fuerte-wp'), array(
				Field::make( 'checkbox', 'fuertewp_restrictions_disable_xmlrpc', __( 'Disable XML-RPC API.', 'fuerte-wp' ) )
					->set_default_value( 'yes' )
					->set_option_value( 'yes' )
					->set_help_text( __( 'Disable the old and insecure XML-RPC API in WordPress. <a href="https://blog.wpscan.com/is-wordpress-xmlrpc-a-security-problem/" target="_blank">Learn more</a>.', 'fuerte-wp' ) ),

				Field::make( 'checkbox', 'fuertewp_restrictions_disable_admin_create_edit', __( 'Disable admin creation/edition.', 'fuerte-wp' ) )
					->set_default_value( 'yes' )
					->set_option_value( 'yes' )
					->set_help_text( __( 'Disable the creation of new admin accounts and the editing of existing admin accounts.', 'fuerte-wp' ) ),

				Field::make( 'checkbox', 'fuertewp_restrictions_disable_weak_passwords', __( 'Disable weak passwords.', 'fuerte-wp' ) )
					->set_default_value( 'yes' )
					->set_option_value( 'yes' )
					->set_help_text( __( 'Disable the use of weak passwords. User can\'t uncheck "Confirm use of weak password". Let users type their own password, but must be somewhat secure (following WP built in recommendation library).', 'fuerte-wp' ) ),

				Field::make( 'checkbox', 'fuertewp_restrictions_force_strong_passwords', __( 'Force strong passwords.', 'fuerte-wp' ) )
					->set_default_value( '' )
					->set_option_value( 'yes' )
					->set_help_text( __( 'Force strong passwords usage, making password field read-only. Users must use WordPress provided strong password.', 'fuerte-wp' ) ),

				Field::make( 'multiselect', 'fuertewp_restrictions_disable_admin_bar_roles', __( 'Disable admin bar for roles.', 'fuerte-wp' ) )
					->add_options( 'fuertewp_get_wp_roles' )
					->set_default_value( [ 'subscriber', 'customer' ] )
					->set_help_text( __( 'Disable WordPress admin bar for selected roles.', 'fuerte-wp' ) ),

				Field::make( 'checkbox', 'fuertewp_restrictions_restrict_permalinks', __( 'Restrict Permalinks configuration.', 'fuerte-wp' ) )
					->set_default_value( 'yes' )
					->set_option_value( 'yes' )
					->set_help_text( __( 'Restrict Permalinks configuration access.', 'fuerte-wp' ) ),

				Field::make( 'checkbox', 'fuertewp_restrictions_restrict_acf', __( 'Restrict ACF fields editing.', 'fuerte-wp' ) )
					->set_default_value( 'yes' )
					->set_option_value( 'yes' )
					->set_help_text( __( 'Restrict Advanced Custom Fields editing access in the backend (Custom Fields menu).', 'fuerte-wp' ) ),

				Field::make( 'checkbox', 'fuertewp_restrictions_disable_theme_editor', __( 'Disable Theme Editor.', 'fuerte-wp' ) )
					->set_default_value( 'yes' )
					->set_option_value( 'yes' )
					->set_help_text( __( 'Disables the built in Theme code editor.', 'fuerte-wp' ) ),

				Field::make( 'checkbox', 'fuertewp_restrictions_disable_plugin_editor', __( 'Disable Plugin Editor.', 'fuerte-wp' ) )
					->set_default_value( 'yes' )
					->set_option_value( 'yes' )
					->set_help_text( __( 'Disables the built in Plugin code editor.', 'fuerte-wp' ) ),

				Field::make( 'checkbox', 'fuertewp_restrictions_disable_theme_install', __( 'Disable Theme install.', 'fuerte-wp' ) )
					->set_default_value( 'yes' )
					->set_option_value( 'yes' )
					->set_help_text( __( 'Disables installation of new Themes.', 'fuerte-wp' ) ),

				Field::make( 'checkbox', 'fuertewp_restrictions_disable_plugin_install', __( 'Disable Plugin install.', 'fuerte-wp' ) )
					->set_default_value( 'yes' )
					->set_option_value( 'yes' )
					->set_help_text( __( 'Disables installation of new Plugins.', 'fuerte-wp' ) ),

				Field::make( 'checkbox', 'fuertewp_restrictions_disable_customizer_css', __( 'Disable Customizer CSS Editor.', 'fuerte-wp' ) )
					->set_default_value( 'yes' )
					->set_option_value( 'yes' )
					->set_help_text( __( 'Disables Customizer Additional CSS Editor.', 'fuerte-wp' ) ),
			) )

			->add_tab( __('Advanced Restrictions', 'fuerte-wp'), array(
				Field::make( 'html', 'fuertewp_advanced_restrictions_header', __( 'Note:' ) )
					->set_html( __( '<p>Only for power users. Leave a field blank to not use those restrictions.</p>', 'fuerte-wp' ) ),

				Field::make( 'textarea', 'fuertewp_restricted_scripts', __( 'Restricted Scripts.', 'fuerte-wp' ) )
					->set_rows( 4 )
					->set_default_value( 'export.php
//plugins.php
update.php
update-core.php' )
					->set_help_text( __( 'One per line. Restricted scripts by file name.<br>These file names will be checked against <a href="https://codex.wordpress.org/Global_Variables" target="_blank">$pagenow</a>, and also will be thrown into <a href="https://developer.wordpress.org/reference/functions/remove_menu_page/" target="_blank">remove_menu_page</a>.<br/>You can comment a line with // to not use it.', 'fuerte-wp' ) ),

				Field::make( 'textarea', 'fuertewp_restricted_pages', __( 'Restricted Pages.', 'fuerte-wp' ) )
					->set_rows( 4 )
					->set_default_value( 'wprocket
updraftplus
better-search-replace
backwpup
backwpupjobs
backwpupeditjob
backwpuplogs
backwpupbackups
backwpupsettings
limit-login-attempts
wp_stream_settings
transients-manager
pw-transients-manager
envato-market
elementor-license' )
					->set_help_text( __( 'One per line. Restricted pages by "page" URL variable.<br/>In wp-admin, checks for URLs like: <i>admin.php?page=</i>', 'fuerte-wp' ) ),

				Field::make( 'textarea', 'fuertewp_removed_menus', __( 'Removed Menus.', 'fuerte-wp' ) )
					->set_rows( 4 )
					->set_default_value( 'backwpup
check-email-status
limit-login-attempts
envato-market' )
					->set_help_text( __( 'One per line. Menus to be removed. Use menu <i>slug</i>.<br/>These slugs will be thrown into <a href="https://developer.wordpress.org/reference/functions/remove_menu_page/" target="_blank">remove_menu_page</a>.', 'fuerte-wp' ) ),

				Field::make( 'textarea', 'fuertewp_removed_submenus', __( 'Removed Submenus.', 'fuerte-wp' ) )
					->set_rows( 4 )
					->set_default_value( 'options-general.php|updraftplus
options-general.php|limit-login-attempts
options-general.php|mainwp_child_tab
options-general.php|wprocket
tools.php|export.php
tools.php|transients-manager
tools.php|pw-transients-manager
tools.php|better-search-replace' )
					->set_help_text( __( 'One per line. Submenus to be removed. Use: <i>parent-menu-slug<strong>|</strong>submenu-slug</i>, separared with a pipe.<br/>These will be thrown into <a href="https://developer.wordpress.org/reference/functions/remove_submenu_page/" target="_blank">remove_submenu_page</a>.', 'fuerte-wp' ) ),

				Field::make( 'textarea', 'fuertewp_removed_adminbar_menus', __( 'Removed Admin Bar menus.', 'fuerte-wp' ) )
					->set_rows( 4 )
					->set_default_value( 'wp-logo
tm-suspend
updraft_admin_node' )
					->set_help_text( __( 'One per line. Admin bar menus to be removed. Use: <i>adminbar-item-node-id</i>.<br/>These nodes will be thrown into <a href="https://developer.wordpress.org/reference/classes/wp_admin_bar/remove_node/#finding-toolbar-node-ids" target="_blank">remove_node</a>. Check the docs on how to find an admin bar node id.', 'fuerte-wp' ) ),
			) );

	}

	/**
	 * Check options & clears Fuerte-WP options cache
	 */
	public function fuertewp_theme_options_saved( $data, $options ) {
		global $current_user;

		// Check if current_user is a super user, if not, add it
		if ( ! isset( $current_user ) ) {
			$current_user = wp_get_current_user();
		}

		$super_users = carbon_get_theme_option( 'fuertewp_super_users' );

		if ( empty( $super_users ) || ! is_array( $super_users ) ) {
			// No users at all. Add current_user back as super user
			carbon_set_theme_option( 'fuertewp_super_users', $current_user->user_email );
		} else {
			if ( ! in_array( $current_user->user_email, $super_users ) ) {
				// Current_user not found in the array, add it back as super user
				//$super_users[] = $current_user->user_email;
				array_unshift( $super_users, $current_user->user_email );

				carbon_set_theme_option( 'fuertewp_super_users', $super_users );
			}
		}

		// Clears options cache
		$version_to_string = str_replace( '.', '', FUERTEWP_VERSION );
		delete_transient( 'fuertewp_cache_config_' . $version_to_string );
	}

	/**
	 * Plugins list Settings link
	 */
	function add_action_links( $links ) {
		global $fuertewp, $current_user;

		if ( ! isset( $current_user ) ) {
			$current_user = wp_get_current_user();
		}

		if ( ! in_array( strtolower( $current_user->user_email ), $fuertewp['super_users'] ) || defined( 'FUERTEWP_FORCE' ) && true === FUERTEWP_FORCE ) {
			return $links;
		}

		$fuertewp_link = [
			/* translators: %s: plugin settings URL */
			sprintf( __( '<a href="%s">Settings</a>', 'fuerte-wp'), admin_url( 'options-general.php?page=crb_carbon_fields_container_fuerte-wp.php' ) ),
		];

		return array_merge( $links, $fuertewp_link );
	}

}
