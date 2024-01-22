<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * MistergooddealcomParser class file
 *
 * @author keywordrush.com <support@keywordrush.com> 
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2018 keywordrush.com
 */
class MistergooddealcomParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'EUR';

    public function parseCatalog($max)
    {
        return $this->xpathArray(".//a[@class='prd_link']/@href");
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//h1[@itemprop='name']");
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//*[@itemprop='description']");
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//*[@itemprop='price']/@content");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@class='sale_price']//*[@class='price price_basic']/text()");
    }

    public function parseManufacturer()
    {
        return $this->xpathScalar(".//*[@itemprop='brand']/@content");
    }

    public function parseImg()
    {
        return $this->xpathScalar(".//*[@itemprop='image']/@src");
    }

    public function parseImgLarge()
    {
        
    }

    public function parseExtra()
    {
        $extra = array();


        $extra['rating'] = TextHelper::ratingPrepare($this->xpathScalar(".//*[@itemprop='ratingValue']"));

        /*
          // wrong html...
          $names = $this->xpathArray("//div[@id='carac_product']//li/*[@class='label']");
          $values = $this->xpathArray("//div[@id='carac_product']//li/*[@class='value']");
          $feature = array();
          for ($i = 0; $i < count($names); $i++)
          {
          if (!empty($values[$i]) && !empty($names[$i]))
          {
          $feature['name'] = sanitize_text_field($names[$i]);
          $feature['value'] = sanitize_text_field($values[$i]);
          $extra['features'][] = $feature;
          }
          }
         * 
         */
        return $extra;
    }

    public function isInStock()
    {
        return true;
    }

}
