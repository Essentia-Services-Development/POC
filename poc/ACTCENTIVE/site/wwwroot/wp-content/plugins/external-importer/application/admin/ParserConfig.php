<?php

namespace ExternalImporter\application\admin;

defined('\ABSPATH') || exit;

use ExternalImporter\application\Plugin;
use ExternalImporter\application\components\Config;
use ExternalImporter\application\components\Throttler;
use ExternalImporter\application\helpers\TextHelper;

/**
 * ParserConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class ParserConfig extends Config
{

    public static $cookies = array();

    public function page_slug()
    {
        return Plugin::getSlug() . '-settings-parser';
    }

    public function option_name()
    {
        return Plugin::getSlug() . '-settings-parser';
    }

    public function header_name()
    {
        return __('Extractor', 'external-importer');
    }

    public function add_admin_menu()
    {
        \add_submenu_page('options.php', __('Extractor settings', 'external-importer') . ' &lsaquo; ' . Plugin::getName(), __('Extractor settings', 'external-importer'), 'manage_options', $this->page_slug(), array($this, 'settings_page'));
    }

    protected function options()
    {
        $options = array(
            'respect_robots' => array(
                'title' => __('Respect robots.txt', 'external-importer'),
                'label' => __('Read and respect robots.txt rules', 'external-importer'),
                'callback' => array($this, 'render_checkbox'),
                'default' => false,
            ),
            'use_sessions' => array(
                'title' => __('Sessions', 'external-importer'),
                'label' => __('Keep session alive', 'external-importer'),
                'description' => __('Save cookies between requests.', 'external-importer') . '<br />' .
                    sprintf('<a href="%s">', \get_admin_url(\get_current_blog_id(), 'admin.php?page=external-importer-tools&action=session_destroy')) . __('Clear session variables', 'external-importer') . ' </a>',
                'callback' => array($this, 'render_checkbox'),
                'default' => true,
            ),
            'daily_limit' => array(
                'title' => __('Daily limit', 'external-importer'),
                'description' => __('The maximum number of requests for each store. 0 - unlimited.', 'external-importer') . '<br />' .
                    __('If the limit is reached, then all automatic requests to the store will be throttled until the end of the day.', 'external-importer'),
                'callback' => array($this, 'render_input'),
                'class' => 'small-text',
                'type' => 'number',
                'validator' => array(
                    'trim',
                ),
                'default' => 500,
            ),
            'throttle_1' => array(
                'title' => __('Throttle for 1 hour', 'external-importer'),
                'label' => sprintf(__('Throttle all automatic requests to the store for 1 hour if %d errors occur', 'external-importer'), Throttler::ERRORS_COUNT_1HOUR),
                'description' => '',
                'callback' => array($this, 'render_checkbox'),
                'default' => true,
            ),
            'throttle_24' => array(
                'title' => __('Throttle for 24 hours', 'external-importer'),
                'label' => sprintf(__('Throttle all automatic requests to the store for 24 hours if %d errors occur', 'external-importer'), Throttler::ERRORS_COUNT_24HOURS),
                'description' => '',
                'callback' => array($this, 'render_checkbox'),
                'default' => true,
            ),
            'ae_integration' => array(
                'title' => __('Affiliate Egg integration', 'external-importer'),
                'description' => sprintf(__('Use <a target="_blank" href="%s">Affiliate Egg</a> parsers if possible.', 'external-importer'), 'https://www.keywordrush.com/affiliateegg'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'enabled' => __('Enabled', 'external-importer'),
                    'disabled' => __('Disabled', 'external-importer'),
                ),
                'default' => 'enabled',
            ),
            'proxy_list' => array(
                'title' => __('Proxy list', 'external-importer'),
                'description' => sprintf(__('Сomma-separated list of proxies in the form of %s, eg: %s', 'external-importer'), 'user:password@proxyserver:proxyport', 'socks4://11.22.33.44:1080,http://10.20.30.40:8080'),
                'callback' => array($this, 'render_textarea'),
                'default' => '',
                'validator' => array(
                    array(
                        'call' => array($this, 'proxyListFilter'),
                        'type' => 'filter',
                    ),
                ),
            ),
            'proxy_domains' => array(
                'title' => __('Proxy whitelist domains', 'external-importer'),
                'description' => sprintf(__('Сomma-separated list of domains for which to use proxies, eg: %s', 'external-importer'), 'amazon.com,aliexpress.com'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    array(
                        'call' => array($this, 'proxyDomainsFilter'),
                        'type' => 'filter',
                    ),
                ),
            ),
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
                'title' => __('Crawlbase (formerly Proxycrawl) token', 'external-importer'),
                'description' => __('Your crawlbase.com token. Use your Normal token or JavaScript token.', 'external-importer'),
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
            ),
            'cookies' => array(
                'title' => __('Custom cookies', 'external-importer'),
                'callback' => array($this, 'render_cookies_fields_block'),
                'validator' => array(
                    array(
                        'call' => array($this, 'formatCookiesFields'),
                        'type' => 'filter',
                    ),
                ),
            ),
        );

        if ($this->isAdminPage())
        {
            $t1 = Throttler::getThrottledByDailyLimit();
            $t2 = Throttler::getThrottledByErrors1();
            $t3 = Throttler::getThrottledByErrors24();

            $options['daily_limit']['description'] .= '<br /><em>' . sprintf(__('Currently throttled domains: %s.', 'external-importer'), TextHelper::formatMoreList($t1, 10, '0')) . '</em>';
            $options['throttle_1']['description'] .= '<em>' . sprintf(__('Currently throttled domains: %s.', 'external-importer'), TextHelper::formatMoreList($t2, 10, '0')) . '</em>';
            $options['throttle_24']['description'] .= '<em>' . sprintf(__('Currently throttled domains: %s.', 'external-importer'), TextHelper::formatMoreList($t3, 10, '0')) . '</em>';
        }


        return $options;
    }

    public function render_cookies_fields_line($args)
    {
        $i = isset($args['_field']) ? $args['_field'] : 0;
        $name = isset($args['value'][$i]['name']) ? $args['value'][$i]['name'] : '';
        $value = isset($args['value'][$i]['value']) ? $args['value'][$i]['value'] : '';

        echo '<input name="' . \esc_attr($args['option_name']) . '['
            . \esc_attr($args['name']) . '][' . $i . '][name]" value="'
            . \esc_attr($name) . '" class="text" placeholder="' . \esc_attr(__('Domain name', 'external-importer')) . '"  type="text"/>';
        echo ' &#x203A; ';
        echo '<input name="' . \esc_attr($args['option_name']) . '['
            . \esc_attr($args['name']) . '][' . $i . '][value]" value="'
            . \esc_attr($value) . '" class="regular-text ltr" placeholder="' . \esc_attr(__('Cookies', 'external-importer')) . '"  type="text"/>';
    }

    public function render_cookies_fields_block($args)
    {
        if (is_array($args['value']))
            $total = count($args['value']) + 3;
        else
            $total = 3;

        for ($i = 0; $i < $total; $i++)
        {
            echo '<div style="padding-bottom: 5px;">';
            $args['_field'] = $i;
            $this->render_cookies_fields_line($args);
            echo '</div>';
        }
        if ($args['description'])
            echo '<p class="description">' . $args['description'] . '</p>';
    }

    public function settings_page()
    {
        PluginAdmin::getInstance()->render('settings', array('page_slug' => $this->page_slug()));
    }

    public function getExtractorConfig()
    {
        return array(
            'respect_robots' => $this->option('respect_robots'),
            'use_sessions' => $this->option('use_sessions'),
        );
    }

    public function proxyListFilter($value)
    {
        return TextHelper::commaList($value);
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

    public function formatCookiesFields($values)
    {
        $results = array();
        foreach ($values as $k => $value)
        {
            if ($host = TextHelper::getHostName($values[$k]['name']))
                $name = $host;
            else
                $name = preg_replace('/^www\./', '', strtolower(trim(\sanitize_text_field($value['name']))));

            $value = trim($value['value']);
            if (!$value || !$name || in_array($name, array_column($results, 'name')) || !TextHelper::isValidDomainName($name))
                continue;

            $result = array('name' => $name, 'value' => $value);
            $results[] = $result;
        }

        return $results;
    }

    public function getCookie($domain)
    {
        if (isset(self::$cookies[$domain]))
            return self::$cookies[$domain];

        $cookies = $this->option('cookies');

        if ($cookies)
        {
            foreach ($this->option('cookies') as $cookie)
            {
                if ($cookie['name'] == $domain)
                {
                    self::$cookies[$domain] = $cookie['value'];
                    return self::$cookies[$domain];
                }
            }
        }

        self::$cookies[$domain] = '';
        return self::$cookies[$domain];
    }

    public function getCookieByUrl($url)
    {
        return $this->getCookie(TextHelper::getHostName($url));
    }
}
