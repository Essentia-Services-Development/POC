<?php

namespace ExternalImporter\application\models;

defined('\ABSPATH') || exit;

/**
 * Model class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
abstract class Model {

    public static $db;
    private static $models = array();
    protected $charset_collate = '';

    abstract public function tableName();

    abstract public function getDump();

    public function __construct()
    {
        if (!empty($this->getDb()->charset))
            $this->charset_collate = 'DEFAULT CHARACTER SET ' . $this->getDb()->charset;
        if (!empty($this->getDb()->collate))
            $this->charset_collate .= ' COLLATE ' . $this->getDb()->collate;
        if (!$this->charset_collate)
            $this->charset_collate = '';
    }

    public function attributeLabels()
    {
        return array();
    }

    public function getDb()
    {
        if (self::$db !== null)
            return self::$db;
        else
        {
            self::$db = $GLOBALS['wpdb'];
            return self::$db;
        }
    }

    public static function model($className = __CLASS__)
    {
        if (isset(self::$models[$className]))
            return self::$models[$className];
        else
        {
            return self::$models[$className] = new $className;
        }
    }

    public function getAttributeLabel($attribute)
    {
        $labels = $this->attributeLabels();
        if (isset($labels[$attribute]))
            return $labels[$attribute];
        else
            return $this->generateAttributeLabel($attribute);
    }

    public function generateAttributeLabel($name)
    {
        return ucwords(trim(strtolower(str_replace(array('-', '_', '.'), ' ', preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $name)))));
    }

    public function find(array $params)
    {
        return $this->getDb()->get_row($this->prepareFindSql($params), \ARRAY_A);
    }

    public function findAll(array $params)
    {
        return $this->getDb()->get_results($this->prepareFindSql($params), \ARRAY_A);
    }

    private function prepareFindSql(array $params)
    {
        $values = array();
        $sql = 'SELECT ';

        if (!empty($params['select']))
            $sql .= $params['select'];
        else
            $sql .= ' *';
        $sql .= ' FROM ' . $this->tableName();
        if ($params)
        {
            if (!empty($params['where']))
            {
                if (is_array($params['where']) && isset($params['where'][0]) && isset($params['where'][1]))
                {
                    $sql .= ' WHERE ' . $params['where'][0];
                    $values += $params['where'][1];
                } elseif (!is_array($params['where']))
                    $sql .= ' WHERE ' . $params['where'];
            }
            if (!empty($params['group']))
            {
                $sql .= ' GROUP BY ' . $params['group'];
            }
            if (!empty($params['order']))
            {
                $sql .= ' ORDER BY ' . $params['order'];
            }
            if (!empty($params['limit']))
            {
                $sql .= ' LIMIT %d';
                $values[] = $params['limit'];
            }
            if (!empty($params['offset']))
            {
                $sql .= ' OFFSET %d';
                $values[] = $params['offset'];
            }

            if ($values)
                $sql = $this->getDb()->prepare($sql, $values);
        }
        return $sql;
    }

    public function findByPk($id)
    {
        return $this->getDb()->get_row($this->getDb()->prepare('SELECT * FROM ' . $this->tableName() . ' WHERE id = %d', $id), ARRAY_A);
    }

    public function delete($id)
    {
        return $this->getDb()->delete($this->tableName(), array('id' => $id), array('%d'));
    }

    public function deleteAll($where)
    {
        $values = array();
        $sql = 'DELETE FROM ' . $this->tableName();
        $sql .= $this->prepareWhere($where);
        return $this->getDb()->query($sql);
    }

    public function count($where = null)
    {
        $sql = "SELECT COUNT(*) FROM " . $this->tableName();
        if ($where)
            $sql .= $this->prepareWhere($where);
        return $this->getDb()->get_var($sql);
    }

    public function max($colum, $where = null)
    {
        $sql = "SELECT MAX(" . $colum . ") FROM " . $this->tableName();
        if ($where)
            $sql .= $this->prepareWhere($where);
        return $this->getDb()->get_var($sql);
    }

    public function min($colum, $where = null)
    {
        $sql = "SELECT MIN(" . $colum . ") FROM " . $this->tableName();
        if ($where)
            $sql .= $this->prepareWhere($where);
        return $this->getDb()->get_var($sql);
    }

    public function avg($colum, $where = null)
    {
        $sql = "SELECT AVG(" . $colum . ") FROM " . $this->tableName();
        if ($where)
            $sql .= $this->prepareWhere($where);
        return $this->getDb()->get_var($sql);
    }

    public function prepareWhere($where, $winclude = true)
    {
        if ($winclude)
            $sql = ' WHERE ';
        else
            $sql = '';
        $values = array();
        if (is_array($where) && isset($where[0]) && isset($where[1]))
        {
            $sql .= $where[0];
            $values += $where[1];
        } elseif (is_string($where))
            $sql .= $where;
        else
            throw new \Exception('Wrong WHERE params.');
        if ($values)
            $sql = $this->getDb()->prepare($sql, $values);
        return $sql;
    }

    public function save(array $item)
    {
        $item['id'] = (int) $item['id'];
        if (!$item['id'])
        {
            $item['id'] = null;
            $this->getDb()->insert($this->tableName(), $item);
            return $this->getDb()->insert_id;
        } else
        {
            $this->getDb()->update($this->tableName(), $item, array('id' => $item['id']));
            return $item['id'];
        }
    }

    public function cleanOld($days, $optimize = true, $date_field = 'create_date')
    {
        $this->deleteAll('TIMESTAMPDIFF( DAY, ' . $date_field . ', "' . \current_time('mysql') . '") > ' . $days);
        if ($optimize)
            $this->optimizeTable();
    }

    public function optimizeTable()
    {
        $this->getDb()->query('OPTIMIZE TABLE ' . $this->tableName());
    }

}
