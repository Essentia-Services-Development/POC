<?php

class PeepSo3_Compatibility_PMP {

    private static $instance;

    public static function get_instance() {
        return isset(self::$instance) ? self::$instance : self::$instance = new self;
    }

    // #5696 dequeue PMP admin scripts to avoid them breaking our config panels
    private function __construct() {

        add_action('init', function() {
            if(isset($_REQUEST['page']) && stristr($_REQUEST['page'],'peepso')) {
                remove_action( 'admin_enqueue_scripts', 'pmpro_admin_enqueue_scripts' );
            }
        });

    }
}

PeepSo3_Compatibility_PMP::get_instance();