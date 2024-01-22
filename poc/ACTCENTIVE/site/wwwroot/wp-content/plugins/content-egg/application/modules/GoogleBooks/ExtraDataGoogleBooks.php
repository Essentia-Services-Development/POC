<?php

namespace ContentEgg\application\modules\GoogleBooks;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ExtraData;

/**
 * ExtraDataGoogleBooks class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class ExtraDataGoogleBooks extends ExtraData {

	public $isbn = array();
	public $language;
	public $saleInfo;
	public $subtitle;
	public $authors;
	public $pageCount;
	public $printType;
	public $categories;
	public $publisher;

}
