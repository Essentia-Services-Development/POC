<?php

class PeepSo3_Compatibility_Wholesale {
	private static $instance;

	public static function get_instance() {
		return isset(self::$instance) ? self::$instance : self::$instance = new self;
	}

	private function __construct()
	{
		// WWLC login redirect
		add_filter('wwlc_login_redirect_url', function($redirect_to, $user){

			if(stristr($_SERVER['REQUEST_URI'],'peepsoajax')) {
				return '';
			}

			return $redirect_to;
		}, 999, 2);
	}
}

if(!defined('PEEPSO_DISABLE_COMPATIBILITY_WHOLESALE')) {
	PeepSo3_Compatibility_Wholesale::get_instance();
}