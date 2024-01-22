<?php

namespace ContentEgg\application\models;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\ContentManager;
use ContentEgg\application\components\ModuleManager;
use ContentEgg\application\components\ContentProduct;

/**
 * ProductModel class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class ProductModel extends Model {

    const TRANSIENT_LAST_SYNC_DATE = 'cegg_products_last_sync';
    const PRODUCTS_TTL = 3600;

    public function tableName()
    {
        return $this->getDb()->prefix . 'cegg_product';
    }

    public function getDump()
    {

        return "CREATE TABLE " . $this->tableName() . " (
                    id int(11) unsigned NOT NULL auto_increment,
                    unique_id varchar(255) NOT NULL,
                    module_id varchar(255) NOT NULL,
                    meta_id bigint(20) unsigned DEFAULT NULL,
                    post_id bigint(20) unsigned DEFAULT 0,
                    create_date datetime NOT NULL,
                    last_update datetime NOT NULL default '0000-00-00 00:00:00',
                    stock_status tinyint(1) DEFAULT 0,
                    last_in_stock datetime NOT NULL default '0000-00-00 00:00:00',
                    price float(12,2) DEFAULT NULL,
                    price_old float(12,2) DEFAULT NULL,
                    currency_code char(3) DEFAULT NULL,
                    title text,
                    img text,
                    url text,
                    PRIMARY KEY  (id),
                    KEY uid (unique_id(80),module_id(30)),
                    KEY post_id (post_id)
                    ) $this->charset_collate;";
    }

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'title' => __('Title', 'content-egg'),
            'stock_status' => __('Stock', 'content-egg'),
            'price' => __('Price', 'content-egg'),
        );
    }

    public function scanProducts()
    {
        $per_page = 100;
        $meta_keys = $this->getCeMetaKeys();
        $sql_part = $this->getDb()->postmeta . ' WHERE meta_key IN (' . join(',', $meta_keys) . ') LIMIT ' . $per_page;
        $sql = 'SELECT SQL_CALC_FOUND_ROWS * FROM ' . $sql_part;
        $products = $this->getDb()->get_results($sql);
        $total = (int) $this->getDb()->get_var('SELECT FOUND_ROWS();');
        $this->processProducts($products);

        for ($page = 2; $page <= ceil($total / $per_page); $page++)
        {
            $offset = ( $page - 1 ) * $per_page;
            $sql = 'SELECT * FROM ' . $sql_part . ' OFFSET ' . $offset;
            $this->processProducts($this->getDb()->get_results($sql));
        }
    }

    private function getCeMetaKeys()
    {
        $module_ids = array_keys(ModuleManager::getInstance()->getAffiliateParsers(true, true));
        $meta_keys = array();
        foreach ($module_ids as $module_id)
        {
            $meta_keys[] = "'" . \esc_sql(ContentManager::META_PREFIX_DATA . $module_id) . "'";
        }

        return $meta_keys;
    }

    public function maybeScanProducts($forced = false)
    {
        if (!$this->getLastSync() || $forced)
        {
            $this->truncateTable();
            ProductModel::model()->scanProducts();
            \set_transient(self::TRANSIENT_LAST_SYNC_DATE, time(), self::PRODUCTS_TTL);

            return true;
        }

        return false;
    }

    public function getLastSync()
    {
        return \get_transient(self::TRANSIENT_LAST_SYNC_DATE);
    }

    private function processProducts(array $metas)
    {
        $all_products = array();
        foreach ($metas as $meta)
        {
            if (!$data = @unserialize($meta->meta_value))
            {
                continue;
            }

            // corrupted data?
            if (!is_array($data))
            {
                continue;
            }

            $all_products = array_merge($all_products, $this->processModuleData($data, $meta));
        }
        if ($all_products)
        {
            $this->multipleInsert($all_products);
        }
    }

    private function processModuleData(array $data, $meta)
    {
        $products = array();
        foreach ($data as $unique_id => $d)
        {
            if (!$unique_id)
            {
                continue;
            }

            $product = array(
                'last_update' => '',
                'stock_status' => ContentProduct::STOCK_STATUS_UNKNOWN,
                'price' => 0,
                'price_old' => 0,
                'currency_code' => '',
                'title' => '',
                'img' => '',
                'url' => '',
            );
            foreach ($product as $k => $v)
            {
                if (isset($d[$k]))
                {
                    $product[$k] = $d[$k];
                } elseif (strstr($k, '_'))
                {
                    $pieces = explode('_', $k);
                    for ($i = 1; $i < count($pieces); $i++)
                    {
                        $pieces[$i] = ucfirst($pieces[$i]);
                    }
                    $kd = join('', $pieces);
                    if (isset($d[$kd]))
                    {
                        $product[$k] = $d[$kd];
                    }
                }
            }

            if ($product['last_update'])
            {
                $product['last_update'] = date("Y-m-d H:i:s", $product['last_update']);
            } else
            {
                $product['last_update'] = null;
            }

            $product['id'] = null;
            $product['create_date'] = \current_time('mysql');
            $product['unique_id'] = $d['unique_id'];
            $product['module_id'] = str_replace(ContentManager::META_PREFIX_DATA, '', $meta->meta_key);
            $product['meta_id'] = $meta->meta_id;
            $product['post_id'] = $meta->post_id;

            $products[] = $product;
        }

        return $products;
    }

    static public function getStockStatuses()
    {
        return array(
            ContentProduct::STOCK_STATUS_IN_STOCK => __('In stock', 'content-egg'),
            ContentProduct::STOCK_STATUS_OUT_OF_STOCK => __('Out of stock', 'content-egg'),
            ContentProduct::STOCK_STATUS_UNKNOWN => __('Unknown', 'content-egg'),
        );
    }

    static public function getStockStatus($status_id)
    {
        $statuses = ProductModel::getStockStatuses();
        if (isset($status[$status_id]))
        {
            return $status[$status_id];
        } else
        {
            return null;
        }
    }

}
