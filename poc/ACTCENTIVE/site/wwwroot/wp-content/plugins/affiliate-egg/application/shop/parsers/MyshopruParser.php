<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * MyshopruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class MyshopruParser extends MicrodataShopParser {

    protected $_product;

    public function parseCatalog($max)
    {
        return array();
    }

    public function parseTitle()
    {
        if (!$this->_parseProduct())
            return false;

        if (isset($this->_product['title']))
            return html_entity_decode($this->_product['title']);
    }

    public function parseDescription()
    {
        if (isset($this->_product['description_short']))
            return html_entity_decode($this->_product['description_short']);
    }

    public function parseImg()
    {
        if (!empty($this->_product['images'][0]['original']['href']))
            return $this->_product['images'][0]['original']['href'];
    }

    public function parsePrice()
    {
        if (!empty($this->_product['cost']))
            return $this->_product['cost'];
        elseif (!empty($this->_product['old_cost']))
            return $this->_product['old_cost'];
    }

    public function parseOldPrice()
    {
        if (!empty($this->_product['old_cost']))
            return $this->_product['old_cost'];
    }

    public function _parseProduct()
    {

        $url = strtok($this->getUrl(), '?');
        if (!preg_match('~\/(\d+)\.html$~', $url, $matches))
            return false;

        $id = $matches[1];
        try
        {
            $result = $this->requestGet('https://my-shop.ru/cgi-bin/shop2.pl?q=product&id=' . urlencode($id) . '&view_id=2a244fcc-c96d-4adc-855b-f99edefab434', false);
        } catch (\Exception $e)
        {
            return array();
        }
        $result = json_decode($result, true);

        if (!$result || !isset($result['product']))
            return false;

        $this->_product = $result['product'];
        return $this->_product;
    }

    public function isInStock()
    {
        if (isset($this->_product['availability']['status']) && !$this->_product['availability']['status'])
            return false;
        else
            return true;
    }

}
