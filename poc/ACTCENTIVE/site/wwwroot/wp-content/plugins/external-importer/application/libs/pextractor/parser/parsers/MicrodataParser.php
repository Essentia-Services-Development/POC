<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\AbstractParser;
use ExternalImporter\application\libs\pextractor\parser\ParserFormat;
use ExternalImporter\application\libs\pextractor\ExtractorHelper;

/**
 * MicrodataParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class MicrodataParser extends AbstractParser {

    const FORMAT = ParserFormat::MICRODATA;

    public function parseTitle()
    {
        $paths = array(
            ".//h1[@itemprop='name']/text()",
            ".//h1[@itemprop='name']",
            ".//h2[@itemprop='name']",
            ".//h1/*[@itemprop='name']",
            ".//h1//*[@itemprop='name']",
            ".//h3[@itemprop='name']",
            ".//meta[@itemprop='name']/@content",
            ".//*[@itemtype='http://schema.org/Product']//*[@itemprop='name']",
            ".//*[@itemprop='name']/h1",
            ".//*[@itemprop='name']/h2",
        );

        return $this->xpathScalar($paths);
    }

    public function parseDescription()
    {
        $paths = array(
            ".//*[@itemtype='http://schema.org/Product']//*[@itemprop='description']/@content",
            ".//*[@itemtype='http://schema.org/Product']//*[@itemprop='description']",
            ".//*[@itemprop='description']/@content",
            ".//*[@itemprop='description']",
        );
        return $this->xpathScalar($paths, true);
    }

    public function parsePrice()
    {
        $paths = array(
            ".//*[@itemprop='offers']//*[@itemprop='price']/@content",
            ".//*[@itemtype='http://schema.org/Offer']//*[@itemprop='price']/@content",
            ".//*[@itemtype='http://schema.org/Product']//*[@itemprop='price']/@content",
            ".//*[@itemprop='offers']//*[@itemprop='price']",
            ".//*[@itemtype='http://schema.org/Offer']//*[@itemprop='price']",
            ".//*[@itemtype='http://schema.org/Product']//*[@itemprop='price']",
            ".//*[@itemprop='price']/@content",
            ".//*[@itemprop='lowPrice']/@content",
            ".//*[@itemprop='lowprice']/@content",
            ".//*[@itemprop='price']",
            ".//*[@itemprop='lowPrice']",
            ".//*[@itemprop='highPrice']/@content",
            ".//*[@itemprop='highPrice']",
        );
        return $this->xpathScalar($paths);
    }

    public function parseCurrencyCode()
    {
        $paths = array(
            ".//*[@itemprop='offers']//*[@itemprop='priceCurrency']/@content",
            ".//*[@itemtype='http://schema.org/Offer']//*[@itemprop='priceCurrency']/@content",
            ".//*[@itemprop='priceCurrency']/@content",
            ".//*[@itemprop='priceCurrency']",
        );
        return $this->xpathScalar($paths);
    }

    public function parseImage()
    {
        $paths = array(
            ".//img[@itemprop='image']/@src",
            ".//meta[@itemprop='image']/@content",
            ".//img[@itemprop='image']/@data-src",
        );
        return $this->xpathScalar($paths);
    }

    public function parseImages()
    {
        $paths = array(
            ".//img[@itemprop='image']/@src",
        );
        $images = $this->xpathArray($paths);

        if (count($images) > 1)
            return $images;
    }

    public function parseManufacturer()
    {
        $paths = array(
            ".//*[@itemprop='brand']/*[@itemprop='name']/@content",
            ".//*[@itemprop='brand']//*[@itemprop='name']",
            ".//*[@itemprop='brand']/@content",
            ".//*[@itemprop='brand']",
        );
        return $this->xpathScalar($paths);
    }

    public function parseAvailability()
    {
        $paths = array(
            ".//*[@itemprop='offers']//*[@itemprop='availability']/@href",
            ".//*[@itemtype='http://schema.org/Offer']//*[@itemprop='availability']/@href",
            ".//*[@itemtype='http://schema.org/Offer']//*[@itemprop='availability']/@content",
            ".//*[@itemprop='availability']/@href",
            ".//*[@itemprop='availability']/@content",
        );
        return $this->xpathScalar($paths);
    }

    public function parseCondition()
    {
        $paths = array(
            ".//*[@itemprop='offers']//*[@itemprop='itemCondition']/@href",
            ".//*[@itemtype='http://schema.org/Offer']//*[@itemprop='itemCondition']/@href",
            ".//*[@itemtype='http://schema.org/Offer']//*[@itemprop='itemCondition']/@content",
            ".//*[@itemprop='itemCondition']/@href",
            ".//*[@itemprop='itemCondition']/@content",
        );
        return $this->xpathScalar($paths);
    }

    protected function parseRatingValue()
    {
        $paths = array(
            ".//*[@itemprop='aggregateRating']//*[@itemprop='ratingValue']/@content",
            ".//*[@itemprop='ratingValue']/@content",
            ".//*[@itemprop='aggregateRating']//*[@itemprop='ratingValue']",
            ".//*[@itemprop='ratingValue']",
        );
        if (!$rating = $this->xpathScalar($paths))
            return null;

        $paths = array(
            ".//*[@itemprop='aggregateRating']//*[@itemprop='bestRating']/@content",
            ".//*[@itemprop='bestRating']/@content",
            ".//*[@itemprop='aggregateRating']//*[@itemprop='bestRating']",
            ".//*[@itemprop='bestRating']",
        );
        $best_rating = (int) $this->xpathScalar($paths);

        return ExtractorHelper::ratingPrepare($rating, $best_rating);
    }

    protected function parseReviewCount()
    {
        $paths = array(
            ".//*[@itemprop='aggregateRating']//*[@itemprop='reviewCount']/@content",
            ".//*[@itemprop='reviewCount']/@content",
            ".//*[@itemprop='aggregateRating']//*[@itemprop='reviewCount']",
            ".//*[@itemprop='reviewCount']",
        );
        return (int) $this->xpathScalar($paths);
    }

    public function parseCategoryPath()
    {
        $paths = array(
            ".//*[@itemtype='https://schema.org/BreadcrumbList'//*[@itemprop='name']",
            ".//*[@itemtype='http://schema.org/BreadcrumbList']//*[@itemprop='name']",
            ".//*[@itemtype='https://schema.org/BreadcrumbList']//*[@itemprop='name']/@content",
            ".//*[@itemtype='https://schema.org/BreadcrumbList']//*[@itemprop='itemListElement']",
            ".//*[@itemtype='http://schema.org/BreadcrumbList']//*[@itemprop='itemListElement']",
            ".//*[@itemtype='https://data-vocabulary.org/Breadcrumb']",
            ".//*[@itemtype='//schema.org/BreadcrumbList']//*[@itemprop='name']",
        );

        return $this->xpathArray($paths);
    }

    protected function getReviewsXpath()
    {
        return array(
            'author' => array(
                ".//*[@itemprop='review']//*[@itemprop='author']//*[@itemprop='name']",
                ".//*[@itemprop='review']//*[@itemprop='author']",
            ),
            'review' => array(
                ".//*[@itemprop='review']//*[@itemprop='reviewBody']",
                ".//*[@itemprop='review']//*[@itemprop='description']",
                ".//*[@itemprop='reviewBody']",
            ),
            'date' => array(
                ".//*[@itemprop='review']//*[@itemprop='datePublished']/@content",
                ".//*[@itemprop='review']//*[@itemprop='datePublished']",
            ),
            'rating' => array(
                ".//*[@itemprop='review']//*[@itemprop='ratingValue']/@content",
            ),
        );
    }

    public function parseSku()
    {
        $paths = array(
            ".//*[@itemprop='sku']/@content",
            ".//*[@itemprop='sku']",
        );
        return $this->xpathScalar($paths);
    }

    public function parseMpn()
    {
        $paths = array(
            ".//*[@itemprop='mpn']/@content",
        );
        return $this->xpathScalar($paths);
    }

    public function parseGtin()
    {
        $paths = array(
            ".//*[@itemprop='gtin8']/@content",
            ".//*[@itemprop='gtin12']/@content",
            ".//*[@itemprop='gtin13']/@content",
            ".//*[@itemprop='gtin14']/@content",
            ".//*[@itemprop='isbn']/@content",
            ".//*[@itemprop='ean']/@content",
        );        
        return $this->xpathScalar($paths);        
    }

}
