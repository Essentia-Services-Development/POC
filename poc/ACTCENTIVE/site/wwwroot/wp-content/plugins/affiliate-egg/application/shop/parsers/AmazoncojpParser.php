<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * AmazoncojpParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2019 keywordrush.com
 */
require_once dirname(__FILE__) . '/AmazoncomParser.php';

class AmazoncojpParser extends AmazoncomParser {

    protected $canonical_domain = 'https://www.amazon.co.jp';
    protected $currency = 'JPY';

    public function parsePrice()
    {
        if ($price = $this->xpathScalar(".//*[@class='a-lineitem']//*[@id='priceblock_ourprice']"))
            return $price;
        else
            return parent::parsePrice();
    }

}
