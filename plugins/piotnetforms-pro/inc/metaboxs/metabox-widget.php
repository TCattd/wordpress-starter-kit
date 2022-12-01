<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function piotnetforms_editor_meta_box() {
	add_meta_box( 'piotnetforms-editor-meta-box', 'Piotnet Forms', 'piotnetforms_editor_meta_box_output', 'piotnetforms' );
}
add_action( 'add_meta_boxes', 'piotnetforms_editor_meta_box', 10 );

function piotnetforms_editor_meta_box_output( $post ) {
	require_once __DIR__ . '/../class/piotnetforms-editor.php';
	require_once __DIR__ . '/../widgets/section.php';
	require_once __DIR__ . '/../widgets/spacer.php';

	$post_meta_data = get_post_meta( $post->ID, '_piotnetforms_data', true );
	$data           = json_decode( $post_meta_data, true );
	$widget_content = $data['content'];

	$editor = new piotnetforms_Editor();

	$widget_object = new piotnetforms_Section();

	$editor->register_widget( $widget_object );

	$widget_object = new piotnetforms_Text();

	$editor->register_widget( $widget_object );

	$widget_object = new Piotnetforms_Form_Builder_Data();

	$editor->register_widget( $widget_object );

	$widget_object = new piotnetforms_Button();

	$editor->register_widget( $widget_object );

	$widget_object = new piotnetforms_Image();

	$editor->register_widget( $widget_object );

	$widget_object = new piotnetforms_Icon();

	$editor->register_widget( $widget_object );

	$widget_object = new piotnetforms_Spacer();

	$editor->register_widget( $widget_object );

	echo '<div class="piotnetforms-editor">';

	echo '<div class="piotnetforms-settings">';
	$editor->editor_panel();
	echo '</div>';

	echo '<div class="piotnet-widget-preview piotnetforms" id="piotnetforms" data-piotnet-widget-preview data-piotnet-sortable>';
	$editor->editor_preview( $widget_content );
	echo '</div>';

	echo '<div class="piotnetforms-editor__bottom">';
	echo '<div class="piotnetforms-editor__save" data-piotnetforms-editor-save><i class="far fa-save"></i> Save</div>';
	echo '</div>';

	echo '</div>';

	// echo '<br>';

	echo "<div><input type='hidden' name='piotnet-widget-post-id' value='{$post->ID}' data-piotnet-widget-post-id></div>";
	echo "<div><input type='hidden' name='piotnet-widget-breakpoint-tablet' value='1025px' data-piotnet-widget-breakpoint-tablet></div>";
	echo "<div><input type='hidden' name='piotnet-widget-breakpoint-mobile' value='768px' data-piotnet-widget-breakpoint-mobile></div>";

	echo "<div><textarea style='display:none' name='piotnetforms-data' data-piotnetforms-data>{$post_meta_data}</textarea></div>";

	// Collape Menu
	// echo "<script>jQuery(document).ready(function( $ ) { $('body').addClass('folded'); })</script>";

	echo '<div data-piotnetforms-ajax-url="' . admin_url( 'admin-ajax.php' ) . '"></div>';
	echo '<div data-piotnetforms-tinymce-upload="' . plugins_url() . '/piotnetforms-pro/inc/tinymce/tinymce-upload.php"></div>';
	echo '<div data-piotnetforms-stripe-key="' . esc_attr( get_option( 'piotnetforms-stripe-publishable-key' ) ) . '"></div>';
	echo '<script src="https://maps.googleapis.com/maps/api/js?key=' . esc_attr( get_option( 'piotnetforms-google-maps-api-key' ) ) . '&libraries=places&callback=piotnetformsAddressAutocompleteInitMap" async defer></script>'; ?>
		<style>
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
}

function piotnetforms_editor_meta_box_save( $post_id ) {
	if ( isset( $_POST['piotnetforms_data'] ) ) {
		$raw_data = stripslashes( $_POST['piotnetforms_data'] );

		$data            = json_decode( $raw_data, true );
		$data['version'] = DATA_VERSION_PIOTNET;
		$data_str        = json_encode( $data );
		update_post_meta( $post_id, '_piotnetforms_data', wp_slash( $data_str ) );
	}

	if ( isset( $_POST['piotnet-widgets-css'] ) ) {
		$widgets_css      = $_POST['piotnet-widgets-css'];
		$revision_version = intval( get_post_meta( $post_id, '_piotnet-revision-version', true ) ) + 1;
		update_post_meta( $post_id, '_piotnet-revision-version', $revision_version );

		$upload     = wp_upload_dir();
		$upload_dir = $upload['basedir'];
		$upload_dir = $upload_dir . '/piotnetforms/css/';

		$file = fopen( $upload_dir . $post_id . '.css', 'wb' );
		fwrite( $file, stripslashes( $widgets_css ) );
		fclose( $file );
	}
}
add_action( 'save_post', 'piotnetforms_editor_meta_box_save' );

add_action( 'admin_head', 'piotnetforms_css' );

function piotnetforms_css() {
	// TODO load css from file
	echo '<style data-piotnet-widget-css-head>' . get_post_meta( get_the_ID(), '_piotnet-widgets-css', true ) . '</style>';

	$post_id = get_the_ID();

	if ( $post_id != false ) {
		$widget_settings = get_post_meta( $post_id, '_piotnetforms-settings', true );

		if ( ! empty( $widget_settings ) ) {
			$widget_settings = json_decode( $widget_settings, true );
			if ( ! empty( $widget_settings['fonts'] ) ) {
				$fonts = $widget_settings['fonts'];
				foreach ( $fonts as $font ) {
					echo '<link href="' . $font . '" rel="stylesheet">';
				}
			}
		}
	}
}
