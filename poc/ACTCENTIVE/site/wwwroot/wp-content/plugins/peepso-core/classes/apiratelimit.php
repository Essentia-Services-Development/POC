<?php

class PeepSoApiRateLimit {

    public static function check($api_name, $limit, $time_group=NULL) {

        // SETUP
        global $wpdb;
        $table = $wpdb->prefix.'peepso_api_rate_limit';

        if(NULL == $time_group) {
            $time_group = date('Y-m-d H');
        }

        // DEFAULT STATE
        $do_request = TRUE;

        // VALIDATE
        if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) return FALSE;

        $count = $wpdb->get_row("SELECT * FROM $table WHERE api_name='$api_name' AND time_group='$time_group'", ARRAY_A);

        if(!is_array($count)) {
            $count = array('api_name'=>$api_name,'count'=>1,'attempt_count'=>1, 'time_group'=>$time_group);
            $wpdb->insert($table,$count);
        }

        if($count['time_group']==$time_group && $count['count'] >= $limit) {
            $do_request = FALSE;
            $wpdb->query("UPDATE $table SET attempt_count=attempt_count+1 WHERE api_name='$api_name' AND time_group='$time_group' ");
        } elseif($count['time_group']==$time_group) {
            $wpdb->query("UPDATE $table SET count=count+1, attempt_count=attempt_count+1  WHERE api_name='$api_name' AND time_group='$time_group' ");
        }

        return $do_request;
    }

    public static function clear() {
        global $wpdb;
        $table = $wpdb->prefix.'peepso_api_rate_limit';
        $wpdb->query("DELETE FROM $table");
    }
}