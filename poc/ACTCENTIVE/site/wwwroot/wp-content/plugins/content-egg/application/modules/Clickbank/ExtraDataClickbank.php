<?php

namespace ContentEgg\application\modules\Clickbank;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ExtraData;

/**
 * ExtraDataClickbank class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class ExtraDataClickbank extends ExtraData {

	public $activateDate;
	public $category;
	public $subCategory;
	public $categoryIdPath;
	public $subCategoryIdPath;
	public $commission;
	public $initialDollarsPerSale;
	public $gravity;
	public $pctPerSale;
	public $pctPerRebill;
	public $averageDollarsPerSale;
	public $totalRebill;
	public $affiliateUrl;
	public $affiliateUrlProvided;
	public $de;
	public $en;
	public $es;
	public $fr;
	public $it;
	public $pt;
	public $standard;
	public $physical;
	public $rebill;
	public $upsell;
	public $standardUrlPresent;
	public $mobileEnabled;
	public $spotlightActive;
	public $whitelistVendor;
	public $hotpick;
	public $preferredVendor;
	public $dollarTrial;
	public $vendorTier;
	public $marketPlaceStarRating;

}
