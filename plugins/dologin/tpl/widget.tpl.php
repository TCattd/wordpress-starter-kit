<?php

namespace dologin;

defined('WPINC') || exit;

$list = $this->cls('Auth')->history_list(20);
$count = $this->cls('Auth')->count_list();
$is_admin = current_user_can('manage_options');

echo '<h2>' . __('Blocked login attempts total', 'dologin') . ': ' . $count . '</h2>';
echo '<h2>' . __('Login Attempts Log', 'dologin') . '</h2>';
?>
<style type="text/css">
	.dologin-widget-table {
		width: 100%;
		max-width: 100%;
		border-collapse: collapse;
	}

	.dologin-widget-table thead th {
		background-color: #222;
		color: #FFFFFF;
		font-weight: bold;
		border-color: #474747;
		text-align: left;
		padding: 6px 4px;
		border: 1px solid #cccccc;
	}

	.dologin-widget-table tbody td {
		text-align: left;
		padding: 6px 4px;
		border: 1px solid #cccccc;
	}
</style>
<table class="wp-list-table striped dologin-widget-table">
	<thead>
		<tr>
			<th>IP</th>
			<th>Location</th>
			<th>Date</th>
		</tr>
	</thead>
	<tbody>
		<?php if (!$list) : ?>
			<tr>
				<td><?php echo __('No list yet.', 'dologin'); ?></td>
			</tr>
		<?php else : ?>
			<?php
			foreach ($list as $k => $v) {
				$ip_geo = explode(', ', $v->ip_geo);
				$ip_geo_desc = array();
				foreach ($ip_geo as $v2) {
					$v2 = explode(':', $v2);
					if (in_array($v2[0], array('country', 'city'))) {
						$ip_geo_desc[] = $v2[1];
					}
				}
				$ip_geo_desc = implode('-', $ip_geo_desc);
				echo '<tr><td>' . (!$is_admin ? '**' : $v->ip) . '</td><td>' . $ip_geo_desc . '</td><td>' . date('m/d H:i', $v->dateline) . '</td></tr>';
			}

			?>
		<?php endif; ?>
	</tbody>
</table>

<div>
	<a href="<?php echo menu_page_url('dologin', 0); ?>#log" style="text-align: right; display: block;"><?php echo __('Check more', 'dologin'); ?></a>
</div>