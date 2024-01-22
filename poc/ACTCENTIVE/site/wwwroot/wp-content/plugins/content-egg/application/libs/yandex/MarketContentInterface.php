<?php

namespace ContentEgg\application\libs\yandex;

defined( '\ABSPATH' ) || exit;

/**
 * MarketContentInterface
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 *
 */
interface MarketContentInterface {

	public function search( $query, $params );

	public function details( $model_id, $params = array() );

	public function opinions( $model_id, $params = array() );

	public function offers( $model_id, $params = array() );
}
