<?php

if(!function_exists('human_time_diff_round_alt')) {

	function human_time_diff_round_alt( $from, $to = '', $round= 'floor' ) {

		if ( empty( $to ) ) {
			$to = time();
		}

		$diff = (int) abs( $to - $from );

		if ( $diff < HOUR_IN_SECONDS ) {
			$mins = round( $diff / MINUTE_IN_SECONDS );
			if ( $mins <= 1 )
				$mins = 1;
			/* translators: min=minute */
			$since = sprintf( _n( '%s min', '%s mins', $mins, 'peepso-theme-gecko' ), $mins );
		} elseif ( $diff < DAY_IN_SECONDS && $diff >= HOUR_IN_SECONDS ) {
			$hours = $round( $diff / HOUR_IN_SECONDS );
			if ( $hours <= 1 )
				$hours = 1;
			$since = sprintf( _n( '%s hour', '%s hours', $hours, 'peepso-theme-gecko' ), $hours );
		} elseif ( $diff < WEEK_IN_SECONDS && $diff >= DAY_IN_SECONDS ) {
			$days = $round( $diff / DAY_IN_SECONDS );
			if ( $days <= 1 )
				$days = 1;
			$since = sprintf( _n( '%s day', '%s days', $days, 'peepso-theme-gecko' ), $days );
		} elseif ( $diff < 30 * DAY_IN_SECONDS && $diff >= WEEK_IN_SECONDS ) {
			$weeks = $round( $diff / WEEK_IN_SECONDS );
			if ( $weeks <= 1 )
				$weeks = 1;
			$since = sprintf( _n( '%s week', '%s weeks', $weeks, 'peepso-theme-gecko' ), $weeks );
		} elseif ( $diff < YEAR_IN_SECONDS && $diff >= 30 * DAY_IN_SECONDS ) {
			$months = $round( $diff / ( 30 * DAY_IN_SECONDS ) );
			if ( $months <= 1 )
				$months = 1;
			$since = sprintf( _n( '%s month', '%s months', $months, 'peepso-theme-gecko' ), $months );
		} elseif ( $diff >= YEAR_IN_SECONDS ) {
			$years = $round( $diff / YEAR_IN_SECONDS );
			if ( $years <= 1 )
				$years = 1;
			$since = sprintf( _n( '%s year', '%s years', $years, 'peepso-theme-gecko' ), $years );
		}
		return $since;
	}
}

class GeckoAppHelper
{
    public static function is_app($section='')
    {
        $method = 'is_app';
        if(strlen($section)) $method.= "_".$section;

//        echo "method: $method<br>";
//        echo "PeepSo:".intval(class_exists('PeepSo'))."<br>";
//        echo "PeepSoAppHelper:".intval(class_exists('PeepSoAppHelper'))."<br>";
//        echo "is_callable:".intval(is_callable('PeepSoAppHelper', $method) )."<br>";

        return (class_exists('PeepSo') && class_exists('PeepSoAppHelper') && is_callable('PeepSoAppHelper', $method) && PeepSoAppHelper::$method());
    }
}