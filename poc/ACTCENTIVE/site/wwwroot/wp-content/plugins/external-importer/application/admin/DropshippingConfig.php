<?php

namespace ExternalImporter\application\admin;

defined('\ABSPATH') || exit;

use ExternalImporter\application\Plugin;
use ExternalImporter\application\components\Config;
use ExternalImporter\application\helpers\TextHelper;

/**
 * DropshippingConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */

class DropshippingConfig extends Config {

    public static $deeplinks = array();

    public function page_slug()
    {
        return Plugin::getSlug() . '-settings-dropshipping';
    }

    public function option_name()
    {
        return Plugin::getSlug() . '-settings-dropshipping';
    }

    public function header_name()
    {
        return __('Dropshipping', 'external-importer');
    }

    public function add_admin_menu()
    {
        \add_submenu_page('options.php', __('Dropshipping settings', 'external-importer') . ' &lsaquo; ' . Plugin::getName(), __('Dropshipping settings', 'external-importer'), 'manage_options', $this->page_slug(), array($this, 'settings_page'));
    }

    protected function options()
    {
        return array(
            'price_rules' => array(
                'title' => __('Pricing rules', 'external-importer'),
                'callback' => array($this, 'render_price_rules_block'),
                'validator' => array(
                    array(
                        'call' => array($this, 'formatPriceRules'),
                        'type' => 'filter',
                    ),
                ),
            ),
            'round' => array(
                'title' => __('Rounded pricing', 'external-importer'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'precise' => __('Precise price', 'external-importer'),
                    'round' => __('Round', 'external-importer'),
                    'round_up' => __('Round up', 'external-importer'),
                    'round_down' => __('Round down', 'external-importer'),
                    'round_up_50' => __('Round up to nearest 50', 'external-importer'),
                    'round_up_100' => __('Round up to nearest 100', 'external-importer'),
                ),
                'default' => 'round',
            ),
            'round_precision' => array(
                'title' => __('Round precision', 'external-importer'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    '0.' => __('Without pennies', 'external-importer'),
                    '1.' => __('Round to 10 pennies', 'external-importer'),
                    '2.' => __('With pennies', 'external-importer'),
                ),
                'default' => '1.',
            ),
            'old_price' => array(
                'title' => __('Old price', 'external-importer'),
                'callback' => array($this, 'render_dropdown'),
                'label' => __('Apply the rules to old prices also', 'external-importer'),
                'description' => __('If not selected old prices will not be imported.', 'external-importer'),
                'callback' => array($this, 'render_checkbox'),
                'default' => true,
            ),
            'product_type' => array(
                'title' => __('Product type', 'external-importer'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'simple' => __('Apply to simple products', 'external-importer'),
                    'external' => __('Apply to external products', 'external-importer'),
                    'any' => __('Apply to any type of product', 'external-importer'),
                ),
                'default' => 'simple',
            ),
        );
    }

    public function settings_page()
    {
        PluginAdmin::getInstance()->render('settings', array('page_slug' => $this->page_slug()));
    }

    public function render_price_rules_row($args)
    {
        $i = isset($args['_field']) ? $args['_field'] : 0;

        $domain = isset($args['value'][$i]['domain']) ? $args['value'][$i]['domain'] : '';
        $price_from = isset($args['value'][$i]['price_from']) ? $args['value'][$i]['price_from'] : '';
        $price_to = isset($args['value'][$i]['price_to']) ? $args['value'][$i]['price_to'] : '';
        $margin = isset($args['value'][$i]['margin']) ? $args['value'][$i]['margin'] : '';
        $margin_type = isset($args['value'][$i]['margin_type']) ? $args['value'][$i]['margin_type'] : 'percent';

        if ($margin)
            echo '&darr;';
        else
            echo '&nbsp;&nbsp;&nbsp;';
        echo ' <input name="' . \esc_attr($args['option_name']) . '['
        . \esc_attr($args['name']) . '][' . $i . '][domain]" value="'
        . \esc_attr($domain) . '" class="text" placeholder="' . \esc_attr(__('Domain name (optional)', 'external-importer')) . '"  type="text"/>';
        echo ' &#x203A; ';
        echo '<input name="' . \esc_attr($args['option_name']) . '['
        . \esc_attr($args['name']) . '][' . $i . '][price_from]" value="'
        . \esc_attr($price_from) . '" class="text" placeholder="' . \esc_attr(__('Price from (optional)', 'external-importer')) . '"  type="text"/>';
        echo ' &#8594; ';
        echo '<input name="' . \esc_attr($args['option_name']) . '['
        . \esc_attr($args['name']) . '][' . $i . '][price_to]" value="'
        . \esc_attr($price_to) . '" class="text" placeholder="' . \esc_attr(__('Price to (optional)', 'external-importer')) . '"  type="text"/>';

        echo ' &#x3d; ';

        echo '<select class="" name="' . \esc_attr($args['option_name']) . '['
        . \esc_attr($args['name']) . '][' . $i . '][margin_type]">';
        echo '<option value="percent"' . ($margin_type == 'percent' ? ' selected="selected"' : '') . '>' . \esc_html(__('Percentage', 'external-importer')) . '</option>';
        echo '<option value="flat"' . ($margin_type == 'flat' ? ' selected="selected"' : '') . '>' . \esc_html(__('Flat ammount', 'external-importer')) . '</option>';
        echo '</select>';

        echo '<input name="' . \esc_attr($args['option_name']) . '['
        . \esc_attr($args['name']) . '][' . $i . '][margin]" value="'
        . \esc_attr($margin) . '" class="text" placeholder="' . \esc_attr(__('Margin value (required)', 'external-importer')) . '"  type="text"/>';
    }

    public function render_price_rules_block($args)
    {
        if (is_array($args['value']))
            $total = count($args['value']) + 3;
        else
            $total = 3;

        for ($i = 0; $i < $total; $i++)
        {
            echo '<div style="padding-bottom: 15px;">';
            $args['_field'] = $i;
            $this->render_price_rules_row($args);
            echo '</div>';
        }
        if ($args['description'])
            echo '<p class="description">' . $args['description'] . '</p>';
    }

    public function formatPriceRules($values)
    {
        $results = array();
        foreach ($values as $k => $value)
        {
            $values[$k]['margin'] = (float) $values[$k]['margin'];
            if (!$values[$k]['margin'])
            {
                unset($values[$k]);
                continue;
            }

            if ($v['margin_type'] == 'flat')
                $values[$k]['margin'] = number_format($values[$k]['margin'], 2, '.', '');

            if ($host = TextHelper::getHostName($value['domain']))
                $domain = $host;
            else
                $domain = preg_replace('/^www\./', '', strtolower(trim(\sanitize_text_field($value['domain']))));

            if ($domain && TextHelper::isValidDomainName($domain))
                $values[$k]['domain'] = $domain;
            else
                $values[$k]['domain'] = '';

            if ((float) $value['price_from'])
                $values[$k]['price_from'] = (float) $value['price_from'];

            if ((float) $value['price_to'])
                $values[$k]['price_to'] = (float) $value['price_to'];
        }
        $values = array_values($values);
        return $values;
    }

}
