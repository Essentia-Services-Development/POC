<?php

namespace ContentEgg\application\modules\Daisycon\models;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\models\FeedProductModel;

/**
 * DaisyconProductModel class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2019 keywordrush.com
 */
class DaisyconProductModel extends FeedProductModel {

	public function tableName() {
		return $this->getDb()->prefix . 'cegg_daisycon_product';
	}

	public function getDump() {
		return "CREATE TABLE " . $this->tableName() . " (
                    id varchar(255) NOT NULL,
                    stock_status tinyint(1) DEFAULT 0,
                    price float(12,2) DEFAULT NULL,
                    title text,
                    ean varchar(13) DEFAULT NULL,                                        
                    orig_url text,
                    product text,
                    KEY id (id(32)),
                    KEY uid (stock_status),
                    KEY orig_url (orig_url(60)),
                    KEY ean (ean(13)),                                        
                    KEY price (price),
                    FULLTEXT (title)
                    ) $this->charset_collate;";
	}

	public static function model( $className = __CLASS__ ) {
		return parent::model( $className );
	}

}
