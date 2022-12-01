<div class="piotnetforms-dashboard__title"><?php echo __( 'License', 'piotnetforms' ); ?></div>
<div class="piotnetforms-dashboard__item-content">
    <div class="piotnetforms-license__description">
        <?php
		if ( !empty( $message ) ) {
			?>
            <div class="piotnetforms-license__description">Status: <?php echo $message; ?></div>
            <?php
		}
?>

    <?php if ( !$has_key ) {
    	$home_url = urlencode( get_option( 'home' ) );
    	$active_nonce = wp_create_nonce( 'active_nonce' );
    	$redirect_url =  urlencode( get_admin_url( null, "admin.php?page=piotnetforms&action=active_license&nonce=$active_nonce" ) );
    	$active_url = Piotnetforms_License_Service::HOMEPAGE_URL . '/dashboard/active/?type=2&pluginId=2&v=' . PIOTNETFORMS_PRO_VERSION ."&h={$home_url}&r={$redirect_url}"; ?>
        <p><?php _e( 'Please activate your license to enable all features and receive new updates.', 'piotnetforms' ); ?></p>
        <br>
        <a class="button button-secondary" href="<?php echo $active_url; ?>" target="_blank"><?php _e( 'Activate', 'piotnetforms' ); ?></a>
        <?php
    }
?>
    </div>
    <?php

if ( $has_key ) {
	$license_status = isset( $license_data['status'] ) ? $license_data['status'] : 'INVALID';
	$license_display_name = isset( $license_data['displayName'] ) ? $license_data['displayName'] : 'Noname';

	if ( $license_status == 'VALID' && isset( $license_data['expiredAt'] ) ) {
		if ( $license_data['expiredAt'] === false ) {
			$license_status = "CAN'T GET THE EXPIRED DATE";
		} elseif ( new DateTime() > $license_data['expiredAt'] ) {
			$license_status = 'EXPIRED';
		}
	}

	if ( $license_status == 'VALID' ) {
		$status_html = '<strong><a style="color:green">' . $license_status . '</a></strong>';
	} else {
		$license_status = str_replace( '_', ' ', $license_status );
		$status_html = '<strong><a style="color:red">' . $license_status . '</a></strong>';
	} ?>
        <div class="piotnetforms-license__description">
            License status: <?php echo $status_html; ?></strong><br>
            Connected as: <div class="piotnetforms-tooltip">
                <strong><?php echo $license_display_name; ?></strong>
                <span class="piotnetforms-tooltiptext">You can change display name in <strong>Manage Licenses</strong></span>
            </div>
            <br>
        </div>

        <form method="post" action="#">
            <a class="button button-secondary" href="<?php echo Piotnetforms_License_Service::HOMEPAGE_URL . '/dashboard/licenses'?>" target="_blank" style="margin-right: 5px"><?php _e( 'Manage licenses', 'piotnetforms' ); ?></a>
            <input type="submit" name="submit" id="submit" class="button button-primary" value="Remove license">
            <input type="hidden" name="action" value="remove_license">
            <br>
        </form>
        <?php
}?>
</div>
