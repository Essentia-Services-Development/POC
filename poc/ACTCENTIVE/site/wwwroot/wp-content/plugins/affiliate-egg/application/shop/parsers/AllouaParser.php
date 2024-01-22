<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * AllouaParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class AllouaParser extends MicrodataShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'UAH';

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//*[@class='product-card__content']//a/@href");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//div[@class='v-price-box__old']//span[@class='sum']");

    }    
    
}
