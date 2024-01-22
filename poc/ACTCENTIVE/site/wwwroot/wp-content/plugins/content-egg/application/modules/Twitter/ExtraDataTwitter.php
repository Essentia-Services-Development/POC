<?php

namespace ContentEgg\application\modules\Twitter;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ExtraData;

/**
 * ExtraDataTwitter class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class ExtraDataTwitter extends ExtraData {

	public $links = array();

}

class ExtraTwitterLinks {

	public $userId;
	public $statusesCount;
	public $followersCount;
	public $friendsCount;
	public $media;
	public $profileImage;

}
