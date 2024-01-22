<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * InfibeamParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class InfibeamParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'INR';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//*[@id='content-slot']//div[@class='slide']//div[@class='title']/a/@href"), 0, $max);
        if (!$urls)
            $urls = array_slice($this->xpathArray(".//*[@id='resultPane']//div[@class='title']/a/@href"), 0, $max);
        foreach ($urls as $key => $url)
        {
            if (!preg_match('/^https?:/', $url))
                $urls[$key] = 'https://www.infibeam.com' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//h1[@itemprop='name']");
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//*[@id='productDetails']//div[@class='catalog-desc']");
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//*[@id='price-after-discount']/span[@class='price' or @class='currentPrice']");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@id='base-price']/span[contains(@class, 'price')]");
    }

    public function parseManufacturer()
    {
        return '';
    }

    public function parseImg()
    {
        $img = $this->xpathScalar(".//meta[@property='og:image']/@content");
        if (!$img)
            return '';
        return str_replace('x200x200.jpg', 'x320x320.jpg', $img);
    }

    public function parseImgLarge()
    {
        $img = $this->xpathScalar(".//meta[@property='og:image']/@content");
        if (!$img)
            return '';
        return str_replace('x200x200.jpg', 'x607x1000.jpg', $img);
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();
        $names = $this->xpathArray(".//*[@id='productFeatures']//td[1]");
        $values = $this->xpathArray(".//*[@id='productFeatures']//td[2]");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]))
            {
                $feature['name'] = sanitize_text_field($names[$i]);
                $feature['value'] = sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }

        $extra['comments'] = array();
        $users = $this->xpathArray(".//*[@id='reviews-detail']//div[@class='review']/b");
        $dates = $this->xpathArray(".//*[@id='reviews-detail']//span[@class='easy-date']");
        $comments = $this->xpathArray(".//*[@id='reviews-detail']//p[@class='review-text']");
        $ratings = $this->xpathArray(".//*[@id='reviews-detail']//span[@class='rating-star']/img/@alt");
        for ($i = 0; $i < count($comments); $i++)
        {
            $comment['comment'] = sanitize_text_field($comments[$i]);
            if (isset($dates[$i]))
                $comment['date'] = strtotime($dates[$i]);
            if (isset($users[$i]))
                $comment['user'] = trim($users[$i]);
            if (isset($ratings[$i]))
                $comment['rating'] = TextHelper::ratingPrepare(self::getStarRating($ratings[$i]));

            $extra['comments'][] = $comment;
        }

        $rating_str = $this->xpathScalar(".//*[@itemprop='aggregateRating']//img/@alt");
        if ($rating_str)
        {
            $extra['rating'] = TextHelper::ratingPrepare(self::getStarRating($rating_str));
        }

        return $extra;
    }

    public function isInStock()
    {
        $in_stock = trim($this->xpathScalar(".//*[@id='product_overview']/div"));
        if ($in_stock == 'In Stock.')
            return true;
        else
            return false;
    }

    private static function getStarRating($str)
    {
        preg_match('/\s\d\s/', $str, $matches);
        if ($matches)
            return (int) trim($matches[0]);
        else
            return null;
    }

}
