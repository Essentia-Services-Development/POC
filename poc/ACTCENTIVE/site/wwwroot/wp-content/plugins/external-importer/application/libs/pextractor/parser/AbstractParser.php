<?php

namespace ExternalImporter\application\libs\pextractor\parser;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\Product;
use ExternalImporter\application\libs\pextractor\client\XPath;
use ExternalImporter\application\helpers\TextHelper;
use ExternalImporter\application\libs\pextractor\ExtractorHelper;

/**
 * AbstractParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
abstract class AbstractParser implements ParserInterface
{

    const FORMAT = 0;

    protected $url;
    protected $base_uri;
    protected $host;
    protected $xpath;
    protected $html;

    public function __construct($url)
    {
        $this->url = $url;
        $base = parse_url($this->getUrl());
        $this->base_uri = $base['scheme'] . '://' . $base['host'];
        $this->host = TextHelper::getHostName($url);
    }

    public function getHttpOptions()
    {
        return array();
    }

    public function parseProduct(XPath $xpath, $html, $update_mode = false)
    {
        $this->xpath = $xpath;
        $this->html = $html;

        if (!$this->preParseProduct())
            return false;

        $product = new Product;

        if ($update_mode)
            $properties = array('title', 'price', 'oldPrice', 'inStock', 'availability', 'image', 'currencyCode');
        else
            $properties = ProductProcessor::getProductProperties();

        foreach ($properties as $property)
        {
            $parseMethod = 'parse' . ucfirst($property);
            if (method_exists($this, $parseMethod))
                $product->$property = $this->$parseMethod();
            else
                $product->$property = null;
        }

        if ($product == new Product)
            return false;

        return ProductProcessor::prepare($product, $this->base_uri);
    }

    protected function preParseProduct()
    {
        if (strpos($this->html, '<script src="/_Incapsula_Resource?'))
            throw new \Exception('Incapsula bot protection.', 403);

        return true;
    }

    public function parseListing(XPath $xpath, $html)
    {
        $this->xpath = $xpath;
        $this->html = $html;

        if (!$this->preParseListing())
            return false;

        $listing = new Listing;
        $listing->links = $this->parseLinks();
        $listing->pagination = $this->parsePagination();

        return ListingProcessor::prepare($listing, $this->url);
    }

    public function getBaseUri()
    {
        return $this->base_uri;
    }

    protected function preParseListing()
    {
        if (strpos($this->html, '<script src="/_Incapsula_Resource?'))
            throw new \Exception('Incapsula bot protection.', 403);

        return true;
    }

    public function parseLinks()
    {
        return array();
    }

    public function parsePagination()
    {
        return array();
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function parseFeatures()
    {
        if (!$xpaths = $this->getFeaturesXpath())
            return array();

        if (isset($xpaths['name']) || isset($xpaths['name-value']))
            $xpaths = array($xpaths);

        foreach ($xpaths as $xpath)
        {
            $names = $values = array();

            if (isset($xpath['name-value']))
            {
                if (!$name_values = $this->xpathArray($xpath['name-value']))
                    continue;

                if (isset($xpath['separator']))
                    $separator = $xpath['separator'];
                else
                    $separator = ':';

                foreach ($name_values as $name_value)
                {
                    $parts = explode($separator, $name_value, 2);
                    if (count($parts) !== 2)
                        continue;

                    $names[] = $parts[0];
                    $values[] = $parts[1];
                }
            }
            elseif (isset($xpath['name']) && isset($xpath['value']))
            {
                $names = $this->xpathArray($xpath['name']);
                $values = $this->xpathArray($xpath['value']);
            }

            if (!$names || !$values || count($names) != count($values))
                continue;

            $features = array();
            for ($i = 0; $i < count($names); $i++)
            {
                $feature = array();
                $feature['name'] = ucfirst(\sanitize_text_field(trim($names[$i], " \r\n:-")));
                $feature['value'] = trim(\sanitize_text_field($values[$i]), " \r\n:-");
                if (in_array($feature['name'], array('Condition')))
                    continue;
                $features[] = $feature;
            }

            if ($features)
                return $features;
        }
        return array();
    }

    protected function getFeaturesXpath()
    {
        return array();
    }

    public function parseReviews()
    {
        if (!$xpaths = $this->getReviewsXpath())
            return array();

        if (isset($xpaths['review']))
            $xpaths = array($xpaths);

        foreach ($xpaths as $xpath)
        {
            $reviews = $ratings = $authors = $dates = array();

            if (!empty($xpath['review']))
                $reviews = $this->xpathArray($xpath['review'], true);

            if (!$reviews)
                continue;

            if (!empty($xpath['rating']))
                $ratings = $this->xpathArray($xpath['rating']);

            if (!empty($xpath['author']))
                $authors = $this->xpathArray($xpath['author']);

            if (!empty($xpath['date']))
                $dates = $this->xpathArray($xpath['date']);

            $results = array();
            for ($i = 0; $i < count($reviews); $i++)
            {
                $review = array();
                $review['review'] = \normalize_whitespace(TextHelper::sanitizeHtml(html_entity_decode($reviews[$i])));

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
                    $review['author'] = \sanitize_text_field(html_entity_decode($authors[$i]));

                if (isset($dates[$i]))
                    $review['date'] = strtotime($dates[$i]);

                $results[] = $review;
            }

            if ($results)
                return $results;
        }
        return array();
    }

    protected function getReviewsXpath()
    {
        return array();
    }

    public function xpathScalar($path, $return_child = false)
    {
        return $this->xpath->xpathScalar($path, $return_child);
    }

    public function xpathArray($path, $return_child = false)
    {
        return $this->xpath->xpathArray($path, $return_child);
    }

    public function getName()
    {
        $path = explode('\\', get_called_class());
        return array_pop($path);
    }

    public function parseVariations()
    {
        return array();
    }
}
