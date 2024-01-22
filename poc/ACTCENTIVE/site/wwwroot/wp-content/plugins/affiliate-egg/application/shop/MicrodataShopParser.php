<?php

namespace Keywordrush\AffiliateEgg;

use Keywordrush\AffiliateEgg\TextHelper;

/**
 * MicrodataShopParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2018 keywordrush.com
 */
abstract class MicrodataShopParser extends ShopParser {

    public function parseTitle()
    {
        $paths = array(
            ".//h1[@itemprop='name']/text()",
            ".//h1[@itemprop='name']",
            ".//h2[@itemprop='name']",
            ".//h1/*[@itemprop='name']",
            ".//h1//*[@itemprop='name']",
            ".//h3[@itemprop='name']",
            ".//meta[@property='og:title']/@content",
        );
        return $this->xpathScalarMulty($paths);
    }

    public function parseDescription()
    {
        $paths = array(
            ".//*[@itemtype='http://schema.org/Product']//*[@itemprop='description']/@content",
            ".//*[@itemtype='http://schema.org/Product']//*[@itemprop='description']",
            ".//*[@itemprop='description']/@content",
            ".//*[@itemprop='description']",
            ".//meta[@property='og:description']/@content",
        );
        return $this->xpathScalarMulty($paths, true);
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
            ".//meta[@property='og:price:amount']/@content",
            ".//meta[@property='og:price:standard_amount']/@content",
            ".//meta[@property='product:price:amount']/@content",
        );
        return $this->xpathScalarMulty($paths);
    }

    public function parseManufacturer()
    {
        $paths = array(
            ".//*[@itemprop='brand']/*[@itemprop='name']/@content",
            ".//*[@itemprop='brand']//*[@itemprop='name']",
            ".//*[@itemprop='brand']",
        );
        return $this->xpathScalarMulty($paths);
    }

    public function parseImg()
    {
        $paths = array(
            ".//img[@itemprop='image']/@src",
            ".//meta[@itemprop='image']/@content",
            ".//meta[@property='og:image']/@content",
            ".//*[@property='og:image']/@content",
            ".//img[@itemprop='image']/@data-src",
            ".//meta[@property='og:image:secure_url']/@content",
        );
        return $this->xpathScalarMulty($paths);
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();

        if ($res = $this->_parseComments())
            $extra['comments'] = $res;

        if ($res = $this->_parseRating())
            $extra['rating'] = $res;

        return $extra;
    }

    protected function _parseRating()
    {
        $paths = array(
            ".//*[@itemprop='aggregateRating']//*[@itemprop='ratingValue']/@content",
            ".//*[@itemprop='ratingValue']/@content",
            ".//*[@itemprop='aggregateRating']//*[@itemprop='ratingValue']",
            ".//*[@itemprop='ratingValue']",
            ".//meta[@property='og:rating']/@content"
        );
        $rating = $this->xpathScalarMulty($paths);
        if (!$rating)
            return null;

        $paths = array(
            ".//*[@itemprop='aggregateRating']//*[@itemprop='bestRating']/@content",
            ".//*[@itemprop='bestRating']/@content",
            ".//*[@itemprop='aggregateRating']//*[@itemprop='bestRating']",
            ".//*[@itemprop='bestRating']",
        );
        if (!$best_rating = (int) $this->xpathScalarMulty($paths))
            $best_rating = 5;

        $rating = (float) str_replace(',', '.', $rating);

        return TextHelper::ratingPrepare($rating / ($best_rating / 5));
    }

    protected function _parseComments()
    {
        $paths = array(
            ".//*[@itemprop='review']//*[@itemprop='author']//*[@itemprop='name']",
            ".//*[@itemprop='review']//*[@itemprop='author']",
        );
        $users = $this->xpathArrayMulty($paths);
        $paths = array(
            ".//*[@itemprop='review']//*[@itemprop='reviewBody']",
            ".//*[@itemprop='review']//*[@itemprop='description']",
            ".//*[@itemprop='reviewBody']",
        );
        $comments = $this->xpathArrayMulty($paths);

        $paths = array(
            ".//*[@itemprop='review']//*[@itemprop='datePublished']/@content",
            ".//*[@itemprop='review']//*[@itemprop='datePublished']",
        );
        $dates = $this->xpathArrayMulty($paths);

        $paths = array(
            ".//*[@itemprop='review']//*[@itemprop='ratingValue']/@content",
        );
        $ratings = $this->xpathArrayMulty($paths);

        $results = array();
        for ($i = 0; $i < count($comments); $i++)
        {
            $result = array();
            $result['comment'] = \sanitize_text_field($comments[$i]);
            if (!empty($users[$i]))
                $result['name'] = \sanitize_text_field($users[$i]);
            if (!empty($ratings[$i]))
                $result['rating'] = TextHelper::ratingPrepare($ratings[$i]);
            if (!empty($dates[$i]))
                $result['date'] = strtotime($dates[$i]);
            $results[] = $result;
        }
        return $results;
    }

    public function isInStock()
    {
        $paths = array(
            ".//*[@itemprop='offers']//*[@itemprop='availability']/@href",
            ".//*[@itemtype='http://schema.org/Offer']//*[@itemprop='availability']/@href",
            ".//*[@itemtype='http://schema.org/Offer']//*[@itemprop='availability']/@content",
            ".//*[@itemprop='availability']/@href",
            ".//*[@itemprop='availability']/@content",
            ".//*[@itemprop='availability']",
        );
        $res = $this->xpathScalarMulty($paths);
        if ($res && stristr($res, 'OutOfStock'))
            return false;
        else
            return true;
    }

    public function parseCurrency()
    {
        $paths = array(
            ".//*[@itemprop='offers']//*[@itemprop='priceCurrency']/@content",
            ".//*[@itemtype='http://schema.org/Offer']//*[@itemprop='priceCurrency']/@content",
            ".//*[@itemprop='priceCurrency']/@content",
            ".//meta[@property='product:price:currency']/@content",
        );

        $res = $this->xpathScalarMulty($paths);
        $res = strtoupper($res);
        preg_replace('/[^A-Z]/', '', $res);
        if (strlen($res) == 3)
            return $res;
        else
            return $this->currency;
    }

}
