<?php
/* Copyright 2011 Ungureanu Madalin (email : madalin@reflectionmedia.ro)
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

if( file_exists( dirname(__FILE__). '/wck-fep/wck-fep.php' ) )
	require_once( 'wck-fep/wck-fep.php' );

if( file_exists( dirname(__FILE__). '/wck-static-metabox-api.php' ) )
	require_once( 'wck-static-metabox-api.php' );


/*

Usage Example 1:


$fint = array(
		array( 'type' => 'text', 'title' => 'Title', 'description' => 'Description for this input' ),
		array( 'type' => 'textarea', 'title' => 'Description' ),
		array( 'type' => 'upload', 'title' => 'Image', 'description' => 'Upload a image' ),
		array( 'type' => 'select', 'title' => 'Select This', 'options' => array( 'Option 1', 'Option 2', 'Option 3' ) ),
		array( 'type' => 'checkbox', 'title' => 'Check This', 'options' => array( 'Option 1', 'Option 2', 'Option 3' ) ),
		array( 'type' => 'radio', 'title' => 'Radio This', 'options' => array( 'Radio 1', 'Radio 2', 'Radio 3' ) ),
	);

$args = array(
	'metabox_id' => 'rm_slider_content',
	'metabox_title' => 'Slideshow Class',
	'post_type' => 'slideshows',
	'meta_name' => 'rmscontent',
	'meta_array' => $fint
);

new Wordpress_Creation_Kit_PB( $args );


On the frontend:

$meta = get_post_meta( $post->ID, 'rmscontent', true );

*/

class Wordpress_Creation_Kit_PB{

	private $defaults = array(
							'metabox_id' => '',
							'metabox_title' => 'Meta Box',
							'post_type' => 'post',
							'meta_name' => '',
							'meta_array' => array(),
							'page_template' => '',
							'post_id' => '',
							'single' => false,
							'unserialize_fields' => false,
							'sortable' => true,
							'context' => 'post_meta',
                            'mb_context' => 'normal'
						);
	private $args;


	/* Constructor method for the class. */
	function __construct( $args ) {

		/* Global that will hold all the arguments for all the custom boxes */
		global $wck_objects;

		/* Merge the input arguments and the defaults. */
		$this->args = wp_parse_args( $args, $this->defaults );

		/* Add the settings for this box to the global object */
		$wck_objects[$this->args['metabox_id']] = $this->args;

		/*print scripts*/
		add_action('admin_enqueue_scripts', array( &$this, 'wck_print_scripts' ), 9);
		/* add our own ajaxurl because we are going to use the wck script also in frontend and we want to avoid any conflicts */
		add_action( 'admin_head', array( &$this, 'wck_print_ajax_url' ) );

		// Set up the AJAX hooks
		add_action("wp_ajax_wck_add_meta".$this->args['meta_name'], array( &$this, 'wck_add_meta') );
		add_action("wp_ajax_wck_update_meta".$this->args['meta_name'], array( &$this, 'wck_update_meta') );
		add_action("wp_ajax_wck_show_update".$this->args['meta_name'], array( &$this, 'wck_show_update_form') );
		add_action("wp_ajax_wck_refresh_list".$this->args['meta_name'], array( &$this, 'wck_refresh_list') );
		add_action("wp_ajax_wck_remove_meta".$this->args['meta_name'], array( &$this, 'wck_remove_meta') );
		add_action("wp_ajax_wck_reorder_meta".$this->args['meta_name'], array( &$this, 'wck_reorder_meta') );

		add_action('add_meta_boxes', array( &$this, 'wck_add_metabox') );

        /* For single forms we save them the old fashion way */
        if( $this->args['single'] ){
            add_action('save_post', array($this, 'wck_save_single_metabox'), 10, 2);
            /* wp_insert_post executes after save_post so at this point if we have the error global we can redirect the page
             and add the error message and error fields urlencoded as $_GET */
            add_action('wp_insert_post', array($this, 'wck_single_metabox_redirect_if_errors'), 10, 2);
            /* if we have any $_GET errors alert them with js so we have consistency */
            add_action('admin_print_footer_scripts', array($this, 'wck_single_metabox_errors_display') );
        }

	}


	//add metabox using wordpress api

	function wck_add_metabox() {

		global $pb_wck_pages_hooknames;

		if( $this->args['context'] == 'post_meta' ){
			if( $this->args['post_id'] == '' && $this->args['page_template'] == '' ){
				add_meta_box($this->args['metabox_id'], $this->args['metabox_title'], array( &$this, 'wck_content' ), $this->args['post_type'], $this->args['mb_context'], 'high',  array( 'meta_name' => $this->args['meta_name'], 'meta_array' => $this->args['meta_array']) );
				/* add class to meta box */
				add_filter( "postbox_classes_".$this->args['post_type']."_".$this->args['metabox_id'], array( &$this, 'wck_add_metabox_classes' ) );
			}
			else{
				if( !empty( $_GET['post'] ) )
					$post_id = filter_var( $_GET['post'], FILTER_SANITIZE_NUMBER_INT );
				else if( !empty( $_POST['post_ID'] ) )
					$post_id = filter_var( $_POST['post_ID'], FILTER_SANITIZE_NUMBER_INT );
				else
					$post_id = '';


				if( $this->args['post_id'] != '' && $this->args['page_template'] != '' ){
					$template_file = get_post_meta($post_id,'_wp_page_template',TRUE);
					if( $this->args['post_id'] == $post_id && $template_file == $this->args['page_template'] )
						add_meta_box($this->args['metabox_id'], $this->args['metabox_title'], array( &$this, 'wck_content' ), 'page', $this->args['mb_context'], 'high',  array( 'meta_name' => $this->args['meta_name'], 'meta_array' => $this->args['meta_array'] ) );

					/* add class to meta box */
					add_filter( "postbox_classes_page_".$this->args['metabox_id'], array( &$this, 'wck_add_metabox_classes' ) );
				}
				else{

					if( $this->args['post_id'] != '' ){
						if( $this->args['post_id'] == $post_id ){
							$post_type = get_post_type( $post_id );
							add_meta_box($this->args['metabox_id'], $this->args['metabox_title'], array( &$this, 'wck_content' ), $post_type, $this->args['mb_context'], 'high',  array( 'meta_name' => $this->args['meta_name'], 'meta_array' => $this->args['meta_array'] ) );
							/* add class to meta box */
							add_filter( "postbox_classes_".$post_type."_".$this->args['metabox_id'], array( &$this, 'wck_add_metabox_classes' ) );
						}
					}

					if(  $this->args['page_template'] != '' ){
						$template_file = get_post_meta($post_id,'_wp_page_template',TRUE);
						if ( $template_file == $this->args['page_template'] ){
							add_meta_box($this->args['metabox_id'], $this->args['metabox_title'], array( &$this, 'wck_content' ), 'page', $this->args['mb_context'], 'high',  array( 'meta_name' => $this->args['meta_name'], 'meta_array' => $this->args['meta_array']) );
							/* add class to meta box */
							add_filter( "postbox_classes_page_".$this->args['metabox_id'], array( &$this, 'wck_add_metabox_classes' ) );
						}
					}

				}

			}
		}
		else if( $this->args['context'] == 'option' ){
            if( !empty( $pb_wck_pages_hooknames[$this->args['post_type']] ) ) {
                add_meta_box($this->args['metabox_id'], $this->args['metabox_title'], array(&$this, 'wck_content'), $pb_wck_pages_hooknames[$this->args['post_type']], $this->args['mb_context'], 'high', array('meta_name' => $this->args['meta_name'], 'meta_array' => $this->args['meta_array']));
                /* add class to meta box */
                add_filter("postbox_classes_" . $pb_wck_pages_hooknames[$this->args['post_type']] . "_" . $this->args['metabox_id'], array(&$this, 'wck_add_metabox_classes'));
            }
		}
	}

	/* Function used to add classes to the wck meta boxes */
	function wck_add_metabox_classes( $classes ){
		array_push($classes,'wck-post-box');
		return $classes;
	}

	function wck_content($post, $metabox){
		if( !empty( $post->ID ) )
			$post_id = $post->ID;
		else
			$post_id = '';



//		//output the add form or themes metabox content
        if ($metabox['id'] == 'wppb-ul-themes-settings' ) {
            echo $metabox['args']['meta_array']; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
        else self::create_add_form($metabox['args']['meta_array'], $metabox['args']['meta_name'], $post);

        //output the entries only for repeater fields
        if( !$this->args['single'] )
            echo self::wck_output_meta_content($metabox['args']['meta_name'], $post_id, $metabox['args']['meta_array']); //phpcs:ignore  WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * The function used to create a form element
	 *
	 * @since 1.0.0
	 *
	 * @param string $meta Meta name.
	 * @param array $details Contains the details for the field.
	 * @param string $value Contains input value;
	 * @param string $context Context where the function is used. Depending on it some actions are preformed.;
	 * @param int $post_id The post ID;
	 * @return string $element input element html string.
	 */

	function wck_output_form_field( $meta, $details, $value = '', $context = '', $post_id = '' ){
		$element = '';

		if( $context == 'edit_form' ){
			$edit_class = '.mb-table-container ';
			$var_prefix = 'edit';
		}
		else if( $context == 'fep' ){
			/* id prefix for frontend posting */
			$frontend_prefix = 'fep-';
		}
		else{
            if( isset( $details['default'] ) && !( $this->args['single'] == true && !is_null( $value ) ) ) {
                $value = apply_filters("wck_default_value_{$meta}_" . Wordpress_Creation_Kit_PB::wck_generate_slug( $details['title'], $details ), $details['default']);
            }
        }

        /* for single post meta metaboxes we need a prefix in the name attr of the input because in the case we have multiple single metaboxes on the same
        post we need to prevent the fields from having the same name attr */
        if( $this->args['context'] == 'post_meta' && $this->args['single'] && $context != 'fep' )
            $single_prefix = $this->args['meta_name'].'_';
        else
            $single_prefix = '';

        $element .= '<label for="'. $single_prefix . esc_attr( Wordpress_Creation_Kit_PB::wck_generate_slug( $details['title'],  $details ) ) .'" class="field-label">'. apply_filters( "wck_label_{$meta}_". Wordpress_Creation_Kit_PB::wck_generate_slug( $details['title'], $details ), ucfirst($details['title']) ) .':';
        if( !empty( $details['required'] ) && $details['required'] )
			$element .= '<span class="required">*</span>';
		$element .= '</label>';

		$element .= '<div class="mb-right-column">';

		// Allow for appeding custom markup before the element.
		$element .= apply_filters( 'wck_output_form_field_before_customtype_' . $details['type'], '', $value, $details, $single_prefix );

		/*
		include actual field type
		possible field types: text, textarea, select, checkbox, radio, upload, wysiwyg editor, datepicker, country select, user select, cpt select
		*/

		if( function_exists( 'wck_nr_get_repeater_boxes' ) ){
			$cfc_titles = wck_nr_get_repeater_boxes();
			if( in_array( $details['type'], $cfc_titles ) ){
				$details['type'] = 'nested repeater';
			}
		}

		if ( isset( $details['options'] ) ) {
			// Allow to filter the options for specific field slugs.
			$details = apply_filters( 'wck_output_form_field_options_by_slug', $details, $details['slug'], $value, $single_prefix );
		}

		if( file_exists( dirname( __FILE__ ).'/fields/'.$details['type'].'.php' ) ){
			require( dirname( __FILE__ ).'/fields/'.$details['type'].'.php' );
		}

        // Add a filter that allows us to add support for custom field types, not just the ones defined in fields (wck api)
        $element .=  apply_filters('wck_output_form_field_customtype_' . $details['type'], '', $value, $details, $single_prefix);


		// Allow for appeding custom markup after the element.
		$element .= apply_filters( 'wck_output_form_field_after_customtype_' . $details['type'], '', $value, $details, $single_prefix );

		if( !empty( $details['description'] ) ){
			$element .= '<p class="description">'. $details['description'].'</p>';
		}

		$element .= '</div><!-- .mb-right-column -->';

		$element = apply_filters( "wck_output_form_field_{$meta}_" . Wordpress_Creation_Kit_PB::wck_generate_slug( $details['title'], $details ), $element );

		return $element;

	}


	/**
	 * The function used to create the form for adding records
	 *
	 * @since 1.0.0
	 *
	 * @param array $fields Contains the desired inputs in the repeater field. Must be like: array('Key:type').
	 * Key is used for the name attribute of the field, label of the field and as the meta_key.
	 * Supported types: input, textarea, upload
	 * @param string $meta It is used in update_post_meta($id, $meta, $results);. Use '_' prefix if you don't want
	 * the meta to apear in custom fields box.
	 * @param object $post Post object
	 */
	function create_add_form($fields, $meta, $post, $context = '' ){
		$nonce = wp_create_nonce( 'wck-add-meta' );
		if( !empty( $post->ID ) )
			$post_id = $post->ID;
		else
			$post_id = '';

        /* for single forms we need the values that are stored in the meta */
        if( $this->args['single'] == true ) {
            if ($this->args['context'] == 'post_meta')
                $results = get_post_meta($post_id, $meta, true);
            else if ($this->args['context'] == 'option')
                $results = get_option( apply_filters( 'wck_option_meta' , $meta ));

            /* Filter primary used for CFC/OPC fields in order to show/hide fields based on type */
            $wck_update_container_css_class = apply_filters("wck_add_form_class_{$meta}", '', $meta, $results );
        }
        ?>
		<div id="<?php echo esc_attr( $meta ) ?>" style="padding:10px 0;" class="wck-add-form<?php if( $this->args['single'] ) echo ' single' ?> <?php if( !empty( $wck_update_container_css_class ) ) echo esc_attr( $wck_update_container_css_class ); ?>">
			<ul class="mb-list-entry-fields">
				<?php
				$element_id = 0;
				if( !empty( $fields ) ){
					foreach( $fields as $details ){

						do_action( "wck_before_add_form_{$meta}_element_{$element_id}" );

                        /* set values in the case of single forms */
                        $value = '';
                        if( $this->args['single'] == true ) {
                            $value = null;
                            if (isset($results[0][Wordpress_Creation_Kit_PB::wck_generate_slug($details['title'], $details )]))
                                $value = $results[0][Wordpress_Creation_Kit_PB::wck_generate_slug($details['title'], $details )];
                        }
                        ?>
							<li class="row-<?php echo esc_attr( Wordpress_Creation_Kit_PB::wck_generate_slug( $details['title'], $details ) ) ?>">
                                <?php echo self::wck_output_form_field( $meta, $details, $value, $context, $post_id ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</li>
						<?php

						do_action( "wck_after_add_form_{$meta}_element_{$element_id}" );

						$element_id++;
					}
				}
				?>
                <?php if( ! $this->args['single'] || $this->args['context'] == 'option' ){ ?>
                    <li style="overflow:visible;" class="add-entry-button">
                        <a href="javascript:void(0)" class="button-primary" onclick="addMeta('<?php echo esc_js($meta); ?>', '<?php echo esc_js( $post_id ); ?>', '<?php echo esc_js($nonce); ?>')"><span><?php if( $this->args['single'] ) echo esc_html( apply_filters( 'wck_add_entry_button', __( 'Save', 'profile-builder' ), $meta, $post ) ); else echo esc_html( apply_filters( 'wck_add_entry_button', __( 'Add Entry', 'profile-builder' ), $meta, $post ) ); ?></span></a>
                    </li>
                <?php }elseif($this->args['single'] && $this->args['context'] == 'post_meta' ){ ?>
                    <input type="hidden" name="_wckmetaname_<?php echo esc_attr( $meta ) ?>#wck" value="true">
                <?php } ?>
            </ul>
		</div>
		<script>wck_set_to_widest( '.field-label', '<?php echo esc_js( $meta ) ?>' );</script>
		<?php
	}

	/**
	 * The function used to display a form to update a reccord from meta
	 *
	 * @since 1.0.0
	 *
	 * @param string $meta It is used in get_post_meta($id, $meta, $results);. Use '_' prefix if you don't want
	 * the meta to apear in custom fields box.
	 * @param int $id Post id
	 * @param int $element_id The id of the reccord. The meta is stored as array(array());
	 */
	function mb_update_form($fields, $meta, $id, $element_id){

		$update_nonce = wp_create_nonce( 'wck-update-entry' );

		if( $this->args['context'] == 'post_meta' )
			$results = get_post_meta($id, $meta, true);
		else if ( $this->args['context'] == 'option' )
			$results = get_option( apply_filters( 'wck_option_meta' , $meta ) );

		/* Filter primary used for CFC/OPC fields in order to show/hide fields based on type */
		$wck_update_container_css_class = " class='wck_update_container update_container_$meta'";
		$wck_update_container_css_class = apply_filters("wck_update_container_class_{$meta}", $wck_update_container_css_class, $meta, $results, $element_id );

		$form = '';
		$form .= '<tr id="update_container_'.$meta.'_'.$element_id.'" ' . $wck_update_container_css_class . '><td colspan="4">';
		if($results != null){
			$i = 0;
			$form .= '<ul class="mb-list-entry-fields">';

			if( !empty( $fields ) ){
				foreach( $fields as $field ){
					$details = $field;
					if( isset( $results[$element_id][Wordpress_Creation_Kit_PB::wck_generate_slug( $details['title'], $details )] ) )
						$value = $results[$element_id][Wordpress_Creation_Kit_PB::wck_generate_slug( $details['title'], $details )];
					else
						$value = '';

					$form = apply_filters( "wck_before_update_form_{$meta}_element_{$i}", $form, $element_id, $value );

					$form .= '<li class="row-'. esc_attr( Wordpress_Creation_Kit_PB::wck_generate_slug( $details['title'], $details ) ) .'">';

					$form .= self::wck_output_form_field( $meta, $details, $value, 'edit_form', $id );

					$form .= '</li>';

					$form = apply_filters( "wck_after_update_form_{$meta}_element_{$i}", $form, $element_id, $value );

					$i++;
				}
			}
			$form .= '<li style="overflow:visible;">';
			$form .= '<a href="javascript:void(0)" class="button-primary" onclick=\'updateMeta("'.esc_js($meta).'", "'.esc_js($id).'", "'.esc_js($element_id).'", "'.esc_js($update_nonce).'")\'><span>'. apply_filters( 'wck_save_changes_button', __( 'Save Changes', 'profile-builder' ), $meta ) .'</span></a>';
			$form .= '<a href="javascript:void(0)" class="button-secondary" style="margin-left:10px;" onclick=\'removeUpdateForm("'. esc_js( 'update_container_'.$meta.'_'.$element_id ). '" )\'><span>'. apply_filters( 'wck_cancel_button', __(   'Cancel', 'profile-builder' ), $meta ) .'</span></a>';
			$form .= '</li>';

			$form .= '</ul>';
		}
		$form .= '</td></tr>';

		return $form;
	}


	/**
	 * The function used to output the content of a meta
	 *
	 * @since 1.0.0
	 *
	 * @param string $meta It is used in get_post_meta($id, $meta, $results);. Use '_' prefix if you don't want
	 * the meta to apear in custom fields box.
	 * @param int $id Post id
	 */
	function wck_output_meta_content($meta, $id, $fields, $box_args = '' ){
		/* in fep $this->args is empty so we need it as a parameter */
		if( !empty( $box_args ) )
			$this->args = wp_parse_args( $box_args, $this->defaults );


		if( $this->args['context'] == 'post_meta' || $this->args['context'] == '' )
			$results = get_post_meta($id, $meta, true);
		else if ( $this->args['context'] == 'option' )
			$results = get_option( apply_filters( 'wck_option_meta' , $meta ) );

		$list = '';
		$list .= '<table id="container_'.esc_attr($meta).'" class="mb-table-container widefat';

		if( $this->args['single'] ) $list .= ' single';
		if( !$this->args['sortable'] ) $list .= ' not-sortable';

		$list .= '" post="'.esc_attr($id).'">';


		if( !empty( $results ) ){
			$list .= apply_filters( 'wck_metabox_content_header_'.$meta , '<thead><tr><th class="wck-number">#</th><th class="wck-content">'. __( 'Content', 'profile-builder' ) .'</th><th class="wck-edit">'. __( 'Edit', 'profile-builder' ) .'</th><th class="wck-delete">'. __( 'Delete', 'profile-builder' ) .'</th></tr></thead>' );
			$i=0;
			foreach ($results as $result){

				$list .= self::wck_output_entry_content( $meta, $id, $fields, $results, $i );

				$i++;
			}
		}
		$list .= apply_filters( 'wck_metabox_content_footer_'.$meta , '', $id );
		$list .= '</table>';

		$list = apply_filters('wck_metabox_content_'.$meta, $list, $id);
		return $list;
	}

	function wck_output_entry_content( $meta, $id, $fields, $results, $element_id ){
		$edit_nonce = wp_create_nonce( 'wck-edit-entry' );
		$delete_nonce = wp_create_nonce( 'wck-delete-entry' );
		$entry_nr = $element_id +1;

		$wck_element_class = '';
		$wck_element_class = apply_filters( "wck_element_class_{$meta}", $wck_element_class, $meta, $results, $element_id );

		$list = '';
		$list .= '<tr id="element_'.$element_id.'" ' . $wck_element_class . '>';
		$list .= apply_filters( 'wck_add_content_before_columns', '', $list, $meta );
		$list .= '<td style="text-align:center;vertical-align:middle;" class="wck-number">'. $entry_nr .'</td>';
		$list .= '<td class="wck-content"><ul>' . "\r\n";

		$j = 0;

		if( !empty( $fields ) ){
			foreach( $fields as $field ){
				$details = $field;

				if( !empty( $results[$element_id][Wordpress_Creation_Kit_PB::wck_generate_slug( $details['title'], $details )] ) )
					$value = $results[$element_id][Wordpress_Creation_Kit_PB::wck_generate_slug( $details['title'], $details )];
				else
					$value ='';

				/* filter display value */
				$value = apply_filters( "wck_displayed_value_{$meta}_element_{$j}", $value );

				/* display it differently based on field type*/
				if( $details['type'] == 'upload' ){
					$display_value = self::wck_get_entry_field_upload($value);
				} elseif ( $details['type'] == 'user select' ) {
					$display_value = self::wck_get_entry_field_user_select( $value ) . '</pre>';
				} elseif ( $details['type'] == 'cpt select' ){
					$display_value = self::wck_get_entry_field_cpt_select( $value ) . '</pre>';
                } elseif ( $details['type'] == 'checkbox' && is_array( $value ) ){
                    $display_value = implode( ', ', $value );
				} elseif ( $details['type'] == 'select' || $details['type'] === 'select-2' ){
				    if ( $details['slug'] === 'pble-label' || $details['slug'] === 'field'){
                        $display_value = '<pre>' . self::wck_get_entry_field_select( $value, $details ) . '</pre>';
                    } else {
                        $display_value = '<pre>' . __(self::wck_get_entry_field_select( $value, $details ), 'profile-builder') . '</pre>'; //phpcs:ignore  WordPress.WP.I18n.NonSingularStringLiteralText
                    }
                } else {
					$display_value = '<pre>'.htmlspecialchars( $value ) . '</pre>';
				}

                $display_value = apply_filters( "wck_pre_displayed_value_{$meta}_element_{$field['slug']}", $display_value );

				$list = apply_filters( "wck_before_listed_{$meta}_element_{$j}", $list, $element_id, $value );
				/*check for nested repeater type and set it acordingly */
							if( strpos( $details['type'], 'CFC-') === 0 )
									$details['type'] = 'nested-repeater';

				$list .= '<li class="row-'. esc_attr( Wordpress_Creation_Kit_PB::wck_generate_slug( $details['title'], $details ) ) .'" data-type="'.$details['type'].'"><strong>'.$details['title'].': </strong>'.$display_value.' </li>' . "\r\n";

				$list = apply_filters( "wck_after_listed_{$meta}_element_{$j}", $list, $element_id, $value );

				$j++;

				/* In CFC/OPC we need the field title. Find it out and output it if found */
				if ($meta == 'wck_cfc_fields') {
					if( !empty( $results[$element_id][Wordpress_Creation_Kit_PB::wck_generate_slug( $details['title'], $details )] ) ){
						$field_title = $results[$element_id][Wordpress_Creation_Kit_PB::wck_generate_slug( $details['title'], $details )];
						if ($field_title == "Field Type")
							$cfc_field_type = $value;
					}
				}
			}
		}
		$list .= '</ul>';

		$list = apply_filters( 'wck_after_content_element', $list, $meta, $id, $results, $element_id );
		/* check if we have nested repeaters */
		if( function_exists( 'wck_nr_check_for_nested_repeaters' ) ){
			if( wck_nr_check_for_nested_repeaters( $fields ) === true ){
				$list .= wck_nr_handle_repeaters( $meta, $id, $fields, $results, $element_id );
			}
		}

		if( $element_id === 0 ){
			$list .= "<script>wck_set_to_widest( 'strong', '". $meta ."' );</script>";
		}

		$list .= '</td>';
		$list .= '<td style="text-align:center;vertical-align:middle;" class="wck-edit"><a href="javascript:void(0)" class="button-secondary"  onclick=\'showUpdateFormMeta("'.esc_js($meta).'", "'.esc_js($id).'", "'.esc_js($element_id).'", "'.esc_js($edit_nonce).'")\' title="'. __( 'Edit this item', 'profile-builder' ) .'">'. apply_filters( 'wck_edit_button', __('Edit','profile-builder'), $meta ) .'</a></td>';
		$list .= '<td style="text-align:center;vertical-align:middle;" class="wck-delete"><a href="javascript:void(0)" class="mbdelete" onclick=\'removeMeta("'.esc_js($meta).'", "'.esc_js($id).'", "'.esc_js($element_id).'", "'.esc_js($delete_nonce).'")\' title="'. __( 'Delete this item', 'profile-builder' ) .'">'. apply_filters( 'wck_delete_button', __( 'Delete', 'profile-builder' ), $meta) .'</a></td>';
		$list .= apply_filters( 'wck_add_content_after_columns', '', $list, $meta );

		$list .= "</tr> \r\n";

		return $list;
	}

	/* function to generate the output for the select field */
	function wck_get_entry_field_select( $value, $field_details ){
		if ( (!is_array( $field_details ) && !isset( $field_details['options']) ) || empty( $value )){
			return $value;
		}

        //convert field_details to single array if option groups are defined.
        if( Wordpress_Creation_Kit_PB::wck_is_multi( $field_details['options'] ) ){
            $select_options = array();
            foreach($field_details['options']['optgroups'] as $optgroup) {
                foreach($optgroup['options'] as $group_option ){

					if( !is_array( $group_option ) )
                    	$select_options[] = $group_option;
						
                }
            }

            $field_details['options'] = $select_options;
        }

		foreach( $field_details['options'] as $option ){

			if( is_array( $option ) ){

				if( !empty( $option['field_name'] ) )
					return $option['field_name'];

			} elseif ( strpos( $option, $value ) !== false ){
				if( strpos( $option, '%' ) === false ){
					return $value;
				} else {
					$option_parts = explode( '%', $option );
					if( !empty( $option_parts ) ){
						if( empty( $option_parts[0] ) && count( $option_parts ) == 3 ){
							$label = $option_parts[1];
							return $label;
						}
					}
				}
			}

		}

        //this was added for select-2 custom values that do not exist in the defined options
		return $value;
	}

	/* function to generate output for upload field */
	function wck_get_entry_field_upload($id){
		if( !empty ( $id ) && is_numeric( $id ) ){
			$file_src = wp_get_attachment_url($id);
			$thumbnail = wp_get_attachment_image( $id, array( 80, 60 ), true );
			$file_name = get_the_title( $id );

			if ( preg_match( '/^.*?\.(\w+)$/', get_attached_file( $id ), $matches ) )
				$file_type = esc_html( strtoupper( $matches[1] ) );
			else
				$file_type = strtoupper( str_replace( 'image/', '', get_post_mime_type( $id ) ) );

			return $display_value = '<div class="upload-field-details">'. $thumbnail .'<p><span class="file-name">'. $file_name .'</span><span class="file-type">'. $file_type . '</span></p></div>';
		} else {
			return '';
		}
	}

	/* function to generate output for user select */
	function wck_get_entry_field_user_select($id){
		if( !empty ( $id ) && is_numeric( $id ) ){
			$user = get_user_by( 'id', $id );
			if ( $user )
				return '<pre>'.htmlspecialchars( $user->display_name );
			else
				return 'Error - User ID not found in database';

		} else {
			return '';
		}
	}

	/* function to generate output for cpt select */
	function wck_get_entry_field_cpt_select($id){
		if( !empty ( $id ) && is_numeric( $id ) ){
			$post = get_post( $id );

			if ( $post != null ){
				if ( $post->post_title == '' )
					$post->post_title = 'No title. ID: ' . $id;

				return '<pre>'.htmlspecialchars( $post->post_title );
			}
			else
				return 'Error - Post ID not found in database';

		} else {
			return '';
		}
	}

	/* enque the js/css */
	function wck_print_scripts($hook){
		global $pb_wck_pages_hooknames;

		if( $this->args['context'] == 'post_meta' ) {
			if( 'post.php' == $hook || 'post-new.php' == $hook){

				/* only add on profile builder custom post types */
				if( !empty( $_GET['post'] ) )
					$post_id = filter_var( $_GET['post'], FILTER_SANITIZE_NUMBER_INT );
				else if( !empty( $_POST['post_ID'] ) )
					$post_id = filter_var( $_POST['post_ID'], FILTER_SANITIZE_NUMBER_INT );
				else
					$post_id = '';
				if( !empty( $post_id ) ){
					$current_post_type = get_post_type( $post_id );
					if( strpos( $current_post_type, 'wppb-' ) === false )
						return '';
				}

				self::wck_enqueue();
			}
		}
		elseif( $this->args['context'] == 'option' ){
			if( !empty( $pb_wck_pages_hooknames ) && isset( $this->args[ 'post_type' ] ) && isset( $pb_wck_pages_hooknames[ $this->args[ 'post_type' ] ] ) && $pb_wck_pages_hooknames[$this->args['post_type']] == $hook ){
				self::wck_enqueue( 'options' );
			}
		}
	}

	/* our own ajaxurl */
	function wck_print_ajax_url(){
		echo '<script type="text/javascript">var wppbWckAjaxurl = "'. esc_js( apply_filters( 'wck_ajax_url', admin_url('admin-ajax.php') ) ).'";</script>';
	}


	/* Helper function for enqueueing scripts and styles */
	private static function wck_enqueue( $context = '' ){

		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );
		wp_enqueue_script( 'jquery-ui-sortable' );

		if( $context == 'options' ){
			wp_enqueue_script( 'thickbox' );
			wp_enqueue_style( 'thickbox' );
		}

		wp_enqueue_script('wordpress-creation-kit', plugins_url('/wordpress-creation-kit.js', __FILE__), array('jquery', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-sortable' ), PROFILE_BUILDER_VERSION );
		wp_register_style('wordpress-creation-kit-css', plugins_url('/wordpress-creation-kit.css', __FILE__), array(), PROFILE_BUILDER_VERSION );
		wp_enqueue_style('wordpress-creation-kit-css');

		// wysiwyg
//		wp_register_script( 'wck-tinymce', plugins_url( '/assets/js/tiny_mce/tiny_mce.js', __FILE__ ), array(), '1.0', true );
//		wp_enqueue_script( 'wck-tinymce' );
//		wp_register_script( 'wck-tinymce-init', plugins_url( '/assets/js/tiny_mce/wck_tiny_mce_init.js', __FILE__ ), array(), '1.0', true );
//		wp_enqueue_script( 'wck-tinymce-init' );

        //select 2 KEEP the wppb-select2 handle so we don't include it multiple times, seeing we include it from PB as well
        wp_enqueue_script( 'wppb-select2', plugins_url( '/assets/js/select2/select2.min.js', __FILE__ ), array(), PROFILE_BUILDER_VERSION, true );
        wp_enqueue_script( 'wppb-select2-compat', WPPB_PLUGIN_URL . 'assets/js/select2-compat.js', array(), PROFILE_BUILDER_VERSION, true );
        wp_enqueue_style( 'wppb-select2-style', plugins_url( '/assets/js/select2/select2.min.css', __FILE__ ), false, PROFILE_BUILDER_VERSION );

		//datepicker
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_style( 'jquery-style', plugins_url( '/assets/datepicker/datepicker.css', __FILE__ ) );


        /* media upload */
        wp_enqueue_media();
        wp_enqueue_script('wck-upload-field', plugins_url('/fields/upload.js', __FILE__), array('jquery') );

	}

	/* Helper function for required fields */
	function wck_test_required( $meta_array, $meta, $values, $id ){
        $fields = apply_filters( 'wck_before_test_required', $meta_array, $meta, $values, $id );
		$required_fields = array();
		$required_fields_with_errors = array();
		$required_message = '';

		$errors = '';

		if( !empty( $fields ) ){
			foreach( $fields as $field ){
				if( !empty( $field['required'] ) && $field['required'] )
					$required_fields[Wordpress_Creation_Kit_PB::wck_generate_slug( $field['title'], $field )] = $field['title'];
			}
		}

		if( !empty( $values ) ){
			foreach( $values as $key => $value ){
				if( array_key_exists( $key, $required_fields ) && apply_filters( "wck_required_test_{$meta}_{$key}", empty( $value ), $value, $id ) ){
					$required_message .= apply_filters( "wck_required_message_{$meta}_{$key}", __( "Please enter a value for the required field ", "profile-builder" ) . "$required_fields[$key] \n", $value );
					$required_fields_with_errors[] = $key;
				}
			}
		}

		$required_message .= apply_filters( "wck_extra_message", "", $fields, $required_fields, $meta, $values, $id );
		$required_fields_with_errors = apply_filters( "wck_required_fields_with_errors", $required_fields_with_errors, $fields, $required_fields, $meta, $value, $id );

		if( $required_message != '' ){
			$errors = array( 'error' => $required_message, 'errorfields' => $required_fields_with_errors );
		}

		return $errors;
	}

	/* Checks to see wether the current user can modify data */
	function wck_verify_user_capabilities( $context, $meta = '', $id = 0 ) {

		$return = true;

		// Meta is an option
		if( $context == 'option' && !current_user_can( 'manage_options' ) )
			$return = false;

		// Meta is post related
		if( $context == 'post_meta' && is_user_logged_in() ) {

			// Current user must be able to edit posts
			if( !current_user_can( 'edit_posts' ) )
				$return = false;

			// If the user can't edit others posts the current post must be his/hers
			elseif( !current_user_can( 'edit_others_posts' ) ) {

				$current_post = get_post( $id );
				$current_user = wp_get_current_user();

				if( $current_user->ID != $current_post->post_author )
					$return = false;

			}

		}

		// Return
		if( $return )
			return $return;
		else
			return array( 'error' => __( 'You are not allowed to do this.', 'profile-builder' ), 'errorfields' => '' );

	}


	/* ajax add a reccord to the meta */
	function wck_add_meta(){
		check_ajax_referer( "wck-add-meta" );
		if( !empty( $_POST['meta'] ) )
			$meta = sanitize_text_field( $_POST['meta'] );
		else
			$meta = '';
		if( !empty( $_POST['id'] ) )
			$id = absint($_POST['id']);
		else
			$id = '';
		if( !empty( $_POST['values'] ) && is_array( $_POST['values'] ) )
			$values = array_map( 'wppb_sanitize_value', $_POST['values'] );//phpcs:ignore  WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		else
			$values = array();

		// Security checks
		if( true !== ( $error = self::wck_verify_user_capabilities( $this->args['context'], $meta, $id ) ) ) {
			header( 'Content-type: application/json' );
			die( json_encode( $error ) );
		}

		$values = apply_filters( "wck_add_meta_filter_values_{$meta}", $values );

		/* check required fields */
		$errors = self::wck_test_required( $this->args['meta_array'], $meta, $values, $id );
		if( $errors != '' ){
			header( 'Content-type: application/json' );
			die( json_encode( $errors ) );
		}


		if( $this->args['context'] == 'post_meta' )
			$results = get_post_meta($id, $meta, true);
		else if ( $this->args['context'] == 'option' )
			$results = get_option( apply_filters( 'wck_option_meta' , $meta, $values ) );

		/* we need an array here */
		if( empty( $results ) && !is_array( $results ) )
			$results = array();

        /* for single metaboxes owerwrite entries each time so we have a maximum of one */
        if( $this->args['single'] )
            $results = array( $values );
        else
            $results[] = $values;

		/* make sure this does not output anything so it won't break the json response below
		will keep it do_action for compatibility reasons
		 */
		ob_start();
			do_action( 'wck_before_add_meta', $meta, $id, $values );
		$wck_before_add_meta = ob_get_clean(); //don't output it


		if( $this->args['context'] == 'post_meta' )
			update_post_meta($id, $meta, $results);
		else if ( $this->args['context'] == 'option' )
			update_option( apply_filters( 'wck_option_meta' , $meta, $results ), wp_unslash( $results ) );

		/* if unserialize_fields is true add for each entry separate post meta for every element of the form  */
		if( $this->args['unserialize_fields'] && $this->args['context'] == 'post_meta' ){

			$meta_suffix = count( $results );
			if( !empty( $values ) ){
				foreach( $values as $name => $value ){
					update_post_meta($id, $meta.'_'.$name.'_'.$meta_suffix, $value);
				}
			}
		}

		$entry_list = $this->wck_refresh_list( $meta, $id );
		$add_form = $this->wck_add_form( $meta, $id );

		header( 'Content-type: application/json' );
		die( json_encode( array( 'entry_list' => $entry_list, 'add_form' => $add_form ) ) );

	}

	/* ajax update a reccord in the meta */
	function wck_update_meta(){
		check_ajax_referer( "wck-update-entry" );
		if( !empty( $_POST['meta'] ) )
			$meta = sanitize_text_field( $_POST['meta'] );
		else
			$meta = '';
		if( !empty( $_POST['id'] ) )
			$id = absint($_POST['id']);
		else
			$id = '';
		if( isset( $_POST['element_id'] ) )
			$element_id = absint( $_POST['element_id'] );
		else
			$element_id = 0;
		if( !empty( $_POST['values'] ) && is_array( $_POST['values']) )
			$values = array_map( 'wppb_sanitize_value', $_POST['values'] ); //phpcs:ignore  WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		else
			$values = array();

		// Security checks
		if( true !== ( $error = self::wck_verify_user_capabilities( $this->args['context'], $meta, $id ) ) ) {
			header( 'Content-type: application/json' );
			die( json_encode( $error ) );
		}

		$values = apply_filters( "wck_update_meta_filter_values_{$meta}", $values, $element_id );

		/* check required fields */
		$errors = self::wck_test_required( $this->args['meta_array'], $meta, $values, $id );
		if( $errors != '' ){
			header( 'Content-type: application/json' );
			die( json_encode( $errors ) );
		}

		if( $this->args['context'] == 'post_meta' )
			$results = get_post_meta($id, $meta, true);
		else if ( $this->args['context'] == 'option' )
			$results = get_option( apply_filters( 'wck_option_meta' , $meta, $values, $element_id ) );

		$results[$element_id] = $values;

		/* make sure this does not output anything so it won't break the json response below
		will keep it do_action for compatibility reasons
		 */
		ob_start();
			do_action( 'wck_before_update_meta', $meta, $id, $values, $element_id );
		$wck_before_update_meta = ob_get_clean(); //don't output it


		if( $this->args['context'] == 'post_meta' )
			update_post_meta($id, $meta, $results);
		else if ( $this->args['context'] == 'option' )
			update_option( apply_filters( 'wck_option_meta' , $meta, $results, $element_id ), wp_unslash( $results ) );

		/* if unserialize_fields is true update the coresponding post metas for every element of the form  */
		if( $this->args['unserialize_fields'] && $this->args['context'] == 'post_meta' ){

			$meta_suffix = $element_id + 1;
			if( !empty( $values ) ){
				foreach( $values as $name => $value ){
					update_post_meta($id, $meta.'_'.$name.'_'.$meta_suffix, $value);
				}
			}
		}

		$entry_content = $this->wck_refresh_entry( $meta, $id, $element_id );

		header( 'Content-type: application/json' );
		die( json_encode( array( 'entry_content' => $entry_content ) ) );
	}

	/* ajax to refresh the meta content | or used in other function to return the */
	/* this is used in Repeater Fields as an ajax action so we have to keep it dual purpose */
	function wck_refresh_list( $meta = '', $id = '' ){
		if( isset( $_POST['meta'] ) )
			$meta = sanitize_text_field( $_POST['meta'] );

		if( isset( $_POST['id'] ) )
			$id = absint($_POST['id']);

		ob_start();
			echo self::wck_output_meta_content($meta, $id, $this->args['meta_array']); //phpcs:ignore  WordPress.Security.EscapeOutput.OutputNotEscaped
			do_action( "wck_refresh_list_{$meta}", $id );
		$entry_list = ob_get_clean();

		if( strpos( current_filter(), 'wp_ajax_wck_refresh_list') === 0 ){
			echo $entry_list; //phpcs:ignore  WordPress.Security.EscapeOutput.OutputNotEscaped
			exit;
		}
		else{
			return $entry_list;
		}
	}

	/* function that returns the content of an entry */
	function wck_refresh_entry( $meta = '', $id = '', $element_id = '' ){

		if( $this->args['context'] == 'post_meta' )
			$results = get_post_meta($id, $meta, true);
		else if ( $this->args['context'] == 'option' )
			$results = get_option( apply_filters( 'wck_option_meta' , $meta, $element_id ) );

		ob_start();
			echo self::wck_output_entry_content( $meta, $id, $this->args['meta_array'], $results, $element_id ); //phpcs:ignore  WordPress.Security.EscapeOutput.OutputNotEscaped
			do_action( "wck_refresh_entry_{$meta}", $id );
		$entry_content = ob_get_clean();

		return $entry_content;
	}

	/* function that returns the add the form for single */
	function wck_add_form( $meta = '', $id = '' ){

		$post = get_post($id);

		ob_start();
			self::create_add_form($this->args['meta_array'], $meta, $post );
			do_action( "wck_ajax_add_form_{$meta}", $id );
		$add_form = ob_get_clean();

		return $add_form;
	}


	/* ajax to show the update form */
	function wck_show_update_form(){
		check_ajax_referer( "wck-edit-entry" );
		$meta = isset(  $_POST['meta'] ) ? sanitize_text_field( $_POST['meta'] ) : '';
		$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : '';
		$element_id = isset( $_POST['element_id'] ) ? absint( $_POST['element_id'] ) : '';

        do_action( "wck_before_adding_form_{$meta}", $id, $element_id );

		echo self::mb_update_form($this->args['meta_array'], $meta, $id, $element_id);//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		do_action( "wck_after_adding_form", $meta, $id, $element_id );
        do_action( "wck_after_adding_form_{$meta}", $id, $element_id );

		exit;
	}

	/* ajax to remove a reccord from the meta */
	function wck_remove_meta(){
		check_ajax_referer( "wck-delete-entry" );
		if( !empty( $_POST['meta'] ) )
			$meta = sanitize_text_field( $_POST['meta'] );
		else
			$meta = '';
		if( !empty( $_POST['id'] ) )
			$id = absint( $_POST['id'] );
		else
			$id = '';
		if( isset( $_POST['element_id'] ) )
			$element_id = absint( $_POST['element_id'] );
		else
			$element_id = '';

		// Security checks
		if( true !== ( $error = self::wck_verify_user_capabilities( $this->args['context'], $meta, $id ) ) ) {
			header( 'Content-type: application/json' );
			die( json_encode( $error ) );
		}

		if( $this->args['context'] == 'post_meta' )
			$results = get_post_meta($id, $meta, true);
		else if ( $this->args['context'] == 'option' )
			$results = get_option( apply_filters( 'wck_option_meta' , $meta, $element_id ) );

		$old_results = $results;
		unset($results[$element_id]);
		/* reset the keys for the array */
		$results = array_values($results);

		/* make sure this does not output anything so it won't break the json response below
		will keep it do_action for compatibility reasons
		 */
		ob_start();
			do_action( 'wck_before_remove_meta', $meta, $id, $element_id );
		$wck_before_remove_meta = ob_get_clean(); //don't output it

		if( $this->args['context'] == 'post_meta' )
			update_post_meta($id, $meta, $results);
		else if ( $this->args['context'] == 'option' )
			update_option( apply_filters( 'wck_option_meta' , $meta, $results, $element_id ), wp_unslash( $results ) );



		/* TODO: optimize so that it updates from the deleted element forward */
		/* if unserialize_fields is true delete the coresponding post metas */
		if( $this->args['unserialize_fields'] && $this->args['context'] == 'post_meta' ){

			$meta_suffix = 1;

			if( !empty( $results ) ){
				foreach( $results as $result ){
					foreach ( $result as $name => $value){
						update_post_meta($id, $meta.'_'.$name.'_'.$meta_suffix, $value);
					}
					$meta_suffix++;
				}
			}

			if( count( $results ) == 0 )
				$results = $old_results;

			if( !empty( $results ) ){
				foreach( $results as $result ){
					foreach ( $result as $name => $value){
						delete_post_meta( $id, $meta.'_'.$name.'_'.$meta_suffix );
					}
					break;
				}
			}
		}

		$entry_list = $this->wck_refresh_list( $meta, $id );
		$add_form = $this->wck_add_form( $meta, $id );

		header( 'Content-type: application/json' );
		die( json_encode( array( 'entry_list' => $entry_list, 'add_form' => $add_form ) ) );
	}


	/* ajax to reorder records */
	function wck_reorder_meta(){
		if( !empty( $_POST['meta'] ) )
			$meta = sanitize_text_field( $_POST['meta'] );
		else
			$meta = '';
		if( !empty( $_POST['id'] ) )
			$id = absint($_POST['id']);
		else
			$id = '';
		if( !empty( $_POST['values'] ) && is_array( $_POST['values'] ) )
			$elements_id = array_map( 'absint', $_POST['values'] );
		else
			$elements_id = array();

		// Security checks
		if( true !== ( $error = self::wck_verify_user_capabilities( $this->args['context'], $meta, $id ) ) ) {
			header( 'Content-type: application/json' );
			die( json_encode( $error ) );
		}

		/* make sure this does not output anything so it won't break the json response below
		will keep it do_action for compatibility reasons
		 */
		ob_start();
			do_action( 'wck_before_reorder_meta', $meta, $id, $elements_id );
		$wck_before_reorder_meta = ob_get_clean(); //don't output it

		if( $this->args['context'] == 'post_meta' )
			$results = get_post_meta($id, $meta, true);
		else if ( $this->args['context'] == 'option' )
			$results = get_option( apply_filters( 'wck_option_meta' , $meta ) );

		$new_results = array();
		if( !empty( $elements_id ) ){
			foreach($elements_id as $element_id){
				$new_results[] = $results[$element_id];
			}
		}

		$results = $new_results;

		if( $this->args['context'] == 'post_meta' )
			update_post_meta($id, $meta, $results);
		else if ( $this->args['context'] == 'option' )
			update_option( apply_filters( 'wck_option_meta' , $meta, $results, $element_id ), wp_unslash( $results ) );


		/* if unserialize_fields is true reorder all the coresponding post metas  */
		if( $this->args['unserialize_fields'] && $this->args['context'] == 'post_meta' ){

			$meta_suffix = 1;
			if( !empty( $new_results ) ){
				foreach( $new_results as $result ){
					foreach ( $result as $name => $value){
						update_post_meta($id, $meta.'_'.$name.'_'.$meta_suffix, $value);
					}
					$meta_suffix++;
				}
			}

		}

		$entry_list = $this->wck_refresh_list( $meta, $id );
		header( 'Content-type: application/json' );
		die( json_encode( array( 'entry_list' => $entry_list ) ) );
	}

    /**
     * Function that saves the entries for single forms on posts(no options). It is hooke on the 'save_post' hook
     * It is executed on each WCK object instance so we need to restrict it on only the ones that are present for that post
     */
    function wck_save_single_metabox( $post_id, $post ){
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return $post_id;

		/* only go through for metaboxes defined for this post type */
		if( get_post_type( $post_id ) != $this->args['post_type'] )
			return $post_id;

        // Check the user's permissions.
        if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            }
        } else {
            if ( ! current_user_can( 'edit_posts', $post_id ) ) {
                return $post_id;
            }
        }

        if( !empty( $_POST ) ){
            /* for single metaboxes we save a hidden input that contains the meta_name attr as a key so we need to search for it */
            foreach( $_POST as $request_key => $request_value ){
                if( strpos( $request_key, '_wckmetaname_' ) !== false && strpos( $request_key, '#wck' ) !== false ){
                    /* found it so now retrieve the meta_name from the key formatted _wckmetaname_actuaname#wck */
                    $request_key = str_replace( '_wckmetaname_', '', $request_key );
                    $meta_name = sanitize_text_field( str_replace( '#wck', '', $request_key ) );
                    /* we have it so go through only on the WCK object instance that has this meta_name */
                    if( $this->args['meta_name'] == $meta_name ){

                        /* get the meta values from the $_POST and store them in an array */
                        $meta_values = array();
                        if( !empty( $this->args['meta_array'] ) ){
                            foreach ($this->args['meta_array'] as $meta_field){
                                /* in the $_POST the names for the fields are prefixed with the meta_name for the single metaboxes in case there are multiple metaboxes that contain fields wit hthe same name */
                                $single_field_name = $this->args['meta_name'] .'_'. Wordpress_Creation_Kit_PB::wck_generate_slug( $meta_field['title'],$meta_field );
                                if (isset($_POST[$single_field_name])) {
                                    /* checkbox needs to be stored as string not array */
                                    if( $meta_field['type'] == 'checkbox' )
                                        $_POST[$single_field_name] = implode( ', ', $_POST[$single_field_name] );//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

                                    $meta_values[Wordpress_Creation_Kit_PB::wck_generate_slug( $meta_field['title'], $meta_field )] = wppb_sanitize_value( $_POST[$single_field_name] ); //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
                                }
                                else
                                    $meta_values[Wordpress_Creation_Kit_PB::wck_generate_slug( $meta_field['title'], $meta_field )] = '';
                            }
                        }

                        /* test if we have errors for the required fields */
                        $errors = self::wck_test_required( $this->args['meta_array'], $meta_name, $meta_values, $post_id );
                        if( !empty( $errors ) ){
                            /* if we have errors then add them in the global. We do this so we get all errors from all single metaboxes that might be on that page */
                            global $wck_single_forms_errors;
                            if( !empty( $errors['errorfields'] ) ){
                                foreach( $errors['errorfields'] as $key => $field_name ){
                                    $errors['errorfields'][$key] = $this->args['meta_name']. '_' .$field_name;
                                }
                            }
                            $wck_single_forms_errors[] = $errors;
                        }
                        else {
                            /* no errors so we can save */
                            update_post_meta($post_id, $meta_name, array($meta_values));
                            /* handle unserialized fields */
                            if ($this->args['unserialize_fields']) {
                                if (!empty($this->args['meta_array'])) {
                                    foreach ($this->args['meta_array'] as $meta_field) {
                                        update_post_meta($post_id, $meta_name . '_' . Wordpress_Creation_Kit_PB::wck_generate_slug( $meta_field['title'], $meta_field ) . '_1', array_map( 'wppb_sanitize_value', $_POST[$this->args['meta_name'] . '_' . Wordpress_Creation_Kit_PB::wck_generate_slug( $meta_field['title'], $meta_field )] ) ); //phpcs:ignore
                                    }
                                }
                            }
                        }
                        break;
                    }
                }
            }
        }
    }

    /**
     * Function that checks if we have any errors in the required fields from the single metaboxes. It is executed on 'wp_insert_post' hook
     * that comes after 'save_post' so we should have the global errors by now. If we have errors perform a redirect and add the error messages and error fields
     * in the url
     */
    function wck_single_metabox_redirect_if_errors( $post_id, $post ){
        global $wck_single_forms_errors;
        if( !empty( $wck_single_forms_errors ) ) {
            $error_messages = '';
            $error_fields = '';
            foreach( $wck_single_forms_errors as $wck_single_forms_error ){
                $error_messages .= $wck_single_forms_error['error'];
                $error_fields .= implode( ',', $wck_single_forms_error['errorfields'] ).',';
            }
            if( isset( $_SERVER["HTTP_REFERER"] ) )
                wp_safe_redirect( add_query_arg( array( 'wckerrormessages' => base64_encode( urlencode( $error_messages ) ), 'wckerrorfields' => base64_encode( urlencode( $error_fields ) ) ), esc_url_raw( $_SERVER["HTTP_REFERER"] ) ) );

            exit;
        }
    }

    /** Function that displays the error messages, if we have any, as js alerts and marks the fields with red
     */
    function wck_single_metabox_errors_display(){
        /* only execute for the WCK objects defined for the current post type */
        global $post;
        if( get_post_type( $post ) != $this->args['post_type'] )
            return;

        /* and only do it once */
        global $allready_saved;
        if( isset( $allready_saved ) && $allready_saved == true )
            return;
        $allready_saved = true;

        /* mark the fields */
        if( isset( $_GET['wckerrorfields'] ) && !empty( $_GET['wckerrorfields'] ) ){
            echo '<script type="text/javascript">';
            $field_names = explode( ',', sanitize_text_field( urldecode( base64_decode( $_GET['wckerrorfields'] ) ) ) );//phpcs:ignore  WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			if( !empty( $field_names ) ) {
				foreach ($field_names as $field_name) {
					echo "jQuery( '.field-label[for=\"" . esc_js($field_name) . "\"]' ).addClass('error');";
				}
			}
            echo '</script>';
        }

        /* alert the error messages */
        if( isset( $_GET['wckerrormessages'] ) ){
            echo '<script type="text/javascript">alert("'.  str_replace( '%0A', '\n', esc_js( urldecode( base64_decode( $_GET['wckerrormessages'] ) ) ) ) .'")</script>';//phpcs:ignore
        }
    }


	/**
	 * The function used to generate slugs in WCK
	 *
	 * @since 1.1.1
	 *
	 * @param string $string The input string from which we generate the slug
	 * @return string $slug The henerated slug
	 */
	static function wck_generate_slug( $string, $details = array() ){
        if( !empty( $details['slug'] ) )
            $slug = $details['slug'];
        else
		    $slug = rawurldecode( sanitize_title_with_dashes( remove_accents( $string ) ) );
		return $slug;
	}

	static function wck_strip_script_tags(){

	}

    static function wck_generate_select_option($option, $values, $i, $current_value){

		$disabled = '';

		if( is_array( $option ) ){

			if( !empty( $option['field_name'] ) ){
				$label = $option['field_name'];

				if( !empty( $values ) )
					$value_attr = $values[$i];
				else
					$value_attr = $option;
				
				if( !empty( $option['disabled'] ) && $option['disabled'] == true ){
					$disabled = ' disabled';
				}
			}

		} elseif( strpos( $option, '%' ) === false ){
            $label = $option;
            if( !empty( $values ) )
                $value_attr = $values[$i];
            else
                $value_attr = $option;
        } else {
            $option_parts = explode( '%', $option );
            if( !empty( $option_parts ) ){
                if( empty( $option_parts[0] ) && count( $option_parts ) == 3 ){
                    $label = $option_parts[1];
                    if( !empty( $values ) )
                        $value_attr = $values[$i];
                    else
                        $value_attr = $option_parts[2];
                }
                else{
                    $label = $option;
                    if( !empty( $values ) )
                        $value_attr = $values[$i];
                    else
                        $value_attr = $option;
                }
            }
        }

		// title is set only for disabled options to let users know those fields are available in a paid version
		if( !empty( $disabled ) ){
			if( isset( $value_attr['field_name'] ) && $value_attr['field_name'] == 'Subscription Plans' )
				$title = esc_attr( __( 'Install the free Paid Member Subscriptions plugin to get access this field.', 'profile-builder' ) );
			else
				$title = esc_attr( __( 'This field is available in our paid plans.', 'profile-builder' ) );
		}

		if( isset( $value_attr['field_name'] ) )
        	$optionOutput = '<option value=""  '. esc_attr( $disabled ) . ( !empty( $disabled ) ? ' title="'. $title .'"'  : '' ) . ' >'. esc_html( $label ) .'</option>';
		else
        	$optionOutput = '<option value="'. esc_attr( $value_attr ) .'"  '. selected( $value_attr, $current_value, false ) . ( !empty( $disabled ) ? ' title="'. $title .'"'  : '' ) . ' >'. esc_html( $label ) .'</option>';

        return $optionOutput;
    }

    static function wck_is_multi($a) {
        foreach ($a as $v) {
            if (is_array($v)) return true;
        }
        return false;
    }
}


/*
Helper class that creates admin menu pages ( both top level menu pages and submenu pages )
Default Usage:

$args = array(
			'page_type' => 'menu_page',
			'page_title' => '',
			'menu_title' => '',
			'capability' => '',
			'menu_slug' => '',
			'icon_url' => '',
			'position' => '',
			'parent_slug' => ''
		);

'page_type'		(string) (required) The type of page you want to add. Possible values: 'menu_page', 'submenu_page'
'page_title' 	(string) (required) The text to be displayed in the title tags and header of
				the page when the menu is selected
'menu_title'	(string) (required) The on-screen name text for the menu
'capability'	(string) (required) The capability required for this menu to be displayed to
				the user.
'menu_slug'	    (string) (required) The slug name to refer to this menu by (should be unique
				for this menu).
'icon_url'	    (string) (optional for 'page_type' => 'menu_page') The url to the icon to be used for this menu.
				This parameter is optional. Icons should be fairly small, around 16 x 16 pixels
				for best results.
'position'	    (integer) (optional for 'page_type' => 'menu_page') The position in the menu order this menu
				should appear.
				By default, if this parameter is omitted, the menu will appear at the bottom
				of the menu structure. The higher the number, the lower its position in the menu.
				WARNING: if 2 menu items use the same position attribute, one of the items may be
				overwritten so that only one item displays!
'parent_slug' 	(string) (required for 'page_type' => 'submenu_page' ) The slug name for the parent menu
				(or the file name of a standard WordPress admin page) For examples see http://codex.wordpress.org/Function_Reference/add_submenu_page $parent_slug parameter
'priority'	    (int) (optional) How important your function is. Alter this to make your function
				be called before or after other functions. The default is 10, so (for example) setting it to 5 would make it run earlier and setting it to 12 would make it run later.

public $hookname ( for required for 'page_type' => 'menu_page' ) string used internally to
				 track menu page callbacks for outputting the page inside the global $menu array
				 ( for required for 'page_type' => 'submenu_page' ) The resulting page's hook_suffix,
				 or false if the user does not have the capability required.
*/

class WCK_Page_Creator_PB{

	private $defaults = array(
							'page_type' => 'menu_page',
							'page_title' => '',
							'menu_title' => '',
							'capability' => '',
							'menu_slug' => '',
							'icon_url' => '',
							'position' => '',
							'parent_slug' => '',
							'priority' => 10,
							'network_page' => false
						);
	private $args;
	public $hookname;


	/* Constructor method for the class. */
	function __construct( $args ) {

		/* Global that will hold all the arguments for all the menu pages */
		global $wck_pages;

		/* Merge the input arguments and the defaults. */
		$this->args = wp_parse_args( $args, $this->defaults );

		/* Add the settings for this page to the global object */
		$wck_pages[$this->args['page_title']] = $this->args;

		if( !$this->args['network_page'] ){
			/* Hook the page function to 'admin_menu'. */
			add_action( 'admin_menu', array( &$this, 'wck_page_init' ), $this->args['priority'] );
		}
		else{
			/* Hook the page function to 'admin_menu'. */
			add_action( 'network_admin_menu', array( &$this, 'wck_page_init' ), $this->args['priority'] );
		}
	}

	/**
	 * Function that creates the admin page
	 */
	function wck_page_init(){
		global $pb_wck_pages_hooknames;

        /* don't add the page at all if the user doesn't meet the capabilities */
        if( !empty( $this->args['capability'] ) ){
            if( !current_user_can( $this->args['capability'] ) )
                return;
        }

		/* Create the page using either add_menu_page or add_submenu_page functions depending on the 'page_type' parameter. */
		if( $this->args['page_type'] == 'menu_page' ){
			$this->hookname = add_menu_page( $this->args['page_title'], $this->args['menu_title'], $this->args['capability'], $this->args['menu_slug'], array( &$this, 'wck_page_template' ), $this->args['icon_url'], $this->args['position'] );

			$pb_wck_pages_hooknames[$this->args['menu_slug']] = $this->hookname;
		}
		else if( $this->args['page_type'] == 'submenu_page' ){
			$this->hookname = add_submenu_page( $this->args['parent_slug'], $this->args['page_title'], $this->args['menu_title'], $this->args['capability'], $this->args['menu_slug'], array( &$this, 'wck_page_template' ) );

			$pb_wck_pages_hooknames[$this->args['menu_slug']] = $this->hookname;
		}

		do_action( 'WCK_Page_Creator_PB_after_init', $this->hookname );

		/* Create a hook for adding meta boxes. */
		add_action( "load-{$this->hookname}", array( &$this, 'wck_settings_page_add_meta_boxes' ) );
		/* Load the JavaScript needed for the screen. */
		add_action( 'admin_enqueue_scripts', array( &$this, 'wck_page_enqueue_scripts' ) );
		add_action( "admin_head-{$this->hookname}", array( &$this, 'wck_page_load_scripts' ) );
	}

	/**
	 * Do action 'add_meta_boxes'. This hook isn't executed by default on a admin page so we have to add it.
	 */
	function wck_settings_page_add_meta_boxes() {
        global $post;
		do_action( 'add_meta_boxes', $this->hookname, $post );
	}

	/**
	 * Loads the JavaScript files required for managing the meta boxes on the theme settings
	 * page, which allows users to arrange the boxes to their liking.
	 *
	 * @global string $bareskin_settings_page. The global setting page (returned by add_theme_page in function
	 * bareskin_settings_page_init ).
	 * @since 1.0.0
	 * @param string $hook The current page being viewed.
	 */
	function wck_page_enqueue_scripts( $hook ) {
		if ( $hook == $this->hookname ) {
			wp_enqueue_script( 'common' );
			wp_enqueue_script( 'wp-lists' );
			wp_enqueue_script( 'postbox' );
		}
	}

	/**
	 * Loads the JavaScript required for toggling the meta boxes on the theme settings page.
	 *
	 * @global string $bareskin_settings_page. The global setting page (returned by add_theme_page in function
	 * bareskin_settings_page_init ).
	 * @since 1.0.0
	 */
	function wck_page_load_scripts() {
		?>
		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready( function($) {
				$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
				postboxes.add_postbox_toggles( '<?php echo esc_js( $this->hookname ); ?>' );
			});
			//]]>
		</script><?php
	}

	/**
	 * Outputs default template for the page. It contains placeholders for metaboxes. It also
	 * provides two action hooks 'wck_before_meta_boxes' and 'wck_after_meta_boxes'.
	 */
	function wck_page_template(){
		?>
		<div class="wrap">

			<?php if( !empty( $this->args['page_icon'] ) ): ?>
			<div id="<?php echo esc_attr( $this->args['menu_slug'] );//phpcs:ignore ?>-icon" style="background: url('<?php echo $this->args['page_icon']; ?>') no-repeat;" class="icon32">
				<br/>
			</div>
			<?php endif; ?>

			<h2><?php echo esc_html( $this->args['page_title'] ) ?></h2>

			<div id="poststuff">

				<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
				<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>

				<?php do_action( 'wck_before_meta_boxes', $this->hookname ); ?>

				<div class="metabox-holder">
					<div class="wck-post-body">
						<div class="post-box-container column-1 normal">
							<?php do_action( 'wck_before_column1_metabox_content', $this->hookname ); ?>
							<?php do_meta_boxes( $this->hookname, 'normal', null ); ?>
							<?php do_action( 'wck_after_column1_metabox_content', $this->hookname ); ?>
						</div>
						<div class="post-box-container column-3 advanced">
							<?php do_action( 'wck_before_column3_metabox_content', $this->hookname ); ?>
							<?php do_meta_boxes( $this->hookname, 'advanced', null ); ?>
							<?php do_action( 'wck_after_column3_metabox_content', $this->hookname ); ?>
						</div>
					</div>
                    <div class="post-box-container column-2 side"><?php do_meta_boxes( $this->hookname, 'side', null ); ?></div>

				</div>

				<?php do_action( 'wck_after_meta_boxes', $this->hookname ); ?>

			</div><!-- #poststuff -->

		</div><!-- .wrap -->
		<?php
	}
}



?>
