<?php

namespace ContentEgg\application\modules\Awin\models;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\models\FeedProductModel;

/**
 * AwinProductModel class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2019 keywordrush.com
 */
class AwinProductModel extends FeedProductModel {

	public function tableName() {
		return $this->getDb()->prefix . 'cegg_awin_product';
	}

	public static function model( $className = __CLASS__ ) {
		return parent::model( $className );
	}

}
