<?php

namespace ContentEgg\application\modules\SkimlinksCoupons;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ExtraData;

/**
 * ExtraDataTradetrackerCoupons class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class ExtraDataSkimlinksCoupons extends ExtraData {

	public $offer_type;
	public $merchant_details = array();
	public $terms;

}
