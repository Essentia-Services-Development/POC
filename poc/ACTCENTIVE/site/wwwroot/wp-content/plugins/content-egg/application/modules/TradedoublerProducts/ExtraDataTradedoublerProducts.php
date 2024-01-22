<?php

namespace ContentEgg\application\modules\TradedoublerProducts;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ExtraData;

/**
 * ExtraDataTradedoublerProducts class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class ExtraDataTradedoublerProducts extends ExtraData {

	public $language;
	public $fields;
	public $feedId;
	public $modified;
	public $availability;
	public $deliveryTime;
	public $condition;
	public $shippingCost;
	public $sourceProductId;
	public $programLogo;

}
