<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

/**
 * AlibabacomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class AlibabacomAdvanced extends AdvancedParser {

    public function parseLinks()
    {
        if ($urls = $this->xpathArray(array(".//h2[contains(@class, 'title')]/a/@href", ".//div[contains(@class, 'organic-offer-wrapper')]/a[contains(@href, 'product-detail')]/@href")))
            return $urls;

        $html = $this->html;

        // category page
        if (!preg_match('/aggregationSearchPage\(({.+?}\));/ims', $html, $matched))
            return array();
        if (!preg_match('/DATA: ({.+?}]})\s\s\s/ims', $matched[1], $matched_data))
            return array();
        if (!$items = json_decode(trim($matched_data[1]), true))
            return array();
        if (!isset($items['itemList']))
            return array();

        $urls = array();
        foreach ($items['itemList'] as $item)
        {
            if (!isset($item['productDetailUrl']))
                continue;
            $urls[] = $item['productDetailUrl'];
        }
        return $urls;
    }

    public function parseCategoryPath()
    {
        $paths = array(
            ".//div[@id='module_breadcrumb']//a",
        );

        if ($categs = $this->xpathArray($paths))
        {
            array_shift($categs);
            array_shift($categs);
            return $categs;
        }
    }

    public function parseFeatures()
    {
        if ($features = parent::parseFeatures())
            return $features;

        $features = array();
        if (preg_match_all('~"attrName":"(.+?)"~', $this->html, $matches1) && preg_match_all('~"attrValue":"(.+?)"~', $this->html, $matches2))
        {
            if (count($matches1[1]) != count($matches2[1]))
                return array();

            foreach ($matches1[1] as $i => $name)
            {
                $features[] = array(
                    'name' => $name,
                    'value' => $matches2[1][$i],
                );
            }
        }

        return $features;
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name' => ".//*[@class='do-entry-list']//dt[@class='do-entry-item']",
                'value' => ".//*[@class='do-entry-list']//dd[@class='do-entry-item-val']",
            ),
        );
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//h1");
    }

    public function parseDescription()
    {
        return $this->xpathScalar("//div[@id='J-rich-text-description']/text()");
    }

    public function parsePrice()
    {
        $xpath = array(
            ".//div[@class='ma-reference-price']/span/span",
            ".//div[@class='ma-spec-price ma-price-promotion']/@title",
            ".//*[@class='ma-spec-price ma-price-promotion']",
        );

        return $this->xpathScalar($xpath);
    }

    public function parseOldPrice()
    {
        if ($price = $this->xpathScalar(".//*[@class='ma-spec-price ma-price-original']"))
            return $price;
    }

    public function parseImage()
    {
        if ($images = $this->parseImages())
            return reset($images);
    }

    public function parseImages()
    {
        $images = array();
        $results = $this->xpathArray(".//ul[@class='main-image-thumb-ul']//img/@src");
        foreach ($results as $img)
        {
            if (!strstr($img, '.jpg'))
                continue;

            $img = str_replace('.jpg_50x50.jpg', '.jpg', $img);
            $images[] = $img;
        }
        return $images;
    }

}
