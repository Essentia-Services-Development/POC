<?php

if(!class_exists('PeepSo3_Site_Health')) {
    require_once(dirname(__FILE__) . '/site_health.php');
    //new PeepSoError('Autoload issue: PeepSo3_Site_health not found ' . __FILE__);
}

class PeepSo3_Site_Health_Memory extends PeepSo3_Site_Health {

    public function test() {

        // Uncomment to force the error
        // $override = '32M';

        $test = ini_get('memory_limit');
        if(isset($override)) {
            $test = $override;
        }

        $condition = PeepSoSystemRequirements::MEMORY_REQUIRED;

        $this->label = sprintf(__('The required memory limit is %s - your server has %s','peepso-core'), $condition, $test);

        if( $test != -1 && $this->get_bytes($test) < $this->get_bytes($condition)) {
            $this->status = 'critical';
        }

        return $this->result();
    }

    private function get_bytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);

        $val = (int) $val;

        switch($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        return $val;
    }
}

PeepSo3_Site_Health_Memory::get_instance();