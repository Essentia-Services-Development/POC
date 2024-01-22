<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * ButikruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class ButikruParser extends ShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//div[@data-test='product-item']/a/@href");
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//*[@class='card-header']//h4");
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//section[@class='sm-width-100p md-width-30 center-by-margins sm-padding-1-r']//p[contains(@class, 'wspace-pre-line')]");
    }

    public function parsePrice()
    {
        return $this->xpathScalar(array(".//div[@class='card-header']//span[@data-test='discount-price']", ".//div[@class='card-header']//span[@data-test='original-price']"));
    }

    public function parseOldPrice()
    {

        return $this->xpathScalar(".//div[@class='card-header']//span[@data-test='original-price']");
    }

    public function parseManufacturer()
    {

        return $this->xpathScalar(".//div[contains(@class, 'card-sticky')]//a[@data-test='brand']");
    }

    public function parseImg()
    {
        return $this->xpathScalar(".//img[@data-test='image']/@src");
    }

    public function parseImgLarge()
    {
        
    }

    public function parseExtra()
    {
        $extra = array();
        return $extra;
    }

    public function isInStock()
    {
        if ($this->parsePrice())
            return true;
        else
            return false;
    }

}
