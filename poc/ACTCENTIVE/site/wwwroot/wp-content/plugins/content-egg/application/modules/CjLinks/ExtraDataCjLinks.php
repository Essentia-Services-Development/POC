<?php

namespace ContentEgg\application\modules\CjLinks;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ExtraData;

/**
 * ExtraDataCjLinks class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class ExtraDataCjLinks extends ExtraData {

	public $advertiserId;
	public $advertiserName;
	public $advertiserSite;
	public $creativeHeight;
	public $creativeWidtht;
	public $language;
	public $linkHtml;
	public $destination;
	public $linkName;
	public $linkType;
	public $promotionStartDate;
	public $promotionEndDate;
	public $promotionType;
	public $couponCode;
	public $category;

}
