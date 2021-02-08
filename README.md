# Whooo's WordPress Starter Kit
---

**Don't clone this repository.**

## What's included
1. [Picostrap](https://picostrap.com/) theme v1.2.0
2. [Picostrap](https://picostrap.com/) child theme v1.1.0
3. [Advanced Custom Fields Pro](https://www.advancedcustomfields.com/pro/) v5.9.4
4. [Gravity Forms](https://www.gravityforms.com/) v2.4.22.4
5. [EditorConfig](https://editorconfig.org/) file to follow [WordPress codings standars](https://make.wordpress.org/core/handbook/best-practices/coding-standards/).

## How to
* Download this repository (don't clone it). Code -> Download Zip. Use this files in your new project.
* If you don't use any of the included plugins, delete them from your project.
* Picostrap already included auto-compilation for it's SCSS files and a live-reload feature built in. You don't need to install anything.
* Use your local dev enviroment to develop your new theme. If you don't have a local dev enviroment, we recommend [Local WP](https://localwp.com/).

## Rules
* You must follow [WordPress codings standars](https://make.wordpress.org/core/handbook/best-practices/coding-standards/). That's why we include the .editorconfig file. Use it. There's support for it on many code editors and IDEs. [Check it out](https://editorconfig.org/).
* You must use the included theme, [Picostrap](https://picostrap.com/). **Use the [child theme](https://developer.wordpress.org/themes/advanced-topics/child-themes/)** for your modifications. **Don't edit the main theme** nor add or remove files from it. Don't touch the main theme folder. Your work must be done inside the [child theme](https://developer.wordpress.org/themes/advanced-topics/child-themes/).
* You must use [SCSS](https://sass-lang.com/) for your CSS, and [Bootstrap 4 o 5](https://getbootstrap.com/). Theme already include Bootstrap v4 (and soon it will include v5).
* If you need to generate a form for the site (any purpose), you must use the included plugin for it, Gravity Forms. No other contact form plugin is allowed.
* If you need to generate [post metaboxes](https://www.advancedcustomfields.com/resources/adding-fields-posts/), an [Options Page](https://www.advancedcustomfields.com/resources/options-page/) or [Gutenberg Blocks](https://www.advancedcustomfields.com/resources/blocks/), use the included plugin Advanced Custom Fields Pro.
* Use the proper WP's functions and methods to work in a theme or plugin. Again, [WordPress codings standars](https://make.wordpress.org/core/handbook/best-practices/coding-standards/) are a must. So you need to know the [Template Hierarchy](https://developer.wordpress.org/themes/basics/template-hierarchy/), use the proper calls to [include styles and scripts](https://developer.wordpress.org/themes/basics/including-css-javascript/) in your theme, know [the loop](https://developer.wordpress.org/themes/basics/the-loop/), use [get_stylesheet_directory_uri()](https://developer.wordpress.org/reference/functions/get_stylesheet_directory_uri/) to call the proper URL to include an image, etc. If WordPress provides a way to work with something, doing that "something" in a non-WordPress way is not ok.
* Don't hard-code URLs in your theme or plugins. Ever.

## Questions?
Please, ask at #devs in Slack.
