<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\Product;

/**
 * EtsycomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class EtsycomAdvanced extends AdvancedParser {

    public function parseLinks()
    {
        $path = array(
            ".//a[contains(@class, 'listing-link')]/@href",
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
            ".//nav[@class='search-pagination']//a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseDescription()
    {
        $paths = array(
            ".//p[@class='wt-text-body-01 wt-break-word']",
        );

        return $this->xpathScalar($paths, true);
    }

    public function parsePrice()
    {
        $paths = array(
            ".//div[@data-buy-box-region='price']//p[@class='wt-text-title-03 wt-mr-xs-1']/span[2]",
            ".//div[@class='wt-mb-xs-3']//p[@class='wt-text-title-03 wt-mr-xs-2']",
            ".//*[@class='text-largest strong override-listing-price']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//div[@class='wt-text-strikethrough wt-mr-xs-1']",
            ".//div[@class='wt-mb-xs-3']//p[contains(@class, 'wt-text-strikethrough')]",
        );

        return $this->xpathScalar($paths);
    }

    public function parseImages()
    {
        $paths = array(
            ".//div[contains(@class, 'image-carousel-container')]//img/@data-src",
        );

        return $this->xpathArray($paths);
    }

    public function getReviewsXpath()
    {
        return array(
            array(
                'review' => ".//div[@id='same-listing-reviews-panel' or @id='reviews']//*[contains(@id, 'review-preview-toggle')]",
                'rating' => ".//div[@id='same-listing-reviews-panel' or @id='reviews']//span[@class='wt-display-inline-block wt-mr-xs-1']//span[last()]/@data-rating",
                'author' => ".//div[@id='same-listing-reviews-panel' or @id='reviews']//a[@class='wt-text-link wt-mr-xs-1']",
                'date' => ".//div[@id='same-listing-reviews-panel'or @id='reviews']//p[@class='wt-text-caption wt-text-gray']/text()",
            ),
        );
    }

    public function parseCurrencyCode()
    {
        if ($this->parsePrice())
        {
            if (preg_match('/"locale_currency_code":"([A-Z]+?)"/', $this->html, $matches))
                return $matches[1];
        }
    }

    public function afterParseFix(Product $product)
    {
        foreach ($product->reviews as $i => $r)
        {
            if ($r['rating'])
                $product->reviews[$i]['rating']++;
        }
        return $product;
    }

}
