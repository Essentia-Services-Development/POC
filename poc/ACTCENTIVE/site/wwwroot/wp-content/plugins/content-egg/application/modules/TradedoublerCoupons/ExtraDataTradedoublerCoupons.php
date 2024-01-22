<?php

namespace ContentEgg\application\modules\TradedoublerCoupons;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ExtraData;

/**
 * ExtraDataTradedoublerCoupons class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class ExtraDataTradedoublerCoupons extends ExtraData {

	public $programId;
	public $programName;
	public $voucherTypeId;
	public $siteSpecific;
	public $landingUrl;
	public $discountAmount;
	public $isPercentage;
	public $publisherInformation;
	public $languageId;
	public $exclusive;
	public $currencyId;

}
