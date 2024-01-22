<?php

namespace ExternalImporter\application\helpers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\Product;
use ExternalImporter\application\components\WooImporter;
Use ExternalImporter\application\helpers\TextHelper;

/**
 * WooHelper class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class WooHelper
{

    public static function isWooInstalled()
    {
        if (class_exists('\WooCommerce'))
            return true;
        elseif (in_array('woocommerce/woocommerce.php', \apply_filters('active_plugins', \get_option('active_plugins'))))
            return true;
        else
            return false;
    }

    public static function getCategoryList()
    {
        $terms = \get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => false));

        if (!$terms || \is_wp_error($terms))
            return array();
        $categories = array();
        foreach ($terms as $term)
        {
            $categories[$term->term_id . '.'] = $term->name;
        }
        return $categories;
    }

    public static function createCategory($category)
    {
        if (!is_array($category))
            $category = array($category);

        return self::createNestedCategories($category);
    }

    public static function createNestedCategories(array $categoryPath)
    {
        $parent = 0;
        foreach ($categoryPath as $category)
        {
            $category = \sanitize_text_field($category);

            if (!$ids = \term_exists($category, 'product_cat', $parent))
            {
                $ids = \wp_insert_term($category, 'product_cat', array('parent' => $parent));
                if (\is_wp_error($ids))
                    return false;
            }

            $parent = $ids['term_id'];
        }
        return $parent;
    }

    public static function uploadMedias(array $image_urls, $post_id, $title = '')
    {
        $attach_ids = array();
        foreach ($image_urls as $image_url)
        {
            if ($attach_id = self::uploadMedia($image_url, $post_id, $title))
                $attach_ids[] = $attach_id;
        }
        return $attach_ids;
    }

    public static function uploadMedia($image_url, $post_id, $title = '')
    {
        $check_image_type = \apply_filters('exi_check_image_type', true);

        if (!$file_name = ImageHelper::saveImgLocaly($image_url, $title, $check_image_type))
            return false;

        $uploads = \wp_upload_dir();
        $img_path = ltrim(trailingslashit($uploads['subdir']), '\/') . $file_name;
        $img_file = ImageHelper::getFullImgPath($img_path);

        $img_file = \apply_filters('exi_handle_upload_media', $img_file);

        return self::addMedia($img_file, $post_id, $title);
    }

    public static function addMedia($img_file, $post_id, $title = '')
    {
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $filetype = \wp_check_filetype(basename($img_file), null);
        $attachment = array(
            'guid' => $img_file,
            'post_mime_type' => $filetype['type'],
            'post_title' => $title,
            'post_content' => '',
            'post_status' => 'inherit'
        );

        $attach_id = \wp_insert_attachment($attachment, $img_file, $post_id);

        $attach_data = \wp_generate_attachment_metadata($attach_id, $img_file);
        \wp_update_attachment_metadata($attach_id, $attach_data);
        return $attach_id;
    }

    public static function getCurrencyRate($currencyCode)
    {
        $woo_currency = \get_woocommerce_currency();
        if (!$currencyCode || $currencyCode == $woo_currency)
            return 1;

        if ($currency_rate = CurrencyHelper::getCurrencyRate($currencyCode, $woo_currency))
            return $currency_rate;
        else
            return 1;
    }

    public static function convertPrice($ammount, $currencyCode, $convert = true)
    {
        if (!$convert)
            return $ammount;
        $currency_rate = self::getCurrencyRate($currencyCode);
        $wooCurrency = \get_woocommerce_currency();
        return (float) CurrencyHelper::getInstance()->numberFormat($ammount * $currency_rate, $wooCurrency, $thousand_sep = '', $decimal_sep = '.');
    }

    public static function buildTemplate($template, Product $product)
    {
        if (!$template)
            return '';

        $template = TextHelper::spin($template);

        if (!preg_match_all('/%.+?%/', $template, $matches))
            return $template;

        $replace = array();
        foreach ($matches[0] as $pattern)
        {
            // random
            if (stristr($pattern, '%RANDOM'))
            {
                preg_match('/%RANDOM\((\d+),(\d+)\)%/', $pattern, $rmatches);
                if ($rmatches)
                    $replace[$pattern] = rand((int) $rmatches[1], (int) $rmatches[2]);
                else
                    $replace[$pattern] = rand(0, 9999999);
                continue;
            }

            if (strtoupper($pattern) == '%SOURCE_ID_ALIEXPRESS%')
                $replace[$pattern] = self::parseIdFromUrlAliexpress($product->link);
            elseif (strtoupper($pattern) == '%SOURCE_ID_EBAY%')
                $replace[$pattern] = self::parseIdFromUrlEbay($product->link);
            elseif (strtoupper($pattern) == '%SOURCE_ID_AMAZON%')
                $replace[$pattern] = self::parseIdFromUrlAmazon($product->link);

            // product
            if (stristr($pattern, '%PRODUCT.'))
            {
                $replace[$pattern] = '';
                $pattern_parts = explode('.', $pattern);
                $var_name = $pattern_parts[1];
                $var_name = rtrim($var_name, '%');

                if (strtoupper($var_name) == 'ATTRIBUTE' && isset($pattern_parts[2]))
                {
                    $attribute_name = rtrim($pattern_parts[2], '%');
                    $attribute_name = \sanitize_text_field($attribute_name);
                    foreach ($product->features as $feature)
                    {
                        if ($feature['name'] == $attribute_name)
                        {
                            $replace[$pattern] = $feature['value'];
                            break;
                        }
                    }
                } elseif (property_exists($product, $var_name))
                {
                    if (is_array($product->$var_name) && isset($product->$var_name[0]))
                        $replace[$pattern] = serialize($product->$var_name);
                    else
                        $replace[$pattern] = $product->$var_name;
                }

                continue;
            }
        }

        return str_ireplace(array_keys($replace), array_values($replace), $template);
    }

    public static function isRehubTheme()
    {
        return (in_array(basename(\get_template_directory()), array('rehub', 'rehub-theme'))) ? true : false;
    }

    public static function isEiProduct($product_id)
    {
        return WooImporter::getProductUrlMeta($product_id) ? true : false;
    }

    public static function dateFormatFromGmt($timestamp, $time = true)
    {
        $format = \get_option('date_format');
        if ($time)
            $format .= ' ' . \get_option('time_format');

        $timestamp = strtotime(\get_date_from_gmt(date('Y-m-d H:i:s', $timestamp)));
        return \date_i18n($format, $timestamp);
    }

    public static function dateFormatHuman($timestamp, $time = true)
    {
        $show_date_time = self::dateFormatFromGmt($timestamp, $time);

        if ($timestamp > strtotime('-1 day', \current_time('timestamp', true)))
        {
            $show_date = sprintf(
                    __('%s ago', '%s = human-readable time difference', 'external-importer'), \human_time_diff($timestamp, \current_time('timestamp', true))
            );
        } else
            $show_date = self::dateFormatFromGmt($timestamp, false);

        return sprintf('<abbr datetime="%1$s" title="%2$s">%3$s</abbr>', \esc_attr($show_date_time), \esc_attr($show_date_time), \esc_html($show_date));
    }

    public static function fixJsonBrackets($str)
    {
        $str = trim($str);
        if (!json_decode($str))
            return $str;

        return preg_replace('~^\{(.+)\}$~', '[$1]', $str);
    }

    public static function parseIdFromUrlAliexpress($url)
    {
        $regex = '/aliexpress.+?\/.+?([0-9]{10,})\.html/';
        if (preg_match($regex, $url, $matches))
            return $matches[1];
        else
            return '';
    }

    public static function parseIdFromUrlAmazon($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL))
            return '';

        if (!strstr($url, 'amazon'))
            return '';


        $regex = '~/(?:exec/obidos/ASIN/|o/|gp/product/|gp/offer-listing/|(?:(?:[^"\'/]*)/)?dp/|)(B[0-9]{2}[0-9A-Z]{7}|[0-9]{9}(X|0-9])|[0-9]{10}|B0B[A-Z0-9]{7})(?:(?:/|\?|\#)(?:[^"\'\s]*))?~isx';
        if (preg_match($regex, $url, $matches))
            return $matches[1];
        else
            return '';
    }

    public static function parseIdFromUrlEbay($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL))
            return '';
        $regex = '~\/itm\/(\d+)~';
        if (preg_match($regex, $url, $matches))
            return $matches[1];
        else
            return '';
    }

}
