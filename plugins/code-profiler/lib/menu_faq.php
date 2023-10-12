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
// Display FAQ tab.


echo code_profiler_display_tabs( 5 );

?>
<br />
<table class="widefat">
	<tr>
		<td style="border-bottom:1px solid #c3c4c7">
			<h4><?php esc_html_e('What is Code Profiler?', 'code-profiler') ?></h4>
			<?php esc_html_e('Code Profiler measures the performance of your plugins and themes at the PHP level, and helps you to quickly find any potential problem in your WordPress installation. It generates an extremely detailed and easy to read analysis in the form of charts and tables that shows not only which plugin or theme, but also which PHP script, class, method and function is slowing down your website. It displays many useful additional information such as file I/O operations and disk I/O usage as well.', 'code-profiler') ?>
			<br />
			<?php esc_html_e('It makes it very simple to locate any speed problem in your themes or plugins in order to solve it and speed up your website.', 'code-profiler') ?>
		</td>
	</tr>
	<tr>
		<td style="border-bottom:1px solid #c3c4c7">
			<h4><?php esc_html_e('Why does the execution time returned by Code Profiler seem higher than when I load my site in my browser?', 'code-profiler') ?></h4>
			<?php esc_html_e('Because the profiler needs to analyze your PHP code, it must exclude and disable any kind of website optimization: caching, CDN and PHP OPcache.', 'code-profiler') ?>
			<br />
			<?php esc_html_e('A caching sofware and a CDN service will prevent execution of your code and thus must be disabled by the profiler.', 'code-profiler') ?>
			<br />
			<?php esc_html_e('Regarding the opcode cache, PHP includes an opcode optimizer (since v7.0): after creating the first set of opcodes, it forwards it to the optimizer where it will get through 13 passes that will optimize it (peephole, jump, call, literal optimizations etc). That means that at the end of the day, the code in the opcache may have been optimized and thus may not match the original code found in your scripts. For that reason, it must be disabled. That problem occurs with other profilers too such as the xdebug extension, which cannot run well when the optimizer is turned on because it removes some virtual machine instructions needed by them.', 'code-profiler') ?>
		</td>
	</tr>
	<tr>
		<td style="border-bottom:1px solid #c3c4c7">
			<h4><?php esc_html_e('Why do I see numbers such as "3E-06" or "1.2E-05" after opening the CSV file with my spreadsheet editor?', 'code-profiler') ?></h4>
			<?php esc_html_e('Spreadsheet editors such as LibreOffice Calc and Microsoft Excel can use scientific exponential notation to display very small numbers, for instance, 0.000025 will become 2.5e-5. Select the whole column of numbers and, in the toolbar of your spreadsheet editor, click the button to increase the number of decimals to 6.', 'code-profiler') ?>
		</td>
	</tr>
	<tr>
		<td style="border-bottom:1px solid #c3c4c7">
			<h4><?php esc_html_e('Do I need to deactivate or uninstall Code Profiler when I\'m not using it ?', 'code-profiler') ?></h4>
			<?php esc_html_e('There\'s no need to deactivate Code Profiler when you don\'t use it, it has no performance impact on your site.', 'code-profiler') ?>
			<br />
			<?php esc_html_e('Because an update can affect the performance of your site, you should run it after every plugin or theme update.', 'code-profiler') ?>
		</td>
	</tr>
	<tr>
		<td style="border-bottom:1px solid #c3c4c7">
			<h4><?php esc_html_e('Is it possible to exclude a plugin during the profiling?', 'code-profiler') ?></h4>
			<?php
			printf(
				esc_html__('You can exclude one or more plugins during the profiling by using the %sFreesoul Deactivate Plugins%s plugin available in the WordPress.org repo:', 'code-profiler'),
				'<a href="https://wordpress.org/plugins/freesoul-deactivate-plugins/" target="_blank" rel="noopener noreferrer">',
				'</a>'
			);
			echo '<ul>'.
				'<ol>'. esc_html__('Download, install and activate the plugin.', 'code-profiler') .'</ol>'.
				'<ol>'. esc_html__('Go to its main page, click the gear icon and select "Code Profiler".', 'code-profiler') .'</ol>'.
				'<ol>'. esc_html__('Select the "According to this page settings" option, uncheck the plugin(s) you want to exclude during the profiling and save your settings.', 'code-profiler') .'</ol>'.
				'<ol>'. esc_html__('Go back to Code Profiler\'s page and run it.', 'code-profiler') .'</ol>'.
			'</ul>';
				?>
		</td>
	</tr>
	<tr>
		<td style="border-bottom:1px solid #c3c4c7">
			<h4><?php esc_html_e('Why do some scripts show an execution time of 0 second?', 'code-profiler') ?></h4>
			<?php esc_html_e('That can happen if your PHP version is 7.1 or 7.2. With those versions, Code Profiler can only use microseconds for its metrics, while with versions 7.3 and above it can use the system\'s high resolution time in nanoseconds. It can also happen if a PHP script has only a couple of lines of code, its execution time is too quick to be measured, hence it will show 0.', 'code-profiler') ?>
		</td>
	</tr>
	<tr>
		<td style="border-bottom:1px solid #c3c4c7"><a name="composerwarning"></a>
			<h4><?php esc_html_e('Why does Code Profiler warn me that I have multiple plugins using Composer?', 'code-profiler') ?></h4>
			<?php
			echo esc_html_e('Composer, a tool for dependency management in PHP, is included in many popular plugins and themes. It is used to autoload PHP classes.', 'code-profiler') .
			'<br />' .
			esc_html__('Code Profiler will inform you if two or more activated plugins use it because you will need to take it into consideration when reading and interpreting the results. Let\'s take an example:', 'code-profiler').
			'<br />' .
			esc_html__('Assuming you have four plugins, #1, #2, #3 and #4. Both plugins #1 and #4 include and require Composer. WordPress will start and load plugin #1, which will run an instance of Composer to load its classes. Immediately after, WordPress will load plugins #2 and #3. Then, it will load plugin #4, which too will need to load its classes. However, plugin #4 will not start a new instance of Composer but, instead, will rely on the one from plugin #1 to load its own classes.', 'code-profiler').
			'<br />' .
			esc_html__('As a result, the execution time of plugin #1 will increase (its instance of Composer is used to load classes for plugin #4 too), while the execution time of plugin #4 will decrease (it doesn\'t need to start a new instance of Composer).', 'code-profiler').
			' '.
			esc_html__('Therefore, if you have a dozen or more plugins using Composer, it is important to take into consideration that the execution time of plugin #1 may be much higher than other plugins.', 'code-profiler').
			'<br />' .
			esc_html__('Also, assuming you are a developer and just want to profile a plugin that you wrote and that includes Composer, you will need to disable any other plugin using Composer in order to get the most accurate results for your plugin only.', 'code-profiler');
			?>
			<br />&nbsp;
		</td>
	</tr>
	<tr>
		<td style="border-bottom:1px solid #c3c4c7">
			<h4><?php esc_html_e('Is Code Profiler multisite compatible?', 'code-profiler') ?></h4>
			<?php esc_html_e('Code Profiler is multisite compatible. Note however that for security reasons, only the superadmin can run it.', 'code-profiler') ?>
		</td>
	</tr>
	<tr>
		<td>
			<h4><?php esc_html_e('What is your Privacy Policy?', 'code-profiler') ?></h4>
			<?php esc_html_e('Code Profiler does not collect any private data from you or your visitors. It does not use cookies either. Your website can run Code Profiler and be compliant with privacy laws such as the General Data Protection Regulation (GDPR) or the California Consumer Privacy Act (CCPA).', 'code-profiler') ?>
		</td>
	</tr>
</table>
<?php

// =====================================================================
// EOF
