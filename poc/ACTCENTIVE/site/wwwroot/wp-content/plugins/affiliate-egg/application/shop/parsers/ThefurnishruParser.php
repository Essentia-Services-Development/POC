<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * ThefurnishruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class ThefurnishruParser extends ShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        return array_slice($this->xpathArray(".//*[@class='products-listing']//a[@class='name']/@href"), 0, $max);
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//h1[@class='title-h1']");
    }

    public function parseDescription()
    {
        return sanitize_text_field($this->xpathScalar(".//div[@class='toggle-block-content']"));
    }

    public function parsePrice()
    {
        $price = $this->xpathScalar(".//div[@class='product-purchase-cost-current']");
        if (!$price)
            $price = $this->xpathScalar(".//label[@for='buy_method_clear']");
        return $price;
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//div[@class='product-purchase-cost-old']");
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//div[@class='product-properties']//div[contains(.,'Бренд:')]/b/a");
    }

    public function parseImg()
    {
        $img = $this->xpathScalar(".//img[@class='product-gallery-big-image']/@src"); //small_products_preview
        if ($img && !preg_match('/^http:/', $img))
            $img = 'http:' . $img;
        return $img;
    }

    public function parseImgLarge()
    {
        $img = '';
        if ($this->item['orig_img'])
        {
            $img = str_replace("small_image/300x", "image/550x", $this->item['orig_img']);
            $img = str_replace("slider_big_", "", $img);
        }
        return $img;
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();
        $feature = array();
        $names = $this->xpathArray(".//div[contains(@class,'product-property')]/div[@class='product-property-name']");
        $values = $this->xpathArray(".//div[contains(@class,'product-property')]/div[@class='product-property-value']");
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]) && trim($names[$i]) != "Особенности")
            {
                $feature['name'] = sanitize_text_field(str_replace(":", "", $names[$i]));
                $feature['value'] = sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }

        $extra['images'] = array();
        $results = $this->xpathArray(".//ul[@class='product-gallery-preview-list']/li/a/img/@src");
        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            if ($res)
            {
                if (!preg_match('/^http:/', $res))
                    $res = 'http:' . $res;
                $res = str_replace("slider_thumb_", "", $res);
                $extra['images'][] = $res;
            }
        }
        return $extra;
    }

    public function isInStock()
    {
        return $this->xpath->evaluate("boolean(.//div[@class='product-buy-availability' and contains(.,'В наличии')])");
    }

}
