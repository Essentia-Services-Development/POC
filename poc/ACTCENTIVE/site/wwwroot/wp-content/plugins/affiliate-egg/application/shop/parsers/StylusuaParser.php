<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * StylusuaParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class StylusuaParser extends LdShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'UAH';

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//*[@class='name-block']/a/@href");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@class='price-block']//*[@class='old-price']");
    }

}
