<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * ProskaterruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class ProskaterruParser extends ShopParser {

    protected $charset = 'windows-1251';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//ul[contains(@class, 'productn-list')]//div[@class='quick-see']/a/@href"), 0, $max);
        foreach ($urls as $key => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$key] = "http://www.proskater.ru" . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//div[contains(@class,'product-item')]//h1[@itemprop='name']");
    }

    public function parseDescription()
    {
        $descr = $this->xpathScalar(".//div[@class='product-info']//p[@class='description']");
        $results = $this->xpathArray(".//div[@class='product-info']//div[@class='column']/div/li/text()");
        $des = implode(' ', $results);
        $des = $descr . " " . $des;
        return $des;
    }

    public function parsePrice()
    {
        return preg_replace('/[^0-9]/', '', $this->xpathScalar(".//div[@class='price-block']//span[@itemprop='price']"));
    }

    public function parseOldPrice()
    {
        return preg_replace('/[^0-9]/', '', $this->xpathScalar(".//div[@class='price-block']//span[@class='old-price']"));
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//div[@class='brand-item']/a/img/@alt");
    }

    public function parseImg()
    {
        return $this->xpathScalar(".//meta[@property='og:image']/@content");
    }

    public function parseImgLarge()
    {
        $img = $this->xpathScalar(".//ul[@class='gallery']/li/a/@href");
        return $img;
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();

        $names = $this->xpathArray(".//div[@class='product-info']//div[@class='column']/p/strong");
        $values = $this->xpathArray(".//div[@class='product-info']//div[@class='column']/p");
        $feature = array();

        for ($i = 0; $i < count($names); $i++)
        {
            $feature['name'] = sanitize_text_field(str_replace(":", "", $names[$i]));
            $val = str_replace($names[$i], '', $values[$i]);
            $feature['value'] = trim(sanitize_text_field(strip_tags($val)));
            $extra['features'][] = $feature;
        }

        $extra['images'] = array();
        $results = $this->xpathArray(".//ul[@class='gallery']/li/a/img/@src");
        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            if ($res)
            {
                $extra['images'][] = $res;
            }
        }
        return $extra;
    }

    public function isInStock()
    {
        $res = $this->xpath->evaluate("boolean(.//div[@class='is' and text()='нет в наличии'])");
        return ($res) ? false : true;
    }

}
