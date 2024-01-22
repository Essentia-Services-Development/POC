<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * AlibabacomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2018 keywordrush.com
 */
class AlibabacomParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'USD';

    public function parseCatalog($max)
    {
        if ($urls = $this->xpathArray(array(".//h2[contains(@class, 'title')]/a/@href", ".//div[contains(@class, 'organic-offer-wrapper')]/a[contains(@href, 'product-detail')]/@href")))
            return $urls;

        $html = $this->dom->saveHTML();

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
        if ($price = $this->xpathScalar(".//*[@class='ma-spec-price ma-price-promotion']"))
            return $price;

        if ($price = $this->xpathScalar(".//meta[@property='og:price:amount']/@content"))
        {
            $this->currency = $this->xpathScalar(".//meta[@property='og:price:currency']/@content");
        }
        return $price;
    }

    public function parseOldPrice()
    {
        if ($price = $this->xpathScalar(".//*[@class='ma-spec-price ma-price-original']"))
            return $price;
    }

    public function parseManufacturer()
    {
        
    }

    public function parseImg()
    {
        $image = $this->xpathScalar(".//meta[@property='og:image']/@content");
        if ($image && !preg_match('/^https?:/', $image))
            return 'https:' . $image;
    }

    public function parseImgLarge()
    {
        
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();
        $names = $this->xpathArray(".//*[@class='do-entry-list']//dt[@class='do-entry-item']");
        $values = $this->xpathArray(".//*[@class='do-entry-list']//dd[@class='do-entry-item-val']");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            $feature['name'] = trim(\sanitize_text_field($names[$i]), ':');
            $feature['value'] = \sanitize_text_field($values[$i]);
            $extra['features'][] = $feature;
        }
        return $extra;
    }

    public function isInStock()
    {
        return true;
    }

}
