<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/*
  Name: Adayroi.com
  URI: https://www.adayroi.com
  Icon: http://www.google.com/s2/favicons?domain=adayroi.com
  CPA:
  SEARCH URI: https://www.adayroi.com/tim-kiem?text=%KEY+WORD%
 * 
 */

/**
 * Adayroicom class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2018 keywordrush.com
 */
class AdayroicomParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'VND';

    public function parseCatalog($max)
    {
        if ($urls = $this->_getCategoryUrls())
            return $urls;
        if ($urls = $this->_getSearchUrls())
            return $urls;
    }

    private function _getCategoryUrls()
    {
        if (!preg_match('~\-c(\d+)~', $this->getUrl(), $matches))
            return array();
        $result = \wp_remote_get('https://rest.adayroi.com/cxapi/v2/adayroi/search?fields=FULL&q=&categoryCode=' . $matches[1] . '&pageSize=32', array(
            'headers' => array('Content-Type' => 'application/json; charset=utf-8'))
        );
        if (\is_wp_error($result))
            return array();
        $body = \wp_remote_retrieve_body($result);
        if (!$body)
            return array();

        $result = json_decode($body, true);
        if (!$result || !isset($result['products']))
            return false;
        $urls = array();
        foreach ($result['products'] as $item)
        {
            $urls[] = $item['url'];
        }
        return $urls;
    }

    private function _getSearchUrls()
    {
        if (!preg_match('~q=(.+)~', $this->getUrl(), $matches))
            return array();

        $keyword = $matches[1];
        try
        {
            $result = $this->requestGet('https://rest.adayroi.com/cxapi/v2/adayroi/search?fields=FULL&q=&pageSize=32&text=' . $keyword, false);
        } catch (\Exception $e)
        {
            return array();
        }
        $result = json_decode($result, true);
        if (!$result || !isset($result['products']))
            return false;
        $urls = array();
        foreach ($result['products'] as $item)
        {
            $urls[] = $item['url'];
        }
        return $urls;
    }

    public function parseTitle()
    {
        if (!$this->_parseProduct())
            return;
        if (isset($this->_product['currentOffer']['name']))
            return $this->_product['currentOffer']['name'];
    }

    private function _parseProduct()
    {
        if (!preg_match('~\-p\-([0-9A-Z]+)~', $this->getUrl(), $matches))
            return false;

        $result = \wp_remote_get('https://rest.adayroi.com/cxapi/v2/adayroi/product/detail?fields=FULL&productCode=' . $matches[1] . '&pageSize=32', array(
            'headers' => array('Content-Type' => 'application/json; charset=utf-8'))
        );

        if (\is_wp_error($result))
            return array();

        $body = \wp_remote_retrieve_body($result);

        if (!$body)
            return array();

        $result = json_decode($body, true);
        if (!$result || !isset($result['currentOffer']))
            return false;

        $this->_product = $result;
        return $this->_product;
    }

    public function parseDescription()
    {
        if (isset($this->_product['currentOffer']['description']))
            return $this->_product['currentOffer']['description'];
    }

    public function parsePrice()
    {
        if (isset($this->_product['offers'][0]['offerPrice']))
            return $this->_product['offers'][0]['offerPrice'];
        if (isset($this->_product['currentOffer']['offerPrice']))
            return $this->_product['currentOffer']['offerPrice'];
    }

    public function parseOldPrice()
    {
        if (isset($this->_product['productPrice']['value']))
            return $this->_product['productPrice']['value'];
    }

    public function parseImg()
    {
        if (isset($this->_product['images'][0]['url']))
            return str_replace('/80_80/', '/820_820/', $this->_product['images'][0]['url']);
    }

}
