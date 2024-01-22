<?php

if(!class_exists('PeepSo3_DBDelta')) {

    class PeepSo3_DBDelta
    {

        public static function _($table, $version, $sql)
        {

            @include_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            if (!function_exists('dbDelta')) {
                new PeepSoError("dbDelta() not found");
                return;
            }

            // Run dbDelta() once in a while no matter what
            $override = (rand(1, 100) == 1) ? TRUE : FALSE;

            global $wpdb;
            $version = PeepSo::PLUGIN_VERSION . PeepSo::PLUGIN_RELEASE . '-' . $version;
            $charset_collate = $wpdb->get_charset_collate();

            $sql .= " ENGINE=InnoDB $charset_collate;";

            if (get_option($table) != $version || $override) {
                dbDelta($sql);
                update_option($table, $version);
            }
        }
    }

}