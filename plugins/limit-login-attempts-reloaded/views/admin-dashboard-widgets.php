<?php

use LLAR\Core\Config;
use LLAR\Core\Helpers;
use LLAR\Core\LimitLoginAttempts;

if (!defined('ABSPATH')) exit();

$active_app = ( Config::get( 'active_app' ) === 'custom' && LimitLoginAttempts::$cloud_app ) ? 'custom' : 'local';

$wp_locale = str_replace( '_', '-', get_locale() );

$retries_chart_title = '';
$retries_chart_desc = '';
$retries_chart_color = '';

$api_stats = false;
$retries_count = 0;
if ($active_app === 'local') {

	$retries_stats = Config::get('retries_stats');

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

	if ($retries_count === 0) {

		$retries_chart_title = __('Hooray! Zero failed login attempts (past 24 hrs)', 'limit-login-attempts-reloaded');
		$retries_chart_color = '#66CC66';
	} else if ($retries_count < 100) {

		$retries_chart_title = sprintf(_n('%d failed login attempt ', '%d failed login attempts ', $retries_count, 'limit-login-attempts-reloaded'), $retries_count);
		$retries_chart_title .= __('(past 24 hrs)', 'limit-login-attempts-reloaded');
		$retries_chart_desc = __('Your site is currently at a low risk for brute force activity', 'limit-login-attempts-reloaded');
		$retries_chart_color = '#FFCC66';
	} else {

		$retries_chart_title = __('Warning: Your site has experienced over 100 failed login attempts in the past 24 hours', 'limit-login-attempts-reloaded');
		$retries_chart_desc = sprintf(__('Your site is currently at a high risk for brute force activity. Consider <a href="%s" target="_blank">premium protection</a> if frequent attacks persist or website performance is degraded', 'limit-login-attempts-reloaded'), 'https://www.limitloginattempts.com/info.php?from=dashboard-widget');
		$retries_chart_color = '#FF6633';
	}

} else {

	$api_stats = LimitLoginAttempts::$cloud_app->stats();

	if ($api_stats && !empty($api_stats['attempts']['count'])) {

		$retries_count = (int)end($api_stats['attempts']['count']);
	}

	$retries_chart_title = __('Failed Login Attempts Today', 'limit-login-attempts-reloaded');
	$retries_chart_desc = __('All failed login attempts have been neutralized in the cloud', 'limit-login-attempts-reloaded');
	$retries_chart_color = '#66CC66';
}

?>

<div id="llar-admin-dashboard-widgets">
    <div class="llar-widget">
        <div class="widget-content">
            <div class="chart">
                <div class="doughnut-chart-wrap"><canvas id="llar-attack-velocity-chart"></canvas></div>
                <span class="llar-retries-count"><?php echo esc_html( Helpers::short_number( $retries_count ) ); ?></span>
            </div>
            <script type="text/javascript">
                (function () {

                    var ctx = document.getElementById('llar-attack-velocity-chart').getContext('2d');
                    var llar_retries_chart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            datasets: [{
                                data: [1],
                                value: <?php echo esc_js($retries_count); ?>,
                                backgroundColor: ['<?php echo esc_js($retries_chart_color); ?>'],
                                borderWidth: [0]
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
            <div class="title"><?php echo esc_html($retries_chart_title); ?></div>
            <div class="desc"><?php echo $retries_chart_desc; ?></div>
        </div>
    </div>
    <div class="llar-widget widget-2">
        <div class="widget-content">
			<?php
			$chart2_label = '';
			$chart2_labels = array();
			$chart2_datasets = array();

			if ($active_app === 'custom') {

				$stats_dates = array();
				$stats_values = array();
				$date_format = trim(get_option('date_format'), ' yY,._:;-/\\');
				$date_format = str_replace('F', 'M', $date_format);

				$dataset = array(
					'label' => __('Failed Login Attempts', 'limit-login-attempts-reloaded'),
					'data' => [],
					'backgroundColor' => 'rgb(54, 162, 235)',
					'borderColor' => 'rgb(54, 162, 235)',
					'fill' => false,
				);

				if ($api_stats && !empty($api_stats['attempts'])) {

					foreach ($api_stats['attempts']['at'] as $timest) {

						$stats_dates[] = date($date_format, $timest);
					}

					$chart2_label = __('Requests', 'limit-login-attempts-reloaded');
					$chart2_labels = $stats_dates;

					$dataset['data'] = $api_stats['attempts']['count'];
				}

				$chart2_datasets[] = $dataset;

			} else {

				$date_format = trim(get_option('date_format'), ' yY,._:;-/\\');
				$date_format = str_replace('F', 'M', $date_format);

				$retries_stats = Config::get('retries_stats');

				if (is_array($retries_stats) && $retries_stats) {

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

					$chart2_labels[] = (new DateTime())->format($date_format);
					$chart2_data[] = 0;
				}


				$chart2_datasets[] = array(
					'label' => __('Failed Login Attempts', 'limit-login-attempts-reloaded'),
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
                (function () {

                    var ctx = document.getElementById('llar-api-requests-chart').getContext('2d');
                    var llar_stat_chart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: <?php echo json_encode($chart2_labels); ?>,
                            datasets: <?php echo json_encode($chart2_datasets); ?>
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
</div>
