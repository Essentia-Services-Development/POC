<?php

namespace ContentEgg\application\modules\Aliexpress2;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ExtraData;

/**
 * ExtraDataAliexpress class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class ExtraDataAliexpress2 extends ExtraData {

	public $commission_rate;
	public $evaluate_rate;
	public $first_level_category_id;
	public $first_level_category_name;
	public $hot_product_commission_rate;
	public $lastest_volume;
	public $original_price;
	public $original_price_currency;
	public $second_level_category_id;
	public $second_level_category_name;
	public $shop_id;
	public $shop_url;
	public $image_urls = array();
	public $product_video_url;
	public $platform_product_type;

}
