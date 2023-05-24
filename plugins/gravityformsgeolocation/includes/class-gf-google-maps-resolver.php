<?php

namespace Gravity_Forms\Gravity_Forms_Geolocation;

defined( 'ABSPATH' ) || die();

class GF_Google_Maps_Resolver {

	protected $script_name;
	protected $required_libs;

	public function resolve( $script_name, $required_libs ) {
		$this->script_name   = $script_name;
		$this->required_libs = $required_libs;

		global $wp_scripts;

		$found = $this->search_for_duplicate_script();

		// No other maps scripts found, bail.
		if ( empty( $found ) ) {
			return;
		}

		$self_script = $wp_scripts->registered[ $this->script_name ];

		// Dequeue own script
		$wp_scripts->dequeue( $this->script_name );

		// Get libs from existing src
		$script_obj = array_shift( $found );
		$libs       = $this->get_libs_from_src( $script_obj );

		// Loaded lib already has places, we're good, so we can set the queued var and bail.
		if ( in_array( 'places', $libs ) ) {
			return;
		}

		// Rebuild libs array and add it to the src for the other loaded script.
		$libs = array_merge( $libs, $this->required_libs );

		$wp_scripts->registered[ $script_obj->handle ]->src = add_query_arg( array( 'libraries' => implode( ',', $libs ) ), $script_obj->src );

		// If our script has localized data, add it to the existing script's data value.
		if ( ! isset( $self_script->extra['data'] ) ) {
			return;
		}

		$this->add_config_data_to_other_script( $script_obj, $self_script );
	}

	private function search_for_duplicate_script() {
		global $wp_scripts;
		$script_name = $this->script_name;

		return array_filter( array_map( function ( $script ) use ( $wp_scripts, $script_name ) {
			if ( $script == $script_name ) {
				return false;
			}

			$script_obj = rgar( $wp_scripts->registered, $script, false );

			if ( ! $script_obj ) {
				return false;
			}

			if ( ! strpos( $script_obj->src, 'maps.googleapis.com/maps/api/js' ) ) {
				return false;
			}

			return $script_obj;
		}, $wp_scripts->queue ) );
	}

	private function get_libs_from_source( $script_obj ) {
		parse_str( parse_url( $script_obj->src )['query'], $query_vars );

		return strlen( rgar( $query_vars, 'libraries', '' ) ) ? explode( ',', rgar( $query_vars, 'libraries', '' ) ) : array();
	}

	private function add_config_data_to_other_script( $script_obj, $self_script ) {
		global $wp_scripts;

		if ( ! isset( $wp_scripts->registered[ $script_obj->handle ]->extra['data'] ) ) {
			$wp_scripts->registered[ $script_obj->handle ]->extra['data'] = '';
		}

		$wp_scripts->registered[ $script_obj->handle ]->extra['data'] .= "\n" . $self_script->extra['data'];
	}

}