<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/*
  Name: Lamoda RU
  URI: http://www.lamoda.ru
  Icon: http://www.google.com/s2/favicons?domain=lamoda.ru
  CPA: admitad, gdeslon, actionpay, cityads
 */

/**
 * LamodaruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class LamodaruParser extends MicrodataShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'RUB';
    protected $user_agent = array('DuckDuckBot', 'facebot', 'ia_archiver');
    protected $headers = array(
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
        'Accept-Language' => 'en-us,en;q=0.5',
        'Cache-Control' => 'no-cache',
        'Connection' => 'keep-alive',
    );

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//a[contains(@class, 'products-list-item__link')]/@href"), 0, $max);
        if (!$urls)
            $urls = array_slice($this->xpathArray(".//div[@class='ii-products-list-item']/a/@href"), 0, $max);
        return $urls;
    }

    public function parseOldPrice()
    {
        $html = $this->dom->saveHTML();
        if (preg_match("/original: '(\d+)',/", $html, $mathces))
            return $mathces[1];
    }

}
