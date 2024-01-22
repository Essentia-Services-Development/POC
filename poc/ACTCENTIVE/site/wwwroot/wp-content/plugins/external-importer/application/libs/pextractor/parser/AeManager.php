<?php

namespace ExternalImporter\application\libs\pextractor\parser;

defined('\ABSPATH') || exit;

use ExternalImporter\application\admin\ParserConfig;
use \Keywordrush\AffiliateEgg\ParserManager;

/**
 * AeManager class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class AeManager {

    const MIN_AE_VERSION = '9.9.6';

    private static $instance;

    public static function getInstance()
    {
        if (self::$instance === null)
            self::$instance = new self;
        return self::$instance;
    }

    public function isIntegrationPossible()
    {
        if (ParserConfig::getInstance()->option('ae_integration') != 'enabled')
            return false;

        if (!class_exists('\Keywordrush\AffiliateEgg\AffiliateEgg'))
            return false;

        if (!\Keywordrush\AffiliateEgg\LicConfig::getInstance()->option('license_key'))
            return false;

        if (version_compare(self::MIN_AE_VERSION, \Keywordrush\AffiliateEgg\AffiliateEgg::version(), '>'))
            return false;

        return true;
    }

    public function isParserExists($uri)
    {
        if (ParserManager::getInstance()->isExporterExists($uri))
            return true;
        else
            return false;
    }

}
