<?php

namespace ContentEgg\application\libs\affilinet;

defined( '\ABSPATH' ) || exit;

use ContentEgg\application\libs\RestClient;

/**
 * AffilinetProducts class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 *
 * @link: http://publisher.affili.net/Solutions/Webservices_Webservices.aspx?nr=1&pnp=54#Product
 * @link: http://publisher.affili.net/HtmlContent/de/downloads/Web%20Services/Documentation_SearchProductsV3.pdf
 */
require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class AffilinetProducts extends RestClient {

	const API_URI_BASE = 'https://product-api.affili.net/V3/productservice.svc/JSON';

	protected $password;
	protected $publisher_id;

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
	public function __construct( $password, $publisher_id, $responseType = 'json' ) {
		$this->setPassword( $password );
		$this->setPublisherId( $publisher_id );
		$this->setResponseType( $responseType );
		$this->setUri( self::API_URI_BASE );
	}

	public function setPassword( $password ) {
		$this->password = $password;
	}

	public function setPublisherId( $publisher_id ) {
		$this->publisher_id = $publisher_id;
	}

	/**
	 * search products
	 * @link: http://publisher.affili.net/HtmlContent/de/downloads/Web%20Services/Documentation_SearchProductsV3.pdf
	 */
	public function search( $keywords, array $options ) {
		$options['Query'] = $keywords;
		$response         = $this->restGet( '/SearchProducts', $options );

		return $this->_decodeResponse( $response );
	}

	/**
	 * search products by EAN
	 * @link: http://publisher.affili.net/HtmlContent/de/downloads/Web%20Services/Documentation_SearchProductsV3.pdf
	 */
	public function searchEan( $ean, array $options ) {
		//In the GET call, the value of the FQ parameter can be specified  several times, like this:
		// ...&fq=field1:value1&fq=field2:value2&fq=field3:value3,...
		// *: Alternatively, you could also use this way of writing:
		// ...&fq=field1:value1,field2:value2,field3:value3&...
		$options['FQ'] = 'EAN:' . $ean;
		$response      = $this->restGet( '/SearchProducts', $options );

		return $this->_decodeResponse( $response );
	}

	/**
	 * get products by ids
	 * @link: http://publisher.affili.net/HtmlContent/de/downloads/Web%20Services/Documentation_GetProductsV3.pdf
	 */
	public function products( $productIds, $options = array() ) {
		if ( is_array( $productIds ) ) {
			$productIds = join( ',', $productIds );
		}
		$options['ProductIds'] = $productIds;

		$response = $this->restGet( '/GetProducts', $options );

		return $this->_decodeResponse( $response );
	}

	protected function _decodeResponse( $response, $responseType = null ) {
		// Please note the JSON output begins with a byte order mark (BOM).
		// If you run into problems while  parsing the JSON response, try removing the BOM first.
		if ( substr( $response, 0, 3 ) == pack( "CCC", 0xEF, 0xBB, 0xBF ) ) {
			$response = substr( $response, 3 );
		}

		return parent::_decodeResponse( $response, $responseType );
	}

	public function restGet( $path, array $query = null ) {
		if ( ! $query ) {
			$query = array();
		}
		$query['PublisherId'] = $this->publisher_id;
		$query['Password']    = $this->password;

		return parent::restGet( $path, $query );
	}

}
