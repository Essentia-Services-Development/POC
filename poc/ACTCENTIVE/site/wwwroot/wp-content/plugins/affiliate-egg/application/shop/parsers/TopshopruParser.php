<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * TopshopruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class TopshopruParser extends ShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        $urls = array();
        $urls = array_slice($this->xpathArray(".//*[@id='products']//a[@class='mc_item_pic']/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://www.top-shop.ru' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return trim($this->xpathScalar(".//h1[@class='gr_zag_lev_1']"));
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//meta[@property='og:description']/@content");
    }

    public function parsePrice()
    {
        $price = $this->xpathScalar(".//div[contains(@class, 'ic_main_price')]");
        if (!$price)
            $price = $this->xpathScalar(".//div[@class='ic_main_price sale_price']");
        return $price;
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//div[@class='ic_old_price']");
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//span[contains(@class, 'js_ectrack_full')]/@data-brand");
    }

    public function parseImg()
    {
        $img = $this->xpathScalar(".//meta[@property='og:image']/@content");
        if (!preg_match('/^http:\/\//', $img))
            $img = 'http:' . $img;
        return $img;
    }

    public function parseImgLarge()
    {
        
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();

        $results = $this->xpathArray(".//*[@id='characteristics']/li/div");
        $feature = array();
        foreach ($results as $res)
        {
            $expl = explode(":", $res, 2);
            if (count($expl) == 2 && $expl[0] != "Бренд" && $expl[0] != "Категория")
            {
                $feature['name'] = sanitize_text_field($expl[0]);
                $feature['value'] = sanitize_text_field($expl[1]);
                $extra['features'][] = $feature;
            }
        }

        $extra['images'] = array();
        $results = $this->xpathArray(".//*[@id='js_carousel_thumb']//a/@href");
        foreach ($results as $res)
        {
            if ($res)
            {
                $extra['images'][] = $res;
            }
        }

        $extra['rating'] = TextHelper::ratingPrepare($this->xpathScalar(".//*[@itemprop='aggregateRating']//*[@itemprop='ratingValue']/@content"));

        return $extra;
    }

    public function isInStock()
    {
        $res = $this->xpath->evaluate("boolean(.//span[@class='card_temp_absent_text'])");
        if ($res)
            return false;
        return true;
    }

}
