<?php

namespace ContentEgg\application\libs\bolcom;

defined('\ABSPATH') || exit;

use ContentEgg\application\libs\RestClient;

/**
 * BolcomJwtApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2022 keywordrush.com
 *
 * @link: https://affiliate.bol.com/nl/handleiding/handleiding-toegang-api
 */
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class BolcomJwtApi extends RestClient {

    const API_URI_BASE = 'https://api.bol.com/catalog/v4';

    protected $client_id;
    protected $client_secret;
    protected $access_token;
    protected $_responseTypes = array(
        'json',
    );

    public function __construct($client_id, $client_secret)
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->setUri(self::API_URI_BASE);
        $this->setResponseType('json');
    }

    public function setAccessToken($access_token)
    {
        $this->access_token = $access_token;
    }

    /**
     * Search for items
     * @link: https://affiliate.bol.com/nl/api-documentatie#get-catalog-v4-search
     */
    public function search($keywords, array $options)
    {
        $options['q'] = $keywords;
        $response = $this->restGet('/search', $options);

        return $this->_decodeResponse($response);
    }

    /**
     * The products operation gets detailed information for products.
     * @link: https://affiliate.bol.com/nl/api-documentatie#get-catalog-v4-products-{productId}
     */
    public function products($item_ids, $options = array())
    {
        //The unique id for one or more products (comma separated).
        if (is_array($item_ids))
        {
            $item_ids = join(',', $item_ids);
        }

        $response = $this->restGet('/products/' . urlencode($item_ids), $options);

        return $this->_decodeResponse($response);
    }

    public function requestAccessToken()
    {
        $query = array(
            'grant_type' => 'client_credentials',
        );

        $this->setCustomHeaders(array('Authorization' => 'Basic ' . base64_encode($this->client_id . ":" . $this->client_secret)));
        $response = $this->restPost('https://login.bol.com/token', $query);
        return $this->_decodeResponse($response);
    }

    public function restGet($path, array $query = null)
    {
        $this->addCustomHeaders(array('Authorization' => 'Bearer ' . $this->access_token));
        return parent::restGet($path, $query);
    }

}
