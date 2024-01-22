<?php

namespace ContentEgg\application;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\Scheduler;
use ContentEgg\application\components\ContentManager;
use ContentEgg\application\components\ModuleManager;

/**
 * ModuleUpdateScheduler class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class ModuleUpdateScheduler extends Scheduler {

    const CRON_TAG = 'cegg_module_updater_cron';
    const BYKEYWORD_UPDATE_LIMIT_FOR_MODULE = 50;
    const ITEMS_UPDATE_LIMIT_FOR_MODULE = 50;

    public static function getCronTag()
    {
        return self::CRON_TAG;
    }

    public static function run()
    {
        @set_time_limit(2000);

        // 1. By keyword update
        self::byKeywordUpdate();
        // 2. Price update
        self::priceUpdate();
    }

    public static function byKeywordUpdate()
    {
        global $wpdb;

        $module_ids = ModuleManager::getInstance()->getByKeywordUpdateModuleIds();
        if (!$module_ids)
            return;

        $time = time();

        shuffle($module_ids);
        foreach ($module_ids as $module_id)
        {
            $post_ids = \apply_filters('cegg_keyword_update_posts', array(), $module_id);

            if (!$post_ids)
            {
                $module = ModuleManager::getInstance()->factory($module_id);
                $ttl = $module->config('ttl');
                $meta_key_keyword = self::addKeywordPrefix($module_id);
                $meta_key_last_bykeyword_update = self::addByKeywordUpdatePrefix($module_id);

                $limit = (int) \apply_filters('cegg_update_limit_keyword', self::BYKEYWORD_UPDATE_LIMIT_FOR_MODULE);

                $sql = "SELECT last_bykeyword_update.post_id
            FROM    {$wpdb->postmeta} last_bykeyword_update
            INNER JOIN  {$wpdb->postmeta} keyword 
            ON last_bykeyword_update.post_id = keyword.post_id
                AND keyword.meta_key = %s
            WHERE   
                {$time} - last_bykeyword_update.meta_value  > {$ttl}
                AND last_bykeyword_update.meta_key = %s
            ORDER BY    last_bykeyword_update.meta_value ASC
            LIMIT " . $limit;

                $query = $wpdb->prepare($sql, $meta_key_keyword, $meta_key_last_bykeyword_update);
                $post_ids = $wpdb->get_col($query);
            }

            if (!$post_ids)
                continue;

            // update!
            foreach ($post_ids as $post_id)
            {
                ContentManager::updateByKeyword($post_id, $module_id);
            }
        }
    }

    public static function priceUpdate()
    {
        global $wpdb;

        $module_ids = ModuleManager::getInstance()->getItemsUpdateModuleIds();
        if (!$module_ids)
            return;

        $time = time();
        shuffle($module_ids);

        $limit = (int) \apply_filters('cegg_update_module_limit', self::ITEMS_UPDATE_LIMIT_FOR_MODULE);

        foreach ($module_ids as $module_id)
        {
            $module = ModuleManager::getInstance()->factory($module_id);
            $ttl_items = $module->config('ttl_items');
            $meta_key_last_update = self::addLastItemsUpdatePrefix($module_id);

            $sql = "SELECT last_update.post_id
            FROM    {$wpdb->postmeta} last_update
            WHERE   
                {$time} - last_update.meta_value  > {$ttl_items}
                AND last_update.meta_key = %s
            ORDER BY    last_update.meta_value ASC
            LIMIT " . $limit;

            $query = $wpdb->prepare($sql, $meta_key_last_update);
            $results = $wpdb->get_results($query);
            if (!$results)
                continue;

            // update!
            foreach ($results as $r)
            {
                ContentManager::updateItems($r->post_id, $module_id);
            }
        }
    }

    public static function addByKeywordUpdatePrefix($module_id)
    {
        return ContentManager::META_PREFIX_LAST_BYKEYWORD_UPDATE . $module_id;
    }

    public static function addKeywordPrefix($module_id)
    {
        return ContentManager::META_PREFIX_KEYWORD . $module_id;
    }

    public static function addLastItemsUpdatePrefix($module_id)
    {
        return ContentManager::META_PREFIX_LAST_ITEMS_UPDATE . $module_id;
    }

}
