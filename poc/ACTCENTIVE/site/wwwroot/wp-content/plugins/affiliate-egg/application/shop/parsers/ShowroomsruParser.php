<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * ShowroomsruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class ShowroomsruParser extends ShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//ul[contains(@class,'product-list')]/li//div[@class='content']/a/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^http:/', $url))
                $urls[$i] = 'http:' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        $title = '';
        $brand = '';
        $results = $this->xpathScalar(".//script[contains(.,'var product_obj')]");
        preg_match("/name:\s'(.+?)'/msi", $results, $match);
        if (isset($match[1]))
        {
            $title = $match[1];
            preg_match("/brand:\s'(.+?)'/msi", $results, $match);
            if (isset($match[1]))
                $title = str_ireplace($match[1], '', $title);
            $title = str_replace('  ', ' ', $title);
        }
        return $title;
    }

    public function parseDescription()
    {
        $res = $this->xpathScalar(".//div[@class='description']//div[@class='content']");
        if (!$res)
        {
            $results = $this->xpathScalar(".//script[contains(.,'#product-description')]");
            preg_match("/html\('(.+?)'/msi", $results, $match);
            if (isset($match[1]))
                $res = $match[1];
        }
        return $res;
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//*[@itemprop='price']/@content");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//del[@class='old-sum']");
    }

    public function parseManufacturer()
    {
        $ret = '';
        $results = $this->xpathScalar(".//script[contains(.,'var product_obj')]");
        preg_match("/brand:\s'(.+?)'/msi", $results, $match);
        if ($match[1])
            $ret = $match[1];
        return $ret;
    }

    public function parseImg()
    {
        return $this->xpathScalar(".//div[@class='gallery1']//li[@class='active']//a/img/@src");
    }

    public function parseImgLarge()
    {
        return $this->xpathScalar(".//div[@class='gallery1']//li[@class='active']/div/a/@href");
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();

        $names = $this->xpathArray(".//dl[@class='product-info-list']/dt");
        $values = $this->xpathArray(".//dl[@class='product-info-list']/dd");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]) && $names[$i] != 'Бренд')
            {
                $feature['name'] = sanitize_text_field($names[$i]);
                $feature['value'] = sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }
        $extra['images'] = array();
        $results = $this->xpathArray(".//div[@class='gallery1']//li/div/a/@href");
        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            if ($res)
            {
                if (!preg_match('/^https?:\/\//', $res))
                    $res = 'https://www.showrooms.ru' . $res;
                $extra['images'][] = $res;
            }
        }
        return $extra;
    }

    public function isInStock()
    {
        $res = $this->xpath->evaluate("boolean(.//p[@class='no-product-notification'])");
        if ($res)
            return false;
        return true;
    }

}
