<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * HauteletruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class HauteletruParser extends ShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        return array_slice($this->xpathArray(".//ul[contains(@class,'products-grid')]//h3[@class='product-name']/a/@href"), 0, $max);
    }

    public function parseTitle()
    {
        return trim($this->xpathScalar(".//div[@class='product-name']/h1"));
    }

    public function parseDescription()
    {
        return '';
    }

    public function parsePrice()
    {
        return preg_replace('/[^0-9,]/', '', $this->xpathScalar(".//div[@class='price-box']/p[@class='special-price']/span[1]"));
    }

    public function parseOldPrice()
    {
        return preg_replace('/[^0-9,]/', '', $this->xpathScalar(".//div[@class='price-box']/p[@class='old-price']/span[1]"));
    }

    public function parseManufacturer()
    {
        return trim($this->xpathScalar(".//div[@class='brand-label']"));
    }

    public function parseImg()
    {
        return $this->xpathScalar(".//div[@class='owl-carousel']/div/img/@src");
    }

    public function parseImgLarge()
    {
        $img = '';
        if ($this->item['orig_img'])
            $img = str_replace('480x/', '960x/', $this->item['orig_img']);
        return $img;
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();

        $names = str_replace(":", "", $this->xpathArray(".//dl[@class='product-info-tab']//dt"));
        $values = str_replace(":", "", $this->xpathArray(".//dl[@class='product-info-tab']//dd"));
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]) && trim($names[$i]) != "Бренд" && $values[$i] != "Не применимо" && $values[$i] != "Нет")
            {
                $feature['name'] = sanitize_text_field($names[$i]);
                $feature['value'] = sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }

        $extra['images'] = array();
        $results = $this->xpathArray(".//div[@class='img-wrapper']/div[@class='owl-carousel']/div/img/@src");
        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            if ($res)
            {
                $extra['images'][] = $res;
            }
        }
        return $extra;
    }

    public function isInStock()
    {
        $res = $this->xpath->evaluate("boolean(.//p[@class='availability out-of-stock'])");
        if ($res)
            return false;
        return true;
    }

}
