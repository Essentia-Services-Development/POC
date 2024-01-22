<?php

namespace ContentEgg\application\modules\Kelkoo;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ExtraData;

/**
 * ExtraDataKelkoo class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 *
 */
class ExtraDataKelkoo extends ExtraData {

	public $offerType;
	public $lastUpdateDate;
	public $country;
	public $monthPrice;
	public $rebatePercentage;
	public $rebateEndDate;
	public $deliveryCost;
	public $priceDiscountText;
	public $totalPrice;
	public $unitPrice;
	public $timeToDeliver;
	public $condition;
	public $warranty;
	public $greenLabel;
	public $flag = array();
	//public $features = array();
	public $merchant = array();
	public $googleProductCategory = array();
	public $ecotax;
	public $madeIn;

}
