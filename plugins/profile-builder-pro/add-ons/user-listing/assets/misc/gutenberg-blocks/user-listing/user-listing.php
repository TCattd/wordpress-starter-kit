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
            'wppb-block-user-listing',
            add_query_arg( [ 'action' => 'wppb-block-user-listing.js', ], admin_url( 'admin-ajax.php' ) ),
            [ 'wp-blocks', 'wp-element', 'wp-editor' ],
            microtime(),
            true
        );
        register_block_type(
            __DIR__,
            [
                'render_callback' => function( $attributes, $content ) {
                    ob_start();
                    do_action( 'wppb/user-listing/render_callback', $attributes, $content );
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
    'wppb/user-listing/render_callback',
    function( $attributes, $content ) {
        if ( $attributes['is_preview'] ) {
            echo '
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 230 200"
                    style="width: "100%";"
                >
                    <title>User Listing Block Preview</title>
                    <rect
                        width="129.7204"
                        height="14.749001"
                        x="25.764795"
                        y="34.563004"
                        rx="5.9528437"
                        id="rect2-5"
                        style="fill:#a0a5aa;stroke-width:0.6690836" />
                    <rect
                        width="24.549999"
                        height="11.875"
                        x="104.34999"
                        y="58.875"
                        rx="1.9546179"
                        id="rect4-6"
                        style="fill:#a0a5aa;stroke-width:0.5563103" />
                    <rect
                        width="25.036329"
                        height="3.3360386"
                        x="132.75174"
                        y="62.885708"
                        rx="1.3634222"
                        id="rect14-2"
                        style="fill:#a0a5aa;stroke-width:0.58587795" />
                    <rect
                        width="71.924995"
                        height="8.03125"
                        x="26.771873"
                        y="58.890625"
                        rx="5.7265129"
                        id="rect4-6-9"
                        style="fill:#a0a5aa;stroke-width:0.78307992" />
                    <rect
                        width="34.084526"
                        height="10.219671"
                        x="28.566124"
                        y="77.482635"
                        rx="1.5641322"
                        id="rect2-1"
                        style="fill:#a0a5aa;stroke-width:0.28549075" />
                    <rect
                        width="30.018661"
                        height="10.219671"
                        x="65.600845"
                        y="77.482635"
                        rx="1.3775505"
                        id="rect2-1-2"
                        style="fill:#a0a5aa;stroke-width:0.26792243" />
                    <rect
                        width="22.328876"
                        height="10.219671"
                        x="98.039368"
                        y="77.482635"
                        rx="1.0246677"
                        id="rect2-1-7"
                        style="fill:#a0a5aa;stroke-width:0.23107174" />
                    <rect
                        width="20.295944"
                        height="10.219671"
                        x="122.69971"
                        y="77.482635"
                        rx="0.93137687"
                        id="rect2-1-0"
                        style="fill:#a0a5aa;stroke-width:0.22030179" />
                    <rect
                        width="35.498741"
                        height="10.219671"
                        x="145.68069"
                        y="77.482635"
                        rx="1.6290302"
                        id="rect2-1-9"
                        style="fill:#a0a5aa;stroke-width:0.29135326" />
                    <rect
                        width="15.16942"
                        height="10.219671"
                        x="183.68767"
                        y="77.482635"
                        rx="0.69612169"
                        id="rect2-1-3"
                        style="fill:#a0a5aa;stroke-width:0.19045742" />
                    <rect
                        width="9.2395"
                        height="9.2395"
                        x="30.120932"
                        y="91.99575"
                        rx="4.4873724"
                        id="rect36-6"
                        style="fill:#a0a5aa;stroke-width:1.49579084" />
                    <rect
                        width="14.952198"
                        height="3.5854998"
                        x="44.906754"
                        y="94.822754"
                        rx="0.81426305"
                        id="rect14-0"
                        style="fill:#a0a5aa;stroke-width:0.46938983" />
                    <rect
                        width="15.703499"
                        height="3.5854998"
                        x="66.188004"
                        y="94.822754"
                        rx="0.85517722"
                        id="rect14-0-2"
                        style="fill:#a0a5aa;stroke-width:0.48103797" />
                    <rect
                        width="18.203499"
                        height="3.5854998"
                        x="98.938004"
                        y="94.822754"
                        rx="0.99132156"
                        id="rect14-0-6"
                        style="fill:#a0a5aa;stroke-width:0.51791513" />
                    <rect
                        width="2.8909988"
                        height="3.5854998"
                        x="131.313"
                        y="94.822754"
                        rx="0.15743729"
                        id="rect14-0-1"
                        style="fill:#a0a5aa;stroke-width:0.20639782" />
                    <rect
                        width="18.765999"
                        height="3.5854998"
                        x="146.438"
                        y="94.822754"
                        rx="1.0219541"
                        id="rect14-0-8"
                        style="fill:#a0a5aa;stroke-width:0.5258562" />
                    <rect
                        width="11.328499"
                        height="3.5854998"
                        x="185.5005"
                        y="94.822754"
                        rx="0.61692458"
                        id="rect14-0-7"
                        style="fill:#a0a5aa;stroke-width:0.4085708" />
                    <rect
                        width="9.2395"
                        height="9.2395"
                        x="30.180794"
                        y="107.78169"
                        rx="4.4873724"
                        id="rect36-6-9"
                        style="fill:#a0a5aa;stroke-width:1.49579084" />
                    <rect
                        width="14.952198"
                        height="3.5854998"
                        x="44.966614"
                        y="110.6087"
                        rx="0.81426305"
                        id="rect14-0-20"
                        style="fill:#a0a5aa;stroke-width:0.46938983" />
                    <rect
                        width="15.703499"
                        height="3.5854998"
                        x="66.247864"
                        y="110.6087"
                        rx="0.85517722"
                        id="rect14-0-2-2"
                        style="fill:#a0a5aa;stroke-width:0.48103797" />
                    <rect
                        width="18.203499"
                        height="3.5854998"
                        x="98.997864"
                        y="110.6087"
                        rx="0.99132156"
                        id="rect14-0-6-3"
                        style="fill:#a0a5aa;stroke-width:0.51791513" />
                    <rect
                        width="2.8909988"
                        height="3.5854998"
                        x="131.37286"
                        y="110.6087"
                        rx="0.15743729"
                        id="rect14-0-1-7"
                        style="fill:#a0a5aa;stroke-width:0.20639782" />
                    <rect
                        width="18.765999"
                        height="3.5854998"
                        x="146.49786"
                        y="110.6087"
                        rx="1.0219541"
                        id="rect14-0-8-5"
                        style="fill:#a0a5aa;stroke-width:0.5258562" />
                    <rect
                        width="11.328499"
                        height="3.5854998"
                        x="185.56038"
                        y="110.6087"
                        rx="0.61692458"
                        id="rect14-0-7-9"
                        style="fill:#a0a5aa;stroke-width:0.4085708" />
                    <rect
                        width="1.6977561"
                        height="2.6132281"
                        x="106.99594"
                        y="183.02811"
                        rx="0.092455983"
                        id="rect14-0-1-7-2-2"
                        style="fill:#a0a5aa;stroke-width:0.13503075" />
                    <rect
                        width="1.6977561"
                        height="2.6132281"
                        x="103.42175"
                        y="183.02811"
                        rx="0.092455983"
                        id="rect14-0-1-7-2-9"
                        style="fill:#a0a5aa;stroke-width:0.13503075" />
                    <rect
                        width="1.6977561"
                        height="2.6132281"
                        x="99.847557"
                        y="183.02811"
                        rx="0.092455983"
                        id="rect14-0-1-7-2-3"
                        style="fill:#a0a5aa;stroke-width:0.13503075" />
                    <rect
                        width="8.636241"
                        height="2.6132281"
                        x="110.57013"
                        y="183.02811"
                        rx="0.4703103"
                        id="rect14-0-1-7-2-19"
                        style="fill:#a0a5aa;stroke-width:0.30454916" />
                    <rect
                        width="8.5257559"
                        height="2.6132281"
                        x="121.08281"
                        y="183.02811"
                        rx="0.46429354"
                        id="rect14-0-1-7-2-4"
                        style="fill:#a0a5aa;stroke-width:0.30259481" />
                    <rect
                        width="9.2395"
                        height="9.2395"
                        x="30.180798"
                        y="122.59712"
                        rx="4.4873724"
                        id="rect36-6-4"
                        style="fill:#a0a5aa;stroke-width:1.49579084" />
                    <rect
                        width="14.952198"
                        height="3.5854998"
                        x="44.966621"
                        y="125.42416"
                        rx="0.81426305"
                        id="rect14-0-4"
                        style="fill:#a0a5aa;stroke-width:0.46938983" />
                    <rect
                        width="15.703499"
                        height="3.5854998"
                        x="66.247864"
                        y="125.42416"
                        rx="0.85517722"
                        id="rect14-0-2-3"
                        style="fill:#a0a5aa;stroke-width:0.48103797" />
                    <rect
                        width="18.203499"
                        height="3.5854998"
                        x="98.997864"
                        y="125.42416"
                        rx="0.99132156"
                        id="rect14-0-6-0"
                        style="fill:#a0a5aa;stroke-width:0.51791513" />
                    <rect
                        width="2.8909988"
                        height="3.5854998"
                        x="131.37286"
                        y="125.42416"
                        rx="0.15743729"
                        id="rect14-0-1-78"
                        style="fill:#a0a5aa;stroke-width:0.20639782" />
                    <rect
                        width="18.765999"
                        height="3.5854998"
                        x="146.49786"
                        y="125.42416"
                        rx="1.0219541"
                        id="rect14-0-8-6"
                        style="fill:#a0a5aa;stroke-width:0.5258562" />
                    <rect
                        width="11.328499"
                        height="3.5854998"
                        x="185.56036"
                        y="125.42416"
                        rx="0.61692458"
                        id="rect14-0-7-8"
                        style="fill:#a0a5aa;stroke-width:0.4085708" />
                    <rect
                        width="9.2395"
                        height="9.2395"
                        x="30.240658"
                        y="138.38306"
                        rx="4.4873724"
                        id="rect36-6-9-8"
                        style="fill:#a0a5aa;stroke-width:1.49579084" />
                    <rect
                        width="14.952198"
                        height="3.5854998"
                        x="45.026482"
                        y="141.21007"
                        rx="0.81426305"
                        id="rect14-0-20-4"
                        style="fill:#a0a5aa;stroke-width:0.46938983" />
                    <rect
                        width="15.703499"
                        height="3.5854998"
                        x="66.307724"
                        y="141.21007"
                        rx="0.85517722"
                        id="rect14-0-2-2-3"
                        style="fill:#a0a5aa;stroke-width:0.48103797" />
                    <rect
                        width="18.203499"
                        height="3.5854998"
                        x="99.057724"
                        y="141.21007"
                        rx="0.99132156"
                        id="rect14-0-6-3-1"
                        style="fill:#a0a5aa;stroke-width:0.51791513" />
                    <rect
                        width="2.8909988"
                        height="3.5854998"
                        x="131.43272"
                        y="141.21007"
                        rx="0.15743729"
                        id="rect14-0-1-7-4"
                        style="fill:#a0a5aa;stroke-width:0.20639782" />
                    <rect
                        width="18.765999"
                        height="3.5854998"
                        x="146.55772"
                        y="141.21007"
                        rx="1.0219541"
                        id="rect14-0-8-5-9"
                        style="fill:#a0a5aa;stroke-width:0.5258562" />
                    <rect
                        width="11.328499"
                        height="3.5854998"
                        x="185.62024"
                        y="141.21007"
                        rx="0.61692458"
                        id="rect14-0-7-9-2"
                        style="fill:#a0a5aa;stroke-width:0.4085708" />
                    <rect
                        width="9.2395"
                        height="9.2395"
                        x="30.172953"
                        y="153.11601"
                        rx="4.4873724"
                        id="rect36-6-0"
                        style="fill:#a0a5aa;stroke-width:1.49579084" />
                    <rect
                        width="14.952198"
                        height="3.5854998"
                        x="44.958775"
                        y="155.94301"
                        rx="0.81426305"
                        id="rect14-0-68"
                        style="fill:#a0a5aa;stroke-width:0.46938983" />
                    <rect
                        width="15.703499"
                        height="3.5854998"
                        x="66.240021"
                        y="155.94301"
                        rx="0.85517722"
                        id="rect14-0-2-9"
                        style="fill:#a0a5aa;stroke-width:0.48103797" />
                    <rect
                        width="18.203499"
                        height="3.5854998"
                        x="98.990021"
                        y="155.94301"
                        rx="0.99132156"
                        id="rect14-0-6-2"
                        style="fill:#a0a5aa;stroke-width:0.51791513" />
                    <rect
                        width="2.8909988"
                        height="3.5854998"
                        x="131.36502"
                        y="155.94301"
                        rx="0.15743729"
                        id="rect14-0-1-6"
                        style="fill:#a0a5aa;stroke-width:0.20639782" />
                    <rect
                        width="18.765999"
                        height="3.5854998"
                        x="146.49002"
                        y="155.94301"
                        rx="1.0219541"
                        id="rect14-0-8-64"
                        style="fill:#a0a5aa;stroke-width:0.5258562" />
                    <rect
                        width="11.328499"
                        height="3.5854998"
                        x="185.55252"
                        y="155.94301"
                        rx="0.61692458"
                        id="rect14-0-7-95"
                        style="fill:#a0a5aa;stroke-width:0.4085708" />
                    <rect
                        width="9.2395"
                        height="9.2395"
                        x="30.232813"
                        y="168.90195"
                        rx="4.4873724"
                        id="rect36-6-9-0"
                        style="fill:#a0a5aa;stroke-width:1.49579084" />
                    <rect
                        width="14.952198"
                        height="3.5854998"
                        x="45.018635"
                        y="171.72896"
                        rx="0.81426305"
                        id="rect14-0-20-48"
                        style="fill:#a0a5aa;stroke-width:0.46938983" />
                    <rect
                        width="15.703499"
                        height="3.5854998"
                        x="66.299881"
                        y="171.72896"
                        rx="0.85517722"
                        id="rect14-0-2-2-7"
                        style="fill:#a0a5aa;stroke-width:0.48103797" />
                    <rect
                        width="18.203499"
                        height="3.5854998"
                        x="99.049881"
                        y="171.72896"
                        rx="0.99132156"
                        id="rect14-0-6-3-17"
                        style="fill:#a0a5aa;stroke-width:0.51791513" />
                    <rect
                        width="2.8909988"
                        height="3.5854998"
                        x="131.42488"
                        y="171.72896"
                        rx="0.15743729"
                        id="rect14-0-1-7-27"
                        style="fill:#a0a5aa;stroke-width:0.20639782" />
                    <rect
                        width="18.765999"
                        height="3.5854998"
                        x="146.54988"
                        y="171.72896"
                        rx="1.0219541"
                        id="rect14-0-8-5-2"
                        style="fill:#a0a5aa;stroke-width:0.5258562" />
                    <rect
                        width="11.328499"
                        height="3.5854998"
                        x="185.6124"
                        y="171.72896"
                        rx="0.61692458"
                        id="rect14-0-7-9-26"
                        style="fill:#a0a5aa;stroke-width:0.4085708" />
                </svg>';
        } else {
            if ( $attributes['single'] ) {
                $atts = [
                    'name' => ' name="' . esc_attr( $attributes['name'] ) . '"',
                    'meta_key' => '',
                    'meta_value' => '',
                    'include' => '',
                    'exclude' => '',
                    'single' => ' single',
                    'id' => $attributes['id'] !== '' ? ' id="' . esc_attr( $attributes['id'] ) . '"' : '',
                ];
            } else {
                $atts = [
                    'name' => ' name="' . esc_attr( $attributes['name'] ) . '"',
                    'meta_key' => $attributes['meta_key'] !== '' ? ' meta_key="' . esc_attr( $attributes['meta_key'] ) . '"' : '',
                    'meta_value' => $attributes['meta_value'] !== '' ? ' meta_value="' . esc_attr( $attributes['meta_value'] ) . '"' : '',
                    'include' => $attributes['include_manual'] !== '' ? ' include="' . esc_attr( $attributes['include_manual'] ) . '"' : '',
                    'exclude' => $attributes['exclude_manual'] !== '' ? ' exclude="' . esc_attr( $attributes['exclude_manual'] ) . '"' : '',
                    'single' => '',
                    'id' => '',
                ];
            }
            echo '<div class="wppb-block-container">' . do_shortcode( '[wppb-list-users' . $atts['name'] . $atts['meta_key'] . $atts['meta_value'] . $atts['include'] . $atts['exclude'] . $atts['single'] . $atts['id'] . ' ]' ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
    },
    10,
    2
);

/**
 * Register: JavaScript.
 */
add_action(
    'wp_ajax_wppb-block-user-listing.js',
    function() {
        header( 'Content-Type: text/javascript' );
        $ul_names = wppb_get_userlisting_names();
        $default_key = is_null( key($ul_names) ) ? '' : key($ul_names);

        $wppb_manage_fields = get_option( 'wppb_manage_fields', 'not_found' );
        $meta_names = array();
        foreach( $wppb_manage_fields as $value ){
            $meta_names[] = $value['meta-name'];
        }

        $query_args = array(
            'fields'  => array( 'ID', 'user_login', 'display_name' ),
            'orderby' => array( 'display_name', 'user_login' ),
        );

        $users = get_users( apply_filters( 'wppb_edit_other_users_dropdown_query_args', $query_args ) );

        ?>
        ( function ( blocks, i18n, element, serverSideRender, blockEditor, components ) {
            var { __ } = i18n;
            var el = element.createElement;
            var PanelBody = components.PanelBody;
            var SelectControl = components.SelectControl;
            var ToggleControl = components.ToggleControl;
            var TextControl = components.TextControl;
            var InspectorControls = wp.editor.InspectorControls;

            blocks.registerBlockType( 'wppb/user-listing', {
                icon:
                    el('svg', {},
                        el( 'path',
                            {
                                d: "m 7.5454697,4.7968766 c -0.3132,-0.1879 -0.5677,-0.3911 -0.7888,-0.6858 -0.5671,-0.756 -0.6818,-1.7686 -0.2868,-2.6289 0.1444,-0.3146 0.311,-0.53120005 0.5549,-0.77470005 0.2166,-0.2163 0.5029,-0.40179999 0.7874,-0.51559999 0.3422,-0.1369 0.6858,-0.1999 1.0541,-0.1956 0,0 0.127,0.012 0.127,0.012 0.2763,0.019 0.5302,0.081 0.7874,0.184 0.2860003,0.1144 0.5696003,0.29809999 0.7874003,0.51559999 0.2546,0.2543 0.4271,0.48210005 0.5732,0.81280005 0.3766,0.8521 0.2517,1.8482 -0.3051,2.5908 -0.2253,0.3002 -0.4702,0.4945 -0.7888,0.6858 0.3393,0.113 0.6096,0.2129 0.9271,0.3849 0.5965,0.3234 1.1469,0.8302 1.524,1.3931 0.112,0.1673 0.2053,0.3282 0.2952,0.508 0.045,0.089 0.098,0.1833 0.1239,0.2794 0,0 -0.4064,0.1759 -0.4064,0.1759 0,0 -0.3429,0.1416 -0.3429,0.1416 -0.166,-0.3913 -0.3968,-0.7554 -0.687,-1.0668 -0.5655,-0.607 -1.3231,-1.0157 -2.1451003,-1.1375 0,0 -0.3556,-0.032 -0.3556,-0.032 0,0 -0.127,-0.012 -0.127,-0.012 0,0 -0.089,0 -0.089,0 0,0 -0.1524,0.013 -0.1524,0.013 0,0 -0.1143,0 -0.1143,0 -0.4373,0.043 -0.8451,0.1501 -1.2446,0.3336 -0.3929,0.1804 -0.7492,0.4405 -1.0541,0.7454 -0.331,0.331 -0.5922,0.7253 -0.7747,1.1557 0,0 -0.7493,-0.3175 -0.7493,-0.3175 0.026,-0.096 0.079,-0.1901 0.1238,-0.2794 0.088,-0.1764 0.1854,-0.344 0.2953,-0.508 0.4591,-0.6854 1.1065,-1.2149 1.8542,-1.5582 0,0 0.5969,-0.2198 0.5969,-0.2198 z m 1.1176,-3.98600005 c -0.1665,0.026 -0.2821,0.038 -0.4445,0.096 -0.4802,0.17090005 -0.8602,0.53290005 -1.0484,1.00720005 -0.3652,0.9201 0.1181,1.9804 1.0611,2.2987 0.1212,0.041 0.3304,0.087 0.4572,0.089 0.3746,0 0.631,-0.036 0.9652,-0.2198 0.1698,-0.093 0.3491003,-0.2417 0.4730003,-0.3898 0.5852,-0.6995 0.5568,-1.7239 -0.093,-2.3737 -0.1542003,-0.1542 -0.3574003,-0.286 -0.5576003,-0.37140005 -0.1728,-0.073 -0.4218,-0.1355 -0.6096,-0.136 0,0 -0.2032,0 -0.2032,0 z M 3.1639696,13.255076 c -0.2125,-0.1388 -0.3735,-0.2363 -0.5588,-0.4192 -0.4208,-0.4156 -0.7485,-1.0898 -0.7493,-1.688999 0,0 0,-0.2794 0,-0.2794 3e-4,-0.2719 0.1042,-0.653 0.2141,-0.9017004 0.3308,-0.7483 1.0161,-1.3002 1.8179001,-1.4605 0.1127,-0.023 0.2802,-0.049 0.3937,-0.051 0,0 0.2413,0 0.2413,0 0.2877,5e-4 0.6784,0.1001 0.9398,0.2198 0.3017,0.1382 0.5269,0.2944 0.762,0.5295 0.8629,0.8630004 0.9885,2.2254004 0.313,3.2384994 -0.2418,0.3625 -0.5152,0.5912 -0.8845,0.8128 1.2654,0.3696 2.2673,1.2718 2.7957,2.4765 0.1021,0.2329 0.1921,0.5136 0.2469,0.762 0.029,0.1329 0.061,0.404 0.107,0.508 0,0 0.021,-0.1016 0.021,-0.1016 0,0 0.04,-0.2413 0.04,-0.2413 0.073,-0.3772 0.1762,-0.7046 0.3367,-1.0541 0.5194,-1.1312 1.5480003,-2.0015 2.7393003,-2.3495 -0.3658,-0.2194 -0.6453,-0.4541 -0.8846,-0.8128 -0.6746,-1.011699 -0.549,-2.376399 0.3131,-3.2384994 0.2316,-0.2316 0.4644,-0.3932 0.762,-0.5295 0.2613,-0.1197 0.652,-0.2193 0.9398,-0.2198 0,0 0.2413,0 0.2413,0 0.1192,0 0.3135,0.032 0.4318,0.058 0.83,0.183 1.5094,0.7641 1.823,1.5547004 0.085,0.2151 0.1705,0.5694 0.1709,0.8001 0,0 0,0.2794 0,0.2794 -9e-4,0.596799 -0.3285,1.276199 -0.7493,1.688999 -0.1895,0.1857 -0.3428,0.2781 -0.5588,0.4192 0.2561,0.084 0.5088,0.1713 0.7493,0.2954 0.3241,0.1671 0.6249,0.3747 0.9017,0.6118 0,0 0.127,0.118 0.127,0.118 0.1079,0.095 0.1132,0.1117 0.2037,0.2067 0.5745,0.6038 0.9695,1.4269 1.1054,2.2479 0,0 0.026,0.1651 0.026,0.1651 0.018,0.088 0.035,0.085 0.036,0.1905 0,0 0,0.2032 0,0.2032 0,0 0,0.1524 0,0.1524 -0.018,0.1161 -0.1052,0.215 -0.215,0.2526 -0.045,0.016 -0.08,0.014 -0.127,0.014 0,0 -5.7785,0 -5.7785,0 0,0 -1.9431003,0 -1.9431003,0 0,0 -0.4826,-0.01 -0.4826,-0.01 -0.042,-0.01 -0.094,-0.035 -0.1265,-0.062 -0.06,-0.049 -0.083,-0.1056 -0.1148,-0.1729 -0.057,0.3013 -0.3963,0.2413 -0.6223,0.2413 0,0 -1.9177,0 -1.9177,0 0,0 -4.7498001,0 -4.7498001,0 0,0 -0.99059995,0 -0.99059995,0 -0.1598,0 -0.3117,0.027 -0.4251,-0.1148 -0.1001004,-0.125 -0.072,-0.3528 -0.07,-0.5075 0,-0.1058 0.018,-0.1026 0.036,-0.1905 0,0 0.055,-0.3175 0.055,-0.3175 0.1159,-0.5796 0.3658,-1.1548 0.7051,-1.6383 0.096,-0.137 0.20140005,-0.2681 0.31190005,-0.3937 0,0 0.1181,-0.127 0.1181,-0.127 0.091,-0.1044 0.2523999,-0.2451 0.3609999,-0.3354 0.4137,-0.3442 0.8656,-0.5935 1.3716,-0.7734 0,0 0.1905,-0.06 0.1905,-0.06 z M 4.2307697,9.2708766 c -0.1662,0.024 -0.313,0.053 -0.4699,0.1164 -0.4760001,0.1923 -0.8492001,0.5864 -1.0116001,1.0738004 -0.054,0.1635 -0.07,0.3004 -0.082,0.4699 0,0.04 -0.011,0.06 -0.01,0.1016 0.018,0.2432 0.035,0.4034 0.1287,0.634999 0.195,0.4815 0.5946001,0.8471 1.0865001,1.0075 0.136,0.044 0.3271,0.083 0.4699,0.085 0.2805,0 0.5306,-0.039 0.7874,-0.1563 0.2273,-0.104 0.3999,-0.2319 0.5703,-0.4152 0.1304,-0.1401 0.2581,-0.3424 0.3288,-0.5207 0.074,-0.186 0.1271,-0.421899 0.1296,-0.622299 0.01,-0.7541 -0.465,-1.4417004 -1.1811,-1.6881004 -0.2332,-0.08 -0.5039,-0.1091 -0.7493,-0.086 z m 8.7757003,0 c -0.1279,0.021 -0.245,0.04 -0.3683,0.081 -0.4781,0.1592 -0.8628,0.5067 -1.0614,0.9714004 -0.3146,0.7358 -0.095,1.586399 0.5407,2.076499 0.1647,0.1268 0.3243,0.2075 0.5207,0.273 0.1433,0.048 0.3313,0.087 0.4826,0.089 0.2735,0 0.5369,-0.042 0.7874,-0.1563 0.2062,-0.094 0.4174,-0.247 0.5693,-0.4152 0.4754,-0.5263 0.5847,-1.275399 0.2903,-1.917699 -0.1018,-0.2224 -0.2494,-0.4049004 -0.4278,-0.5703004 -0.1274,-0.1182 -0.3103,-0.2285 -0.4699,-0.2968 -0.1549,-0.066 -0.4034,-0.1342 -0.5715,-0.1344 0,0 -0.2921,0 -0.2921,0 z M 7.9137697,16.749376 c -0.01,-0.118 -0.084,-0.3544 -0.1264,-0.4699 -0.1188,-0.3249 -0.2184,-0.5241 -0.4112,-0.8128 -0.2187,-0.3274 -0.4937,-0.6163 -0.8086,-0.8525 -1.0512,-0.7885 -2.4482,-0.9504 -3.6449001,-0.4009 -0.3436,0.1578 -0.6328,0.3521 -0.9144,0.6037 -0.2594,0.2315 -0.4843,0.5037 -0.663,0.8021 -0.1844999,0.3082 -0.39169995,0.7737 -0.44189995,1.1303 0,0 7.01040005,0 7.01040005,0 z m 8.7757003,0 c -0.05,-0.3561 -0.2575,-0.8227 -0.442,-1.1303 -0.1938,-0.3229 -0.4312,-0.6006 -0.7137,-0.849 -1.2735,-1.1191 -3.1595,-1.1677 -4.5085,-0.1559 -0.315,0.2362 -0.59,0.5251 -0.8087,0.8525 -0.1924,0.2882 -0.3222003,0.5478 -0.4318003,0.8763 -0.036,0.109 -0.096,0.2954 -0.1057,0.4064 0,0 7.0104003,0 7.0104003,0 z"
                            }
                        )
                    ),
                attributes: {
                    name : {
                        type: 'string',
                        default: "<?php echo esc_attr( $default_key ); ?>",
                    },
                    single : {
                        type: 'boolean',
                        default: false,
                    },
                    meta_key : {
                        type: 'string',
                        default: '',
                    },
                    meta_value : {
                        type: 'string',
                        default: '',
                    },
                    include_manual : {
                        type: 'string',
                        default: '',
                    },
                    exclude_manual : {
                        type: 'string',
                        default: '',
                    },
                    id : {
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
                            Object.assign( blockEditor.useBlockProps(), { key: 'wppb/user-listing/render' } ),
                            el( serverSideRender,
                                {
                                    block: 'wppb/user-listing',
                                    attributes: props.attributes,
                                }
                            )
                        ),
                        el( InspectorControls, { key: 'wppb/user-listing/inspector' },
                            [
                                el( PanelBody,
                                    {
                                        title: __( 'Listing Settings' , 'profile-builder' ),
                                        key: 'wppb/user-listing/inspector/listing-settings',
                                    },
                                    [
                                        el( SelectControl,
                                            {
                                                label: __( 'User Listing' , 'profile-builder' ),
                                                key: 'wppb/user-listing/inspector/listing-settings/form_name',
                                                help: __( 'Select the desired User Listing' , 'profile-builder' ),
                                                value: props.attributes.form_name,
                                                options: [
                                                    {
                                                        label: "<?php echo esc_html( ( $default_key !== '' ? $ul_names[$default_key] : 'Default' ) ); ?>",
                                                        value: "<?php echo esc_attr( $default_key ); ?>"
                                                    },
        <?php
        foreach ( $ul_names as $post_name => $post_title ) {
            if ( $post_name !== $default_key ) {
        ?>
                                                    {
                                                        label: "<?php echo esc_html( $post_title ); ?>",
                                                        value: "<?php echo esc_attr( $post_name ); ?>"
                                                    },
        <?php
            }
        }
        ?>
                                                ],
                                                onChange: ( value ) => { props.setAttributes( { name: value } ); }
                                            }
                                        ),
                                        el( ToggleControl,
                                            {
                                                label: __( 'Single' , 'profile-builder' ),
                                                key: 'wppb/user-listing/inspector/listing-settings/single',
                                                help: __( 'Select whether to show the Single User Listing template' , 'profile-builder' ),
                                                checked: props.attributes.single,
                                                onChange: ( value ) => { props.setAttributes( { single: !props.attributes.single } ); }
                                            }
                                        ),
                                        props.attributes.single == false ?
                                            el( SelectControl,
                                                {
                                                    label: __( 'Meta Key' , 'profile-builder' ),
                                                    key: 'wppb/user-listing/inspector/listing-settings/meta_key',
                                                    help: __( 'Select the Meta Name of a field. Only users that have the designated Meta Value for this field will be included in the Listing' , 'profile-builder' ),
                                                    value: props.attributes.meta_key,
                                                    options: [
                                                        {
                                                            label: __( '' , 'profile-builder' ),
                                                            value: ''
                                                        },
        <?php
        if( !empty( $meta_names ) ){
            foreach( $meta_names as $meta_name ){
                if( $meta_name ){
                    ?>
                                                        {
                                                            label: '<?php echo esc_html( $meta_name ); ?>',
                                                            value: '<?php echo esc_html( $meta_name ); ?>'
                                                        },
                    <?php
                }
            }
        }
        ?>
                                                    ],
                                                    onChange: ( value ) => { props.setAttributes( { meta_key: value } ); }
                                                }
                                            ):
                                            '',
                                        props.attributes.single == false && props.attributes.meta_key != '' ?
                                            el( TextControl,
                                                {
                                                    label: __( 'Meta Value' , 'profile-builder' ),
                                                    key: 'wppb/user-listing/inspector/listing-settings/meta_value',
                                                    help: __( 'Input the desired Meta Value for the selected field' , 'profile-builder' ),
                                                    value: props.attributes.meta_value,
                                                    onChange: ( value ) => { props.setAttributes( { meta_value: value } ); }
                                                }
                                            ):
                                            '',
                                        props.attributes.single == false ?
                                            el( TextControl,
                                                {
                                                    label: __( 'Include' , 'profile-builder' ),
                                                    key: 'wppb/user-listing/inspector_advanced/listing-settings/include_manual',
                                                    help: __( 'Input a list of user IDs. Only the selected users will be included in the Listing' , 'profile-builder' ),
                                                    value: props.attributes.include_manual,
                                                    onChange: ( value ) => { props.setAttributes( { include_manual: value } ); }
                                                }
                                            ):
                                            '',
                                        props.attributes.single == false ?
                                            el( TextControl,
                                                {
                                                    label: __( 'Exclude' , 'profile-builder' ),
                                                    key: 'wppb/user-listing/inspector_advanced/listing-settings/exclude_manual',
                                                    help: __( 'Input a list of user IDs. The selected users will be omitted from the Listing' , 'profile-builder' ),
                                                    value: props.attributes.exclude_manual,
                                                    onChange: ( value ) => { props.setAttributes( { exclude_manual: value } ); }
                                                }
                                            ):
                                            '',
                                        props.attributes.single == true ?
                                            el( TextControl,
                                                {
                                                    label: __( 'ID' , 'profile-builder' ),
                                                    key: 'wppb/user-listing/inspector/listing-settings/id',
                                                    help: __( 'Input the ID for the desired user. Leaving this field blank will show the Single User Listing for the current user' , 'profile-builder' ),
                                                    value: props.attributes.id,
                                                    onChange: ( value ) => { props.setAttributes( { id: value } ); }
                                                }
                                            ) :
                                            '',
                                    ]
                                ),
                            ]
                        ),
                        el( blockEditor.InspectorAdvancedControls, { key: 'wppb/user-listing/inspector_advanced' },
                            [
                                props.attributes.single == false ?
                                    el( TextControl,
                                        {
                                            label: __( 'Meta Key' , 'profile-builder' ),
                                            key: 'wppb/user-listing/inspector_advanced/listing-settings/meta_key',
                                            help: __( 'Manually type in the meta name of a field' , 'profile-builder' ),
                                            value: props.attributes.meta_key,
                                            onChange: ( value ) => { props.setAttributes( { meta_key: value } ); }
                                        }
                                    ):
                                    '',
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
