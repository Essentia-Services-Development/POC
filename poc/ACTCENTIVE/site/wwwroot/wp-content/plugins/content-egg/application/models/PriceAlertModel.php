<?php

namespace ContentEgg\application\models;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\helpers\TextHelper;

/**
 * PriceAlertModel class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class PriceAlertModel extends Model {

	const STATUS_INACTIVE = 0;
	const STATUS_ACTIVE = 1;
	const STATUS_DELETED = 3;
	const CLEAN_DELETED_DAYS = 180;

	public function tableName() {
		return $this->getDb()->prefix . 'cegg_price_alert';
	}

	public function getDump() {
		return "CREATE TABLE " . $this->tableName() . " (
                    id bigint(20) unsigned NOT NULL auto_increment,
                    unique_id varchar(255) NOT NULL,
                    module_id varchar(255) NOT NULL,
                    post_id bigint(20) unsigned DEFAULT NULL,
                    create_date datetime NOT NULL,
                    complet_date datetime NOT NULL default '0000-00-00 00:00:00',                    
                    email varchar(255) NOT NULL,
                    price float(12,2) DEFAULT NULL,                    
                    start_price float(12,2) DEFAULT NULL,                    
                    status tinyint(1) DEFAULT 0,
                    activkey varchar(16) NOT NULL,
                    PRIMARY KEY  (id),
                    KEY uid (unique_id(80),module_id(30)),
                    KEY status (status)
                    ) $this->charset_collate;";
	}

	public static function model( $className = __CLASS__ ) {
		return parent::model( $className );
	}

	public function save( array $item ) {
		if ( empty( $item['create_date'] ) ) {
			$item['create_date'] = \current_time( 'mysql' );
		}
		if ( ! isset( $item['status'] ) ) {
			$item['status'] = self::STATUS_INACTIVE;
		}
		if ( ! isset( $item['activkey'] ) ) {
			$item['activkey'] = TextHelper::randomPassword( 16 );
		}

		return parent::save( $item );
	}

	public function cleanOld( $days, $optimize = true, $date_field = 'complet_date' ) {
		$this->deleteAll( 'status = ' . self::STATUS_DELETED . ' AND TIMESTAMPDIFF( DAY, ' . $date_field . ', "' . \current_time( 'mysql' ) . '") > ' . $days );
		if ( $optimize ) {
			$this->optimizeTable();
		}
	}

	public function unsubscribeAll( $email ) {
		$sql = 'DELETE FROM ' . $this->tableName() . ' WHERE email = %s AND status != %d';
		$this->getDb()->query( $this->getDb()->prepare( $sql, $email, self::STATUS_DELETED ) );
	}

	public static function getStatus( $id ) {
		$statuses = self::getStatuses();
		if ( isset( $statuses[ $id ] ) ) {
			return $statuses[ $id ];
		} else {
			return null;
		}
	}

	public static function getStatuses() {
		return array(
			self::STATUS_INACTIVE => 'INACTIVE',
			self::STATUS_ACTIVE   => 'ACTIVE',
			self::STATUS_DELETED  => 'DELETED',
		);
	}

}
