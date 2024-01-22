<?php

namespace ExternalImporter\application\helpers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\Extractor;
use ExternalImporter\application\admin\ParserConfig;
use ExternalImporter\application\components\CurlProxy;
use ExternalImporter\application\components\Throttler;
use ExternalImporter\application\components\scrap\ScrapFactory;

/**
 * ParserHelper class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class ParserHelper {

    private static $extractor = null;

    public static function parseListing($url, $formats = null)
    {
        self::$extractor = new Extractor();
        $error = '';
        $error_code = 0;
        $listing = null;
        $httpOptions = array();
        if ($cookie = ParserConfig::getInstance()->getCookieByUrl($url))
            $httpOptions['headers'] = array('Cookie' => $cookie);

        ScrapFactory::init();
        
        try
        {
            CurlProxy::initProxy($url);
            $listing = self::$extractor->extractListing($url, ParserConfig::getInstance()->getExtractorConfig(), null, $formats, $httpOptions);
        } catch (\Exception $e)
        {
            $error = $e->getMessage();
            $error_code = $e->getCode();
        }

        if (!$listing && !$error)
        {
            $error = __('Products not found.', 'external-importer');
            $error_code = 1;
        }

        Throttler::addQueryLog($url, $error_code);

        if ($error)
            throw new \Exception($error, $error_code);
        else
            return $listing;
    }

    public static function parseProduct($url, $update_mode = false, $formats = null)
    {        
        self::$extractor = new Extractor();
        $error = '';
        $error_code = 0;
        $product = null;
        $httpOptions = array();
        if ($cookie = ParserConfig::getInstance()->getCookieByUrl($url))
            $httpOptions['headers'] = array('Cookie' => $cookie);

        ScrapFactory::init();
        
        try
        {
            CurlProxy::initProxy($url);
            $config = ParserConfig::getInstance()->getExtractorConfig();
            $product = self::$extractor->extractProduct($url, $config, null, $formats, $httpOptions, $update_mode);
        } catch (\Exception $e)
        {
            $error = $e->getMessage();
            $error_code = $e->getCode();
        }

        if (!$error && !$product)
        {
            $error = __('Product data not found.', 'external-importer');
            $error_code = 1;
        }

        Throttler::addQueryLog($url, $error_code);

        if ($error)
            throw new \Exception($error, $error_code);
        else
            return $product;
    }

    public static function getLastExtractor()
    {
        return self::$extractor;
    }

}
