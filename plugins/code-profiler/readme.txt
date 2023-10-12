=== Code Profiler - WordPress Performance Profiling and Debugging Made Easy ===
Contributors: bruandet, nintechnet
Tags: profiler, debug, optimize, performance, seo, benchmark, statistics, debugging, speed, profiling
Requires at least: 5.0
Tested up to: 6.3
Stable tag: 1.6.4
License: GPLv3 or later
Requires PHP: 7.1
License URI: https://www.gnu.org/licenses/gpl-3.0.html

A profiler to measure the performance of your WordPress plugins and themes.

== Description ==

= A profiler to measure the performance of your WordPress plugins and themes. =

Code Profiler helps you to measure the performance of your plugins and themes at the PHP level and to quickly find any potential problem in your WordPress installation.

You can profile the frontend and backend of WordPress, a custom URL, or even send a POST payload, custom cookies and HTTP headers to profile a contact form, a checkout process or an AJAX action among many other possibilities.

It generates an extremely detailed and easy to read analysis in the form of charts and tables that shows not only which plugin or theme, but also which PHP script, class, method and function is slowing down your website. It displays many useful additional information such as database queries, file I/O operations and disk I/O usage as well.

It makes it very simple to locate any bottleneck problem in your themes or plugins in order to solve it and speed up your website.
Install, activate it and you can start profiling your site right away.


[youtube https://www.youtube.com/watch?v=3PLDk-ZvQtU]


= Features =

* Plugins & themes performance profiling.
* [Pro] Scripts performance profiling.
* [Pro] Methods & functions performance profiling.
* [Pro] Database queries performance.
* [Pro] Remote connections monitoring.
* [Pro] File I/O operations monitoring.
* File I/O statistics.
* Disk I/O monitoring.
* WP-CLI integration.
* Export charts in PNG image format.
* GET/POST methods, custom cookies and HTTP headers.
* [Pro] Export all tables and charts in CSV file format.
* [Pro] Filtering options

Learn more about [Code Profiler Pro](https://code-profiler.com/).

== Frequently Asked Questions ==

= What are the differences between Code Profiler and services such as Google PageSpeed or GTmetrix? =

They are completely different: Code Profiler analyzes the code performance of your plugins and themes on your server, at the PHP level. Google PageSpeed and GTmetrix, instead, analyze the content of a web page from a browser's perspective.

= Will Code Profiler work if I have a caching plugin or a PHP opcode cache, or if my website is using a CDN service? =

In most cases, Code Profiler will be able to bypass caching from plugins and CDN services, as well as your PHP opcode cache. If there were a problem, it would warn you about it.

= Why do I see numbers such as "3E-06" or "1.2E-05" after opening the CSV file with my spreadsheet editor? =

Spreadsheet editors such as LibreOffice Calc and Microsoft Excel can use scientific exponential notation to display very small numbers, for instance, 0.000025 will become 2.5e-5. Select the whole column of numbers and, in the toolbar of your spreadsheet editor, click the button to increase the number of decimals to 6.

= Why do some scripts show an execution time of 0 second? =

That can happen if your PHP version is 7.1 or 7.2. With those versions, Code Profiler can only use microseconds for its metrics, while with versions 7.3 and above it can use the system's high resolution time in nanoseconds. It can also happen if a PHP script has only a couple of lines of code, its execution time is too quick to be measured, hence it will show 0.

= Do I need to deactivate Code Profiler when I'm not using it ? =

There's no need to deactivate Code Profiler when you don't use it, it has no performance impact on your site.

= Why does Code Profiler warn me that I have multiple plugins using Composer? =

Composer, a tool for dependency management in PHP, is included in many popular plugins and themes. It is used to autoload PHP classes.
Code Profiler will inform you if two or more activated plugins use it because you will need to take it into consideration when reading and interpreting the results. Let's take an example:
Assuming you have four plugins, #1, #2, #3 and #4. Both plugins #1 and #4 include and require Composer. WordPress will start and load plugin #1, which will run an instance of Composer to load its classes. Immediately after, WordPress will load plugins #2 and #3. Then, it will load plugin #4, which too will need to load its classes. However, plugin #4 will not start a new instance of Composer but, instead, will rely on the one from plugin #1 to load its own classes.
As a result, the execution time of plugin #1 will increase (its instance of Composer is used to load classes for plugin #4 too), while the execution time of plugin #4 will decrease (it doesn't need to start a new instance of Composer). Therefore, if you have a dozen or more plugins using Composer, it is important to take into consideration that the execution time of plugin #1 may be much higher than other plugins.
Also, assuming you are a developer and just want to profile a plugin that you wrote and that includes Composer, you will need to disable any other plugin using Composer in order to get the most accurate results for your plugin only.

= Is Code Profiler multisite compatible? =
Code Profiler is multisite compatible. Note however that for security reasons, only the superadmin can run it.

= What are the requirements for running Code Profiler? =

* WordPress >=5.0.
* PHP >=7.1 or 8.x.

= What is your Privacy Policy? =

Code Profiler does not collect any private data from you or your visitors. It does not use cookies either. Your website can run Code Profiler and be compliant with the General Data Protection Regulation (GDPR) or other similar privacy laws.


== Installation ==

1. Upload the ZIP file from the WordPress "Plugins > Add New" menu.
2. Activate the plugin.
3. Plugin settings are located in the 'Code Profiler' menu.

== Screenshots ==

1. Running the profiler.
2. Plugins and Themes performance section.
3. WP-CLI integration.
4. File I/O statistics.
5. Disk I/O statistics.
6. [Pro version]: Scripts performance.
7. [Pro version]: Methods and Functions performance.
8. [Pro version]: CSV Export.
9. [Pro version]: CSV Export.
10. [Pro version]: File I/O List
11. [Pro version]: Database queries performance.
12. [Pro version]: Remote connections monitoring.

== Changelog ==

= 1.6.4 (August 3, 2023) =

* Added the possibility to send custom HTTP headers when profiling a site. See "Advanced Options > HTTP headers" in the profiler's main page.
* Several small fixes and adjustments.
* Updated Charts.js libraries.

= 1.6.3 (June 18, 2023) =

* A new option was added to tweak the size of the memory buffer used by the profiler. It can be found in "Settings > Buffer size".
* The profiler will disable the PHP display_errors directive so that potential notice, warning and error messages won't mess up with the AJAX response.
* Small fixes and adjustments.

= 1.6.2 (May 20, 2023) =

* Fixed an issue where the execution time in the "Plugins & Theme Performance" section did not include any occurred remote connection.
* Updated Charts.js libraries.
* Updated FAQ and Help sections.
* Small fixes and adjustments.

= 1.6.1 (April 22, 2023) =

* Added a hook to prevent a timeout if a plugin changed cURL timeout options (props @davidbawiec).
* Several small fixes and adjustments.

= 1.6 (March 12, 2023) =

* [Pro version]: We added a new section: Remote Connections. It shows all HTTP connections originating from your WordPress website and includes the URL, the HTTP status code, the duration and, as usual, a full backtrace.
* Updated browser's signatures.
* Updated Charts.js libraries.
* Small fixes and adjustments.

= 1.5.5 (February 24, 2023) =

* Improved the system information report.
* [Pro version] : When viewing a file, its name will be displayed in the title bar of the browser.
* The log will be automatically deleted when it reaches 100KB.
* Added a "Debug" checkbox below the log that can be used to increase its verbosity.
* Updated Charts.js libraries.
* Added a warning if the WordPress MU folder is not writable.
* Several small fixes and adjustments.

= 1.5.4 (January 18, 2023) =

* Fixed a potential "404 Not Found" HTTP error when the site home page is different from the WordPress installation directory.

= 1.5.3 (January 10, 2023) =

* It is possible to edit the name of a profile by clicking the "Quick Edit" row action link below its name in the profiles list.
* Updated Charts.js libraries.
* Small fixes and adjustments.

= 1.5.2 (December 07, 2022) =

* Added screen reader accessibility to all 3 graphs (Plugins and Theme, File I/O, Disk I/O).
* The timeout of the profiler's process was increased from 180 to 300 seconds.
* [Pro version] : Fixed a bug where the file viewer couldn't locate a function in a script if there was a namespace declared, because it was looking for a method instead of a function.
* Updated Charts.js libraries.
* Small fixes and adjustments.

= 1.5.1 (October 29, 2022) =

* [Pro version] : The profiler can display a full backtrace for each caller function. That option can be enabled in "Settings > Methods & Functions > Generate a PHP backtrace for each caller function".
* Compatibility with PHP 8.2.
* Improved anonymous functions detection.
* Updated Charts.js libraries.
* [Pro version] : Updated Code Mirror libraries.
* Small fixes and adjustments.

= 1.5 (September 12, 2022) =

* You can select the accuracy and precision level of the profiler. If you have a slow WordPress site with a lot of plugins installed and your server or reverse proxy is timing out when Code Profiler is running, you can lower the accuracy level in order to speed up the profiling process and avoid the server timeout. This option can be found in the "Settings" tab.
* [Free version] : Similarly to the Pro version, the profiler will display a warning below the "Plugins and Themes Performance" chart if it detects that multiple plugins are using Composer dependency manager.
* Added reverse proxy/CDN detection to the system information report.
* Updated Charts.js libraries.
* [Pro version] : Updated Code Mirror libraries.
* Updated the user-agent signatures.
* Small fixes and adjustments.

= 1.4.4 (July 28, 2022) =

* Mu-plugins are now processed by the profiler. They will have the "MU" abbreviation displayed beside their name on all graphs and tables.
* We added a new "Support" tab in the profiler's page that displays a system information report. When contacting the support for help, please copy and paste it in your ticket.
* [Pro version] : Code Profiler will temporarily disable any "wp-content/db.php" script found on your site to prevent it from interfering with the profiler and database queries. That option can be changed in the "Settings" page.
* Fixed a potential "Serialization of Closure is not allowed" PHP error.
* Small fixes and adjustments.

= 1.4.3 (July 02, 2022) =

* When running the profiler as an authenticated user, you can now enter the name of that user.
* [Pro version] : Fixed a bug in the "File I/O List" section where files located outside the ABSPATH had their name truncated.
* Updated Charts.js libraries.
* [Pro version] : Updated Code Mirror libraries.
* Small fixes and adjustments.

= 1.4.2 (June 02, 2022) =

* The profiler will memorize the settings used for the last profile.
* In the "Profiles List" section, the "Filter" input field will also apply to the profiles name.
* Added Edge browser to the user-agent signatures list box.
* [Pro version] : You can export the list of profiles. Click on the "Download as a CSV file" button below the table in the "Profiles List" section.
* Updated Charts.js libraries.
* [Pro version] : Updated Code Mirror libraries.
* Small fixes and adjustments.

= 1.4.1 (April 26, 2022) =

* Added an "Advanced Options" button in the profiler's main page that allows you to select the HTTP method (GET or POST), send a POST payload as well as custom cookies. Those features are helpful if, for instance, you want to profile a contact form, a checkout process or an AJAX action etc.
* Small fixes and adjustments.
* [Pro version]: Updated Code Mirror libraries.

= 1.4 (March 30, 2022) =

* [Pro version]: We added a new section: "Database queries performances". It shows all database queries for the plugins and the theme, their processing time and a backtrace that lists the scripts and function calls that lead to the query.
* [Pro version]: All "Name" columns were renamed to "Component".
* Fixed an issue in the "Plugins and Theme" graph where special characters were wrongly replaced with their HTML entities.
* WP-CLI: Fixed an issue where all error and warning messages were json-encoded.
* The cURL timeout was increased from 90 to 180 seconds.
* Updated Charts.js libraries.
* [Pro version]: Updated Code Mirror libraries.
* Small fixes and adjustments.

= 1.3.1 (February 26, 2022) =

* Fixed some bugs with right-to-left (RTL) WordPress sites: the tooltip in the profiler main page and the checkboxes below the log were all messed up.
* By default, the profiler will always abort and throw an error if the server didn't return a "200 OK" HTTP status code. You can change that behaviour in the "Settings" section if the page you are profiling needs to return a different code (3xx, 4xx or 5xx).
* WP-CLI: Added support for HTTP basic authentication with the `--u` and `--p` parameters. Run `wp code-profiler help` for more details.
* WP-CLI: Added support for WordPress user authentication with the `--user` parameter. Run `wp code-profiler help` for more details.
* [Pro version]: WP-CLI: You can now profile any page/post with the `--dest` parameter. Run `wp code-profiler-pro help` for more details.
* When disabling the plugin, it will delete its MU plugin.
* The list of posts has been removed from the dropdown menu. To profile a post, please use the new "Custom post" input field that was introduced in v1.2.
* [Pro version]: Fixed a bug in the "Method & Function" table where the action links were not working on smartphones because of a wrong value used for the colspan attribute.
* [Pro version] : Replaced strcmp() with strnatcmp() to sort some table columns with a "natural order" algorithm.
* You can exclude a plugin during the profiling by using the "Freesoul Deactivate Plugins" plugin available in the WordPress.org repo. See "Is it possible to exclude a plugin during the profiling?" in the "FAQ" section.
* Small fixes and adjustments.

= 1.3 (February 21, 2022) =

* The "Profiles List" table has now 5 new sortable columns that will display several important metrics: "Items" (number of plugins + the current theme), "Time" (execution time in seconds), "Memory" (peak memory in megabytes), "File I/O" (sum of all file I/O operations) and "SQL" (number of database queries). Note that those metrics will be displayed on profiles created with this new version of Code Profiler, not on older profiles. The same metrics can be seen on each profile's page (click on the tooltip beside the profile's name) and also when running the profiler from the WP-CLI command line tool.
* [Pro version] : The "Methods & Functions Performance" section now shows all callers of a function. Clicking on the "View Call" action link opens a new row with the list of callers and, for each one, the full path and line number.
* [Pro version] : Fixed a bug in the "File I/O List" section where it was not possible to open a renamed file in the file viewer.
* [Pro version] : It's possible to enter your license from WP-CLI command line (`wp code-profiler-pro license`).
* Fixed a bug where temporary files were not deleted after an HTTP error.
* Fixed a bug where the columns sorting order was not remembered after deleting a profile in the "Profiles List" table.
* Small fixes and adjustments.

= 1.2 (January 30, 2022) =

* Added the possibility to profile custom post types and custom URI.
* Added support for HTTP Basic authentication. If you're running Code Profiler on a staging site that is password protected, the profiler should automatically detect it.
* Added an option to disable WordPress WP-Cron when running the profiler. This option will prevent WP-Cron to run scheduled tasks in the background that could affect the results of the profiler. It is enabled by default.
* Small fixes and adjustments.

= 1.1.1 (January 12, 2022) =

* Fixed a potential error when running the profiler.

= 1.1 (January 11, 2022) =

* Added support for WP-CLI: you can run the profiler from a terminal. Enter `wp code-profiler help` to display the available command line options. WP-CLI integration can be turned on/off in the plugin "Settings" page.
* Updated Chart.js library.
* [Pro version]: Updated Codemirror libraries.
* Small fixes and adjustments.

= 1.0.1 (December 26, 2021) =

* Added compatibility with blogs that have permalinks disabled (e.g., `?page_id=1` or `?p=1`).
* Added compatibility with child pages (e.g., `/foo/bar/`).
* Added some extra HTTP headers to prevent aggressive caching.

= 1.0 (December 12, 2021) =

* Initial release.
