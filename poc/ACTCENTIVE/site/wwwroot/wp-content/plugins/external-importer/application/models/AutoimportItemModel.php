<?php

namespace ExternalImporter\application\models;

defined('\ABSPATH') || exit;

/**
 * AutoimportItemModel class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class AutoimportItemModel extends Model {

    public function tableName()
    {
        return $this->getDb()->prefix . 'exi_autoimport_item';
    }

    public function getDump()
    {
        return "CREATE TABLE " . $this->tableName() . " (
                    id int(11) unsigned NOT NULL auto_increment,
                    autoimport_id int(11) unsigned NOT NULL,
                    create_date datetime NOT NULL,
                    url_hash char(32) NOT NULL,                    
                    PRIMARY KEY  (id),
                    KEY autoimport_id_url_hash (autoimport_id, url_hash)
                    ) $this->charset_collate;";
    }

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function save(array $item)
    {
        $item['create_date'] = \current_time('mysql');
        $this->getDb()->insert($this->tableName(), $item);
    }

    public static function generateUrlHash($url)
    {
        return md5($url);
    }

}
