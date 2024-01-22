<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * GetwearcomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class GetwearcomParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'USD';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//div[@class='gtwr-thumbnail js-thumbnail']/a/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://getwear.com' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return trim(str_replace(",", "", $this->xpathScalar(".//h1[@class='product-page__title']/text()")));
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//section[@class='product-page__sticky__section']/p");
    }

    public function parsePrice()
    {
        return (float) preg_replace('/[^0-9,]/', '', $this->xpathScalar(".//h1[@class='product-page__title']/nobr"));
    }

    public function parseOldPrice()
    {
        return '';
    }

    public function parseManufacturer()
    {
        return 'Getwear';
    }

    public function parseImg()
    {
        return $this->xpathScalar(".//div[@class='product-page__photo-wrap']/img/@src");
    }

    public function parseImgLarge()
    {
        return '';
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();

        $extra['images'] = array();

        $results = $this->xpathArray(".//figure[@class='product-page__photo']/img/@src");
        foreach ($results as $i => $res)
        {
            if ($res)
            {
                $extra['images'][] = $res;
            }
        }
        return $extra;
    }

    public function isInStock()
    {
        return true;
    }

}
