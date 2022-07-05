<?php

add_shortcode( 'compare', 'wppb_toolbox_compare_shortcode' );
function wppb_toolbox_compare_shortcode( $atts, $content ){
	extract(
		$out = shortcode_atts(	array( 'val1' => '', 'val2' => '', 'operator' => ''), $atts )
	);

	foreach($out as $key => $value){
		$out[$key] = str_replace('&#8221;', '', $value );
	}

	$l = $out['val1'];
	$r = $out['val2'];

    $operators = array(
        '=='    => function($l, $r) {
                    return $l == $r;
                },
        '==='   => function($l, $r) {
                    return $l === $r;
                },
        '!='    => function($l, $r) {
                    return $l != $r;
                },
        '<'     => function($l, $r) {
                    return $l < $r;
                },
        '>'     => function($l, $r) {
                    return $l > $r;
                },
        '<='    => function($l, $r) {
                    return $l <= $r;
                },
        '>='    => function($l, $r) {
                    return $l >= $r;
                },
        ''      => function($l, $r) {
                    return $l == $r;
                },
    );

	if ( !array_key_exists($out['operator'], $operators ) )
		return '<p>The compare operator <strong style="padding:0 10px;">' . $out["operator"] . '</strong> is not recognized. Please try: == , ===, !=, <, >, <=, >=';

	$bool = $operators[$out['operator']]($l, $r);

	if( $bool )
		return do_shortcode( $content );
}
