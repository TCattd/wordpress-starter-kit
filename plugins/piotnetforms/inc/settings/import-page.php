<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once __DIR__ . '/../source/import.php';

function piotnetforms_import() {
	if ( ! isset( $_POST['import_nonce'] ) || ! wp_verify_nonce( $_POST['import_nonce'], 'import_action' ) ) {
	} else {
		$file_content = file_get_contents( $_FILES['json_file']['tmp_name'] );
		$data         = json_decode( $file_content, true );
		if ( json_last_error() > 0 ) {
			$last_error_msg = json_last_error_msg();
			print_r( "Can't parse file: " . $last_error_msg );
		} elseif ( array_key_exists( 'error', $data ) ) {
			print_r( 'Error: ' . $data['error'] );
		} else {
			$post = [
				'post_title'  => $data['title'],
				'post_status' => 'publish',
				'post_type'   => 'piotnetforms',
			];

			$post_id = wp_insert_post( $post );

			if ( is_wp_error( $post_id ) ) {
				print_r( "Can't insert post: " . $post_id->get_error_message() );
			} else {
				piotnetforms_do_import( $post_id, $data );
				print_r( 'Successfully Imported' );
			}
		}
	}
}

piotnetforms_import();

?>
<form method="post" enctype="multipart/form-data" action="">	
<?php
	wp_nonce_field( 'import_action', 'import_nonce' );
?>
	<h1 style="margin-bottom: 50px;">Import JSON File</h1>
	<input type="file" id="json_file" name="json_file">
	<?php submit_button( __( 'Import', 'piotnetforms' ) ); ?>
</form>
