<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 *  NotikruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class NotikruParser extends MicrodataShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//table[contains(@class,'goods_list_view')]//tr[contains(@class,'good_list_title')]/following::tr[contains(@cn-nam,'list_list_')][1]/td/a/@href"), 0, $max);
        if (!$urls)
            $urls = array_slice($this->xpathArray(".//table[contains(@class,'accessoryList')]//td/a[1]/@href"), 0, $max);
        if (!$urls)
            $urls = array_slice($this->xpathArray(".//ul[contains(@class,'resultNotesList')]/li//tr[@class='noteComplectation'][1]/td[1]//strong/a/@href"), 0, $max);
        if (!$urls)
            $urls = $this->xpathArray(".//*[@class='title']/a/@href");
        if (!$urls)
            $urls = $this->xpathArray(".//li[@class='marginBottom20  cn-blk']/a/@href");

        return $urls;
    }

    public function parseTitle()
    {
        if ($t = $this->xpathScalar(".//h1[@class='goodtitlemain']/text()"))
            return $t;
        else
            return parent::parseTitle();
    }

    public function parsePrice()
    {
        if ($t = $this->xpathScalar(".//span[@class='product-price']"))
            return $t;
        else
            return parent::parsePrice();
    }

    public function parseDescription()
    {
        return '';
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//div[@class='contBox2']//*[contains(@style, 'line-through')]");
    }

}
