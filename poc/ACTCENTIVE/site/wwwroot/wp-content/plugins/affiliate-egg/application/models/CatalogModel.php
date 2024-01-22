<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * CatalogModel class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class CatalogModel extends Model {

    public function tableName()
    {
        return $this->getDb()->prefix . 'affegg_catalog';
    }

    public function getDump()
    {

        return "CREATE TABLE " . $this->tableName() . " (
                    id int(11) unsigned NOT NULL auto_increment,
                    egg_id int(11) unsigned NOT NULL,
                    num smallint(6) NOT NULL,
                    create_date datetime NOT NULL,
                    last_update datetime NOT NULL default '0000-00-00 00:00:00',
                    prod_limit smallint(3) DEFAULT 0,                    
                    row_err_count smallint(5) DEFAULT '0',                    
                    last_error varchar(255) DEFAULT NULL,                    
                    shop_id varchar(30) NOT NULL,                    
                    orig_url text,
                    orig_url_hash varchar(32) NOT NULL,                    
                    PRIMARY KEY  (id),
                    KEY egg_id (egg_id),
                    KEY num (num),
                    KEY orig_url_hash (orig_url_hash(10)),
                    KEY shop_id (shop_id(15)),
                    KEY last_update (last_update)                    
                    ) $this->charset_collate;";
    }

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function parseAndSave($url, $egg_id, $num, $prod_limit, $limit)
    {
        if ($prod_limit < $limit)
            $limit = $prod_limit;
        try
        {
            $product_urls = ParserManager::getInstance()->parseCatalog($url, $limit);
        } catch (\Exception $e)
        {
            return false;
        }
        if (!$product_urls)
            return false;

        $catalog = array();
        $catalog['id'] = null;
        $catalog['egg_id'] = $egg_id;
        $catalog['num'] = $num;
        $catalog['prod_limit'] = $prod_limit;
        $catalog['create_date'] = current_time('mysql');
        $catalog['last_update'] = current_time('mysql');
        $catalog['orig_url'] = $url;
        $catalog['orig_url_hash'] = md5($catalog['orig_url']);
        $catalog['shop_id'] = ParserManager::getInstance()->getShopIdByUrl($url);

        $catalog_id = $this->save($catalog);
        if (!$catalog_id)
            return false;

        $this->parseProductsForCatalog($product_urls, $catalog_id, $egg_id, $num, $limit);
        return $catalog_id;
    }

    public function parseAndUpdate($id)
    {
        $catalog = self::model()->findByPk($id);
        if (!$catalog)
            return false;

        $egg = EggModel::model()->findByPk($catalog['egg_id']);
        $total = ProductModel::model()->count('egg_id = ' . $egg['id'] . ' AND catalog_id != ' . $catalog['id']);
        if ($catalog['prod_limit'] < $egg['prod_limit'] - $total)
            $limit = $catalog['prod_limit'];
        else
            $limit = $egg['prod_limit'] - $total;
        if ($limit <= 0)
            $limit = 1;

        $error = '';
        try
        {
            $product_urls = ParserManager::getInstance()->parseCatalog($catalog['orig_url'], $limit);
        } catch (\Exception $e)
        {
            $error = $e->getMessage();
        }
        if (!$error && !$product_urls)
            $error = 'Products not found.';

        if ($error)
        {
            $catalog['last_update'] = current_time('mysql');
            $catalog['row_err_count']++;
            $catalog['last_error'] = $error;
            $this->save($catalog);
            return false;
        }

        $catalog['last_update'] = current_time('mysql');
        $catalog['row_err_count'] = 0;
        $catalog['last_error'] = '';
        $this->save($catalog);

        $this->parseProductsForCatalog($product_urls, $catalog['id'], $catalog['egg_id'], $catalog['num'], $limit);
        return true;
    }

    private function parseProductsForCatalog(array $product_urls, $catalog_id, $egg_id, $num, $limit)
    {
        $added = 0;
        $added_prod_ids = array();
        $product_sleep = GeneralConfig::getInstance()->option('product_sleep');
        foreach ($product_urls as $key => $url)
        {
            if ($added >= $limit)
                return true;

            $product = ProductModel::model()->getByUrl($url, $egg_id);
            if ($product)
            {
                if ($product['catalog_id'] == $catalog_id)
                {
                    ProductModel::model()->setNum($product['id'], $num + $added);
                    $added_prod_ids[] = $product['id'];
                    $added++;
                }
                continue;
            }
            if ($prod_id = ProductModel::model()->parseAndSave($url, $egg_id, $num + $added, $catalog_id))
            {
                $added++;
                $added_prod_ids[] = $prod_id;
            }

            if ($product_sleep && $key < count($product_urls) - 1)
                usleep($product_sleep);
        }
        if ($added_prod_ids)
        {
            $params = array(
                'select' => 'id',
                'where' => 'id NOT IN (' . join(',', $added_prod_ids) . ') AND catalog_id = ' . $catalog_id
            );
            $products = ProductModel::model()->findAll($params);
            foreach ($products as $product)
            {
                ProductModel::model()->delete($product['id']);
            }
        }
        return true;
    }

    public function getByUrl($url, $egg_id)
    {
        $sql = 'SELECT id, num, prod_limit from ' . $this->tableName() . ' WHERE orig_url_hash=%s AND egg_id=%d';
        $sql = $this->getDb()->prepare($sql, array(md5($url), $egg_id));
        return $this->getDb()->get_row($sql, \ARRAY_A);
    }

    public function setNum($id, $num)
    {
        $catalog = $this->findByPk($id);
        $diff = $num - $catalog['num'];
        $this->getDb()->update($this->tableName(), array('num' => $num), array('id' => $id));
        $this->getDb()->query($this->getDb()->prepare("UPDATE " . ProductModel::model()->tableName() . " SET num = num + %d WHERE catalog_id = %d", $diff, $id));
    }

    public function setLimit($id, $limit, $align_products = false)
    {
        $catalog = $this->findByPk($id);
        $this->getDb()->update($this->tableName(), array('prod_limit' => $limit), array('id' => $id));

        if (!$align_products)
            return;

        if ($catalog['prod_limit'] > $limit)
        {
            $products = ProductModel::model()->findAll(
                    array(
                        'select' => 'id',
                        'limit' => 2147483647,
                        'offset' => $limit,
                        'order' => 'num',
                        'where' => array('catalog_id = %d', array($id))));
            foreach ($products as $product)
            {
                ProductModel::model()->delete($product['id']);
            }
        } elseif ($catalog['prod_limit'] < $limit)
        {
            //@todo
        }
    }

    public function delete($id)
    {
        if (parent::delete($id))
        {
            $products = ProductModel::model()->findAll(array('select' => 'id', 'where' => array('catalog_id = %d', array($id))));
            foreach ($products as $product)
            {
                ProductModel::model()->delete($product['id']);
            }
        }
    }

}
