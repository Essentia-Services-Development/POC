<?php

namespace ContentEgg\application\components;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\ModuleManager;
use ContentEgg\application\components\ContentManager;
use ContentEgg\application\helpers\ImageHelper;
use ContentEgg\application\admin\GeneralConfig;
use ContentEgg\application\components\ExternalFeaturedImage;

/**
 * FeaturedImage class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class FeaturedImage
{

    public function __construct()
    {
        if (\is_admin())
        {
            $this->adminInit();
        }
    }

    public function adminInit()
    {
        if (GeneralConfig::getInstance()->option('external_featured_images') != 'disabled')
        {
            return;
        }

        // priority 11 - after meta save
        \add_action('save_post', array($this, 'setImage'), 11, 2);
    }

    public static function doAction($post_id, $item = null)
    {
        if (GeneralConfig::getInstance()->option('external_featured_images') == 'disabled')
        {
            if (!\has_post_thumbnail($post_id))
            {
                FeaturedImage::setFeaturedImage($post_id, $item);
            }
        } else
        {
            ExternalFeaturedImage::setExternalFeaturedImage($post_id, $item);
        }
    }

    public function setImage($post_id)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        {
            return;
        }

        if (\get_post_status($post_id) == 'auto-draft' || \wp_is_post_revision($post_id))
        {
            return;
        }

        if (\has_post_thumbnail($post_id))
        {
            return;
        }

        self::setFeaturedImage($post_id);
    }

    public static function setFeaturedImage($post_id, $item = null)
    {
        if ($item)
        {
            $data = array($item);
        } else
        {
            $data = self::getData($post_id);
        }

        foreach ($data as $d)
        {
            if (!$img_file = self::getImgFile($d))
            {
                continue;
            }

            return self::attachThumbnail($img_file, $post_id, $d['title']);
        }

        return false;
    }

    public static function getData($post_id)
    {
        $modules_ids = ModuleManager::getInstance()->getParserModulesIdList();
        $data = array();
        foreach ($modules_ids as $module_id)
        {
            $module = ModuleManager::factory($module_id);
            if (!$featured_image = $module->config('featured_image', false))
            {
                continue;
            }

            if (!$d = ContentManager::getData($post_id, $module->getId()))
            {
                continue;
            }

            if ($featured_image == 'second' && isset($d[1]))
            {
                unset($d[0]);
            } elseif ($featured_image == 'last')
            {
                $d = array_reverse($d);
            } elseif ($featured_image == 'rand')
            {
                shuffle($d);
            }

            $data = array_merge($data, $d);
        }

        foreach ($data as $i => $d)
        {
            if (empty($d['img']))
            {
                unset($data[$i]);
            }
        }

        return array_values($data);
    }

    public static function getImgFile($item)
    {
        if (empty($item['img']))
        {
            return false;
        }

        // already saved? dublicate image file
        if (isset($item['img_file']) && $item['img_file'])
        {
            $img_file = ImageHelper::getFullImgPath($item['img_file']);
            if (!is_file($img_file))
            {
                return false;
            }

            $uploads = \wp_upload_dir();
            $dublicate_name = \wp_unique_filename($uploads['path'], basename($item['img_file']));
            $dublicate_file = $uploads['path'] . '/' . $dublicate_name;

            if (!copy($img_file, $dublicate_file))
            {
                return false;
            }

            return $dublicate_file;
        } else
        {
            // save image localy
            $file_name = ImageHelper::saveImgLocaly($item['img'], $item['title']);
            if (!$file_name)
            {
                return false;
            }
            $uploads = \wp_upload_dir();
            $image = ltrim(trailingslashit($uploads['subdir']), '\/') . $file_name;

            $img_file = ImageHelper::getFullImgPath($image);
            $img_file = \apply_filters('cegg_handle_upload_media', $img_file);

            return $img_file;
        }
    }

    public static function attachThumbnail($img_file, $post_id, $title = '')
    {
        require_once( ABSPATH . 'wp-admin/includes/image.php' );

        $title = \sanitize_text_field($title);
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
        if ($title)
        {
            \update_post_meta($attach_id, '_wp_attachment_image_alt', $title);
        }

        return \set_post_thumbnail($post_id, $attach_id);
    }

}
