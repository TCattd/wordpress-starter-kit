=== Check & Log Email ===
Contributors: wpchill, silkalns, giucu91
Tags: check, test, email, smtp, pop, send, delivery
Requires at least: 5.0
Tested up to: 6.2
Stable tag: 1.0.7

Check & Log email allows you to test if your WordPress installation is sending emails correctly by sending a test email to an address of your choice. Allows overriding of email headers and carbon copying to another address.

== Description ==

In need of a tool that allows you to easily log and view all emails sent from WordPress? Check & Log might be just the solution for that. 
This WordPress plugin helps you store sent emails for auditing purposes, as well as debug email related problems in your WordPress site. Works best on eCommerce websites that have been created with WooCommerce or Easy Digital Downloads.

Some of its features include:

- Test email sending - if you’re not sure whether WordPress is sending emails, you can use this plugin to find out. The process is very simple: you need to choose an address that will receive a simple test email. You can even override the custom header with your own values, to avoid any other issues.
- Viewing logged emails - they can be viewed from the admin interface, as they are stored in a separate table. You get the option to filter them based on subject, email, date, etc.
- Deleting logged emails - you can delete them by going to the admin interface. You can either delete them in bulk or  selectively - by date, email, or subject. 
- Ability to change the "mail from" email address, "mail from name" and override default email WordPress addresses. 

== Installation ==

Install the plugin from the plugin repository and activate.

== Frequently Asked Questions ==

= How do I use it? =

Check the WordPress Admin Dashboard for "Check & Log Email" menu item.

= Why did you write this plugin? =

Someone using one of my other plugins had trouble with emails not being sent. I knocked this together to help him (and anyone else).

== Screenshots ==

1. Check & Log Email - Status
2. Check & Log Email - View Logs
3. Check & Log Email - Settings

== Changelog ==

= v1.0.7 - 08/03/2022 =
- Fixed: Incompatibility with Post SMTP ( [#51]( https://github.com/WPChill/check-email/issues/51) )

= v1.0.6 - 02/05/2022 =
- Fixed: Security, sanitization and escaping

= v1.0.5 - 10/03/2022 =
- Fixed: Replaced deprecated jQuery code ( https://github.com/WPChill/check-email/issues/32 )
- Fixed: HTML code was being shown in dashboard widget ( https://github.com/WPChill/check-email/issues/33 )
- Added: Tabs and grouped settings by tabs ( https://github.com/WPChill/check-email/issues/37 )
- Added: Quick install WP SMTP plugin from settings ( https://github.com/WPChill/check-email/issues/37 )

= v1.0.4 - 28/10/2021 =
- Fixed: URL got too long when bulk deleting email logs. (https://github.com/WPChill/check-email/issues/30)
- Fixed: Sanitization and Escaping

= v1.0.3 - 24/09/2021 =
- Fixed: Secutiry issue

= v1.0.2 - 16/06/2021 =
- Added: From column in Email Logs. ( https://github.com/WPChill/check-email/issues/24 )

= v1.0.2 - 16/06/2021 =
- Added: Translation for roles and notices. ( https://github.com/WPChill/check-email/issues/10 )
- Added: Headers of the emails in the view log tab. ( https://github.com/WPChill/check-email/issues/12 )
- Fixed: Admin subpages link bug. ( https://github.com/WPChill/check-email/issues/9 )
- Fixed: Incompatibility with DIVI Theme. We enqued our JS only on Logs Page. ( https://github.com/WPChill/check-email/issues/13 )
- Fixed: Incompatibility with WpLogging plugin. ( https://github.com/WPChill/check-email/issues/8 )
- Fixed: Error by adding unique prefixes for Check-Email functions. ( https://github.com/WPChill/check-email/issues/16 )
- Fixed: Redirect error when using custom folder structure for WP Core. ( https://github.com/WPChill/check-email/issues/21 )
- Fixed: Deprecated jQuery functions.

= 1.0.1 =
* Fixed admin menu capabilities.
* Rezolved incompatibility with Wp Mail Logging.

= 1.0.0 =
* Added Email Logs for all the emails sent through Wordpress.

= 0.6.1 =
Added feedback form. Improved CSS backend.

= 0.6.0 =
Fixed loopback error.

= 0.5.7 =
Added support for the wp_mail_from filter

= 0.5.6 =
Tested with WordPress 5.1.1

= 0.5.5 =
Fixed typo (sorry sorry sorry)

= 0.5.4 =
Added FAQ about the location of the tool in the WordPress admin area

= 0.5.3 =
Fixed deprecation error messages. Tested with 4.7.2.

= 0.5.2 =
Fixed un-encoded output related to XSS bug

= 0.5.1 =
Properly fixed XSS vulnerability (apologies)

= 0.5 =
Fixed XSS vulnerability found by Antonis Manaras

= 0.4 =
Added more information from php.ini, fixed incorrect textdomains

= 0.3 =
Moved the page to the Tools menu

= 0.2 =
Now displays SMTP server name

= 0.1.3 =
Fixed version number

= 0.1.2 =
Fixed bug in Plugin Register caused by latest version of WordPress

= 0.1.1 =
Fixed typo in plugin name

= 0.1 =
Initial version
