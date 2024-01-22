<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * CitycomuaParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class CitycomuaParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'UAH';

    public function parseCatalog($max)
    {
        $urls = $this->xpathArray(".//*[@class='right_col']//*[@class='catalog_goods_title']/a/@href");
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://city.com.ua' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//h1");
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//*[@class='review_cont']");
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//*[@itemprop='price']");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@class='old_price']");
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//*[@itemprop='brand']");
    }

    public function parseImg()
    {
        $img = $this->xpathScalar(".//img[@itemprop='image']/@src");

        if (!preg_match('/^https?:\/\//', $img))
            $img = 'http://city.com.ua' . $img;
        return $img;
    }

    public function parseImgLarge()
    {
        $img = $this->parseImg();
        return str_replace('/s_320/', '/original/', $img);
    }

    public function parseExtra()
    {
        $extra = array();
        $extra['features'] = array();
        $names = $this->xpathArray(".//*[@id='id4']//td[1]");
        $values = $this->xpathArray(".//*[@id='id4']//td[2]");
        $i = 0;
        foreach ($names as $name)
        {
            if (!isset($values[$i]))
                continue;
            $feature['name'] = \sanitize_text_field($name);
            $feature['value'] = \sanitize_text_field($values[$i]);
            $extra['features'][] = $feature;
            $i++;
        }

        $extra['images'] = array();
        $images = $this->xpathArray(".//ul[@class='thumbnails']//li[position() > 1]/img/@src");
        foreach ($images as $img)
        {
            $img = str_replace('/s_100/', '/s_320/', $img);
            if (!preg_match('/^https?:\/\//', $img))
                $img = 'http://city.com.ua' . $img;
            $extra['images'][] = $img;
        }

        $extra['comments'] = array();
        $users = $this->xpathArray(".//*[@itemprop='review']//*[@itemprop='author']");
        $comments = $this->xpathArray(".//*[@itemprop='review']//*[@itemprop='reviewBody']");
        $ratings = $this->xpathArray(".//*[@itemprop='review']//*[@itemprop='ratingValue']");
        $dates = $this->xpathArray(".//*[@itemprop='review']//*[@itemprop='datePublished']");

        for ($i = 0; $i < count($comments); $i++)
        {
            $comment['comment'] = sanitize_text_field($comments[$i]);
            $comment['comment'] = trim(str_replace('Читать полностью', '', $comment['comment']));
            if (!empty($users[$i]))
                $comment['name'] = sanitize_text_field($users[$i]);
            if (!empty($ratings[$i]))
                $comment['rating'] = TextHelper::ratingPrepare($ratings[$i]);
            if (!empty($dates[$i]))
                $comment['date'] = strtotime($dates[$i]);
            $extra['comments'][] = $comment;
        }

        $extra['rating'] = TextHelper::ratingPrepare($this->xpathScalar(".//*[@itemprop='ratingValue']"));
        return $extra;
    }

    public function isInStock()
    {
        if ($this->xpathScalar(".//*[@itemprop='availability']/@href") == 'http://schema.org/OutOfStock')
            return false;
        else
            return true;
    }

}
