=== Reveal IDs ===
Contributors: Alphawolf
Donate link: https://www.schloebe.de/donate/
Tags: reveal, id, wp-admin, hidden, category, post, page, media, links, capability, user, restore, comments, taxonomy, custom posts, post type, multisite, ms
Requires at least: 3.0
Tested up to: 6.3.99
Stable tag: trunk
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

What this plugin does is to reveal most removed IDs on admin pages, as it was in versions prior to 2.5.

== Description ==

With WordPress 2.5 being released, the IDs on all admin pages have been removed as it is. Propably due to the fact that the common user dont need them. However, for advanced WordPress Users/ developers those IDs were quite interesting for some plugins or template tags.

What this plugin does is to reveal most removed entry IDs on admin pages, showing the entry IDs, as it was in versions prior to 2.5

**Features:**

* Sortable columns for WP 3.1 or higher

[Developer on Twitter](https://twitter.com/wpseek "Developer on Twitter")

**Included languages:**

* English
* German (de_DE) (Thanks to me ;-))
* Brazilian Portuguese (pt_BR) (Thanks for contributing brazilian portuguese language goes to [Maurício Samy Silva](https://www.maujor.com))
* Italian (it_IT) (Thanks for contributing italian language goes to Gianluca Urgese)
* Spanish (es_ES) (Thanks for contributing spanish language goes to [Karin Sequen](https://www.es-xchange.com))
* Russian (ru_RU) (Thanks for contributing russian language goes to [Dimitry German](https://grugl.me))
* Belorussian (by_BY) (Thanks for contributing belorussian language goes to [FatCow](https://www.fatcow.com))
* Dutch (nl_NL) (Thanks for contributing dutch language goes to [wpwebshop.com](https://wpwebshop.com/premium-wordpress-plugins/))
* European Portuguese (pt_PT) (Thanks for contributing european portuguese language goes to [PL Monteiro](https://thepatientcapacitor.com/))
* Polish (pl_PL) (Thanks for contributing polish language goes to Simivar)
* Romanian (ro_RO) (Thanks for contributing romanian language goes to [Anunturi Jibo](https://www.jibo.ro))
* Georgian (ge_KA) (Thanks for contributing georgian language goes to [Online Casino Bluebook](https://www.onlinecasinobluebook.com/))
* Swedish (sv_SE) (Thanks for contributing swedish language goes to [Tor-Bjorn Fjellner](https://fjellner.com/))
* Ukrainian (uk) (Thanks for contributing ukrainian language goes to [Everycloud](https://www.everycloudtech.com/))

**Looking for more WordPress plugins? Visit [www.schloebe.de/portfolio/](https://www.schloebe.de/portfolio/)**

== Frequently Asked Questions ==

None.

== Installation ==

1. Download the plugin and unzip it.
1. Upload the folder reveal-ids-for-wp-admin-25/ to your /wp-content/plugins/ folder.
1. Activate the plugin from your WordPress admin panel.
1. Installation finished.

== Changelog ==

= 1.6.1 =
* Revert PHP8 requirement

= 1.6.0 =
* PHP 8.2 compatibility

= 1.5.5 =
* PHP 8.2 compatibility

= 1.5.4 =
* WordPress 5.3 compatibility

= 1.5.3 =
* Showing ID columns for plugins that register non-public post types and taxonomies

= 1.5.2 =
* Workaround for third-party plugin incompatibilities

= 1.5.1 =
* Sortable columns for users listing on multisite/network

= 1.5.0 =
* Full support for multisite
* Code cleanup

= 1.4.7 =
* WordPress 4.7 compatibility
* PHP 7 compatibility

= 1.4.6.2 =
* Added ukrainian localization (Thanks to Alisa Bagrii!)

= 1.4.6.1 =
* Backend cleanup
* Added uninstall.php

= 1.4.6 =
* Fixed an issue that caused a redirection loop in some environments

= 1.4.5 =
* Fixed a 'Redefining already defined constructor' bug that occured on several configurations
* Prepwork for Language Packs

= 1.4.1 =
* Increased width and added word-wrap for ID columns (for long IDs)

= 1.4.0 =
* Sortable columns for WP 3.1 or higher

= 1.3.0 =
* Complete Code rewrite
* Full support for custom post types
* Full support for custom taxonomies

= 1.2.7 =
* Plugin now requires at least 2.6
* Readme.txt updated to be more compliant with the readme.txt standard
* Moved screenshots off the package to the assets/ folder

= 1.2.6 =
* Removed capability options. If you don't want to see IDs on a particular panel, just remove them via Screen Options.
* Issues with SSL fixed

= 1.2.5 =
* Maintenance Update

= 1.2.4 =
* Maintenance Update

= 1.2.3 =
* Fixed ID columns so that other plugins' custom columns won't be empty anymore

= 1.2.2 =
* Added georgian localization (Thanks to Kasia!)

= 1.2.1 =
* Added romanian localization (Thanks to Anunturi Jibo!)

= 1.2.0 =
* FIXED: German localization wasn't loading

= 1.1.9 =
* Added polish localization (Thanks to Simivar!)
* Added european portuguese localization (Thanks to PL Monteiro!)

= 1.1.8 =
* Added dutch localization (Thanks to wpwebshop.com!)

= 1.1.7 =
* Code cleanup

= 1.1.6 =
* Category IDs show up in WP 3.0 now

= 1.1.5 =
* Added IDs for tag management page

= 1.1.4 =
* Using new hooks to add ID columns where javascript was used before (due to missing hooks) in WP 2.8 and above
* Fixed an issue with capabilites

= 1.1.3 =
* Support for Fluency Admin Theme plugin
* Support for Changelog readme.txt standard

= 1.1.2 =
* Added IDs for comments page

= 1.1.1 =
* Minor code changes

= 1.1.0 =
* Added IDs for link categories page
* Fixed bug that occured on WP 2.5

= 1.0.6 =
* Link IDs now show up again
* Fixed issue with category and user IDs

= 1.0.5 =
* Improved compatibility with WP 2.7 (UI)

= 1.0.4 =
* Code improvements
* Improved compatibility with WP 2.7

= 1.0.3 =
* Changed include mechanism
* Improved activation process

= 1.0.2 =
* Added italian localization (Thanks to Gianluca Urgese!)
* Fixed incompatibility with Gengo plugin (Thanks to dragunoff!)

= 1.0.1 =
* Some changes to the options page

= 1.0 =
* Added IDs for users management page
* More reliable way of displaying category IDs (removed alpha status)
* Added brazilian portuguese localization (Thanks to Maurício Samy Silva!)
* Some code cleanup

= 0.7.6 =
* Small cosmetic change
* Yeah, really. Nothing more.

= 0.7.5 =
* A lot cleaner code
* Minor language fixes

= 0.7.4 =
* Minor fix in category management

= 0.7.3 =
* Fixed error that occured on older blogs (2.1.*) that have recently been updated to WP 2.5 (Thanks to Lars-Tilo Handke for testing!)

= 0.7.2 =
* Fixed error that occasionally damaged category creation

= 0.7.1 =
* Added hint to use the 'Show category IDs' option at your own risk since this seems to not work properly at the moment

= 0.7 =
* More IDs to reveal
* Rights management (Who's allowed to see IDs...)

= 0.5 =
* Plugin released

== Screenshots ==

1. The added ID column
1. Admin Options Page
