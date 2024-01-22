<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * CromacomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class CromacomParser extends LdShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'INR';
    protected $_prices = array();

    public function restPostGet($url, $fix_encoding = true)
    {
        //fix incorrect json
        $html = parent::restPostGet($url, $fix_encoding = true);
        $html = str_replace('"availability": inStock', '"availability": "inStock"', $html);
        $html = preg_replace('/"description": ".+?",/ims', '', $html);
        return $html;
    }

    public function parseCatalog($max)
    {
        $path = array(
            ".//div[@class='product-info']//a/@href",
            ".//a[@class='product__list--name']/@href",
            ".//a[@class='productMainLink']/@href",
            ".//a[@class='productMainLink']/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePrice()
    {
        $url = strtok($this->getUrl(), '?');
        if (!preg_match('~/p/(\d+)~', $url, $matches))
            return;

        $url = 'https://api.croma.com/products/mobile-app/v1/' . urlencode($matches[1]) . '/price';
        try
        {
            $result = $this->requestGet($url, false);
        } catch (\Exception $e)
        {
            return;
        }

        $result = str_replace('<?xml encoding="UTF-8">', '', $result);
        if (!$result = json_decode($result, true))
            return;

        $this->_prices = $result;

        if (isset($this->_prices['sellingPrice']['value']))
            return $this->_prices['sellingPrice']['value'];
        elseif (isset($this->_prices['mrp']['value']))
            return $this->_prices['mrp']['value'];
    }

    public function parseOldPrice()
    {
        if (isset($this->_prices['mrp']['value']))
            return $this->_prices['mrp']['value'];
    }

    public function parseImg()
    {
        $paths = array(
            ".//img[@id='0prod_img']/@data-src",
        );

        return $this->xpathScalar($paths);
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();
        $extra['features'] = array();

        $names = $this->xpathArray(".//div[@class='cp-specification']//li/h4");
        $values = $this->xpathArray(".//div[@class='cp-specification']//li[@class='cp-specification-spec-details']");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (empty($values[$i]))
                continue;

            $feature['name'] = \sanitize_text_field($names[$i]);
            $feature['value'] = \sanitize_text_field($values[$i]);
            $extra['features'][] = $feature;
        }
        return $extra;
    }

    public function isInStock()
    {
        if ($this->xpathScalar(".//span[@id='outofstockmsg']") == 'This product is currently Out of Stock.')
            return false;
        else
            return true;
    }

}
