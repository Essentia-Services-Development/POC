<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\Product;

/**
 * CoolblueAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class CoolblueAdvanced extends AdvancedParser {

    public function parseLinks()
    {
        $path = array(
            ".//*[@id='direct-naar-resultaten' or @id='facet_productlist']//h2/a/@href",
            ".//div[contains(@class, 'product-card__title')]//a[@class='link']/@href",
            ".//*[@class='product__display']//a/@href",
            ".//a[contains(@class, 'product__title')]//a[@class='link']/@href",
            ".//*[@class='productlist_item_top_product']//h4/a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePagination()
    {
        $path = array(
            ".//ul[@class='pagination']//li/a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseDescription()
    {
        $paths = array(
            ".//div[@class='js-pros-and-cons']",
        );

        return $this->xpathScalar($paths, true);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//span[@class='sales-price__former-price']",                        
            ".//div[@class='grid-section-xs--gap-4']//*[@class='sales-price__former-price']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseImages()
    {
        return $this->xpathArray(".//div[contains(@class, 'product-media-gallery__slide-container')]//img/@data-src");
    }

    public function parseCategoryPath()
    {
        $paths = array(
            ".//ol[contains(@class, 'breadcrumbs')]/li/a",
        );

        if ($categs = $this->xpathArray($paths))
        {
            array_shift($categs);
            return $categs;
        }
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name' => ".//div[contains(@class, 'js-specifications-content')]//dt[contains(@class, 'product-specs__item-title')]",
                'value' => ".//div[contains(@class, 'js-specifications-content')]//dd[contains(@class, 'product-specs__item-spec')]",
            ),
        );
    }

    public function afterParseFix(Product $product)
    {
        $product->image = str_replace('/422x390/', '/max/500x500/', $product->image);
        return $product;
    }

}
