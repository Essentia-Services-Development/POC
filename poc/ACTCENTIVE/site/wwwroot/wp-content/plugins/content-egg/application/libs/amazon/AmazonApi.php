<?php

namespace ContentEgg\application\libs\amazon;

defined('\ABSPATH') || exit;

use ContentEgg\application\libs\RestClient;
use ContentEgg\application\libs\amazon\AmazonLocales;
use ContentEgg\application\libs\amazon\AwsV4;

/**
 * PHP interface to Amazon Product Advertising API 5.0
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 *
 * @link: https://webservices.amazon.com/paapi5/documentation/
 */
class AmazonApi extends RestClient {

    private $access_key_id;
    private $secret_access_key;
    private $associate_tag;
    private $locale;
    protected $_responseTypes = array(
        'json',
    );

    public function __construct($access_key_id, $secret_access_key, $associate_tag, $locale = 'us')
    {
        $this->access_key_id = $access_key_id;
        $this->secret_access_key = $secret_access_key;
        $this->setAssociateTag($associate_tag);
        $this->setLocale($locale);
        $this->setResponseType('json');
    }

    public function setAssociateTag($associate_tag)
    {
        $this->associate_tag = $associate_tag;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
        $this->setUri(AmazonLocales::getApiEndpoint($this->locale));
    }

    public function getAssociateTag()
    {
        return $this->associate_tag;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    /*
     * The SearchItems operation searches for items on Amazon based on a search query.
     * @link: https://webservices.amazon.com/paapi5/documentation/search-items.html
     */

    public function SearchItems(array $payload)
    {
        $payload['Operation'] = 'SearchItems';
        $payload['PartnerType'] = 'Associates';

        $response = $this->restPost('/paapi5/searchitems', $payload);

        return $this->_decodeResponse($response);
    }

    /**
     * @link: https://webservices.amazon.com/paapi5/documentation/get-items.html
     */
    public function GetItems(array $payload)
    {
        $payload['Operation'] = 'GetItems';
        $payload['PartnerType'] = 'Associates';
        $payload['ItemIdType'] = 'ASIN';
        $response = $this->restPost('/paapi5/getitems', $payload);

        return $this->_decodeResponse($response);
    }

    /**
     * @link: https://webservices.amazon.com/paapi5/documentation/get-variations.html
     */
    public function GetVariations(array $payload)
    {
        $payload['Operation'] = 'GetVariations';
        $payload['PartnerType'] = 'Associates';
        $response = $this->restPost('/paapi5/getvariations', $payload);

        return $this->_decodeResponse($response);
    }

    public function restPost($path, $data = null, $enctype = null, $opts = array())
    {
        $awsv4 = new AwsV4($this->access_key_id, $this->secret_access_key);
        if (isset($data['Operation']))
        {
            $awsv4->setOperation($data['Operation']);
        }
        $data = json_encode($data);
        $awsv4->setRegionName(AmazonLocales::getRegion($this->locale));
        $awsv4->setPath($path);
        $awsv4->setPayload($data);
        $awsv4->setRequestMethod("POST");
        $awsv4->setHost(parse_url($this->getUri(), PHP_URL_HOST));
        $this->setCustomHeaders($awsv4->getHeaders());

        return parent::restPost($path, $data);
    }

    protected function myErrorHandler($response)
    {
        $response_code = (int) \wp_remote_retrieve_response_code($response);
        $data = $this->_decodeResponse(\wp_remote_retrieve_body($response));

        if ($response_code == 200 && isset($data['Errors']) && count($data) > 1)
        {
            return;
        }

        if (!isset($data['Errors']))
        {
            return parent::myErrorHandler($response);
        }

        $errors = array();
        foreach ($data['Errors'] as $error)
        {
            $message = $error['Message'];
            if ($error['Code'] == 'TooManyRequests')
            {
                $message = str_replace('Please verify the number of requests made per second to the Amazon Product Advertising API.', '', $message);
                $message .= ' For more information please refer to https://ce-docs.keywordrush.com/modules/affiliate/amazon#api-rates';
            }

            $errors[] = $error['Code'] . ': ' . $message;
        }
        $error_mess = join('; ', $errors);

        throw new \Exception($error_mess, $response_code);
    }

}
