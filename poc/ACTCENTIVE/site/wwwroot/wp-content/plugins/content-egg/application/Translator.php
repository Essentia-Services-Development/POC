<?php

namespace ContentEgg\application;

defined('\ABSPATH') || exit;

use ContentEgg\application\admin\GeneralConfig;

/**
 * Translator class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class Translator {

    private static $pairs = array();

    public static function __($str)
    {
        return self::translate($str);
    }

    public static function translate($str)
    {
        $texts = GeneralConfig::getInstance()->option('frontend_texts');
        if (!empty($texts[$str]))
            return $texts[$str];
        else
            return __($str, 'content-egg-tpl');
    }
}
