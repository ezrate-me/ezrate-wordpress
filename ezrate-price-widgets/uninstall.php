<?php
/**
 * Uninstall: remove the plugin's options and cached data (unless the site owner turned «delete on uninstall» off).
 *
 * @package EZRate_Price_Widgets
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$ezrpw_settings = get_option( 'ezrpw_settings', array() );
if ( is_array( $ezrpw_settings ) && isset( $ezrpw_settings['cleanup'] ) && ! $ezrpw_settings['cleanup'] ) {
	return;
}
foreach ( array( 'ezrpw_widgets', 'ezrpw_settings', 'ezrpw_last_data', 'ezrpw_status' ) as $ezrpw_option ) {
	delete_option( $ezrpw_option );
}
foreach ( array( 'ezrpw_data', 'ezrpw_fetch_lock', 'ezrpw_fetch_backoff', 'ezrpw_manual_refresh' ) as $ezrpw_transient ) {
	delete_transient( $ezrpw_transient );
}
wp_clear_scheduled_hook( 'ezrpw_refresh_event' );
