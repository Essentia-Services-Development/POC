<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/*
  Name: Bodybuilding.com
  URI: http://www.bodybuilding.com
  Icon: http://www.google.com/s2/favicons?domain=bodybuilding.com
  Search URI: http://search.bodybuilding.com/bbsearch/slp/full?context=store&query=%KEYWORD%
  CPA:
 */

/**
 * BodybuildingcomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class BodybuildingcomParser extends LdShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'USD';

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//*[@class='product__img-wrapper']//a/@href");
    }

    public function parsePrice()
    {
        if ($p = $this->xpathScalar("(.//*[@class='sku-chooser__info']//*[contains(@class, 'sku-chooser__sale-price')])[2]"))
        {
            $p = trim($p, " \r\n.,");
            return $p;
        } else
            return parent::parsePrice();
    }

    public function parseOldPrice()
    {
        //return $this->xpathScalar("(.//div[contains(@id,'right-content-prod')]//span[@class='strike'])[1]");
    }

}
