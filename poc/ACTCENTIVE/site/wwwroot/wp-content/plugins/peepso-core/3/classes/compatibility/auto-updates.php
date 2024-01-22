<?php

class PeepSo3_Compatibility_Auto_Updates {

	private static $instance;

	public static function get_instance()
	{
		return isset(self::$instance) ? self::$instance : self::$instance = new self;
	}

	private function __construct() {

		// Disable the auto-updates text in plugin listing
		add_filter( 'plugin_auto_update_setting_html', function ( $html, $plugin_file, $plugin_data ) {

			if ( stristr( $plugin_file, 'peepso' ) ) {
				//return '';
			}

			return $html;

		}, 9999, 4 );

		// Remove any PeepSo plugins when the auto_update_plugins value is written
		// Overrides enabling via bulk actions
		add_filter( 'pre_update_site_option_auto_update_plugins', function ( $value ) {
			return self::remove_peepso($value);
		}, 9999 );

		// Remove any PeepSo plugins when the auto_update_plugins value is accessed
		// Overrides enabling when PeepSo is inactive
		add_filter('site_option_auto_update_plugins', function( $value ) {
			return self::remove_peepso( $value );
		}, 9999);
	}

	private function remove_peepso($value) {

		if ( is_array( $value ) && count( $value ) ) {
			foreach ( $value as $k => $v ) {
				if ( stristr( $v, 'peepso' ) ) {
					unset( $value[ $k ] );
				}
			}
		}

		return $value;
	}
}

if(!defined('PEEPSO_DISABLE_COMPATIBILITY_AUTO_UPDATES')) {
    PeepSo3_Compatibility_Auto_Updates::get_instance();
}