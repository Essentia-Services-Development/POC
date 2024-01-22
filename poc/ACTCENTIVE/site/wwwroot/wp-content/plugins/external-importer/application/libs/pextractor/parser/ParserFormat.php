<?php

namespace ExternalImporter\application\libs\pextractor\parser;

defined('\ABSPATH') || exit;

/**
 * Format class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2019 keywordrush.com
 */
class ParserFormat {

    const ADVANCED_PARSER = 1;
    const AE_PARSER = 2;
    const JSON_LD = 4;
    const MICRODATA = 8;
    const RDFA = 16;
    const OGP = 32;
    const MICROFORMATS = 64;
    const TWITTER_CARDS = 128;
    const MAGIC_PARSER = 256;
    const ALL_LISTING = self::ADVANCED_PARSER | self::AE_PARSER | self::JSON_LD | self::MAGIC_PARSER;
    const ALL_PRODUCT = self::ADVANCED_PARSER | self::AE_PARSER | self::JSON_LD | self::MICRODATA | self::RDFA | self::OGP | self::MICROFORMATS | self::TWITTER_CARDS | self::MAGIC_PARSER;
    const ALL = self::ADVANCED_PARSER | self::AE_PARSER | self::JSON_LD | self::MICRODATA | self::RDFA | self::OGP | self::MICROFORMATS | self::TWITTER_CARDS | self::MAGIC_PARSER;
    const ALL_PRODUCT_AUTO = self::JSON_LD | self::MICRODATA | self::RDFA | self::OGP | self::MICROFORMATS | self::TWITTER_CARDS | self::MAGIC_PARSER;
    const ALL_LISTING_AUTO = self::MAGIC_PARSER | self::JSON_LD;

}
