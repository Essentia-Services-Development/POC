<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * YooxcomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class YooxcomParser extends ShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//a[@class='itemlink']/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://www.yoox.com' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return trim($this->xpathScalar(".//div[@id='itemTitle']/h1"));
    }

    public function parseDescription()
    {
        $descr = trim($this->xpathScalar(".//div[@id='tabs-1']/ul/li[@id='ItemDescription']"));
        return trim(str_replace("Детали:", "", $descr));
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//*[@itemprop='price' or @itemprop='lowPrice']");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@class='text-secondary text-linethrough']");
    }

    public function parseManufacturer()
    {
        return trim($this->xpathScalar(".//div[@id='itemTitle']/h2"));
    }

    public function parseImg()
    {
        return $this->xpathScalar(".//div[@id='itemImage']//div/img/@src");
    }

    public function parseImgLarge()
    {
        $img = '';
        if ($this->item['orig_img'])
            $img = str_replace('12_f.jpg', '14_f.jpg', $this->item['orig_img']);
        return $img;
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();

        $names = $this->xpathArray(".//ul[contains(@class, 'item-info-content')]/li/span[1]");
        $values = $this->xpathArray(".//ul[contains(@class, 'item-info-content')]/li/span[2]");
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


        $extra['images'] = array();
        $results = $this->xpathArray(".//ul[@id='itemThumbs']/li/img/@src");
        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            if ($res)
            {
                $extra['images'][] = preg_replace('/9_(\w)\.jpg/', "12_$1.jpg", $res);
            }
        }
        return $extra;
    }

    public function isInStock()
    {
        return true;
    }

    public function getCurrency()
    {
        return $this->xpathScalar(".//*[@itemprop='priceCurrency']/@content");
    }

}
