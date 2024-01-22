<?php

namespace ContentEgg\application\modules\Impactradius;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ExtraData;

/**
 * ExtraDataZanox class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class ExtraDataImpactradius extends ExtraData {

	public $CatalogId;
	public $CampaignId;
	public $CampaignName;
	public $MultiPack = '';
	public $Bullets = array();
	public $Labels = array();
	public $MobileUrl;
	public $ProductBid;
	public $AdditionalImageUrls = array();
	public $EstimatedShipDate;
	public $LaunchDate;
	public $ExpirationDate;
	public $Asin;
	public $Mpn;
	public $ShippingRate;
	public $ShippingWeight;
	public $ShippingWeightUnit;
	public $ShippingLength;
	public $ShippingWidth;
	public $ShippingHeight;
	public $ShippingLengthUnit;
	public $ShippingLabel;
	public $OriginalFormatCategory;
	public $OriginalFormatCategoryId;
	public $ParentName;
	public $ParentSku;
	public $IsParent;
	public $Colors = array();
	public $Material;
	public $Pattern;
	public $Size;
	public $SizeUnit;
	public $Weight;
	public $WeightUnit;
	public $Condition;
	public $AgeGroup;
	public $Gender;
	public $Adult;
	public $Uri;

}
