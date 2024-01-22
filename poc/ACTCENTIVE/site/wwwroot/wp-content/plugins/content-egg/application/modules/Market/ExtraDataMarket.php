<?php

namespace ContentEgg\application\modules\Market;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ExtraData;

/**
 * ExtraDataMarket class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class ExtraDataMarket extends ExtraData {

	public $priceMax;
	public $priceMin;
	public $offersCount;
	public $categoryId;
	public $offers = array();
	public $opinions = array();
	public $details = array();

}

class ExtraMarketOffer {

	public $id;
	public $name;
	public $onStock;
	public $url;
	public $price;
	public $currency;
	public $currencyCode;
	public $shopId;
	public $shopName;
	public $shopRating;
	public $shopGradeTotal;
	public $delivery;
	public $img;
	public $warranty;
	public $description;

}

class ExtraMarketOpinion {

	public $id;
	public $date;
	public $grade;
	public $text;
	public $agree;
	public $reject;
	public $visibility;
	public $author;
	public $pro;
	public $contra;
	public $usageTime;

}
