<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * MediamarktruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class MediamarktruParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:60.0) Gecko/20100101 Firefox/60.0';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//*[@class='catalog__content']//div[@class='product__image--wrap']/a/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'https://www.mediamarkt.ru' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//h1[@class='card__title']");
    }

    public function parseDescription()
    {
        return $this->xpathScalar("(.//div[contains(@class,'reveal-block__content') and contains(@class,'reveal-block__content--block')])[1]");
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//div[@class='card-info__price']//div[contains(@class, 'price')]");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//div[@class='price__old--num price__old--item-old-price']");
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//*[@itemprop='brand']");
    }

    public function parseImg()
    {
        $img = $this->xpathScalar(".//*[@itemprop='image']/@content");
        if (!$img)
            $img = $this->xpathScalar(".//meta[@property='og:image']/@content");
        return $img;
    }

    public function parseImgLarge()
    {
        return str_replace('/400x400.', '/1000x1000.', $this->parseImg());
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();
        $names = $this->xpathArray(".//ul[@itemprop='description']//span[@class='characteristics__text']");
        $values = $this->xpathArray(".//ul[@itemprop='description']//span[@class='characteristics__spec']");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (isset($values[$i]))
            {
                $feature['name'] = sanitize_text_field($names[$i]);
                $feature['value'] = sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }

        $extra['comments'] = array();
        $users = $this->xpathArray(".//*[@class='reveal-block__content']//div[@itemprop='author']");
        $dates = $this->xpathArray(".//*[@class='reveal-block__content']//div[@itemprop='datePublished']");
        $ratings = $this->xpathArray(".//*[@class='reveal-block__content']//div[@itemprop='ratingValue']");
        $comments = $this->xpathArray(".//*[@class='reveal-block__content']//div[@itemprop='description']");
        for ($i = 0; $i < count($comments); $i++)
        {
            $comment['comment'] = sanitize_text_field($comments[$i]);
            if (!empty($users[$i]))
                $comment['name'] = sanitize_text_field($users[$i]);
            if (!empty($ratings[$i]))
                $comment['rating'] = TextHelper::ratingPrepare($ratings[$i]);
            if (!empty($dates[$i]))
                $comment['date'] = strtotime($dates[$i]);
            $extra['comments'][] = $comment;
        }
        $extra['rating'] = TextHelper::ratingPrepare($this->xpathScalar(".//*[@itemprop='ratingValue']/@content"));
        return $extra;
    }

    public function isInStock()
    {
        if ($this->parsePrice())
            return true;
        else
            return false;
    }

}
