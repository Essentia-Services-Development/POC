<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * TargetcomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com> 
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class TargetcomParser extends MicrodataShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'USD';
    private $_product = null;
    protected $headers = array(
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language' => 'en-us,en;q=0.5',
        'Cache-Control' => 'no-cache',
        'Connection' => 'keep-alive',
    );

    public function parseCatalog($max)
    {
        if ($urls = $this->_getSearchUrls())
            return $urls;

        // category
        if (!preg_match('~\/\-\/N\-([0-9a-z]+)~', $this->getUrl(), $matches))
            return array();

        $category = $matches[1];

        try
        {
            $result = $this->requestGet('https://redsky.target.com/v2/plp/search/?category=' . urlencode($category) . '&channel=web&count=24&default_purchasability_filter=true&facet_recovery=false&offset=0&pageId=%2Fc%2Fhp0vg&pricing_store_id=86&store_ids=86%2C1768%2C533%2C1771%2C926&visitorId=01704272A1FD0201895671D6E0355827&include_sponsored_search_v2=true&ppatok=AOxT33a&platform=desktop&useragent=Mozilla%2F5.0+%28Macintosh%3B+Intel+Mac+OS+X+10.15%3B+rv%3A72.0%29+Gecko%2F20100101+Firefox%2F72.0&key=eb2551e4accc14f38cc42d32fbc2b2ea');
        } catch (\Exception $e)
        {
            return array();
        }
        $result = str_replace('<?xml encoding="UTF-8">', '', $result);
        $result = json_decode($result, true);
        if (!$result || !isset($result['search_response']['items']['Item']))
            return false;
        $urls = array();
        foreach ($result['search_response']['items']['Item'] as $item)
        {
            $urls[] = $item['url'];
        }
        return $urls;
    }

    private function _getSearchUrls()
    {
        if (!$query = parse_url($this->getUrl(), PHP_URL_QUERY))
            return array();
        parse_str($query, $arr);
        if (!isset($arr['searchTerm']))
            return array();

        try
        {
            $result = $this->requestGet('https://redsky.target.com/redsky_aggregations/v1/web/plp_search_v1?key=ff457966e64d5e877fdbad070f276d18ecec4a01&channel=WEB&count=24&default_purchasability_filter=true&include_sponsored=true&keyword=' . urlencode($arr['searchTerm']) . '&offset=0&page=%2Fs%2F' . urlencode($arr['searchTerm']) . '&platform=desktop&pricing_store_id=86&store_ids=86%2C2106%2C1060%2C2765%2C1799&useragent=Mozilla%2F5.0+%28Macintosh%3B+Intel+Mac+OS+X+10.15%3B+rv%3A91.0%29+Gecko%2F20100101+Firefox%2F91.0&visitor_id=017B7CE4635D020195A67B31B05047C0');
        } catch (\Exception $e)
        {
            return array();
        }
        $result = str_replace('<?xml encoding="UTF-8">', '', $result);
        $result = json_decode($result, true);

        if (!$result || !isset($result['data']['search']['products']))
            return false;
        $urls = array();
        foreach ($result['data']['search']['products'] as $item)
        {
            $urls[] = $item['item']['enrichment']['buy_url'];
        }
        return $urls;
    }
    
    public function parseTitle()
    {
        return parent::parseTitle();
    }

    public function parseDescription()
    {
        if ($d = $this->xpathScalar(".//div[@id='specAndDescript']//div[contains(@class, 'h-padding-l-default')]//div[@class='h-margin-v-default']", true))
            return $d;
        else
            return parent::parseDescription();
    }

    public function parsePrice()
    {
        $this->_maybeGetProducts();
        if (isset($this->_product['price']['current_retail']))
            return $this->_product['price']['current_retail'];
        elseif (isset($this->_product['price']['current_retail_min']))
            return $this->_product['price']['current_retail_min'];
    }

    public function parseOldPrice()
    {
        $this->_maybeGetProducts();
        if (isset($this->_product['price']['reg_retail']))
            return $this->_product['price']['reg_retail'];
        elseif (isset($this->_product['price']['reg_retail_min']))
            return $this->_product['price']['reg_retail_min'];
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();
        $names_values = $this->xpathArray(".//div[@id='specAndDescript']//div[contains(@class, 'h-padding-h-default')]/div/div");
        $extra['features'] = array();
        for ($i = 0; $i < count($names_values); $i++)
        {
            $parts = explode(":", $names_values[$i]);
            if (count($parts) != 2)
                continue;

            $feature = array();
            $feature['name'] = \sanitize_text_field($parts[0]);
            $feature['value'] = \sanitize_text_field($parts[1]);
            $extra['features'][] = $feature;
        }
        return $extra;
    }

    private function _maybeGetProducts()
    {        
        if ($this->_product !== null)
            return;

        if (!preg_match('~\/A-(\d+)~', $this->getUrl(), $matches))
            return false;

        $uri = 'https://redsky.target.com/redsky_aggregations/v1/web/pdp_client_v1?key=ff457966e64d5e877fdbad070f276d18ecec4a01&tcin='. urlencode($matches[1]).'&store_id=86&pricing_store_id=86';
        
        $json = $this->getRemoteJson($uri);
        if (isset($json['data']['product']))
            $this->_product = $json['data']['product'];
    }

    public function isInStock()
    {
        return true;
    }

}
