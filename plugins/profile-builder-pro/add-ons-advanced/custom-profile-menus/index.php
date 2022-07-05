<?php

/*
	Profile Builder - Custom Profile Menus Add-On
	License: GPL2

	== Copyright ==
	Copyright 2015 Cozmoslabs (www.cozmoslabs.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

// include this only in front-end
if( ! is_admin() ) {
	include_once( "wppb-custom-profile-menus.php" );
}

// include custom walker nav menu class
include_once( "class-wppb_cpm_walker_nav_menu.php" );

/* Define plugin directory */
define( 'WPPB_IN_CPM_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __FILE__ ) ) );


/**
 * Function that adds a Profile Builder Custom Menus meta box to the Appearance -> Menus page
 *
 * @since v.1.0.0
 */
function wppb_in_cpm_add_box() {
	add_meta_box(
		'wppb-cpm-box',
		'PB Custom Profile Menus',
		'wppb_in_cpm_nav_menu_box',
		'nav-menus',
		'side',
		'low'
	);
}
add_action( 'admin_head-nav-menus.php', 'wppb_in_cpm_add_box' );

/**
 * Function that adds content to the Profile Builder Custom Menus meta box
 *
 * @since v.1.0.0
 */
function wppb_in_cpm_nav_menu_box() {
	global $nav_menu_selected_id;

	$wppb_cpm_elems = array(
		'wppb_cpm_login_logout' 		=>	__( 'Login', 'profile-builder' ) . '/' . __( 'Logout', 'profile-builder' ),
		'wppb_cpm_logout' 				=>	__( 'Logout', 'profile-builder' ),
		'wppb_cpm_login_iframe' 		=>	__( 'Login - iFrame', 'profile-builder' ),
		'wppb_cpm_edit_profile_iframe' 	=>	__( 'Edit Profile - iFrame', 'profile-builder' ),
		'wppb_cpm_register_iframe' 		=>	__( 'Register - iFrame', 'profile-builder' ),
	);

	class wppbINCPMItems {
		public $ID;
		public $db_id = 0;
		public $object = 'custom';
		public $object_id;
		public $menu_item_parent = 0;
		public $type = 'custom';
		public $title;
		public $url;
		public $target = '';
		public $attr_title = '';
		public $classes = array();
		public $xfn = '';
	}

	$wppb_cpm_elems_obj = array();

	foreach( $wppb_cpm_elems as $value => $title ) {
		$wppb_cpm_elems_obj[$value] 			= new wppbINCPMItems();
		$wppb_cpm_elems_obj[$value]->object_id	= esc_attr( $value );
		$wppb_cpm_elems_obj[$value]->title		= esc_attr( $title );
		$wppb_cpm_elems_obj[$value]->url		= '';
		$wppb_cpm_elems_obj[$value]->object		= esc_attr( $value );
		$wppb_cpm_elems_obj[$value]->type		= esc_attr( $value );
	}

	$walker = new Walker_Nav_Menu_Checklist( array() );

	?>
	<div id="wppb-cpm-links" class="wppb-cpm-links-div">
		<div id="tabs-panel-wppb-cpm-links-all" class="tabs-panel tabs-panel-view-all tabs-panel-active">
			<ul id="wppb-cpm-links-checklist" class="list:wppb-cpm-links categorychecklist form-no-clear">
				<?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $wppb_cpm_elems_obj ), 0, (object) array( 'walker' => $walker ) ); ?>
			</ul>
		</div>

		<p class="button-controls">
			<span class="add-to-menu">
					<input type="submit"<?php disabled( $nav_menu_selected_id, 0 ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu', 'profile-builder' ); ?>" name="add-wppb-cpm-links-menu-item" id="submit-wppb-cpm-links" />
					<span class="spinner"></span>
			</span>
		</p>
	</div>
<?php
}

/**
 * Function that modify the "type_label" in menu
 *
 * @since v.1.0.0
 */
function wppb_in_cpm_nav_menu_type_label( $menu_item ) {
	$wppb_cpm_elems = array( 'wppb_cpm_login_logout', 'wppb_cpm_logout', 'wppb_cpm_login_iframe', 'wppb_cpm_edit_profile_iframe', 'wppb_cpm_register_iframe' );

	if ( isset( $menu_item->object, $menu_item->url ) && in_array( $menu_item->type, $wppb_cpm_elems ) ) {
		$menu_item->type_label = 'PB Custom Profile Menus';
		$menu_item->object = 'custom';
	}

	return $menu_item;
}
add_filter( 'wp_setup_nav_menu_item', 'wppb_in_cpm_nav_menu_type_label' );

/**
 * Function that change the walker
 * @return string
 * @since v.1.0.0
 */
function wppb_in_cpm_change_nav_menu_walker( $walker ) {
    global $wp_version;
    if ( version_compare( $wp_version, "5.4", "<" ) ) {
        $walker = 'WPPB_IN_CPM_Walker_Nav_Menu';
    }

	return $walker;
}
add_filter( 'wp_edit_nav_menu_walker', 'wppb_in_cpm_change_nav_menu_walker', 99 );

/**
 * Function that adds the menu custom fields
 *
 * @since v.1.0.0
 */
function wppb_in_cpm_extra_fields( $item_id, $item, $depth, $args ) {
	global $wp_roles;

	?>
	<input type="hidden" name="wppb-custom-menus" value="<?php echo wp_create_nonce( 'wppb-custom-menus' ); //phpcs:ignore ?>"/>

	<?php
	if( isset( $item->type ) && strstr( $item->type, 'wppb_cpm' ) != '' ) {
		$wppb_cpm_login_label = get_post_meta( $item->ID, 'wppb-cpm-login-label', true );
		$wppb_cpm_logout_label = get_post_meta( $item->ID, 'wppb-cpm-logout-label', true );
		$wppb_cpm_form_page_url = get_post_meta( $item->ID, 'wppb-cpm-form-page-url', true );

		if( $item->type == 'wppb_cpm_login_logout' ) {
			?>
			<script>
				jQuery("label[for='edit-menu-item-title-<?php echo esc_js($item->ID); ?>']").closest('.description.description-wide').css('display', 'none');
			</script>

			<div class="wppb-cpm-field wppb-cpm-loginout-navigation-label">
				<p class="description"><?php esc_html_e( 'Login Label', 'profile-builder' ); ?></p>
				<label class="wppb-cpm-login-nav-label" for="wppb-cpm-login-label-<?php echo esc_attr( $item->ID ); ?>">
					<input type="text" id="wppb-cpm-login-label-<?php echo esc_attr( $item->ID ); ?>"
						   class="widefat code edit-menu-item-label"
						   name="wppb-cpm-login-label-<?php echo esc_attr( $item->ID ); ?>"
						   value="<?php echo( ! empty( $wppb_cpm_login_label ) ? esc_attr( $wppb_cpm_login_label ) : esc_html__( 'Login', 'profile-builder' ) ); ?>">
				</label>

				<p class="description"><?php esc_html_e( 'Logout Label', 'profile-builder' ); ?></p>
				<label class="wppb-cpm-logout-nav-label" for="wppb-cpm-logout-label-<?php echo esc_attr( $item->ID ); ?>">
					<input type="text" id="wppb-cpm-logout-label-<?php echo esc_attr( $item->ID ); ?>"
						   class="widefat code edit-menu-item-label"
						   name="wppb-cpm-logout-label-<?php echo esc_attr( $item->ID ); ?>"
						   value="<?php echo( ! empty( $wppb_cpm_logout_label ) ? esc_attr( $wppb_cpm_logout_label ) : esc_html__( 'Logout', 'profile-builder' ) ); ?>">
				</label>
			</div>
		<?php
		}

		$wppb_cpm_form_page_url_label = __( 'Form page URL', 'profile-builder' );
		switch( $item->type ) {
			case 'wppb_cpm_login_logout' :
				$wppb_cpm_form_page_url_label = __( 'Login page URL', 'profile-builder' );
				break;
			case 'wppb_cpm_login_iframe' :
				$wppb_cpm_form_page_url_label = __( 'Login Form page URL', 'profile-builder' );
				break;
			case 'wppb_cpm_edit_profile_iframe' :
				$wppb_cpm_form_page_url_label = __( 'Edit Profile Form page URL', 'profile-builder' );
				break;
			case 'wppb_cpm_register_iframe' :
				$wppb_cpm_form_page_url_label = __( 'Register Form page URL', 'profile-builder' );
				break;
		}

		if( $item->type != 'wppb_cpm_logout' ) {
			?>
			<div class="wppb-cpm-field wppb-cpm-url">
				<p class="description"><?php echo esc_html( $wppb_cpm_form_page_url_label ); ?></p>

				<label class="wppb-cpm-menu-item-url-label" for="wppb-cpm-menu-url-<?php echo esc_attr( $item->ID ); ?>">
					<input type="text" id="wppb-cpm-menu-url-<?php echo esc_attr( $item->ID ); ?>"
						   class="widefat code edit-menu-item-url" name="wppb-cpm-menu-url-<?php echo esc_attr( $item->ID ); ?>"
						   value="<?php echo( ! empty( $wppb_cpm_form_page_url ) ? esc_attr( $wppb_cpm_form_page_url ) : '' ); ?>">
				</label>
			</div>
		<?php
		}
	}

	$wppb_cpm_lilo = get_post_meta( $item->ID, 'wppb_cpm_lilo', true );

	$display_roles = apply_filters( 'wppb_cpm_roles', $wp_roles->role_names, $item );
	if( ! $display_roles )
		return;

	// by default nothing is checked (will match "everyone" radio)
	$logged_in_out = '';

	// specific roles are saved as an array, so "loggedin" or an array equals "loggedin" is checked
	if( is_array( $wppb_cpm_lilo ) || $wppb_cpm_lilo == 'loggedin' ) {
		$logged_in_out = 'loggedin';
	} else if( $wppb_cpm_lilo == 'loggedout' ) {
		$logged_in_out = 'loggedout';
	}

	// the specific roles to check
	$checked_roles = is_array( $wppb_cpm_lilo ) ? $wppb_cpm_lilo : false;
	?>
	<div class="wppb-cpm-field wppb-cpm_logged_in_out_field description-wide">
		<span class="description"><?php esc_html_e( "Display Mode", 'profile-builder' ); ?></span>

		<input type="hidden" class="wppb-cpm-menu-id" value="<?php echo esc_attr( $item->ID ) ;?>" />

		<div class="wppb-cpm-logged-input-holder wppb-cpm-loggedin">
			<input type="radio" class="wppb-cpm-logged-in-out" name="wppb-cpm-logged-in-out[<?php echo esc_attr( $item->ID ) ;?>]" id="wppb-cpm_logged_in-for-<?php echo esc_attr( $item->ID ) ;?>" <?php checked( 'loggedin', $logged_in_out ); ?> value="loggedin" />
			<label for="wppb-cpm_logged_in-for-<?php echo esc_attr( $item->ID ) ;?>">
				<?php esc_html_e( 'Logged In Users', 'profile-builder'); ?>
			</label>
		</div>

		<div class="wppb-cpm-logged-input-holder wppb-cpm-loggedout">
			<input type="radio" class="wppb-cpm-logged-in-out" name="wppb-cpm-logged-in-out[<?php echo esc_attr( $item->ID ) ;?>]" id="wppb-cpm_logged_out-for-<?php echo esc_attr( $item->ID ) ;?>" <?php checked( 'loggedout', $logged_in_out ); ?> value="loggedout" />
			<label for="wppb-cpm_logged_out-for-<?php echo esc_attr( $item->ID ) ;?>">
				<?php esc_html_e( 'Logged Out Users', 'profile-builder'); ?>
			</label>
		</div>

		<div class="wppb-cpm-logged-input-holder wppb-cpm-everyone">
			<input type="radio" class="wppb-cpm-logged-in-out" name="wppb-cpm-logged-in-out[<?php echo esc_attr( $item->ID ) ;?>]" id="wppb-cpm_by_role-for-<?php echo esc_attr( $item->ID ) ;?>" <?php checked( '', $logged_in_out ); ?> value="" />
			<label for="wppb-cpm_by_role-for-<?php echo esc_attr( $item->ID ) ;?>">
				<?php esc_html_e( 'Everyone', 'profile-builder'); ?>
			</label>
		</div>

	</div>

	<div class="wppb-cpm-field wppb-cpm_role_field description-wide">
		<span class="description"><?php esc_html_e( "Restrict menu item to a minimum role", 'profile-builder' ); ?></span>
		<span class="description" style="margin-bottom: 6px;"><?php esc_html_e( "Works only if Display Mode: Logged In Users is selected", 'profile-builder' ); ?></span>

		<?php
		/* Loop through each of the available roles. */
		foreach ( $display_roles as $role => $name ) {
			/* If the role has been selected, make sure it's checked. */
			$checked = checked( true, ( is_array( $checked_roles ) && in_array( $role, $checked_roles ) ), false );

			?>
			<div class="wppb-cpm-role-input-holder">
				<input type="checkbox" name="wppb-cpm-role[<?php echo esc_attr( $item->ID ) ;?>][<?php echo esc_attr( $role ); ?>]" id="wppb-cpm_role-<?php echo esc_attr( $role ); ?>-for-<?php echo esc_attr( $item->ID ) ;?>" <?php echo $checked; //phpcs:ignore?> value="<?php echo esc_attr( $role ); ?>" />
				<label for="wppb-cpm_role-<?php echo esc_attr( $role ); ?>-for-<?php echo esc_attr( $item->ID ) ;?>">
					<?php echo esc_html( $name ); ?>
				</label>
			</div>
		<?php
		}
		?>
	</div>

	<?php
	if( isset( $item->type ) && strstr( $item->type, 'wppb_cpm' ) != '' && ( $item->type == 'wppb_cpm_login_iframe' || $item->type == 'wppb_cpm_edit_profile_iframe' || $item->type == 'wppb_cpm_register_iframe' ) ) {
		$wppb_cpm_iframe_title = get_post_meta( $item->ID, 'wppb-cpm-iframe-title', true );
		$wppb_cpm_iframe_height = get_post_meta( $item->ID, 'wppb-cpm-iframe-height', true );
		$wppb_cpm_iframe_width = get_post_meta( $item->ID, 'wppb-cpm-iframe-width', true );

		switch( $item->type ) {
			case 'wppb_cpm_login_iframe' :
				$wppb_cpm_iframe_default_title = __( "Login", 'profile-builder' );
				break;
			case 'wppb_cpm_edit_profile_iframe' :
				$wppb_cpm_iframe_default_title = __( "Edit Profile", 'profile-builder' );
				break;
			case 'wppb_cpm_register_iframe' :
				$wppb_cpm_iframe_default_title = __( "Register", 'profile-builder' );
				break;
		}

		?>
		<div class="wppb-cpm-field wppb-cpm-iframe-title">
			<p class="description"><?php esc_html_e( "iFrame Title", 'profile-builder' ); ?></p>

			<label class="wppb-cpm-menu-item-iframe-title" for="wppb-cpm-menu-iframe-title-<?php echo esc_attr( $item->ID ); ?>">
				<input type="text" id="wppb-cpm-menu-iframe-title-<?php echo esc_attr( $item->ID ); ?>"
					   class="widefat code edit-menu-item-iframe-title" name="wppb-cpm-menu-iframe-title-<?php echo esc_attr( $item->ID ); ?>"
					   value="<?php echo( ! empty( $wppb_cpm_iframe_title ) ? esc_attr( $wppb_cpm_iframe_title ) : esc_attr( $wppb_cpm_iframe_default_title ) ); ?>">
			</label>
		</div>

		<div class="wppb-cpm-field wppb-cpm-iframe-size">
			<p class="description"><?php esc_html_e( "iFrame Size", 'profile-builder' ); ?></p>

			<label class="wppb-cpm-menu-item-iframe-height" for="wppb-cpm-menu-iframe-height-<?php echo esc_attr( $item->ID ); ?>">
				<?php esc_html_e( "Height (px)", 'profile-builder' ); ?>
				<input type="text" id="wppb-cpm-menu-iframe-height-<?php echo esc_attr( $item->ID ); ?>"
					   class="widefat code edit-menu-item-iframe-height" name="wppb-cpm-menu-iframe-height-<?php echo esc_attr( $item->ID ); ?>"
					   value="<?php echo( ! empty( $wppb_cpm_iframe_height ) ? esc_attr( $wppb_cpm_iframe_height ) : ( $item->type === 'wppb_cpm_login_iframe' ? 300 : 600 ) ); ?>">
			</label>

			<label class="wppb-cpm-menu-item-iframe-width" for="wppb-cpm-menu-iframe-width-<?php echo esc_attr( $item->ID ); ?>">
				<?php esc_html_e( "Width (px)", 'profile-builder' ); ?>
				<input type="text" id="wppb-cpm-menu-iframe-width-<?php echo esc_attr( $item->ID ); ?>"
					   class="widefat code edit-menu-item-iframe-width" name="wppb-cpm-menu-iframe-width-<?php echo esc_attr( $item->ID ); ?>"
					   value="<?php echo( ! empty( $wppb_cpm_iframe_width ) ? esc_attr( $wppb_cpm_iframe_width ) : 600 ); ?>">
			</label>
		</div>
	<?php
	}
}
add_action( 'wp_nav_menu_item_custom_fields', 'wppb_in_cpm_extra_fields', 10, 4);

/**
 * Function that save the menu values
 *
 * @since v.1.0.0
 */
function wppb_in_cpm_update_menu( $menu_id, $menu_item_db_id ) {
	// verify this came from our screen and with proper authorization.
	if( ! isset( $_POST['wppb-custom-menus'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['wppb-custom-menus'] ), 'wppb-custom-menus' ) ) {
		return;
	}

	if( ! empty( $_POST['wppb-cpm-login-label-' . $menu_item_db_id] ) ) {
		update_post_meta( $menu_item_db_id, 'wppb-cpm-login-label', trim( sanitize_text_field( $_POST['wppb-cpm-login-label-' . $menu_item_db_id] ) ) );
	} else {
		delete_post_meta( $menu_item_db_id, 'wppb-cpm-login-label' );
	}

	if( ! empty( $_POST['wppb-cpm-logout-label-' . $menu_item_db_id] ) ) {
		update_post_meta( $menu_item_db_id, 'wppb-cpm-logout-label', trim( sanitize_text_field( $_POST['wppb-cpm-logout-label-' . $menu_item_db_id] ) ) );
	} else {
		delete_post_meta( $menu_item_db_id, 'wppb-cpm-logout-label' );
	}

	if( ! empty( $_POST['wppb-cpm-menu-url-' . $menu_item_db_id] ) ) {
		update_post_meta( $menu_item_db_id, 'wppb-cpm-form-page-url', trim( sanitize_text_field( $_POST['wppb-cpm-menu-url-' . $menu_item_db_id] ) ) );
	} else {
		delete_post_meta( $menu_item_db_id, 'wppb-cpm-form-page-url' );
	}

	if( ! empty( $_POST['wppb-cpm-menu-iframe-title-' . $menu_item_db_id] ) ) {
		update_post_meta( $menu_item_db_id, 'wppb-cpm-iframe-title', trim( sanitize_text_field( $_POST['wppb-cpm-menu-iframe-title-' . $menu_item_db_id] ) ) );
	} else {
		delete_post_meta( $menu_item_db_id, 'wppb-cpm-iframe-title' );
	}

	if( ! empty( $_POST['wppb-cpm-menu-iframe-height-' . $menu_item_db_id] ) ) {
		update_post_meta( $menu_item_db_id, 'wppb-cpm-iframe-height', trim( sanitize_text_field( $_POST['wppb-cpm-menu-iframe-height-' . $menu_item_db_id] ) ) );
	} else {
		delete_post_meta( $menu_item_db_id, 'wppb-cpm-iframe-height' );
	}

	if( ! empty( $_POST['wppb-cpm-menu-iframe-width-' . $menu_item_db_id] ) ) {
		update_post_meta( $menu_item_db_id, 'wppb-cpm-iframe-width', trim( sanitize_text_field( $_POST['wppb-cpm-menu-iframe-width-' . $menu_item_db_id] ) ) );
	} else {
		delete_post_meta( $menu_item_db_id, 'wppb-cpm-iframe-width' );
	}

	global $wp_roles;

	$allowed_roles = apply_filters( 'wppb_cpm_roles', $wp_roles->role_names );

	$saved_data = false;

	if( isset( $_POST['wppb-cpm-logged-in-out'][$menu_item_db_id] ) && $_POST['wppb-cpm-logged-in-out'][$menu_item_db_id] == 'loggedin' && ! empty ( $_POST['wppb-cpm-role'][$menu_item_db_id] ) ) {
		$custom_roles = array();
		// only save allowed roles
		foreach( $_POST['wppb-cpm-role'][$menu_item_db_id] as $role ) { //phpcs:ignore
			if ( array_key_exists ( $role, $allowed_roles ) ) $custom_roles[] = sanitize_text_field( $role );
		}
		if ( ! empty ( $custom_roles ) ) $saved_data = $custom_roles;
	} else if ( isset( $_POST['wppb-cpm-logged-in-out'][$menu_item_db_id]  )  && in_array( $_POST['wppb-cpm-logged-in-out'][$menu_item_db_id], array( 'loggedin', 'loggedout' ) ) ) {
		$saved_data = sanitize_text_field( $_POST['wppb-cpm-logged-in-out'][$menu_item_db_id] );
	}

	if ( $saved_data ) {
		update_post_meta( $menu_item_db_id, 'wppb_cpm_lilo', $saved_data );
	} else {
		delete_post_meta( $menu_item_db_id, 'wppb_cpm_lilo' );
	}
}
add_action( 'wp_update_nav_menu_item', 'wppb_in_cpm_update_menu', 10, 2 );

/**
 * Function that adds the necessary scripts
 *
 * @since v.1.0.0
 */
function wppb_in_cpm_scripts() {
	if( ! is_customize_preview() ) {
		if( file_exists( WPPB_IN_CPM_PLUGIN_DIR . '/assets/js/wppb_cpm_main.js' ) ) {
			wp_enqueue_script( 'wppb-cpm-script', plugin_dir_url( __FILE__ ) . 'assets/js/wppb_cpm_main.js', array( 'jquery' ), PROFILE_BUILDER_VERSION );

			wp_enqueue_script( 'thickbox' );
			wp_enqueue_style( 'thickbox' );
		}

		if( file_exists( WPPB_IN_CPM_PLUGIN_DIR . '/assets/css/style-frontend.css' ) ) {
			wp_enqueue_style( 'wppb-cpm-style-frontend', plugin_dir_url( __FILE__ ) . 'assets/css/style-frontend.css', '', PROFILE_BUILDER_VERSION );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'wppb_in_cpm_scripts' );

function wppb_in_cpm_scripts_backend( $hook ) {
	if( 'nav-menus.php' != $hook ) {
		return;
	}

	if( file_exists( WPPB_IN_CPM_PLUGIN_DIR . '/assets/css/style-backend.css' ) ) {
		wp_enqueue_style( 'wppb-cpm-style-backend', plugin_dir_url( __FILE__ ) . 'assets/css/style-backend.css', '', PROFILE_BUILDER_VERSION );
	}
}
add_action( 'admin_enqueue_scripts', 'wppb_in_cpm_scripts_backend' );

/**
 * Function used to change page template in iframe
 *
 * @since v.1.0.0
 */
function wppb_in_cpm_change_page_template( $template ) {
	if ( isset( $_GET['wppb_cpm_iframe'] ) && $_GET['wppb_cpm_iframe'] == 'yes' ) {
		show_admin_bar( false );
		return dirname( __FILE__ ) . '/assets/wppb-cpm-template.php';
	} else {
		return $template;
	}
}
add_filter( 'template_include', 'wppb_in_cpm_change_page_template', 99 );

/**
 * Adds value of new field to $item object
 *
 * @since 1.0.0
 */
function wppb_in_cpm_setup_nav_item( $menu_item ) {
	if( ! empty( $menu_item->ID ) ) {
		$roles = get_post_meta( $menu_item->ID, 'wppb_cpm_lilo', true );
	}

	if ( ! empty( $roles ) ) {
		$menu_item->roles = $roles;
	}

	return $menu_item;
}
add_filter( 'wp_setup_nav_menu_item', 'wppb_in_cpm_setup_nav_item' );

/**
 * Function that hides the elements on the frontend
 *
 * @since v.1.0.0
 */
function wppb_in_cpm_hide_menu_elements( $items ) {
	$hide_children_of = array();

	// Iterate over the items to search and destroy
	foreach ( $items as $key => $item ) {
		$visible = true;

		// hide any item that is the child of a hidden item
		if( in_array( $item->menu_item_parent, $hide_children_of ) ){
			$visible = false;
			$hide_children_of[] = $item->ID; // for nested menus
		}

		// check any item that has NMR roles set
		if( $visible && isset( $item->roles ) ) {
			// check all logged in, all logged out, or role
			switch( $item->roles ) {
				case 'loggedin' :
					$visible = is_user_logged_in() ? true : false;
					break;
				case 'loggedout' :
					$visible = ! is_user_logged_in() ? true : false;
					break;
				default:
					$visible = false;
					if ( is_array( $item->roles ) && ! empty( $item->roles ) ) {
						foreach ( $item->roles as $role ) {
							if ( current_user_can( $role ) )
								$visible = true;
						}
					}

					break;
			}

		}

		// add filter to work with plugins that don't use traditional roles
		$visible = apply_filters( 'wppb_cpm_roles_item_visibility', $visible, $item );

		// unset non-visible item
		if ( ! $visible ) {
			$hide_children_of[] = $item->ID; // store ID of item
			unset( $items[$key] ) ;
		}

	}

	return $items;
}
if ( ! is_admin() ) {
	add_filter( 'wp_get_nav_menu_items', 'wppb_in_cpm_hide_menu_elements' );
}

function wppb_in_cpm_arg_to_redirect_url( $redirect_url ) {
    if( !empty( $redirect_url ) )//avoid adding the query arg if we do not have a redirect url
	    $redirect_url = add_query_arg( 'wppb_cpm_redirect', 'yes', $redirect_url );

	return $redirect_url;
}
add_filter( 'wppb_register_redirect', 'wppb_in_cpm_arg_to_redirect_url', 100 );
add_filter( 'wppb_edit_profile_redirect', 'wppb_in_cpm_arg_to_redirect_url', 100 );
add_filter( 'wppb_after_login_redirect_url', 'wppb_in_cpm_arg_to_redirect_url', 100 );
add_filter( 'wppb_after_logout_redirect_url', 'wppb_in_cpm_arg_to_redirect_url', 100 );
