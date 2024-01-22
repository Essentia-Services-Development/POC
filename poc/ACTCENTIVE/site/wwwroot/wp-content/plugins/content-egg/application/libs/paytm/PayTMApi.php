<?php

namespace ContentEgg\application\libs\paytm;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * PayTMApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 *
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class PayTMApi extends RestClient {

	/**
	 * @var array Response Format Types
	 */
	protected $_responseTypes = array(
		'json',
	);

	/**
	 * Constructor
	 *
	 * @param string $responseType
	 */
	public function __construct() {
		$this->setResponseType( 'json' );
	}

	public function search( $keyword, array $options ) {
		$options['userQuery'] = $keyword;

		if ( empty( $options['page_count'] ) ) {
			$options['page_count'] = 1;
		}

		$options['channel']       = 'web';
		$options['child_site_id'] = 1;
		$options['site_id']       = 1;
		$options['version']       = 2;
		$options['resolution']    = '960x720';
		$options['quality']       = 'high';

		$response = $this->restGet( 'https://search.paytm.com/search/', $options );

		return $this->_decodeResponse( $response );
	}

	public function product( $product_code ) {
		$options                  = array();
		$options['channel']       = 'web';
		$options['child_site_id'] = 1;
		$options['site_id']       = 1;
		$options['version']       = 2;

		$response = $this->restGet( 'https://catalog.paytm.com/v1/p/' . urlencode( $product_code ), $options );

		return $this->_decodeResponse( $response );
	}

}
