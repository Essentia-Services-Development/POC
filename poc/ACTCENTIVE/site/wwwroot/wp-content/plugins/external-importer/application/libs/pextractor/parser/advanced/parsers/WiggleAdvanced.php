<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\client\XPath;
use ExternalImporter\application\libs\pextractor\client\Dom;

/**
 * WiggleAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class WiggleAdvanced extends AdvancedParser {

    public function parseLinks()
    {
        $path = array(
            ".//div[@id='search-results']//div[contains(@class,'js-result-list-item')]//a[@data-ga-action='Product Title']/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePagination()
    {
        $path = array(
            ".//div[contains(@class, 'bem-paginator__block--align-center')]//a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//div[@class='bem-pricing__list-price']/span[@class='bem-pricing__list-price--origin']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseImage()
    {
        if ($images = $this->parseImages())
            return reset($images);
    }

    public function parseImages()
    {
        $images = array();
        $results = $this->xpathArray(".//ul[@class='bem-pdp__gallery-list']//img/@src");
        foreach ($results as $img)
        {
            $img = str_replace('w=73&h=73', 'w=430&h=430', $img);
            $images[] = $img;
        }
        return $images;
    }

    public function parseCategoryPath()
    {
        $paths = array(
            ".//ol[@class='bem-breadcrumb__list']/li",
        );

        return $this->xpathArray($paths);
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name-value' => ".//li[@class='bem-pdp__features-item']",
            ),
        );
    }

    public function parseReviews()
    {
        if (!preg_match('~bazaarvoice\.com\/static\/(.+?)\/~', $this->html, $matches))
            return array();
        $p1 = $matches[1];

        if (!preg_match('~\/product-media\/(\d+)/~', $this->html, $matches))
            return array();
        $p2 = $matches[1];

        $url = 'https://wiggle.ugc.bazaarvoice.com/' . urlencode($p1) . '/' . urlencode($p2) . '/reviews.djs?format=embeddedhtml';

        if (!$response = $this->getRemote($url))
            return array();

        if (!preg_match('/\{"BVRRRatingSummarySourceID":"(.+?)\},/ims', $response, $matches))
            return array();

        $xpath = new XPath(Dom::createFromString(stripslashes($matches[1])));

        if (!$reviews = $xpath->xpathArray(".//span[@class='BVRRReviewText']", true))
            return array();

        $ratings = $xpath->xpathArray(".//span[@class='BVRRNumber BVRRRatingNumber']");
        $authors = $xpath->xpathArray(".//span[contains(@class, 'BVRRNickname')]");
        $dates = $xpath->xpathArray(".//span[contains(@class, 'BVRRReviewDate')]");

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
