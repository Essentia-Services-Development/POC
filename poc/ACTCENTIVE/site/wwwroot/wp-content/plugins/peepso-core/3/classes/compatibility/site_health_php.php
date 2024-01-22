<?php

if(!class_exists('PeepSo3_Site_Health')) {
    require_once(dirname(__FILE__) . '/site_health.php');
    //new PeepSoError('Autoload issue: PeepSo3_Site_health not found ' . __FILE__);
}

class PeepSo3_Site_Health_PHP extends PeepSo3_Site_Health {

    public function test() {

        // Uncomment to force the error
        // $override = '7.2.0';

        $test = PHP_VERSION;
        if(isset($override)) {
            $test = $override;
        }

        $condition = PeepSoSystemRequirements::PHP_RECOMMENDED;

        $this->label = sprintf(__('The recommended PHP version is %s or higher - your server has %s','peepso-core'), $condition, $test);

        // Test recommended
        if(version_compare($test, $condition) < 0) {
            $this->status = 'recommended';
        }

        return $this->result();
    }
}

PeepSo3_Site_Health_PHP::get_instance();