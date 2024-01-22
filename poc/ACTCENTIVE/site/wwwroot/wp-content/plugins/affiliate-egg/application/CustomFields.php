<?php

namespace Keywordrush\AffiliateEgg;
defined('\ABSPATH') || exit;

/**
 * AffiliateEggAdmin class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class CustomFields {

    public function __construct()
    {
        \add_action('save_post', array($this, 'run'), 10, 2);
    }

    public static function getMetaPrefixes()
    {
        return array(
            'title',
            'description',
            'img',
            'price',
            'old_price',
            'currency',
            'manufacturer',
            'orig_url',
            'status',
            'in_stock',
            'create_date',
            'last_update',
        );
    }

    public static function run($post_id, $post)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;

        if (empty($post) || \get_post_status($post_id) == 'auto-draft' || \wp_is_post_revision($post_id))
            return;

        $pattern = \get_shortcode_regex();
        if (!preg_match_all('/' . $pattern . '/s', $post->post_content, $matches) || !array_key_exists(2, $matches) || !in_array(Shortcode::shortcode, $matches[2]))
        {
            self::deleteFields($post_id);
            return;
        }

        $egg_ids = array();
        \delete_post_meta($post_id, '_affegg_egg_id');
        foreach ($matches['2'] as $key => $name)
        {
            if ($name !== Shortcode::shortcode)
                continue;

            $attr = \shortcode_parse_atts($matches[3][$key]);
            if (empty($attr['id']))
                continue;

            $egg_id = (int) $attr['id'];

            if (in_array($egg_id, $egg_ids))
                continue;

            // save egg_id in meta
            \add_post_meta($post_id, '_affegg_egg_id', $egg_id, false);
            $egg_ids[] = $egg_id;
        }

        self::setProductsFields($post_id, $egg_ids);
    }

    private static function deleteFields($post_id)
    {
        \delete_post_meta($post_id, '_affegg_egg_id');
        \delete_post_meta($post_id, '_affegg_product_id');
        \delete_post_meta($post_id, 'affegg_products');
        foreach (self::getMetaPrefixes() as $field)
        {
            \delete_post_meta($post_id, 'affegg_product_' . $field);
        }
    }

    private static function setProductsFields($post_id, array $egg_ids)
    {
        $all_products = array();
        foreach ($egg_ids as $egg_id)
        {
            $products = ProductModel::model()->getEggProducts($egg_id);
            $all_products = array_merge($all_products, $products);
        }
        \update_post_meta($post_id, 'affegg_products', $all_products);

        // and now select and save "mine" product
        $save_custom_fields = GeneralConfig::getInstance()->option('save_custom_fields');
        switch ($save_custom_fields)
        {
            case 'price_min':
                usort($all_products, function($a, $b) {
                    if (!(float) $a['price'])
                        return 1;
                    if (!(float) $b['price'])
                        return -1;
                    return (float) $a['price'] - (float) $b['price'];
                });
                break;
            case 'price_max':
                usort($all_products, function($a, $b) {
                    return (float) $b['price'] - (float) $a['price'];
                });
                break;
            case 'last':
                $all_products = array_reverse($all_products);
                break;
            case 'rand':
                shuffle($all_products);
                break;
        }

        if ($all_products)
        {
            $mine_product = $all_products[0];
            \update_post_meta($post_id, '_affegg_product_id', $mine_product['id']);
            foreach (self::getMetaPrefixes() as $field)
            {
                \update_post_meta($post_id, 'affegg_product_' . $field, $mine_product[$field]);
            }
        } else
        {
            \delete_post_meta($post_id, '_affegg_product_id');
            foreach (self::getMetaPrefixes() as $field)
            {
                \delete_post_meta($post_id, 'affegg_product_' . $field);
            }
        }
    }

    public static function updateFieldsByEgg(array $egg_ids)
    {
        global $wpdb;

        if (!$egg_ids)
            return;
        $post_ids = $wpdb->get_col("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_affegg_egg_id' AND meta_value IN (" . join(',', $egg_ids) . ")");
        if (!$post_ids)
            return;

        foreach ($post_ids as $post_id)
        {
            $post_egg_ids = \get_post_meta($post_id, '_affegg_egg_id');
            self::setProductsFields($post_id, $post_egg_ids);
        }
    }

}
