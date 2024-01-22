<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * MotiviruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class MotiviruParser extends ShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        return array_slice($this->xpathArray(".//ul[@id='products']//a[@data-zg-role='product-link']/@href"), 0, $max);
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//div[@id='maincontent']/h1");
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//div[@id='product-description']/div/text()[last()]");
    }

    public function parsePrice()
    {
        return (float) preg_replace('/[^0-9\.]/', '', $this->xpathScalar(".//div[@class='product-price']/p"));
    }

    public function parseOldPrice()
    {
        return '';
    }

    public function parseManufacturer()
    {
        return "Motivi";
    }

    public function parseImg()
    {
        $img = $this->xpathScalar(".//ul[@id='gallery']/li/a/img/@src");
        if ($img)
            $img = str_replace('small.jpg', 'medium.jpg', $img);
        return $img;
    }

    public function parseImgLarge()
    {
        $img = $this->xpathScalar(".//ul[@id='gallery']/li/a/img/@src");
        if ($img)
            $img = str_replace('small.jpg', 'big.jpg', $img);
        return $img;
    }

    public function parseExtra()
    {
        $extra = array();
        $extra['features'] = array();

        $extra['images'] = array();

        $results = $this->xpathArray(".//ul[@id='gallery']/li/a/img/@src");
        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            if ($res)
            {
                $extra['images'][] = str_replace('small.jpg', 'big.jpg', $res);
            }
        }
        return $extra;
    }

    public function isInStock()
    {
        return true; //JS
    }

}
