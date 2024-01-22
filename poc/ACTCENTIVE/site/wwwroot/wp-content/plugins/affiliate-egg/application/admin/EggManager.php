<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * EggManager class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class EggManager {

    const TOTAL_PRODUCT_LIMIT = 500;
    const CATALOG_PRODUCT_LIMIT = 200;

    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new self;

        return self::$instance;
    }

    public function updateUrls(array $urls, $egg_id, $total_limit)
    {
        @set_time_limit(900);
        $num = 0;
        $catalog_saved_ids = array();
        $product_saved_ids = array();
        foreach ($urls as $url)
        {
            $remaining_limit = $total_limit - count($catalog_saved_ids) - count($product_saved_ids);
            if (list($url_type, $id) = $this->updateUrl($url, $egg_id, $num, $remaining_limit))
            {
                if ($url_type == 'product')
                {
                    $product_saved_ids[] = $id;
                    $num++;
                } elseif ($url_type == 'catalog')
                {
                    $catalog_saved_ids[] = $id;
                    $max = ProductModel::model()->getDb()->get_var('SELECT MAX(num) FROM ' . ProductModel::model()->tableName() . ' WHERE catalog_id = ' . $id);
                    $num = $max + 1;
                }
            }
            if ($num >= $total_limit)
                break;
        }

        $params = array(
            'select' => 'id',
            'where' => 'egg_id=' . $egg_id
        );
        if ($catalog_saved_ids)
            $params['where'] .= ' AND id NOT IN (' . join(',', $catalog_saved_ids) . ')';
        $catalogs = CatalogModel::model()->findAll($params);
        foreach ($catalogs as $catalog)
        {
            CatalogModel::model()->delete($catalog['id']);
        }

        $params = array(
            'select' => 'id',
            'where' => 'egg_id=' . $egg_id . ' AND catalog_id=0'
        );
        if ($product_saved_ids)
            $params['where'] .= ' AND id NOT IN (' . join(',', $product_saved_ids) . ')';
        $products = ProductModel::model()->findAll($params);
        foreach ($products as $product)
        {
            ProductModel::model()->delete($product['id']);
        }
    }

    public function updateUrl($url, $egg_id, $num, $remaining_limit)
    {
        $catalog_default = array(
            'limit' => EggManager::CATALOG_PRODUCT_LIMIT,
        );
        $img_default = array(
            'title' => '',
            'price' => 0,
        );

        $url = trim($url);
        if (!$url)
            return false;

        $catalog_atts = null;
        $img_atts = null;
        if ($url[0] == '[' && preg_match('/^\[catalog(.*?)\](.+)/', $url, $matches))
        {
            if ($matches[1])
                $catalog_atts = shortcode_atts($catalog_default, shortcode_parse_atts($matches[1]));
            else
                $catalog_atts = $catalog_default;
            $url = trim($matches[2]);
        } elseif ($url[0] == '[' && preg_match('/^\[img(.*?)\](.+)/', $url, $matches))
        {
            if ($matches[1])
                $img_atts = shortcode_atts($img_default, shortcode_parse_atts($matches[1]));
            else
                $img_atts = $img_default;
            $url = trim($matches[2]);
        } elseif (in_array(strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION)), array('gif', 'jpg', 'jpeg', 'png')))
        {
            $img_atts = $img_default;
        }
        if (!FormValidator::valid_url($url))
            return false;
        if (!$img_atts && !ParserManager::getInstance()->getShopIdByUrl($url))
            return false;

        // product url (or image)
        if ($catalog_atts === null || $img_atts)
        {
            $product = ProductModel::model()->getByUrl($url, $egg_id);
            if ($product)
            {
                if ($product['num'] != $num && !$product['catalog_id'])
                    ProductModel::model()->setNum($product['id'], $num);
                return array('product', $product['id']);
            }
            if ($img_atts)
            {
                if ($id = ProductModel::model()->parseAndSaveImg($url, $egg_id, $num, $img_atts))
                    return array('product', $id);
                else
                    return false;
            }

            if ($id = ProductModel::model()->parseAndSave($url, $egg_id, $num))
                return array('product', $id);
        }

        // catalog url   
        if ($catalog_atts === null)
            $catalog_atts = $catalog_default;
        $catalog_atts['limit'] = absint($catalog_atts['limit']);
        if ($catalog_atts['limit'] > EggManager::CATALOG_PRODUCT_LIMIT)
            $catalog_atts['limit'] = EggManager::CATALOG_PRODUCT_LIMIT;
        $catalog = CatalogModel::model()->getByUrl($url, $egg_id);

        if ($catalog)
        {
            if ($catalog['prod_limit'] != $catalog_atts['limit'])
            {
                CatalogModel::model()->setLimit($catalog['id'], $catalog_atts['limit'], true);
            }
            if ($catalog['num'] != $num)
            {
                CatalogModel::model()->setNum($catalog['id'], $num);
            }
            return array('catalog', $catalog['id']);
        } else
        {
            if ($id = CatalogModel::model()->parseAndSave($url, $egg_id, $num, $catalog_atts['limit'], $remaining_limit))
                return array('catalog', $id);
        }
        return false;
    }

    public function getFormattedUrls($egg_id)
    {
        $result = array();
        $sql = 'SELECT * from ' . ProductModel::model()->tableName() . ' WHERE egg_id=%d AND catalog_id=0 ORDER BY num';
        $sql = ProductModel::model()->getDb()->prepare($sql, $egg_id);
        $rows = ProductModel::model()->getDb()->get_results($sql, \ARRAY_A);
        foreach ($rows as $key => $row)
        {
            if ($row['prod_type'] == ProductModel::PROD_TYPE_IMG)
            {
                $rows[$key]['orig_url'] = '[img';
                if ($row['title'])
                    $rows[$key]['orig_url'] .= ' title="' . $row['title'] . '"';
                if ($row['price'])
                    $rows[$key]['orig_url'] .= ' price="' . $row['price'] . '"';
                $rows[$key]['orig_url'] .= ']' . $row['orig_url'];
            }
        }

        $sql = 'SELECT * from ' . CatalogModel::model()->tableName() . ' WHERE egg_id=%d ORDER BY num';
        $sql = CatalogModel::model()->getDb()->prepare($sql, $egg_id);
        $catalogs = CatalogModel::model()->getDb()->get_results($sql, \ARRAY_A);
        foreach ($catalogs as $key => $catalog)
        {
            $url = '[catalog';
            if ($catalog['prod_limit'] !== EggManager::CATALOG_PRODUCT_LIMIT)
                $url .= ' limit=' . $catalog['prod_limit'];
            $url .= ']';
            $url .= $catalog['orig_url'];
            $catalogs[$key]['orig_url'] = $url;
        }
        $rows = array_merge($rows, $catalogs);

        usort($rows, function($a, $b) {
            return $a['num'] - $b['num'];
        });

        foreach ($rows as $row)
        {
            $result[] = $row['orig_url'];
        }
        return $result;
    }

}
