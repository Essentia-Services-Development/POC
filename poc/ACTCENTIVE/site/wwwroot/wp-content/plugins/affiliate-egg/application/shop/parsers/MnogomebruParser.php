<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * MnogomebruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class MnogomebruParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'RUB';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//div[contains(@class,'catalog-list')]//a/@href"), 0, $max);
        foreach ($urls as $key => $url)
        {
            $urls[$key] = 'https://mnogomeb.ru' . $url;
        }
        return $urls;
    }

    /**
     * Название товара
     * @return String
     */
    public function parseTitle()
    {
        $title = $this->xpathScalar(".//h1[@itemprop='name']");
        if (!$title)
            $title = $this->xpathScalar(".//*[@class='item-content']//h1");
        return $title;
    }

    /**
     * Описание товара
     * @return String
     */
    public function parseDescription()
    {
        $description = $this->xpathScalar(".//div[@itemprop='description']");
        /*
          if (!$description)
          $description = join('. ', $this->xpathArray(".//*[@id='other_chars']//div[@class='fieldset-body']/div"));
         * 
         */
        return $description;
    }

    /**
     * Цена
     * @return String
     */
    public function parsePrice()
    {
        $price = $this->xpathScalar(".//*[@itemprop='offers']//*[@itemprop='price']/@content");
        if (!$price)
            $price = $this->xpathScalar(".//*[@class='actual_price']");
        return $price;
    }

    /**
     * Старая цена (без скидки)
     * @return String
     */
    public function parseOldPrice()
    {
        $price = $this->xpathScalar(".//*[@itemtype='http://schema.org/Offer']//*[@class='old']/s");
        if (!$price)
            $price = $this->xpathScalar(".//*[@class='price']//*[@class='old']");
        return $price;
    }

    /**
     * Производитель
     * @return String
     */
    public function parseManufacturer()
    {
        return '';
    }

    /**
     * Основная картинка товара.
     * @return String
     */
    public function parseImg()
    {
        $img = $this->xpathScalar(".//img[@itemprop='image']/@data-big-image");
        if (!$img)
            $img = $this->xpathScalar(".//div[@class='main-image']//img/@data-big-image");

        if (!preg_match('/^http:/', $img))
            $img = 'https://mnogomeb.ru' . $img;
        return $img;
    }

    /**
     * Большая картинка товара.
     * @return String
     */
    public function parseImgLarge()
    {
        return '';
    }

    /**
     * Любые дополнительные данные по товару
     * @return array
     */
    public function parseExtra()
    {
        $extra = array();

        /*
          // JavaScript output
          $extra['features'] = array();
          $list = $this->xpathArray(".//div[@id='other_chars']//div[@class='col1']");
          foreach ($list as $str)
          {
          $parts = explode(' — ', $str);
          if (count($parts) != 2)
          continue;
          $feature['name'] = sanitize_text_field($parts[0]);
          $feature['value'] = sanitize_text_field($parts[1]);
          $extra['features'][] = $feature;
          }
         */

        $extra['images'] = array();
        $images = $this->xpathArray(".//div[@class='additional-images-wrapper']//img/@data-main-image");
        foreach ($images as $key => $img)
        {
            if ($key == 0)
                continue;
            if (!preg_match('/^http:/', $img))
                $img = 'http://mnogomeb.ru' . $img;
            $extra['images'][] = $img;
        }

        return $extra;
    }

    /**
     * Наличие товара
     * @return boolean
     */
    public function isInStock()
    {
        if ($this->parsePrice())
            return true;
        else
            return false;
    }

}
