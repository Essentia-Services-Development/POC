<?php

class PeepSo3_Web_Push {

    public static function enabled() {

        if(!PeepSo::is_dev_mode('web_push'))                        { return FALSE; }
        if(!PeepSo::get_option_new('web_push'))                     { return FALSE; }
        if(!strlen(PeepSo::get_option_new('web_push_public_key')))  { return FALSE; }
        if(!strlen(PeepSo::get_option_new('web_push_private_key'))) { return FALSE; }

        return TRUE;
    }

    public static function user_web_push($user_id = 0) {

        if(!PeepSo3_Web_Push::enabled()) {
            return FALSE;
        }

        if(!$user_id) {
            $user_id = get_current_user_id();
        }

        if(!$user_id) { return FALSE; }

        $default = PeepSo::get_option_new('web_push_user_default');

        $user_preference = get_user_option('peepso_web_push', $user_id);

        if(!is_numeric($user_preference)) { $user_preference = $default; }

        return $user_preference;
    }
}