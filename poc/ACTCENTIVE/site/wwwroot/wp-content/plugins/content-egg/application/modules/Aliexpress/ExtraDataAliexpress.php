<?php

namespace ContentEgg\application\modules\Aliexpress;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ExtraData;

/**
 * ExtraDataAliexpress class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class ExtraDataAliexpress extends ExtraData {

	public $lotNum;
	public $packageType;
	public $_30daysCommission;
	public $commissionRate;
	public $validTime;
	public $volume;
	public $evaluateScore;
	public $commission;

}
