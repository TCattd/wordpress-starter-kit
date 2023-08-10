<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2023 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {

	die( 'These aren\'t the droids you\'re looking for.' );
}

if ( ! defined( 'JSMSTM_PLUGINDIR' ) ) {

	die( 'Do. Or do not. There is no try.' );
}

if ( ! class_exists( 'JsmStmCompat' ) ) {

	class JsmStmCompat {

		public function __construct() {

			add_filter( 'jsmstm_metabox_table_metadata', array( $this, 'add_yoast_seo_term_meta' ), 10, 2 );
		}

		public function add_yoast_seo_term_meta( $term_meta, $term_obj ) {

			$tax_opts = get_option( 'wpseo_taxonomy_meta' );

			if ( isset( $tax_opts[ $term_obj->taxonomy ][ $term_obj->term_id ] ) ) {

				$term_meta[ 'wpseo_taxonomy_meta' ][] = $tax_opts[ $term_obj->taxonomy ][ $term_obj->term_id ];
			}

			return $term_meta;
		}
	}
}
