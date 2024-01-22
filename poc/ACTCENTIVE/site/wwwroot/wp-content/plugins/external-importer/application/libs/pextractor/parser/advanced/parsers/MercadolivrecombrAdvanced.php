<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

/**
 * MercadolivrecombrAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class MercadolivrecombrAdvanced extends AdvancedParser {

    public function parseLinks()
    {
        $path = array(
            ".//h2[contains(@class, 'item__title')]/a/@href",
            "id('searchResults')//a/@href",
        );

        $urls = $this->xpathArray($path);

        foreach ($urls as $i => $url)
        {
            $urls[$i] = strtok($url, '?');
            $urls[$i] = strtok($urls[$i], '#');
        }

        return $urls;
    }

    public function parsePagination()
    {
        $path = array(
            ".//div[@class='pagination__container']//a[contains(@href, 'Desde')]/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseDescription()
    {
        $paths = array(
            ".//ul[@class='ui-pdp-features mt-24']",
            ".//p[@class='ui-pdp-description__content']",
        );


        return $this->xpathScalar($paths, true);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//del/span[@class='price-tag-symbol']/@content"
        );

        return $this->xpathScalar($paths);
    }

    public function parseImages()
    {
        $paths = array(
            ".//*[@class='ui-pdp-gallery__figure']/img/@data-srcset",
            ".//*[contains(@class, 'gallery-image-container')]//img/@src",
        );

        $images = $this->xpathArray($paths);
        array_shift($images);
        return $images;
    }

    public function getFeaturesXpath()
    {
        return array(
            array('name-value' => ".//div[@class='ui-pdp-list ui-pdp-specs__list']//li[@class='ui-pdp-list__item']"),
            array(
                'name' => ".//ul[contains(@class, 'specs-list')]//li[@class='specs-item specs-item-secondary']/strong",
                'value' => ".//ul[contains(@class, 'specs-list')]//li[@class='specs-item specs-item-secondary']/span"
            ),
            array(
                'name' => ".//div[@class='ui-vpp-highlighted-specs__striped-specs']//th",
                'value' => ".//div[@class='ui-vpp-highlighted-specs__striped-specs']//td"
            ),
        );
    }

    public function parseCurrencyCode()
    {
        return 'BRL';
    }

}
