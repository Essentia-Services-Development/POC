<?php

namespace ContentEgg\application\modules\CjProducts;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ExtraData;

/**
 * ExtraDataCJ class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class ExtraDataCjProducts extends ExtraData {

	public $advertiserId;
	public $advertiserName;
	public $advertiserCategory;
	public $catalogId;
	public $isbn;
	public $manufacturerSku;
	public $sku;
	public $upc;
	// new GraphQl API
	public $id;
	public $adId;
	public $gtin;
	public $lastUpdated;
	public $itemListId;
	public $salePriceEffectiveDateStart;
	public $salePriceEffectiveDateEnd;
	public $targetCountry;
	public $availabilityDate;
	public $shipping = array();
	public $color;
	public $condition;
	public $energyEfficiencyClass;
	public $energyEfficiencyClassMax;
	public $energyEfficiencyClassMin;
	public $expirationDate;
	public $mpn;
	public $unitPricingBaseMeasure;
	public $unitPricingMeasure;

}
