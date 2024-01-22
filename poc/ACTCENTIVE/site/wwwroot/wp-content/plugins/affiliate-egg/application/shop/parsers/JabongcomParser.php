<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/*
  Name: Jabong.com
  URI: http://www.jabong.com
  Icon: http://www.google.com/s2/favicons?domain=jabong.com
  Search URI: http://www.jabong.com/find/%KEY-WORD%/?q=%KEYWORD%&qc=%KEYWORD%
  CPA:
 */

/**
 * JabongcomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class JabongcomParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'INR';
    protected $user_agent = 'wget';
    protected $headers = array(
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language' => 'en-us,en;q=0.5',
        'Cache-Control' => 'no-cache',
        'Connection' => 'keep-alive',
    );

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//section[contains(@class,'search-product')]//div[contains(@class,'product-tile')]/a/@href"), 0, $max);
        foreach ($urls as $key => $url)
        {
            if (!preg_match('/^https?:/', $url))
                $urls[$key] = 'http://www.jabong.com' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//*[@class='content']//span[@itemprop='name']");
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//*[@itemprop='description']");
    }

    public function parsePrice()
    {
        $p = $this->xpathScalar(".//*[@itemprop='offers']//span[@itemprop='price']");
        if (!$p)
            $p = $this->xpathScalar(".//*[@class='actual-price']/@content");
        return $p;
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@itemprop='offers']//span[contains(@class,'standard-price')]");
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//*[@class='content']//span[@itemprop='brand']");
    }

    public function parseImg()
    {
        $img = $this->xpathScalar(".//meta[@property='og:image']/@content");
        return str_replace('-catalog_xxs_lr.jpg', '-pdp_slider_xs.jpg', $img);
    }

    public function parseImgLarge()
    {
        $img = $this->xpathScalar(".//meta[@property='og:image']/@content");
        return str_replace('-catalog_xxs_lr.jpg', '-pdp_slider_l.jpg', $img);
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();

        $names = $this->xpathArray(".//ul[contains(@class,'prod-main-wrapper')]//li/span[@class='product-info-left']");
        $values = $this->xpathArray(".//ul[contains(@class,'prod-main-wrapper')]//li/span[2]");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]) && $names[$i] != 'Condition:' && $names[$i] != 'Brand')
            {
                $feature['name'] = str_replace(":", "", $names[$i]);
                $feature['value'] = $values[$i];
                $extra['features'][] = $feature;
            }
        }

        $extra['images'] = $this->xpathArray("(.//*[@id='product-details-wrapper']//img[contains(@class, 'primary-image')]/@src)[position() > 2]");
        return $extra;
    }

    public function isInStock()
    {
        $in_stock = trim(strip_tags($this->xpathScalar(".//*[@itemprop='availability']/@href")));
        if ($in_stock == 'http://schema.org/OutOfStock')
            return false;
        else
            return true;
    }

}
