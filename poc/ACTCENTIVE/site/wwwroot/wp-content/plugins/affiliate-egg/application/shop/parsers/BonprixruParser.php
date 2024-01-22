<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * BonprixruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class BonprixruParser extends MicrodataShopParser {

    protected $currency = 'RUB';

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//*[@class='product-image']/a/@href");
    }

    public function parseImg()
    {
        return str_replace('https://www.bonprix.ru//image01.bonprix.ru', 'https://image01.bonprix.ru', parent::parseImg());
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@class='product-offer_container']//*[@class='integer-place']");
    }

}
