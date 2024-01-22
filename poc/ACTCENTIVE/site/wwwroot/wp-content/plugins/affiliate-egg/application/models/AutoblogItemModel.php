<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * AutoblogItemModel class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class AutoblogItemModel extends Model {

    public function tableName()
    {
        return $this->getDb()->prefix . 'affegg_autoblog_item';
    }

    public function getDump()
    {

        return "CREATE TABLE " . $this->tableName() . " (
                    id int(11) unsigned NOT NULL auto_increment,
                    autoblog_id int(11) unsigned NOT NULL,
                    create_date datetime NOT NULL,
                    url_hash char(32) NOT NULL,                    
                    PRIMARY KEY  (id),
                    KEY autoblog_id_url_hash (autoblog_id, url_hash)
                    ) $this->charset_collate;";
    }

    public function save(array $item)
    {
        $item['create_date'] = current_time('mysql');
        $this->getDb()->insert($this->tableName(), $item);
    }

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public static function createUrlHash($url)
    {
        return md5($url);
    }

}
