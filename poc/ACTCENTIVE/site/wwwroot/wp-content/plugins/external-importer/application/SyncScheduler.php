<?php

namespace ExternalImporter\application;

defined('\ABSPATH') || exit;

use ExternalImporter\application\components\Scheduler;
use ExternalImporter\application\components\WooImporter;
use ExternalImporter\application\admin\SyncConfig;
use ExternalImporter\application\components\Synchronizer;
use ExternalImporter\application\components\Throttler;

/**
 * GalleryScheduler class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class SyncScheduler extends Scheduler {

    const CRON_TAG = 'ei_sync_products';
    const PRODUCT_LIMIT = 17;
    const SLEEP_MIN = 2;
    const SLEEP_MAX = 6;

    public static function getCronTag()
    {
        return self::CRON_TAG;
    }

    public static function initAction()
    {
        self::initSchedule();
        parent::initAction();
    }

    public static function getProductLimit()
    {
        return \apply_filters('ei_sync_product_limit', self::PRODUCT_LIMIT);
    }

    public static function run()
    {
        global $wpdb;

        @set_time_limit(595);
        $time = time();
        $ttl = round((float) SyncConfig::getInstance()->option('cache_duration') * 86400);
        $throttled = Throttler::getThrottledDomains();

        $sql = "SELECT last_update.post_id FROM {$wpdb->postmeta} last_update";
        $sql .= " JOIN {$wpdb->posts} AS post ON last_update.post_id = post.ID";


        if ($throttled)
            $sql .= " JOIN {$wpdb->postmeta} AS domain ON last_update.post_id = domain.post_id";

        $sql .= $wpdb->prepare(" WHERE {$time} - last_update.meta_value > {$ttl} AND last_update.meta_key = %s", WooImporter::META_LAST_UPDATE);

        if ($throttled)
            $sql .= $wpdb->prepare(" AND domain.meta_key = %s", WooImporter::META_PRODUCT_DOMAIN);

        if (SyncConfig::getInstance()->option('published_only'))
            $sql .= " AND post.post_status = 'publish'";

        if ($disable_sync_product_ids = \apply_filters('ei_disable_sync_product_ids', array()))
            $sql .= " AND post.ID NOT IN (" . join(',', $disable_sync_product_ids) . ")";

        if ($throttled)
        {
            $throttled_sql = array_map(function($v) {
                return "'" . \esc_sql($v) . "'";
            }, $throttled);
            $throttled_sql = implode(',', $throttled_sql);

            $sql .= ' AND domain.meta_value NOT IN (' . $throttled_sql . ')';
        }

        $sql .= $wpdb->prepare(" ORDER BY last_update.meta_value ASC LIMIT %d", self::getProductLimit());

        
        if (!$product_ids = $wpdb->get_col($sql))
            return;

        shuffle($product_ids);
        foreach ($product_ids as $product_id)
        {
            Synchronizer::maybeUpdateProduct($product_id);
            sleep(rand(self::SLEEP_MIN, self::SLEEP_MAX));
        }
    }

    public static function initSchedule()
    {
        \add_filter('cron_schedules', array(__CLASS__, 'addSchedule'));
    }

    public static function addSchedule($schedules)
    {
        $schedules['ten_min'] = array(
            'interval' => 60 * 10,
            'display' => __('Every 10 minutes'),
        );
        return $schedules;
    }

    public static function maybeAddScheduleEvent()
    {
        if (!(float) SyncConfig::getInstance()->option('cache_duration'))
            return;

        if (SyncConfig::getInstance()->option('update_mode') != 'cron')
            return;

        self::initSchedule();
        self::addScheduleEvent('ten_min');
    }

}
