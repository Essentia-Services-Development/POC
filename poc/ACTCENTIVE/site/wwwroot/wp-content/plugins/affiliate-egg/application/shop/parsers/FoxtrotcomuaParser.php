<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * FoxtrotcomuaParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class FoxtrotcomuaParser extends LdShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'UAH';

    // Incapsula !!
    
    public function parseCatalog($max)
    {
        return $this->xpathArray(array(".//div[@class='card__body']//a/@href"));
    }

    public function parseDescription()
    {
        return '';
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//div[@class='product-box__main-price__discount']/p");
    }

    public function parseImg()
    {
        if ($img = $this->xpathScalar(".//div[contains(@class, 'product-img__carousel')]/img/@src"))
            return $img;
        else
            return parent::parseImg();
    }

}
