<?php

namespace ExternalImporter\application\libs\pextractor\parser\parsers;

defined('\ABSPATH') || exit;

use ExternalImporter\application\libs\pextractor\parser\AbstractParser;
use ExternalImporter\application\libs\pextractor\parser\ParserFormat;
use ExternalImporter\application\libs\pextractor\parser\Product;
use ExternalImporter\application\libs\pextractor\client\Browser;

/**
 * MicrodataParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class AdvancedParser extends AbstractParser {

    const FORMAT = ParserFormat::ADVANCED_PARSER;

    protected $url;
    protected $user_agent;
    protected $cookies;
    protected $timeout;

    public function getHttpOptions()
    {
        $options = parent::getHttpOptions();
        if ($this->user_agent)
        {
            if (!is_array($this->user_agent))
                $this->user_agent = array($this->user_agent);
            $options['user-agent'] = $this->user_agent[array_rand($this->user_agent)];
        }

        if ($this->cookies)
            $options['cookies'] = $this->cookies;
        if ($this->timeout)
            $options['timeout'] = $this->timeout;

        return $options;
    }

    public function afterParseFix(Product $product)
    {
        return $product;
    }

    public function getRemoteJson($url, $use_session = false, $method = 'GET', array $headers = array())
    {
        $browser = new Browser();
        $config = array('use_sessions' => $use_session);
        $httpOptions['headers'] = array(
            'Accept' => 'application/json, text/plain, */*',
            'Accept-Language' => 'en-us,en;q=0.5',
            'Cache-Control' => 'no-cache',
        );
        $httpOptions['method'] = $method;        
        $httpOptions['headers'] = array_merge($httpOptions['headers'], $headers);
        
        $url = \apply_filters('ei_create_from_url', $url, $httpOptions);
        
        try
        {
            $response = $browser->request($url, $config, $httpOptions);
        } catch (\Exception $e)
        {
            return false;
        }

        $response = str_replace('<?xml encoding="UTF-8">', '', $response);
        if (!$result = json_decode($response, true))
            return false;

        return $result;
    }

    public function getRemote($url, $use_session = false)
    {
        $browser = new Browser();
        $config = array('use_sessions' => $use_session);
        try
        {
            $response = $browser->request($url, $config);
        } catch (\Exception $e)
        {
            return false;
        }

        return $response;
    }

}
