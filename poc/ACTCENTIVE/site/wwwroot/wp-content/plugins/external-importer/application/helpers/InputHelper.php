<?php

namespace ExternalImporter\application\helpers;

defined('\ABSPATH') || exit;

/**
 * InputHelper class file
 * 
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 * 
 */
class InputHelper {

    public static function get($var, $default = null, $stripslashes = false)
    {
        if (!isset($_GET[$var]))
            return $default;
        else
            return $stripslashes ? \stripslashes_deep($_GET[$var]) : $_GET[$var];
    }

    public static function post($var, $default = null, $stripslashes = false)
    {
        if (!isset($_POST[$var]))
            return $default;
        else
            return $stripslashes ? \stripslashes_deep($_POST[$var]) : $_POST[$var];
    }

}
