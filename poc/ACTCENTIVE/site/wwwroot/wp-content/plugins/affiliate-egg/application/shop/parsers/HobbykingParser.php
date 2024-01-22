<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * HobbykingParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class HobbykingParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'USD';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//*[@id='tableContent']//table//div[1]/a/@href"), 0, $max);
        foreach ($urls as $key => $url)
        {
            $urls[$key] = 'http://www.hobbyking.com/hobbyking/store/' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return trim($this->xpathScalar(".//h1[@id='productDescription']"));
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//*[@itemprop='description']");
    }

    public function parsePrice()
    {
        return preg_replace('/[^0-9,\.]/', '', $this->xpathScalar(".//div[@class='box']//span[@id='price_lb']"));
    }

    public function parseOldPrice()
    {
        
    }

    public function parseManufacturer()
    {
        return '';
    }

    public function parseImg()
    {
        $img = trim($this->xpathScalar(".//*[@id='mainpic1']/@src"));
        if (!$img)
            return '';
        $img = 'http://www.hobbyking.com/hobbyking/store/' . $img;
        return $img;
    }

    public function parseImgLarge()
    {
        return $this->parseImg();
    }

    public function parseExtra()
    {
        return array();
    }

    public function isInStock()
    {
        $availability = $this->xpathScalar(".//*[@id='details']/div[1]/div[2]/div/table/tbody/tr[1]/td[3]/div/div[1]");
        if ($availability == 'stock')
            return true;
        else
            return false;
    }

    public function getCurrency()
    {
        return trim($this->xpathScalar(".//*[@id='currency-list']/b"));
    }

}
