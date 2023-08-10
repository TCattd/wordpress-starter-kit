=== GenTime ===
Contributors: Cybr
Tags: admin bar, generation, time, load, php, speed, performance
Requires at least: 3.1.0
Tested up to: 6.2
Requires PHP: 5.6.0
Stable tag: 1.1.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

GenTime accurately shows the page generation time in the WordPress admin bar.

== Description ==

= GenTime =

This plugin shows site administrators the time in seconds of how fast the page loaded in the WordPress admin bar.

That's it, pretty simple!

== Installation ==

1. Install GenTime either via the WordPress.org plugin directory or upload the files to your server.
1. Either Network Activate this plugin or activate it on a single site.
1. That's it!

= Filters =

**Changes the minimum role for which the GenTime is shown:**

`
add_filter( 'gentime_minimum_role', 'my_gentime_minimum_role' );
function my_gentime_minimum_role( $default = 'install_plugins' ) {

    // See http://codex.wordpress.org/Roles_and_Capabilities for a list of role names
    $role = 'edit_pages';

    return $role;
}
`

**Changes the number of decimals to output:**
`
add_filter( 'gentime_decimals', 'my_gentime_decimals' );
function my_gentime_decimals( $default = 3 ) {
    return 4;
}
`

== Changelog ==

= 1.1.0 =
* Added: Now uses WordPress 5.8's more accurate function, when available, `timer_float()`.
* Updated: Now uses HTML5 for the styles output.
* Other: Refactored the plugin to support PHP 5.6 or later, from 5.2 or later.
* Other: Cleaned up code.

= 1.0.4 =
* Fixed: This plugin is now converted to UNIX line feed.
* Improved: Early sanitation of translation strings.
* Updated: POT file.
* Confirmed: WordPress 4.6 support.
* Other: The plugin license has been upgraded to GPLv3.
* Other: Cleaned up code.

= 1.0.3 =
* Fixed: The cache now works as intended.
* Fixed/Improved: Erroneous order of function checking. Which actually had no impact.
* Other: `gentime_minimum_role` filter now converts input to string.

= 1.0.2 =
* Added: POT translation file.
* Improved: Slightly improved performance (every Herz counts) by adding PHP runtime static cache earlier.
* Confirmed: WordPress 4.5+ compatibility.
* Cleaned up code.

= 1.0.1 =
* Changed: Minimum capability from edit_plugins to install_plugins so that the generation time is still shown when the Editor has been disabled.
* Added: PHP Staticvar caching for capability.
* Confirmed: 4.4.0+ support.
* Cleaned up PHP.

= 1.0.0 =
* Initial Release
