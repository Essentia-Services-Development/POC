<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

/**
 * LuluhypermarketcomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class LuluhypermarketcomAdvanced extends AdvancedParser {

    public function parseLinks()
    {
        $path = array(
            ".//div[@class='product-box']//a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePagination()
    {
        $path = array(
            ".//div[@class='pagination']//a[not(@class='active')]/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseTitle()
    {
        $paths = array(
            ".//h1[@class='product-name']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//div[@id='description-link']//p", true);
    }

    public function parsePrice()
    {
        $paths = array(
            ".//div[@class='price-tag detail']//span[@class='item price']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//div[@class='price-tag detail']//span[@class='off']/text()",
        );

        return str_replace(',', '', $this->xpathScalar($paths));
    }

    public function parseImage()
    {
        if ($images = $this->parseImages())
            return reset($images);
    }

    public function parseImages()
    {
        return $this->xpathArray(".//div[@id='productShowcaseCarousel']//img/@src");
    }

    public function parseManufacturer()
    {
        $paths = array(
            ".//meta[@property='product:brand']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseCategoryPath()
    {
        $paths = array(
            ".//ol[@class='breadcrumb']//li[position() > 1]/a",
        );

        return $this->xpathArray($paths);
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name' => ".//div[@id='myTabContent']//span[@class='label']",
                'value' => ".//div[@id='myTabContent']//span[@class='value']",
            ),
        );
    }

}
