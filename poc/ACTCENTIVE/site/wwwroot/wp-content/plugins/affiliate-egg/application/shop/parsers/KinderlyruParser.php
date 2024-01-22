<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * KinderlyruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class KinderlyruParser extends ShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//ul[contains(@class, 'product__list')]//a[@itemprop='url']/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://www.kinderly.ru' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        $mnf = $this->parseManufacturer();
        $title = $this->xpathScalar(".//meta[@itemprop='name']/@content");
        $title = str_replace($mnf, '', $title);
        $title = preg_replace('/\s+/', ' ', $title);
        $title = trim($title, ', ');

        return $title;
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//div[@class='product-text']");
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//*[@itemprop='offers']//meta[@itemprop='price']/@content");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@class='product-full-actions__price-old']");
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//meta[@itemprop='brand']/@content");
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

        $extra['features'] = array();
        $names = $this->xpathArray(".//*[@class='product-full__characteristics']//*[@class='product-full__characteristics-list-item-left']");
        $values = $this->xpathArray(".//*[@class='product-full__characteristics']//*[@class='product-full__characteristics-list-item-right']");
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

        return $extra;
    }

    public function isInStock()
    {
        $availability = $this->xpathScalar(".//link[@itemprop='availability']/@href");
        if ($availability == 'http://schema.org/InStock')
            return true;
        else
            return false;
    }

}
