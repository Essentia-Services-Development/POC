<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\ExtractorHelper;
use ExternalImporter\application\libs\pextractor\client\XPath;
use ExternalImporter\application\libs\pextractor\client\Dom;

/**
 * LdlccomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class LdlccomAdvanced extends AdvancedParser {

    public function parseLinks()
    {
        $path = array(
            ".//div[@class='pdt-info']//h3/a/@href",
            ".//*[@id='productListingWrapper']//a[@class='nom']/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePagination()
    {
        $path = array(
            ".//ul[@class='pagination']//li//a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseCategoryPath()
    {
        $paths = array(
            ".//div[@class='breadcrumb']//a",
        );

        return $this->xpathArray($paths);
    }

    public function parseFeatures()
    {
        if (!$trs = $this->xpathArray(".//table[@id='product-parameters']//tr", true))
            return array();

        $features = array();
        $values = array();
        $i = 0;
        foreach ($trs as $tr)
        {
            $xpath = new XPath(Dom::createFromString($tr));

            $value = \sanitize_text_field($xpath->xpathScalar("//td[@class='checkbox' or @class='no-checkbox']"));
            if ($name = \sanitize_text_field($xpath->xpathScalar(".//td[@class='label']/h3")))
            {
                $i++;
                $feature['name'] = $name;
                $feature['value'] = $value;
                $features[$i] = $feature;
            } else
                $features[$i]['value'] .= ' | ' . $value;
                
        }

        return $features;
    }

    public function getReviewsXpath()
    {
        return array(
            array(
                'review' => "",
                'rating' => "",
                'author' => "",
                'date' => "",
            ),
        );
    }

    public function parseReviews()
    {
        $users = $this->xpathArray(".//ul[@class='list-reviews']//div[@class='author']/strong");
        $comments = $this->xpathArray(".//ul[@class='list-reviews']//div[@class='txt']//p");
        $ratings = $this->xpathArray(".//ul[@class='list-reviews']//div[@class='ratingClient']/span/@class");
        $dates = $this->xpathArray(".//ul[@class='list-reviews']//div[@class='author']/div[@class='date']/em");
        $r = array();
        for ($i = 0; $i < count($comments); $i++)
        {
            $comment['review'] = \sanitize_text_field($comments[$i]);
            if (!empty($users[$i]))
                $comment['author'] = \sanitize_text_field($users[$i]);
            if (!empty($ratings[$i]))
                $comment['rating'] = ExtractorHelper::ratingPrepare((int) str_replace('star-', '', $ratings[$i]), 10);
            if (!empty($dates[$i]))
            {
                $date = str_replace('LDLC le ', '', $dates[$i]);
                $date = str_replace('/', '-', $date);
                $comment['date'] = strtotime($date);
            }
            $r[] = $comment;
        }
        return $r;
    }

    public function parseCurrencyCode()
    {
        return 'EUR';
    }

}
