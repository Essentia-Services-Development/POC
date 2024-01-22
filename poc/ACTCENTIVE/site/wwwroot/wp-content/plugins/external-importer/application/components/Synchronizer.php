<?php

namespace ExternalImporter\application\components;

defined('\ABSPATH') || exit;

use ExternalImporter\application\admin\SyncConfig;
use ExternalImporter\application\components\WooImporter;
use ExternalImporter\application\components\Throttler;
use ExternalImporter\application\helpers\ParserHelper;

/**
 * Synchronizer class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class Synchronizer {

    public static function initAction()
    {
        if (SyncConfig::getInstance()->option('delete_attachments'))
            \add_action('before_delete_post', array(__CLASS__, 'deleteProductAttachments'));
    }

    public static function maybeUpdateProduct($product_id)
    {
        if ($disable_sync_product_ids = \apply_filters('ei_disable_sync_product_ids', array()))
        {
            if (in_array($product_id, $disable_sync_product_ids))
                return;
        }

        if (!$cache_duration = (float) SyncConfig::getInstance()->option('cache_duration'))
            return;

        if (!$last_update = (int) WooImporter::getLastUpdateMeta($product_id))
            return;

        if (time() - $last_update < round($cache_duration * 86400))
            return;

        if (!$url = WooImporter::getProductUrlMeta($product_id))
            return;

        if (Throttler::isThrottledByUrl($url))
            return;

        self::updateProduct($product_id);
    }

    public static function updateProduct($product_id)
    {
        if (!$url = WooImporter::getProductUrlMeta($product_id))
            return;

        $product = null;
        $error = '';
        $error_code = 0;

        try
        {
            $product = ParserHelper::parseProduct($url, true);
        } catch (\Exception $e)
        {
            $error_code = $e->getCode();
            $error = $e->getMessage();
        }
        if (!$product)
            $product = WooImporter::getProductMeta($product_id);

        if ($error_code == 404 || $error_code == 410)
        {
            $product->inStock = false;
            $product->availability = '';
        }

        WooImporter::update($product, $product_id);

        if ($error)
            WooImporter::addProductInfoMeta($product_id, array('last_error_code' => $error_code, 'last_error' => $error));
    }

    public static function deleteProductAttachments($post_id)
    {
        if (\get_post_type($post_id) != 'product')
            return;

        if (!WooImporter::getProductMeta($post_id))
            return;

        // featured image        
        if (\has_post_thumbnail($post_id))
            \wp_delete_attachment(\get_post_thumbnail_id($post_id), true);

        // gallery
        $attachments = \get_children(array(
            'post_parent' => $post_id,
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'post_parent' => $post_id,
        ));
        if (!$attachments)
            return;

        foreach ($attachments as $attachment)
        {
            \wp_delete_attachment($attachment->ID, true);
        }
    }

}
