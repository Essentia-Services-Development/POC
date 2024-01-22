<?php

namespace ExternalImporter\application;

defined('\ABSPATH') || exit;

use ExternalImporter\application\admin\FrontendConfig;

/**
 * Translator class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class Translator {

    private static $pairs = array();

    public static function __($str)
    {
        return self::translate($str);
    }

    public static function translate($str)
    {
        if (!isset(self::$pairs[$str]))
        {
            self::$pairs[$str] = '';
            if (!$texts = FrontendConfig::getInstance()->option('frontend_texts'))
                $texts = array();

            foreach ($texts as $text)
            {
                if ($text['name'] == $str)
                {
                    self::$pairs[$str] = $text['value'];
                }
            }
        }

        if (self::$pairs[$str])
            return self::$pairs[$str];
        else
            return __($str, 'external-importer');
    }

}
