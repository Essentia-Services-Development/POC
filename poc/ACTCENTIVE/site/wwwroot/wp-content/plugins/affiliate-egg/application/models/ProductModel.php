<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * ProductModel class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class ProductModel extends Model {

    const STATUS_INVISIBLE = 0;
    const STATUS_VISIBLE = 1;
    const NOT_IN_STOCK = 0;
    const IN_STOCK = 1;
    const PROD_TYPE_IMG = 1;

    public function tableName()
    {
        return $this->getDb()->prefix . 'affegg_product';
    }

    public function getDump()
    {

        return "CREATE TABLE " . $this->tableName() . " (
                    id int(11) unsigned NOT NULL auto_increment,
                    egg_id int(11) unsigned NOT NULL,
                    catalog_id int(11) unsigned DEFAULT NULL,
                    num smallint(6) NOT NULL,
                    prod_type tinyint(1) DEFAULT '0',
                    create_date datetime NOT NULL,
                    last_update datetime NOT NULL default '0000-00-00 00:00:00',
                    status tinyint(1) NOT NULL,                    
                    in_stock tinyint(1) NOT NULL,
                    last_in_stock datetime NOT NULL default '0000-00-00 00:00:00',                    
                    row_err_count smallint(5) DEFAULT '0',
                    shop_id varchar(30) NOT NULL,                    
                    last_error varchar(255) DEFAULT NULL,
                    title text,
                    price float(12,2) DEFAULT NULL,
                    currency char(3) DEFAULT NULL,
                    old_price float(12,2) DEFAULT NULL,
                    manufacturer varchar(255) DEFAULT NULL,                    
                    img_file varchar(255) DEFAULT NULL,                    
                    img text,
                    orig_img text,
                    orig_img_large text,
                    description text,
                    orig_url_hash varchar(32) NOT NULL,
                    orig_url text,
                    extra longtext,
                    PRIMARY KEY  (id),
                    KEY egg_id_status (egg_id,status),
                    KEY egg_id_catalog_id (egg_id,catalog_id),
                    KEY num (num),
                    KEY prod_type (prod_type),
                    KEY catalog_id (catalog_id),
                    KEY orig_url_hash (orig_url_hash(10)),
                    KEY shop_id (shop_id(15)),
                    KEY last_update (last_update),                    
                    KEY create_date (create_date),
                    KEY in_stock (in_stock),
                    KEY price (price),
                    KEY old_price (old_price)
                    ) $this->charset_collate;";
    }

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function save(array $item)
    {
        $item['id'] = (int) $item['id'];

        // save img
        if ($item['img'] && GeneralConfig::getInstance()->option('save_img'))
        {
            // bug fix
            if (!empty($item['img_file']) && $item['img'] == $item['orig_img'])
            {
                @unlink(self::getFullImgPath($item['img_file']));
                $item['img_file'] = '';
            }

            if (!$item['id'] || !$item['img_file'])
            {
                $local_img_name = ImageHelper::saveImgLocaly($item['img'], $item['title']);
                if ($local_img_name)
                {
                    $uploads = \wp_upload_dir();
                    $item['img'] = $uploads['url'] . '/' . $local_img_name;
                    $item['img_file'] = ltrim(trailingslashit($uploads['subdir']), '\/') . $local_img_name;
                }
            }
        } elseif (!empty($item['img_file']))
        {
            @unlink(self::getFullImgPath($item['img_file']));
            $item['img_file'] = '';
        }
        if (!$item['id'])
        {
            $item['id'] = null;
            $item['create_date'] = current_time('mysql');
            $item['orig_url_hash'] = md5($item['orig_url']);
            $item = apply_filters('affegg_product_insert', $item);
            if ($this->getDb()->insert($this->tableName(), $item))
                $item['id'] = $this->getDb()->insert_id;
            else
                return null;
        } else
        {
            $item = apply_filters('affegg_product_update', $item);
            $this->getDb()->update($this->tableName(), $item, array('id' => $item['id']));
        }
        // save price history
        if (GeneralConfig::getInstance()->option('price_history_days'))
        {
            PriceHistoryModel::model()->saveForProduct($item);
            // ...and send price alerts
            if (GeneralConfig::getInstance()->option('price_alert_enabled'))
                PriceAlert::getInstance()->sendAlert($item);
        }

        return $item['id'];
    }

    public static function saveImgLocaly($img_uri, $title = '', $check_file_type = true)
    {
        return ImageHelper::saveImgLocaly($img_uri, $title, $check_file_type);
    }

    public function getEggProducts($egg_id, $limit = null)
    {
        $sql = 'SELECT * from ' . $this->tableName() . ' WHERE egg_id=%d ORDER by num,id';
        if ($limit)
            $sql .= ' LIMIT ' . (int) $limit;
        $sql = $this->getDb()->prepare($sql, $egg_id);
        $rows = $this->getDb()->get_results($sql, \ARRAY_A);
        if (!$rows)
            return array();

        foreach ($rows as $i => $r)
        {
            $rows[$i]['extra'] = unserialize($r['extra']);
        }
        return $rows;
    }

    public function delete($id)
    {
        $item = $this->findByPk($id);
        if ($item['img_file'] && is_file($item['img_file']))
            @unlink(self::getFullImgPath($item['img_file']));

        if (parent::delete($id))
        {
            // delete price history            
            PriceHistoryModel::model()->deleteAll(array('product_id = %d', array($id)));
        }
    }

    public function parseAndSave($url, $egg_id, $num, $catalog_id = 0)
    {
        try
        {
            $product = ParserManager::getInstance()->parseProduct($url);
        } catch (\Exception $e)
        {
            return false;
        }
        if ($product && is_array($product))
        {
            $product['id'] = null;
            $product['egg_id'] = $egg_id;
            $product['catalog_id'] = $catalog_id;
            $product['create_date'] = current_time('mysql');
            $product['last_update'] = current_time('mysql');
            $product['last_in_stock'] = current_time('mysql');
            $product['status'] = ProductModel::STATUS_VISIBLE;
            $product['num'] = $num;
            $product['shop_id'] = ParserManager::getInstance()->getShopIdByUrl($url);
            $product['extra'] = serialize($product['extra']);
            return $this->save($product);
        } else
            return false;
    }

    public function parseAndSaveImg($url, $egg_id, $num, array $img_atts = array())
    {
        if (!FileHelper::validateRemoteImage($url))
            return false;

        $product['id'] = null;
        $product['egg_id'] = $egg_id;
        $product['catalog_id'] = 0;
        $product['prod_type'] = self::PROD_TYPE_IMG;
        $product['create_date'] = current_time('mysql');
        $product['last_update'] = current_time('mysql');
        $product['status'] = ProductModel::STATUS_VISIBLE;
        $product['num'] = $num;
        $product['shop_id'] = 0;
        $product['orig_url'] = $url;
        $product['orig_img'] = $url;
        $product['img'] = $url;
        $product['description'] = '';
        $product['extra'] = serialize(array());

        if (!empty($img_atts['title']))
            $product['title'] = sanitize_text_field($img_atts['title']);
        else
            $product['title'] = '';
        if (!empty($img_atts['price']))
            $product['price'] = (float) $img_atts['price'];
        return $this->save($product);
    }

    public function parseAndUpdate($p_id)
    {
        $product = self::model()->findByPk($p_id);
        if (!$product)
            return false;
        $error = '';
        try
        {
            $data = ParserManager::getInstance()->parseProduct($product['orig_url']);
        } catch (\Exception $e)
        {
            $error = $e->getMessage();
        }
        if ($error)
        {
            $product['last_update'] = current_time('mysql');
            $product['row_err_count']++;
            $product['last_error'] = $error;
            $this->save($product);
            return false;
        } else
        {
            $product['last_update'] = current_time('mysql');
            $product['row_err_count'] = 0;
            $product['last_error'] = '';
            if ($data['in_stock'])
                $product['last_in_stock'] = current_time('mysql');
            //$product['title'] = $data['title'];
            $product['price'] = $data['price'];
            $product['in_stock'] = $data['in_stock'];
            $product['currency'] = $data['currency'];
            $product['old_price'] = $data['old_price'];
            //$product['manufacturer'] = $data['manufacturer'];
            $product['img'] = $data['img'];
            $product['orig_img'] = $data['orig_img'];
            //$product['description'] = $data['description'];
            //$product['extra'] = serialize($data['extra']);            
            return $this->save($product);
        }
    }

    public function getByUrl($url, $egg_id)
    {
        $sql = 'SELECT * from ' . $this->tableName() . ' WHERE orig_url_hash=%s AND egg_id=%d';
        $sql = $this->getDb()->prepare($sql, array(md5($url), $egg_id));
        return $this->getDb()->get_row($sql, \ARRAY_A);
    }

    public function setNum($id, $num)
    {
        $this->getDb()->update($this->tableName(), array('num' => $num), array('id' => $id));
    }

    public static function getFullImgPath($img_path)
    {
        $uploads = \wp_upload_dir();
        return trailingslashit($uploads['basedir']) . $img_path;
    }

    public function getEggWidgetProducts(array $params)
    {
        //SELECT * FROM `table` WHERE id >= (SELECT FLOOR( MAX(id) * RAND()) FROM `table` ) ORDER BY id LIMIT 1;	
        $sql = 'SELECT *';
        if ($params['sortby'] == 'discount')
            $sql .= ', old_price-price as discount';

        $sql .= ' from ' . $this->tableName();

        //where
        $ids = array();
        $where = array();
        if ($params['affegg_ids'])
        {
            $ids = explode(',', $params['affegg_ids']);
            if ($ids)
            {
                $inQuery = implode(',', array_fill(0, count($ids), '%d'));
                $where[] = 'egg_id IN(' . $inQuery . ')';
            }
        }
        if ($params['in_stock'])
            $where[] = 'in_stock = 1';
        if ($params['sortby'] == 'discount')
            $where[] = 'old_price != 0';
        if ($where)
            $sql .= " WHERE " . implode(" AND ", $where);

        //order
        if ($params['sortby'] == 'last')
            $params['sortby'] = "egg_id";
        $sql .= ' ORDER BY ';
        if ($params['sortby'] == 'random')
            $sql .= 'RAND()';
        elseif ($params['sortby'] == 'create_date')
            $sql .= 'create_date DESC';
        elseif ($params['sortby'] == 'last_update')
            $sql .= 'last_update DESC';
        elseif ($params['sortby'] == 'egg_id')
            $sql .= 'egg_id DESC';
        elseif ($params['sortby'] == 'discount')
            $sql .= 'discount DESC';

        //limit
        if ($params['limit'])
            $sql .= ' LIMIT ' . (int) $params['limit'];
        if ($ids)
            $sql = $this->getDb()->prepare($sql, $ids);
        $rows = $this->getDb()->get_results($sql, \ARRAY_A);
        if (!$rows)
            return array();
        foreach ($rows as $i => $r)
        {
            $rows[$i]['extra'] = unserialize($r['extra']);
        }
        return $rows;
    }

}
