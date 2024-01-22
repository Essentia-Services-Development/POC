<?php

namespace ContentEgg\application\libs;

use ContentEgg\application\helpers\TextHelper;

/**
 * RestClient class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 *
 * Simple Rest Client
 * @todo: PUT/DELETE Request
 */
class RestClient {

    protected static $timeout = 15; //sec
    protected static $useragent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:93.0) Gecko/20100101 Firefox/93.0';

    /**
     * Endpoint uri of this web service
     * @var string
     */
    protected $_uri = null;

    /**
     * @var String Return Type
     */
    protected $_responseType = null;

    /**
     * @var array Response Format Types
     */
    protected $_responseTypes = array();
    protected $_custom_header = array();
    protected static $_httpClient = null;

    public function __construct($uri = null)
    {
        if (!empty($uri))
        {
            $this->setUri($uri);
        }
    }

    /**
     * Set responseType
     */
    public function setResponseType($responseType = 'json')
    {
        if (!in_array($responseType, $this->_responseTypes, true))
        {
            throw new \Exception('Invalid Response Type');
        }
        $this->_responseType = $responseType;
    }

    /**
     * Retrieve responseType
     */
    public function getResponseType()
    {
        return $this->_responseType;
    }

    /**
     * Sets the HTTP client object to use for retrieving the feeds.  If none
     * is set, the default Http_Client will be used.
     */
    public static function setHttpClient($httpClient)
    {
        self::$_httpClient = $httpClient;
    }

    /**
     * Gets the HTTP client object.
     */
    public static function getHttpClient($opts = array())
    {
        $_opts = array(
            'sslverify' => false,
            'redirection' => 5,
            'timeout' => static::$timeout,
            'user-agent' => static::$useragent,
        );
        if ($opts)
        {
            $_opts = $opts + $_opts;
        }

        if (self::$_httpClient == null)
        {
            //Get WP http client
            self::$_httpClient = new WpHttpClient();
            self::$_httpClient->setHeaders('Accept-Charset', 'ISO-8859-1,utf-8');
            self::$_httpClient->setRedirection($_opts['redirection']);
            self::$_httpClient->setTimeout($_opts['timeout']);
            self::$_httpClient->setSslVerify($_opts['sslverify']);
            self::$_httpClient->setUserAgent($_opts['user-agent']);
        }

        return self::$_httpClient;
    }

    /**
     * Set the URI to use in the request
     */
    public function setUri($uri)
    {
        $this->_uri = $uri;
    }

    public function getUri()
    {
        return $this->_uri;
    }

    public function setCustomHeaders($headers = array())
    {
        $this->_custom_header = $headers;
    }

    public function addCustomHeaders($headers = array())
    {
        $this->_custom_header = array_merge($this->_custom_header, $headers);
    }

    /**
     * Performs an HTTP GET request
     *
     * @param string $path
     * @param array $query Array of GET parameters
     */
    public function restGet($path, array $query = null)
    {
        $this->_prepareRest($path);
        $client = self::getHttpClient();
        $client->setParameterGet($query);

        return $this->_getResult($client->request('GET'));
    }

    private function _prepareRest($path)
    {

        if (strstr($path, 'http://') || strstr($path, 'https://'))
        {
            $uri = $path;
        } else
        {
            $uri = $this->getUri();
            if ($path && $path[0] != '/' && $uri[strlen($uri) - 1] != '/')
            {
                $path = '/' . $path;
            }
            $uri = $uri . $path;
        }

        $client = self::getHttpClient();

        $client->resetParameters();
        $client->setUri($uri);

        foreach ($this->_custom_header as $header => $value)
        {
            $client->setHeaders($header, $value);
        }
    }

    /**
     * Performs an HTTP POST request
     *
     * @param string $path
     * @param mixed $data Raw data to send
     * @param string $enctype
     */
    public function restPost($path, $data = null, $enctype = null, $opts = array())
    {
        $this->_prepareRest($path);
        $client = self::getHttpClient($opts);
        if (is_string($data))
        {
            $client->setRawData($data, $enctype);
        } elseif (is_array($data) || is_object($data))
        {
            $client->setParameterPost((array) $data);
        }

        return $this->_getResult($client->request('POST'));
    }

    final public function get($path, array $query = null)
    {
        return $this->restGet($path, $query);
    }

    protected function _getResult($response)
    {
        if (\is_wp_error($response))
        {
            $error_mess = "HTTP request fails: " . $response->get_error_code() . " - " . $response->get_error_message() . '.';
            throw new \Exception($error_mess);
        }

        $this->myErrorHandler($response);

        return \wp_remote_retrieve_body($response);
    }

    protected function myErrorHandler($response)
    {
        $response_code = (int) \wp_remote_retrieve_response_code($response);
        if ($response_code != 200 && $response_code != 206)
        {
            $response_message = \wp_remote_retrieve_response_message($response);
            $error_mess = "HTTP request status fails: " . $response_code . " - " . $response_message . '.';
            $error_mess .= ' Server replay: ' . \wp_remote_retrieve_body($response);
            throw new \Exception($error_mess, $response_code);
        }
    }

    protected function _decodeResponse($response, $responseType = null)
    {
        if ($responseType == null)
        {
            $responseType = $this->_responseType;
        }

        switch ($responseType)
        {
            case 'php':
            case 'php_serial':
                $res = @unserialize($response);
                if ($res === false)
                {
                    throw new \Exception('Response serialization error.');
                }
                break;
            case 'json':
                $res = json_decode($response, true);
                break;
            case 'xml':
            case 'rss':
            case 'atom':
                $res = TextHelper::unserialize_xml($response);
                break;
            default :
                $res = $response;
        }
        if (is_array($res))
        {
            array_walk_recursive($res, array($this, '_fixUtf8'));
        } elseif (is_scalar($res))
        {
            $this->_fixUtf8($res);
        }

        return $res;
    }

    protected function _fixUtf8(&$text)
    {
        $regex = '/
					(
						(?: [\x00-\x7F]                  # single-byte sequences   0xxxxxxx
						|   [\xC2-\xDF][\x80-\xBF]       # double-byte sequences   110xxxxx 10xxxxxx
						|   \xE0[\xA0-\xBF][\x80-\xBF]   # triple-byte sequences   1110xxxx 10xxxxxx * 2
						|   [\xE1-\xEC][\x80-\xBF]{2}
						|   \xED[\x80-\x9F][\x80-\xBF]
						|   [\xEE-\xEF][\x80-\xBF]{2}';
        $regex .= '){1,40}                          # ...one or more times
					)
					| .                                  # anything else
					/x';
        $text = preg_replace($regex, '$1', $text);
    }

}
