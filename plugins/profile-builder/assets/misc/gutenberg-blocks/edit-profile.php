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
            'wppb-block-edit-profile',
            add_query_arg( [ 'action' => 'wppb-block-edit-profile.js', ], admin_url( 'admin-ajax.php' ) ),
            [ 'wp-blocks', 'wp-element', 'wp-editor' ],
            microtime(),
            true
        );
        register_block_type(
            'wppb/edit-profile',
            [
                'api_version'              => 2,
                'title'                    => __( 'Edit Profile' , 'profile-builder' ),
                'description'              => __( 'Displays the Front End Edit Profile Form.' , 'profile-builder' ),
                'category'                 => 'wppb-block',
                'keywords'                 => [
                    __( 'Edit' , 'profile-builder' ),
                    __( 'Profile' , 'profile-builder' ),
                    __( 'Edit Profile' , 'profile-builder' ),
                    __( 'Form' , 'profile-builder' ),
                ],
                'example'                  => [
                    'attributes'           => [
                        'form_name'        => '',
                        'redirect_url'     => '',
                        'is_preview'       => true,
                    ],
                ],
                'editor_script'            => 'wppb-block-edit-profile',
                'attributes'               => [
                    'form_name'            => [
                        'type'             => 'string',
                        'default'          => '',
                    ],
                    'redirect_url'         => [
                        'type'             => 'string',
                        'default'          => '',

                    ],
                    'is_preview'           => [
                        'type'             => 'boolean',
                        'default'          => false,
                    ],
                ],

                'render_callback' => function( $attributes, $content ) {
                    ob_start();
                    do_action( 'wppb/edit-profile/render_callback', $attributes, $content );
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
    'wppb/edit-profile/render_callback',
    function( $attributes, $content ) {
        if ( $attributes['is_preview'] ) {
            echo '
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 230 430"
                    style="width: "100%";"
                >
                    <title>Edit Profile Block Preview</title>
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
                    'form_name' => '',
                    'redirect_url' => $attributes['redirect_url'] !== '' ? ' redirect_url="' . esc_url( $attributes['redirect_url'] ) . '"' : '',
                ];
            } else {
                $atts = [
                    'form_name' => ' form_name="' . $form_name . '"',
                    'redirect_url' => '',
                ];
            }
            echo '<div class="wppb-block-container">' . do_shortcode( '[wppb-edit-profile' . $atts['form_name'] . $atts['redirect_url'] . ' ]' ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
    },
    10,
    2
);

/**
 * Register: JavaScript.
 */
add_action(
    'wp_ajax_wppb-block-edit-profile.js',
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
            var TextControl = components.TextControl;
            var Button = components.Button;
            var Text = components.__experimentalText;
            var InspectorControls = wp.editor.InspectorControls;

            blocks.registerBlockType( 'wppb/edit-profile', {
                icon:
                    el('svg', {},
                        el( 'path',
                            {
                                d: "m 6.0748388,9.5111184 c -0.099,-0.038 -0.3519,-0.2294 -0.4445,-0.3021 -0.2808,-0.2202 -0.5499,-0.4486 -0.7896,-0.7139 -0.3274,-0.3624 -0.6189,-0.7523 -0.8327,-1.1938 -0.2727,-0.563 -0.4316,-1.1673 -0.4744,-1.7907 0,0 -0.011,-0.1397 -0.011,-0.1397 0,0 0,-0.5715 0,-0.5715 0,0 0.011,-0.127 0.011,-0.127 0.041,-0.5995 0.1724,-1.1913 0.4242,-1.7399 0.5843,-1.2728 1.7588,-2.28950004 3.0949,-2.70480004 0.5504,-0.1709 1.0894,-0.2356 1.6637,-0.2289 0,0 0.1397,0.012 0.1397,0.012 1.1751002,0.051 2.3266002,0.5475 3.1877002,1.34560004 0,0 0.2027,0.1916 0.2027,0.1916 0.5567,0.5821 0.9719,1.2857 1.2034,2.0574 0.089,0.2952 0.1506,0.5954 0.1834,0.9017 0,0 0.023,0.2413 0.023,0.2413 0,0 0.013,0.2667 0.013,0.2667 0,0 -0.013,0.3683 -0.013,0.3683 0,0.2138 -0.075,0.6249 -0.1255,0.8382 -0.3294,1.3871 -1.2433,2.4909 -2.3891,3.302 0.6849,0.2905 1.494,0.5944996 2.0955,1.0250996 0,0 0.2413,0.177 0.2413,0.177 0.1102,0.065 0.3181,0.1248 0.3358,0.2711 0.014,0.1159 -0.085,0.2251 -0.1545,0.3048 -0.1628,0.1872 -0.3068,0.3483 -0.575,0.2835 -0.051,-0.012 -0.093,-0.034 -0.1397,-0.058 0,0 -0.5588,-0.3592 -0.5588,-0.3592 -0.3912,-0.2347 -0.8643,-0.4613 -1.2954,-0.611 -2.1439002,-0.7439996 -4.5605002,-0.4705 -6.4897002,0.7249 -1.6213,1.0045 -2.8241,2.5675 -3.3528,4.401501 0,0 -0.097,0.3683 -0.097,0.3683 -0.063,0.2544 -0.1152,0.5525 -0.1463,0.8128 0,0 -0.034,0.3683 -0.034,0.3683 0.01,0.1204 0.027,0.2455 0.10599999,0.3424 0.1026,0.1269 0.3208,0.1654 0.4759,0.1656 0,0 4.9911,0 4.9911,0 0,0 2.7686,0 2.7686,0 0,0 0.1905,-0.013 0.1905,-0.013 0,0 1.3208002,0 1.3208002,0 0,0 0.1905,-0.013 0.1905,-0.013 0,0 0.6477,0 0.6477,0 0,0 0.2159,-0.013 0.2159,-0.013 0.2658,-3e-4 0.552,-0.016 0.8128,0.031 0.4383,0.079 0.8052,0.3029 1.1557,0.5678 0,0 0.3175,0.2498 0.3175,0.2498 0.053,0.042 0.119,0.083 0.1524,0.1422 0,0 -10.9855002,0 -10.9855002,0 0,0 -1.8669,0 -1.8669,0 0,0 -0.1397,-0.012 -0.1397,-0.012 -0.40049999,-0.027 -0.86169999,-0.085 -1.12069999,-0.433 -0.2141002,-0.2879 -0.20410019896,-0.6506 -0.2001002,-0.9906 0,-0.1616 0.055,-0.4918 0.084,-0.6604 0.06,-0.3549 0.1319,-0.7072 0.2284,-1.0541 0.1803,-0.6484 0.4123,-1.286301 0.73319999,-1.879601 0.5931,-1.0966 1.3661,-1.9912 2.371,-2.732 0.4948,-0.3647 1.1427,-0.7539 1.7018,-1.0104996 0,0 0.635,-0.278 0.635,-0.278 0,0 0.3175,-0.1324 0.3175,-0.1324 z m 2.2225,-8.54580004 c 0,0 -0.2413,0.028 -0.2413,0.028 -0.3083,0.042 -0.6099,0.11870004 -0.9017,0.22570004 -0.9628,0.353 -1.7756,1.0837 -2.2446,1.9932 -0.2473,0.4795 -0.4289,1.1231 -0.4351,1.6637 0,0 -0.013,0.2032 -0.013,0.2032 0,0 0.013,0.2286 0.013,0.2286 0,0.1545 0.04,0.3921 0.071,0.5461 0.095,0.4748 0.2701,0.9329 0.5234,1.3462 0.3003,0.4903 0.678,0.8906 1.1454,1.2246 0.3045,0.2177 0.6732,0.4094 1.0287,0.528 0.3581,0.1195 0.6406,0.1781 1.016,0.2154 0,0 0.127,0 0.127,0 0,0 0.127,0.013 0.127,0.013 0,0 0.3429,-0.013 0.3429,-0.013 0.1312,0 0.3877,-0.042 0.5207,-0.068 0.3128,-0.062 0.6662002,-0.1686 0.9525002,-0.3094 0,0 0.4191,-0.2243 0.4191,-0.2243 0.8838,-0.5502 1.5388,-1.4014 1.8142,-2.4077 0.066,-0.2429 0.1412,-0.6399 0.1416,-0.889 0,0 0,-0.4191 0,-0.4191 0,0 -0.012,-0.127 -0.012,-0.127 -0.016,-0.2261 -0.056,-0.4407 -0.1107,-0.6604 -0.3201,-1.2801 -1.3029,-2.3887 -2.5447,-2.8441 -0.3424002,-0.1255 -0.8554002,-0.25420004 -1.2192002,-0.25340004 0,0 -0.4572,0 -0.4572,0 0,0 -0.063,0 -0.063,0 z M 21.124339,6.8514184 c 0.13,-0.023 0.169,0.047 0.254,0.1324 0,0 0.3937,0.3937 0.3937,0.3937 0,0 1.7653,1.7653 1.7653,1.7653 0,0 0.4826,0.4826 0.4826,0.4826 0.065,0.065 0.1575,0.1366 0.1176,0.2413 -0.02,0.053 -0.1364,0.1584996 -0.1811,0.2031996 0,0 -0.3175,0.317 -0.3175,0.317 0,0 -0.127,0.115 -0.127,0.115 0,0 -1.5113,1.5111 -1.5113,1.5111 0,0 -2.3241,2.3241 -2.3241,2.3241 0,0 -0.4313,0.431801 -0.4313,0.431801 0,0 -0.2926,0.3048 -0.2926,0.3048 -0.095,0.095 -0.2788,0.2909 -0.381,0.3564 -0.089,0.057 -0.1807,0.081 -0.2794,0.1135 0,0 -0.4191,0.1397 -0.4191,0.1397 0,0 -1.3462,0.453 -1.3462,0.453 0,0 -0.8382,0.2794 -0.8382,0.2794 -0.3029,0.101 -0.5243,0.1858 -0.8509,0.182 -0.1179,0 -0.2656,-0.06 -0.381,-0.094 -0.057,-0.017 -0.1162,-0.032 -0.1343,-0.097 -0.022,-0.078 0.03,-0.1904 0.053,-0.2665 0,0 0.2001,-0.6477 0.2001,-0.6477 0,0 0.6986,-2.260601 0.6986,-2.260601 0,0 0.1777,-0.5715 0.1777,-0.5715 0.019,-0.057 0.056,-0.1991 0.083,-0.2413 0.026,-0.041 0.1157,-0.1263 0.1545,-0.1651 0,0 0.3048,-0.3048 0.3048,-0.3048 0,0 0.9525,-0.9525 0.9525,-0.9525 0,0 2.5781,-2.5780996 2.5781,-2.5780996 0,0 1.1049,-1.1049 1.1049,-1.1049 0,0 0.3048,-0.3048 0.3048,-0.3048 0.058,-0.058 0.1125,-0.1273 0.1905,-0.1578 z m 1.6891,2.9518 c 0,0 -1.2192,-1.2192 -1.2192,-1.2192 0,0 -0.3048,-0.3048 -0.3048,-0.3048 -0.027,-0.026 -0.089,-0.099 -0.127,-0.099 -0.032,0 -0.068,0.04 -0.089,0.061 0,0 -0.1905,0.1905 -0.1905,0.1905 0,0 -0.7747,0.7747 -0.7747,0.7747 0,0 -2.1463,2.1462996 -2.1463,2.1462996 0,0 -0.6858,0.6858 -0.6858,0.6858 -0.086,0.086 -0.3089,0.298 -0.3683,0.381 0,0 1.2192,1.2192 1.2192,1.2192 0,0 0.3048,0.3048 0.3048,0.3048 0.026,0.027 0.089,0.099 0.127,0.099 0.032,0 0.068,-0.04 0.089,-0.061 0,0 0.1905,-0.1905 0.1905,-0.1905 0,0 0.7747,-0.7747 0.7747,-0.7747 0,0 2.1463,-2.1463 2.1463,-2.1463 0,0 0.6858,-0.6858 0.6858,-0.6858 0.086,-0.086 0.3088,-0.2978996 0.3683,-0.3809996 z m -6.4897,3.3908996 c -0.051,0.059 -0.061,0.1437 -0.085,0.2159 0,0 -0.1493,0.4826 -0.1493,0.4826 0,0 -0.334,1.079501 -0.334,1.079501 0,0 -0.1305,0.4318 -0.1305,0.4318 0,0 0.381,-0.1228 0.381,-0.1228 0,0 0.6858,-0.2286 0.6858,-0.2286 0,0 0.7112,-0.237 0.7112,-0.237 0,0 0.3937,-0.135501 0.3937,-0.135501 0,0 -0.4953,-0.508 -0.4953,-0.508 0,0 -0.9779,-0.9779 -0.9779,-0.9779 z"
                            }
                        )
                    ),
                attributes: {
                    form_name : {
                        type: 'string',
                        default: ''
                    },
                    redirect_url : {
                        type: 'string',
                        default: '',
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
                            Object.assign( blockEditor.useBlockProps(), { key: 'wppb/edit-profile/render' } ),
                            el( serverSideRender,
                                {
                                    block: 'wppb/edit-profile',
                                    attributes: props.attributes,
                                }
                            )
                        ),
                        el( InspectorControls, { key: 'wppb/edit-profile/inspector' },
                            [
                                el( PanelBody,
                                    {
                                        title: __( 'Form Settings' , 'profile-builder' ),
                                        key: 'wppb/edit-profile/inspector/form-settings'
                                    },
                                    [
                                        el( SelectControl,
                                            {
                                                label: __( 'Form' , 'profile-builder' ),
                                                key: 'wppb/edit-profile/inspector/form_settings/form_name',
                                                help: __( 'Select the desired Edit Profile form' , 'profile-builder' ),
                                                value: props.attributes.form_name,
                                                options: [
                                                    {
                                                        label: __( 'Default' , 'profile-builder' ),
                                                        value: ''
                                                    },
        <?php
        $wppb_module_settings = get_option( 'wppb_module_settings', 'not_found' );

        if ( !( ( $wppb_module_settings !== 'not_found' && (
                    !isset( $wppb_module_settings['wppb_multipleEditProfileForms'] ) ||
                    $wppb_module_settings['wppb_multipleEditProfileForms'] !== 'show'
                ) ) ||
            $wppb_module_settings === 'not_found' ) ){
            $args = array(
                'post_type'      => 'wppb-epf-cpt',
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
                                        props.attributes.form_name != '' ?
                                            el( Text,
                                                {
                                                    key: 'wppb/edit-profile/inspector/form_settings/notice'
                                                },
                                                [
                                                    __( 'Edit the Settings for this form ' , 'profile-builder' ),
                                                    el( Button,
                                                        {
                                                            key: 'wppb/edit-profile/inspector/form_settings/notice_button',
                                                            href: '<?php echo esc_url( admin_url( 'edit.php?post_type=wppb-epf-cpt' ) ); ?>',
                                                            target: '_blank',
                                                            text: __( 'here' , 'profile-builder' ),
                                                            variant: 'link'
                                                        }
                                                        )
                                                ]
                                            ) :
                                            '',
                                    ]
                                ),
                                props.attributes.form_name == '' ?
                                    el( PanelBody,
                                        {
                                            title: __( 'Redirect' , 'profile-builder' ),
                                            key: 'wppb/edit-profile/inspector/redirect'
                                        },
                                        el( SelectControl,
                                            {
                                                label: __( 'After Edit Profile' , 'profile-builder' ),
                                                key: 'wppb/edit-profile/inspector/redirect/redirect_url',
                                                help: __( 'Select a page for an After Edit Profile Redirect' , 'profile-builder' ),
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
                                        )
                                    ) :
                                    '',
                            ]
                        ),
                        el( blockEditor.InspectorAdvancedControls, { key: 'wppb/edit-profile/inspector_advanced' },
                            [
                                props.attributes.form_name == '' ?
                                    el( TextControl,
                                        {
                                            label: __( 'After Edit Profile' , 'profile-builder' ),
                                            key: 'wppb/edit-profile/inspector_advanced/redirect/redirect_url',
                                            help: __( 'Manually type in an After Edit Profile Redirect URL' , 'profile-builder' ),
                                            value: props.attributes.redirect_url,
                                            onChange: ( value ) => { props.setAttributes( { redirect_url: value } ); }
                                        }
                                    ) :
                                    ''
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
