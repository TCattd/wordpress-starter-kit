<?php

/**
 * Plugin Name:       DoLogin Security
 * Description:       Passwordless login. 2FA verification login. GeoLocation (Continent/Country/City) or IP range to limit login attempts. Support Whitelist and Blacklist. Support WooCommerce. Login attempt limit. CLI supported for generating passwordless login.
 * Version:           3.7.1
 * Author:            WPDO
 * WC requires at least: 1.0.0
 * WC tested up to:   6.8.2
 * License:           GPLv3
 * License URI:       http://www.gnu.org/licenses/gpl.html
 * Text Domain:       dologin
 * Domain Path:       /lang
 *
 * Copyright (C) 2023 WPDO
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.

 */
defined('WPINC') || exit;

if (defined('DOLOGIN_V')) {
	return;
}

define('DOLOGIN_V', '3.7.1');

!defined('DOLOGIN_DIR') && define('DOLOGIN_DIR', __DIR__ . '/'); // Full absolute path '/usr/local/***/wp-content/plugins/dologin/' or MU
!defined('DOLOGIN_PLUGIN_URL') && define('DOLOGIN_PLUGIN_URL', plugin_dir_url(__FILE__)); // Full URL path '//example.com/wp-content/plugins/dologin/'

!defined('DOLOGIN_LOGO') && define('DOLOGIN_LOGO', '<img src="' . DOLOGIN_PLUGIN_URL . 'assets/shield.svg" class="dologin-logo" style="max-width:50px;max-height:37px;"> ');

require_once DOLOGIN_DIR . 'autoload.php';

// Define CLI
if ((defined('WP_CLI') && WP_CLI) || PHP_SAPI == 'cli') {
	!defined('DOLOGIN_CLI') &&  define('DOLOGIN_CLI', true);

	// Register CLI cmd
	if (method_exists('WP_CLI', 'add_command')) {
		WP_CLI::add_command('dologin', 'dologin\CLI');
	}
}

/**
 * API for external plugin usage
 * @since  1.4.1
 */
if (!function_exists('dologin_gen_link')) {
	function dologin_gen_link($src, $uid = false)
	{
		if (!$uid) {
			$user = wp_get_current_user();
		} else {
			$user = get_user_by('id', (int) $uid);
		}

		$uid = $user->ID;
		$src .= '-' . $user->display_name;

		return \dologin\Pswdless::cls()->gen_link($src, $uid, true);
	}
}

\dologin\Core::cls();
