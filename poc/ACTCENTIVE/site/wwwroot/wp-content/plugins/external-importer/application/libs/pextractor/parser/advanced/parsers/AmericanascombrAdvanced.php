<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\Product;

/**
 * AmericanascombrAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class AmericanascombrAdvanced extends AdvancedParser {

    protected $user_agent = 'ia_archiver';

    public function parseLinks()
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

    public function parsePagination()
    {
        $path = array(
            ".//ul[contains(@class, 'pagination-product-grid ')]//li/a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePrice()
    {
        $paths = array(
            ".//div[contains(@class, 'main-offer__Container')]//span[contains(@class,'price__SalesPrice')]",
            ".//div[contains(@class, 'main-offer__Container')]//span[contains(@class,'main-offer__SalesPrice')]",
        );

        return $this->xpathScalar($paths);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//div[contains(@class, 'main-offer__Container')]//span[contains(@class,'price__Strike')]"
        );

        return $this->xpathScalar($paths);
    }

    public function parseImages()
    {
        $images = $this->xpathArray(".//div[@class='image-gallery-thumbnail-inner']/img/@src");
        array_shift($images);
        return $images;
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name' => ".//section[contains(@class, 'info__StyledCard')]//table//td[1]",
                'value' => ".//section[contains(@class, 'info__StyledCard')]//table//td[2]",
            ),
        );
    }

    public function parseCurrencyCode()
    {
        return 'BRL';
    }

    public function afterParseFix(Product $product)
    {
        $product->inStock = true;
        $product->availability = null;
        return $product;
    }

}
