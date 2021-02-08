<?php
/**
 * Plugin Name: Template Debugger
 * Plugin URI: https://wordpress.org/plugins/quick-edit-template-link/
 * Description: A template debugger that helps you identify what template files are being used on the page you're viewing
 * Version: 3.1.2
 * Author: Danny Hearnah - ChubbyNinjaa
 * Author URI: https://twitter.com/danny_developer
 * License: GPL2
 *
 * Copyright 2019  DANNY HEARNAH  (email : dan.hearnah@gmail.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

defined('ABSPATH') or die("No direct access");


require 'class/qetl_templateDebugger.php';
$QETL_TD = new qetl_templateDebugger();
