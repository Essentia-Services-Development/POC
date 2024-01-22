<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * MrdomruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class MrdomruParser extends MicrodataShopParser {

    protected $charset = 'UTF-8';

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//a[@class='reset_link']/@href");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//div[contains(@class, 'price_and_card')]//*[@class='line_through']");
    }

}
