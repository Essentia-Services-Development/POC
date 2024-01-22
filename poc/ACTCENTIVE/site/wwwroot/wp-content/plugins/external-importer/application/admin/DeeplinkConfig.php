<?php

namespace ExternalImporter\application\admin;

defined('\ABSPATH') || exit;

use ExternalImporter\application\Plugin;
use ExternalImporter\application\components\Config;
use ExternalImporter\application\helpers\TextHelper;

/**
 * DeeplinkConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class DeeplinkConfig extends Config {

    public static $deeplinks = array();

    public function page_slug()
    {
        return Plugin::getSlug() . '-settings-deeplink';
    }

    public function option_name()
    {
        return Plugin::getSlug() . '-settings-deeplink';
    }

    public function header_name()
    {
        return __('Deeplinks', 'external-importer');
    }

    public function add_admin_menu()
    {
        \add_submenu_page('options.php', __('Deeplink settings', 'external-importer') . ' &lsaquo; ' . Plugin::getName(), __('Deeplink settings', 'external-importer'), 'manage_options', $this->page_slug(), array($this, 'settings_page'));
    }

    protected function options()
    {
        return array(
            'deeplinks' => array(
                'title' => __('Deeplinks', 'external-importer'),
                'callback' => array($this, 'render_deeplink_fields_block'),
                'validator' => array(
                    array(
                        'call' => array($this, 'formatDeeplinkFields'),
                        'type' => 'filter',
                    ),
                ),
            ),
        );
    }

    public function settings_page()
    {
        PluginAdmin::getInstance()->render('settings', array('page_slug' => $this->page_slug()));
    }

    public function render_deeplink_fields_line($args)
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
        . \esc_attr($value) . '" class="regular-text ltr" placeholder="' . \esc_attr(__('Deeplink or affiliate parameter', 'external-importer')) . '"  type="text"/>';
    }

    public function render_deeplink_fields_block($args)
    {
        if (is_array($args['value']))
            $total = count($args['value']) + 5;
        else
            $total = 5;

        for ($i = 0; $i < $total; $i++)
        {
            echo '<div style="padding-bottom: 5px;">';
            $args['_field'] = $i;
            $this->render_deeplink_fields_line($args);
            echo '</div>';
        }
        if ($args['description'])
            echo '<p class="description">' . $args['description'] . '</p>';
    }

    public function formatDeeplinkFields($values)
    {
        $results = array();
        foreach ($values as $k => $value)
        {
            if ($host = TextHelper::getHostName($values[$k]['name']))
                $name = $host;
            else
                $name = preg_replace('/^www\./', '', strtolower(trim(\sanitize_text_field($value['name']))));

            if (!$name || in_array($name, array_column($results, 'name')) || !TextHelper::isValidDomainName($name))
                continue;

            $value = trim(\wp_strip_all_tags($value['value']));

            $result = array('name' => $name, 'value' => $value);
            $results[] = $result;
        }

        return $results;
    }

    public function isDeeplinkExists($domain)
    {
        if (!$deeplinks = $this->option('deeplinks'))
            return false;

        foreach ($deeplinks as $deeplink)
        {
            if ($deeplink['name'] == $domain)
                return true;
        }
        return false;
    }

    public function getDeeplink($domain)
    {
        if (isset(self::$deeplinks[$domain]))
            return self::$deeplinks[$domain];

        foreach ($this->option('deeplinks') as $deeplink)
        {
            if ($deeplink['name'] == $domain)
            {
                self::$deeplinks[$domain] = $deeplink['value'];
                return self::$deeplinks[$domain];
            }
        }

        self::$deeplinks[$domain] = '';
        return self::$deeplinks[$domain];
    }

    public function getDeeplinkByUrl($url)
    {
        return $this->getDeeplink(TextHelper::getHostName($url));
    }

    public function addDeeplinkDomain($domain)
    {
        if (!$deeplinks = $this->option('deeplinks'))
            $deeplinks = array();

        $deeplink = array(
            'name' => $domain,
            'value' => '',
        );
        array_push($deeplinks, $deeplink);

        if (!$this->option_values)
            $this->option_values = array();

        $this->option_values['deeplinks'] = $deeplinks;
        \update_option('external-importer-settings-deeplink', $this->option_values);
    }

    public function maybeAddDeeplinkDomainByUrl($url)
    {
        $domain = TextHelper::getHostName($url);
        if ($this->isDeeplinkExists($domain))
            return;

        $this->addDeeplinkDomain($domain);
    }

}
