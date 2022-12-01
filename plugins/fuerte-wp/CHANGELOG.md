# Changelog

# 1.4.3 / 2022-11-28
- REST API enabled by default for all users. Needed for the modern WordPress themes and other plugins.
- Resolved an issue with Elementor Template Kits import.

# 1.4.2 / 2022-07-22
- PHP 8.x compatibility check.
- Tested up to WordPress 6.0.
- Added an option to enable/disble the sender email address.
- Added an option to disable Customizer additional CSS editor.
- Added an option to force REST API usage to logged in users only.
- Fixed a compatibility bug with Elementor's editor.

# 1.3.11 / 2022-01-11
- Fixed bug with un-trimmed advanced restrictions.
- Fixed a bug with cached options values (transient).

# 1.3.8 / 2021-10-08
- Option to configure Theme Editor restriction.
- Option to configure Plugin Editor restriction.
- Option to configure new plugins installation restriction.
- Option to configure new themes installation restriction.
- Fixed some translation errors.

# 1.3.5 / 2021-09-13
- PHP 7.3 compatibility.

# 1.3.1 / 2021-08-27
- Reworked as full featured plugin.
- Added an options page for easy configuration.
- New logo, courtesy of [Nicolás Franz](https://nicolasfranz.com). Many thanks, pal!

# 1.2.0 / 2021-08-13
- Converted to a plugin.
- Added ability to access plugins management, but don't allow install or upload new plugins. Also Fuerte-WP will auto-protect itself from deactivation.
- Added ability to restrict access to Permalinks configuration.
- Added ability to use custom site logo (from Customizer) as WP login logo.

# 1.1.3 / 2021-04-22
- Added ability to disable the old XML-RPC API.
- Now it hides ACF cog inside ACF meta fields UI, and prevent opening ACF custom post type (acf-field-group), to avoid non super admins to access ACF editing UI. So, non super-admin users can't access ACF Custom Fields UI, even if they put the URL directly into the address bar.

# 1.1.2 / 2021-04-16
- Added missing support to disable update emails for plugins and themes.

## 1.1.1 / 2021-04-09
- Added support to control several WP's automatic emails.
- Added support to disable WP admin bar for specific roles.

## 1.1.0 / 2021-04-07
- Fuerte-WP configuration file now lives outside wp-config.php file, into his own wp-config-fuerte.php file. This to make it easier to deploy it to several WP installations, without the need to edit the wp-config.php file in all of them. Check the readme on how to install it.
- Added option to enable or disable strong passwords enforcing.
- Added support to prevent use of weak passwords.
- Added support for remove_menu_page.
- Added ability to disable WordPress's new Application Passwords feature.

## 1.0.1 / 2020-10-29
- Now using a proper Class.
- Added option to change WP sender email address.
- Added configuration to remove custom submenu items (remove_submenu_page).
- Force user creation and editing to use WP default strong password suggestion, for non super users.
- Prevent admin accounts creation or edition, for non super users.
- Customizable not allowed error message.

## 1.0.0 / 2020-10-27
- Initial release.
- Enable and force auto updates for WP core.
- Enable and force auto updates for plugins.
- Enable and force auto updates for themes.
- Enable and force auto updates for translations.
- Disables email triggered when WP auto updates.
- Change [WP recovery email](https://make.wordpress.org/core/2019/04/16/fatal-error-recovery-mode-in-5-2/) so WP crashes will go to a different email than the Administration Email Address in WP General Settings.
- Disables WP theme and plugin editor for non super users.
- Remove items from WP menu for non super users.
- Restrict editing or deleting super users.
- Disable ACF Custom Fields editor access for non super users.
- Restrict access to some pages inside wp-admin, like plugins or theme uploads, for non super users. Restricted pages can be extended vía configuration.
