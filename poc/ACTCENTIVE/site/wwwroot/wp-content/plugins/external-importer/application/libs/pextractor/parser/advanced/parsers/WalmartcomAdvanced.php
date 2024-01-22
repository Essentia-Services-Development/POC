<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\ExtractorHelper;
use ExternalImporter\application\libs\pextractor\parser\Product;

/**
 * WalmartcomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class WalmartcomAdvanced extends AdvancedParser {

    private $_meta = array();

    public function parseLinks()
    {
        if ($urls = $this->_parseLinks1())
            return $this->_prep($urls);

        if ($urls = $this->_parseLinks2())
            return $this->_prep($urls);

        $path = array(
            ".//a[contains(@class, 'product-title-link')]/@href",
            ".//*[@id='tile-container']//a[1]/@href",
            ".//*[@class='search-result-listview-items']//a[@itemprop='url']/@href",
            ".//*[@id='searchProductResult']//a[@itemprop='url']/@href",
            ".//a[contains(@href, '/ip/')]/@href",
        );

        return $this->_prep($this->xpathArray($path));
    }

    public function _prep(array $urls)
    {
        foreach ($urls as $i => $url)
        {
            $urls[$i] = strtok($url, '?');
        }
        return $urls;
    }

    private function _parseLinks1()
    {
        if (preg_match_all('~"canonicalUrl":"(.+?)"~', $this->html, $matches))
            return $matches[1];
    }

    private function _parseLinks2()
    {
        if (!preg_match('~\/(\d+_\d+)~', $this->getUrl(), $matches))
            return array();

        $url = 'https://www.walmart.com/search/api/preso?cat_id=' . urlencode($matches[1]) . '&prg=desktop&page=1';

        if ($page = ExtractorHelper::getQueryVar('page', $this->getUrl()))
            $url = \add_query_arg('page', $page, $url);

        $result = $this->getRemoteJson($url);

        if (!$result || !isset($result['items']))
            return array();

        $urls = array();
        foreach ($result['items'] as $item)
        {
            $urls[] = $item['productPageUrl'];
        }

        if (isset($result['pageMetadata']))
            $this->_meta = $result['pageMetadata'];

        return $urls;
    }

    public function parsePagination()
    {
        if (isset($this->_meta['canonicalNext']))
            return array($this->_meta['canonicalNext']);

        if (preg_match('~"totalItemCount":(\d+)~', $this->html, $matches))
        {
            $total = (int) $matches[1];
            $pages = $total / 40;
            if ($pages > 100)
                $pages = 100;

            $urls = array();
            for ($i = 1; $i < $pages; $i++)
            {
                $urls[] = \add_query_arg('page', $i, $this->getUrl());
            }
            return $urls;
        }
    }

    public function parseTitle()
    {
        $paths = array(
            ".//h1[@itemprop='name']/text()",
        );

        return $this->xpathScalar($paths);
    }

    public function parseDescription()
    {
        $path = array(
            ".//div[@class='about-desc']",
            ".//div[@itemprop='description']",
            ".//div[@class='product-short-description']",
        );

        return $this->xpathScalar($path, true);
    }

    public function parsePrice()
    {
        $path = array(
            "(.//*[contains(@class, 'Price-enhanced')]//*[@class='Price-group']/@aria-label)[1]",
        );

        return $this->xpathScalar($path);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//div[@class='price-old display-inline']//span[@class='visuallyhidden']",
            ".//*[@class='price-old display-inline']//*[@class='price-group']/@aria-label",
            ".//div[@class='prod-PriceHero']//span[@class='price-characteristic']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseImage()
    {
        $paths = array(
            ".//div[@class='prod-hero-image']//img/@src",
            ".//div[@class='hover-zoom-hero-image-container']//img/@src",
        );

        return $this->xpathScalar($paths);
    }

    public function parseImages()
    {
        if (!preg_match_all('/{"id":".+?","url":"(.+?)","zoomable"/', $this->html, $matches))
            return array();

        $images = array();
        foreach ($matches[1] as $img)
        {
            $img = str_replace('.jpeg', '.jpeg?odnWidth=612&odnHeight=612&odnBg=ffffff', $img);
            $images[] = $img;
        }
        return $images;
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name' => ".//div[@class='product-specifications']//td[1]",
                'value' => ".//div[@class='product-specifications']//td[2]",
            ),
        );
    }

    public function parseFeatures()
    {
        $features = parent::parseFeatures();

        if (preg_match('~"specifications":(\[.+?\])~', $this->html, $matches))
        {
            $ff = json_decode($matches[1], true);
            if ($ff && is_array($ff))
            {
                foreach ($ff as $f)
                {
                    if (!isset($f['name']) || !isset($f['value']))
                        continue;
                    $features[] = array(
                        'name' => $f['name'],
                        'value' => $f['value'],
                    );
                }
            }
        }

        if ($gtin = $this->xpathScalar(".//*[@itemprop='gtin13']/@content"))
            $features[] = array('name' => 'GTIN', 'value' => $gtin);
        elseif ($gtin = preg_match('~"gtin13":"(.+?)"~', $this->html, $matches))
            $features[] = array('name' => 'GTIN', 'value' => $matches[1]);

        return $features;
    }

    public function afterParseFix(Product $product)
    {
        if (!$product->price)
            $product->oldPrice = 0;

        if (!$product->reviews)
            return $product;

        foreach ($product->reviews as $i => $review)
        {
            $product->reviews[$i]['review'] = str_replace('See more', '', $review['review']);
        }
        return $product;
    }

}
