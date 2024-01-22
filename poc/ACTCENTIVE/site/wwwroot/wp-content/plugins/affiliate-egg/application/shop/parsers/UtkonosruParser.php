<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * UtkonosruParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class UtkonosruParser extends ShopParser {

    protected $charset = 'utf-8';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//div[@class='grid_9 main_content']//a[@class='goods_caption']/@href"), 0, $max);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'https://www.utkonos.ru' . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return $this->xpathScalar(".//meta[@property='og:title']/@content");
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//meta[@property='og:description']/@content");
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//div[@class='goods_item_control-price']//*/@data-static-now-price");
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//div[@class='goods_item_control-price']//*/@data-static-old-price");
    }

    public function parseManufacturer()
    {

        return trim($this->xpathScalar(".//div[@class='goods_view_item-property_container']//*[normalize-space(text())='Бренд']/..//a"));
    }

    public function parseImg()
    {
        $img = $this->xpathScalar(".//div[@class='goods_view_item-pic']//a/@data-pic-medium");
        if ($img && !preg_match('/^https?:\/\//', $img))
            $img = 'https://www.utkonos.ru' . $img;
        return preg_replace("/\?.+/msi", "", $img);
    }

    public function parseImgLarge()
    {
        $img = $this->xpathScalar(".//div[@class='goods_view_item-pic']//a/@data-pic-high");
        if ($img && !preg_match('/^https?:\/\//', $img))
            $img = 'https://www.utkonos.ru' . $img;
        return preg_replace("/\?.+/msi", "", $img);
    }

    public function parseExtra()
    {
        $extra = array();

        $extra['features'] = array();

        $names = $this->xpathArray(".//div[@class='goods_view_item-property_container']//div[@class='goods_view_item-property_title']");
        $values = $this->xpathArray(".//div[@class='goods_view_item-property_container']//div[@class='goods_view_item-property_value']");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (!empty($values[$i]) && trim($names[$i]) != "Бренд")
            {
                $feature['name'] = \sanitize_text_field($names[$i]);
                $feature['value'] = \sanitize_text_field($values[$i]);
                $extra['features'][] = $feature;
            }
        }

        $extra['rating'] = TextHelper::ratingPrepare($this->xpathScalar(".//*[@class='goods_view_item-preamble_rating']//span[@class='selected']/@data-ratingpos"));
        return $extra;
    }

    public function isInStock()
    {
        return true;
    }

}
