<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * PostelDeluxeruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class PostelDeluxeruParser extends MicrodataShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'RUB';

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//li[@class='item  portrait']//a/@href");
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//*[@class='product-name']/span");
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//*[@class='block block-product-info']//*[@class='price-box']//*[@class='special-price']");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@class='block block-product-info']//*[@class='price-box']//*[@class='old-price']//*[@class='price']");
    }

    public function parseImg()
    {
        return $this->xpathScalar(".//img[@id='image']/@src");
    }

}
