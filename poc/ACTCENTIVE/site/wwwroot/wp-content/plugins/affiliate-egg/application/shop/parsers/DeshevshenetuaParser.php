<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * DeshevshenetuaParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class DeshevshenetuaParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'UAH';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//*[@class='product_title']/a/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://deshevshe.net.ua' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//h1[@itemprop='name']");
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//*[@id='description']");
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//*[@itemprop='price']/@content");
    }

    public function parseOldPrice()
    {
        return '';
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//*[@itemprop='brand']/@content");
    }

    public function parseImg()
    {
        $img = $this->xpathScalar(".//*[@itemprop='image']/@src");
        return preg_replace('/\.m\.jpg\?\d+$/', '.b.jpg', $img);
    }

    public function parseImgLarge()
    {
        return '';
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();
        $feature_names = $this->xpathArray(".//*[@id='characteristic']//th");
        $feature_values = $this->xpathArray(".//*[@id='characteristic']//td");
        for ($i = 0; $i < count($feature_names); $i++)
        {
            if (empty($feature_values[$i]))
                continue;
            $feature = array();
            $feature['name'] = sanitize_text_field($feature_names[$i]);
            $feature['value'] = sanitize_text_field($feature_values[$i]);
            $extra['features'][] = $feature;
        }

        $extra['comments'] = array();
        $users = $this->xpathArray(".//*[@class='reviews__comments__body']//*[@itemprop='author']");
        $comments = $this->xpathArray(".//*[@class='reviews__comments__body']//*[@itemprop='reviewBody']");
        $ratings = $this->xpathArray(".//*[@class='reviews__comments__body']//*[@itemprop='ratingValue']/@content");
        $dates = $this->xpathArray(".//*[@class='reviews__comments__body']//*[@itemprop='datePublished']");
        //$comments_positive = $this->xpathArray(".//*[@class='reviews__comments__body']//*[@class='comment-text positive']");
        //$comments_negative = $this->xpathArray(".//*[@class='reviews__comments__body']//*[@class='comment-text negative']");
        for ($i = 0; $i < count($comments); $i++)
        {
            $comment['comment'] = sanitize_text_field($comments[$i]);
            //if (!empty($comments_positive[$i]))
            //  $comment['comment'] .= "\r\n" . sanitize_text_field($comments_positive[$i]);
            //if (!empty($comments_negative[$i]))
            //  $comment['comment'] .= "\r\n" . sanitize_text_field($comments_negative[$i]);
            if (!empty($users[$i]))
                $comment['name'] = sanitize_text_field($users[$i]);
            if (!empty($ratings[$i]))
                $comment['rating'] = TextHelper::ratingPrepare($ratings[$i]);
            if (!empty($dates[$i]))
                $comment['date'] = strtotime($dates[$i]);
            $extra['comments'][] = $comment;
        }
        $extra['rating'] = TextHelper::ratingPrepare($this->xpathScalar(".//meta[@itemprop='ratingValue']/@content"));

        return $extra;
    }

    public function isInStock()
    {
        if (!$this->parsePrice() || trim($this->xpathScalar(".//*[@class='wareAvail']")) == 'Нет в наличии')
            return false;
        else
            return true;
    }

}
