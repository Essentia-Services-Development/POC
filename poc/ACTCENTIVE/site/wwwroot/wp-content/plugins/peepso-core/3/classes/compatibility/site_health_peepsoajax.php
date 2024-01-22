<?php

if(!class_exists('PeepSo3_Site_Health')) {
    require_once(dirname(__FILE__) . '/site_health.php');
    //new PeepSoError('Autoload issue: PeepSo3_Site_health not found ' . __FILE__);
}

class PeepSo3_Site_Health_PeepSoAJAX extends PeepSo3_Site_Health {

    public function test() {

        if(PeepSo3_Helper_PeepSoAJAX_Online::maybe_dismissed()) { return; }

        // Uncomment to force the error
        // $override = 1;

        $test = PeepSo3_Mayfly::get('peepsoajax_is_broken'); // 0 = not broken, 1 = broken, NULL = not tested
        if(isset($override)) {
            $test = $override;
        }

        $this->label = PeepSo3_Helper_PeepSoAJAX_Online::get_message();
        $this->description = PeepSo3_Helper_PeepSoAJAX_Online::get_description();
        if($test == 1) {
            $this->status = 'critical';
        }

        return $this->result();
    }
}

PeepSo3_Site_Health_PeepSoAJAX::get_instance();