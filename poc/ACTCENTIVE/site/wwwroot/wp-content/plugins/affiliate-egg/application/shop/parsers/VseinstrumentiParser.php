<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * VseinstrumentiParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class VseinstrumentiParser extends ShopParser {

    protected $charset = 'utf-8';
    //protected $user_agent = array('DuckDuckBot', 'facebot', 'ia_archiver');

    protected $headers = array(
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
        'Accept-Language' => 'en-us,en;q=0.5',
        'Cache-Control' => 'no-cache',
        'Connection' => 'keep-alive',
    );

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//*[@id='goodsListing']//li//a[@itemprop='url']/@href"), 0, $max);
        if (!$urls)
            $urls = array_slice($this->xpathArray(".//*[@id='goodsListingBox']//li//div[@class='catalogItemName']/a/@href"), 0, $max);
        if (!$urls)
            $urls = array_slice($this->xpathArray(".//*[contains(@class, 'catalogItem')]//li//div[@class='catalogItemName']/a/@href"), 0, $max);
        if (!$urls)
            $urls = array_slice($this->xpathArray(".//*[@class='product-name']/a[@class='link']/@href"), 0, $max);

        // subdomains support
        $host = parse_url($this->url, PHP_URL_HOST);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^http:\/\//', $url))
                $urls[$i] = 'http://' . $host . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//h1[@itemprop='name']");
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//div[@itemprop='description']/p");
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//meta[@itemprop='price']/@content");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//div[@itemtype='http://schema.org/Offer']//*[@class='saled-price-value']");
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//div[@class='nav']//div[last()]//span");
    }

    public function parseImg()
    {
        $img = $this->xpathScalar(".//img[@itemprop='image']/@src");
        return $img;
    }

    public function parseImgLarge()
    {
        return str_replace('/350x315/', '/1500x1350/', $this->parseImg());
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();
        $feature = array();
        $names = $this->xpathArray(".//*[@id='goodThValue']//span[@class='thName']");
        $values = $this->xpathArray(".//*[@id='goodThValue']//span[@class='thValue']");

        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]) && trim($names[$i]) != "Особенности")
            {
                $feature['name'] = sanitize_text_field(str_replace(":", "", $names[$i]));
                $feature['value'] = sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }

        $extra['comments'] = array();
        $users = $this->xpathArray(".//*[@id='tabResponses_content']//div[contains(@class,'respUserName')]");
        $comments = $this->xpathArray(".//*[@id='tabResponses_content']//div[@class='responseCommentText']");
        $ratings = $this->xpathArray(".//*[@id='tabResponses_content']//meta[@itemprop='ratingValue']/@content");
        for ($i = 0; $i < count($comments); $i++)
        {
            $comments[$i] = str_replace('Комментарий: ', '', $comments[$i]);
            $comment['comment'] = sanitize_text_field($comments[$i]);
            if (!empty($users[$i]))
                $comment['name'] = sanitize_text_field($users[$i]);
            if (!empty($ratings[$i]))
                $comment['rating'] = TextHelper::ratingPrepare($ratings[$i]);

            $extra['comments'][] = $comment;
        }

        $extra['rating'] = TextHelper::ratingPrepare($this->xpathScalar(".//*[@class='rating']//meta[@itemprop='ratingValue']/@content"));

        return $extra;
    }

    public function isInStock()
    {
        $a = $this->xpathScalar(".//meta[@itemprop='availability']/@content");
        if ($a == 'OutOfStock')
            return false;
        else
            return true;
    }

}
