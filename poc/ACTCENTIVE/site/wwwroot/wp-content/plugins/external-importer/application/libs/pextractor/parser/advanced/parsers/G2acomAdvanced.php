<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

/**
 * G2acomAdvanced class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class G2acomAdvanced extends AdvancedParser {

    public function getHttpOptions()
    {
        $httpOptions = parent::getHttpOptions();

        // reset session cookies
        $httpOptions['cookies'] = array();
        $httpOptions['headers'] = array(
            'Accept' => '', //!!!
            'Accept-Language' => 'en-us,en;q=0.5',
            'Cache-Control' => 'no-cache',
        );
        $httpOptions['user-agent'] = 'ia_archiver';

        return $httpOptions;
    }

    public function parseLinks()
    {
        $path = array(
            ".//h3[contains(@class, 'indexes__Title')]/a/@href",            
            ".//h3[@class='Card__title']/a/@href",
        );

        return $this->xpathArray($path);
    }

    public function parsePagination()
    {
        $path = array(
            ".//nav[@class='pagination']//a[contains(@href, 'page=')]/@href",
        );

        return $this->xpathArray($path);
    }

    public function parseOldPrice()
    {
        $paths = array(
            ".//div[contains(@class, 'product-info__payments')]//span[@class='product-page-v2-price__old-price']",
        );

        return $this->xpathScalar($paths);
    }

    public function parseImage()
    {
        $paths = array(
            ".//div[contains(@class, 'indexes__StyledGalleryItem')]//img/@src",
            ".//meta[@property='og:image']/@content",
        );

        return $this->xpathArray($paths);
    }

    public function parseImages()
    {
        $paths = array(
            ".//div[contains(@class, 'page--product2__gallery--digital')]//img[not(contains(@data-src, 'youtube'))]/@data-src",
        );

        return $this->xpathArray($paths);
    }

    public function getFeaturesXpath()
    {
        return array(
            array(
                'name' => ".//*[@class='attributes-list']//span[@class='attributes-list__name']",
                'value' => ".//*[@class='attributes-list']//span[@class='attributes-list__value']",
            ),
        );
    }

}
