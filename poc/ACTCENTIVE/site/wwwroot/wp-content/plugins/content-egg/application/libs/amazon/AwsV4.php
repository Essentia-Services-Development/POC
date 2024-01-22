<?php

namespace ContentEgg\application\libs\amazon;

defined('\ABSPATH') || exit;
/**
 * AWS Signature Version 4 code to sign your request to AWS
 * Modified version of Amazon AwsV4 class (https://webservices.amazon.com/paapi5/documentation/without-sdk.html)
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2019 keywordrush.com
 */

/**
 * Copyright 2019 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */
class AwsV4 {

    private $accessKeyID = null;
    private $secretAccessKey = null;
    private $path = null;
    private $regionName = null;
    private $serviceName = 'ProductAdvertisingAPI';
    private $httpMethodName = null;
    private $awsHeaders = array();
    private $payload = "";
    private $HMACAlgorithm = "AWS4-HMAC-SHA256";
    private $aws4Request = "aws4_request";
    private $strSignedHeader = null;
    private $xAmzDate = null;
    private $currentDate = null;
    private $operation = null;
    private $host = null;

    public function __construct($accessKeyID, $secretAccessKey)
    {
        $this->accessKeyID = $accessKeyID;
        $this->secretAccessKey = $secretAccessKey;
        $this->xAmzDate = $this->getTimeStamp();
        $this->currentDate = $this->getDate();
    }

    function setPath($path)
    {
        $this->path = $path;
    }

    function setOperation($operation)
    {
        $this->operation = $operation;
    }

    function setHost($host)
    {
        $this->host = $host;
    }

    function setServiceName($serviceName)
    {
        $this->serviceName = $serviceName;
    }

    function setRegionName($regionName)
    {
        $this->regionName = $regionName;
    }

    function setPayload($payload)
    {
        $this->payload = $payload;
    }

    function setRequestMethod($method)
    {
        $this->httpMethodName = $method;
    }

    function addHeader($headerName, $headerValue)
    {
        $this->awsHeaders [$headerName] = $headerValue;
    }

    private function prepareCanonicalRequest()
    {
        $canonicalURL = "";
        $canonicalURL .= $this->httpMethodName . "\n";
        $canonicalURL .= $this->path . "\n" . "\n";
        $signedHeaders = '';
        foreach ($this->awsHeaders as $key => $value)
        {
            $signedHeaders .= $key . ";";
            $canonicalURL .= $key . ":" . $value . "\n";
        }
        $canonicalURL .= "\n";
        $this->strSignedHeader = substr($signedHeaders, 0, - 1);
        $canonicalURL .= $this->strSignedHeader . "\n";
        $canonicalURL .= $this->generateHex($this->payload);

        return $canonicalURL;
    }

    private function prepareStringToSign($canonicalURL)
    {
        $stringToSign = '';
        $stringToSign .= $this->HMACAlgorithm . "\n";
        $stringToSign .= $this->xAmzDate . "\n";
        $stringToSign .= $this->currentDate . "/" . $this->regionName . "/" . $this->serviceName . "/" . $this->aws4Request . "\n";
        $stringToSign .= $this->generateHex($canonicalURL);

        return $stringToSign;
    }

    private function calculateSignature($stringToSign)
    {
        $signatureKey = $this->getSignatureKey($this->secretAccessKey, $this->currentDate, $this->regionName, $this->serviceName);
        $signature = hash_hmac("sha256", $stringToSign, $signatureKey, true);
        $strHexSignature = strtolower(bin2hex($signature));

        return $strHexSignature;
    }

    public function getHeaders()
    {
        $this->awsHeaders['content-encoding'] = 'amz-1.0';
        $this->awsHeaders['content-type'] = 'application/json; charset=utf-8';
        $this->awsHeaders['host'] = $this->host;
        $this->awsHeaders['x-amz-date'] = $this->xAmzDate;
        $this->awsHeaders['x-amz-target'] = $this->buildAmzTarget();

        ksort($this->awsHeaders);
        $canonicalURL = $this->prepareCanonicalRequest();
        $stringToSign = $this->prepareStringToSign($canonicalURL);
        $signature = $this->calculateSignature($stringToSign);
        if ($signature)
        {
            $this->awsHeaders ['Authorization'] = $this->buildAuthorizationString($signature);

            return $this->awsHeaders;
        }
    }

    /**
     * Build Amz target for X-Amz-Target header
     * @return - Amz target
     */
    private function buildAmzTarget()
    {
        return 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.' . $this->operation;
    }

    private function buildAuthorizationString($strSignature)
    {
        return $this->HMACAlgorithm . " " . "Credential=" . $this->accessKeyID . "/" . $this->getDate() . "/" . $this->regionName . "/" . $this->serviceName . "/" . $this->aws4Request . "," . "SignedHeaders=" . $this->strSignedHeader . "," . "Signature=" . $strSignature;
    }

    private function generateHex($data)
    {
        return strtolower(bin2hex(hash("sha256", $data, true)));
    }

    private function getSignatureKey($key, $date, $regionName, $serviceName)
    {
        $kSecret = "AWS4" . $key;
        $kDate = hash_hmac("sha256", $date, $kSecret, true);
        $kRegion = hash_hmac("sha256", $regionName, $kDate, true);
        $kService = hash_hmac("sha256", $serviceName, $kRegion, true);
        $kSigning = hash_hmac("sha256", $this->aws4Request, $kService, true);

        return $kSigning;
    }

    private function getTimeStamp()
    {
        return gmdate("Ymd\THis\Z");
    }

    private function getDate()
    {
        return gmdate("Ymd");
    }

}
