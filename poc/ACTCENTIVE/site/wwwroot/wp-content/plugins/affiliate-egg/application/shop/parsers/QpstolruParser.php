<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * QpstolruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class QpstolruParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'RUB';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//div[@class='goods-element']//div[@class='title']/a/@href"), 0, $max);
        foreach ($urls as $key => $url)
        {
            $urls[$key] = 'http://www.qpstol.ru' . $url;
        }
        return $urls;
    }

    /**
     * Название товара
     * @return String
     */
    public function parseTitle()
    {
        return $this->xpathScalar(".//h1[@itemprop='name']");
    }

    /**
     * Описание товара
     * @return String
     */
    public function parseDescription()
    {
        return $this->xpathScalar(".//div[@itemprop='description']");
    }

    /**
     * Цена
     * @return String
     */
    public function parsePrice()
    {
        return $this->xpathScalar(".//*[@itemprop='offers']//*[@itemprop='price']");
    }

    /**
     * Старая цена (без скидки)
     * @return String
     */
    public function parseOldPrice()
    {
        
    }

    /**
     * Производитель
     * @return String
     */
    public function parseManufacturer()
    {
        return $this->xpathScalar(".//*[@itemprop='brand']");
    }

    /**
     * Основная картинка товара.
     * @return String
     */
    public function parseImg()
    {
        return $this->xpathScalar(".//img[@itemprop='image']/@src");
    }

    /**
     * Большая картинка товара.
     * @return String
     */
    public function parseImgLarge()
    {
        $img = $this->parseImg();
        return str_replace('_D.jpg', '.jpg', $img);
    }

    /**
     * Любые дополнительные данные по товару
     * @return array
     */
    public function parseExtra()
    {
        $extra = array();
        $extra['features'] = array();
        $names = $this->xpathArray(".//ul[@class='parametrs-products-list']//span[@class='title']");
        $values = $this->xpathArray(".//ul[@class='parametrs-products-list']//span[@class='val']");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]))
            {
                $feature['name'] = \sanitize_text_field($names[$i]);
                $feature['value'] = \sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }


        $extra['images'] = array();
        $images = $this->xpathArray(".//ul[contains(@class,'pr-det_images-thumbs')]//img/@src");
        foreach ($images as $key => $img)
        {
            if ($key == 0)
                continue;
            $extra['images'][] = str_replace('_S.jpg', '_D.jpg', $img);
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
