<?php

namespace ContentEgg\application\modules\Pixabay;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ExtraData;

/**
 * ExtraDataBingImages class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class ExtraDataPixabay extends ExtraData {

	public $likes;
	public $favorites;
	public $views;
	public $comments;
	public $downloads;
	public $previewURL;
	public $imageWidth;
	public $user_id;
	public $user;
	public $type;
	public $userImageURL;
	public $imageHeight;

}
