<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * KupivipruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class KupivipruParser extends MicrodataShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//div[@class='image']/a/@href");
    }

    public function parseTitle()
    {
        if ($n = $this->xpathScalar(".//h1[@itemprop='name']/div[@class='name']") . ' ' . $this->xpathScalar(".//h1[@itemprop='name']/div[@class='brand']"))
            return $n;
        else
            parent::parseTitle();
    }

    public function parseManufacturer()
    {
        return '';
    }

    public function parseDescription()
    {
        return '';
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//div[@class='price-info']//span/@data-text");
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();

        $extra['features'] = array();
        $names = $this->xpathArray(".//div[@class='product-features']//span[@class='bold']");
        $values = $this->xpathArray(".//div[@class='product-features']//div/span/span[2]");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]) && trim($names[$i]) != "Артикул магазина")
            {
                $feature['name'] = sanitize_text_field($names[$i]);
                $feature['value'] = sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }

        return $extra;
    }

}
