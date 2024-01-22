<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * ComfyuaParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class ComfyuaParser extends MicrodataShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'UAH';

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//*[@class='product-item__img-wr']//a/@href");
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//h1");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@class='price-box__content price-box__content_old']");
    }

}
