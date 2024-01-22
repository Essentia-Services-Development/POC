<?php

if(!class_exists('PeepSo3_Compatibility_Advanced_Post_Cache')) {
    class PeepSo3_Compatibility_Advanced_Post_Cache
    {
        private static $instance;

        public static function get_instance()
        {
            return isset(self::$instance) ? self::$instance : self::$instance = new self;
        }

        private function __construct()
        {
            // Disable Advanced Post Cache for anything PeepSo
            add_action('advanced_post_cache_skip_for_post_type', function ($skip, $post_types) {

                if (!is_array($post_types)) {
                    $post_types = [$post_types];
                }

                foreach ($post_types as $post_type) {
                    if (stristr($post_type, 'peepso')) {
                        $skip = TRUE;
                        break;
                    }
                }

                return $skip;
            }, 2, 999);
        }
    }

    if (!defined('PEEPSO_DISABLE_COMPATIBILITY_ADVANCED_POST_CACHE')) {
        PeepSo3_Compatibility_Advanced_Post_Cache::get_instance();
    }
}