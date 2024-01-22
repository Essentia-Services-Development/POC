<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * TextHelper class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class TextHelper {

    public static function truncate($string, $length = 80, $etc = '...', $charset = 'UTF-8', $break_words = false, $middle = false)
    {
        if ($length == 0)
            return '';

        if (mb_strlen($string, 'UTF-8') > $length)
        {
            $length -= min($length, mb_strlen($etc, 'UTF-8'));
            if (!$break_words && !$middle)
            {
                $string = preg_replace('/\s+?(\S+)?$/', '', mb_substr($string, 0, $length + 1, $charset));
            }
            if (!$middle)
            {
                return mb_substr($string, 0, $length, $charset) . $etc;
            } else
            {
                return mb_substr($string, 0, $length / 2, $charset) . $etc . mb_substr($string, -$length / 2, $charset);
            }
        } else
        {
            return $string;
        }
    }

    static function rus2latin($str)
    {
        $iso = array(
            "Є" => "YE", "І" => "I", "Ѓ" => "G", "і" => "i", "№" => "#", "є" => "ye", "ѓ" => "g",
            "А" => "A", "Б" => "B", "В" => "V", "Г" => "G", "Д" => "D",
            "Е" => "E", "Ё" => "YO", "Ж" => "ZH",
            "З" => "Z", "И" => "I", "Й" => "J", "К" => "K", "Л" => "L",
            "М" => "M", "Н" => "N", "О" => "O", "П" => "P", "Р" => "R",
            "С" => "S", "Т" => "T", "У" => "U", "Ф" => "F", "Х" => "X",
            "Ц" => "C", "Ч" => "CH", "Ш" => "SH", "Щ" => "SHH", "Ъ" => "'",
            "Ы" => "Y", "Ь" => "", "Э" => "E", "Ю" => "YU", "Я" => "YA",
            "а" => "a", "б" => "b", "в" => "v", "г" => "g", "д" => "d",
            "е" => "e", "ё" => "yo", "ж" => "zh",
            "з" => "z", "и" => "i", "й" => "j", "к" => "k", "л" => "l",
            "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r",
            "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "x",
            "ц" => "c", "ч" => "ch", "ш" => "sh", "щ" => "shh", "ъ" => "",
            "ы" => "y", "ь" => "", "э" => "e", "ю" => "yu", "я" => "ya",
            "'" => "", "\"" => "", " " => "-"
        );

        return strtr($str, $iso);
    }

    public static function clear($str)
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $str);
    }

    public static function clearId($str)
    {
        return preg_replace('/[^a-zA-Z0-9_\-~\.@]/', '', $str);
    }

    public static function clear_utf8($str)
    {
        $str = preg_replace("/[^\pL\s\d\-\.\+_]+/ui", '', $str);
        $str = preg_replace("/\s+/ui", ' ', $str);
        return $str;
    }

    public static function subidClear($str, $lenght = 50)
    {
        $str = self::rus2latin($str);
        $str = preg_replace("/[^\d\w_]/iu", "", $str);
        $str = mb_substr($str, 0, $lenght);
        return $str;
    }

    private static function get_random($matches)
    {
        $rand = array_rand($split = explode("|", $matches[1]));
        return $split[$rand];
    }

    public static function spin($str)
    {
        //$new_str = preg_replace_callback('/\{([^{}]*)\}/uim', array('TextHelper', 'get_random'), $str);
        $new_str = preg_replace_callback(
                '/\{([^{}]*)\}/uim', function ($matches) {
            $rand = array_rand($split = explode("|", $matches[1]));
            return $split[$rand];
        }
                , $str);
        if ($new_str !== $str)
            $str = TextHelper::spin($new_str);
        return $str;
    }

    public static function parsePriceAmount($money)
    {
        if (is_float($money) || is_int($money))
            return $money;
        $cleanString = preg_replace('/([^0-9\.,])/i', '', $money);
        $onlyNumbersString = preg_replace('/([^0-9])/i', '', $money);

        $separatorsCountToBeErased = strlen($cleanString) - strlen($onlyNumbersString) - 1;

        $stringWithCommaOrDot = preg_replace('/([,\.])/', '', $cleanString, $separatorsCountToBeErased);
        $removedThousendSeparator = preg_replace('/(\.|,)(?=[0-9]{3,}$)/', '', $stringWithCommaOrDot);

        return (float) str_replace(',', '.', $removedThousendSeparator);
    }

    public static function commaList($str, $input_delimer = ',', $return_delimer = ',')
    {
        $parts = explode($input_delimer, $str);
        $parts = array_map('trim', $parts);
        return join($return_delimer, $parts);
    }

    public static function commaListArray($str, $input_delimer = ',')
    {
        if (!$str)
            return array();
        $parts = explode($input_delimer, $str);
        $parts = array_map('trim', $parts);
        return $parts;
    }

    public static function ratingPrepare($rating, $min_rating = 1, $max_rating = 5)
    {
        $rating = (float) $rating;
        $rating = abs(round($rating));
        if ($rating < $min_rating || $rating > $max_rating)
            return null;
        else
            return $rating;
    }

    public static function randomPassword($len = 8)
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < $len; $i++)
        {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }

    public static function removeNonUtf8($str)
    {

        return mb_convert_encoding($str, 'UTF-8', 'UTF-8');
//        $regex = '/((?: [\x00-\x7F]|[\xC0-\xDF][\x80-\xBF]|[\xE0-\xEF][\x80-\xBF]{2}|[\xF0-\xF7][\x80-\xBF]{3}){1,100})|./x';
//        return preg_replace($regex, '$1', $str);
    }

    public static function getHostName($url)
    {
        $url = trim($url);
        return TextHelper::getDomainWithoutSubdomain(strtolower(str_ireplace('www.', '', parse_url($url, PHP_URL_HOST))));
    }

    public static function isValidDomainName($domain)
    {
        return preg_match('/^(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/', $domain);
    }

    public static function parseOriginalUrl($url, $go_param)
    {
        if (!$query = parse_url($url, PHP_URL_QUERY))
            return '';
        parse_str($query, $arr);
        if (isset($arr[$go_param]))
            return $arr[$go_param];
        else
            return '';
    }

    public static function parseDomain($url, $go_param)
    {
        if (!$url = TextHelper::parseOriginalUrl($url, $go_param))
            return '';
        return TextHelper::getHostName($url);
    }

    public static function getDomainWithoutSubdomain($domain)
    {
        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,7})$/i', $domain, $regs))
        {
            return $regs['domain'];
        }
        return $domain;
    }

    /**
     * @link: https://stackoverflow.com/questions/17219916/json-decode-returns-json-error-syntax-but-online-formatter-says-the-json-is-ok
     */
    public static function fixHiddenCharacters($ld)
    {
        for ($i = 0; $i <= 31; ++$i)
        {
            $ld = str_replace(chr($i), "", $ld);
        }
        $ld = str_replace(chr(127), "", $ld);

        // This is the most common part
        // Some file begins with 'efbbbf' to mark the beginning of the file. (binary level)
        // here we detect it and we remove it, basically it's the first 3 characters 
        if (0 === strpos(bin2hex($ld), 'efbbbf'))
        {
            $ld = substr($ld, 3);
        }
        return $ld;
    }

    public static function fixFullUrl($url, $base_uri)
    {
        if (!$url)
            return $url;
        $base_parts = parse_url($base_uri);
        if (preg_match('~^//~', $url))
            return $base_parts['scheme'] . ':' . $url;

        if (parse_url($url, PHP_URL_HOST))
            return $url;
        if (!preg_match('~^/~', $url))
            $url = '/' . $url;
        return $base_uri . $url;
    }

    public static function fixFullUrls($urls, $base_uri)
    {
        if (!is_array($urls))
            $urls = array($urls);

        foreach ($urls as $i => $url)
        {
            $urls[$i] = self::fixFullUrl($url, $base_uri);
        }
        return $urls;
    }

    public static function urlHost($url)
    {
        return strtolower(preg_replace('/^www\./', '', parse_url(strtolower(trim($url)), PHP_URL_HOST)));
    }

    public static function sanitizeHtml($string)
    {
        $allowed_html = array(
            'abbr' => array(),
            'br' => array(),
            'blockquote' => array(),
            'cite' => array(),
            'code' => array(),
            'del' => array(),
            'em' => array(),
            'strong' => array(),
            'pre' => array(),
            'i' => array(),
            'p' => array(),
            'b' => array(),
            'ul' => array(),
            'ol' => array(),
            'li' => array(),
            'strike' => array(),
            's' => array(),
            'table' => array(),
            'tr' => array(),
            'td' => array(),
            'th' => array(),
            'div' => array(),
            'h2' => array(),
            'h3' => array(),
        );

        return \wp_kses($string, $allowed_html);
    }

    static public function removeExtraBr($str)
    {
        return preg_replace('#<br[^>]*>(\s*<br[^>]*>)+#', '<br />', $str);
    }

    static public function removeExtraBreaks($str)
    {
        return trim(preg_replace("/(\r\n)+/i", "\r\n", $str));
    }

    static public function getQueryVar($name, $url)
    {
        if (!$query = parse_url($url, PHP_URL_QUERY))
            return null;

        parse_str($query, $vars);
        if (isset($vars[$name]))
            return $vars[$name];
        else
            return null;
    }

}
