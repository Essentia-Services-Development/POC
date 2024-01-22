<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * AsoscomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class AsoscomParser extends LdShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'GBP';
    protected $user_agent = array('ia_archiver');
    protected $_product = array();
    protected $headers = array(
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language' => 'en-us,en;q=0.5',
        'Cache-Control' => 'no-cache',
        'Connection' => 'keep-alive',
    );

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//article[contains(@id, 'product-')]//a/@href");
    }

    protected function preParseProduct()
    {
        if (!$this->_parseProduct())
            return false;

        return parent::preParseProduct();
    }

    private function _parseProduct()
    {
        if (!preg_match('~\/prd\/(\d+)~', $this->getUrl(), $matches))
            return;

        $request_url = 'https://www.asos.com/api/product/catalogue/v3/stockprice?productIds='.$matches[1].'&store=COM&currency=GBP&keyStoreDataversion=hgk0y12-29';

        if (!$response = $this->getRemoteJson($request_url))
            return;

        if (!isset($response[0]))
            return;

        $this->_product = $response[0];
        return $this->_product;
    }    
    
    public function parsePrice()
    {
        if (isset($this->_product['productPrice']['current']['value']))
            return $this->_product['productPrice']['current']['value'];
    }    
    
    public function parseOldPrice()
    {
        if (isset($this->_product['productPrice']['previous']['value']))
            return $this->_product['productPrice']['previous']['value'];
    }

}
