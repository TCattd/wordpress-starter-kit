<?php

namespace dologin;

defined('WPINC') || exit;

$__gui = $this->cls('GUI');

$current_user_phone = $this->cls('SMS')->current_user_phone();
$current_user_2fa = $this->cls('TwoFA')->current_status();

?>
<form method="post" action="<?php menu_page_url('dologin'); ?>" class="dologin-relative">
	<?php wp_nonce_field('dologin'); ?>

	<h3 class="dologin-title-short"><?php echo __('Limit Login Attempt Settings', 'dologin'); ?></h3>

	<table class="wp-list-table striped dologin-table">
		<tbody>
			<tr>
				<th><?php echo __('Lockout', 'dologin'); ?></th>
				<td>
					<p><?php $__gui->build_input('max_retries', 'dologin-input-short2'); ?> <?php echo __('Allowed retries', 'dologin'); ?></p>
					<p><?php $__gui->build_input('duration', 'dologin-input-short2'); ?> <?php echo __('minutes lockout', 'dologin'); ?></p>
					<div class="dologin-desc">
						<?php echo sprintf(__('If hit %1$s maximum retries in %2$s minutes, the login attempt from that IP will be temporarily disabled.', 'dologin'), '<code>' . Conf::val('max_retries') . '</code>', '<code>' . Conf::val('duration') . '</code>'); ?>
					</div>
				</td>
			</tr>
		</tbody>
	</table>

	<h3 class="dologin-title-short"><?php echo __('2FA Settings', 'dologin'); ?></h3>

	<table class="wp-list-table striped dologin-table">
		<tbody>
			<tr>
				<th><?php echo __('Two-factor Authentication', 'dologin'); ?></th>
				<td>
					<?php $__gui->build_switch('2fa'); ?>
					<div class="dologin-desc">
						<?php echo __('Verify 2FA code for each login attempt.', 'dologin'); ?>
						<?php echo __('Users need to finish 2FA validation in their profile.', 'dologin'); ?>
						<br /><?php echo sprintf(__('Can use any 2FA app, e.g. %s', 'dologin'), '<code>Google Authenticator</code>'); ?>
					</div>
				</td>
			</tr>

			<tr>
				<th><?php echo __('Force 2FA Auth Validation', 'dologin'); ?></th>
				<td>
					<?php $__gui->build_switch('2fa_force'); ?>
					<div class="dologin-desc">
						<?php echo __('If enabled this, any user without 2FA setup in profile will not be able to login.', 'dologin'); ?>
						<a href="profile.php"><?php echo __('Click here to manage your 2FA secret', 'dologin'); ?></a>
						<?php if (!$current_user_2fa && Conf::val('2fa') && Conf::val('2fa_force')) : ?>
							<div class="dologin-warning-h3">
								<?php echo __('You need to setup your 2FA before enabling this setting to avoid yourself being blocked from next time login.', 'dologin'); ?>
							</div>
						<?php endif; ?>
					</div>
				</td>
			</tr>
		</tbody>
	</table>

	<h3 class="dologin-hide"><?php echo __('Short Code Auth Settings', 'dologin'); ?></h3>

	<table class="dologin-hide wp-list-table striped dologin-table">
		<tbody>
			<tr>
				<th><?php echo __('Two Step SMS Auth', 'dologin'); ?></th>
				<td>
					<?php $__gui->build_switch('sms'); ?>
					<div class="dologin-desc">
						<?php echo __('Verify text code for each login attempt.', 'dologin'); ?>
						<?php echo __('Users need to setup the Dologin Phone number in their profile.', 'dologin'); ?>
						<?php echo __('The phone number need to specify the coutry calling codes.', 'dologin'); ?>
						<?php echo sprintf(__('Text message is sent by API from %s.', 'dologin'), '<a href="https://www.doapi.us" target="_blank">DoAPI.us</a>'); ?>
					</div>
				</td>
			</tr>

			<tr>
				<th><?php echo __('Force SMS Auth Validation', 'dologin'); ?></th>
				<td>
					<?php $__gui->build_switch('sms_force'); ?>
					<div class="dologin-desc">
						<?php echo __('If enabled this, any user without phone set in profile will not be able to login.', 'dologin'); ?>
						<a href="profile.php"><?php echo __('Click here to set your Dologin Security phone number', 'dologin'); ?></a>
						<?php if (!$current_user_phone && Conf::val('sms') && Conf::val('sms_force')) : ?>
							<?php echo '<div class="dologin-warning-h3">' . __('You need to setup your Dologin Phone number before enabling this setting to avoid yourself being blocked from next time login.', 'dologin') . '</div>'; ?>
						<?php else : ?>
					</div>
					<div class="dologin-desc">
						<button type="button" class="button button-primary" id="dologin_test_sms"><?php echo __('Test SMS message', 'dologin'); ?></button>
						<span id='dologin_test_sms_res'></span>
						<?php echo __('This will send a test text message to your phone number.', 'dologin'); ?>
					<?php endif; ?>
					</div>
				</td>
			</tr>
		</tbody>
	</table>

	<h3 class="dologin-title-short"><?php echo __('reCAPTCHA Settings', 'dologin'); ?></h3>

	<table class="wp-list-table striped dologin-table">
		<tbody>
			<tr>
				<th><?php echo __('Google reCAPTCHA', 'dologin'); ?></th>
				<td>
					<?php $__gui->build_switch('gg'); ?>
					<div class="dologin-desc">
						<?php echo sprintf(__('This will enable reCAPTCHA on %s page.', 'dologin'), __('Login')); ?>
					</div>
				</td>
			</tr>

			<tr>
				<th><?php echo __('Google reCAPTCHA on Register Page', 'dologin'); ?></th>
				<td>
					<?php $__gui->build_switch('recapt_register'); ?>
					<div class="dologin-desc">
						<?php echo sprintf(__('This will enable reCAPTCHA on %s page.', 'dologin'), __('Register')); ?>
					</div>
				</td>
			</tr>

			<!-- https://core.trac.wordpress.org/ticket/49521 -->
			<tr>
				<th><?php echo __('Google reCAPTCHA on Lost Password Page', 'dologin'); ?></th>
				<td>
					<?php $__gui->build_switch('recapt_forget'); ?>
					<div class="dologin-desc">
						<?php echo sprintf(__('This will enable reCAPTCHA on %s page.', 'dologin'), __('Lost Password')); ?>
					</div>
				</td>
			</tr>

			<tr>
				<th><?php echo __('Google reCAPTCHA Keys', 'dologin'); ?></th>
				<td>
					<div class="dologin-row-flex">
						<div style="margin-right: 50px;">
							<p><label>
									<span class="dologin_text_label_prefix"><?php echo __('Site Key', 'dologin'); ?>:</span>
									<?php $__gui->build_input('gg_pub_key', ''); ?>
								</label></p>
							<p><label>
									<span class="dologin_text_label_prefix"><?php echo __('Secret Key', 'dologin'); ?>:</span>
									<?php $__gui->build_input('gg_priv_key', ''); ?>
								</label></p>
						</div>
						<div>
							<?php
							if (Conf::val('gg') || (Conf::val('gg_pub_key') && Conf::val('gg_priv_key'))) {
								$this->cls('Captcha')->show();
							}
							?>
						</div>
					</div>

					<div class="dologin-desc">
						<?php echo sprintf(__('<a %s>Click here</a> to generate keys from Google reCAPTCHA.', 'dologin'), 'href="https://www.google.com/recaptcha/admin#list" target="_blank"'); ?>
						<?php echo __('Note: v2 supported only.', 'dologin'); ?>
					</div>
				</td>
			</tr>
		</tbody>
	</table>

	<h3 class="dologin-title-short"><?php echo __('General Settings', 'dologin'); ?></h3>

	<table class="wp-list-table striped dologin-table">
		<tbody>
			<tr>
				<th><?php echo __('Whitelist', 'dologin'); ?></th>
				<td>
					<div class="field-col">
						<?php $__gui->build_textarea('whitelist'); ?>
					</div>
					<div class="field-col field-col-desc">
						<div class="dologin-desc">
							<?php echo __('Format', 'dologin'); ?>: <code>prefix1:value1, prefix2:value2</code>.
							<?php echo __('Both prefix and value are case insensitive.', 'dologin'); ?>
							<?php echo __('Spaces around comma/colon are allowed.', 'dologin'); ?>
							<?php echo __('One rule set per line.', 'dologin'); ?>
						</div>
						<div class="dologin-desc">
							<?php echo __('Prefix list', 'dologin'); ?>: <code>ip</code>, <code><?php echo implode('</code>, <code>', IP::$PREFIX_SET); ?></code>.
						</div>
						<div class="dologin-desc"><?php echo __('IP prefix with colon is optional. IP value support wildcard (*).', 'dologin'); ?></div>
						<div class="dologin-desc">
							<?php echo sprintf(__('Use %s to append comments in the end of each line.', 'dologin'), '<code>#</code>'); ?>
							<?php echo sprintf(__('Use %s to exclude one value.', 'dologin'), '<code>!:</code>'); ?>
						</div>
						<div class="dologin-desc dologin-row-flex">
							<div style="margin-right: 10px;">
								<button type="button" class="button button-primary" id="dologin_get_ip" title="<?php echo sprintf(__('This will send a request to %s to get your public Geolocation info.', 'dologin'), 'https://doapi.us'); ?>"><?php echo __('Check My Geolocation Data', 'dologin'); ?></button>
							</div>
							<code id="dologin_mygeolocation">-</code>
						</div>
					</div>
				</td>
			</tr>

			<tr>
				<th><?php echo __('Blacklist', 'dologin'); ?></th>
				<td>
					<div class="field-col">
						<?php $__gui->build_textarea('blacklist'); ?>
					</div>
					<div class="field-col field-col-desc">
						<div class="dologin-desc">
							<?php echo sprintf(__('Same format as %s', 'dologin'), '<strong>' . __('Whitelist', 'dologin') . '</strong>'); ?>
						</div>
						<div class="dologin-desc"><?php echo __('Example', 'dologin'); ?> 1) <code>ip:1.2.3.*</code></div>
						<div class="dologin-desc"><?php echo __('Example', 'dologin'); ?> 2) <code>42.20.*.*, continent_code: NA</code> (<?php echo __('Dropped optional prefix', 'dologin'); ?> <code>ip:</code>)</div>
						<div class="dologin-desc"><?php echo __('Example', 'dologin'); ?> 3) <code>continent: North America, country_code: US, subdivision_code: NY</code></div>
						<div class="dologin-desc"><?php echo __('Example', 'dologin'); ?> 4) <code>subdivision_code: NY, postal: 10001</code></div>
						<div class="dologin-desc"><?php echo __('Example', 'dologin'); ?> 5) <code>ip: 1.2.3.* # This is my IP</code></div>
						<div class="dologin-desc"><?php echo __('Example', 'dologin'); ?> 6) <code>country_code: US, ip!: 1.2.3.4</code> (<?php echo __('Match all visitors from US except the IP 1.2.3.4', 'dologin'); ?> )</div>
					</div>
				</td>
			</tr>

			<tr>
				<th><?php echo __('GDPR Compliance', 'dologin'); ?></th>
				<td>
					<?php $__gui->build_switch('gdpr'); ?>
					<div class="dologin-desc">
						<?php echo __('With this feature turned on, all logged IPs get obfuscated (md5-hashed).', 'dologin'); ?>
					</div>
				</td>
			</tr>

			<tr>
				<th><?php echo __('Auto Upgrade', 'dologin'); ?></th>
				<td>
					<?php $__gui->build_switch('auto_upgrade'); ?>
					<div class="dologin-desc">
						<?php echo __('Enable this option to get the latest features at the first moment.', 'dologin'); ?>
					</div>
				</td>
			</tr>

		</tbody>
	</table>

	<div class='dologin-top20'></div>

	<?php submit_button(__('Save Changes', 'dologin'), 'primary', 'dologin-submit'); ?>
	<?php submit_button(__('Save Changes', 'dologin'), 'primary dologin-float-submit', 'dologin-float-submit'); ?>

</form>