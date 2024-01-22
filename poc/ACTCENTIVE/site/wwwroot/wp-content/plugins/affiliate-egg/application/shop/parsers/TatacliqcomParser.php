<?php

namespace Keywordrush\AffiliateEgg; defined( '\ABSPATH' ) || exit;

/**
 * TatacliqcomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class TatacliqcomParser extends ShopParser {

    protected $currency = 'INR';
    private $_product;
    //protected $user_agent = array('DuckDuckBot', 'facebot', 'ia_archiver');
    protected $user_agent = array('ia_archiver');
    protected $headers = array(
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language' => 'en-us,en;q=0.5',
        'Cache-Control' => 'no-cache',
        'Connection' => 'keep-alive',
    );

    public function parseCatalog($max)
    {
        // search page
        if (!$query = parse_url($this->getUrl(), PHP_URL_QUERY))
            return array();
        parse_str($query, $arr);
        if (!isset($arr['text']))
            return array();
        $keyword = $arr['text'];
        try
        {
            $result = $this->requestGet('https://www.tatacliq.com/marketplacewebservices/v2/mpl/products/searchProducts?searchText='. urlencode($keyword).'%3Arelevance%3AinStockFlag%3Atrue&isKeywordRedirect=false&isKeywordRedirectEnabled=true&channel=WEB&isMDE=true&isTextSearch=false&isFilter=false&qc=false&test=v1&page=0&isPwa=true&pageSize=40&typeID=all', false);
        } catch (\Exception $e)
        {
            return array();
        }
        $result = json_decode($result, true);
        if (!$result || !isset($result['searchresult']))
            return array();
        $urls = array();
        foreach ($result['searchresult'] as $item)
        {
            if (isset($item['webURL']))
                $urls[] = $item['webURL'];
        }
        return $urls;
    }

    public function parseTitle()
    {
        $this->_getProduct();
        if (!$this->_product)
            return;
        if (isset($this->_product['productName']))
            return $this->_product['productName'];
    }

    public function _getProduct()
    {
        if (!preg_match('~/p-(mp\d+)~i', $this->getUrl(), $matches))
            return false;
        
        $id = strtolower($matches[1]);
        try
        {
            $result = $this->requestGet('https://www.tatacliq.com/marketplacewebservices/v2/mpl/products/productDetails/' . urlencode($id) . '?isPwa=true&isMDE=true', false);
        } catch (\Exception $e)
        {
            return false;
        }

        $result = json_decode($result, true);

        $this->_product = $result;
        return $this->_product;
    }

    public function parseDescription()
    {
        if (isset($this->_product['productDescription']))
            return $this->_product['productDescription'];
    }

    public function parsePrice()
    {
        if (isset($this->_product['winningSellerPrice']['doubleValue']))
            return $this->_product['winningSellerPrice']['doubleValue'];
    }

    public function parseOldPrice()
    {

        if (isset($this->_product['mrpPrice']['doubleValue']))
            return $this->_product['mrpPrice']['doubleValue'];
    }

    public function parseManufacturer()
    {
        if (isset($this->_product['brandName']))
            return $this->_product['brandName'];
    }

    public function parseImg()
    {
        if (isset($this->_product['galleryImagesList'][0]['galleryImages']))
            return $this->_product['galleryImagesList'][0]['galleryImages'][0]['value'];
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();
        if (isset($this->_product['classifications']))
        {
            $feature = array();
            foreach ($this->_product['classifications'] as $c)
            {
                if ($c['groupName'] == 'Warranty')
                    continue;
                foreach ($c['specifications'] as $s)
                {

                    $feature['name'] = \sanitize_text_field($s['key']);
                    $feature['value'] = \sanitize_text_field($s['value']);
                    $extra['features'][] = $feature;
                }
            }
        }
        return $extra;
    }

    public function isInStock()
    {
        if (isset($this->_product['winningSellerAvailableStock']) && ((int) $this->_product['winningSellerAvailableStock'] == '0' || (int) $this->_product['winningSellerAvailableStock'] == '-1'))
            return false;
        else
            return true;
    }

}
