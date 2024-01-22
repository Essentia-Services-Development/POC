<?php

if(!class_exists('PeepSo3_Site_Health')) {
    require_once(dirname(__FILE__) . '/site_health.php');
    //new PeepSoError('Autoload issue: PeepSo3_Site_health not found ' . __FILE__);
}

class PeepSo3_Site_Health_MySQL extends PeepSo3_Site_Health {

    public function test() {

        // Uncomment to force the error
        // $override = '4.0';

        global $wpdb;

        $test = $wpdb->db_version();
        if(isset($override)) {
            $test = $override;
        }

        $condition = PeepSoSystemRequirements::MYSQL_REQUIRED;

        $this->label = sprintf(__('The minimum required MySQL version is %s - your server has %s','peepso-core'),$condition, $test);

        // Test required
        if($wpdb->is_mysql && !stristr($wpdb->dbh->server_info, 'maria') && version_compare($test, $condition ) < 0) {
            $this->status = 'critical';
        }

        return $this->result();
    }
}

PeepSo3_Site_Health_MySQL::get_instance();