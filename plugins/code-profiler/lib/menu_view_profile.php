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

if (! defined( 'ABSPATH' ) ) { die('Forbidden'); }

// =====================================================================
// Display selected profile.

// Display the profile name
if ( preg_match('`/\d{10}\.\d+?\.(.+)$`', $profile_path, $match ) ) {
	$profile_name = sanitize_file_name( $match[1] );
} else {
	$profile_name = 'profile';
}
// Fetch summary to display in tooltip (CP >1.2)
$summary_stats = code_profiler_getsummarystats( $profile_path );

?><br />
<div class="alignleft actions bulkactions">
	<select style="max-width: none;" onchange='window.location="?page=code-profiler&cptab=profiles_list&action=view_profile&section=" + this.value;'>
	<?php
	foreach( $section_list as $num => $section_name ) {
		$orderby	= '';
		$order 	= '';
		// Set the sorting order for the select box URI
		if ( $num == 2 || $num == 3 ) {
			$orderby = 'time'; $order = 'desc';
		} elseif ( $num == 4 ) {
			$orderby = 'order'; $order = 'asc';
		}
		echo '<option value="'. (int) $num .
				sprintf(
					'&id=%s&orderby=%s&order=%s',
					esc_attr( $id ),
					esc_attr( $orderby ),
					esc_attr( $order )
				) .'"'.
				selected( $num, $section, false ) .'>'.
				sprintf(
					esc_html__('Page %s: %s', 'code-profiler'),
					(int) $num,
					esc_html( $section_name )
				) .
		'</option>';
	}
	?>
	</select>
	<?php
	if ( $section > 1 ) {
		if ( $section == 3 || $section == 4 ) {
			$orderby	= 'time';
			$order	= 'desc';
		} elseif ( $section == 5 ) {
			$orderby	= 'order';
			$order	= 'asc';
		}
		?>
		<a class="prev-page button" href="?page=code-profiler&cptab=profiles_list&action=view_profile&id=<?php echo esc_attr( $id ) ?>&section=<?php echo esc_attr( $section - 1 ) ?><?php
			sprintf(
				'&orderby=%s&order=%s',
				esc_attr( $orderby ),
				esc_attr( $order )
			)
		?>">&lsaquo;</a>

		<?php
	} else {
		?>
		<input type="button" class="prev-page button" value="&lsaquo;" disabled />
		<?php
	}
	if ( $section < count( $section_list ) ) {
		if ( $section == 1 || $section == 2 ) {
			$orderby	= 'time';
			$order	= 'desc';
		} elseif ( $section == 3 ) {
			$orderby	= 'order';
			$order	= 'asc';
		}
		?>
		<a class="next-page button" href="?page=code-profiler&cptab=profiles_list&action=view_profile&id=<?php echo esc_attr( $id ) ?>&section=<?php echo esc_attr( $section + 1 ) ?><?php
			sprintf(
				'&orderby=%s&order=%s',
				esc_attr( $orderby ),
				esc_attr( $order )
			)
			?>">&rsaquo;</a>
		<?php

	} else {
		?>
		<input class="next-page button" type="button" value="&rsaquo;" disabled />
		<?php
	}
	if ( $section != 4 ) {
		echo '<p><span class="description">';
		printf( esc_html__( 'Viewing: %s', 'code-profiler' ), esc_html( $match[1] ) );
		echo '</span>';
		if ( $summary_stats ) {
			echo '<span class="code-profiler-tip" data-tip="'. esc_attr( $summary_stats ) .'"></span>';
		}
		echo '</p>';
	}
?>
</div>
<?php
if ( $section == 1 ) {
	require 'menu_view_plugins.php';

} elseif ( $section == 2 ) {
	require 'menu_view_iostats.php';

} elseif ( $section == 3 ) {
	require 'menu_view_diskio.php';

} elseif ( $section == 4 ) {
	require 'menu_pro.php';

}

if ( isset( $buffer['error'] ) ) {
	echo $buffer['error'];
	return;
}

if ( $section == 4 ) { return; }

// Display footer with help, menu and buttons.

// Hide it while the graphs are loading
if ( $section == 1 || $section == 2 || $section == 3 ) {
	echo '<div id="cp-footer-buttons" style="display:none" class="tablenav bottom">';
} else {
	echo '<div class="tablenav bottom">';
}

?>
	<div id="cp-footer-help" style="display:none">
		<?php include 'help.php'; ?>
		<br />
	</div>

	<div style="text-align:center;">
		<?php
		if (! empty( $hidden ) ) {
			echo '<p class="description">';
			printf( esc_html__('Hidden items: %s', 'code-profiler'), $hidden );

			if ( $type == 'slugs' &&  isset( $hidden_max_plugins ) ) {
				?>
				<span class="code-profiler-tip" data-tip="<?php printf( esc_attr__('Only the first %s plugins will be shown on the graph. You can modify that value in the "Settings" page.', 'code-profiler' ), (int) $chart_max_plugins ) ?>"></span>
				</p>
				<?php
			} else {
				?>
				<span class="code-profiler-tip" data-tip="<?php esc_attr_e('Items that have an empty value are not shown. If you want to display them anyway, you can enable that option in the "Settings" page.', 'code-profiler' ) ?>"></span>
				</p>
				<?php
			}
		}
		?>
	</div>

	<?php
	if (! empty( $composer_warning ) ) {
		echo $composer_warning;
	}
	?>

	<div class="alignleft actions bulkactions">
		<input type="button" class="button-primary" style="min-width:100px" value="<?php esc_attr_e('Help', 'code-profiler' )?>" onclick="jQuery('#cp-footer-help').slideToggle(500);"/>
	</div>

	<div class="tablenav-pages">

	<?php
	if ( isset( $rotate_img ) ) {
	?>
		<button type="button" class="button-secondary" id="htov" title="<?php esc_attr_e('Click to rotate graph', 'code-profiler' )?>"><span class="dashicons dashicons-image-rotate" style="display: inline-block;vertical-align: middle;"></span></button>&nbsp;
	<?php
	}
	if ( isset( $save_png ) ) {
	?>
		<a type="button" class="button-secondary" id="download-png-img" download="<?php echo esc_attr( $profile_name) ?>_plugins.png" title="<?php esc_attr_e('Click to download as an image', 'code-profiler' )?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-download" style="display: inline-block;vertical-align: middle;"></span><?php esc_attr_e('Download as a PNG image', 'code-profiler' )?></a>&nbsp;
	<?php
	}
	?>
	</div>
</div>

<?php

// =====================================================================
// EOF
