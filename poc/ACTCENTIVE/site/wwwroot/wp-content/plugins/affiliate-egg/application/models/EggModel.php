<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * EggModel class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class EggModel extends Model {

    public function tableName()
    {
        return $this->getDb()->prefix . 'affegg_egg';
    }

    public function getDump()
    {
        return "CREATE TABLE " . $this->tableName() . " (
                    id int(11) unsigned NOT NULL auto_increment,
                    create_date datetime NOT NULL,
                    update_date datetime NOT NULL default '0000-00-00 00:00:00',
                    name varchar(200) DEFAULT NULL,
                    prod_limit smallint(3) DEFAULT 0,
                    template varchar(100) DEFAULT NULL,
                    user_id int(11) DEFAULT NULL,
                    PRIMARY KEY  (id),
                    KEY create_date (create_date),
                    KEY update_date (update_date)                    
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
            'name' => __('Name', 'affegg'),
            'create_date' => __('Date created', 'affegg'),
            'update_date' => __('Last updated', 'affegg'),
        );
    }

    public function save(array $item)
    {
        $item['id'] = (int) $item['id'];
        if (!$item['id'])
        {
            if (!isset($item['user_id']))
                $item['user_id'] = get_current_user_id();
            $item['id'] = null;
            $item['create_date'] = current_time('mysql');
            $this->getDb()->insert($this->tableName(), $item);

            do_action('affegg_product_create', $item);
            return $this->getDb()->insert_id;
        } else
        {
            $this->getDb()->update($this->tableName(), $item, array('id' => $item['id']));

            do_action('affegg_product_update', $item);
            return $item['id'];
        }
    }

    public function validate(array $item)
    {
        if (!in_array($item['template'], array_keys(TemplateManager::getInstance()->getEggTemplatesList())))
            return false;
        return true;
    }

    public function delete($id)
    {
        if (parent::delete($id))
        {
            $catalogs = CatalogModel::model()->findAll(array('select' => 'id', 'where' => array('egg_id = %d', array($id))));
            foreach ($catalogs as $catalog)
            {
                CatalogModel::model()->delete($catalog['id']);
            }
            $products = ProductModel::model()->findAll(array('select' => 'id', 'where' => array('egg_id = %d AND catalog_id=0', array($id))));
            foreach ($products as $product)
            {
                ProductModel::model()->delete($product['id']);
            }
        }
    }

    public function forcedUpdateProducts($id)
    {

        $params = array(
            'select' => 'id',
            'where' => array('egg_id = %d', array($id)),
        );
        $products = ProductModel::model()->findAll($params);

        $product_sleep = GeneralConfig::getInstance()->option('product_update_sleep');

        $time_limit = round(300 + (count($products) * $product_sleep / 1000000));
        @set_time_limit($time_limit);

        foreach ($products as $key => $product)
        {
            if ($product_sleep && $key > 0)
                usleep($product_sleep);

            ProductModel::model()->parseAndUpdate($product['id']);
        }
    }

    public function forcedUpdateCatalogs($id)
    {

        @set_time_limit(600);
        $params = array(
            'select' => 'id',
            'where' => array('egg_id = %d', array($id)),
        );
        $catalogs = CatalogModel::model()->findAll($params);
        foreach ($catalogs as $catalog)
        {
            CatalogModel::model()->parseAndUpdate($catalog['id']);
        }
    }

}
