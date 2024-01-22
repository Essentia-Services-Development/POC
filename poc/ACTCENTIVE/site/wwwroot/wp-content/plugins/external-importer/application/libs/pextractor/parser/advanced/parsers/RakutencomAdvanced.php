<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\Product;

/**
 * RakutencomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class RakutencomAdvanced extends AdvancedParser {

    public function parseLinks()
    {
        $path = array(
            ".//div[contains(@class, 'productList_layoutContent')]//div[@class='text dib']/a/@href",
            ".//div[@class='product']/a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePagination()
    {
        $path = array(
            ".//ul[contains(@class, 'rc-pagination')]//li/a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePrice()
    {
        if (preg_match('/"summary_new_best_price":{"value":"(.+?)"}/', $this->html, $matches))
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

    public function parseImages()
    {
        $images = array();
        $results = $this->xpathArray(".//ul[contains(@class, 'prdGalleryList')]//img/@data-pm-lazy-src");
        foreach ($results as $img)
        {
            $img = str_replace('_ML.jpg', '.jpg', $img);
            $images[] = $img;
        }
        return $images;
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name' => ".//table[@class='spec_table_ctn']//th",
                'value' => ".//table[@class='spec_table_ctn']//td",
            ),
        );
    }

    public function parseCurrencyCode()
    {
        return 'EUR';
    }

    public function afterParseFix(Product $product)
    {
        $product->image = str_replace('_ML.jpg', '.jpg', $product->image);
        return $product;
    }

}
