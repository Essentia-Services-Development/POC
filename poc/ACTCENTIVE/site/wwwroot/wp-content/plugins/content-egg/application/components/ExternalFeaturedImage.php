<?php

namespace ContentEgg\application\components;

defined('\ABSPATH') || exit;

use ContentEgg\application\admin\GeneralConfig;
use ContentEgg\application\components\FeaturedImage;

/**
 * ExternalFeaturedImage class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2019 keywordrush.com
 */
class ExternalFeaturedImage {

    const EXTERNAL_URL_META = '_cegg_thumbnail_external';
    const FAKE_INT_START = '99999';

    public static function initAction()
    {
        if (GeneralConfig::getInstance()->option('external_featured_images') == 'disabled')
        {
            return;
        }

        \add_filter('get_post_metadata', array(__CLASS__, 'getFakeThumbnailId'), 10, 4);

        if (\is_admin())
        {
            \add_filter('admin_post_thumbnail_html', array(__CLASS__, 'adminThumbnail'));
        }

        \add_filter('wp_get_attachment_image_src', array(__CLASS__, 'replaceImageSrc'), 10, 4);
        \add_filter('woocommerce_product_get_image_id', array(__CLASS__, 'getFakeImageId'), 99, 2);
        \add_filter('post_thumbnail_html', array(__CLASS__, 'replaceThumbnail'), 10, 5);
        \add_action('wpseo_add_opengraph_images', array(__CLASS__, 'addOpengraphImage'));
        \add_action('woocommerce_structured_data_product', array(__CLASS__, 'addStructuredDataProduct'), 10, 2);
        \add_action('content_egg_save_data', array(__CLASS__, 'setImage'), 13, 4);
    }

    private static function generateFakeId($post_id)
    {
        $max_len = strlen(strval(PHP_INT_MAX)) - 1;
        $post_id_len = strlen(strval($post_id));

        $fake_id = self::FAKE_INT_START;
        $fake_id .= str_repeat('0', $max_len - $post_id_len - strlen($fake_id));
        $fake_id .= $post_id;

        return $fake_id;
    }

    private static function getRealId($post_id)
    {
        if (strlen(strval($post_id)) != strlen(strval(PHP_INT_MAX)) - 1)
        {
            return false;
        }

        if (substr((string) $post_id, 0, strlen(self::FAKE_INT_START)) != self::FAKE_INT_START)
        {
            return false;
        }

        return (int) substr_replace((string) $post_id, '', 0, strlen(self::FAKE_INT_START));
    }

    public static function setImage($data, $module_id, $post_id, $is_last_iteration)
    {
        if (\get_post_type($post_id) == 'product')
        {
            return;
        }

        if (!$is_last_iteration)
        {
            return;
        }

        self::setExternalFeaturedImage($post_id);
    }

    public static function setExternalFeaturedImage($post_id, $item = null)
    {
        if (GeneralConfig::getInstance()->option('external_featured_images') == 'enabled_internal_priority' && self::hasInternalImage($post_id))
        {
            return false;
        }

        if (!$item)
        {
            $data = FeaturedImage::getData($post_id);
            if (!$data)
            {
                return;
            }
            $item = $data[0];
        }
        if (empty($item['img']))
        {
            return;
        }

        $img_url = $item['img'];

        return self::updateExternalMeta($img_url, $post_id);
    }

    public static function updateExternalMeta($url, $post_id)
    {
        $old = \get_post_meta($post_id, self::EXTERNAL_URL_META, true);
        if ($old && $old['url'] == $url)
        {
            return true;
        }

        $save = array();
        $save['url'] = $url;

        $width = $height = 0;
        if (ini_get('allow_url_fopen'))
        {
            list( $width, $height ) = @getimagesize($url);
        }
        $save['width'] = $width;
        $save['height'] = $height;

        return \update_post_meta($post_id, self::EXTERNAL_URL_META, $save);
    }

    public static function adminThumbnail($html)
    {
        global $post;
        if (empty($post) || !$external_img = \get_post_meta($post->ID, self::EXTERNAL_URL_META, true))
        {
            return $html;
        }

        if (empty($external_img['url']))
        {
            return $html;
        }

        $html .= '<div><img class="size-post-thumbnail" src="' . \esc_url($external_img['url']) . '">';
        $html .= '<p class="howto">' . __('External featured image', 'content-egg') . '</p></div>';

        return $html;
    }

    public static function getFakeImageId($value, $product)
    {
        if (GeneralConfig::getInstance()->option('external_featured_images') == 'enabled_internal_priority' && self::hasInternalImage($product->get_id()))
        {
            return $value;
        }

        $product_id = $product->get_id();
        if (\get_post_meta($product_id, self::EXTERNAL_URL_META, true))
        {
            return self::generateFakeId($product_id);
        } else
        {
            return $value;
        }
    }

    public static function getFakeThumbnailId($value, $object_id, $meta_key, $single)
    {
        if ($meta_key != '_thumbnail_id')
        {
            return $value;
        }

        if (GeneralConfig::getInstance()->option('external_featured_images') == 'enabled_internal_priority' && self::hasInternalImage($object_id))
        {
            return $value;
        }

        if (\get_post_meta($object_id, self::EXTERNAL_URL_META, true))
        {
            return self::generateFakeId($object_id);
        } else
        {
            return $value;
        }
    }

    public static function replaceImageSrc($image, $attachment_id, $size, $icon)
    {
        if (!$post_id = self::getRealId($attachment_id))
        {
            return $image;
        }

        if (!$external_img = \get_post_meta($post_id, self::EXTERNAL_URL_META, true))
        {
            return $image;
        }

        $external_url = $external_img['url'];

        if ($image_size = self::getImageSize($size))
        {
            return array($external_url, $image_size['width'], $image_size['height'], $image_size['crop']);
        } else
        {
            if (!empty($external_img['width']))
            {
                $width = $external_img['width'];
            } else
            {
                $width = 800;
            }

            if (!empty($external_img['height']))
            {
                $height = $external_img['height'];
            } else
            {
                $height = 600;
            }

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
        {
            return $_wp_additional_image_sizes[$size];
        }

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
        if (!$external_img = \get_post_meta($post_id, self::EXTERNAL_URL_META, true))
        {
            return $html;
        }

        if (GeneralConfig::getInstance()->option('external_featured_images') == 'enabled_internal_priority' && self::hasInternalImage($post_id))
        {
            return $html;
        }

        $url = $external_img['url'];
        $alt = \get_post_field('post_title', $post_id);
        $class = 'cegg-external-img wp-post-image';
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
        $meta_type = 'post';
        $meta_key = '_thumbnail_id';

        $meta_cache = \wp_cache_get($object_id, $meta_type . '_meta');
        if (!$meta_cache)
        {
            $meta_cache = \update_meta_cache($meta_type, array($object_id));
            $meta_cache = $meta_cache[$object_id];
        }

        if (isset($meta_cache[$meta_key]))
        {
            $meta_value = $meta_cache[$meta_key][0];
        } else
        {
            $meta_value = false;
        }

        if ($meta_value)
        {
            return true;
        } else
        {
            return false;
        }
    }

    public static function getExternalUrl($post_id)
    {
        $external_img = \get_post_meta($post_id, self::EXTERNAL_URL_META, true);
        if (!$external_img || empty($external_img['url']))
        {
            return false;
        } else
        {
            return $external_img['url'];
        }
    }

    public static function addOpengraphImage($object)
    {
        if (!$post_id = \get_the_ID())
        {
            return;
        }

        if (!$external_url = self::getExternalUrl($post_id))
        {
            return;
        }

        $object->add_image($external_url);
    }

    public static function addStructuredDataProduct($markup, $product)
    {
        if (!empty($markup['image']))
        {
            return $markup;
        }

        if (!$external_url = self::getExternalUrl($product->get_id()))
        {
            return $markup;
        }

        $markup['image'] = $external_url;

        return $markup;
    }  

}
