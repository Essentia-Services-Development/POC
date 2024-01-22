<?php

namespace Keywordrush\AffiliateEgg;
defined('\ABSPATH') || exit;

/**
 * CurlProxy class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class CurlProxy {

    const TRANSIENT_NAME = 'ae-proxy';

    private static $api_client;

    /**
     * Whether URL should be sent through the proxy server.
     */
    static public function needSendThroughProxy($url)
    {
        $shop_id = ParserManager::getInstance()->getShopIdByUrl($url);
        if (!$shop_id)
            return false;

        $host = TextHelper::gethostname($url);
        if ($host == 'gimmeproxy.com')
            return false;
        try
        {
            if ((bool) ProxyConfig::getInstance()->option($shop_id))
                return true;
        } catch (\Exception $e)
        {
            return false;
        }

        return false;
    }

    static public function initProxy($url)
    {
        if (!self::needSendThroughProxy($url))
            return false;

        \add_action('http_api_curl', array(__CLASS__, 'addProxy'), 10, 3);
        return true;
    }

    static public function addProxy($handle, $r, $url)
    {
        if (!self::needSendThroughProxy($url))
            return;

        $proxies = TextHelper::commaListArray(ProxyConfig::getInstance()->option('proxies'));
        if ($proxies)
        {
            $randIndex = array_rand($proxies);
            $proxy = $proxies[$randIndex];
            curl_setopt($handle, CURLOPT_PROXY, $proxy);

            return;
        }

        // Gimmeproxy
        $proxy_ttl = (int) ProxyConfig::getInstance()->option('proxy_ttl');
        if (!$proxy_ttl)
            $proxy = false;
        else
            $proxy = \get_transient(self::TRANSIENT_NAME);

        if (!empty($proxy['added_time']) && time() - $proxy['added_time'] > $proxy_ttl)
            $proxy = false;

        if ($proxy === false)
        {
            $proxy = self::getProxy();
            $proxy['added_time'] = time();
            if ($proxy_ttl)
                \set_transient(self::TRANSIENT_NAME, $proxy, $proxy_ttl);
            else
                self::clearTransientData();
        }
        if ($proxy && isset($proxy['curl']))
        {
            \curl_setopt($handle, CURLOPT_PROXY, $proxy['curl']);
        }
    }

    static public function clearTransientData()
    {
        \delete_transient(self::TRANSIENT_NAME);
    }

    static public function getProxy()
    {
        parse_str(ProxyConfig::getInstance()->option('gproxy_parameters'), $params);
        if (!$params || !is_array($params))
            $params = array();
        return self::getApiClient()->getProxy($params);
    }

    static private function getApiClient()
    {
        if (self::$api_client === null)
        {
            self::$api_client = new GimmeproxyApi(ProxyConfig::getInstance()->option('gproxy_api_key'));
        }
        return self::$api_client;
    }

}
