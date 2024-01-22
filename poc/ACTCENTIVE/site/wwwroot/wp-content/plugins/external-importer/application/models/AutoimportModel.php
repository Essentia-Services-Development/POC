<?php

namespace ExternalImporter\application\models;

defined('\ABSPATH') || exit;

use ExternalImporter\application\helpers\TextHelper;

/**
 * AutoimportModel class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class AutoimportModel extends Model {

    const INACTIVATE_AFTER_ERROR_COUNT = 20;

    public function tableName()
    {
        return $this->getDb()->prefix . 'exi_autoimport';
    }

    public function getDump()
    {
        return "CREATE TABLE " . $this->tableName() . " (
                    id bigint(20) unsigned NOT NULL auto_increment,
                    create_date datetime NOT NULL,
                    status tinyint(1) unsigned DEFAULT NULL,
                    recurrency int(11) NOT NULL,
                    process_products tinyint(3) NOT NULL,                    
                    last_run datetime DEFAULT NULL,
                    listing_url text,
                    domain varchar(32) NOT NULL,                    
                    name varchar(200) DEFAULT NULL,    
                    post_count int(11) DEFAULT '0',                    
                    row_err_count smallint(5) DEFAULT '0',
                    last_error varchar(255) DEFAULT NULL,
                    extra longtext,
                    PRIMARY KEY  (id),
                    KEY run (status,last_run,recurrency),                   
                    KEY domain (domain)
                    ) $this->charset_collate;";
    }

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function save(array $item)
    {
        $item['id'] = (int) $item['id'];

        if (!$item['id'])
        {
            $item['id'] = null;

            if (empty($item['listing_url']))
                throw new \Exception('Listing URL can not be empty.');

            $item['create_date'] = \current_time('mysql');
            $item['domain'] = TextHelper::getHostName($item['listing_url']);
        }

        if (!isset($item['extra']))
            $item['extra'] = array();

        if (is_array($item['extra']))
            $item['extra'] = serialize($item['extra']);

        return parent::save($item);
    }

}
