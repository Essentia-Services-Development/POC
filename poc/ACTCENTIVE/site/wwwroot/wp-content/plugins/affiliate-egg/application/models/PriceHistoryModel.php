<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * PriceHistoryModel class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class PriceHistoryModel extends Model {

    public function tableName()
    {
        return $this->getDb()->prefix . 'affegg_price_history';
    }

    public function getDump()
    {
        return "CREATE TABLE " . $this->tableName() . " (
                    id bigint(20) unsigned NOT NULL auto_increment,
                    product_id int(11) unsigned NOT NULL,
                    create_date datetime NOT NULL,
                    price float(12,2) NOT NULL,                    
                    PRIMARY KEY  (id),
                    KEY product_id (product_id),
                    KEY create_date (create_date)
                    ) $this->charset_collate;";
    }

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function save(array $item)
    {
        if (empty($item['create_date']))
            $item['create_date'] = \current_time('mysql');
        $this->getDb()->insert($this->tableName(), $item);
        return true;
    }

    public function getLastPriceValue($product_id, $offset = null)
    {
        $params = array(
            'select' => 'price',
            'where' => array('product_id = %d', array($product_id)),
            'order' => 'create_date DESC',
            'limit' => 1
        );
        if ($offset)
            $params['offset'] = $offset;
        $row = $this->find($params);
        if (!$row)
            return null;
        return $row['price'];
    }

    public function getPreviousPriceValue($product_id)
    {
        return $this->getLastPriceValue($product_id, 1);
    }

    public function getFirstDateValue($product_id)
    {
        $params = array(
            'select' => 'create_date',
            'where' => array('product_id = %d', array($product_id)),
            'order' => 'create_date ASC',
            'limit' => 1
        );
        $row = $this->find($params);
        if (!$row)
            return null;
        return $row['create_date'];
    }

    public function getLastPrices($product_id, $limit = 5)
    {
        $params = array(
            'where' => array('product_id = %d', array($product_id)),
            'order' => 'create_date DESC',
            'limit' => $limit,
        );
        return $this->findAll($params);
    }

    public function getMaxPrice($product_id)
    {
        $where = $this->prepareWhere((array('product_id = %s', array($product_id))));
        $sql = 'SELECT t.* FROM ' . $this->tableName() . ' t';
        $sql .= ' JOIN (SELECT product_id, MAX(price) price FROM ' . $this->tableName() . $where . ') t2 ON t.price = t2.price AND t.product_id = t2.product_id;';
        return $this->getDb()->get_row($sql, \ARRAY_A);
    }

    public function getMinPrice($product_id)
    {
        $where = $this->prepareWhere((array('product_id = %s', array($product_id))));
        $sql = 'SELECT t.* FROM ' . $this->tableName() . ' t';
        $sql .= ' JOIN (SELECT product_id, MIN(price) price FROM ' . $this->tableName() . $where . ') t2 ON t.price = t2.price AND t.product_id = t2.product_id;';
        return $this->getDb()->get_row($sql, \ARRAY_A);
    }

    public function saveForProduct(array $product)
    {
        if (empty($product['id']) || empty($product['price']))
            return false;

        $latest_price = $this->getLastPriceValue($product['id']);

        // price changed?
        if ($latest_price && (float) $latest_price == (float) $product['price'])
            return false;

        $save = array(
            'product_id' => $product['id'],
            'price' => $product['price'],
        );
        $id = $this->save($save);

        // clean up & optimize
        if (rand(1, 10) == 10)
        {
            $this->cleanOld((int) GeneralConfig::getInstance()->option('price_history_days'));
        }

        return $id;
    }

}
