<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

use Keywordrush\AffiliateEgg\TextHelper;

/**
 * TomtopcomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2018 keywordrush.com
 */
class TomtopcomParser extends MicrodataShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'USD';

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//*[@class='productTitle']/@href");
    }

    public function parseOldPrice()
    {
        
    }

    public function getCurrency()
    {
        $currency = $this->xpathScalar(".//*[@class='top_nav_right']//span[@class='flag_currency']");
        if ($currency && strlen($currency) == 3)
            return $currency;
        else
            return parent::getCurrency();
    }

}
