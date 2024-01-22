<?php

namespace ContentEgg\application\libs\yandex;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\ParserClient;

/**
 * MarketContentParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 *
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class MarketContentParser extends ParserClient implements MarketContentInterface {

	public $debug = false;

	public function search( $keyword, $params ) {
		$uri = 'https://market.yandex.ru/search.xml';

		$query         = array();
		$query['text'] = $keyword;
		$this->loadXPath( $uri, $query );
		//$titles = array_slice($this->xpathArray(".//h3[@class='snippet-card__header']/a/text()"), 0, 5);
		$results_like_api = array();
	}

	public function details( $model_id, $params = array() ) {
		throw new \Exception( 'The details method do not implemented yet' );
	}

	public function opinions( $model_id, $params = array() ) {

	}

	public function offers( $model_id, $params = array() ) {

	}

}
