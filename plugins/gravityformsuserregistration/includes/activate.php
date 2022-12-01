<?php

defined( 'ABSPATH' ) || die();

define( 'WP_INSTALLING', true );

global $current_site;

// include GF User Registration functionality
require_once( gf_user_registration()->get_base_path() . '/includes/signups.php' );

GFUserSignups::prep_signups_functionality();

do_action( 'activate_header' );

function do_activate_header() {
	do_action( 'activate_wp_head' );
}

add_action( 'wp_head', 'do_activate_header' );

function wpmu_activate_stylesheet() {
	?>
	<style type="text/css">
		form {
			margin-top: 2em;
		}

		#submit, #key {
			width: 90%;
			font-size: 24px;
		}

		#language {
			margin-top: .5em;
		}

		.error {
			background: #f66;
		}

		span.h3 {
			padding: 0 8px;
			font-size: 1.3em;
			font-family: "Lucida Grande", Verdana, Arial, "Bitstream Vera Sans", sans-serif;
			font-weight: bold;
			color: #333;
		}
	</style>
	<?php
}

add_action( 'wp_head', 'wpmu_activate_stylesheet' );

$var = gf_user_registration()->get_activation_var();

get_header(); ?>

	<div id="content" class="widecolumn">
		<?php if ( empty( $_GET[ $var ] ) && empty( $_POST['key'] ) ) { ?>

			<h2><?php esc_html_e( 'Activation Key Required', 'gravityformsuserregistration' ) ?></h2>
			<form name="activateform" id="activateform" method="post" action="<?php echo esc_url( gf_user_registration()->get_activation_url() ); ?>">
				<p>
					<label for="key"><?php esc_html_e( 'Activation Key:', 'gravityformsuserregistration' ) ?></label>
					<br /><input type="text" name="key" id="key" value="" size="50" />
				</p>
				<p class="submit">
					<input id="submit" type="submit" name="Submit" class="submit" value="<?php esc_attr_e( 'Activate', 'gravityformsuserregistration' ) ?>" />
				</p>
			</form>

		<?php } else {

			$key    = ! empty( $_GET[ $var ] ) ? $_GET[ $var ] : $_POST['key'];
			$result = GFUserSignups::activate_signup( $key );
			if ( is_wp_error( $result ) ) {
				if ( 'already_active' == $result->get_error_code() || 'blog_taken' == $result->get_error_code() ) {
					$signup = $result->get_error_data();
					?>
					<h2><?php esc_html_e( 'Your account is now active!', 'gravityformsuserregistration' ); ?></h2>
					<?php
					echo '<p class="lead-in">';
					if ( $signup->domain . $signup->path == '' ) {
						printf(
							/* translators: 1: Opening a tag with login URL, 2: Closing a tag, 3: Username, 4: User email address, 5: Opening a tag with lost password URL. */
							esc_html__( 'Your account has been activated. You may now %1$slog in%2$s to the site using your chosen username of &#8220;%3$s&#8221;. Please check your email inbox at %4$s for your login instructions. If you do not receive an email, please check your junk or spam folder. If you still do not receive an email within an hour, you can %5$sreset your password%2$s.', 'gravityformsuserregistration' ),
							sprintf( '<a href="%s">', network_site_url( 'wp-login.php', 'login' ) ),
							'</a>',
							$signup->user_login,
							$signup->user_email,
							sprintf( '<a href="%s">', wp_lostpassword_url() ),
							'</a>'
						);
					} else {
						printf(
							/* translators: 1: Site link, 2: Username, 3: User email address, 4: Opening a tag with lost password URL, 5: Closing a tag. */
							esc_html__( 'Your site at %1$s is active. You may now log in to your site using your chosen username of &#8220;%2$s&#8221;. Please check your email inbox at %3$s for your login instructions. If you do not receive an email, please check your junk or spam folder. If you still do not receive an email within an hour, you can %4$sreset your password%5$s.', 'gravityformsuserregistration' ),
							sprintf( '<a href="http://%1$s">%2$s</a>', $signup->domain, $signup->domain ),
							$signup->user_login,
							$signup->user_email,
							sprintf( '<a href="%s">', wp_lostpassword_url() ),
							'</a>'
						);
					}
					echo '</p>';
				} else {
					?>
					<h2><?php esc_html_e( 'An error occurred during the activation', 'gravityformsuserregistration' ); ?></h2>
					<p><?php esc_html_e( $result->get_error_message() ); ?></p>
					<?php
				}
			} else {
				extract( $result );
				$url  = is_multisite() ? get_blogaddress_by_id( (int) $blog_id ) : home_url( '', 'http' );
				$user = new WP_User( (int) $user_id );
				?>
				<h2><?php esc_html_e( 'Your account is now active!', 'gravityformsuserregistration' ); ?></h2>

				<div id="signup-welcome">
					<p><span class="h3"><?php esc_html_e( 'Username:', 'gravityformsuserregistration' ); ?></span> <?php echo esc_html( $user->user_login ); ?></p>

					<p>
						<span class="h3"><?php esc_html_e( 'Password:', 'gravityformsuserregistration' ); ?></span>
						<?php
						if ( $result['password_hash'] ) {
							esc_html_e( 'Set at registration.', 'gravityformsuserregistration' );
						} elseif ( ! empty( $user->user_activation_key ) ) {
							esc_html_e( 'Check your email for the set password link.', 'gravityformsuserregistration' );
						} else {
							printf( '<a href="%s">%s</a>', esc_url( gf_user_registration()->get_set_password_url( $user ) ), esc_html__( 'Set your password.', 'gravityformsuserregistration' ) );
						}
						?>
					</p>
				</div>

				<?php if ( $url != network_home_url( '', 'http' ) ) : ?>
					<p class="view">
					<?php
						printf(
							/* translators: 1: Opening a tag with site URL, 2: Closing a tag, 3: Opening a tag with login URL. */
							esc_html__( 'Your account is now activated. %1$sView your site%2$s or %3$sLog in%2$s', 'gravityformsuserregistration' ),
							sprintf( '<a href="%s">', $url ),
							'</a>',
							sprintf( '<a href="%s">', $url . 'wp-login.php' )
						);
					?>
					</p>
				<?php else: ?>
					<p class="view">
					<?php
						printf(
							/* translators: 1: Opening a tag with login URL, 2: Closing a tag, 3: Opening a tag with home URL. */
							esc_html__( 'Your account is now activated. %1$sLog in%2$s or go back to the %3$shomepage%2$s.', 'gravityformsuserregistration' ),
							sprintf( '<a href="%s">', network_site_url( 'wp-login.php', 'login' ) ),
							'</a>',
							sprintf( '<a href="%s">', network_home_url() )
						);
					?>
					</p>
				<?php endif;
			}
		}
		?>
	</div>
	<script type="text/javascript">
		var key_input = document.getElementById('key');
		key_input && key_input.focus();
	</script>
<?php get_footer(); ?>