<?php

namespace Keywordrush\AffiliateEgg;

use Keywordrush\AffiliateEgg\TextHelper;

/**
 * LdShopParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
abstract class LdShopParser extends ShopParser {

    protected $ld_json;
    protected $product_types = array('Product', 'VideoGame', 'Hotel', 'http://schema.org/Product', 'Book', 'https://schema.org/Product', 'SoftwareApplication', 'ProductModel');

    public function parseLdJson()
    {
        $lds = $this->xpathArray(".//script[@type='application/ld+json']", true);
        foreach ($lds as $ld)
        {
            $ld = TextHelper::fixHiddenCharacters($ld);
            
            $ld = preg_replace('/\/\*.+?\*\//', '', $ld);
            $ld = trim($ld, ";");

            if (!$data = json_decode($ld, true))
                continue;
            if (isset($data['mainEntity']))
                $data = $data['mainEntity'];

            if (isset($data['@graph']))
            {
                foreach ($data['@graph'] as $d)
                {
                    if (isset($d['@type']) && is_array($d['@type']))
                        $d['@type'] = reset($d['@type']);

                    if (isset($d['@type']) && is_scalar($d['@type']) && (in_array($d['@type'], $this->product_types) || in_array(ucfirst(strtolower($d['@type'])), $this->product_types)))
                        $data = $d;
                }
            } elseif (isset($data[0]) && isset($data[1]))
            {
                foreach ($data as $d)
                {
                    if (isset($d['@type']) && (in_array($d['@type'], $this->product_types) || in_array(ucfirst(strtolower($d['@type'])), $this->product_types)))
                        $data = $d;
                }
            } elseif (isset($data[0]))
                $data = $data[0];


            if (isset($data['@type']) && (in_array($data['@type'], $this->product_types) || in_array(ucfirst(strtolower($data['@type'])), $this->product_types)))
            {
                $this->ld_json = $data;
                return $this->ld_json;
            }
        }

        return false;
    }

    public function parseTitle()
    {
        $this->ld_json = null;
        if (!$this->parseLdJson())
            return;

        if (isset($this->ld_json['name']))
            return $this->ld_json['name'];
    }

    public function parseDescription()
    {
        if (isset($this->ld_json['description']))
            return $this->ld_json['description'];
    }

    public function parsePrice()
    {
        if (isset($this->ld_json['offers'][0]['price']))
            return $this->ld_json['offers'][0]['price'];
        elseif (isset($this->ld_json['offers']['price']))
            return $this->ld_json['offers']['price'];
        elseif (isset($this->ld_json['offers']['Price']))
            return $this->ld_json['offers']['Price'];
        elseif (isset($this->ld_json['offers']['lowPrice']))
            return $this->ld_json['offers']['lowPrice'];
        elseif (isset($this->ld_json['offers'][0]['lowPrice']))
            return $this->ld_json['offers'][0]['lowPrice'];
        elseif (isset($this->ld_json['priceRange']))
            return $this->ld_json['priceRange'];
        elseif (isset($this->ld_json['offers'][0]['priceSpecification']['price']))
            return $this->ld_json['offers'][0]['priceSpecification']['price'];
    }

    public function parseManufacturer()
    {
        if (isset($this->ld_json['brand']['name']))
            return $this->ld_json['brand']['name'];
        elseif (isset($this->ld_json['brand']) && !is_array($this->ld_json['brand']))
            return $this->ld_json['brand'];
    }

    public function parseImg()
    {
        if (isset($this->ld_json['image']))
        {
            if (isset($this->ld_json['image']['url']))
                return $this->ld_json['image']['url'];
            elseif (is_array($this->ld_json['image']) && isset($this->ld_json['image'][0]['url']))
                return $this->ld_json['image'][0]['url'];
            elseif (is_array($this->ld_json['image']) && isset($this->ld_json['image'][0]['contentURL']))
                return $this->ld_json['image'][0]['contentURL'];
            elseif (is_array($this->ld_json['image']) && isset($this->ld_json['image'][0]['contentUrl']))
                return $this->ld_json['image'][0]['contentUrl'];
            elseif (is_array($this->ld_json['image']) && is_array($this->ld_json['image']) && isset($this->ld_json['image'][0]))
                return $this->ld_json['image'][0];
            elseif (isset($this->ld_json['image']['contentUrl']) && is_array($this->ld_json['image']['contentUrl']) && isset($this->ld_json['image']['contentUrl'][0]))
                return $this->ld_json['image']['contentUrl'][0];
            elseif (isset($this->ld_json['image']['contentUrl']) && !is_array($this->ld_json['image']['contentUrl']))
                return $this->ld_json['image']['contentUrl'];
            else
                return $this->ld_json['image'];
        }

        if (isset($this->ld_json['offers']['image']))
        {
            return $this->ld_json['offers']['image'];
        }
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();

        $extra['comments'] = array();
        if (isset($this->ld_json['review']))
            $reviews = $this->ld_json['review'];
        elseif (isset($this->ld_json['reviews']))
            $reviews = $this->ld_json['reviews'];
        else
            $reviews = array();

        if ($reviews && is_array($reviews))
        {
            foreach ($reviews as $review)
            {
                $comment = array();
                if (!empty($review['reviewBody']))
                    $reviewBody = $review['reviewBody'];
                elseif (!empty($review['description']))
                    $reviewBody = $review['description'];
                else
                    $reviewBody = '';
                $reviewBody = \sanitize_text_field(html_entity_decode($reviewBody));
                if (!$reviewBody)
                    continue;
                $comment['comment'] = $reviewBody;

                if (!empty($review['author']) && !is_array($review['author']))
                    $name = $review['author'];
                elseif (!empty($review['author']['name']))
                    $name = $review['author']['name'];
                else
                    $name = '';
                if ($name)
                    $comment['name'] = sanitize_text_field($name);

                if (!empty($review['datePublished']))
                    $comment['date'] = strtotime($review['datePublished']);
                if (!empty($review['reviewRating']['ratingValue']))
                {
                    if (!empty($review['reviewRating']['bestRating']))
                        $bestRating = (int) $review['reviewRating']['bestRating'];
                    else
                        $bestRating = 5;

                    $comment['rating'] = TextHelper::ratingPrepare($review['reviewRating']['ratingValue'] / ($bestRating / 5));
                }
                $extra['comments'][] = $comment;
            }
        }

        if (isset($this->ld_json['aggregateRating']['ratingValue']))
        {
            if (!empty($this->ld_json['aggregateRating']['bestRating']))
                $bestRating = (int) $this->ld_json['aggregateRating']['bestRating'];
            else
                $bestRating = 5;
            if (!$bestRating)
                $bestRating = 5;
            $extra['rating'] = TextHelper::ratingPrepare($this->ld_json['aggregateRating']['ratingValue'] / ($bestRating / 5));
        }
        return $extra;
    }

    public function isInStock()
    {
        if (isset($this->ld_json['offers'][0]['availability']))
            $availability = $this->ld_json['offers'][0]['availability'];
        elseif (isset($this->ld_json['offers']['availability']))
            $availability = $this->ld_json['offers']['availability'];
        elseif (isset($this->ld_json['offers']['offers'][0]['availability']))
            $availability = $this->ld_json['offers']['offers'][0]['availability'];
        else
            $availability = '';

        if ($availability && in_array($availability, array('OutOfStock', 'SoldOut', 'http://schema.org/OutOfStock', 'https://schema.org/OutOfStock', 'http://schema.org/SoldOut', 'http://schema.org/Discontinued')))
            return false;
        else
            return true;
    }

    public function getCurrency()
    {
        if (isset($this->ld_json['offers'][0]['priceCurrency']))
            return $this->ld_json['offers'][0]['priceCurrency'];
        elseif (isset($this->ld_json['offers']['priceCurrency']))
            return $this->ld_json['offers']['priceCurrency'];
        elseif (isset($this->ld_json['offers'][0]['priceSpecification']['priceCurrency']))
            return $this->ld_json['offers'][0]['priceSpecification']['priceCurrency'];
        else
            return parent::getCurrency();
    }

}
