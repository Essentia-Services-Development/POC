<?php

namespace ExternalImporter\application\admin;

defined('\ABSPATH') || exit;

use ExternalImporter\application\Plugin;
use ExternalImporter\application\components\Config;
use ExternalImporter\application\admin\LicConfig;

/**
 * GeneralConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class GeneralConfig extends Config {

    public function page_slug()
    {
        return Plugin::getSlug() . '-settings';
    }

    public function option_name()
    {
        return Plugin::getSlug() . '-settings';
    }

    public function header_name()
    {
        return __('General', 'external-importer');
    }

    public function add_admin_menu()
    {
        \add_submenu_page(Plugin::getSlug(), __('Settings', 'external-importer') . ' &lsaquo; ' . Plugin::getName(), __('Settings', 'external-importer'), 'manage_options', $this->page_slug(), array($this, 'settings_page'));
    }

    protected function options()
    {
        return array(
            'log_target_email' => array(
                'title' => __('Email alerts', 'external-importer'),
                'description' => __('This options allows you to specify which types of alerts you want to receive to admin email.', 'external-importer'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'error' => __('Errors only', 'external-importer'),
                    'warning_error' => __('Warnings and Errors only', 'external-importer'),
                    'all' => __('All', 'external-importer'),
                    'none' => __('None', 'external-importer'),
                ),
                'default' => 'none',
            ),
            'log_target_db' => array(
                'title' => __('Log alerts', 'external-importer'),
                'description' => __('This options allows you to specify which types of alerts you want to log to DB.', 'external-importer'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'all' => __('All', 'external-importer'),
                    'all_without_debug' => __('All without debug', 'external-importer'),
                ),
                'default' => 'all_without_debug',
            ),
            'fixer_api_key' => array(
                'title' => __('Fixer API key', 'external-importer'),
                'description' => sprintf(__('Set this if you want to use <a target="_blank" href="%s">Fixer.io</a> exchange rates.', 'external-importer'), 'https://fixer.io/'),
                'callback' => array($this, 'render_input'),
                'validator' => array(
                    'trim',
                ),
                'default' => '',
            ),
        );
    }

    public function adminInit()
    {
        $l = LicConfig::getInstance()->option('lic' . 'ense' . '_key');
        if (strlen($l) != 32 && strlen($l) != 36)
            return;

        parent::adminInit();
    }

    public function settings_page()
    {
        PluginAdmin::getInstance()->render('settings', array('page_slug' => $this->page_slug()));
    }

}
