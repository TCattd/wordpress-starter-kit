<?php

use LLAR\Core\Config;
use LLAR\Core\Helpers;
use LLAR\Core\LimitLoginAttempts;

if( !defined( 'ABSPATH' ) ) exit();

$active_tab = "dashboard";
$active_app = Config::get( 'active_app' );

if( !empty($_GET["tab"]) && in_array( $_GET["tab"], array( 'logs-local', 'logs-custom', 'settings', 'debug', 'premium', 'help' ) ) ) {

	if( !LimitLoginAttempts::$cloud_app && $_GET['tab'] === 'logs-custom' ) {

		$active_tab = 'logs-local';
	} else {

		$active_tab = sanitize_text_field( $_GET["tab"] );
	}
}

$auto_update_choice = Config::get( 'auto_update_choice' );
?>

<?php if( $active_app === 'local' ) : ?>
<div id="llar-header-upgrade-message">
    <p><span class="dashicons dashicons-info"></span>
        <?php echo sprintf( __( 'Thank you for using the free version of <b>Limit Login Attempts Reloaded</b>. <a href="%s" target="_blank">Upgrade to our cloud app</a> for enhanced protection, visual metrics & premium support.', 'limit-login-attempts-reloaded' ),
            'https://www.limitloginattempts.com/info.php?from=plugin-'.( ( substr( $active_tab, 0, 4 ) === 'logs' ) ? 'logs' : $active_tab )
        ); ?></p>
</div>
<?php endif; ?>

<?php if( ( $auto_update_choice || $auto_update_choice === null ) && !Helpers::is_auto_update_enabled() ) : ?>
<div class="notice notice-error llar-auto-update-notice">
    <p>
        <?php _e( 'Do you want Limit Login Attempts Reloaded to provide the latest version automatically?', 'limit-login-attempts-reloaded' ); ?>
        <a href="#" class="auto-enable-update-option" data-val="yes"><?php _e( 'Yes, enable auto-update', 'limit-login-attempts-reloaded' ); ?></a> | <a href="#" class="auto-enable-update-option" data-val="no"><?php _e( 'No thanks', 'limit-login-attempts-reloaded' ); ?></a>
    </p>
</div>
<?php endif; ?>

<div class="wrap limit-login-page-settings">
    <h2><?php echo __( 'Limit Login Attempts Reloaded', 'limit-login-attempts-reloaded' ); ?></h2>

    <h2 class="nav-tab-wrapper">
        <a href="<?php echo $this->get_options_page_uri('dashboard'); ?>" class="nav-tab <?php if($active_tab == 'dashboard'){echo 'nav-tab-active';} ?> "><?php _e('Dashboard', 'limit-login-attempts-reloaded'); ?></a>
        <a href="<?php echo $this->get_options_page_uri('settings'); ?>" class="nav-tab <?php if($active_tab == 'settings'){echo 'nav-tab-active';} ?> "><?php _e('Settings', 'limit-login-attempts-reloaded'); ?></a>

        <?php if( $active_app === 'custom' ) : ?>
            <a href="<?php echo $this->get_options_page_uri('logs-custom'); ?>" class="nav-tab <?php if($active_tab == 'logs-custom'){echo 'nav-tab-active';} ?> "><?php _e('Logs', 'limit-login-attempts-reloaded'); ?></a>
        <?php else : ?>
            <a href="<?php echo $this->get_options_page_uri('logs-local'); ?>" class="nav-tab <?php if($active_tab == 'logs-local'){echo 'nav-tab-active';} ?> "><?php _e('Logs', 'limit-login-attempts-reloaded'); ?></a>
		<?php endif; ?>

        <a href="<?php echo $this->get_options_page_uri('debug'); ?>" class="nav-tab <?php if($active_tab == 'debug'){echo 'nav-tab-active';} ?>"><?php _e('Debug', 'limit-login-attempts-reloaded'); ?></a>
        <a href="<?php echo $this->get_options_page_uri('help'); ?>" class="nav-tab <?php if($active_tab == 'help'){echo 'nav-tab-active';} ?>"><?php _e('Help', 'limit-login-attempts-reloaded'); ?></a>

        <?php if( $active_app === 'local' ) : ?>
        <a href="<?php echo $this->get_options_page_uri('premium'); ?>" class="nav-tab <?php if($active_tab == 'premium'){echo 'nav-tab-active';} ?>"><?php _e('Premium / Extensions', 'limit-login-attempts-reloaded'); ?></a>
        <?php endif; ?>

        <?php if($active_tab == 'logs-custom') : ?>
        <a class="llar-failover-link" href="<?php echo $this->get_options_page_uri('logs-local'); ?>"><?php _e( 'Failover', 'limit-login-attempts-reloaded' ); ?></a>
        <?php endif; ?>
    </h2>

    <?php include_once(LLA_PLUGIN_DIR.'views/tab-'.$active_tab.'.php'); ?>
</div>

<script>
    (function($) {
        const $auto_update_notice = $('.llar-auto-update-notice');

        $(document).ready(function() {
            $auto_update_notice.on('click', ' .auto-enable-update-option', function(e){
                e.preventDefault();

                $.post(ajaxurl, {
                    action: 'toggle_auto_update',
                    value: $(this).data('val'),
                    sec: '<?php echo wp_create_nonce( "llar-action" ); ?>'
                }, function(response){
                    if(response.success) {
                        $auto_update_notice.remove();
                    }
                });
            })
        })
    })(jQuery);
</script>