<?php

namespace ContentEgg\application\modules\Envato;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ExtraData;

/**
 * ExtraDataEnvato class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class ExtraDataEnvato extends ExtraData {

	public $classification_url;
	public $number_of_sales;
	public $author_username;
	public $author_url;
	public $author_image;
	public $summary;
	public $updated_at;
	public $published_at;
	public $trending;
	public $previews = array();
	public $tags = array();

}
