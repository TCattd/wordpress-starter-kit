<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

use Elementor\Controls_Manager;

class WPPB_Elementor {
    private static $_instance = null;
    public $locations = array(
        array(
            'element' => 'common',
            'action'  => '_section_style',
        ),
        array(
            'element' => 'section',
            'action'  => 'section_advanced',
        ),
        array(
            'element' => 'container',
            'action'  => 'section_layout',
        )
    );
    public $section_name = 'wppb_section_visibility_settings';

	/**
	 * Register plugin action hooks and filters
	 */
	public function __construct() {
        // Register new section to display restriction controls
        $this->register_sections();

        // Setup controls
        $this->register_controls();

        // Filter widget content
		add_filter( 'elementor/widget/render_content', array( $this, 'widget_render' ), 10, 2 );

		// Filter sections display & add custom messages
		add_action( 'elementor/frontend/section/should_render', array( $this, 'section_render' ), 10, 2 );
		add_action( 'elementor/frontend/section/after_render', array( $this, 'section_custom_messages' ), 10, 2 );

        // Filter container display & add custom messages
        add_action( 'elementor/frontend/container/should_render', array( $this, 'section_render' ), 10, 2 );
        add_action( 'elementor/frontend/container/after_render', array( $this, 'section_custom_messages' ), 10, 2 );

        // Filter Elementor `the_content` hook
        add_action( 'elementor/frontend/the_content', array( $this, 'filter_elementor_templates' ), 20 );
	}

    /**
     *
     * Ensures only one instance of the class is loaded or can be loaded.
     *
     * @return WPPB_Elementor An instance of the class.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) )
            self::$_instance = new self();

        return self::$_instance;
    }

    private function register_sections() {
        foreach( $this->locations as $where ) {
            add_action( 'elementor/element/'.$where['element'].'/'.$where['action'].'/after_section_end', array( $this, 'add_section' ), 10, 2 );
        }
    }

    // Register controls to sections and widgets
    private function register_controls() {
        foreach( $this->locations as $where )
            add_action('elementor/element/'.$where['element'].'/'.$this->section_name.'/before_section_end', array( $this, 'add_controls' ), 10, 2 );
    }

    public function add_section( $element, $args ) {
        $exists = \Elementor\Plugin::instance()->controls_manager->get_control_from_stack( $element->get_unique_name(), $this->section_name );

        if( !is_wp_error( $exists ) )
            return false;

        $element->start_controls_section(
            $this->section_name, array(
                'tab'   => Controls_Manager::TAB_ADVANCED,
                'label' => __( 'Profile Builder Content Restriction', 'profile-builder' )
            )
        );

        $element->end_controls_section();
    }

    // Define controls
	public function add_controls( $element, $args ) {
		$element_type = $element->get_type();

		$element->add_control(
			'wppb_restriction_loggedin_users', array(
				'label'       => __( 'Restrict to logged in users', 'profile-builder' ),
				'type'        => Controls_Manager::SWITCHER,
				'description' => __( 'Allow only logged in users to see this content.', 'profile-builder' ),
                'default'   => '',
                'condition'   => array(
                    'wppb_restriction_loggedout_users!' => 'yes'
                )
			)
		);

        $element->add_control(
            'wppb_restriction_loggedout_users', array(
                'label'       => __( 'Restrict to logged out users', 'profile-builder' ),
                'type'        => Controls_Manager::SWITCHER,
                'description' => __( 'Allow only logged out users to see this content.', 'profile-builder' ),
                'default'   => '',
                'condition'   => array(
                    'wppb_restriction_loggedin_users!' => 'yes'
                )
            )
        );

		$element->add_control(
			'wppb_restriction_user_roles_heading', array(
				'label'     => __( 'Restrict by User Roles', 'profile-builder' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
                'condition'   => array(
                    'wppb_restriction_loggedout_users!' => 'yes'
                )
			)
		);

		$element->add_control(
            'wppb_restriction_user_roles', array(
                'type'        => Controls_Manager::SELECT2,
                'options'     => wp_roles()->get_names(),
                'multiple'    => 'true',
				'label_block' => 'true',
				'description' => __( 'Allow users which have the specified role to see this content.', 'profile-builder' ),
                'condition'   => array(
                    'wppb_restriction_loggedout_users!' => 'yes'
                )
            )
        );

		$element->add_control(
			'wppb_restriction_custom_messages_heading', array(
				'label'     => __( 'Restriction Messages', 'profile-builder' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$element->add_control(
			'wppb_restriction_default_messages', array(
				'label'       => __( 'Enable Restriction Messages', 'profile-builder' ),
				'type'        => Controls_Manager::SWITCHER,
				'description' => __( 'Replace hidden content with the default messages from PB -> Settings -> Content Restriction, a custom message or an Elementor Template.', 'profile-builder' ),
			)
		);

		$element->add_control(
			'wppb_restriction_custom_messages', array(
				'label'       => __( 'Enable Custom Messages', 'profile-builder' ),
				'type'        => Controls_Manager::SWITCHER,
				'description' => __( 'Add a custom message or template.', 'profile-builder' ),
				'condition'   => array(
					'wppb_restriction_default_messages' => 'yes'
				)
			)
		);

		$element->add_control(
			'wppb_restriction_custom_messages_type', array(
				'label'   => __( 'Content type', 'profile-builder' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => array(
					'text' => array(
						'title' => __( 'Text', 'profile-builder' ),
						'icon'  => 'fa fa-align-left',
					),
					'template' => array(
						'title' => __( 'Template', 'profile-builder' ),
						'icon'  => 'fa fa-th-large',
					)
				),
				'default'   => 'text',
				'condition' => array(
					'wppb_restriction_default_messages' => 'yes',
					'wppb_restriction_custom_messages'  => 'yes'
				),
			)
		);

		//DCE_HELPER::get_all_template()
		$element->add_control(
			'wppb_restriction_fallback_template', array(
				'type'        => Controls_Manager::SELECT2,
				'options'     => $this->get_elementor_templates(),
				'label'       => __( 'Select Template', 'profile-builder' ),
				'default'     => '',
				'label_block' => 'true',
				'condition'   => array(
					'wppb_restriction_default_messages'     => 'yes',
					'wppb_restriction_custom_messages'      => 'yes',
					'wppb_restriction_custom_messages_type' => 'template'
				),
			)
		);

		$element->add_control(
			'wppb_restriction_fallback_text', array(
				'type'        => Controls_Manager::WYSIWYG,
				'default'     => '',
				'condition'   => array(
					'wppb_restriction_default_messages'     => 'yes',
					'wppb_restriction_custom_messages'      => 'yes',
					'wppb_restriction_custom_messages_type' => 'text'
				),
			)
		);

	}

    // Verifies if element is hidden
	public function is_hidden( $element ) {
		$settings = $element->get_settings();

		if( is_user_logged_in() && $settings['wppb_restriction_loggedout_users'] === 'yes' ) {
            return true;
        }

		if( !empty( $settings['wppb_restriction_user_roles'] ) && is_user_logged_in() ) {

            $user_data = get_userdata( get_current_user_id() );

            foreach( $settings['wppb_restriction_user_roles'] as $restriction_role ) {
                foreach( $user_data->roles as $user_role ) {
                    if( $user_role == $restriction_role ) {
                        return false;
                    }
                }
            }

            return true;
		} else if ( !is_user_logged_in() && (
					( $settings['wppb_restriction_loggedin_users'] == 'yes' ) || ( !empty( $settings['wppb_restriction_user_roles'] ) )
				) ) {

			return true;
		}

		return false;
	}

	// Retrieves custom element message or the default message from PB settings
	private function get_custom_message( $element ) {
		$settings = $element->get_settings();

		if( $settings['wppb_restriction_default_messages'] != 'yes' )
			return false;

		if( $settings['wppb_restriction_custom_messages'] == 'yes' ) {

			if( $settings['wppb_restriction_custom_messages_type'] == 'text' )
				return $settings['wppb_restriction_fallback_text'];
			elseif( $settings['wppb_restriction_custom_messages_type'] == 'template' ) {
				return $this->render_template( $settings['wppb_restriction_fallback_template'] );
			}
		} else {
			if( is_user_logged_in() )
				return wppb_content_restriction_process_content_message( 'logged_in', get_current_user_id() );
			else
				return wppb_content_restriction_process_content_message( 'logged_out', get_current_user_id() );
		}
	}

	// Widget display & custom messages
	public function widget_render( $content, $widget ) {
		if( $this->is_hidden( $widget ) ) {

			if( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				$widget->add_render_attribute( '_wrapper', 'class', 'wppb-visibility-hidden' );

				return $content;
			}

			if( $message = $this->get_custom_message( $widget ) ) {
				return $message;
			}

			return '<style>' . $widget->get_unique_selector() . '{display:none !important}</style>';
		}

		return $content;
	}

	// Section display
	public function section_render( $should_render, $element ) {
		if( $this->is_hidden( $element ) === true )
			return false;

		return $should_render;
	}

	// Section custom messages
	public function section_custom_messages( $element ) {
		if( $this->is_hidden( $element ) && ( $message = $this->get_custom_message( $element ) ) ) {

			$element->add_render_attribute(
				'_wrapper', 'class', array(
					'elementor-section',
				)
			);

			$element->before_render();
				echo $message;//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$element->after_render();
		}
	}

	// Render an Elementor template based on ID
	// Based on Elementor Pro template shortcode
	public function render_template( $id ) {
		return Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $id, true );
	}

	// Retrieve defined Elementor templates
	private function get_elementor_templates() {
		$templates = array();

		foreach( \Elementor\Plugin::instance()->templates_manager->get_source('local')->get_items() as $template ) {
			$templates[$template['template_id']] = $template['title'] . ' (' . $template['type'] . ')';
		}

		return $templates;
	}

    public function filter_elementor_templates( $content ) {
        $document = \Elementor\Plugin::$instance->documents->get_current();

        return wppb_content_restriction_filter_content( $content, $document->get_post() );
    }
}

// Instantiate Plugin Class
WPPB_Elementor::instance();
