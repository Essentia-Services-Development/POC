<?php

namespace ExternalImporter\application\libs\pextractor\client;

defined('\ABSPATH') || exit;

/**
 * Browser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022keywordrush.com
 */
class Browser
{

    const TIMEOUT = 60;
    const REDIRECTION = 5;
    const SSLVERIFY = false;

    public static function getUserAgents()
    {
        //@link: https://www.whatismybrowser.com/guides/the-latest-user-agent/
        return array(
            // firefox
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:88.0) Gecko/20100101 Firefox/88.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:100.0) Gecko/20100101 Firefox/100.0',
            'Mozilla/5.0 (X11; Linux i686; rv:88.0) Gecko/20100101 Firefox/88.0',
            // chrome
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.66 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.66 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 11_2_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.192 Safari/537.36',
            // safari
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.3 Safari/605.1.15',
            // edge
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.66 Safari/537.36 Edg/86.0.622.68',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 11_0_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.66 Safari/537.36 Edg/86.0.622.68',
            //opera
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.66 Safari/537.36 OPR/72.0.3815.320',
            'Mozilla/5.0 (Windows NT 10.0; WOW64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.66 Safari/537.36 OPR/72.0.3815.320',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 11_0_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.66 Safari/537.36 OPR/72.0.3815.320',
        );
    }

    public static function getRandomUserAgent()
    {
        $user_agent = self::getUserAgents();
        return $user_agent[array_rand($user_agent)];
    }

    public static function getDefaultHeaders()
    {
        $headers = array(
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language' => 'en-us,en;q=0.5',
            'Cache-Control' => 'no-cache',
            //'Connection' => 'keep-alive',
        );

        /*
          if (function_exists('gzinflate'))
          $headers['Accept-encoding'] = 'gzip, deflate';
          else
          $headers['Accept-encoding'] = 'identity';
         * 
         */

        return $headers;
    }

    public function request($url, array $config = array(), array $httpOptions = array())
    {
        // Rectional between #! URL to _escaped_fragment_ URL
        $url = str_replace('#!', '?_escaped_fragment_=', $url);

        $defaults = array(
            'method' => 'GET',
            'timeout' => self::TIMEOUT,
            'redirection' => self::REDIRECTION,
            'sslverify' => self::SSLVERIFY,
            'body' => null,
            'cookies' => array()
        );

        $session = new Session($url);
        if (isset($config['use_sessions']) && filter_var($config['use_sessions'], FILTER_VALIDATE_BOOLEAN))
            $session->applay($defaults);

        $httpOptions = array_replace($defaults, $httpOptions);

        if (empty($httpOptions['user-agent']))
            $httpOptions['user-agent'] = self::getRandomUserAgent();

        $default_headers = self::getDefaultHeaders();
        if (empty($httpOptions['headers']))
            $httpOptions['headers'] = $default_headers;
        else
            $httpOptions['headers'] = array_replace($default_headers, $httpOptions['headers']);

        foreach ($httpOptions['headers'] as $h_name => $h_value)
        {
            if (!$h_value)
                unset($httpOptions['headers'][$h_name]);
        }

        if (isset($httpOptions['headers']['Cookie']) && isset($httpOptions['cookies']))
            unset($httpOptions['cookies']);

        // Read and respect robots.txt
        if (isset($config['respect_robots']) && filter_var($config['respect_robots'], FILTER_VALIDATE_BOOLEAN))
        {
            $robots = new RobotsTxt($url, $httpOptions);
            if (!$robots->isUrlAllowed())
                throw new \Exception('URL restricted by robots.txt', 100);
        }

        // Debug
        if ($cache = $this->getFromCache($url))
            return $cache;


        $response = \wp_remote_request($url, $httpOptions);
        if (\is_wp_error($response))
            throw new \Exception($response->get_error_message(), 111);

        $session->save($httpOptions['user-agent'], \wp_remote_retrieve_cookies($response));
        $response_code = (int) \wp_remote_retrieve_response_code($response);

        if (!in_array($response_code, array(200, 206, 202)))
            throw new \Exception('Error in URL request: ' . \wp_remote_retrieve_response_message($response) . ' (' . $response_code . ')', $response_code);

        $html = \wp_remote_retrieve_body($response);
        $this->saveToCache($url, $html);
        return $html;
    }

    private function saveToCache($url, $html)
    {
        if (!\ExternalImporter\application\Plugin::isDevEnvironment())
            return;

        if (!file_put_contents($this->getCacheFileName($url), $html))
            return false;
    }

    private function getFromCache($url)
    {
        if (!\ExternalImporter\application\Plugin::isDevEnvironment())
            return;

        $filename = $this->getCacheFileName($url);
        if (file_exists($filename) && is_readable($filename) && filectime($filename) > time() - 30 * 3600 * 24)
            return file_get_contents($filename);

        return false;
    }

    private function getCacheFileName($url)
    {
        $file_name = \sanitize_file_name(md5($url)) . '.html';
        return trailingslashit($this->getTemporaryDirectory()) . $file_name;
    }

    protected function getTemporaryDirectory()
    {
        $upload_dir = \wp_upload_dir();
        $dir = $upload_dir['basedir'] . '/ei-debug';

        if (is_dir($dir))
            return $dir;

        $files = array(
            array(
                'file' => 'index.html',
                'content' => '',
            ),
            array(
                'file' => '.htaccess',
                'content' => 'deny from all',
            ),
        );

        foreach ($files as $file)
        {
            if (\wp_mkdir_p($dir) && !file_exists(trailingslashit($dir) . $file['file']))
            {
                if ($file_handle = @fopen(trailingslashit($dir) . $file['file'], 'w'))
                {
                    fwrite($file_handle, $file['content']);
                    fclose($file_handle);
                }
            }
        }

        if (!is_dir($dir))
            throw new \Exception('Can not create temporary directory.');

        return $dir;
    }
}
