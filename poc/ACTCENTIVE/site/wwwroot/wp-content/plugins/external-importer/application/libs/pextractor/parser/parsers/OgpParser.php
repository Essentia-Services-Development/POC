<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\AbstractParser;
use ExternalImporter\application\libs\pextractor\parser\ParserFormat;

/**
 * OgpParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2019 keywordrush.com
 */
class OgpParser extends AbstractParser {

    const FORMAT = ParserFormat::OGP;

    public function parseTitle()
    {
        $paths = array(
            ".//meta[@property='og:title']/@content",
            ".//*[@property='product:plural_title']/@content",
            ".//meta[@name='og:title']/@content",
        );
        return $this->xpathScalar($paths);
    }

    public function parseDescription()
    {
        $paths = array(
            ".//meta[@property='og:description']/@content",
        );
        return $this->xpathScalar($paths);
    }

    public function parsePrice()
    {
        $paths = array(
            ".//meta[@property='og:price:amount']/@content",
            ".//meta[@property='og:price:standard_amount']/@content"
        );
        return $this->xpathScalar($paths);
    }

    public function parseCurrencyCode()
    {
        $paths = array(
            ".//meta[@property='og:price:currency']/@content",
        );
        return $this->xpathScalar($paths);
    }

    public function parseImage()
    {
        $paths = array(
            ".//meta[@property='og:image:secure_url']/@content",
            ".//meta[@property='og:image']/@content",
            ".//meta[@property='og:image:url']/@content",
            ".//*[@property='og:image']/@content",
        );
        return $this->xpathScalar($paths);
    }

    public function parseImages()
    {
        $paths = array(
            ".//meta[@property='og:image']/@content",
        );
        $images = $this->xpathArray($paths);
        if (count($images) > 1)
            return $images;
    }

    protected function parseRatingValue()
    {
        $paths = array(
            ".//meta[@property='og:rating']/@content"
        );
        return $this->xpathScalar($paths);
    }

}
