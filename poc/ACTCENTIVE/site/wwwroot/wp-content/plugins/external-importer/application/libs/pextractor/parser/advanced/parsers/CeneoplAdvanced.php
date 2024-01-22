<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\Product;

/**
 * CeneoplAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class CeneoplAdvanced extends AdvancedParser {

    public function parseLinks()
    {
        $path = array(
            ".//a[contains(@class, 'go-to-product')]/@href",
        );
        if ($urls = array_merge($this->xpathArray($path), $this->xpathArray(".//*[@class='cat-prod-row-name']//a[2]/@href")))
            return $urls;

        $path = array(
            ".//*[@class='cat-prod-row-name']/a/@href",
            ".//strong[@class='cat-prod-row__name']/a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePagination()
    {
        $path = array(
            ".//div[@class='pagination']//a[@class='pagination__item']/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//div[@class='lnd_text lnd_col-10']", true);
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name' => ".//*[@id='productTechSpecs']//th/text()",
                'value' => ".//*[@id='productTechSpecs']//td",
            ),
        );
    }

    public function getReviewsXpath()
    {
        return array(
            'review' => ".//div[contains(@class, 'js_product-review')]//div[@class='user-post__text']",
            'rating' => ".//div[contains(@class, 'js_product-review')]//span[@class='user-post__score-count']",
            'author' => ".//div[contains(@class, 'js_product-review')]//span[@class='user-post__author-name']",
            'date' => ".//div[contains(@class, 'js_product-review')]//time/@datetime",
        );
    }

    public function afterParseFix(Product $product)
    {
        if ($product->categoryPath)
            array_shift($product->categoryPath);

        return $product;
    }

}
