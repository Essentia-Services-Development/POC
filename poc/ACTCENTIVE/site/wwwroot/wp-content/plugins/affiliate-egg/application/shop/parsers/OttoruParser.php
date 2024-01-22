<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * OttoruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class OttoruParser extends ShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//a[@class='brandProductLink adsLink']/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://www.otto.ru' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return trim($this->xpathScalar(".//div[@class='ADSMid']/h1"));
    }

    public function parseDescription()
    {
        $description = '';
        $results = $this->xpathArray(".//div[@class='ADSMidDef']//p[@class='glossarLinks']/text()[normalize-space()]");
        if ($results)
            $description = implode(" ", $results);
        return $description;
    }

    public function parsePrice()
    {
        $price = '';
        $res = $this->xpathScalar(".//script[contains(.,'item.priceRaw')]");

        preg_match('/item\.priceRaw\s=\s\'(\d+).+?\'/ums', $res, $matches);
        if ($matches)
            $price = trim(strip_tags($matches[1]));
        return $price;
    }

    public function parseOldPrice()
    {
        $oldPrice = '';
        $res = $this->xpathScalar(".//script[contains(.,'item.oldPrice')]");
        preg_match('/item\.oldPrice\s=\s\'(\d+).+?\'/ums', $res, $matches);
        if ($matches)
            $oldPrice = trim(strip_tags($matches[1]));
        return $oldPrice;
    }

    public function parseManufacturer()
    {
        $manufacturer = '';
        $res = $this->xpathScalar(".//script[contains(.,'\"vendor\":')]");
        preg_match('/"vendor":\s"(.*?)",/ums', $res, $matches);
        if (isset($matches[1]) && $matches[1])
            $manufacturer = trim(strip_tags($matches[1]));
        return $manufacturer;
    }

    public function parseImg()
    {
        $img = $this->xpathScalar(".//div[@class='slides']//img/@src");
        $img = str_replace('$ov_thumbnail$', '$ov_formatg$', $img);
        return $img;
    }

    public function parseImgLarge()
    {
        $img = '';
        if ($this->item['orig_img'])
            $img = str_replace('$ov_formatg$', '$formatz$', $this->item['orig_img']);
        return $img;
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();

        $results = $this->xpathArray(".//div[@class='ADSMidDef']/text()[normalize-space()]");

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
        $results = $this->xpathArray(".//div[@class='slides']//img/@src");
        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            $extra['images'][] = str_replace('/ov_thumbnail/', '/ov_formatg/', $res);
        }
        return $extra;
    }

    public function isInStock()
    {
        $res = $this->xpathScalar(".//script[contains(.,\"item.available = true;\")]");

        if ($res)
            return true;
        else
            return false;
    }

}
