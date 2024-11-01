<?php
// ============================ Generic YD WP functions ==============================

if( !function_exists( 'yd_update_options' ) ) {
	function yd_update_options( $option_key, $number, $to_update, $fields, $prefix ) {
		$options = $newoptions = get_option( $option_key );
		foreach( $to_update as $key ) {
			$newoptions[$number][$key] = strip_tags( stripslashes( $fields[$prefix . $key . '-' . $number] ) );
			//echo $key . " = " . $prefix . $key . '-' . $number . " = " . $newoptions[$number][$key] . "<br/>";
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option( $option_key, $options );
			return TRUE;
		} else {
			return FALSE;
		}
	}
}

?>