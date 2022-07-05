<?php

/*
Description: Enables editing of labels from Profile Builder.
*/


/* function that enqueues the necessary scripts */
function wppb_le_scripts_and_styles( $hook ) {
	if( $hook == 'profile-builder_page_pb-labels-edit' ) {
		wp_enqueue_script( 'pble_init', plugin_dir_url( __FILE__ ) . 'assets/js/init.js', array( 'jquery' ) );
		wp_enqueue_script( 'pble_chosen', plugin_dir_url( __FILE__ ) . 'assets/chosen/chosen.jquery.min.js', array( 'jquery' ) );
		wp_enqueue_style( 'pble_chosen_css', plugin_dir_url( __FILE__ ) . 'assets/chosen/chosen.css' );
		wp_enqueue_style( 'pble_css', plugin_dir_url( __FILE__ ) . 'assets/css/style.css' );
	}
}
add_action( 'admin_enqueue_scripts', 'wppb_le_scripts_and_styles' );

/* load required files */
require_once 'potx.php';
require_once 'inc/class-pble-import.php';
require_once 'inc/class-pble-export.php';
//require_once 'assets/lib/wck-api/wordpress-creation-kit.php';

/* scan labels on plugin activate if not already scanned */
function wppb_le_scan_on_plugin_activate() {
	$pble_check = get_option( 'pble_backup', 'not_set' );

	if( empty( $pble_check ) || $pble_check === 'not_set' ) {
		// use of output buffer to fix "headers already sent" notice on plugin activation
		ob_start();
        wppb_le_scan_labels( wp_create_nonce( 'wppb_rescan_labels' ) );
		$output = ob_get_clean();
	}
}
register_activation_hook( __FILE__, 'wppb_le_scan_on_plugin_activate' );

/* scan pble labels */
function wppb_le_scan_labels( $nonce ) {

	if( !wp_verify_nonce( $nonce, 'wppb_rescan_labels' ) )
		return;

	// create directory iterator
	$ite = new RecursiveDirectoryIterator( WPPB_PLUGIN_DIR );

	// array with files to get strings from
	$pb_files_to_get = apply_filters( 'pb_files_to_get',
		array(
			'functions.php',
			'login.php',
			'recover.php',
			'register.php',
			'logout.php',
			'class-formbuilder.php',
			'edit-profile.php',
			'admin-approval.php',
			'email-confirmation.php',
			'userlisting.php',
			'email.php',
			'username.php',
			'password-repeat.php',
            'profile-builder.catalog.php'
		)
	);

	global $wppb_strings;
	$wppb_strings = array();

	// loop through directory and get _e() and __() function calls
	foreach( new RecursiveIteratorIterator( $ite ) as $filename => $current_file ) {
		// http://php.net/manual/en/class.splfileinfo.php
		if( isset( $current_file ) ) {
			$current_file_pathinfo = pathinfo( $current_file );
			if( isset( $current_file_pathinfo['extension'] ) ) {
				if( ! empty( $current_file_pathinfo['extension'] ) && $current_file_pathinfo['extension'] == "php" ) {
					if( in_array( basename( $current_file ), $pb_files_to_get ) ) {
						if( file_exists( $current_file ) ) {
							_wppb_le_potx_process_file( realpath( $current_file ), 0, '_wppb_le_output_str2' );
						}
					}
				}
			}
		}
	}

	update_option( 'pble_backup', $wppb_strings );

}

// populate array with Profile Builder labels
function _wppb_le_output_str2( $str ) {
	global $wppb_strings;
	if( is_array( $wppb_strings ) && ! in_array( $str, $wppb_strings ) ) {
		$wppb_strings[] = $str;
	}
}

/* scan pble labels on Rescan button click */
function wppb_le_rescan() {
	
	if( isset( $_POST['rescan'] ) && isset( $_POST['wppb_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['wppb_nonce'] ), 'wppb_rescan_labels' ) ) {
        wppb_le_scan_labels( sanitize_text_field( $_POST['wppb_nonce'] ) );
	}

}
add_action( 'init', 'wppb_le_rescan' );

/* rescan success message */
function wppb_le_rescan_success_message() {
	if( isset( $_POST['rescan'] ) && isset( $_POST['wppb_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['wppb_nonce'] ), 'wppb_rescan_labels' ) ) {
		global $wppb_strings;
		$wppb_strings_count = count( $wppb_strings );

		$rescan_message = '<div id="message" class="updated"><p>' . $wppb_strings_count . __(' labels scanned.', 'profile-builder') . '</p></div>';
		echo wp_kses_post( $rescan_message );
	}
}
add_action( 'admin_notices', 'wppb_le_rescan_success_message' );

/*
 * change text strings
 *
 * @link http://codex.wordpress.org/Plugin_API/Filter_Reference/gettext
 */
function wppb_le_text_strings( $translated_text, $text, $domain ) {
	if( is_admin() )
		return $translated_text;

	$edited_labels = get_option( 'pble' );

	if( empty( $edited_labels ) || $edited_labels === 'not_set' ) {
		return $translated_text;
	}

	if( is_array( $edited_labels ) && ! empty( $edited_labels ) ) {
		foreach( $edited_labels as $inner_array ) {
			if( $text === $inner_array['pble-label'] || $text === htmlentities($inner_array['pble-label']) ) {
				$translated_text = wp_kses_post( $inner_array['pble-newlabel'] );
			}
		}
	}

	return $translated_text;
}
add_filter( 'gettext', 'wppb_le_text_strings', 8, 3 );

function wppb_le_ngettext_strings( $translated_text, $single, $plural, $number, $domain ){
	if( is_admin() )
		return $translated_text;

    if( $domain != 'profile-builder' )
        return $translated_text;

    $edited_labels = get_option( 'pble' );

    if( empty( $edited_labels ) || $edited_labels === 'not_set' ) {
        return $translated_text;
    }

    if( is_array( $edited_labels ) && ! empty( $edited_labels ) ) {
        foreach( $edited_labels as $inner_array ) {
            if( $single === $inner_array['pble-label'] ) {
                $translated_text = wp_kses_post( $inner_array['pble-newlabel'] );
            }
            if( $plural === $inner_array['pble-label'] ) {
                $translated_text = wp_kses_post( $inner_array['pble-newlabel'] );
            }
        }
    }

    return $translated_text;
}
add_filter( 'ngettext', 'wppb_le_text_strings', 8, 5 );


function wppb_le_remove_gettext_filter( $screen ) {
	if( is_object( $screen ) && $screen->id == 'profile-builder_page_pb-labels-edit' ) {
		remove_filter( 'gettext', 'wppb_le_text_strings', 8 );
	}
}
add_action( 'current_screen', 'wppb_le_remove_gettext_filter' );

function wppb_le_remove_gettext_filter_from_ajax(){
	remove_filter( 'gettext', 'wppb_le_text_strings', 8 );
}
add_action('wp_ajax_wck_add_formpble', 'wppb_le_remove_gettext_filter_from_ajax');
add_action('wp_ajax_wck_refresh_listpble', 'wppb_le_remove_gettext_filter_from_ajax');
add_action('wp_ajax_wck_refresh_entrypble', 'wppb_le_remove_gettext_filter_from_ajax');


/* PB Labels Edit subpage content function */
function wppb_le_page() {
	// create Labels Edit page
	$args = array(
		'menu_title' 	=> __( 'Labels Edit', 'profile-builder' ),
		'page_title' 	=> __( 'Labels Edit', 'profile-builder' ),
		'menu_slug'		=> 'pb-labels-edit',
		'page_type'		=> 'submenu_page',
		'capability'	=> 'manage_options',
		'priority'		=> 5,
		'parent_slug'	=> 'profile-builder'
	);
	if( class_exists( 'WCK_Page_Creator_PB' ) ) {
		new WCK_Page_Creator_PB( $args );
	}

	// array with Profile Builder strings to edit
	$wppb_strings = get_option( 'pble_backup', array() );
	$pble_labels = $wppb_strings;


	// array with fields for Edit Labels metabox
	$pble_fields = array(
		array( 'type' => 'select', 'slug' => 'pble-label', 'title' => __( 'Label to Edit', 'profile-builder' ), 'default-option' => true, 'values' => $pble_labels, 'options' => $pble_labels, 'description' => 'Here you will see the default label so you can copy it.' ),
		array( 'type' => 'textarea', 'slug' => 'pble-newlabel', 'title' => __( 'New Label', 'profile-builder' ) ),
	);

	// create Edit Labels metabox
	$pble_args = array(
		'metabox_id' 	=> 'pble-id',
		'metabox_title' => __( 'Edit Labels', 'profile-builder' ),
		'post_type' 	=> 'pb-labels-edit',
		'meta_name' 	=> 'pble',
		'meta_array' 	=> $pble_fields,
		'context'		=> 'option'
	);
	if( class_exists( 'Wordpress_Creation_Kit_PB' ) ) {
		new Wordpress_Creation_Kit_PB( $pble_args );
	}
}
add_action( 'init', 'wppb_le_page', 11 );

// add Rescan side meta-box
function wppb_le_side_metabox() {
	add_meta_box(
		'pble-id-side',
		__( 'Rescan Lables', 'profile-builder' ),
		'wppb_le_rescan_button',
		'profile-builder_page_pb-labels-edit',
		'side'
	);
}
add_action( 'add_meta_boxes', 'wppb_le_side_metabox' );

// Rescan side meta-box content
function wppb_le_rescan_button() {
	?>
	<div class="wrap">
		<p>Rescan all Profile Builder labels.</p>

		<form action="" method="post">
            <input type="hidden" name="wppb_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wppb_rescan_labels' ) ); ?>" />
			<input type="submit" class="button-primary" name="rescan" value="Rescan" />
		</form>
	</div>
<?php
}

// add Informations side meta-box
function wppb_le_info_side_metabox() {
	add_meta_box(
		'pble-id-side-info',
		__( 'Informations', 'profile-builder' ),
		'wppb_le_info',
		'profile-builder_page_pb-labels-edit',
		'side'
	);
}
add_action( 'add_meta_boxes', 'wppb_le_info_side_metabox' );

// Informations side meta-box content
function wppb_le_info() {
	?>
	<div class="wrap">
		<p><b>Variables:</b></p>
		<ul>
			<li>%1$s</li>
			<li>%2$s</li>
			<li>%s</li>
			<li>etc.</li>
		</ul>
		<p><b>Place them like in the default string!</b></p>
		<p>Example:</p>
		<p>
			<b>Old Label:</b><br>in %1$d sec, click %2$s.%3$s<br>
			<b>New Label:</b><br>click %2$s.%3$s in %1$d sec<br>
		</p>
		<a href="http://www.cozmoslabs.com/?p=40126" target="_blank">Read more detailed informations</a>
	</div>
<?php
}

// add Import and Export side meta-box
function wppb_le_impexp_metabox() {
	add_meta_box(
		'pble-id-side-impexp',
		__( 'Import and Export Labels', 'profile-builder' ),
		'wppb_le_impexp_content',
		'profile-builder_page_pb-labels-edit',
		'side'
	);
}
add_action( 'add_meta_boxes', 'wppb_le_impexp_metabox' );

// Import and Export side meta-box content
function wppb_le_impexp_content() {
	// call import function
    wppb_le_import();
	?>
	<p>
		<?php esc_html_e( 'Import Labels from a .json file.', 'profile-builder' ); ?>
		<br>
		<?php esc_html_e( 'Easily import the labels from another site.', 'profile-builder' ); ?>
	</p>
	<form name="pble-upload" method="post" action="" enctype= "multipart/form-data">

		<input type="hidden" name="wppb_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wppb_import_labels' ) ); ?>" />

		<div class="wrap">
			<input type="file" name="pble-upload" value="pble-upload" id="pble-upload" />
		</div>
		<div class="wrap">
			<input class="button-primary" type="submit" name="pble-import" value=<?php esc_html_e( 'Import', 'profile-builder' ); ?> id="pble-import" onclick="return confirm( '<?php esc_html_e( 'This will overwrite all your old edited labels!\nAre you sure you want to continue?', 'profile-builder' ); ?>' )" />
		</div>
	</form>
	<hr>
	<p>
		<?php esc_html_e( 'Export Labels as a .json file.', 'profile-builder' ); ?>
		<br>
		<?php esc_html_e( 'Easily import the labels into another site.', 'profile-builder' ); ?>
	</p>
	<div class="wrap">
		<form action="" method="post">
			<input type="hidden" name="wppb_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wppb_export_labels' ) ); ?>" />
			<input class="button-primary" type="submit" name="pble-export" value=<?php esc_html_e( 'Export', 'profile-builder' ); ?> id="pble-export" />
		</form>
	</div>
<?php
}

/* function that check for already edited labels */
function wppb_le_check_for_errors( $message, $fields, $required_fields, $meta_name, $posted_values, $post_id ) {
	if ( $meta_name == 'pble' ) {
		/* todo: broken check for added fields so you can't edit same label twice - fix it for future version
		$pble_posted_labels = get_option( $meta_name, 'not_set' );
		$posted_labels = array();


		if( ! empty( $pble_posted_labels ) ) {
			foreach( $pble_posted_labels as $label ) {
					$posted_labels[] = $label['pble-label'];
			}

			if( ( in_array( $posted_values['pble-label'], $posted_labels ) ) ) {
				$message = __( "This label is already edited!", 'profile-builder' );
			}
		}
		*/

		if( $posted_values['pble-label'] == '' ) {
			$message = __( "You must select a label to edit!", 'profile-builder' );
		}
	}
	return $message;
}
add_filter( 'wck_extra_message', 'wppb_le_check_for_errors', 10, 6 );

/* function that change table header */
function wppb_le_header( $list_header ){
	$delete_all_nonce = wp_create_nonce( 'pble-delete-all-entries' );

	return '<thead><tr><th class="wck-number">#</th><th class="wck-content">'. __( 'Labels', 'profile-builder' ) .'</th><th class="wck-edit">'. __( 'Edit', 'profile-builder' ) .'</th><th class="wck-delete"><a id="wppb-delete-all-fields" class="wppb-delete-all-fields" onclick="wppb_le_delete_all_fields(event, this.id, \'' . esc_js($delete_all_nonce) . '\')" title="' . __('Delete all', 'profile-builder') . '" href="#">'. __( 'Delete all', 'profile-builder' ) .'</a></th></tr></thead>';
}
add_action( 'wck_metabox_content_header_pble', 'wppb_le_header' );

/* function that delete all edited labels */
add_action("wp_ajax_pble_delete_all_fields", 'wppb_le_delete_all_fields_callback' );
function wppb_le_delete_all_fields_callback(){
	check_ajax_referer( "pble-delete-all-entries" );

	if( ! empty( $_POST['meta'] ) )
		$meta_name = sanitize_text_field( $_POST['meta'] );
	else
		$meta_name = '';

	if( $meta_name == 'pble' ) {
		delete_option( 'pble' );
	}
	exit;
}

/* function that calls chosen after refresh */
function wppb_le_chosen_pble( $id ) {
	echo "<script type=\"text/javascript\">wppb_le_chosen(); wppb_le_description( jQuery( '.update_container_pble .mb-select' ) ); </script>";
}
add_action( "wck_ajax_add_form_pble", "wppb_le_chosen_pble" );
add_action( "wck_after_adding_form_pble", "wppb_le_chosen_pble" );

/* import class arguments and call */
function wppb_le_import() {
	if( isset( $_POST['pble-import'] ) && isset( $_POST['wppb_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['wppb_nonce'] ), 'wppb_import_labels' ) ) {
		if( isset( $_FILES['pble-upload'] ) ) {
			$pble_args = array(
				'pble'
			);

			$pble_json_upload = new WPPB_LE_Import( $pble_args );
			$pble_json_upload->upload_json_file();
			/* show error/success messages */
			$pble_messages = $pble_json_upload->get_messages();
			foreach ( $pble_messages as $pble_message ) {
				echo '<div id="message" class='. esc_attr( $pble_message['type'] ) .'><p>'. $pble_message['message'] .'</p></div>';  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}
	}
}

/* export class arguments and call */
add_action( 'admin_init', 'wppb_le_export' );
function wppb_le_export() {
	if( isset( $_POST['pble-export'] ) && isset( $_POST['wppb_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['wppb_nonce'] ), 'wppb_export_labels' ) ) {
		$check_export = get_option( 'pble', 'not_set' );
		if( empty( $check_export ) || $check_export === 'not_set' ) {
			echo '<div id="message" class="error"><p>' . esc_html__('No labels edited, nothing to export!', 'profile-builder') . '</p></div>';
		} else {
			$pble_args = array(
				'pble'
			);

			$pble_prefix = 'PBLE_';
			$pble_json_export = new WPPB_LE_Export( $pble_args );
			$pble_json_export->download_to_json_format( $pble_prefix );
		}
	}
}