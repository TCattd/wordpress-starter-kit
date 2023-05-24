<?php
/**
 * Object responsible for organizing and constructing the form settings page.
 */

namespace Gravity_Forms\Gravity_Forms_Google_Analytics\Settings;

defined( 'ABSPATH' ) || die();

use Gravity_Forms\Gravity_Forms_Google_Analytics\GF_Google_Analytics;
use Gravity_Forms\Gravity_Forms\Settings\Settings;
use GFCommon;
use GFAddOn;
use GFAPI;
use GFFormsModel;

class Form_Settings {

	/**
	 * Add-on instance.
	 *
	 * @var GF_Google_Analytics
	 */
	private $addon;
	/**
	 * Defines the capability needed to access the Add-On form settings page.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $_capabilities_form_settings The capability needed to access the Add-On form settings page.
	 */
	protected $_capabilities_form_settings = 'gravityforms_googleanalytics';

	/**
	 * Plugin_Settings constructor.
	 *
	 * @since 1.0
	 *
	 * @param GF_Google_Analytics $addon GF_Google_Analytics instance.
	 */
	public function __construct( $addon ) {
		$this->addon = $addon;
	}

	/**
	 * Get tab attributes so correct tab appears as selected.
	 *
	 * @since 1.0
	 *
	 * @return array Array of tab attributes.
	 */
	public function get_tab_attributes() {
		$active_attrs   = 'aria-selected=true class=active';
		$inactive_attrs = 'aria-selected=false';

		$tab_attributes = array();

		if ( rgget( 'settingstype' ) == 'form' ) {
			$tab_attributes['current_tab']     = 'form_settings';
			$tab_attributes['feed_link_attrs'] = $inactive_attrs;
			$tab_attributes['form_link_attrs'] = $active_attrs;
		} else {
			$tab_attributes['current_tab']     = 'feed';
			$tab_attributes['feed_link_attrs'] = $active_attrs;
			$tab_attributes['form_link_attrs'] = $inactive_attrs;
		}

		return $tab_attributes;
	}

	/**
	 * Display the form settings page.
	 *
	 * This form settings page has tabs for the feed settings and the form settings.
	 *
	 * @since 1.0
	 *
	 * @param array $form the current form.
	 */
	public function form_settings_page( $form ) {

		// Set up the data we need to display the tabs.
		$tab_attributes = $this->get_tab_attributes();

		$feed_settings_params = http_build_query( array_merge( $_GET, array( 'settingstype' => 'feed' ) ) );
		$form_settings_params = http_build_query( array_merge( $_GET, array( 'settingstype' => 'form' ) ) );

		// Display the navigation tabs.
		echo '<nav class="gform-settings-tabs__navigation" role="tablist" style="margin-bottom:.875rem">
			<a role="tab" href="' . admin_url( 'admin.php?' . esc_html( $feed_settings_params ) ) . '" ' . esc_attr( $tab_attributes['feed_link_attrs'] ) . '>' . esc_html__( 'Feed Settings', 'gravityformsgoogleanalytics' ) . '</a>
			<a role="tab" href="' . admin_url( 'admin.php?' . esc_html( $form_settings_params ) ) . '" ' . esc_attr( $tab_attributes['form_link_attrs'] ) . '>' . esc_html__( 'Form Settings', 'gravityformsgoogleanalytics' ) . '</a>
		</nav>';

		// Display the tab contents.
		if ( 'form_settings' == $tab_attributes['current_tab'] ) {

			if ( ! $this->addon->initialize_api() ) {
				printf( '<div>%s</div>', $this->addon->configure_addon_message() );
				return;
			}
			// Get fields.
			$sections = array_values( $this->addon->form_settings_fields( $form ) );
			$sections = $this->addon->prepare_settings_sections( $sections, 'form_settings' );
			$renderer = new Settings(
				array(
					'capability'     => $this->_capabilities_form_settings,
					'fields'         => $sections,
					'initial_values' => GF_Google_Analytics::get_instance()->get_form_settings( $form ),
					'save_callback'  => function( $values ) use ( $form ) {
						$this->save_form_settings( $form, $values );
					},
					'after_fields'   => function() use ( $form ) {

						printf(
							'<script type="text/javascript">var form = %s;</script>',
							wp_json_encode( $form )
						);
					},
				)
			);
			$renderer->render();
		} else {
			if ( $this->addon->is_detail_page() ) {
				// Feed edit page.
				$feed_id = $this->addon->get_current_feed_id();
				$this->addon->feed_edit_page( $form, $feed_id );
			} else {
				// Feed list UI.
				$this->addon->feed_list_page( $form );
			}
		}

	}

	/***
	 * Saves form settings to form object.
	 *
	 * @since 1.0
	 *
	 * @param array $form the current form.
	 * @param array $settings the settings to save.
	 *
	 * @return true|false True on success or false on error
	 */
	public function save_form_settings( $form, $settings ) {
		$form[ $this->addon->get_slug() ] = $settings;
		$result                           = GFFormsModel::update_form_meta( $form['id'], $form );

		return ! ( false === $result );
	}

	/**
	 * Configures the settings which should be rendered on the feed edit page.
	 *
	 * @since  1.0.0
	 *
	 * @return array
	 */
	public function get_feed_settings_fields() {
		$form_id = rgget( 'id' );
		$form    = GFAPI::get_form( $form_id );

		return array(
			array(
				'title'  => esc_html__( 'Feed Name', 'gravityformsgoogleanalytics' ),
				'fields' => array(
					array(
						'label'    => esc_html__( 'Feed Name', 'gravityformsgoogleanalytics' ),
						'type'     => 'text',
						'name'     => 'feedName',
						'class'    => 'medium',
						'required' => true,
						'tooltip'  => '<strong>' . esc_html__( 'Feed Name', 'gravityformsgoogleanalytics' ) . '</strong>' . esc_html__( 'Enter a feed name to uniquely identify this feed.', 'gravityformsgoogleanalytics' ),
					),
				),
			),
			array(
				'title'  => esc_html__( 'Feed Goal', 'gravityformsgoogleanalytics' ),
				'fields' => $this->get_goal_select_fields( $form, 'feed' ),
			),
			array(
				'title'  => __( 'Advanced', 'gravityformsgoogleanalytics' ),
				'fields' => array(
					array(
						'label'         => esc_html__( 'Submission Event Value', 'gravityformsgoogleanalytics' ),
						'type'          => 'text',
						'name'          => 'gaeventvalue',
						'class'         => 'medium merge-tag-support mt-position-right',
						'tooltip'       => sprintf( '<strong>%s</strong>%s', esc_html__( 'Submission Event Value', 'gravityformsgoogleanalytics' ), __( 'Enter your Google Analytics event value for form submission. Default is 0. Total fields may be used here, but Google Analytics expects integers, so the value used will be rounded (e.g., 29.50 will be rounded to 30).', 'gravityformsgoogleanalytics' ) ),
						'default_value' => 0,
					),
				),
			),
			array(
				'title'  => __( 'Conditional Logic Settings', 'gravityformsgoogleanalytics' ),
				'fields' => array(
					array(
						'name'    => 'conditionalLogic',
						'label'   => esc_html__( 'Conditional Logic', 'gravityformsgoogleanalytics' ),
						'type'    => 'feed_condition',
						'tooltip' => '<strong>' . __( 'Conditional Logic', 'gravityformsgoogleanalytics' ) . '</strong>' . esc_html__( 'When conditions are enabled, conversions will only be sent when the conditions are met.', 'gravityformsgoogleanalytics' ),
					),
				),
			),
		);
	}

	/**
	 * Check if the selected goal exists, and if not, clear the relevant settings.
	 *
	 * @since 1.0
	 *
	 * @param string $type Whether we're checking feed or pagination fields.
	 *
	 * @return bool whether or not the goal exists.
	 */
	private function maybe_delete_previous_goal( $type ) {
		$goals = $this->addon->get_goals();

		// If we're doing manual config, no need to maybe erase prior goal.
		if ( $this->addon->manual_configuration() ) {
			return;
		}
		if ( $type === 'feed' ) {
			$active_feed = $this->addon->get_current_feed();
			if ( ! isset( $active_feed['meta'] ) ) {
				return false;
			}
			if ( $goals ) {
				foreach ( $goals as $goal ) {
					if ( rgar( $goal, 'name' ) === rgar( $active_feed['meta'], 'gaeventgoal' ) ) {
						return true;
					}
				}
			}
			if ( $active_feed['id'] ) {
				$this->addon->update_feed_meta( $active_feed['id'], $active_feed['meta'] );
			}
			return false;
		}

		$current_settings = $this->addon->get_form_settings( $this->addon->get_current_form() );
		if ( $current_settings && rgar( $current_settings, 'pagination_gaeventgoal' ) ) {
			$selected_goal = rgar( $current_settings, 'pagination_gaeventgoal' );
			if ( $goals ) {
				foreach ( $goals as $goal ) {
					if ( rgar( $goal, 'name' ) === $selected_goal ) {
						return true;
					}
				}
			}
			$current_settings = array();
			$this->addon->save_form_settings( $this->addon->get_current_form(), $current_settings );
			return false;
		}
	}

	public function manual_goal_config_fields( $form, $type ) {
		if ( $type !== 'pagination' && $type !== 'feed' ) {
			return;
		}

		$this->maybe_delete_previous_goal( $type );

		$prefix = ( $type === 'pagination' ) ? 'pagination_' : '';
		return array(
			array(
				'type'    => 'select_goal',
				'name'    => 'select_goal',
				'label'   => __( 'Goal', 'gravityformsgoogleanalytics' ),
				'tooltip' => sprintf( '<strong>%s</strong>%s', esc_html__( 'Goal Selection', 'gravityformsgoogleanalytics' ), esc_html__( 'Select an existing Google Analytics goal or create a new one.', 'gravityformsgoogleanalytics' ) ),
			),
			array(
				'type'          => 'hidden',
				'name'          => $prefix . 'gaeventgoalid',
				'default_value' => 0,
			),
			array(
				'type'          => 'hidden',
				'name'          => $prefix . 'gaeventgoal',
				'default_value' => __( 'Submission:', 'gravityformsgoogleanalytics' ) . ' ' . $form['title'],
			),
			array(
				'type'          => 'hidden',
				'name'          => $prefix . 'gaeventcategory',
				'default_value' => '',
			),
			array(
				'type'          => 'hidden',
				'name'          => $prefix . 'gaeventaction',
				'default_value' => '',
			),
			array(
				'type'          => 'hidden',
				'name'          => $prefix . 'gaeventlabel',
				'default_value' => '',
			),
			array(
				'type'    => 'event_category',
				'name'    => 'feed_event_category',
				'label'   => __( 'Event Category', 'gravityformsgoogleanalytics' ),
				'tooltip' => sprintf( '<strong>%s</strong>%s', esc_html__( 'Event Category', 'gravityformsgoogleanalytics' ), esc_html__( 'This will be the event category used for Google Analytics.', 'gravityformsgoogleanalytics' ) ),
			),
			array(
				'type'    => 'event_action',
				'name'    => 'feed_event_action',
				'label'   => __( 'Event Action', 'gravityformsgoogleanalytics' ),
				'tooltip' => sprintf( '<strong>%s</strong>%s', esc_html__( 'Event Action', 'gravityformsgoogleanalytics' ), esc_html__( 'This will be the event action used for Google Analytics.', 'gravityformsgoogleanalytics' ) ),
			),
			array(
				'type'    => 'event_label',
				'name'    => 'feed_event_label',
				'label'   => __( 'Event Label', 'gravityformsgoogleanalytics' ),
				'tooltip' => sprintf( '<strong>%s</strong>%s', esc_html__( 'Event Label', 'gravityformsgoogleanalytics' ), esc_html__( 'This will be the event label used for Google Analytics.', 'gravityformsgoogleanalytics' ) ),
			),
		);
	}

	/**
	 * Get the fields that are used to select a goal.
	 *
	 * @since 1.0
	 *
	 * @param array  $form The current form object.
	 * @param string $type Whether we're rendering feed or pagination fields.
	 *
	 * @return array Array of fields.
	 */
	public function get_goal_select_fields( $form, $type ) {
		if ( $type !== 'pagination' && $type !== 'feed' ) {
			return;
		}

		$this->maybe_delete_previous_goal( $type );

		$prefix = ( $type === 'pagination' ) ? 'pagination_' : '';
		return array(
			array(
				'type'    => 'select_goal',
				'name'    => 'select_goal',
				'label'   => __( 'Goal', 'gravityformsgoogleanalytics' ),
				'tooltip' => sprintf( '<strong>%s</strong>%s', esc_html__( 'Goal Selection', 'gravityformsgoogleanalytics' ), esc_html__( 'Select an existing Google Analytics goal or create a new one.', 'gravityformsgoogleanalytics' ) ),
			),
			array(
				'type'          => 'hidden',
				'name'          => $prefix . 'gaeventgoalid',
				'default_value' => 0,
			),
			array(
				'type'          => 'hidden',
				'name'          => $prefix . 'gaeventgoal',
				'default_value' => __( 'Submission:', 'gravityformsgoogleanalytics' ) . ' ' . $form['title'],
			),
			array(
				'type'          => 'hidden',
				'name'          => $prefix . 'gaeventcategory',
				'default_value' => '',
			),
			array(
				'type'          => 'hidden',
				'name'          => $prefix . 'gaeventaction',
				'default_value' => '',
			),
			array(
				'type'          => 'hidden',
				'name'          => $prefix . 'gaeventlabel',
				'default_value' => '',
			),
			array(
				'type'    => 'event_category',
				'name'    => 'feed_event_category',
				'label'   => __( 'Event Category', 'gravityformsgoogleanalytics' ),
				'tooltip' => sprintf( '<strong>%s</strong>%s', esc_html__( 'Event Category', 'gravityformsgoogleanalytics' ), esc_html__( 'This will be the event category used for Google Analytics.', 'gravityformsgoogleanalytics' ) ),
			),
			array(
				'type'    => 'event_action',
				'name'    => 'feed_event_action',
				'label'   => __( 'Event Action', 'gravityformsgoogleanalytics' ),
				'tooltip' => sprintf( '<strong>%s</strong>%s', esc_html__( 'Event Action', 'gravityformsgoogleanalytics' ), esc_html__( 'This will be the event action used for Google Analytics.', 'gravityformsgoogleanalytics' ) ),
			),
			array(
				'type'    => 'event_label',
				'name'    => 'feed_event_label',
				'label'   => __( 'Event Label', 'gravityformsgoogleanalytics' ),
				'tooltip' => sprintf( '<strong>%s</strong>%s', esc_html__( 'Event Label', 'gravityformsgoogleanalytics' ), esc_html__( 'This will be the event label used for Google Analytics.', 'gravityformsgoogleanalytics' ) ),
			),
		);
	}

	/**
	 * Configures the columns for the feed page.
	 *
	 * @since  1.0.0
	 *
	 * @return array
	 */
	public function feed_list_columns() {
		return array(
			'feedName'        => esc_html__( 'Name', 'gravityformsgoogleanalytics' ),
			'gaeventcategory' => esc_html__( 'Category', 'gravityformsgoogleanalytics' ),
			'gaeventaction'   => esc_html__( 'Action', 'gravityformsgoogleanalytics' ),
			'gaeventlabel'    => esc_html__( 'Label', 'gravityformsgoogleanalytics' ),
			'gaeventvalue'    => esc_html__( 'Value', 'gravityformsgoogleanalytics' ),
		);
	}

	/**
	 * Add pagination form settings to Gravity Forms.
	 *
	 * @since 1.0.0
	 *
	 * @param array $form     The form.
	 *
	 * @return array Updated form settings
	 */
	public function pagination_form_settings( $form ) {
		if ( isset( $form['pagination'] ) ) {
			return array(
				array(
					'title'  => esc_html__( 'Form Settings', 'gravityformsgoogleanalytics' ),
					'fields' => array(
						array(
							'name'    => 'google_analytics_pagination',
							'label'   => esc_html__( 'Pagination Tracking', 'gravityformsgoogleanalytics' ),
							'type'    => 'checkbox',
							'choices' => array(
								array(
									'label' => esc_html__( 'Enable pagination tracking', 'gravityformsgoogleanalytics' ),
									'name'  => 'google_analytics_pagination',
								),
							),
						),
						array(
							'name'          => 'pagination_value',
							'label'         => esc_html__( 'Pagination Event Value', 'gravityformsgoogleanalytics' ),
							'type'          => 'text',
							'input_type'    => 'number',
							'default_value' => 0,
							'tooltip'       => sprintf( '<strong>%s</strong>%s', esc_html__( 'Pagination Event Value', 'gravityformsgoogleanalytics' ), __( 'Enter your Google Analytics event value for pagination events. This value will be sent whenever the user moves between pages. Default is 0. Google Analytics expects integers, so the value used will be rounded (e.g., 29.50 will be rounded to 30).', 'gravityformsgoogleanalytics' ) ),
						),
					),
				),
				array(
					'title'      => 'Select Goal',
					'fields'     => $this->get_goal_select_fields( $form, 'pagination' ),
					'id'         => 'select_goal_group',
					'dependency' => array(
						'live'   => true,
						'fields' => array(
							array(
								'field'  => 'google_analytics_pagination',
								'values' => array( '1' ),
							),
						),
					),
				),
			);
		} else {
			return array(
				array(
					'title'  => esc_html__( 'Form Settings', 'gravityformsgoogleanalytics' ),
					'fields' => array(
						array(
							'label' => esc_html__( 'Pagination Tracking', 'gravityformsgoogleanalytics' ),
							'type'  => 'html',
							'html'  => '<p>' . esc_html__( 'Add a Page field to your form to begin tracking pagination events.', 'gravityformsgoogleanalytics' ) . '</p>',
						),
					),
				),
			);
		}
	}

}
