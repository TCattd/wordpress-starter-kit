<?php
	function piotnetforms_acf_repeater_meta_box() {
		add_meta_box( 'piotnetforms-acf-repeater-meta-box', 'piotnetforms ACF Repeater', 'piotnetforms_acf_repeater_meta_box_output', 'elementor_library' );
	}
	add_action( 'add_meta_boxes', 'piotnetforms_acf_repeater_meta_box' );

	function piotnetforms_acf_repeater_meta_box_output( $post ) {
		$piotnetforms_acf_repeater_name = get_post_meta( $post->ID, '_piotnetforms_acf_repeater_name', true );
		$piotnetforms_acf_repeater_preview_post_id = get_post_meta( $post->ID, '_piotnetforms_acf_repeater_preview_post_id', true );
		?>
			<table class="form-table">
		        <tr valign="top">
		        <th scope="row"><?php _e('Repeater Name:','piotnetforms'); ?></th>
		        <td><input type="text" class="regular-text" id="piotnetforms_acf_repeater_name" name="piotnetforms_acf_repeater_name" value="<?php echo esc_attr( $piotnetforms_acf_repeater_name ); ?>" /></td>
		        </tr>
		        <tr valign="top">
		        <th scope="row"><?php _e('Preview Post ID:','piotnetforms'); ?></th>
		        <td><input type="number" class="regular-text" id="piotnetforms_acf_repeater_preview_post_id" name="piotnetforms_acf_repeater_preview_post_id" value="<?php echo esc_attr( $piotnetforms_acf_repeater_preview_post_id ); ?>" /></td>
		        </tr>
		    </table>
		<?php
	}

	function piotnetforms_acf_repeater_meta_box_save( $post_id ) {
		if (isset($_POST['piotnetforms_acf_repeater_name']) && isset($_POST['piotnetforms_acf_repeater_preview_post_id'])) {
			$piotnetforms_acf_repeater_name = sanitize_text_field( $_POST['piotnetforms_acf_repeater_name'] );
			update_post_meta( $post_id, '_piotnetforms_acf_repeater_name', $piotnetforms_acf_repeater_name );

			$piotnetforms_acf_repeater_preview_post_id = sanitize_text_field( $_POST['piotnetforms_acf_repeater_preview_post_id'] );
			update_post_meta( $post_id, '_piotnetforms_acf_repeater_preview_post_id', $piotnetforms_acf_repeater_preview_post_id );
		}
	}  
	add_action( 'save_post', 'piotnetforms_acf_repeater_meta_box_save' );
?>