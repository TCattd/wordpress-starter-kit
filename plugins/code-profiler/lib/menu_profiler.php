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
// Display Summary tab.

// Used to display profiler's error messages while running
echo '<div class="error notice is-dismissible" style="display:none" id="code-profiler-error"><p></p></div>';
echo code_profiler_display_tabs( 1 );
$error = 0;
if (! function_exists('register_tick_function') ) {
	$error = 'register_tick_function';
} elseif (! function_exists('stream_wrapper_unregister') ) {
	$error = 'stream_wrapper_unregister';
} elseif (! function_exists('stream_wrapper_register') ) {
	$error = 'stream_wrapper_register';
} elseif (! function_exists('stream_wrapper_restore') ) {
	$error = 'stream_wrapper_restore';
}
if (! empty( $error ) ) {
	printf( CODE_PROFILER_ERROR_NOTICE, sprintf(
		esc_html__('Your PHP configuration is missing the "%s" function. Code Profiler cannot run without it. Please contact your server administrator about this error.', 'code-profiler'), $error )
	);
}
if (! is_writable( CODE_PROFILER_UPLOAD_DIR ) ) {
	printf( CODE_PROFILER_ERROR_NOTICE, sprintf(
		esc_html__('The following folder is not writable: %s. Please change its permissions or ownership so that Code Profiler can write to it.', 'code-profiler'), CODE_PROFILER_UPLOAD_DIR )
	);
}

// Clear the log if it's too big
code_profiler_clearlog();

$home = home_url('/'); // Frontend
$site = site_url('/'); // Backend
// Get user login name
$current_user = wp_get_current_user();
// Profile's name
$profile = code_profiler_profile_name();

$cp_options = get_option('code-profiler');
// Memorized options
if ( empty( $cp_options['mem_where'] ) ) {
	$cp_options['mem_where'] = 'frontend';
}
if ( empty( $cp_options['mem_post'] ) ) {
	$cp_options['mem_post'] = '';
}
if ( empty( $cp_options['mem_user'] ) ) {
	$cp_options['mem_user'] = 'unauthenticated';
}
if ( $cp_options['mem_where'] == 'backend') {
	$cp_options['mem_user'] = 'authenticated';
}
if ( empty( $cp_options['mem_method'] ) ) {
	$cp_options['mem_method'] = 'get';
}
if ( empty( $cp_options['ua'] ) ) {
	$cp_options['ua'] = 'Firefox';
}
$cookies				= '';
$payload				= '';
$custom_headers	= '';
if ( isset( $cp_options['cookies'] ) ) {
	$cookies = trim( json_decode( $cp_options['cookies'] ) );
}
if ( isset( $cp_options['payload'] ) ) {
	$payload	= trim( json_decode( $cp_options['payload'] ) );
}
if ( isset( $cp_options['custom_headers'] ) ) {
	$custom_headers = trim( json_decode( $cp_options['custom_headers'] ) );
}
?>
<table class="form-table">
	<tr>
		<th scope="row"><?php esc_html_e('Page to profile', 'code-profiler') ?> <span class="code-profiler-tip" data-tip="<?php esc_attr_e('Select the page you want the profiler to analyze. It can be in the frontend of the site, in the admin backend or a custom URL.', 'code-profiler') ?>"></span></th>
		<td>
			<p><label><input type="radio" name="where" value="frontend" onclick="cpjs_front_or_backend(1);"<?php checked( $cp_options['mem_where'], 'frontend') ?> /> <?php esc_html_e('Website frontend', 'code-profiler') ?></label>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<label><input type="radio" name="where" value="custom" onclick="cpjs_front_or_backend(3);"<?php checked( $cp_options['mem_where'], 'custom') ?> /> <?php esc_html_e('Custom post/URL', 'code-profiler') ?></label>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<label><input type="radio" name="where" value="backend" onclick="cpjs_front_or_backend(2);"<?php checked( $cp_options['mem_where'], 'backend') ?> /> <?php esc_html_e('Admin backend', 'code-profiler') ?></label>
			</p>
			<br />
			<?php
			if ( $cp_options['mem_where'] == 'frontend') {
				echo '<p id="p-frontend">';
			} else {
				echo '<p id="p-frontend" style="display:none">';
			}
			?>
				<?php esc_html_e('Select a page:', 'code-profiler') ?>
				<select name="frontend" id="id-frontend">
					<?php echo code_profiler_fetch_pages( $home, $cp_options ); ?>
				</select>
			</p>
			<?php
			if ( $cp_options['mem_where'] == 'custom') {
				$value = $cp_options['mem_post'];
				echo '<p id="p-custom">';
			} else {
				$value = '';
				echo '<p id="p-custom" style="display:none">';
			}
			?>
				<input name="custom" id="id-custom" type="text" value="<?php echo esc_attr( $value ) ?>" size="70" placeholder="<?php
				echo esc_attr(
					sprintf(
						__('e.g., %s', 'code-profiler'),
						"{$home}foo/bar/"
					)
				) ?>" />
			</p>
			<?php
			if ( $cp_options['mem_where'] == 'backend') {
				echo '<p id="p-backend">';
			} else {
				echo '<p id="p-backend" style="display:none">';
			}
			if (! empty( $cp_options['mem_username'] ) ) {
				$username = $cp_options['mem_username'];
			} else {
				$username = $current_user->user_login;
			}
			?>
				<?php esc_html_e('Select a page:', 'code-profiler') ?>
				<select name="backend" id="id-backend"><?php echo code_profiler_fetch_admin_pages( $site, $cp_options ) ?></select>
			</p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e('Run profiler as', 'code-profiler') ?> <span class="code-profiler-tip" data-tip="<?php esc_attr_e('The profiler can access the requested page as an unauthenticated user (frontend only) or as an authenticated user (frontend and backend). You can enter the login name of any registered user, but make sure that the user has the required capability to access the page you are going to profile, otherwise Code Profiler will return an error if the user isn\'t allowed to access it.', 'code-profiler') ?>"></span></th>
		<td>
			<p><label><input type="radio" onclick="cpjs_authenticated(0);" name="user" value="unauthenticated" id="user-unauthenticated"<?php checked( $cp_options['mem_user'], 'unauthenticated'); disabled( $cp_options['mem_where'], 'backend'); ?> /> <?php esc_html_e('Unauthenticated user', 'code-profiler') ?></label></p>
			<p><label><input type="radio" onclick="cpjs_authenticated(1);" name="user" value="authenticated" id="user-authenticated"<?php checked( $cp_options['mem_user'], 'authenticated') ?> /> <?php esc_html_e('Authenticated user', 'code-profiler') ?></label></p>
			<p><label><?php esc_html_e('User:', 'code-profiler') ?> <input type="text" name="username" id="user-name" value ="<?php esc_attr_e( $username ) ?>"<?php disabled( $cp_options['mem_user'], 'unauthenticated') ?> /></label></p>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e('User agent', 'code-profiler') ?> <span class="code-profiler-tip" data-tip="<?php esc_attr_e('Some themes and plugins may execute different code depending on the type of device used to visit the website (e.g., a desktop computer, a mobile phone, a search engine bot etc). This option lets you change the User-Agent request header used by Code Profiler.', 'code-profiler') ?>"></span></th>
		<td>
			<select name="ua" id="ua-id">
			<?php
			foreach( CODE_PROFILER_UA as $types => $types_array ) {
				echo '<optgroup label="'. esc_attr( $types ) .'">';
				foreach( $types_array as $name => $value ) {
					echo '<option value="'. esc_attr( $name ) .'"'. selected( $cp_options['ua'], $name, false ) .'>'. esc_html( $name ) .'</option>';
				}
				echo '</optgroup>';
			}
			?>
			</select>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e('Name of the profile', 'code-profiler') ?> <span class="code-profiler-tip" data-tip="<?php esc_attr_e('Enter the name for this profile. It will be saved to the "Profiles List" page, with all other profiles.', 'code-profiler') ?>"></span></th>
		<td>
			<p><label><input type="text" class="regular-text" name="profile" maxlength="100" value="<?php echo esc_attr( $profile ) ?>" id="profile-name" /></label></p>
			<p class="description"><?php esc_html_e('Max 100 characters', 'code-profiler') ?></p>
		</td>
	</tr>
</table>
<?php
if (! empty( $cookies ) || ! empty( $custom_headers ) || $cp_options['mem_method'] == 'post') {
	echo '<div id="cp-advanced-settings">';
	$disabled_button = ' disabled';
} else {
	echo '<div id="cp-advanced-settings" style="display:none">';
	$disabled_button = '';
}
?>
	<table class="form-table">
		<tr>
			<th scope="row"><?php esc_html_e('HTTP Method', 'code-profiler') ?> <span class="code-profiler-tip" data-tip="<?php esc_attr_e('You can select which HTTP method will be used by the profiler: GET or POST. If you select POST, you can also send an optional payload.', 'code-profiler') ?>"></span></th>
			<td>
				<p><label><input onclick="cpjs_get_post(0);" type="radio" name="method" value="get" id="get-method"<?php checked( $cp_options['mem_method'], 'get') ?> /> <?php esc_html_e('GET (default)', 'code-profiler') ?></label></p>
				<p><label><input onclick="cpjs_get_post(1);" type="radio" name="method" value="post" id="post-method"<?php checked( $cp_options['mem_method'], 'post') ?> /> POST</label></p>
				<p><textarea name="post-value" id="post-value" class="regular-text code"<?php disabled( $cp_options['mem_method'], 'get') ?> maxlength="4000" rows="6"><?php echo esc_textarea( $payload ) ?></textarea></p>
				<p class="description"><?php printf( esc_html__('Optional POST payload in %s format, one item per line.', 'code-profiler'), '<code>name=value</code>') ?> <?php printf( esc_html__('%sView example%s', 'code-profiler'), '<a href="'. plugins_url('/static/help/http_method.png', dirname( __FILE__ ) ) .'" target="_blank" rel="noopener noreferrer">', '</a>') ?> </p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e('Cookie', 'code-profiler') ?> <span class="code-profiler-tip" data-tip="<?php esc_attr_e('Some websites may execute different code if the user has one or more specific cookies. You can use this field for that purpose.', 'code-profiler') ?>"></span></th>
			<td>
				<p><textarea name="cp-cookies" id="cp-cookies" class="regular-text code" maxlength="4000" rows="6"><?php echo esc_textarea( $cookies ) ?></textarea></p>
				<p class="description"><?php printf( esc_html__('Optional cookie in %s format, one item per line.', 'code-profiler'), '<code>name=value</code>') ?> <?php printf( esc_html__('%sView example%s', 'code-profiler'), '<a href="'. plugins_url('/static/help/cookies.png', dirname( __FILE__ ) ) .'" target="_blank" rel="noopener noreferrer">', '</a>') ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row" class="row-med"><?php esc_html_e('HTTP headers', 'code-profiler'); ?> <span class="code-profiler-tip" data-tip="<?php esc_attr_e('You can use this field to add custom HTTP headers or even override existing ones (e.g., host, user-agent, accept-language etc). HTTP header names are case-insensitive and Code Profiler will automatically convert them to lowercase. HTTP header values are case-sensitive and only ASCII printable characters are allowed.', 'code-profiler') ?>"></span></th>
			<td>
				<textarea autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" id="custom-headers" name="custom_headers" class="regular-text code" rows="6"><?php echo esc_textarea( $custom_headers ) ?></textarea>
				<p class="description"><?php printf( esc_html__('Optional HTTP header in %s format, one item per line.', 'code-profiler'), '<code>name: value</code>') ?> <?php printf( esc_html__('%sView example%s', 'code-profiler'), '<a href="'. plugins_url('/static/help/custom_headers.png', dirname( __FILE__ ) ) .'" target="_blank" rel="noopener noreferrer">', '</a>') ?></p>
			</td>
		</tr>
	</table>
</div>
<?php wp_nonce_field('start_profiler_nonce', 'cp_nonce', 0); ?>
<br />
<div>
	<input type="button" class="button-secondary" id="button-adv-settings" name="adv-settings" value="<?php esc_attr_e('Advanced Options', 'code-profiler') ?>" onclick="cpjs_show_adv_settings();"<?php echo $disabled_button ?> />
	&nbsp;&nbsp;&nbsp;
	<input type="button" class="button-primary" id="start-profile" name="start-profile" onClick="cpjs_start_profiler();" value="<?php esc_attr_e('Start Profiling', 'code-profiler') ?> »" title="<?php esc_attr_e('Click to start profiling your code.', 'code-profiler') ?>" />
</div>
<div id="cp-progress-div" style="display:none">
	<br />
	<div class="cp-progress-bar"><span id="cp-span-progress" style="width:0%"></span></div>
	<img style="vertical-align:middle;display:none" id="progress-gif" src="<?php echo plugins_url('/static/progress.gif', dirname (__FILE__ ) ) ?>" />&nbsp;&nbsp;<font id="progress-text" style="display:none"><?php esc_html_e('Starting the profiler...', 'code-profiler') ?></font>
</div>

<?php

// =====================================================================
// Fetch pages and posts.

function code_profiler_fetch_pages( $home, $cp_options ) {

	$posts = '';

	if ( empty( $cp_options['mem_post'] ) ) {
		$cp_options['mem_post'] = $home;
	}

	if ( $cp_options['mem_post'] == $home ) {
		$pages = sprintf(
			'<option value="%1$s" title="%1$s" selected>%2$s</option>',
			esc_attr( $home ),
			esc_html__('Homepage', 'code-profiler')
		);
	} else {
		$pages = sprintf(
			'<option value="%1$s" title="%1$s">%2$s</option>',
			esc_attr( $home ),
			esc_html__('Homepage', 'code-profiler')
		);
	}
	$args = [
		'posts_per_page' => -1,
		'post_type'      => ['page'],
		'post_status'    => 'publish',
		'orderby'        => 'post_name',
		'order'          => 'ASC'
	];
	$query = new WP_Query( $args );

	if ( $query ) {
		$items = $query->posts;
		foreach( $items as $item ) {
			if ( empty( $item->post_title ) ) {
				$item->post_title = __('Untitled');
			}
			$permalink = get_permalink( $item->ID );

			if ( $cp_options['mem_post'] == $permalink ) {
				$pages .= sprintf(
					'<option value="%1$s" title="%1$s" selected>%2$s</option>',
					esc_attr( $permalink ),
					esc_html( $item->post_title )
				);
			} else {
				$pages .= sprintf(
					'<option value="%1$s" title="%1$s">%2$s</option>',
					esc_attr( $permalink ),
					esc_html( $item->post_title )
				);
			}
		}
	}

	return sprintf('<optgroup label="%s">%s</optgroup>',
		esc_attr__('Pages', 'code-profiler'),
		$pages
	);
}

// =====================================================================
// Fetch admin pages (backend)

function code_profiler_fetch_admin_pages( $home, $cp_options ) {

	$backend = '';

	if ( empty( $cp_options['mem_post'] ) ) {
		$cp_options['mem_post'] = $home;
	}

	if (! empty( $GLOBALS[ 'menu' ] ) ) {
		foreach( $GLOBALS[ 'menu' ] as $menu => $value ) {
			if (! empty( $value[0] ) && substr( $value[2], -4 ) == '.php') {
				// E.g., "Comments", "Plugins" etc
				$value[0] = trim( preg_replace('/\s*<.+$/', '', $value[0] ) );

				if ( $cp_options['mem_post'] == "{$home}wp-admin/{$value[2]}" ) {
					$backend .= sprintf(
						'<option value="%1$s" title="%1$s" selected>%2$s</option>',
						esc_attr("{$home}wp-admin/{$value[2]}"),
						esc_html( $value[0] )
					);
				} else {
					$backend .= sprintf(
						'<option value="%1$s" title="%1$s">%2$s</option>',
						esc_attr("{$home}wp-admin/{$value[2]}"),
						esc_html( $value[0] )
					);
				}
			}
		}
	}
	if ( empty( $backend ) ) {
		$backend = sprintf(
			'<option value="%1$swp-admin/" title="%1$swp-admin/">%2$s</option>',
			esc_attr( $home ),
			esc_html__('Dashboard', 'code-profiler')
		);

	}

	return $backend;
}

// =====================================================================
// EOF
