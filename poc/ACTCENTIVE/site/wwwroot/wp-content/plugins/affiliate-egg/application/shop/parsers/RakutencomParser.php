<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * RakutencomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com> 
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class RakutencomParser extends LdShopParser {

    protected $charset = 'ISO-8859-1';
    protected $currency = 'EUR';
    protected $headers = array(
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language' => 'en-us,en;q=0.5',
        'Cache-Control' => 'no-cache',
        'Connection' => 'keep-alive',
    );

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//div[contains(@class, 'productList_layoutImg')]//a/@href");
    }

    public function parseDescription()
    {
        if ($d = $this->xpathScalar(".//div[@id='shop_content']//div[@class='custom_details']"))
            return $d;
        else
            $d = parent::parseDescription();
        if (!strstr($d, 'Rakuten'))
            return $d;
    }

    public function parsePrice()
    {
        if (preg_match('/"summary_new_best_price":{"value":"(.+?)"}/', $this->dom->saveHTML(), $matches))
            return $matches[1];

        $paths = array(
            ".//div[@class='v2_fpp_price']/text()",
            ".//*[@id='prdBuyBoxV2']//*[contains(@class, 'price')]",
            ".//*[@id='prdBuyBoxV2']//p[@class='price typeNew spacerBottomXs']"
        );

        return $this->xpathScalar($paths);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//div[@class='infosPrice']//span[@class='oldPrice spacerLeftXs']",
            ".//section[@id='prdBuyBoxV2']//span[contains(@class, 'oldPrice')]",
            ".//span[@class='v2_fpp_price_original v2_fpp_discount_disclaimer']",
            ".//span[@class='v2_fpp_price_original']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseImg()
    {
        if ($p = parent::parseImg())
            return $p;
        else
            return $this->xpathScalar(".//div[@class='prdMainPhotoCtn']//img/@src");
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();

        $names = $this->xpathArray(".//table[@class='spec_table_ctn']//th");
        $values = $this->xpathArray(".//table[@class='spec_table_ctn']//td");
        $feature = array();
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
