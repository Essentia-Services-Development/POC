<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * BanggoodParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class BanggoodParser extends LdShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'USD';

    public function parseCatalog($max)
    {
        $path = array(
            ".//div[@class='p-wrap']//a/@href",
            ".//dd[@class='name']/a/@href",
            ".//span[@class='title']/a/@href",
            ".//*[@class='hot_others_box_c']//a[1]/@href",
            ".//a[@class='products_name']/@href",
            ".//ul/li//div[@class='p-wrap']//a[@class='title']/@href",
        );

        $urls = $this->xpathArray($path);

        if (!$urls && preg_match_all('/<a class="title" href="(.+?)" title=/', $this->html, $matches))
            $urls = $matches[1];

        foreach ($urls as $i => $url)
        {
            $urls[$i] = strtok($url, '?');
            if (!preg_match('~-p-\d+\.html~', $urls[$i]))
                unset($urls[$i]);
        }

        $urls = array_unique($urls);
        return $urls;
    }

    public function parseTitle()
    {
        if ($title = parent::parseTitle())
            return $title;
        else
            return $this->xpathScalar(".//h1[@class='product-title']");
    }

    public function parseDescription()
    {
        $path = array(
            ".//div[@class='tab-cnt-description']",
        );

        if ($d = $this->xpathScalar($path, true))
        {
            $parts = explode('<div id="specification">', $d);
            if (count($parts) == 2)
                return '<div id="specification">' . $parts[1];
            else
                return $d;
        }        
    }

    /*
      public function parsePrice()
      {
      if ($p = parent::parsePrice())
      return $p;

      $html = $this->dom->saveHTML();
      if (preg_match('/,"value":"([0-9\.]+?)","currency":/', $html, $matches))
      return $matches[1];
      if (preg_match('/\{"price":([0-9\.]+?),"/', $html, $matches))
      return $matches[1];
      elseif (preg_match('/"price": "([0-9\.]+?)",/', $html, $matches))
      return $matches[1];
      }
     * 
     */

    public function parseOldPrice()
    {
        // JS
    }

    public function parseManufacturer()
    {
        return '';
    }

    public function parseImg()
    {
        return $this->xpathScalar(".//ul[@class='list cf']//li/@data-large");
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();
        if (!$this->isInStock())
            return $extra;

        $extra['comments'] = $this->_parseReviews();
        return $extra;
    }

    public function _parseReviews()
    {
        //coded URL
    }

    public function isInStock()
    {
        if ($p = parent::isInStock())
            return $p;

        if (strstr($this->xpathScalar(".//title"), 'Banggood.com sold out'))
            return false;
        if ($this->xpathScalar(".//*[@class='addToCartBtn_box']//a[contains(@class, 'arrivalnotice')]") == 'In Stock Alert')
            return false;

        return parent::isInStock();
    }

    /*
      public function getCurrency()
      {
      if ($p = parent::getCurrency())
      return $p;

      if (preg_match('/,"currency":"(\w+)",/', $this->dom->saveHTML(), $matches))
      return $matches[1];
      else
      return $this->currency;
      }
     * 
     */
}
