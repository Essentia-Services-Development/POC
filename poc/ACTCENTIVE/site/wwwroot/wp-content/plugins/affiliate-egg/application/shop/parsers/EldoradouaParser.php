<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * EldoradouaParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class EldoradouaParser extends MicrodataShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'UAH';

    // Incapsula !!
    
    public function parseCatalog($max)
    {
        return $this->xpathArray(".//*[@class='good-description']//a/@href");
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//h1");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@class='content-information']//*[contains(@class, 'old-price-value')]");
    }

}
