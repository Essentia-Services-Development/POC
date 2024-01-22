<?php

namespace ContentEgg\application\helpers;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\ContentManager;
use ContentEgg\application\models\PriceHistoryModel;
use ContentEgg\application\helpers\ArrayHelper;
use ContentEgg\application\admin\GeneralConfig;
use ContentEgg\application\components\ModuleManager;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\libs\amazon\AmazonLocales;
use ContentEgg\application\Translator;

/**
 * TemplateHelper class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 *
 */
class TemplateHelper
{

    const MERHANT_LOGO_DIR = 'ce-logos';
    const IMG_SMALL = 'small';
    const IMG_MEDIUM = 'medium';
    const IMG_LARGE = 'large';
    const IMG_ORIGINAL = 'original';

    static $global_id = 0;
    static $logos = null;
    static $shop_info = null;
    static $merchnat_info = null;

    public static function formatPriceCurrency($price, $currencyCode, $before_symbol = '', $after_symbol = '')
    {
        $decimal_sep = __('number_format_decimal_point', 'content-egg-tpl');
        $thousand_sep = __('number_format_thousands_sep', 'content-egg-tpl');
        if ($decimal_sep == 'number_format_decimal_point')
        {
            $decimal_sep = null;
        }
        if ($thousand_sep == 'number_format_thousands_sep')
        {
            $thousand_sep = null;
        }

        return CurrencyHelper::getInstance()->currencyFormat($price, $currencyCode, $thousand_sep, $decimal_sep, $before_symbol, $after_symbol);
    }

    public static function currencyTyping($c)
    {
        return CurrencyHelper::getInstance()->getSymbol($c);
    }

    /*
     * @deprecated
     */

    public static function number_format_i18n($number, $decimals = 0, $currency = null)
    {
        $decimal_sep = __('number_format_decimal_point', 'content-egg-tpl');
        $thousand_sep = __('number_format_thousands_sep', 'content-egg-tpl');
        if ($decimal_sep == 'number_format_decimal_point')
        {
            $decimal_sep = null;
        }
        if ($thousand_sep == 'number_format_thousands_sep')
        {
            $thousand_sep = null;
        }

        return CurrencyHelper::getInstance()->numberFormat($number, $currency, $thousand_sep, $decimal_sep, $decimals);
    }

    /*
     * @deprecated
     */

    public static function price_format_i18n($number, $currency = null)
    {
        return self::number_format_i18n($number, $decimal = null, $currency);
    }

    public static function truncate($string, $length = 80, $etc = '...', $charset = 'UTF-8', $break_words = false, $middle = false)
    {
        if ($length == 0)
        {
            return '';
        }

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
            }
            else
            {
                return mb_substr($string, 0, $length / 2, $charset) . $etc . mb_substr($string, -$length / 2, $charset);
            }
        }
        else
        {
            return $string;
        }
    }

    static public function getTimeLeft($end_time_gmt, $return_array = false)
    {
        $current_time = strtotime(gmdate("M d Y H:i:s"));
        $timeleft = strtotime($end_time_gmt) - $current_time;
        if ($timeleft < 0)
        {
            return '';
        }

        $days_left = floor($timeleft / 86400);
        $hours_left = floor(($timeleft - $days_left * 86400) / 3600);
        $min_left = floor(($timeleft - $days_left * 86400 - $hours_left * 3600) / 60);
        if ($return_array)
        {
            return array(
                'days' => $days_left,
                'hours' => $hours_left,
                'min' => $min_left,
            );
        }

        if ($days_left)
        {
            return $days_left . __('d', 'content-egg-tpl') . ' ';
        }
        elseif ($hours_left)
        {
            return $hours_left . __('h', 'content-egg-tpl') . ' ';
        }
        elseif ($min_left)
        {
            return $min_left . __('m', 'content-egg-tpl');
        }
        else
        {
            return '<1' . __('m', 'content-egg-tpl');
        }
    }

    public static function filterData($data, $field_name, $field_values, $extra = false, $inverse = false)
    {
        $results = array();
        foreach ($data as $key => $d)
        {
            if ($extra)
            {
                if (!isset($d['extra']) || !isset($d['extra'][$field_name]))
                {
                    continue;
                }
                $value = $d['extra'][$field_name];
            }
            else
            {
                if (!isset($d[$field_name]))
                {
                    continue;
                }
                $value = $d[$field_name];
            }
            if (!is_array($field_values))
            {
                $field_values = array($field_values);
            }

            if (!$inverse && in_array($value, $field_values))
            {
                $results[$key] = $d;
            }
            elseif ($inverse && !in_array($value, $field_values))
            {
                $results[$key] = $d;
            }
        }

        return $results;
    }

    public static function formatDatetime($datetime, $type = 'mysql', $separator = ' ')
    {
        if ('mysql' == $type)
        {
            return mysql2date(get_option('date_format'), $datetime) . $separator . mysql2date(get_option('time_format'), $datetime);
        }
        else
        {
            return date_i18n(get_option('date_format'), $datetime) . $separator . date_i18n(get_option('time_format'), $datetime);
        }
    }

    public static function formatDate($timestamp, $gmt = false)
    {
        return date_i18n(get_option('date_format'), $timestamp, $gmt);
    }

    public static function splitAttributeName($attribute)
    {
        return trim(preg_replace('/([A-Z])([a-z])/', ' $1$2', $attribute));
    }

    public static function getAmazonLink($itemLinks, $description)
    {
        // api 5 fix
        if (!is_array($itemLinks) || !$itemLinks)
        {
            return '';
        }

        foreach ($itemLinks as $link)
        {
            if ($link['Description'] == $description)
            {
                return $link['URL'];
            }
        }

        return false;
    }

    public static function getLastUpdate($module_id, $post_id = null)
    {
        if (!$post_id)
        {
            global $post;
            $post_id = $post->ID;
        }

        $res = \get_post_meta($post_id, ContentManager::META_PREFIX_LAST_ITEMS_UPDATE . $module_id, true);
        $res2 = \get_post_meta($post_id, ContentManager::META_PREFIX_LAST_BYKEYWORD_UPDATE . $module_id, true);

        if ($res2 && $res2 > $res)
            $res = $res2;

        if (!$res)
            $res = time();

        return $res;
    }

    public static function dateFormatFromGmt($timestamp, $time = true)
    {
        $format = \get_option('date_format');
        if ($time)
        {
            $format .= ' ' . \get_option('time_format');
        }

        // last update date stored in gmt, convert into local time
        $timestamp = strtotime(\get_date_from_gmt(date('Y-m-d H:i:s', $timestamp)));

        return \date_i18n($format, $timestamp);
    }

    public static function getLastUpdateFormattedAmazon(array $data, $time = true)
    {
        if (isset($data['Amazon']))
        {
            $item = current($data['Amazon']);
        }
        elseif (isset($data['AmazonNoApi']))
        {
            $item = current($data['AmazonNoApi']);
        }
        else
        {
            return false;
        }

        if (empty($item['last_update']))
        {
            return false;
        }

        $last_update = $item['last_update'];

        return self::dateFormatFromGmt($last_update, $time);
    }

    public static function getLastUpdateFormatted($module_id, $post_id = null, $time = true)
    {
        if (!$post_id || $post_id === true) // $post_id === true - fix func params...
        {
            global $post;
            $post_id = $post->ID;
        }

        $last_update = self::getLastUpdate($module_id, $post_id);

        return self::dateFormatFromGmt($last_update, $time);
    }

    public static function filterDataByType($data, $type)
    {
        $results = array();
        foreach ($data as $module_id => $items)
        {
            $module = \ContentEgg\application\components\ModuleManager::getInstance()->factory($module_id);
            if ($module->getParserType() == $type)
            {
                $results[$module_id] = $items;
            }
        }

        return $results;
    }

    public static function filterDataByModule($data, $module_ids)
    {
        if (!is_array($module_ids))
        {
            $module_ids = array($module_ids);
        }
        $results = array();

        foreach ($data as $module_id => $items)
        {
            if (in_array($module_id, $module_ids))
            {
                $results[$module_id] = $items;
            }
        }

        return $results;
    }

    public static function priceHistoryPrices($unique_id, $plugin_id, $limit = 5)
    {
        $prices = PriceHistoryModel::model()->getLastPrices($unique_id, $plugin_id, $limit);
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

    public static function priceHistoryMax($unique_id, $module_id)
    {
        if (!$price = PriceHistoryModel::model()->getMaxPrice($unique_id, $module_id))
        {
            return null;
        }

        return array('price' => $price['price'], 'date' => strtotime($price['create_date']));
    }

    public static function priceHistoryMin($unique_id, $module_id)
    {
        if (!$price = PriceHistoryModel::model()->getMinPrice($unique_id, $module_id))
        {
            return null;
        }

        return array('price' => $price['price'], 'date' => strtotime($price['create_date']));
    }

    public static function priceHistorySinceDate($unique_id, $module_id)
    {
        if (!$date = PriceHistoryModel::model()->getFirstDateValue($unique_id, $module_id))
        {
            return null;
        }

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
            {
                $products[] = $prod;
            }
        }

        return $products;
    }

    public static function priceHistoryMorrisChart($unique_id, $module_id, $days = 180, array $options = array(), $htmlOptions = array())
    {
        $where = PriceHistoryModel::model()->prepareWhere(
            (array('unique_id = %s AND module_id = %s', array($unique_id, $module_id))),
            false
        );
        $params = array(
            'select' => 'date(create_date) as date, price as price',
            'where' => $where . ' AND TIMESTAMPDIFF( DAY, create_date, "' . \current_time('mysql') . '") <= ' . $days,
            //'group' => 'date',
            'order' => 'date ASC'
        );
        $results = PriceHistoryModel::model()->findAll($params);
        $results = array_reverse($results);
        $prices = array();
        /**
         * php fix for selecting non-aggregate columns
         * @see: https://stackoverflow.com/questions/1066453/mysql-group-by-and-order-by
         */
        foreach ($results as $key => $r)
        {
            if ($key > 0 && $results[$key - 1]['date'] == $r['date'])
            {
                continue;
            }
            $price = array(
                'date' => $r['date'],
                'price' => $r['price'],
            );
            $prices[] = $price;
        }

        //add last known price to the chart
        /*
          $price = array(
          'date' => $r['date'],
          'price' => $r['price'],
          );
          $prices[] = $price;
         *
         */
        $data = array(
            'chartType' => 'Area',
            'data' => $prices,
            'xkey' => 'date',
            'ykeys' => array('price'),
            'labels' => array(Translator::__('Price')),
        );
        $options = array_merge($data, $options);

        $id = $module_id . '-' . $unique_id . '-chart' . rand(0, 10000);
        self::viewMorrisChart($id, $options, $htmlOptions);
    }

    public static function viewMorrisChart($id, array $options, $htmlOptions = array('style' => 'height: 250px;'))
    {
        // morris.js
        \wp_enqueue_style('morrisjs');
        \wp_enqueue_script('morrisjs');

        if (!empty($options['chartType']) && in_array($options['chartType'], array(
            'Line',
            'Area',
            'Donut',
            'Bar'
        )))
        {
            $chartType = $options['chartType'];
            unset($options['chartType']);
        }
        else
        {
            $chartType = 'Line';
        }
        $options['element'] = $id;

        $html_attr = '';
        foreach ($htmlOptions as $name => $value)
        {
            $html_attr .= ' ' . esc_attr($name) . '="' . esc_attr($value) . '"';
        }


        echo '<div style="direction: ltr;" id="' . esc_attr($id) . '"' . $html_attr . '></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '<script>';
        echo 'jQuery(document).ready(function($) {';
        echo 'new Morris.' . esc_html($chartType) . '(' . json_encode($options) . ')';
        echo '})';
        echo '</script>';
    }

    public static function isPriceAlertAllowed($unique_id = null, $module_id = null)
    {
        return \ContentEgg\application\PriceAlert::isPriceAlertAllowed($unique_id, $module_id);
    }

    public static function getCurrencyPos($currency)
    {
        return CurrencyHelper::getInstance()->getCurrencyPos($currency);
    }

    public static function getCurrencySymbol($currency)
    {
        return CurrencyHelper::getInstance()->getSymbol($currency);
    }

    public static function getCurrencyName($currency)
    {
        return CurrencyHelper::getInstance()->getName($currency);
    }

    public static function getCustomLogo($domain)
    {
        if (self::$logos === null)
        {
            $logos = GeneralConfig::getInstance()->option('logos');
            if (!$logos)
            {
                $logos = array();
            }
            foreach ($logos as $logo)
            {
                self::$logos[$logo['name']] = $logo['value'];
            }
        }

        if (isset(self::$logos[$domain]))
        {
            return self::$logos[$domain];
        }
        else
        {
            return false;
        }
    }

    private static function getMerchantImageUrl(array $item, $prefix = '', $remote_url = null, $blank_on_error = false)
    {
        $default_ext = 'png'; // ???

        if (!strpos($remote_url, 'www.google.com/s2/favicons?domain'))
        {
            // custom logos for Offer module
            if (isset($item['module_id']) && $item['module_id'] == 'Offer' && !empty($item['logo']))
            {
                return $item['logo'];
            }

            // custom logos
            if (isset($item['domain']) && $custom_logo = self::getCustomLogo($item['domain']))
            {
                return $custom_logo;
            }
        }

        if (!empty($item['domain']))
        {
            $logo_file_name = $item['domain'];
        }
        elseif (!empty($item['logo']))
        {
            $logo_file_name = md5($item['logo']);
        }
        else
        {
            return $blank_on_error ? self::getBlankImg() : false;
        }

        $logo_file_name = str_replace('.', '-', $logo_file_name);
        $logo_file_name .= '.' . $default_ext;
        $logo_file_name = $prefix . $logo_file_name;

        // check in distrib
        if (file_exists(\ContentEgg\PLUGIN_PATH . 'res/logos/' . $logo_file_name))
        {
            return \ContentEgg\PLUGIN_RES . '/logos/' . $logo_file_name;
        }

        $uploads = \wp_upload_dir();
        if (!$logo_dir = self::getMerchantLogoDir())
        {
            return $blank_on_error ? self::getBlankImg() : false;
        }
        $logo_file = \trailingslashit($logo_dir) . $logo_file_name;
        $logo_url = $uploads['baseurl'] . '/' . self::MERHANT_LOGO_DIR . '/' . $logo_file_name;

        // logo exists
        if (file_exists($logo_file))
        {
            return $logo_url;
        }

        // download
        if (!$remote_url)
        {
            return $blank_on_error ? self::getBlankImg() : false;
        }
        if ($logo_file_name = ImageHelper::downloadImg($remote_url, $logo_dir, $logo_file_name, '', true))
        {
            return $uploads['baseurl'] . '/' . self::MERHANT_LOGO_DIR . '/' . $logo_file_name;
        }
        else
        {
            // save blank to prevent new requests
            copy(\ContentEgg\PLUGIN_PATH . 'res/img/blank.gif', $logo_file);

            return $blank_on_error ? self::getBlankImg() : false;
        }
    }

    public static function getMerhantLogoUrl(array $item, $blank_on_error = false)
    {
        $prefix = '';
        if (!empty($item['module_id']))
        {
            $parser = ModuleManager::getInstance()->parserFactory($item['module_id']);
            if ($parser->getConfigInstance()->option_exists('show_large_logos') && !filter_var($parser->config('show_large_logos'), FILTER_VALIDATE_BOOLEAN))
            {
                return $blank_on_error ? self::getBlankImg() : false;
            }
        }

        if (!empty($item['logo']))
        {
            $remote_url = $item['logo'];
        }
        elseif (!empty($item['domain']))
        {
            $item['domain'] = preg_replace('/^https:\/\//', '', $item['domain']);
            $remote_url = 'https://logo.clearbit.com/' . urlencode($item['domain']) . '?size=128';
        }
        else
        {
            $remote_url = '';
        }

        return self::getMerchantImageUrl($item, $prefix, $remote_url, $blank_on_error);
    }

    public static function getMerhantIconUrl(array $item, $blank_on_error = false)
    {
        $prefix = 'icon_';
        if (!empty($item['module_id']))
        {
            $parser = ModuleManager::getInstance()->parserFactory($item['module_id']);
            if ($parser->getConfigInstance()->option_exists('show_small_logos') && !filter_var($parser->config('show_small_logos'), FILTER_VALIDATE_BOOLEAN))
            {
                return $blank_on_error ? self::getBlankImg() : false;
            }
        }

        $item['domain'] = preg_replace('/^https:\/\//', '', $item['domain']);
        $remote_url = 'https://t2.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=https://' . urlencode($item['domain']) . '&size=16';

        return self::getMerchantImageUrl($item, $prefix, $remote_url, $blank_on_error);
    }

    public static function getMerchantName(array $item, $print = false)
    {
        return self::getMerhantName($item, $print);
    }

    public static function getMerhantName(array $item, $print = false)
    {
        if (!empty($item['domain']))
        {
            $name = ucfirst($item['domain']);

            if ($name == 'Aliexpress.com')
            {
                $name = 'Aliexpress';
            }
            elseif ($name == 'Flipkart.com')
            {
                $name = 'Flipkart';
            }
            elseif ($name == 'Ebay.com')
            {
                $name = 'eBay';
            } //it's should be ONLY "eBay" without ".com"
            elseif (strstr($name, 'Ebay.'))
            {
                $name = $name = 'eBay';
            }
        }
        elseif (!empty($item['merchant']))
        {
            $name = $item['merchant'];
        }
        else
        {
            $name = '';
        }

        if ($print)
        {
            echo \esc_html($name);
        }
        else
        {
            return $name;
        }
    }

    public static function getMerchantLogoDir()
    {
        $uploads = \wp_upload_dir();
        $logo_dir = \trailingslashit($uploads['basedir']) . self::MERHANT_LOGO_DIR;
        if (is_dir($logo_dir))
        {
            return $logo_dir;
        }

        if (\wp_mkdir_p($logo_dir))
        {
            return $logo_dir;
        }
        else
        {
            return false;
        }
    }

    public static function getBlankImg()
    {
        return \ContentEgg\PLUGIN_RES . '/img/blank.gif';
    }

    public static function mergeData(array $data)
    {
        foreach ($data as $module_id => $items)
        {
            foreach ($items as $item_ar)
            {
                $item_ar['module_id'] = $module_id;
                $all_items[] = $item_ar;
            }
        }

        return $all_items;
    }

    public static function getMaxPriceItem(array $data)
    {
        if (!$data)
            return false;

        return $data[ArrayHelper::getMaxKeyAssoc($data, 'price', true)];
    }

    public static function getMinPriceItem(array $data)
    {
        if (!$data)
            return false;

        return $data[ArrayHelper::getMinKeyAssoc($data, 'price', true)];
    }

    public static function getCommonCurrencyCode($data)
    {
        $first = reset($data);
        $currency = $first['currencyCode'];
        foreach ($data as $d)
        {
            if (!empty($d['currencyCode']) && $d['currencyCode'] != $currency)
            {
                return false;
            }
        }

        return $currency;
    }

    public static function getShopsList($data)
    {
        $list = array();
        foreach ($data as $d)
        {
            if (!isset($list[$d['domain']]))
            {
                if (!empty($d['merchant']))
                {
                    $list[$d['domain']] = $d['merchant'];
                }
                else
                {
                    $list[$d['domain']] = self::getNameFromDomain($d['domain']);
                }
            }
        }

        return $list;
    }

    public static function getNameFromDomain($domain)
    {
        $parts = explode('.', $domain);
        $merchant = $parts[0];
        if ($merchant == 'ebay')
        {
            return 'eBay';
        }

        return ucfirst($merchant);
    }

    public static function sortByPrice(array $data, $order = 'asc', $field = 'price')
    {
        if (!in_array($order, array('asc', 'desc')))
        {
            $order = 'asc';
        }

        if (!in_array($field, array('price', 'discount')))
        {
            $field = 'price';
        }

        // convert all prices to one currency
        $currency_codes = array();
        foreach ($data as $d)
        {
            if (empty($d['currencyCode']))
            {
                continue;
            }

            if (!isset($currency_codes[$d['currencyCode']]))
            {
                $currency_codes[$d['currencyCode']] = 1;
            }
            else
            {
                $currency_codes[$d['currencyCode']]++;
            }
        }
        arsort($currency_codes);
        $base_currency = key($currency_codes);
        foreach ($data as $key => $d)
        {
            $rate = 1;
            if (!empty($d['currencyCode']) && $d['currencyCode'] != $base_currency)
            {
                $rate = CurrencyHelper::getCurrencyRate($d['currencyCode'], $base_currency);
            }
            if (!$rate)
            {
                $rate = 1;
            }

            if (isset($d['price']))
            {
                if ($field == 'discount')
                {
                    if (!empty($d['priceOld']))
                    {
                        $data[$key]['converted_price'] = (float) ($d['priceOld'] - $d['price']) * $rate;
                    }
                    else
                    {
                        $data[$key]['converted_price'] = 0.00001;
                    }
                }
                else
                {
                    $data[$key]['converted_price'] = (float) $d['price'] * $rate;
                }
            }
            else
            {
                $data[$key]['converted_price'] = 0;
                $data[$key]['price'] = 0;
                if ($field == 'discount')
                {
                    $data[$key]['converted_price'] = 99999999999;
                }
            }
            if (isset($d['stock_status']) && $d['stock_status'] == ContentProduct::STOCK_STATUS_OUT_OF_STOCK)
            {
                if ($field == 'discount')
                {
                    $data[$key]['converted_price'] = -1;
                }
                else
                {
                    $data[$key]['converted_price'] = 0;
                }
            }
        }

        // modules priority
        $modules_priority = array();
        foreach ($data as $d)
        {
            $module_id = $d['module_id'];

            if (isset($modules_priority[$module_id]))
            {
                continue;
            }
            if (!ModuleManager::getInstance()->moduleExists($module_id))
            {
                continue;
            }

            $module = ModuleManager::getInstance()->factory($module_id);
            $modules_priority[$module_id] = (int) $module->config('priority');
        }

        // sort by price and priority
        usort($data, function ($a, $b) use ($modules_priority)
        {

            if (!$a['price'] && !$b['price'])
            {
                return $modules_priority[$a['module_id']] - $modules_priority[$b['module_id']];
            }

            if (!$a['converted_price'])
            {
                return 1;
            }

            if (!$b['converted_price'])
            {
                return -1;
            }

            if ($a['converted_price'] == $b['converted_price'])
            {
                return $modules_priority[$a['module_id']] - $modules_priority[$b['module_id']];
            }

            if ($modules_priority[$a['module_id']] != $modules_priority[$b['module_id']])
            {
                if ($a['converted_price'] >= 30 && $b['converted_price'] >= 30 && abs($a['converted_price'] - $b['converted_price']) < 1)
                {
                    return $modules_priority[$a['module_id']] - $modules_priority[$b['module_id']];
                }
            }

            return ($a['converted_price'] < $b['converted_price']) ? -1 : 1;
        });

        if ($order == 'desc')
        {
            $data = array_reverse($data);
        }

        return $data;
    }

    public static function sortAllByPrice(array $data, $order = 'asc', $field = 'price')
    {
        return TemplateHelper::sortByPrice(self::mergeAll($data), $order, $field);
    }

    public static function mergeAll(array $data)
    {
        $all_items = array();
        foreach ($data as $module_id => $items)
        {
            foreach ($items as $item_ar)
            {
                $item_ar['module_id'] = $module_id;
                $all_items[] = $item_ar;
            }
        }

        return $all_items;
    }

    public static function buyNowBtnText($print = true, array $item = array(), $forced_text = '')
    {
        return self::btnText('btn_text_buy_now', __('BUY NOW', 'content-egg-tpl'), $print, $item, $forced_text);
    }

    public static function couponBtnText($print = true, array $item = array(), $forced_text = '')
    {
        return self::btnText('btn_text_coupon', __('Shop Sale', 'content-egg-tpl'), $print, $item, $forced_text);
    }

    public static function getCurrentUserEmail()
    {
        if (!$current_user = wp_get_current_user())
        {
            return '';
        }

        return $current_user->user_email;
    }

    public static function getDaysAgo($ptime)
    {
        $etime = current_time('timestamp') - $ptime;
        if ($etime < 1)
        {
            return '';
        }
        $d = $etime / (24 * 60 * 60);

        if ($d < 1)
        {
            return Translator::__('today');
        }
        $d = ceil($d);

        if ($d > 1)
        {
            return sprintf(Translator::__('%d days ago'), $d);
        }
        else
        {
            return sprintf(Translator::__('%d day ago'), $d);
        }
    }

    public static function getAmazonDisclaimer()
    {
        if ($d = GeneralConfig::getInstance()->option('disclaimer_text'))
        {
            return $d;
        }
        else
        {
            return __('As an Amazon associate I earn from qualifying purchases.', 'content-egg-tpl') . ' ' . __('Product prices and availability are accurate as of the date/time indicated and are subject to change. Any price and availability information displayed on Amazon at the time of purchase will apply to the purchase of this product.', 'content-egg-tpl');
        }
    }

    public static function printAmazonDisclaimer()
    {
        echo '<i class="egg-ico-info-circle cegg-disclaimer" ' . self::buildTagParams(array('title' => self::getAmazonDisclaimer())) . '></i>'; // phpcs:ignore
    }

    public static function btnText($option_name, $default, $print = true, array $item = array(), $forced_text = '')
    {
        if ($forced_text)
        {
            $text = $forced_text;
        }
        else
        {
            $text = GeneralConfig::getInstance()->option($option_name);
            if (!$text)
            {
                $text = $default;
            }
        }

        $text = self::replacePatterns($text, $item);

        if (!$print)
        {
            return $text;
        }

        echo \esc_attr($text);
    }

    private static function replacePatterns($template, array $item)
    {
        if (!$item)
        {
            return $template;
        }
        if (!preg_match_all('/%[a-zA-Z0-9_\.\,\(\)]+%/', $template, $matches))
        {
            return $template;
        }

        $replace = array();
        foreach ($matches[0] as $pattern)
        {
            if (stristr($pattern, '%PRICE%'))
            {
                if (!empty($item['price']) && $item['currencyCode'])
                {
                    $replace[$pattern] = TemplateHelper::formatPriceCurrency($item['price'], $item['currencyCode']);
                }
                else
                {
                    $replace[$pattern] = '';
                }
                continue;
            }
            if (stristr($pattern, '%MERCHANT%'))
            {
                if ($merchant = TemplateHelper::getMerhantName($item))
                {
                    $replace[$pattern] = $merchant;
                }
                else
                {
                    $replace[$pattern] = '';
                }
                continue;
            }
            if (stristr($pattern, '%DOMAIN%'))
            {
                if (!empty($item['domain']))
                {
                    $replace[$pattern] = $item['domain'];
                }
                else
                {
                    $replace[$pattern] = TemplateHelper::getMerhantName($item);
                }
                continue;
            }
            if (stristr($pattern, '%STOCK_STATUS%'))
            {
                $replace[$pattern] = TemplateHelper::getStockStatusStr($item);
                continue;
            }
        }

        return str_ireplace(array_keys($replace), array_values($replace), $template);
    }

    public static function getStockStatusClass(array $item)
    {
        if (!isset($item['stock_status']))
        {
            return '';
        }

        if ($item['stock_status'] == ContentProduct::STOCK_STATUS_IN_STOCK)
        {
            return 'instock';
        }
        elseif ($item['stock_status'] == ContentProduct::STOCK_STATUS_OUT_OF_STOCK)
        {
            return 'outofstock';
        }
        elseif ($item['stock_status'] == ContentProduct::STOCK_STATUS_UNKNOWN)
        {
            return 'unknown';
        }
        else
        {
            return '';
        }
    }

    public static function getStockStatusStr(array $item)
    {
        if (!isset($item['stock_status']))
        {
            return '';
        }

        $show_status = GeneralConfig::getInstance()->option('show_stock_status');
        if ($show_status == 'hide_status')
        {
            return '';
        }
        elseif ($show_status == 'show_outofstock' && $item['stock_status'] == ContentProduct::STOCK_STATUS_IN_STOCK)
        {
            return '';
        }
        elseif ($show_status == 'show_instock' && $item['stock_status'] == ContentProduct::STOCK_STATUS_OUT_OF_STOCK)
        {
            return '';
        }

        if ($item['stock_status'] == ContentProduct::STOCK_STATUS_IN_STOCK)
        {
            return TemplateHelper::__('in stock');
        }
        elseif ($item['stock_status'] == ContentProduct::STOCK_STATUS_OUT_OF_STOCK)
        {
            return TemplateHelper::__('out of stock');
        }
        else
        {
            return '';
        }
    }

    public static function getPrivacyUrl()
    {
        if ($id = \get_option('wp_page_for_privacy_policy', ''))
        {
            return \get_permalink($id);
        }
        else
        {
            return '';
        }
    }

    public static function getGroupsList(array $all_items, $sort_groups = array())
    {
        if (!isset($all_items[0]))
        {
            $all_items = TemplateHelper::sortAllByPrice($all_items);
        }

        $groups = array_unique(array_column($all_items, 'group'));
        $groups = array_filter($groups);
        $groups = array_values($groups);

        if ($sort_groups)
        {
            $res = array();
            foreach ($sort_groups as $g)
            {
                if (in_array($g, $groups))
                {
                    $res[] = $g;
                }
            }

            return $res;
        }
        else
        {
            natsort($groups);

            return $groups;
        }
    }

    public static function filterByGroup(array $data, $group)
    {
        $res = array();
        foreach ($data as $plugin_id => $d)
        {
            $r = array_filter($d, function ($data) use ($group)
            {
                return isset($data) && $data['group'] == $group;
            });
            if ($r)
            {
                $res[$plugin_id] = $r;
            }
        }

        return $res;
    }

    public static function generateGlobalId($prefix)
    {
        return $prefix . self::$global_id++;
    }

    public static function isModuleDataExist($items, $module_id)
    {
        foreach ($items as $item)
        {
            if (isset($item['module_id']) && $item['module_id'] == $module_id)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }

    public static function isCashbackTrakerActive()
    {
        if (class_exists('\CashbackTracker\application\Plugin'))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public static function getCashbackStr(array $product)
    {
        if (GeneralConfig::getInstance()->option('cashback_integration') != 'enabled')
        {
            return '';
        }

        if (!self::isCashbackTrakerActive())
        {
            return '';
        }

        return \CashbackTracker\application\components\DeeplinkGenerator::getCashbackStrByUrl($product['url']);
    }

    public static function hideParamPrepare($hide)
    {
        if (!$hide)
        {
            return array();
        }

        $allowed_hide = array(
            'price',
            'priceOld',
            'domain',
            'rating',
            'title',
            'stock_status',
            'img',
            'merchant',
            'description'
        );
        $hide = TextHelper::getArrayFromCommaList($hide);
        if (in_array('price', $hide) && !in_array('priceOld', $hide))
        {
            $hide[] = 'priceOld';
        }

        return array_intersect($hide, $allowed_hide);
    }

    public static function printRel($echo = true)
    {
        if (!$rel = self::getRelValue())
        {
            return;
        }

        $res = ' rel="' . \esc_attr($rel) . '"';
        if ($echo)
        {
            echo $res; // phpcs:ignore
        }
        else
        {
            return $res;
        }
    }

    public static function getRelValue()
    {
        $rel = GeneralConfig::getInstance()->option('rel_attribute');

        return join(' ', $rel);
    }

    public static function printRating(array $item, $size = 'default')
    {
        if (!$item['rating'])
        {
            return;
        }
        if (!in_array($size, array('small', 'big', 'default')))
        {
            $size = 'default';
        }

        $rating = $item['rating'] * 20;
        echo '<span class="egg-stars-container egg-stars-' . esc_attr($size) . ' egg-stars-' . esc_attr($rating) . '">★★★★★</span>';
    }

    public static function getButtonColor()
    {
        if (!$color = \wp_strip_all_tags(GeneralConfig::getInstance()->option('button_color')))
        {
            $color = '#d9534f';
        }

        return $color;
    }

    public static function getPriceColor()
    {
        if (!$color = \wp_strip_all_tags(GeneralConfig::getInstance()->option('price_color')))
        {
            $color = '#dc3545';
        }

        return $color;
    }

    public static function getButtonColorHower()
    {
        return TemplateHelper::adjustBrightness(TemplateHelper::getButtonColor(), -0.15);
    }

    public static function adjustBrightness($hexCode, $adjustPercent)
    {
        $hexCode = ltrim($hexCode, '#');

        if (strlen($hexCode) == 3)
        {
            $hexCode = $hexCode[0] . $hexCode[0] . $hexCode[1] . $hexCode[1] . $hexCode[2] . $hexCode[2];
        }

        $hexCode = array_map('hexdec', str_split($hexCode, 2));

        foreach ($hexCode as &$color)
        {
            $adjustableLimit = $adjustPercent < 0 ? $color : 255 - $color;
            $adjustAmount = ceil($adjustableLimit * $adjustPercent);

            $color = str_pad(dechex($color + $adjustAmount), 2, '0', STR_PAD_LEFT);
        }

        return '#' . implode($hexCode);
    }

    public static function findAmazonLocales(array $items)
    {
        $locales = array();
        foreach ($items as $item)
        {
            if (!isset($item['extra']['locale']))
            {
                continue;
            }
            if (!in_array($item['extra']['locale'], $locales))
            {
                $locales[] = $item['extra']['locale'];
            }
        }

        return $locales;
    }

    /*
     * @link: https://webservices.amazon.com/paapi5/documentation/add-to-cart-form.html
     */

    public static function generateAddAllToCartUrl(array $items, $locale)
    {
        $url = 'https://www.' . AmazonLocales::getDomain($locale) . '/gp/aws/cart/add.html?';

        $i = 1;
        foreach ($items as $item)
        {
            if (!isset($item['extra']['locale']) || $item['extra']['locale'] != $locale)
            {
                continue;
            }

            if ($i > 1)
            {
                $url .= '&';
            }

            $url .= 'ASIN.' . $i . '=' . $item['extra']['ASIN'] . '&Quantity.' . $i . '=1';
            $i++;
        }

        $url .= '&AssociateTag=' . self::getAssociateTagForAmazonLocale($locale, $item['module_id']);

        return $url;
    }

    public static function getAssociateTagForAmazonLocale($locale, $module_id = 'Amazon')
    {
        if ($module_id == 'AmazonNoApi')
        {
            $module = ModuleManager::factory('AmazonNoApi');
        }
        else
        {
            $module = ModuleManager::factory('Amazon');
        }

        return $module->getAssociateTagForLocale($locale);
    }

    public static function __($str)
    {
        return Translator::translate($str);
    }

    public static function esc_html_e($str)
    {
        echo esc_html(Translator::translate($str));
    }

    public static function displayImage(array $item, $max_width, $max_height, array $params = array())
    {
        if (!isset($item['img']))
        {
            return;
        }

        $params['src'] = self::getOptimizedImage($item, $max_width, $max_height);

        if (!empty($item['title']))
        {
            $params['alt'] = $item['title'];
        }
        elseif (!empty($item['_alt']))
        {
            $params['alt'] = $item['_alt'];
        }
        if ($sizes = self::getImageSizesRatio($item, $max_width, $max_height))
        {
            $params = array_merge($params, $sizes);
        }

        echo '<img ' . self::buildTagParams($params) . ' />'; // phpcs:ignore
    }

    public static function buildTagParams($params = array())
    {
        $res = '';
        $i = 0;
        foreach ($params as $key => $value)
        {
            if ($i > 0)
            {
                $res .= ' ';
            }
            $res .= \esc_attr($key) . '="' . \esc_attr($value) . '"';
            $i++;
        }

        return $res;
    }

    public static function getImageSizesRatio(array $item, $max_width, $max_height)
    {
        if ($item['module_id'] == 'Amazon' && strpos($item['img'], 'https://m.media-amazon.com') !== false)
        {
            if (!isset($item['extra']['primaryImages']))
            {
                return array();
            }

            $width = $item['extra']['primaryImages']['Large']['Width'];
            $height = $item['extra']['primaryImages']['Large']['Height'];

            if (!$max_width)
            {
                $max_width = $width;
            }
            if (!$max_height)
            {
                $max_height = $height;
            }

            $ratio = $width / $height;

            if ($ratio > 1 && $width > $max_width)
            {
                return array('width' => round($max_width), 'height' => round($max_width / $ratio));
            }
            else
            {
                return array('width' => round($max_height * $ratio), 'height' => round($max_height));
            }
        }

        return array();
    }

    public static function getOptimizedImage(array $item, $max_width, $max_height)
    {

        if ($item['module_id'] == 'Amazon' && strpos($item['img'], 'https://m.media-amazon.com') !== false)
        {
            if (!isset($item['extra']['primaryImages']))
            {
                return $item['img'];
            }

            if ($max_height <= 160)
            {
                return $item['extra']['primaryImages']['Medium']['URL'];
            }
            elseif ($max_height <= 75)
            {
                return $item['extra']['primaryImages']['Small']['URL'];
            }
            else
            {
                return $item['img'];
            }
        }

        return $item['img'];
    }

    public static function generateStaticRatings($count, $post_id = null)
    {
        if (!$post_id)
        {
            global $post;
            if (!empty($post->ID))
            {
                $post_id = $post->ID;
            }
            else
            {
                $post_id = $count;
            }
        }

        $ratings = array();
        mt_srand($post_id);
        $rating = 10;
        for ($i = 0; $i < $count; $i++)
        {
            if ($i <= 3)
            {
                $rand = mt_rand(0, 6) / 10;
            }
            elseif ($count > 9 && $i > 4)
            {
                $rand = mt_rand(0, 3) / 10;
            }
            elseif ($i > 8)
            {
                $rand = mt_rand(0, 4) / 10;
            }
            else
            {
                $rand = mt_rand(0, 10) / 10;
            }

            $rating = round($rating - $rand, 2);
            $ratings[] = $rating;
        }

        return $ratings;
    }

    public static function printProgressRing($value)
    {
        if ($value <= 0)
        {
            return;
        }

        $p = round($value * 100 / 10);
        $r1 = round($p * 314 / 100);
        $r2 = 314 - $r1;

        echo '<svg width="75" height="75" viewBox="0 0 120 120"><circle cx="60" cy="60" r="50" fill="none" stroke="#E1E1E1" stroke-width="12"/><circle cx="60" cy="60" r="50" transform="rotate(-90 60 60)" fill="none" stroke-dashoffset="314" stroke-dasharray="314"  stroke="dodgerblue" stroke-width="12" ><animate attributeName="stroke-dasharray" dur="3s" values="0,314;' . esc_attr($r1) . ',' . esc_attr($r2) . '" fill="freeze" /></circle><text x="60" y="63" fill="black" text-anchor="middle" dy="7" font-size="27">' . esc_html($value) . '</text></svg>';
    }

    public static function getChance($position, $max = 1)
    {
        global $post;
        if (!empty($post->ID))
        {
            $post_id = $post->ID;
        }
        else
        {
            $post_id = time();
        }
        mt_srand($post_id + $position);

        return mt_rand(0, 1);
    }

    public static function getShopInfo(array $item)
    {
        if (!isset($item['domain']))
        {
            return;
        }

        $domain = $item['domain'];

        if (self::$shop_info === null)
        {
            $merchants = GeneralConfig::getInstance()->option('merchants');
            if (!$merchants)
            {
                $merchants = array();
            }
            foreach ($merchants as $merchant)
            {
                self::$shop_info[$merchant['name']] = $merchant['shop_info'];
            }
        }

        if (isset(self::$shop_info[$domain]))
        {
            return self::$shop_info[$domain];
        }
        else
        {
            return '';
        }
    }

    public static function printShopInfo(array $item, array $p = array(), $text = '')
    {
        if (!$shop_info = self::getShopInfo($item))
        {
            return;
        }

        $params = array(
            'data-toggle' => 'cegg-popover',
            'data-html' => 'true',
            'data-placement' => 'left',
            'data-title' => self::getMerhantName($item),
            'data-content' => $shop_info,
            'tabindex' => '0',
            'data-trigger' => 'focus',
        );

        $params = array_merge($params, $p);

        self::displayInfoIcon($params, $text);
    }

    public static function displayInfoIcon($params = array(), $text = '')
    {
        echo '<i class="egg-ico-info-circle" ' . self::buildTagParams($params) . '>'; // phpcs:ignore
        if ($text)
            echo ' <small style="cursor: pointer;">' . esc_html($text) . '</small>';
        echo '</i>';
    }

    public static function getMerchnatInfo(array $item)
    {
        if (!isset($item['domain']))
        {
            return array();
        }

        $domain = $item['domain'];

        if (self::$merchnat_info === null)
        {
            $merchants = GeneralConfig::getInstance()->option('merchants');
            if (!$merchants)
            {
                $merchants = array();
            }
            foreach ($merchants as $merchant)
            {
                self::$merchnat_info[$merchant['name']] = $merchant;
            }
        }

        if (isset(self::$merchnat_info[$domain]))
        {
            return self::$merchnat_info[$domain];
        }
        else
        {
            return array();
        }
    }

    public static function t($s)
    {
        return Translator::__($s);
    }

    public static function selectItemByDescription(array $items)
    {
        $min_len = 999999;
        $selected = null;
        foreach ($items as $item)
        {
            if (!$item['description'])
                continue;

            if (mb_strlen($item['description'], 'UTF-8') < $min_len)
            {
                $min_len = mb_strlen($item['description'], 'UTF-8');
                $selected = $item;
            }
        }
        if (!$selected)
            return reset($items);

        return $selected;
    }
}
