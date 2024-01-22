<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * BolcomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class BolcomParser extends LdShopParser
{

    protected $charset = 'utf-8';
    protected $currency = 'EUR';

    public function parseCatalog($max)
    {
        $path = array(
            ".//div[@class='product-title--inline']//a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePrice()
    {
        if ($price = parent::parsePrice())
            return $price;

        if (isset($this->ld_json['workExample']['potentialAction']['expectsAcceptanceOf']['price']))
            return $this->ld_json['workExample']['potentialAction']['expectsAcceptanceOf']['price'];
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//section[contains(@class, 'buy-block__prices')]//del[@class='buy-block__list-price']",
        );

        return $this->xpathScalar($paths);
    }
    
    public function parseDescription()
    {
        $paths = array(
            ".//div[@class='product-description']",
        );

        if ($d = $this->xpathScalar($paths, true))
            return $d;
        else
            return parent::parseDescription();
    }
    
    public function parseImg()
    {
        $paths = array(
            ".//meta[@property='og:image']/@content",
            ".//img[@class='js_product_img']/@src"
        );

        if ($img = $this->xpathScalar($paths))
            return $img;
        else
            return parent::parseImg();
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();

        $extra['features'] = array();
        $names = $this->xpathArray(".//div[@class='specs']//dt/text()[normalize-space()]");
        $values = $this->xpathArray(".//div[@class='specs']//dd");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (empty($values[$i]))
                continue;
            $feature['name'] = \sanitize_text_field($names[$i]);
            $feature['value'] = \sanitize_text_field($values[$i]);
            $extra['features'][] = $feature;
        }

        if ($r = $this->xpathScalar(".//div[@class='pdp-header__rating']//div[@class='u-pl--xxs']"))
        {
            $r = explode('(', $r);
            if (count($r) == 2)
                $extra['ratingCount'] = (int) $r[1];
        }

        return $extra;
    }

    public function isInStock()
    {
        if ($this->xpathScalar(".//div[contains(@class, 'buy-block__options')]//a[@data-button-type='buy']"))
            return true;
        else
            return parent::isInStock();
    }
    
}
