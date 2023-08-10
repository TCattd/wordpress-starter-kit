=== JSM Show Post Metadata ===
Plugin Name: JSM Show Post Metadata
Plugin Slug: jsm-show-post-meta
Text Domain: jsm-show-post-meta
Domain Path: /languages
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl.txt
Assets URI: https://jsmoriss.github.io/jsm-show-post-meta/assets/
Tags: custom fields, meta, post meta, post types, delete, debug, inspector
Contributors: jsmoriss
Requires PHP: 7.2.34
Requires At Least: 5.5
Tested Up To: 6.2.2
Stable Tag: 3.1.1

Show post metadata (aka custom fields) in a metabox when editing posts / pages - a great tool for debugging issues with post metadata.

== Description ==

**The JSM Show Post Metadata plugin displays post (ie. posts, pages, and custom post types) meta keys (aka custom field names) and their unserialized values in a metabox at the bottom of post editing pages.**

The current user must have the [WordPress *manage_options* capability](https://wordpress.org/support/article/roles-and-capabilities/#manage_options) (allows access to administration options) to view the Post Metadata metabox, and the *manage_options* capability to delete individual meta keys.

The default *manage_options* capability can be modified using the 'jsmspm_show_metabox_capability' and 'jsmspm_delete_meta_capability' filters (see filters.txt in the plugin folder).

There are no plugin settings - simply install and activate the plugin.

= Related Plugins =

* [JSM Show Comment Metadata](https://wordpress.org/plugins/jsm-show-comment-meta/)
* [JSM Show Term Metadata](https://wordpress.org/plugins/jsm-show-term-meta/)
* [JSM Show User Metadata](https://wordpress.org/plugins/jsm-show-user-meta/)
* [JSM Show Registered Shortcodes](https://wordpress.org/plugins/jsm-show-registered-shortcodes/)

== Installation ==

== Frequently Asked Questions ==

== Screenshots ==

01. The "Post Metadata" metabox added to admin post editing pages.

== Changelog ==

<h3 class="top">Version Numbering</h3>

Version components: `{major}.{minor}.{bugfix}[-{stage}.{level}]`

* {major} = Major structural code changes and/or incompatible API changes (ie. breaking changes).
* {minor} = New functionality was added or improved in a backwards-compatible manner.
* {bugfix} = Backwards-compatible bug fixes or small improvements.
* {stage}.{level} = Pre-production release: dev < a (alpha) < b (beta) < rc (release candidate).

<h3>Repositories</h3>

* [GitHub](https://jsmoriss.github.io/jsm-show-post-meta/)
* [WordPress.org](https://plugins.trac.wordpress.org/browser/jsm-show-post-meta/)

<h3>Changelog / Release Notes</h3>

**Version 3.2.0 (2023/07/17)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Update for the `SucomUtil` class.
* **Requires At Least**
	* PHP v7.2.34.
	* WordPress v5.5.

**Version 3.1.1 (2023/07/08)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Minor code optimization and standardization:
		* Replaced `{get|update|delete}_{comment|post|term|user}_meta()` functions by `{get|update|delete}_metadata()`.
* **Requires At Least**
	* PHP v7.2.34.
	* WordPress v5.5.

**Version 3.1.0 (2023/07/04)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Renamed the `sucomBlockPostbox()` javascript function to `sucomEditorPostbox()`.
* **Requires At Least**
	* PHP v7.2.34.
	* WordPress v5.5.

**Version 3.0.14 (2023/06/02)**

* **New Features**
	* None.
* **Improvements**
	* None.
* **Bugfixes**
	* None.
* **Developer Notes**
	* Update for the `SucomUtil` class.
* **Requires At Least**
	* PHP v7.2.
	* WordPress v5.5.

== Upgrade Notice ==

= 3.2.0 =

(2023/07/17) Update for the `SucomUtil` class.

= 3.1.1 =

(2023/07/08) Minor code optimization and standardization.

= 3.1.0 =

(2023/07/04) Renamed the `sucomBlockPostbox()` javascript function to `sucomEditorPostbox()`.

= 3.0.14 =

(2023/06/02) Update for the `SucomUtil` class.

