<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * TehnosilaruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class TehnosilaruParser extends ShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//div[contains(@class,'cat_items')]//div[@class='title']/a/@href"), 0, $max);
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//*[@id='item-details']//h1");
    }

    public function parseDescription()
    {
        $res = $this->xpathArray(".//div[@class='item-description']/node()[normalize-space()]");
        return sanitize_text_field(implode(' ', $res));
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//meta[@itemprop='price']/@content");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//div[@class='old-price']/span");
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar("//meta[@itemprop='brand']/@content");
    }

    public function parseImg()
    {
        return $this->xpathScalar(".//div[@class='media']//img[@itemprop='image']/@src");
    }

    public function parseImgLarge()
    {
        return $this->xpathScalar(".//div[@class='media']//div[contains(@class,'media-big')]/a/@href");
    }

    public function parseExtra()
    {
        $extra = array();


        $extra['features'] = array();

        $names = $this->xpathArray(".//div[contains(@class,'specs')]//span[@class='name']");
        $values = $this->xpathArray(".//div[contains(@class,'specs')]//span[@class='value']");

        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($names[$i]))
            {
                $feature['name'] = sanitize_text_field($names[$i]);
                $feature['value'] = sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }

        $extra['images'] = array();
        $results = $this->xpathArray(".//div[contains(@class,'gallery')]//div[@class='media-small']/img/@src");
        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            if ($res && preg_match('/^\/\//', $res))
                $res = 'http:' . $res;
            $extra['images'][] = str_replace('80x80', 'o', $res);
        }

        return $extra;
    }

    public function isInStock()
    {
        $res = $this->xpath->evaluate("boolean(.//div[@class='not-present'])");
        if ($res)
            return false;
        return true;
    }

}
