<?php

class PeepSo3_WordPress_Language
{

    private static $instance;

    public static function get_instance()
    {

        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct() {

        if (PeepSo::get_option('login_with_email', 0) == 2) {

            add_filter('gettext', function($translation, $text, $domain) {

                if (strtolower($text) == 'username or email address') {
                    return trim(__('Email&nbsp;address:'),':');
                }

                if(strtolower($text) == 'unknown email address. check again or try your username.') {
                    $translation = __('Unknown email address.', 'peepso-core');
                }

                return $translation;

            }, 3, 999);
        }
    }
}

PeepSo3_WordPress_Language::get_instance();