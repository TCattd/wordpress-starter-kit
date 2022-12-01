<?php

if( !defined( 'ABSPATH' ) ) exit();

/**
 * @var $this Limit_Login_Attempts
 */

$debug_info = '';

$ips = $server = array();

foreach ($_SERVER as $key => $value) {

	if( in_array( $key, array( 'SERVER_ADDR' ) ) || is_array( $value ) ) continue;

	$ips_for_check = array_map( 'trim', explode( ',', $value ) );
	foreach ( $ips_for_check as $ip ) {

		if( $this->is_ip_valid( $ip ) ) {

			if( !in_array( $ip, $ips ) ) {
				$ips[] = $ip;
			}

			if( !isset( $server[$key] ) ) {
				$server[$key] = '';
            }

			if( in_array( $ip, array( '127.0.0.1', '0.0.0.0' ) ) )
				$server[$key] = $ip;
			else
				$server[$key] .= 'IP'.array_search( $ip, $ips ) . ',';
		}
    }
}

foreach ($server as $server_key => $ips ) {
	$debug_info .= $server_key . ' = ' . trim( $ips, ',' ) . "\n";
}
?>

<table class="form-table">
	<tr>
		<th scope="row" valign="top"><?php echo __( 'Debug info', 'limit-login-attempts-reloaded' ); ?></th>
		<td>
			<textarea cols="70" rows="10" onclick="this.select()" readonly><?php echo esc_textarea($debug_info); ?></textarea>
			<p class="description"><?php _e( 'Copy the contents of the window and provide to support.', 'limit-login-attempts-reloaded' ); ?></p>
		</td>
	</tr>
</table>
