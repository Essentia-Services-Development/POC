<?php

namespace ContentEgg\application\modules\AffilinetProducts;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ExtraData;

/**
 * ExtraDataAffilinetProducts class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class ExtraDataAffilinetProducts extends ExtraData {

	public $addToCartUrl;
	public $LastShopUpdate;
	public $LastProductChange;
	public $Score;
	//public $ProductId;
	public $ArticleNumber;
	public $ShopId;
	public $ShopCategoryId;
	public $AffilinetCategoryId;
	public $ShopCategoryPath;
	public $AffilinetCategoryPath;
	//public $DeliveryTime; //Currently not in use
	public $Brand;
	public $Distributor;
	public $Keywords;
	public $PriceInformation;
	public $Properties;
	public $ProgramId;
	public $ShopCategoryIdPath;
	public $AffilinetCategoryIdPath;
	public $DisplayPrice;
	public $DisplayShipping;
	public $DisplayBasePrice;
	public $PricePrefix;
	public $PriceSuffix;
	public $ShippingPrefix;
	public $ShippingSuffix;
	public $BasePricePrefix;
	public $BasePriceSuffix;
	public $logo;

}
