<?php

namespace ExternalImporter\application\components;

defined('\ABSPATH') || exit;

use ExternalImporter\application\models\AutoimportModel;
use ExternalImporter\application\AutoimportSheduler;
use ExternalImporter\application\helpers\ParserHelper;
use ExternalImporter\application\models\AutoimportItemModel;

/**
 * Autoimport class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class Autoimport {

    public static function run($id)
    {
        if (!$autoimport = AutoimportModel::model()->findByPk($id))
            return false;

        $autoimport['extra'] = unserialize($autoimport['extra']);

        // pre-save
        $autoimport['last_run'] = \current_time('mysql');
        AutoimportModel::model()->save($autoimport);

        // 1. parse listing
        if (!$urls = self::listing($autoimport))
            return false;

        // 2. filter
        if (!$urls = self::filterDublicates($urls, $autoimport))
            return false;
        self::saveItems($urls, $autoimport);

        // 3. parse products
        if (!$products = self::products($urls, $autoimport))
            return false;

        // 4. import
        $total = self::import($products, $autoimport);

        // save
        $autoimport['row_err_count'] = 0;
        $autoimport['last_error'] = '';
        $autoimport['post_count'] += $total;
        AutoimportModel::model()->save($autoimport);
        return true;
    }

    public static function listing(array $autoimport)
    {
        try
        {
            $listing = ParserHelper::parseListing($autoimport['listing_url']);
        } catch (\Exception $e)
        {
            self::saveError($autoimport, $e->getMessage());
            return false;
        }
        return array_slice($listing->links, 0, $autoimport['process_products']);
    }

    public static function products(array $urls, array $autoimport)
    {
        $products = array();
        foreach ($urls as $url)
        {
            try
            {
                $product = ParserHelper::parseProduct($url);
            } catch (\Exception $e)
            {
                self::saveError($autoimport, $e->getMessage());
                continue;
            }
            $products[] = $product;
        }

        return $products;
    }

    public static function saveError(array $autoimport, $error)
    {
        $autoimport['last_error'] = $error;
        $autoimport['row_err_count']++;
        if ($autoimport['row_err_count'] >= AutoimportModel::INACTIVATE_AFTER_ERROR_COUNT)
        {
            $autoimport['status'] = 0;
            AutoimportSheduler::maybeClearScheduleEvent();
        }
        AutoimportModel::model()->save($autoimport);
    }

    public static function filterDublicates(array $urls, array $autoimport)
    {
        foreach ($urls as $key => $url)
        {
            $query = 'url_hash = %s AND autoimport_id = %d';
            $query = AutoimportItemModel::model()->getDb()->prepare($query, array(AutoimportItemModel::generateUrlHash($url), $autoimport['id']));
            if (AutoimportItemModel::model()->count($query))
                unset($urls[$key]);
        }
        return array_values($urls);
    }

    public static function saveItems($urls, array $autoimport)
    {
        foreach ($urls as $url)
        {
            $item = array();
            $item['autoimport_id'] = $autoimport['id'];
            $item['url_hash'] = AutoimportItemModel::generateUrlHash($url);
            AutoimportItemModel::model()->save($item);
        }
    }

    public static function import(array $products, array $autoimport)
    {
        $params = array('category' => $autoimport['extra']['category']);
        $total = 0;
        foreach ($products as $product)
        {
            try
            {
                $product_id = WooImporter::maybeInsert($product, $params);
            } catch (\Exception $e)
            {
                continue;
            }
            $total++;
        }
        return $total;
    }

}
