<?php

namespace ContentEgg\application\modules\Ebay2;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ExtraData;

/**
 * ExtraDataEbay2 class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class ExtraDataEbay2 extends ExtraData {

	public $locale;
	public $seller = array();
	public $condition;
	public $conditionId;
	public $shippingOptions = array();
	public $buyingOptions = array();
	public $epid;
	public $itemLocation = array();
	public $categories = array();
	public $images = array();
	public $qualifiedPrograms = array();
	public $adultOnly;
	public $legacyItemId;
	public $availableCoupons;
	public $topRatedBuyingExperience;
	public $priorityListing;
	public $IsEligibleForSuperSaverShipping;

	public $unitPriceType;
	public $unitPrice;
	public $unitPriceCurrency;
	public $pricePerUnitDisplay;

}