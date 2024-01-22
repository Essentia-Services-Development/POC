<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\Product;

/**
 * XciteAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class XciteAdvanced extends AdvancedParser {

    public function parseLinks()
    {
        $path = array(
            ".//a[@class='xc-product-grid-item__name']/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//div[contains(@class, 'price-box')]//span[@class='before-price']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseImages()
    {
        $images = array();
        $results = $this->xpathArray(".//div[contains(@class, 'pd-carousel')]//img/@src");
        foreach ($results as $img)
        {
            $images[] = str_replace('/70x80/', '/550x400/', $img);
        }
        return $images;
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name' => ".//table[@class='data-table']//th",
                'value' => ".//table[@class='data-table']//td",
            ),
            array(
                'name' => ".//table[contains(@id, 'product-attribute-specs-table')]//th",
                'value' => ".//table[contains(@id, 'product-attribute-specs-table')]//td",
            ),
        );
    }

    public function afterParseFix(Product $product)
    {
        if ($product->categoryPath)
            array_shift($product->categoryPath);

        return $product;
    }

}
