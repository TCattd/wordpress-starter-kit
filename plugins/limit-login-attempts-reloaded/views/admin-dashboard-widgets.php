<?php

if (!defined('ABSPATH')) exit();

$active_app = ( $this->get_option( 'active_app' ) === 'custom' && $this->app ) ? 'custom' : 'local';

$retries_chart_title = '';
$retries_chart_desc = '';
$retries_chart_color = '';
$retries_chart_show_actions = false;

$api_stats = false;
$retries_count = 0;
if ($active_app === 'local') {

	$retries_stats = $this->get_option('retries_stats');

	if ($retries_stats) {
		if (array_key_exists(date_i18n('Y-m-d'), $retries_stats)) {
			$retries_count = (int)$retries_stats[date_i18n('Y-m-d')];
		}
	}

	if ($retries_count === 0) {

		$retries_chart_title = __('Hooray! Zero failed login attempts today', 'limit-login-attempts-reloaded');
		$retries_chart_color = '#66CC66';
	} else if ($retries_count < 100) {

		$retries_chart_title = sprintf(_n('%d failed login attempt ', '%d failed login attempts ', $retries_count, 'limit-login-attempts-reloaded'), $retries_count);
		$retries_chart_title .= __('today', 'limit-login-attempts-reloaded');
		$retries_chart_desc = __('Your site might have been discovered by hackers', 'limit-login-attempts-reloaded');
		$retries_chart_color = '#FFCC66';
	} else {

		$retries_chart_title = __('Warning: Your site is experiencing over 100 failed login attempts today', 'limit-login-attempts-reloaded');
		$retries_chart_desc = __('Your site may be under a brute-force attack', 'limit-login-attempts-reloaded');
		$retries_chart_color = '#FF6633';
		$retries_chart_show_actions = true;
	}

} else {

	$api_stats = $this->app->stats();

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
                <canvas id="llar-attack-velocity-chart"></canvas>
                <span class="llar-retries-count"><?php echo esc_html($retries_count); ?></span>
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
                            cutoutPercentage: 70,
                            title: {
                                display: false,
                                // text: 'Local Attack Velocity'
                            },
                            tooltips: {
                                enabled: false
                            },
                            layout: {
                                padding: {
                                    // bottom: 40
                                }
                            },
                            valueLabel: {
                                display: true,
                                fontSize: 25,
                                color: '#3e76c1',
                                backgroundColor: 'rgba(0,0,0,0)',
                                bottomMarginPercentage: -6
                            },
                        }
                    });

                })();
            </script>
            <div class="title"><?php echo esc_html($retries_chart_title); ?></div>
            <div class="desc"><?php echo $retries_chart_desc; ?></div>
			<?php if ($retries_chart_show_actions) : ?>
                <div class="actions">
                    <ol>
                        <li><?php _e('Ensure your passwords are secure.', 'limit-login-attempts-reloaded'); ?></li>
                        <li><?php _e('Make sure WordPress and all your plugins are updated.', 'limit-login-attempts-reloaded'); ?></li>
                        <li><?php echo sprintf(__('Consider <a href="%s" target="_blank">upgrading to premium</a> for advanced protection.', 'limit-login-attempts-reloaded'), 'https://www.limitloginattempts.com/info.php?from=dashboard-widget'); ?></li>
                    </ol>
                </div>
			<?php endif; ?>
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

				$retries_stats = $this->get_option('retries_stats');

				if (is_array($retries_stats) && $retries_stats) {

					$daterange = new DatePeriod(
						new DateTime(key($retries_stats)),
						new DateInterval('P1D'),
						new DateTime()
					);

					$chart2_data = array();
					foreach ($daterange as $date) {

						$chart2_labels[] = $date->format($date_format);
						$chart2_data[] = (!empty($retries_stats[$date->format("Y-m-d")])) ? $retries_stats[$date->format("Y-m-d")] : 0;
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
                            tooltips: {
                                mode: 'index',
                                intersect: false,
                            },
                            hover: {
                                mode: 'nearest',
                                intersect: true
                            },
                            scales: {
                                xAxes: [{
                                    display: true,
                                    scaleLabel: {
                                        display: false
                                    }
                                }],
                                yAxes: [{
                                    display: true,
                                    scaleLabel: {
                                        display: false
                                    },
                                    ticks: {
                                        beginAtZero: true,
                                        userCallback: function (label, index, labels) {
                                            if (Math.floor(label) === label) {
                                                return label;
                                            }
                                        },
                                    }
                                }]
                            }
                        }
                    });

                })();
            </script>
        </div>
    </div>
</div>
