<?php
add_action( 'wp_ajax_piotnetforms_booking', 'piotnetforms_booking' );
add_action( 'wp_ajax_nopriv_piotnetforms_booking', 'piotnetforms_booking' );

function find_element_recursive_form_booking_piotnetforms( $elements, $form_id ) {
	foreach ( $elements as $element ) {
		if ( $form_id === $element['id'] ) {
			return $element;
		}

		if ( ! empty( $element['elements'] ) ) {
			$element = find_element_recursive( $element['elements'], $form_id );

			if ( $element ) {
				return $element;
			}
		}
	}

	return false;
}

function piotnetforms_booking() {
	$post_id    = $_POST['post_id'];
	$element_id = $_POST['element_id'];
	$date       = $_POST['date'];

	if ( ! empty( $element_id ) && ! empty( $post_id ) ) {
		$data     = json_decode( get_post_meta( $post_id, '_piotnetforms_data', true ), true );
		$widgets  = $data['widgets'];
		$settings = $widgets[ $element_id ]['settings'];

		if (!empty($_POST['multi_step_form_id'])) {
			$settings['piotnetforms_booking_form_id'] = $_POST['multi_step_form_id'];
		}

		require_once __DIR__ . '/templates/template-form-booking.php';

		piotnetforms_template_form_booking( $settings, $element_id, $post_id, $date );
	}

	wp_die();
}
