<?php

namespace ContentEgg\application\libs\avantlink;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * AvantlinkApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2018 keywordrush.com
 *
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class AvantlinkApi extends RestClient {

	const API_URI_BASE = 'https://www.avantlink.com/api.php';

	protected $affiliate_id;
	protected $website_id;
	protected $_responseTypes = array(
		'json',
		'xml'
	);

	public function __construct( $affiliate_id, $website_id ) {
		$this->affiliate_id = $affiliate_id;
		$this->website_id   = $website_id;
		$this->setUri( self::API_URI_BASE );
		$this->setResponseType( 'json' );
	}

	/**
	 * Product Search
	 * @link: https://support.avantlink.com/hc/en-us/articles/204687335-ProductSearch
	 */
	public function search( $keywords, array $options ) {
		$options['search_term'] = $keywords;
		$options['module']      = 'ProductSearch';
		$response               = $this->restGet( '', $options );

		return $this->_decodeResponse( $response );
	}

	/**
	 * This module allows you to get a real time price check on a Merchant's product.
	 * @link: https://support.avantlink.com/hc/en-us/articles/203994739-ProductPriceCheck
	 */
	public function priseCheck( $merchant_id, $sku, $datafeed_id = null ) {
		$options                = array();
		$options['sku']         = $sku;
		$options['merchant_id'] = $merchant_id;
		if ( $datafeed_id ) {
			$options['datafeed_id'] = $datafeed_id;
		}
		$options['show_retail_price'] = true;
		$options['module']            = 'ProductPriceCheck';

		// This module has not provided a method of directly obtaining a JSON response.
		$this->setResponseType( 'xml' );
		$options['output'] = 'xml';
		$response          = $this->restGet( '', $options );

		return $this->_decodeResponse( $response );
	}

	public function restGet( $path, array $query = null ) {
		$query['affiliate_id'] = $this->affiliate_id;
		$query['website_id']   = $this->website_id;
		if ( ! isset( $query['output'] ) ) {
			$query['output'] = 'json';
		}

		return parent::restGet( $path, $query );
	}

}
