<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * ShopcoutureruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class ShopcoutureruParser extends ShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//div[@class='gallery']//span[@class='ph2']/a/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://shop-couture.ru' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//div[@class='info']/span");
    }

    public function parseDescription()
    {
        return "";
    }

    public function parsePrice()
    {
        return (float) preg_replace('/[^0-9,]/', '', $this->xpathScalar(".//div[contains(@class,'pr')]/span[@class='red']"));
    }

    public function parseOldPrice()
    {
        return (float) preg_replace('/[^0-9,]/', '', $this->xpathScalar(".//div[contains(@class,'pr')]/span[@class='lThr']"));
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//div[@class='info']/div[@class='name']");
    }

    public function parseImg()
    {
        $img = $this->xpathScalar(".//div[@class='img']/div[@class='big']/img/@src");
        if ($img && !preg_match('/^http:\/\//', $img))
            $img = 'http://shop-couture.ru' . $img;
        return $img;
    }

    public function parseImgLarge()
    {
        $img = $this->xpathScalar(".//div[@class='img']/div[@class='big']/a/@href");
        if ($img && !preg_match('/^http:\/\//', $img))
            $img = 'http://shop-couture.ru' . $img;
        return $img;
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();

        $results = $this->xpathArray(".//div[@class='tabs']/div[@class='cont1']/p");
        $feature = array();
        foreach ($results as $res)
        {
            $splited = explode(":", $res, 2);
            if (count($splited) == 2)
            {
                $feature['name'] = sanitize_text_field($splited[0]);
                $feature['value'] = sanitize_text_field($splited[1]);
                $extra['features'][] = $feature;
            }
        }

        $extra['images'] = array();

        $results = $this->xpathArray(".//div[@class='carBl']/a[@class='fancybox']/@href");
        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            if ($res)
            {
                $extra['images'][] = 'http://shop-couture.ru' . $res;
            }
        }
        return $extra;
    }

    public function isInStock()
    {
        return true;
    }

}
