<?php

namespace Keywordrush\AffiliateEgg;
defined('\ABSPATH') || exit;

/**
 * Autoupdate class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class Autoupdate {

    private $current_version;
    private $api_base;

    /**
     * Plugin filename relative to the plugins directory.
     * @var type String
     */
    private $plugin_file;
    private $slug;

    public function __construct($current_version, $plugin_file, $api_base, $slug = null)
    {
        if (!LicConfig::getInstance()->option('license_key'))
            return;

        $this->current_version = $current_version;
        $this->plugin_file = $plugin_file;
        $this->api_base = $api_base;

        if (!$slug)
            $this->slug = $slug;
        else
            $this->slug = basename($this->plugin_file, '.php');
        \add_filter('pre_set_site_transient_update_plugins', array($this, 'checkUpdate'));
        \add_filter('plugins_api', array($this, 'getRemoteInfo'), 10, 3);
    }

    public function checkUpdate($transient)
    {
        $remote_version = $this->getRemoteVersion();
        if ($remote_version && version_compare($this->current_version, $remote_version, '<'))
        {
            $plugin_data = get_plugin_data(PLUGIN_FILE);
            $res = new \stdClass();
            $res->slug = $this->slug;
            $res->new_version = $remote_version;
            $res->url = $plugin_data['PluginURI'];
            $res->package = $this->api_base . '?' . http_build_query($this->getRequestArray('download'));
            $transient->response[$this->plugin_file] = $res;
        }
        return $transient;
    }

    /**
     * Add our self-hosted description
     */
    public function getRemoteInfo($false, $action, $arg)
    {
        if (!is_object($arg) || !isset($arg->slug) || $arg->slug != $this->slug)
            return false;

        if (!$response = $this->getRemote('info'))
            return false;
        $result = json_decode($response['body'], true);
        if ($result && isset($result['info']))
        {
            $plugin_data = get_plugin_data(PLUGIN_FILE);
            $res = (object) $result['info'];
            $res->slug = $this->slug;
            $res->name = $plugin_data['Name'];
            $res->plugin_name = $plugin_data['Name'];
            $res->download_link = AffiliateEgg::getApiBase() . '?' . http_build_query($this->getRequestArray('download'));
            $res->sections = array(
                'description' => sprintf(__('New  '
                        . ' of %s plugin.', 'affegg'), $plugin_data['Name']) . ' ' .
                
                
                 sprintf(__('Please <a target="_blank" href="%s">find here</a> the releases notes.', 'affegg'), 'https://www.keywordrush.com/changelog/affiliate-egg/readme.txt')
            );

            return $res;
        } else
            return false;
    }

    public function getRemoteVersion()
    {
        if (!$response = $this->getRemote('version'))
            return false;
        $result = json_decode($response['body'], true);
        if ($result && !empty($result['version']))
            return $result['version'];
        else
            return false;
    }

    private function getRemote($cmd)
    {
        $response = wp_remote_post(AffiliateEgg::getApiBase(), array(
            'method' => 'POST', 'timeout' => 15, 'httpversion' => '1.0', 'blocking' => true, 'headers' => array(), 'body' => $this->getRequestArray($cmd), 'cookies' => array())
        );
        if (is_wp_error($response))
            return false;
        $response_code = (int) wp_remote_retrieve_response_code($response);

        if ($response_code != 200)
            return false;
        return $response;
    }

    private function getRequestArray($cmd)
    {
        return array('cmd' => $cmd, 'd' => parse_url(site_url(), PHP_URL_HOST), 'p' => AffiliateEgg::product_id, 'v' => AffiliateEgg::version(), 'key' => LicConfig::getInstance()->option('license_key'));
    }

}
