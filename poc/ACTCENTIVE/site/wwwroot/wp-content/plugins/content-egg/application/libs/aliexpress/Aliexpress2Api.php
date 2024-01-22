<?php

namespace ContentEgg\application\libs\aliexpress;

defined('\ABSPATH') || exit;

use ContentEgg\application\libs\RestClient;

/**
 * Aliexpress2Api class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 *
 * Aliexpress Affiliates API
 * @link: https://developers.aliexpress.com/en/doc.htm?apiName=aliexpress.affiliate.product.query&docId=118192&docType=1
 */
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'RestClient.php';

class Aliexpress2Api extends RestClient
{

    const API_URI_BASE = 'http://gw.api.taobao.com/router/rest';

    protected $app_key;
    protected $app_secret;
    protected $_responseTypes = array(
        'json',
    );
    protected $sign_method = 'md5';
    protected $api_version = '2.0';

    /**
     * Constructor
     *
     * @param string $responseType
     */
    public function __construct($app_key, $app_secret)
    {
        $this->app_key = $app_key;
        $this->app_secret = $app_secret;
        $this->setResponseType('json');
        $this->setUri(self::API_URI_BASE);
    }

    /**
     * Product search
     * @link: https://developers.aliexpress.com/en/doc.htm?apiName=aliexpress.affiliate.product.query&docId=45803&docType=2
     */
    public function search($keywords, array $options)
    {
        $options['keywords'] = $keywords;
        $options['method'] = 'aliexpress.affiliate.product.query';

        $response = $this->restGet('', $options);

        return $this->_decodeResponse($response);
    }

    /**
     * Hot product search
     * @link: https://developers.aliexpress.com/en/doc.htm?docId=45794&docType=2&source=search
     */
    public function searchHot($keywords, array $options)
    {
        $options['keywords'] = $keywords;
        $options['method'] = 'aliexpress.affiliate.hotproduct.query';

        $response = $this->restGet('', $options);

        return $this->_decodeResponse($response);
    }

    /**
     * Product details
     * @link: https://developers.aliexpress.com/en/doc.htm?docId=48595&docType=2&source=search
     */
    public function product($product_id, array $options)
    {
        if (is_array($product_id))
        {
            $product_id = join(',', $product_id);
        }

        $options['product_ids'] = $product_id;
        $options['method'] = 'aliexpress.affiliate.productdetail.get';

        $response = $this->restGet('', $options);

        return $this->_decodeResponse($response);
    }

    protected function generateSign($params)
    {
        ksort($params);
        $stringToBeSigned = $this->app_secret;
        foreach ($params as $k => $v)
        {
            $stringToBeSigned .= $k . $v;
        }
        $stringToBeSigned .= $this->app_secret;

        return strtoupper(md5($stringToBeSigned));
    }

    public function restGet($path, array $query = null)
    {
        $query['app_key'] = $this->app_key;
        $query['v'] = $this->api_version;
        $query['format'] = $this->getResponseType();
        $query['sign_method'] = $this->sign_method;
        $query['timestamp'] = date('Y-m-d H:i:s');
        $query['sign'] = $this->generateSign($query);

        return parent::restGet($path, $query);
    }

    protected function myErrorHandler($response)
    {
        $response_code = (int) \wp_remote_retrieve_response_code($response);
        $data = $this->_decodeResponse(\wp_remote_retrieve_body($response));

        if ($response_code != 200 || !$data)
        {
            return parent::myErrorHandler($response);
        }

        $mess = '';

        $r = reset($data);
        if ($r && isset($r['resp_result']['resp_code']) && (int) $r['resp_result']['resp_code'] != 200)
        {
            $code = (int) $r['resp_result']['resp_code'];
            $mess = $r['resp_result']['resp_msg'];
        } elseif (isset($data['error_response']))
        {
            $code = (int) $data['error_response']['code'];
            $mess = $data['error_response']['msg'];
            if (isset($data['error_response']['sub_msg']))
            {
                $mess .= ' - ' . $data['error_response']['sub_msg'];
            }
        }

        if ($mess)
        {
            throw new \Exception($mess, $code);
        }

        return parent::myErrorHandler($response);
    }

}
