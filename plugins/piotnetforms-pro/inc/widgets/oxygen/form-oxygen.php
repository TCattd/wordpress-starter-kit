<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PiotnetformsOxygen extends OxyEl {
	public $css_added = false;

	public function init() {
		$this->El->useAJAXControls();
	}

	public function name() {
		return __( 'Piotnet Forms', 'piotnetforms' );
	}

	public function slug() {
		return 'piotnetforms_oxygen';
	}

	public function button_place() {
		return 'piotnetforms::basic';
	}

	public function button_priority() {
		return '';
	}

	public function isBuilderEditorActive() {
		if ( isset( $_GET['oxygen_iframe'] ) || defined( 'OXY_ELEMENTS_API_AJAX' ) ) {
			return true;
		}

		return false;
	}

	public function icon() {
		return plugin_dir_url( __FILE__ ) . '../../../assets/icons/i-form-oxygen.svg';
	}

	public function controls() {
		$forms = [];

		global $wpdb;

		$forms_posts = $wpdb->get_results( "SELECT ID, post_title FROM {$wpdb->prefix}posts WHERE post_type = 'piotnetforms'", ARRAY_A );

		foreach ( $forms_posts as $forms_post ) {
			$forms[$forms_post['ID']] = $forms_post['post_title'];
		}

		$form_id = $this->addOptionControl(
			[
				'name' 			=> __( 'Select a Form', 'piotnetforms' ),
				'slug' 			=> 'form_id',
				'type' 			=> 'dropdown',
				'value' 		=> $forms,
				'default' 		=> '',
				'css'			=> false
			]
		);

		$form_id->rebuildElementOnChange();
	}

	public function render( $options, $defaults, $content ) {
		$form_id = !empty( $options['form_id'] ) ? $options['form_id'] : '';
		$editor_attr = !empty( $_GET['post_id'] ) ? ' editor=true' : '';
		$shortcode = '[piotnetforms id=' . $form_id . ']';
		if ( ! empty( $form_id ) ) {
			echo '<div>';
			echo do_shortcode( $shortcode );
			echo '</div>';
		}
	}
}

new PiotnetformsOxygen();
