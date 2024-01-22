<?php

namespace ContentEgg\application\models;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\admin\GeneralConfig;

/**
 * PriceHistoryModel class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class PriceHistoryModel extends Model {

	public function tableName() {
		return $this->getDb()->prefix . 'cegg_price_history';
	}

	public function getDump() {

		return "CREATE TABLE " . $this->tableName() . " (
                    unique_id varchar(255) NOT NULL,
                    module_id varchar(255) NOT NULL,
                    create_date datetime NOT NULL,
                    price float(12,2) NOT NULL,
                    price_old float(12,2) DEFAULT NULL,
                    price_old_date datetime DEFAULT NULL,
                    post_id bigint(20) unsigned DEFAULT NULL,
                    is_latest tinyint(1) DEFAULT 0,
                    KEY uid (unique_id(80),module_id(30)),
                    KEY create_date (create_date),
                    KEY price (price),
                    KEY price_old (price_old),
                    KEY is_latest (is_latest)
                    ) $this->charset_collate;";
	}

	public static function model( $className = __CLASS__ ) {
		return parent::model( $className );
	}

	public function save( array $item ) {
		$item['is_latest'] = 1;
		if ( empty( $item['create_date'] ) ) {
			$item['create_date'] = current_time( 'mysql' );
		}

		if ( empty( $item['price_old'] ) ) {
			$old_data = $this->getOldPrice( $item['unique_id'], $item['module_id'] );
			if ( $old_data ) {
				$item['price_old']      = $old_data['price'];
				$item['price_old_date'] = $old_data['create_date'];
			}
		}

		$this->getDb()->update( $this->tableName(), array( 'is_latest' => 0 ), array(
			'unique_id' => $item['unique_id'],
			'module_id' => $item['module_id']
		) );
		$this->getDb()->insert( $this->tableName(), $item );

		\do_action( 'content_egg_price_history_save', $item );

		return true;
	}

	private function getOldPrice( $unique_id, $module_id ) {
		// price known date
		$price_drops_days = (int) GeneralConfig::getInstance()->option( 'price_drops_days' );
		$sql              = 'SELECT create_date FROM ' . $this->tableName() . ' WHERE unique_id = %s AND module_id = %s AND create_date <= NOW() - INTERVAL %d DAY ORDER BY create_date DESC LIMIT 1';
		$sql              = $this->getDb()->prepare( $sql, array( $unique_id, $module_id, $price_drops_days ) );
		$known_date       = $this->getDb()->get_var( $sql );

		$where = '';
		if ( $known_date ) {
			$where = $this->getDb()->prepare( 'create_date > %s', array( $known_date ) );
		} else {
			$where = $this->getDb()->prepare( 'create_date >= NOW() - INTERVAL %d DAY', array( $price_drops_days ) );
		}

		$sql = 'SELECT t.*
            FROM ' . $this->tableName() . ' t
            WHERE price=(SELECT MAX(price) FROM ' . $this->tableName() . ' WHERE unique_id = %s AND module_id = %s AND ' . $where . ')
            AND unique_id = %s AND module_id = %s AND ' . $where;

		$sql      = $this->getDb()->prepare( $sql, array( $unique_id, $module_id, $unique_id, $module_id ) );
		$old_data = $this->getDb()->get_row( $sql, \ARRAY_A );

		return $old_data;
	}

	public function getLastPriceValue( $unique_id, $module_id, $offset = null ) {
		$params = array(
			'select' => 'price',
			'where'  => array( 'unique_id = %s AND module_id = %s', array( $unique_id, $module_id ) ),
			'order'  => 'create_date DESC',
			'limit'  => 1
		);
		if ( $offset ) {
			$params['offset'] = $offset;
		}
		$row = $this->find( $params );
		if ( ! $row ) {
			return null;
		}

		return $row['price'];
	}

	public function getPreviousPriceValue( $unique_id, $module_id ) {
		return $this->getLastPriceValue( $unique_id, $module_id, 1 );
	}

	public function getFirstDateValue( $unique_id, $module_id ) {
		$params = array(
			'select' => 'create_date',
			'where'  => array( 'unique_id = %s AND module_id = %s', array( $unique_id, $module_id ) ),
			'order'  => 'create_date ASC',
			'limit'  => 1
		);
		$row    = $this->find( $params );
		if ( ! $row ) {
			return null;
		}

		return $row['create_date'];
	}

	public function getLastPrices( $unique_id, $module_id, $limit = 5 ) {
		$params = array(
			'where' => array( 'unique_id = %s AND module_id = %s', array( $unique_id, $module_id ) ),
			'order' => 'create_date DESC',
			'limit' => $limit,
		);

		return $this->findAll( $params );
	}

	public function getMaxPrice( $unique_id, $module_id ) {
		$where = $this->prepareWhere( ( array(
			'unique_id = %s AND module_id = %s',
			array( $unique_id, $module_id )
		) ) );
		$sql   = 'SELECT t.* FROM ' . $this->tableName() . ' t';
		$sql   .= ' JOIN (SELECT unique_id, MAX(price) maxPrice FROM ' . $this->tableName() . $where . ') t2 ON t.price = t2.maxPrice AND t.unique_id = t2.unique_id;';

		return $this->getDb()->get_row( $sql, \ARRAY_A );
	}

	public function getMinPrice( $unique_id, $module_id ) {
		$where = $this->prepareWhere( ( array(
			'unique_id = %s AND module_id = %s',
			array( $unique_id, $module_id )
		) ) );
		$sql   = 'SELECT t.* FROM ' . $this->tableName() . ' t';
		$sql   .= ' JOIN (SELECT unique_id, MIN(price) minPrice FROM ' . $this->tableName() . $where . ') t2 ON t.price = t2.minPrice AND t.unique_id = t2.unique_id;';

		return $this->getDb()->get_row( $sql, \ARRAY_A );
	}

	public function saveData( array $data, $module_id, $post_id = null ) {
		if ( ! $post_id ) {
			global $post;
			if ( ! empty( $post ) ) {
				$post_id = $post->ID;
			}
		}
		$saved = 0;
		foreach ( $data as $key => $d ) {
			if ( empty( $d['unique_id'] ) || empty( $d['price'] ) ) {
				continue;
			}

			$latest_price = $this->getLastPriceValue( $d['unique_id'], $module_id );

			// price changed?
			if ( $latest_price && (float) $latest_price == (float) $d['price'] ) {
				continue;
			}

			$save = array(
				'unique_id' => $d['unique_id'],
				'module_id' => $module_id,
				'price'     => $d['price'],
				'post_id'   => $post_id,
			);
			$this->save( $save );
			$saved ++;
		}

		// clean up & optimize
		if ( $saved && rand( 1, 10 ) == 10 ) {
			$this->cleanOld( (int) GeneralConfig::getInstance()->option( 'price_history_days' ) );
		}
	}

	public function getPriceMoversOld( array $params = array() ) {

		$defaults = array(
			'time_period' => 7,
			'limit'       => 5,
			'drop_type'   => 'absolute',
			'direction'   => 'drops',
		);
		$params   = \wp_parse_args( $params, $defaults );

		$params['time_period'] = (int) $params['time_period'];
		$params['limit']       = (int) $params['limit'];

		if ( $params['direction'] == 'drops' ) {
			$order = 'DESC';
		} else {
			$order = 'ASC';
		}

		if ( $params['drop_type'] == 'relative' ) {
			$change = '(100 - (p_last.price * 100) / p_prev.price)';
		} //relative
		else {
			$change = '(p_prev.price - p_last.price)';
		} // absolute

		$sql = '
            SELECT
               MAX(p_last.create_date) as last_date,
               p_last.unique_id,
               p_last.post_id,
               p_last.module_id,
               p_prev.price as old_price,
               p_prev.create_date as old_date,
               p_last.price as last_price,
               ' . $change . ' as `change`
            FROM ' . $this->tableName() . ' p_last
               INNER JOIN (SELECT unique_id, create_date, MAX(price) as price FROM ' . $this->tableName() . ' GROUP BY unique_id) AS p_prev
                    ON p_last.unique_id = p_prev.unique_id	 
                    AND p_last.create_date >= NOW() - INTERVAL ' . $params['time_period'] . ' DAY
               INNER JOIN ' . $this->getDb()->posts . ' AS post
                   ON post.ID = p_last.post_id
                   AND post.post_status = "publish"
            GROUP BY unique_id	
            ORDER BY `change` ' . $order . '
            LIMIT ' . $params['limit'];

		return $this->getDb()->get_results( $sql, \ARRAY_A );
	}

	public function getPriceMovers( array $params = array(), $double_limit = false ) {
		$defaults              = array(
			'limit'       => 5,
			'last_update' => 7,
			'drop_type'   => 'absolute',
			'direction'   => 'drops',
		);
		$params                = \wp_parse_args( $params, $defaults );
		$params['limit']       = (int) $params['limit'];
		$params['last_update'] = (int) $params['last_update'];
		if ( $params['direction'] == 'drops' ) {
			$order           = 'DESC';
			$direction_where = 'price_old - price >= 0';
		} else {
			$order           = 'ASC';
			$direction_where = 'price_old - price <= 0';
		}

		$limit = $params['limit'];
		if ( $double_limit ) {
			$limit *= 2;
		}

		if ( $params['drop_type'] == 'relative' ) {
			$change = '(100 - (price * 100) / price_old)';
		} else {
			$change = '(price_old - price)';
		} // absolute

		$sql     = '
            SELECT
                price_history.*, ' . $change . ' as pchange
            FROM ' . $this->tableName() . ' as price_history
               INNER JOIN ' . $this->getDb()->posts . ' AS post
                   ON post.ID = price_history.post_id
                   AND post.post_status = "publish"
            WHERE ' . $direction_where . ' AND is_latest = 1 AND create_date >= NOW() - INTERVAL ' . $params['last_update'] . ' DAY
            GROUP BY unique_id	
            ORDER BY pchange ' . $order . '
            LIMIT ' . $limit;
		$results = $this->getDb()->get_results( $sql, \ARRAY_A );

		$return = array();
		foreach ( $results as $i => $r ) {
			if ( \get_post_status( $r['post_id'] ) == 'publish' ) {
				$return[] = $r;
			}
		}

		return $return;
	}

}
