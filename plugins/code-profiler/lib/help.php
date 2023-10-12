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

if (! defined('ABSPATH') ) {
	die('Forbidden');
}

// =====================================================================
// Display contextual help below each chart and table.

if ( $type == 'slugs') {

	?>
	<table class="widefat">
		<tr>
			<td><h4><?php echo esc_html( $section_list[ $section ] ) ?></h4>
				<p>
					<?php esc_html_e('This chart shows the execution time of all activated plugins and the current theme, in seconds and percentage, for the current profile.', 'code-profiler') ?>
					<br />
					<?php printf(
						esc_html__('Note that the execution time excludes any kind of website optimization (caching, CDN, PHP OPcache etc). Consult the %sFAQ%s for more details about that.', 'code-profiler'),
						'<a href="?page=code-profiler&cptab=faq">',
						'</a>'
					) ?>
				</p>
			</td>
		</tr>
	</table>
	<?php

} elseif ( $type == 'iostats') {

	?>
	<table class="widefat">
		<tr>
			<td><h4><?php echo esc_html( $section_list[ $section ] ) ?></h4>
				<p><?php esc_html_e('The chart displays all file I/O operations that occurred while your website was loading. The following operations are monitored by Code Profiler:', 'code-profiler') ?></p>
				<table class="widefat" style="border:none;box-shadow:none">
					<tr>
						<td style="width:50%">
							<code>cast</code>: <?php esc_html_e('Retrieve the underlaying resource (stream_select).', 'code-profiler') ?>
						</td>
						<td style="width:50%">
							<code>chgrp</code>: <?php esc_html_e('Change file group.', 'code-profiler') ?>
						</td>
					</tr>
					<tr>
						<td>
							<code>chmod</code>: <?php esc_html_e('Change file mode.', 'code-profiler') ?>
						</td>
						<td>
							<code>chown</code>: <?php esc_html_e('Change file owner.', 'code-profiler') ?>
						</td>
					</tr>
					<tr>
						<td>
							<code>close</code>: <?php esc_html_e('Close a resource (fclose).', 'code-profiler') ?>
						</td>
						<td>
							<code>closedir</code>: <?php esc_html_e('Close directory handle.', 'code-profiler') ?>
						</td>
					</tr>
					<tr>
						<td>
							<code>eof</code>: <?php esc_html_e('Test for end-of-file on a file pointer (feof).', 'code-profiler') ?>
						</td>
						<td>
							<code>flush</code>: <?php esc_html_e('Flush the output (fflush).', 'code-profiler') ?>
						</td>
					</tr>
					<tr>
						<td>
							<code>lock</code>: <?php esc_html_e('Advisory file locking (flock).', 'code-profiler') ?>
						</td>
						<td>
							<code>mkdir</code>: <?php esc_html_e('Create a directory.', 'code-profiler') ?>
						</td>
					</tr>
					<tr>
						<td>
							<code>open</code>: <?php esc_html_e('Open file (e.g., fopen, file_get_contents).', 'code-profiler') ?>
						</td>
						<td>
							<code>opendir</code>: <?php esc_html_e('Open directory handle.', 'code-profiler') ?>
						</td>
					</tr>
					<tr>
						<td>
							<code>read</code>: <?php esc_html_e('Read from stream (e.g., fread, fgets).', 'code-profiler') ?>
						</td>
						<td>
							<code>readdir</code>: <?php esc_html_e('Read entry from directory handle.', 'code-profiler') ?>
						</td>
					<tr>
					<tr>
						<td>
							<code>rename</code>: <?php esc_html_e('Rename a file or directory.', 'code-profiler') ?>
						</td>
						<td>
							<code>rewinddir</code>: <?php esc_html_e('Rewind directory handle.', 'code-profiler') ?>
						</td>
					</tr>
					<tr>
						<td>
							<code>rmdir</code>: <?php esc_html_e('Remove a directory.', 'code-profiler') ?>
						</td>
						<td>
							<code>seek</code>: <?php esc_html_e('Seek to specific location in a stream (fseek).', 'code-profiler') ?>
						</td>
					</tr>
					<tr>
						<td>
							<code>set_option</code>: <?php esc_html_e('Change stream options.', 'code-profiler') ?>
						</td><td>
							<code>stat</code>: <?php esc_html_e('Retrieve information about a file resource (fstat).', 'code-profiler') ?>
						</td>
					</tr>
					<tr>
						<td>
							<code>tell</code>: <?php esc_html_e('Retrieve the current position of a stream. (fseek).', 'code-profiler') ?>
						</td><td>
							<code>truncate</code>: <?php esc_html_e('Truncate stream (ftruncate).', 'code-profiler') ?>
						</td>
					</tr>
					<tr>
						<td>
							<code>touch</code>: <?php esc_html_e('Set access and modification time of file.', 'code-profiler') ?>
						</td>
						<td>
							<code>unlink</code>: <?php esc_html_e('Delete a file.', 'code-profiler') ?>
						</td>
					</tr>
					<tr>
						<td>
							<code>url_stat</code>: <?php esc_html_e('Retrieve information about a file. This method is called in response to all stat related functions, such as copy, filesize, filetype, is_dir, is_file, file_exists etc.', 'code-profiler') ?>
						</td>
						<td>
							<code>write</code>: <?php esc_html_e('Write to stream (fwrite).', 'code-profiler') ?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<?php

} elseif ( $type == 'diskio') {

	?>
	<table class="widefat">
		<tr>
			<td><h4><?php echo esc_html( $section_list[ $section ] ) ?></h4>
				<p><?php esc_html_e('The chart shows the total amount of disk I/O read and write data, in bytes, that occurred while your website was loading. It includes all activated plugins, the current theme and also WordPress.', 'code-profiler') ?></p>

			</td>
		</tr>
	</table>
	<?php

// Profiles list
} elseif ( $type == 'profiles_list') {

	?>
	<table class="widefat">
		<tr>
			<td><h4><?php esc_html_e('Profiles List', 'code-profiler') ?></h4>
				<p><?php esc_html_e('This page shows all saved profiles with sortable columns:', 'code-profiler') ?></p>
				<ul class="license-help-view">
					<li><?php esc_html_e('Profile: name of the saved profile.', 'code-profiler') ?></li>
					<li><?php esc_html_e('Date: creation date of the profile.', 'code-profiler') ?></li>
					<li><?php esc_html_e('Item: number of plugins + the current theme.', 'code-profiler') ?></li>
					<li><?php esc_html_e('Time: execution time in second.', 'code-profiler') ?></li>
					<li><?php esc_html_e('Memory: peak memory in megabytes.', 'code-profiler') ?></li>
					<li><?php esc_html_e('File I/O: sum of all file I/O operations.', 'code-profiler') ?></li>
					<li><?php esc_html_e('SQL: sum of database queries (WordPress, all plugins and the theme).', 'code-profiler') ?></li>
				</ul>
			</td>
		</tr>
	</table>
	<?php
}
// =====================================================================
// EOF
