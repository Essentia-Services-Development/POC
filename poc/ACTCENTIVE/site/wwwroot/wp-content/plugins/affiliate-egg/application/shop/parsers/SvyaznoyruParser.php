<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * SvyaznoyruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class SvyaznoyruParser extends LdShopParser {

    protected $charset = 'utf-8';
    protected $user_agent = array('DuckDuckBot', 'facebot', 'ia_archiver');
    protected $headers = array(
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
        'Accept-Language' => 'en-us,en;q=0.5',
        'Cache-Control' => 'no-cache',
        'Connection' => 'keep-alive',
        'Accept-Encoding' => 'identity',
    );

    public function parseCatalog($max)
    {
        return array_slice($this->xpathArray(".//a[@class='b-product-block__main-link']/@href"), 0, $max);
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//div[@class='b-offer-box__price-old']/s");
    }

}
