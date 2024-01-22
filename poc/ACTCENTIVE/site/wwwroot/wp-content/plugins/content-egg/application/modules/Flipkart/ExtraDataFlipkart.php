<?php

namespace ContentEgg\application\modules\Flipkart;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ExtraData;

/**
 * ExtraDataFlipkart class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class ExtraDataFlipkart extends ExtraData {

	public $productFamily = array();
	public $codAvailable;
	public $offers = array();
	public $attributes = array();
	public $shippingCharges = array();
	public $estimatedDeliveryTime;
	public $sellerName;
	public $sellerAverageRating;
	public $sellerNoOfRatings;
	public $keySpecs = array();
	public $detailedSpecs = array();
	public $specificationList = array();
	public $booksInfo = array();
	public $lifeStyleInfo = array();
	public $maximumRetailPrice = array();

}
