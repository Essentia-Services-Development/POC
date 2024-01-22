<?php

namespace ContentEgg\application\libs\walmart;

defined('\ABSPATH') || exit;

use ContentEgg\application\libs\RestClient;

/**
 * WalmartApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link httpS://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 *
 * @link: https://walmart.io/docs/affiliate/introduction
 */
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class WalmartApi extends RestClient
{

    const API_URI_BASE = 'https://developer.api.walmart.com/api-proxy/service/affil/product/v2';

    private $key_version;
    private $consumer_id;
    private $private_key;
    protected $_responseTypes = array(
        'json',
    );

    public function __construct($key_version, $consumer_id, $private_key)
    {
        $this->key_version = $key_version;
        $this->consumer_id = $consumer_id;
        $this->private_key = $private_key;
        $this->setUri(self::API_URI_BASE);
        $this->setResponseType('json');
    }

    /**
     * Search for items
     * @link: https://walmart.io/docs/affiliate/search
     */
    public function search($keywords, array $options)
    {
        $options['query'] = $keywords;
        $response = $this->restGet('/search', $options);

        return $this->_decodeResponse($response);
    }

    /**
     * @link: https://walmart.io/docs/affiliate/product-lookup
     */
    public function searchUpc($upc, $options = array())
    {
        $options['upc'] = $upc;

        $response = $this->restGet('/items', $options);

        return $this->_decodeResponse($response);
    }

    /**
     * Product Lookup API
     * @link: https://walmart.io/docs/affiliate/product-lookup
     */
    public function products($item_ids, $options = array())
    {
        if (is_array($item_ids))
        {
            $item_ids = join(',', $item_ids);
        }

        $options['ids'] = $item_ids;

        $response = $this->restGet('/items', $options);

        return $this->_decodeResponse($response);
    }

    /**
     * Reviews API
     * @link: https://walmart.io/docs/affiliate/reviews
     */
    public function reviews($item_id, $options = array())
    {
        $response = $this->restGet('/reviews/' . urlencode($item_id), $options);

        return $this->_decodeResponse($response);
    }

    /**
     * @link: https://walmart.io/docs/affiliate/onboarding-guide
     */
    public function restGet($path, array $query = null)
    {
        $timestamp = (string) round(microtime(true)) . '555';
        $headers = array(
            'WM_SEC.KEY_VERSION' => $this->key_version,
            'WM_CONSUMER.ID' => $this->consumer_id,
            'WM_CONSUMER.INTIMESTAMP' => $timestamp,
            'WM_SEC.AUTH_SIGNATURE' => $this->calculateSignature($timestamp),
            'Accept' => 'application/json'
        );

        $this->setCustomHeaders($headers);

        return parent::restGet($path, $query);
    }

    private function calculateSignature($timestamp)
    {
        if (!extension_loaded('openssl'))
        {
            throw new \Exception('No OpenSSL extension loaded.');
        }

        $message = $this->consumer_id . "\n" . $timestamp . "\n" . $this->key_version . "\n";
        $pkeyid = openssl_get_privatekey($this->private_key);
        openssl_sign($message, $signature, $pkeyid, OPENSSL_ALGO_SHA256);
        $signature = base64_encode($signature);
        openssl_free_key($pkeyid);

        return $signature;
    }

}
