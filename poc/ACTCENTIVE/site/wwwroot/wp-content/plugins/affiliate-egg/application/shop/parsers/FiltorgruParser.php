<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * FiltorgruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class FiltorgruParser extends ShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//div[@class='product-info']/a/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://www.filtorg.ru' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//div[@class='product-info']/h1");
    }

    public function parseDescription()
    {
        $res = $this->xpathArray(".//div[@id='content_block_description']/node()");
        return sanitize_text_field(implode(' ', $res));
    }

    public function parsePrice()
    {
        return (float) preg_replace('/[^0-9\.]/', '', $this->xpathScalar(".//span[@class='price']"));
    }

    public function parseOldPrice()
    {
        return (float) preg_replace('/[^0-9\.]/', '', $this->xpathScalar(".//strike/span[contains(@class,'list-price')]"));
    }

    public function parseManufacturer()
    {
        return '';
    }

    public function parseImg()
    {
        $ret = $this->xpathScalar(".//div[contains(@id,'product_images')]//a/@href");
        if ($ret)
        {
            if (!preg_match('/^http:\/\//', $ret))
                $ret = 'http://www.filtorg.ru' . $ret;
        }
        return $ret;
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
        $results = $this->xpathArray(".//div[contains(@id,'product_images')]/a/@href");
        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            if ($res)
            {
                if (!preg_match('/^http:\/\//', $res))
                    $res = 'http://www.filtorg.ru' . $res;
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
