<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/*
  Name: Piluli.ru
  URI: http://www.piluli.ru
  Icon: http://www.google.com/s2/favicons?domain=piluli.ru
  CPA: admitad, gdeslon, actionpay, cityads
 */

/**
 * LamodauaParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class PiluliruParser extends ShopParser {

    /**
     *
     * @var String Указывайте исходную кодировку страницы
     */
    protected $charset = 'utf-8';

    /**
     *
     * @var String Указывайте валюту магазина
     */
    protected $currency = 'RUB';

    /**
     * Парсер каталога магазина. Метод должен вернуть массив URL на карточки товаров.
     * @param Integer $max
     * @return Array
     */
    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//*[@id='filter_results']//a[@class='item_img']/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://www.piluli.ru' . $url;
        }
        return $urls;
    }

    /**
     * Название товара
     * @return String
     */
    public function parseTitle()
    {
        return trim($this->xpathScalar(".//h1[@itemprop='name']"));
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//meta[@itemprop='description']/@content");
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//*[@itemprop='offers']//*[@itemprop='price']");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@itemprop='offers']//*[@class='b-talk__market-oldprice']");
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//meta[@itemprop='brand']/@content");
    }

    public function parseImg()
    {
        $img = $this->xpathScalar(".//meta[@property='og:image']/@content");
        if (!$img)
            $img = $this->xpathScalar(".//div[@class='b-photo']//img/@src");
        if ($img && !preg_match('/^http:\/\//', $img))
            $img = 'http://www.piluli.ru/' . $img;
        return $img;
    }

    /**
     * Большая картинка товара.
     * @return String
     */
    public function parseImgLarge()
    {
        return '';
    }

    public function parseExtra()
    {
        $extra = array();
        $extra['features'] = array();
        $extra['images'] = array();


        $extra['comments'] = array();
        $users = $this->xpathArray(".//div[@class='comments_block']//span[@class='review_user_name']");
        $dates = $this->xpathArray(".//div[@class='comments_block']//div[@class='product_reviews_comments']/span[2]");
        $comments = $this->xpathArray(".//div[@class='comments_block']//div[@class='comments_block_item_text']");
        for ($i = 0; $i < count($comments); $i++)
        {
            if (!empty($comments[$i]))
            {

                $comment['name'] = '';
                if (isset($users[$i]))
                {
                    $comment['name'] = sanitize_text_field($users[$i]);
                }

                $comment['date'] = '';
                if (isset($dates[$i]))
                {
                    $comment['date'] = strtotime($dates[$i]);
                }

                $comment['comment'] = sanitize_text_field($comments[$i]);
                $extra['comments'][] = $comment;
            }
        }

        return $extra;
    }

    public function isInStock()
    {
        $availability = $this->xpathScalar(".//meta[@itemprop='availability']/@content");
        if ($availability == 'http://schema.org/InStock')
            return true;
        else
            return false;
    }

}
