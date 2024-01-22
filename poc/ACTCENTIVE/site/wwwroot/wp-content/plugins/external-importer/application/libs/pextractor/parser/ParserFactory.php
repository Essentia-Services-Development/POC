<?php

namespace ExternalImporter\application\libs\pextractor\parser;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\parsers\MicrodataParser;
use ExternalImporter\application\libs\pextractor\parser\parsers\JsonLdParser;
use ExternalImporter\application\libs\pextractor\parser\parsers\RdfaParser;
use ExternalImporter\application\libs\pextractor\parser\parsers\OgpParser;
use ExternalImporter\application\libs\pextractor\parser\parsers\MicroformatsParser;
use ExternalImporter\application\libs\pextractor\parser\parsers\TwitterCardsParser;
use ExternalImporter\application\libs\pextractor\parser\parsers\MagicParser;
use ExternalImporter\application\libs\pextractor\parser\parsers\AeParser;
use ExternalImporter\application\libs\pextractor\parser\advanced\AdvancedManager;
use ExternalImporter\application\libs\pextractor\parser\ParserFormat;
use ExternalImporter\application\libs\pextractor\parser\AeManager;

/**
 * ParserFactory class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class ParserFactory {

    public static $parsers = array(
        ParserFormat::ADVANCED_PARSER => null,
        AeParser::FORMAT => null,
        JsonLdParser::FORMAT => JsonLdParser::class,
        MicrodataParser::FORMAT => MicrodataParser::class,
        RdfaParser::FORMAT => RdfaParser::class,
        OgpParser::FORMAT => OgpParser::class,
        MicroformatsParser::FORMAT => MicroformatsParser::class,
        TwitterCardsParser::FORMAT => TwitterCardsParser::class,
        MagicParser::FORMAT => MagicParser::class,
    );

    public static function createParsers($formats, $url)
    {
        $parsers = array();
        foreach (self::$parsers as $format => $class)
        {
            if ($format & $formats)
            {
                if ($format == ParserFormat::ADVANCED_PARSER && AdvancedManager::getInstance()->isParserExists($url))
                    $parsers[$format] = AdvancedManager::getInstance()->getParserInstance($url);
                elseif ($format == ParserFormat::AE_PARSER && AeManager::getInstance()->isIntegrationPossible() && AeManager::getInstance()->isParserExists($url))
                    $parsers[$format] = new AeParser($url);
                elseif ($class)
                    $parsers[$format] = new $class($url);
            }
        }
        return $parsers;
    }

}
