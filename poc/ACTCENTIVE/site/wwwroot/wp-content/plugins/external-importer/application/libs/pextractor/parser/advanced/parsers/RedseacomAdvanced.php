<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

/**
 * RedseacomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class RedseacomAdvanced extends AdvancedParser {

    public function parseLinks()
    {
        $path = array(
            ".//a[@class='product-item-link']/@href",
        );

        if ($urls = $this->xpathArray($path))
            return $urls;

        if (preg_match_all('/<a href="(.+?)" class="product.+?>/', $this->html, $matches))
            return $matches[1];
    }

    public function parsePagination()
    {
        $path = array(
            ".//div[@class='pages']//ul[@class='items pages-items']/li/a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePrice()
    {
        $paths = array(
            ".//span[@class='special-price']//span[@class='price']",
            ".//div[@class='price-box price-final_price']//span[@class='price']",
        );

        if ($p = $this->xpathScalar($paths))
            return $p;

        if (preg_match('/<meta itemprop="price" content="(.+?)".+?>/', $this->html, $matches))
            return round((float) $matches[1]);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//span[@class='old-price']//span[@class='price']",
        );

        if ($p = $this->xpathScalar($paths))
            return $p;

        if (preg_match('/<span  id="old-price.+? data-price-amount="(.+?)"/', $this->html, $matches))
            return round((float) $matches[1]);
    }

    public function parseImage()
    {
        if ($images = $this->parseImages())
            return reset($images);
    }

    public function parseImages()
    {
        if (!preg_match_all('/,"img":"(.+?)",/', $this->html, $matches))
            return array();

        return array_map('stripslashes', $matches[1]);
    }

    public function parseCategoryPath()
    {
        if (preg_match('/,"category":"(.+?)"/', $this->html, $matches))
            return array(stripslashes($matches[1]));
    }

    public function parseFeatures()
    {
        // invalid html, use regex
        if (!preg_match_all('/<th class="col label" scope="row">(.+?)<\/th>/', $this->html, $matches))
            return array();

        $names = $matches[1];

        if (!preg_match_all('/<td class="col data">(.+?)<\/td>/', $this->html, $matches))
            return array();

        $values = $matches[1];

        if (count($names) != count($values))
            return array();

        $features = array();
        for ($i = 0; $i < count($names); $i++)
        {
            $feature = array();
            $feature['name'] = \sanitize_text_field(trim($names[$i], " \r\n:"));
            $feature['value'] = \sanitize_text_field($values[$i]);
            $features[] = $feature;
        }

        return $features;
    }

    public function parseCurrencyCode()
    {
        return 'SAR';
    }

}
