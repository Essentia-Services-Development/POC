<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\AbstractParser;
use ExternalImporter\application\libs\pextractor\parser\ParserFormat;
use ExternalImporter\application\libs\pextractor\client\XPath;
use ExternalImporter\application\libs\pextractor\parser\Product;
use ExternalImporter\application\libs\pextractor\parser\Listing;
use ExternalImporter\application\libs\pextractor\parser\ProductProcessor;
use ExternalImporter\application\libs\pextractor\parser\ListingProcessor;
use \Keywordrush\AffiliateEgg\ParserManager;

/**
 * AeParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class AeParser extends AbstractParser {

    const FORMAT = ParserFormat::AE_PARSER;

    public function parseProduct(XPath $xpath, $html, $update_mode = false)
    {
        $this->xpath = $xpath;
        $this->html = $html;

        if (!$this->preParseProduct())
            return false;

        if (!$result = ParserManager::getInstance()->parseProduct($this->getUrl(), $xpath->getDomXPath(), $this->html))
            return false;

        $product = new Product;

        $product->title = $result['title'];
        $product->description = $result['description'];
        $product->price = $result['price'];
        $product->currencyCode = $result['currency'];
        $product->image = $result['orig_img'];
        $product->oldPrice = $result['old_price'];
        $product->manufacturer = $result['manufacturer'];
        $product->inStock = $result['in_stock'];

        if (isset($result['extra']['category']))
            $product->category = $result['extra']['category'];

        if (isset($result['extra']['rating']))
            $product->ratingValue = $result['extra']['rating'];

        if (isset($result['extra']['ratingCount']))
            $product->reviewCount = $result['extra']['ratingCount'];

        if (isset($result['extra']['images']))
            $product->images = $result['extra']['images'];

        if (isset($result['extra']['features']))
            $product->features = $result['extra']['features'];

        if (isset($result['extra']['categoryPath']))
            $product->categoryPath = $result['extra']['categoryPath'];

        $product->reviews = $this->getAeReviews($result);

        return ProductProcessor::prepare($product, $this->base_uri);
    }

    private function getAeReviews(array $result)
    {
        if (!isset($result['extra']['comments']))
            return array();

        $reviews = array();
        foreach ($result['extra']['comments'] as $comment)
        {
            $review = array();
            if (isset($comment['comment']))
                $review['review'] = $comment['comment'];
            else
                continue;

            if (isset($comment['rating']))
                $review['rating'] = $comment['rating'];

            if (isset($comment['name']))
                $review['author'] = $comment['name'];

            if (isset($comment['date']))
                $review['date'] = $comment['date'];

            $reviews[] = $review;
        }
        return $reviews;
    }

    public function parseListing(XPath $xpath, $html)
    {
        $this->xpath = $xpath;
        $this->html = $html;

        if (!$this->preParseListing())
            return false;

        if (!$result = ParserManager::getInstance()->parseCatalog($this->getUrl(), 1000, null, $xpath->getDomXPath(), $this->html))
            return false;

        $listing = new Listing;
        $listing->links = $result;

        return ListingProcessor::prepare($listing, $this->url);
    }

}
