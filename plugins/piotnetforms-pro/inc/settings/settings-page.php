<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! empty( $_GET['action'] ) && $_GET['action'] == 'active_license' && ! empty( $_GET['siteKey'] ) && ! empty( $_GET['licenseKey'] ) ) {
	if ( isset( $_GET['nonce'] ) && wp_verify_nonce( $_GET['nonce'], 'active_nonce' ) ) {
		Piotnetforms_License_Service::set_key( $_GET['siteKey'], $_GET['licenseKey'] );
		$share_data = isset( $_GET['shareData'] ) && $_GET['shareData'] == 'yes';
		Piotnetforms_License_Service::set_share_data( $share_data ? 'yes' : 'no' );
		Piotnetforms_License_Service::clean_get_info_cache();
	}
	echo '<meta http-equiv="refresh" content="0; url=' . get_admin_url( null, 'admin.php?page=piotnetforms' ) . '" />';
	return;
}

$has_key = Piotnetforms_License_Service::has_key();
$message = '';

if ( isset( $_POST['action'] ) && $_POST['action'] == 'remove_license' ) {
	if ( $has_key ) {
		$res = Piotnetforms_License_Service::remove_license();
		if ( isset( $res['data'] ) && isset( $res['data']['status'] ) && $res['data']['status'] == 'S' ) {
			$message = 'Deactivate license successfully.';
		}
	}
	Piotnetforms_License_Service::clear_license_data();
	Piotnetforms_License_Service::clear_key();
	$has_key = false;
}

$license_data = Piotnetforms_License_Service::get_license_data( true );

if ( isset( $license_data ) && isset( $license_data['error'] ) ) {
	$license_error = $license_data['error'];
	$res_msg = isset( $license_error['message'] ) ? $license_error['message'] : 'Unknown message';
	$res_code = isset( $license_error['code'] ) ? $license_error['code'] : '9999';
	$message = "$res_msg [$res_code]";
}

$license_data = Piotnetforms_License_Service::get_license_data();
$has_valid_license = Piotnetforms_License_Service::has_valid_license();

function piotnetforms_constantcontact_get_token( $code, $redirect_uri, $api_key, $app_secret ) {
	$curl = curl_init();
	curl_setopt_array( $curl, [
	CURLOPT_URL => 'https://authz.constantcontact.com/oauth2/default/v1/token?code='.$code.'&redirect_uri='.urlencode( $redirect_uri ).'&grant_type=authorization_code',
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => '',
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 0,
	CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => 'POST',
	CURLOPT_HTTPHEADER => [
		'Content-Type: application/x-www-form-urlencoded',
		'Authorization: Basic '.base64_encode( $api_key.':'.$app_secret )
	],
	] );
	$response = curl_exec( $curl );
	curl_close( $curl );
	return json_decode( $response );
}

if ( isset( $_GET['post'] ) ) {
	if ( !$has_valid_license ) {
		?>
				<script type="text/javascript">window.location.href = '<?php echo get_admin_url( null, 'edit.php?post_type=piotnetforms&page=piotnetforms&tab=license' ); ?>'</script>
			<?php
	} ?>
		<div class="piotnetforms-builder<?php if ( !empty( get_option( 'piotnetforms_dark_mode' ) ) ) {
			echo ' piotnetforms-builder--dark-mode';
		} ?>">
		<div data-piotnetforms-ajax-url="<?php echo admin_url( 'admin-ajax.php' ); ?>"></div>
		<div data-piotnetforms-tinymce-upload="<?php echo plugins_url() ; ?>/piotnetforms-pro/inc/tinymce/tinymce-upload.php"></div>
	<?php
				if ( current_user_can( 'edit_others_posts' ) ) {
					$post_id = $_GET['post'];
					$piotnetforms_data = get_post_meta( $post_id, '_piotnetforms_data', true );
					$form_id = empty( get_post_meta( $post_id, '_piotnetforms_version', true ) ) && !empty( get_post_meta( $post_id, '_piotnetforms_form_id', true ) ) ? get_post_meta( $post_id, '_piotnetforms_form_id', true ) : $post_id;

					$global_settings = [];
					$single_settings = [];

					$editor = new piotnetforms_Editor();

					$widget_object                              = new Piotnetforms_Form_Global();
					echo $editor->register_widget_info( $widget_object );
					$editor->register_widget( $widget_object );
					echo $editor->register_script( $widget_object );
					$global_settings_structure[$widget_object->get_type()] = [
						'type' => $widget_object->get_type(),
						'fields' => [],
					];

					$widget_object                              = new Piotnetforms_Single_Settings();
					echo $editor->register_widget_info( $widget_object );
					$editor->register_widget( $widget_object );
					echo $editor->register_script( $widget_object );
					$single_settings_structure[$widget_object->get_type()] = [
						'type' => $widget_object->get_type(),
						'fields' => [],
					];

					$widget_object                              = new piotnetforms_Section();
					echo $editor->register_widget_info( $widget_object );
					$editor->register_widget( $widget_object );

					$widget_object                              = new piotnetforms_Column();
					echo $editor->register_widget_info( $widget_object );

					$widget_object                              = new piotnetforms_Text();
					echo $editor->register_widget_info( $widget_object );
					$editor->register_widget( $widget_object );
					echo $editor->register_script( $widget_object );

					$widget_object                              = new Piotnetforms_Field();
					echo $editor->register_widget_info( $widget_object );
					$editor->register_widget( $widget_object );
					echo $editor->register_script( $widget_object );

					// $widget_object                              = new piotnetforms_Social_Icon();
					// echo $editor->register_widget_info( $widget_object );
					// $editor->register_widget( $widget_object );
					// echo $editor->register_script( $widget_object );

					$widget_object                              = new Piotnetforms_Submit();
					echo $editor->register_widget_info( $widget_object );
					$editor->register_widget( $widget_object );
					echo $editor->register_script( $widget_object );

					$widget_object                              = new piotnetforms_Image();
					echo $editor->register_widget_info( $widget_object );
					$editor->register_widget( $widget_object );
					echo $editor->register_script( $widget_object );

					$widget_object                              = new Piotnetforms_Form_Step();
					echo $editor->register_widget_info( $widget_object );
					$editor->register_widget( $widget_object );
					echo $editor->register_script( $widget_object );

					$widget_object                              = new Piotnetforms_Booking();
					echo $editor->register_widget_info( $widget_object );
					$editor->register_widget( $widget_object );
					echo $editor->register_script( $widget_object );

					$widget_object                              = new Piotnetforms_Woocommerce_Checkout();
					echo $editor->register_widget_info( $widget_object );
					$editor->register_widget( $widget_object );

					$widget_object                              = new Piotnetforms_Preview_Submissions();
					echo $editor->register_widget_info( $widget_object );
					$editor->register_widget( $widget_object );
					echo $editor->register_script( $widget_object );

					$widget_object                              = new Piotnetforms_Lost_Password();
					echo $editor->register_widget_info( $widget_object );
					$editor->register_widget( $widget_object );
					echo $editor->register_script( $widget_object );

					$widget_object                              = new Piotnetforms_Form_Builder_Data();
					echo $editor->register_widget_info( $widget_object );
					$editor->register_widget( $widget_object );

					$widget_object                              = new Piotnetforms_Multi_Step_Start();
					echo $editor->register_widget_info( $widget_object );
					$editor->register_widget( $widget_object );
					echo $editor->register_script( $widget_object );

					$widget_object                              = new Piotnetforms_Multi_Step_End();
					echo $editor->register_widget_info( $widget_object );
					$editor->register_widget( $widget_object );
					// echo $editor->register_script( $widget_object );

					$widget_object                              = new Piotnetforms_Multi_Step_Form();
					echo $editor->register_widget_info( $widget_object );
					$editor->register_widget( $widget_object );

					// $widget_object                              = new piotnetforms_Divider();
					// echo $editor->register_widget_info( $widget_object );
					// $editor->register_widget( $widget_object );
					// echo $editor->register_script( $widget_object );

					$widget_object                              = new piotnetforms_Shortcode();
					echo $editor->register_widget_info( $widget_object );
					$editor->register_widget( $widget_object );

					$widget_object                              = new piotnetforms_Button();
					echo $editor->register_widget_info( $widget_object );
					$editor->register_widget( $widget_object );
					echo $editor->register_script( $widget_object );

					$widget_object                              = new piotnetforms_Icon();
					echo $editor->register_widget_info( $widget_object );
					$editor->register_widget( $widget_object );
					echo $editor->register_script( $widget_object );

					$widget_object                              = new piotnetforms_Icon_List();
					echo $editor->register_widget_info( $widget_object );
					$editor->register_widget( $widget_object );
					echo $editor->register_script( $widget_object );

					$widget_object                              = new piotnetforms_Space();
					echo $editor->register_widget_info( $widget_object );
					$editor->register_widget( $widget_object );
					echo $editor->register_script( $widget_object ); ?>
				<div class="piotnetforms-builder__header">
					<div class="piotnetforms-builder__header-left">
						<div class="piotnetforms-builder__header-logo">
							<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../assets/images/piotnet-logo.svg'; ?>">
						</div>
						<div class="piotnetforms-builder__header-left-buttons">
							<div class="piotnetforms-builder__header-button" data-piotnetforms-builder-button="add" data-piotnetforms-editor-widgets-open-button>
								<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../assets/icons/e-add.svg'; ?>">
							</div>
							<div class="piotnetforms-builder__header-button" data-piotnetforms-builder-button="layers" data-piotnetforms-navigator-toggle title="Navigator">
								<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../assets/icons/e-layers.svg'; ?>">
							</div>
							<div class="piotnetforms-builder__header-button piotnetforms-builder__header-button--disabled" data-piotnetforms-builder-button="undo" title="Undo [Experimental]">
								<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../assets/icons/e-undo.svg'; ?>">
							</div>
							<div class="piotnetforms-builder__header-button piotnetforms-builder__header-button--disabled" data-piotnetforms-builder-button="redo" title="Redo [Experimental]">
								<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../assets/icons/e-redo.svg'; ?>">
							</div>
						</div>
					</div>
					<div class="piotnetforms-builder__header-center">
						<div class="piotnetforms-builder__header-title">
							<div class="piotnetforms-builder__header-title-text">
								<?php echo get_the_title( $post_id ); ?>
							</div>
							<div class="piotnetforms-builder__header-title-edit" data-piotnetforms-builder-header-title-edit data-piotnetforms-settings-menu-item="single-settings">
								<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../assets/icons/e-edit.svg'; ?>">
							</div>
						</div>
					</div>
					<div class="piotnetforms-builder__header-right">
						<div class="piotnetforms-builder__header-right-buttons">
							<div class="piotnetforms-builder__header-button active" data-piotnetforms-builder-button="responsive-desktop" data-piotnet-control-responsive="desktop">
								<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../assets/icons/e-desktop.svg'; ?>">
							</div>
							<div class="piotnetforms-builder__header-button" data-piotnetforms-builder-button="responsive-tablet" data-piotnet-control-responsive="tablet">
								<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../assets/icons/e-tablet.svg'; ?>">
							</div>
							<div class="piotnetforms-builder__header-button" data-piotnetforms-builder-button="responsive-mobile" data-piotnet-control-responsive="mobile">
								<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../assets/icons/e-mobile.svg'; ?>">
							</div>
							<div class="piotnetforms-builder__header-button" data-piotnetforms-builder-button="zoom">
								<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../assets/icons/e-zoom.svg'; ?>" class="piotnetforms-builder__header-button-image-zoom-in">
								<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../assets/icons/e-zoom-out.svg'; ?>" class="piotnetforms-builder__header-button-image-zoom-out">
							</div>
							<div class="piotnetforms-editor__save" data-piotnetforms-editor-save>Save</div>
							<a class="piotnetforms-builder__header-button" data-piotnetforms-builder-button="view" href="<?php echo get_permalink( $post_id ); ?>" target="_blank">
								<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../assets/icons/e-view.svg'; ?>">
							</a>
							<div class="piotnetforms-builder__header-button able-active" data-piotnetforms-builder-button="more">
								<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../assets/icons/e-more.svg'; ?>">
								<ul class="piotnetforms-builder__header-button-submenu">
									<li data-piotnetforms-settings-menu-item="single-settings">
										<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../assets/icons/e-settings.svg'; ?>">
										<span>Form Settings</span>
									</li>
									<li data-piotnetforms-settings-menu-item="form-global">
										<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../assets/icons/e-settings-global.svg'; ?>">
										<span>Global Settings</span>
									</li>
									<li>
										<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../assets/icons/e-entries.svg'; ?>">
										<a href="<?php echo esc_url( get_admin_url( null, 'edit.php?post_type=piotnetforms-data&form_id=' ) . $form_id ) ; ?>" target="_blank">Form Entries</a>
									</li>
									<li>
										<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../assets/icons/e-export.svg'; ?>">
										<a href="<?php echo esc_url( get_admin_url( null, 'admin-ajax.php?action=piotnetforms_export&id=' ) . $form_id ) ; ?>">Export Template</a>
									</li>
									<li data-piotnetforms-builder-button="light-mode">
										<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../assets/icons/e-lightmode.svg'; ?>">
										<span>Light Mode</span>
									</li>
									<li data-piotnetforms-builder-button="dark-mode">
										<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../assets/icons/e-darkmode.svg'; ?>">
										<span>Dark Mode</span>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			<?php

					echo '<div class="piotnetforms-editor">';

					echo '<div class="piotnetforms-settings">';
					$editor->editor_panel();
					echo '</div>';

				// echo '<div class="piotnet-widget-preview piotnetforms" id="piotnetforms" data-piotnet-widget-preview data-piotnet-sortable>';
			// 	$editor->editor_preview( $widgets_settings );
			// echo '</div>';?>
				<div class="piotnetforms-editor__bottom">
					<div class="piotnetforms-editor__collapse">
						<div class="piotnetforms-editor__collapse-close" data-piotnetforms-editor-collapse-button-close>
							<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../assets/icons/e-collapse-close.svg'; ?>">
						</div>
						<div class="piotnetforms-editor__collapse-open" data-piotnetforms-editor-collapse-button-open>
							<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../assets/icons/e-collapse-open.svg'; ?>">
						</div>
					</div>
					<div class="piotnetforms-builder__shortcode">
						<input type="text" readonly="readonly" value="<?php echo '[piotnetforms id=' . $post_id . ']'; ?>" data-piotnet-click-to-copy>
						<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../assets/icons/e-copy.svg'; ?>">
					</div>
				</div>
			<?php
				// echo '<div class="piotnetforms-editor__tools">';
				// 	echo '<div class="piotnetforms-editor__tools-item piotnetforms-editor__device-desktop" data-piotnet-control-responsive="desktop"><img src="' . plugin_dir_url( __FILE__ ) . '../../assets/images/i-desktop.svg"></div>';
				// 	echo '<div class="piotnetforms-editor__tools-item piotnetforms-editor__device-tablet" data-piotnet-control-responsive="tablet"><img src="' . plugin_dir_url( __FILE__ ) . '../../assets/images/i-tablet.svg"></div>';
				// 	echo '<div class="piotnetforms-editor__tools-item piotnetforms-editor__device-mobile" data-piotnet-control-responsive="mobile"><img src="' . plugin_dir_url( __FILE__ ) . '../../assets/images/i-mobile.svg"></div>';

						// 	echo '<div class="piotnetforms-editor__tools-item piotnetforms-editor__view"><a href="' . get_permalink($post_id) . '" target="_blank"><img src="' . plugin_dir_url( __FILE__ ) . '../../assets/images/i-view.svg"></a></div>';
						// 	echo '<div class="piotnetforms-editor__tools-item piotnetforms-editor__tools-item--settings" data-piotnetforms-settings-menu-toggle><img src="' . plugin_dir_url( __FILE__ ) . '../../assets/images/i-global-settings.svg" title="Settings"></div>';
						// 	echo '<div class="piotnetforms-editor__tools-item piotnetforms-editor__wp-dashboard"><a href="' . admin_url( 'edit.php?post_type=piotnetforms' ) . '"><i class="fab fa-wordpress-simple"></i></a></div>';
	//                 echo '<div class="piotnetforms-editor__tools-item piotnetforms-editor__navigator" data-piotnetforms-navigator-toggle><img src="' . plugin_dir_url( __FILE__ ) . '../../assets/images/i-layer.svg" title="Navigator"></div>';
						// echo '</div>';

						// echo '<div class="piotnetforms-editor__save" data-piotnetforms-editor-save><i class="far fa-save"></i><i class="icon-spinner-of-dots"></i> Save</div>';

					echo '</div>';

					// echo '<br>';

					echo "<div class='piotnetforms-navigator piotnetforms-navigator-hidden'>
			    <div class='piotnetforms-navigator-header'>
                    <img src='" . plugin_dir_url( __FILE__ ) . "../../assets/icons/e-arrow-down.svg' class='piotnetforms-navigator-expand-toggle'>
					<div class='piotnetforms-navigator-header-title' style='font-weight: 600;'>Navigator</div>
					<img class='piotnetforms-navigator-close' src='" . plugin_dir_url( __FILE__ ) . "../../assets/icons/e-close.svg'>
				</div>
				<div class='piotnetforms-navigator-widgets'></div>
			</div>";

					echo "<div><input type='hidden' name='piotnet-widget-post-id' value='" . esc_attr( $post_id ) . "' data-piotnet-widget-post-id></div>";
					echo "<div><input type='hidden' name='piotnet-widget-breakpoint-tablet' value='1025px' data-piotnet-widget-breakpoint-tablet></div>";
					echo "<div><input type='hidden' name='piotnet-widget-breakpoint-mobile' value='767px' data-piotnet-widget-breakpoint-mobile></div>";

					$htmlspecialchars_data = htmlspecialchars( $piotnetforms_data, ENT_QUOTES, 'UTF-8' );
					echo "<div><textarea style='display:none' name='piotnetforms-data' data-piotnetforms-data>{$htmlspecialchars_data}</textarea></div>";

					$piotnetforms_global_settings = !empty( get_option( 'piotnetforms_global_settings' ) ) ? stripslashes( get_option( 'piotnetforms_global_settings' ) ) : json_encode( $global_settings_structure );
					$htmlspecialchars_global_settings = htmlspecialchars( $piotnetforms_global_settings, ENT_QUOTES, 'UTF-8' );

					if ( !empty( $htmlspecialchars_global_settings ) ) {
						$htmlspecialchars_global_settings_array = json_decode( $piotnetforms_global_settings, true );
						foreach ( $htmlspecialchars_global_settings_array as $key => $value ) {
							if ( !isset( $global_settings_structure[$key] ) ) {
								$htmlspecialchars_global_settings_array[$key] = $global_settings_structure[$key];
							}
						}
					}

					$htmlspecialchars_global_settings = json_encode( $htmlspecialchars_global_settings_array );

					echo "<div><textarea style='display:none' name='piotnetforms-global-style' data-piotnetforms-global-style>{$htmlspecialchars_global_settings}</textarea></div>";

					$piotnetforms_single_settings = !empty( get_post_meta( $post_id, '_piotnetforms_single_settings', true ) ) ? stripslashes( get_post_meta( $post_id, '_piotnetforms_single_settings', true ) ) : json_encode( $single_settings_structure );
					$htmlspecialchars_single_settings = htmlspecialchars( $piotnetforms_single_settings, ENT_QUOTES, 'UTF-8' );

					if ( !empty( $htmlspecialchars_single_settings ) ) {
						$htmlspecialchars_single_settings_array = json_decode( $piotnetforms_single_settings, true );
						foreach ( $htmlspecialchars_single_settings_array as $key => $value ) {
							if ( !isset( $single_settings_structure[$key] ) ) {
								$htmlspecialchars_single_settings_array[$key] = $single_settings_structure[$key];
							}
						}
					}

					$htmlspecialchars_single_settings = json_encode( $htmlspecialchars_single_settings_array );

					echo "<div><textarea style='display:none' name='piotnetforms-single-settings' data-piotnetforms-single-settings>{$htmlspecialchars_single_settings}</textarea></div>";

					// Collape Menu
					// echo "<script>jQuery(document).ready(function( $ ) { $('body').addClass('folded'); })</script>";
					$form_title = ! empty( get_the_title( $post_id ) ) ? get_the_title( $post_id ) : ( 'Piotnet Forms #' . $post_id );
					echo '<div data-piotnetforms-form-title="' . $form_title . '"></div>';
					echo '<div data-piotnetforms-form-id="' . $form_id . '"></div>';
					echo '<div data-piotnetforms-ajax-url="' . admin_url( 'admin-ajax.php' ) . '"></div>';
					echo '<div data-piotnetforms-tinymce-upload="' . plugins_url() . '/piotnetforms-pro/inc/tinymce/tinymce-upload.php"></div>';
					echo '<div data-piotnetforms-stripe-key="' . esc_attr( get_option( 'piotnetforms-stripe-publishable-key' ) ) . '"></div>';

					$dynamic_tags = [
						'post' => [
							'text' => 'Post',
							'submenu' => [
								'post_title' => [
									'text' => 'Post Title',
									'tag' => '{{post_title | length:0}}',
								],
								'post_url' => [
									'text' => 'Post URL',
									'tag' => '{{post_url}}',
								],
								'post_content' => [
									'text' => 'Post Content',
									'tag' => '{{post_content | length:0}}',
								],
								'post_excerpt' => [
									'text' => 'Post Excerpt',
									'tag' => '{{post_excerpt | length:50}}',
								],
								'post_time' => [
									'text' => 'Post Time',
									'tag' => '{{post_time | format:F j, Y}}',
								],
								'post_modified_time' => [
									'text' => 'Post Modified Time',
									'tag' => '{{post_modified_time | format:F j, Y}}',
								],
								'post_comments_number' => [
									'text' => 'Post Comments Number',
									'tag' => '{{post_comments_number}}',
								],
								'post_terms' => [
									'text' => 'Post Terms',
									'tag' => '{{post_terms | taxonomy:tags | separator:, | link:true}}',
								],
								'post_id' => [
									'text' => 'Post ID',
									'tag' => '{{post_id}}',
								],
								'post_featured_image' => [
									'text' => 'Post Featured Image',
									'tag' => '{{post_featured_image | size:full}}',
								],
								'post_titles' => [
									'text' => 'Post Titles of Post Type',
									'tag' => '{{shortcode | shortcode:[piotnetforms_get_posts post_type=post value=title]}}',
								],
								'post_ids' => [
									'text' => 'Post IDs of Post Type',
									'tag' => '{{shortcode | shortcode:[piotnetforms_get_posts post_type=post value=id]}}',
								],
							],
						],
						'custom_field' => [
							'text' => 'Custom Field',
							'submenu' => [
								'post_custom_field' => [
									'text' => 'Post Custom Field',
									'tag' => '{{post_custom_field | name:your_field_name}}',
								],
								'acf_field' => [
									'text' => 'ACF Field',
									'tag' => '{{acf_field | name:your_field_name}}',
								],
								'metabox_field' => [
									'text' => 'Metabox Field',
									'tag' => '{{metabox_field | name:your_field_name}}',
								],
								'pods_field' => [
									'text' => 'Pods Field',
									'tag' => '{{pods_field | name:your_field_name}}',
								],
								'toolset_field' => [
									'text' => 'Toolset Field',
									'tag' => '{{toolset_field | name:your_field_name}}',
								],
								'jetengine_field' => [
									'text' => 'JetEngine Field',
									'tag' => '{{jetengine_field | name:your_field_name}}',
								],
							],
						],
						'author_info' => [
							'text' => 'Author Info',
							'submenu' => [
								'author_info_display_name' => [
									'text' => 'Author Display Name',
									'tag' => '{{author_info | meta:display_name}}',
								],
								'author_info_nicename' => [
									'text' => 'Author Nice Name',
									'tag' => '{{author_info | meta:user_nicename}}',
								],
								'author_info_email' => [
									'text' => 'Author Email',
									'tag' => '{{author_info | meta:user_email}}',
								],
								'author_info_description' => [
									'text' => 'Author Bio',
									'tag' => '{{author_info | meta:description}}',
								],
								'author_info_meta' => [
									'text' => 'Author Meta',
									'tag' => '{{author_info | meta:user_meta}}',
								],
							],
						],
						'user_info' => [
							'text' => 'User Info',
							'submenu' => [
								'user_id' => [
									'text' => 'User ID',
									'tag' => '{{user_info | meta:ID}}',
								],
								'user_login' => [
									'text' => 'User Login',
									'tag' => '{{user_info | meta:user_login}}',
								],
								'user_nicename' => [
									'text' => 'User Nicename',
									'tag' => '{{user_info | meta:user_nicename}}',
								],
								'user_email' => [
									'text' => 'User Email',
									'tag' => '{{user_info | meta:user_email}}',
								],
								'user_url' => [
									'text' => 'User Url',
									'tag' => '{{user_info | meta:user_url}}',
								],
								'user_registered' => [
									'text' => 'User Registered',
									'tag' => '{{user_info | meta:user_registered}}',
								],
								'user_url' => [
									'text' => 'User Url',
									'tag' => '{{user_info | meta:user_url}}',
								],
								'user_status' => [
									'text' => 'User Status',
									'tag' => '{{user_info | meta:user_status}}',
								],
								'display_name' => [
									'text' => 'User Display Name',
									'tag' => '{{user_info | meta:display_name}}',
								],
								'custom_user_meta' => [
									'text' => 'Custom User Meta',
									'tag' => '{{user_info | meta:custom_user_meta}}',
								],
							],
						],
						'wc' => [
							'text' => 'Woocommerce',
							'submenu' => [
								'wc_product_title' => [
									'text' => 'Product Title',
									'tag' => '{{wc_product_title}}',
								],
								'wc_product_price' => [
									'text' => 'Product Price',
									'submenu' => [
										'wc_product_price_full' => [
											'text' => 'Full Price',
											'tag' => '{{wc_product_price | format:full}}',
										],
										'wc_product_price_original' => [
											'text' => 'Original Price',
											'tag' => '{{wc_product_price | format:original}}',
										],
										'wc_product_price_sale' => [
											'text' => 'Sale Price',
											'tag' => '{{wc_product_price | format:sale}}',
										],
									],
								],
								'wc_product_discount_percentage' => [
									'text' => 'Product Discount Percentage',
									'tag' => '{{wc_product_discount_percentage}}',
								],
								'wc_product_rating' => [
									'text' => 'Product Rating',
									'tag' => '{{wc_product_rating}}',
								],
								'wc_product_short_description' => [
									'text' => 'Product Short Description',
									'tag' => '{{wc_product_short_description | length:0}}',
								],
								'wc_product_terms' => [
									'text' => 'Product Terms',
									'submenu' => [
										'wc_product_terms_categories' => [
											'text' => 'Product Categories',
											'tag' => '{{post_terms | taxonomy:product_cat | separator:, | link:true}}',
										],
										'wc_product_terms_tags' => [
											'text' => 'Product Tags',
											'tag' => '{{post_terms | taxonomy:product_tag | separator:, | link:true}}',
										],
									],
								],
								'wc_category_thumbnail' => [
									'text' => 'Category Thumbnail',
									'tag' => '{{wc_category_thumbnail}}',
								],
							],
						],
						'request' => [
							'text' => 'URL Parameter',
							'tag' => '{{request | parameter:utm_source}}',
						],
						'current_date_time' => [
							'text' => 'Current Date Time',
							'tag' => '{{current_date_time | date_format:Y-m-d H:i:s}}',
						],
						'shortcode' => [
							'text' => 'Shortcode',
							'tag' => '{{shortcode | shortcode:[your_shortcode]}}',
						],
						'remote_ip' => [
							'text' => 'Remote IP',
							'tag' => '{{remote_ip}}',
						],
						'archive' => [
							'text' => 'Archive',
							'submenu' => [
								'archive_title' => [
									'text' => 'Archive Title',
									'tag' => '{{archive_title}}',
								],
								'archive_description' => [
									'text' => 'Archive Description',
									'tag' => '{{archive_description | length:0}}',
								],
								'archive_meta' => [
									'text' => 'Archive Meta',
									'tag' => '{{archive_meta | term_id:term_id | meta_key:meta_key}}',
								],
							],
						],
						'term' => [
							'text' => 'Term',
							'submenu' => [
								'term_id' => [
									'text' => 'Term ID',
									'tag' => '{{term_id}}',
								],
								'term_name' => [
									'text' => 'Term Name',
									'tag' => '{{term_name}}',
								],
								'term_description' => [
									'text' => 'Term Description',
									'tag' => '{{term_description}}',
								],
								'term_url' => [
									'text' => 'Term URL',
									'tag' => '{{term_url}}',
								],
								'term_count' => [
									'text' => 'Term Count',
									'tag' => '{{term_count}}',
								],
								'term_color' => [
									'text' => 'Term Color',
									'tag' => '{{term_color}}',
								],
								'term_image' => [
									'text' => 'Term Image',
									'tag' => '{{term_image}}',
								],
								'term_meta' => [
									'text' => 'Term Meta',
									'tag' => '{{term_meta | meta_key:meta_key}}',
								],
							],
						],
						'css_variables' => [
							'text' => 'CSS Variables',
							'tag' => 'var(--your-variable)',
						],
					]; ?>
			<ul class="piotnet-control-dynamic-tags-menu" data-piotnet-control-dynamic-tags-menu>
				<?php foreach ( $dynamic_tags as $key => $tag ) : ?>
					<li class="piotnet-control-dynamic-tags-menu__item"<?php if ( !empty( $tag['tag'] ) ) {
						echo ' data-piotnet-control-dynamic-tag="' . $tag['tag'] . '"';
					} ?>>
						<span class="piotnet-control-dynamic-tags-menu__item-text">
							<?php echo $tag['text']; ?>
						</span>
						<?php if ( !empty( $tag['submenu'] ) ) : ?>
							<ul class="piotnet-control-dynamic-tags-submenu">
								<?php foreach ( $tag['submenu'] as $tag_submenu_name => $tag_submenu_item ) : ?>
									<li class="piotnet-control-dynamic-tags-menu__item"<?php if ( !empty( $tag_submenu_item['tag'] ) ) {
										echo ' data-piotnet-control-dynamic-tag="' . $tag_submenu_item['tag'] . '"';
									} ?>>
										<span class="piotnet-control-dynamic-tags-menu__item-text">
											<?php echo $tag_submenu_item['text']; ?>
										</span>
									</li>
									<?php if ( !empty( $tag_submenu_item['submenu'] ) ) : ?>
										<ul class="piotnet-control-dynamic-tags-submenu">
											<?php foreach ( $tag_submenu_item['submenu'] as $tag_submenu_name_2 => $tag_submenu_item_2 ) : ?>
												<li class="piotnet-control-dynamic-tags-menu__item"<?php if ( !empty( $tag_submenu_item_2['tag'] ) ) {
													echo ' data-piotnet-control-dynamic-tag="' . $tag_submenu_item_2['tag'] . '"';
												} ?>>
													<span class="piotnet-control-dynamic-tags-menu__item-text">
														<?php echo $tag_submenu_item_2['text']; ?>
													</span>
												</li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>

			<style>
				/*html.wp-toolbar {
				    padding-top: 0;
				    box-sizing: border-box;
				}

				html, body {
					height: 100%;
					overflow: hidden;
				}

				#wpadminbar {
					display: none;
				}*/

				#post-body {
					display: flex;
					flex-wrap: wrap;
					margin-right: 0 !important;
				}

				#postbox-container-1 {
					float:right !important;
					margin-right: 0 !important;
					order: 3;
					width: 100% !important;
				}

				#side-sortables {
					width: 100% !important;
				}
			</style>
			<?php

																													$controls_manager = new Controls_Manager_Piotnetforms();
					$controls_manager->render();

					echo $this->tab_widget_template();
					echo $this->output_template();
					echo $this->division_output_template();

					$post_url = get_permalink( $post_id );

					if ( is_ssl() ) {
						$post_url = str_replace( 'http://', 'https://', $post_url );
					}

					$request_parameter = ( strpos( $post_url, '?' ) !== false ) ? '&' : '?';

					echo '<div class="piotnetforms-preview">';
					echo '<div class="piotnetforms-preview__inner" data-piotnetforms-preview-inner>';
					echo '<iframe class="piotnetforms-preview__iframe" data-piotnetforms-preview-iframe="' . esc_url( $post_url . $request_parameter ). 'action=piotnetforms"></iframe>';
					echo '</div>';
					echo '</div>';
					echo '<div class="piotnetforms-editor__loading active" data-piotnetforms-editor-loading><div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div></div>';
					echo '</div>';
				}
} else {
	if ( current_user_can( 'manage_options' ) ) :
		?>
	
	<div class="piotnetforms-dashboard piotnetforms-dashboard--templates">
		<div class="piotnetforms-header">
			<div class="piotnetforms-header__left">
				<div class="piotnetforms-header__logo">
					<img src="<?php echo plugin_dir_url( __FILE__ ) . '../../assets/images/piotnet.svg'; ?>" alt="">
				</div>
				<h2 class="piotnetforms-header__headline"><?php esc_html_e( 'Piotnet Forms Settings', 'piotnetforms' ); ?></h2>
			</div>
			<div class="piotnetforms-header__right">
					<a class="piotnetforms-header__button piotnetforms-header__button--gradient" href="https://piotnetforms.com/?wpam_id=1" target="_blank">
					<?php echo __( 'Go to Piotnet Forms', 'piotnetforms' ); ?>
					</a>
			</div>
		</div>
		<div class="piotnetforms-dashboard__sidebar">
			<div class="piotnetforms-dashboard__category">
				<?php
					$templates_categories = [
						'license' => __( 'License', 'piotnetforms' ),
						'general' => __( 'General', 'piotnetforms' ),
						'integration' => __( 'Integration', 'piotnetforms' ),
						'about' => __( 'About', 'piotnetforms' ),
					];

		$tab = !empty( $_GET['tab'] ) ? $_GET['tab'] : 'license';

		foreach ( $templates_categories as $key => $templates_category ) :
			?>
					<div class="piotnetforms-dashboard__category-item<?php if ( $key == $tab ) {
						echo ' active';
					} ?>" data-piotnetforms-dashboard-category='<?php echo $key; ?>'><?php echo $templates_category; ?></div>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="piotnetforms-dashboard__content">
            <div class="piotnetforms-dashboard__item<?php if ( $tab == 'license' ) {
            	echo ' active';
            } ?>" data-piotnetforms-dashboard-item-category="license">
                <?php require_once 'settings-license.php'; ?>
			</div>

            <div class="piotnetforms-dashboard__item<?php if ( $tab == 'general' ) {
            	echo ' active';
            } ?>" data-piotnetforms-dashboard-item-category="general">
                <div class="piotnetforms-dashboard__title"><?php echo __( 'General', 'piotnetforms' ); ?></div>
                <div class="piotnetforms-dashboard__item-content">
                    <form method="post" action="options.php">
                        <?php settings_fields( 'piotnetforms-general-group' ); ?>
                        <?php do_settings_sections( 'piotnetforms-general-group' ); ?>
                        <?php
            				$disable_form_css = esc_attr( get_option( 'piotnetforms-disable-form-css' ) );
		$disable_form_preview = esc_attr( get_option( 'piotnetforms_disable_form_preview' ) );
		$disable_ssl_verify_license = esc_attr( get_option( 'piotnetforms_disable_ssl_verify_license' ) );
		$beta_version = esc_attr( get_option( 'piotnetforms_beta_version' ) ); ?>
                        <table class="form-table" style="margin-top: -20px;">
                            <tr valign="top">
                                <th scope="row"><?php esc_html_e( 'Disable Form CSS', 'piotnetforms' ); ?></th>
                                <td><input type="checkbox" name="piotnetforms-disable-form-css" value="1" <?php checked( $disable_form_css, 1 ); ?>/></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><?php _e( 'Preview Piotnet Forms Visibility', 'piotnetforms' ); ?></th>
                                <td style="display: flex;">
                                	<span>
                                		<input type="checkbox" name="piotnetforms_disable_form_preview" value="true" <?php if ( $disable_form_preview == 'true' ) {
                                			echo 'checked';
                                		}; ?>/>
                            		</span>
                        			<span>
                        				Only the Logged in account has permission to access Preview Piotnet Forms and discourage search engines from indexing Piotnet Forms post type
                        			</span>
                        		</td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><?php _e( 'Disable verify SSL when validate License', 'piotnetforms' ); ?></th>
                                <td><input type="checkbox" name="piotnetforms_disable_ssl_verify_license" value="true" <?php if ( $disable_ssl_verify_license == 'true' ) {
                                	echo 'checked';
                                }; ?>/>Only use it when you have trouble with validating license (SSL certificate problem)</td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><?php _e( 'Subscribe to Beta updates', 'piotnetforms' ); ?></th>
                                <td><input type="checkbox" name="piotnetforms_beta_version" value="yes" <?php if ( $beta_version == 'yes' ) {
                                	echo 'checked';
                                }; ?>/></td>
                            </tr>
                        </table>
                        <?php submit_button( __( 'Save Settings', 'piotnetforms' ) ); ?>
                    </form>
                </div>
            </div>

            <div class="piotnetforms-dashboard__item<?php if ( $tab == 'integration' ) {
            	echo ' active';
            } ?>" data-piotnetforms-dashboard-item-category="integration">
                <?php require_once 'settings-integration.php'; ?>
            </div>

            <div class="piotnetforms-dashboard__item<?php if ( $tab == 'about' ) {
            	echo ' active';
            } ?>" data-piotnetforms-dashboard-item-category="about">
                <div class="piotnetforms-dashboard__title"><?php echo __( 'About', 'piotnetforms' ); ?></div>
                <div class="piotnetforms-dashboard__item-content">
                    <h3><?php _e( 'Document', 'piotnetforms' ); ?></h3>
                    <a href="https://piotnetforms.com/documents-version-2/?wpam_id=1" target="_blank">https://piotnetforms.com/documents-version-2/</a>
                    <h3><?php _e( 'Support', 'piotnetforms' ); ?></h3>
                    <a href="mailto:piotnetforms-support@piotnet.com">piotnetforms-support@piotnet.com</a>
                    <h3><?php _e( 'Reviews', 'piotnetforms' ); ?></h3>
                    <a href="https://wordpress.org/plugins/piotnetforms/#reviews" target="_blank">https://wordpress.org/plugins/piotnetforms/#reviews</a>
                </div>
            </div>
		</div>
	</div>
	<?php endif; ?>
<?php
} ?>
