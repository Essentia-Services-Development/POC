<?php

namespace ContentEgg\application\modules\TradetrackerCoupons;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ExtraData;

/**
 * ExtraDataTradetrackerCoupons class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class ExtraDataTradetrackerCoupons extends ExtraData {

	public $campaign = array();
	public $creationDate;
	public $modificationDate;
	public $materialBannerDimension;
	public $referenceSupported;
	public $conditions;
	public $discountFixed;
	public $discountVariable;

}
