<?php
namespace dologin;
defined( 'WPINC' ) || exit;

$list = $this->cls( 'Auth' )->history_list( 20 );
$count = $this->cls( 'Auth' )->count_list();
$pagination = Util::pagination( $count, 20 );
?>
<div class="dologin-relative">
	<h3 class="dologin-title-short">
		<?php echo __( 'Login Attempts Log', 'dologin' ); ?>
	</h3>

	<div class="dologin-float-submit">
		<a href="<?php echo Util::build_url( Router::ACTION_AUTH, Auth::TYPE_CLEAR_LOG ); ?>" class="button dologin-btn-warning"><?php echo __( 'Clear records older than one month', 'dologin' ); ?></a>
	</div>
</div>

<?php echo __( 'Total', 'dologin' ) . ': ' . $count; ?>

<?php echo $pagination; ?>

<table class="wp-list-table widefat striped">
	<thead>
	<tr>
		<th>#</th>
		<th><?php echo __( 'Date', 'dologin' ); ?></th>
		<th><?php echo __( 'IP', 'dologin' ); ?></th>
		<th><?php echo __( 'GeoLocation', 'dologin' ); ?></th>
		<th><?php echo __( 'Login As', 'dologin' ); ?></th>
		<th><?php echo __( 'Gateway', 'dologin' ); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ( $list as $v ) : ?>
		<tr>
			<td><?php echo $v->id; ?></td>
			<td><?php echo Util::readable_time( $v->dateline ); ?></td>
			<td><?php echo $v->ip; ?></td>
			<td><?php echo $v->ip_geo; ?></td>
			<td><?php echo $v->username; ?></td>
			<td><?php echo $v->gateway; ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>

<?php echo $pagination; ?>