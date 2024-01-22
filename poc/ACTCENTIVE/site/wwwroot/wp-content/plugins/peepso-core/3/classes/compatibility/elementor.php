<?php

class PeepSo3_Compatibility_Elementor {
    
    private static $instance;

    public static function get_instance() {
        return isset(self::$instance) ? self::$instance : self::$instance = new self;
    }

    private function __construct() {
		$active_plugins = get_option('active_plugins');
		$elementor_exist = FALSE;

		foreach ($active_plugins as $plugin) {
			if (stripos($plugin, 'elementor-pro') !== FALSE) {
				$elementor_exist = TRUE;
			}
		}
		
		if ($elementor_exist && strpos($_SERVER['REQUEST_URI'], 'peepsoajax') !== FALSE) {
			add_filter('pre_handle_404', function($handled, $wp_query) {
				$GLOBALS['old_post'] = $wp_query->post;
				$wp_query->post = NULL;
				return $handled;
			}, 1, 2);
	
			add_filter('pre_handle_404', function($handled, $wp_query) {
				$wp_query->post = $GLOBALS['old_post'];
				return $handled;
			}, 99, 2);
		}
    }
}

if (!defined('PEEPSO_DISABLE_COMPATIBILITY_ELEMENTOR')) {
	PeepSo3_Compatibility_Elementor::get_instance();
}