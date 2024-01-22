<?php

/**
 * This class serves as a replacement for setcookie()
 * The purpose is to be able to set SameSite with an options array
 * If PHP is below 7.3 SameSite will not be set
 */
class PeepSo3_Cookie {

    public static function set($name, $value, $expires, $options = [] ) {

        $default_options = [
            'expires' => 0,
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'],
            'secure' => isset($_SERVER['HTTPS']) ? true : false,
            'httponly' => TRUE,
        ];

        if ($default_options['secure']) {
            $default_options['samesite'] = 'None';
        }

        $options = array_merge(['expires'=>$expires], $options);
        $options = array_merge($default_options, $options);

        $success = FALSE;

        if (version_compare(PHP_VERSION, '7.3.0') >= 0) {
            // 7.3.0+ syntax
            $success = setcookie($name, $value, $options);
        } else {
            // 7.2.0 syntax
            $success = setcookie($name, $value, $options['expires'], $options['path'], $options['domain'], $options['secure'], $options['httponly']);
        }

        if($success) {
            return TRUE;
        } else {
            new PeepSoError(__CLASS__."::".__METHOD__." setcookie failed, PHP: ".phpversion());
            return FALSE;
        }

    }

}