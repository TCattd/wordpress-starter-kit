<?php

use LLAR\Core\CloudApp;
use LLAR\Core\Config;
use LLAR\Core\Helpers;
use LLAR\Core\LimitLoginAttempts;

if( !defined( 'ABSPATH' ) ) exit();

$active_app = Config::get( 'active_app' );
$active_app = ( $active_app === 'custom' && LimitLoginAttempts::$cloud_app ) ? 'custom' : 'local';

$wp_locale = str_replace( '_', '-', get_locale() );

$retries_chart_title = '';
$retries_chart_desc = '';
$retries_chart_color = '';

$api_stats = false;
$retries_count = 0;
if( $active_app === 'local' ) {

	$retries_stats = Config::get( 'retries_stats' );

	if( $retries_stats ) {
		foreach ( $retries_stats as $key => $count ) {
		    if( is_numeric( $key ) && $key > strtotime( '-24 hours' ) ) {
			    $retries_count += $count;
            }
		    elseif( !is_numeric( $key ) && date_i18n( 'Y-m-d' ) === $key ) {
			    $retries_count += $count;
            }
        }
	}

    if( $retries_count === 0 ) {

		$retries_chart_title = __( 'Hooray! Zero failed login attempts (past 24 hrs)', 'limit-login-attempts-reloaded' );
		$retries_chart_color = '#66CC66';
    }
    else if ( $retries_count < 100 ) {

		$retries_chart_title = sprintf( _n( '%d failed login attempt ', '%d failed login attempts ', $retries_count, 'limit-login-attempts-reloaded' ), $retries_count );
		$retries_chart_title .= __( '(past 24 hrs)', 'limit-login-attempts-reloaded' );
		$retries_chart_desc = __( 'Your site is currently at a low risk for brute force activity', 'limit-login-attempts-reloaded' );
		$retries_chart_color = '#FFCC66';
    } else {

		$retries_chart_title = __( 'Warning: Your site has experienced over 100 failed login attempts in the past 24 hours', 'limit-login-attempts-reloaded' );
		$retries_chart_desc = sprintf(__('Your site is currently at a high risk for brute force activity. Consider <a href="%s" target="_blank">premium protection</a> if frequent attacks persist or website performance is degraded', 'limit-login-attempts-reloaded'), 'https://www.limitloginattempts.com/info.php?from=plugin-dashboard-status');
		$retries_chart_color = '#FF6633';
    }

} else {

	$api_stats = LimitLoginAttempts::$cloud_app->stats();

	if( $api_stats && !empty( $api_stats['attempts']['count'] )) {

		$retries_count = (int) end( $api_stats['attempts']['count'] );
    }

	$retries_chart_title = __( 'Failed Login Attempts Today', 'limit-login-attempts-reloaded' );
	$retries_chart_desc = __( 'All failed login attempts have been neutralized in the cloud', 'limit-login-attempts-reloaded' );
	$retries_chart_color = '#66CC66';
}

?>

<div id="llar-dashboard-page">
	<div class="dashboard-header">
		<h1><?php _e( 'Limit Login Attempts Reloaded Dashboard', 'limit-login-attempts-reloaded' ); ?></h1>
	</div>
	<div class="dashboard-section-1 <?php echo esc_attr( $active_app ); ?>">
		<div class="info-box-1">
            <div class="section-title"><?php _e( 'Failed Login Attempts', 'limit-login-attempts-reloaded' ); ?><?php echo $active_app === 'custom' ? '<span class="llar-premium-label"><span class="dashicons dashicons-yes-alt"></span>' . __( 'Premium protection enabled', 'limit-login-attempts-reloaded' ) . '</span>' : ''; ?></div>
            <div class="section-content">
                <div class="chart">
                    <div class="doughnut-chart-wrap"><canvas id="llar-attack-velocity-chart"></canvas></div>
                    <span class="llar-retries-count"><?php echo esc_html( Helpers::short_number( $retries_count ) ); ?></span>
                </div>
                <script type="text/javascript">
					(function(){

						var ctx = document.getElementById('llar-attack-velocity-chart').getContext('2d');
						var llar_retries_chart = new Chart(ctx, {
							type: 'doughnut',
							data: {
								// labels: ['Success', 'Warning', 'Warning', 'Fail'],
								datasets: [{
									data: [1],
									value: <?php echo esc_js( $retries_count ); ?>,
									backgroundColor: ['<?php echo esc_js( $retries_chart_color ); ?>'],
									borderWidth: [0],
								}]
							},
							options: {
								responsive: true,
								cutout: 50,
								title: {
									display: false,
								},
                                plugins: {
                                    tooltip: {
                                        enabled: false
                                    }
                                }
							}
						});

					})();
                </script>
                <div class="title"><?php echo esc_html( $retries_chart_title ); ?></div>
                <div class="desc"><?php echo $retries_chart_desc; ?></div>
            </div>
        </div>
        <div class="info-box-2">
            <div class="section-content">
                <?php
                $chart2_label = '';
                $chart2_labels = array();
				$chart2_datasets = array();

                if( $active_app === 'custom' ) {

                    $stats_dates = array();
                    $stats_values = array();
                    $date_format = trim( get_option( 'date_format' ), ' yY,._:;-/\\' );
                    $date_format = str_replace( 'F', 'M', $date_format );

					$dataset = array(
						'label' => __('Failed Login Attempts', 'limit-login-attempts-reloaded'),
						'data' => [],
						'backgroundColor' => 'rgb(54, 162, 235)',
						'borderColor' => 'rgb(54, 162, 235)',
						'fill' => false,
					);

                    if( $api_stats && !empty( $api_stats['attempts'] ) ) {

                        foreach ($api_stats['attempts']['at'] as $timest) {

                            $stats_dates[] = date( $date_format, $timest );
                        }

                        $chart2_label = __('Requests', 'limit-login-attempts-reloaded');
                        $chart2_labels = $stats_dates;

                        $dataset['data'] = $api_stats['attempts']['count'];
                    }

					$chart2_datasets[] = $dataset;

                } else {

					$date_format = trim( get_option( 'date_format' ), ' yY,._:;-/\\' );
					$date_format = str_replace( 'F', 'M', $date_format );

					$retries_stats = Config::get( 'retries_stats' );

					if( is_array( $retries_stats ) && $retries_stats ) {
                        $key = key( $retries_stats );
                        $start = is_numeric( $key ) ? date_i18n( 'Y-m-d', $key ) : $key;

						$daterange = new DatePeriod(
							new DateTime( $start ),
							new DateInterval('P1D'),
							new DateTime('-1 day')
						);

						$retries_per_day = [];
						foreach ( $retries_stats as $key => $count ) {

						    $date = is_numeric( $key ) ? date_i18n( 'Y-m-d', $key ) : $key;

						    if( empty( $retries_per_day[$date] ) ) {
							    $retries_per_day[$date] = 0;
                            }

							$retries_per_day[$date] += $count;
						}

						$chart2_data = array();
						foreach ($daterange as $date) {

							$chart2_labels[] = $date->format( $date_format );
							$chart2_data[] = (!empty($retries_per_day[$date->format("Y-m-d")])) ? $retries_per_day[$date->format("Y-m-d")] : 0;
						}
                    } else {

						$chart2_labels[] = (new DateTime())->format( $date_format );
						$chart2_data[] = 0;
                    }


                    $chart2_datasets[] = array(
						'label' => __( 'Failed Login Attempts', 'limit-login-attempts-reloaded' ),
						'data' => $chart2_data,
						'backgroundColor' => 'rgb(54, 162, 235)',
						'borderColor' => 'rgb(54, 162, 235)',
						'fill' => false,
                    );
				}

                ?>

                <div class="llar-chart-wrap">
                    <canvas id="llar-api-requests-chart" style=""></canvas>
                </div>

                <script type="text/javascript">
					(function(){

						var ctx = document.getElementById('llar-api-requests-chart').getContext('2d');
						var llar_stat_chart = new Chart(ctx, {
							type: 'line',
							data: {
								labels: <?php echo json_encode( $chart2_labels ); ?>,
                                datasets: <?php echo json_encode( $chart2_datasets ); ?>
							},
							options: {
								responsive: true,
								maintainAspectRatio: false,
                                plugins: {
                                    tooltip: {
                                        mode: 'index',
                                        intersect: false,
                                        callbacks: {
                                            label: function (context) {
                                                return context.raw.toLocaleString('<?php echo esc_js( $wp_locale ); ?>');
                                            }
                                        }
                                    },
                                },
								hover: {
									mode: 'nearest',
									intersect: true
								},
								scales: {
									x: {
										display: true,
										scaleLabel: {
											display: false
										}
									},
									y: {
										display: true,
										scaleLabel: {
											display: false
										},
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(label, index, labels) {
												if (Math.floor(label) === label) {
													return label.toLocaleString('<?php echo esc_js( $wp_locale ); ?>');
												}
											},
										}
									}
								}
							}
						});

					})();
                </script>

            </div>
        </div>
        <?php if( $active_app === 'local' ) : ?>
		<div class="info-box-3">
            <div class="section-content">
                <div class="title"><?php _e( 'Premium Protection Disabled', 'limit-login-attempts-reloaded' ); ?></div>
                <div class="desc"><?php _e( 'As a free user, your local server is absorbing the traffic brought on by brute force attacks, potentially slowing down your website. Upgrade to Premium today to outsource these attacks through our cloud app, and slow down future attacks with advanced throttling.', 'limit-login-attempts-reloaded' ); ?></div>
                <div class="actions">
                    <a href="https://www.limitloginattempts.com/info.php?from=plugin-dashboard-cta" target="_blank" class="button button-primary"><?php _e( 'Upgrade to Premium', 'limit-login-attempts-reloaded' ); ?></a><br>
                </div>
            </div>
        </div>
        <?php endif; ?>
	</div>
	<div class="dashboard-section-2" style="display:none;">
        <div class="info-box-1">
            <div class="section-title"><?php _e( 'Notifications', 'limit-login-attempts-reloaded' ); ?></div>
            <ul class="notifications-list">
                <li><a href="#">12 issues found in most recent scan</a></li>
                <li><a href="#">Updates are available for WordPress (v5.6) and 10 plugins</a></li>
            </ul>
        </div>
        <div class="info-box-2">
            <div class="info-box-icon">
                <span class="dashicons dashicons-rest-api"></span>
            </div>
            <div class="info-box-content">
                <div class="title"><?php _e( 'Multiply Your Protection By Adding More Domains', 'limit-login-attempts-reloaded' ); ?></div>
                <div class="desc"><?php _e( 'When you upgrade to premium, you can synchronize your IP safelist and blacklist between multiple sites. This is a great way to improve your network performance and slow down future attacks.', 'limit-login-attempts-reloaded' ); ?></div>
                <div class="actions">
                    <a href="#"><?php _e( 'Learn More', 'limit-login-attempts-reloaded' ); ?></a>
                    <a href="#"><?php _e( 'Connect This Site', 'limit-login-attempts-reloaded' ); ?></a>
                </div>
            </div>
        </div>
    </div>
	<div class="dashboard-section-3">
        <div class="info-box-1">
            <div class="info-box-icon">
                <span class="dashicons dashicons-admin-tools"></span>
            </div>
            <div class="info-box-content">
                <div class="title"><a href="<?php echo $this->get_options_page_uri('logs-'.$active_app); ?>"><?php _e( 'Tools', 'limit-login-attempts-reloaded' ); ?></a></div>
                <div class="desc"><?php _e( 'View lockouts logs, block or whitelist usernames or IPs, and more.', 'limit-login-attempts-reloaded' ); ?></div>
            </div>
        </div>
        <div class="info-box-1">
            <div class="info-box-icon">
                <span class="dashicons dashicons-sos"></span>
            </div>
            <div class="info-box-content">
                <div class="title"><a href="https://www.limitloginattempts.com/info.php?from=plugin-dashboard-help" target="_blank"><?php _e( 'Help', 'limit-login-attempts-reloaded' ); ?></a></div>
                <div class="desc"><?php _e( 'Find the documentation and help you need.', 'limit-login-attempts-reloaded' ); ?></div>
            </div>
        </div>
        <div class="info-box-1">
            <div class="info-box-icon">
                <span class="dashicons dashicons-admin-generic"></span>
            </div>
            <div class="info-box-content">
                <div class="title"><a href="<?php echo $this->get_options_page_uri('settings'); ?>"><?php _e( 'Global Options', 'limit-login-attempts-reloaded' ); ?></a></div>
                <div class="desc"><?php _e( 'Many options such as notifications, alerts, premium status, and more.', 'limit-login-attempts-reloaded' ); ?></div>
            </div>
        </div>
    </div>
    <?php if( $stats_global = CloudApp::stats_global() ) : ?>
	<div class="dashboard-section-4">
        <?php
		$stats_global_dates = array();
		$date_format = trim( get_option( 'date_format' ), ' yY,._:;-/\\' );
		$date_format = str_replace( 'F', 'M', $date_format );

		foreach ( $stats_global['attempts']['day']['at'] as $timest ) {

			$stats_global_dates[] = date( $date_format, $timest );
		}
		
		$countries_list = Helpers::get_countries_list();
        ?>
        <div class="info-box-1">
            <div class="section-title">
                <span><?php _e( 'Failed Login Attempts By Country', 'limit-login-attempts-reloaded' ); ?></span>
                <span class="section-title-info"><?php _e( 'Global Network (Premium Users)', 'limit-login-attempts-reloaded' ); ?></span>
            </div>
            <div class="section-content">
                <table class="lockouts-by-country-table">
                    <tr>
                        <th><?php _e( 'Country', 'limit-login-attempts-reloaded' ); ?></th>
                        <th><?php _e( 'Attempts', 'limit-login-attempts-reloaded' ); ?></th>
                    </tr>
                    <?php foreach( $stats_global['countries'] as $country_data ) :

                        $country_code = ( array_key_exists( $country_data['code'], $countries_list ) ) ? $country_data['code'] : 'ZZ';
                        $country_name = apply_filters( 'llar_country_name', $countries_list[$country_code], $country_code );
                        ?>
                        <tr>
                            <td>
                                <?php if( $country_code !== 'ZZ' ) : ?>
                                <img class="flag-icon" src="<?php echo LLA_PLUGIN_URL; ?>/assets/img/flags/<?php echo esc_attr( $country_data['code'] ); ?>.png">
                                <?php endif; ?>
                            <?php echo esc_html( $country_name ); ?></td>
                            <td><?php echo esc_html( number_format_i18n( $country_data['attempts'] ) ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <p class="countries-table-info"><?php _e( 'today', 'limit-login-attempts-reloaded' ); ?></p>
                <p class="countries-table-info-right"><?php _e( 'Block by country feature available with <a href="https://www.limitloginattempts.com/info.php?from=plugin-dashboard-country" target="_blank">premium plus plan</a>.', 'limit-login-attempts-reloaded' ) ?></p>
            </div>
        </div>

        <div class="info-box-2">
            <div class="section-title">
				<span><?php _e( 'Total Failed Login Attempts', 'limit-login-attempts-reloaded' ); ?></span>
                <span class="section-title-info"><?php _e( 'Global Network (Premium Users)', 'limit-login-attempts-reloaded' ); ?></span>
            </div>
            <div class="section-content">
                <div class="llar-chart-wrap">
                    <canvas id="llar-total-attacks-blocked-chart" style=""></canvas>
                </div>
                <script type="text/javascript">
                    (function(){

                        var ctx = document.getElementById('llar-total-attacks-blocked-chart').getContext('2d');
                        var llar_total_attacks_blocked_chart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: <?php echo json_encode( $stats_global_dates ); ?>,
                                datasets: [{
                                    label: '<?php echo esc_js( __( 'Total Attempts', 'limit-login-attempts-reloaded' ) ); ?>',
                                    data: <?php echo json_encode( $stats_global['attempts']['day']['count'] ); ?>,
                                    backgroundColor: 'rgb(255, 159, 64)',
                                    borderColor: 'rgb(255, 159, 64)',
                                    fill: false
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    tooltip: {
                                        mode: 'index',
                                        intersect: false,
                                        callbacks: {
                                            label: function (context) {
                                                return context.raw.toLocaleString('<?php echo esc_js( $wp_locale ); ?>');
                                            }
                                        }
                                    },
                                },
                                hover: {
                                    mode: 'nearest',
                                    intersect: true
                                },
                                scales: {
                                    x: {
                                        display: true,
                                        scaleLabel: {
                                            display: false
                                        }
                                    },
                                    y: {
                                        display: true,
                                        scaleLabel: {
                                            display: false
                                        },
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(label, index, labels) {
                                                if (Math.floor(label) === label) {
                                                    return label.toLocaleString('<?php echo esc_js( $wp_locale ); ?>');
                                                }
                                            },
                                        }
                                    }
                                },
                            }
                        });

                    })();
                </script>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once( LLA_PLUGIN_DIR . 'views/onboarding-popup.php')?>