<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * PepperfrycomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class PepperfrycomParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'INR';
    protected $json_data = array();

    public function parseCatalog($max)
    {
        return array_slice($this->xpathArray(".//*[@class='clip-grid-view']//*[contains(@class, 'clip-card-more-prdct')]/a/@href"), 0, $max);
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//h1[@itemprop='name']");
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//*[@itemprop='description']");
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//meta[@itemprop='price']/@content");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//span[contains(@class, 'vip-old-price-amt')]");
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//*[@itemprop='brand']");
    }

    public function parseImg()
    {
        return $this->xpathScalar(".//img[@itemprop='image']/@src");
    }

    public function parseImgLarge()
    {
        return $this->xpathScalar(".//img[@itemprop='image']/@data-bigimg");
    }

    public function parseExtra()
    {
        $extra = array();


        $extra['features'] = array();
        $keys = $this->xpathArray(".//*[@id='itemDetail']//p[not(span)]/b");
        $values = $this->xpathArray(".//*[@id='itemDetail']/p[not(span) and not(contains(@class, 'pf-large'))]/text()");
        $feature = array();
        for ($i = 0; $i < count($keys); $i++)
        {
            if (!isset($values[$i]))
                continue;
            $feature['name'] = \sanitize_text_field($keys[$i]);
            $feature['value'] = \sanitize_text_field($values[$i]);
            $extra['features'][] = $feature;
        }

        $extra['images'] = $this->xpathArray(".//*[contains(@class, 'vip-product-options')]//li[position() > 1]/a/@data-img");

        return $extra;
    }

    public function isInStock()
    {
        return true;
    }

}
