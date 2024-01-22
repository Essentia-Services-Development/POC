<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * TikivnParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class TikivnParser extends LdShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'VND';

    public function parseCatalog($max)
    {
        $path = array(
            ".//a[@class='product-item']/@href",
            ".//div[contains(@class, 'product-item')]//a/@href",
            ".//*[@class='search-a-product-item']",
            ".//p[@class='title']/a/@href",
        );

        $urls = $this->xpathArray($path);
        foreach ($urls as $i => $url)
        {
            $urls[$i] = strtok($url, '?');
        }

        return $urls;
    }

    public function parseDescription()
    {
        $path = array(
            ".//div[@class='content']//div[contains(@class, 'ToggleContent__Wrapper-sc')]",
            ".//div[@class='summary']//div[@class='group border-top']",
        );
        return $this->xpathScalar($path, true);
    }

    public function parseOldPrice()
    {
        if (preg_match('/,"list_price":(\d+),"/', $this->dom->saveHTML(), $matches))
            return $matches[1];

        $paths = array(
            ".//div[@class='summary']//p[@class='original-price']",
        );

        if ($price = $this->xpathScalar($paths))
            return $price;
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();

        $names = $this->xpathArray(".//div[contains(@class, 'style__Wrapper-')]//table//tr/td[1]");
        $values = $this->xpathArray(".//div[contains(@class, 'style__Wrapper-')]//table//tr/td[2]");

        $feature = array();
        $extra['features'] = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]))
            {
                $feature['name'] = \sanitize_text_field($names[$i]);
                $feature['value'] = \sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }

        return $extra;
    }

}
