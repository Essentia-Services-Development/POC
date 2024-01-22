<?php

namespace ContentEgg\application;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\ContentManager;
use ContentEgg\application\components\FeaturedImage;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\helpers\CurrencyHelper;
use ContentEgg\application\admin\GeneralConfig;
use ContentEgg\application\components\ModuleManager;
use ContentEgg\application\helpers\TemplateHelper;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\Translator;

/**
 * WooIntegrator class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class WooIntegrator {

    const META_WOO_SYNC_MODULE_UNIQUE_ID = '_cegg_woo_sync_muid';

    private static $attributes_list;

    public static function initAction()
    {
        if (!class_exists('\WooCommerce'))
            return;

        \add_action('woocommerce_before_single_product', array(__CLASS__, 'touchEmptyPost'), 10);
        \add_action('content_egg_save_data', array(__CLASS__, 'wooHandler'), 13, 4);

        if (GeneralConfig::getInstance()->option('woocommerce_echo_update_date'))
            \add_action('woocommerce_single_product_summary', array(__CLASS__, 'echoUpdateDate'), 25);

        if (GeneralConfig::getInstance()->option('woocommerce_echo_price_per_unit'))
            \add_action('woocommerce_single_product_summary', array(__CLASS__, 'echoPricePerUnit'), 20);

        if (GeneralConfig::getInstance()->option('woocommerce_btn_text'))
        {
            \add_filter('woocommerce_product_single_add_to_cart_text', array(__CLASS__, 'customButtonText'), 10, 2);
            \add_filter('woocommerce_product_add_to_cart_text', array(__CLASS__, 'customButtonText'), 10, 2);
        }
    }

    public static function touchEmptyPost()
    {
        global $post;
        if (!$post->post_content)
            $post->post_content = apply_filters('the_content', $post->post_content);
    }

    public static function wooHandler($data, $module_id, $post_id, $is_last_iteration)
    {
        if (\get_post_type($post_id) != 'product' || !$product = \wc_get_product($post_id))
            return;

        if (!$is_last_iteration)
            return;

        // Get all post data
        $affiliate_modules = ModuleManager::getInstance()->getAffiliteModulesList(true);
        $modules_data = array();
        foreach ($affiliate_modules as $module_id => $module_name)
        {
            if (!$data = ContentManager::getViewData($module_id, $post_id))
                continue;
            $modules_data[$module_id] = $data;
        }

        // Check for Manual sync
        $is_product_sync = false;
        $is_attr_sync = false;
        foreach ($modules_data as $module_id => $data)
        {
            foreach ($data as $item)
            {
                if (!$is_product_sync && !empty($item['woo_sync']))
                {
                    self::wooSync($item, $module_id, $post_id);
                    $is_product_sync = true;
                }

                if (!$is_attr_sync && !empty($item['woo_attr']))
                {
                    self::wooCreateAttr($item, $module_id, $post_id);
                    $is_attr_sync == true;
                }

                if ($is_product_sync && $is_attr_sync)
                    return;
            }
        }

        if ($is_product_sync)
            return;

        /**
         * Automatic sync
         */
        $woocommerce_product_sync = GeneralConfig::getInstance()->option('woocommerce_product_sync');
        $woocommerce_modules = GeneralConfig::getInstance()->option('woocommerce_modules');
        if (!$woocommerce_modules || $woocommerce_product_sync == 'manually')
            return;

        $modules_data = array_intersect_key($modules_data, $woocommerce_modules);
        if ($item = ContentManager::getMainProduct($modules_data, $woocommerce_product_sync))
        {
            self::wooSync($item, $item['module_id'], $post_id);

            // also sync attr if not exist
            if (!$product->get_attributes() && GeneralConfig::getInstance()->option('woocommerce_attributes_sync'))
                self::wooCreateAttr($item, $item['module_id'], $post_id);
        }
    }

    public static function wooSync(array $item, $module_id, $post_id)
    {
        if (!$product = \wc_get_product($post_id))
            return false;

        $item = \apply_filters('cegg_before_woo_sync', $item, $module_id, $post_id);

        // set price
        if (!empty($item['price']))
        {
            $currency_rate = 1;
            $woo_currency = \get_woocommerce_currency();
            if ($item['currencyCode'] && $item['currencyCode'] != $woo_currency)
            {
                $currency_rate = CurrencyHelper::getCurrencyRate($item['currencyCode'], $woo_currency);
                if (!$currency_rate)
                    $currency_rate = 1;
            }

            $product->set_price($item['price'] * $currency_rate);
            if ($item['priceOld'])
            {
                if (\apply_filters('cegg_dont_sync_sale_price', false))
                {
                    $product->set_regular_price($item['price'] * $currency_rate);
                } else
                {
                    if (!\apply_filters('cegg_dont_touch_retail_price', false))
                        $product->set_regular_price($item['priceOld'] * $currency_rate);

                    if (!\apply_filters('cegg_dont_touch_sale_price', false))
                        $product->set_sale_price($item['price'] * $currency_rate);
                }
            } else
            {
                /*
                 * If my initial import from Products filled in all the correct Retail values, 
                 * I don’t want them touched.  Retail should never really be changed regardless 
                 * of the new lowest price.
                 */
                if ($product->get_regular_price() && \apply_filters('cegg_dont_touch_retail_price', false))
                {
                    $product->set_sale_price($item['price'] * $currency_rate);
                } else
                {
                    $product->set_regular_price($item['price'] * $currency_rate);
                    if (!\apply_filters('cegg_dont_touch_sale_price', false))
                        $product->set_sale_price(null);
                }
            }
        } else
        {
            $product->set_price(null);
            $product->set_regular_price(null);
            $product->set_sale_price(null);
        }

        // External products cannot be stock managed.
        if ($product->get_type() == 'simple' && isset($item['stock_status']))
        {
            if ($item['stock_status'] == ContentProduct::STOCK_STATUS_OUT_OF_STOCK)
                $product->set_stock_status('outofstock');
            else
                $product->set_stock_status('instock');
        }

        if ($item['description'] && !$product->get_description() && !\apply_filters('cegg_disable_description_sync', false))
        {
            if (\apply_filters('cegg_sync_full_description', false))
                $product->set_description($item['description']);
            elseif (!$product->get_short_description() && !\apply_filters('cegg_dont_touch_short_description', false))
                $product->set_short_description($item['description']);
        }

        $product->set_date_modified(time());

        // image
        FeaturedImage::doAction($post_id, $item);

        if ($product->get_type() == 'external' && \apply_filters('cegg_sync_woo_url_allowed', true))
            $product->set_product_url($item['url']);

        // update meta
        self::setMetaSyncUniqueId($post_id, $module_id, $item['unique_id']);

        return $product->save();
    }

    public static function wooCreateAttr(array $item, $module_id, $post_id)
    {
        if (empty($item['features']) || !is_array($item['features']))
            return;

        if (!$product = \wc_get_product($post_id))
            return false;

        $attributes = $product->get_attributes();
        $registered_taxonomy_count = 0;
        $taxonomy_count = 0;
        foreach ($item['features'] as $feature)
        {
            $feature['name'] = \wc_clean($feature['name']);

            if (!\apply_filters('cegg_disable_attributes_modification', false))
            {
                $prepared = self::modifyAttribute($feature['name'], $feature['value']);
                $feature['name'] = $prepared['name'];
                $feature['value'] = $prepared['value'];
            }

            $f_name = \wc_clean($feature['name']);
            $f_slug = self::getSlug($f_name);

            // exists?
            if (isset($attributes[$f_slug]) || isset($attributes['pa_' . $f_slug]))
                continue;

            $f_value = \wc_sanitize_term_text_based($feature['value']);
            $term_ids = array();
            $taxonomy = '';
            $prepared = self::isTaxonomyAttribute($f_name, $f_value, $f_slug);
            if ($prepared)
            {
                $taxonomy_count++;
                $f_name = \wc_clean($prepared['name']);

                // Taxonomy Attribute
                // @see: class-wc-admin-attributes.php -> process_add_attribute() 
                $attr_data = array(
                    'attribute_label' => $f_name,
                    'attribute_name' => $f_slug,
                    'attribute_type' => 'text',
                );

                $attr_id = self::createTaxonomyAttribute($attr_data);

                if ($attr_id)
                {
                    $taxonomy = \wc_attribute_taxonomy_name_by_id($attr_id);

                    // Register the taxonomy now so that the import works!
                    if (!\taxonomy_exists($taxonomy))
                    {
                        $taxonomy = TextHelper::truncate($taxonomy, 32, '', 'UTF-8', true);
                        \register_taxonomy(
                                $taxonomy, apply_filters('woocommerce_taxonomy_objects_' . $taxonomy, array('product')), apply_filters('woocommerce_taxonomy_args_' . $taxonomy, array(
                            'hierarchical' => true,
                            'show_ui' => false,
                            'query_var' => true,
                            'rewrite' => false,
                                ))
                        );
                        $registered_taxonomy_count++;
                    }

                    $f_value_array = self::value2Array($f_value);

                    // Creates the term and taxonomy relationship if it doesn't already exist.                    
                    // It may be confusing but the returned array consists of term_taxonomy_ids instead of term_ids.
                    \wp_set_object_terms($product->get_id(), $f_value_array, $taxonomy);

                    $term_ids = array();
                    foreach ($f_value_array as $term)
                    {
                        if ($term_info = \term_exists($term, $taxonomy))
                            $term_ids[] = $term_info['term_id'];
                    }
                    $term_ids = array_map('intval', $term_ids);
                } else
                    $attr_id = 0;
            } else
            {
                // Local Attribute
                $attr_id = 0;
                $f_value_array = self::value2Array($f_value);
            }

            $attribute = new \WC_Product_Attribute();
            $attribute->set_id($attr_id); // 0 for product level attributes. ID for global attributes.
            if ($taxonomy)
                $attribute->set_name($taxonomy);
            else
                $attribute->set_name($f_name);
            // attribute value or array of term ids/names.
            if ($term_ids)
                $attribute->set_options($term_ids);
            else
                $attribute->set_options($f_value_array);
            $attribute->set_visible(true); // If visible on frontend.
            $attributes[] = $attribute;
        }

        $product->set_attributes($attributes);
        $res = $product->save();

        if ($taxonomy_count)
            \flush_rewrite_rules();
        return $res;
    }

    public static function createTaxonomyAttribute($attribute)
    {
        global $wpdb;

        if (empty($attribute['attribute_label']))
            return false;

        $attribute['attribute_label'] = \wc_clean($attribute['attribute_label']);

        if (empty($attribute['attribute_name']))
            $attribute['attribute_name'] = \wc_sanitize_taxonomy_name($attribute['attribute_label']);

        if (empty($attribute['attribute_type']))
            $attribute['attribute_type'] = 'text';

        if (empty($attribute['attribute_orderby']))
            $attribute['attribute_orderby'] = 'menu_order';

        // validate slug
        if (strlen($attribute['attribute_name']) >= 28 || \wc_check_if_attribute_name_is_reserved($attribute['attribute_name']))
            return false;

        if (\taxonomy_exists(\wc_attribute_taxonomy_name($attribute['attribute_name'])))
            return \wc_attribute_taxonomy_id_by_name($attribute['attribute_name']);

        // Create the taxonomy
        $insert = $wpdb->insert($wpdb->prefix . 'woocommerce_attribute_taxonomies', $attribute);

        if (\is_wp_error($insert))
            return false;

        $id = $wpdb->insert_id;

        //\do_action('woocommerce_attribute_added', $id, $attribute);

        \wp_schedule_single_event(time(), 'woocommerce_flush_rewrite_rules');
        \delete_transient('wc_attribute_taxonomies');

        return $id;
    }

    public static function getAttributesList()
    {
        if (self::$attributes_list === null)
        {
            $attributes_list = GeneralConfig::getInstance()->option('woocommerce_attributes_list');
            $attributes_list = explode(',', $attributes_list);
            $attributes_list = array_map('trim', $attributes_list);
            $attributes_list = array_map('mb_strtolower', $attributes_list);
            $attributes_list = array_filter($attributes_list);
            self::$attributes_list = $attributes_list;
        }
        return self::$attributes_list;
    }

    public static function modifyAttribute($name, $value, $slug = '')
    {
        $ignore_names = array('model', 'wifi', 'version');
        foreach ($ignore_names as $in)
        {
            if (mb_stristr($name, $in, true, 'utf-8') !== false)
                return array('name' => $name, 'value' => $value);
        }

        /**
         * Modifiers
         */
        // 16 M; 8 GB; 1.5 GB; 8MP; 30 fps; 2 m; 0.5600 kg; 6,37 кг        
        if (preg_match('/^([0-9]*[.,]?[0-9]+)[\s+]?([\p{L}]+)$/u', $value, $matches))
        {
            $name = $name . ' (' . $matches[2] . ')';
            $value = $matches[1];
        }
        return array('name' => $name, 'value' => $value);
    }

    public static function value2Array($value)
    {
        // arrays
        $list = preg_split('/[,|;\/]\s/', $value);
        $list = array_map('trim', $list);
        $list = array_map('\wc_sanitize_term_text_based', $list);
        return $list;
    }

    public static function isTaxonomyAttribute($name, $value, $slug = '')
    {
        /**
         *  Black / white list filter
         */
        $attributes_filter = GeneralConfig::getInstance()->option('woocommerce_attributes_filter');
        if ($attributes_filter)
        {
            if (in_array(mb_strtolower($name, 'utf-8'), self::getAttributesList()) || ($slug && in_array($slug, self::getAttributesList())))
                $in_list = true;
            else
                $in_list = false;

            if ($attributes_filter == 'whitelist' && $in_list)
                return array('name' => $name, 'value' => $value);
            elseif ($attributes_filter == 'blacklist' && !$in_list)
                return array('name' => $name, 'value' => $value);
            else
                return false;
        }

        /**
         * Default filter
         */
        // ignore names
        $ignore_names = array('depth', 'height', 'weight', 'package', 'model', 'pack of', 'warranty', 'title', 'of items', 'ean', 'department', 'dimensions', 'network type');
        foreach ($ignore_names as $in)
        {
            if (mb_stristr($name, $in, true, 'utf-8') !== false)
                return false;
        }

        if (strstr($name, '(') && strstr($name, ')') && is_numeric($value))
        {
            return array('name' => $name, 'value' => $value);
        }

        // Yes/No/0/1
        $yes_array = array('yes');
        foreach ($yes_array as $yes)
        {
            if (mb_strtolower($value, 'utf-8') == $yes)
                return array('name' => $name, 'value' => ucfirst($value));
        }
        $no_array = array('no', '0', '-');
        foreach ($no_array as $no)
        {
            if (mb_strtolower($value, 'utf-8') == $no)
                return false;
        }

        // arrays
        $list = preg_split('/[,|;\/]\s/', $value);
        if (count($list) > 1 && count($list) <= 5)
        {
            foreach ($list as $l)
            {
                if (mb_strlen($l, 'utf-8') > 20)
                    return false;
            }
            return array('name' => $name, 'value' => $value);
        }

        // short string value
        if (mb_strlen($value, 'utf-8') < 20)
            return array('name' => $name, 'value' => $value);

        return false;
    }

    public static function getSlug($name)
    {
        // already exists?
        $taxonomies = \wc_get_attribute_taxonomies();
        foreach ($taxonomies as $taxonomie)
        {
            if ($taxonomie->attribute_label == $name)
                return $taxonomie->attribute_name;
        }

        $slug = strtolower(TextHelper::sluggable($name));
        if (strlen($slug) >= 28)
            $slug = substr($slug, 0, 27);
        $slug = \wc_sanitize_taxonomy_name($slug);
        return $slug;
    }

    public static function setMetaSyncUniqueId($post_id, $module_id, $unique_id)
    {
        if (!$unique_id)
            \delete_post_meta($post_id, self::META_WOO_SYNC_MODULE_UNIQUE_ID);
        else
            \update_post_meta($post_id, self::META_WOO_SYNC_MODULE_UNIQUE_ID, $module_id . '[&]' . $unique_id);
    }

    public static function getMetaSyncUniqueId($post_id)
    {
        return \get_post_meta($post_id, self::META_WOO_SYNC_MODULE_UNIQUE_ID, true);
    }

    public static function getSyncItem($post_id)
    {
        if (!$muids = self::getMetaSyncUniqueId($post_id))
            return null;

        $ids = explode('[&]', $muids);
        if (count($ids) != 2)
            return null;

        $module_id = $ids[0];
        $unique_id = $ids[1];

        $item = ContentManager::getProductbyUniqueId($unique_id, $module_id, $post_id);
        if ($item)
            $item['module_id'] = $module_id;
        return $item;
    }

    public static function echoUpdateDate()
    {
        global $post;

        if (!$echo_update_date = GeneralConfig::getInstance()->option('woocommerce_echo_update_date'))
            return;

        if (\is_product() && $post->ID)
        {
            if (!$item = self::getSyncItem($post->ID))
                return;

            if ($echo_update_date == 'amazon' && ($item['module_id'] != 'Amazon' && !strstr($item['module_id'], 'AE__amazon')))
                return;

            if (empty($item['last_update']))
                return;


            $date = TemplateHelper::dateFormatFromGmt($item['last_update'], true);

            echo '<span class="price_updated">';
            echo esc_html(sprintf(Translator::__('Last updated on %s'), $date));
            echo '</span>';
        }
    }

    public static function echoPricePerUnit()
    {
        global $post;

        if (!$woocommerce_echo_price_per_unit = GeneralConfig::getInstance()->option('woocommerce_echo_price_per_unit'))
            return;

        if (\is_product() && $post->ID)
        {
            if (!$item = self::getSyncItem($post->ID))
                return;

            if (empty($item['extra']['pricePerUnitDisplay']))
                return;

            echo '<div class="cegg_price_per_unit">';
            echo esc_html(sprintf(Translator::__('Price per unit: %s'), $item['extra']['pricePerUnitDisplay']));
            echo '</div>';
        }
    }

    public static function customButtonText($default, $product)
    {
        if ($product->get_type() != 'external')
            return $default;

        if (!$item = self::getSyncItem($product->get_id()))
            return $default;

        return TemplateHelper::btnText('woocommerce_btn_text', $default, false, $item);
    }

}
