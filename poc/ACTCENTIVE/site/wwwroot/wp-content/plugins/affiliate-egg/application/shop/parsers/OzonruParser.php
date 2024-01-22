<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * OzonruParser class file 
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class OzonruParser extends MicrodataShopParser {

    protected $charset = 'utf-8';
    protected $headers = array(
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language' => 'ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
        'Cache-Control' => 'max-age=0',
        'Connection' => 'keep-alive',
    );

    public function parseCatalog($max)
    {
        // Incapsula installeds        
        return $this->xpathArray(".//a[@class='img-wrapper']/@href");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//div[@class='discount discount-block']//span[@class='price-number cross']");
    }

}
