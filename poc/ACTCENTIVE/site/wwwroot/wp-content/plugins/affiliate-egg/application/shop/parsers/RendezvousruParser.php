<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * RendezvousruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class RendezvousruParser extends MicrodataShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//a[@class='item-link']/@href");
    }

    public function parseDescription()
    {
        return '';
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//span[@class='item-price-old']/span[@class='item-price-value']");
    }

}
