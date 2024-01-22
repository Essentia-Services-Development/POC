<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * ReadytowearruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class ReadytowearruParser extends ShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//table[contains(@class,'catalog')]//td/a/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://www.ready-to-wear.ru' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {

        $title = trim($this->xpathScalar(".//div[@class='item_header']/h1"));
        preg_match("/[A-Z]+(.+)/msi", $title, $match);
        if (isset($match[1]))
            $title = trim($match[1]);
        return $title;
    }

    public function parseDescription()
    {
        $descr = '';
        $res = $this->xpathArray(".//div[@class='tabs_text']/div[1]/text()[normalize-space()]");
        if ($res)
            $descr = implode(' ', $res);
        return $descr;
    }

    public function parsePrice()
    {
        return preg_replace('/[^0-9]/', '', $this->xpathScalar(".//p[@class='price']/span[not(@*)]"));
    }

    public function parseOldPrice()
    {
        return preg_replace('/[^0-9]/', '', $this->xpathScalar(".//p[@class='price']/span[@class='old_price']"));
    }

    public function parseManufacturer()
    {
        $manuf = '';
        $res = trim($this->xpathScalar(".//div[@class='item_header']/h1"));
        preg_match("/([A-Z]+).+/msi", $res, $match);
        if (isset($match[1]))
            $manuf = trim($match[1]);
        return $manuf;
    }

    public function parseImg()
    {
        $img = $this->xpathScalar(".//div[@id='thumbnails-mask']/input[contains(@value,'resize_cache')]/@value");
        if ($img && !preg_match('/^http:\/\//', $img))
            $img = 'http://www.ready-to-wear.ru' . $img;
        return $img;
    }

    public function parseImgLarge()
    {
        $img = $this->xpathScalar(".//div[@id='thumbnails-mask']/input[not(contains(@value,'resize_cache'))]/@value");
        if ($img && !preg_match('/^http:\/\//', $img))
            $img = 'http://www.ready-to-wear.ru' . $img;
        return $img;
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['images'] = array();
        $results = $this->xpathArray(".//div[@id='thumbnails-mask']/input[contains(@value,'resize_cache')]/@value");
        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            if ($res && !preg_match('/^http:\/\//', $res))
                $res = 'http://www.ready-to-wear.ru' . $res;
            $extra['images'][] = $res;
        }
        return $extra;
    }

    public function isInStock()
    {
        $res = $this->xpath->evaluate("boolean(.//input[@id='addto_button' and contains(@value,'нет в наличии')])");
        return ($res) ? false : true;
    }

}
