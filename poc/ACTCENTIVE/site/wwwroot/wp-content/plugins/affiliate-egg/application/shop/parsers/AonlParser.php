<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

use Keywordrush\AffiliateEgg\TextHelper;

/**
 * AonlParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class AonlParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'EUR';
    protected $_images = array();

    public function parseCatalog($max)
    {
        // redirect from search to product page
        if ($this->parseTitle())
        {
            $turl = $this->xpathScalar(".//*[@class='shareicon shareTwitter']/a/@href");
            if ($turl)
            {
                return array($this->_parseProductUrl($turl));
            }
        }

        $urls = array_slice($this->xpathArray(".//a[@id='hypTitle']/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://www.ao.nl' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//*[@id='productInformation']/h1[@itemprop='name']");
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//*[@class='richContent']");
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//*[@itemtype='http://schema.org/Offer']/*[@itemprop='price']/@content");
    }

    public function parseOldPrice()
    {
        
    }

    public function parseManufacturer()
    {
        
    }

    public function parseImg()
    {
        $json = $this->xpathScalar(".//script[@id='mediaData']");
        if (!$json || !$list = json_decode($json, true))
            return '';
        if (!isset($list['images']))
            return '';
        $this->_images = $list['images'];
        return 'https:' . $this->_images[0]['thumb'];
    }

    public function parseImgLarge()
    {
        if ($this->_images)
            return $this->_images[0]['large'];
    }

    public function parseExtra()
    {
        $extra = array();

        if ($this->_images)
        {
            $extra['images'] = array();
            foreach ($this->_images as $key => $img)
            {
                if ($key > 0)
                    $extra['images'][] = $img['thumb'];
            }
        }

        $names = $this->xpathArray(".//*[@class='accordionContentInner']//td[2]");
        $values = $this->xpathArray(".//*[@class='accordionContentInner']//td[3]");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]))
            {
                $feature['name'] = sanitize_text_field($names[$i]);
                $feature['value'] = sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }

        if ($rating = $this->xpathScalar(".//*[@itemprop='ratingValue']"))
        {
            $extra['rating'] = TextHelper::ratingPrepare($rating);
        }

        $extra['comments'] = array();
        $users = $this->xpathArray(".//*[@itemtype='http://schema.org/Review']//*[@itemprop='author']//*[@itemprop='name']");
        $comments = $this->xpathArray(".//*[@itemtype='http://schema.org/Review']//*[@itemprop='reviewBody']");
        $ratings = $this->xpathArray(".//*[@itemtype='http://schema.org/Review']//*[@class='pointRating']");

        for ($i = 0; $i < count($comments); $i++)
        {
            $comment['comment'] = sanitize_text_field($comments[$i]);
            if (!empty($users[$i]))
                $comment['name'] = sanitize_text_field($users[$i]);
            if (!empty($ratings[$i]))
            {
                $rating_parts = explode('/', $rating);
                if (count($rating_parts) == 2)
                    $comment['rating'] = TextHelper::ratingPrepare((int) $rating_parts[0]);
            }
            $extra['comments'][] = $comment;
        }


        return $extra;
    }

    public function isInStock()
    {
        // Helaas is onze webshop niet meer actief.
        return false;

        if ($this->parsePrice())
            return true;
        else
            return false;
    }

    protected function _parseProductUrl($url)
    {
        $query = parse_url($url, PHP_URL_QUERY);
        parse_str($query, $params);
        if (!empty($params['url']))
            return $params['url'];
    }

}
