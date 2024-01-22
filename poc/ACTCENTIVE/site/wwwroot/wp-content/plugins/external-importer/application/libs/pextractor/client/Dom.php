<?php

namespace ExternalImporter\application\libs\pextractor\client;

defined('\ABSPATH') || exit;

/**
 * Dom class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class Dom
{

    private static $html;

    public static function load($html)
    {
        $html = self::decodeCharset($html);

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        libxml_use_internal_errors(true);
        if (!$dom->loadHTML($html))
            throw new \Exception('Can\'t load DOM Document.');

        return $dom;
    }

    public static function createFromUrl($url, array $config = array(), array $httpOptions = array())
    {
        $browser = new Browser();

        $url = \apply_filters('ei_create_from_url', $url, $httpOptions);

        self::$html = $browser->request($url, $config, $httpOptions);
        return self::createFromString(self::$html);
    }

    public static function createFromString($html)
    {
        self::$html = $html;
        return self::load(self::$html);
    }

    public static function getHtml()
    {
        return self::$html;
    }

    public static function decodeCharset($html)
    {
        $encoding_hint = '<?xml encoding="UTF-8">';

        $allowed = $allowed2 = array('UTF-8', 'ISO-8859-1', 'WINDOWS-1252', 'ISO-8859-7', 'EUC-JP');
        $allowed2[] = 'WINDOWS-1255';
        $charset = '';
        $encoding_list = array();

        $regex = '~<meta(?!\s*(?:name|value)\s*=)[^>]*?charset\s*=[\s"\']*([^\s"\'/>]*)~ims';

        if (preg_match($regex, $html, $matches) && in_array(strtoupper($matches[1]), $allowed2))
        {
            $charset = strtoupper($matches[1]);
        }

        if (!$charset)
        {
            $encoding_list = array_merge($encoding_list, $allowed);
            $encoding_list = array_unique($encoding_list);

            $charset = mb_detect_encoding(strip_tags($html), $encoding_list);
        }

        if ($charset && strtoupper($charset) != 'UTF-8')
        {
            $encoding_hint .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
            $html = $encoding_hint . $html;
            $result = iconv($charset, 'UTF-8//TRANSLIT//IGNORE', $html);
        }
        else
            $result = $encoding_hint . $html;

        // This will convert all non-ascii characters into an html named or numeric character entity. 
        return mb_convert_encoding($result, 'HTML-ENTITIES', 'UTF-8');
    }
}
