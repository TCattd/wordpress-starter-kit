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

if (! defined('ABSPATH') ) { die('Forbidden'); }

// =====================================================================
// Display Plugins & Theme stats.

$buffer = code_profiler_get_profile_data( $profile_path, 'slugs');
if ( isset( $buffer['error'] ) ) {
	return;
}

// Fetch options
$cp_options = get_option('code-profiler');

// Name vs slug
if ( empty( $cp_options['display_name'] ) || ! in_array( $cp_options['display_name'], ['full', 'slug' ] ) ) {
	$cp_options['display_name'] = 'full';
}
// Truncate name
if ( empty( $cp_options['truncate_name'] ) || ! preg_match('/^\d+$/', ( $cp_options['truncate_name'] ) ) ) {
	$cp_options['truncate_name'] = 30;
}
// Horizontal vs vertical chart
if ( empty( $cp_options['chart_type'] ) || ! in_array( $cp_options['chart_type'], ['x', 'y' ] ) ) {
	$axis = 'x';
} else {
	$axis = $cp_options['chart_type'];
}
// Max plugins to display
if ( empty( $cp_options['chart_max_plugins'] ) || ! preg_match('/^\d+$/', ( $cp_options['chart_max_plugins'] ) ) ) {
	$chart_max_plugins = 25;
} else {
	$chart_max_plugins = $cp_options['chart_max_plugins'];
}

// Check if we should warn about composer
if (! empty( $cp_options['warn_composer'] ) ) {
	$composer_warning = code_profiler_composer_warning( $profile_path, $cp_options['display_name'] );
}

$label		= [];
$data			= '';
$theme		= esc_attr__('theme', 'code-profiler');
$total_time	= 0;
$hidden		= 0;
$count		= 0;
// Sort by value
usort( $buffer, function( $a, $b ) {
	return $b[1] <=> $a[1];
} );
foreach( $buffer as $k => $slugs ) {

	if ( empty( $slugs[1] ) && ! empty( $cp_options['hide_empty_value'] ) ) {
		$hidden++;
		$hidden_empty = 1;
		continue;
	}

	$count++;
	if ( $count > $chart_max_plugins ) {
		$hidden++;
		$hidden_max_plugins = 1;
		continue;
	}

	// Check whether we should use the name or the slug
	if ( $cp_options['display_name'] == 'slug') {
		$n = $slugs[0];
	} else {
		$n = $slugs[2];
	}

	// Truncate and sanitise the name
	if ( strlen( $n ) > $cp_options['truncate_name'] ) {
		$n = mb_substr( $n, 0, $cp_options['truncate_name'] , 'utf-8') .'...';
	}

	// Mark theme as such
	if ( $slugs[3] == 'theme') {
		$label[] = "{$n} ($theme)";
	// ...and MU plugins
	} elseif ( $slugs[3] == 'mu-plugin') {
		$label[] = "{$n} (MU)";
	} else {
		$label[] = $n;
	}

	$data .= "{$slugs[1]},";
	$total_time +=  $slugs[1];
}
$data  = rtrim( $data, ',');
$count = count( $label ) - 1;
?>
<div style="width:80vw;margin:auto" aria-hidden="true">
	<canvas id="myChart"></canvas>
</div>
<script>
jQuery(document).ready(function() {
	'use strict';
	cpjs_plugins_chart(
		'<?php echo esc_js( $axis ) ?>', [<?php
			for( $i = 0; $i < $count; $i++ ) {
				echo "'". addslashes( $label[ $i ] ) ."',";
			}
			echo "'". esc_js( $label[ $i ] ) ."'";
		?>],
		[<?php echo esc_js( $data ) ?>], <?php echo esc_js( number_format( $total_time, 4 ) ) ?>
	);
});
</script>

<!-- Screen-reader only -->
<div class="visually-hidden">
<?php
	$sr_data = explode(',', $data );
	echo "<table>\n".
		"\t<tr>\n".
			"\t\t<td>".	esc_html__('Component name', 'code-profiler') 		."</td>".
			"<td>".		esc_html__('Time in seconds', 'code-profiler') 		."</td>".
			"<td>".		esc_html__('Time in percentage', 'code-profiler')	."</td>\n";
	for( $i = 0; $i <= $count; $i++ ) {
		if ( empty( $sr_data[ $i ] ) ) {
			$sr_data[ $i ] = 0;
		}
		echo "\t<tr>\n\t\t<td>". esc_html( $label[ $i ] ) ."</td>".
		"<td>". esc_html( number_format( $sr_data[ $i ], 3 ) ) ."</td>".
		"<td>". esc_html( number_format( ( $sr_data[ $i ] / $total_time ) * 100 ) ) ."%</td>\n\t</tr>\n";
	}
	echo "</table>\n";
?>
</div>
<!-- End of Screen-reader only -->

<?php
// Actions below stats
$save_png	= 1;
$rotate_img	= 1;
$type			= 'slugs';

// =====================================================================
// Warn if multiple plugins are using composer

function code_profiler_composer_warning( $profile_path, $display_name ) {

	if (! file_exists( "$profile_path.composer.profile" ) ) {
		return;
	}
	$res = json_decode( file_get_contents( "$profile_path.composer.profile" ), true );
	// Make sure there are at least two items (free version only)
	if ( $res === false || count( $res ) < 2 ) {
		return;
	}

	$list		= '';
	$first	= '';
	$count	= 1;
	foreach( $res as $slug => $name ) {
		if ( $display_name == 'full') {
			$list .= "$count: <strong>". esc_html( $name ) .'</strong>, ';
			if ( empty( $first ) ) {
				$first = '<strong>'. esc_html( $name ) .'</strong>';
			}
		} else {
			$list .= "$count: <strong>". esc_html( $slug ) .'</strong>, ';
			if ( empty( $first ) ) {
				$first = '<strong>'. esc_html( $slug ) .'</strong>';
			}
		}
		$count++;
	}
	$list = rtrim( $list, ', ') .'.';

	$msg = esc_html__('Code Profiler has detected that the following components, sorted by execution order, are using Composer dependency manager:', 'code-profiler') . "<br />$list<br />".
	sprintf(
		esc_html__('As that may increase the execution time of %s, make sure to consult the following FAQ: %s%s%s', 'code-profiler'),
		$first,
		'<a href="?page=code-profiler&cptab=faq#composerwarning" target="_blank" rel="noopener noreferrer">',
		esc_html__('Why does Code Profiler warn me that I have multiple plugins using Composer?', 'code-profiler'),
		'</a>'
	);

	return '<div class="cp-notice cp-notice-orange"><p>'. $msg .'</p></div>';

}
// =====================================================================
// EOF
