<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * UlmartruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class UlmartruParser extends ShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        //$urls = array_slice($this->xpathArray(".//div[contains(@class,'h-products')]//a[@class='g-underline']/@href"), 0, $max);
        $urls = array_slice($this->xpathArray(".//div[@id='catalogGoodsBlock']//span//a[contains(@class,'must_be_href')]/@href"), 0, $max);
        if (!$urls)
            $urls = array_slice($this->xpathArray(".//div[@id='catalogGoodsBlock']//span/a/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'http://www.ulmart.ru' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return trim($this->xpathScalar(".//section[contains(@class,'b-product-card')]//h1[contains(@class,'main-h1 main-h1_bold')]"));
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//p/span[@itemprop='description']");
    }

    public function parsePrice()
    {
        return preg_replace('/[^0-9]/', '', $this->xpathScalar(".//meta[@itemprop='price']/@content"));
    }

    public function parseOldPrice()
    {
        return preg_replace('/[^0-9]/', '', $this->xpathScalar(".//div[@class='panel-body']//*[contains(@class, 'b-price_old')]/span[1]"));
    }

    public function parseManufacturer()
    {
        return '';
    }

    public function parseImg()
    {
        return $this->xpathScalar(".//div[@id='product_card_img']/img[@itemprop='image']/@src");
    }

    public function parseImgLarge()
    {
        $img = '';
        if ($this->item['orig_img'])
            $img = str_replace('/mid/', '/big/', $this->item['orig_img']);
        return $img;
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();

        $names = $this->xpathArray(".//div[@id='properties-full-sections']//*[@class='b-dotted-line__title']/text()[normalize-space()]");
        $values = $this->xpathArray(".//div[@id='properties-full-sections']//*[@class='b-dotted-line__content']");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]) && trim($names[$i]) != "Описание")
            {
                $feature['name'] = sanitize_text_field($names[$i]);
                $feature['value'] = sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }

        $extra['images'] = array();
        $results = $this->xpathArray(".//ul[@id='product_card_nav']/li/a/img/@src");
        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            if ($res)
            {
                $res = str_replace('s.jpg', '.jpg', $res);
                $extra['images'][] = str_replace('/small/', '/big/', $res);
            }
        }
        return $extra;
    }

    public function isInStock()
    {
        if ($this->parsePrice())
            return true;
        else
            return false;

        // "региональная доствка" по дефолту, часто "нет в наличии"
        /*
          $val = $this->xpathScalar(".//div[contains(@class,'b-product-card__alert')]/div[1]");
          if ($val == 'Нет в наличии')
          return false;
          else
          return true;
         * 
         */
    }

}
