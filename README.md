# WordPress Starter Kit
---

**Don't clone this repository.**

## What's included
1. [Picostrap](https://picostrap.com/) theme v1.2.1
2. [Picostrap](https://picostrap.com/) child theme v1.1.0
3. [Advanced Custom Fields Pro](https://www.advancedcustomfields.com/pro/) v5.9.4
4. [Gravity Forms](https://www.gravityforms.com/) v2.4.22.4
5. [EditorConfig](https://editorconfig.org/) file to follow [WordPress codings standars](https://make.wordpress.org/core/handbook/best-practices/coding-standards/).
6. Several recommended [WordPress plugins for theme development](https://developer.wordpress.org/themes/getting-started/setting-up-a-development-environment/). For use during development.

## How to develop with this kit
* Download this repository (don't clone it). Download it using the button: Code -> Download Zip. Use this files in your new project.
* If you don't use any of the included plugins, delete them from your project.
* Picostrap already included auto-compilation for it's SCSS files and a live-reload feature built in. You don't need to install anything.
* Use your local dev enviroment to develop your new theme. If you don't have a local dev enviroment, we recommend [Local WP](https://localwp.com/).
* Configure your local WordPress instance with [WP_DEBUG enabled](https://developer.wordpress.org/themes/getting-started/setting-up-a-development-environment/#wp_debug).
* Update any development plugin included. Then enable the ones you want to use during development.

## Don't forget
* You must follow [WordPress codings standars](https://make.wordpress.org/core/handbook/best-practices/coding-standards/). That's why we include the .editorconfig file. Use it. There's support for it on many code editors and IDEs. [Check it out](https://editorconfig.org/).
* You must use the included theme, [Picostrap](https://picostrap.com/). **Use the [child theme](https://developer.wordpress.org/themes/advanced-topics/child-themes/)** for your modifications. **Don't edit the main theme** nor add or remove files from it. Don't touch the main theme folder. Your work must be done inside the [child theme](https://developer.wordpress.org/themes/advanced-topics/child-themes/).
* You must use [SCSS](https://sass-lang.com/) for your CSS, and [Bootstrap 4 o 5](https://getbootstrap.com/). Theme already include Bootstrap v4 (and soon it will include v5).
* If you need to generate a form for the site (any purpose), you must use the included plugin for it, Gravity Forms. No other contact form plugin is allowed.
* If you need to generate [post metaboxes](https://www.advancedcustomfields.com/resources/adding-fields-posts/), an [Options Page](https://www.advancedcustomfields.com/resources/options-page/) or [Gutenberg Blocks](https://www.advancedcustomfields.com/resources/blocks/), use the included plugin Advanced Custom Fields Pro.
* Use the proper WP's functions and methods to work in a theme or plugin. Again, [WordPress codings standars](https://make.wordpress.org/core/handbook/best-practices/coding-standards/) are a must. So you need to know the [Template Hierarchy](https://developer.wordpress.org/themes/basics/template-hierarchy/), use the proper calls to [include styles and scripts](https://developer.wordpress.org/themes/basics/including-css-javascript/) in your theme, know [the loop](https://developer.wordpress.org/themes/basics/the-loop/), use [get_stylesheet_directory_uri()](https://developer.wordpress.org/reference/functions/get_stylesheet_directory_uri/) to call the proper URL to include an image, etc. If WordPress provides a way to work with something, doing that "something" in a non-WordPress way is not ok.
* Test your theme with the recommended [WordPress unit test](https://codex.wordpress.org/Theme_Unit_Test). Download the sample [XML with data](https://raw.githubusercontent.com/WPTT/theme-unit-test/master/themeunittestdata.wordpress.xml), and import it into your WordPress development instance using WordPress default importer inside the wp-admin: Tools -> Import -> WordPress. Check the "Download and import file attachments" before importing. Make sure your theme looks correct with simple pages, posts, etc.
* It's ok to copy snippets and solutions from the internet. But please: keep in mind the licenses of the code you're using. When pasting a snippet, make sure to format it to use WP's coding standards. Don't mix code indentention. Using .editorconfig is for that. The use of [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) with [WordPress Coding Standards](https://github.com/WordPress/WordPress-Coding-Standards) ([2](https://gist.github.com/nunomorgadinho/b2d8e5b8f5fec5b0ed946b24fa288a91)) will certainly help, and better if you can setup your code editor to do it automatically for your. Correctly [prefix every function](https://developer.wordpress.org/plugins/plugin-basics/best-practices/#prefix-everything) you copy with a unique prefix for the project, don't keep the name copied from the internet as is.
* Don't make a mess with your code. This will help others and the future you to maintain the project easily. Use PHP includes and separate segments of code according to their functionality. Don't throw every snippet in the functions.php file an make a one big mess inside. Don't make a gigantic unique SCSS or JS file that no-one can understand after. Use SCSS imports. Use multiple enqueued JS for different functionalities.
* Don't hard-code URLs in your theme or plugins. Ever.

## To Production
* Delete any unused installed plugin or theme. Don't delete the main Picostrap theme; his child theme depend on it.
* Delete any post or page not in use. Don't forget to delete data (posts, pages, attachments) imported with the WordPress unit test XML.
* Disable and delete any development plugin installed.
* Disable WP_DEBUG in your wp-config.php
* If you used Advanced Custom Fields Pro, then enable [Local JSON caching](https://www.advancedcustomfields.com/resources/local-json/).
* Don't forget to change any email (sender/"from" and recipient/"to" addresses) in your forms and WooCommerce if was used. Recipient should be pointing to the correct client's email address.
* Check that the emails are properly configured. Use [Stop WP Emails Going to Spam](https://wordpress.org/plugins/stop-wp-emails-going-to-spam/) to set the correct email address for the email sent from WP (anything@thefinaldomain.tld), and make sure the SPF records exists. Send a test email with [Check & Log Email](https://wordpress.org/plugins/check-email/) to verify everything is set up correctly.
* Create a different administrator user for the final client. Don't give the client the same user as Agency is using.
* Done.

## Questions?
Please, ask at #devs in Slack.
