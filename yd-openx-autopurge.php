<?php
/**
 * @package YD_OpenX-Autopurge
 * @author Yann Dubois
 * @version 0.1.0
 */

/*
 Plugin Name: YD OpenX Autopurge
 Plugin URI: http://www.yann.com/wp-plugins/yd-openx-autopurge
 Description: Uses WP "pseudo-cron" system to automatically purge OpenX statistics data.
 Author: Yann Dubois
 Version: 0.1.0
 Author URI: http://www.yann.com/
 */

/**
 * @copyright 2009  Yann Dubois  ( email : yann _at_ abc.fr )
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
/**
 Revision 0.1.0:
 - First beta release
 */
/**
 *	TODO:
 *  - Test, debug, final release
 */

/** Plugin install **/
function yd_oa_plugin_install() {
	if ( !wp_next_scheduled( 'yd_daily_openx_purge_hook' ) ) {
		wp_schedule_event( 
			mktime( 0, 0, 0 ), 
			'daily', 
			'yd_daily_openx_purge_hook' 
		);
	}
}
register_activation_hook(__FILE__, 'yd_oa_plugin_install');
add_action( 'yd_daily_openx_purge_hook', 'yd_daily_openx_purge' ); 


/** Create Text Domain For Translations **/
add_action('init', 'yd_oa_plugin_textdomain');
function yd_oa_plugin_textdomain() {
	$plugin_dir = basename(dirname(__FILE__));
	load_plugin_textdomain(
		'yd-openx-autopurge', 
		'wp-content/plugins/' . $plugin_dir, $plugin_dir 
	);
}

/** Create custom admin menu page **/
add_action('admin_menu', 'yd_oa_plugin_menu');
function yd_oa_plugin_menu() {
	add_options_page(
	__('YD OpenX Autopurge',
		'yd-openx-autopurge'), 
	__('YDOpenX', 'yd-openx-autopurge'),
	8,
	__FILE__,
		'yd_oa_plugin_options'
		);
}
function yd_oa_plugin_options() {
	$options = get_option( 'yd_openx_autopurge' );
	$i = 0;
	echo '<div class="wrap">';
	echo '<h2>YD OpenX Autopurge</h2>';
	echo '<div style="float:right;">'
	. '<img src="http://www.yann.com/yd-openx-autopurge-v010-logo.gif" alt="YD logo" />'
	. '</div>';
	echo __('Server time is now:', 'yd-openx-autopurge') . ' ' .
		date( __('j F y, G:i:s'), time() ) . '<br/>';
	echo __('The auto-purge is scheduled to run next around:', 'yd-openx-autopurge') . ' ' .
		date( __('j F y, G:i:s'), wp_next_scheduled( 'yd_daily_openx_purge_hook' ) ) . '<br/>';
	if( isset( $options[$i]["last_run"] ) && $options[$i]["last_run"] > 0 ) {
		echo __('The purge was last performed on:', 'yd-openx-autopurge') . ' ' .
			date( __('j F y, G:i:s'), $options[$i]["last_run"] ) . '<br/>';
	}
	if( isset( $_GET["do"] ) ) {
		echo '<p>' . __('Action:', 'yd-openx-autopurge') . ' '
		. __('I should now', 'yd-openx-autopurge') . ' ' . $_GET["do"] . '.</p>';
		if(			$_GET["do"] == __('Purge now', 'yd-openx-autopurge') ) {
			//yd_oa_plugin_update_options();
			yd_purge_openx();
			echo '<p>' . __('OpenX data has been purged', 'yd-openx-autopurge') . '</p>';
		} elseif(	$_GET["do"] == __('Update options', 'yd-openx-autopurge') ) {
			if( yd_oa_plugin_update_options() ) {
				echo '<strong style="color:#0C0">' .
					__( 'Database connexion OK! - Options updated OK!', 'yd-openx-autopurge' ) .
					'</strong><br/>';
			} else {
				echo '<strong style="color:#F00">' .
					__( 'Could not connect to database, please check your settings.', 'yd-openx-autopurge' ) .
					'</strong><br/>';
			}
		} else {
			echo '<strong style="color:#F00">' .
				__( 'Error: Unknown action!', 'yd-openx-autopurge' ) .
				'</strong><br/>';
		}
	} else {
		echo '<p>'
		. '<a href="http://www.yann.com/wp-plugins/yd-openx-autopurge" target="_blank" title="Plugin FAQ">';
		echo __('Welcome to YD OpenX Autopurge Admin Page.', 'yd-openx-autopurge')
		. '</a></p>';
	}
	echo '</div>';
	$options = get_option( 'yd_openx_autopurge' );	// need to fetch options again 
													//in case they've been updated!
	yd_show_ox_db_size( $options, $i );
	//---
	echo '<div class="wrap">';
	echo '<form method="get">';
	echo __( 'Nb. of days to keep:', 'yd-openx-autopurge' ) .
		'<input type="text" name="yd_oa-days-0" value="' . $options[$i]["days"] . '" ';
	echo "><br />";
	echo __( 'Server name:', 'yd-openx-autopurge' ) .
		'<input type="text" name="yd_oa-server_name-0" value="' . $options[$i]["server_name"] . '" ';
	echo "><br />";
	echo __( 'Database name:', 'yd-openx-autopurge' ) .
		'<input type="text" name="yd_oa-db_name-0" value="' . $options[$i]["db_name"] . '" ';
	echo "><br />";
	echo __( 'Database login:', 'yd-openx-autopurge' ) .
		'<input type="text" name="yd_oa-db_login-0" value="' . $options[$i]["db_login"] . '" ';
	echo "><br />";
	echo __( 'Database password:', 'yd-openx-autopurge' ) .
		'<input type="password" name="yd_oa-db_pass-0" value="' . $options[$i]["db_pass"] . '" ';
	echo "><br />";
	echo __( 'Scheduled daily auto-purge time (hh:mm:ss):', 'yd-openx-autopurge' );
	echo '<select name="yd_oa-hour-0">';
	for( $j=0; $j<24; $j++ ) {
		echo '<option value="' . sprintf( "%02d", $j) . '" ';
		if( $j == $options[$i]["hour"] ) echo ' selected="selected" ';
		echo '>' . sprintf( "%02d", $j) . '</option>';
	}
	echo '</select>';
	echo ':';
	echo '<select name="yd_oa-minute-0">';
	for( $j=0; $j<60; $j++ ) {
		echo '<option value="' . sprintf( "%02d", $j) . '" ';
		if( $j == $options[$i]["minute"] ) echo ' selected="selected" ';
		echo '>' . sprintf( "%02d", $j) . '</option>';
	}
	echo '</select>';
	echo ':';	
	echo '<select name="yd_oa-second-0">';
	for( $j=0; $j<60; $j++ ) {
		echo '<option value="' . sprintf( "%02d", $i) . '" ';
		if( $j == $options[$i]["second"] ) echo ' selected="selected" ';
		echo '>' . sprintf( "%02d", $j) . '</option>';
	}
	echo '</select>';
	echo '<br/>';
	echo '<input type="submit" name="do" value="' . __('Update options', 'yd-openx-autopurge') . '"><br/>';
	echo '<input type="submit" name="do" value="' . __('Purge now', 'yd-openx-autopurge') . '"><br/>';
	echo '<input type="hidden" name="page" value="' . $_GET["page"] . '">';
	echo '</form></div>';
	//---
}

function yd_show_ox_db_size( $options, $i ) {
	$oxdb = new wpdb( 
		$options[$i]['db_login'],
		$options[$i]['db_pass'],
		$options[$i]['db_name'],
		$options[$i]['server_name']
	);
	$res = $oxdb->get_results( "SHOW TABLE STATUS", ARRAY_A );
	foreach( $res as $array ) {
		//echo $array['Name'] . ' - ' . $array['Data_length'] . '<br/>';
		if( ! preg_match( "/^ox_/", $array['Name'] ) ) continue;
		$total = $total + $array['Data_length'] + $array['Index_length'];
	}
	$totalk = ( $total / 1024 );
	$totalm = ( $totalk / 1024 );
	$totalg = ( $totalm / 1024 );
	echo '<div class="wrap">' .
		__( 'Total OpenX database size (bytes):', 'yd-openx-autopurge' ) . 
		' <strong>' . number_format( $total );
	if( $totalk > 1 ) echo ' = (' . number_format( $totalk, 2 ) . ' KB)';
	if( $totalm > 1 ) echo ' = (' . number_format( $totalm, 2 ) . ' MB)';
	if( $totalg > 1 ) echo ' = (' . number_format( $totalg, 2 ) . ' GB)';
	echo '</strong><br/></div>';
}

/** Update options of the admin page **/
function yd_oa_plugin_update_options(){
	$to_update = Array(
		'days',
		'server_name',
		'db_name',
		'db_login',
		'db_pass',
		'hour',
		'minute',
		'second'
	);
	$oxdb = new wpdb( 
		$_GET['yd_oa-db_login-0'],
		$_GET['yd_oa-db_pass-0'],
		$_GET['yd_oa-db_name-0'],
		$_GET['yd_oa-server_name-0']
	);
	$tbl = $oxdb->get_col( "SHOW TABLES;" );
	if( !is_array( $tbl ) || empty( $tbl ) || count( $tbl )==0 ) return FALSE;
	if( !preg_match( "/^[0-9]+$/", $_GET["yd_oa-days-0"] ) ) $_GET["yd_oa-days-0"] = 60;
	yd_update_options( 'yd_openx_autopurge', 0, $to_update, $_GET, 'yd_oa-' );
	if ( !wp_next_scheduled( 'yd_daily_openx_purge_hook' ) ) {
		wp_schedule_event( 
			mktime( $_GET["yd_oa-hour-0"], $_GET["yd_oa-minute-0"], $_GET["yd_oa-second-0"] ), 
			'daily', 
			'yd_daily_openx_purge_hook' 
		);
	} else {
		wp_clear_scheduled_hook( 'yd_daily_openx_purge_hook' );
		wp_schedule_event( 
			mktime( $_GET["yd_oa-hour-0"], $_GET["yd_oa-minute-0"], $_GET["yd_oa-second-0"] ), 
			'daily', 
			'yd_daily_openx_purge_hook' 
		);
	}
	return TRUE;
}

function yd_daily_openx_purge() {
	yd_purge_openx( FALSE );
}

/** Actual Purge function **/
function yd_purge_openx( $echo=TRUE ) {

	if( $echo ) echo "Purge ox...<br/>\n";
	
	$options = get_option( 'yd_openx_autopurge' );
	$i = 0;
	$oxdb = new wpdb( 
		$options[$i]['db_login'],
		$options[$i]['db_pass'],
		$options[$i]['db_name'],
		$options[$i]['server_name']
	);
	
	// ---
	
	if( $echo ) echo "Purge ox_data_intermediate_ad...";
	$query = "
		SELECT count( data_intermediate_ad_id ) 
		FROM ox_data_intermediate_ad 
		WHERE day < SUBDATE( NOW(), INTERVAL " . $options[$i]['days'] . " DAY );
	";
	$nb = $oxdb->get_var( $query );
	if( $echo ) echo "$nb records to erase<br/>\n";
	$query = "
		DELETE FROM
			ox_data_intermediate_ad
		WHERE
			day < SUBDATE( NOW(), INTERVAL " . $options[$i]['days'] . " DAY );
	";
	$oxdb->query( $query );
	$oxdb->query( "OPTIMIZE TABLE `ox_data_intermediate_ad`;" );
	if( $echo ) echo "Ok.<br/>\n";
	
	// ---
	
	if( $echo ) echo "Purge ox_data_summary_ad_hourly...";
	$query = "
		SELECT count( data_summary_ad_hourly_id ) 
		FROM ox_data_summary_ad_hourly 
		WHERE day < SUBDATE( NOW(), INTERVAL " . $options[$i]['days'] . " DAY );
	";
	$nb = $oxdb->get_var( $query );
	if( $echo ) echo "$nb records to erase<br/>\n";
	$query = "
		DELETE FROM
			ox_data_summary_ad_hourly
		WHERE
			day < SUBDATE( NOW(), INTERVAL " . $options[$i]['days'] . " DAY );
	";
	$oxdb->query( $query );
	$oxdb->query( "OPTIMIZE TABLE `ox_data_summary_ad_hourly`;" );
	if( $echo ) echo "Ok.<br/>\n";
	
	// ---
	
	if( $echo ) echo "Purge ox_log_maintenance_priority...";
	$query = "
		SELECT count( log_maintenance_priority_id ) 
		FROM ox_log_maintenance_priority 
		WHERE start_run < SUBDATE( NOW(), INTERVAL " . $options[$i]['days'] . " DAY );
	";
	$nb = $oxdb->get_var( $query );
	if( $echo ) echo "$nb records to erase<br/>\n";
	$query = "
		DELETE FROM
			ox_log_maintenance_priority
		WHERE
			start_run < SUBDATE( NOW(), INTERVAL " . $options[$i]['days'] . " DAY );
	";
	$oxdb->query( $query );
	$oxdb->query( "OPTIMIZE TABLE `ox_log_maintenance_priority`;" );
	if( $echo ) echo "Ok.<br/>\n";
	
	// ---
	
	if( $echo ) echo "Purge ox_log_maintenance_statistics...";
	$query = "
		SELECT count( log_maintenance_statistics_id ) 
		FROM ox_log_maintenance_statistics 
		WHERE start_run < SUBDATE( NOW(), INTERVAL " . $options[$i]['days'] . " DAY );
	";
	$nb = $oxdb->get_var( $query );
	if( $echo ) echo "$nb records to erase<br/>\n";
	$query = "
		DELETE FROM
			ox_log_maintenance_statistics
		WHERE
			start_run < SUBDATE( NOW(), INTERVAL " . $options[$i]['days'] . " DAY );
	";
	$oxdb->query( $query );
	$oxdb->query( "OPTIMIZE TABLE ox_log_maintenance_statistics;" );
	if( $echo ) echo "Ok.<br/>\n";
	
	// ---
	
	if( $echo ) echo "Purge ox_userlog...";
	$query = "
		SELECT count( userlogid ) 
		FROM ox_userlog 
		WHERE timestamp < " . ( time() - ( 60 * 60 * 24 * $options[$i]['days'] ) ) . ";
	";
	$nb = $oxdb->get_var( $query );
	if( $echo ) echo "$nb records to erase (" . ( time() - ( 60 * 60 * 24 * $options[$i]['days'] ) ) . ")<br/>\n";
	$query = "
		DELETE FROM
			ox_userlog
		WHERE
			timestamp < " . ( time() - ( 60 * 60 * 24 * $options[$i]['days'] ) ) . ";
	";
	$oxdb->query( $query );
	$oxdb->query( "OPTIMIZE TABLE ox_userlog;" );
	if( $echo ) echo "Ok.<br/>\n";
	
	// ---
	
	if( $echo ) echo "optimizing tables...<br/>\n";
	$tbl = $oxdb->get_col( "SHOW TABLES;" );
	foreach( $tbl as $table ) {
		if( $echo ) echo $table . "<br/>\n";
		$oxdb->query( "OPTIMIZE TABLE " . $table . ";" );
	}
	
	// ---
	
	$val["yd_oa-last_run-0"] = time();
	$to_update = Array(
		'last_run'
	);
	yd_update_options( 'yd_openx_autopurge', 0, $to_update, $val, 'yd_oa-' );	
	
	if( $echo ) echo "Finished.<br/>\n";

}

// ============================ Generic YD WP functions ==============================

include( 'yd-wp-lib.inc.php' );

?>