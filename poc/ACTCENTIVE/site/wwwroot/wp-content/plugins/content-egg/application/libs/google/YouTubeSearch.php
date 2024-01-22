<?php

namespace ContentEgg\application\libs\google;

defined('\ABSPATH') || exit;

use ContentEgg\application\libs\RestClient;

/**
 * YouTubeSearch class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 *
 * @link: https://developers.google.com/youtube/v3/docs/search/list
 *
 */
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class YouTubeSearch extends RestClient {

    const API_URI_BASE = 'https://www.googleapis.com/youtube/v3';

    /**
     * @var array Response Format Types
     */
    protected $_responseTypes = array(
        'atom',
        'json',
    );

    /**
     * Constructor
     *
     * @param string $responseType
     */
    public function __construct($responseType = 'json')
    {
        $this->setResponseType($responseType);
        $this->setUri(self::API_URI_BASE);
    }

    public function search($query, array $params = array())
    {
        $_query = array();
        $_query['q'] = $query;
        $_query['part'] = 'snippet';
        $_query['videoEmbeddable'] = 'true';
        $_query['type'] = 'video';


        //$params['format'] = 5;

        foreach ($params as $key => $param)
        {
            switch ($key)
            {
                case 'relevanceLanguage':
                case 'order':
                //case 'format':
                case 'key':
                case 'part':
                case 'safeSearch':
                case 'channelId':
                case 'videoEmbeddable':
                case 'type':
                case 'videoLicense':
                    $_query[$key] = $param;
                    break;
                case 'maxResults':
                case 'pageToken':
                    $_query[$key] = ( (int) $param > 50 ) ? 50 : (int) $param;
                    break;
                case 'strict':
                    $_query[$key] = ( (bool) $param ) ? true : false;
                    break;
            }
        }
        $response = $this->restGet('/search', $_query);

        return $this->_decodeResponse($response);
    }

}
