<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * KaratovruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class KaratovruParser extends MicrodataShopParser {

    protected $charset = 'UTF-8';

    public function parseCatalog($max)
    {
        return array_slice($this->xpathArray(".//a[@class='b-product-item__preview']/@href"), 0, $max);
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//*[@class='b-buy-form__title']");
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//*[@class='b-buy-form__price-value']/text()");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@class='b-buy-form__price-info']//del");
    }

    public function parseImg()
    {
        return $this->xpathScalar(".//*[@class='standard-preview']/img/@src");
    }

}
