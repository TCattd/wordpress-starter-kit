=== Error Log Viewer by BestWebSoft ===
Contributors: bestwebsoft
Donate link: https://bestwebsoft.com/donate/
Tags: add debug tool, error log, error log viewer, php error log, debug tool, clear log, display errors, error, eror, error reporting, save log, find log
Requires at least: 5.6
Tested up to: 6.2
Stable tag: 1.1.2
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Get latest error log messages to diagnose website problems. Define and fix issues faster.

== Description ==

Simple utility plugin that helps to find and view log files with errors right from your WordPress admin dashboard. Get access to all your log files from one place. View the latest activity, select logs by date, or view a full log file. Get email notifications about log changes.

Get access to your website logs and troubleshoot issues faster!

[View Demo](https://bestwebsoft.com/demo-error-log-viewer/?ref=readme)

https://www.youtube.com/watch?v=8LR0F4GgXhM

= Features =

* Enable WordPress error logging with:
	* .htaccess
	* wp-config.php using `inl_set`
	* wp-config.php using `WP_DEBUG`
* Search and view error logs:
	* PHP
	* WordPress
* Configure email notifications about log changes:
	* Set the email
	* Change frequency
* Configure log monitor settings:
	* Choose the log to be displayed
	* Choose what to show in the log:
		* Certain last lines in the file
		* Log for a certain period of time
		* Full file
* View or save the part of PHP error logs as TXT file
* Compatible with latest WordPress version
* Incredibly simple settings for fast setup without modifying code
* Detailed step-by-step documentation and videos

If you have a feature suggestion or idea you'd like to see in the plugin, we'd love to hear about it! [Suggest a Feature](https://support.bestwebsoft.com/hc/en-us/requests/new)

= Documentation & Videos =

* [[Doc] User Guide](https://bestwebsoft.com/documentation/error-log-viewer/error-log-viewer-user-guide/)
* [[Doc] Installation](https://bestwebsoft.com/documentation/how-to-install-a-wordpress-product/how-to-install-a-wordpress-plugin/)

= Help & Support =

Visit our Help Center if you have any questions, our friendly Support Team is happy to help - <https://support.bestwebsoft.com/>

= Affiliate Program =

Earn 20% commission by selling the premium WordPress plugins and themes by BestWebSoft — https://bestwebsoft.com/affiliate/

= Translation =

* Russian (ru_RU)
* Ukrainian (uk)

Some of these translations are not complete. We are constantly adding new features which should be translated. If you would like to create your own language pack or update the existing one, you can send [the text of PO and MO files](https://codex.wordpress.org/Translating_WordPress) to [BestWebSoft](https://support.bestwebsoft.com/hc/en-us/requests/new) and we'll add it to the plugin. You can download the latest version of the program for work with PO and MO [files Poedit](https://www.poedit.net/download.php).

= Recommended Plugins =

* [Updater](https://bestwebsoft.com/products/wordpress/plugins/updater/?k=e2d89a7eca0a903ab58d99e7ffa3b510) - Automatically check and update WordPress website core with all installed plugins and themes to the latest versions.

== Installation ==

1. Upload the `error-log-viewer` folder to `/wp-content/plugins/` directory.
2. Activate the plugin via the 'Plugins' menu in WordPress.
3. Plugin settings are located in "Error Log Viewer".

[View a Step-by-step Instruction on Error Log Viewer Installation](https://bestwebsoft.com/documentation/how-to-install-a-wordpress-product/how-to-install-a-wordpress-plugin/).

== Frequently Asked Questions ==

= I can’t view, download or clear the log file. =

Probably there is a problem with access to files and folders. For more information, please go to [Changing File Permissions](https://codex.wordpress.org/Changing_File_Permissions).

= What is the difference between three methods of a log file creating, which are offered by the plugin? =

All methods are equivalent, so when you turn on them all only one of them will work.
There may be unwanted conflicts.

1) Error logging via '.htaccess' using 'ini_set'

This method is suitable if you have an access to the file ".htaccess" to edit it.
Also, this method allows you to create a log file, its name, change the absolute path to it.
'php_flag' and 'php_value' change the value of Apache directives by changing the server configuration. The plugin uses this method only to enable PHP errors logging and specifying the path to the log files. Other configuration settings you can change by yourself.
For more information, please visit [How to change configuration settings](http://php.net/manual/en/configuration.changes.php) and [Directives list php.ini](http://php.net/manual/en/ini.list.php).

2) Error logging via 'wp-config.php' using 'ini_set'

If you don't have an access to ".htaccess", you can use file "wp-config.php" to change server configuration settings using the 'ini_set' option and specifying a variety of error logging settings and other options. The plugin uses this method only to activate the PHP error logging and specifying the path to the log files.
For more information, please visit [Runtime Configuration](http://php.net/manual/en/errorfunc.configuration.php#ini.error-log) and [ini_set](http://php.net/manual/en/function.ini-set.php).

3) Error logging via 'wp-config.php' using 'WP_DEBUG'

This method is used for debugging errors using the WordPress PHP constants and declaring them in the "wp-config.php" file. This is a standard WordPress debugging method. This is a very good method which is recommended for using on WordPress sites, but errors are recorded in the file "debug.log" to the 'wp-content' directory. You can’t change the absolute path to file logs. This method is considered to be a priority on the WordPress sites. After declaring of these constants other methods won’t work.
For more information, please visit [Errors Debugging on the WordPress](https://codex.wordpress.org/Debugging_in_WordPress).

= Why I can’t select all three methods to enable debug? =

Because all methods are equivalent, so when you turn on them all only one of them will work.
There may be unwanted conflicts.

= I clicked on the checkbox to receive notification about the logs to my mailbox, however, the letters come less than it exposed in the settings. Why? =

The function of notification sending implemented using WordPress hook 'wp_shedule_event'. If during the chosen period of time the site has been inactive (no sign on it), this hook won’t work.

= After creating a log file there are identical files appear in tabs PHP Error Log Viewer and WP Error Log Viewer. Why? =

It depends on the configuration of your server. In the tab of the log viewing the file will be only one.

= I have some problems with the plugin's work. What Information should I provide to receive proper support? =

Please make sure that the problem hasn't been discussed yet on our forum (<https://support.bestwebsoft.com>). If no, please provide the following data along with your problem's description:

- The link to the page where the problem occurs
- The name of the plugin and its version. If you are using a pro version - your order number.
- The version of your WordPress installation
- Copy and paste into the message your system status report. Please read more here: [Instruction on System Status](https://bestwebsoft.com/documentation/admin-panel-issues/system-status/)

== Screenshots ==

1. Settings page for create log file.
2. Settings page for selecting viewed file.
3. Settings page for sending e-mail.
4. PHP error log monitor.
5. WP error log monitor.

== Changelog ==

= V1.1.2 - 11.01.2022 =
* Update : BWS panel section is updated.
* Update : All functionality has been updated for WordPress 5.8.1.
* Bugfix : Bugs with adding data to wp-config were fixed.

= V1.1.1 - 02.04.2020 =
* Update : The plugin settings page was changed.
* Update : All functionality was updated for PHP 7.4.
* Update : BWS menu has been updated.
* Update : All functionality has been updated for WordPress 5.4.

= V1.1.0 - 14.11.2019 =
* NEW : Ability to send an email notification when a fatal error occurs.

= V1.0.9 - 04.09.2019 =
* Update: The deactivation feedback has been changed. Misleading buttons have been removed.

= V1.0.8 - 25.12.2018 =
* Update : All functionality has been updated for WordPress 5.0.2.

= V1.0.7 - 19.07.2018 =
* NEW : Ability to clear log file has been added.
* Bugfix : Error log display area was fixed.

= V1.0.6 - 17.04.2017 =
* Bugfix : Multiple Cross-Site Scripting (XSS) vulnerability was fixed.

= V1.0.5 - 12.10.2016 =
* Update : BWS plugins section is updated

= V1.0.4 - 11.07.2016 =
* Update : We updated all functionality for wordpress 4.5.3.
* Update : BWS panel section is updated.

= V1.0.3 - 25.04.2016 =
* Update : We updated all functionality for wordpress 4.5.

= V1.0.2 - 09.12.2015 =
* Bugfix : The bug with plugin menu duplicating was fixed.

= V1.0.1 - 20.10.2015 =
* NEW : We added ability to restore settings to defaults.

= V1.0.0 - 08.09.2015 =
* Release date of Error Log Viewer

== Upgrade Notice ==

= V1.1.2 =
* The compatibility with new WordPress version updated.
* Plugin optimization completed.
* Bugs fixed

= V1.1.1 =
* Usability improved.

= V1.1.0 =
* New features added.

= V1.0.9 =
* Usability improved.

= V1.0.8 =
* The compatibility with new WordPress version updated.

= V1.0.7 =
* New features added
* Bugs fixed

= V1.0.6 =
* Bugs fixed.

= V1.0.5 =
* Plugin optimization completed.

= V1.0.4 =
* We updated all functionality for wordpress 4.5.3. BWS panel section is updated.

= V1.0.3 =
* We updated all functionality for wordpress 4.5.

= V1.0.2 =
* The bug with plugin menu duplicating was fixed.

= V1.0.1 =
* We added ability to restore settings to defaults.

= V1.0.0 =
* Release date of Error Log Viewer
