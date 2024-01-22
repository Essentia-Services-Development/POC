<?php

namespace ExternalImporter\application\libs\pextractor;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\client\Dom;
use ExternalImporter\application\libs\pextractor\client\XPath;
use ExternalImporter\application\libs\pextractor\parser\ParserFactory;
use ExternalImporter\application\libs\pextractor\parser\ParserFormat;
use ExternalImporter\application\libs\pextractor\parser\Product;
use ExternalImporter\application\libs\pextractor\parser\ProductProcessor;
use ExternalImporter\application\libs\pextractor\parser\Listing;
use ExternalImporter\application\libs\pextractor\parser\ListingProcessor;
use ExternalImporter\application\helpers\TextHelper;

/**
 * Extractor class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2019 keywordrush.com
 */
class Extractor {

    protected $last_used_parsers = array();

    public function extractListing($url, array $config = array(), $html = null, $formats = null, array $httpOptions = array())
    {
        if (!$formats)
            $formats = ParserFormat::ALL_LISTING;

        if (!$parsers = ParserFactory::createParsers($formats, $url))
            return false;

        if (isset($parsers[ParserFormat::ADVANCED_PARSER]))
            $httpOptions = array_replace($parsers[ParserFormat::ADVANCED_PARSER]->getHttpOptions(), $httpOptions);

        $xpath = $this->createXPath($url, $config, $html, $httpOptions);
        $listing = $this->parseListing($xpath, Dom::getHtml(), $parsers);
        return $listing;
    }

    public function extractProduct($url, array $config = array(), $html = null, $formats = null, array $httpOptions = array(), $update_mode = false)
    {
        if (!$formats)
            $formats = ParserFormat::ALL_PRODUCT;

        if (!$parsers = ParserFactory::createParsers($formats, $url))
            return false;

        if (isset($parsers[ParserFormat::ADVANCED_PARSER]))
            $httpOptions = array_replace($parsers[ParserFormat::ADVANCED_PARSER]->getHttpOptions(), $httpOptions);

        $xpath = $this->createXPath($url, $config, $html, $httpOptions);

        if ($product = $this->parseProduct($xpath, Dom::getHtml(), $parsers, $update_mode))
        {
            $product->link = $url;
            $product->domain = TextHelper::getHostName($url);
        }
        return $product;
    }

    protected function parseListing(XPath $xpath, $html, array $parsers)
    {
        $listing = new Listing;
        foreach ($parsers as $parser)
        {
            if (!$result = $parser->parseListing($xpath, $html))
                continue;

            $tmp_listing = clone $listing;
            $listing = ListingProcessor::mergeListings($listing, $result);

            if ($listing != $tmp_listing)
                $this->last_used_parsers[] = $parser->getName();

            //if ($listing->links && $listing->pagination)
            if ($listing->links)
                return $listing;
        }

        if ($listing->links)
            return $listing;
        else
            return false;
    }

    protected function parseProduct(XPath $xpath, $html, array $parsers, $update_mode = false)
    {
        $product = new Product;
        foreach ($parsers as $parser)
        {
            if (!$result = $parser->parseProduct($xpath, $html, $update_mode))
                continue;

            $tmp_product = clone $product;
            $product = ProductProcessor::mergeProducts($product, $result);

            if ($product != $tmp_product)
                $this->last_used_parsers[] = $parser->getName();

        }

        if (isset($parsers[ParserFormat::ADVANCED_PARSER]))
        {
            $parser = $parsers[ParserFormat::ADVANCED_PARSER];
            $product = $parser->afterParseFix($product);
        }

        // final prepare
        if (count($this->last_used_parsers) > 1)
            $product = ProductProcessor::prepare($product, $parser->getBaseUri());

        if (ProductProcessor::isRequaredFieldsFilled($product))
            return $product;
        else
            return false;
    }

    protected function createXPath($url, array $config = array(), $html = null, array $httpOptions = array())
    {
        return new XPath($this->createDom($url, $config, $html, $httpOptions));
    }

    protected function createDom($url, array $config = array(), $html = null, array $httpOptions = array())
    {
        if ($html)
            return Dom::createFromString($html);
        else
            return Dom::createFromUrl($url, $config, $httpOptions);
    }

    public function getLastUsedParsers()
    {
        return $this->last_used_parsers;
    }

}
