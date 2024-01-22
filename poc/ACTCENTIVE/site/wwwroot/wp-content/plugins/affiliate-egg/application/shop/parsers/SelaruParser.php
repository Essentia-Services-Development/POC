<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * SelaruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class SelaruParser extends ShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//div[@class='name']/a/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://www.sela.ru/' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//div[@id='product_page']/div/h1");
    }

    public function parseDescription()
    {
        return "";
    }

    public function parsePrice()
    {
        return (float) preg_replace('/[^0-9,]/', '', $this->xpathScalar(".//div[@class='price']/div[@class='new']/span"));
    }

    public function parseOldPrice()
    {
        return (float) preg_replace('/[^0-9,]/', '', $this->xpathScalar(".//div[@class='price']/div[@class='old']/span"));
    }

    public function parseManufacturer()
    {
        return "Sela";
    }

    public function parseImg()
    {
        $img = $this->xpathScalar(".//div[@id='product_page']//ul/li/a[@id='pre0']/img/@src");
        if ($img && !preg_match('/^http:\/\//', $img))
            $img = 'http://www.sela.ru' . $img;
        return $img;
    }

    public function parseImgLarge()
    {
        $img = '';
        if ($this->item['orig_img'])
            $img = str_replace("/catalog/meduim", "/cat800/front", $this->item['orig_img']);
        return $img;
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();

        $feature = array();

        $names = $this->xpathArray(".//td[1]/text()[normalize-space()]");
        $values = $this->xpathArray(".//td[2]/text()[normalize-space()]");
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]) && trim($names[$i]) != "Особенности")
            {
                $feature['name'] = sanitize_text_field($names[$i]);
                $feature['value'] = sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }

        $results = $this->xpathArray(".//td/text()[contains(., 'Особенности')]/following::td/p/text()");
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

        $results = $this->xpathArray(".//div[@id='product_carousel']//a[@class='zoom']/@href");
        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            if ($res)
            {
                $extra['images'][] = 'http://www.sela.ru' . $res;
            }
        }
        return $extra;
    }

    public function isInStock()
    {
        $res = $this->xpath->evaluate("boolean(.//div[@class='info'][contains(., 'заказ невозможен')])");
        return ($res) ? false : true;
    }

}
