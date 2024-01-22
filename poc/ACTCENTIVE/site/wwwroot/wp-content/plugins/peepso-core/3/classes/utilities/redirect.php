<?php

class PeepSo3_Utility_Redirect {

    public static function _($url) {

        if(!headers_sent()) {
            nocache_headers();
            wp_redirect($url);
            die();
        }

        echo '<script>window.location.replace("'.$url.'");</script>';
        die();
    }

    public static function https() {
        if (!is_ssl()) {
            $redirect= "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            PeepSo::redirect($redirect);
        }
    }
}