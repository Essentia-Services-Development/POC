<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\AbstractParser;
use ExternalImporter\application\libs\pextractor\parser\ParserFormat;

/**
 * MicrodataParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2019 keywordrush.com
 */
class RdfaParser extends AbstractParser {

    const FORMAT = ParserFormat::RDFA;

    public function parseTitle()
    {
        $paths = array(
            ".//*[@typeof='Product']//*[@property='name']/text()",
            ".//*[@typeof='Product']//*[@property='name']",
            ".//h1[@property='name']/text()",
            ".//h1[@property='name']",
            ".//h2[@property='name']",
            ".//h1/*[@property='name']",
            ".//h1//*[@property='name']",
            ".//h3[@property='name']",
        );
        return $this->xpathScalar($paths);
    }

    public function parseDescription()
    {
        $paths = array(
            ".//*[@typeof='Product']//*[@property='description']/@content",
            ".//*[@typeof='Product']//*[@property='description']",
            ".//*[@property='description']/@content",
            ".//*[@property='description']",
        );
        return $this->xpathScalar($paths, true);
    }

    public function parsePrice()
    {
        $paths = array(
            ".//*[@property='offers']//*[@property='price']/@content",
            ".//*[@typeof='Offer']//*[@property='price']/@content",
            ".//*[@typeof='Product']//*[@property='price']/@content",
            ".//*[@property='offers']//*[@property='price']",
            ".//*[@typeof='Offer']//*[@property='price']",
            ".//*[@typeof='Product']//*[@property='price']",
            ".//*[@typeof='AggregateOffer']//*[@property='lowPrice']/@content",
            ".//*[@typeof='AggregateOffer']//*[@property='highPrice']/@content",
            ".//*[@typeof='AggregateOffer']//*[@property='lowPrice']",
            ".//*[@typeof='AggregateOffer']//*[@property='highPrice']",
            ".//*[@property='price']/@content",
            ".//*[@property='lowPrice']/@content",
            ".//*[@property='lowprice']/@content",
            ".//*[@property='price']",
            ".//*[@property='lowPrice']",
            ".//*[@property='highPrice']/@content",
            ".//*[@property='highPrice']",
        );
        return $this->xpathScalar($paths);
    }

    public function parseCurrencyCode()
    {
        $paths = array(
            ".//*[@property='offers']//*[@property='priceCurrency']/@content",
            ".//*[@typeof='Offer']//*[@property='priceCurrency']/@content",
            ".//*[@property='priceCurrency']/@content",
            ".//*[@property='priceCurrency']",
        );
        return $this->xpathScalar($paths);
    }

    public function parseImage()
    {
        $paths = array(
            ".//*[@typeof='Product']//img[@property='image']/@src",
            ".//img[@property='image']/@src",
            ".//meta[@property='image']/@content",
        );
        return $this->xpathScalar($paths);
    }

    public function parseManufacturer()
    {
        $paths = array(
            ".//*[@property='brand']/*[@property='name']/@content",
            ".//*[@property='brand']//*[@property='name']",
            ".//*[@property='brand']/@content",
            ".//*[@property='brand']",
        );
        return $this->xpathScalar($paths);
    }

    public function parseAvailability()
    {
        $paths = array(
            ".//*[@property='offers']//*[@property='availability']/@href",
            ".//*[@typeof='Offer']//*[@property='availability']/@href",
            ".//*[@typeof='Offer']//*[@property='availability']/@content",
            ".//*[@property='availability']/@href",
            ".//*[@property='availability']/@content",
        );
        return $this->xpathScalar($paths);
    }

    public function parseCondition()
    {
        $paths = array(
            ".//*[@property='offers']//*[@property='itemCondition']/@href",
            ".//*[@typeof='Offer']//*[@property='itemCondition']/@href",
            ".//*[@typeof='Offer']//*[@property='itemCondition']/@content",
            ".//*[@property='itemCondition']/@href",
            ".//*[@property='itemCondition']/@content",
        );
        return $this->xpathScalar($paths);
    }

    protected function parseRatingValue()
    {
        $paths = array(
            ".//*[@property='aggregateRating']//*[@property='ratingValue']/@content",
            ".//*[@property='aggregateRating']//*[@property='ratingValue']",
            ".//*[@typeof='schema:Product']//*[@property='schema::ratingValue']/@content",
            ".//*[@typeof='schema:Product']//*[@property='schema::ratingValue']",
        );
        if (!$rating = $this->xpathScalar($paths))
            return null;

        $paths = array(
            ".//*[@property='aggregateRating']//*[@property='bestRating']/@content",
            ".//*[@property='bestRating']/@content",
            ".//*[@property='aggregateRating']//*[@property='bestRating']",
            ".//*[@property='bestRating']",
        );
        $best_rating = (int) $this->xpathScalar($paths);

        return ExtractorHelper::ratingPrepare($rating, $best_rating);
    }

    protected function parseReviewCount()
    {
        $paths = array(
            ".//*[@property='aggregateRating']//*[@property='reviewCount']/@content",
            ".//*[@property='reviewCount']/@content",
            ".//*[@property='aggregateRating']//*[@property='reviewCount']",
            ".//*[@property='reviewCount']",
        );
        return (int) $this->xpathScalar($paths);
    }

    public function parseCategoryPath()
    {
        $paths = array(
            ".//*[@typeof='BreadcrumbList']//*[@property='name']",
        );
        return $this->xpathArray($paths);
    }

    protected function getReviewsXpath()
    {
        return array(
            'author' => array(
                ".//*[@property='review']//*[@property='author']//*[@property='name']",
                ".//*[@property='review']//*[@property='author']",
                ".//*[@typeof='Review']//*[@property='author']",
            ),
            'review' => array(
                ".//*[@property='review']//*[@property='reviewBody']",
                ".//*[@typeof='Review']//*[@property='reviewBody']",
            ),
            'date' => array(
                ".//*[@property='review']//*[@property='datePublished']/@content",
                ".//*[@property='review']//*[@property='datePublished']",
                ".//*[@typeof='Review']//*[@property='datePublished']",
            ),
            'rating' => array(
                ".//*[@property='review']//*[@property='ratingValue']",
                ".//*[@typeof='Review']//*[@property='ratingValue']",
            ),
        );
    }

}
