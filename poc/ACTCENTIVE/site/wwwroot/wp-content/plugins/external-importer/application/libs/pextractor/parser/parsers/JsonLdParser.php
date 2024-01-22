<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\AbstractParser;
use ExternalImporter\application\libs\pextractor\parser\ParserFormat;
use ExternalImporter\application\libs\pextractor\ExtractorHelper;

/**
 * JsonLdParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class JsonLdParser extends AbstractParser {

    const FORMAT = ParserFormat::JSON_LD;

    protected $allowedTypesProduct = array('Product', 'VideoGame', 'Hotel', 'Book', 'SoftwareApplication', 'ProductModel', 'Movie', 'IndividualProduct', 'ProductGroup');
    protected $allowedTypesBreadcrumb = array('BreadcrumbList');
    protected $allowedTypesReview = array('Review');
    protected $allowedTypesImage = array('ImageObject');
    protected $allowedTypesList = array('ItemList');
    protected $ld_product;
    protected $ld_breadcrumb;
    protected $ld_images = array();
    protected $ld_reviews = array();
    protected $ld_list = array();
    protected $offer = array();

    protected function preParseProduct()
    {
        if (!$this->parseLdJson())
            return false;

        $this->parseLdOffer();

        return true;
    }

    protected function preParseListing()
    {
        if (!$this->parseLdJson())
            return false;

        return true;
    }

    protected function parseLdJson()
    {
        $lds = $this->xpathArray(".//script[@type='application/ld+json']", true);

        foreach ($lds as $ld)
        {
            $ld = $this->fixLdJson($ld);

            if (!$data = json_decode($ld, true))
                continue;

            if (count($data) == 1 && isset($data[0]))
                $data = $data[0];

            if (!$this->ld_breadcrumb && isset($data['breadcrumb']))
            {
                if (isset($data['breadcrumb']['@type']) && $this->isTypeAllowed($data['breadcrumb']['@type'], $this->allowedTypesBreadcrumb))
                    $this->ld_breadcrumb = $data['breadcrumb'];
            }

            if (isset($data['mainEntity']))
                $data = $data['mainEntity'];

            if (isset($data['@graph']))
                $entities = $data['@graph'];
            elseif (isset($data[0]) && isset($data[1]))
                $entities = $data;
            elseif (isset($data[0]))
                $entities = array($data[0]);
            else
                $entities = array($data);

            foreach ($entities as $data)
            {
                if (!isset($data['@type']))
                    continue;

                $type = $data['@type'];

                if ($this->isTypeAllowed($type, $this->allowedTypesProduct))
                    $this->ld_product = $data;
                elseif ($this->isTypeAllowed($type, $this->allowedTypesBreadcrumb))
                    $this->ld_breadcrumb = $data;
                elseif ($this->isTypeAllowed($type, $this->allowedTypesReview))
                    $this->ld_reviews[] = $data;
                elseif ($this->isTypeAllowed($type, $this->allowedTypesImage))
                    $this->ld_images[] = $data;
                elseif ($this->isTypeAllowed($type, $this->allowedTypesList))
                    $this->ld_list[] = $data;
                elseif ($type == 'WebPageElement' && isset($data['offers']['itemOffered'][0]))
                    $this->ld_product = $data['offers']['itemOffered'][0];
            }
        }

        if ($this->ld_product || $this->ld_breadcrumb || $this->ld_reviews || $this->ld_images || $this->ld_list)
            return true;
        else
            return false;
    }

    public function parseLinks()
    {
        if (isset($this->ld_list[0]['itemListElement']))
            $list = $this->ld_list[0]['itemListElement'];
        elseif (isset($this->ld_list['itemListElement']))
            $list = $this->ld_list['itemListElement'];
        else
            return array();

        if (!is_array($list))
            return array();

        $urls = array();
        foreach ($list as $l)
        {
            if (isset($l['url']))
                $urls[] = $l['url'];
        }

        return $urls;
    }

    protected function parseLdOffer()
    {
        if (!$this->ld_product)
            return false;

        if (isset($this->ld_product['offers'][0]))
            $this->offer = $this->ld_product['offers'][0];
        elseif (isset($this->ld_product['offers']))
            $this->offer = $this->ld_product['offers'];
        elseif (isset($this->ld_product['Offers']))
            $this->offer = $this->ld_product['Offers'];
        else
            $this->offer = array();
    }

    protected function fixLdJson($ld)
    {
        $ld = ExtractorHelper::fixHiddenCharacters($ld);
        $ld = preg_replace('/\/\*.+?\*\//', '', $ld);
        $ld = trim($ld, ";");
        return $ld;
    }

    protected function isTypeAllowed($type, array $allowed_types)
    {
        if (is_array($type))
            $type = reset($type);

        $type = strtolower(preg_replace('/https?:\/\/schema\.org\//', '', $type));
        $allowed = array_map('strtolower', $allowed_types);
        if (in_array($type, $allowed))
            return true;
        else
            return false;
    }

    public function parseTitle()
    {
        if (isset($this->ld_product['name']))
            return $this->ld_product['name'];
    }

    public function parseDescription()
    {
        if (isset($this->ld_product['description']))
            return $this->ld_product['description'];
    }

    public function parsePrice()
    {
        if (!empty($this->offer['price']))
            return $this->offer['price'];
        elseif (isset($this->offer['Price']))
            return $this->offer['Price'];
        elseif (isset($this->offer['lowPrice']))
            return $this->offer['lowPrice'];
        elseif (isset($this->offer['priceRange']))
            return $this->offer['priceRange'];
        elseif (isset($this->offer['priceSpecification']))
            return $this->offer['priceSpecification']['price'];
    }

    public function parseCurrencyCode()
    {
        if (isset($this->offer['priceCurrency']))
            return $this->offer['priceCurrency'];
    }

    public function parseImage()
    {

        if (isset($this->ld_product['image']))
        {
            if (isset($this->ld_product['image']['url']))
                return $this->ld_product['image']['url'];
            elseif (is_array($this->ld_product['image']) && isset($this->ld_product['image'][0]['url']))
                return $this->ld_product['image'][0]['url'];
            elseif (is_array($this->ld_product['image']) && isset($this->ld_product['image'][0]['contentUrl']))
                return $this->ld_product['image'][0]['contentUrl'];            
            elseif (is_array($this->ld_product['image']) && isset($this->ld_product['image'][0]['contentURL']))
                return $this->ld_product['image'][0]['contentURL'];
            elseif (is_array($this->ld_product['image']) && is_array($this->ld_product['image']) && isset($this->ld_product['image'][0]))
                return $this->ld_product['image'][0];
            elseif (isset($this->ld_product['image']['contentUrl']) && is_array($this->ld_product['image']['contentUrl']) && isset($this->ld_product['image']['contentUrl'][0]))
                return $this->ld_product['image']['contentUrl'][0];
            elseif (isset($this->ld_product['image']['contentUrl']) && !is_array($this->ld_product['image']['contentUrl']))
                return $this->ld_product['image']['contentUrl'];
            elseif (is_scalar($this->ld_product['image']))
                return $this->ld_product['image'];
        }

        if (isset($this->ld_product['offers']['image']))
        {
            return $this->ld_product['offers']['image'];
        }
    }

    public function parseImages()
    {
        if (isset($this->ld_product['image']['contentUrl']) && is_array($this->ld_product['image']['contentUrl']))
            return $this->ld_product['image']['contentUrl'];

        if ($this->ld_images)
            $images = $this->ld_images;
        elseif (isset($this->ld_product['image']) && is_array($this->ld_product['image']))
            $images = $this->ld_product['image'];
        else
            return;

        if (is_array($images) && !isset($images[0]))
            $images = array($images);

        $results = array();
        foreach ($images as $img)
        {
            if (isset($img['url']))
                $results[] = $img['url'];
            elseif (isset($img['contentUrl']))
                $results[] = $img['contentUrl'];
            elseif (isset($img['thumbnailUrl']))
                $results[] = $img['thumbnailUrl'];
            elseif (!is_array($img))
                $results[] = $img;
        }
        return $results;
    }

    public function parseManufacturer()
    {
        if (isset($this->ld_product['brand']['name']))
            return $this->ld_product['brand']['name'];
        elseif (isset($this->ld_product['brand']) && !is_array($this->ld_product['brand']))
            return $this->ld_product['brand'];
    }

    public function parseAvailability()
    {
        if (isset($this->offer['availability']))
            return $this->offer['availability'];
    }

    public function parseCondition()
    {
        if (isset($this->offer['itemCondition']))
            return $this->offer['itemCondition'];
    }

    protected function parseRatingValue()
    {
        if (isset($this->ld_product['aggregateRating']['ratingValue']))
        {
            if (isset($this->ld_product['aggregateRating']['bestRating']))
                $bestRating = (int) $this->ld_product['aggregateRating']['bestRating'];
            else
                $bestRating = null;

            return ExtractorHelper::ratingPrepare($this->ld_product['aggregateRating']['ratingValue'], $bestRating);
        }
    }

    protected function parseReviewCount()
    {
        if (isset($this->offer['aggregateRating']['reviewCount']))
            return (int) $this->offer['aggregateRating']['reviewCount'];

        if (isset($this->ld_product['aggregateRating']['reviewCount']))
            return (int) $this->ld_product['aggregateRating']['reviewCount'];
    }

    public function parseCategory()
    {
        if (isset($this->ld_product['category']) && !is_array($this->ld_product['category']))
            return $this->ld_product['category'];
    }

    public function parseCategoryPath()
    {
        if (isset($this->ld_product['itemListElement']) && is_array($this->ld_product['itemListElement']))
            $itemListElement = $this->ld_product['itemListElement'];
        elseif ($this->ld_breadcrumb && isset($this->ld_breadcrumb['itemListElement']))
            $itemListElement = $this->ld_breadcrumb['itemListElement'];
        else
            return;

        $category_path = array();
        foreach ($itemListElement as $item)
        {
            if (isset($item['item']['name']))
                $category_path[] = $item['item']['name'];
            elseif (isset($item['name']))
                $category_path[] = $item['name'];
        }
        return $category_path;
    }

    public function parseReviews()
    {
        $comments = array();
        if (isset($this->ld_product['review']))
            $reviews = $this->ld_product['review'];
        elseif (isset($this->ld_product['reviews']))
            $reviews = $this->ld_product['reviews'];
        elseif ($this->ld_reviews)
            $reviews = $this->ld_reviews;
        else
            return;

        if (!is_array($reviews))
            return;

        if (count($reviews) == 1 && isset($reviews[0]) && isset($reviews[0][0]))
            $reviews = $reviews[0];

        if (!isset($reviews[0]) && isset($reviews['reviewBody']))
            $reviews = array($reviews);

        foreach ($reviews as $review)
        {
            $comment = array();
            if (!empty($review['reviewBody']))
                $reviewBody = $review['reviewBody'];
            elseif (!empty($review['description']))
                $reviewBody = $review['description'];
            else
                $reviewBody = '';
            $reviewBody = \sanitize_textarea_field($reviewBody);
            if (!$reviewBody)
                continue;
            $comment['review'] = $reviewBody;

            if (!empty($review['author']) && !is_array($review['author']))
                $name = $review['author'];
            elseif (!empty($review['author']['name']))
                $name = $review['author']['name'];
            else
                $name = '';
            if ($name)
                $comment['author'] = \sanitize_text_field($name);

            if (!empty($review['datePublished']))
                $comment['date'] = strtotime($review['datePublished']);
            if (!empty($review['reviewRating']['ratingValue']))
            {
                if (!empty($review['reviewRating']['bestRating']))
                    $bestRating = (int) $review['reviewRating']['bestRating'];
                else
                    $bestRating = null;

                $comment['rating'] = ExtractorHelper::ratingPrepare($review['reviewRating']['ratingValue'], $bestRating);
            }
            $comments[] = $comment;
        }

        return $comments;
    }

    public function parseFeatures()
    {
        if (isset($this->ld_product['additionalProperty']))
            $features = $this->ld_product['additionalProperty'];
        else
            return array();

        $results = array();
        $result = array();
        foreach ($features as $f)
        {
            if (!isset($f['name']) || !isset($f['value']))
                continue;

            $result['name'] = \sanitize_text_field(html_entity_decode($f['name']));
            $result['value'] = \sanitize_text_field(html_entity_decode($f['value']));
            $results[] = $result;
        }

        return $results;
    }

    public function parseSku()
    {
        if (isset($this->ld_product['sku']))
            return $this->ld_product['sku'];
        elseif (isset($this->ld_product['productID']))
            return $this->ld_product['productID'];
    }

    public function parseMpn()
    {
        if (isset($this->ld_product['mpn']))
            return $this->ld_product['mpn'];
    }

    public function parseGtin()
    {
        if (isset($this->ld_product['gtin8']))
            return $this->ld_product['gtin8'];
        elseif (isset($this->ld_product['gtin12']))
            return $this->ld_product['gtin12'];
        elseif (isset($this->ld_product['gtin13']))
            return $this->ld_product['gtin13'];
        elseif (isset($this->ld_product['gtin14']))
            return $this->ld_product['gtin14'];
        elseif (isset($this->ld_product['isbn']))
            return $this->ld_product['isbn'];
        elseif (isset($this->ld_product['ean']))
            return $this->ld_product['ean'];
    }

}
