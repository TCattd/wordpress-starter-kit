<?php
/**
 * @param array() $mustache_vars is the array containing the names of the variable that must be proccessed by mustache in the template. The array must be in this form:
 * array( array( 'name' => '', 'type' => '' ) ... ), and for loop tags it also contains a 'children' element which contains other mustache_vars array(  array( 'name' => 'users', 'type' => 'loop_tag', 'children' => $merge_tags  )
 * @param string $template the template that needs to be procesed
 * @param array $extra_values in this array we can pass any variables that are required for that specific implementation of the mustache processing system
 *
 */
class PB_Mustache_Generate_Template{
	var $mustache_vars = array( );
	var $template = '';
	var $extra_values = array( );

	/**
	 * constructor for the class
	 *
	 * @param array $mustache_vars the array of variables
	 * @param string $template the html template
	 * @param $extra_values in this array we can pass any variables that are required for that specific implementation of the mustache processing system
	 *
	 * @since 2.0.0
	 *
	 */
	function __construct( $mustache_vars, $template, $extra_values ){

		// Include Mustache Templates
		if( !class_exists( 'Mustache_Autoloader' ) ){

			if( !defined( 'WPPB_PAID_PLUGIN_DIR' ) || ( defined( 'WPPB_PAID_PLUGIN_DIR' ) && defined( 'PROFILE_BUILDER_PAID_VERSION' ) ) ){
				if( file_exists( WPPB_PLUGIN_DIR . '/assets/lib/Mustache/Autoloader.php' ) )
					require_once( WPPB_PLUGIN_DIR.'/assets/lib/Mustache/Autoloader.php' );
			} else if( defined( 'WPPB_PAID_PLUGIN_DIR' ) ){
				if( file_exists( WPPB_PAID_PLUGIN_DIR . '/assets/lib/Mustache/Autoloader.php' ) )
					require_once( WPPB_PAID_PLUGIN_DIR.'/assets/lib/Mustache/Autoloader.php' );
			}

		}

		Mustache_Autoloader::register();

		$this->mustache_vars = $mustache_vars;
		$this->template = $template;
		$this->extra_values = $extra_values;

		$this->process_mustache_vars();
	}

	/**
	 * construct the mustache_vars_array from $mustache_vars through filters
	 *
	 * @since 2.0.0
	 *
	 */
	function process_mustache_vars(){
		if( !empty( $this->mustache_vars ) ){
			foreach( $this->mustache_vars as $var ){
				foreach( $var['variables'] as $variables ){
					if( empty( $variables['children'] ) )
						$variables['children'] = array();
					$this->mustache_vars_array[ $variables['name'] ] = apply_filters( 'mustache_variable_'. $variables['type'], '', $variables['name'], $variables['children'], $this->extra_values );
				}
			}
		}
	}


	/**
	 * Process the mustache template from the html template and the processed mustache variable array.
	 *
	 * @since 2.0.0
	 *
	 * @return $content the proccessed template ready for output.
	 */
    function process_template(){
        $m = new Mustache_Engine;
        try {
            if( !empty( $this->mustache_vars_array ) ){
                foreach( $this->mustache_vars_array  as $key => $value ){
                    if( is_string( $value ) ) {
                        $this->mustache_vars_array[$key] = str_replace('[', '&#91;', $value);
                        $this->mustache_vars_array[$key] = str_replace(']', '&#93;', $value);
                    }
                }
            }
            $content = do_shortcode( $m->render( $this->template, $this->mustache_vars_array ) );
        } catch (Exception $e) {
            $content = $e->getMessage();
        }

        return apply_filters( 'wppb_mustache_template', $content );
    }

	/**
	 * Handle toString method
	 *
	 * @since 2.0.0
	 *
	 * @return string $html html for the template.
	 */
	public function __toString() {
		$html = $this->process_template();
		return "{$html}";
	}


}


class PB_Mustache_Generate_Admin_Box{

	var $id; // string meta box id
	var $title; // string title
	var $page; // string|array post type to add meta box to
	var $priority;
	var $mustache_vars;
	var $default_value;


	/**
	 * constructor for the class
	 *
	 * @param string $id the meta box id
	 * @param string $title the title of the metabox
	 * @param string|array $page post type to add meta box to
	 * @param string $priority the priority within the context where the boxes should show ('high', 'core', 'default' or 'low')
	 * @param array $mustache_vars is the array containing the names of the variable that must be proccessed by mustache in the template. The array must be in this form:
	 * array( array( 'name' => '', 'type' => '' ) ... ), and for loop tags it also contains a 'children' element which contains other mustache_vars array(  array( 'name' => 'users', 'type' => 'loop_tag', 'children' => $merge_tags  )
	 * @param string $default_value the default template that populates the codemirror box.
	 *
	 * @since 2.0.0
	 *
	 */
	function __construct( $id, $title, $page, $priority, $mustache_vars, $default_value = '', $fields = array() ){

		$this->mustache_vars = $mustache_vars;

		$this->id = $id;
		$this->title = $title;
		$this->page = $page;
		$this->priority = $priority;
		$this->default_value = $default_value;

		if(!is_array($this->page)) {
			$this->page = array($this->page);
		}

		if( empty( $fields ) ){
			$this->fields = array(
				array( // Textarea
					'label'	=> '', // <label>
					'desc'	=> '', // description
					'id'	=> $id, // field id and name
					'type'	=> 'textarea', // type of field
					'default'	=> $default_value, // type of field
				)
			);
		}
		else{
			$this->fields = $fields;
		}

        $this->save_default_values();

        if( defined( 'DOING_AJAX' ) && DOING_AJAX )
            return;

		add_action( 'add_meta_boxes', array( $this, 'add_box' ) );
		add_action( 'save_post',  array( $this, 'save_box' ), 11, 2);
		add_action( 'wp_insert_post',  array( $this, 'save_box' ), 11, 2);
		add_action( 'admin_enqueue_scripts', array( $this, 'wppb_print_mustache_script' ) );
		add_action( 'admin_head', array( $this, 'wppb_print_codemirror_script' ) );

		add_action( 'wck_before_meta_boxes', array( $this, 'wppb_mustache_page_before' ) );
		add_action( 'wck_after_meta_boxes', array( $this, 'wppb_mustache_page_after' ) );
	}

	/**
	 * Function that print the required scripts for the mustache templates
	 *
	 * @param string $hook the page hook
	 *
	 * @since 2.0.0
	 *
	 */
	function wppb_print_mustache_script( $hook ){
		if ( isset( $_GET['post_type'] ) || isset( $_GET['post'] ) || isset( $_GET['page'] ) ){
			if ( isset( $_GET['post_type'] ) )
				$post_type = sanitize_text_field( $_GET['post_type'] );

			elseif ( isset( $_GET['post'] ) )
				$post_type = get_post_type( absint( $_GET['post'] ) );
			else if( isset( $_GET['page'] ) ){
				$screen = get_current_screen();
				$post_type = $screen->id;
			}

			if ( ( $this->page[0] == $post_type ) ){
				if( file_exists( WPPB_PLUGIN_DIR . 'assets/lib/codemirror/lib/codemirror.css' ) )
					wp_enqueue_style( 'codemirror-style', WPPB_PLUGIN_URL . 'assets/lib/codemirror/lib/codemirror.css', false, PROFILE_BUILDER_VERSION );
				else if( defined( 'WPPB_PAID_PLUGIN_URL' ) )
					wp_enqueue_style( 'codemirror-style', WPPB_PAID_PLUGIN_URL . 'assets/lib/codemirror/lib/codemirror.css', false, PROFILE_BUILDER_VERSION );

                if( !wp_script_is( 'codemirror', 'registered' ) && !wp_script_is( 'codemirror', 'enqueued' ) ) {

					if( file_exists(  WPPB_PLUGIN_DIR . 'assets/lib/codemirror/lib/codemirror.js' ) ){
						wp_enqueue_script('codemirror', WPPB_PLUGIN_URL . 'assets/lib/codemirror/lib/codemirror.js', array(), PROFILE_BUILDER_VERSION);
						wp_enqueue_script('codemirror-mode-overlay-js', WPPB_PLUGIN_URL . 'assets/lib/codemirror/addon/mode/overlay.js', array(), '1.0');
						wp_enqueue_script('codemirror-mode-xml-js', WPPB_PLUGIN_URL . 'assets/lib/codemirror/mode/xml/xml.js', array(), '1.0');
						wp_enqueue_script('codemirror-fullscreen-js', WPPB_PLUGIN_URL . 'assets/lib/codemirror/addon/display/fullscreen.js', array(), '1.0');
					} else if ( defined( 'WPPB_PAID_PLUGIN_URL' ) ){
						wp_enqueue_script('codemirror', WPPB_PAID_PLUGIN_URL . 'assets/lib/codemirror/lib/codemirror.js', array(), PROFILE_BUILDER_VERSION);
						wp_enqueue_script('codemirror-mode-overlay-js', WPPB_PAID_PLUGIN_URL . 'assets/lib/codemirror/addon/mode/overlay.js', array(), '1.0');
						wp_enqueue_script('codemirror-mode-xml-js', WPPB_PAID_PLUGIN_URL . 'assets/lib/codemirror/mode/xml/xml.js', array(), '1.0');
						wp_enqueue_script('codemirror-fullscreen-js', WPPB_PAID_PLUGIN_URL . 'assets/lib/codemirror/addon/display/fullscreen.js', array(), '1.0');
					}

                }

				wp_enqueue_script('jquery-ui-accordion');
			}
		}
	}

	/**
	 * Function that prints the codemirror initialization and the css
	 *
	 * @param string $hook the page hook
	 *
	 * @since 2.0.0
	 *
	 */
	function wppb_print_codemirror_script(){
		global $printed_codemirror_scripts;

		if( $printed_codemirror_scripts )
			return;

		$post_type = NULL;

		if ( isset( $_GET['post_type'] ) || isset( $_GET['post'] ) || isset( $_GET['page'] ) ){
			if ( isset( $_GET['post_type'] ) )
				$post_type = sanitize_text_field( $_GET['post_type'] );
			elseif ( isset( $_GET['post'] ) )
				$post_type = get_post_type( absint( $_GET['post'] ) );
			else if( isset( $_GET['page'] ) ){
				$screen = get_current_screen();
				if( $screen !== null && is_object( $screen ) ) {
					$post_type = $screen->id;
				}
			}

			if ( ( $this->page[0] == $post_type ) ){
				if( file_exists( WPPB_PLUGIN_DIR . 'assets/lib/class-mustache-templates/class-mustache-templates.css' ) )
                	wp_enqueue_style( 'class-mustache-css', WPPB_PLUGIN_URL . 'assets/lib/class-mustache-templates/class-mustache-templates.css', false, PROFILE_BUILDER_VERSION );
				else if( defined( 'WPPB_PAID_PLUGIN_URL' ) )
                	wp_enqueue_style( 'class-mustache-css', WPPB_PAID_PLUGIN_URL . 'assets/lib/class-mustache-templates/class-mustache-templates.css', false, PROFILE_BUILDER_VERSION );
					
                if( wp_script_is( 'codemirror-mode-overlay-js', 'enqueued' ) ) {
					if( file_exists( WPPB_PLUGIN_DIR . 'assets/lib/class-mustache-templates/class-mustache-templates.css' ) )
						wp_enqueue_script( 'class-mustache-js', WPPB_PLUGIN_URL . 'assets/lib/class-mustache-templates/class-mustache-templates.js', array("jquery"), PROFILE_BUILDER_VERSION );
					else if( defined( 'WPPB_PAID_PLUGIN_URL' ) )
						wp_enqueue_script( 'class-mustache-js', WPPB_PAID_PLUGIN_URL . 'assets/lib/class-mustache-templates/class-mustache-templates.js', array("jquery"), PROFILE_BUILDER_VERSION );
                }

                $printed_codemirror_scripts = true;
			}
		}
	}

	/**
	 * Function that adds the mustache metabox
	 *
	 * @since 2.0.0
	 *
	 */
	function add_box() {
        global $post_type;

		foreach ( $this->page as $page ) {
			add_meta_box( $this->id, $this->title, array( $this, 'meta_box_callback' ), $page, 'normal', $this->priority, array( 'post_type' => $post_type ) );

			/* if it's not a post type add a side metabox with a save button */
			if( isset( $_GET['page'] ) )
				add_meta_box( 'page-save-metabox', __( 'Save', 'profile-builder' ), array( $this, 'page_save_meta_box' ), $page, 'side' );
		}
	}

	/**
	 * Function output the content for the metabox
	 *
	 *
	 * @since 2.0.0
	 *
	 */
	function meta_box_callback( $post, $metabox ) {
		global $post;
        if( isset( $_GET['page'] ) )
            $post_type = NULL;
        else
            $post_type = $metabox['args']['post_type'];

        /* save default values in the database as post meta for post types */
        if( !empty( $this->fields ) && !empty( $post->ID ) ){
            foreach( $this->fields as $field ){
                if( !empty( $field['default'] ) ){
                    /* see if we have an option with this name, if we have don't do anything */
                    $field_saved_value = get_post_meta( $post->ID, $field['id'], true );
                    if( empty( $field_saved_value ) ){
                        add_post_meta( $post->ID, $field['id'], $field['default'], true );
                    }
                }
            }
        }

		// Use nonce for verification
		echo '<input type="hidden" name="' . esc_attr( $post_type ) . '_meta_box_nonce" value="' . esc_attr( wp_create_nonce( basename( __FILE__) ) ) . '" />';

		// Begin the field table and loop
		echo '<table class="form-table meta_box mustache-box">';
		foreach ( $this->fields as $field ) {

			// get data for this field
			extract( $field );
			if ( !empty( $desc ) )
				$desc = '<span class="description">' . $desc . '</span>';

			// get value of this field if it exists for this post
			if( isset( $_GET['page'] ) ){
				$meta = get_option( $id );
			}
			else{
				$meta = get_post_meta( $post->ID, $id, true );
			}

			//try to set a default value
			if( empty( $meta ) && !empty( $default ) ){
				$meta = $default;
			}


			// begin a table row with
			echo '<tr>
					<td class="' . esc_attr( $id ) . ' ' . esc_attr( $type ) . '">';
					if( $type != 'header' && !empty( $label ) ){
					    echo '<label for="' . esc_attr( $id ) . '" class="wppb_mustache_label">' . esc_html( $label ) . '</label>';
                    }
					switch( $type ) {
						// text
						case 'text':
							echo '<input type="text" name="' . esc_attr( $id ) . '" id="' . esc_attr( $id ) . '" value="' . esc_attr( $meta ) . '" size="30" />
									<br />' . wp_kses_post( $desc ) ;
						break;
						// textarea
						case 'textarea':

							echo '<textarea name="' . esc_attr( $id ) . '" id="' . esc_attr( $id ) . '" cols="220" rows="4" class="wppb_mustache_template">' . esc_textarea( $meta ) . '</textarea>';
							echo  wp_kses_post( $desc ) ;
						break;
						// checkbox
						case 'checkbox':
							echo '<input type="checkbox" name="' . esc_attr( $id ) . '" id="' . esc_attr( $id ) . '"' . checked( esc_attr( $meta ), 'on', false ) . ' value="on" />
									<label for="' . esc_attr( $id ) . '">' . esc_html( $desc ) . '</label>';
						break;
						// select
						case 'select':
							echo '<select name="' . esc_attr( $id ) . '" id="' . esc_attr( $id ) . '">';
							foreach ( $options as $option )
								echo '<option' . selected( esc_attr( $meta ), $option['value'], false ) . ' value="' . esc_attr( $option['value'] ) . '">' . esc_html( $option['label'] ) . '</option>';
							echo '</select><br />' . wp_kses_post( $desc );
						break;
						// radio
						case 'radio':
							foreach ( $options as $option )
								echo '<input type="radio" name="' . esc_attr( $id ) . '" id="' . esc_attr( $id ) . '-' . esc_attr( $option['value'] ) . '" value="' . esc_attr( $option['value'] ) . '"' . checked( esc_attr( $meta ), $option['value'], false ) . ' />
										<label for="' . esc_attr( $id ) . '-' . esc_attr( $option['value'] ) . '">' . esc_html( $option['label'] ) . '</label><br />';
							echo '' . wp_kses_post( $desc );
						break;
						// checkbox_group
						case 'checkbox_group':
							foreach ( $options as $option )
								echo '<input type="checkbox" value="' . esc_attr( $option['value'] ) . '" name="' . $id . '[]" id="' . $id . '-' . $option['value'] . '"' , is_array( $meta ) && in_array( $option['value'], $meta ) ? ' checked="checked"' : '' , ' />
										<label for="' . esc_attr( $id ) . '-' . esc_attr( $option['value'] ) . '">' . esc_html( $option['label'] ) . '</label><br />';
							echo '' . wp_kses_post( $desc );
						break;
						// text
						case 'header':
							echo '<h4>'. esc_html( $default ) .'</h4>';
						break;
					} //end switch

					if( $type == 'textarea' ){
					?>
						<div class="stp-extra">
						<?php
						if( !empty( $this->mustache_vars ) ){
							foreach( $this->mustache_vars as $mustache_var_group ){
								?>
								<h4><?php echo esc_html( $mustache_var_group['group-title'] ); ?></h4>
								<pre><?php 	do_action( 'wppb_before_mustache_vars_display', $mustache_var_group, $id, $post_type ); $this->display_mustache_available_vars( $mustache_var_group['variables'], 0 ); ?></pre>
								<?php
							}
						}
						?>
						</div>
					<?php
					}
			echo '</td></tr>';
		} // end foreach
		echo '</table>'; // end table
	}

	/**
	 * Function that ads a side metabox on pages ( not post types ) with a save button
	 *
	 *
	 * @since 2.0.0
	 *
	 */
	function page_save_meta_box() {
		?>
		<input type="submit" value="<?php esc_html_e( 'Save Changes', 'profile-builder' ); ?>" class="button button-primary button-large mustache-save">
	<?php
	}

	/**
	 * Function that ads a form start on pages ( not post types ) and also the 'save_post' action
	 *
	 *
	 * @since 2.0.0
	 *
	 */
	function wppb_mustache_page_before( $hook ){
        global $started_mustache_form;

        if( $started_mustache_form )
            return;

        if( isset( $_GET['page'] ) && $hook == $this->page[0] ){
			/* if we are saving do the action 'save_post' */
			if( isset( $_GET['mustache_action'] ) && $_GET['mustache_action'] == 'save' ) {
                /* to avoid conflicts with other plugins we send an empty post object as the second parameter with a fake post type */
				$post = new WP_Post( new stdClass() );
                $post->post_type = 'wppb-mustache-settings-page';
                remove_all_actions('save_post', 10 );//remove all actions that could run on the save_post hook on priority 10 than could expect a numeric post id instead of a string like $this->id. Ex: add_action( 'save_post', array( $this, 'refresh_post_word_count' ) ); in WPML translation management
				remove_all_actions('save_post', PHP_INT_MAX );//same for PHP_INT_MAX priority: add_action( 'save_post', array( $this, 'queue_save_post_actions' ), PHP_INT_MAX, 2 );
				do_action( 'save_post', $this->id, $post, false );
            }

			echo '<form action="'. esc_url( add_query_arg( array('mustache_action' => 'save') ) ) .'" method="post">';
            $started_mustache_form = true;
		}
	}

	/**
	 * Function that ads a form end on pages ( not post types )
	 *
	 *
	 * @since 2.0.0
	 *
	 */
	function wppb_mustache_page_after( $hook ){
        global $ended_mustache_form;
        if( $ended_mustache_form )
            return;

		if( isset( $_GET['page'] ) && $hook == $this->page[0] ){
			echo '</form>';
            $ended_mustache_form = true;
		}
	}


	/**
	 * Function that transforms and echoes the $mustache_vars into despayable forms. It takes care of nested levels and adds {{}} as well
	 *
	 * @param array $mustache_vars is the array containing the names of the variable that must be proccessed by mustache in the template. The array must be in this form:
	 * array( array( 'name' => '', 'type' => '' ) ... ), and for loop tags it also contains a 'children' element which contains other mustache_vars array(  array( 'name' => 'users', 'type' => 'loop_tag', 'children' => $merge_tags  )
	 * @param int $level the current level of the nested variables.
	 *
	 * @since 2.0.0
	 *
	 */
	function display_mustache_available_vars( $mustache_vars, $level ){
		if( !empty( $mustache_vars ) ){
			foreach( $mustache_vars  as $var ){
			    if ( $var[ 'name' ] != 'password' ) {
                    if ( empty( $var[ 'children' ] ) ) {
                        echo str_repeat( "&nbsp;&nbsp;", $level );//phpcs:ignore  WordPress.Security.EscapeOutput.OutputNotEscaped
                        if ( !empty( $var[ 'label' ] ) )
                            echo esc_html( apply_filters( 'wppb_variable_label', $var[ 'label' ] . ':' ) );
                        if ( !empty( $var[ 'unescaped' ] ) && $var[ 'unescaped' ] === true )
                            echo '{{{';
                        else
                            echo '{{';
                        echo esc_html( $var[ 'name' ] );
                        if ( !empty( $var[ 'unescaped' ] ) && $var[ 'unescaped' ] === true )
                            echo '}}}';
                        else
                            echo '}}';
                        echo PHP_EOL;
                    } else {
                        echo str_repeat( "&nbsp;&nbsp;", $level ); //phpcs:ignore  WordPress.Security.EscapeOutput.OutputNotEscaped
                        echo '{{#' . esc_html( $var[ 'name' ] ) . '}}' . PHP_EOL;
                        $level++;
                        $this->display_mustache_available_vars( $var[ 'children' ], $level );
                        $level--;
                        echo str_repeat( "&nbsp;&nbsp;", $level ); //phpcs:ignore  WordPress.Security.EscapeOutput.OutputNotEscaped
                        echo '{{/' . esc_html( $var[ 'name' ] ) . '}}' . PHP_EOL;
                    }
                }
			}
		}
	}

	/**
	 * Function that saves the values from the metabox
	 *
	 * @param int $post_id the post id
	 * @param post object $post
	 *
	 * @since 2.0.0
	 *
	 */
	function save_box( $post_id, $post ){
		global $post_type;
		/* addition to save as option if we are not on a post type */
		if( !is_numeric( $post_id ) && isset( $_POST['_meta_box_nonce'] ) && @wp_verify_nonce( sanitize_text_field( $_POST['_meta_box_nonce'] ),  basename( __FILE__ ) ) ){
			foreach ( $this->fields as $field ) {

				if ( isset( $_POST[$field['id']] ) ) {
					update_option( $field['id'], wp_unslash( $_POST[$field['id']] ) );//phpcs:ignore  WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				}

			}
		}

		// verify nonce
		if ( ! ( in_array( $post_type, $this->page ) && isset( $_POST[$post_type . '_meta_box_nonce'] ) && @wp_verify_nonce( sanitize_text_field( $_POST[$post_type . '_meta_box_nonce'] ),  basename( __FILE__ ) ) ) )
			return $post_id;
		// check autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;
		// check permissions
		if ( !current_user_can( 'edit_page', $post_id ) )
			return $post_id;

		// loop through fields and save the data
		foreach ( $this->fields as $field ) {
			if( $field['type'] == 'tax_select' ) {
				// save taxonomies
				if ( isset( $_POST[$field['id']] ) )
					$term = $_POST[$field['id']]; //phpcs:ignore  WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				wp_set_object_terms( $post_id, $term, $field['id'] );
			}
			else {
				// save the rest
				$old = get_post_meta( $post_id, $field['id'], true );
				if ( isset( $_POST[$field['id']] ) ) {
					$new = $_POST[$field['id']]; /* phpcs:ignore  WordPress.Security.ValidatedSanitizedInput.InputNotSanitized */
				} else {
					$new = '';
				}

				if ( $new && $new != $old ) {
					if ( is_array( $new ) ) {
						foreach ( $new as &$item ) {
							//$item = esc_attr( $item );
						}
						unset( $item );
					} else {
						//$new = esc_attr( $new );
					}
					update_post_meta( $post_id, $field['id'], $new );
				} elseif ( '' == $new && $old ) {
					delete_post_meta( $post_id, $field['id'], $old );
				}
			}
		} // end foreach
	}


    /**
     * Function that saves the default values for each field in the database for options
     *
     *
     * @since 2.0.0
     *
     */

     function save_default_values(){
        /* only do it on pages where we save as options and we have fields */
        if( !empty( $this->fields ) && !post_type_exists($this->page[0]) ){
            foreach( $this->fields as $field ){
                if( !empty( $field['default'] ) ){
                    /* see if we have an option with this name, if we have don't do anything */
                    $field_saved_value = get_option( $field['id'] );
                    if( empty( $field_saved_value ) ) {
                        update_option( $field['id'], $field['default'] );
                    }
                }
            }
        }
     }
}
