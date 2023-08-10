<?php
/**
 * Single page view for Conversational Forms
 */

namespace Gravity_Forms\Gravity_Forms_Conversational_Forms\Style_Layers\Layers\Views;

use \GFForms;
use \GFAPI;
use \GFCommon;
use \GFFormDisplay;
use Gravity_Forms\Gravity_Forms_Conversational_Forms\GF_Conversational_Forms;
use \Gravity_Forms\Gravity_Forms_Conversational_Forms\Style_Layers\GFCF_Style_Layers_Provider;

// Cleans up some of WP 404 handling
global $wp_query;
$wp_query->is_404 = false;

// Form ID. In WP 5.5+, form ID is passed in $args. Otherwise, we need to grab it from our filter.
$form_id = empty( $args['form_id'] ) ? apply_filters( 'gform_full_screen_form_id', 0 ) : $args['form_id'];

// Form Data
$form = GFAPI::get_form( $form_id );

require_once( \GFCommon::get_base_path() . '/form_display.php' );
GFFormDisplay::enqueue_form_scripts( $form, false );

$settings  = $form['gf_theme_layers'];
// Two style blocks being output fwiw
$css_props = GF_Conversational_Forms::conversational_style_css_props( $form_id, $form, '#gform-conversational.gform-theme.gform-theme--type-conversational' );
$form_is_submitted = ! empty( $_POST[ 'is_submit_' . $form_id ] );

// "Body" Classes
$classes = array(
	'gform-conversational',
	'gform-conversational--layout-' . esc_attr( $settings['page_layout'] ),
	'gform_wrapper',
	'gform-theme',
	'gform-theme--foundation',
	'gform-theme--framework',
	'gform-theme--orbital',
	'gform-theme--type-conversational',
	$settings['enable_welcome_screen'] && ! $form_is_submitted ? 'gform-conversational--welcome-active' : '',
	$settings['enable_progress_bar'] ? 'gform-conversational--progress-bar-active' : '',
);

$submission_info = isset( GFFormDisplay::$submission[ $form_id ] ) ? GFFormDisplay::$submission[ $form_id ] : false;
$is_valid        = rgar( $submission_info, 'is_valid' ) || rgar( $submission_info, 'is_confirmation' ) || rgpost( 'gform_send_resume_link' ) || rgpost( 'gform_save' );
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<?php wp_head(); ?>
	</head>
	<body <?php body_class( 'page-template-gform-conversational' ); ?>>
		<?php wp_body_open(); ?>
		<div
			id="gform-conversational"
			class="<?php echo implode( ' ', $classes ); ?>"
			data-form-submitted="<?php echo $form_is_submitted && $is_valid ? 'true' : 'false'; ?>"
			data-js="gform-conversational"
		>
			<?php echo $css_props; ?>

			<?php // Background Image & Overlay
			if ( $settings['enable_background_image_settings'] && $settings['background_image'] ) :
				$bgd_overlay_opacity  = isset( $settings['background_image_overlay_brightness'] ) ? 100 - $settings['background_image_overlay_brightness'] : 100;
				?>
				<div class="gform-conversational__background">
					<div class="gform-conversational__background-image" style="background-image: url(<?php esc_attr_e( $settings['background_image'] ); ?>);"></div>
					<div class="gform-conversational__background-overlay" style="opacity: <?php esc_attr_e( $bgd_overlay_opacity ); ?>%;"></div>
				</div>
			<?php endif; ?>

			<main class="gform-conversational__screens">

				<?php // Logo
				if ( $settings['enable_logo'] && $settings['logo'] ) :
					$logo_tag   = $settings['logo_link'] ? 'a' : 'div';
					$logo_class = $settings['logo_link'] ? 'link' : 'no-link';
					?>
                    <header class="gform-conversational__header" data-js="gform-conversational-logo">
						<?php printf(
							'<%s class="gform-conversational__logo-wrap gform-conversational__logo-wrap--%s"%s%s>',
							$logo_tag,
							$logo_class,
							$settings['logo_link'] ? ' href="' . esc_url( $settings['logo_link'] ) . '"' : '',
							$settings['logo_link'] ? ' rel="home"' : ''
						); ?>
                            <img class="gform-conversational__logo" src="<?php esc_attr_e( $settings['logo'] ); ?>" alt="<?php esc_attr_e( get_bloginfo( 'name' ) ); ?>"/>
						<?php printf( '</%s>', $logo_tag ); ?>
                    </header>
				<?php endif; ?>

				<?php // Welcome Screen
				if ( $settings['enable_welcome_screen'] && ! $form_is_submitted ) : ?>
					<div
						class="gform-conversational__screen gform-conversational__screen--welcome active"
						data-js="gform-conversational-welcome-screen"
					>
						<div class="gform-conversational__screen-wrapper">
							<div class="gform-conversational__screen-content">
								<?php if ( $settings['welcome_screen_image'] ) : ?>
									<div class="gform-conversational__welcome-image gform-conversational__welcome--hidden" data-js="gform-welcome-screen-element">
										<img src="<?php esc_attr_e( $settings['welcome_screen_image'] ); ?>" alt="<?php esc_attr_e( $settings['welcome_screen_image_alt_text'] ); ?>" />
									</div>
								<?php endif; ?>

								<?php if ( $settings['welcome_screen_title'] ) : ?>
									<h1 class="gform-conversational__welcome-title gform-conversational__welcome--hidden" data-js="gform-welcome-screen-element">
										<?php echo esc_html( $settings['welcome_screen_title'] ); ?>
									</h1>
								<?php endif; ?>

								<?php if ( $settings['welcome_screen_message'] ) : ?>
									<div class="gform-conversational__welcome-message gform-conversational__welcome--hidden" data-js="gform-welcome-screen-element">
										<p class="gform-conversational__welcome-message-copy">
											<?php echo esc_html( $settings['welcome_screen_message'] ); ?>
										</p>
									</div>
								<?php endif; ?>

								<div class="gform-conversational__welcome-cta gform-conversational__welcome--hidden" data-js="gform-welcome-screen-element">
									<button
										class="gform-conversational__nav-button gform-conversational__nav-button--welcome gform-button gform-theme-button--size-xl active"
										data-js="gform-conversational-nav-begin"
									>
										<span class="gform-conversational__nav-button-text">
											<?php echo esc_html( $settings['welcome_screen_button_text'] ); ?>
										</span>
										<span
											class="gform-conversational__nav-button-icon gform-orbital-icon gform-orbital-icon--arrow-narrow-right"
											title="<?php esc_attr_e( $settings['welcome_screen_button_text'] ); ?>"
											aria-hidden="true"
										></span>
									</button>
								</div>
							</div>
						</div>
					</div>
				<?php endif; ?>

				<?php // Form ?>
				<div class="gform-conversational__screen gform-conversational__screen--form<?php echo $form_is_submitted || ! $settings['enable_welcome_screen'] ? ' active' : ''; ?>" data-js="gform-conversational-form">
					<div class="gform-conversational__screen-wrapper">
						<div class="gform-conversational__screen-content">
							<?php // Form ?>
							<div class="gform-conversational__form-fields">
								<?php gravity_form( $form_id, false, false ); ?>
							</div>
						</div>
					</div>
				</div>

				<?php // Navigation ?>
				<div class="gform-conversational__form-nav" data-js="gform-conversational-nav">
					<div class="gform-conversational__form-nav-wrapper">
						<?php // Progress Bar
						$next_disabled = $form_is_submitted ? 'disabled="disabled"' : '';
						if ( $settings['enable_progress_bar'] ) :
							$field_count        = count( $form['fields'] );
							$progress_bar_width = $form_is_submitted ? '100%' : '0%';

							if ( $form_is_submitted ) {
								$progress_text = '100%';
							} else {
								$progress_text = $settings['progress_bar_progression_type'] === 'proportion' ? '0/' . $field_count : '0%';
							}
							// Translators: %s: The percentage of the form being completed.
							$progress_string         = esc_html__( '%s Completed!', 'gravityformsconversationalforms' );
							$progress_completed_text = sprintf( $progress_string, esc_html( $progress_text ) );
							?>
							<div
								class="gform-conversational__progress-bar"
								aria-hidden="true"
								data-js="gform-conversational-progress-bar"
								data-js-progression-type="<?php esc_attr_e( $settings['progress_bar_progression_type'] ); ?>"
								data-js-progression-count="<?php esc_attr_e( $field_count ); ?>"
							>
                                <span
                                    class="gform-conversational__progress-bar-label"
                                    data-js="gform-conversational-progress-bar-label"
                                    data-label="<?php echo $progress_string; ?>"
                                >
                                    <?php echo $progress_completed_text; ?>
                                </span>
								<div class="gform-conversational__progress-bar-outer">
                                    <span
                                        class="gform-conversational__progress-bar-inner"
                                        data-js="gform-conversational-progress-bar-inner"
                                        style="width:<?php echo $progress_bar_width; ?>;"
                                    ></span>
								</div>
							</div>
						<?php endif; ?>

						<div class="gform-conversational__nav-buttons" data-js="gform-conversational-nav-buttons">
							<button
								type="button"
								class="gform-conversational__nav-button gform-conversational__nav-button--prev gform-button gform-theme-button--size-xs active"
								data-js="gform-conversational-nav-prev"
								disabled="disabled"
							>
                                <span class="gform-conversational__nav-button-text">
                                    <?php echo esc_html__( 'Previous', 'gravityformsconversationalforms' ); ?>
                                </span>
								<span
									class="gform-conversational__nav-button-icon gform-orbital-icon gform-orbital-icon--arrow-sm-left"
									title="<?php esc_attr_e( 'Previous', 'gravityformsconversationalforms' ); ?>"
									aria-hidden="true"
								></span>
							</button>

							<button
								type="button"
								class="gform-conversational__nav-button gform-conversational__nav-button--next gform-button gform-theme-button--size-xs active"
								data-js="gform-conversational-nav-next"
								<?php echo esc_attr( $next_disabled ); ?>
							>
                                <span class="gform-conversational__nav-button-text">
                                    <?php echo esc_html__( 'Next', 'gravityformsconversationalforms' ); ?>
                                </span>
								<span
									class="gform-conversational__nav-button-icon gform-orbital-icon gform-orbital-icon--arrow-sm-right"
									title="<?php esc_attr_e( 'Next', 'gravityformsconversationalforms' ); ?>"
									aria-hidden="true"
								></span>
							</button>
						</div>
					</div>
				</div>
			</main>
		</div>
		<?php wp_footer(); ?>
	</body>
</html>
