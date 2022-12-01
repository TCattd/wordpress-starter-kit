<?php
/**
 * API wrapper for the Recaptcha service.
 *
 * @since 1.0
 * @package Gravity_Forms\Gravity_Forms_RECAPTCHA
 */

namespace Gravity_Forms\Gravity_Forms_RECAPTCHA;

/**
 * Class RECAPTCHA_API
 *
 * @package Gravity_Forms\Gravity_Forms_RECAPTCHA
 */
class RECAPTCHA_API {
	/**
	 * Google Recaptcha token verification URL.
	 *
	 * @since 1.0
	 * @var string
	 */
	private $verification_url = 'https://www.google.com/recaptcha/api/siteverify';

	/**
	 * Get the result of token verification from the Recaptcha API.
	 *
	 * @param string $token  The token to verify.
	 * @param string $secret The site's secret key.
	 *
	 * @return array|\WP_Error
	 */
	public function verify_token( $token, $secret ) {
		return wp_remote_post(
			$this->verification_url,
			array(
				'body' => array(
					'secret'   => $secret,
					'response' => $token,
				),
			)
		);
	}
}
