<?php

namespace ExternalImporter\application\models;

defined('\ABSPATH') || exit;

/**
 * QueryModel class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class QueryModel extends Model {

    const DELETE_OLDER_DAYS = 1;

    public function tableName()
    {
        return $this->getDb()->prefix . 'exi_query';
    }

    public function getDump()
    {
        return "CREATE TABLE " . $this->tableName() . " (
                    id bigint(20) unsigned NOT NULL auto_increment,
                    create_date datetime NOT NULL,
                    domain varchar(32) NOT NULL,
                    error_code smallint(3) unsigned DEFAULT 0,
                    PRIMARY KEY  (id),
                    KEY domain (domain),
                    KEY error_code (error_code),
                    KEY create_date (create_date)                    
                    ) $this->charset_collate;";
    }

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function save(array $item)
    {
        if (rand(1, 5) == 1)
            $this->cleanOld(self::DELETE_OLDER_DAYS);

        if (isset($item['id']))
            $item['id'] = (int) $item['id'];
        else
            $item['id'] = null;

        if (!$item['id'])
        {
            $item['id'] = null;
            $item['create_date'] = \current_time('mysql');
        }

        return parent::save($item);
    }

    public function getDailyList($limit)
    {
        $today_start = \current_time('Y-m-d 00:00:00');
        $sql = 'SELECT domain FROM ' . $this->tableName() . ' WHERE create_date > %s GROUP BY domain HAVING count(id) >= %d';
        $sql = $this->getDb()->prepare($sql, array($today_start, $limit));
        return $this->getDb()->get_col($sql);
    }

    public function getErroredList($hours, $errors_count)
    {
        $sql = 'SELECT domain FROM ' . $this->tableName() . ' WHERE TIMESTAMPDIFF(HOUR, create_date, %s) < %d';
        $sql .= ' AND error_code != 0';
        $sql .= ' GROUP BY domain HAVING count(id) >= %d';
        $sql = $this->getDb()->prepare($sql, array(\current_time('mysql'), $hours, $errors_count));
        return $this->getDb()->get_col($sql);
    }

}
