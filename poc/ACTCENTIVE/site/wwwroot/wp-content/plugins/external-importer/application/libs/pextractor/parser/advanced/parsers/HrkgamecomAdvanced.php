<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

/**
 * HrkgamecomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class HrkgamecomAdvanced extends AdvancedParser {

    public function parseLinks()
    {
        $path = array(
            ".//div[contains(@class, 'item')]//a[@class='header']/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePagination()
    {
        $path = array(
            ".//div[contains(@class, 'pagination')]//a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//div[contains(@class, 'about_product')]", true);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//*[@class='price_block']//*[@class='rt_price']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseImage()
    {
        if ($image = $this->xpathScalar(".//div[@class='column panel_image ui fluid image']//img[@itemprop='image']/@data-src"))
            return $image;

        if ($images = $this->parseImages())
            return reset($images);
    }

    public function parseImages()
    {
        return $this->xpathArray(array(".//ul[@id='imageGallery']//li[not(contains(@data-thumb, 'header_'))]/@data-thumb"));
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name-value' => ".//div[@class='system_req_content']//li",
            ),
        );
    }

    public function getReviewsXpath()
    {
        return array(
            array(
                'review' => ".//*[@class='reviews']//*[@itemprop='reviewBody']",
                'rating' => ".//*[@class='reviews']//*[@class='ebay-star-rating']/@aria-label",
                'author' => ".//*[@class='reviews']//*[@itemprop='author']",
                'date' => ".//*[@class='reviews']//*[@itemprop='datePublished']",
            ),
        );
    }

}
