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
            'wppb-block-login',
            add_query_arg( [ 'action' => 'wppb-block-login.js', ], admin_url( 'admin-ajax.php' ) ),
            [ 'wp-blocks', 'wp-element', 'wp-editor' ],
            microtime(),
            true
        );
        register_block_type(
            'wppb/login',
            [
                'api_version'                 => 2,
                'title'                       => __( 'Login' , 'profile-builder' ),
                'description'                 => __( 'Displays the Front End Login form.' , 'profile-builder' ),
                'category'                    => 'wppb-block',
                'keywords'                    => [
                    __( 'Login' , 'profile-builder' ),
                    __( 'Form' , 'profile-builder' ),
                    __( 'Form' , 'profile-builder' ),
                ],
                'example'                     => [
                    'attributes'              => [
                        'register_url'        => '',
                        'lostpassword_url'    => '',
                        'auth_field'          => false,
                        'redirect_url'        => '',
                        'logout_redirect_url' => '',
                        'is_preview'          => true,
                        'is_editor'           => true,
                    ],
                ],
                'editor_script'               => 'wppb-block-login',
                'attributes'                  => [
                    'register_url'            => [
                        'type'                => 'string',
                        'default'             => '',
                    ],
                    'lostpassword_url'        => [
                        'type'                => 'string',
                        'default'             => '',
                    ],
                    'auth_field'              => [
                        'type'                => 'boolean',
                        'default'             => false,
                    ],
                    'redirect_url'            => [
                        'type'                => 'string',
                        'default'             => '',
                    ],
                    'logout_redirect_url'     => [
                        'type'                => 'string',
                        'default'             => '',
                    ],
                    'is_preview'              => [
                        'type'                => 'boolean',
                        'default'             => false,
                    ],
                    'is_editor'               => [
                        'type'                => 'boolean',
                        'default'             => false,
                    ],
                ],

                'render_callback' => function( $attributes, $content ) {
                    ob_start();
                    do_action( 'wppb/login/render_callback', $attributes, $content );
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
    'wppb/login/render_callback',
    function( $attributes, $content ) {
        if ( $attributes['is_preview'] ) {
            echo '
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 230 130"
                    style="width: "100%";"
                >
                    <title>Login Block Preview</title>
                    <rect
                        width="36.328499"
                        height="10.030812"
                        x="35.712097"
                        y="82.629547"
                        rx="1.9783683"
                        id="rect4"
                        style="fill:#a0a5aa;stroke-width:1.22376513" />
                    <rect
                        width="32.748241"
                        height="15"
                        x="28.485535"
                        y="27.950914"
                        rx="2.6073439"
                        id="rect6"
                        style="fill:#a0a5aa;stroke-width:0.72212797" />
                    <rect
                        width="6.177"
                        height="6.177"
                        x="28.503256"
                        y="84.55645"
                        rx="3"
                        id="rect38"
                        style="fill:#a0a5aa" />
                    <rect
                        width="49.609749"
                        height="10.030812"
                        x="28.485535"
                        y="53.187717"
                        rx="2.7016351"
                        id="rect4-3"
                        style="fill:#a0a5aa;stroke-width:1.43007195" />
                    <rect
                        width="47.265999"
                        height="10.030812"
                        x="28.485535"
                        y="67.738503"
                        rx="2.5739999"
                        id="rect4-6"
                        style="fill:#a0a5aa;stroke-width:1.39588225" />
                    <rect
                        width="28.646679"
                        height="15"
                        x="28.485535"
                        y="95.820312"
                        rx="2.2807865"
                        id="rect6-7"
                        style="fill:#a0a5aa;stroke-width:0.67539418" />
                    <rect
                        width="120.60584"
                        height="10.030812"
                        x="81.445129"
                        y="53.187717"
                        rx="6.5679221"
                        id="rect4-3-5"
                        style="fill:#a0a5aa;stroke-width:2.22976208" />
                    <rect
                        width="120.60584"
                        height="10.030812"
                        x="81.445129"
                        y="67.738503"
                        rx="6.5679221"
                        id="rect4-3-5-5"
                        style="fill:#a0a5aa;stroke-width:2.22976208" />
                </svg>';
        } else {
            $atts = [
                'redirect_url' => $attributes['redirect_url'] !== '' ? ' redirect_url="' . esc_url( $attributes['redirect_url'] ) . '"' : '',
                'logout_redirect_url' => $attributes['logout_redirect_url'] !== '' ? ' logout_redirect_url="' . esc_url( $attributes['logout_redirect_url'] ) . '"' : '',
                'register_url' => $attributes['register_url'] !== '' ? ' register_url="' . esc_url( $attributes['register_url'] ) . '"' : '',
                'lostpassword_url' => $attributes['lostpassword_url'] !== '' ? ' lostpassword_url="' . esc_url( $attributes['lostpassword_url'] ) . '"' : '',
                'show_2fa_field' => $attributes['auth_field'] ? ' show_2fa_field="yes"' : '',
                'block' => $attributes['is_editor'] ? ' block="true"' : '',
            ];
            echo '<div class="wppb-block-container">' . do_shortcode( '[wppb-login' . $atts['redirect_url'] . $atts['logout_redirect_url'] . $atts['register_url'] . $atts['lostpassword_url'] . $atts['show_2fa_field'] . $atts['block'] . ' ]' ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
    },
    10,
    2
);

/**
 * Register: JavaScript.
 */
add_action(
    'wp_ajax_wppb-block-login.js',
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
            var InspectorControls = wp.editor.InspectorControls;

            blocks.registerBlockType( 'wppb/login', {
                icon:
                    el('svg', {},
                        el( 'path',
                            {
                                d: "m 6.0706026,9.5351799 c -0.049,-0.058 -0.116,-0.093 -0.1778,-0.1361 0,0 -0.2921,-0.215 -0.2921,-0.215 -0.271,-0.209 -0.5276,-0.4398005 -0.7609,-0.6903005 -0.6574,-0.7057 -1.1049,-1.5984 -1.2531,-2.5527 -0.033,-0.2141 -0.056,-0.4312 -0.056,-0.6477 0,0 -0.013,-0.1905 -0.013,-0.1905 0,0 0.012,-0.127 0.012,-0.127 0,0 0,-0.2032 0,-0.2032 0,0 0.024,-0.2667 0.024,-0.2667 0.052,-0.524 0.1749,-1.0425 0.3921,-1.524 0.202,-0.448 0.4804,-0.897 0.8163,-1.2573 0.4289,-0.4601 0.8673,-0.82600002 1.4227,-1.12640002 0.4417,-0.2388 0.9181,-0.4034 1.4097,-0.5017 0.182,-0.036 0.5303,-0.086 0.7112,-0.086 0,0 0.5588,0 0.5588,0 0,0 0.127,0.012 0.127,0.012 0.4205,0.031 0.8586,0.1211 1.2573004,0.2594 0.228,0.079 0.4449,0.1769 0.6604,0.2846 0.428,0.214 0.881,0.52860002 1.2192,0.86680002 0,0 0.2264,0.2286 0.2264,0.2286 0.314,0.3471 0.586,0.7353 0.7926,1.1557 0.2328,0.4733 0.395,0.9891 0.4703,1.5113 0,0 0.048,0.4445 0.048,0.4445 0,0 0,0.1524 0,0.1524 0,0 0.012,0.127 0.012,0.127 0,0 -0.012,0.1651 -0.012,0.1651 0,0 0,0.1524 0,0.1524 0,0.1838 -0.055,0.501 -0.092,0.6858 -0.1916,0.9577 -0.6557,1.7812 -1.3078,2.5019 -0.2216,0.245 -0.489,0.4864005 -0.7471,0.6928005 0,0 -0.3683,0.2851 -0.3683,0.2851 0,0 0.7112,0.294 0.7112,0.294 0.4778,0.2051001 0.9765,0.4238001 1.397,0.7332001 0,0 0.2159,0.1606 0.2159,0.1606 0.114,0.07 0.3334,0.1273 0.3424,0.2854 0.01,0.1322 -0.083,0.2249 -0.1668,0.3175 -0.1531,0.1696 -0.2979,0.3206 -0.5439,0.2665 -0.056,-0.012 -0.089,-0.028 -0.1397,-0.054 0,0 -0.3683,-0.238 -0.3683,-0.238 0,0 -0.3302,-0.2031 -0.3302,-0.2031 -0.3462,-0.2023 -0.8018,-0.4102 -1.1811,-0.5419 -2.0699004,-0.7183001 -4.4065004,-0.4751 -6.2992004,0.6233 -1.1403,0.6618 -2.1261,1.6345 -2.7887,2.776 -0.3052,0.5258 -0.5014,0.9871 -0.6911,1.5621 -0.1602,0.4851 -0.243,0.969 -0.31009996,1.4732 -0.029,0.2171 -0.047,0.4918 0.14499996,0.6428 0.1112,0.088 0.2464,0.098 0.381,0.1077 0,0 0.3175,0.012 0.3175,0.012 0,0 0.2159,-0.013 0.2159,-0.013 0,0 5.4737,0 5.4737,0 0,0 2.4892004,0 2.4892004,0 0,0 0.1905,-0.013 0.1905,-0.013 0,0 0.9652,0 0.9652,0 0,0 0.1905,-0.013 0.1905,-0.013 0,0 0.5842,0 0.5842,0 0,0 0.1905,-0.013 0.1905,-0.013 0.1812,-2e-4 0.3537,8e-4 0.5334,0.031 0.4647,0.078 0.8372,0.3135 1.2065,0.5931 0.096,0.073 0.3826,0.2872 0.4445,0.3666 0,0 -10.9728004,0 -10.9728004,0 0,0 -1.9558,0 -1.9558,0 0,0 -0.2794,-0.024 -0.2794,-0.024 -0.38079996,-0.045 -0.75009996,-0.1458 -0.95949996,-0.4972 -0.1656,-0.2781 -0.149,-0.6159 -0.1454,-0.9271 0,0 0.06,-0.508 0.06,-0.508 0.056,-0.3828 0.1484,-0.821 0.2521,-1.1938 0.3774,-1.3579 0.94869996,-2.4793 1.89979996,-3.5306 0,0 0.1497,-0.1524 0.1497,-0.1524 0,0 0.091,-0.1004 0.091,-0.1004 0,0 0.1005,-0.091 0.1005,-0.091 0,0 0.1524,-0.1497 0.1524,-0.1497 0.44,-0.3982 0.8806,-0.7242 1.3843,-1.0362 0.4188,-0.2596 0.8591,-0.4819001 1.3081,-0.6843001 0,0 0.4191,-0.1765 0.4191,-0.1765 0.074,-0.03 0.1646,-0.072 0.2413,-0.086 z m 2.2987,-8.57130052 c 0,0 -0.2667,0.028 -0.2667,0.028 -0.2065,0.026 -0.4484,0.073 -0.6477,0.13260002 -0.9851,0.2936 -1.823,0.9193 -2.3675,1.7942 0,0 -0.1938,0.3429 -0.1938,0.3429 -0.2397,0.5043 -0.3866,1.0288 -0.411,1.5875 0,0 -0.012,0.1651 -0.012,0.1651 0,0 0,0.1143 0,0.1143 0,0 0.012,0.1397 0.012,0.1397 0.073,1.676 1.2024,3.165 2.7945,3.6957005 0.3376,0.1125 0.6741,0.18 1.0287,0.2044 0,0 0.127,0.012 0.127,0.012 0,0 0.2921,0 0.2921,0 0,0 0.1524,-0.012 0.1524,-0.012 0.5446,-0.037 1.0731,-0.179 1.5621004,-0.4235005 0.4517,-0.2258 0.8945,-0.5572 1.2283,-0.9366 0.3685,-0.4186 0.6334,-0.8589 0.8203,-1.3843 0.1129,-0.3175 0.2242,-0.8327 0.2247,-1.1684 0,0 0,-0.3937 0,-0.3937 0,-0.2412 -0.074,-0.6158 -0.1378,-0.8509 -0.2305,-0.8513 -0.7243,-1.5963 -1.4116,-2.1488 -0.3837,-0.3084 -0.8162,-0.5405 -1.2827004,-0.696 -0.3178,-0.1059 -0.6444,-0.16860002 -0.9779,-0.19160002 0,0 -0.1524,-0.01 -0.1524,-0.01 0,0 -0.2921,0 -0.2921,0 0,0 -0.089,0 -0.089,0 z M 19.964403,14.22148 c 0,0 -1.3462,-1.3462 -1.3462,-1.3462 0,0 -0.8255,-0.8255 -0.8255,-0.8255 0,0 -0.6477,-0.6477 -0.6477,-0.6477 -0.059,-0.059 -0.2252,-0.2057 -0.2427,-0.2794 -0.019,-0.082 0.052,-0.1392 0.103,-0.1905 0,0 0.381,-0.381 0.381,-0.381 0.051,-0.051 0.1097,-0.1189 0.1905,-0.09 0.06,0.022 0.2361,0.212 0.2921,0.268 0,0 0.635,0.635 0.635,0.635 0,0 2.2733,2.2733 2.2733,2.2733 0,0 0.7112,0.7112 0.7112,0.7112 0.063,0.063 0.2316,0.2042 0.2333,0.2921 0,0.088 -0.1709,0.2298 -0.2333,0.2921 0,0 -0.2921,0.2907 -0.2921,0.2907 0,0 -0.1397,0.1036 -0.1397,0.1036 0,0 -0.2921,0.2915 -0.2921,0.2915 0,0 -2.2606,2.2606 -2.2606,2.2606 0,0 -0.635,0.635 -0.635,0.635 -0.057,0.057 -0.2304,0.2454 -0.2921,0.2681 -0.077,0.028 -0.1388,-0.029 -0.1905,-0.078 0,0 -0.1662,-0.1773 -0.1662,-0.1773 0,0 -0.2148,-0.2159 -0.2148,-0.2159 -0.051,-0.051 -0.1251,-0.1091 -0.1036,-0.1905 0.019,-0.074 0.1719,-0.208 0.2306,-0.2667 0,0 0.5842,-0.5842 0.5842,-0.5842 0,0 0.7874,-0.7874 0.7874,-0.7874 0,0 1.2954,-1.2954 1.2954,-1.2954 0,0 -5.5118,0 -5.5118,0 0,0 -1.2827,0 -1.2827,0 0,0 -0.254,0 -0.254,0 -0.045,-6e-4 -0.089,0 -0.1194,-0.041 -0.024,-0.031 -0.02,-0.074 -0.02,-0.1114 0,0 0,-0.6731 0,-0.6731 9e-4,-0.1227 0.037,-0.1394 0.1524,-0.1397 0,0 7.2009,0 7.2009,0 z"
                            }
                        )
                    ),
                attributes: {
                    register_url : {
                        type: 'string',
                        default: ''
                    },
                    lostpassword_url : {
                        type: 'string',
                        default: '',
                    },
                    auth_field : {
                        type: 'boolean',
                        default: false,
                    },
                    redirect_url : {
                        type: 'string',
                        default: '',
                    },
                    logout_redirect_url : {
                        type: 'string',
                        default: '',
                    },
                    is_preview : {
                        type: 'boolean',
                        default: false,
                    },
                    is_editor : {
                        type: 'boolean',
                        default: true,
                    },
                },

                edit: function ( props ) {
                    return [
                        el(
                            'div',
                            Object.assign( blockEditor.useBlockProps(), { key: 'wppb/login/render' } ),
                            el( serverSideRender,
                                {
                                    block: 'wppb/login',
                                    attributes: props.attributes,
                                }
                            )
                        ),
                        el( InspectorControls, { key: 'wppb/login/inspector' },
                            [
                                el( PanelBody,
                                    {
                                        title: __( 'Form Settings' , 'profile-builder' ),
                                        key: 'wppb/login/inspector/form-settings'
                                    },
                                    [
                                        el( SelectControl,
                                            {
                                                label: __( 'Registration Page' , 'profile-builder' ),
                                                key: 'wppb/register/inspector/form_settings/register_url',
                                                help: __( 'Add a link to a Registration Page' , 'profile-builder' ),
                                                value: props.attributes.register_url,
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
                                                onChange: ( value ) => { props.setAttributes( { register_url: value } ); }
                                            }
                                        ),
                                        el( SelectControl,
                                            {
                                                label: __( 'Recover Password Page' , 'profile-builder' ),
                                                key: 'wppb/register/inspector/form_settings/lostpassword_url',
                                                help: __( 'Add a link to a Recover Password Page' , 'profile-builder' ),
                                                value: props.attributes.lostpassword_url,
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
                                                onChange: ( value ) => { props.setAttributes( { lostpassword_url: value } ); }
                                            }
                                        ),
        <?php
        if ( wppb_is_2fa_active() ) {
        ?>
                                        el( ToggleControl,
                                            {
                                                label: __( 'Show Authenticator Code Field' , 'profile-builder' ),
                                                key: 'wppb/login/inspector/form-settings/auth_field',
                                                checked: props.attributes.auth_field,
                                                onChange: ( value ) => { props.setAttributes( { auth_field: !props.attributes.auth_field } ); }
                                            }
                                        )
        <?php
        }
        ?>
                                    ]
                                ),
                                el( PanelBody,
                                    {
                                        title: __( 'Redirects' , 'profile-builder' ),
                                        key: 'wppb/login/inspector/redirects'
                                    },
                                    [
                                        el( SelectControl,
                                            {
                                                label: __( 'After Login' , 'profile-builder' ),
                                                key: 'wppb/login/inspector/form-settings/redirect_url',
                                                help: __( 'Select a page for an After Login Redirect' , 'profile-builder' ),
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
                                        ),
                                        el( SelectControl,
                                            {
                                                label: __( 'After Logout' , 'profile-builder' ),
                                                key: 'wppb/login/inspector/form-settings/logout_redirect_url',
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
                                        )
                                    ]
                                )
                            ]
                        ),
                        el( blockEditor.InspectorAdvancedControls, { key: 'wppb/login/inspector_advanced' },
                            [
                                el( TextControl,
                                    {
                                        label: __( 'Registration URL' , 'profile-builder' ),
                                        key: 'wppb/login/inspector_advanced/form-settings/register_url',
                                        help: __( 'Manually type in a Registration Page URL' , 'profile-builder' ),
                                        value: props.attributes.register_url,
                                        onChange: ( value ) => { props.setAttributes( { register_url: value } ); }
                                    },
                                ),
                                el( TextControl,
                                    {
                                        label: __( 'Recover Password URL' , 'profile-builder' ),
                                        key: 'wppb/login/inspector_advanced/form-settings/lostpassword_url',
                                        help: __( 'Manually type in a Recover Password Page URL' , 'profile-builder' ),
                                        value: props.attributes.lostpassword_url,
                                        onChange: ( value ) => { props.setAttributes( { lostpassword_url: value } ); }
                                    }
                                ),
                                el( TextControl,
                                    {
                                        label: __( 'After Login' , 'profile-builder' ),
                                        key: 'wppb/login/inspector_advanced/form-settings/redirect_url',
                                        help: __( 'Manually type in an After Login Redirect URL' , 'profile-builder' ),
                                        value: props.attributes.redirect_url,
                                        onChange: ( value ) => { props.setAttributes( { redirect_url: value } ); }
                                    }
                                ),
                                el( TextControl,
                                    {
                                        label: __( 'After Logout' , 'profile-builder' ),
                                        key: 'wppb/login/inspector_advanced/form-settings/logout_redirect_url',
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
