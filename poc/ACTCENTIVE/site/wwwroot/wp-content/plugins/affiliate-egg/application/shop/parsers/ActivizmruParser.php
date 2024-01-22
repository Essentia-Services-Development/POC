<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * ActivizmruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class ActivizmruParser extends ShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//*[@id='goods_list']//div[@class='best_text']//a/@href"), 0, $max);
        if (!$urls)
            $urls = array_slice($this->xpathArray(".//*[@class='result-section']//a[@class='result-section__title']/@href"), 0, $max);

        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://activizm.ru' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return trim($this->xpathScalar(".//h1[@itemprop='name']"));
    }

    public function parseDescription()
    {
        return trim($this->xpathScalar(".//*[@id='description']/p[2]"));
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//span[@itemprop='lowPrice']");
    }

    public function parseOldPrice()
    {
        return '';
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//b[@itemprop='brand']");
    }

    public function parseImg()
    {
        return $this->xpathScalar(".//meta[@property='og:image']/@content");
    }

    public function parseImgLarge()
    {
        $img = $this->parseImg();
        if (!$img)
            return '';
        return str_replace('320px/320-', '/full/', $img);
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();
        $names = $this->xpathArray(".//*[@id='specifications']//tr/th");
        array_shift($names);
        $values = $this->xpathArray(".//*[@id='specifications']//tr/td");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!$name = \sanitize_text_field($names[$i]))
                continue;
            if (empty($values[$i]))
                continue;

            $feature['name'] = $name;
            $feature['value'] = \sanitize_text_field($values[$i]);
            $extra['features'][] = $feature;
        }
        return $extra;
    }

    public function isInStock()
    {
        return true;
    }

}
