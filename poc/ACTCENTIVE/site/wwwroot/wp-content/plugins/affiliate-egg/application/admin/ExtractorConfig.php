<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * ExtractorConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class ExtractorConfig extends Config {

    public function page_slug()
    {
        return 'affiliate-egg-extractor-settings';
    }

    public function option_name()
    {
        return 'affegg_extractor';
    }

    public function add_admin_menu()
    {
        \add_submenu_page('options.php', __('Extractor settings', 'affegg') . ' &lsaquo; Affiliate Egg', __('Proxy settings', 'affegg'), 'manage_options', $this->page_slug, array($this, 'settings_page'));
    }

    protected function options()
    {
        $options = array(
            'scraperapi_token' => array(
                'title' => __('Scraperapi API key', 'external-importer'),
                'description' => __('Your scraperapi.com token.', 'external-importer'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
            ),
            'scraperapi_domains' => array(
                'title' => __('Scraperapi whitelist domains', 'external-importer'),
                'description' => sprintf(__('Сomma-separated list of domains should send requests through the %s service.', 'external-importer'), 'scraperapi'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    array(
                        'call' => array($this, 'proxyDomainsFilter'),
                        'type' => 'filter',
                    ),
                ),
            ),
            'proxycrawl_token' => array(
                'title' => __('Proxycrawl token', 'external-importer'),
                'description' => __('Your proxycrawl.com token. Use your Normal token or JavaScript token.', 'external-importer'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
            ),
            'proxycrawl_domains' => array(
                'title' => __('Proxycrawl whitelist domains', 'external-importer'),
                'description' => sprintf(__('Сomma-separated list of domains should send requests through the %s service.', 'external-importer'), 'proxycrawl'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    array(
                        'call' => array($this, 'proxyDomainsFilter'),
                        'type' => 'filter',
                    ),
                ),
            ),
            'scrapingdog_token' => array(
                'title' => __('Scrapingdog API key', 'external-importer'),
                'description' => __('Your scrapingdog.com token.', 'external-importer'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
            ),
            'scrapingdog_domains' => array(
                'title' => __('Scrapingdog whitelist domains', 'external-importer'),
                'description' => sprintf(__('Сomma-separated list of domains should send requests through the %s service.', 'external-importer'), 'scrapingdog'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    array(
                        'call' => array($this, 'proxyDomainsFilter'),
                        'type' => 'filter',
                    ),
                ),
            )
        );

        return $options;
    }

    public function settings_page()
    {
        AffiliateEggAdmin::getInstance()->render('settings', array('page_slug' => $this->page_slug()));
    }

    public function proxyDomainsFilter($value)
    {
        $domains = TextHelper::commaListArray($value);
        $results = array();
        foreach ($domains as $domain)
        {
            if ($h = TextHelper::getHostName($domain))
                $host = $h;
            else
                $host = preg_replace('/^www\./', '', strtolower(trim(\sanitize_text_field($domain))));

            if ($host && TextHelper::isValidDomainName($host))
                $results[] = $host;
        }
        $results = array_unique($results);
        return join(',', $results);
    }

}
