<?php

namespace ContentEgg\application\modules\Walmart;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ExtraData;

/**
 * WalmartDataEnvato class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 *
 */
class ExtraDataWalmart extends ExtraData {

	public $parentItemId;
	public $comments = array();
	public $productTrackingUrl;
	public $ninetySevenCentShipping;
	public $standardShipRate;
	public $twoThreeDayShippingRate;
	public $overnightShippingRate;
	public $specialBuy;
	public $customerRatingImage;
	public $size;
	public $color;
	public $marketplace;
	public $shipToStore;
	public $freeShipToStore;
	public $modelNumber;
	public $categoryNode;
	public $bundle;
	public $clearance;
	public $preOrder;
	public $offerType;
	public $isTwoDayShippingEligible;
	public $availableOnline;
	public $sellerInfo;
	public $shippingPassEligible;
	public $addToCartUrl;
	public $affiliateAddToCartUrl;
	public $imageEntities = array();

}
