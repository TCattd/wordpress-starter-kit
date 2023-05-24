<?php namespace CheckEmail\Core;

use CheckEmail\Core\UI\Page\Check_Email_Log_List_Page;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

class Check_Email_Admin_Capability_Giver implements Loadie {

	public function load() {
		add_filter( 'user_has_cap', array( $this, 'add_cap_to_admin_cap_list' ), 10, 4 );
	}
        
	public function add_cap_to_admin_cap_list( $allcaps, $caps, $args, $user ) {
		if ( ! in_array( 'administrator', $user->roles ) ) {
			return $allcaps;
		}

		if ( array_key_exists( Check_Email_Log_List_Page::CAPABILITY, $allcaps ) ) {
			return $allcaps;
		}

		$allcaps[ Check_Email_Log_List_Page::CAPABILITY ] = true;

		return $allcaps;
	}

	public function add_cap_to_admin() {
		$admin = get_role( 'administrator' );

		if ( is_null( $admin ) ) {
			return;
		}

		$admin->add_cap( Check_Email_Log_List_Page::CAPABILITY );
	}
}