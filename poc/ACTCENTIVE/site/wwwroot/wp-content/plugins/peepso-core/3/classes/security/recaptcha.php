<?php

class PeepSo3_ReCaptcha {
    public static function should_enqueue() {
        return (PeepSo::get_option('site_registration_recaptcha_enable', 0) || PeepSo::get_option('recaptcha_login_enable', 0));
    }

    public static function url() {
        $host = 'https://www.google.com';
        if (intval(PeepSo::get_option('site_registration_recaptcha_use_globally', 0))) {
            $host = 'https://www.recaptcha.net';
        }

        return $host;
    }
}