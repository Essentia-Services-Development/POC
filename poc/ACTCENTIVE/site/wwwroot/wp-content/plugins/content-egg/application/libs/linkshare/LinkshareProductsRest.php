<?php

namespace ContentEgg\application\libs\linkshare;

defined('\ABSPATH') || exit;

use ContentEgg\application\libs\RestClient;

/**
 * LinkshareProductsRest class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 *
 * @link: https://rakutenlinkshare.zendesk.com/hc/en-us/articles/200973227-Product-Search-API-Overview
 * @link: https://developers.rakutenmarketing.com/console/registry/resource/_system/governance/apimgt/applicationdata/provider/LinkShare/ProductSearch/1.0/documentation/files/ProductSearch_1.0-Guide(updated).pdf
 * @link: https://developers.rakutenadvertising.com/documentation/en-US/affiliate_apis
 * @link: https://developers.rakutenadvertising.com/guides/product_search
 *
 */
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class LinkshareProductsRest extends RestClient
{

    const API_URI_BASE = 'http://productsearch.linksynergy.com';

    private $token;

    /**
     * @var array Response Format Types
     */
    protected $_responseTypes = array(
        'xml',
    );

    /**
     * Constructor
     *
     * @param string $responseType
     */
    public function __construct($token)
    {
        $this->setResponseType('xml');
        $this->setUri(self::API_URI_BASE);
        $this->token = $token;
    }

    public function search($keyword, array $params = array(), $search_logic = 'AND')
    {
        switch ($search_logic)
        {
            case 'AND':
                $params['keyword'] = $keyword;
                break;
            case 'OR':
                $params['one'] = $keyword;
                break;
            case 'EXACT':
                $params['exact'] = $keyword;
                break;
            default :
                $params['keyword'] = $keyword;
        }
        $params['token'] = $this->token;
        $response = $this->restGet('/productsearch', $params);

        $response = preg_replace('/(<price\scurrency=[\'"]([A-Z]+?)[\'"]>[0-9\.]+?<\/price>)/', '$1<currency>$2</currency>', $response);

        return $this->_decodeResponse($response);
    }

}
