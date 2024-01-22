<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * E5ruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class E5ruParser extends ShopParser {

    protected $charset = 'UTF-8';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//ul[contains(@class,'list products')]/li/a[1]/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://www.e5.ru' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//div[@class='content']//h1[@itemprop='name']");
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//div[@class='shortenDiv']/div");
    }

    public function parsePrice()
    {
        return preg_replace('/[^0-9]/', '', $this->xpathScalar(".//div[@class='goods_info']//span[@itemprop='price']"));
    }

    public function parseOldPrice()
    {
        return '';
    }

    public function parseManufacturer()
    {
        return trim($this->xpathScalar(".//td[@class='first']/span[normalize-space(text())='Производитель']/../parent::tr/td[2]"));
    }

    public function parseImg()
    {
        $img = $this->xpathScalar(".//div[@id='gallery']//img[@itemprop='image']/@src");
        return $img;
    }

    public function parseImgLarge()
    {
        $img = '';
        if ($this->item['orig_img'])
            $img = str_replace('/big/', '/full/', $this->item['orig_img']);
        return $img;
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();

        $names = $this->xpathArray(".//tr[not(@*)]/td[@class='first']/span[normalize-space(text())]");
        $values = $this->xpathArray(".//tr[not(@*)]/td[2][normalize-space(text())]");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]) && $names[$i] != 'Производитель')
            {
                $feature['name'] = sanitize_text_field(str_replace(":", "", $names[$i]));
                $feature['value'] = sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }

        $extra['images'] = array();
        //$results = $this->xpathArray(".//ul[@class='ad-thumb-list']/li/a[@class='' or not(@*)]/@href");
        $results = $this->xpathArray(".//ul[@class='ad-thumb-list']/li/a[@class='' or not(@class)]/@href");
        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            $extra['images'][] = $res;
        }
        return $extra;
    }

    public function isInStock()
    {
        $res = $this->xpath->evaluate("boolean(.//div[@class='stock-in-trade']/noindex[contains(.,'Нет в наличии')])");
        return ($res) ? false : true;
    }

}
