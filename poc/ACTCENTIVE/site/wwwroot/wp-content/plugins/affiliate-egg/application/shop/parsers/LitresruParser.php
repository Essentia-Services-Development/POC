<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * LitresruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class LitresruParser extends MicrodataShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        return $this->xpathArray(array(".//a[@class='img-a']/@href", ".//*[@class='art-item__name']/a/@href", ".//*[@class='booktitle']/div[1]/a/@href"));
    }

    public function parsePrice()
    {
        if (preg_match('~price: "(.+?)",~', $this->dom->saveHTML(), $matches))
            return $matches[1];
    }  
    
    
}
