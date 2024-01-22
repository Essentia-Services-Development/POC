<?php

namespace Keywordrush\AffiliateEgg;
defined('\ABSPATH') || exit;

/**
 * FeaturedImage class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class FeaturedImage {

    public function __construct()
    {
        \add_action('save_post', array($this, 'run'), 10, 2);
    }

    public static function run($post_id, $post)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;

        if (empty($post) || \get_post_status($post_id) == 'auto-draft' || \wp_is_post_revision($post_id))
            return;

        if (\has_post_thumbnail($post_id))
            return;

        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $pattern = \get_shortcode_regex();
        if (!preg_match_all('/' . $pattern . '/s', $post->post_content, $matches) || !array_key_exists(2, $matches) || !in_array(Shortcode::shortcode, $matches[2]))
            return;

        foreach ($matches['2'] as $key => $name)
        {
            if ($name !== Shortcode::shortcode)
                continue;

            $attr = shortcode_parse_atts($matches[3][$key]);
            if (empty($attr['id']))
                continue;

            $egg_id = (int) $attr['id'];
            $products = ProductModel::model()->getEggProducts($egg_id);

            $set_featured_img = GeneralConfig::getInstance()->option('set_featured_img');
            if ($set_featured_img == 'second' && isset($products[1]) && !empty($products[1]['img']))
                unset($products[0]);
            elseif ($set_featured_img == 'last')
                $products = array_reverse($products);
            elseif ($set_featured_img == 'rand')
                shuffle($products);
            foreach ($products as $product)
            {
                if (!$product['img'])
                    continue;

                $img_uri = '';
                if ($product['orig_img_large'])
                {
                    $filetype = \wp_check_filetype($product['orig_img_large'], null);
                    if (substr($filetype['type'], 0, 5) == 'image')
                        $img_uri = $product['orig_img_large'];
                    else
                        $product['orig_img_large'] = '';
                }

                if (!$img_uri)
                    $img_uri = $product['img'];

                if ($product['orig_img_large'] || !$product['img_file'])
                {
                    $local_img_name = ImageHelper::saveImgLocaly($img_uri, $product['title']);
                    if (!$local_img_name)
                        continue;
                    $uploads = \wp_upload_dir();
                    $img_file = ltrim(trailingslashit($uploads['subdir']), '\/') . $local_img_name;
                    $file_path = ProductModel::getFullImgPath($img_file);
                } else
                    $file_path = ProductModel::getFullImgPath($product['img_file']);
                $filetype = wp_check_filetype(basename($file_path), null);

                $attachment = array(
                    'guid' => $file_path,
                    'post_mime_type' => $filetype['type'],
                    'post_title' => $product['title'],
                    'post_content' => '',
                    'post_status' => 'inherit'
                );
                $attach_id = wp_insert_attachment($attachment, $file_path, $post_id);
                $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
                wp_update_attachment_metadata($attach_id, $attach_data);
                set_post_thumbnail($post_id, $attach_id);
                return true;
            }
        }
    }

}
