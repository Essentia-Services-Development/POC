<?php

namespace ContentEgg\application\modules\Shareasale;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ExtraData;

/**
 * ExtraDataShareasale class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class ExtraDataShareasale extends ExtraData {

	public $productid;
	public $merchantid;
	public $thumbnail;
	public $category;
	public $lastupdated;
	public $status;
	public $partnumber;
	public $merchantcategory;
	public $merchantsubcategory;
	public $crosssell;
	public $merchantgroup;
	public $compatiablewith;
	public $quantitydiscount;
	public $bestseller;
	public $addtocarturl;
	public $reviewsurl;
	public $www;
	public $programcategory;
	public $commissiontext;
	public $salecomm;
	public $leadcomm;
	public $hitcomm;
	public $reversalrate7day;
	public $reversalrate30day;
	public $avesale7day;
	public $avesale30day;
	public $powerranktop100;

}
