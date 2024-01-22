<?php

namespace ContentEgg\application\libs;

defined( '\ABSPATH' ) || exit;

/**
 * ParserClient class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 *
 */
class ParserClient {

	protected $charset = 'utf-8';
	protected $xpath;
	protected $url;
	protected static $_httpClient = null;

	public function __construct( $url = null ) {
		if ( $url ) {
			$this->setUrl( $url );
		}
	}

	public function setUrl( $url ) {
		$this->url   = $url;
		$this->xpath = null;
		$this->loadXPath( $url );
	}

	public function getUrl() {
		return $this->url;
	}

	public function getCharset() {
		return $this->charset;
	}

	/**
	 * Gets the HTTP client object.
	 */
	public static function getHttpClient( $opts = array() ) {
		$_opts = array(
			'sslverify'   => false,
			'redirection' => 3,
			'timeout'     => 10,
			'user-agent'  => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.16; rv:86.0) Gecko/20100101 Firefox/86.0',
		);
		if ( $opts ) {
			$_opts = $opts + $_opts;
		}

		if ( self::$_httpClient == null ) {
			//Get WP http client
			self::$_httpClient = new WpHttpClient();
			self::$_httpClient->setHeaders( 'Accept-Charset', 'ISO-8859-1,utf-8' );
			self::$_httpClient->setUserAgent( $_opts['user-agent'] );
			self::$_httpClient->setRedirection( $_opts['redirection'] );
			self::$_httpClient->setTimeout( $_opts['timeout'] );
			self::$_httpClient->setSslVerify( $_opts['sslverify'] );
		}

		return self::$_httpClient;
	}

	/**
	 * Sets the HTTP client object to use for retrieving the feeds.  If none
	 * is set, the default Http_Client will be used.
	 */
	public static function setHttpClient( $httpClient ) {
		self::$_httpClient = $httpClient;
	}

	public function loadXPath( $url, $query = null ) {
		$this->xpath = $this->getXPath( $url, $query );
	}

	public function getXPath( $url, $query = null ) {
		return $xpath = new \DomXPath( $this->getDom( $url, $query ) );
	}

	public function getDom( $url, $query = null ) {
		$dom                     = new \DomDocument();
		$dom->preserveWhiteSpace = false;
		libxml_use_internal_errors( true );
		if ( ! $dom->loadHTML( $this->restGet( $url, $query ) ) ) {
			throw new \Exception( 'Can\'t load DOM Document.' );
		}

		return $dom;
	}

	public function restGet( $uri, $query = null ) {
		$client = self::getHttpClient();
		$client->resetParameters();
		$client->setUri( $uri );
		if ( $query ) {
			$client->setParameterGet( $query );
		}
		$body = $this->getResult( $client->request( 'GET' ) );

		return $this->decodeCharset( $body );
	}

	protected function getResult( $response ) {
		if ( \is_wp_error( $response ) ) {
			$error_mess = "HTTP request fails: " . $response->get_error_code() . " - " . $response->get_error_message() . '.';
			throw new \Exception( $error_mess );
		}

		$response_code = (int) \wp_remote_retrieve_response_code( $response );

		if ( $response_code != 200 ) {
			$response_message = \wp_remote_retrieve_response_message( $response );
			$error_mess       = "HTTP request status fails: " . $response_code . " - " . $response_message . '.';
			throw new \Exception( $error_mess, $response_code );
		}

		return \wp_remote_retrieve_body( $response );
	}

	public function decodeCharset( $str ) {
		$encoding_hint = '<?xml encoding="UTF-8">';

		if ( strtolower( $this->charset ) != 'utf-8' ) {
			$str = $encoding_hint . '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . $str;

			return iconv( $this->charset, 'utf-8', $str );
		} else {
			return $encoding_hint . $str;
		}
	}

	public function xpathScalar( $path, $return_child = false ) {
		if ( is_array( $path ) ) {
			return $this->xpathScalarMulty( $path, $return_child );
		}

		$res = $this->xpath->query( $path );
		if ( $res && $res->length > 0 ) {
			if ( $return_child ) {
				foreach ( $res as $tag ) {
					return $this->xpathReturnChild( $tag );
				}
			}

			return trim( strip_tags( $res->item( 0 )->nodeValue ) );
		} else {
			return null;
		}
	}

	public function xpathScalarMulty( array $paths, $return_child = false ) {
		foreach ( $paths as $path ) {
			if ( $r = $this->xpathScalar( $path, $return_child ) ) {
				return $r;
			}
		}

		return $r;
	}

	public function xpathArray( $path, $return_child = false ) {
		if ( is_array( $path ) ) {
			return $this->xpathArrayMulty( $path, $return_child );
		}

		$res    = $this->xpath->query( $path );
		$return = array();
		if ( $res && $res->length > 0 ) {
			foreach ( $res as $tag ) {
				if ( $return_child ) {
					$return[] = $this->xpathReturnChild( $tag );
				} else {
					$return[] = trim( strip_tags( $tag->nodeValue ) );
				}
			}
		}

		return $return;
	}

	public function xpathArrayMulty( array $paths, $return_child = false ) {
		foreach ( $paths as $path ) {
			if ( $r = $this->xpathArray( $path, $return_child ) ) {
				return $r;
			}
		}

		return $r;
	}

	protected function xpathReturnChild( $tag ) {
		$innerHTML = '';
		$children  = $tag->childNodes;
		foreach ( $children as $child ) {
			$tmp_doc = new \DOMDocument();
			$tmp_doc->appendChild( $tmp_doc->importNode( $child, true ) );
			$innerHTML .= $tmp_doc->saveHTML();
		}

		return trim( $innerHTML );
	}

}
