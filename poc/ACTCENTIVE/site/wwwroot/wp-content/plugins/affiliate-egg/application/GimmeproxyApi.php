<?php

namespace Keywordrush\AffiliateEgg;
defined('\ABSPATH') || exit;

/**
 * GimmeproxyApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 * 
 * @link: https://gimmeproxy.com/#how
 */
class GimmeproxyApi extends RestClient {

    const API_URI_BASE = 'http://gimmeproxy.com/api';

    protected $apiKey;
    protected $_responseTypes = array(
        'json',
    );

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
        $this->setUri(self::API_URI_BASE);
        $this->setResponseType('json');
    }

    public function getProxy(array $params = array())
    {
        $allowed_params = array('get', 'post', 'cookies', 'referer', 'user-agent', 'supportsHttps', 'anonymityLevel', 'protocol', 'port', 'country', 'maxCheckPeriod', 'websites', 'minSpeed', 'notCountry');
        $params = array_intersect_key($params, array_flip($allowed_params));
        $response = $this->restGet('/getProxy', $params);
        return $this->_decodeResponse($response);
    }

    public function restGet($path, array $query = null)
    {
        if ($this->apiKey)
            $query['api_key'] = $this->apiKey;
        return parent::restGet($path, $query);
    }

}
