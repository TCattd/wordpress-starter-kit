<?php
namespace dologin;
defined( 'WPINC' ) || exit;


$menu_list = array(
	'setting'	=> __( 'Settings', 'dologin' ),
	'pswdless'	=> __( 'Passwordless Login', 'dologin' ),
	'log'		=> __( 'Login Attempts Log', 'dologin' ),
);

?>
<div class="wrap dologin-settings">
	<h1 class="dologin-h1">
		<?php echo __( 'DoLogin Security', 'dologin' ); ?>
	</h1>
	<span class="dologin-desc">
		v<?php echo Core::VER; ?>
	</span>
	<hr class="wp-header-end">
</div>

<div class="dologin-wrap">
	<h2 class="dologin-header nav-tab-wrapper">
	<?php
		$i = 1;
		foreach ($menu_list as $tab => $val){
			$accesskey = $i <= 9 ? "dologin-accesskey='$i'" : '';
			echo "<a class='dologin-tab nav-tab' href='#$tab' data-dologin-tab='$tab' $accesskey>$val</a>";
			$i ++;
		}
	?>
	</h2>

	<div class="dologin-body">
	<?php
		// include all tpl for faster UE
		foreach ($menu_list as $tab => $val) {
			echo "<div data-dologin-layout='$tab'>";
			require DOLOGIN_DIR . "tpl/$tab.tpl.php";
			echo "</div>";
		}
	?>
	</div>

</div>

<h2 style="margin: 30px;">
	<a href="https://wordpress.org/support/plugin/dologin/reviews/?rate=5#new-post" target="_blank"><?php echo __( 'Rate Us!' ); ?>
		<span class="wporg-ratings rating-stars" style="text-decoration: none;">
			<span class="dashicons dashicons-star-filled" style="color:#ffb900 !important;"></span><span class="dashicons dashicons-star-filled" style="color:#ffb900 !important;"></span><span class="dashicons dashicons-star-filled" style="color:#ffb900 !important;"></span><span class="dashicons dashicons-star-filled" style="color:#ffb900 !important;"></span><span class="dashicons dashicons-star-filled" style="color:#ffb900 !important;"></span>
		</span>
	</a>
</h2>