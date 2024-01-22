<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * FirstcrycomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class FirstcrycomParser extends MicrodataShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'INR';
    protected $json_data = array();

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//*[@id='maindiv']//div[@class='list_img wifi']/a/@href");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//div[@class='div-prod-price']//*[@id='original_mrp']");
    }

}
