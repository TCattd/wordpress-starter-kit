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
// Display the profiler's support page.

if ( defined('CODE_PROFILER_TEXTAREA_HEIGHT') ) {
	$th = (int) CODE_PROFILER_TEXTAREA_HEIGHT;
} else {
	$th = '450';
}

echo code_profiler_display_tabs( 6 );
?>
<br />
<p><?php esc_html_e('When contacting the support for help, please copy and paste the system information report below in your ticket:', 'code-profiler') ?></p>
<table class="form-table">
	<tr>
		<td>
			<textarea dir="auto" id="cp-troubleshooter" class="large-text code" style="height:<?php echo $th; ?>px;" wrap="off" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"><?php
			require_once __DIR__ .'/class-troubleshooter.php';
			new CP_Troubleshooter();
			?></textarea>
			<p><input type="button" onClick="cpjs_copy_textarea('cp-troubleshooter')" class="button-secondary" value="<?php esc_attr_e('Copy text', 'code-profiler') ?>" /></p>
		</td>
	</tr>
</table>
<?php

// =====================================================================
// EOF
