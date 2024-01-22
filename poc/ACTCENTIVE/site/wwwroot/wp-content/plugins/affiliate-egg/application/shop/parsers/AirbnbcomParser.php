<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * AirbnbcomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
require_once dirname(__FILE__) . '/AirbnbruParser.php';

class AirbnbcomParser extends AirbnbruParser {

    protected $charset = 'utf-8';
    protected $currency = 'USD';

    public function parseCatalog($max)
    {
        $urls = parent::parseCatalog($max);
        foreach ($urls as $i => $url)
        {
            $urls[$i] = str_replace('https://www.airbnb.ru', 'https://www.airbnb.com', $url);
        }

        return $urls;
    }

    public function getCurrency()
    {
        $currency = $this->xpathScalar(".//meta[@itemprop='priceCurrency']/@content");
        if (!$currency)
            $currency = 'USD';
        return $currency;
    }

}
