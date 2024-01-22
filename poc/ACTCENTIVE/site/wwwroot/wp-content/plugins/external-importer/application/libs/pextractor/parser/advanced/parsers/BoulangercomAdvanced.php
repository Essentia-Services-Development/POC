<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\client\XPath;
use ExternalImporter\application\libs\pextractor\client\Dom;

/**
 * BoulangercomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class BoulangercomAdvanced extends AdvancedParser {

    public function getHttpOptions()
    {
        $httpOptions = parent::getHttpOptions();

        // reset cookies
        $httpOptions['cookies'] = array();
        return $httpOptions;
    }

    public function parseLinks()
    {
        $path = array(
            ".//div[@class='designations']/h2/a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePagination()
    {
        $path = array(
            ".//div[@class='navigationListe']//span/a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//*[@class='priceBarre']/*[@class='exponent']",
        );

        return $this->xpathScalar($paths);
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name' => ".//table[@class='characteristic']//td[@class='characteristics-table']/span",
                'value' => ".//table[@class='characteristic']//td[@class='characteristics-table']/text()",
            ),
        );
    }

    public function parseReviews()
    {
        if (!$url = $this->xpathScalar(".//noscript/iframe[contains(@src, 'reviews.htm?format=noscript')]/@src"))
            return array();

        if (!$response = $this->getRemote($url))
            return array();

        $xpath = new XPath(Dom::createFromString($response));

        if (!$reviews = $xpath->xpathArray(".//span[@class='BVRRReviewText']", true))
            return array();

        $ratings = $xpath->xpathArray(".//span[@class='BVRRNumber BVRRRatingNumber']");
        $authors = $xpath->xpathArray(".//span[@class='BVRRNickname']");
        $dates = $xpath->xpathArray(".//span[@class='BVRRValue BVRRReviewDate']");

        $results = array();
        for ($i = 0; $i < count($reviews); $i++)
        {
            $review = array();
            $review['review'] = $reviews[$i];

            if (isset($ratings[$i]))
                $review['rating'] = $ratings[$i];

            if (isset($authors[$i]))
                $review['author'] = $authors[$i];

            if (isset($dates[$i]))
                $review['date'] = strtotime($dates[$i]);

            $results[] = $review;
        }
        return $results;
    }

}
