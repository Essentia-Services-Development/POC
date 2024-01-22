<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * MercadolivrecombrParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class MercadolivrecombrParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'BRL';
    protected $_ld;

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//h2[contains(@class, 'item__title')]/a/@href"), 0, $max);
        if (!$urls)
            $urls = array_slice($this->xpathArray("id('searchResults')//a/@href"), 0, $max);
        return $urls;
    }

    public function parseTitle()
    {
        $this->_parseLd();
        if (!$this->_ld)
            return false;

        return $this->_ld['name'];
    }

    public function _parseLd()
    {
        $lds = $this->xpathArray(".//script[@type='application/ld+json']", true);
        foreach ($lds as $ld)
        {
            if (!$data = json_decode($ld, true))
                continue;

            if (isset($data['@type']) && $data['@type'] == 'Product')
            {
                $this->_ld = $data;
                break;
            }
        }
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//*[@class='item-description__text']");
    }

    public function parsePrice()
    {
        if (isset($this->_ld['offers']['price']))
            return $this->_ld['offers']['price'];
        elseif (isset($this->_ld['offers'][0]['price']))
            return $this->_ld['offers'][0]['price'];
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//del/span[@class='price-tag-symbol']/@content");
    }

    public function parseManufacturer()
    {
        if (isset($this->_ld['brand']['name']))
            return $this->_ld['brand']['name'];
    }

    public function parseImg()
    {
        return $this->_ld['image'];
    }

    public function parseImgLarge()
    {
        
    }

    public function parseExtra()
    {
        $extra = array();
        if (isset($this->_ld['aggregateRating']))
            $extra['rating'] = TextHelper::ratingPrepare($this->_ld['aggregateRating']['ratingValue']);

        if (!empty($this->_ld['reviews']))
        {
            foreach ($this->_ld['reviews'] as $review)
            {
                $comment = array();
                if (empty($review['reviewBody']))
                    continue;
                $comment['comment'] = sanitize_text_field($review['reviewBody']);
                if (!empty($review['author']))
                    $comment['name'] = sanitize_text_field($review['author']['name']);
                if (!empty($review['datePublished']))
                    $comment['date'] = strtotime($review['datePublished']);
                if (!empty($review['reviewRating']))
                    $comment['rating'] = TextHelper::ratingPrepare($review['reviewRating']['ratingValue']);
                $extra['comments'][] = $comment;
            }
        }

        return $extra;
    }

    public function isInStock()
    {
        if (!empty($this->_ld['offers']['availability']) && $this->_ld['offers']['availability'] == 'http://schema.org/OutOfStock')
            return false;
        else
            return true;
    }

}
