<?php

namespace ContentEgg\application\modules\Ebay;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ExtraData;

/**
 * ExtraDataEbay class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class ExtraDataEbay extends ExtraData {

	public $pictureURLSuperSize;
	public $pictureURLLarge;
	public $topRatedListing;
	public $discountPriceInfo;
	public $autoPay;
	public $charityId;
	public $country;
	public $distance;
	public $galleryPlusPictureURL;
	public $galleryURL;
	public $globalId;
	public $itemId;
	public $listingInfo;
	public $location;
	public $paymentMethod;
	public $postalCode;
	public $sellingStatus;
	public $shippingInfo;
	public $storeInfo;
	public $subtitle;
	public $quantity;
	public $seller;
	public $highBidder;
	public $quantitySold;
	public $itemSpecifics;
	public $hitCount;
	public $returnPolicy;
	public $minimumToBid;
	public $conditionID;
	public $conditionDisplayName;
	public $globalShipping;
	public $isMultiVariationListing;
	public $unitPriceType;
	public $unitPriceQuantity;

}

class ExtraEbayListingInfo {

	public $bestOfferEnabled;
	public $buyItNowAvailable;
	public $buyItNowPrice;
	public $convertedBuyItNowPrice;
	public $startTime;
	public $endTime;
	public $listingType;
	public $gift;
	public $oneDayShippingAvailable;
	public $handlingTime;
	public $watchCount;

}

class ExtraEbaySellingStatus {

	public $currentPrice;
	public $convertedCurrentPrice;
	public $sellingState;
	public $timeLeft;
	public $bidCount;

}

class ExtraEbayShippingInfo {

	public $shippingServiceCost;
	public $shippingType;
	public $shipToLocations;
	public $expeditedShipping;
	public $oneDayShippingAvailable;
	public $handlingTime;

}

class ExtraEbaySeller {

	public $userID;
	public $feedbackRatingStar;
	public $feedbackScore;
	public $positiveFeedbackPercent;

}

class ExtraEbayHighBidder {

	public $userID;
	public $feedbackPrivate;
	public $feedbackRatingStar;
	public $feedbackScore;

}
