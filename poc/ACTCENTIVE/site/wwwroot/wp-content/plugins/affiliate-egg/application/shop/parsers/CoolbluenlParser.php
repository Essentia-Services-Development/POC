<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * CoolbluenlParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class CoolbluenlParser extends LdShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'EUR';
    protected $user_agent = array('Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:100.0) Gecko/20100101 Firefox/100.0');

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//*[@id='direct-naar-resultaten' or @id='facet_productlist']//h2/a/@href"), 0, $max);

        if (!$urls)
            $urls = array_slice($this->xpathArray(".//div[contains(@class, 'product-card__title')]//a[@class='link']/@href"), 0, $max);

        if (!$urls)
            $urls = array_slice($this->xpathArray(".//*[@class='product__display']//a/@href"), 0, $max);

        if (!$urls)
            $urls = array_slice($this->xpathArray(".//a[contains(@class, 'product__title')]//a[@class='link']/@href"), 0, $max);

        // top lists
        if (!$urls)
            $urls = array_slice($this->xpathArray(".//*[@class='productlist_item_top_product']//h4/a/@href"), 0, $max);

        $urls = array_unique($urls);

        $host = parse_url($this->getUrl(), PHP_URL_HOST);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'https://' . $host . $url;
        }
        return $urls;
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//span[@class='sales-price__former-price']",                        
            ".//div[@class='grid-section-xs--gap-4']//*[@class='sales-price__former-price']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();

        $extra['features'] = array();
        $names = $this->xpathArray(".//div[@class='js-specifications-content']//dt[@class='product-specs__item-title']");
        $values = $this->xpathArray(".//div[@class='js-specifications-content']//dd[@class='product-specs__item-spec']");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (empty($values[$i]))
                continue;
            $feature['name'] = sanitize_text_field($names[$i]);
            $feature['value'] = sanitize_text_field($values[$i]);
            $extra['features'][] = $feature;
        }

        return $extra;
    }

    public function isInStock()
    {
        if (isset($this->ld_json['offers'][0]['availability']))
            $availability = $this->ld_json['offers'][0]['availability'];
        elseif (isset($this->ld_json['offers']['availability']))
            $availability = $this->ld_json['offers']['availability'];
        else
            $availability = '';

        if ($availability && in_array($availability, array('OutOfStock', 'SoldOut', 'http://schema.org/OutOfStock', 'https://schema.org/OutOfStock', 'http://schema.org/SoldOut', 'https://schema.org/SoldOut')))
            return false;
        else
            return true;
    }

}
