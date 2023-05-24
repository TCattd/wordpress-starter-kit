=== Header Footer Code Manager ===
Contributors: DraftPress, 99robots, charliepatel
Tags: header, footer, code manager, snippet, functions.php, tracking, google analytics, adsense, verification, pixel
Requires at least: 4.9
Requires PHP: 5.6.20
Tested up to: 6.1.1
Stable tag: 1.1.32
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://draftpress.com

Easily add tracking code snippets, conversion pixels, or other scripts required by third party services for analytics, marketing, or chat features.

== Description ==
Header Footer Code Manager by 99 Robots is a easy interface to add snippets to the header or footer or above or below the content of your page.

= BENEFITS =
* Never have to worry about inadvertently breaking your site by adding code
* Avoid inadvertently placing snippets in the wrong place
* Eliminate the need for a dozen or more silly plugins just to add a small code snippet - Less plugins is always better!
* Never lose your code snippets when switching or changing themes
* Know exactly which snippets are loading on your site, where they display, and who added them

= FEATURES =
* Add an unlimited number of scripts and styles anywhere and on any post / page
* Manage which posts or pages the script loads
* Supports custom post types
* Supports ability to load only on a specific post or page, or latest posts
* Control where exactly on the page the script is loaded - head, footer, before content, or after content
* Script can load only on desktops or mobile. Enable or disable one or the other.
* Use shortcodes to manually place the code anywhere
* Label every snippet for easy reference
* Plugin logs which user added and last edited the snippet, and when

= PAGE DISPLAY OPTIONS =
1. Site wide on every post / page
2. Specific post
3. Specific page
4. Specific category
5. Specific tag
6. Specific custom post type
7. Latest posts only (you choose how many)
8. Manually place using shortcodes

= INJECTION LOCATIONS =
1. Head section
2. Footer
3. Top of content
4. Bottom of content

= DEVICE OPTIONS =
* Show on All Devices
* Only Desktop
* Only Mobile Devices

= SUPPORTED SERVICES =
* Google Analytics
* Google Adsense
* Google Tag Manager
* Clicky Web Analytics or other analytics tracking scripts
* Chat modules such as Olark, Drip, or
* Pinterest site verification
* Facebook Pixels, Facebook Scripts, Facebook og:image Tag
* Google Conversion Pixels
* Twitter
* Heatmaps from Crazy Egg, notification bars Hello Bar, etc.
* It can accept ANY code snippet (HTML / Javascript / CSS) from any service
* and the list goes on and on...

== MULTISITE NOTE ==
If using this plugin on a multisite network, please make sure that the plugin is activated on a subsite level only.

> #### Plugin Information
> * [Plugin Site](https://draftpress.com/products/header-footer-code-manager/)
> * [Plugin Documentation](https://www.draftpress.com/docs/header-footer-code-manager)
> * [Free Plugins on WordPress.org](https://profiles.wordpress.org/99robots#content-plugins)
> * [Premium Plugins](https://www.draftpress.com/products)

== Installation ==

1. Upload `header-footer-code-manager` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to plugins page to see instructions for shortcode and php template tags

NOTE: If using this plugin on a multisite network, please make sure that the plugin is activated on a subsite level only.

== Screenshots ==

1. HFCM Settings
2. Dashboard - All Snippets
3. Add New Snippet - Read the documentation at:
http://www.draftpress.com/docs/header-footer-code-manager
4. Choose where you want your snippet to be displayed
5. HFCM PRO vs. FREE

== Frequently Asked Questions ==

= Q. Why do my scripts appear as text on the website? =
A. Please make sure to enclose your script within script tags - <<script>> Insert Script Here <</script>>.

= Q. Where are this plugin’s Settings located? =
A. After activating the plugin, you can click on settings link under the plugin name OR you can click the HFCM tab on the left side navigation. From there, you can add, edit, remove, and review code snippets.

= Q. How do I add code snippets to all my posts/pages? =
A. With the version 1.1.4 of the HFCM plugin, we have replaced the Specific Custom Post Types with the ability to add code to All Post Types which include posts, pages, attachments and custom post types.

= Q. I have a question =
A. Since this is a free plugin, please ask all questions on the support forum here on WordPress.org. We will try to respond to every question within 48 hours.

= Q. How can I request a feature or encourage future development? =
A. Free plugins rely on user feedback. Therefore, the best thing you can do for us is to leave a review to encourage others to try the plugin. The more users, the more likely newer features will be added. That's a very small thing to ask for in exchange for a FREE plugin.

= Q. Do you support X or Y tracking scripts? =
A. If your script is not supported, just let us know and we'll look into it immediately. We will do our best to ensure all reputable services are supported. When requesting support for a particular script, it would be nice to get a sample of the script so that we can see its structure.

== Changelog ==
= 1.1.32 = 2022-12-16
* ADDED: Warning message to caution about file editing
* FIXED: Snippet including in case of rest api in some cases

= 1.1.31 = 2022-12-09
* ADDED: Warning message to caution users of using improper code or untrusted sources code that can break site or create security risks.
* UPDATED: Compatibility with WordPress 6.1.1

= 1.1.30 = 2022-11-09
* FIXED: Proper checks for user access and capabilities
* UPDATED: Compatibility with WordPress 6.1

= 1.1.29 = 2022-09-21
* FIXED: Description not showing on the RSS feed page

= 1.1.28 = 2022-09-18
* FIXED: Check for not rendering the snippets on the RSS feed page
* UPDATED: Compatibility with WordPress 6.0.2

= 1.1.27 = 2022-07-15
* FIXED: Internationalization support for PO Translation files. Plugin now supports translation to additional languages in addition to the base language, English.
* ADDED: 1 Translation for Hindi.
* UPDATED: Compatibility with WordPress 6.0.1

= 1.1.26 = 2022-07-01
* FIXED: Code snippet sanitization, removed due to incompatibility with functionality

= 1.1.25 = 2022-06-29
* UPDATED: Code improvements as per WordPress standards

= 1.1.24 = 2022-06-25
* FIXED: XSS Security Vulnerability fix

= 1.1.23 = 2022-06-10
* UPDATED: Compatibility with WordPress 6.0

= 1.1.22 = 2022-05-10
* FIXED: PHP warnings when adding/editing snippets
* UPDATED: Add confirmation before deleting snippets
* UPDATED: Copy shortcode button

= 1.1.21 = 2022-04-21
* ADDED: Copy shortcode to clipboard buttons on edit snippet page and on snippet list page
* UPDATED: Compatibility with WordPress 5.9.3
* UPDATED: Included Custom Taxonomies for snippets
* UPDATED: Snippet code editor size
* ADDED: Increased Number of Allowed Posts/Page Exclusions to 200K+ posts.

= 1.1.20 = 2022-03-26
* FIXED: MultiSite issue with subsites in network

= 1.1.19 = 2022-03-24
* FIXED: MultiSite slow query issue
* UPDATED: Compatibility with WordPress 5.9.2

= 1.1.18 = 2022-03-05
* ADDED: Ability to apply snippets to search, home, archive page only
* ADDED: Snippet search functionality
* ADDED: Snippet type filter
* ADDED: Snippet sort by location
* ADDED: Delete snippet button on edit snippet page

= 1.1.17 = 2022-02-17
* FIXED: XSS vulnerability with request parameter page in the HFCM snippet listing screen
* UPDATED: Compatibility with WordPress 5.9
* UPDATED: Text & Plugin assets
* UPDATED: Snippet column length

= 1.1.16 = 2021-12-13
* FIXED: Author not showing on Add/Edit snippet screen
* ADDED: PRO banner
* UPDATED: Text & Plugin icon

= 1.1.15 = 2021-10-26
* UPDATED: Code improvements as per WordPress standards
* Proper escaping
* Proper variable naming
* Added recommended spacing by WordPress

= 1.1.14 = 2021-10-08
* FIXED: SQL Vulnerability with listing orderby
* FIXED: DbDelta query
* UPDATED: Compatibility with WordPress 5.8.1

= 1.1.13 = 2021-09-06
* FIXED: bool function return type
* UPDATED: Minimum required PHP version
* UPDATED: Lowest WordPress version that the plugin will work on

= 1.1.12 = 2021-09-04
* FIXED: Import upload file type error
* FIXED: Location options for site display (specific categories)
* UPDATED: File format for the import/export files
* UPDATED: Compatibility with WordPress 5.8

= 1.1.11 = 2021-08-10
* FIXED: Warnings - Undefined Variables
* FIXED: Selectize issue of not able to select first option from the dropdown
* ADDED: Snippet types
* ADDED: Code Editor in place of textarea
* ADDED: Import/Export Snippets
* UPDATED: Compatibility with WordPress 5.8

= 1.1.10 = 2021-04-23
* FIXED: Warnings - Undefined Variables
* UPDATED: Compatibility with WordPress 5.7.1

= 1.1.9 = 2021-02-18
* UPDATED: Compatibility with WordPress 5.6.1

= 1.1.8 = 2020-09-01
* FIXED: Specific Taxonomy Snippets showing on archives with at least one instance.
* UPDATED: Compatibility with WordPress 5.5.0

= 1.1.7 = 2020-04-20
* UPDATED: Compatibility with WordPress 5.4.0
* FIXED: Warnings and Exclude Pages/Posts showing up on Shortcode Only screen

= 1.1.6 = 2019-09-22
* FIXED: Specific Pages targeting Blog index page, even when it is not selected.

= 1.1.5 = 2019-08-29
* FIXED: Unable to target Blog index page

= 1.1.4 = 2019-08-15
* UPDATED: All snippets list now shows 20 snippets in the first page instead of 10
* ADDED: Replaced Specific Custom Post Types under Site Display to include the functionality to add code snippets to all post types, including posts, pages, custom post types & attachments
* UPDATED: Compatibility with WordPress 5.2.2

= 1.1.3 = 2019-05-03
* UPDATED: Compatibility with WordPress 5.1.1

= 1.1.2 = 2019-01-07
* FIXED: Blank page on dismissing notice when on a few admin pages
* UPDATED: Admin notice will now only show on the HFCM plugin admin pages

= 1.1.1 = 2018-12-31
* FIXED: Warning - Unexpected Output - headers already sent

= 1.1.0 = 2018-12-31
* UPDATED: Code Optimization
* UPDATED: Added plugin settings link, and update documentation.
* Compatible with WordPress 5.0.2

= 1.0.9 = 2018-10-09
* UPDATED: Code Optimization

= 1.0.8 = 2018-10-04
* FIXED: Updated obsolete code causing errors.

= 1.0.7 = 2018-10-01
* ADDED: Functionality to Exclude Posts and Pages from the Site Wide option.

= 1.0.6 = 2018-07-10
* FIXED: Latest Posts dropdown selection always resets to 1 on save / update.

= 1.0.5 = 2018-06-14
* UPDATED: "All Snippets" page to show 10 snippets before pagination starts.

= 1.0.4 = 2018-05-23
* FIXED: style-admin.css not loading on "Add New Snippet" page.
* FIXED: Post List not loading when selecting "Specific Posts" in "Site Display" under Add New Snippet.

= 1.0.3 = 2017-06-09
* Compatible with WordPress 4.8

= 1.0.2 = 2016-9-22
* FIXED: Updated code triggering a fatal error for sites with older PHP versions.

= 1.0.1 = 2016-9-20
* FIXED: Updated code triggering a fatal error for sites with older PHP versions; now compatible.

= 1.0.0 = 2016-7-20
* Initial release - HFCM is born! :)
