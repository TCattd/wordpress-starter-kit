<?php
/**
 * Plugin Name: Piotnetforms Pro
 * Description: Piotnet Forms Pro
 * Plugin URI:  https://piotnetforms.com/
 * Version:     1.0.78
 * Author:      Piotnet
 * Author URI:  https://piotnet.com/
 * Text Domain: piotnetforms
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

require_once __DIR__ . '/inc/variables.php';

define( 'PIOTNETFORMS_PRO_VERSION', '1.0.78' );

class Piotnetforms_pro extends Piotnetforms_Variables_Pro {

	public function __construct() {

		parent::__construct();

		add_action( 'plugins_loaded', [ $this, 'init' ] );
		register_activation_hook( __FILE__, [ $this, 'plugin_activate' ] );
	}

	public function init() {
        if ( ! defined( 'PIOTNETFORMS_VERSION' ) ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );
            return;
        }

		add_action( 'init', [ $this, 'register_post_type' ] );

		add_filter( 'single_template', [ $this, 'single_template' ] );

		require_once __DIR__ . '/inc/managers/controls.php';

		require_once __DIR__ . '/inc/class/piotnetforms-editor.php';

		require_once __DIR__ . '/inc/ajax/preview.php';

		require_once __DIR__ . '/inc/ajax/get-json-file.php';

		require_once __DIR__ . '/inc/ajax/save.php';

		require_once __DIR__ . '/inc/ajax/save-draft.php';

        require_once __DIR__ . '/inc/ajax/export.php';

        require_once __DIR__ . '/inc/ajax/duplicate.php';

		require_once __DIR__ . '/inc/shortcode/shortcode-widget.php';

		add_action( 'wp_enqueue_scripts', [ $this, 'load_jquery' ] );

		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue' ] );

		add_action( 'admin_enqueue_scripts', [ $this, 'add_admin_scripts' ], 10, 1 );

		add_action( 'admin_footer', [ $this, 'admin_footer' ], 10, 1 );

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ] );

		add_action( 'admin_menu', [ $this, 'admin_menu' ], 600 );

		add_action( 'init', [ $this, 'piotnetforms_pdf_font_post_type' ] );

		add_action('add_meta_boxes', [$this, 'piotnetforms_pdf_metabox']);

		add_action( 'save_post_piotnetforms-fonts', [$this, 'piotnetforms_pdf_save_custom_font'] );

		add_filter('upload_mimes', [$this,'piotnetforms_add_custom_upload_mimes']);

		add_filter( 'the_content', [ $this, 'add_wrapper' ] );

		add_filter( 'manage_' . $this->slug . '_posts_columns', [ $this, 'set_custom_edit_columns' ] );
		add_action( 'manage_' . $this->slug . '_posts_custom_column', [ $this, 'custom_column' ], 10, 2 );

		// add_action( 'wp_footer', [ $this, 'enqueue_footer' ], 600 );

		add_action( 'wp_head', [ $this, 'enqueue_head' ], 600 );

		$upload     = wp_upload_dir();
		$upload_dir = $upload['basedir'];
		$upload_dir = $upload_dir . '/piotnetforms';
		if ( ! is_dir( $upload_dir ) ) {
			mkdir( $upload_dir, 0755 );
			mkdir( $upload_dir . '/css', 0755 );
			mkdir( $upload_dir . '/files', 0755 );
		} else {
			if ( ! is_dir( $upload_dir . '/files') ) {
				mkdir( $upload_dir . '/files', 0755 );
			}
			if ( @chmod( $upload_dir, 0700 ) ) {
				@chmod( $upload_dir, 0755 );
				@chmod( $upload_dir . '/css', 0755 );
				@chmod( $upload_dir . '/files', 0755 );
			}
		}

		add_action( 'in_plugin_update_message-piotnetforms-pro/piotnetforms-pro.php', [ $this, 'update_message' ], 10, 2 );
		add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 2 );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'plugin_action_links' ], 10, 1 );

		add_action( 'admin_init', [ $this, 'plugin_redirect' ] );

		$activated_license = get_option( 'piotnetforms-activated' );
		// if( $activated_license != 1 ) {
		// 	add_action( 'admin_notices', [ $this, 'piotnetforms_admin_notice__error'] );
		// }

		add_filter( 'post_row_actions', [ $this, 'modify_list_row_actions' ], 10, 2 );

		add_filter( 'page_row_actions', [ $this, 'modify_list_row_actions' ], 10, 2 );

		add_filter( 'body_class', [ $this, 'add_body_class' ] );

		// Forms

		add_action( 'init', [ $this, 'piotnetforms_abandonment_database_post_type' ] );
		add_action( 'init', [ $this, 'piotnetforms_database_post_type' ] );
		add_action( 'init', [ $this, 'piotnetforms_booking_post_type' ] );

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts_woocommerce_sales_funnels' ] );

		require_once( __DIR__ . '/inc/forms/shortcode-piotnetforms-edit-post.php' );
		require_once( __DIR__ . '/inc/forms/shortcode-piotnetforms-delete-post.php' );
		require_once( __DIR__ . '/inc/forms/shortcode-for-piotnetforms-delete-post.php' );
		require_once( __DIR__ . '/inc/forms/ajax-form-builder.php' );
		require_once( __DIR__ . '/inc/forms/ajax-form-builder-preview-submission.php' );
		require_once( __DIR__ . '/inc/forms/ajax-form-booking.php' );
		require_once( __DIR__ . '/inc/forms/ajax-campaign-select-list.php' );
		require_once( __DIR__ . '/inc/forms/ajax-campaign-fields.php' );
		require_once( __DIR__ . '/inc/forms/ajax-getresponse-custom-fields.php' );
		require_once( __DIR__ . '/inc/forms/ajax-getresponse-select-list.php' );
		require_once( __DIR__ . '/inc/forms/ajax-mailchimp-get-list.php' );
		require_once( __DIR__ . '/inc/forms/ajax-mailerlite-get-groups.php' );
		require_once( __DIR__ . '/inc/forms/ajax-mailerlite-get-fields.php' );
		require_once( __DIR__ . '/inc/forms/ajax-mailchimp-get-groups.php' );
		require_once( __DIR__ . '/inc/forms/ajax-mailchimp-get-fields.php' );
		require_once( __DIR__ . '/inc/forms/ajax-mailpoet-get-custom-fields.php');
		require_once( __DIR__ . '/inc/forms/ajax-zoho-get-tag-name.php');
		require_once( __DIR__ . '/inc/forms/ajax-form-builder-woocommerce-checkout.php' );
		require_once( __DIR__ . '/inc/forms/ajax-woocommerce-sales-funnels-add-to-cart.php' );
		require_once( __DIR__ . '/inc/forms/ajax-stripe-intents.php' );
		require_once( __DIR__ . '/inc/forms/ajax-delete-post.php' );
		require_once( __DIR__ . '/inc/forms/export-form-submission.php' );
		require_once( __DIR__ . '/inc/forms/form-database-meta-box.php' );
		require_once( __DIR__ . '/inc/forms/meta-box-acf-repeater.php' );
		require_once( __DIR__ . '/inc/forms/ajax-form-abandonment.php' );
		require_once( __DIR__ . '/inc/forms/templates/template-form-booking.php' );
		require_once( __DIR__ . '/inc/forms/meta-box-piotnetforms-shortcode-in-post.php' );

		// Custom Price Woocommerce
    	add_action( 'woocommerce_before_calculate_totals', [ $this, 'piotnetforms_apply_custom_price_to_cart_item'], 30, 1 );

    	// Booking Woocommerce
    	add_action( 'woocommerce_checkout_order_processed', [ $this, 'piotnetforms_woocommerce_checkout_order_processed'], 10, 1 );

    	// Redirect Woocommerce
    	add_action( 'template_redirect', [ $this, 'piotnetforms_woocommerce_checkout_redirect' ] );

    	add_action( 'woocommerce_add_order_item_meta', function ( $itemId, $values, $key ) {
			if ( isset( $values['fields'] ) ) {
				foreach ($values['fields'] as $item) {
					if (!empty($item['label'])) {
						wc_add_order_item_meta( $itemId, $item['label'], $item['value'] );
					}
				}
			}
		}, 10, 3 );

    	if (function_exists('get_field')) {
    		add_filter('acf/settings/remove_wp_meta_box', '__return_false');
    	}

    	add_action( 'restrict_manage_posts', [ $this, 'piotnetforms_filter' ] );
    	add_filter( 'parse_query', [ $this, 'piotnetforms_filter_posts' ] );

    	add_filter('manage_piotnetforms-aban_posts_columns', [$this,'piotnetforms_filter_column'], 10);
		add_action('manage_piotnetforms-aban_posts_custom_column', [$this,'piotnetforms_filter_column_content'], 10, 2);

		add_filter('manage_piotnetforms-data_posts_columns', [$this,'piotnetforms_filter_column'], 10);
		add_action('manage_piotnetforms-data_posts_custom_column', [$this,'piotnetforms_filter_column_content'], 10, 2);

		add_action('admin_footer', [$this,'piotnetforms_filter_export_btn'] );

		add_filter( 'woocommerce_is_checkout', array( $this, 'piotnetforms_woocommerce_checkout_load' ), 9999 );

		add_action( 'wp_head', array( $this, 'piotnetforms_woocommerce_checkout_load_cart' ), 10 );

		add_filter( 'woocommerce_checkout_fields' , array( $this, 'piotnetforms_woocommerce_checkout_remove_checkout_fields'), 10 ,1 );

		add_action( 'save_post_piotnetforms', [$this,'save_form'], 10, 3 );

		if ( class_exists( 'WooCommerce' ) ) { 
			add_filter( 'woocommerce_get_item_data', [ $this, 'piotnetforms_woocommerce_add_to_cart' ], 10, 2 );
		}

		require_once ( 'updater.php' );
		$plugin_slug = plugin_basename( __FILE__ );
		$license = get_option('piotnetforms_license');
        $license_key = null;
        if (isset($license) && isset($license['license_key'])) {
		    $license_key = $license['license_key'];
        }
		new Piotnetforms_Updater ( $plugin_slug, $license_key );

        // add_action( 'init', [ $this, 'check_starter_forms' ] );
	}

	public function piotnetforms_woocommerce_checkout_redirect(){
		if ( class_exists( 'WooCommerce' ) ) {
			/* do nothing if we are not on the appropriate page */
			if( !is_wc_endpoint_url( 'order-received' ) || empty( $_GET['key'] ) ) {
				return;
			}
		 
			$order_id = wc_get_order_id_by_order_key( $_GET['key'] );
			$order = wc_get_order( $order_id );
		    $order_items = $order->get_items();

		    foreach ($order_items as $key => $value) {
	            $redirect_url = wc_get_order_item_meta( $key, 'piotnetforms_woocommerce_checkout_redirect', true );

	            if (!empty($redirect_url)) {
	            	wc_delete_order_item_meta( $key, 'piotnetforms_woocommerce_checkout_redirect' );
	            	wp_redirect( $redirect_url );
	            }
	        }
        }
	}

	public function piotnetforms_woocommerce_add_to_cart( $item_data, $cart_item ) {
	    if ( empty( $cart_item['fields'] ) ) {
	        return $item_data;
	    }

	    $fields = apply_filters( 'piotnetforms/form_builder/woocommerce_add_to_cart_fields', $cart_item['fields'] );

	    foreach ($fields as $item) {
	    	$item_data[] = array(
		        'key'     => $item['label'],
		        'value'   => $item['value'],
		        'display' => '',
		    );
	    }
	 
	    return $item_data;
	}

	function check_starter_forms() {
	    $option_key = 'piotnetforms_starter_forms_imported';
        $starter_forms_imported = get_option($option_key, null);
        if (!isset($starter_forms_imported)) {
            $this->import_starter_forms();
        }
        $data = [
                "importedDate" => time()
        ];
        update_option($option_key, $data);
    }

    function import_starter_forms() {
	    $dir = __DIR__ . "/assets/forms/starter/";
        $files = array_diff(scandir($dir), array('.', '..'));
        if (count($files) === 0) {
            return;
        }

        foreach ($files as $file) {
            $content = file_get_contents( $dir . $file );
            $data         = json_decode( $content, true );

            $post = [
                'post_title'  => $data['title'],
                'post_status' => 'publish',
                'post_type'   => 'piotnetforms',
            ];

            $post_id = wp_insert_post( $post );
            piotnetforms_do_import( $post_id, $data );
        }
    }

    public function save_form( $post_id, $post, $update ) {
	    if ( $update ) {
	        $raw_data = get_post_meta( $post_id, '_piotnetforms_data', true );
	        if (!empty(get_post_meta( $post_id, '_piotnetforms_form_id', true ))) {
				$data_str = str_replace('"form_id":"' . get_post_meta( $post_id, '_piotnetforms_form_id', true ), '"form_id":"' . get_the_title($post_id), $raw_data);
				$data_str = str_replace('"piotnetforms_booking_form_id":"' . get_post_meta( $post_id, '_piotnetforms_form_id', true ), '"piotnetforms_booking_form_id":"' . get_the_title($post_id), $data_str);
				$data_str = str_replace('"piotnetforms_woocommerce_checkout_form_id":"' . get_post_meta( $post_id, '_piotnetforms_form_id', true ), '"piotnetforms_woocommerce_checkout_form_id":"' . get_the_title($post_id), $data_str);
				$data_str = str_replace('"piotnetforms_conditional_logic_form_form_id":"' . get_post_meta( $post_id, '_piotnetforms_form_id', true ), '"piotnetforms_conditional_logic_form_form_id":"' . get_the_title($post_id), $data_str);
				$data_str = str_replace('"piotnetforms_repeater_form_id":"' . get_post_meta( $post_id, '_piotnetforms_form_id', true ), '"piotnetforms_repeater_form_id":"' . get_the_title($post_id), $data_str);
				update_post_meta( $post_id, '_piotnetforms_data', wp_slash( $data_str ) );
	        }
	        
	        update_post_meta( $post_id, '_piotnetforms_form_id', get_the_title($post_id) );
	    }
	}

	public function add_contextmenu() {
		ob_start();
		?>
		<div class="piotnetforms-contextmenu" data-piotnetforms-contextmenu>
			<div class="piotnetforms-contextmenu__item" data-piotnetforms-contextmenu-action="copy-style">Copy Style</div>
			<div class="piotnetforms-contextmenu__item" data-piotnetforms-contextmenu-action="paste-style">Paste Style</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public function add_wrapper( $content ) {
		$post_id          = get_the_ID();

        $raw_data = get_post_meta( $post_id, '_piotnetforms_data', true );
        $data = json_decode( $raw_data, true );
        $widget_content = !empty($data['content']) ? $data['content'] : '';

		if ( ! empty( $widget_content ) ) {
			if ( isset( $_GET['action'] ) && $_GET['action'] == 'piotnetforms' ) {
				$editor = new piotnetforms_Editor();
				if ( is_user_logged_in() ) {
					if ( current_user_can( 'edit_others_posts' ) ) {
						$content = $editor->editor_preview( $widget_content );
						$content = '<div class="piotnetforms-widget-preview" id="piotnetforms" data-piotnetforms-widget-preview data-piotnet-sortable>' . $content . '</div>';
						$content .= $this->add_contextmenu();
					}
				}
			}

			if ( !isset( $_GET['action'] ) ) {
				$content          = $this->piotnetforms_render_loop( $widget_content, $post_id );
				$content          = '<div id="piotnetforms">' . $content . '</div>';

				$upload     = wp_upload_dir();
				$upload_dir = $upload['baseurl'];
				$upload_dir = $upload_dir . '/piotnetforms/css/';

				$css_file = $upload_dir . $post_id . '.css';
				$content .= '<link rel="stylesheet" href="' . $css_file . '?ver=' . get_post_meta( $post_id, '_piotnet-revision-version', true ) . '" media="all">';

				wp_enqueue_script( 'piotnetforms-script' );
				wp_enqueue_style( 'piotnetforms-style' );
				wp_enqueue_style( 'piotnetforms-global-style' );
			}
		} else {
			if ( is_user_logged_in() ) {
				if ( current_user_can( 'edit_others_posts' ) ) {
					if ( isset( $_GET['action'] ) && $_GET['action'] == 'piotnetforms' ) {
						$content = '<div class="piotnetforms-widget-preview" id="piotnetforms" data-piotnetforms-widget-preview data-piotnet-sortable></div>';
						$content .= $this->add_contextmenu();
					}
				}
			}
		}

		return $content . $this->enqueue_footer();
	}

	public function load_jquery() {
	    if ( ! wp_script_is( 'jquery', 'enqueued' )) {

	        //Enqueue
	        wp_enqueue_script( 'jquery' );

	    }
	}

	public function add_body_class( $classes ) {
		$classes[] = 'piotnetforms-edit';
		return $classes;
	}

	public function modify_list_row_actions( $actions, $post ) {
		// Check for your post type.
		if ( $post->post_type == "piotnetforms" ) {
			$url = admin_url() . 'admin.php?page=piotnetforms&post=' . $post->ID;

			$url_html = '<a href="' . $url . '">' . __( 'Edit With Piotnet Forms', 'piotnetforms' ) . '</a>';

            $url_export_html = '<a href="' . esc_url( get_admin_url( null, 'admin-ajax.php?action=piotnetforms_export&id=' ) ) . $post->ID . '">' . __( 'Export', 'piotnetforms' ) . '</a>';

            $duplicate_html = '<a href="' . esc_url( get_admin_url( null, 'admin-ajax.php?action=piotnetforms_duplicate&id=' ) ) . $post->ID . '">' . __( 'Duplicate', 'piotnetforms' ) . '</a>';

			$actions['edit_with_piotnetforms'] = $url_html;
			$actions['export_piotnetforms'] = $url_export_html;
			$actions['duplicate_piotnetforms'] = $duplicate_html;
		}

		return $actions;
	}

	public function plugin_action_links( $links ) {
		$activated_license = get_option( 'piotnetforms-activated' );
		$links[]           = '<a href="' . esc_url( get_admin_url( null, 'admin.php?page=piotnetforms' ) ) . '">' . esc_html__( 'Settings', 'piotnetforms' ) . '</a>';
		if ( $activated_license != 1 ) {
			$links[] = '<a href="' . esc_url( get_admin_url( null, 'admin.php?page=piotnetforms' ) ) . '" class="piotnetforms-plugins-gopro">' . esc_html__( 'Activate License', 'piotnetforms' ) . '</a>';
		}
		return $links;

	}

	public function update_message( $data, $response ) {
		echo '<br> ';
		printf(
			__( 'To enable updates, please login your account on the <a href="%1$s">Plugin Settings</a> page. If you have not purchased yet, please visit <a href="%2$s">https://piotnetforms.com</a>. If you can not update, please download new version on <a href="https://piotnetforms.com/my-account/">https://piotnetforms.com/my-account/</a>.', 'piotnetforms' ),
			admin_url( 'admin.php?page=piotnetforms' ),
			'https://piotnetforms.com'
		);
	}

	public function enqueue_frontend() {
		wp_enqueue_script( $this->slug . '-script' );
		wp_enqueue_style( $this->slug . '-style' );
		if ( file_exists( wp_upload_dir()['basedir'] . '/piotnetforms/css/global.css' ) ) {
			wp_enqueue_style( $this->slug . '-global-style' );
		}
	}

	public function enqueue_frontend_all() {
		wp_enqueue_script( $this->slug . '-script' );
		wp_enqueue_script( $this->slug . '-flatpickr-script' );
		wp_enqueue_script( $this->slug . '-image-picker-script' );
		wp_enqueue_script( $this->slug . '-ion-rangeslider-script' );
		wp_enqueue_script( $this->slug . '-selectize-script' );
		wp_enqueue_script( $this->slug . '-signature-pad-script' );
		wp_enqueue_script( $this->slug . '-tinymce-script' );
		wp_enqueue_script( $this->slug . '-jquery-mask-script' );
		wp_enqueue_script( $this->slug . '-jquery-validation-script' );
		wp_enqueue_script( $this->slug . '-nice-number-script' );
		wp_enqueue_script( $this->slug . '-preview-submission-script' );
		wp_enqueue_script( $this->slug . '-stripe-script' );
		wp_enqueue_script( $this->slug . '-abandonment-script' );
		wp_enqueue_script( $this->slug . '-image-upload-script' );
		wp_enqueue_script( $this->slug . '-advanced-script' );
		wp_enqueue_script( $this->slug . '-multi-step-script' );
		wp_enqueue_script( $this->slug . '-date-time-script' );

		wp_enqueue_style( $this->slug . '-style' );
		wp_enqueue_style( $this->slug . '-flatpickr-style' );
		wp_enqueue_style( $this->slug . '-image-picker-style' );
		wp_enqueue_style( $this->slug . '-rangeslider-style' );
		wp_enqueue_style( $this->slug . '-selectize-style' );
		wp_enqueue_style( $this->slug . '-fontawesome-style' );
		wp_enqueue_style( $this->slug . '-jquery-ui' );
	}

	public function enqueue() {
		wp_register_script( $this->slug . '-script', plugin_dir_url( __FILE__ ) . 'assets/js/minify/frontend.min.js', [ 'jquery' ], PIOTNETFORMS_PRO_VERSION );
		wp_register_script( $this->slug . '-flatpickr-script', plugin_dir_url( __FILE__ ) . 'assets/js/minify/frontend/flatpickr.min.js', [ 'jquery' ], PIOTNETFORMS_PRO_VERSION );
		wp_register_script( $this->slug . '-image-picker-script', plugin_dir_url( __FILE__ ) . 'assets/js/minify/frontend/image-picker.min.js', [ 'jquery' ], PIOTNETFORMS_PRO_VERSION );
		wp_register_script( $this->slug . '-ion-rangeslider-script', plugin_dir_url( __FILE__ ) . 'assets/js/minify/frontend/ion-rangeslider.min.js', [ 'jquery' ], PIOTNETFORMS_PRO_VERSION );
		wp_register_script( $this->slug . '-selectize-script', plugin_dir_url( __FILE__ ) . 'assets/js/minify/frontend/selectize.min.js', [ 'jquery' ], PIOTNETFORMS_PRO_VERSION );
		wp_register_script( $this->slug . '-signature-pad-script', plugin_dir_url( __FILE__ ) . 'assets/js/minify/frontend/signature-pad.min.js', [ 'jquery' ], PIOTNETFORMS_PRO_VERSION );
		wp_register_script( $this->slug . '-tinymce-script', plugin_dir_url( __FILE__ ) . 'assets/js/minify/frontend/tinymce.min.js', [ 'jquery' ], PIOTNETFORMS_PRO_VERSION );
		wp_register_script( $this->slug . '-jquery-mask-script', plugin_dir_url( __FILE__ ) . 'assets/js/minify/frontend/jquery-mask.min.js', [ 'jquery' ], PIOTNETFORMS_PRO_VERSION );
		wp_register_script( $this->slug . '-jquery-validation-script', plugin_dir_url( __FILE__ ) . 'assets/js/minify/frontend/jquery-validation.min.js', [ 'jquery' ], PIOTNETFORMS_PRO_VERSION );
		wp_register_script( $this->slug . '-nice-number-script', plugin_dir_url( __FILE__ ) . 'assets/js/minify/frontend/nice-number.min.js', [ 'jquery' ], PIOTNETFORMS_PRO_VERSION );
		wp_register_script( $this->slug . '-preview-submission-script', plugin_dir_url( __FILE__ ) . 'assets/js/minify/frontend/preview-submission.min.js', [ 'jquery' ], PIOTNETFORMS_PRO_VERSION );
		wp_register_script( $this->slug . '-stripe-script', plugin_dir_url( __FILE__ ) . 'assets/js/minify/frontend/stripe.min.js', [ 'jquery' ], PIOTNETFORMS_PRO_VERSION );
		wp_register_script( $this->slug . '-abandonment-script', plugin_dir_url( __FILE__ ) . 'assets/js/minify/frontend/abandonment.min.js', [ 'jquery' ], PIOTNETFORMS_PRO_VERSION );
		wp_register_script( $this->slug . '-image-upload-script', plugin_dir_url( __FILE__ ) . 'assets/js/minify/frontend/image-upload.min.js', [ 'jquery' ], PIOTNETFORMS_PRO_VERSION );
		wp_register_script( $this->slug . '-advanced-script', plugin_dir_url( __FILE__ ) . 'assets/js/minify/frontend/advanced.min.js', [ 'jquery' ], PIOTNETFORMS_PRO_VERSION );
		wp_register_script( $this->slug . '-multi-step-script', plugin_dir_url( __FILE__ ) . 'assets/js/minify/frontend/multi-step.min.js', [ 'jquery' ], PIOTNETFORMS_PRO_VERSION );
		wp_register_script( $this->slug . '-date-time-script', plugin_dir_url( __FILE__ ) . 'assets/js/minify/frontend/date-time.min.js', [ 'jquery' ], PIOTNETFORMS_PRO_VERSION );

		wp_register_style( $this->slug . '-style', plugin_dir_url( __FILE__ ) . 'assets/css/minify/frontend.min.css', [], PIOTNETFORMS_PRO_VERSION );
		wp_register_style( $this->slug . '-flatpickr-style', plugin_dir_url( __FILE__ ) . 'assets/css/minify/flatpickr.min.css', [], PIOTNETFORMS_PRO_VERSION );
		wp_register_style( $this->slug . '-image-picker-style', plugin_dir_url( __FILE__ ) . 'assets/css/minify/image-picker.min.css', [], PIOTNETFORMS_PRO_VERSION );
		wp_register_style( $this->slug . '-rangeslider-style', plugin_dir_url( __FILE__ ) . 'assets/css/minify/rangeslider.min.css', [], PIOTNETFORMS_PRO_VERSION );
		wp_register_style( $this->slug . '-selectize-style', plugin_dir_url( __FILE__ ) . 'assets/css/minify/selectize.min.css', [], PIOTNETFORMS_PRO_VERSION );
		wp_register_style( $this->slug . '-fontawesome-style', plugin_dir_url( __FILE__ ) . 'assets/css/minify/fontawesome.min.css', [], PIOTNETFORMS_PRO_VERSION );
		wp_register_style( $this->slug . '-jquery-ui', plugin_dir_url( __FILE__ ) . 'assets/css/jquery-ui.css', [], PIOTNETFORMS_PRO_VERSION );

		if ( file_exists( wp_upload_dir()['basedir'] . '/piotnetforms/css/global.css' ) ) {
			wp_register_style( $this->slug . '-global-style', wp_upload_dir()['baseurl'] . '/piotnetforms/css/global.css', [], intval( get_option( 'piotnet-global-css-version' ) ) );
		}

		if ( is_user_logged_in() ) {
			if ( current_user_can( 'edit_others_posts' ) ) {
				if ( isset( $_GET['action'] ) && $_GET['action'] == 'piotnetforms' ) {
					$this->enqueue_frontend_all();
					$this->admin_enqueue();
				}
			}
		}

		global $post;
		if ( is_object($post) ) {
            if ( has_shortcode( $post->post_content, 'piotnetforms') ) {
                $this->enqueue_frontend();
            }
        }

		$shortcode = get_post_meta( get_the_ID(), '_piotnetforms_shortcode_in_post', true );
		if (!empty($shortcode)) {
			$this->enqueue_frontend();
			$shortcode = explode('|', $shortcode);
			foreach ($shortcode as $shortcode_item) {
				$shortcode_atts = shortcode_parse_atts($shortcode_item);
				if (!empty($shortcode_atts['id'])) {
					$post_id = intval($shortcode_atts['id']);
					$upload     = wp_upload_dir();
					$upload_dir = $upload['baseurl'];
					$upload_dir = $upload_dir . '/piotnetforms/css/';
					$css_file = $upload_dir . $post_id . '.css';

					wp_enqueue_style( $this->slug . '-style-' . $post_id, $css_file, [], get_post_meta( $post_id, '_piotnet-revision-version', true ) );
				}
			}
		}
	}

	public function add_admin_scripts( $hook ) {
		global $post;

		if ( isset($_GET['page']) && isset($_GET['post']) ) {
			if ( $_GET['page'] == 'piotnetforms' ) {
				wp_enqueue_script( $this->slug . '-jquery-ui-script', plugin_dir_url( __FILE__ ) . 'assets/js/src/lib/jquery-ui.js', [ 'jquery' ], PIOTNETFORMS_PRO_VERSION );
				wp_enqueue_script( $this->slug . '-editor-script', plugin_dir_url( __FILE__ ) . 'assets/js/minify/editor.min.js', [ 'jquery' ], PIOTNETFORMS_PRO_VERSION );
				wp_enqueue_script( $this->slug . '-editor-forms-script', plugin_dir_url( __FILE__ ) . 'assets/js/minify/preview.min.js', [ 'jquery' ], PIOTNETFORMS_PRO_VERSION );
				wp_enqueue_style( $this->slug . '-admin-style', plugin_dir_url( __FILE__ ) . 'assets/css/minify/admin.min.css', [], PIOTNETFORMS_PRO_VERSION );
				wp_enqueue_script( $this->slug . '-script', plugin_dir_url( __FILE__ ) . 'assets/js/minify/frontend.min.js', [ 'jquery' ], PIOTNETFORMS_PRO_VERSION );
				wp_enqueue_style( $this->slug . '-style', plugin_dir_url( __FILE__ ) . 'assets/css/minify/frontend.min.css', [], PIOTNETFORMS_PRO_VERSION );
				wp_enqueue_media();
			}
		}

		wp_enqueue_script( $this->slug . '-admin-script', plugin_dir_url( __FILE__ ) . 'assets/js/minify/admin.min.js', [ 'jquery' ], PIOTNETFORMS_PRO_VERSION );
	}

	public function enqueue_head() {
		if ( is_user_logged_in() ) {
			if ( current_user_can( 'edit_others_posts' ) ) {
				if ( isset( $_GET['action'] ) && $_GET['action'] == 'piotnetforms' ) {
					//                  echo '<style data-piotnet-widget-css-head>' . get_post_meta( get_the_ID(), '_piotnet-widgets-css', true ) . '</style>';

					$post_id = get_the_ID();

					if ( $post_id != false ) {
						$widget_settings = get_post_meta( $post_id, '_piotnet-widget-settings', true );

						if ( ! empty( $widget_settings ) ) {
							$widget_settings = json_decode( $widget_settings, true );
							if ( ! empty( $widget_settings['fonts'] ) ) {
								$fonts = $widget_settings['fonts'];
								foreach ( $fonts as $font ) {
									echo '<link href="' . $font . '" rel="stylesheet">';
								}
							}
						}
					}
				}
			}
		}
	}

	public function enqueue_footer() {
		ob_start();
		echo '<div data-piotnetforms-ajax-url="' . admin_url( 'admin-ajax.php' ) . '"></div>';
		echo '<div data-piotnetforms-plugin-url="' . plugins_url() . '"></div>';
		echo '<div data-piotnetforms-tinymce-upload="' . plugins_url() . '/piotnetforms-pro/inc/forms/tinymce/tinymce-upload.php"></div>';
		echo '<div data-piotnetforms-stripe-key="' . esc_attr( get_option('piotnetforms-stripe-publishable-key') ) . '"></div>';
		echo '<div class="piotnetforms-break-point" data-piotnetforms-break-point-md="1025" data-piotnetforms-break-point-lg="767"></div>';
		?>
			<script type="text/javascript">
				function clearValidity($this){
				    const $parent = jQuery($this).closest('.piotnetforms-field-subgroup');
				    const $firstOption = $parent.find('.piotnetforms-field-option input');
				    $firstOption.each(function(){
				    	jQuery(this)[0].setCustomValidity('');
				    }); 
				}
			
				function piotnetformsAddressAutocompleteInitMap() {
				    var inputs = document.querySelectorAll('[data-piotnetforms-address-autocomplete]');

				    inputs.forEach(function(el, index, array){
				        var autocomplete = new google.maps.places.Autocomplete(el);
				        var country = el.getAttribute('data-piotnetforms-address-autocomplete-country');
				        var map_lat = el.getAttribute('data-piotnetforms-google-maps-lat');
				        var map_lng = el.getAttribute('data-piotnetforms-google-maps-lng');
				        var zoom = el.getAttribute('data-piotnetforms-google-maps-zoom');

				        if(country == 'All') {
				          autocomplete.setComponentRestrictions({'country': []});
				        } else {
				          autocomplete.setComponentRestrictions({'country': country});
				        }

				        var $mapSelector = el.closest('.piotnetforms-fields-wrapper').querySelectorAll('[data-piotnetforms-address-autocomplete-map]');
				        if($mapSelector.length>0) {
				            var myLatLng = { lat: parseFloat(map_lat), lng: parseFloat(map_lng) };
				            var map_zoom = parseInt(zoom);
				            var map = new google.maps.Map($mapSelector[0], {
				                center: myLatLng,
				                // center: {lat: -33.8688, lng: 151.2195},
				                zoom: map_zoom
				            });

				            var infowindow = new google.maps.InfoWindow();
				            var infowindowContent = el.closest('.piotnetforms-fields-wrapper').querySelectorAll('.infowindow-content')[0];
				            infowindow.setContent(infowindowContent);
				            var marker = new google.maps.Marker({
				              map: map,
				              anchorPoint: new google.maps.Point(0, -29)
				            });

				            autocomplete.addListener('place_changed', function() {
				              infowindow.close();
				              marker.setVisible(false);
				              var place = autocomplete.getPlace();
				              if (!place.geometry) {
				                // User entered the name of a Place that was not suggested and
				                // pressed the Enter key, or the Place Details request failed.
				                window.alert("No details available for input: '" + place.name + "'");
				                return;
				              }

				              // If the place has a geometry, then present it on a map.
				              if (place.geometry.viewport) {
				                map.fitBounds(place.geometry.viewport);
				              } else {
				                map.setCenter(place.geometry.location);
				                map.setZoom(17);  // Why 17? Because it looks good.
				              }
				              marker.setPosition(place.geometry.location);
				              marker.setVisible(true);

				              var address = '';
				              if (place.address_components) {
				                address = [
				                  (place.address_components[0] && place.address_components[0].short_name || ''),
				                  (place.address_components[1] && place.address_components[1].short_name || ''),
				                  (place.address_components[2] && place.address_components[2].short_name || '')
				                ].join(' ');
				              }

				              infowindowContent.children['place-icon'].src = place.icon;
				              infowindowContent.children['place-name'].textContent = place.name;
				              infowindowContent.children['place-address'].textContent = address;
				              infowindow.open(map, marker);
				            });
				        }

				        autocomplete.addListener('place_changed', function() {
				          var place = autocomplete.getPlace();
				          el.setAttribute('data-piotnetforms-google-maps-lat', place.geometry.location.lat());
				          el.setAttribute('data-piotnetforms-google-maps-lng', place.geometry.location.lng());
				          el.setAttribute('data-piotnetforms-google-maps-formatted-address', place.formatted_address);
				          el.setAttribute('data-piotnetforms-google-maps-zoom', '17');

				          var $distanceCalculation = document.querySelectorAll('[data-piotnetforms-calculated-fields-distance-calculation]');

				          $distanceCalculation.forEach(function(el, index, array){

				            if (el.getAttribute('data-piotnetforms-calculated-fields-distance-calculation-from') !== null) {
				              var origin = el.getAttribute('data-piotnetforms-calculated-fields-distance-calculation-from');
				            } else {
				              var $origin = document.getElementById( el.getAttribute('data-piotnetforms-calculated-fields-distance-calculation-from-field-shortcode').replace('[field id="', 'form-field-').replace('"]','') );
				              var origin = $origin.getAttribute('data-piotnetforms-google-maps-formatted-address');
				            }

				            if (el.getAttribute('data-piotnetforms-calculated-fields-distance-calculation-to') !== null) {
				              var destination = el.getAttribute('data-piotnetforms-calculated-fields-distance-calculation-to');
				            } else {
				              $destination = document.getElementById( el.getAttribute('data-piotnetforms-calculated-fields-distance-calculation-to-field-shortcode').replace('[field id="', 'form-field-').replace('"]','') );
				              var destination = $destination.getAttribute('data-piotnetforms-google-maps-formatted-address');
				            }

				            if (origin != '' && destination != '') {
				              var distanceUnit = el.getAttribute('data-piotnetforms-calculated-fields-distance-calculation-unit');
				              calculateDistance(origin, destination, el.closest('.piotnetforms-field-container').querySelector('.piotnetforms-calculated-fields-form__value'), distanceUnit, el);
				            }

				          });

				        });
				    });
				}

				// calculate distance
				function calculateDistance(origin, destination, $el, distanceUnit, $input) {

				    if (origin != '' && destination != '') {
				      var service = new google.maps.DistanceMatrixService();
				      service.getDistanceMatrix(
				          {
				              origins: [origin],
				              destinations: [destination],
				              travelMode: google.maps.TravelMode.DRIVING,
				              unitSystem: google.maps.UnitSystem.IMPERIAL, // miles and feet.
				              // unitSystem: google.maps.UnitSystem.metric, // kilometers and meters.
				              avoidHighways: false,
				              avoidTolls: false
				          }, function (response, status) {
				            if (status != google.maps.DistanceMatrixStatus.OK) {
				                // console.log(err);
				            } else {
				                var origin = response.originAddresses[0];
				                var destination = response.destinationAddresses[0];
				                if (response.rows[0].elements[0].status === "ZERO_RESULTS") {
				                    // console.log("Better get on a plane. There are no roads between "  + origin + " and " + destination);
				                } else {
				                    var distance = response.rows[0].elements[0].distance;
				                    var duration = response.rows[0].elements[0].duration;
				                    // console.log(response.rows[0].elements[0].distance);
				                    var distance_in_kilo = distance.value / 1000; // the kilom
				                    var distance_in_mile = distance.value / 1609.34; // the mile
				                    var duration_text = duration.text;
				                    var duration_value = duration.value;

				                    var event = new Event("change");

				                    if (distanceUnit == 'km') {
				                      $el.innerHTML = distance_in_kilo.toFixed(2);
				                      $input.value = distance_in_kilo.toFixed(2);
				                      jQuery($input).change();
				                    } else {
				                      $el.innerHTML = distance_in_mile.toFixed(2);
				                      $input.value = distance_in_mile.toFixed(2);
				                      jQuery($input).change();
				                    }
				                }
				            }
				        });
				    }
				}

				// get distance results
				function callback(response, status) {
				    if (status != google.maps.DistanceMatrixStatus.OK) {
				        console.log(err);
				    } else {
				        var origin = response.originAddresses[0];
				        var destination = response.destinationAddresses[0];
				        if (response.rows[0].elements[0].status === "ZERO_RESULTS") {
				            console.log("Better get on a plane. There are no roads between "  + origin + " and " + destination);
				        } else {
				            var distance = response.rows[0].elements[0].distance;
				            var duration = response.rows[0].elements[0].duration;
				            console.log(response.rows[0].elements[0].distance);
				            var distance_in_kilo = distance.value / 1000; // the kilom
				            var distance_in_mile = distance.value / 1609.34; // the mile
				            var duration_text = duration.text;
				            var duration_value = duration.value;

				            console.log(distance_in_mile.toFixed(2) + 'miles');
				            return distance_in_kilo.toFixed(2);
				            // $('#duration_text').text(duration_text);
				            // $('#duration_value').text(duration_value);
				            // $('#from').text(origin);
				            // $('#to').text(destination);
				        }
				    }
				}

				document.addEventListener( 'elementor/popup/show', function(event, id, instance){
				  piotnetformsAddressAutocompleteInitMap();
				} );
			</script>
		<?php
		return ob_get_clean();
	}

	public function admin_footer() {
		echo '<div data-piotnetforms-admin-url="' . admin_url() . '"></div>"';
		echo '<div data-piotnetforms-plugin-url="' . plugins_url() . '"></div>';

		if( get_option( 'piotnetforms-features-stripe-payment', 2 ) == 2 || get_option( 'piotnetforms-features-stripe-payment', 2 ) == 1 ) {
			// echo '<script src="https://js.stripe.com/v3/"></script>';
			echo '<div data-piotnetforms-stripe-key="' . esc_attr( get_option('piotnetforms-stripe-publishable-key') ) . '"></div>';
		}
		global $pagenow;
		global $typenow;
		if ("piotnetforms" == $typenow) :
		?>
			<script type="text/javascript">
				jQuery(document).ready(function( $ ) {
					$(window).load(function() {
						var $toolbar = $(document).find('#titlediv');
						$toolbar.append('<button class="button button-primary button-large" data-edit-with-piotnetforms>Edit With Piotnet Forms</button>');
					});

					$(document).on('click', '[data-edit-with-piotnetforms]', function(e){
						e.preventDefault();
						var post_id = $('#post_ID').val(),
							post_title = $('[name="post_title"]').val(),
							status = $('#original_post_status').val(),
							admin_url = $('[data-piotnetforms-admin-url]').attr('data-piotnetforms-admin-url');
                        $('#submitpost [type="submit"]').trigger('click');

						if (status === 'auto-draft') {
							var data = {
								post_id: post_id,
								post_title: post_title,
								action: 'piotnetforms_save_draft',
							};

							$.post(ajaxurl, data, function (response) {
								window.location.href = admin_url + 'admin.php?page=piotnetforms&post=' + post_id;
							});
						} else {
							window.location.href = admin_url + 'admin.php?page=piotnetforms&post=' + post_id;
						}

					});
				});
			</script>
		<?php
		endif;

		if (( $pagenow == 'edit.php' ) && !empty($_GET['post_type'])) {
			if (sanitize_text_field($_GET['post_type']) == 'piotnetforms') {
				if ( get_option( 'piotnetforms_do_flush', false ) ) {
					delete_option( 'piotnetforms_do_flush' );
					flush_rewrite_rules();
				}
			}
		}
	}

	public function tab_widget_template() {
		?>
		<script type="text/html" data-piotnetforms-template id="piotnetforms-tab-widget-template">
			<div class="piotnetforms-widget-controls" data-piotnetforms-widget-controls="<%= data['widget_id'] %>">
				<div class="piotnet-tabs" data-piotnet-tabs="">
					<% for ( var key in data['tabs'] ) { var tab = data['tabs'][key]; %>
					<div class="piotnet-tabs__item <%= tab.active ? 'active' : '' %>" data-piotnet-tabs-item="<%= tab['name'] %>"><%= tab['label'] %></div>
					<% } %>
				</div>
				<% for ( var key in data['tabs'] ) {
					var tab = data['tabs'][key];
					var sections = tab['sections'];
				%>
				<div class="piotnet-tabs-content <%= tab.active ? 'active' : '' %>" data-piotnet-tabs-content="<%= tab['name'] %>">
					<%
					for ( var key in sections ) {
						var section = sections[key];
						const field_group_attributes = [];
						if ( section.conditions ) {
						field_group_attributes.push("data-piotnet-control-conditions='" + JSON.stringify(section.conditions) + "'");
						}
					%>
					<div class="piotnet-controls-section <%= section.active ? 'active' : '' %>" data-piotnet-controls-section="<%= section['name'] %>" <% _.each(field_group_attributes, function(field_group_attribute) { %><%= " " + field_group_attribute %><% }); %>>
						<div class="piotnet-controls-section__header" data-piotnet-controls-section-header="">
							<div class="piotnet-controls-section__header-label"><%= section['label'] %></div>
							<div class="piotnet-controls-section__header-icon">
								<i class="fas fa-caret-down"></i>
								<i class="fas fa-caret-up"></i>
							</div>
						</div>
						<div class="piotnet-controls-section__body" data-piotnet-controls-section-body=""></div>
					</div>
					<% } %>
				</div>
				<% } %>
			</div>
		</script>
		<?php
	}

	public function output_template() {
		?>
		<script type="text/html" data-piotnetforms-template id="piotnetforms-output-template">
			<%
			view.add_attribute('widget_wrapper_editor', 'class', 'piotnet-widget');
			view.add_attribute('widget_wrapper_editor', 'data-piotnet-editor-widgets-item', JSON.stringify( data.widget_info ));
			view.add_attribute('widget_wrapper_editor', 'data-piotnet-editor-widgets-item-id', data.widget_id);
			view.add_attribute('widget_wrapper_editor', 'draggable', 'true');
			%>
			<div <%= view.render_attributes('widget_wrapper_editor') %>>
				<div class="piotnet-widget__controls">
					<div class="piotnet-widget__controls-item piotnet-widget__controls-item--edit" title="Edit" data-piotnet-control-edit>
						<i class="fas fa-th"></i>
					</div>
					<div class="piotnet-widget__controls-item piotnet-widget__controls-item--duplicate" title="Duplicate" data-piotnet-control-duplicate>
						<i class="far fa-clone"></i>
					</div>
					<div class="piotnet-widget__controls-item piotnet-widget__controls-item--remove" title="Delete" data-piotnet-control-remove>
						<i class="fas fa-times"></i>
					</div>
				</div>
				<div class="piotnet-widget__container"></div>
			</div>
		</script>
		<?php
	}

	private function division_output_template() {
		?>
		<script type="text/html" data-piotnetforms-template id="piotnetforms-division-output-template">
			<%
			const division_type = data.division_type;
			%>
			<div <%= view.render_attributes('widget_wrapper_editor') %>>
				<div class="<%= division_type %>__controls">
					<div class="<%= division_type %>__controls-item <%= division_type %>__controls-item--edit" title="Edit" data-piotnet-control-edit>
						<i class="fas fa-th"></i>
					</div>
					<div class="<%= division_type %>__controls-item <%= division_type %>__controls-item--duplicate" title="Duplicate" data-piotnet-control-duplicate>
						<i class="far fa-clone"></i>
					</div>
					<div class="<%= division_type %>__controls-item <%= division_type %>__controls-item--remove" title="Delete" data-piotnet-control-remove>
						<i class="fas fa-times"></i>
					</div>
				</div>
				<div <%= view.render_attributes('widget_wrapper_container') %>></div>
			</div>
		</script>
		<?php
	}

	public function register_post_type() {
		register_post_type(
			$this->slug,
			[
				'labels'       => [
					'name'          => __( $this->post_type_name, 'piotnetforms' ),
					'singular_name' => __( $this->post_type_name, 'piotnetforms' ),
				],
				'public'       => true,
				'has_archive'  => true,
				'show_in_menu' => false,
				'supports'     => [
					'title',
					'custom-fields',
				],
			]
		);

		remove_post_type_support( $this->slug, 'editor' );
	}

	public function single_template($single) {
	    global $post;
	    if ( $post->post_type == 'piotnetforms' ) {
	        return plugin_dir_path( __FILE__ ) . 'inc/templates/single-template.php';
	    }
	    return $single;
	}

	public function admin_menu() {

		add_menu_page(
			$this->plugin_name,
			$this->plugin_name,
			'edit_others_posts',
			$this->slug,
			[ $this, 'settings_page' ],
			'dashicons-piotnetforms-icon'
		);

		add_submenu_page( $this->slug, 'Settings', 'Settings', 'edit_others_posts', $this->slug, [ $this, 'settings_page' ] );

		add_submenu_page( $this->slug, 'Forms', 'Forms', 'edit_others_posts', 'edit.php?post_type=' . $this->slug );

		add_submenu_page( $this->slug, 'Import', 'Import', 'edit_others_posts', 'import-piotnetforms', [ $this, 'import_page' ] );

		add_submenu_page($this->slug, 'Database', 'Database', 'manage_options', 'edit.php?post_type=piotnetforms-data');

		add_submenu_page($this->slug, 'Abandonment', 'Abandonment', 'manage_options', 'edit.php?post_type=piotnetforms-aban');

		add_submenu_page($this->slug, 'Booking', 'Booking', 'manage_options', 'edit.php?post_type=piotnetforms-book');

		add_submenu_page($this->slug, 'PDF Custom Font', 'PDF Custom Font', 'manage_options', 'edit.php?post_type=piotnetforms-fonts');

		add_action( 'admin_init', [ $this, 'piotnet_base_settings' ] );
	}

	public function piotnet_base_settings() {
		// register_setting( $this->slug . '-settings-group', $this->slug . '-settings' );
		// register_setting( $this->slug . '-settings-group', $this->slug . '-settings-object' );
		// register_setting( $this->slug . '-settings-group', $this->slug . '-css' );

		register_setting( 'piotnetforms-google-sheets-group', 'piotnetforms-google-sheets-client-id' );
		register_setting( 'piotnetforms-google-sheets-group', 'piotnetforms-google-sheets-client-secret' );

		register_setting( 'piotnetforms-google-calendar-group', 'piotnetforms-google-calendar-client-api-key' );
		register_setting( 'piotnetforms-google-calendar-group', 'piotnetforms-google-calendar-client-id' );
		register_setting( 'piotnetforms-google-calendar-group', 'piotnetforms-google-calendar-client-secret' );

		register_setting( 'piotnetforms-google-maps-group', 'piotnetforms-google-maps-api-key' );

		register_setting( 'piotnetforms-stripe-group', 'piotnetforms-stripe-publishable-key' );
		register_setting( 'piotnetforms-stripe-group', 'piotnetforms-stripe-secret-key' );

		register_setting( 'piotnetforms-mailchimp-group', 'piotnetforms-mailchimp-api-key' );

		register_setting( 'piotnetforms-mailerlite-group', 'piotnetforms-mailerlite-api-key' );

		register_setting( 'piotnetforms-activecampaign-group', 'piotnetforms-activecampaign-api-key' );
		register_setting( 'piotnetforms-activecampaign-group', 'piotnetforms-activecampaign-api-url' );

		register_setting( 'piotnetforms-recaptcha-group', 'piotnetforms-recaptcha-site-key' );
		register_setting( 'piotnetforms-recaptcha-group', 'piotnetforms-recaptcha-secret-key' );

		register_setting( 'piotnetforms-recaptcha-group', 'piotnetforms-mailerLite-api-key' );

		register_setting( 'piotnetforms-getresponse-group', 'piotnetforms-getresponse-api-key' );

		register_setting( 'piotnetforms-zoho-group', 'piotnetforms-zoho-domain' );
		register_setting( 'piotnetforms-zoho-group', 'piotnetforms-zoho-client-id' );
		register_setting( 'piotnetforms-zoho-group', 'piotnetforms-zoho-client-secret' );
		register_setting( 'piotnetforms-zoho-group', 'piotnetforms-zoho-refresh-token' );
		register_setting( 'piotnetforms-zoho-group', 'piotnetforms-zoho-token' );

		register_setting( 'piotnetforms-paypal-group', 'piotnetforms-paypal-client-id' );

		register_setting( 'piotnetforms-twilio-group', 'piotnetforms-twilio-account-sid' );
		register_setting( 'piotnetforms-twilio-group', 'piotnetforms-twilio-author-token' );

		register_setting( 'piotnetforms-sendfox-group', 'piotnetforms-sendfox-access-token' );

		register_setting( 'piotnetforms-settings-group', 'piotnetforms-username' );
		register_setting( 'piotnetforms-settings-group', 'piotnetforms-password' );
	}

	public function settings_page() {

		require_once __DIR__ . '/inc/settings/settings-page.php';

	}

	public function import_page() {

		require_once __DIR__ . '/inc/settings/import-page.php';

	}

	public function admin_enqueue() {
		wp_enqueue_style( $this->slug . '-admin-css', plugin_dir_url( __FILE__ ) . 'assets/css/minify/admin.min.css', false, PIOTNETFORMS_PRO_VERSION );
	}

	public function set_custom_edit_columns( $columns ) {
		$columns['piotnet-widget-shortcode'] = __( 'Shortcode', 'piotnetforms' );
		return $columns;
	}

	public function custom_column( $column, $post_id ) {
		switch ( $column ) {
			case 'piotnet-widget-shortcode':
				echo '<input class="piotnet-widget-shortcode-input" type="text" readonly="" onfocus="this.select()" value="[' . $this->slug . ' id=' . $post_id  . ']">';
				break;
		}
	}

	public function plugin_redirect() {

		if ( get_option( 'piotnetforms_do_activation_redirect', false ) ) {
			delete_option( 'piotnetforms_do_activation_redirect' );
			flush_rewrite_rules();
			wp_redirect( 'admin.php?page=piotnetforms' );
		}

	}

	public function plugin_row_meta( $links, $file ) {

		if ( strpos( $file, 'piotnetforms' ) !== false ) {
			$links[] = '<a href="https://piotnetforms.com/tutorials" target="_blank">' . esc_html__( 'Video Tutorials', 'piotnetforms' ) . '</a>';
			$links[] = '<a href="https://piotnetforms.com/change-log" target="_blank">' . esc_html__( 'Change Log', 'piotnetforms' ) . '</a>';
		}
		return $links;

	}

	public function plugin_activate() {

		add_option( 'piotnetforms_do_activation_redirect', true );
		add_option( 'piotnetforms_do_flush', true );

	}

    /**
     * @return piotnetforms_Base_Control[]
     */
    private function new_widget( string $class_name ) {
        return new $class_name();
    }

	public function piotnetforms_render_loop( $loop, $post_id ) {

		ob_start();

		foreach ( $loop as $widget_item ) {
			$widget            = $this->new_widget( $widget_item['class_name'] );
			$widget->settings  = $widget_item['settings'];
			$widget_id         = $widget_item['id'];
			$widget->widget_id = $widget_id;
			$widget->post_id   = $post_id;

			if ( ! empty( $widget_item['fonts'] ) ) {
				$fonts = $widget_item['fonts'];
				if ( ! empty( $fonts ) ) {
					echo '<script>jQuery(document).ready(function( $ ) {';
					foreach ( $fonts as $font ) :
						?>
						$('head').append('<link href="<?php echo $font; ?>" rel="stylesheet">');
						<?php
					endforeach;
					echo '})</script>';
				}
			}

			$widget_type = $widget->get_type();
			if ( $widget_type === 'section' || $widget_type === 'column' ) {
				$visibility = @$widget->widget_visibility();
				if ($visibility) {
					echo @$widget->output_wrapper_start( $widget_id );
					$visibility = @$widget->widget_visibility();
					if ( isset( $widget_item['elements'] ) ) {
						echo @$this->piotnetforms_render_loop( $widget_item['elements'], $post_id );
					}
				}
			} else {
				$output = @$widget->output( $widget_id );
				$output = @$this->piotnetforms_dynamic_tags( $output );
				echo @$output;
			}

			if ( $widget_type === 'section' || $widget_type === 'column' ) {
				echo @$widget->output_wrapper_end( $widget_id );
			}
		}

		return ob_get_clean();
	}

	public function piotnetforms_dynamic_tags( $output ) {

		if ( stripos( $output, '{{' ) !== false && stripos( $output, '}}' ) !== false ) {
			$pattern = '~\{\{\s*(.*?)\s*\}\}~';
			preg_match_all( $pattern, $output, $matches );
			$dynamic_tags = [];

			if ( ! empty( $matches[1] ) ) {
				$matches = array_unique( $matches[1] );

				foreach ( $matches as $key => $match ) {
					if ( stripos( $match, '|' ) !== false ) {
						$match_attr = explode( '|', $match );
						$attr_array = [];
						foreach ( $match_attr as $key_attr => $value_attr ) {
							if ( $key_attr != 0 ) {
								$attr                           = explode( ':', $value_attr, 2 );
								$attr_array[ trim( $attr[0] ) ] = trim( $attr[1] );
							}
						}

						$dynamic_tags[] = [
							'dynamic_tag' => '{{' . $match . '}}',
							'name'        => trim( $match_attr[0] ),
							'attr'        => $attr_array,
						];
					} else {
						$dynamic_tags[] = [
							'dynamic_tag' => '{{' . $match . '}}',
							'name'        => trim( $match ),
						];
					}
				}
			}

			if ( ! empty( $dynamic_tags ) ) {
				foreach ( $dynamic_tags as $tag ) {
					$tag_value = '';

					if ( $tag['name'] == 'current_date_time' ) {
						if ( empty( $tag['attr']['date_format'] ) ) {
							$tag_value = date( 'Y-m-d H:i:s' );
						} else {
							$tag_value = date( $tag['attr']['date_format'] );
						}
					}

					if ( $tag['name'] == 'request' ) {
						if ( !empty( $tag['attr']['parameter'] ) ) {
							$tag_value = $_REQUEST[ $tag['attr']['parameter'] ];
						}
					}

					if ( $tag['name'] == 'user_info' ) {
						if (is_user_logged_in()) {
							if ( !empty( $tag['attr']['meta'] ) ) {
								$meta = $tag['attr']['meta'];
								$current_user = wp_get_current_user();

								switch ( $meta ) {
									case 'ID':
									case 'user_login':
									case 'user_nicename':
									case 'user_email':
									case 'user_url':
									case 'user_registered':
									case 'user_status':
									case 'display_name':
										$tag_value = $current_user->$meta;
										break;
									default:
										$tag_value = get_user_meta( get_current_user_id(), $tag['attr']['meta'], true );
								}
							}
						}
					}

					if ( $tag['name'] == 'post_id' ) {
						$tag_value = get_the_ID();
					}

					if ( $tag['name'] == 'post_title' ) {
						$tag_value = get_the_title();
					}

					if ( $tag['name'] == 'post_url' ) {
						$tag_value = get_permalink();
					}

					if ( $tag['name'] == 'post_url' ) {
						$tag_value = get_permalink();
					}

					if ( $tag['name'] == 'shortcode' ) {
						if ( !empty( $tag['attr']['shortcode'] ) ) {
							$tag_value = do_shortcode( $tag['attr']['shortcode'] );
						}
					}

					$output = str_replace( $tag['dynamic_tag'], $tag_value, $output );
				}
			}
		}

		return $output;
	}

	// Forms functions

	public function piotnetforms_abandonment_database_post_type() {
	    register_post_type('piotnetforms-aban',
			array(
				'labels'      => array(
					'name'          => __('Form Abandonment'),
					'singular_name' => __('Form Abandonment'),
				),
				'public'      => true,
				'has_archive' => true,
				'show_in_menu' => false,
				'publicly_queryable'  => false,
				'supports' => array(
					'title',
					'custom-fields',
				),
			)
	    );

	    remove_post_type_support( 'piotnetforms-aban', 'editor' );
	}

	public function piotnetforms_database_post_type() {
	    register_post_type('piotnetforms-data',
			array(
				'labels'      => array(
					'name'          => __('Piotnetforms Database'),
					'singular_name' => __('Piotnetforms Database'),
				),
				'public'      => true,
				'has_archive' => true,
				'show_in_menu' => false,
				'publicly_queryable'  => false,
				'supports' => array(
					'title',
					'custom-fields',
				),
			)
	    );

	    remove_post_type_support( 'piotnetforms-database', 'editor' );
	}

	public function piotnetforms_booking_post_type() {
	    register_post_type('piotnetforms-book',
			array(
				'labels'      => array(
					'name'          => __('Form Booking'),
					'singular_name' => __('Form Booking'),
				),
				'public'      => true,
				'has_archive' => true,
				'show_in_menu' => false,
				'supports' => array(
					'title',
					'custom-fields',
				),
			)
	    );
	}

	public function piotnetforms_woocommerce_checkout_load( $is_checkout ) {

		if ( ! is_admin() ) {

			$shortcode = get_post_meta( get_the_ID(), '_piotnetforms_shortcode_in_post', true );
			if (!empty($shortcode)) {
				$shortcode = explode('|', $shortcode);
				foreach ($shortcode as $shortcode_item) {
					$shortcode_atts = shortcode_parse_atts($shortcode_item);
					if (!empty($shortcode_atts['id'])) {
						$raw_data = get_post_meta( intval($shortcode_atts['id']), '_piotnetforms_data', true );
						if (strpos($raw_data, '"piotnetforms_woocommerce_checkout_product_id":') !== false) {
							$is_checkout = true;
						}
					}
				}
			}

			$raw_data = get_post_meta( get_the_ID(), '_piotnetforms_data', true );
			if (strpos($raw_data, '"piotnetforms_woocommerce_checkout_product_id":') !== false) {
				$is_checkout = true;
			}
		}

		return $is_checkout;
	}

	public function piotnetforms_woocommerce_checkout_load_cart() {

		if ( ! is_admin() ) {

			$shortcode = get_post_meta( get_the_ID(), '_piotnetforms_shortcode_in_post', true );
			if (!empty($shortcode)) {
				$shortcode = explode('|', $shortcode);
				foreach ($shortcode as $shortcode_item) {
					$shortcode_atts = shortcode_parse_atts($shortcode_item);
					if (!empty($shortcode_atts['id'])) {
						$raw_data = get_post_meta( intval($shortcode_atts['id']), '_piotnetforms_data', true );
						if (strpos($raw_data, '"piotnetforms_woocommerce_checkout_product_id":') !== false) {

							WC()->cart->empty_cart();

							$raw_data = explode('"piotnetforms_woocommerce_checkout_product_id":"', $raw_data);
							$string = $raw_data[1];
							$pos = stripos($string, '"');
							$product_id = substr($string,0,$pos);

							WC()->cart->add_to_cart( $product_id, 1 );
						}
					}
				}
			}

			$raw_data = get_post_meta( get_the_ID(), '_piotnetforms_data', true );
			if (strpos($raw_data, '"piotnetforms_woocommerce_checkout_product_id":') !== false) {

				WC()->cart->empty_cart();

				$raw_data = explode('"piotnetforms_woocommerce_checkout_product_id":"', $raw_data);
				$string = $raw_data[1];
				$pos = stripos($string, '"');
				$product_id = substr($string,0,$pos);

				WC()->cart->add_to_cart( $product_id, 1 );
			}
		}
	}

	public function piotnetforms_woocommerce_checkout_remove_checkout_fields( $fields ){

		$shortcode = get_post_meta( get_the_ID(), '_piotnetforms_shortcode_in_post', true );
		if (!empty($shortcode)) {
			$shortcode = explode('|', $shortcode);
			foreach ($shortcode as $shortcode_item) {
				$shortcode_atts = shortcode_parse_atts($shortcode_item);
				if (!empty($shortcode_atts['id'])) {
					$raw_data = get_post_meta( intval($shortcode_atts['id']), '_piotnetforms_data', true );
					if (strpos($raw_data, '"piotnetforms_woocommerce_checkout_remove_fields":') !== false || get_post_meta( get_the_ID(), '_piotnetforms_shortcode_in_post', true ) == '1') {
						$raw_data = stripslashes($raw_data);
						$raw_data = explode('"piotnetforms_woocommerce_checkout_remove_fields":', $raw_data);
						$string = $raw_data[1];
						$pos = stripos($string, ']'); // Fix Alert [
						$remove_fields = json_decode(substr($string,0,$pos) . ']'); // Fix Alert [

						if (!empty($remove_fields)) {
							foreach ($remove_fields as $field) {
								if (strpos($field, 'billing') !== false) {
									unset($fields['billing'][$field]);
								}
								if (strpos($field, 'order') !== false) {
									unset($fields['order'][$field]);
								}
								if (strpos($field, 'shipping') !== false) {
									unset($fields['shipping'][$field]);
								}
							}

						}
					}
				}
			}
		}

	    $raw_data = get_post_meta( get_the_ID(), '_piotnetforms_data', true );
		if (strpos($raw_data, '"piotnetforms_woocommerce_checkout_remove_fields":') !== false || get_post_meta( get_the_ID(), '_piotnetforms_shortcode_in_post', true ) == '1') {
			$raw_data = stripslashes($raw_data);
			$raw_data = explode('"piotnetforms_woocommerce_checkout_remove_fields":', $raw_data);
			$string = $raw_data[1];
			$pos = stripos($string, ']'); // Fix Alert [
			$remove_fields = json_decode(substr($string,0,$pos) . ']'); // Fix Alert [

			if (!empty($remove_fields)) {
				foreach ($remove_fields as $field) {
					if (strpos($field, 'billing') !== false) {
						unset($fields['billing'][$field]);
					}
					if (strpos($field, 'order') !== false) {
						unset($fields['order'][$field]);
					}
					if (strpos($field, 'shipping') !== false) {
						unset($fields['shipping'][$field]);
					}
				}

			}
		}

	    return $fields;

	}

	public function piotnetforms_filter(){
	    if (isset($_GET['post_type'])) {
	        $type = $_GET['post_type'];
		    if ( $type == 'piotnetforms-data' || $type == 'piotnetforms-aban' ){
		        $form_id = array();
		        $submissions = new WP_Query( array(
		            'post_type' => $type,
		            'posts_per_page' => -1,
	            ) );

	            if ($submissions->have_posts()) : while ( $submissions->have_posts()) : $submissions->the_post();
	                $form_id[get_post_meta(get_the_ID(),'form_id',true)] = get_post_meta(get_the_ID(),'form_id',true);
	            endwhile; endif; wp_reset_postdata();
		        ?>
		        <select name="form_id">
		        <option value=""><?php _e('All Form ID', 'piotnetforms'); ?></option>
		        <?php
		            $current_v = isset($_GET['form_id'])? $_GET['form_id']:'';
		            foreach ($form_id as $label => $value) {
		                printf
		                    (
		                        '<option value="%s"%s>%s</option>',
		                        $value,
		                        $value == $current_v? ' selected="selected"':'',
		                        $label
		                    );
		                }
		        ?>
		        </select>
		        <?php
		    }
	    }
	}

	public function piotnetforms_filter_posts( $query ){
	    global $pagenow;
	    if (isset($_GET['post_type'])) {
	        $type = $_GET['post_type'];
	        if ( $type == 'piotnetforms-data' || $type == 'piotnetforms-aban' ){
			    if ( is_admin() && $pagenow=='edit.php' && isset($_GET['form_id']) && $_GET['form_id'] != '' && $query->is_main_query()) {
			        $query->query_vars['meta_key'] = 'form_id';
			        $query->query_vars['meta_value'] = $_GET['form_id'];
			    }
		    }
	    }
	}

	public function piotnetforms_filter_column($defaults) {
	    $defaults['form_id'] = 'Form ID';
	    $defaults['status'] = 'Status';
	    return $defaults;
	}

	public function piotnetforms_filter_column_content($column_name, $post_ID) {
	    if ($column_name == 'form_id') {
	        echo get_post_meta($post_ID,'form_id',true);
	    }

	    if ($column_name == 'status') {
	    	$status = !empty( get_post_meta($post_ID,'status',true) ) ? get_post_meta($post_ID,'status',true) : 'Success';
	        echo $status;
	    }
	}

	public function piotnetforms_filter_export_btn() {
	    if (isset($_GET['post_type'])) {
	        $type = $_GET['post_type'];
	        if ( $type == 'piotnetforms-data' || $type == 'piotnetforms-aban' ){
	    ?>
		    <script type="text/javascript">
		        jQuery(document).ready( function($) {
		        	<?php if ( !empty($_GET['form_id']) ) : ?>
		            	$('.tablenav.top .clear, .tablenav.bottom .clear').before('<a class="button button-primary user_export_button" style="margin-top:3px;" href="<?php echo esc_url( get_admin_url( null, 'admin-ajax.php?action=piotnetforms_export_form_submission' . '&post_status=' . $_REQUEST['post_status'] . '&post_type=' . $_REQUEST['post_type'] . '&m=' .$_REQUEST['m'] . '&form_id=' . $_REQUEST['form_id'] ) ); ?>"><?php esc_attr_e('Click on Filter and then click here to export as csv', 'piotnetforms');?></a>');
	            	<?php else : ?>
	            		$('.tablenav.top .clear, .tablenav.bottom .clear').before('<input class="button button-primary user_export_button" style="margin-top:3px;" type="submit" value="<?php esc_attr_e('Select Form ID and click on Filter to export as csv', 'piotnetforms');?>" />');
            		<?php endif; ?>
		        });
		    </script>
	    <?php
			}
		}
	}

	public function enqueue_scripts_woocommerce_sales_funnels() {
		wp_register_script( 'piotnetforms-woocommerce-sales-funnels-script', plugin_dir_url( __FILE__ ) . 'assets/js/minify/woocommerce.min.js', array('jquery'), PIOTNETFORMS_PRO_VERSION );
		wp_register_style( 'piotnetforms-woocommerce-sales-funnels-style', plugin_dir_url( __FILE__ ) . 'assets/css/minify/woocommerce.min.css', [], PIOTNETFORMS_PRO_VERSION );
	}

	public function piotnetforms_woocommerce_checkout_order_processed( $order_id ){
	    $order = wc_get_order( $order_id );
	    $order_items = $order->get_items();

	    foreach ($order_items as $key => $value) {
            $piotnetforms_booking = wc_get_order_item_meta( $key, 'piotnetforms_booking', true );
            $piotnetforms_booking_fields = wc_get_order_item_meta( $key, 'piotnetforms_booking_fields', true );

            if (!empty($piotnetforms_booking)) {
            	$piotnetforms_booking = json_decode( $piotnetforms_booking, true );
            	$piotnetforms_booking_fields = json_decode( $piotnetforms_booking_fields, true );

            	$my_post = array(
					'post_title'    => wp_strip_all_tags( 'Piotnet Addons Form Database ' ),
					'post_status'   => 'publish',
					'post_type'		=> 'piotnetforms-data',
				);

				$form_database_post_id = wp_insert_post( $my_post );

				if (!empty($form_database_post_id)) {

					$my_post_update = array(
						'ID'           => $form_database_post_id,
						'post_title'   => '#' . $form_database_post_id,
					);
					wp_update_post( $my_post_update );

					foreach ($piotnetforms_booking_fields as $field) {
						update_post_meta( $form_database_post_id, $field['name'], $field['value'] );
					}

				}

            	foreach ($piotnetforms_booking as $booking) {
            		$date = $booking['piotnetforms_booking_date'];
					$slot_availble = 0;
					$slot = $booking['piotnetforms_booking_slot'];
					$slot_query = new WP_Query(array(
						'posts_per_page' => -1 ,
						'post_type' => 'piotnetforms-book',
						'meta_query' => array(
					       'relation' => 'AND',
						        array(
						            'key' => 'piotnetforms_booking_id',
						            'value' => $booking['piotnetforms_booking_id'],
						            'type' => 'CHAR',
						            'compare' => '=',
						        ),
						        array(
						            'key' => 'piotnetforms_booking_slot_id',
						            'value' => $booking['piotnetforms_booking_slot_id'],
						            'type' => 'CHAR',
						            'compare' => '=',
						        ),
						        array(
						            'key' => 'piotnetforms_booking_date',
						            'value' => $date,
						            'type' => 'CHAR',
						            'compare' => '=',
						        ),
						        array(
						            'key' => 'payment_status',
						            'value' => 'succeeded',
						            'type' => 'CHAR',
						            'compare' => '=',
						        ),
						),
					));

					$slot_reserved = 0;

					if ($slot_query->have_posts()) {
						while($slot_query->have_posts()) {
							$slot_query->the_post();
							$slot_reserved += intval( get_post_meta(get_the_ID(), 'piotnetforms_booking_quantity', true) );
						}
					}

					wp_reset_postdata();

					$slot_availble = $slot - $slot_reserved;

					$booking_slot = 1;

					if (!empty($booking['piotnetforms_booking_slot_quantity_field'])) {
						$booking_quantity_field_name = str_replace('"]', '', str_replace('[field id="', '', $booking['piotnetforms_booking_slot_quantity_field']) );

						foreach ($piotnetforms_booking_fields as $field) {
							if ($booking_quantity_field_name == $field['name']) {
							 	$booking_slot = intval( $field['value'] );
							}
						}
					}

					if ($slot_availble >= $booking_slot && !empty($slot_availble) && !empty($booking_slot)) {
						$booking_post = array(
							'post_title'    =>  '#' . $form_database_post_id . ' ' . $booking['piotnetforms_booking_title'],
							'post_status'   => 'publish',
							'post_type'		=> 'piotnetforms-book',
						);

						$form_booking_posts_id = wp_insert_post( $booking_post );

						if (empty($form_database_post_id)) {
							$form_database_post_id = $form_booking_posts_id;
							$booking_post = array(
								'ID' => $form_booking_posts_id,
								'post_title' =>  '#' . $form_booking_posts_id . ' ' . $booking['piotnetforms_booking_title'],
							);
							wp_update_post( $booking_post );
						}

						foreach ($piotnetforms_booking_fields as $field) {
							update_post_meta( $form_booking_posts_id, $field['name'], $field['value'] );
						}

						foreach ($booking as $key_booking => $booking_data) {
							update_post_meta( $form_booking_posts_id, $key_booking, $booking_data );
						}

						update_post_meta( $form_booking_posts_id, 'piotnetforms_booking_date', $date );
						update_post_meta( $form_booking_posts_id, 'piotnetforms_booking_quantity', $booking_slot );
						update_post_meta( $form_booking_posts_id, 'order_id', $form_database_post_id );
						update_post_meta( $form_booking_posts_id, 'order_id_woocommerce', $order_id );
						update_post_meta( $form_booking_posts_id, 'payment_status', 'succeeded' );
					}
            	}

            }

            wc_delete_order_item_meta( $key, 'piotnetforms_booking' );
            wc_delete_order_item_meta( $key, 'piotnetforms_booking_fields' );
        }
	}

	public function piotnetforms_apply_custom_price_to_cart_item( $cart ) {
		if ( class_exists( 'WooCommerce' ) ) {
	        foreach ( $cart->get_cart() as $cart_item ) {
		        if( isset($cart_item['piotnetforms_custom_price']) ) {
		            $cart_item['data']->set_price( $cart_item['piotnetforms_custom_price'] );
		        }
		    }
	    }
    }

    public function admin_notice_missing_main_plugin() {
        if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

        $message = sprintf(
            esc_html__( '"%1$s" requires "%2$s" to be installed and activated. ', 'pafe' ),
            '<strong>' . esc_html__( 'Piotnet Forms Pro', 'pafe' ) . '</strong>',
            '<strong>' . esc_html__( 'Piotnet Forms Free Version', 'pafe' ) . '</strong>'
        ) . ' Get it now <a href="https://wordpress.org/plugins/piotnetforms/" target="_blank">https://wordpress.org/plugins/piotnetforms/</a>';

        printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
	}
	//PDF Post Type
	public function piotnetforms_pdf_font_post_type() {
	    register_post_type('piotnetforms-fonts',
			array(
				'labels'      => array(
					'name'          => __('Piotnetforms PDF Custom Font'),
					'singular_name' => __('Piotnetforms PDF Custom Font'),
				),
				'public'      => true,
				'has_archive' => true,
				'show_in_menu' => false,
				'publicly_queryable'  => false,
				'supports' => array(
					'title',
					'editor',
				),
			)
	    );

	    remove_post_type_support( 'piotnetforms-fonts', 'editor' );
	}

	public function piotnetforms_pdf_metabox(){
		add_meta_box('piotnetforms-pdf', 'PDF custom font (TTF)', [$this, 'piotnetforms_pdf_metabox_output'], 'piotnetforms-fonts');
	}

	public function piotnetforms_pdf_metabox_output($post){
		$pdf_font = get_post_meta($post->ID, '_piotnetforms_pdf_font', true);
		$html = '<div class="piotnetforms-custom-font">
			<input id="piotnetforms-pdf-font-url" type="text" name="piotnetforms_pdf_font" value="'.$pdf_font.'" readonly/>
			<button type="submit" id="piotnetforms-pdf-upload-font" class="button">Upload/Add font</button>
			<button type="submit" id="piotnetforms-pdf-remove-font" class="button">Remove font</button>
		</div>';
		echo $html;
	}
	
	public function piotnetforms_pdf_save_custom_font($post_id){
		$pdf_font = !empty($_POST['piotnetforms_pdf_font']) ? $_POST['piotnetforms_pdf_font'] : '';
		update_post_meta($post_id, '_piotnetforms_pdf_font', $pdf_font);
	}
	
	public function piotnetforms_add_custom_upload_mimes($existing_mimes) {
		$existing_mimes['ttf'] = 'application/x-font-ttf';
		//$existing_mimes['otf'] = 'application/x-font-otf';
        //$existing_mimes['woff'] = 'application/x-font-woff';
        return $existing_mimes;
   }
}

$piotnetforms_pro = new Piotnetforms_Pro();
