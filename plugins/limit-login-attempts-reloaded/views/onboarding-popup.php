<?php

use LLAR\Core\Config;

if( !defined( 'ABSPATH' ) ) exit();

/**
 * @var $this LLAR\Core\LimitLoginAttempts
 */

$admin_email = ( !is_multisite() ) ? get_option( 'admin_email' ) : get_site_option( 'admin_email' );
$onboarding_popup_shown = Config::get( 'onboarding_popup_shown' );
$setup_code = Config::get( 'app_setup_code' );

if( $onboarding_popup_shown || !empty( $setup_code ) ) return;

ob_start(); ?>
<div class="llar-onboarding-popup-content">
	<div class="title"><?php _e( 'Please tell us where Limit Login Attempts Reloaded should send security notifications for your website?', 'limit-login-attempts-reloaded' ); ?></div>
	<div class="field-wrap">
		<input type="email" id="llar-subscribe-email" placeholder="you@example.com" value="<?php esc_attr_e( $admin_email ); ?>">
		<div class="field-desc"><?php _e( 'We do not use this email address for any other purpose unless you opt-in to receive other mailings. You can turn off alerts in the settings.', 'limit-login-attempts-reloaded' ); ?></div>
	</div>
	<div class="security-alerts-options">
		<div class="info"><?php _e( 'Would you also like to join our email newsletter to receive plugin updates, WordPress security news, and other relevant content?', 'limit-login-attempts-reloaded' ); ?></div>
		<div class="buttons">
			<span data-val="yes"><?php _e( 'Yes', 'limit-login-attempts-reloaded' ); ?></span>
			<span data-val="no"><?php _e( 'No', 'limit-login-attempts-reloaded' ); ?></span>
		</div>
	</div>
</div>
<?php
$popup_complete_install_content = ob_get_clean();
?>
<?php
ob_start(); ?>
<div class="llar-onboarding-popup-content llar-app-setup-popup">
    <div class="title"><?php _e( 'Activate Premium', 'limit-login-attempts-reloaded' ); ?></div>
    <div class="desc"><?php _e( 'Enter your setup code to enable cloud protection. This will provide the highest level of security and performance during brute force attacks.', 'limit-login-attempts-reloaded' ); ?></div>
    <div class="field-wrap">
        <div class="field">
            <input type="text" id="llar-setup-code-field" placeholder="<?php esc_attr_e( 'Enter Setup Code', 'limit-login-attempts-reloaded' ); ?>">
            <span class="error"></span>
        </div>
        <div class="button-col">
            <button class="button button-primary" id="llar-app-install-btn">
                <?php _e( 'Install', 'limit-login-attempts-reloaded' ); ?>
                <span class="preloader-wrapper"><span class="spinner llar-app-ajax-spinner"></span></span>
            </button>
        </div>
    </div>
    <div class="divider-line"><span><?php _e( 'Or', 'limit-login-attempts-reloaded' ); ?></span></div>
    <div class="bottom-buttons">
        <div class="text"><?php _e( 'If you don\'t have one, you can purchase one now.', 'limit-login-attempts-reloaded' ); ?></div>
        <div class="buttons">
            <a href="https://checkout.limitloginattempts.com/plan?from=plugin-welcome" target="_blank"
               class="button button-primary size-medium"><?php _e( 'Upgrade To Premium', 'limit-login-attempts-reloaded' ); ?></a>
            <a href="https://www.limitloginattempts.com/features/?from=plugin-welcome" target="_blank"
               class="button button-secondary"><?php _e( 'Learn More', 'limit-login-attempts-reloaded' ); ?></a>
            <button class="button-link" id="llar-popup-no-thanks-btn"><?php _e( 'No thanks', 'limit-login-attempts-reloaded' ); ?></button>
        </div>
    </div>
</div>
<?php
$popup_app_setup_content = ob_get_clean();
?>
<script>
    (function($){

        $(document).ready(function(){
            const app_setup_popup = $.confirm({
                title: '<?php _e( 'Please Complete Limit Login Attempts Reloaded Installation', 'limit-login-attempts-reloaded' ) ?>',
                content: `<?php echo trim( $popup_app_setup_content ); ?>`,
                type: 'default',
                typeAnimated: true,
                draggable: false,
                boxWidth: '40%',
                bgOpacity: 0.9,
                useBootstrap: false,
                lazyOpen: true,
                buttons: false,
                closeIcon: true
            });

            $.confirm({
                title: '<?php _e( 'Complete Limit Login Attempts Reloaded Installation', 'limit-login-attempts-reloaded' ) ?>',
                content: `<?php echo trim( $popup_complete_install_content ); ?>`,
                type: 'default',
                typeAnimated: true,
                draggable: false,
                boxWidth: '40%',
                bgOpacity: 0.9,
                useBootstrap: false,
                closeIcon: true,
                onClose: function() {
                    $.post(ajaxurl, {
                        action: 'dismiss_onboarding_popup',
                        sec: '<?php echo esc_js( wp_create_nonce( "llar-action" ) ); ?>'
                    }, function(){});
                },
				buttons: {
                    continue: {
                        text: '<?php _e( 'Continue', 'limit-login-attempts-reloaded' ) ?>',
                        btnClass: 'btn-blue',
                        keys: ['enter'],
                        action: function(){

                            app_setup_popup.open();

                            $.post(ajaxurl, {
                                action: 'subscribe_email',
                                email: $('body').find('#llar-subscribe-email').val(),
                                is_subscribe_yes: !!$('body').find('.security-alerts-options .buttons span[data-val="yes"].llar-act').length,
                                sec: '<?php echo esc_js( wp_create_nonce( "llar-action" ) ); ?>'
                            }, function(){});
                        }
                    }
				}
            });


            $('body').on('click', '.security-alerts-options .buttons span', function() {
                const $this = $(this);
                $this.parent().find('span').removeClass('llar-act');
                $this.addClass('llar-act');
            });

            $('body').on('click', '#llar-app-install-btn', function(e) {
                e.preventDefault();

                const $this = $(this);
                const $error = $this.closest('.field-wrap').find('.error');

                if($this.hasClass('button-disabled')) {
                    return;
                }

                const setup_code = $this.closest('.field-wrap').find('input').val();

                $error.text('').hide();
                $this.addClass('button-disabled');

                $.post(ajaxurl, {
                    action: 'app_setup',
                    code: setup_code,
                    sec: '<?php echo esc_js( wp_create_nonce( "llar-action" ) ); ?>'
                }, function(response){

                    if(response.success) {
                        setTimeout(function(){

                            window.location = window.location + '&llar-cloud-activated';

                        }, 500);
                    }

                    if(!response.success && response.data.msg) {

                        $error.text(response.data.msg).show();
                    }

                    $this.removeClass('button-disabled');

                });

            });

            $('body').on('click', '#llar-popup-no-thanks-btn', function(e) {
                e.preventDefault();

                app_setup_popup.close();
            });
        })

    })(jQuery)
</script>