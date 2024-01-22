<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * RuiherbcomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class RuiherbcomParser extends MicrodataShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'USD';
    protected $headers = array(
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language' => 'en-us,en;q=0.5',
        'Cache-Control' => 'no-cache',
        
    );
    
    
    public function parseCatalog($max)
    {

        $xpath = array(
            ".//div[@itemtype='http://schema.org/Product']//a[contains(@href, '/pr/')]/@href",
            ".//div[contains(@class, 'products')]//div[@class='absolute-link-wrapper']/a/@href",
            ".//*[contains(@class, 'product-inner')]/a[1]/@href",
            ".//div[@class='panel']//a[@class='image-link']/@href",
            ".//div[@id='display-results-content']//a[@class='image-link']/@href",
            ".//div[@class='panel']/article/a[@class='image-link']/@href",
            ".//div[@class='panel']//bdi//a[@class='product-image']/@href",
            ".//*[@class='absolute-link-wrapper']/a/@href",
            ".//*[contains(@class, 'product-link')]/a/@href",
        );
        
        return $this->xpathArray($xpath);
    }

    public function parseTitle()
    {
        if ($r = parent::parseTitle())
            return $r;
        $res = $this->xpathScalar(".//*[contains(@class, 'prod-title')]");
        $mnf = $this->parseManufacturer();
        $res = trim(str_replace($mnf, "", $res), ", ");
        return $res;
    }

    public function parsePrice()
    {
        if ($r = parent::parsePrice())
            return $r;

        $price = $this->xpathScalar(".//meta[@property='og:price:amount']/@content");
        if (!$price)
        // reviews page
            $price = $this->xpathScalar(".//*[@class='pricesWraper']/p[@class='prod-price']");
        return $price;
    }

    public function parseOldPrice()
    {

        $price = $this->xpathScalar(".//meta[@property='og:standard_price']/@content");
        if ($price < $this->parsePrice())
            return $price;

        $price = $this->xpathScalar(".//*[@id='price' and @class='col-xs-15 col-md-16 price strikethrough']");
        return $price;
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//*[@id='brand']//strong");
    }

    public function parseImg()
    {
        $res = $this->xpathScalar(".//meta[@property='og:images']//@content");
        $res = str_replace("/b/", "/v/", $res);
        if ($res)
            return $res;

        // reviews page
        $img = $this->xpathScalar(".//div[contains(@class,'prod-image-holder')]//img/@src");

        if (!$img)
            $img = $this->xpathScalar(".//img[@id='iherb-product-image']/@src");
        return $img;
    }

    public function parseImgLarge()
    {
        return $this->xpathScalar(".//div[contains(@class,'prod-im-big')]//img/@src");
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();

        $extra['features'] = array();

        $results = $this->xpathArray(".//ul[@id='product-specs-list']/li[not(div)]");
        $feature = array();
        foreach ($results as $res)
        {
            $expl = explode(":", $res, 2);
            if (count($expl) == 2)
            {
                $feature['name'] = \sanitize_text_field($expl[0]);
                $feature['value'] = \sanitize_text_field($expl[1]);
                $extra['features'][] = $feature;
            }
        }

        $extra['images'] = array();
        $results = $this->xpathArray(".//div[@class='smImHolder']//img/@src");
        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            $extra['images'][] = str_replace('/b/', '/l/', $res);
        }

        $extra['comments'] = array();
        $comments = $this->xpathArray(".//div[@class='recent-reviews' or @class='prodOverview-section']//bdi/p");
        $ratings = $this->xpathScalar(".//div[@class='recent-reviews' or @class='prodOverview-section']//*[@class='rating']//a/i[2]/@class");
        for ($i = 0; $i < count($comments); $i++)
        {
            $comment['comment'] = sanitize_text_field($comments[$i]);

            if (!empty($ratings[$i]))
            {
                preg_match('/icon-stars_(\d+)/', $ratings[$i], $matches);
                if ($matches)
                    $comment['rating'] = TextHelper::ratingPrepare($matches[1]);
            }
            $extra['comments'][] = $comment;
        }

        return $extra;
    }

    public function getCurrency()
    {
        if ($r = $this->xpathScalar(".//meta[@property='og:price:currency']/@content"))
            return $r;
        else
            return $this->currency;
    }

}
