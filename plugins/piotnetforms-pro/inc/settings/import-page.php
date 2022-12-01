<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/../source/import.php';

?>

<div class="piotnetforms-dashboard piotnetforms-dashboard--import">
	<form method="post" enctype="multipart/form-data" action="">
		<?php
		wp_nonce_field( 'import_action', 'import_nonce' );
?>
		<div class="piotnetforms-dashboard__title">Import Form</div>
		<div class="piotnetforms-dashboard__import-form">
			<input type="file" id="json_file" name="json_file">
			<input type="hidden" name="action" value="import_json_file">
			<?php submit_button( __( 'Import Now', 'piotnetforms' ) ); ?>
		</div>
		<?php
	if ( isset( $_POST['action'] ) ) {
		$arrContextOptions=[
			'ssl'=>[
				'verify_peer'=>false,
				'verify_peer_name'=>false,
			],
		];

		$import_action = $_POST['action'];
		if ( 'import_json_file' === $import_action && isset( $_POST['import_nonce'] ) && wp_verify_nonce( $_POST['import_nonce'], 'import_action' ) ) {
			$file_content = file_get_contents( $_FILES['json_file']['tmp_name'] );
			piotnetforms_import( $file_content );
		} elseif ( 'select_template' === $import_action && isset( $_POST['select_template_nonce'] ) && wp_verify_nonce( $_POST['select_template_nonce'], 'select_template_action' ) && isset( $_POST['templates'] ) ) {
			$template     = $_POST['templates'];
			$file_content = file_get_contents( __DIR__ . '/../../assets/forms/templates/' . $template, false, stream_context_create( $arrContextOptions ) );
			piotnetforms_import( $file_content );
		}
	}
?>
	</form>
</div>