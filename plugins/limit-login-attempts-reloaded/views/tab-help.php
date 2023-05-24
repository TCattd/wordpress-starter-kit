<?php

use LLAR\Core\Config;

if( !defined( 'ABSPATH' ) ) exit();
?>

<div class="llar-help-page">
    <?php if( Config::get( 'active_app' ) === 'local' ) : ?>
    <div class="section-1">
        <div class="block">
            <div class="title"><?php _e( 'Upgrade Now to Access Premium Support', 'limit-login-attempts-reloaded' ); ?></div>
            <div class="text"><?php _e( 'Our technical support team is available by email to help with any questions.', 'limit-login-attempts-reloaded' ); ?></div>
            <div class="actions">
                <a class="button button-primary" href="https://checkout.limitloginattempts.com/plan?from=plugin-help-tab" target="_blank">
                    <?php _e( 'Upgrade To Premium', 'limit-login-attempts-reloaded' ); ?>
                </a>
            </div>
        </div>
        <div class="block">
            <div class="title"><?php _e( 'Free Support', 'limit-login-attempts-reloaded' ); ?></div>
            <div class="text"><?php _e( 'Support for free customers is available via our forums page on WordPress.org. The majority of requests <b>receive an answer within a few days</b>.', 'limit-login-attempts-reloaded' ); ?></div>
            <div class="actions">
                <a class="button button-secondary" href="https://wordpress.org/support/plugin/limit-login-attempts-reloaded/" target="_blank"><?php _e( 'Go To Support Forums', 'limit-login-attempts-reloaded' ); ?></a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <a href="https://www.limitloginattempts.com/gdpr-qa/?from=plugin-help-tab" target="_blank" class="gdpr-information-link"><?php _e( 'GDPR Information', 'limit-login-attempts-reloaded' ); ?></a>

    <h2 class="all-doc-title"><a href="#" target="_blank"><?php _e( 'All Documentation', 'limit-login-attempts-reloaded' ); ?></a></h2>
    <div class="documentation-section">
        <div class="questions">
            <a class="question" href="https://www.limitloginattempts.com/services/cloud-service/?from=plugin-help-tab" target="_blank">
                <div class="title"><?php _e( 'Cloud Service & Security', 'limit-login-attempts-reloaded' ); ?></div>
                <div class="desc"><?php _e( 'Questions regarding the cloud service including how to activate, logs and storage, and compliance.', 'limit-login-attempts-reloaded' ); ?></div>
            </a>
            <a class="question" href="https://www.limitloginattempts.com/services/technical-questions/?from=plugin-help-tab" target="_blank">
                <div class="title"><?php _e( 'Technical Questions', 'limit-login-attempts-reloaded' ); ?></div>
                <div class="desc"><?php _e( 'Popular technical questions about the service including admin blocking, definitions, and email notifications.', 'limit-login-attempts-reloaded' ); ?></div>
            </a>
            <a class="question" href="https://www.limitloginattempts.com/services/accounts/?from=plugin-help-tab" target="_blank">
                <div class="title"><?php _e( 'Accounts & Billing', 'limit-login-attempts-reloaded' ); ?></div>
                <div class="desc"><?php _e( 'Questions regarding updating billing info, cancellation, and expiration.', 'limit-login-attempts-reloaded' ); ?></div>
            </a>
            <a class="question" href="https://www.limitloginattempts.com/services/pre-sales-questions/?from=plugin-help-tab" target="_blank">
                <div class="title"><?php _e( 'Pre-sales Questions', 'limit-login-attempts-reloaded' ); ?></div>
                <div class="desc"><?php _e( 'Questions regarding premium software sales.', 'limit-login-attempts-reloaded' ); ?></div>
            </a>
        </div>
        <div class="top-list">
            <div class="title"><?php _e( 'Top Topics and Questions', 'limit-login-attempts-reloaded' ); ?></div>
            <ol>
                <li><a href="https://www.limitloginattempts.com/under-attack/?from=plugin-help-tab" target="_blank"><?php _e( 'How do I know if I\'m under attack?', 'limit-login-attempts-reloaded' ); ?></a></li>
                <li><a href="https://www.limitloginattempts.com/how-can-i-tell-that-the-premium-plugin-is-working/?from=plugin-help-tab" target="_blank"><?php _e( 'How can I tell that the premium plugin is working?', 'limit-login-attempts-reloaded' ); ?></a></li>
                <li><a href="https://www.limitloginattempts.com/services/technical-questions/?from=plugin-help-tab#1499254962846-5ae4b44a-7b13" target="_blank"><?php _e( 'What do I do if the admin gets blocked?', 'limit-login-attempts-reloaded' ); ?></a></li>
                <li><a href="https://www.limitloginattempts.com/why-am-i-still-seeing-login-attempts-even-after-the-ip-got-blocked/?from=plugin-help-tab" target="_blank"><?php _e( 'Why am I still seeing login attempts even after the IP got blocked?', 'limit-login-attempts-reloaded' ); ?></a></li>
                <li><a href="https://www.limitloginattempts.com/could-these-failed-login-attempts-be-fake/?from=plugin-help-tab" target="_blank"><?php _e( 'Could these failed login attempts be fake?', 'limit-login-attempts-reloaded' ); ?></a></li>
                <li><a href="https://www.limitloginattempts.com/the-logs-tab-how-system-works/?from=plugin-help-tab" target="_blank"><?php _e( 'The Logs tab: How the system works', 'limit-login-attempts-reloaded' ); ?></a></li>
                <li><a href="https://www.limitloginattempts.com/services/accounts/?from=plugin-help-tab#1499254962846-5ae4b44a-7b13" target="_blank"><?php _e( 'What happens if my site exceeds the request limits in the plan?', 'limit-login-attempts-reloaded' ); ?></a></li>
                <li><a href="https://www.limitloginattempts.com/services/technical-questions/?from=plugin-help-tab#1499254962814-d0468adc-2d49" target="_blank"><?php _e( 'What do I do if all users get blocked?', 'limit-login-attempts-reloaded' ); ?></a></li>
                <li><a href="https://www.limitloginattempts.com/services/technical-questions/?from=plugin-help-tab#1499255188068-f47985cb-d829" target="_blank"><?php _e( 'I just installed LLAR and I\'m already getting several failed login attempts', 'limit-login-attempts-reloaded' ); ?></a></li>
                <li><a href="https://www.limitloginattempts.com/services/technical-questions/?from=plugin-help-tab#1499255188068-f47985cb-d842" target="_blank"><?php _e( 'What URLs are being attacked and protected?', 'limit-login-attempts-reloaded' ); ?></a></li>
            </ol>
        </div>
    </div>
</div>