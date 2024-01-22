<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\AbstractParser;
use ExternalImporter\application\libs\pextractor\parser\ParserFormat;

/**
 * MicroformatsParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2019 keywordrush.com
 */
class MicroformatsParser extends AbstractParser {

    const FORMAT = ParserFormat::MICROFORMATS;

    public function parseTitle()
    {
        $paths = array(
            ".//*[@class='hproduct']//*[@class='fn']",
            ".//*[@class='h-product']//*[@class='p-name']",
            ".//*[contains(concat(' ', normalize-space(@class), ' '), ' hproduct ')]//*[contains(concat(' ', normalize-space(@class), ' '), ' fn ')]",
            ".//*[contains(concat(' ', normalize-space(@class), ' '), ' h-product ')]//*[contains(concat(' ', normalize-space(@class), ' '), ' p-name ')]",
        );
        return $this->xpathScalar($paths);
    }

    public function parseDescription()
    {
        $paths = array(
            ".//*[@class='h-product']//*[@class='e-description']",
            ".//*[contains(concat(' ', normalize-space(@class), ' '), ' h-product ')]//*[contains(concat(' ', normalize-space(@class), ' '), ' e-description ')]",
            ".//*[contains(concat(' ', normalize-space(@class), ' '), ' hproduct ')]//*[contains(concat(' ', normalize-space(@class), ' '), ' description ')]",
        );
        return $this->xpathScalar($paths, true);
    }

    public function parsePrice()
    {
        $paths = array(
            ".//*[@class='h-product']//*[@class='p-price']/@value",
            ".//*[@class='h-product']//*[@class='p-price']",
            ".//*[contains(concat(' ', normalize-space(@class), ' '), ' h-product ')]//*[contains(concat(' ', normalize-space(@class), ' '), ' p-price ')]",
        );
        return $this->xpathScalar($paths);
    }

    public function parseCurrencyCode()
    {
        $paths = array(
            ".//*[@class='h-product']//*[@class='p-name']",
        );
        return $this->xpathScalar($paths);
    }

    public function parseImage()
    {
        $paths = array(
            ".//*[@class='h-product']//img[@class='u-photo']/@src",
            ".//*[contains(concat(' ', normalize-space(@class), ' '), ' h-product ')]//img[contains(concat(' ', normalize-space(@class), ' '), ' u-photo ')]/@src",
            ".//img[@class='u-photo']/@src",
            ".//*[contains(concat(' ', normalize-space(@class), ' '), ' hproduct ')]//*[contains(concat(' ', normalize-space(@class), ' '), ' photo ')]",
        );
        return $this->xpathScalar($paths);
    }

    public function parseManufacturer()
    {
        $paths = array(
            ".//*[@class='h-product']//*[@class='p-brand']",
            ".//*[@class='h-product']//*[@class='p-brand h-card']",
            ".//*[contains(concat(' ', normalize-space(@class), ' '), ' h-product ')]//*[contains(concat(' ', normalize-space(@class), ' '), ' p-brand ')]",
            ".//*[contains(concat(' ', normalize-space(@class), ' '), ' hproduct ')]//*[contains(concat(' ', normalize-space(@class), ' '), ' brand ')]",
        );
        return $this->xpathScalar($paths);
    }

    public function parseCategoryPath()
    {
        $paths = array(
            ".//*[@class='h-product']//*[@class='p-category']",
            ".//*[contains(concat(' ', normalize-space(@class), ' '), ' h-product ')]//*[contains(concat(' ', normalize-space(@class), ' '), ' p-category ')]",
            ".//*[contains(concat(' ', normalize-space(@class), ' '), ' hproduct ')]//*[contains(concat(' ', normalize-space(@class), ' '), ' category ')]",
        );
        return $this->xpathArray($paths);
    }

    protected function parseRatingValue()
    {
        $paths = array(
            ".//*[contains(concat(' ', normalize-space(@class), ' '), ' hproduct ')]//*[contains(concat(' ', normalize-space(@class), ' '), ' rating ')]",
        );
        return $this->xpathScalar($paths);
    }

}
