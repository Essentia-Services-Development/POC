<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * People4peopleParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class People4peopleruParser extends ShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//ul[@class='product-list']/li/span[@class='product-list__item-title']/a/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://people4people.ru' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//div[@class='product-details__content']/h1");
    }

    public function parseDescription()
    {
        return "";
    }

    public function parsePrice()
    {
        $price = (float) preg_replace('/[^0-9\.]/', '', $this->xpathScalar(".//span[@class='product-details__price-item']"));
        if (!$price)
            $price = (float) preg_replace('/[^0-9\.]/', '', $this->xpathScalar(".//span[@class='product-details__price-item product-details__price-item_new']"));
        return $price;
    }

    public function parseOldPrice()
    {
        return (float) preg_replace('/[^0-9\.]/', '', $this->xpathScalar(".//span[@class='product-details__price-item product-details__price-item_old']"));
    }

    public function parseManufacturer()
    {
        return "People";
    }

    public function parseImg()
    {
        $img = $this->xpathScalar(".//div[@class='product-gallery__img']/a/img/@src");
        if ($img && !preg_match('/^http:/', $img))
            $img = 'http:' . $img;
        return $img;
    }

    public function parseImgLarge()
    {
        return '';
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();

        $results = $this->xpathArray(".//div[@id='product-desc']/span[@class='product-details__param']");
        $feature = array();
        foreach ($results as $res)
        {
            $splited = explode(":", $res, 2);
            if (count($splited) == 2)
            {
                $feature['name'] = sanitize_text_field($splited[0]);
                $feature['value'] = sanitize_text_field($splited[1]);
                $extra['features'][] = $feature;
            }
        }

        $extra['images'] = array();

        $results = $this->xpathArray(".//ul[@class='product-gallery__nav-list']/li/img/@data-zoom");
        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            if ($res)
            {
                $extra['images'][] = 'http://people4people.ru' . $res;
            }
        }
        return $extra;
    }

    public function isInStock()
    {
        $res = $this->xpath->evaluate("boolean(.//span[contains(., 'нет в наличии в интернет-магазине')])");
        return ($res) ? false : true;
    }

}
