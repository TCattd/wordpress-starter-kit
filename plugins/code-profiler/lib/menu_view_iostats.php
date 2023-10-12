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

$buffer = code_profiler_get_profile_data( $profile_path, 'iostats' );
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

$label  = [];
$data   = '';
$color  = '';
$colorh = '';
$colorb = '';
$hidden = 0;
$total_calls = 0;
foreach( $buffer as $k => $slugs ) {
	if ( empty( $slugs[1] ) && ! empty( $cp_options['hide_empty_value'] ) ) {
		$hidden++;
		continue;
	}
	$label[]			= $slugs[0];
	$data 			.= "{$slugs[1]},";
	$total_calls	+= $slugs[1];
}
$count = count( $label ) - 1;
$data  = rtrim( $data, ',' );

?>
<div style="width:80vw;margin:auto" aria-hidden="true">
	<canvas id="myChart"></canvas>
</div>
<script>
jQuery(document).ready(function() {
	'use strict';
	cpjs_iostats_chart('<?php echo esc_js( $axis ) ?>', [<?php
			for( $i = 0; $i < $count; $i++ ) {
				echo "'". esc_js( $label[ $i ] ) ."',";
			}
			echo "'". esc_js( $label[ $i ] ) ."'";
		?>],
		[<?php echo esc_js( $data ) ?>], <?php echo esc_js( $total_calls ) ?>
	);
});
</script>

<!-- Screen-reader only -->
<div class="visually-hidden">
<?php
	$sr_data = explode(',', $data );
	echo "<table>\n".
		"\t<tr>\n".
			"\t\t<td>".	esc_html__('I/O operations', 'code-profiler') 		."</td>".
			"<td>".		esc_html__('Total calls', 'code-profiler') ."</td>\n";
	for( $i = 0; $i <= $count; $i++ ) {
		echo "\t<tr>\n\t\t<td>". esc_html( $label[ $i ] ) ."</td>".
		"<td>". esc_html( $sr_data[ $i ] ) ."</td>\n\t</tr>\n";
	}
	echo "</table>\n";
?>
</div>
<!-- End of Screen-reader only -->

<?php
// Actions below stats
$save_png   = 1;
$rotate_img = 1;
$type       = 'iostats';
// =====================================================================
// EOF
