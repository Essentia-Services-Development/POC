<?php

namespace ContentEgg\application\modules\Ozon;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ExtraData;

/**
 * ExtraDataOzon class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class ExtraDataOzon extends ExtraData {

	public $Author;
	public $Availability;
	public $BargainSale;
	public $ClientRatingCount;
	public $DigitalTypeId;
	public $InSuite;
	public $IsNew;
	public $IsSpecialPrice;
	public $ItemAvailabilityId;
	public $ItemType;
	public $ItemTypeId;
	public $ScoreToAdd;
	public $Weight;
	public $OtherName;
	public $Media;
	public $Year;
	public $Detail = array();
	public $Capability = array();
	public $Reviews = array();
	public $Gallery = array();

}

class ExtraDataOzonReviews {

	public $Age;
	public $Comment;
	public $Date;
	public $FIO;
	public $Country;
	public $Rate;
	public $Title;
	public $GradeAll;
	public $GradeNo;
	public $GradeYes;

}
