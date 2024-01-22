<?php

if(!class_exists('PeepSo3_Site_Health')) {
    require_once(dirname(__FILE__) . '/site_health.php');
    //new PeepSoError('Autoload issue: PeepSo3_Site_health not found ' . __FILE__);
}

class PeepSo3_Site_Health_Caching extends PeepSo3_Site_Health {

    public function test() {

    	$has_errors = FALSE;
	    $this->label = __('No known incompatible caching solution detected','peepso-core');

	    // Advanced Post Cache
        if(class_exists('Advanced_Post_Cache')) {
			$this->description .= '<b>Advanced Post Cache</b> detected<br/>';
	        $has_errors = TRUE;
        }

        // WP_CACHE
	    if(defined('WP_CACHE') && TRUE == WP_CACHE) {
		    $this->description .= '<b>WP_CACHE</b> constant is enabled<br/>';
		    $has_errors = TRUE;
	    }

	    // advanced-cache.php
	    if(file_exists(WP_CONTENT_DIR.DIRECTORY_SEPARATOR.'advanced-cache.php')) {
		    $this->description .= '<b>advanced-cache.php</b> drop-in detected<br/>';
		    $has_errors = TRUE;
	    }

	    // batcache
	    if(class_exists('batcache')) {
		    $this->description .= '<b>batcache</b> detected<br/>';
		    $has_errors = TRUE;
	    }

	    // object-cache.php
	    if(file_exists(WP_CONTENT_DIR.DIRECTORY_SEPARATOR.'object-cache.php')) {
		    $this->description .= '<b>object-cache.php</b> drop-in detected<br/>';
		    $has_errors = TRUE;
	    }

        if($has_errors) {
	        $this->status = 'recommended';
	        $this->description =  __('Some server-side caching solutions might break PeepSo features and in some extreme cases lead to content loss. PeepSo cannot guarantee proper functioning on your server.','peepso-core')
	                             . '<br/><br/>'
	                             . __('Please make sure to have regular full backups set up at least daily in case of unexpected behavior.','peepso-core')
	                             . '<br/><br/>'
	                             . $this->description;
	        $this->label = __('Possibly incompatible caching discovered - it might interfere with PeepSo','peepso-core');
        }

        return $this->result();
    }
}

PeepSo3_Site_Health_Caching::get_instance();