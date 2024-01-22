<?php

namespace ContentEgg\application\models;

defined('\ABSPATH') || exit;

use ContentEgg\application\helpers\TextHelper;

/**
 * FeedProductModel class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
abstract class FeedProductModel extends Model
{

    public function getDump()
    {
        return "CREATE TABLE " . $this->tableName() . " (
                    id bigint(20) unsigned NOT NULL,
                    stock_status tinyint(1) DEFAULT 0,
                    price float(12,2) DEFAULT NULL,
                    title text,
                    ean varchar(13) DEFAULT NULL,
                    orig_url text,
                    product text,
                    PRIMARY KEY  (id),
                    KEY uid (stock_status),
                    KEY orig_url (orig_url(60)),
                    KEY ean (ean(13)),
                    KEY price (price),
                    FULLTEXT (title)
                    ) $this->charset_collate;";
    }

    public function searchByUrl($url, $partial_match = false, $limit = 1)
    {
        $like = $this->getDb()->esc_like($url);
        if ($partial_match)
        {
            $like .= '%';
        }

        if (!$partial_match)
        {
            $limit = 1;
        }

        $sql = $this->getDb()->prepare('SELECT * FROM ' . $this->tableName() . ' WHERE orig_url LIKE %s LIMIT %d', $like, $limit);

        return $this->getDb()->get_results($sql, \ARRAY_A);
    }

    public function searchByEan($ean, $limit = 10)
    {
        $ean = TextHelper::fixEan($ean);
        $sql = $this->getDb()->prepare('SELECT * FROM ' . $this->tableName() . ' WHERE ean = %s LIMIT %d', $ean, $limit);

        return $this->getDb()->get_results($sql, \ARRAY_A);
    }

    public function searchByKeyword($keyword, $limit = 10, $options = array())
    {
        $where = '';
        if (!empty($options['price_min']))
        {
            $where = $this->getDb()->prepare('price >= %d', $options['price_min']);
        }

        if (!empty($options['price_max']))
        {
            if ($where)
            {
                $where .= ' AND ';
            }
            $where .= $this->getDb()->prepare('price <= %d', $options['price_max']);
        }
        if ($where)
        {
            $where = ' AND ' . $where;
        }

        if (isset($options['search_type']) && $options['search_type'] == 'exact')
            $sql = $this->getDb()->prepare('SELECT * FROM ' . $this->tableName() . ' WHERE title COLLATE utf8mb4_unicode_520_ci LIKE %s' . $where . ' LIMIT %d', '%' . $keyword . '%', $limit);
        else
            $sql = $this->getDb()->prepare('SELECT * FROM ' . $this->tableName() . ' WHERE MATCH (title) AGAINST (%s)' . $where . ' LIMIT %d', $keyword, $limit);

        return $this->getDb()->get_results($sql, \ARRAY_A);
    }

    public function searchById($id)
    {
        $sql = $this->getDb()->prepare('SELECT * FROM ' . $this->tableName() . ' WHERE id = %s LIMIT 1', $id);

        return $this->getDb()->get_row($sql, \ARRAY_A);
    }

    public function getAllUrls()
    {
        return $this->getDb()->get_col('SELECT orig_url FROM ' . $this->tableName());
    }

    public function getEans()
    {
        return $this->getDb()->get_col('SELECT ean FROM ' . $this->tableName() . ' WHERE ean != ""');
    }

    public function getDublicateEans()
    {
        return $this->getDb()->get_col('SELECT ean, COUNT(*) c FROM ' . $this->tableName() . ' WHERE ean != "" GROUP BY ean HAVING c > 1');
    }

}
