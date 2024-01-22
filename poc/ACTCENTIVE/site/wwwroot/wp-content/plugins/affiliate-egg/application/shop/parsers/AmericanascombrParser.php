<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * AmericanascombrParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class AmericanascombrParser extends LdShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'BRL';
    protected $user_agent = 'ia_archiver';

    public function parseCatalog($max)
    {
        $path = array(
            ".//div[contains(@class, 'RippleContainer')]/a/@href",
            ".//div[@class='product-cards__thumbnail']/a/@href",
        );

        $urls = $this->xpathArray($path);
        foreach ($urls as $i => $url)
        {
            $urls[$i] = strtok($url, '?');
        }

        return $urls;
    }

    public function parsePrice()
    {
        $paths = array(
            ".//div[contains(@class, 'main-offer__Container')]//span[contains(@class,'price__SalesPrice')]",
            ".//div[contains(@class, 'main-offer__Container')]//span[contains(@class,'main-offer__SalesPrice')]",
        );

        if ($p = $this->xpathScalar($paths))
            return $p;

        return parent::parsePrice();
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//div[contains(@class, 'main-offer__Container')]//span[contains(@class,'price__Strike')]"
        );

        return $this->xpathScalar($paths);
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();

        $extra['features'] = array();
        $names = $this->xpathArray(".//section[contains(@class, 'info__StyledCard')]//table//td[1]");
        $values = $this->xpathArray(".//section[contains(@class, 'info__StyledCard')]//table//td[2]");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (empty($values[$i]))
                continue;
            $feature['name'] = \sanitize_text_field($names[$i]);
            $feature['value'] = \sanitize_text_field($values[$i]);
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
