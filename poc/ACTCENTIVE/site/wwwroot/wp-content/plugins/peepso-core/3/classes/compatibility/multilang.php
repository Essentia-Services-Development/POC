<?php

add_action('init', function() {
    if(!is_admin() && get_current_user_id() && class_exists('TRP_Translate_Press') ) {
        global $TRP_LANGUAGE;
        if (strlen($TRP_LANGUAGE)) {
            update_user_option(get_current_user_id(), 'peepso_trp_lang', $TRP_LANGUAGE);
        }
    }
});


function PeepSo3_MultiLang__($string, $domain, $user_id, $fallback = TRUE) {

    if(PeepSo3_Third_Party::has_multilingual_trp()) {
        $locale = PeepSo3_MultiLang_User_Locale($user_id);
        if (strstr($locale, 'en_')) {
            return $string;
        }

        $mo = new MO;
        $path = apply_filters('peepso_absolute_textdomain_path', PeepSo::absolute_textdomain_path(), $domain);
        $path .= $domain . '-' . $locale . '.mo';


        if ($mo->import_from_file($path)) {
            return $mo->translate($string);
        }

        // If nothing found, try falling back to the site language
        if($fallback) {
            return PeepSo3_MultiLang__($string, get_option('WPLANG'), $user_id, FALSE);
        }
    }

    // Finally, fallback to regular gettext
    return __($string, $domain);
}

function PeepSo3_MultiLang_User_Locale($user_id) {
    $locale = get_user_option('peepso_trp_lang', $user_id);
    if (!strlen($locale)) {
        $locale = get_option('WPLANG');
    }
    return $locale;
}