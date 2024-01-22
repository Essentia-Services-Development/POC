<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * IntimshopruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class IntimshopruParser extends LdShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//div[contains(@class,'grid')]//div[@class='item_picture']/a/@href");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//p[@class='item_price']//span[@class='old_price']");
    }

}
