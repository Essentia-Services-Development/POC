<?php

namespace ContentEgg\application\modules\Awin;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\components\ExtraData;

/**
 * ExtraDataExtraDataAwin class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2019 keywordrush.com
 *
 */
class ExtraDataAwin extends ExtraData {

	public $merchant_product_id;
	public $merchant_id;
	public $merchant_category;
	public $category_id;
	public $store_price;
	public $delivery_cost;
	public $language;
	public $last_updated;
	public $number_available;
	public $stock_quantity;
	public $valid_from;
	public $valid_to;
	public $is_for_sale;
	public $web_offer;
	public $pre_order;

}
