<?php

if(!class_exists('PeepSo3_Site_Health')) {
    require_once(dirname(__FILE__) . '/site_health.php');
    //new PeepSoError('Autoload issue: PeepSo3_Site_health not found ' . __FILE__);
}

class PeepSo3_Site_Health_Imagick extends PeepSo3_Site_Health {

    public function test() {

        // Uncomment to force the error
        // $override = FALSE;

        $test = (extension_loaded('imagick')) ? TRUE : FALSE;
        if(isset($override)) {
            $test = $override;
        }

        $this->label = __('The PHP Imagick extension is recommended','peepso-core');

        if(!$test) {
            $this->status = 'recommended';
        }

        return $this->result();
    }
}

PeepSo3_Site_Health_Imagick::get_instance();