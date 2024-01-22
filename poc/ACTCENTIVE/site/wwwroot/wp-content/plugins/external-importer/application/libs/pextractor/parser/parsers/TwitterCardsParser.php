<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\AbstractParser;
use ExternalImporter\application\libs\pextractor\parser\ParserFormat;

/**
 * TwitterCardsParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2019 keywordrush.com
 */
class TwitterCardsParser extends AbstractParser {

    const FORMAT = ParserFormat::TWITTER_CARDS;

    public function parseTitle()
    {
        $paths = array(
            ".//meta[@name='twitter:title']/@content",
        );
        return $this->xpathScalar($paths);
    }

    public function parseDescription()
    {
        $paths = array(
            ".//meta[@property='twitter:description']/@content",
        );
        return $this->xpathScalar($paths);
    }

    public function parseImage()
    {
        $paths = array(
            ".//meta[@name='twitter:image']/@content",
        );
        return $this->xpathScalar($paths);
    }

}
