<?php

namespace Keywordrush\AffiliateEgg;

/**
 * Cpa class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class CustomDeeplink {

    private static $instance;

    public static function getInstance()
    {
        if (self::$instance === null)
            self::$instance = new self;
        return self::$instance;
    }

    public function getLink($url, $deeplink)
    {
        if (!$host = parse_url($deeplink, PHP_URL_HOST))
            return false;

        $host = preg_replace('/^www\./', '', $host);

        preg_match('/^([a-z0-9]+)\./i', $host, $matches);
        if (!$matches)
            return false;

        if ($host == 'ad.zanox.com')
            return $this->getLinkZanox($url, $deeplink);

        $method_name = 'getLink' . ucfirst(strtolower($matches[1]));
        if (method_exists($this, $method_name))
            return $this->$method_name($url, $deeplink);

        return false;
    }

    private function getLinkZanox($url, $deeplink)
    {
        if (!$url_parts = parse_url($url))
            return false;

        // zanox + asos deeplink generation
        if ($url_parts['host'] !== 'www.asos.com')
            return false;

        /**
         *  In order to use this deeplink please replace the "http://www.asos.com/" 
         *  portion of your desired destination URL with your affiliate link and place 
         * the rest of the link after the &ULP=. Please then make sure to change all ? 
         * in the ULP URL to &. Failure to do so may mean your sales won't be tracked by 
         * zanox. Example deeplink formation: 
         * http://www.asos.com/Men/New-In-Shoes-Accs/Cat/pgecategory.aspx?cid=6994&countryid=19 
         * BECOMES 
         * http://ad.zanox.com/ppc?1234C5678910T&ULP=Men/New-In-Shoes-Accs/Cat/pgecategory.aspx&cid=6994&countryid=19 
         */
        $result = $deeplink;

        $path = preg_replace('/^\/ru\//', '', $url_parts['path']);
        $path = preg_replace('/[^a-zA-Z0-9~\!\#\$\&\\-\;\=\?\-\[\]_\/\.]/', '', urldecode($path));
        $result .= $path;
        if (!empty($url_parts['query']))
        {
            $query = preg_replace('/[^a-zA-Z0-9~\!\#\$\&\\-\;\=\?\-\[\]_\/\.]+/', '', urldecode($url_parts['query']));
            $result .= '&';
            $result .= $query;
        }
        return $result;
    }

    private function getLinkShopozz($url, $deeplink)
    {
        $partner_id_name = 'src';
        $ebay_uri = 'http://shopozz.ru/items/{{item_id}}';
        $amazon_uri = 'http://shopozz.ru/amazon/item/{{item_id}}';
        return $this->getLinkAmazonEbayShipping($url, $deeplink, $partner_id_name, $ebay_uri, $amazon_uri);
    }

    private function getLinkShopotam($url, $deeplink)
    {
        $partner_id_name = 'puebtdid';
        $ebay_uri = 'http://shopotam.ru/catalog/{{item_id}}-item.html';
        $amazon_uri = 'https://shopotam.ru/amazon/product/{{item_id}}.html';
        return $this->getLinkAmazonEbayShipping($url, $deeplink, $partner_id_name, $ebay_uri, $amazon_uri);
    }

    private function getLinkAmazonEbayShipping($url, $deeplink, $partner_id_name, $ebay_uri, $amazon_uri)
    {
        // item id
        if (!$url_host = parse_url($url, PHP_URL_HOST))
            return false;
        $url_host = strtolower(preg_replace('/^www\./', '', $url_host));

        if ($url_host == 'ebay.com')
        {
            // ebay
            $item_id = self::getEbayItemId($url);
            if (!$item_id)
                return $url;

            $res = str_replace('{{item_id}}', $item_id, $ebay_uri);
        } elseif ($url_host == 'amazon.com')
        {
            // amazon
            $item_id = self::getAmazonItemId($url);
            if (!$item_id)
                return $url;

            $res = str_replace('{{item_id}}', $item_id, $amazon_uri);
        } else
            return false;

        // partner id
        $partner_id = '';
        if ($deeplink_query = parse_url($deeplink, PHP_URL_QUERY))
        {
            parse_str($deeplink_query, $query_array);
            if (!empty($query_array[$partner_id_name]))
            {
                $res .= '?' . http_build_query($query_array);
            }
        }

        return $res;
    }

    static function getEbayItemId($url)
    {
        preg_match('#\/(\d{12})#', $url, $matches);
        if ($matches)
            return $matches[1];
        else
            return false;
    }

    static function getAmazonItemId($url)
    {
        preg_match('/(dp|gp\/product)\/(\w{10})/', $url, $matches);
        if ($matches)
            return $matches[2];
        else
            return false;
    }

}
