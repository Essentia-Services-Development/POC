<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * VangoldruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class VangoldruParser extends MicrodataShopParser {

    protected $charset = 'UTF-8';

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//div[@class='picture']/a/@href");
    }

    public function parseTitle()
    {
        if ($t = $this->xpathScalar(".//h1"))
            return $t;
        else
            return parent::parseTitle();
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//div[@class='old_price_new_style']");
    }

}
