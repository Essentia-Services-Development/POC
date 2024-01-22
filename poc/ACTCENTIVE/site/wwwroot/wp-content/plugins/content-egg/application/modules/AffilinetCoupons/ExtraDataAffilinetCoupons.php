<?php

namespace ContentEgg\application\modules\AffilinetCoupons;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ExtraData;

/**
 * ExtraDataAffilinetCoupons class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class ExtraDataAffilinetCoupons extends ExtraData {

	public $ProgramId;
	public $VoucherType;
	public $LastChangeDate;
	public $IntegrationCode;
	public $IsExclusive;
	public $MinimumOrderValue;
	public $CustomerRestriction;
	public $logo;

}
