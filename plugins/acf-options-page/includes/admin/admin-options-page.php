<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( !class_exists('acf_admin_options_page') ):

class acf_admin_options_page {
	
	/*
	*  __construct
	*
	*  This function will setup the class functionality
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
		
		// actions
		add_action('init', array($this, 'init'));
		add_action('admin_menu', array($this,'admin_menu'), 99, 0);
		
		
		// filters
		add_filter( 'acf/location/rule_types', 					array($this, 'rule_types'), 10, 1 );
		add_filter( 'acf/location/rule_values/options_page',	array($this, 'rule_values'), 10, 1 );
		add_filter( 'acf/location/rule_match/options_page',		array($this, 'rule_match'), 10, 3 );
		
	}
	
	
	/*
	*  acf_location_rules_types
	*
	*  this function will add "Options Page" to the ACF location rules
	*
	*  @type	function
	*  @date	2/02/13
	*
	*  @param	{array}	$choices
	*  @return	{array}	$choices
	*/
	
	function rule_types( $choices ) {
		
	    $choices[ __("Forms",'acf') ]['options_page'] = __("Options Page",'acf');
		
	    return $choices;
	    
	}
	
	
	/*
	*  acf_location_rules_values_options_page
	*
	*  this function will populate the available pages in the ACF location rules
	*
	*  @type	function
	*  @date	2/02/13
	*
	*  @param	{array}	$choices
	*  @return	{array}	$choices
	*/
	
	function rule_values( $choices ) {
		
		// vars
		$pages = acf_get_options_pages();
		
		
		// populate
		if( !empty($pages) ) {
		
			foreach( $pages as $page ) {
			
				$choices[ $page['menu_slug'] ] = $page['menu_title'];
				
			}
			
		} else {
			
			$choices[''] = __('No options pages exist', 'acf');
			
		}
		
		
		// return
	    return $choices;
	}
	
	
	/*
	*  rule_match
	*
	*  description
	*
	*  @type	function
	*  @date	24/02/2014
	*  @since	5.0.0
	*
	*  @param	
	*  @return	
	*/
	
	function rule_match( $match, $rule, $options ) {
		
		// vars
		$options_page = false;
		
		
		// $options does not contain a default for "options_page"
		if( isset($options['options_page']) ) {
		
			$options_page = $options['options_page'];
			
		}
		
		
		// match
		if( $rule['operator'] == "==" ) {
		
        	$match = ( $options_page === $rule['value'] );
        	
        } elseif( $rule['operator'] == "!=" ) {
        
        	$match = ( $options_page !== $rule['value'] );
        	
        }
        
        
        // return
        return $match;
        
    }
    
	
	/*
	*  init
	*
	*  description
	*
	*  @type	function
	*  @date	5/4/17
	*  @since	5.5.10
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function init() {
		
		// vars
		$parent = acf_get_options_page('acf-options');
		
		
		// allow for backwards compatibility filter
		$settings = apply_filters('acf/options_page/settings', array(
			'title' 		=> $parent['page_title'],
			'menu'			=> $parent['menu_title'],
			'slug' 			=> $parent['menu_slug'],
			'capability'	=> $parent['capability'],
			'pages' 		=> array(),
		));
		
		
		// update
		acf_update_options_page('acf-options', array(
			'page_title'	=> $settings['title'],
			'menu_title'	=> $settings['menu'],
			'menu_slug' 	=> $settings['slug'],
			'capability'	=> $settings['capability'],
		));
		
		
		// add sub options pages
		if( !empty($settings['pages']) ) {
			
			foreach( $settings['pages'] as $page ) {
				
				acf_add_options_sub_page(array(
					'page_title'	=> $page,
					'menu_title'	=> $page,
					'parent_slug'	=> $settings['slug'],
				));
				
			}
				
		}
		
	}
	
	
	/*
	*  admin_menu
	*
	*  description
	*
	*  @type	function
	*  @date	24/02/2014
	*  @since	5.0.0
	*
	*  @param	
	*  @return	
	*/
	
	function admin_menu() {
		
		// vars
		$pages = acf_get_options_pages();
		
		
		// bail early if no pages
		if( empty($pages) ) return;
		
		
		// loop
		foreach( $pages as $page ) {
			
			// vars
			$slug = '';
			
			
			// parent
			if( empty($page['parent_slug']) ) {
				
				$slug = add_menu_page( $page['page_title'], $page['menu_title'], $page['capability'], $page['menu_slug'], array($this, 'html'), $page['icon_url'], $page['position'] );
			
			// child
			} else {
				
				$slug = add_submenu_page( $page['parent_slug'], $page['page_title'], $page['menu_title'], $page['capability'], $page['menu_slug'], array($this, 'html') );
				
			}
			
			
			// actions
			add_action("load-{$slug}", array($this,'admin_load'));
			
		}
		
	}
	
	
	/*
	*  load
	*
	*  description
	*
	*  @type	function
	*  @date	2/02/13
	*  @since	3.6
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function admin_load() {
		
		/* do nothing */
		
	}
	
}


// end class
endif;

?>