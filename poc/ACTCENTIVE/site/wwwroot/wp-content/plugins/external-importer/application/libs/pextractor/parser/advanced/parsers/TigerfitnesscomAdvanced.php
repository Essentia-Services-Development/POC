<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

/**
 * TigerfitnesscomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class TigerfitnesscomAdvanced extends AdvancedParser {

    public function parseLinks()
    {
        $path = array(
            ".//a[@class='productitem--image-link']/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePagination()
    {
        $path = array(
            ".//ul[@class='pagination--inner']//a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseDescription()
    {
        $path = array(
            ".//div[@class='product-description rte']",
        );

        return $this->xpathScalar($path, true);
    }

    public function getReviewsXpath()
    {
        return array(
            array(
                'review' => ".//div[@class='stamped-reviews']//p[@class='stamped-review-content-body']",
                'rating' => ".//div[@class='stamped-reviews']//span/@data-rating",
                'author' => ".//div[@class='stamped-reviews']//strong[@class='author']",
                'date' => ".//div[@class='stamped-reviews']//div[@class='created']",
            ),
        );
    }

    public function parseCurrencyCode()
    {
        return 'USD';
    }

}
