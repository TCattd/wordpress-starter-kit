<?php
	function piotnetforms_shortcode_in_post_meta_box() {
		add_meta_box( 'piotnetforms-shortcode-in-post-meta-box', 'Piotnetforms Shortcode in this Post/Page', 'piotnetforms_shortcode_in_post_meta_box_output', ['post', 'page'] );
	}
	add_action( 'add_meta_boxes', 'piotnetforms_shortcode_in_post_meta_box' );

	function piotnetforms_shortcode_in_post_meta_box_output( $post ) {

		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'piotnetform_inner_custom_box', 'piotnetform_inner_custom_box_nonce' );

		$piotnetforms_shortcode_in_post = get_post_meta( $post->ID, '_piotnetforms_shortcode_in_post', true ); ?>
			<table class="form-table">
		        <tr valign="top">
			        <td><?php _e( 'Enter each of Piotnetforms Shortcode in this Post/Page, separated by pipe char ("|").', 'piotnetforms' ); ?></td>
			    </tr>
			    <tr valign="top">
		        	<td><input type="text" class="regular-text" id="piotnetforms_shortcode_in_post" name="piotnetforms_shortcode_in_post" value="<?php echo esc_attr( $piotnetforms_shortcode_in_post ); ?>" /></td>
		        </tr>
		    </table>
		<?php
	}

	function piotnetforms_shortcode_in_post_meta_box_save( $post_id ) {

		// Check if our nonce is set.
		if ( ! isset( $_POST['piotnetform_inner_custom_box_nonce'] ) ) {
			return $post_id;
		}

		$nonce = $_POST['piotnetform_inner_custom_box_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'piotnetform_inner_custom_box' ) ) {
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
		}
		if ( isset( $_POST['piotnetforms_shortcode_in_post'] ) ) {
			$piotnetforms_shortcode_in_post = sanitize_text_field( $_POST['piotnetforms_shortcode_in_post'] );
			update_post_meta( $post_id, '_piotnetforms_shortcode_in_post', $piotnetforms_shortcode_in_post );
		} else {
			delete_post_meta( $post_id, '_piotnetforms_shortcode_in_post' );
		}
	}
	add_action( 'save_post', 'piotnetforms_shortcode_in_post_meta_box_save' );
