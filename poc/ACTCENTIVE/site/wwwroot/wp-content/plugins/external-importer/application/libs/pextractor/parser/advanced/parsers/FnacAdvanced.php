<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\client\XPath;
use ExternalImporter\application\libs\pextractor\client\Dom;

/**
 * FnacAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class FnacAdvanced extends AdvancedParser
{

    public function getHttpOptions()
    {
        $httpOptions = parent::getHttpOptions();
        $httpOptions['cookies'] = array();
        $httpOptions['user-agent'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:107.0) Gecko/20100101 Firefox/107.0';
        $httpOptions['headers'] = array(
            'Cookie' => 'kacd_FRPRD_DEFAZ=3848392752~rv=59~id=b7ef6494191129f52f3902359544623d; QueueITAccepted-SDFrts345E-V3_frprdfnaccom=EventId%3Dfrprdfnaccom%26QueueId%3D4fa059ef-a183-494f-9486-a289c295f6e8%26RedirectType%3Dsafetynet%26IssueTime%3D1670940007%26Hash%3D6624277454894e952d2b3eaed6bc846493a8cace402e6b9e6fa51815a8245405; datadome=2BFFZS8lumjWCf6f4jbHaBN20WMbO0taINx8WJdkBw7S7X9hpaeUVKu59R-QebzB350DKzYttBU~JSizdmv-~zf-21h4rjpZ12T1mTMXgyalK3RzqMKisfsl3Vqa6lQs; ORGN=FnacAff; OrderInSession=0; kameleoonVisitorCode=tia907…862; cto_bundle=Y0RT3V92RHFUQzN1NExxYW5kcFpIYnF6RmRCdEdrbUViblFzRzRBeiUyRkZjbEZPbFVrUiUyRkJhZEVERTljUklQVXdvQ0JZMiUyRjBPQXViNGk0NVQxNGNIRFI2V2pxY3MwZWNGY0h1MFRnb0xRVnBrTm1ZMTZLcCUyQkZ3dmttMmxUajl0TlY4Skh3cHE3THVZOWR6bWpsTkFramVZTVFtRnZQajRockJ4SmhGWXo3V2FVM2dRYUloOHhoakxkVDYxYjQ4SW5LQmdKRg; cto_pxsig=9MtrlMEV9H_c8JpHSlTsFw; cto_bidid=Z2OHj19oWmJqTkpqZSUyRkdvUWg2JTJCVEViWTZNSllVdjZKUWg2QW44S1JhenlobjJJZm5BcWxTUFJVcVp4Y2o0aVglMkZEa1FjZ0lLMTRpcWpJJTJCUDM0dld5ejZLNG9HMERCRm5McSUyRjZBblA4JTJGMzElMkIlMkZFS2MlM0Q',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'en-us,en;q=0.5',
            'Cache-Control' => 'no-cache',
        );

        return $httpOptions;
    }

    public function parseLinks()
    {
        $path = array(
            ".//div[@clas='Carousel-item js-Carousel-item']//a/@href",
            ".//p[@class='Article-desc']/a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePagination()
    {
        $path = array(
            ".//div[@class='infinite__container infinite__container--bottom']//a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//div[@class='prodDetailSec']");
    }

    public function parseOldPrice()
    {
        $p = $this->xpathScalar(array(".//*[@class='f-priceBox']//*[@class='f-priceBox-price f-priceBox-price--old']", ".//span[@class='f-priceBox-price f-priceBox-price--reco f-priceBox-price--alt']", ".//*[contains(@class, 'f-priceBox-price--old')]"), true);
        return str_replace('&euro;', '.', $p);
    }

    public function parseImage()
    {
        if ($images = $this->parseImages())
            return reset($images);
    }

    public function parseImages()
    {
        return $this->xpathArray(".//div[@class='f-productVisuals-thumbnailsWrapper']//div/@data-src-zoom");
    }

    public function parseCategoryPath()
    {
        $paths = array(
            ".//ul[@class='f-breadcrumb']//li/a",
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
                'name' => ".//*[@class='f-productDetails-table']//td[1]",
                'value' => ".//*[@class='f-productDetails-table']//td[2]",
            ),
        );
    }

    public function parseReviews()
    {
        if (!$url = $this->xpathScalar(".//section[@id='CustomerReviews']//a[@class='productStrate__button productStrate__button--white']/@href"))
            return array();

        if (!$response = $this->getRemote($url))
            return array();

        $xpath = new XPath(Dom::createFromString($response));

        $r = array();
        $users = $xpath->xpathArray(".//section[contains(@class, 'customerReviewsSection')]//div[contains(@class, 'f-reviews-title--author')]");
        $comments = $xpath->xpathArray(".//section[contains(@class, 'customerReviewsSection')]//p[@class='f-reviews-txt']");
        $ratings = $xpath->xpathArray(".//section[contains(@class, 'customerReviewsSection')]//span[contains(@class, 'f-star')]");

        for ($i = 0; $i < count($comments); $i++)
        {
            $comment['review'] = \sanitize_text_field($comments[$i]);
            if (!empty($users[$i]))
                $comment['author'] = \sanitize_text_field($users[$i]);
            if (!empty($ratings[$i]))
                $comment['rating'] = $ratings[$i];
            $r[] = $comment;
        }
        return $r;
    }

    public function parseCurrencyCode()
    {
        return 'EUR';
    }
}
