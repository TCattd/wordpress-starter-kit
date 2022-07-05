<?php

add_shortcode( 'wppb-format-date', 'wppb_toolbox_format_date_handler' );
function wppb_toolbox_format_date_handler( $atts ){
	$a = shortcode_atts( array(
		   'date' => null,
		   'format' => null,
	),$atts );

	if ($a['date'] === null)
		return;

	if ($a['format'] === null)
		return $a['date'];

	$date = strtotime($a['date']);

	return date($a['format'], $date);
}
