<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * WildberriesruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class WildberriesruParser extends MicrodataShopParser {

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//a[contains(@class, 'j-open-full-product-card')]/@href");
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//*[@id='description']//p");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@class='inner-price j-final-saving']//del");
    }

}
