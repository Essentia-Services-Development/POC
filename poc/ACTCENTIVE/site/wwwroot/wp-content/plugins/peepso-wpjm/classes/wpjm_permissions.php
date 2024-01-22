<?php

class PeepSoWPJM_Permissions {

    private static $instance;

    public static function get_instance() {
        return self::$instance ? self::$instance : new self();
    }

    private function __construct() {

        // Hijack the WPEM shortcodes if user is not able to create / manage events
        if(!self::user_can_create()) {
            remove_shortcode('submit_job_form');
            add_shortcode('submit_job_form', function ($one, $two, $three) {
                return __('Sorry, you can\'t create job listings','peepso-wpem');
            }, 10, 3);
        }
    }

    public static function user_can_create() {
        return apply_filters('peepso_permissions_wpjm_create',(get_current_user_id()>0));
    }
}

