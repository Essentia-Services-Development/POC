<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * MebelviaruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class MebelviaruParser extends ShopParser {

    /**
     *
     * @var String Указывайте исходную кодировку страницы
     */
    protected $charset = 'utf-8';

    /**
     *
     * @var String Указывайте валюту магазина
     */
    protected $currency = 'RUB';

    /**
     * Парсер каталога магазина. Метод должен вернуть массив URL на карточки товаров.
     * @param Integer $max
     * @return Array
     */
    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//a[@class='catalog_item-photo']/@href"), 0, $max);

        foreach ($urls as $key => $url)
        {
            $urls[$key] = 'http://mebelvia.ru' . $url;
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
        return $this->xpathScalar(".//meta[@itemprop='price']/@content");
    }

    /**
     * Старая цена (без скидки)
     * @return String
     */
    public function parseOldPrice()
    {
        return $this->xpathScalar(".//div[@class='productPrice']/div[@class='old']");
    }

    /**
     * Производитель
     * @return String
     */
    public function parseManufacturer()
    {
        
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
        $img = $this->xpathScalar(".//img[@itemprop='image']/@data-large");
        if (!preg_match('/^http:/', $img))
            $img = 'http://mebelvia.ru' . $img;
        return $img;
    }

    /**
     * Любые дополнительные данные по товару
     * @return array
     */
    public function parseExtra()
    {
        $extra = array();

        /**
         * Характеристики товара
         */
        $extra['features'] = array();
        $names = $this->xpathArray(".//ul[@class='productFeature']//span");
        $values = $this->xpathArray(".//ul[@class='productFeature']//b");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]))
            {
                $feature['name'] = rtrim(sanitize_text_field($names[$i]), ':');
                $feature['value'] = sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }

        $extra['images'] = array();
        $images = $this->xpathArray(".//div[@class='slider']//img[@class='js_small_img']/@originimg");
        foreach ($images as $key => $img)
        {
            if ($key == 0)
                continue;

            if (!preg_match('/^http:/', $img))
                $img = 'http://mebelvia.ru' . $img;
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
        if ($this->xpathScalar(".//*[@itemprop='availability']/@href") == 'http://schema.org/InStock')
            return true;
        else
            return false;
    }

}
