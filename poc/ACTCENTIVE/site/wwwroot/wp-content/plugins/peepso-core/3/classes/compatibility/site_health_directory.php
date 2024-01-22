<?php

if(!class_exists('PeepSo3_Site_Health')) {
    require_once(dirname(__FILE__) . '/site_health.php');
    //new PeepSoError('Autoload issue: PeepSo3_Site_health not found ' . __FILE__);
}

class PeepSo3_Site_Health_Directory extends PeepSo3_Site_Health {

    public function test() {
        // Uncomment to force the error
        // $override = FALSE;
        
        $peepso_dir = PeepSo::get_option('site_peepso_dir', '', TRUE);
        $this->label = __('PeepSo can not write to directory','peepso-core');

        $form_fields = array('site_peepso_dir');
        $url = wp_nonce_url('admin.php?page=peepso_config&tab=advanced', 'peepso-config-nonce', 'peepso-config-nonce');

        if (FALSE === ($creds = request_filesystem_credentials($url, '', false, false, $form_fields))) {
            $test = FALSE;
        }

        // now we have some credentials, try to get the wp_filesystem running
        if (!WP_Filesystem($creds)) {
            // our credentials were no good, ask the user for them again
            request_filesystem_credentials($url, '', true, false, $form_fields);
            $test = FALSE;
        }

        global $wp_filesystem;

        if ($peepso_dir == '') {
            $test = TRUE;
        } else {
            if (!preg_match('/^\S.*\S$/', $peepso_dir)) {
                $this->label = __('PeepSo directory contains spaces at the begin or end of the provided path','peepso-core');
                $test = FALSE;
            }

            if (!$wp_filesystem->is_dir($peepso_dir) || !$wp_filesystem->is_dir($peepso_dir . DIRECTORY_SEPARATOR . 'users')) {
                $test = FALSE;
            } 
            
            if(!isset($test)) {
                $test = $wp_filesystem->is_writable($peepso_dir);
            }
        }
        
        if(isset($override)) {
            $test = $override;
        }

        if(!$test) {
            $this->status = 'critical';
        }

        return $this->result();
    }
}

PeepSo3_Site_Health_Directory::get_instance();