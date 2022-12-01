<?php

class GF_Pending_Activations {

	protected $_slug  = 'gravityformsuserregistration_pending_activations';
	protected $_title = 'Pending Activations';

	private static $_instance = null;

	public static function get_instance() {

		if ( self::$_instance == null ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	public function __construct() {

		if ( doing_action( 'init' ) || did_action( 'init' ) ) {
			$this->init();
		} else {
			add_action( 'init', array( $this, 'init' ) );
		}

	}

	public function init() {

		add_action( 'gform_form_settings_menu', array( $this, 'add_form_settings_menu' ), 10, 2 );
		add_action( 'admin_menu', array( $this, 'register_submenu_page_under_users' ) );

		if ( self::is_pending_activations_page() ) {
			add_action( 'admin_notices', array( $this, 'display_pending_activation_page_notices' ) );
		}

		if ( gf_user_registration()->is_gravityforms_supported( '2.0' ) ) {
			add_filter( 'gform_entry_detail_meta_boxes', array( $this, 'register_meta_box' ), 10, 2 );
		} else {
			add_action( 'gform_entry_detail_sidebar_middle', array( $this, 'entry_pending_activation_meta_box' ), 10, 2 );
		}

		$view    = rgget( 'view' );
		$subview = rgget( 'subview' );

		if ( rgget( 'page' ) == 'gf_edit_forms' && $view == 'settings' && $subview == $this->_slug ) {
			require_once( GFCommon::get_base_path() . '/tooltips.php' );
			add_action( 'gform_form_settings_page_' . $this->_slug, array( $this, 'form_settings_page' ) );
		}

		add_action( 'gform_userregistration_cron', array( $this, 'cron_remove_passwords' ) );

	}

	/**
	 * Remove encrypted passwords after a short period.
	 *
	 * @return void
	 */
	public function cron_remove_passwords() {
		global $wpdb;

		if ( ! is_multisite() ) {
			require_once gf_user_registration()->get_base_path() . '/includes/signups.php';
			GFUserSignups::prep_signups_functionality();
		}

		$sql = "SELECT signup_id, meta FROM {$wpdb->signups} 
				WHERE registered < SUBDATE( CURDATE(), INTERVAL 7 DAY ) 
				AND meta LIKE '%s:8:\"password\";%'
				AND meta NOT LIKE '%s:8:\"password\";s:0:\"\";%'
				LIMIT 1000";

		$results = $wpdb->get_results( $sql );

		foreach ( $results as $signup ) {

			$signup->meta = maybe_unserialize( $signup->meta );
			if ( ! is_array( $signup->meta ) ) {
				$signup->meta = array();
			}

			$signup->meta['password'] = '';

			$wpdb->update( $wpdb->signups, array(
				'meta' => serialize( $signup->meta ),
			), array( 'signup_id' => $signup->signup_id ) );

		}

	}

	public function add_form_settings_menu( $tabs, $form_id ) {
		if ( gf_user_registration()->has_feed_type( 'create', array( 'id' => $form_id ) ) ) {
			$tabs[] = array(
				'name'         => $this->_slug,
				'label'        => __( $this->_title, 'gravityformsuserregistration' ),
				'icon'         => file_get_contents( gf_user_registration()->get_base_path() . '/images/pending-menu-icon.svg' ),
				'capabilities' => array( 'promote_users', 'gravityforms_user_registration', 'gform_full_access' ),
			);
		}

		return $tabs;
	}

	public function form_settings_page() {

		$form    = $this->get_current_form();
		$form_id = $form['id'];
		$form    = gf_apply_filters( 'gform_admin_pre_render', $form_id, $form );

		GFFormSettings::page_header( __( $this->_title, 'gravityformsuserregistration' ) );

		$this->maybe_handle_submission();

		// Prepare panel class.
		$panel_class = gf_user_registration()->is_gravityforms_supported( '2.5-dev-1' ) ? 'gform-settings-panel' : 'gform_panel gform_panel_form_settings';
		?>

		<div class="<?php echo $panel_class; ?>" id="form_settings">

			<?php $this->get_page_content(); ?>

		</div>

		<?php
		GFFormSettings::page_footer();
	}

	public function get_current_form() {
		return rgempty( 'id', $_GET ) ? false : GFFormsModel::get_form_meta( rgget( 'id' ) );
	}

	/**
	 * Determine whether the current page is the pending activations page.
	 *
	 * @since 4.7
	 *
	 * @return bool
	 */
	public static function is_pending_activations_page() {
		global $pagenow;

		return $pagenow === 'users.php' && sanitize_text_field( rgget( 'page' ) === 'gf-pending-activations' );
	}

	/*
	 * Displays Pending Activations list.
	 *
	 * @since Unknown
	 */
	public static function get_page_content() {
		global $pagenow;

		if (
			! gf_user_registration()->is_gravityforms_supported( '2.5-dev-1' )
			|| self::is_pending_activations_page()
		) {
			return self::get_legacy_page_content();
		}

		require_once( gf_user_registration()->get_base_path() . '/includes/class-gf-pending-activations-list.php' );

		$form      = rgget( 'id' ) ? GFAPI::get_form( rgget( 'id' ) ) : false;
		$is_global = ! $form;

		?>

		<style type="text/css">
			.nav-tab-wrapper { margin: 0 0 10px !important; }
			.fixed .column-date { white-space: nowrap; width: auto; }
		</style>

		<?php
		printf( '<header class="gform-settings-panel__header"><h4 class="gform-settings-panel__title"><span>%s</span></h4></header>', __( 'Pending Activations', 'gravityformsuserregistration' ) );
		?>

		<div class="gform-settings-panel__content">
			<form id="list_form" method="post" action="">

				<?php
				$table = new GF_Pending_Activations_List();
				$table->prepare_items();
				$table->display();
				?>

				<input type="hidden" name="is_submit" value="1" />
				<input type="hidden" id="single_action" name="single_action" value="" />
				<input type="hidden" id="item" name="item" value="" />

				<?php wp_nonce_field( 'action', 'action_nonce' ); ?>

			</form>
		</div>

		<script type="text/javascript">

			function singleItemAction(action, activationKey) {
				jQuery('#item').val(activationKey);
				jQuery('#single_action').val(action);
				jQuery('#list_form')[0].submit();
			}

		</script>

		<?php
	}

	/*
	 * Displays Pending Activations list for Gravity Forms <2.5.
	 *
	 * @since 4.5
	 */
	public static function get_legacy_page_content() {

		require_once( gf_user_registration()->get_base_path() . '/includes/class-gf-pending-activations-list.php' );

		$form      = rgget( 'id' ) ? GFAPI::get_form( rgget( 'id' ) ) : false;
		$is_global = ! $form;

		?>

		<style type="text/css">
			.nav-tab-wrapper { margin: 0 0 10px !important; }
			.fixed .column-date { white-space: nowrap; width: auto; }
		</style>

		<div class="wrap">

			<?php
			printf( '<%1$s>%2$s</%1$s>', $is_global ? 'h2' : 'h3', __( 'Pending Activations', 'gravityformsuserregistration' ) );
			?>

			<form id="list_form" method="post" action="">

				<?php
				$table = new GF_Pending_Activations_List();
				$table->prepare_items();
				$table->display();
				?>

				<input type="hidden" name="is_submit" value="1" />
				<input type="hidden" id="single_action" name="single_action" value="" />
				<input type="hidden" id="item" name="item" value="" />

				<?php wp_nonce_field( 'action', 'action_nonce' ); ?>

			</form>

		</div>

		<script type="text/javascript">

			function singleItemAction(action, activationKey) {
				jQuery('#item').val(activationKey);
				jQuery('#single_action').val(action);
				jQuery('#list_form')[0].submit();
			}

		</script>

		<?php
	}

	/**
	 * Get pending activations or total pending activations.
	 *
	 * @since unknown
	 *
	 * @param int   $form_id The Form ID
	 * @param array $args    {
	 *     Query args for returned results. Supported:
	 *
	 *     @type string order     ASC or DESC.
	 *     @type string orderby   Column to sort by.
	 *     @type int    page      Page number of results.
	 *     @type int    per_page  Number of results to return per page.
	 *     @type bool   get_total If total rows should be returned.
	 *     @type bool   lead_id   Filter results by lead ID.
	 * }
	 *
	 * @return mixed
	 */
	public static function get_pending_activations( $form_id, $args = array() ) {
		global $wpdb;

		if ( $form_id == 'all' ) {
			$form_id = '';
		}

		extract(
			wp_parse_args(
				$args,
				array(
					'order'     => 'DESC',
					'order_by'  => 'registered',
					'page'      => 1,
					'per_page'  => 10,
					'get_total' => false,
					'lead_id'   => false,
				)
			)
		);

		if ( ! is_multisite() ) {
			require_once( gf_user_registration()->get_base_path() . '/includes/signups.php' );
			GFUserSignups::prep_signups_functionality();
		}

		$where = array();

		if ( $form_id ) {
			$where[] = $wpdb->prepare( 'l.form_id = %d', $form_id );
		}

		if ( $lead_id ) {
			$where[] = $wpdb->prepare( 'l.id = %d', $lead_id );
		}

		$where[] = 's.active = 0';
		$where   = 'WHERE ' . implode( ' AND ', $where );

		$order        = "ORDER BY {$order_by} {$order}";
		$offset       = ( $page * $per_page ) - $per_page;
		$limit_offset = $get_total ? '' : "LIMIT $per_page OFFSET $offset";
		$method       = $get_total ? 'get_var' : 'get_results';

		if ( $form_id ) {
			$entry_table      = self::get_entry_table_name();
			$entry_meta_table = self::get_entry_meta_table_name();

			$entry_id_column = version_compare( self::get_gravityforms_db_version(), '2.3-dev-1', '<' ) ? 'lead_id' : 'entry_id';

			$charset_db = empty( $wpdb->charset ) ? 'utf8mb4' : $wpdb->charset;

			$collate = ! empty( $wpdb->collate ) ? " COLLATE {$wpdb->collate}" : '';

			$activation_key_col = 's.activation_key';
			$meta_value_col     = 'lm.meta_value';

			// Convert Charset only if necessary.
			$activation_key_charset = self::get_column_charset( 'activation_key', $wpdb->signups );
			$meta_value_charset     = self::get_column_charset( 'meta_value', $entry_meta_table );

			// Check if activation_key needs to be converted.
			if ( $activation_key_charset !== $charset_db ) {
				$activation_key_col = "CONVERT( s.activation_key USING {$charset_db} )";
			}

			// Check if meta_value needs to be converted.
			if ( $meta_value_charset !== $charset_db ) {
				$meta_value_col = "CONVERT( lm.meta_value USING {$charset_db} )";
			}

			$select = $get_total ? 'SELECT count(s.activation_key)' : 'SELECT s.*';
			$sql    = "
                $select FROM {$entry_meta_table} lm
                INNER JOIN {$wpdb->signups} s ON {$activation_key_col} = {$meta_value_col} {$collate} AND lm.meta_key = 'activation_key'
                INNER JOIN {$entry_table} l ON l.id = lm.{$entry_id_column}
                $where
                $order
                $limit_offset";

			$results = $wpdb->$method( $sql );
		} else {
			$select  = $get_total ? 'SELECT count(s.activation_key)' : 'SELECT s.*';
			$results = $wpdb->$method(
				"$select FROM $wpdb->signups s
                $where
                $order
                $limit_offset"
			);
		}

		return $results;
	}

	/**
	 * Get the character encoding for a column in a table.
	 *
	 * @since 4.6.2
	 *
	 * @param string $column_name Desired column to get charset.
	 * @param string $table_name  Desired table in which column is present.
	 * @param string $db_name     Desired database in which table is present.
	 *
	 * @return string|null
	 */
	public static function get_column_charset( $column_name, $table_name, $db_name = DB_NAME ) {
		global $wpdb;
		return $wpdb->get_var(
			$wpdb->prepare(
				'SELECT character_set_name FROM information_schema.`COLUMNS` WHERE table_schema = %s AND table_name = %s AND column_name = %s',
				$db_name,
				$table_name,
				$column_name
			)
		);
	}

	/**
	 * Handle User activation form submission.
	 */
	public static function handle_submission() {
		if ( ! wp_verify_nonce( rgpost( 'action_nonce' ), 'action' ) && ! check_admin_referer( 'action_nonce', 'action_nonce' ) ) {
			die( 'You have failed...' );
		}

		require_once( gf_user_registration()->get_base_path() . '/includes/signups.php' );
		GFUserSignups::prep_signups_functionality();

		$action = rgpost( 'single_action' );
		$action = ! $action ? rgpost( 'action' ) != -1 ? rgpost( 'action' ) : rgpost( 'action2' ) : $action;

		$items      = rgpost( 'item' ) ? array( rgpost( 'item' ) ) : rgpost( 'items' );
		$item_count = count( $items );
		$messages   = array();
		$errors     = array();

		foreach ( $items as $key ) {
			switch ( $action ) {

				case 'delete':
					$success = GFUserSignups::delete_signup( $key );

					if ( self::is_pending_activations_page() ) {
						break;
					}

					if ( $success ) {
						gf_user_registration()->add_message_once(
							_n(
								'User registration deleted.',
								'User registrations deleted.',
								count( $items ),
								'gravityformsuserregistration'
							)
						);
					} else {
						gf_user_registration()->add_error_message_once(
							_n(
								'There was an issue deleting this user registration.',
								'There was an issue deleting one or more selected user registrations.',
								count( $items ),
								'gravityformsuserregistration'
							)
						);
					}
					break;

				case 'activate':
					$userdata = GFUserSignups::activate_signup( $key );

					if ( self::is_pending_activations_page() ) {
						break;
					}

					if ( is_wp_error( $userdata ) ) {
						$error  = _n( 'There was an issue activating this item', 'There was an issue activating one or more selected items', count( $items ), 'gravityformsuserregistration' );
						$error .= ': ' . $userdata->get_error_message();
						gf_user_registration()->add_error_message_once( $error );
					} else {
						$message = _n( 'User activated.', 'Users activated.', count( $items ), 'gravityformsuserregistration' );
						gf_user_registration()->add_message_once( $message );
					}

					break;
			}
		}
	}

	/**
	 * Display the "User(s) activated." WordPress admin notice on the pending activations screen.
	 *
	 * @since 4.7
	 */
	public function display_pending_activation_page_notices() {
		$items  = $this->get_activation_request_items();
		$action = $this->get_pending_activation_action();

		if ( empty( $items ) || empty( $action ) ) {
			return;
		}

		if ( 'delete' === $action ) {
			printf(
				'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
				esc_html( _n( 'User registration deleted.', 'User registrations deleted.', count( $items ), 'gravityformsuserregistration' ) )
			);
			return;
		}

		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			esc_html( _n( 'User activated.', 'Users activated.', count( $items ), 'gravityformsuserregistration' ) )
		);
	}

	/**
	 * Get the action of the $_POST request - either activate, delete, or none.
	 *
	 * @since 4.7
	 *
	 * @return string
	 */
	private function get_pending_activation_action() {
		$action = array_values(
			array_filter(
				array( rgpost( 'single_action' ), rgpost( 'action' ) ),
				function( $action ) {
					return in_array( $action, array( 'activate', 'delete' ), true );
				}
			)
		);

		return count( $action ) === 1 ? $action[0] : '';
	}

	/**
	 * Get the items from the activation request.
	 *
	 * @since 4.7
	 *
	 * @return array
	 */
	private function get_activation_request_items() {
		$items = rgpost( 'item' );

		if ( ! empty( $items ) ) {
			return array( $items );
		}

		$items = rgpost( 'items' );

		return is_array( $items ) ? $items : array();
	}

	public function register_submenu_page_under_users() {
		add_submenu_page(
			'users.php',
			__( 'Pending Activations', 'gravityformsuserregistration' ),
			__( 'Pending Activations', 'gravityformsuserregistration' ),
			GFCommon::current_user_can_which( array( 'promote_users', 'gravityforms_user_registration', 'gform_full_access' ) ),
			'gf-pending-activations',
			array( $this, 'pending_activations_page' )
		);
	}

	/**
	 * Maybe handle the form submission and display admin messages.
	 *
	 * @since 4.6.4
	 */
	public function maybe_handle_submission() {
		if ( rgpost( 'is_submit' ) ) {
			self::handle_submission();
			GFCommon::display_admin_message();
		}
	}

	/**
	 * Display the Pending Activations page (found under the Users Admin Menu).
	 *
	 * @since Unknown
	 */
	public function pending_activations_page() {
		$this->maybe_handle_submission();
		self::get_page_content();
	}

	public function entry_pending_activation_meta_box( $form, $entry ) {

		if ( ! $this->is_entry_pending_activation( $entry ) ) {
			return;
		}

		?>

		<div class="postbox" id="gf_user_registration_pending_activation">

			<h3 class="hndle" style="cursor:default;">
				<span><?php _e( 'User Registration', 'gravityforms' ); ?></span>
			</h3>

			<div class="inside">
				<div>
					<?php $this->add_pending_activation_meta_box( array( 'entry' => $entry ) ) ?>
				</div>
			</div>
		</div>



		<?php
	}

	public function is_entry_pending_activation( $entry ) {
		global $wpdb;
		return self::get_pending_activations( $entry['form_id'], array( 'lead_id' => $entry['id'], 'get_total' => true ) ) > 0;
	}

	/**
	 * Include the activate user button in the sidebar of the entry detail page.
	 *
	 * @param array $meta_boxes The properties for the meta boxes.
	 * @param array $entry The entry currently being viewed/edited.
	 *
	 * @return array
	 */
	public function register_meta_box( $meta_boxes, $entry ) {
		if ( $this->is_entry_pending_activation( $entry ) ) {
			$meta_boxes['gf_user_pending_activation'] = array(
				'title'    => esc_html__( 'User Registration', 'gravityformsuserregistration' ),
				'callback' => array( $this, 'add_pending_activation_meta_box' ),
				'context'  => 'side',
			);
		}

		return $meta_boxes;
	}

	/**
	 * The callback used to echo the content to the gf_user_registration meta box.
	 *
	 * @param array $args An array containing the form and entry objects.
	 */
	public function add_pending_activation_meta_box( $args ) {
		require_once( gf_user_registration()->get_base_path() . '/includes/signups.php' );

		$entry_id       = rgar( $args['entry'], 'id' );
		$activation_key = GFUserSignups::get_lead_activation_key( $entry_id );

		?>

		<div id="gf_user_pending_activation">
			<a onclick="activateUser( '<?php echo $activation_key; ?>' );" class="button" id="gf_user_pending_activate_link" style="vertical-align:middle;">
				<?php esc_html_e( 'Activate User', 'gravityformsuserregistration' ); ?>
			</a>
			<?php gform_tooltip( sprintf( '<h6>%s</h6> %s', esc_html__( 'Pending Activation', 'gravityformsuserregistration' ), esc_html__( 'This entry created a user who is pending activation. Click the "Activate User" button to activate the user.', 'gravityformsuserregistration' ) ) ); ?>
		</div>

		<script type="text/javascript">

			function activateUser(activationKey) {

				if (!confirm(<?php echo json_encode( esc_html__( 'Are you sure you want to activate this user?', 'gravityformsuserregistration' ) ); ?>)) {
					return;
				}

				var spinner = new ajaxSpinner('#gf_user_pending_activate_link', 'margin-left:10px');

				jQuery.post(ajaxurl, {
					key:     activationKey,
					action: 'gf_user_activate',
					nonce:  '<?php echo wp_create_nonce( 'gf_user_activate' ); ?>'
				}, function (response) {

					// if there is an error message, alert it
					if ( ! response.success ) {

						alert( response.data.message );
						spinner.destroy();

					} else {

						jQuery('#gf_user_pending_activation').html('<div class="updated" style="margin:-12px;"><p><?php esc_html_e( 'User Activated Successfully!', 'gravityformsuserregistration' ); ?></p></div>');
						setTimeout('jQuery( "#gf_user_registration_pending_activation" ).slideUp();', 5000);
						spinner.destroy();

					}

				});

			}

			function ajaxSpinner(elem, style) {

				this.elem = elem;
				this.image = '<img src="<?php echo GFCommon::get_base_url(); ?>/images/spinner.gif" style="' + style + '" />';

				this.init = function () {
					this.spinner = jQuery(this.image);
					jQuery(this.elem).after(this.spinner);
					return this;
				}

				this.destroy = function () {
					jQuery(this.spinner).remove();
				}

				return this.init();
			}

		</script>

		<?php
	}

	/**
	 * Returns the entry table name for the current version of Gravity Forms.
	 *
	 * @since 3.8.3
	 *
	 * @return string
	 */
	public static function get_entry_table_name() {
		return version_compare( self::get_gravityforms_db_version(), '2.3-dev-1', '<' ) ? GFFormsModel::get_lead_table_name() : GFFormsModel::get_entry_table_name();
	}

	/**
	 * Returns the entry meta table name for current version of Gravity Forms.
	 *
	 * @since 3.8.3
	 *
	 * @return string
	 */
	public static function get_entry_meta_table_name() {
		return version_compare( self::get_gravityforms_db_version(), '2.3-dev-1', '<' ) ? GFFormsModel::get_lead_meta_table_name() : GFFormsModel::get_entry_meta_table_name();
	}

	/**
	 * Returns the database version for the current version of Gravity Forms.
	 *
	 * @since 3.8.3
	 *
	 * @return string
	 */
	public static function get_gravityforms_db_version() {
		return gf_user_registration()->get_gravityforms_db_version();
	}
}

/**
 * Returns an instance of the GF_Pending_Activations class
 *
 * @see    GF_Pending_Activations::get_instance()
 * @return GF_Pending_Activations
 */
function gf_pending_activations() {
	return GF_Pending_Activations::get_instance();
}
