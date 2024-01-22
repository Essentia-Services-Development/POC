<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * DomostiruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class DomostiruParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $currency = 'RUB';

    public function parseCatalog($max)
    {
        return array_slice($this->xpathArray(".//div[contains(@class,'css_div__center_column')]//div[@class='css_div__title']/a/@href"), 0, $max);
    }

    /**
     * Название товара
     * @return String
     */
    public function parseTitle()
    {
        return $this->xpathScalar(".//h1[contains(@class,'css_h1__product_details-title')]");
    }

    /**
     * Описание товара
     * @return String
     */
    public function parseDescription()
    {
        $description = $this->xpathScalar(".//*[@id='tab_c0']//div[@class='css_div__block']");
        $description = trim(str_replace('ОПИСАНИЕ:', '', $description));
        return $description;
    }

    /**
     * Цена
     * @return String
     */
    public function parsePrice()
    {
        return $this->xpathScalar(".//*[@class='css_div__product_details-price']/text()");
    }

    /**
     * Старая цена (без скидки)
     * @return String
     */
    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@class='css_div__product_details-old_price']");
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
        return $this->xpathScalar(".//table[@class='css_table__photo']//img/@src");
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

        /**
         * Характеристики товара
         */
        $extra['features'] = array();
        $names = $this->xpathArray(".//*[@id='charasteristics']//td/b");
        $values = $this->xpathArray(".//*[@id='charasteristics']//td[2]");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]))
            {
                $feature['name'] = sanitize_text_field($names[$i]);
                $feature['value'] = sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }

        $extra['images'] = array();
        $images = $this->xpathArray(".//table[@class='css_table__list']//img/@src");
        foreach ($images as $key => $img)
        {
            $extra['images'][] = str_replace('/80x80/', '/460x460/', $img);
        }

        $extra['comments'] = array();
        $users = $this->xpathArray(".//*[@id='ProductCommentsForm']//span/b");
        $comments = $this->xpathArray(".//*[@id='ProductCommentsForm']//div[@class='css_div__comment']");
        for ($i = 0; $i < count($comments); $i++)
        {
            $comment['comment'] = sanitize_text_field($comments[$i]);
            if (!empty($users[$i]))
                $comment['name'] = sanitize_text_field($users[$i]);
            $extra['comments'][] = $comment;
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
