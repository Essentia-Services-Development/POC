<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * JumiacokeParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
require_once dirname(__FILE__) . '/JumiacomegParser.php';

class JumiacokeParser extends JumiacomegParser {

    protected $charset = 'utf-8';
    protected $currency = 'KES';

    public function parsePrice()
    {
        $price = $this->xpathScalar("(.//*[@class='price-box']//*/@data-price)[1]");
        if ($price)
            return $price;
        else
            return parent::parsePrice();
    }

}
