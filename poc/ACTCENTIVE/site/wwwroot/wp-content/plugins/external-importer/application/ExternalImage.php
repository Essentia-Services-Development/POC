<?php

namespace ExternalImporter\application;

defined('\ABSPATH') || exit;

use ExternalImporter\application\admin\WooConfig;
use ExternalImporter\application\components\WooImporter;

/**
 * ExternalImage class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class ExternalImage {

    const EXTERNAL_URL_META = '_ei_external_thumbnails';
    const FAKE_INT_START = '99998';

    public static function initAction()
    {
        self::initFeaturedImage();
        self::initGallery();
    }

    public static function initFeaturedImage()
    {
        if (in_array(WooConfig::getInstance()->option('import_image'), array('disabled', 'enabled')))
            return;

        \add_filter("get_post_metadata", array(__CLASS__, 'getFakeThumbnailId'), 10, 4);

        if (\is_admin())
            \add_filter('admin_post_thumbnail_html', array(__CLASS__, 'adminThumbnail'));

        \add_filter('wp_get_attachment_image_src', array(__CLASS__, 'replaceImageSrc'), 10, 4);
        \add_filter('woocommerce_product_get_image_id', array(__CLASS__, 'getFakeImageId'), 99, 2);
        \add_filter('post_thumbnail_html', array(__CLASS__, 'replaceThumbnail'), 10, 5);
        \add_action('woocommerce_structured_data_product', array(__CLASS__, 'addStructuredDataProduct'), 10, 2);
        \add_action('wpseo_add_opengraph_images', array(__CLASS__, 'addOpengraphImage'));
    }

    public static function initGallery()
    {
        if (in_array(WooConfig::getInstance()->option('import_gallery'), array('disabled', 'enabled')))
            return;

        \add_filter('wp_get_attachment_image_src', array(__CLASS__, 'replaceImageSrc'), 10, 4);
        \add_filter('woocommerce_product_get_gallery_image_ids', array(__CLASS__, 'getFakeGalleryIds'), 99, 2);
    }

    private static function generateFakeId($post_id, $image_key)
    {
        if ($image_key > 9)
            $image_key = 9;

        $max_len = strlen(strval(PHP_INT_MAX)) - 1;
        $post_id_len = strlen(strval($post_id));

        $fake_id = self::FAKE_INT_START;
        $fake_id .= str_repeat('0', $max_len - $post_id_len - strlen($fake_id) - 1);
        $fake_id .= $post_id;
        $fake_id .= $image_key;

        return $fake_id;
    }

    private static function getRealId($post_id)
    {
        if (strlen(strval($post_id)) != strlen(strval(PHP_INT_MAX)) - 1)
            return false;

        if (substr((string) $post_id, 0, strlen(self::FAKE_INT_START)) != self::FAKE_INT_START)
            return false;

        $real = substr_replace((string) $post_id, '', 0, strlen(self::FAKE_INT_START));
        $real = substr($real, 0, -1);
        return (int) $real;
    }

    private static function getRealImageKey($post_id)
    {
        $post_id = (string) $post_id;
        return (int) ($post_id[strlen($post_id) - 1]);
    }

    public static function maybeSetExternalFeaturedImage($objProduct, $img_url)
    {
        if (!$img_url)
            return false;

        $import_image = WooConfig::getInstance()->option('import_image');
        if (in_array($import_image, array('disabled', 'enabled')))
            return false;

        if ($import_image == 'internal_priority' && \has_post_thumbnail($objProduct->get_id()))
            return false;

        return self::updateExternalMeta($img_url, $objProduct->get_id(), 0);
    }

    public static function maybeSetExternalGallery($objProduct, array $img_urls)
    {
        if (!$img_urls)
            return false;

        $import_gallery = WooConfig::getInstance()->option('import_gallery');
        if (in_array($import_gallery, array('disabled', 'enabled')))
            return false;

        if ($import_gallery == 'internal_priority' && $objProduct->get_gallery_image_ids())
            return false;

        $img_urls = array_values($img_urls);
        foreach ($img_urls as $key => $img_url)
        {
            self::updateExternalMeta($img_url, $objProduct->get_id(), $key + 1);
        }

        return true;
    }

    public static function updateExternalMeta($url, $post_id, $image_key = 0)
    {
        $meta = \get_post_meta($post_id, self::EXTERNAL_URL_META, true);
        if ($meta && isset($meta[$image_key]) && $meta[$image_key]['url'] == $url)
            return true;

        if (!$meta)
            $meta = array();

        $meta[$image_key]['url'] = $url;

        $width = $height = 0;
        if (ini_get('allow_url_fopen'))
            list($width, $height) = @getimagesize($url);
        $meta[$image_key]['width'] = $width;
        $meta[$image_key]['height'] = $height;

        return \update_post_meta($post_id, self::EXTERNAL_URL_META, $meta);
    }

    public static function getExternalImageMeta($post_id, $image_key = 0)
    {
        if (!$meta = \get_post_meta($post_id, self::EXTERNAL_URL_META, true))
            return false;

        if (isset($meta[$image_key]))
            return $meta[$image_key];
        else
            return false;
    }

    public static function adminThumbnail($html)
    {
        global $post;
        if (empty($post) || !$external_img = self::getExternalImageMeta($post->ID))
            return $html;

        if (empty($external_img['url']))
            return $html;

        $html .= '<div><img class="size-post-thumbnail" src="' . \esc_url($external_img['url']) . '">';
        $html .= '<p class="howto">' . __('External featured image', 'external-importer') . '</p></div>';

        return $html;
    }

    public static function getFakeImageId($value, $product)
    {
        $product_id = $product->get_id();
        if (WooConfig::getInstance()->option('import_image') == 'internal_priority' && self::hasInternalImage($product_id))
            return $value;

        $product_id = $product->get_id();
        if (self::getExternalImageMeta($product_id))
            return self::generateFakeId($product_id, 0);
        else
            return $value;
    }

    public static function getFakeThumbnailId($value, $object_id, $meta_key, $single)
    {
        if ($meta_key != '_thumbnail_id')
            return $value;

        if (WooConfig::getInstance()->option('import_image') == 'internal_priority' && self::hasInternalImage($object_id))
            return $value;

        if (self::getExternalImageMeta($object_id))
            return self::generateFakeId($object_id, 0);
        else
            return $value;
    }

    public static function getFakeGalleryIds($value, $objProduct)
    {
        $object_id = $objProduct->get_id();

        if (WooConfig::getInstance()->option('import_gallery') == 'internal_priority' && self::hasInternalGallery($object_id))
            return $value;

        $product = WooImporter::getProductMeta($object_id);
        if (empty($product->images) || !is_array($product->images))
            return array();

        $product->images = array_slice($product->images, 0, 9);

        $value = array();
        foreach ($product->images as $i => $image)
        {
            $image_key = $i + 1;
            $value[] = self::generateFakeId($object_id, $image_key);
        }
        return $value;
    }

    public static function replaceImageSrc($image, $attachment_id, $size, $icon)
    {
        if (!$post_id = self::getRealId($attachment_id))
            return $image;

        $image_key = self::getRealImageKey($attachment_id);

        if (!$external_img = self::getExternalImageMeta($post_id, $image_key))
            return $image;

        $external_url = $external_img['url'];

        if ($image_size = self::getImageSize($size))
            return array($external_url, $image_size['width'], $image_size['height'], $image_size['crop']);
        else
        {
            if (!empty($external_img['width']))
                $width = $external_img['width'];
            else
                $width = 800;

            if (!empty($external_img['height']))
                $height = $external_img['height'];
            else
                $height = 600;

            return array($external_url, $width, $height, false);
        }
    }

    public static function getImageSize($size)
    {
        if (is_array($size))
        {
            return array(
                'width' => isset($size[0]) ? $size[0] : null,
                'height' => isset($size[1]) ? $size[1] : null,
                'crop' => isset($size[2]) ? $size[2] : null,
            );
        }

        global $_wp_additional_image_sizes;
        if (isset($_wp_additional_image_sizes[$size]))
            return $_wp_additional_image_sizes[$size];

        $default = array('thumbnail', 'medium', 'medium_large', 'large');
        if (in_array($size, $default))
        {
            return array(
                'width' => \get_option("{$size}_size_w"),
                'height' => \get_option("{$size}_size_h"),
                'crop' => \get_option("{$size}_crop"),
            );
        }
        return array();
    }

    public static function replaceThumbnail($html, $post_id, $post_thumbnail_id, $size, $attr)
    {
        if (!$external_img = self::getExternalImageMeta($post_id))
            return $html;

        if (WooConfig::getInstance()->option('import_image') == 'internal_priority' && self::hasInternalImage($post_id))
            return $html;

        $url = $external_img['url'];
        $alt = \get_post_field('post_title', $post_id);
        $class = 'ei-external-img wp-post-image';
        $attr = array('alt' => $alt, 'class' => $class);
        //$attr = \apply_filters('wp_get_attachment_image_attributes', $attr, $size);
        $attr = array_map('esc_attr', $attr);
        $html = sprintf('<img src="%s"', esc_url($url));
        foreach ($attr as $name => $value)
        {
            $html .= " $name=" . '"' . $value . '"';
        }
        $html .= ' />';
        return $html;
    }

    public static function hasInternalImage($object_id)
    {
        return self::hasMeta($object_id, '_thumbnail_id');
    }

    public static function hasInternalGallery($object_id)
    {
        return self::hasMeta($object_id, '_product_image_gallery');
    }

    public static function hasMeta($object_id, $meta_key, $meta_type = 'post')
    {
        if (!$meta_cache = \wp_cache_get($object_id, $meta_type . '_meta'))
        {
            $meta_cache = \update_meta_cache($meta_type, array($object_id));
            $meta_cache = $meta_cache[$object_id];
        }

        if (isset($meta_cache[$meta_key]))
            $meta_value = $meta_cache[$meta_key][0];
        else
            $meta_value = false;

        if ($meta_value)
            return true;
        else
            return false;
    }

    public static function getExternalUrl($post_id)
    {
        if (!$external_img = self::getExternalImageMeta($post_id, 0))
            return false;
        return $external_img['url'];
    }

    public static function addStructuredDataProduct($markup, $product)
    {
        if (!empty($markup['image']))
            return $markup;

        if (!$external_url = self::getExternalUrl($product->get_id()))
            return $markup;

        $markup['image'] = $external_url;
        return $markup;
    }
    
    public static function addOpengraphImage($object)
    {
        if (!$post_id = \get_the_ID())
            return;

        if (!$external_url = self::getExternalUrl($post_id))
            return;

        $object->add_image($external_url);
    }    

}
