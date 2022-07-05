<?php

namespace GFML\Compatibility\UserRegistration;

use GFSignup;
use SitePress;
use WP_User;

class Hooks {
	/** @var SitePress */
	private $sitepress;

	public function __construct( SitePress $sitepress ) {
		$this->sitepress = $sitepress;
	}

	public function addHooks() {
		add_filter( 'gform_user_registration_signup_meta', [ $this, 'onSubmission' ] );
		add_filter( 'insert_user_meta', [ $this, 'onActivation' ], 10, 3 );
	}

	/**
	 * Save the language the user submitted the form into the entry meta data.
	 *
	 * @param array $meta Entry meta data.
	 * @return array
	 */
	public function onSubmission( $meta ) {
		$meta['icl_admin_language'] = $this->sitepress->get_current_language();
		$meta['icl_admin_locale']   = $this->sitepress->get_locale_from_language_code( $meta['icl_admin_language'] );
		return $meta;
	}

	/**
	 * Set the user locale and preferred language.
	 *
	 * @param array   $meta User meta data.
	 * @param WP_User $user
	 * @param bool    $update
	 * @return array
	 */
	public function onActivation( $meta, WP_User $user, $update ) {
		if ( ! $update && class_exists( 'GFSignup' ) ) {
			$key    = rgpost( 'key' ); // From ajax.
			$key    = $key ?: rgpost( 'item' ); // From form submission.
			$signup = GFSignup::get( $key );
			if ( $signup instanceof GFSignup && $signup->meta['email'] === $user->user_email ) {
				$meta['icl_admin_language'] = $signup->meta['icl_admin_language'];
				$meta['locale']             = $signup->meta['icl_admin_locale'];
			}
		}
		return $meta;
	}
}
