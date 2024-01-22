<?php

class PeepSo3_Compatibility_Wordpress_Query_Caching {
	private static $instance;

	public static function get_instance() {
		return isset(self::$instance) ? self::$instance : self::$instance = new self;
	}

	private function __construct()
	{
		// Disable caching since WP 6.1
        // Reference: https://make.wordpress.org/core/2022/10/07/improvements-to-wp_query-performance-in-6-1/
        add_action( 'parse_query', function( $wp_query){
            $wp_query->query_vars['cache_results'] = false;
        });
	}
}

if(!defined('PEEPSO_DISABLE_CACHING_WP_QUERY')) {
	PeepSo3_Compatibility_Wordpress_Query_Caching::get_instance();
}