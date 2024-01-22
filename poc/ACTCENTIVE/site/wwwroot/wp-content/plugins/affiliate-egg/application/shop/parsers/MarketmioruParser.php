<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

use Keywordrush\AffiliateEgg\TextHelper;

/**
 * MarketmioruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class MarketmioruParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'RUB';

    public function parseCatalog($max)
    {

        $urls = $this->xpathArray(".//*[@class='category']//h2[@class='product__name']/a/@href");
        $urls = array_slice($urls, 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^http/', $url))
                $urls[$i] = 'https://marketmio.ru' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//h1[contains(@class, 'page-head__title')]");
    }

    public function parseDescription()
    {
        return join('; ', $this->xpathArray(".//div[@class='product__foot']/ul/li"));
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//strong[contains(@class, 'product__price-average')]");
    }

    public function parseOldPrice()
    {
        
    }

    public function parseManufacturer()
    {
        
    }

    public function parseImg()
    {

        return $this->xpathScalar(".//*[@class='product__body']//img[@class='product__image']/@src");
    }

    public function parseImgLarge()
    {
        
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();
        $names = $this->xpathArray(".//ul[@class='specs__list']//li[@class='specs__item']/span[1]");
        $values = $this->xpathArray(".//ul[@class='specs__list']//li[@class='specs__item']/span[2]");
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
        $comments = $this->xpathArray(".//div[contains(@class, 'reviews')]//div[contains(@class, 'review__body')]");
        $ratings = $this->xpathArray(".//div[contains(@class, 'reviews')]//span[@class='value']/span/@title");

        for ($i = 0; $i < count($comments); $i++)
        {
            $comment['comment'] = sanitize_text_field($comments[$i]);
            if (!empty($ratings[$i]))
                $comment['rating'] = TextHelper::ratingPrepare($ratings[$i]);

            $extra['comments'][] = $comment;
        }

        $extra['images'] = array();
        $results = $this->xpathArray(".//*[@class='product__body']//img[@class='product__image']/@src");
        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            $extra['images'][] = $res;
        }

        $prices = $this->xpathScalar(".//*[@class='product__price-range price__range']");
        $prices = explode(' – ', $prices);

        if (count($prices) == 2)
        {
            $extra['min_price'] = TextHelper::parsePriceAmount($prices[0]);
            $extra['max_price'] = TextHelper::parsePriceAmount($prices[1]);
        }

        $extra['rating'] = $this->xpathScalar(".//*[contains(@class, 'rating')]//span[@class='value']/span/@title");

        return $extra;
    }

    public function isInStock()
    {
        return true;
    }

}
