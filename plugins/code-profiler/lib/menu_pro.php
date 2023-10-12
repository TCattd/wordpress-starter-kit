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
// Display Pro section.

?><br /><br />
<div class="wrap about-wrap full-width-layout">

	<h3>Code Profiler Pro: <?php esc_html_e('WordPress website performance profiling made easy.', 'code-profiler') ?></h3>

	<p class="about-text">
		<?php esc_html_e('Code Profiler Pro makes it super easy to locate any performance problem in your themes or plugins by analyzing their scripts, classes & methods, functions as well as monitoring all file I/O operations and remote connections.', 'code-profiler') ?>
	</p>

	<hr />

	<div class="feature-section is-wide has-2-columns">
		<div class="column is-vertically-aligned-center">
			<h3><?php esc_html_e('Scripts performance', 'code-profiler') ?></h3>
			<p><?php esc_html_e('That section shows the time it took for each PHP script to execute its code while the website was loading. The "Time" column shows their respective processing time in seconds and, with a green horizontal bar, the percentage it represents. ', 'code-profiler') ?></p>
		</div>
		<div class="column">
			<a href="<?php echo esc_url( plugins_url( '/static/preview/scripts.png', dirname( __FILE__ ) ) ) ?>" class="thickbox"><img src="<?php echo esc_url( plugins_url( '/static/preview/scripts.png', dirname( __FILE__ ) ) ) ?>" class="cppro" title="<?php esc_html_e('Click to enlarge image.', 'code-profiler') ?>" /></a>
			<p class="description aligncenter"><?php esc_html_e('Click to enlarge image.', 'code-profiler') ?></p>
		</div>
	</div>

	<hr />

	<div class="feature-section is-wide has-2-columns">
		<div class="column">
			<a href="<?php echo esc_url( plugins_url( '/static/preview/functions.png', dirname( __FILE__ ) ) ) ?>" class="thickbox"><img src="<?php echo esc_url( plugins_url( 'static/preview/functions.png', dirname( __FILE__ ) ) ) ?>" class="cppro" title="<?php esc_html_e('Click to enlarge image.', 'code-profiler') ?>" /></a>
			<br /><br /><br />
			<a href="<?php echo esc_url( plugins_url( '/static/preview/function-caller.png', dirname( __FILE__ ) ) ) ?>" class="thickbox"><img src="<?php echo esc_url( plugins_url( '/static/preview/function-caller.png', dirname( __FILE__ ) ) ) ?>" class="cppro" title="<?php esc_html_e('Click to enlarge image.', 'code-profiler') ?>" /></a>
			<br /><br /><br />
			<a href="<?php echo esc_url( plugins_url( '/static/preview/backtrace.png', dirname( __FILE__ ) ) ) ?>" class="thickbox"><img src="<?php echo esc_url( plugins_url( '/static/preview/backtrace.png', dirname( __FILE__ ) ) ) ?>" class="cppro" title="<?php esc_html_e('Click to enlarge image.', 'code-profiler') ?>" /></a>
			<p class="description aligncenter"><?php esc_html_e('Click to enlarge image.', 'code-profiler') ?></p>
		</div>
		<div class="column is-vertically-aligned-center">
			<h3><?php esc_html_e('Methods & functions performance', 'code-profiler') ?></h3>
			<p><?php esc_html_e('That section shows the time it took for each class/method or function to execute its code while the website was loading. The "Time" column shows their respective processing time in seconds and, with a green horizontal bar, the percentage it represents. The "Caller" column shows the sum of all functions that called it.', 'code-profiler') ?></p>
			<p><?php esc_html_e('The two action links below each item let you view the corresponding function and the list of caller functions.', 'code-profiler') ?></p>
			<p><?php esc_html_e('It is also possible to generate a PHP backtrace for each caller function by enabling that feature in the "settings" page.', 'code-profiler') ?></p>
		</div>
	</div>

	<hr />

	<div class="feature-section is-wide has-2-columns">
		<div class="column is-vertically-aligned-center">
			<h3><?php esc_html_e('Database queries performance', 'code-profiler') ?></h3>
			<p><?php esc_html_e('That section shows the time it took to process each database query.', 'code-profiler') ?>
			<br /><?php esc_html_e('The "Time" column shows their respective processing time in seconds and, with a green horizontal bar, the percentage it represents. The "Order" column lets you sort them by execution order.', 'code-profiler') ?></p>
			<p><?php esc_html_e('The "View backtrace" action link below each item lets you view a list of the scripts and function calls that lead to the query, sorted by execution order.') ?></p>
		</div>
		<div class="column">
			<a href="<?php echo esc_url( plugins_url( '/static/preview/queries.png', dirname( __FILE__ ) ) ) ?>" class="thickbox"><img src="<?php echo esc_url( plugins_url( '/static/preview/queries.png', dirname( __FILE__ ) ) ) ?>" class="cppro" title="<?php esc_html_e('Click to enlarge image.', 'code-profiler') ?>" /></a>
			<p class="description aligncenter"><?php esc_html_e('Click to enlarge image.', 'code-profiler') ?></p>
		</div>
	</div>

	<hr />

	<div class="feature-section is-wide has-2-columns">
		<div class="column">
			<a href="<?php echo esc_url( plugins_url( '/static/preview/connections.png', dirname( __FILE__ ) ) ) ?>" class="thickbox"><img src="<?php echo esc_url( plugins_url( '/static/preview/connections.png', dirname( __FILE__ ) ) ) ?>" class="cppro" title="<?php esc_html_e('Click to enlarge image.', 'code-profiler') ?>" /></a>
			<p class="description aligncenter"><?php esc_html_e('Click to enlarge image.', 'code-profiler') ?></p>
		</div>
		<div class="column is-vertically-aligned-center">
			<h3><?php esc_html_e('Remote connections', 'code-profiler') ?></h3>
			<p><?php esc_html_e('That section shows all HTTP connections originating from your WordPress website.', 'code-profiler') ?></p>
			<p><?php esc_html_e('The "Code" column shows the HTTP response status code and, if you move your mouse over a value, it will display the response message.', 'code-profiler') ?></p>
			<p><?php esc_html_e('The "View backtrace" action link below each item lets you view a clickable list of the scripts and function calls that lead to the HTTP request.', 'code-profiler') ?></p>
		</div>
	</div>

	<hr />

	<div class="feature-section is-wide has-2-columns">
		<div class="column is-vertically-aligned-center">
			<h3><?php esc_html_e('File I/O list', 'code-profiler') ?></h3>
			<p><?php esc_html_e('That section shows all files and directories with their respective I/O operations that occurred while the website was loading. The default sorting order is based on the control flow, i.e., the order in which the PHP code was executed while the website was loading. The following 10 operations are monitored by Code Profiler:', 'code-profiler') ?>
			<br /><?php esc_html_e('Open file (read & write), delete file, rename file or directory, remove directory, make directory, open directory, change mode, change group, change owner and change timestamps.', 'code-profiler') ?></p>
			<p><?php esc_html_e('Every files will be displayed. For instance, if a plugin or theme created a temporary file or wrote to a log, it would appear in the list too.', 'code-profiler') ?></p>
		</div>
		<div class="column">
			<a href="<?php echo esc_url( plugins_url( '/static/preview/iolist.png', dirname( __FILE__ ) ) ) ?>" class="thickbox"><img src="<?php echo esc_url( plugins_url( '/static/preview/iolist.png', dirname( __FILE__ ) ) ) ?>" class="cppro" title="<?php esc_html_e('Click to enlarge image.', 'code-profiler') ?>" /></a>
			<p class="description aligncenter"><?php esc_html_e('Click to enlarge image.', 'code-profiler') ?></p>
		</div>

	</div>

	<hr />

	<div class="feature-section is-wide has-2-columns">
		<div class="column">
			<a href="<?php echo esc_url( plugins_url( 'static/preview/plugins-csv.png', dirname( __FILE__ ) ) ) ?>" class="thickbox"><img src="<?php echo esc_url( plugins_url( '/static/preview/plugins-csv.png', dirname( __FILE__ ) ) ) ?>" class="cppro" title="<?php esc_html_e('Click to enlarge image.', 'code-profiler') ?>" /></a>
			<br /><br /><br />
			<a href="<?php echo esc_url( plugins_url( '/static/preview/functions-csv.png', dirname( __FILE__ ) ) ) ?>" class="thickbox"><img src="<?php echo esc_url( plugins_url( '/static/preview/functions-csv.png', dirname( __FILE__ ) ) ) ?>" class="cppro" title="<?php esc_html_e('Click to enlarge image.', 'code-profiler') ?>" /></a>
			<p class="description aligncenter"><?php esc_html_e('Click to enlarge image.', 'code-profiler') ?></p>
		</div>
		<div class="column is-vertically-aligned-center">
			<h3><?php esc_html_e('Create your own graphs and reports effortlessly.', 'code-profiler') ?></h3>
			<p><?php esc_html_e('All charts and tables can be exported as a CSV file (comma-separated values) that you can open with your favorite spreadsheet editor in order to create graphs and reports.', 'code-profiler') ?>
		</div>

	</div>

	<h3><b><a href="https://code-profiler.com/" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Learn more about Code Profiler Pro.', 'code-profiler') ?></a></b></h3>

	<hr />
</div>
<?php
$type = '';
// =====================================================================
// EOF
