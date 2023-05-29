<?php

/**
 * Plugin Name: Disable Cart Fragments
 * Plugin URI: https://wordpress.org/plugins/disable-cart-fragments/
 * Description: A better way to disable WooCommerce's cart fragments script, and re-enqueue it when the cart is updated. Works with all caching plugins.
 * Version: 2.2
 * Author: Optimocha
 * Author URI: https://optimocha.com/
 * License: GPL v3
 * Requires PHP: 5.6 or later
 * WC requires at least: 2.0
 * WC tested up to: 7.3.0
 * Text Domain: disable-cart-fragments
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 3, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */

defined( 'ABSPATH' ) or die();

if( !defined( 'OPTIMOCHA_DCF_PATH' ) ) {
	define( 'OPTIMOCHA_DCF_PATH', plugin_dir_path( __FILE__ ) );
}

if( !defined( 'OPTIMOCHA_DCF_BASENAME' ) ) {
	define( 'OPTIMOCHA_DCF_BASENAME', plugin_basename( __FILE__ ) );
}

if( !defined( 'OPTIMOCHA_DCF_DOMAIN' ) ) {
	define( 'OPTIMOCHA_DCF_DOMAIN', 'disable-cart-fragments' );
}

require OPTIMOCHA_DCF_PATH . "/DCF_Notice_Manager.php";

add_action( 'before_woocommerce_init', function() {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );

if ( ! class_exists( 'Optimocha_Disable_Cart_Fragments' ) ) {

	class Optimocha_Disable_Cart_Fragments {

		function __construct(){
			add_filter( "plugin_action_links_" . OPTIMOCHA_DCF_BASENAME, array( $this, 'settings_links' ) );

			add_action('admin_init', [ $this, 'set_pro_service_notice' ]);

			if( $this->dcf_is_plugin_active( 'speed-booster-pack/speed-booster-pack.php' ) ) {

				add_action( 'admin_notices', array( $this, 'sbp_active_warning' ) );

			} else if( $this->dcf_is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

				add_action( 'wp_enqueue_scripts', array( $this, 'disable_cart_fragments' ), 999 );

			}

		}

		function dcf_is_plugin_active( $plugin ) {
			$is_plugin_active_for_network = false;

			$plugins = get_site_option( 'active_sitewide_plugins' );
			if ( isset( $plugins[ $plugin ] ) ) {
				$is_plugin_active_for_network = true;
			}

			return in_array( $plugin, (array) get_option( 'active_plugins', array() ), true ) || $is_plugin_active_for_network;
		}

		function sbp_active_warning() {

			?>
			<div class="notice notice-error">
				<p><?php _e( "We detected that you're already using another plugin of ours: Speed Booster Pack. Since SBP already has the same \"Disable cart fragments\" feature, you can safely deactivate the Disable Cart Fragments plugin and keep using Speed Booster Pack! :)", OPTIMOCHA_DCF_DOMAIN ); ?>
				</p>
			</div>
			<?php
		}


		/*
		 * Disable Cart Fragments Function
		 */
		function disable_cart_fragments() {
			global $wp_scripts;

			$handle = 'wc-cart-fragments';
			if( isset( $wp_scripts->registered[ $handle ] ) && $wp_scripts->registered[ $handle ] ) {

				$load_cart_fragments_path = $wp_scripts->registered[ $handle ]->src;
				$wp_scripts->registered[ $handle ]->src = null;
				wp_add_inline_script(
					'jquery',
					'
					function optimocha_getCookie(name) {
						var v = document.cookie.match("(^|;) ?" + name + "=([^;]*)(;|$)");
						return v ? v[2] : null;
					}

					function optimocha_check_wc_cart_script() {
					var cart_src = "' . $load_cart_fragments_path . '";
					var script_id = "optimocha_loaded_wc_cart_fragments";

						if( document.getElementById(script_id) !== null ) {
							return false;
						}

						if( optimocha_getCookie("woocommerce_cart_hash") ) {
							var script = document.createElement("script");
							script.id = script_id;
							script.src = cart_src;
							script.async = true;
							document.head.appendChild(script);
						}
					}

					optimocha_check_wc_cart_script();
					document.addEventListener("click", function(){setTimeout(optimocha_check_wc_cart_script,1000);});
					'
				);

			}
		}

		function settings_links( $links ) {
			$pro_link = ' <a href="https://optimocha.com/?ref=disable-cart-fragments" target="_blank">Pro Help</a > ';
			array_unshift( $links, $pro_link );

			return $links;
		}

        public function set_pro_service_notice() {
            new \DCF\DCF_Notice_Manager();
            \DCF\DCF_Notice_Manager::display_notice('dcf_pro_service', '<p><a href="https://optimocha.com/?ref=disable-cart-fragments" target="_blank">' . __( "If you need any help optimizing your website speed, if you're ready to <em>invest in</em> speed optimization, you can visit Optimocha.com by clicking here, and have us speed up your site!", OPTIMOCHA_DCF_DOMAIN ) . '</a></p>', 'info');
		}
	}

	new Optimocha_Disable_Cart_Fragments();
}
