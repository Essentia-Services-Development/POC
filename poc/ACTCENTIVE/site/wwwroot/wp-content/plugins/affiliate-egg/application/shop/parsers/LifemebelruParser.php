<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * MebelviaruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class LifemebelruParser extends MicrodataShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'RUB';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//*[@id='catalog_list']//a[@class='photo']/@href"), 0, $max);
        foreach ($urls as $key => $url)
        {
            $urls[$key] = 'http://lifemebel.ru' . $url;
        }
        return $urls;
    }

    /**
     * Описание товара
     * @return String
     */
    public function parseDescription()
    {
        return $this->xpathScalar(".//div[@class='item_description_text']");
    }

    /**
     * Цена
     * @return String
     */
    public function parsePrice()
    {
        if ($p = $this->xpathScalar(".//div[contains(@class, 'product-price-number')]"))
            return $p;

        $html = $this->dom->saveHTML();

        if (preg_match("/'PROPERTY_ACTION_PRICE':'(.+?)'/", $html, $matches) && TextHelper::parsePriceAmount($matches[1]))
            return $matches[1];

        if (preg_match("/'PROPERTY_SALE_PRICE':'(.+?)'/", $html, $matches) && TextHelper::parsePriceAmount($matches[1]))
            return $matches[1];

        if ($price = $this->xpathScalar(".//*[@class='present']//*[@itemprop='price']/@content"))
            return $price;
        return parent::parsePrice();
    }

    /**
     * Старая цена (без скидки)
     * @return String
     */
    public function parseOldPrice()
    {
        if ($p = $this->xpathScalar(".//*[@itemtype='http://schema.org/Offer']//div[@class='old']"))
            return $p;
        if ($price = $this->xpathScalar(".//*[@class='present']//*[@itemprop='price']/@content"))
            return $price;
        return parent::parsePrice();
    }

}
