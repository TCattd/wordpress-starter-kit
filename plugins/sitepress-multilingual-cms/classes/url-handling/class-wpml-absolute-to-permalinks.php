<?php

class WPML_Absolute_To_Permalinks {

	private $taxonomies_query;
	private $lang;

	/** @var SitePress $sitepress */
	private $sitepress;

	public function __construct( SitePress $sitepress ) {
		$this->sitepress = $sitepress;
	}

	public function convert_text( $text ) {

		$this->lang = $this->sitepress->get_current_language();

		$active_langs_reg_ex = implode( '|', array_keys( $this->sitepress->get_active_languages() ) );

		if ( ! $this->taxonomies_query ) {
			$this->taxonomies_query = new WPML_WP_Taxonomy_Query( $this->sitepress->get_wp_api() );
		}

		$home    = rtrim( $this->sitepress->get_wp_api()->get_option( 'home' ), '/' );
		$parts   = parse_url( $home );
		$abshome = $parts['scheme'] . '://' . $parts['host'];
		$path    = isset( $parts['path'] ) ? ltrim( $parts['path'], '/' ) : '';
		$tx_qvs  = join( '|', $this->taxonomies_query->get_query_vars() );
		$reg_ex  = '@<a([^>]+)?href="((' . $abshome . ')?/' . $path . '/?(' . $active_langs_reg_ex . ')?\?(p|page_id|cat_ID|' . $tx_qvs . ')=([0-9a-z-]+))(#?[^"]*)"([^>]+)?>@i';
		$text    = preg_replace_callback( $reg_ex, [ $this, 'show_permalinks_cb' ], $text );

		return $text;
	}

	function show_permalinks_cb( $matches ) {

		$parts = $this->get_found_parts( $matches );

		$url = $this->get_url( $parts );

		if ( $this->sitepress->get_wp_api()->is_wp_error( $url ) || empty( $url ) ) {
			return $parts->whole;
		}

		$fragment = $this->get_fragment( $url, $parts );

		if ( 'widget_text' == $this->sitepress->get_wp_api()->current_filter() ) {
			$url = $this->sitepress->convert_url( $url );
		}

		return '<a' . $parts->pre_href . 'href="' . $url . $fragment . '"' . $parts->trail . '>';
	}

	private function get_found_parts( $matches ) {
		return (object) array(
			'whole'        => $matches[0],
			'pre_href'     => $matches[1],
			'content_type' => $matches[5],
			'id'           => $matches[6],
			'fragment'     => $matches[7],
			'trail'        => isset( $matches[8] ) ? $matches[8] : '',
		);
	}

	private function get_url( $parts ) {
		$tax = $this->taxonomies_query->find( $parts->content_type );

		// Always enable adjust id to get the current lang id.
		$this->force_adjust_id_to_translated();

		if ( $parts->content_type == 'cat_ID' ) {
			$url = $this->sitepress->get_wp_api()->get_category_link( $parts->id );
		} elseif ( $tax ) {
			$url = $this->sitepress->get_wp_api()->get_term_link( $parts->id, $tax );
		} else {
			$url = $this->sitepress->get_wp_api()->get_permalink( $parts->id );
		}

		// Reapply the behaviour before force_adjust_id_to_translated().
		$this->restore_original_adjust_id_behaviour();

		return $url;
	}

	/** @var bool? $adjust_id_was_enabled State before enabling it. */
	private $adjust_id_was_enabled;
	/** @var bool? $adjust_id_had_filter_get_term */
	private $adjust_id_had_filter_get_term;
	/** @var bool? $adjust_id_had_filter_get_pages */
	private $adjust_id_had_filter_get_pages;

	private function force_adjust_id_to_translated() {
		// Enable Adjust Id Setting if not active.
		// Looking isolated to this file it may not be required, but the
		// get_wp_api() calls may trigger filters which in the end checking
		// for the setting to be enabled.
		$this->adjust_id_was_enabled =
			$this->sitepress->get_setting( 'auto_adjust_ids', false );

		! $this->adjust_id_was_enabled &&
			$this->sitepress->set_setting( 'auto_adjust_ids', true );

		// Add SitePress::get_term_adjust_id() as callback for get_term()
		// if not already added.
		$this->adjust_id_had_filter_get_term =
			has_filter( 'get_term', [ $this->sitepress, 'get_term_adjust_id' ] );

		! $this->adjust_id_had_filter_get_term &&
			add_filter( 'get_term', [ $this->sitepress, 'get_term_adjust_id' ], 1 );

		// Add SitePress::get_pages_adjust_ids as callback to get_pages()
		// if not already added.
		$this->adjust_id_had_filter_get_pages =
			has_filter( 'get_pages', [ $this->sitepress, 'get_pages_adjust_ids' ] );

		! $this->adjust_id_had_filter_get_pages &&
			add_filter( 'get_pages', [ $this->sitepress, 'get_pages_adjust_ids' ], 1, 2 );
	}

	private function restore_original_adjust_id_behaviour() {
		if ( null === $this->adjust_id_was_enabled ) {
			// Called before self::enable_adjust_id().
			return;
		}

		// Restore 'adjust_id' setting.
		! $this->adjust_id_was_enabled &&
			$this->sitepress->set_setting( 'auto_adjust_ids', false );

		// Remove filter callback for get_term() if it wasn't there before.
		! $this->adjust_id_had_filter_get_term &&
			remove_filter( 'get_term', array( $this->sitepress, 'get_term_adjust_id' ), 1 );

		// Remove filter callback for get_pages() if it wasn't there before.
		! $this->adjust_id_had_filter_get_pages &&
			remove_filter( 'get_pages', array( $this, 'get_pages_adjust_ids' ), 1 );

		// Reset state for next iteration.
		$this->adjust_id_was_enabled          = null;
		$this->adjust_id_had_filter_get_term  = null;
		$this->adjust_id_had_filter_get_pages = null;
	}


	private function get_fragment( $url, $parts ) {
		$fragment = $parts->fragment;
		$fragment = $this->remove_query_in_wrong_lang( $fragment );
		if ( $fragment != '' ) {
			$fragment = str_replace( '&#038;', '&', $fragment );
			$fragment = str_replace( '&amp;', '&', $fragment );
			if ( $fragment[0] == '&' ) {
				if ( strpos( $fragment, '?' ) === false && strpos( $url, '?' ) === false ) {
					$fragment[0] = '?';
				}
			}

			if ( strpos( $url, '?' ) ) {
				$fragment = $this->check_for_duplicate_lang_query( $fragment, $url );
			}
		}

		return $fragment;
	}

	private function remove_query_in_wrong_lang( $fragment ) {
		if ( $fragment != '' ) {
			$fragment = str_replace( '&#038;', '&', $fragment );
			$fragment = str_replace( '&amp;', '&', $fragment );
			$start    = $fragment[0];
			parse_str( substr( $fragment, 1 ), $fragment_query );
			if ( isset( $fragment_query['lang'] ) ) {
				if ( $fragment_query['lang'] != $this->lang ) {
					unset( $fragment_query['lang'] );

					$fragment = build_query( $fragment_query );
					if ( strlen( $fragment ) ) {
						$fragment = $start . $fragment;
					}
				}
			}
		}
		return $fragment;
	}

	private function check_for_duplicate_lang_query( $fragment, $url ) {
		$url_parts = explode( '?', $url );
		parse_str( $url_parts[1], $url_query );

		if ( isset( $url_query['lang'] ) ) {
			parse_str( substr( $fragment, 1 ), $fragment_query );
			if ( isset( $fragment_query['lang'] ) ) {
				unset( $fragment_query['lang'] );
				$fragment = build_query( $fragment_query );
				if ( strlen( $fragment ) ) {
					$fragment = '&' . $fragment;
				}
			}
		}
		return $fragment;
	}
}
