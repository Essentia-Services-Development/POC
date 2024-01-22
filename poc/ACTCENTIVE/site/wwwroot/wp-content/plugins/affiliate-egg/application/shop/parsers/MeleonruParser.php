<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * MeleonruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class MeleonruParser extends MicrodataShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//h3[@class='catalog-list-item-title']/a/@href");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@class='good-sidebar-price__value good-sidebar-price__value_old']");
    }

    public function parseImg()
    {
        if (preg_match("/\[\{'src':'(.+?)'/", $this->dom->saveHTML(), $matches))
            return str_replace('/60_60_1/', '/520_520_1/', $matches[1]);
        else
            return parent::parseImg();
    }

    public function parseManufacturer()
    {
        return '';
    }

}
