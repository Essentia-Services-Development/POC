<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/*
  Name: Lamoda UA
  URI: http://www.lamoda.ua
  Icon: http://www.google.com/s2/favicons?domain=lamoda.ua
  CPA: admitad, gdeslon, actionpay, cityads
 */

/**
 * LamodauaParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class LamodauaParser extends MicrodataShopParser {

    /**
     *
     * @var String Указывайте исходную кодировку страницы
     */
    protected $charset = 'utf-8';

    /**
     *
     * @var String Указывайте валюту магазина
     */
    protected $currency = 'UAH';

    /**
     * Парсер каталога магазина. Метод должен вернуть массив URL на карточки товаров.
     * @param Integer $max
     * @return Array
     */
    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//a[@class='products-list-item__link link']/@href"), 0, $max);
        return $urls;
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@class='ii-product-buy']//*[@class='ii-product__price-discount']");
    }

}
