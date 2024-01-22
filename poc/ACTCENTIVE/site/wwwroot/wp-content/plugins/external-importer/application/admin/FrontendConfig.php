<?php

namespace ExternalImporter\application\admin;

defined('\ABSPATH') || exit;

use ExternalImporter\application\Plugin;
use ExternalImporter\application\components\Config;

/**
 * FrontendConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class FrontendConfig extends Config {

    public function page_slug()
    {
        return Plugin::getSlug() . '-settings-frontend';
    }

    public function option_name()
    {
        return Plugin::getSlug() . '-settings-frontend';
    }

    public function header_name()
    {
        return __('Frontend', 'external-importer');
    }

    public function add_admin_menu()
    {
        \add_submenu_page('options.php', __('Frontend Settings', 'external-importer') . ' &lsaquo; ' . Plugin::getName(), __('Frontend Settings', 'external-importer'), 'manage_options', $this->page_slug(), array($this, 'settings_page'));
    }

    private static function frontendTexts()
    {
        return array(
            'Last updated on %s' => __('Last updated on %s', 'external-importer'),
            'Details' => __('Details', 'external-importer'),
            'Disclosure' => __('Disclosure', 'external-importer'),
        );
    }

    protected function options()
    {
        return array(
            'show_update_date' => array(
                'title' => __('Update date', 'external-importer'),
                'label' => __('Show update date', 'external-importer'),
                'callback' => array($this, 'render_checkbox'),
                'default' => false,
            ),
            'show_disclaimer' => array(
                'title' => __('Show disclaimer', 'external-importer'),
                'label' => __('Show disclaimer', 'external-importer'),
                'callback' => array($this, 'render_checkbox'),
                'default' => false,
            ),
            'disclaimer_text' => array(
                'title' => __('Disclaimer text', 'external-importer'),
                'callback' => array($this, 'render_textarea'),
                'default' => __('As an %PRODUCT.domain% associate I earn from qualifying purchases. Product prices and availability are accurate as of the date/time indicated and are subject to change. Any price and availability information displayed on %PRODUCT.domain% at the time of purchase will apply to the purchase of this product.', 'external-importer'),
                'validator' => array(
                    'strip_tags',
                ),
            ),
            'buy_button_text' => array(
                'title' => __('Buy button text', 'external-importer'),
                'description' => __('Overwrite the button text for external products.', 'external-importer') . ' ' . sprintf(__('You can use tags: %s.', 'external-importer'), '%PRODUCT.domain%, %PRODUCT.price%, %PRODUCT.currencyCode%'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'strip_tags',
                ),
            ),
            'local_redirect' => array(
                'title' => __('Local redirect', 'external-importer'),
                'label' => __('Enable local redirect for affiliate links', 'external-importer'),
                'callback' => array($this, 'render_checkbox'),
                'default' => false,
            ),
            'redirect_status' => array(
                'title' => __('Redirect status', 'external-importer'),
                'description' => __('HTTP status code to use.', 'external-importer'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    '300.' => '300 Multiple Choices',
                    '301.' => '301 Moved Permanently',
                    '302.' => '302 Found',
                    '303.' => '303 See Other',
                    '304.' => '304 Not Modified',
                    '305.' => '305 Use Proxy',
                    '306.' => '306 Reserved',
                    '307.' => '307 Temporary Redirect',
                ),
                'default' => '301.',
            ),
            'redirect_prefix' => array(
                'title' => __('Redirect prefix', 'external-importer'),
                'description' => __('Custom prefix for redirected links.', 'external-importer'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                    'allow_empty',
                    array(
                        'call' => array('\ExternalImporter\application\helpers\FormValidator', 'alpha_numeric'),
                        'message' => sprintf(__('The field "%s" can contain only latin letters and digits.', 'external-importer'), __('Redirect prefix', 'external-importer')),
                    ),
                ),
            ),
            'frontend_texts' => array(
                'title' => __('Frontend texts', 'external-importer'),
                'description' => '',
                'callback' => array($this, 'render_translation_block'),
                'section' => __('Translation', 'external-importer'),
                'validator' => array(
                    array(
                        'call' => array($this, 'frontendTextsSanitize'),
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

    public function render_translation_row($args)
    {
        $i = isset($args['_field']) ? $args['_field'] : 0;
        $name = isset($args['value'][$i]['name']) ? $args['value'][$i]['name'] : '';
        $value = isset($args['value'][$i]['value']) ? $args['value'][$i]['value'] : '';

        echo '<input name="' . \esc_attr($args['option_name']) . '['
        . \esc_attr($args['name']) . '][' . $i . '][name]" value="'
        . \esc_attr($name) . '" class="regular-text ltr" type="text" readonly />';

        echo ' &#x203A; ';

        echo '<input name="' . \esc_attr($args['option_name']) . '['
        . \esc_attr($args['name']) . '][' . $i . '][value]" value="'
        . \esc_attr($value) . '" class="regular-text ltr" placeholder="' . \esc_attr(__('Translated string', 'external-importer')) . '"  type="text"/>';
    }

    public function render_translation_block($args)
    {
        if (!$args['value'])
            $args['value'] = array();

        foreach (array_keys(self::frontendTexts()) as $i => $str)
        {
            echo '<div style="padding-bottom: 5px;">';
            $args['_field'] = $i;
            $args['value'][$i]['name'] = $str;
            $this->render_translation_row($args);
            echo '</div>';
        }
        if ($args['description'])
            echo '<p class="description">' . $args['description'] . '</p>';
    }

    public function frontendTextsSanitize($values)
    {
        foreach ($values as $k => $value)
        {
            $values[$k]['value'] = trim(\sanitize_text_field($value['value']));
        }

        return $values;
    }

}
