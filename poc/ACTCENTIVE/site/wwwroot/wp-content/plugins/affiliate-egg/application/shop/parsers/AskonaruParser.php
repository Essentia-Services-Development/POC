<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * AskonaruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class AskonaruParser extends ShopParser {

    public function parseCatalog($max)
    {
        $max++;
        return array_slice($this->xpathArray(".//*[@id='goods' or @class='goodswrp2']//a[@class='title']/@href"), 0, $max);
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//h1[@itemprop='name']");
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//*[@id='descriptioncontainer']");
    }

    public function parsePrice()
    {
        $price = $this->xpathScalar(".//*[@itemprop='price']");
        $price_parts = preg_split('/до/u', $price);
        return $price_parts[0];

        /**
         * 
          $this->headers = array(
          'BX-ACTION-TYPE' => 'get_dynamic',
          'BX-CACHE-MODE' => 'HTMLCACHE',
          );
          try
          {
          $result = $this->requestGet($this->getUrl(), false);
          } catch (\Exception $e)
          {
          return false;
          } */
    }

    public function parseOldPrice()
    {
        $price = $this->xpathScalar(".//*[@class='oldprice']");
        $price_parts = preg_split('/до/u', $price);
        return $price_parts[0];
    }

    public function parseManufacturer()
    {
        
    }

    public function parseImg()
    {
        return $this->xpathScalar(array(".//img[@id='zoom1']/@src", ".//*[@class='hide-show-img']/img/@src"));
    }

    public function parseImgLarge()
    {

        return str_replace('/400x300/', '/', $this->parseImg());
    }

    public function parseExtra()
    {
        $extra = array();
        $extra['features'] = array();
        $names = $this->xpathArray(".//*[@id='behaviour']//td[@class='title']");
        $values = $this->xpathArray(".//*[@id='behaviour']//td[@class='value']");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]))
            {
                $feature['name'] = rtrim(\sanitize_text_field($names[$i]), ':');
                $feature['value'] = \sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }

        $extra['images'] = $this->xpathArray(".//*[@class='slick-track']//a[position() > 1]/@href");

        $extra['comments'] = array();
        $users = $this->xpathArray(".//*[@itemprop='review']//*[@itemprop='author']/text()");
        $comments = $this->xpathArray(".//*[@itemprop='review']//*[@itemprop='description']");
        $ratings = $this->xpathArray(".//*[@itemprop='review']//@rate");
        $dates = $this->xpathArray(".//*[@itemprop='review']//*[@itemprop='datePublished']/@content");
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

        $extra['rating'] = TextHelper::ratingPrepare($this->xpathScalar(".//*[@id='goodratingwrp']/input/@value"));

        return $extra;
    }

    public function isInStock()
    {
        if (isset($this->item['price']) && $this->item['price'])
            return true;
        else
            false;
    }

}
