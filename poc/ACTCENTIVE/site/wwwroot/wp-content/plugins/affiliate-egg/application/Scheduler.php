<?php

namespace Keywordrush\AffiliateEgg;
defined('\ABSPATH') || exit;

/**
 * Scheduler class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class Scheduler {

    const PRODUCT_UPDATE_LIMIT = 35;
    const CATALOG_UPDATE_LIMIT = 3;
    const AUTOBLOG_LIMIT = 3;

    public static function run()
    {
        $time_limit = round(500 + self::PRODUCT_UPDATE_LIMIT * GeneralConfig::getInstance()->option('product_update_sleep') / 1000000);
        @set_time_limit($time_limit);
        self::runAutoblog();
        self::catalogUpdate();
        self::productUpdate();
    }

    public static function productUpdate()
    {
        $updated = 0;
        $product_ttl = (int) GeneralConfig::getInstance()->option('product_ttl');
        if (!$product_ttl)
            return;

        $params = array(
            'select' => 'id, egg_id',
            'where' => array('TIMESTAMPDIFF(SECOND, last_update, "' . current_time('mysql') . '") > %d', array($product_ttl)),
            'order' => 'last_update  ASC',
            'limit' => self::PRODUCT_UPDATE_LIMIT
        );
        $products = ProductModel::model()->findAll($params);

        $product_sleep = GeneralConfig::getInstance()->option('product_update_sleep');
        $changed_egg_ids = array();
        foreach ($products as $key => $product)
        {
            if ($product_sleep && $key > 0)
                usleep($product_sleep);

            ProductModel::model()->parseAndUpdate($product['id']);
            $updated++;
            $changed_egg_ids[] = $product['egg_id'];
        }
        $changed_egg_ids = array_unique($changed_egg_ids);
        if ($changed_egg_ids)
        {
            CustomFields::updateFieldsByEgg($changed_egg_ids);
        }
    }

    public static function catalogUpdate()
    {
        $updated = 0;
        $catalog_ttl = (int) GeneralConfig::getInstance()->option('catalog_ttl');
        if (!$catalog_ttl)
            return;
        $params = array(
            'select' => 'id, egg_id',
            'where' => array('TIMESTAMPDIFF(SECOND, last_update, "' . current_time('mysql') . '") > %d', array($catalog_ttl)),
            'order' => 'last_update  ASC',
            'limit' => self::CATALOG_UPDATE_LIMIT
        );
        $catalogs = CatalogModel::model()->findAll($params);
        $changed_egg_ids = array();
        foreach ($catalogs as $catalog)
        {
            CatalogModel::model()->parseAndUpdate($catalog['id']);
            $updated++;
            $changed_egg_ids[] = $catalog['egg_id'];
        }
        $changed_egg_ids = array_unique($changed_egg_ids);
        if ($changed_egg_ids)
        {
            CustomFields::updateFieldsByEgg($changed_egg_ids);
        }
    }

    public static function runAutoblog()
    {
        $updated = 0;
        $params = array(
            'select' => 'id',
            'where' => 'status = 1 AND (last_check IS NULL OR TIMESTAMPDIFF(SECOND, last_check, "' . current_time('mysql') . '") > check_frequency)',
            'order' => 'last_check  ASC',
            'limit' => self::AUTOBLOG_LIMIT
        );
        $autoblogs = AutoblogModel::model()->findAll($params);

        foreach ($autoblogs as $autoblog)
        {
            AutoblogModel::model()->parseAndPost($autoblog['id']);
            $updated++;
        }
    }

}
