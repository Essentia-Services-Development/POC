<?php

namespace ContentEgg\application\libs\bolcom;

defined('\ABSPATH') || exit;

use ContentEgg\application\libs\RestClient;

/**
 * BolcomApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 *
 * @link: https://affiliate.bol.com/nl/api-documentatie
 */
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class BolcomApi extends RestClient {

    const API_URI_BASE = 'https://api.bol.com/catalog/v4';

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
     * Product Lookup API
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

    public function restGet($path, array $query = null)
    {
        $query['apikey'] = $this->apiKey;
        $query['format'] = 'json';

        return parent::restGet($path, $query);
    }

}
