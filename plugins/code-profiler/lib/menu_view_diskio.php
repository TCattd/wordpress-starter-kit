<?php
/*
 +=====================================================================+
 |    ____          _        ____             __ _ _                   |
 |   / ___|___   __| | ___  |  _ \ _ __ ___  / _(_) | ___ _ __         |
 |  | |   / _ \ / _` |/ _ \ | |_) | '__/ _ \| |_| | |/ _ \ '__|        |
 |  | |__| (_) | (_| |  __/ |  __/| | | (_) |  _| | |  __/ |           |
 |   \____\___/ \__,_|\___| |_|   |_|  \___/|_| |_|_|\___|_|           |
 |                                                                     |
 |  (c) Jerome Bruandet ~ https://code-profiler.com/                   |
 +=====================================================================+
*/

if (! defined( 'ABSPATH' ) ) { die( 'Forbidden' ); }

// =====================================================================
// Display File I/O stats.

$buffer = code_profiler_get_profile_data( $profile_path, 'diskio' );
if ( isset( $buffer['error'] ) ) {
	return;
}

// Fetch options
$cp_options = get_option( 'code-profiler' );

// Horizontal vs vertical chart
if ( empty( $cp_options['chart_type'] ) || ! in_array( $cp_options['chart_type'], ['x', 'y' ] ) ) {
	$axis = 'x';
} else {
	$axis = $cp_options['chart_type'];
}

?>
<div style="width:80vw;margin:auto" aria-hidden="true">
	<canvas id="myChart"></canvas>
</div>
<script>
jQuery(document).ready(function() {
	'use strict';
	cpjs_diskio_chart(
		'<?php echo esc_js( $axis ) ?>', [<?php
			echo	"'". 		esc_js( __('I/O Read bytes', 'code-profiler') ) .
					"', '".	esc_js( __('I/O Write bytes', 'code-profiler') ) .
					"'" ?>], [<?php
			echo	"'". 		(int) $buffer[0][1] ."', '". (int) $buffer[1][1] ."'"
		?>]
	);
});
</script>

<!-- Screen-reader only -->
<div class="visually-hidden">
<?php
	echo "<table>\n".
		"\t<tr>\n".
		"\t\t<td>".	esc_html__('I/O Read bytes', 'code-profiler') 		."</td>".
		"<td>".		esc_html__('I/O Write bytes', 'code-profiler') ."</td>\n".
		"\t<tr>\n\t\t<td>". esc_html( number_format( $buffer[0][1] ) ) ."</td>".
		"<td>". esc_html( number_format( $buffer[1][1] ) ) ."</td>\n\t</tr>\n".
		"</table>\n";
?>
</div>
<!-- End of Screen-reader only -->

<?php
// Actions below stats
$save_png   = 1;
$rotate_img = 1;
$type       = 'diskio';
// =====================================================================
// EOF
