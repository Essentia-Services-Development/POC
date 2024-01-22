<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * MytoysruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class MytoysruParser extends MicrodataShopParser {

    protected $charset = 'UTF-8';

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//a[@class='ddl_product_link']/@data-link");
    }

    public function parseTitle()
    {
        if ($t = $this->xpathScalar(".//h1//span[@id='headInnerSKU']"))
            return $t;
        else
            return parent::parseTitle();
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//span[@id='productcancelledprice1']");
    }

}
