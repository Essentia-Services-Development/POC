<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\Product;
use ExternalImporter\application\libs\pextractor\client\XPath;
use ExternalImporter\application\libs\pextractor\client\Dom;
use ExternalImporter\application\libs\pextractor\ExtractorHelper;

/**
 * BolcomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class BolcomAdvanced extends AdvancedParser {

    public function parseLinks()
    {
        $path = array(
            ".//div[contains(@class, 'product-item__image')]//a/@href",
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
        return $this->xpathScalar(".//div[@class='product-description']", true);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//section[contains(@class, 'buy-block__prices')]//del[@class='buy-block__list-price']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseImage()
    {
        if ($images = $this->parseImages())
            return reset($images);

        $paths = array(
            ".//meta[@property='og:image']/@content",
        );

        return $this->xpathScalar($paths);
    }

    public function parseImages()
    {
        if (preg_match_all('/"imageUrl":"(.+?)"/ims', $this->html, $matches))
            return $matches[1];
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name' => ".//div[@class='specs']//dt/text()[normalize-space()]",
                'value' => ".//div[@class='specs']//dd",
            ),
        );
    }

    public function getReviewsXpath()
    {
        return array(
            array(
                'review' => ".//div[@class='reviews']//div[@class='review__body']",
                'rating' => ".//*[@class='reviews']//*[@class='star-rating']/span/@style",
                'author' => ".//*[@class='reviews']//ul[@class='review-metadata__list']/li[1]",
                'date' => ".//*[@class='reviews']//ul[@class='review-metadata__list']/li[3]",
            ),
        );
    }

    public function parseReviews()
    {
        $f = preg_match('/"reviewCount":"(\d+?)"/', $this->html, $matches);
        if (!$f || (int) $matches[1] <= 5)
            return parent::parseReviews();

        $parts = explode('/', $this->getUrl());
        $product_id = $parts[count($parts) - 2];
        if (!is_numeric($product_id))
            return array();
        $product_id = (int) $product_id;

        $url = 'https://www.bol.com/nl/rnwy/productPage/reviews?productId=' . urlencode($product_id) . '&offset=0&limit=60&loadMore=true';
        if (!$response = $this->getRemote($url))
            return array();

        $xpath = new XPath(Dom::createFromString($response));

        if (!$reviews = $xpath->xpathArray(".//ul[contains(@class, 'review__list')]//div[@class='review__body']", true))
            return array();

        $ratings = $xpath->xpathArray(".//ul[contains(@class, 'review__list')]//*[@class='star-rating']/span/@style");
        $authors = $xpath->xpathArray(".//ul[contains(@class, 'review__list')]//ul[@class='review-metadata__list']/li[1]");
        $dates = $xpath->xpathArray(".//ul[contains(@class, 'review__list')]//ul[@class='review-metadata__list']/li[3]");

        $results = array();
        for ($i = 0; $i < count($reviews); $i++)
        {
            $review = array();
            $review['review'] = $reviews[$i];

            if (isset($ratings[$i]))
            {
                if (strstr($ratings[$i], ':'))
                {
                    $r_parts = explode(":", $ratings[$i]);
                    if (count($r_parts) == 2)
                        $ratings[$i] = $r_parts[1];
                }

                $review['rating'] = ExtractorHelper::ratingPrepare($ratings[$i]);
            }

            if (isset($authors[$i]))
                $review['author'] = $authors[$i];

            if (isset($dates[$i]))
                $review['date'] = strtotime($dates[$i]);

            $results[] = $review;
        }
        return $results;
    }

    public function parseCurrencyCode()
    {
        return 'EUR';
    }

    public function afterParseFix(Product $product)
    {
        if ($product->availability == 'OutOfStock')
        {
            $product->price = null;
            $product->oldPrice = null;
        }

        return $product;
    }

    public function parseAvailability()
    {
        if ($this->xpathScalar(".//div[contains(@class, 'buy-block__options')]//a[@data-button-type='buy']"))
            return 'InStock';
    }

}
