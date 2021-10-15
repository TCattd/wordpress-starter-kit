<?php
/**
 * Fuerte-WP configuration.
 * Version: 1.3.7
 *
 * Author: Esteban Cuevas
 * https://github.com/TCattd/Fuerte-WP
 */

// No access outside WP
defined( 'ABSPATH' ) || die();

/**
 * To debug or test Fuerte-WP
 */
define( 'FUERTEWP_DISABLE', false );
define( 'FUERTEWP_FORCE', false );

/**
 * Edit this configuration array and set up as you like.
 */
$fuertewp = [
	/*
	 Control Fuerte-WP status: enabled / disabled
	*/
	'status' => 'enabled',
	/*
	Super Users accounts, by email address.
	This users will not be affected by Fuerte-WP's restrictions.
	Add one email per item inside the array.
	*/
	'super_users' => [
		'esteban@attitude.cl',
		'esteban@actitud.xyz',
	],
	/*
	General configuration.
	*/
	'general' => [
		'access_denied_message'         => 'Access denied.', // Default access denied message.
		'recovery_email'                => '', // Admin recovery email. If empty, dev@wpdomain.tld will be used. See https://make.wordpress.org/core/2019/04/16/fatal-error-recovery-mode-in-5-2/
		'sender_email'                  => '', // Default site sender email. If empty, no-reply@wpdomain.tld will be used.
		'autoupdate_core'               => true, // Auto update WP core.
		'autoupdate_plugins'            => true, // Auto update plugins.
		'autoupdate_themes'             => true, // Auto update themes.
		'autoupdate_translations'       => true, // Auto update translations.
	],
	/*
	Tweaks
	*/
	'tweaks' => [
		'use_site_logo_login'           => true, // Use customizer logo as WP login logo.
	],
	/*
	Restrictions
	*/
	'restrictions' => [
		'disable_xmlrpc'                => true, // Disable the old XML-RPC API
		'disable_admin_create_edit'     => true, // Disable creation of new admin accounts by non super admins.
		'disable_app_passwords'         => true, // Disable WP application passwords.
		'disable_weak_passwords'        => true, // Disable ability to use a weak passwords. User can't uncheck "Confirm use of weak password". Let users type their own password, but must be somewhat secure (following WP built in recommendation library).
		'force_strong_passwords'        => false, // Force strong passwords usage, make password field read-only. Users must use WP provided strong password.
		'disable_admin_bar_roles'       => [ 'subscriber', 'customer' ], // Disable admin bar for some user roles. Array of WP/WC roles. Empty array to not use this feature.
		'restrict_permalinks'           => true, // Restrict Permalinks config access.
		'restrict_acf'                  => true, // Restrict ACF editing access (Custom Fields menu).
		'disable_theme_editor'          => true, // Disable WP Theme code editor.
		'disable_plugin_editor'         => true, // Disable WP Plugin code editor.
		'disable_theme_install'         => true, // Disable Themes installation.
		'disable_plugin_install'        => true, // Disable Plugins installation.
	],
	/*
	Controls several WordPress notification emails, mainly targeted to site/network admin email address.
	True to keep an email enabled. False to disable an email.
	*/
	'emails' => [
		'fatal_error'                               => true,  // Site admin OR recovery_email address
		'automatic_updates'                         => false, // Site admin
		'comment_awaiting_moderation'               => false, // Site admin
		'comment_has_been_published'                => false, // Post author
		'user_reset_their_password'                 => false, // Site admin
		'user_confirm_personal_data_export_request' => false, // Site admin
		'new_user_created'                          => true,  // Site admin
		'network_new_site_created'                  => false, // Network admin
		'network_new_user_site_registered'          => false, // Network admin
		'network_new_site_activated'                => false, // Network admin
	],
	/*
	Restricted scripts by file name.
	These file names will be checked against $pagenow.
	These file names will be thrown into remove_menu_page.
	*/
	'restricted_scripts' => [
		'export.php',
		//'plugins.php',
		'update.php',
		'update-core.php',
	],
	/*
	Restricted pages by page URL variable.
	In wp-admin, check for admin.php?page=
	*/
	'restricted_pages' => [
		'wprocket', // WP-Rocket
		'updraftplus', // UpdraftPlus
		'backwpup', // BackWPup
		'backwpupjobs', // BackWPup
		'backwpupeditjob', // BackWPup
		'backwpuplogs', // BackWPup
		'backwpupbackups', // BackWPup
		'backwpupsettings', // BackWPup
		'limit-login-attempts', // Limit Login Attempts Reloaded
		'wp_stream_settings', // Stream
		'pw-transients-manager', // Transients Manager
		'envato-market', // Envato Market
		'elementor-license', //  Elementor Pro
		'elementor-connect', // Elementor Pro
	],
	/*
	Menus to be removed. Use menu's slug.
	These slugs will be thrown into remove_menu_page.
	*/
	'removed_menus' => [
		'backwpup', // BackWPup
		'check-email-status', // Check Email
		'limit-login-attempts', // Limit Logins Attempts Reloaded
		'envato-market', // Envato Market
	],
	/*
	Submenus to be removed.
	Use: parent-menu-slug|submenu-slug, separed with a pipe.
	These will be thrown into remove_submenu_page.
	*/
	'removed_submenus' => [
		'options-general.php|updraftplus', // UpdraftPlus
		'options-general.php|limit-login-attempts', // Limit Logins Attempts Reloaded
		'options-general.php|mainwp_child_tab', // MainWP Child
		'options-general.php|wprocket', // WP-Rocket
		'tools.php|export.php', // WP Export
		'tools.php|pw-transients-manager', // Transients Manager
	],
	/*
	Admin bar menus to be removed.
	Use: adminbar-item-node-id
	These will be thrown into $wp_admin_bar->remove_node.
	*/
	'removed_adminbar_menus' => [
		'wp-logo', // WP Logo
		'tm-suspend', // Transients Manager
		'updraft_admin_node', // UpdraftPlus
	],
	/*
	NOT WORKING. WORK IN PROGRESS.

	Recommeded plugins.
	Format: plugin-slug-name/plugin-main-file.php
	*/
	'recommended_plugins' => [
		'imsanity/imsanity.php', // Imsanity
		'safe-svg/safe-svg.php', // Save SVG
		'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php', // Limit Login Attempts Reloaded
	],
	/*
	NOT WORKING. WORK IN PROGRESS.

	Discouraged plugins.
	Format: check the included examples
	*/
	'discouraged_plugins' => [
		[
			// SEO Framework instead of Yoast SEO
			'discouraged_plugin' => 'wordpress-seo/wp-seo-main.php',
			'discouraged_name'   => 'Yoast SEO',
			'alternative_plugin' => 'autodescription/autodescription.php',
			'alternative_name'   => 'SEO Framework',
			'reason'             => 'SEO Framework is lightweight, have less bloat, same features and no promotionals nags like Yoast SEO.',
		],
		[
			// WP Core instead of Clean Filenames
			'discouraged_plugin' => 'sanitize-spanish-filenames/sanitize-spanish-filenames.php',
			'discouraged_name'   => 'Clean Filenames',
			'alternative_plugin' => '',
			'alternative_name'   => '',
			'reason'             => 'Feature included in WP core since version 5.6',
		],
	],
];
