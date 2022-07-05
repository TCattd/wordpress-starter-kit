<?php

class WPPB_ImpEx_Export {

	protected $args_to_export;

	/**
	 * this will take custom options and posttypes that will be exported from database.
	 *
	 * @param array  $args_to_export  custom options and posttypes to export.
	 */
	function __construct( $args_to_export ) {
		$this->args_to_export = $args_to_export;
	}

	/* function to export from database */
	private function export_array( $nonce ) {
		if( !wp_verify_nonce( $nonce, 'wppb_export_settings' ) )
			return array();

		/* export options from database */
		$option_values = array();
		foreach( $this->args_to_export['options'] as $option ) {
		    $get_option = get_option( $option );
		    if( $get_option !== false ) {
                $option_values[$option] = $get_option;
            }
		}

		/* export custom posts from database */
		$all_custom_posts = array();
		foreach( $this->args_to_export['cpts'] as $post_type ) {
			$all_custom_posts[$post_type] = get_posts( "post_type=$post_type&posts_per_page=-1" );
			foreach( $all_custom_posts[$post_type] as $key => $value ) {
				$all_custom_posts[$post_type][$key]->postmeta = get_post_custom( $value->ID );
			}
		}

		/* create and return array for export */
		$all_for_export = array(
			"options" => $option_values,
			"posts" => $all_custom_posts
		);

		return $all_for_export;
	}

	/* export to json file */
	public function download_to_json_format( $prefix ) {

		if( isset( $_POST['cozmos-export'] ) && isset( $_POST['wppb_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['wppb_nonce'] ), 'wppb_export_settings' ) ) {
			
			$all_for_export = $this->export_array( sanitize_text_field( $_POST['wppb_nonce'] ) );
			$json = json_encode( $all_for_export );
			$filename = $prefix . date( 'Y-m-d_h.i.s', time() );
			$filename .= '.json';
			header( "Content-Disposition: attachment; filename=$filename" );
			header( 'Content-type: application/json' );
			header( 'Content-Length: ' . mb_strlen( $json ) );
			header( 'Connection: close' );
			echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			exit;

		}

	}
}
