<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * TomtailoronlineruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class TomtailoronlineruParser extends MicrodataShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//a[contains(@class,'product-tile__link']/@href");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//div[@class='m-product-info-tmpl__product-head']//del");
    }

    public function parseManufacturer()
    {
        return "Tom Tailor";
    }

}
