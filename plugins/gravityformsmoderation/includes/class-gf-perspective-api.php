<?php

namespace Gravity_Forms\Gravity_Forms_Moderation;

use WP_Error;

/**
 * Gravity Forms Perspective API Library.
 *
 * @since     1.0
 * @package   GravityForms
 * @author    Rocketgenius
 * @copyright Copyright (c) 2016, Rocketgenius
 */
class GF_Perspective_API {

	protected $api_key;

	protected $api_url;

	private $addon;

	public $result;

	public function __construct( $addon ) {
		$this->addon   = $addon;
		$this->api_key = $this->addon->get_plugin_setting( 'perspective_api_key' );
		$this->api_url = 'https://commentanalyzer.googleapis.com/v1alpha1/comments:analyze?key=';
	}

	/**
	 * Send a request to the Perspective API and handle errors.
	 *
	 * @param array  $args Request arguments
	 * @param string $key  API key
	 *
	 * @return object|WP_Error
	 */
	public function post_request( $args, $key ) {
		if ( '' == $key ) {
			return new WP_Error( 'empty_key', __( 'You have not entered a Perspective API key. Please go to the Moderation settings page and enter your API key.', 'gravityformsmoderation' ) );
		}

		$response = wp_remote_post(
			$this->api_url . $key,
			$args
		);

		$response_code     = wp_remote_retrieve_response_code( $response );
		$response_body_obj = json_decode( wp_remote_retrieve_body( $response ) );

		if ( 200 !== $response_code ) {
			if ( ! empty( $response_body_obj->error->message ) ) {
				$message = $response_body_obj->error->message;
			} else {
				$message = wp_remote_retrieve_response_message( $response );
			}

			return new WP_Error( $response_code, $message );
		}

		return $response_body_obj;
	}

	/**
	 * Run a test API request to make sure the API key is valid.
	 *
	 * @since 1.0
	 *
	 * @param string $locale WordPress locale code.
	 * @param string $key Prespective API key.
	 *
	 * @return object|WP_Error Returs the result of a sample call to the Perspective API.
	 */
	public function check_api_key( $locale, $key ) {
		$args = $this->build_request_args( $locale, 'Sample request to test whether key is valid' );

		return $this->post_request( $args, $key );
	}


	/**
	 * Send text to the API to get the toxicity score
	 *
	 * @since 1.0
	 *
	 * @param string $locale WordPress locale code.
	 * @param string $text The text to analyze.
	 * @param array  $form Form Object.
	 *
	 * @return array|WP_Error
	 */
	public function analyze_text( $locale, $text, $form ) {
		$args     = $this->build_request_args( $locale, $text, $form );
		$analysis = $this->post_request( $args, $this->api_key );

		if ( is_wp_error( $analysis ) ) {
			return $analysis;
		}

		$language_code = $this->get_api_language_code( $locale );
		$attributes    = $this->get_requested_attributes( $form );
		$score         = array();
		if ( is_array( $attributes ) ) {
			foreach ( $attributes as $attribute ) {
				$formatted_attribute = strtoupper( $attribute );

				if ( $this->language_supports_attribute( $language_code, $attribute ) ) {
					$score[ $attribute ] = $analysis->attributeScores->$formatted_attribute->summaryScore->value;
				}
			}
		} else if ( $this->language_supports_attribute( $language_code, 'toxicity' ) ) {
			$score['toxicity'] = $analysis->attributeScores->TOXICITY->summaryScore->value;
		}

		return $score;
	}

	/**
	 * Build the arguments for the API request.
	 *
	 * @since 1.0
	 *
	 * @param string $locale WordPress locale code.
	 * @param string $text The text to analyze.
	 * @param array  $form The current form.
	 *
	 * @return array
	 */
	public function build_request_args( $locale, $text, $form = array() ) {
		$language_code = $this->get_api_language_code( $locale );

		$body = array(
			'comment'             => array( 'text' => $text ),
			'languages'           => $language_code,
			'requestedAttributes' => $this->get_requested_attributes_for_api_request( $form ),
			'doNotStore'          => true,
		);

		return array(
			'headers' => array(
				'Content-Type' => 'application/json',
			),
			'body'    => wp_json_encode( $body, 16 ), // 16 forces it to use JSON_FORCE_OBJECT - without this, Perspective rejects it.
		);
	}

	/**
	 * Get the attributes to analyze.
	 *
	 * @since 1.0
	 *
	 * @param array $form The current form.
	 *
	 * @return array
	 */
	public function get_requested_attributes( $form ) {
		$form_settings = $this->addon->get_form_settings( $form );
		if ( rgar( $form_settings, 'filtered_attributes' ) ) {
			$attributes = $form_settings['filtered_attributes'];
		} else {
			$attributes = $this->addon->get_plugin_setting( 'filtered_attributes' );
		}

		return $attributes;
	}

	/**
	 * Format the attributes for the API request.
	 *
	 * @since 1.0
	 *
	 * @param array $form The current form.
	 *
	 * @return array
	 */
	public function get_requested_attributes_for_api_request( $form ) {
		$requested_attributes = array();

		if ( empty( $form ) ) {
			$requested_attributes['TOXICITY'] = array();
			return $requested_attributes;
		}

		$attributes = $this->get_requested_attributes( $form );

		if ( ! is_array ( $attributes ) ) {
			$requested_attributes['TOXICITY'] = array();
			return $requested_attributes;
		}

		foreach( $attributes as $attribute ) {
			$requested_attributes[ strtoupper( $attribute ) ] = array();
		}

		return $requested_attributes;
	}

	/**
	 * Get the correct language code to send to the API.
	 *
	 * The Perspective API uses slightly different language codes than WordPress.
	 *
	 * @since 1.0
	 *
	 * @param string $locale The WordPress locale code.
	 *
	 * @return string|bool $language Perspective's language code, false if language isn't supported
	 */
	public function get_api_language_code( $locale ) {
		$supported = $this->get_supported_languages();
		$lang_code = rgar( explode( '_', $locale ), 0 );

		return isset( $supported[ $lang_code ] ) ? $lang_code : false;
	}

	/**
	 * Determine whether a language supports filtering on a given attribute.
	 *
	 * @since 1.0
	 *
	 * @param string $language  The Perspective API's two-letter language code.
	 * @param string $attribute The attribute to check.
	 *
	 * @return bool Whether the attribute is supported
	 */
	public function language_supports_attribute( $language, $attribute ) {
		$supported = $this->get_supported_languages();

		return isset( $supported[ $language ] ) && in_array( strtolower( $attribute ), $supported[ $language ] );
	}

	/**
	 * Returns an array with the language code as the key to the attributes supported by the Perspective API for that language.
	 *
	 * @since 1.0
	 *
	 * @return array[]
	 */
	public function get_supported_languages() {
		return array(
			'ar' => array( 'toxicity', 'severe_toxicity', 'identity_attack', 'insult', 'profanity', 'threat' ),
			'zh' => array( 'toxicity', 'severe_toxicity', 'identity_attack', 'insult', 'profanity', 'threat' ),
			'cs' => array( 'toxicity', 'severe_toxicity', 'identity_attack', 'insult', 'profanity', 'threat' ),
			'nl' => array( 'toxicity', 'severe_toxicity', 'identity_attack', 'insult', 'profanity', 'threat' ),
			'en' => array( 'toxicity', 'severe_toxicity', 'identity_attack', 'insult', 'profanity', 'threat' ),
			'fr' => array( 'toxicity', 'severe_toxicity', 'identity_attack', 'insult', 'profanity', 'threat' ),
			'de' => array( 'toxicity', 'severe_toxicity', 'identity_attack', 'insult', 'profanity', 'threat' ),
			'hi' => array( 'toxicity', 'severe_toxicity', 'identity_attack', 'insult', 'profanity', 'threat' ),
			'id' => array( 'toxicity', 'severe_toxicity', 'identity_attack', 'insult', 'profanity', 'threat' ),
			'it' => array( 'toxicity', 'severe_toxicity', 'identity_attack', 'insult', 'profanity', 'threat' ),
			'ja' => array( 'toxicity', 'severe_toxicity', 'identity_attack', 'insult', 'profanity', 'threat' ),
			'ko' => array( 'toxicity', 'severe_toxicity', 'identity_attack', 'insult', 'profanity', 'threat' ),
			'pl' => array( 'toxicity', 'severe_toxicity', 'identity_attack', 'insult', 'profanity', 'threat' ),
			'pt' => array( 'toxicity', 'severe_toxicity', 'identity_attack', 'insult', 'profanity', 'threat' ),
			'ru' => array( 'toxicity', 'severe_toxicity', 'identity_attack', 'insult', 'profanity', 'threat' ),
			'es' => array( 'toxicity', 'severe_toxicity', 'identity_attack', 'insult', 'profanity', 'threat' ),
			'sv' => array( 'toxicity', 'severe_toxicity', 'identity_attack', 'insult', 'profanity', 'threat' ),
		);
	}

	/**
	 * Determines if the site's language is in the list of supported languages by the Perspective API.
	 *
	 * @since 1.0
	 *
	 * @param string $locale WordPress locale code.
	 *
	 * @return bool True if the site language is supported by the Perspective API. Returns false otherwise.
	 */
	public function is_language_supported( $locale ) {
		$language_code = $this->get_api_language_code( $locale );
		return $language_code !== false;
	}

	/**
	 * Get all the possible attributes that can be filtered.
	 *
	 * @since 1.0
	 *
	 * @return array[] Array of attributes, formatted as checkbox choices.
	 */
	public function get_possible_attributes() {
		return array(
			array(
				'name'  => 'toxicity',
				'label' => __( 'Toxicity', 'gravityformsmoderation' ),
				'default_value' => 1,
			),
			array(
				'name'  => 'severe_toxicity',
				'label' => __( 'Severe Toxicity', 'gravityformsmoderation' ),
			),
			array(
				'name'  => 'identity_attack',
				'label' => __( 'Identity Attack', 'gravityformsmoderation' ),
			),
			array(
				'name'  => 'insult',
				'label' => __( 'Insult', 'gravityformsmoderation' ),
			),
			array(
				'name'  => 'profanity',
				'label' => __( 'Profanity', 'gravityformsmoderation' ),
			),
			array(
				'name'  => 'threat',
				'label' => __( 'Threat', 'gravityformsmoderation' ),
			),
		);
	}

	/**
	 * Get the checkbox choices for the "Attributes to filter" setting.
	 *
	 * @since 1.0
	 *
	 * @param string $locale Locale code to get attributes for.
	 * @return array|void Checkbox choices.
	 */
	public function get_attribute_choices( $locale ) {

		$language_code = $this->get_api_language_code( $locale );

		if ( ! $language_code ) {
			return;
		}

		$attributes = $this->get_possible_attributes();
		$choices    = array();

		foreach ( $attributes as $attribute ) {
			if ( $this->language_supports_attribute( $language_code, $attribute['name'] ) ) {
				$choices[] = $attribute;
			}
		}

		// If there is only one choice, there's no reason to have a setting for users to choose.
		if ( 1 === count( $choices ) ) {
			return;
		}

		return $choices;
	}

}
