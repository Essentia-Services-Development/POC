<?php

if(!class_exists('PeepSo3_Site_Health')) {
    require_once(dirname(__FILE__) . '/site_health.php');
    //new PeepSoError('Autoload issue: PeepSo3_Site_health not found ' . __FILE__);
}

class PeepSo3_Site_Health_Session extends PeepSo3_Site_Health {

    public function test() {

        // Uncomment to force the error
        // $override = 0;

        $test = session_status();
        if(isset($override)) {
            $test = $override;
        }

        $this->label = __('PHP session support is required','peepso-core');

        if(PHP_SESSION_DISABLED == $test) {
            $this->status = 'critical';
        }

        return $this->result();
    }
}

PeepSo3_Site_Health_Session::get_instance();