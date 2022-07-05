<?php

/* include import class */
require_once 'inc/class-pbie-import.php';

/* Import tab content function */
function wppb_pbie_import() {

	if( isset( $_POST['cozmos-import'] ) && isset( $_POST['wppb_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['wppb_nonce'] ), 'wppb_import_setttings' ) ) {
		if( isset( $_FILES['cozmos-upload'] ) ) {
			$pbie_cpts = array(
				'wppb-ul-cpt',
				'wppb-rf-cpt',
				'wppb-epf-cpt'
			);

			$pbie_json_upload = new WPPB_ImpEx_Import( $pbie_cpts );
			$pbie_json_upload->upload_json_file();
			/* show error/success messages */
			$pbie_messages = $pbie_json_upload->get_messages();
			foreach ( $pbie_messages as $pbie_message ) {
				echo '<div id="message" class=';
					echo esc_attr( $pbie_message['type'] );
				echo '>';
				echo '<p>';
					echo esc_html( $pbie_message['message'] );
				echo '</p>';
				echo '</div>';
			}
		}
	}
	?>
	
	<p><?php esc_html_e( 'Import Profile Builder options from a .json file. This allows you to easily import the configuration from another site. ', 'profile-builder' ); ?></p>
	<form name="cozmos-upload" method="post" action="" enctype= "multipart/form-data">
		<input type="hidden" name="wppb_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wppb_import_setttings' ) ); ?>" />

		<div class="wrap">
			<input type="file" name="cozmos-upload" value="cozmos-upload" id="cozmos-upload" />
		</div>
		<div class="wrap">
			<input class="button-secondary" type="submit" name="cozmos-import" value=<?php esc_html_e( 'Import', 'profile-builder' ); ?> id="cozmos-import" onclick="return confirm( '<?php esc_html_e( 'This will overwrite your old PB settings! Are you sure you want to continue?', 'profile-builder' ); ?>' )" />
		</div>
	</form>
<?php
}