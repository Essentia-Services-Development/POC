<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * GipostroyruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class GipostroyruParser extends ShopParser {

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
        $urls = array_slice($this->xpathArray(".//*[@class='result']//li//div[@class='product-description']//a/@href"), 0, $max);

        foreach ($urls as $key => $url)
        {
            $urls[$key] = 'http://www.gipostroy.ru' . $url;
        }
        return $urls;
    }

    /**
     * Название товара
     * @return String
     */
    public function parseTitle()
    {
        return $this->xpathScalar(".//*[@class='product-header']/h1");
    }

    /**
     * Описание товара
     * @return String
     */
    public function parseDescription()
    {
        return trim(join(' ', $this->xpathArray(".//*[@id='product-features']//div[@class='small-6 columns']//p")));
    }

    /**
     * Цена
     * @return String
     */
    public function parsePrice()
    {
        return $this->xpathScalar(".//*[@id='content']//p[@class='price']");
    }

    /**
     * Старая цена (без скидки)
     * @return String
     */
    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@class='old-price-item']");
    }

    /**
     * Производитель
     * @return String
     */
    public function parseManufacturer()
    {
        $extra = $this->parseExtra();
        if (empty($extra['features']))
            return '';
        foreach ($extra['features'] as $f)
        {
            if ($f['name'] == 'Производитель')
                return $f['value'];
        }
    }

    /**
     * Основная картинка товара.
     * @return String
     */
    public function parseImg()
    {
        $img = $this->xpathScalar(".//a[@class='fancybox']/img/@src");
        if (!preg_match('/^http:/', $img))
            $img = 'http://www.gipostroy.ru' . $img;
        return $img;
    }

    /**
     * Большая картинка товара.
     * @return String
     */
    public function parseImgLarge()
    {
        
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
        $names = $this->xpathArray(".//table[@class='charakters-table']//td[@class='nm']");
        $values = $this->xpathArray(".//table[@class='charakters-table']//td[not(@class='nm')]");
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
