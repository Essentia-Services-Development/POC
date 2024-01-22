<?php

class PeepSo3_Block_Legacy_Widget {

    private static $instance;

    public static function get_instance()
    {
        return isset(self::$instance) ? self::$instance : self::$instance = new self;
    }

    private function __construct() {

        add_filter('peepso_fix_block_legacy_widget', function($args, $instance) {

            if(!isset($_GET['legacy-widget-preview'])) {
                return $args;
            }

//var_dump($data['instance']);
            $params = [0=>$args,'instance'=>$instance];

            $params = apply_filters('peepso_legacy_widget_preview_args', $params);
            $args = $params[0];

            $args['before_widget'] = preg_replace('/class="/', 'class="ps-widget--preview ', $args['before_widget'],1);

            return $args;
        },10,2);
    }
}

PeepSo3_Block_Legacy_Widget::get_instance();
