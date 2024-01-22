<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * SuppzcomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class SuppzcomParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'USD';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//div[contains(@class,'category-products')]//h2[@class='product-name']/a/@href"), 0, $max);
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//h1[@itemprop='name']");
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//div[@id='tab_description_tabbed_contents']//div[contains(@class,'std')]");
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//div[@class='product-info']//meta[@itemprop='price']/@content");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//div[@class='product-info']//p[@class='old-price']/span[@class='price']");
    }

    public function parseManufacturer()
    {
        
    }

    public function parseImg()
    {
        return $this->xpathScalar(".//img[@class='etalage_thumb_image']/@src");
    }

    public function parseImgLarge()
    {
        
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['rating'] = TextHelper::ratingPrepare($this->xpathScalar(".//*[@itemprop='aggregateRating']//meta[@itemprop='ratingValue']/@content"));
        return $extra;
    }

    public function isInStock()
    {
        if ($this->xpathScalar(".//*[@id='product_addtocart_form']//*[contains(@class, 'out-of-stock')]"))
            return false;
        else
            return true;
    }

}
