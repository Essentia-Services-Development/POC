<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * MoyouaParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class MoyouaParser extends MicrodataShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'UAH';

    public function parseCatalog($max)
    {
        if ($urls = $this->xpathArray(".//*[contains(@class, 'product-tile_title')]//a/@href"))
            return $urls;

        if ($this->parseTitle())
        {
            // redirect from search to product page
            return array($this->xpathScalar(".//link[@rel='canonical']/@href"));
        }
        return array();
    }

    public function parseDescription()
    {
        return '';
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@class='tovar_info']//*[@class='old_price']");
    }

}
