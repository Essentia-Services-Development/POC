<?php

namespace ContentEgg\application\modules\PayTM;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ExtraData;

/**
 * ExtraDataPayTM class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class ExtraDataPayTM extends ExtraData {

	public $seourl;
	public $url_type;
	public $promo_text;
	public $tag;
	public $product_tag;
	public $search_weight;
	public $merchant_name;
	public $product_code;

}
