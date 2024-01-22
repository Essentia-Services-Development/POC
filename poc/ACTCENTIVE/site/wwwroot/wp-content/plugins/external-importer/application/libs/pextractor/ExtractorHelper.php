<?php

namespace ExternalImporter\application\libs\pextractor;

defined('\ABSPATH') || exit;

/**
 * TextHelper class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class ExtractorHelper {

    public static function ratingPrepare($rating, $bestRating = 5, $worstRating = 1)
    {
        if (!$rating)
            return 0;

        $bestRating = abs((int) $bestRating);
        $rating = abs((int) $rating);
        if (!$bestRating || $rating > $bestRating)
        {
            if ($rating > 5 && $rating <= 10)
                $bestRating = 10;
            elseif ($rating > 10 && $rating <= 100)
                $bestRating = 100;
            else
                $bestRating = 5;
        }

        $rating = $rating / ($bestRating / 5);
        $rating = round($rating, 1);
        return $rating;
    }

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

    /**
     * Truncates text.
     * Modified version of cakephp truncate.
     *
     * Cuts a string to the length of $length and replaces the last characters
     * with the ellipsis if the text is longer than length.
     *
     * ### Options:
     *
     * - `ellipsis` Will be used as Ending and appended to the trimmed string (`ending` is deprecated)
     * - `exact` If false, $text will not be cut mid-word
     * - `html` If true, HTML tags would be handled correctly
     *
     * @param string $text String to truncate.
     * @param int $length Length of returned string, including ellipsis.
     * @param array $options An array of html attributes and options.
     * @return string Trimmed string.
     * @link http://book.cakephp.org/2.0/en/core-libraries/helpers/text.html#TextHelper::truncate
     */
    public static function truncateHtml($text, $length = 100, $options = array())
    {
        $defaults = array(
            'ellipsis' => '...', 'exact' => false, 'html' => true
        );
        if (isset($options['ending']))
        {
            $defaults['ellipsis'] = $options['ending'];
        } elseif (!empty($options['html']))
        {
            $defaults['ellipsis'] = "\xe2\x80\xa6";
        }
        $options += $defaults;
        extract($options);

        if (!function_exists('mb_strlen'))
        {
            class_exists('Multibyte');
        }

        if ($html)
        {
            if (mb_strlen(preg_replace('/<.*?>/', '', $text), 'UTF-8') <= $length)
            {
                return $text;
            }
            $totalLength = mb_strlen(strip_tags($ellipsis), 'UTF-8');
            $openTags = array();
            $truncate = '';

            preg_match_all('/(<\/?([\w+]+)[^>]*>)?([^<>]*)/', $text, $tags, PREG_SET_ORDER);
            foreach ($tags as $tag)
            {
                if (!preg_match('/img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param/s', $tag[2]))
                {
                    if (preg_match('/<[\w]+[^>]*>/s', $tag[0]))
                    {
                        array_unshift($openTags, $tag[2]);
                    } elseif (preg_match('/<\/([\w]+)[^>]*>/s', $tag[0], $closeTag))
                    {
                        $pos = array_search($closeTag[1], $openTags);
                        if ($pos !== false)
                        {
                            array_splice($openTags, $pos, 1);
                        }
                    }
                }
                $truncate .= $tag[1];

                $contentLength = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $tag[3]), 'UTF-8');
                if ($contentLength + $totalLength > $length)
                {
                    $left = $length - $totalLength;
                    $entitiesLength = 0;
                    if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $tag[3], $entities, PREG_OFFSET_CAPTURE))
                    {
                        foreach ($entities[0] as $entity)
                        {
                            if ($entity[1] + 1 - $entitiesLength <= $left)
                            {
                                $left--;
                                $entitiesLength += mb_strlen($entity[0], 'UTF-8');
                            } else
                            {
                                break;
                            }
                        }
                    }

                    $truncate .= mb_substr($tag[3], 0, $left + $entitiesLength, 'UTF-8');
                    break;
                } else
                {
                    $truncate .= $tag[3];
                    $totalLength += $contentLength;
                }
                if ($totalLength >= $length)
                {
                    break;
                }
            }
        } else
        {
            if (mb_strlen($text, 'UTF-8') <= $length)
            {
                return $text;
            }
            $truncate = mb_substr($text, 0, $length - mb_strlen($ellipsis, 'UTF-8'), 'UTF-8');
        }
        if (!$exact)
        {
            $spacepos = mb_strrpos($truncate, ' ', 0, 'UTF-8');
            if ($html)
            {
                $truncateCheck = mb_substr($truncate, 0, $spacepos, 'UTF-8');
                $lastOpenTag = mb_strrpos($truncateCheck, '<', 0, 'UTF-8');
                $lastCloseTag = mb_strrpos($truncateCheck, '>', 0, 'UTF-8');
                if ($lastOpenTag > $lastCloseTag)
                {
                    preg_match_all('/<[\w]+[^>]*>/s', $truncate, $lastTagMatches);
                    $lastTag = array_pop($lastTagMatches[0]);
                    $spacepos = mb_strrpos($truncate, $lastTag, 0, 'UTF-8') + mb_strlen($lastTag, 'UTF-8');
                }
                $bits = mb_substr($truncate, $spacepos, null, 'UTF-8');
                preg_match_all('/<\/([a-z]+)>/', $bits, $droppedTags, PREG_SET_ORDER);
                if (!empty($droppedTags))
                {
                    if (!empty($openTags))
                    {
                        foreach ($droppedTags as $closingTag)
                        {
                            if (!in_array($closingTag[1], $openTags))
                            {
                                array_unshift($openTags, $closingTag[1]);
                            }
                        }
                    } else
                    {
                        foreach ($droppedTags as $closingTag)
                        {
                            $openTags[] = $closingTag[1];
                        }
                    }
                }
            }
            $truncate = mb_substr($truncate, 0, $spacepos, 'UTF-8');
        }
        $truncate .= $ellipsis;

        if ($html)
        {
            foreach ($openTags as $tag)
            {
                $truncate .= '</' . $tag . '>';
            }
        }

        return $truncate;
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

    public static function parsePriceAmount($money)
    {
        if (is_float($money) || is_int($money))
            return $money;
        
        if (strstr($money, '-'))
        {
            $parts = explode('-', $money);
            $money = $parts[0];
        }

        $cleanString = preg_replace('/([^0-9\.,])/i', '', $money);
        $onlyNumbersString = preg_replace('/([^0-9])/i', '', $money);
        $separatorsCountToBeErased = strlen($cleanString) - strlen($onlyNumbersString) - 1;
        $stringWithCommaOrDot = preg_replace('/([,\.])/', '', $cleanString, $separatorsCountToBeErased);
        $removedThousendSeparator = preg_replace('/(\.|,)(?=[0-9]{3,}$)/', '', $stringWithCommaOrDot);
        return (float) str_replace(',', '.', $removedThousendSeparator);
    }

    public static function getHostWithSubdomain($url)
    {
        return strtolower(preg_replace('/^www\./', '', parse_url(trim($url), PHP_URL_HOST)));
    }

    public static function getHostName($url)
    {
        return self::getDomainWithoutSubdomain(self::getHostWithSubdomain($url));
    }

    public static function getDomainWithoutSubdomain($domain)
    {
        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,7})$/i', $domain, $regs))
        {
            return $regs['domain'];
        }
        return $domain;
    }

    public static function resolveUrl($url, $base_uri)
    {
        if (!$url)
            return $url;

        if (is_array($url))
            return self::resolveUrls($urls, $base_uri);

        $base_parts = parse_url($base_uri);

        if ($url[0] == '?')
        {
            $r = $base_parts['scheme'] . '://' . $base_parts['host'];
            if (isset($base_parts['path']))
                $r .= $base_parts['path'];
            $r .= $url;
            return $r;
        }

        // convert url to base url
        $base_uri = self::getBaseUri($base_uri);
        if (preg_match('~^//~', $url))
            return $base_parts['scheme'] . ':' . $url;

        if (parse_url($url, PHP_URL_HOST))
            return $url;
        if (!preg_match('~^/~', $url))
            $url = '/' . $url;
        return $base_uri . $url;
    }

    public static function resolveUrls($urls, $base_uri)
    {
        if (!is_array($urls))
            $urls = array($urls);

        foreach ($urls as $i => $url)
        {
            $urls[$i] = self::resolveUrl($url, $base_uri);
        }
        return $urls;
    }

    public static function filterForeignDomains(array $urls, $base_uri)
    {
        $host = self::getHostName($base_uri);
        foreach ($urls as $i => $url)
        {
            if (self::getHostName($url) != $host)
                unset($urls[$i]);
        }
        $urls = array_values($urls);
        return $urls;
    }

    public static function getBaseUri($url)
    {
        $base = parse_url($url);
        return $base['scheme'] . '://' . $base['host'];
    }

    public static function getQueryVar($name, $url)
    {
        if (!$query = parse_url($url, PHP_URL_QUERY))
            return null;

        parse_str($query, $vars);
        if (isset($vars[$name]))
            return $vars[$name];
        else
            return null;
    }

    public static function clearFeature($str)
    {
        //$str = preg_replace("/[^\pL\s\d\-\.\+_\|\(\)\/\:'\",;]+/ui", '', $str);
        $str = \wc_sanitize_term_text_based($str);
        $str = preg_replace("/\s+/ui", ' ', $str);
        return $str;
    }

    public static function encodeNonAscii($url)
    {
        return preg_replace_callback('/[^\x20-\x7f]/', function($match) {
            return urlencode($match[0]);
        }, $url);
    }

}
