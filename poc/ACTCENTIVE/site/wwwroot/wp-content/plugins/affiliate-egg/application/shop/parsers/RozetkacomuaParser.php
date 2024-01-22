<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * RozetkacomuaParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class RozetkacomuaParser extends LdShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'UAH';

    public function parseCatalog($max)
    {
        if ($urls = $this->xpathArray(".//a[@class='goods-tile__heading']/@href"))
            return $urls;

        // category
        if (preg_match_all('/price_pcs&q;:&q;.+?&q;,&q;href&q;:&q;(.+?)&q;,&q;comments_amount/', $this->dom->saveHTML(), $matches))
            return $matches[1];

        // search
        if ($urls = $this->_parseSearchPage())
            return $urls;

        if ($this->parseTitle())
        {
            // redirect from search to product page
            return array($this->xpathScalar(".//meta[@property='og:url']/@content"));
        }
        return array();
    }

    private function _parseSearchPage()
    {
        if (!preg_match('~text=(.+)&~', $this->getUrl(), $matches))
            return array();

        $keyword = $matches[1];
        try
        {
            $result = $this->requestGet('https://search.rozetka.com.ua/search/api/v4/?text=' . $keyword . '', false);
        } catch (\Exception $e)
        {
            return array();
        }
        $result = json_decode($result, true);
        if (!$result || !isset($result['data']['goods']))
            return array();
        $urls = array();
        foreach ($result['data']['goods'] as $item)
        {
            $urls[] = $item['href'];
        }
        return $urls;
    }

    public function parseTitle()
    {
        if ($p = parent::parseTitle())
            return $p;
        else
            return $this->xpathScalar(".//h1[@class='detail-title h1']");
    }

    public function parseDescription()
    {
        if ($d = $this->xpathScalar(".//p[@class='product-about__brief']"))
            return $d;
        else
            return parent::parseDescription();
    }

    public function parsePrice()
    {
        if ($p = parent::parsePrice())
            return $p;
        else
            return $this->xpathScalar(array(".//meta[@itemprop='price']/@content", ".//div[@class='detail-price-uah']//span[@id='price_label']"));
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//div[@class='product-about__block']//p[@class='product-prices__small']");
    }

    public function parseImg()
    {
        if ($img = parent::parseImg())
            return $img;
        else
            return $this->xpathScalar(array(".//img[@class='product-photo__picture']/@src", ".//div[@id='basic_image']//img/@src"));
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();

        $extra['features'] = array();
        $names = $this->xpathArray(".//section[@class='characteristics-full__group']//dt");
        $values = $this->xpathArray(".//section[@class='characteristics-full__group']//dd");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]) && !empty($names[$i]))
            {
                $feature['name'] = \sanitize_text_field($names[$i]);
                $feature['value'] = \sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }


        $extra['images'] = $this->xpathArray(".//*[@id='preview_details']/div[position() > 1]/a/@data-accord-url");

        if (!$extra['images'])
        {
            $images = array();
            if (preg_match_all('/&q;url&q;:&q;(https:\/\/[a-z0-9_\.\/]+?\.jpg)&q;,&q;width&q;.+?big&/ims', $this->dom->saveHTML(), $matches))
                $extra['images'] = $matches[1];
        }

        $extra['comments'] = array();
        $users = $this->xpathArray(".//*[@id='comments']//*[@itemprop='review']//*[@itemprop='author']");
        $comments = $this->xpathArray(".//*[@id='comments']//*[@itemprop='review']//*[@class='pp-review-text']");
        $ratings = $this->xpathArray(".//*[@id='comments']//*[@itemprop='review']//*[@itemprop='ratingValue']/@content");
        $dates = $this->xpathArray(".//*[@id='comments']//*[@itemprop='review']//*[@itemprop='datePublished']");

        for ($i = 0; $i < count($comments); $i++)
        {
            $comment['comment'] = sanitize_text_field($comments[$i]);
            $comment['comment'] = preg_replace('/\.\.\..+?Еще/', '', $comment['comment']);

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

}
