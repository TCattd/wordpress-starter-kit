<?php

use LLAR\Core\Config;

if( !defined( 'ABSPATH' ) ) exit();
?>
<?php
$app_config = Config::get( 'app_config' );
$full_log_url = !empty( $app_config['key'] ) ? 'https://my.limitloginattempts.com/logs?key=' . esc_attr( $app_config['key'] ) : false;
?>
<div class="llar-table-header">
    <h3><?php _e( 'Event Log', 'limit-login-attempts-reloaded' ); ?></h3>
	<?php if( $full_log_url ): ?>
        <span class="right-link"><a href="<?php echo esc_attr( $full_log_url ); ?>" target="_blank"><?php _e( 'Full Logs', 'limit-login-attempts-reloaded' ); ?></a>
        <i class="llar-tooltip" data-text="<?php esc_attr_e( 'All attempts blocked by access rules are hidden by default. You can see the full log at this link.' ); ?>">
            <span class="dashicons dashicons-editor-help"></span>
        </i>
    </span>
	<?php endif; ?>
</div>

<div class="llar-table-scroll-wrap llar-app-log-infinity-scroll">
    <table class="form-table llar-table-app-log">
        <thead>
            <tr>
                <th scope="col"><?php _e( "Time", 'limit-login-attempts-reloaded' ); ?></th>
                <th scope="col"><?php _e( "IP", 'limit-login-attempts-reloaded' ); ?></th>
                <th scope="col"><?php _e( "Gateway", 'limit-login-attempts-reloaded' ); ?></th>
                <th scope="col"><?php _e( "Login", 'limit-login-attempts-reloaded' ); ?></th>
                <th scope="col"><?php _e( "Rule", 'limit-login-attempts-reloaded' ); ?></th>
                <th scope="col"><?php _e( "Reason", 'limit-login-attempts-reloaded' ); ?></th>
                <th scope="col"><?php _e( "Pattern", 'limit-login-attempts-reloaded' ); ?></th>
                <th scope="col"><?php _e( "Attempts Left", 'limit-login-attempts-reloaded' ); ?></th>
                <th scope="col"><?php _e( "Lockout Duration", 'limit-login-attempts-reloaded' ); ?></th>
                <th scope="col"><?php _e( "Actions", 'limit-login-attempts-reloaded' ); ?></th>
            </tr>
        </thead>
        <tbody></tbody>
        <tfoot class="table-inline-preloader">
            <tr>
                <td colspan="100%">
                    <div class="load-more-button"><a href="#"><?php _e( "Load older events", 'limit-login-attempts-reloaded' ); ?></a></div>
                    <div class="preloader-row">
                        <span class="preloader-icon"></span>
                        <span class="preloader-text"><?php echo sprintf(
								__( 'Loading older events, skipping ACL events. <a href="%s" target="_blank">Full logs</a>', 'limit-login-attempts-reloaded' ),
								$full_log_url
							);
							?></span>
                    </div>
                </td>
            </tr>
        </tfoot>
    </table>
</div>
<script type="text/javascript">
	;(function($){

		$(document).ready(function () {

			var $log_table_body = $('.llar-table-app-log tbody'),
                $preloader = $log_table_body.next('.table-inline-preloader'),
                $load_more_btn = $preloader.find('.load-more-button a'),
                loading_data = false,
				page_offset = '',
                page_limit = 10,
                total_loaded = 0;

			load_log_data();

            $('.llar-global-reload-btn').on('click', function() {
                page_offset = '';
                $log_table_body.find('> tr').remove();
                $preloader.removeClass('hidden');
                total_loaded = 0;
                load_log_data();
            });

            $load_more_btn.on('click', function(e) {
                e.preventDefault();
                total_loaded = 0;
                load_log_data();
            });

            $log_table_body.on('click', '.js-app-log-action', function (e) {
				e.preventDefault();

				var $this = $(this),
					method = $this.data('method'),
					params = $this.data('params');

				if(!confirm('Are you sure?')) return;

                $preloader.addClass('loading');

				$.post(ajaxurl, {
					action: 'app_log_action',
					method: method,
					params: params,
					sec: '<?php echo esc_js( wp_create_nonce( "llar-action" ) ); ?>'
				}, function(response){

                    $preloader.removeClass('loading');

					if(response.success) {

                        if(method === 'lockout/delete') {
                            $('.llar-table-app-lockouts').trigger('llar:refresh');
                        }
					}

				});
			});

			function load_log_data() {

			    if(page_offset === false) {
			        return;
                }

                $preloader.addClass('loading');
				loading_data = true;

				$.post(ajaxurl, {
					action: 'app_load_log',
					offset: page_offset,
                    limit: page_limit,
					sec: '<?php echo wp_create_nonce( "llar-action" ); ?>'
				}, function(response){

                    $preloader.removeClass('loading');

					if(response.success) {

					    if(response.data.html) {
                            $log_table_body.append(response.data.html);
                        }

                        total_loaded += response.data.total_items;

                        if(response.data.offset) {
                            page_offset = response.data.offset;

                            if(response.data.total_items < page_limit && total_loaded < page_limit) {
                                console.log('extra load');
                                load_log_data();
                            }

						} else {
                            $preloader.addClass('hidden');
						    page_offset = false;
                        }

                        loading_data = false;
					}

				});

			}
		});

	})(jQuery);
</script>