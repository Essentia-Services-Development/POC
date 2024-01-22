<?php

if(!class_exists('PeepSo3_Site_Health')) {
    require_once(dirname(__FILE__) . '/site_health.php');
    //new PeepSoError('Autoload issue: PeepSo3_Site_health not found ' . __FILE__);
}

class PeepSo3_Site_Health_MB_Str extends PeepSo3_Site_Health {

    public function test() {

        // Uncomment to force the error
        // $override = FALSE;

        $test = (function_exists('mb_substr') && function_exists('mb_strlen')) ? TRUE : FALSE;
        if(isset($override)) {
            $test = $override;
        }

        $this->label = __('PHP functions mb_substr and mb_strlen are recommended for accurate text processing', 'peepso-coe');

        if(!$test) {
            $this->status = 'recommended';
        }

        return $this->result();
    }
}

PeepSo3_Site_Health_MB_Str::get_instance();