<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\client\XPath;
use ExternalImporter\application\libs\pextractor\client\Dom;
use ExternalImporter\application\helpers\TextHelper;

/**
 * SnapdealcomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class SnapdealcomAdvanced extends AdvancedParser {

    public function parseLinks()
    {
        $path = array(
            ".//div[@class='productWrapper']//div[@class='product-title']/a/@href",
            ".//div[@class='offerCont_wrap']//a[@class='OffersContentBoxLink']/@href",
            ".//div[@id='products']//*[contains(@class,'product-desc-rating')]/a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePagination()
    {
        // ajax
        return array();
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//*[@id='buyPriceBox']//s/span",
        );

        return $this->xpathScalar($paths);
    }

    public function parseManufacturer()
    {
        $paths = array(
            ".//div[@class='prd_mid_info']//span[@class='pID']/a",
        );

        return $this->xpathScalar($paths);
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name-value' => ".//div[@class='spec-body p-keyfeatures']//li",
            ),
            array(
                'name' => ".//*[@id='productSpecs']//table[@class='product-spec']//td[1]",
                'value' => ".//*[@id='productSpecs']//table[@class='product-spec']//td[2]",
            ),
        );
    }

    public function parseInStock()
    {
        if ($this->xpathScalar(".//div[@class='sold-out-err']"))
            return false;
    }

    public function parseReviews()
    {

        if (!$pid = $this->xpathScalar(".//input[@id='productId']/@value"))
            return array();

        if (!$purl = $this->xpathScalar(".//input[@id='pdpPageUrl']/@value"))
            return array();

        $request_url = 'https://www.snapdeal.com/acors/web/getReviewsAndRatings/v2';
        $response = \wp_remote_post($request_url, array(
            'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
            'body' => '{"productId":"' . $pid . '","pageUrl":"' . $purl . '","bucketLabelNames":["mobiles-mobile1phones"]}',
            'method' => 'POST'
        ));

        if (\is_wp_error($response))
            return array();

        if (!$body = \wp_remote_retrieve_body($response))
            return array();

        $xpath = new XPath(Dom::createFromString($body));

        $r = array();
        $users = $xpath->xpathArray(".//div[@class='commentreview']//div[@class='hidden _reviewUserName']");
        $comments = $xpath->xpathArray(".//div[@class='commentreview']//div[@class='user-review']/p");
        $ratings = $xpath->xpathArray(".//div[@class='commentreview']//div[@class='rating']", true);
        $dates = $xpath->xpathArray(".//div[@class='commentreview']//div[@class='_reviewUserName']");

        for ($i = 0; $i < count($comments); $i++)
        {
            $comment['review'] = \sanitize_text_field($comments[$i]);
            if (!empty($users[$i]))
                $comment['author'] = \sanitize_text_field($users[$i]);
            if (!empty($ratings[$i]))
            {
                $comment['rating'] = TextHelper::ratingPrepare(substr_count($ratings[$i], 'active'));
            }
            if (!empty($dates[$i]))
            {
                $date_parts = explode(' on ', $dates[$i]);
                if (count($date_parts) == 2)
                    $comment['date'] = strtotime($date_parts[1]);
            }
            $r[] = $comment;
        }
        return $r;
    }

    public function parseCurrencyCode()
    {
        return 'INR';
    }

}
