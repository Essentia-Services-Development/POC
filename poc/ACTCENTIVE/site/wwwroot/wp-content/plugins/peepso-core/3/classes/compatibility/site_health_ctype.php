<?php

if(!class_exists('PeepSo3_Site_Health')) {
    require_once(dirname(__FILE__) . '/site_health.php');
    //new PeepSoError('Autoload issue: PeepSo3_Site_health not found ' . __FILE__);
}

class PeepSo3_Site_Health_Ctype extends PeepSo3_Site_Health {

    public function test() {

        // Uncomment to force the error
        // $override = FALSE;

        $test = (extension_loaded('ctype')) ? TRUE : FALSE;
        if(isset($override)) {
            $test = $override;
        }

        $this->label = __('The PHP ctype extension is required','peepso-core');

        if(!$test) {
            $this->status = 'critical';
        }

        return $this->result();
    }
}

PeepSo3_Site_Health_Ctype::get_instance();