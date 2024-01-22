<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * NamesruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class NamesruParser extends ShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//*[@class='catalog-section-wrap']//div[@class='name']/a/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://www.names.ru' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//meta[@property='og:description']/@content");
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//*[@class='accordion-body']//div[@itemprop='description']");
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//div[@itemprop='price']");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//div[@class='price-old']/span");
    }

    public function parseManufacturer()
    {
        return trim($this->xpathScalar(".//div[@class='content-block']//h1/a"));
    }

    public function parseImg()
    {
        return $this->xpathScalar(".//div[@class='left-block']/div[@class='big']/img/@src");
    }

    public function parseImgLarge()
    {
        $img = $this->xpathScalar(".//div[@class='left-block']/div[@class='big']/img/@src");
        $img = str_replace("resize_cache/", "", $img);
        $img = str_replace("400_600_0/", "", $img);
        return $img;
    }

    public function parseExtra()
    {
        $extra = array();
        $extra['features'] = array();
        $comp = trim($this->xpathScalar(".//div[@class='content-block']//div[@class='props']/div/span/text()[contains(., 'Состав:')]/following::span"));
        $i = 0;
        if ($comp)
        {
            $extra['features'][$i]['name'] = 'Состав';
            $extra['features'][$i]['value'] = $comp;
        }
        $country = trim($this->xpathScalar(".//div[@class='content-block']//div[@class='props']/div[@class='item country']/span[@class='value']"));
        if ($country)
        {
            $i++;
            $extra['features'][$i]['name'] = 'Страна';
            $extra['features'][$i]['value'] = $country;
        }

        $extra['images'] = array();

        $results = $this->xpathArray(".//a[@class='item swiper-slide']/img/@src");
        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            if ($res)
            {
                $res = str_replace("resize_cache/", "", $res);
                $extra['images'][] = str_replace("400_600_0/", "", $res);
            }
        }
        return $extra;
    }

    public function isInStock()
    {
        //@todo как получить наличие display:none для класса из css файла
        return true;
    }

}
