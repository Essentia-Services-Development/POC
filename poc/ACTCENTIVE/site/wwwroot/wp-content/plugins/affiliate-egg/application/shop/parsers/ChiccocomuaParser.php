<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * ChiccocomuaParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class ChiccocomuaParser extends MicrodataShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'UAH';

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//*[@class='product__item--imgs--inner']//a/@href");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//div[@class='__old']/span");
    }

    public function parseImg()
    {
        return $this->xpathScalar(".//meta[@property='og:image']/@content");
    }

}
