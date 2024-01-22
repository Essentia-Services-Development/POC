<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\Product;
use ExternalImporter\application\libs\pextractor\ExtractorHelper;

/**
 * LazadaAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class LazadaAdvanced extends AdvancedParser {

    
    protected $_product = null;

    protected function preParseProduct()
    {
        $this->_getProduct();
        return parent::preParseProduct();
    }

    public function parseLinks()
    {
        $urls = array();

        if (preg_match_all('/"productUrl":"(.+?)"/', $this->html, $matches))
            $urls = $matches[1];
        elseif (preg_match_all('/"itemUrl":"(.+?)"/', $this->html, $matches))
            $urls = $matches[1];

        $urls = array_merge($urls, $this->_parseLdItemList());

        $results = array();
        foreach ($urls as $i => $url)
        {
            $url = strtok($url, '?');
            if (preg_match('/-i(\d+)[\.\-]/', $url, $matches))
            {
                if (isset($ids[$matches[1]]))
                    continue;
                else
                    $ids[$matches[1]] = true;
            }
            $results[] = $url;
        }

        return $results;
    }

    private function _parseLdItemList()
    {
        $ld_offers = array();
        $lds = $this->xpathArray(".//script[@type='application/ld+json']", true);
        foreach ($lds as $ld)
        {
            if (!$data = json_decode($ld, true))
                continue;

            if (isset($data['@type']) && $data['@type'] == 'ItemList')
            {
                $ld_offers = $data;
                break;
            }
        }

        if (!isset($ld_offers['itemListElement']))
            return array();

        $urls = array();
        foreach ($ld_offers['itemListElement'] as $item)
        {
            if (isset($item['url']))
                $urls[] = $item['url'];
        }

        return $urls;
    }

    private function _getProduct()
    {
        if (!preg_match('/var __moduleData__ = (.+?\]\}\}\});/ims', $this->html, $matches))
            return;

        if (!$json = json_decode($matches[1], true))
            return;

        if (!isset($json['data']['root']))
            return;

        $this->_product = $json['data']['root']['fields'];
    }

    public function parsePagination()
    {
        if (!preg_match('/"resultNr":(\d+),/', $this->html, $matches))
            return;

        $pagination = array();
        $pages = ceil($matches[1] / 40);
        for ($i = 1; $i < $pages; $i++)
        {
            $pagination[] = \add_query_arg('page', $i + 1, $this->getUrl());
            if ($i >= 100)
                break;
        }

        return $pagination;
    }

    public function parseOldPrice()
    {
        if (preg_match('~\\"pdt_price\\":\\"(.+?)\\"~', $this->html, $matches))
            return $matches[1];
    }

    public function parseImages()
    {
        if (!$this->_product || !isset($this->_product['skuGalleries'][0]))
            return;

        $images = array();
        foreach ($this->_product['skuGalleries'][0] as $value)
        {
            if ($value['type'] == 'img')
                $images[] = str_replace('.jpg', '-catalog.jpg_720x720q75.jpg', $value['src']);
        }

        return $images;
    }

    public function parseFeatures()
    {
        if (!$this->_product || !isset($this->_product['specifications']))
            return;

        $features = array();
        $specifications = reset($this->_product['specifications']);
        foreach ($specifications['features'] as $name => $value)
        {
            $feature['name'] = \sanitize_text_field($name);
            $feature['value'] = \sanitize_text_field($value);
            $features[] = $feature;
        }

        return $features;
    }

    public function parseReviews()
    {
        if (!$this->_product || !isset($this->_product['review']['reviews']))
            return array();

        $results = array();
        foreach ($this->_product['review']['reviews'] as $r)
        {
            $review = array();
            if (!isset($r['reviewContent']))
                continue;

            $review['review'] = $r['reviewContent'];

            if (isset($r['rating']))
                $review['rating'] = ExtractorHelper::ratingPrepare($r['rating']);

            if (isset($r['reviewer']))
                $review['author'] = $r['reviewer'];

            if (isset($r['reviewTime']))
                $review['date'] = strtotime($r['reviewTime']);

            $results[] = $review;
        }
        return $results;
    }

    public function afterParseFix(Product $product)
    {
        $product->image = str_replace('.jpg', '.jpg_720x720q75.jpg', $product->image);

        return $product;
    }

}
