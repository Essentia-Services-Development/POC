<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * CarlopazolinicomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class CarlopazolinicomParser extends ShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//div[@class='collection-list-data']/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://www.carlopazolini.com' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        $title1 = trim($this->xpathScalar(".//div[@id='properties']/h1"));
        $title2 = trim($this->xpathScalar(".//div[@id='properties']/h3"));
        return $title1 . " " . $title2;
    }

    public function parseDescription()
    {
        return '';
    }

    public function parsePrice()
    {
        return preg_replace('/[^0-9,]/', '', $this->xpathScalar(".//div[@id='properties']/p[contains(@class,'price')]"));
    }

    public function parseOldPrice()
    {
        return '';
    }

    public function parseManufacturer()
    {
        return "Carlo Pazolini";
    }

    public function parseImg()
    {
        $img = $this->xpathScalar(".//img[@class='img-responsive fullscreen active']/@src");
        if (!preg_match('/^http:\/\//', $img))
            $img = 'http://www.carlopazolini.com' . $img;
        return $img;
    }

    public function parseImgLarge()
    {
        return '';
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();

        $results = $this->xpathArray(".//div[@id='mc-detail']/p");


        $feature = array();
        foreach ($results as $res)
        {
            $expl = explode(":", $res, 2);
            if (count($expl) == 2)
            {
                $feature['name'] = sanitize_text_field($expl[0]);
                $feature['value'] = sanitize_text_field($expl[1]);
                $extra['features'][] = $feature;
            }
        }

        $extra['images'] = array();
        $results = $this->xpathArray(".//img[@class='img-responsive fullscreen']/@src");
        foreach ($results as $i => $res)
        {
            if ($res)
            {
                if (!preg_match('/^http:\/\//', $res))
                    $res = 'http://www.carlopazolini.com' . $res;
                $extra['images'][] = $res;
            }
        }
        return $extra;
    }

    public function isInStock()
    {
        return true;
    }

}
