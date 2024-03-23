<?php
namespace dologin;
defined( 'WPINC' ) || exit;

?>
<div class="dologin-relative">
	<h3 class="dologin-title-short">
		<?php echo __( 'Passwordless Login', 'dologin' ); ?>
	</h3>

	<div class="dologin-float-submit">
		<a href="users.php" class="button button-primary "><?php echo __( 'Generate Links in Users List', 'dologin' ); ?></a>
	</div>
</div>

<table class="wp-list-table widefat striped">
	<thead>
	<tr>
		<th>#</th>
		<th><?php echo __( 'Date', 'dologin' ); ?></th>
		<th><?php echo __( 'User', 'dologin' ); ?></th>
		<th><?php echo __( 'Link', 'dologin' ); ?></th>
		<th><?php echo __( 'Created By', 'dologin' ); ?></th>
		<th><?php echo __( 'Count', 'dologin' ); ?></th>
		<th><?php echo __( 'Last Used At', 'dologin' ); ?></th>
		<th><?php echo __( 'Expired At', 'dologin' ); ?></th>
		<th><?php echo __( 'One Time Usage', 'dologin' ); ?></th>
		<th><?php echo __( 'Status', 'dologin' ); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ( $this->pswdless_log() as $v ) : ?>
		<tr>
			<td><?php echo $v->id; ?></td>
			<td><?php echo Util::readable_time( $v->dateline ); ?></td>
			<td><?php echo $v->username; ?></td>
			<td><span class="dologin_pswd_link dologin_tt dologin_tt--success" data-title="<?php echo __( 'Click to copy', 'dologin' ); ?>"><?php echo $v->link; ?></span></td>
			<td><?php echo $v->src; ?></td>
			<td><?php echo $v->count; ?></td>
			<td><?php echo $v->last_used_at ? Util::readable_time( $v->last_used_at ) : '-'; ?></td>
			<td>
				<?php echo $v->expired_at > time() ? Util::readable_time( $v->expired_at - time(), 3600, false ) : '<font color="red">Expired</font>'; ?>

				<a href="<?php echo Util::build_url( Router::ACTION_PSWD, Pswdless::TYPE_EXPIRE_7, false, null, array( 'dologin_id' => $v->id ) ); ?>" class="button button-primary"><?php echo __( '+7 Days', 'dologin' ); ?></a>
			</td>
			<td>
				<?php echo $v->onetime ? '<font color="green">Yes</font>' : '<font color="red">No</font>'; ?>
				<a href="<?php echo Util::build_url( Router::ACTION_PSWD, Pswdless::TYPE_TOGGLE_ONETIME, false, null, array( 'dologin_id' => $v->id ) ); ?>"><span class="dashicons dashicons-controls-repeat"></span></a>
			</td>
			<td>
				<a href="<?php echo Util::build_url( Router::ACTION_PSWD, Pswdless::TYPE_LOCK, false, null, array( 'dologin_id' => $v->id ) ); ?>"><?php echo $v->active ? '<span class="dashicons dashicons-unlock"></span>' : '<span class="dashicons dashicons-lock"></span>'; ?></a>
				<?php
				if ( $v->active == 1 ) :
					echo '<font color="green">' . __( 'Active', 'dologin') . '</font>';
				else :
					echo '<font color="red">' . __( 'Disabled', 'dologin') . '</font>';
				endif;
				?>
				<a href="<?php echo Util::build_url( Router::ACTION_PSWD, Pswdless::TYPE_DEL, false, null, array( 'dologin_id' => $v->id ) ); ?>" class="dologin-right"><span class="dashicons dashicons-dismiss"></span></a>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>


<p class="description"><?php echo __( 'Here you can generate login links and manage them.', 'dologin' ); ?></p>
<p class="dologin-success">
	<strong>= API =</strong>
	<p>* <?php echo sprintf( __( 'Call the function %s to generate one passwordless login link for the current user.', 'dologin' ), '<code>$link = function_exists( \'dologin_gen_link\' ) ? dologin_gen_link( \'your plugin name or tag\' ) : \'\';</code>' ); ?></p>

	<p>* <?php echo sprintf( __( 'Call the function %1$s to generate a passwordless login link for the user which ID is %2$s.', 'dologin' ), '<code>$link = function_exists( \'dologin_gen_link\' ) ? dologin_gen_link( \'note/tip for this generation\', $user_id ) : \'\';</code>', '<code>$user_id</code>' ); ?></p>

	<p><?php echo __( 'The generated one-time used link will be expired after 7 days.', 'dologin' ); ?></p>

	<p>* <?php echo sprintf( __( 'Define const %s to avoid redirecting to setting page after installtion.', 'dologin' ), '<code>SILENCE_INSTALL</code>' ); ?></p>

</p>

<p class="dologin-success">
	<strong>= CLI =</strong>

	<p>* <?php echo __( 'List all passwordless links', 'dologin' ); ?>: <code>wp dologin list</code></p>

	<p>* <?php echo sprintf( __( 'Generate a passwordless link for one username (for the login name %s)', 'dologin' ), '<code>root</code>' ); ?>: <code>wp dologin gen root</code></p>

	<p>* <?php echo sprintf( __( 'Delete a passwordless link w/ the ID in list (for the record w/ ID %s)', 'dologin' ), '<code>5</code>' ); ?>: <code>wp dologin del 5</code></p>
</p>
