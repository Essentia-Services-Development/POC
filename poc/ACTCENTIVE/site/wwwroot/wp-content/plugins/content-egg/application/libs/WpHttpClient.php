<?php

namespace ContentEgg\application\libs;

defined( '\ABSPATH' ) || exit;

/**
 * WpHttpClient class file
 *
 * Same code from Zend_Http_Client licensed under New BSD License https://framework.zend.com/license
 * @link: http://framework.zend.com/manual/1.12/ru/zend.http.client.html
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 *
 */
class WpHttpClient {

	/**
	 * HTTP request methods
	 */
	const GET = 'GET';
	const POST = 'POST';
	const PUT = 'PUT';
	const HEAD = 'HEAD';
	const DELETE = 'DELETE';
	const TRACE = 'TRACE';
	const OPTIONS = 'OPTIONS';
	const CONNECT = 'CONNECT';

	/**
	 * POST data encoding methods
	 */
	const ENC_URLENCODED = 'application/x-www-form-urlencoded';
	const ENC_FORMDATA = 'multipart/form-data';

	private $timeout;
	private $sslverify;
	private $useragent;
	private $redirection;

	/**
	 * Request URI
	 */
	protected $uri;

	/**
	 * Associative array of request headers
	 *
	 * @var array
	 */
	protected $headers = array();

	/**
	 * HTTP request method
	 *
	 * @var string
	 */
	protected $method = self::GET;

	/**
	 * Associative array of GET parameters
	 *
	 * @var array
	 */
	protected $paramsGet = array();

	/**
	 * Assiciative array of POST parameters
	 *
	 * @var array
	 */
	protected $paramsPost = array();

	/**
	 * Request body content type (for POST requests)
	 *
	 * @var string
	 */
	protected $enctype = null;

	/**
	 * The raw post data to send. Could be set by setRawData($data, $enctype).
	 *
	 * @var string
	 */
	protected $raw_post_data = null;

	/**
	 * The last HTTP response received by the client
	 *
	 * @var EHttpResponse
	 */
	protected $last_response = null;

	/**
	 * Set the URI for the next request
	 */
	public function setUri( $uri ) {
		$this->uri = $uri;
	}

	/**
	 * Get the URI for the next request
	 */
	public function getUri( $as_string = false ) {
		return $this->uri;
	}

	public function setHeaders( $name, $value = null ) {
		// If we got an array, go recusive!
		if ( is_array( $name ) ) {
			foreach ( $name as $k => $v ) {
				if ( is_string( $k ) ) {
					$this->setHeaders( $k, $v );
				} else {
					$this->setHeaders( $v, null );
				}
			}
		} else {
			// Check if $name needs to be split
			if ( $value === null && ( strpos( $name, ':' ) > 0 ) ) {
				list( $name, $value ) = explode( ':', $name, 2 );
			}

			// Make sure the name is valid if we are in strict mode
			/*
			if (!preg_match('/^[a-zA-Z0-9-]+$/', $name))
			{
				throw new \Exception("{$name} is not a valid HTTP header name");
			}
			 *
			 */

			$normalized_name = strtolower( $name );

			// If $value is null or false, unset the header
			if ( $value === null || $value === false ) {
				unset( $this->headers[ $normalized_name ] );
			} else {
				// Header names are storred lowercase internally.
				if ( is_string( $value ) ) {
					$value = trim( $value );
				}
				$this->headers[ $normalized_name ] = array( $name, $value );
			}
		}
	}

	/**
	 * Get the value of a specific header
	 *
	 * Note that if the header has more than one value, an array
	 * will be returned.
	 *
	 * @param string $key
	 *
	 * @return string|array|null The header value or null if it is not set
	 */
	public function getHeader( $key ) {
		$key = strtolower( $key );
		if ( isset( $this->headers[ $key ] ) ) {
			return $this->headers[ $key ][1];
		} else {
			return null;
		}
	}

	/**
	 * Set the next request's method
	 */
	public function setMethod( $method = self::GET ) {

		if ( $method !== self::GET && $method !== self::POST ) {
			throw new \Exception( 'Only GET and POST methods avalible.' );
		}

		$this->method = $method;
	}

	/**
	 * Set a GET parameter for the request. Wrapper around _setParameter
	 *
	 * @param string|array $name
	 * @param string $value
	 *
	 * @return EHttpClient
	 */
	public function setParameterGet( $name, $value = null ) {
		if ( is_array( $name ) ) {
			foreach ( $name as $k => $v ) {
				$this->_setParameter( 'GET', $k, $v );
			}
		} else {
			$this->_setParameter( 'GET', $name, $value );
		}
	}

	/**
	 * Set a POST parameter for the request. Wrapper around _setParameter
	 *
	 * @param string|array $name
	 * @param string $value
	 */
	public function setParameterPost( $name, $value = null ) {
		if ( is_array( $name ) ) {
			foreach ( $name as $k => $v ) {
				$this->_setParameter( 'POST', $k, $v );
			}
		} else {
			$this->_setParameter( 'POST', $name, $value );
		}
	}

	/**
	 * Set a GET or POST parameter - used by SetParameterGet and SetParameterPost
	 *
	 * @param string $type GET or POST
	 * @param string $name
	 * @param string $value
	 *
	 * @return null
	 */
	protected function _setParameter( $type, $name, $value ) {
		$parray = array();
		$type   = strtolower( $type );
		switch ( $type ) {
			case 'get':
				$parray = &$this->paramsGet;
				break;
			case 'post':
				$parray = &$this->paramsPost;
				break;
		}

		if ( $value === null ) {
			if ( isset( $parray[ $name ] ) ) {
				unset( $parray[ $name ] );
			}
		} else {
			$parray[ $name ] = $value;
		}
	}

	public function setTimeout( $value ) {
		$this->timeout = $value;
	}

	public function setSslVerify( $value ) {
		$this->sslverify = $value;
	}

	public function setUserAgent( $value ) {
		$this->useragent = $value;
	}

	public function setRedirection( $value ) {
		$this->redirection = $value;
	}

	/**
	 * Prepare the request body (for POST and PUT requests)
	 *
	 * @return string
	 */
	protected function _prepareBody() {
		// According to RFC2616, a TRACE request should not have a body.
		if ( $this->method == self::TRACE ) {
			return '';
		}

		// If we have raw_post_data set, just use it as the body.
		if ( isset( $this->raw_post_data ) ) {
			$this->setHeaders( 'Content-length', strlen( $this->raw_post_data ) );

			return $this->raw_post_data;
		}

		$body = '';

		// If we have POST parameters, encode and add them to the body
		if ( count( $this->paramsPost ) > 0 ) {

			// Encode body as application/x-www-form-urlencoded
			$this->setHeaders( 'Content-type', self::ENC_URLENCODED );
			$body = http_build_query( $this->paramsPost, '', '&' );
		}

		// Set the content-length if we have a body or if request is POST/PUT
		if ( $body || $this->method == self::POST || $this->method == self::PUT ) {
			$this->setHeaders( 'Content-length', strlen( $body ) );
		}

		return $body;
	}

	/**
	 * Prepare the request headers
	 *
	 * @return array
	 */
	protected function _prepareHeaders() {
		$headers = array();
		// Set the host header
		if ( ! isset( $this->headers['host'] ) ) {
			$host            = parse_url( $this->uri, PHP_URL_HOST );
			$headers['Host'] = "{$host}";
		}

		// Set the connection header
		if ( ! isset( $this->headers['connection'] ) ) {
			$headers['Connection'] = "close";
		}

		// Set the Accept-encoding header if not set - depending on whether
		// zlib is available or not.
		if ( ! isset( $this->headers['accept-encoding'] ) ) {
			if ( function_exists( 'gzinflate' ) ) {
				$headers['Accept-encoding'] = 'gzip, deflate';
			} else {
				$headers['Accept-encoding'] = 'identity';
			}
		}

		// Set the content-type header
		if ( $this->method == self::POST &&
		     ( ! isset( $this->headers['content-type'] ) && isset( $this->enctype ) ) ) {

			$headers['Content-type'] = "{$this->enctype}";
		}

		// Add all other user defined headers
		foreach ( $this->headers as $header ) {
			list( $name, $value ) = $header;
			if ( is_array( $value ) ) {
				$value = implode( ', ', $value );
			}

			$headers[ $name ] = $value;
		}

		return $headers;
	}

	protected function _prepareParams() {
		$options           = array();
		$options['method'] = $this->method;

		if ( $this->timeout !== null ) {
			$options['timeout'] = $this->timeout;
		}
		if ( $this->sslverify !== null ) {
			$options['sslverify'] = $this->sslverify;
		}
		if ( $this->useragent !== null ) {
			$options['user-agent'] = $this->useragent;
		}
		if ( $this->redirection !== null ) {
			$options['redirection'] = $this->redirection;
		}

		$options['headers'] = $this->_prepareHeaders();
		$options['body']    = $this->_prepareBody();

		return $options;
	}

	public function request( $method = null ) {
		if ( ! $this->uri ) {
			throw new \Exception( 'No valid URI has been passed to the client' );
		}

		if ( $method ) {
			$this->setMethod( $method );
		}

		// Add the additional GET parameters to uri
		if ( ! empty( $this->paramsGet ) ) {
			$query = parse_url( $this->uri, PHP_URL_QUERY );
			if ( ! empty( $query ) ) {
				$this->uri .= '&';
			} else {
				$this->uri .= '?';
			}
			$this->uri .= http_build_query( $this->paramsGet, null, '&' );
		}

		$this->last_response = \wp_remote_request( $this->uri, $this->_prepareParams() );

		return $this->last_response;
	}

	public function setRawData( $data, $enctype = null ) {
		$this->raw_post_data = $data;
		//$this->setEncType($enctype);
	}

	/**
	 * Clear all GET and POST parameters
	 *
	 * Should be used to reset the request parameters if the client is
	 * used for several concurrent requests.
	 *
	 */
	public function resetParameters() {
		// Reset parameter data
		$this->paramsGet     = array();
		$this->paramsPost    = array();
		$this->raw_post_data = null;

		// Reset headers
		$allowed_headers = array( 'accept-charset' );
		foreach ( $this->headers as $header => $val ) {
			if ( ! in_array( $header, $allowed_headers ) ) {
				unset( $this->headers[ $header ] );
			}
		}
	}

	/**
	 * Get the last HTTP request as string
	 */
	/*
	  public function getLastRequest()
	  {
	  return $this->last_request;
	  }
	 *
	 */

	/**
	 * Get the last HTTP response received by this client
	 */
	public function getLastResponse() {
		return $this->last_response;
	}

}
