<?php

namespace ExternalImporter\application\components\scrap;

defined('\ABSPATH') || exit;

use ExternalImporter\application\components\scrap\ProxycrawlScrap;
use ExternalImporter\application\components\scrap\ScraperapiScrap;
use ExternalImporter\application\components\scrap\ScrapingdogScrap;

/**
 * ScrapFactory class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class ScrapFactory {

    public static $services = array(
        ProxycrawlScrap::SLUG => ProxycrawlScrap::class,
        ScraperapiScrap::SLUG => ScraperapiScrap::class,
        ScrapingdogScrap::SLUG => ScrapingdogScrap::class,
    );

    public static function init()
    {
        foreach (self::$services as $slug => $class)
        {
            $scrap = new $class();
            $scrap->initAction();
        }
    }

}
