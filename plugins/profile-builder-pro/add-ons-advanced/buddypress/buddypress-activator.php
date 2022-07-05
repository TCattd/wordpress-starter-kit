<?php

/**
 * The code that runs during plugin activation.
 */

if( !function_exists( 'wppb_in_bdp_set_default_settings_on_activation' ) ){

    function wppb_in_bdp_set_default_settings_on_activation( $addon ) {

        if( $addon == 'buddypress' ){

            $wppb_buddypress_settings = get_option( 'wppb_buddypress_settings', 'not_found' );

            // set default values
            if ( $wppb_buddypress_settings == 'not_found' ) {

                // Add a Customized Userlisting that uses bp_visibility attribute.
                $ul_post_id = wp_insert_post( array( 'post_title' => 'Default Userlisting for BuddyPress', 'post_status' => 'publish', 'post_author' => get_current_user_id(), 'post_type' => 'wppb-ul-cpt', 'post_content' => 'Default Userlisting for BuddyPress integration' ), true );

                update_post_meta( $ul_post_id, 'wppb-ul-templates', '{{{extra_search_all_fields}}} <br>
{{{bp_my_friends_tab}}}
<span class="wppb-buddypress-sort-by-text">Sort by:</span>
<ul class="wppb-buddypress-sort-by">
  <li> {{{sort_bp_last_active}}} </li>
  <li> {{{sort_display_name}}} </li>
  <li> {{{sort_registration_date}}} </li>
</ul>
<table class="wppb-table wppb-buddypress-table">
	<tbody>
		{{#users}}
		<tr>
          <td data-label="Avatar" class="wppb-buddypress-avatar"><a href="{{{more_info_url}}}">
            <img src="{{{bp_avatar}}}" class="avatar" width="50" height="50" alt="Profile picture of {{meta_display_name}}">
          </a></td>
		  <td data-label="Name" class="wppb-name"><a href="{{{more_info_url}}}">{{meta_display_name}}</a>{{{bp_latest_update}}}<br>
            <i>{{bp_last_active}}</i></td>
		  <td data-label="Friends" class="wppb-buddypress-friends">{{{bp_add_friend}}}</td>
		</tr>
		{{/users}}
	</tbody>
</table>
{{{pagination}}}' );

                update_post_meta( $ul_post_id, 'wppb-single-ul-templates', '
<ul class="wppb-profile">
  <li>
    <h3>' . __( 'Name', 'profile-builder' ) . '</h3>
  </li>
  <li>
    <label>' . __( 'Username:', 'profile-builder' ) . '</label>
    <span>{{meta_user_name}}</span>
  </li>
  <li bp_visibility="first_name" user_id="{{user_id}}">
    <label>' . __( 'First Name:', 'profile-builder' ) . '</label>
    <span>{{meta_first_name}}</span>
  </li>
  <li bp_visibility="last_name" user_id="{{user_id}}">
    <label>' . __( 'Last Name:', 'profile-builder' ) . '</label>
    <span>{{meta_last_name}}</span>
  </li>
  <li bp_visibility="nickname" user_id="{{user_id}}">
    <label>' . __( 'Nickname:', 'profile-builder' ) . '</label>
    <span>{{meta_nickname}}</span>
  </li>
  <li>
    <label>' . __( 'Display name:', 'profile-builder' ) . '</label>
	<span>{{meta_display_name}}</span>
  </li>
  <li bp_visibility="website" user_id="{{user_id}}">
    <h3>' . __( 'Contact Info', 'profile-builder' ) . '</h3>
  </li>
  <li bp_visibility="website" user_id="{{user_id}}">
  	<label>' . __( 'Website:', 'profile-builder' ) . '</label>
	<span>{{meta_website}}</span>
  </li>
  <li bp_visibility="biographical_info" user_id="{{user_id}}">
    <h3>' . __( 'About Yourself', 'profile-builder' ) . '</h3>
  </li>
  <li bp_visibility="biographical_info" user_id="{{user_id}}">
	<label>' . __( 'Biographical Info:', 'profile-builder' ) . '</label>
	<span>{{{meta_biographical_info}}}</span>
  </li>
</ul>' );

                update_post_meta( $ul_post_id, 'wppb_ul_page_settings', array( array(
                    'default-sorting-criteria' => 'wppb_bdp_last_activity',
                    'default-sorting-order' => 'desc',
                    'roles-to-display' => '*',
                    'number-of-userspage' => 20,
                    'avatar-size-all-userlisting' => 40,
                    'avatar-size-single-userlisting' => 60,
                    'visible-only-to-logged-in-users' => '',
                    'visible-to-following-roles' => '*'
                ) ) );

                $registration_form = 'wppb-default-register';
                $edit_profile_form = 'wppb-default-edit-profile';
                $user_listing = 'Default Userlisting for BuddyPress';
                $all_user_listing = 'yes';

                update_option('wppb_buddypress_settings', array(
                    'UserListing' => $user_listing,
                    'AllUserListing' => $all_user_listing,
                    'EditProfileForm' => $edit_profile_form,
                    'RegistrationForm' => $registration_form,
                ));
            }

        }

    }
    add_action( 'wppb_add_ons_activate', 'wppb_in_bdp_set_default_settings_on_activation', 10, 1);

}
