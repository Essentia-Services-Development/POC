<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * FourlapyParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class FourlapyParser extends ShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//table[@class='catalog-table']//a[@class='item-img']/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://4lapy.ru' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//h1[@class='item-title']");
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//*[contains(@class, 'goods-info-tabs')]//dd[1]/div");
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//*[@class='goods-row-price']//*[contains(@class, 'goods-price__this')]/span");
    }

    public function parseOldPrice()
    {
        $price = $this->xpathScalar(".//*[@class='goods-row-price']//span[contains(@class, 'old')]");
        return str_replace('pуб.', '', $price);
    }

    public function parseManufacturer()
    {
        $extra = $this->parseExtra();
        if ($extra['features'] && !empty($extra['features']['Производитель']))
            return $extra['features']['Производитель'];
    }

    public function parseImg()
    {
        $img = $this->xpathScalar(".//div[@class='goods-image__wrap']//a[contains(@class, 'image-big__item')]/img/@src");
        if (!preg_match('/^http:\/\//', $img))
            $img = 'http://4lapy.ru' . $img;;
        return $img;
    }

    public function parseImgLarge()
    {
        
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();
        $feature = array();
        $names_values = $this->xpathArray(".//*[contains(@class, 'goods-info-tabs')]//dd[2]/p");
        if (!$names_values)
            $names_values = $this->xpathArray(".//*[contains(@class, 'goods-info-tabs')]//dd[1]/p");

        for ($i = 0; $i < count($names_values); $i++)
        {
            $nv = explode(':', $names_values[$i]);
            if (count($nv) != 2)
                continue;
            $feature['name'] = \sanitize_text_field($nv[0]);
            $feature['value'] = \sanitize_text_field($nv[1]);
            $extra['features'][] = $feature;
        }
        return $extra;
    }

    public function isInStock()
    {
        if ($this->parsePrice())
            return true;
        else
            return false;
    }

}
