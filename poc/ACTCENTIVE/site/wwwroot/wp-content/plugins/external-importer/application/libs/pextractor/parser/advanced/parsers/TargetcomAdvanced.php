<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\ExtractorHelper;
use ExternalImporter\application\libs\pextractor\parser\Product;

/**
 * TargetcomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class TargetcomAdvanced extends AdvancedParser {

    private $_meta = array();
    private $_product = null;

    public function parseLinks()
    {
        if (!preg_match('~\/\-\/N\-([0-9a-z]+)~', $this->getUrl(), $matches))
            return array();

        $url = 'https://redsky.target.com/v2/plp/search/?category=' . urlencode($matches[1]) . '&channel=web&count=24&default_purchasability_filter=true&facet_recovery=false&offset=0&pageId=%2Fc%2Fhp0vg&pricing_store_id=86&store_ids=86%2C1768%2C533%2C1771%2C926&visitorId=01704272A1FD0201895671D6E0355827&include_sponsored_search_v2=true&ppatok=AOxT33a&platform=desktop&useragent=Mozilla%2F5.0+%28Macintosh%3B+Intel+Mac+OS+X+10.15%3B+rv%3A72.0%29+Gecko%2F20100101+Firefox%2F72.0&key=eb2551e4accc14f38cc42d32fbc2b2ea';
        if ($offset = ExtractorHelper::getQueryVar('Nao', $this->getUrl()))
            $url = \add_query_arg('offset', $offset, $url);

        $result = $this->getRemoteJson($url);
        if (!$result || !isset($result['search_response']['items']['Item']))
            return array();

        $urls = array();
        foreach ($result['search_response']['items']['Item'] as $item)
        {
            $urls[] = $item['url'];
        }

        if (isset($result['search_response']['metaData']))
            $this->_meta = $result['search_response']['metaData'];

        return $urls;
    }

    public function parsePagination()
    {
        $totalPages = 0;
        foreach ($this->_meta as $m)
        {
            if ($m['name'] == 'totalPages')
            {
                $totalPages = (int) $m['value'];
                break;
            }
        }

        $urls = array();
        for ($i = 1; $i < $totalPages; $i++)
        {
            $urls[] = \add_query_arg('Nao', $i * 24, $this->getUrl());
        }

        return $urls;
    }

    public function parseCategoryPath()
    {
        $paths = array(
            ".//div[@class='h-text-sm h-padding-v-tiny']//*[@itemprop='name']",
        );

        if ($categs = $this->xpathArray($paths))
        {
            array_shift($categs);
            return $categs;
        }
    }

    public function parsePrice()
    {
        $this->_maybeGetProduct();
        if (isset($this->_product['price']['current_retail']))
            return $this->_product['price']['current_retail'];
        elseif (isset($this->_product['price']['current_retail_min']))
            return $this->_product['price']['current_retail_min'];
    }

    public function parseOldPrice()
    {
        $this->_maybeGetProduct();
        if (isset($this->_product['price']['reg_retail']))
            return $this->_product['price']['reg_retail'];
        elseif (isset($this->_product['price']['reg_retail_min']))
            return $this->_product['price']['reg_retail_min'];
    }

    public function parseImages()
    {
        $images = array();
        $results = $this->xpathArray(".//div[@class='slideDeckPicture']//img/@src");
        foreach ($results as $img)
        {
            $images[] = strtok($img, '?');
        }
        return $images;
    }

    public function parseInStock()
    {
        return true;
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name-value' => ".//div[@id='specAndDescript']//div[contains(@class, 'h-padding-h-default')]/div/div",
            ),
        );
    }

    private function _maybeGetProduct()
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

    public function afterParseFix(Product $product)
    {
        $product->availability = '';
        return $product;
    }

}
