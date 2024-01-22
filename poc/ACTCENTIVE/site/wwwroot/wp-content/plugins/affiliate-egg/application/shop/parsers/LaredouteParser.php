<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/*
  Name: Laredoute.ru
  URI: http://www.laredoute.ru
  Icon: http://www.google.com/s2/favicons?domain=laredoute.ru
  CPA: gdeslon
 */

/**
 * LaredouteParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2018 keywordrush.com
 */
class LaredouteParser extends LdShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'RUB';
    //protected $user_agent = array('DuckDuckBot', 'facebot', 'ia_archiver');
    //protected $user_agent = array('wget');
    protected $user_agent = array('Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:73.0) Gecko/20100101 Firefox/73.0');
    protected $headers = array(
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language' => 'en-us,en;q=0.5',
        'Cache-Control' => 'no-cache',
        'Connection' => 'keep-alive',
    );

    function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//li[contains(@data-cerberus, 'area_plpProduit_product')]/a/@href"), 0, $max);
        return $urls;
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(array(".//*[@class='price-block']//*[@class='price-info line-through']", ".//div[@class='price']/span[@class='sale-price-before']"));
    }

}
