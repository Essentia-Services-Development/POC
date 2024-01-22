<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * EldoradoruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class EldoradoruParser extends MicrodataShopParser {

    protected $charset = 'windows-1251';
    protected $headers = array(
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language' => 'en-us,en;q=0.5',
        'Cache-Control' => 'no-cache',
        'Connection' => 'keep-alive',
    );

    public function parseCatalog($max)
    {
        return $this->xpathArray(array(".//ul/li[@data-product-index]//a/@href", ".//div[@itemprop='name']/a/@href"));
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//div[contains(@class, 'goodDescriptionText')]", true);
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@class='bigPriceContainer']//*[@class='product-box-price__old-el']");
    }

}
