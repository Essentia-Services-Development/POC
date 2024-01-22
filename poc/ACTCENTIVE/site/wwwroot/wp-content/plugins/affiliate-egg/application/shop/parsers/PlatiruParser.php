<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * PlatiruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class PlatiruParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $headers = array(
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language' => 'ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
        'Cache-Control' => 'max-age=0',
        'Connection' => 'keep-alive',
    );

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//table[contains(@class, 'goods-table')]//td[@class='product-title']//a/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://plati.ru' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//div[@class='content_center']/h1");
    }

    public function parseDescription()
    {
        return trim($this->xpathScalar(".//div[@class='goods_full_descr']//p"));
    }

    public function parsePrice()
    {
        
    }

    public function parseOldPrice()
    {
        
    }

    public function parseManufacturer()
    {
        
    }

    public function parseImg()
    {
        return $this->xpathScalar(".//div[@class='goods_descr_images']//a/img/@src");
    }

    public function parseImgLarge()
    {
        
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['comments'] = array();
        $comments = $this->xpathArray(".//div[@class='goods_reviews_content']//span[@class='review_text']");
        $dates = $this->xpathArray(".//div[@class='goods_reviews_content']//i[@class='good_review']");

        for ($i = 0; $i < count($comments); $i++)
        {
            $comment['comment'] = sanitize_text_field($comments[$i]);
            if (!empty($dates[$i]))
            {
                $date_parts = explode(';', $dates[$i]);
                $comment['date'] = strtotime($date_parts[0]);
            }
            $extra['comments'][] = $comment;
        }

        return $extra;
    }

    public function isInStock()
    {
        return true;
    }

}
