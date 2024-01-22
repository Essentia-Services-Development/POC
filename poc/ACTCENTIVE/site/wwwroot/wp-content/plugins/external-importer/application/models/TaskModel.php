<?php

namespace ExternalImporter\application\models;

defined('\ABSPATH') || exit;

/**
 * TaskModel class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class TaskModel extends Model {

    const DELETE_OLDER_DAYS = 1;

    public function tableName()
    {
        return $this->getDb()->prefix . 'exi_task';
    }

    public function getDump()
    {
        return "CREATE TABLE " . $this->tableName() . " (
                    id bigint(20) unsigned NOT NULL auto_increment,
                    type tinyint(1) unsigned DEFAULT NULL,
                    create_date datetime NOT NULL,
                    update_date datetime DEFAULT NULL,
                    hash char(32) NOT NULL,
                    init_data longtext,
                    data longtext,
                    PRIMARY KEY  (id),
                    KEY hash (hash),
                    KEY create_date (create_date)                    
                    ) $this->charset_collate;";
    }

    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function save(array $item)
    {
        // clean up & optimize
        if (rand(1, 20) == 20)
            $this->cleanOld(self::DELETE_OLDER_DAYS);

        if (empty($item['init_data']) || !is_array($item['init_data']))
            throw new \Exception('Init data can not be empty.');

        if (isset($item['id']))
            $item['id'] = (int) $item['id'];
        else
        {
            $item['id'] = null;
            $item['create_date'] = \current_time('mysql');
        }
        $item['update_date'] = \current_time('mysql');

        if (empty($item['hash']))
            $item['hash'] = TaskModel::generateTaskHash($item['init_data']);

        if (!isset($item['data']))
            $item['data'] = array();
        $item['init_data'] = serialize($item['init_data']);
        $item['data'] = serialize($item['data']);
        return parent::save($item);
    }

    // create or update
    public function createOrUpdate(array $init_data, $data)
    {
        if (!$task = $this->getTask($init_data))
        {
            $task = array();
            $task['init_data'] = $init_data;
        }
        $task['data'] = $data;
        return self::save($task);
    }

    public function getTask(array $init_data)
    {
        $hash = TaskModel::generateTaskHash($init_data);
        return $this->getTaskByHash($hash);
    }

    public function isTaskExists(array $init_data)
    {
        if ($this->getTask($init_data))
            return true;
        else
            return false;
    }

    public function getTaskByHash($hash)
    {
        $params = array(
            'select' => '*',
            'where' => array('hash = %s', array($hash)),
        );
        if (!$task = $this->find($params))
            return false;

        $task['init_data'] = unserialize($task['init_data']);
        $task['data'] = unserialize($task['data']);
        return $task;
    }

    public static function generateTaskHash(array $init_data)
    {
        return md5(serialize($init_data));
    }

}
