<?php

namespace ContentEgg\application\libs\impactradius;

defined('\ABSPATH') || exit;

use ContentEgg\application\libs\RestClient;

/**
 * ImpactradiusApi class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 *
 * @link: https://developer.impact.com/docs/apis/media-partners-product-data/versions/11/getting-started
 */
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class ImpactradiusApi extends RestClient {

    const _API_VERSION = 12;
    const API_URI_BASE = 'https://product.api.impactradius.com/Mediapartners';

    protected $AccountSid;
    protected $AuthToken;

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
    public function __construct($AccountSid, $AuthToken)
    {
        $this->AccountSid = $AccountSid;
        $this->AuthToken = $AuthToken;
        $this->setUri(self::API_URI_BASE);
        $this->setResponseType('json');
    }

    /**
     * Catalog Item Search
     * @link https://developer.impact.com/docs/apis/media-partners-product-data/versions/11/resources/catalog-items
     */
    public function search($keyword, array $options = array())
    {
        $options['Keyword'] = $keyword;
        $response = $this->restGet('/Catalogs/ItemSearch', $options);

        return $this->_decodeResponse($response);
    }

    public function product($catalog_id, $product_id)
    {
        $path = '/Catalogs/' . $catalog_id . '/Items/' . $product_id;
        $response = $this->restGet($path);

        return $this->_decodeResponse($response);
    }

    public function restGet($path, array $query = null)
    {
        $query['IrVersion'] = self::_API_VERSION; // force API version
        $path = '/' . $this->AccountSid . $path;
        
        $this->setCustomHeaders(array(
            'Authorization' => 'Basic ' . base64_encode($this->AccountSid . ':' . $this->AuthToken),
            'Accept' => 'application/json'
        ));

        return parent::restGet($path, $query);
    }

}
