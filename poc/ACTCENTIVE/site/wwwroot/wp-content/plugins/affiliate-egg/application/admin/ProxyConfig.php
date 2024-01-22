<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * ProxyConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class ProxyConfig extends Config {

    public function page_slug()
    {
        return 'affiliate-egg-proxy-settings';
    }

    public function option_name()
    {
        return 'affegg_proxy';
    }

    public function add_admin_menu()
    {
        \add_submenu_page('options.php', __('Proxy settings', 'affegg') . ' &lsaquo; Affiliate Egg', __('Proxy settings', 'affegg'), 'manage_options', $this->page_slug, array($this, 'settings_page'));
    }

    protected function options()
    {
        $options = array(
            
        
            
            'proxies' => array(
                'title' => __('Proxy List', 'affegg'),
                'description' => sprintf(__('Сomma-separated list of proxies  in the form of %s, eg: %s.', 'affegg'), 'user:password@proxyserver:proxyport', 'socks4://11.22.33.44:1080,http://10.20.30.40:8080'),
                'callback' => array($this, 'render_textarea'),
                'default' => '',
            ),
            'gproxy_api_key' => array(
                'title' => __('API Key', 'affegg') . ' (deprecated)',
                'description' => __('<a target="_blank" href="https://gimmeproxy.com/">Gimmeproxy</a> API key.', 'affegg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'section' => __('Gimmeproxy', 'affegg'),
            ),
            'gproxy_parameters' => array(
                'title' => __('Additional parameters', 'affegg') . ' (deprecated)',
                'description' => __('You can add any additional parameters to fine-tune your requests in format: <i>maxCheckPeriod=300&websites=amazon&country=us&supportsHttps=true</i> (<a target="_blank" href="https://gimmeproxy.com/#api">read more</a>).', 'affegg'),
                'callback' => array($this, 'render_input'),
                'default' => 'maxCheckPeriod=900&supportsHttps=true',
                'section' => __('Gimmeproxy', 'affegg'),
            ),
            'proxy_ttl' => array(
                'title' => __('Proxy cache TTL', 'affegg'),
                'description' => __('Use this parameter to set an maximum proxy time to live, in seconds. Once reaching this limit, a proxy will not be used again.', 'affegg'),
                'callback' => array($this, 'render_input'),
                'default' => 24 * 3600,
                'section' => __('Gimmeproxy', 'affegg'),
            ),
        );

        $shops = ShopManager::getInstance()->getActiveItems(true, true, true);
        foreach ($shops as $shop)
        {
            $options[$shop->id] = array(
                'title' => $shop->getName(true, true),
                'description' => __('Use proxy', 'affegg'),
                'callback' => array($this, 'render_checkbox'),
                'default' => '',
                'validator' => array(
                    'trim',
                    'allow_empty',
                ),
                'section' => __('Hosts list (the hosts in this list will going through a proxy server)', 'affegg'),
            );
        }

        return $options;
    }

    public function settings_page()
    {
        AffiliateEggAdmin::getInstance()->render('settings', array('page_slug' => $this->page_slug()));
    }

}
