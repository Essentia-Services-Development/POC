<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * ShopvichyconsultruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com 
 * 
 * shop.vichyconsult.ru ---> www.vichyconsult.ru
 */
class ShopvichyconsultruParser extends MicrodataShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//div[@class='item-card__info']/a/@href");
    }

    public function parseDescription()
    {
        if ($d = $this->xpathScalar(".//*[@class='product-view__description']"))
            return $d;
        else
            return parent::parseDescription();
    }

    public function parseImg()
    {
        return str_replace('/image/58x/', '/image/', parent::parseImg());
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//p[@class='old-price']//span[@class='price']");
    }

    public function parseManufacturer()
    {
        return 'Vichy';
    }

}
