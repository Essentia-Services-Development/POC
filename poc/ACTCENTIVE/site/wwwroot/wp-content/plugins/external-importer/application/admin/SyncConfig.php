<?php

namespace ExternalImporter\application\admin;

defined('\ABSPATH') || exit;

use ExternalImporter\application\Plugin;
use ExternalImporter\application\components\Config;
use ExternalImporter\application\SyncScheduler;

/**
 * SyncConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class SyncConfig extends Config {

    public function page_slug()
    {
        return Plugin::getSlug() . '-settings-sync';
    }

    public function option_name()
    {
        return Plugin::getSlug() . '-settings-sync';
    }

    public function header_name()
    {
        return __('Synchronization', 'external-importer');
    }

    public function add_admin_menu()
    {
        \add_submenu_page('options.php', __('Synchronization Settings', 'external-importer') . ' &lsaquo; ' . Plugin::getName(), __('Synchronization Settings', 'external-importer'), 'manage_options', $this->page_slug(), array($this, 'settings_page'));
    }

    protected function options()
    {
        $options = array(
            'update_mode' => array(
                'title' => __('Update mode', 'external-importer'),
                'description' => __('Frontend synchronization will activate the product update when any visitor navigates on a product details page.', 'external-importer') .
                ' ' . __('Cron synchronization will run on schedule in the background.', 'external-importer'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'frontend' => __('Frontend synchronization', 'external-importer'),
                    'cron' => __('Cron synchronization', 'external-importer'),
                ),
                'default' => 'frontend',
            ),
            'cache_duration' => array(
                'title' => __('Update period', 'external-importer'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    '0.' => __('Disabled', 'external-importer'),
                    '1.' => __('Once a day', 'external-importer'),
                    '2.' => sprintf(__('Every %d days', 'external-importer'), '2'),
                    '3.' => sprintf(__('Every %d days', 'external-importer'), '3'),
                    '4.' => sprintf(__('Every %d days', 'external-importer'), '4'),
                    '5.' => sprintf(__('Every %d days', 'external-importer'), '5'),
                    '6.' => sprintf(__('Every %d days', 'external-importer'), '6'),
                    '7.' => sprintf(__('Every %d days', 'external-importer'), '7'),
                    '8.' => sprintf(__('Every %d days', 'external-importer'), '8'),
                    '9.' => sprintf(__('Every %d days', 'external-importer'), '9'),
                    '10.' => sprintf(__('Every %d days', 'external-importer'), '10'),
                    '15.' => sprintf(__('Every %d days', 'external-importer'), '15'),
                    '30.' => sprintf(__('Every %d days', 'external-importer'), '30'),
                    '90.' => sprintf(__('Every %d days', 'external-importer'), '90'),
                ),
                'default' => '5.',
                'validator' => array(
                    array(
                        'call' => array($this, 'validateSyncSettings'),
                        'type' => 'filter',
                    ),
                ),
            ),
            'sync_price' => array(
                'title' => __('Price', 'external-importer'),
                'description' => __('Update price', 'external-importer'),
                'callback' => array($this, 'render_checkbox'),
                'default' => true,
            ),
            'sync_old_price' => array(
                'title' => __('Regular price', 'external-importer'),
                'description' => __('Update regular price', 'external-importer'),
                'callback' => array($this, 'render_checkbox'),
                'default' => true,
                'validator' => array(
                    array(
                        'call' => array($this, 'validateOldPrice'),
                        'type' => 'filter',
                    ),
                ),
            ),
            'sync_stock_status' => array(
                'title' => __('Stock status', 'external-importer'),
                'label' => __('Update stock status', 'external-importer'),
                'callback' => array($this, 'render_checkbox'),
                'default' => true,
            ),
            'outofstock_product' => array(
                'title' => __('Out of Stock products', 'external-importer'),
                'description' => __('How to deal with Out of Stock products', 'external-importer'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    '' => __('Do nothing', 'external-importer'),
                    'hide_price' => __('Hide product price', 'external-importer'),
                    'hide_product' => __('Set Catalog Visibility to Hidden', 'external-importer'),
                    'move_to_trash' => __('Move product to trash', 'external-importer'),
                    'move_to_trash_7' => sprintf(__('Move product to trash if unavailable for %d days', 'external-importer'), 7),
                    'move_to_trash_30' => sprintf(__('Move product to trash if unavailable for %d days', 'external-importer'), 30),
                ),
                'default' => '',
            ),
            'delete_attachments' => array(
                'title' => __('Delete attached media', 'external-importer'),
                'label' => __('Delete products with attachments', 'external-importer'),
                'description' => __('When deleting a product, also delete the product gallery and featured image. Make sure you do not use attachments in other posts.', 'external-importer'),
                'callback' => array($this, 'render_checkbox'),
                'default' => false,
            ),
            'published_only' => array(
                'title' => __('Published products', 'external-importer'),
                'label' => __('Only update published products', 'external-importer'),
                'callback' => array($this, 'render_checkbox'),
                'default' => true,
            ),
        );

        if (\apply_filters('exi_enable_custom_cache_duration', false))
        {
            $options['cache_duration']['dropdown_options']['0.5'] = 'Twice a day [custom]';
            $options['cache_duration']['dropdown_options']['0.33'] = 'Three times a day [custom]';
            $options['cache_duration']['dropdown_options']['0.25'] = 'Four times a day  [custom]';            
        }        
        
        if (\ExternalImporter\application\Plugin::isDevEnvironment())
            $options['cache_duration']['dropdown_options']['0.0000578703703'] = '5 sec [debug]';

        return $options;
    }

    public function validateOldPrice($value)
    {
        if ($value && !$this->get_submitted_value('sync_price'))
            return false;
        else
            return $value;
    }

    public function validateSyncSettings($value)
    {
        if (!$this->isSyncFields())
            $value = '0.';

        if (!(float) $value || $this->get_submitted_value('update_mode') == 'frontend')
            SyncScheduler::clearScheduleEvent();
        elseif ($this->get_submitted_value('update_mode') == 'cron')
            SyncScheduler::addScheduleEvent('ten_min');

        return $value;
    }

    public function isSyncFields()
    {
        $sync_fields = array('sync_price', 'sync_old_price', 'sync_stock_status');
        foreach ($sync_fields as $field)
        {
            if ($this->get_submitted_value($field))
                return true;
        }
        return false;
    }

    public function settings_page()
    {
        PluginAdmin::getInstance()->render('settings', array('page_slug' => $this->page_slug()));
    }

}
