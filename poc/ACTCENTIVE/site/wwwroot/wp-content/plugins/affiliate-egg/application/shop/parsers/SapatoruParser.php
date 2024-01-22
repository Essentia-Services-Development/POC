<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * SapatoruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class SapatoruParser extends ShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//div[contains(@class,'catalog-item')]/a[@class='catalog-pic']/@href"), 0, $max);
        if (!$urls)
            $urls = array_slice($this->xpathArray(".//div[@class='catalog-items']//div[@class='catalog__block']/a/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://www.sapato.ru' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//h1[@class='product-info__title']");
    }

    public function parseDescription()
    {
        return '';
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//*[@class='product-info__new-price']");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@class='product-info__old-price']");
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//div[@class='breadcrumbs']//li[last()-1]/a");
    }

    public function parseImg()
    {
        return $this->xpathScalar(".//*[@id='zoom1']/img/@src");
    }

    public function parseImgLarge()
    {
        return '';
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();

        $names = $this->xpathArray(".//div[@class='product-chars']//li/p");
        $values = $this->xpathArray(".//div[@class='product-chars']//li/div/p");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]))
            {
                $feature['name'] = sanitize_text_field($names[$i]);
                $feature['value'] = sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }

        $extra['images'] = array();
        $results = $this->xpathArray(".//ul[@class='product-gallery__list']//a/@href");
        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            $extra['images'][] = $res;
        }
        return $extra;
    }

    public function isInStock()
    {
        $res = $this->xpath->evaluate(".//*[@id='do-purchase']");
        if ($res)
            return true;
        else
            return false;
    }

}
