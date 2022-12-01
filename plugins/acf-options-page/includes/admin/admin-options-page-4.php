<?php 

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( !class_exists('acf_admin_options_page_4') ):

class acf_admin_options_page_4 extends acf_admin_options_page {
	
	/** @var array Contains the current options page */
	var $page;
	
	
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
		
		// globals
		global $plugin_page;
		
		
		// vars
		$this->page = acf_get_options_page( $plugin_page );
		
		
		// verify and remove nonce
		if( isset($_POST['acf_nonce']) && wp_verify_nonce($_POST['acf_nonce'], 'input') ) {
			
			// save
			do_action('acf/save_post', 'options');
			
					
			// redirect
			wp_redirect( add_query_arg(array('message' => '1')) );
			exit;
			
		}
		
		
		// actions
		add_action('admin_enqueue_scripts', array($this,'admin_enqueue_scripts'));
		add_action('admin_head', array($this,'admin_head'));
		add_action('admin_footer', array($this,'admin_footer'));
		
	}
	
	
	/*
	*  admin_enqueue_scripts
	*
	*  @description: run after post query but before any admin script / head actions. A good place to register all actions.
	*  @since: 3.6
	*  @created: 26/01/13
	*/
	
	function admin_enqueue_scripts() {
		
		do_action('acf/input/admin_enqueue_scripts');
		
	}
	
	
	/*
	*  admin_head
	*
	*  @description: 
	*  @since: 2.0.4
	*  @created: 5/12/12
	*/
	
	function admin_head() {	
		
		// get field groups
		$metabox_ids = apply_filters('acf/location/match_field_groups', array(), array(
			'options_page' => $this->page['menu_slug']
		));

		
		// no fields
		if( empty($metabox_ids) ) {
			$this->page['no_fields'] = true;
		}
		
		
		// Style
		echo '<style type="text/css">#side-sortables.empty-container { border: 0 none; }</style>';
		
		
		// add user js + css
		do_action('acf/input/admin_head');
		
		
		// get field groups
		$acfs = apply_filters('acf/get_field_groups', array());
		
		
		if( $acfs )
		{
			foreach( $acfs as $acf )
			{
				// load options
				$acf['options'] = apply_filters('acf/field_group/get_options', array(), $acf['id']);
				
				
				// vars
				$show = in_array( $acf['id'], $metabox_ids ) ? 1 : 0;
				
				if( !$show )
				{
					continue;
				}
				
				
				// add meta box
				add_meta_box(
					'acf_' . $acf['id'], 
					$acf['title'], 
					array($this, 'meta_box_input'), 
					'acf_options_page',
					$acf['options']['position'], 
					'high',
					array( 'field_group' => $acf, 'show' => $show, 'post_id' => 'options' )
				);
				
			}
			// foreach($acfs as $acf)
		}
		// if($acfs)
		
	}
	
	
	/*
	*  meta_box_input
	*
	*  @description: 
	*  @since 1.0.0
	*  @created: 23/06/12
	*/
	
	function meta_box_input( $post, $args )
	{
		// vars
		$options = $args['args'];
		
		
		echo '<div class="options" data-layout="' . $options['field_group']['options']['layout'] . '" data-show="' . $options['show'] . '" style="display:none"></div>';
		
		$fields = apply_filters('acf/field_group/get_fields', array(), $options['field_group']['id']);
					
		do_action('acf/create_fields', $fields, $options['post_id']);
		
	}
	
	
	/*
	*  admin_footer
	*
	*  @description: 
	*  @since: 2.0.4
	*  @created: 5/12/12
	*/
	
	function admin_footer()
	{
		// add togle open / close postbox
		?>
		<script type="text/javascript">
		(function($){
			
			$('.postbox .handlediv').live('click', function(){
				
				var postbox = $(this).closest('.postbox');
				
				if( postbox.hasClass('closed') )
				{
					postbox.removeClass('closed');
				}
				else
				{
					postbox.addClass('closed');
				}
				
			});
			
		})(jQuery);
		</script>
		<?php
	}
	
	
	/*
	*  html
	*
	*  @description: 
	*  @since: 2.0.4
	*  @created: 5/12/12
	*/
	
	function html()
	{
		?>
		<div class="wrap no_move">
		
			<div class="icon32" id="icon-options-general"><br></div>
			<h2><?php echo get_admin_page_title(); ?></h2>
			
			<?php if( !empty($_GET['message']) && $_GET['message'] == '1' ): ?>
			<div id="message" class="updated"><p><?php echo $this->page['updated_message']; ?></p></div>
			<?php endif; ?>
			
			<?php if( !empty($this->page['no_fields']) ): ?>
			<div id="message" class="updated"><p><?php _e("No Custom Field Group found for the options page",'acf'); ?>. <a href="<?php echo admin_url(); ?>post-new.php?post_type=acf"><?php _e("Create a Custom Field Group",'acf'); ?></a></p></div>
			<?php else: ?>
			
			<form id="post" method="post" name="post">
			<div class="metabox-holder has-right-sidebar" id="poststuff">
				
				<!-- Sidebar -->
				<div class="inner-sidebar" id="side-info-column">
					
					<!-- Update -->
					<div class="postbox">
						<h3 class="hndle"><span><?php _e("Publish",'acf'); ?></span></h3>
						<div class="inside">
							<input type="hidden" name="HTTP_REFERER" value="<?php echo $_SERVER['HTTP_REFERER'] ?>" />
							<input type="hidden" name="acf_nonce" value="<?php echo wp_create_nonce( 'input' ); ?>" />
							<input type="submit" class="acf-button" value="<?php _e("Save Options",'acf'); ?>" />
						</div>
					</div>
					
					<?php $meta_boxes = do_meta_boxes('acf_options_page', 'side', null); ?>
					
				</div>
					
				<!-- Main -->
				<div id="post-body">
				<div id="post-body-content">
					<?php $meta_boxes = do_meta_boxes('acf_options_page', 'normal', null); ?>
					<script type="text/javascript">
					(function($){
					
						$('#poststuff .postbox[id*="acf_"]').addClass('acf_postbox');

					})(jQuery);
					</script>
				</div>
				</div>
			
			</div>
			</form>
			
			<?php endif; ?>
		
		</div>
		
		<?php
				
	}
	
}


// instantiate
new acf_admin_options_page_4();


// end class
endif;

?>