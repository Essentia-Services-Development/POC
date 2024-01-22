<?php

namespace ContentEgg\application\libs\ebay;

defined('\ABSPATH') || exit;

use ContentEgg\application\libs\RestClient;

/**
 * EbayBrowse class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 *
 * Ebay Browse API
 * @link: https://developer.ebay.com/api-docs/buy/browse/overview.html
 */
class EbayBrowse extends RestClient
{

    const API_URI_BASE = 'https://api.ebay.com/buy/browse/v1';

    private $siteid;
    private $app_id;
    private $cert_id;
    private $access_token;
    protected $_responseTypes = array(
        'json',
    );

    public function __construct($app_id, $cert_id)
    {
        $this->app_id = $app_id;
        $this->cert_id = $cert_id;
        $this->setUri(self::API_URI_BASE);
        $this->setResponseType('json');
    }

    public function setAccessToken($access_token)
    {
        $this->access_token = $access_token;
    }

    /**
     * @link: https://developer.ebay.com/api-docs/buy/browse/resources/item_summary/methods/search
     */
    public function search($keywords, $options = array(), $headers = array())
    {
        $options['q'] = $keywords;

        $this->setCustomHeaders($headers);
        $response = $this->restGet('/item_summary/search', $options);

        return $this->_decodeResponse($response);
    }

    public function searchByGtin($gtin, $options = array(), $headers = array())
    {
        $options['gtin'] = $gtin;

        $this->setCustomHeaders($headers);
        $response = $this->restGet('/item_summary/search', $options);

        return $this->_decodeResponse($response);
    }

    public function searchByEpid($epid, $options = array(), $headers = array())
    {
        $options['epid'] = $epid;

        $this->setCustomHeaders($headers);
        $response = $this->restGet('/item_summary/search', $options);

        return $this->_decodeResponse($response);
    }

    /**
     * @link: https://developer.ebay.com/api-docs/buy/browse/resources/item/methods/getItem
     */
    public function getItem($id, $options, $headers = array())
    {
        $this->setCustomHeaders($headers);
        $response = $this->restGet('/item/' . urlencode($id), $options);

        return $this->_decodeResponse($response);
    }

    /**
     * @link: https://developer.ebay.com/api-docs/buy/browse/resources/item/methods/getItemByLegacyId
     */
    public function getItemByLegacyId($id, $options, $headers = array())
    {
        $options['legacy_item_id'] = $id;

        $this->setCustomHeaders($headers);
        $response = $this->restGet('/item/get_item_by_legacy_id', $options);

        return $this->_decodeResponse($response);
    }

    /**
     * @link: https://developer.ebay.com/api-docs/buy/browse/resources/item/methods/getItemsByItemGroup
     */
    public function getItemsByItemGroup($id, $options, $headers = array())
    {
        $options['item_group_id'] = $id;

        $this->setCustomHeaders($headers);
        $response = $this->restGet('/item/get_items_by_item_group', $options);

        return $this->_decodeResponse($response);
    }

    public function requestAccessToken()
    {
        $query = array(
            'grant_type' => 'client_credentials',
            'scope' => 'https://api.ebay.com/oauth/api_scope',
        );
        $this->setCustomHeaders(array('Authorization' => 'Basic ' . base64_encode($this->app_id . ":" . $this->cert_id)));
        $response = $this->restPost('https://api.ebay.com/identity/v1/oauth2/token', $query);

        return $this->_decodeResponse($response);
    }

    public function restGet($path, array $query = null)
    {
        $this->addCustomHeaders(array('Authorization' => 'Bearer ' . $this->access_token));

        return parent::restGet($path, $query);
    }
}
