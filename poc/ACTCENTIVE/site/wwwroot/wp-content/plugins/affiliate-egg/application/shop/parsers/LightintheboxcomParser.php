<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * LightintheboxcomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class LightintheboxcomParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'USD';
    protected $_ld;

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//dd[@class='prod-name' or @class='prodName']/a/@href"), 0, $max);
        if (!$urls)
            $urls = array_slice($this->xpathArray(".//a[contains(@class, 'widget') and contains(@class, 'trigger-hover')]/@href"), 0, $max);
        return $urls;
    }

    public function parseTitle()
    {
        $this->_parseLd();
        if (isset($this->_ld['name']))
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
        
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//*[@itemprop='price']/@content");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@class='widget prod-info-price']//*[@class='del-price']");
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//*[@itemprop='brand']/@content");
    }

    public function parseImg()
    {
        return $this->xpathScalar(".//meta[@property='og:image']/@content");
    }

    public function parseImgLarge()
    {
        
    }

    public function parseExtra()
    {
        $extra = array();
        if (isset($this->_ld['aggregateRating']))
            $extra['rating'] = TextHelper::ratingPrepare($this->_ld['aggregateRating']['ratingValue']);

        return $extra;
    }

    public function isInStock()
    {

        $availability = $this->xpathScalar(".//meta[@itemprop='availability']/@content");
        if ($availability == 'http://schema.org/OutOfStock')
            return false;
        else
            return true;
    }

    public function getCurrency()
    {
        $currency = $this->xpathScalar(".//*[@itemprop='priceCurrency']/@content");
        if (!$currency)
            $currency = 'USD';
        return $currency;
    }

}
