<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * NeboruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class NeboruParser extends MicrodataShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//div[@class='item__info']/a/@href");
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//h1");
    }

    public function parseImg()
    {
        return $this->xpathScalar(".//div[@class='slide']/img/@src");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//div[@class='product_price product_price_old']");
    }

}
