<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * KameraexpressnlParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class KameraexpressnlParser extends LdShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'EUR';
    protected $_json;
    protected $_html;    

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//ul[@class='productListing']//a[@class='productLink']/@href"), 0, $max);
        return $urls;
    }

    public function parseTitle()
    {
        $this->_html = $this->dom->saveHTML();
        
        return trim(str_replace('Kamera Express -', '', $this->xpathScalar(".//title")));
    }

    public function parsePrice()
    {
        if (preg_match('/"fixed_list_price":(.+?),"brand_name"/', $this->_html, $matches))
            return $matches[1];
    }

    public function parseOldPrice()
    {
        if (preg_match('/"from_price":(.+?),/', $this->dom->saveHTML(), $matches))
            return $matches[1];
    }

    public function parseImg()
    {
        if (preg_match("/\"media_main\":\"<img id='product-image' class='product-image' src='(.+?)'/", $this->_html, $matches))
            return  $matches[1];
    }

}
