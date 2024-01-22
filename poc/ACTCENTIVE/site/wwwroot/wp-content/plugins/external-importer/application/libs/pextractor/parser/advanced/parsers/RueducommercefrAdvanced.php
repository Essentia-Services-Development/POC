<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

/**
 * RueducommercefrAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class RueducommercefrAdvanced extends AdvancedParser {

    public function parseLinks()
    {
        $path = array(
            ".//*[@class='grid']//article/a/@href",
            ".//h3/a[contains(@class, 'prdLink')]/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePagination()
    {
        $path = array(
            ".//div[@class='pagination']//a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//div[@class='tab-description']", true);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//*[@class='productBuy']//*[@class='prix-conseille']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseImages()
    {
        return $this->xpathArray(".//div[@class='imagesProduit']//li[position() > 1]/a/@data-image");
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name' => ".//div[@class='tab-technique']//div[@class='spec-title']",
                'value' => ".//div[@class='tab-technique']//div[@class='spec']",
            ),
        );
    }

}
