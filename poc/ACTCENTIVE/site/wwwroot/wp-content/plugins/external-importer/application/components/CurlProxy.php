<?php

namespace ExternalImporter\application\components;

defined('\ABSPATH') || exit;

use ExternalImporter\application\helpers\TextHelper;
use ExternalImporter\application\admin\ParserConfig;

/**
 * CurlProxy class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class CurlProxy {

    static public function initProxy($url)
    {
        if (self::needSendThroughProxy($url))
            \add_action('http_api_curl', array(__CLASS__, 'addProxy'), 10, 3);
    }

    static public function needSendThroughProxy($url)
    {
        if (!$proxy_domains = TextHelper::commaListArray(ParserConfig::getInstance()->option('proxy_domains')))
            return false;

        $host = TextHelper::getHostName($url);
        if (in_array($host, $proxy_domains))
            return true;
        else
            return false;
    }

    static public function addProxy($handle, $r, $url)
    {
        if (!$proxies = TextHelper::commaListArray(ParserConfig::getInstance()->option('proxy_list')))
            return;

        $randIndex = array_rand($proxies);
        $proxy = $proxies[$randIndex];
        curl_setopt($handle, CURLOPT_PROXY, $proxy);
    }

}
