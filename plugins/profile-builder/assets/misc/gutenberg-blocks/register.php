<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register: PHP.
 */
add_action(
    'init',
    function() {
        wp_register_script(
            'wppb-block-register',
            add_query_arg( [ 'action' => 'wppb-block-register.js', ], admin_url( 'admin-ajax.php' ) ),
            [ 'wp-blocks', 'wp-element', 'wp-editor' ],
            microtime(),
            true
        );
        register_block_type(
            'wppb/register',
            [
                'api_version'                      => 2,
                'title'                            => __( 'Register' , 'profile-builder' ),
                'description'                      => __( 'Displays the Front End Registration Form.' , 'profile-builder' ),
                'category'                         => 'wppb-block',
                'keywords'                         => [
                    __( 'Register' , 'profile-builder' ),
                    __( 'Form' , 'profile-builder' ),
                ],
                'example'                          => [
                    'attributes'                   => [
                        'form_name'                => '',
                        'role'                     => '',
                        'automatic_login'          => false,
                        'redirect_url'             => '',
                        'logout_redirect_url'      => '',
                        'is_preview'               => true,
                    ],
                ],
                'editor_script'                    => 'wppb-block-register',
                'attributes'                       => [
                    'form_name'                    => [
                        'type'                     => 'string',
                        'default'                  => '',
                    ],
                    'role'                         => [
                        'type'                     => 'string',
                        'default'                  => '',
                    ],
                    'automatic_login'              => [
                        'type'                     => 'boolean',
                        'default'                  => false,
                    ],
                    'redirect_url'                 => [
                        'type'                     => 'string',
                        'default'                  => '',
                    ],
                    'logout_redirect_url'          => [
                        'type'                     => 'string',
                        'default'                  => '',
                    ],
                    'is_preview'                   => [
                        'type'                     => 'boolean',
                        'default'                  => false,
                    ],
                ],

                'render_callback' => function( $attributes, $content ) {
                    ob_start();
                    do_action( 'wppb/register/render_callback', $attributes, $content );
                    return ob_get_clean();
                },
            ]
        );
    }
);

/**
 * Render: PHP.
 *
 * @param array  $attributes Optional. Block attributes. Default empty array.
 * @param string $content    Optional. Block content. Default empty string.
 */
add_action(
    'wppb/register/render_callback',
    function( $attributes, $content ) {
        if ( $attributes['is_preview'] ) {
            echo '
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 230 430"
                    style="width: "100%";"
                >
                    <title>Register Block Preview</title>
                    <rect
                        width="44.373241"
                        height="15"
                        x="27.955204"
                        y="27.950914"
                        rx="3.5329013"
                        id="rect6"
                        style="fill:#a0a5aa;stroke-width:0.84058326" />
                    <rect
                        width="6.177"
                        height="6.177"
                        x="27.955204"
                        y="388.05646"
                        rx="3"
                        id="rect38"
                        style="fill:#a0a5aa" />
                    <rect
                        width="35.717747"
                        height="15"
                        x="27.955204"
                        y="406.9473"
                        rx="2.8437696"
                        id="rect6-7"
                        style="fill:#a0a5aa;stroke-width:0.75415772" />
                    <rect
                        width="121.18815"
                        height="9.191123"
                        x="80.097206"
                        y="101.45288"
                        rx="6.5996327"
                        id="rect4-3-5"
                        style="fill:#a0a5aa;stroke-width:2.13954115" />
                    <rect
                        width="169.35583"
                        height="4.6558123"
                        x="27.955204"
                        y="56.672096"
                        rx="9.2227373"
                        id="rect4-3-5-9"
                        style="fill:#a0a5aa;stroke-width:1.80013108" />
                    <rect
                        width="22.35239"
                        height="11.730559"
                        x="27.955204"
                        y="79.031174"
                        rx="1.2172608"
                        id="rect4-3-1"
                        style="fill:#a0a5aa;stroke-width:1.03807247" />
                    <rect
                        width="58.980843"
                        height="2.9058123"
                        x="80.097206"
                        y="112.6096"
                        rx="3.2119634"
                        id="rect4-3-5-2"
                        style="fill:#a0a5aa;stroke-width:0.83925813" />
                    <rect
                        width="121.18815"
                        height="9.191123"
                        x="80.097206"
                        y="121.21694"
                        rx="6.5996327"
                        id="rect4-3-5-7"
                        style="fill:#a0a5aa;stroke-width:2.13954115" />
                    <rect
                        width="121.18815"
                        height="9.191123"
                        x="80.097206"
                        y="134.21693"
                        rx="6.5996327"
                        id="rect4-3-5-0"
                        style="fill:#a0a5aa;stroke-width:2.13954115" />
                    <rect
                        width="121.18815"
                        height="9.191123"
                        x="80.097206"
                        y="147.15443"
                        rx="6.5996327"
                        id="rect4-3-5-93"
                        style="fill:#a0a5aa;stroke-width:2.13954115" />
                    <rect
                        width="121.18815"
                        height="9.191123"
                        x="80.097206"
                        y="202.06604"
                        rx="6.5996327"
                        id="rect4-3-5-6"
                        style="fill:#a0a5aa;stroke-width:2.13954115" />
                    <rect
                        width="121.18815"
                        height="9.191123"
                        x="80.097206"
                        y="215.05089"
                        rx="6.5996327"
                        id="rect4-3-5-06"
                        style="fill:#a0a5aa;stroke-width:2.13954115" />
                    <rect
                        width="121.18815"
                        height="47.81683"
                        x="80.097206"
                        y="266.19736"
                        rx="6.5996327"
                        id="rect4-3-5-26"
                        style="fill:#a0a5aa;stroke-width:4.88007784" />
                    <rect
                        width="121.18815"
                        height="9.191123"
                        x="80.097206"
                        y="331.47766"
                        rx="6.5996327"
                        id="rect4-3-5-1"
                        style="fill:#a0a5aa;stroke-width:2.13954115" />
                    <rect
                        width="121.18815"
                        height="9.191123"
                        x="80.097206"
                        y="351.15445"
                        rx="6.5996327"
                        id="rect4-3-5-8"
                        style="fill:#a0a5aa;stroke-width:2.13954115" />
                    <rect
                        width="116.34488"
                        height="2.9058123"
                        x="80.097206"
                        y="316.20871"
                        rx="6.3358793"
                        id="rect4-3-5-2-7"
                        style="fill:#a0a5aa;stroke-width:1.17872834" />
                    <rect
                        width="52.793659"
                        height="2.9058123"
                        x="80.097206"
                        y="322.88547"
                        rx="2.8750234"
                        id="rect4-3-5-2-9"
                        style="fill:#a0a5aa;stroke-width:0.7940191" />
                    <rect
                        width="39.270241"
                        height="2.9058123"
                        x="80.097206"
                        y="342.66321"
                        rx="2.1385686"
                        id="rect4-3-5-2-2"
                        style="fill:#a0a5aa;stroke-width:0.68481278" />
                    <rect
                        width="51.114281"
                        height="2.9058123"
                        x="80.097206"
                        y="362.35513"
                        rx="2.7835681"
                        id="rect4-3-5-2-0"
                        style="fill:#a0a5aa;stroke-width:0.78128809" />
                    <rect
                        width="28.901199"
                        height="9.191123"
                        x="27.955204"
                        y="101.45288"
                        rx="1.573894"
                        id="rect4-3-5-5"
                        style="fill:#a0a5aa;stroke-width:1.04483688" />
                    <rect
                        width="27.651199"
                        height="9.191123"
                        x="27.955204"
                        y="121.21694"
                        rx="1.5058219"
                        id="rect4-3-5-5-9"
                        style="fill:#a0a5aa;stroke-width:1.02199209" />
                    <rect
                        width="26.151199"
                        height="9.191123"
                        x="27.955204"
                        y="134.21693"
                        rx="1.4241353"
                        id="rect4-3-5-5-2"
                        style="fill:#a0a5aa;stroke-width:0.99388552" />
                    <rect
                        width="29.151199"
                        height="9.191123"
                        x="27.955204"
                        y="147.15443"
                        rx="1.5875086"
                        id="rect4-3-5-5-28"
                        style="fill:#a0a5aa;stroke-width:1.04934609" />
                    <rect
                        width="19.901199"
                        height="9.191123"
                        x="27.955204"
                        y="202.06604"
                        rx="1.0837744"
                        id="rect4-3-5-5-97"
                        style="fill:#a0a5aa;stroke-width:0.8670221" />
                    <rect
                        width="20.026199"
                        height="9.191123"
                        x="27.955204"
                        y="215.05089"
                        rx="1.0905817"
                        id="rect4-3-5-5-3"
                        style="fill:#a0a5aa;stroke-width:0.86974072" />
                    <rect
                        width="42.526199"
                        height="9.191123"
                        x="27.955204"
                        y="266.19736"
                        rx="2.3158808"
                        id="rect4-3-5-5-6"
                        style="fill:#a0a5aa;stroke-width:1.26741493" />
                    <rect
                        width="27.526199"
                        height="9.191123"
                        x="27.955204"
                        y="331.47766"
                        rx="1.4990147"
                        id="rect4-3-5-5-1"
                        style="fill:#a0a5aa;stroke-width:1.01967943" />
                    <rect
                        width="45.901199"
                        height="9.191123"
                        x="27.955204"
                        y="351.15445"
                        rx="2.4996758"
                        id="rect4-3-5-5-29"
                        style="fill:#a0a5aa;stroke-width:1.31674767" />
                    <rect
                        width="37.85239"
                        height="11.730559"
                        x="27.955204"
                        y="180.14156"
                        rx="2.0613558"
                        id="rect4-3-1-31"
                        style="fill:#a0a5aa;stroke-width:1.3508662" />
                    <rect
                        width="46.10239"
                        height="11.730559"
                        x="27.955204"
                        y="244.31151"
                        rx="2.5106323"
                        id="rect4-3-1-9"
                        style="fill:#a0a5aa;stroke-width:1.49082756" />
                    <rect
                        width="79.401199"
                        height="9.191123"
                        x="35.0494"
                        y="386.54941"
                        rx="4.3240104"
                        id="rect4-3-5-5-29-4"
                        style="fill:#a0a5aa;stroke-width:1.73182523" />
                </svg>';
        } else {
            $form_name = '';
            if ( array_key_exists( 'form_name', $attributes ) ) {
                $form_name = $attributes['form_name'];
            }
            if ( !$form_name || $form_name === '' ) {
                $atts = [
                    'role' => $attributes['role'] !== '' ? ' role="' . esc_attr( $attributes['role'] ) . '"' : '',
                    'form_name' => '',
                    'redirect_url' => $attributes['redirect_url'] !== '' ? ' redirect_url="' . esc_url( $attributes['redirect_url'] ) . '"' : '',
                    'logout_redirect_url' => $attributes['logout_redirect_url'] !== '' ? ' logout_redirect_url="' . esc_url( $attributes['logout_redirect_url'] ) . '"' : '',
                    'automatic_login' => $attributes['automatic_login'] ? ' automatic_login="yes"' : '',
                ];
            } else {
                $atts = [
                    'role' => '',
                    'form_name' => ' form_name="' . $form_name . '"',
                    'redirect_url' => '',
                    'logout_redirect_url' => $attributes['logout_redirect_url'] !== '' ? ' logout_redirect_url="' . esc_url( $attributes['logout_redirect_url'] ) . '"' : '',
                    'automatic_login' => '',
                ];
            }
            echo '<div class="wppb-block-container">' . do_shortcode( '[wppb-register' . $atts['role'] . $atts['form_name'] . $atts['redirect_url'] . $atts['logout_redirect_url'] . $atts['automatic_login'] . ' ]' ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
    },
    10,
    2
);

/**
 * Register: JavaScript.
 */
add_action(
    'wp_ajax_wppb-block-register.js',
    function() {
        header( 'Content-Type: text/javascript' );

        $args = array(
            'post_type'         => 'page',
            'posts_per_page'    => -1
        );

        if( function_exists( 'wc_get_page_id' ) )
            $args['exclude'] = wc_get_page_id( 'shop' );

        $all_pages = get_posts( $args );
        ?>
        ( function ( blocks, i18n, element, serverSideRender, blockEditor, components ) {
            var { __ } = i18n;
            var el = element.createElement;
            var PanelBody = components.PanelBody;
            var SelectControl = components.SelectControl;
            var ToggleControl = components.ToggleControl;
            var TextControl = components.TextControl;
            var Button = components.Button;
            var Text = components.__experimentalText;
            var InspectorControls = wp.editor.InspectorControls;

            blocks.registerBlockType( 'wppb/register', {
                icon:
                    el('svg', {},
                        el( 'path',
                            {
                                d: "m 6.060947,9.5388969 c -0.049,-0.058 -0.1159,-0.093 -0.1778,-0.1361 0,0 -0.2921,-0.215 -0.2921,-0.215 -0.271,-0.2089 -0.5275,-0.4398 -0.7608,-0.6903 -0.6133,-0.6582 -1.0671,-1.5116 -1.2277,-2.4003 -0.043,-0.2401 -0.081,-0.5187 -0.082,-0.762 0,0 -0.013,-0.2794 -0.013,-0.2794 0,0 0.012,-0.127 0.012,-0.127 0,0 0,-0.1651 0,-0.1651 0,0 0.012,-0.127 0.012,-0.127 0,0 0.049,-0.4318 0.049,-0.4318 0.1148,-0.7792 0.4319,-1.5308 0.9068,-2.159 0.5129,-0.6785 1.2601,-1.27849998 2.043,-1.61639998 0.5894,-0.2544 1.2241,-0.4081 1.8669,-0.4156 0,0 0.1651,-0.0130000022 0.1651,-0.0130000022 0,0 0.127,0.0120000021 0.127,0.0120000021 0,0 0.1651,0 0.1651,0 0,0 0.127,0.012 0.127,0.012 0.3658,0.025 0.729,0.096 1.0795,0.2041 1.3113,0.4033 2.4651,1.33699998 3.0701,2.57829998 0.6277,1.2874 0.7121,2.7812 0.1735,4.1148 -0.2399,0.5938 -0.5806,1.1109 -1.0063,1.5875 -0.2369,0.2653 -0.499,0.5094 -0.7768,0.7316 -0.089,0.072 -0.2831,0.2336 -0.381,0.2717 0,0 0,0.025 0,0.025 0,0 0.7112,0.2940999 0.7112,0.2940999 0.6331,0.2717002 1.0023,0.4378002 1.5621,0.8586002 0,0 0.3173,0.185 0.3173,0.185 0.213,0.1813 -0.071,0.4368 -0.203,0.5673 -0.041,0.041 -0.066,0.065 -0.1143,0.097 -0.05,0.034 -0.093,0.051 -0.1524,0.062 -0.1604,0.029 -0.3036,-0.053 -0.4318,-0.138 0,0 -0.381,-0.2499 -0.381,-0.2499 -0.5649,-0.3397 -1.0933,-0.5842 -1.7272,-0.7693 -0.7370002,-0.2153 -1.5072,-0.3063 -2.2733,-0.2975 0,0 -0.127,0.012 -0.127,0.012 0,0 -0.127,0 -0.127,0 0,0 -0.127,0.012 -0.127,0.012 0,0 -0.7366,0.087 -0.7366,0.087 -0.907,0.1512 -1.7832,0.4743 -2.5781,0.9356 -1.6257,0.9436 -2.9202,2.547 -3.4635,4.3507 -0.1191,0.3957 -0.1818,0.6592 -0.2501,1.0668 0,0 -0.0710001,0.6223 -0.0710001,0.6223 0,0.1503 0.042,0.3048 0.16490005,0.4015 0.1042,0.082 0.2529,0.1012 0.381,0.1071 0,0 0.254,0.012 0.254,0.012 0,0 0.2159,-0.013 0.2159,-0.013 0,0 5.4737,0 5.4737,0 0,0 2.4891998,0 2.4891998,0 0,0 0.1905002,-0.013 0.1905002,-0.013 0,0 0.9906,0 0.9906,0 0,0 0.1905,-0.013 0.1905,-0.013 0,0 0.5969,0 0.5969,0 0,0 0.1905,-0.013 0.1905,-0.013 0.2018,-2e-4 0.3568,0 0.5588,0.031 0.4647,0.078 0.8372,0.3136 1.2065,0.5931 0.096,0.073 0.3827,0.2873 0.4445,0.3666 0,0 -10.9728,0 -10.9728,0 0,0 -1.9431,0 -1.9431,0 0,0 -0.1524,-0.012 -0.1524,-0.012 -0.38190005,-0.027 -0.82500005,-0.1105 -1.05950005,-0.4457 -0.206,-0.2942 -0.189,-0.6494 -0.1851,-0.9906 0,0 0.071,-0.5842 0.071,-0.5842 0,0 0.1974,-0.9525 0.1974,-0.9525 0.3729,-1.4916 1.08010005,-2.8205 2.15750005,-3.9243 0,0 0.1776,-0.1656 0.1776,-0.1656 0.4393,-0.4201 0.9205,-0.7823 1.4351,-1.105 0.437,-0.2741 0.9107,-0.5223002 1.3843,-0.7268002 0.1284,-0.055 0.5215,-0.2298999 0.635,-0.2504999 z m 2.2987,-8.57070002 c 0,0 -0.2667,0.027 -0.2667,0.027 -0.2706,0.036 -0.5407,0.092 -0.8001,0.17870002 -1.0421,0.3475 -1.9575,1.1324 -2.4285,2.1291 -0.2717,0.5754 -0.4109,1.2057 -0.4036,1.8415 0,0 0.012,0.1397 0.012,0.1397 0.053,1.2058 0.6753,2.3521 1.6388,3.0748 0.3587,0.269 0.7435,0.4708 1.1684,0.6124 0.3321,0.1108 0.6673,0.1762 1.016,0.2002 0,0 0.127,0.012 0.127,0.012 0,0 0.2794,0 0.2794,0 0,0 0.1524,-0.012 0.1524,-0.012 0.5485,-0.038 1.0816998,-0.1768 1.5748,-0.4234 0.4468,-0.2234 0.8984,-0.5618 1.2284,-0.9367 0.3764,-0.4277 0.6455,-0.8846 0.833,-1.4224 0.1068,-0.3064 0.2114,-0.8199 0.2119,-1.143 0,0 0,-0.3683 0,-0.3683 0,-0.2176 -0.062,-0.561 -0.1159,-0.7747 -0.223,-0.8925 -0.7155,-1.6606 -1.4335,-2.2377 -0.3838,-0.3085 -0.8161,-0.5404 -1.2827002,-0.696 -0.3125998,-0.1042 -0.6365998,-0.1686 -0.9651998,-0.19160002 0,0 -0.1524,-0.01 -0.1524,-0.01 0,0 -0.3937,0 -0.3937,0 z m 7.4295,13.09190012 c 0,0 0,-2.8194 0,-2.8194 0,0 0,-0.7112 0,-0.7112 0,-0.1319 0.062,-0.1395 0.1778,-0.1397 0,0 0.635,0 0.635,0 0.123,0 0.1518,0.049 0.1524,0.1651 0,0 0,0.6858 0,0.6858 0,0 0,2.8194 0,2.8194 0,0 2.8194,0 2.8194,0 0,0 0.6985,0 0.6985,0 0.1339,0 0.1522,0.058 0.1524,0.1778 0,0 0,0.6477 0,0.6477 0,0.129 -0.05,0.1396 -0.1651,0.1397 0,0 -3.5052,0 -3.5052,0 0,0 0,2.8194 0,2.8194 0,0 0,0.7112 0,0.7112 0,0.129 -0.05,0.1396 -0.1651,0.1397 0,0 -0.6604,0 -0.6604,0 -0.1227,-8e-4 -0.1394,-0.037 -0.1397,-0.1524 0,0 0,-3.5179 0,-3.5179 0,0 -2.8194,0 -2.8194,0 0,0 -0.7112,0 -0.7112,0 -0.1299,-8e-4 -0.1395,-0.048 -0.1397,-0.1651 0,0 0,-0.6604 0,-0.6604 0,-0.113 0.043,-0.1381 0.1524,-0.1397 0,0 0.6985,0 0.6985,0 0,0 2.8194,0 2.8194,0 z"
                            }
                        )
                    ),
                attributes: {
                    form_name : {
                        type: 'string',
                        default: ''
                    },
                    role : {
                        type: 'string',
                        default: ''
                    },
                    automatic_login : {
                        type: 'boolean',
                        default: false
                    },
                    redirect_url : {
                        type: 'string',
                        default: ''
                    },
                    logout_redirect_url : {
                        type: 'string',
                        default: ''
                    },
                    is_preview : {
                        type: 'boolean',
                        default: false,
                    },
                },

                edit: function ( props ) {
                    return [
                        el(
                            'div',
                            Object.assign( blockEditor.useBlockProps(), { key: 'wppb/register/render' } ),
                            el( serverSideRender,
                                {
                                    block: 'wppb/register',
                                    attributes: props.attributes,
                                }
                            )
                        ),
                        el( InspectorControls, { key: 'wppb/register/inspector' },
                            [
                                el( PanelBody,
                                    {
                                        title: __( 'Form Settings' , 'profile-builder' ),
                                        key: 'wppb/register/inspector/form_settings',
                                    },
                                    [
                                        el( SelectControl,
                                            {
                                                label: __( 'Form' , 'profile-builder' ),
                                                key: 'wppb/register/inspector/form_settings/form_name',
                                                help: __( 'Select the desired Registration form' , 'profile-builder' ),
                                                value: props.attributes.form_name,
                                                options: [
                                                    {
                                                        label: __( 'Default' , 'profile-builder' ),
                                                        value: ''
                                                    },
        <?php
        $wppb_module_settings = get_option( 'wppb_module_settings', 'not_found' );

        if ( !( ( $wppb_module_settings !== 'not_found' && (
                    !isset( $wppb_module_settings['wppb_multipleRegistrationForms'] ) ||
                    $wppb_module_settings['wppb_multipleRegistrationForms'] !== 'show'
                ) ) ||
            $wppb_module_settings === 'not_found' ) ){
            $args = array(
                'post_type'      => 'wppb-rf-cpt',
                'posts_per_page' => -1
            );

            $the_query = new WP_Query( $args );

            if ( $the_query->have_posts() ) {
                foreach ( $the_query->posts as $post ) {
        ?>
                                                    {
                                                        label: '<?php echo esc_html( $post->post_title ); ?>',
                                                        value: '<?php echo esc_attr( Wordpress_Creation_Kit_PB::wck_generate_slug( $post->post_title ) ); ?>'
                                                    },
        <?php
                }
                wp_reset_postdata();
            }
        }
        ?>
                                                ],
                                                onChange: ( value ) => { props.setAttributes( { form_name: value } ); }
                                            }
                                        ),
                                        props.attributes.form_name == '' ?
                                            el( SelectControl,
                                                {
                                                    label: __( 'Assigned Role' , 'profile-builder' ),
                                                    key: 'wppb/register/inspector/form_settings/role',
                                                    help: __( 'Select a Role to be assigned to users that register via this form' , 'profile-builder' ),
                                                    value: props.attributes.role,
                                                    options: [
                                                        {
                                                            label: __( 'Default' , 'profile-builder' ),
                                                            value: '<?php echo esc_attr( get_option("default_role") ); ?>'
                                                        },
        <?php
        if (!function_exists('get_editable_roles')) {
            require_once ABSPATH . 'wp-admin/includes/user.php';
        }

        $user_roles = get_editable_roles();

        foreach ($user_roles as $key => $role) {
            $user_roles[$key] = $role['name'];
        ?>
                                                        {
                                                            label: '<?php echo esc_html( $role["name"] ); ?>',
                                                            value: '<?php echo esc_attr( $key ); ?>'
                                                        },
        <?php
        }
        ?>
                                                    ],
                                                    onChange: ( value ) => { props.setAttributes( { role: value } ); }
                                                }
                                            ) :
                                            '',
                                        props.attributes.form_name == '' ?
                                            el( ToggleControl,
                                                {
                                                    label: __( 'Automatic Login' , 'profile-builder' ),
                                                    key: 'wppb/register/inspector/form_settings/automatic_login',
                                                    help: __( 'Automatically log in users after they register' , 'profile-builder' ),
                                                    checked: props.attributes.automatic_login,
                                                    onChange: ( value ) => { props.setAttributes( { automatic_login: !props.attributes.automatic_login } ); }
                                                }
                                            ) :
                                            '',
                                        props.attributes.form_name != '' ?
                                            el( Text,
                                                {
                                                    key: 'wppb/register/inspector/form_settings/notice'
                                                },
                                                [
                                                    __( 'Edit the Settings for this form ' , 'profile-builder' ),
                                                    el( Button,
                                                        {
                                                            key: 'wppb/register/inspector/form_settings/notice_button',
                                                            href: '<?php echo esc_url( admin_url( 'edit.php?post_type=wppb-rf-cpt' ) ); ?>',
                                                            target: '_blank',
                                                            text: __( 'here' , 'profile-builder' ),
                                                            variant: 'link'
                                                        },
                                                    )
                                                ]
                                            ) :
                                            '',
                                    ]
                                ),
                                el( PanelBody,
                                    {
                                        title: __( 'Redirects' , 'profile-builder' ),
                                        key: 'wppb/register/inspector/redirects',
                                    },
                                    [
                                        props.attributes.form_name == '' ?
                                            el( SelectControl,
                                                {
                                                    label: __( 'After Registration' , 'profile-builder' ),
                                                    key: 'wppb/register/inspector/redirects/redirect_url',
                                                    help: __( 'Select a page for an After Registration Redirect' , 'profile-builder' ),
                                                    value: props.attributes.redirect_url,
                                                    options: [
                                                        {
                                                            label: __( '' , 'profile-builder' ),
                                                            value: ''
                                                        },
        <?php
        if( !empty( $all_pages ) ){
            foreach ( $all_pages as $page ){
                ?>
                                                        {
                                                            label: '<?php echo esc_html( $page->post_title ) ?>',
                                                            value: '<?php echo esc_url( get_page_link( $page->ID ) ) ?>'
                                                        },
                <?php
            }
        }
        ?>
                                                    ],
                                                    onChange: ( value ) => { props.setAttributes( { redirect_url: value } ); }
                                                }
                                            ) :
                                            '',
                                        el( SelectControl,
                                            {
                                                label: __( 'After Logout' , 'profile-builder' ),
                                                key: 'wppb/register/inspector/redirects/logout_redirect_url',
                                                help: __( 'Select a page for an After Logout Redirect' , 'profile-builder' ),
                                                value: props.attributes.logout_redirect_url,
                                                options: [
                                                    {
                                                        label: __( '' , 'profile-builder' ),
                                                        value: ''
                                                    },
        <?php
        if( !empty( $all_pages ) ){
            foreach ( $all_pages as $page ){
                ?>
                                                    {
                                                        label: '<?php echo esc_html( $page->post_title ) ?>',
                                                        value: '<?php echo esc_url( get_page_link( $page->ID ) ) ?>'
                                                    },
                <?php
            }
        }
        ?>
                                                ],
                                                onChange: ( value ) => { props.setAttributes( { logout_redirect_url: value } ); }
                                            }
                                        ),
                                    ]
                                )
                            ]
                        ),
                        el( blockEditor.InspectorAdvancedControls, { key: 'wppb/register/inspector_advanced' },
                            [
                                props.attributes.form_name == '' ?
                                    el( TextControl,
                                        {
                                            label: __( 'After Registration' , 'profile-builder' ),
                                            key: 'wppb/register/inspector_advanced/redirects/redirect_url',
                                            help: __( 'Manually type in an After Registration Redirect URL' , 'profile-builder' ),
                                            value: props.attributes.redirect_url,
                                            onChange: ( value ) => { props.setAttributes( { redirect_url: value } ); }
                                        }
                                    ) :
                                    '',
                                el( TextControl,
                                    {
                                        label: __( 'After Logout' , 'profile-builder' ),
                                        key: 'wppb/register/inspector_advanced/redirects/logout_redirect_url',
                                        help: __( 'Manually type in an After Logout Redirect URL' , 'profile-builder' ),
                                        value: props.attributes.logout_redirect_url,
                                        onChange: ( value ) => { props.setAttributes( { logout_redirect_url: value } ); }
                                    }
                                )
                            ]
                        )
                    ];
                }
            } );
        } )(
            window.wp.blocks,
            window.wp.i18n,
            window.wp.element,
            window.wp.serverSideRender,
            window.wp.blockEditor,
            window.wp.components
        );
        <?php
        exit;
    }
);
