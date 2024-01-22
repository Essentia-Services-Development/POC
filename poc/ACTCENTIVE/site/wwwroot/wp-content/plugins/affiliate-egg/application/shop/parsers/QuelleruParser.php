<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * QuelleruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class QuelleruParser extends ShopParser {

    protected $charset = 'ISO-8859-5';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//a[@class='ddl_product_link']/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^http/', $url))
                $urls[$i] = 'https:' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//h1");
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//*[@itemprop='description']");
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//*[@itemprop='price']/@content");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@class='product-streichpreis']/text()");
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//*[@itemprop='brand']");
    }

    public function parseImg()
    {
        $img = trim($this->xpathScalar(".//img[@itemprop='image']/@src"));
        if ($img && !preg_match('/^https?:/', $img))
            $img = 'https:' . $img;
        return $img;
    }

    public function parseImgLarge()
    {
        
    }

    public function parseExtra()
    {
        $extra = array();
        return $extra;
    }

    public function isInStock()
    {
        return true;
    }

}
