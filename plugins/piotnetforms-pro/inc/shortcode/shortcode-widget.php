<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// require all widgets
foreach ( glob( __DIR__ . '/../widgets/*.php' ) as $file ) {
	require_once $file;
}

require_once( __DIR__.'/../dynamic-tags.php' );

function piotnetforms_shortcode( $args, $content ) {
	ob_start();

	$has_valid_license = Piotnetforms_License_Service::has_valid_license();

	if ( $has_valid_license ) {
		if ( ! empty( $args['id'] ) ) {
			$post_id = $args['id'];
			$raw_data = get_post_meta( $post_id, '_piotnetforms_data', true );
			$form_id_custom = !empty( $args['form_id'] ) ? $args['form_id'] : '';
			$form_version = empty( get_post_meta( $post_id, '_piotnetforms_version', true ) ) ? 1 : get_post_meta( $post_id, '_piotnetforms_version', true );
			$form_id = $form_version == 1 ? get_post_meta( $post_id, '_piotnetforms_form_id', true ) : $post_id;

			if ( ! empty( $raw_data ) ) {
				wp_enqueue_script( 'piotnetforms-script' );

				echo '<div id="piotnetforms" class="piotnetforms piotnetforms-' . $post_id . '" data-piotnetforms-shortcode-id="' . esc_attr( $post_id ) . '"';
				if ( is_user_logged_in() && current_user_can( 'edit_others_posts' ) ) {
					echo ' data-piotnetforms-shortcode-title="' . get_the_title( $post_id ) . '" data-piotnetforms-edit-url="' . get_admin_url( null, 'admin.php?page=piotnetforms&post=' ) . $post_id . '"';
				}
				echo '>';
				echo '<form id="' . $form_id . '" class="piotnetforms__form" onsubmit="return false">';
				$data = json_decode( $raw_data, true );
				$widget_content = $data['content'];
				@piotnetforms_render_loop( $widget_content, $post_id, $form_id_custom );

				$upload = wp_upload_dir();
				$upload_dir = $upload['baseurl'];
				$upload_dir = $upload_dir . '/piotnetforms/css/';

				$css_file = $upload_dir . $post_id . '.css';
				echo '<link rel="stylesheet" href="' . $css_file . '?ver=' . get_post_meta( $post_id, '_piotnet-revision-version', true ) . '" media="all">';
				echo '</form>';
				echo '</div>';

				enqueue_footer();

				if ( empty( get_option( 'piotnetforms-disable-form-css' ) ) ) {
					wp_enqueue_style( 'piotnetforms-style' );
					wp_enqueue_style( 'piotnetforms-global-style' );
				} else {
					wp_enqueue_style( 'piotnetforms-less-style' );
				}
			}
		}
	} else {
		echo '<p>Please activate your license to enable all features and receive new updates.</p>';
	}
	return ob_get_clean();
}
add_shortcode( 'piotnetforms', 'piotnetforms_shortcode' );

function piotnetforms_render_loop( $loop, $post_id, $form_id='' ) {
	foreach ( $loop as $widget_item ) {
		if ( empty( $widget_item['class_name'] ) ) {
			continue;
		}

		$widget            = new $widget_item['class_name']();

		if ( !empty( $form_id ) ) {
			if ( isset( $widget_item['settings']['form_id'] ) ) {
				$widget_item['settings']['form_id'] = $form_id;
			}
			if ( isset( $widget_item['settings']['piotnetforms_conditional_logic_form_form_id'] ) ) {
				$widget_item['settings']['piotnetforms_conditional_logic_form_form_id'] = $form_id;
			}
			if ( isset( $widget_item['settings']['piotnetforms_booking_form_id'] ) ) {
				$widget_item['settings']['piotnetforms_booking_form_id'] = $form_id;
			}
			if ( isset( $widget_item['settings']['piotnetforms_woocommerce_checkout_form_id'] ) ) {
				$widget_item['settings']['piotnetforms_woocommerce_checkout_form_id'] = $form_id;
			}
		}

		$widget->settings  = $widget_item['settings'];
		$widget_id         = $widget_item['id'];
		$widget->widget_id = $widget_id;
		$widget->post_id   = $post_id;

		if ( ! empty( $widget_item['fonts'] ) ) {
			$fonts = $widget_item['fonts'];
			if ( ! empty( $fonts ) ) {
				echo '<script>jQuery(document).ready(function( $ ) {';
				foreach ( $fonts as $font ) :
					?>
					$('head').append('<link href="<?php echo $font; ?>" rel="stylesheet">');
					<?php
				endforeach;
				echo '})</script>';
			}
		}

		$widget_type = $widget->get_type();
		if ( $widget_type === 'section' || $widget_type === 'column' ) {
			$visibility = @$widget->widget_visibility();
			if ( $visibility ) {
				echo @$widget->output_wrapper_start( $widget_id );
				if ( isset( $widget_item['elements'] ) ) {
					echo @piotnetforms_render_loop( $widget_item['elements'], $post_id, $form_id );
				}
			}
		} else {
			$output = $widget->output( $widget_id );
			$output = piotnetforms_dynamic_tags( $output );
			echo @$output;
		}

		if ( $widget_type === 'section' || $widget_type === 'column' ) {
			echo @$widget->output_wrapper_end( $widget_id );
		}
	}
}

function enqueue_footer() {
	echo '<div data-piotnetforms-ajax-url="' . admin_url( 'admin-ajax.php' ) . '"></div>';
	echo '<div data-piotnetforms-plugin-url="' . plugins_url() . '"></div>';
	echo '<div data-piotnetforms-tinymce-upload="' . plugins_url() . '/piotnetforms-pro/inc/forms/tinymce/tinymce-upload.php"></div>';
	echo '<div data-piotnetforms-stripe-key="' . esc_attr( get_option( 'piotnetforms-stripe-publishable-key' ) ) . '"></div>';
	echo '<div class="piotnetforms-break-point" data-piotnetforms-break-point-md="1025" data-piotnetforms-break-point-lg="767"></div>'; ?>
		<?php if ( is_user_logged_in() && current_user_can( 'edit_others_posts' ) ) : ?>
			<script type="text/javascript">
				jQuery('[data-piotnetforms-shortcode-title]').each(function(){
					var shortcodeID = jQuery(this).attr('data-piotnetforms-shortcode-id');
					if (jQuery('#wp-admin-bar-piotnetforms-default').find('[data-piotnetforms-edit-template="' + shortcodeID + '"]').length == 0) {
						jQuery('#wp-admin-bar-piotnetforms-default').append('<li data-piotnetforms-edit-template="' + shortcodeID + '"><a class="ab-item" href="' + jQuery(this).attr('data-piotnetforms-edit-url') + '">Edit ' + jQuery(this).attr('data-piotnetforms-shortcode-title') + '</a></li>');
					}
				});
			</script>
		<?php endif; ?>
	<?php
}
