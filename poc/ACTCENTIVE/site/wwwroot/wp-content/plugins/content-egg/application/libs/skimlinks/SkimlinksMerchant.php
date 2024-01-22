<?php

namespace ContentEgg\application\libs\skimlinks;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * SkimlinksMerchant class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 * Skimlinks Merchant API
 * @link: http://developers.skimlinks.com/merchant.html
 *
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class SkimlinksMerchant extends RestClient {

	const API_URI_BASE = 'https://merchants.skimapis.com/v3';

	private $apikey;
	private $account_type;
	private $account_id;
	protected $_responseTypes = array(
		'json',
	);

	public function __construct( $apikey, $account_id, $account_type = 'publisher_admin' ) {
		$this->apikey       = $apikey;
		$this->account_id   = $account_id;
		$this->account_type = $account_type;
		$this->setUri( self::API_URI_BASE );
		$this->setResponseType( 'json' );
	}

	public function search( $query, array $params ) {
		$params['search'] = $query;
		$response         = $this->restGet( '/offers', $params );

		return $this->_decodeResponse( $response );
	}

	public function restGet( $path, array $query = null ) {
		$query['apikey']       = $this->apikey;
		$query['account_id']   = $this->account_id;
		$query['account_type'] = $this->account_type;

		return parent::restGet( $path, $query );
	}

}
