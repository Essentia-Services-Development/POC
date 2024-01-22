<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * TemplateHelper class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class TemplateHelper {

    const MERHANT_LOGO_DIR = 'ce-logos';

    public static function formatPriceCurrency($price, $currencyCode, $before_symbol = '', $after_symbol = '')
    {
        return CurrencyHelper::getInstance()->currencyFormat($price, $currencyCode, null, null, $before_symbol, $after_symbol);
    }

    public static function currencyTyping($c)
    {
        return CurrencyHelper::getInstance()->getSymbol($c);
    }

    public static function priceFormat($price, $decimals = 2)
    {
        return number_format($price, $decimals, '.', ' ');
    }

    public static function format_datetime($datetime, $type = 'mysql', $separator = ' ')
    {
        if ('mysql' == $type)
        {
            return mysql2date(get_option('date_format'), $datetime) . $separator . mysql2date(get_option('time_format'), $datetime);
        } else
        {
            return date_i18n(get_option('date_format'), $datetime) . $separator . date_i18n(get_option('time_format'), $datetime);
        }
    }

    public static function getMerhantIconUrl(array $item, $blank_on_error = false)
    {
        $prefix = 'icon_';
        if (empty($item['domain']))
            return $blank_on_error ? self::getBlankImg() : false;
        $remote_url = 'http://www.google.com/s2/favicons?domain=' . urlencode($item['domain']);
        return self::getMerchantImageUrl($item, $prefix, $remote_url, $blank_on_error);
    }

    public static function getMerhantLogoUrl(array $item, $blank_on_error = false)
    {
        $prefix = '';
        if (empty($item['domain']))
            return $blank_on_error ? self::getBlankImg() : false;
        $remote_url = 'https://logo.clearbit.com/' . urlencode($item['domain']) . '?size=128';
        return self::getMerchantImageUrl($item, $prefix, $remote_url, $blank_on_error);
    }

    private static function getMerchantImageUrl(array $item, $prefix = '', $remote_url = null, $blank_on_error = false)
    {
        $default_ext = 'png'; // ???

        if (!empty($item['domain']))
            $logo_file_name = $item['domain'];
        elseif (!empty($item['logo']))
            $logo_file_name = md5($item['logo']);
        else
            return $blank_on_error ? self::getBlankImg() : false;

        $logo_file_name = str_replace('.', '-', $logo_file_name);
        $logo_file_name .= '.' . $default_ext;
        $logo_file_name = $prefix . $logo_file_name;

        // check in distrib
        if (file_exists(\Keywordrush\AffiliateEgg\PLUGIN_PATH . 'res/logos/' . $logo_file_name))
            return \Keywordrush\AffiliateEgg\PLUGIN_RES . '/logos/' . $logo_file_name;

        $uploads = \wp_upload_dir();
        if (!$logo_dir = self::getMerchantLogoDir())
            return $blank_on_error ? self::getBlankImg() : false;
        $logo_file = \trailingslashit($logo_dir) . $logo_file_name;
        $logo_url = $uploads['baseurl'] . '/' . self::MERHANT_LOGO_DIR . '/' . $logo_file_name;

        // logo exists
        if (file_exists($logo_file))
            return $logo_url;

        // download
        if (!$remote_url)
            return $blank_on_error ? self::getBlankImg() : false;
        if ($logo_file_name = ImageHelper::downloadImg($remote_url, $logo_dir, $logo_file_name, '', true))
            return $uploads['baseurl'] . '/' . self::MERHANT_LOGO_DIR . '/' . $logo_file_name;
        else
        {
            // save blank to prevent new requests           
            copy(\Keywordrush\AffiliateEgg\PLUGIN_PATH . 'res/img/blank.gif', $logo_file);
            return $blank_on_error ? self::getBlankImg() : false;
        }
    }

    public static function getMerchantLogoDir()
    {
        $uploads = \wp_upload_dir();
        $logo_dir = \trailingslashit($uploads['basedir']) . self::MERHANT_LOGO_DIR;
        if (is_dir($logo_dir))
            return $logo_dir;

        // create
        if (\wp_mkdir_p($logo_dir))
            return $logo_dir;
        else
            return false;
    }

    public static function getBlankImg()
    {
        return \Keywordrush\AffiliateEgg\PLUGIN_RES . '/img/blank.gif';
    }

    public static function getHostName($url)
    {
        return self::getDomainWithoutSubdomain(strtolower(str_ireplace('www.', '', parse_url($url, PHP_URL_HOST))));
    }

    public static function parseDomain($url, $go_param)
    {
        if (!$query = parse_url($url, PHP_URL_QUERY))
            return '';
        parse_str($query, $arr);
        if (isset($arr[$go_param]))
            return self::getHostName($arr[$go_param]);
        else
            return '';
    }

    public static function getDomainWithoutSubdomain($domain)
    {
        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs))
        {
            return $regs['domain'];
        }
        return $domain;
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

    public static function getLastUpdate($product_id)
    {
        if ($product = ProductModel::model()->findByPk($product_id))
            return $product['last_update'];
        else
            return null;
    }

    public static function getLastUpdateFormatted($product_id, $timezone = true, $time = true)
    {
        $format = \get_option('date_format');
        if ($time)
            $format .= ' ' . \get_option('time_format');
        if ($timezone)
            $format .= ' T';
        // local time
        return get_date_from_gmt(self::getLastUpdate($product_id), $format);
    }

    public static function isPriceAlertAllowed($product_id = null)
    {
        return PriceAlert::isPriceAlertAllowed($product_id);
    }

    public static function getCurrencyPos($currency)
    {
        return CurrencyHelper::getInstance()->getCurrencyPos($currency);
    }

    public static function getCurrencySymbol($currency)
    {
        return CurrencyHelper::getInstance()->getSymbol($currency);
    }

    public static function priceHistoryPrices($product_id, $limit = 5)
    {
        $prices = PriceHistoryModel::model()->getLastPrices($product_id, $limit);
        $results = array();
        foreach ($prices as $price)
        {
            $results[] = array(
                'date' => strtotime($price['create_date']),
                'price' => $price['price'],
            );
        }
        return $results;
    }

    public static function priceHistoryMax($product_id)
    {
        if (!$price = PriceHistoryModel::model()->getMaxPrice($product_id))
            return null;
        return array('price' => $price['price'], 'date' => strtotime($price['create_date']));
    }

    public static function priceHistoryMin($product_id)
    {
        if (!$price = PriceHistoryModel::model()->getMinPrice($product_id))
            return null;
        return array('price' => $price['price'], 'date' => strtotime($price['create_date']));
    }

    public static function priceHistorySinceDate($product_id)
    {
        if (!$date = PriceHistoryModel::model()->getFirstDateValue($product_id))
            return null;
        return strtotime($date);
    }

    public static function priceChangesProducts($limit = 5)
    {
        $params = array(
            //'select' => 'DISTINCT unique_id',
            'order' => 'create_date DESC',
            'where' => 'post_id IS NOT NULL',
            'group' => 'unique_id',
            'limit' => $limit,
        );
        $prices = PriceHistoryModel::model()->findAll($params);
        $products = array();
        // find products
        foreach ($prices as $price)
        {
            if ($prod = ContentManager::getProductbyUniqueId($price['unique_id'], $price['module_id'], $price['post_id']))
                $products[] = $prod;
        }
        return $products;
    }

    public static function priceHistoryMorrisChart($product_id, $days = 180, array $options = array(), $htmlOptions = array())
    {
        $where = PriceHistoryModel::model()->prepareWhere(
                (array('product_id = %d', array($product_id))), false);
        $params = array(
            'select' => 'date(create_date) as date, price as price',
            'where' => $where . ' AND TIMESTAMPDIFF( DAY, create_date, "' . current_time('mysql') . '") <= ' . $days,
            'group' => 'date',
            'order' => 'date DESC'
        );
        $prices = PriceHistoryModel::model()->findAll($params);

        $data = array(
            'chartType' => 'Area',
            'data' => $prices,
            'xkey' => 'date',
            'ykeys' => array('price'),
            'labels' => array(__('Price', 'affegg-tpl')),
        );
        $options = array_merge($data, $options);
        $id = $product_id . '-chart';
        self::viewMorrisChart($id, $options, $htmlOptions);
    }

    public static function viewMorrisChart($id, array $options, $htmlOptions = array('style' => 'height: 250px;'))
    {
        // morris.js
        \wp_enqueue_style('morrisjs');
        \wp_enqueue_script('morrisjs');

        if (!empty($options['chartType']) && in_array($options['chartType'], array('Line', 'Area', 'Donut', 'Bar')))
        {
            $chartType = $options['chartType'];
            unset($options['chartType']);
        } else
            $chartType = 'Line';
        $options['element'] = $id;

        $html_attr = '';
        foreach ($htmlOptions as $name => $value)
        {
            $html_attr .= ' ' . esc_attr($name) . '="' . esc_attr($value) . '"';
        }

        echo '<div id="' . esc_attr($id) . '"' . $html_attr . '></div>
        <script>
        jQuery(document).ready(function($) {
            new Morris.' . $chartType . '(' . json_encode($options) . ');
                });
        </script>';
    }

    public static function printRating(array $item, $size = 'default')
    {
        if (empty($item['extra']['rating']))
            return;
        if (!in_array($size, array('small', 'big', 'default')))
            $size = 'default';

        $rating = $item['extra']['rating'] * 20;
        echo '<span class="egg-stars-container egg-stars-' . $size . ' egg-stars-' . $rating . '">★★★★★</span>';
    }

    public static function adjustBrightness($hexCode, $adjustPercent)
    {
        $hexCode = ltrim($hexCode, '#');

        if (strlen($hexCode) == 3)
            $hexCode = $hexCode[0] . $hexCode[0] . $hexCode[1] . $hexCode[1] . $hexCode[2] . $hexCode[2];

        $hexCode = array_map('hexdec', str_split($hexCode, 2));

        foreach ($hexCode as & $color)
        {
            $adjustableLimit = $adjustPercent < 0 ? $color : 255 - $color;
            $adjustAmount = ceil($adjustableLimit * $adjustPercent);

            $color = str_pad(dechex($color + $adjustAmount), 2, '0', STR_PAD_LEFT);
        }

        return '#' . implode($hexCode);
    }

    public static function dateFormatFromGmt($timestamp, $time = true)
    {
        $format = \get_option('date_format');
        if ($time)
            $format .= ' ' . \get_option('time_format');

        // last update date stored in gmt, convert into local time
        $timestamp = strtotime(\get_date_from_gmt(date('Y-m-d H:i:s', $timestamp)));
        return \date_i18n($format, $timestamp);
    }

    private static function replacePatterns($template, array $item)
    {
        if (!$item)
            return $template;
        if (!preg_match_all('/%[a-zA-Z0-9_\.\,\(\)]+%/', $template, $matches))
            return $template;

        $replace = array();
        foreach ($matches[0] as $pattern)
        {
            if (stristr($pattern, '%PRICE%'))
            {
                if (!empty($item['price']) && !empty($item['currency']))
                    $replace[$pattern] = TemplateHelper::formatPriceCurrency($item['price'], $item['currency']);
                else
                    $replace[$pattern] = '';
                continue;
            }

            if (stristr($pattern, '%DOMAIN%'))
            {
                if (!empty($item['domain']))
                    $replace[$pattern] = $item['domain'];
                else
                    $replace[$pattern] = '';
                continue;
            }
        }
        return str_ireplace(array_keys($replace), array_values($replace), $template);
    }

    public static function btnText($option_name, $default, $print = true, array $item = array(), $forced_text = '')
    {
        if ($forced_text)
            $text = $forced_text;
        else
        {
            $text = GeneralConfig::getInstance()->option($option_name);
            if (!$text)
                $text = $default;
        }

        $text = \esc_attr(self::replacePatterns($text, $item));

        if (!$print)
            return $text;

        echo $text;
    }

    public static function buyNowBtnText($print = true, array $item = array(), $forced_text = '')
    {
        return self::btnText('btn_text_buy_now', __('Buy Now', 'affegg-tpl'), $print, $item, $forced_text);
    }

}
