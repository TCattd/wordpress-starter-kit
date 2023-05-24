<?php
/**
 * Login Form
 *
 * This template can be overridden by copying it to yourtheme/templates/myaccount-login-register.php
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 7.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$wppb_woosync_settings = get_option( 'wppb_woosync_settings');
$shortcode = '[wppb-register]';

if ( $wppb_woosync_settings['RegisterForm'] != 'wppb-default-register' )
    $shortcode = '[wppb-register form_name=' . Wordpress_Creation_Kit_PB::wck_generate_slug( $wppb_woosync_settings['RegisterForm']) .']';

?>

<?php wc_print_notices(); ?>

<?php do_action( 'woocommerce_before_customer_login_form' ); ?>

<?php if ( get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' ) : ?>

<div class="u-columns col2-set" id="customer_login">

	<div class="u-column1 col-1">

<?php endif; ?>

		<h2><?php esc_html_e( 'Login', 'woocommerce' ); //phpcs:ignore ?></h2>

	       <?php echo do_shortcode('[wppb-login]'); ?>

<?php if ( get_option( 'woocommerce_enable_myaccount_registration' ) === 'yes' ) : ?>

	</div>

	<div class="u-column2 col-2">

		<h2><?php esc_html_e( 'Register', 'woocommerce' ); //phpcs:ignore ?></h2>

		<?php echo do_shortcode($shortcode); ?>

	</div>

</div>
<?php endif; ?>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
