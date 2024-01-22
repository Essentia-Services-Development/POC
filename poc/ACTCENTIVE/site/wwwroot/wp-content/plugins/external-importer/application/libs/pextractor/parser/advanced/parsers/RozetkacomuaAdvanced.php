<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

/**
 * RozetkacomuaAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class RozetkacomuaAdvanced extends AdvancedParser {

    public function parseLinks()
    {
        $path = array(
            ".//a[@class='goods-tile__heading']/@href",
        );

        if ($urls = $this->xpathArray($path))
            return $urls;

        if (preg_match_all('/price_pcs&q;:&q;.+?&q;,&q;href&q;:&q;(.+?)&q;,&q;comments_amount/', $this->html, $matches))
            return $matches[1];
    }

    public function parsePagination()
    {
        $path = array(
            ".//ul[@class='pagination__list']//li/a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseDescription()
    {
        if ($d = $this->xpathScalar(".//div[contains(@class, 'product-about__description-content')]", true))
            return $d;

        if (!preg_match('~description&q;:{&q;text&q;:&q;(&l;p&g;.+?)&q;,&q;html&q;~', $this->html, $matches))
            return '';

        return str_replace(
                array('\&q;', '&l;b&g;', '&l;/b&g;', '&l;', '&g;', '&q;'),
                array('"', '<b>', '</b>', '<', '>', '"'),
                $matches[1]);
    }

    public function parseOldPrice()
    {
        if (preg_match('~old_price&q;:&q;(.+?)&q;~', $this->html, $matches))
            return $matches[1];

        $paths = array(
            ".//div[@class='product-about__block']//p[@class='product-prices__small']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseImage()
    {
        if ($images = $this->parseImages())
            return reset($images);
    }

    public function parseImages()
    {

        $paths = array(
            ".//li[contains(@class, 'product-thumbnails__item')]//img/@src",
        );

        if ($images = $this->xpathArray($paths))
        {
            foreach ($images as $i => $image)
            {
                $images[$i] = str_replace('/preview/', '/big/', $image);
            }

            return $images;
        }

        if (preg_match_all('/&q;url&q;:&q;(https:\/\/[a-z0-9_\.\/]+?\.jpg)&q;,&q;width&q;.+?big&/ims', $this->html, $matches))
        {
            $images = $matches[1];
            array_shift($images);
            return $images;
        }
    }

    public function parseCategoryPath()
    {
        $paths = array(
            ".//ul[contains(@class, 'breadcrumbs')]//li/a/span",
        );

        $categs = $this->xpathArray($paths);
        array_pop($categs);
        return $categs;
    }

    public function parseFeatures()
    {
        if ($features = parent::parseFeatures())
            return $features;

        $names = $this->xpathArray(".//dl[@class='characteristics-full__list']//dt");
        $values = $this->xpathArray(".//dl[@class='characteristics-full__list']//dd", true);

        if (!$names || !$values || count($names) != count($values))
            return array();

        $features = array();
        for ($i = 0; $i < count($names); $i++)
        {
            $value = str_replace('</li>', '</li>; ', $values[$i]);
            $feature['name'] = \sanitize_text_field($names[$i]);
            $feature['value'] = \sanitize_text_field(html_entity_decode($value));
            $features[] = $feature;
        }

        return $features;
    }

    public function getReviewsXpath()
    {
        return array(
            array(
                'review' => ".//ul[@class='product-comments__list']//p[contains(@class, 'comment__text')]",
                //'rating' => ".//ul[@class='product-comments__list']//div[@class='product-comment__body']",
                'author' => ".//ul[@class='product-comments__list']//div[@class='comment__author']/text()",
                'date' => ".//ul[@class='product-comments__list']//time",
            ),
        );
    }

    public function parseCurrencyCode()
    {
        return 'UAH';
    }

    public function parseMpn()
    {
        if (!$title = $this->xpathScalar(".//h1[@class='product__title']"))
            return;

        if (preg_match('~\((.+?)\)$~', $title, $matches))
            return $matches[1];
    }

}
