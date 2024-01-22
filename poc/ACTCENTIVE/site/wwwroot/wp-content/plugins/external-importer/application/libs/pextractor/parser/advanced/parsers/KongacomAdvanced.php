<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

/**
 * KongacomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class KongacomAdvanced extends AdvancedParser {

    public function parseLinks()
    {
        $path = array(
            ".//section//ul/li//h3/../../../a/@href",
            ".//section/section/div/div/ul/li/a/@href",
            "//a[contains(@href, '/product/')]/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePagination()
    {
        $path = array(
            ".//ul/li/a[contains(@href, '?page=')]/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//*[@class='aadf4_2-w0o']//*[@class='_10344_3PAla']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseCategoryPath()
    {
        $paths = array(
            ".//ul/li[@name='breadcrumbItem']",
        );

        return $this->xpathArray($paths);
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name' => ".//table[@class='_3a09a_1e-gU']//tr//td[1]",
                'value' => ".//table[@class='_3a09a_1e-gU']//tr/td[2]",
            ),
        );
    }

}
