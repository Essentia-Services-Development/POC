<?php

namespace ExternalImporter\application;

defined('\ABSPATH') || exit;

use ExternalImporter\application\components\Scheduler;
use ExternalImporter\application\helpers\WooHelper;

/**
 * GalleryScheduler class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class GalleryScheduler extends Scheduler {

    const CRON_TAG = 'ei_gallery_download';
    const GALLERY_META = '_ei_gallery';
    const PRODUCT_LIMIT = 25;

    public static function getCronTag()
    {
        return self::CRON_TAG;
    }

    public static function initAction()
    {
        self::initSchedule();
        parent::initAction();
    }

    public static function run()
    {
        global $wpdb;
        $sql = $wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s ORDER BY meta_id ASC LIMIT %d", GalleryScheduler::GALLERY_META, GalleryScheduler::PRODUCT_LIMIT);
        if (!$results = $wpdb->get_results($sql, \ARRAY_A))
        {
            GalleryScheduler::clearScheduleEvent();
            return;
        }

        @set_time_limit(900);

        foreach ($results as $result)
        {
            self::downloadGalleryImages($result['post_id']);
        }
    }

    public static function downloadGalleryImages($product_id)
    {
        $images = \get_post_meta($product_id, GalleryScheduler::GALLERY_META, true);
        \delete_post_meta($product_id, GalleryScheduler::GALLERY_META);

        if (!WooHelper::isWooInstalled())
            return;

        if (!$product = \wc_get_product($product_id))
            return;

        if ($media_ids = WooHelper::uploadMedias($images, $product->get_id(), $product->get_title()))
            $product->set_gallery_image_ids($media_ids);

        $product->save();
    }

    public static function addGalleryTask($product_id, array $images)
    {
        \update_post_meta($product_id, GalleryScheduler::GALLERY_META, $images);
        GalleryScheduler::addScheduleEvent('fifteen_min');
    }

    public static function initSchedule()
    {
        \add_filter('cron_schedules', array(__CLASS__, 'addSchedule'));
    }

    public static function addSchedule($schedules)
    {
        $schedules['fifteen_min'] = array(
            'interval' => 60 * 15,
            'display' => __('Every 15 minutes'),
        );
        return $schedules;
    }

}
