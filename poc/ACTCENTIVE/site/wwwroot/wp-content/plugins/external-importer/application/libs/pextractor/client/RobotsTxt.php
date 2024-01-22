<?php

namespace ExternalImporter\application\libs\pextractor\client;

defined('\ABSPATH') || exit;

use ExternalImporter\application\vendor\RobotsTxtParser\RobotsTxtParser;
use ExternalImporter\application\vendor\RobotsTxtParser\RobotsTxtValidator;

/**
 * RobotsTxt class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2019 keywordrush.com
 */
class RobotsTxt {

    const TRANSIENT_PREFIX = 'EI_ROBOTSTXT_';
    const EXPIRATIONS_DAYS = 7;
    const EXPIRATIONS_DAYS_IF_ERROR = 1;

    protected $host;
    protected $url;
    protected $transinetName;
    protected $httpArgs;

    public function __construct($url, array $httpArgs = array())
    {
        $this->url = $url;
        $this->httpArgs = $httpArgs;
        $this->host = strtolower(parse_url($url, PHP_URL_HOST));
        $this->transinetName = $this->generateTransinetName();
    }

    protected function generateTransinetName()
    {
        return self::TRANSIENT_PREFIX . $this->host;
    }

    public function getRobots()
    {
        $contents = \get_transient($this->transinetName);
        if ($contents === false)
        {
            $contents = $this->getRemoteRobots();
            if ($contents === false)
            {
                $contents = '';
                $expiration_days = self::EXPIRATIONS_DAYS_IF_ERROR;
            } else
                $expiration_days = self::EXPIRATIONS_DAYS;

            \set_transient($this->transinetName, $contents, 24 * 3600 * $expiration_days);
        }

        return $contents;
    }

    public function getRemoteRobots()
    {
        $scheme = strtolower(parse_url($this->url, PHP_URL_SCHEME));
        $r_uri = $scheme . '://' . $this->host . '/robots.txt';

        $response = \wp_remote_get($r_uri, $this->httpArgs);
        if (\is_wp_error($response))
            return false;

        $response_code = (int) \wp_remote_retrieve_response_code($response);
        if ($response_code == 404)
            return ''; // Ok

        if ($response_code !== 200)
            return false;

        return \wp_remote_retrieve_body($response);
    }

    public function isUrlAllowed()
    {
        $parser = new RobotsTxtParser($this->getRobots());
        $validator = new RobotsTxtValidator($parser->getRules());

        if (!empty($this->httpArgs['user-agent']))
            $userAgent = $this->httpArgs['user-agent'];
        else
            $userAgent = '*';

        if ($validator->isUrlAllow($this->url, $userAgent))
            return true;
        else
            return false;
    }

}
