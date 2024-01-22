<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * HoffruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class HoffruParser extends MicrodataShopParser {

    protected $charset = 'UTF-8';

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//a[@class='elem-product__img catalog']/@href");
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//h1");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//div[@class='discount-info']//span[@class='price-old']");
    }

}
