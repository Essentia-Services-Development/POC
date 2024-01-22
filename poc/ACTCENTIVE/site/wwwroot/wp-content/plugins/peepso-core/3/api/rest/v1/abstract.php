<?php

abstract class PeepSo3_REST_V1_Endpoint {

    protected static $instance;

    protected $input;
    protected $wpdb;

    public $state;

    public static $cred = array(
        'create'    => WP_REST_Server::CREATABLE,
        'read'      => WP_REST_Server::READABLE,
        'edit'      => WP_REST_Server::EDITABLE,
        'delete'    => WP_REST_Server::DELETABLE,
    );

    public function __construct()
    {
        $this->input = new PeepSo3_Input();
        $this->wpdb = $GLOBALS['wpdb'];
    }

    final public static function get_instance() {

        static $instances = array();

        $called_class = get_called_class();

        if (!isset($instances[$called_class]))
        {
            $instances[$called_class] = new $called_class();
        }

        return $instances[$called_class];
    }

    public function can($method) {

        if(PeepSo3_Roles::is_admin()) {
            return TRUE;
        }

        if(in_array($method, array('create','read','edit','delete'))) {
            $method = "can_$method";
            return $this->$method();
        }
    }
}