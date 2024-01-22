<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * PetshopruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class PetshopruParser extends ShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//ul[@class='product-list']/li/a/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://www.petshop.ru' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return trim($this->xpathScalar(".//h1[@itemprop='name']"), ", ");
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//*[@id='product-detail-text' or @id='product-features']");
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//*[@itemprop='price']/@value");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@class='product-price-old']/@value");
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//img[@class='brand-logo']/@title");
    }

    public function parseImg()
    {
        $img = $this->xpathScalar(".//*[@class='card-preview']//a[@data-zoom]/img/@src");
        if (!preg_match('/^https?:/', $img))
            $img = 'https:' . $img;
        return $img;
    }

    public function parseImgLarge()
    {
        $img = $this->xpathScalar(".//*[@class='card-preview']//a[@data-zoom]/@href");
        if (!preg_match('/^https?:/', $img))
            $img = 'https:' . $img;
        return $img;
    }

    public function parseExtra()
    {
        $extra = array();
        $extra['rating'] = TextHelper::ratingPrepare($this->xpathScalar(".//*[@data-rating]/@data-rating"));
        return $extra;
    }

    public function isInStock()
    {
        return true;
    }

}
