<?php

namespace ExternalImporter\application\admin;

defined('\ABSPATH') || exit;

use ExternalImporter\application\Plugin;
use ExternalImporter\application\components\Config;
use ExternalImporter\application\helpers\WooHelper;

/**
 * WooConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class WooConfig extends Config {

    public function page_slug()
    {
        return Plugin::getSlug() . '-settings-woo';
    }

    public function option_name()
    {
        return Plugin::getSlug() . '-settings-woo';
    }

    public function header_name()
    {
        return __('Import', 'external-importer');
    }

    public function add_admin_menu()
    {
        \add_submenu_page('options.php', __('Import Settings', 'external-importer') . ' &lsaquo; ' . Plugin::getName(), __('Import Settings', 'external-importer'), 'manage_options', $this->page_slug(), array($this, 'settings_page'));
    }

    protected function options()
    {
        return array(
            'avoid_duplicates' => array(
                'title' => __('Avoid duplicates', 'external-importer'),
                'description' => __('Avoid importing duplicate products', 'external-importer'),
                'callback' => array($this, 'render_checkbox'),
                'default' => true,
                'section' => __('General settings', 'external-importer'),
            ),
            'product_type' => array(
                'title' => __('Product type', 'external-importer'),
                'description' => __("External/affiliate products cannot be added to cart. Instead, customers will click your affiliate link to visit merchant's website.", 'external-importer'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'external' => __('External/Affiliate product', 'external-importer'),
                    'simple' => __('Simple product', 'external-importer'),
                ),
                'default' => 'external',
                'section' => __('General settings', 'external-importer'),
            ),
            'product_status' => array(
                'title' => __('Product status', 'external-importer'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'publish' => __('Published', 'external-importer'),
                    'pending' => __('Pending Review', 'external-importer'),
                    'draft' => __('Draft', 'external-importer'),
                ),
                'default' => 'publish',
                'section' => __('General settings', 'external-importer'),
            ),
            'catalog_visibility' => array(
                'title' => __('Catalog visibility', 'external-importer'),
                'description' => __('This setting determines which shop pages products will be listed on.', 'external-importer'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'visible' => __('Shop and search results', 'external-importer'),
                    'catalog' => __('Shop only', 'external-importer'),
                    'search' => __('Search results only', 'external-importer'),
                    'hidden' => __('Hidden', 'external-importer'),
                ),
                'default' => 'visible',
                'section' => __('General settings', 'external-importer'),
            ),
            'default_category' => array(
                'title' => __('Default category', 'external-importer'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => WooHelper::getCategoryList(),
                'default' => \get_option('default_product_cat'),
                'section' => __('General settings', 'external-importer'),
            ),
            'dynamic_categories' => array(
                'title' => __('Dynamic categories', 'external-importer'),
                'description' => __('Create category automatically from product data (if possible).', 'external-importer'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'disabled' => __('Disabled', 'external-importer'),
                    'create' => __('Create category', 'external-importer'),
                    'nested' => __('Create nested categories', 'external-importer'),
                ),
                'default' => 'disabled',
                'section' => __('General settings', 'external-importer'),
            ),
            'import_image' => array(
                'title' => __('Image', 'external-importer'),
                'description' => __('Import product image or use external image URL.', 'external-importer'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'enabled' => __('Import image to local media library', 'external-importer'),
                    'internal_priority' => __('Use external image and local image has priority', 'external-importer'),
                    'external_priority' => __('Use external image and external image has priority', 'external-importer'),
                    'disabled' => __('Disabled', 'external-importer'),
                ),
                'default' => 'enabled',
                'section' => __('General settings', 'external-importer'),
            ),
            'import_description' => array(
                'title' => __('Description', 'external-importer'),
                'description' => __('', 'external-importer'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'short' => __('Import description field as short description', 'external-importer'),
                    'full' => __('Import description field as full description', 'external-importer'),
                    'auto' => __('Auto (depending on the size in characters)', 'external-importer'),
                    'disabled' => __('Disabled', 'external-importer'),
                ),
                'default' => 'auto',
                'section' => __('General settings', 'external-importer'),
            ),
            'truncate_description' => array(
                'title' => __('Truncate description', 'external-importer'),
                'description' => __("Max character length in description. 0 - no limit.", 'external-importer'),
                'callback' => array($this, 'render_input'),
                'class' => 'small-text',
                'type' => 'number',
                'validator' => array(
                    'trim',
                    'absint',
                ),
                'default' => 0,
                'section' => __('General settings', 'external-importer'),
            ),
            'import_price' => array(
                'title' => __('Price', 'external-importer'),
                'description' => __('Import price', 'external-importer'),
                'callback' => array($this, 'render_checkbox'),
                'default' => true,
                'section' => __('General settings', 'external-importer'),
            ),
            'import_old_price' => array(
                'title' => __('Regular price', 'external-importer'),
                'description' => __('Import regular price', 'external-importer'),
                'callback' => array($this, 'render_checkbox'),
                'default' => true,
                'section' => __('General settings', 'external-importer'),
            ),
            'import_stock_status' => array(
                'title' => __('Stock status', 'external-importer'),
                'label' => __('Import stock status', 'external-importer'),
                'callback' => array($this, 'render_checkbox'),
                'default' => true,
                'section' => __('Simple products', 'external-importer'),
            ),
            'import_url' => array(
                'title' => __('Product URL', 'external-importer'),
                'label' => __('Import product URL', 'external-importer'),
                'callback' => array($this, 'render_checkbox'),
                'default' => true,
                'section' => __('External products', 'external-importer'),
            ),
            'import_attributes' => array(
                'title' => __('Attributes', 'external-importer'),
                'description' => __('Import attributes', 'external-importer'),
                'callback' => array($this, 'render_checkbox'),
                'default' => true,
                'section' => __('General settings', 'external-importer'),
            ),
            'import_gallery' => array(
                'title' => __('Gallery images', 'external-importer'),
                'description' => __('Import gallery images or use external images.', 'external-importer'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'enabled' => __('Import gallery to local media library', 'external-importer'),
                    'internal_priority' => __('Use external images and local images take priority', 'external-importer'),
                    'external_priority' => __('Use external images and external images take priority', 'external-importer'),
                    'disabled' => __('Disabled', 'external-importer'),
                ),
                'default' => 'disabled',
                'section' => __('General settings', 'external-importer'),
            ),
            'import_gallery_number' => array(
                'title' => __('Number of gallery images', 'external-importer'),
                'description' => __('Select how many gallery images you want to import. 0 - all. Note, maximum 9 gallery images will be available if you use external images.', 'external-importer'),
                'callback' => array($this, 'render_input'),
                'class' => 'small-text',
                'type' => 'number',
                'validator' => array(
                    'trim',
                    'absint',
                ),
                'default' => 0,
                'section' => __('General settings', 'external-importer'),
            ),
            'import_reviews' => array(
                'title' => __('User reviews', 'external-importer'),
                'description' => __('Import user reviews', 'external-importer'),
                'callback' => array($this, 'render_checkbox'),
                'default' => false,
                'section' => __('General settings', 'external-importer'),
            ),
            'import_reviews_rating' => array(
                'title' => __('Reviews rating', 'external-importer'),
                'description' => __('Import reviews rating', 'external-importer'),
                'callback' => array($this, 'render_checkbox'),
                'default' => true,
                'section' => __('General settings', 'external-importer'),
            ),
            'import_reviews_number' => array(
                'title' => __('Number of reviews', 'external-importer'),
                'description' => __('Select how many reviews you want to import. 0 - all.', 'external-importer'),
                'callback' => array($this, 'render_input'),
                'class' => 'small-text',
                'type' => 'number',
                'validator' => array(
                    'trim',
                    'absint',
                ),
                'default' => 0,
                'section' => __('General settings', 'external-importer'),
            ),
            'truncate_reviews' => array(
                'title' => __('Truncate reviews', 'external-importer'),
                'description' => __("Max character length in reviews. 0 - no limit.", 'external-importer'),
                'callback' => array($this, 'render_input'),
                'class' => 'small-text',
                'type' => 'number',
                'validator' => array(
                    'trim',
                    'absint',
                ),
                'default' => 0,
                'section' => __('General settings', 'external-importer'),
            ),
            'title_template' => array(
                'title' => __('Title template', 'external-importer'),
                'description' => sprintf(__('You can use product data: %s.', 'external-importer'), '%PRODUCT.title%, %PRODUCT.link%, %PRODUCT.price%, %PRODUCT.currencyCode%, %PRODUCT.manufacturer%, %PRODUCT.ATTRIBUTE.Attribute Name%,  etc') . '<br>' .
                sprintf(__('You can also use formulas like %s.', 'external-importer'), '{Discount|Sale|Cheap}'),
                'callback' => array($this, 'render_input'),
                'validator' => array(
                    'trim',
                ),
                'default' => '',
                'section' => __('General settings', 'external-importer'),
            ),
            'body_template' => array(
                'title' => __('Body template (description)', 'external-importer'),
                'description' => sprintf(__('You can use product data: %s.', 'external-importer'), '%PRODUCT.description%, %PRODUCT.title%, %PRODUCT.link%, %PRODUCT.price%, %PRODUCT.currencyCode%, %PRODUCT.manufacturer%, %PRODUCT.ATTRIBUTE.Attribute Name%,  etc') . '<br>' .
                sprintf(__('You can also use formulas like %s.', 'external-importer'), '{Discount|Sale|Cheap}') . ' ' .
                __('HTML tags are allowed.', 'external-importer'),
                'callback' => array($this, 'render_textarea'),
                'validator' => array(
                    'trim',
                ),
                'default' => '',
                'section' => __('General settings', 'external-importer'),
            ),
            'import_tags' => array(
                'title' => __('Tags', 'external-importer'),
                'description' => __('Add a comma separated list of tags.', 'external-importer') . ' ' .
                sprintf(__('You can use product data: %s.', 'external-importer'), '%PRODUCT.title%, %PRODUCT.link%, %PRODUCT.price%, %PRODUCT.currencyCode%, %PRODUCT.manufacturer%, etc'),
                'callback' => array($this, 'render_input'),
                'validator' => array(
                    'trim',
                ),
                'default' => '',
                'section' => __('General settings', 'external-importer'),
            ),
            'import_sku' => array(
                'title' => __('SKU', 'external-importer'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'enabled' => __('Import if available', 'external-importer'),
                    'generate' => __('Autogenerate', 'external-importer'),
                    'enabled_generate' => __('Import if available or Autogenerate', 'external-importer'),
                    'disabled' => __('Disabled', 'external-importer'),
                ),
                'default' => 'disabled',
                'section' => __('General settings', 'external-importer'),
            ),            
            
            'custom_fields' => array(
                'title' => __('Custom fields', 'external-importer'),
                'description' => __('Add custom fields.', 'external-importer') . ' ' .
                sprintf(__('You can use product data: %s.', 'external-importer'), '%RANDOM%, %PRODUCT.domain%, %PRODUCT.title%, %PRODUCT.link%, %PRODUCT.price%, %PRODUCT.currencyCode%, %PRODUCT.manufacturer%') . '<br>' .
                sprintf(__('Use JSON syntax to add arrays: %s.', 'external-importer'), '{"Design":"%RANDOM(3,9)%","Price":"9","Features":"%RANDOM(5,9)%"}'),
                'callback' => array($this, 'render_custom_fields_block'),
                'section' => __('General settings', 'external-importer'),
                'validator' => array(
                    array(
                        'call' => array($this, 'formatCustomFields'),
                        'type' => 'filter',
                    ),
                ),
            ),
            'currency' => array(
                'title' => __('Currency', 'external-importer'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'convert' => __('Convert to shop default currency', 'external-importer'),
                    'original' => __('Add original (i.e. not converted) price', 'external-importer'),
                ),
                'default' => 'original',
                'section' => __('External products', 'external-importer'),
            ),
        );
    }

    public function settings_page()
    {
        PluginAdmin::getInstance()->render('settings', array('page_slug' => $this->page_slug()));
    }

    public function render_custom_fields_line($args)
    {
        $i = isset($args['_field']) ? $args['_field'] : 0;
        $cf_name = isset($args['value'][$i]['cf_name']) ? $args['value'][$i]['cf_name'] : '';
        $cf_value = isset($args['value'][$i]['cf_value']) ? $args['value'][$i]['cf_value'] : '';

        echo '<input name="' . \esc_attr($args['option_name']) . '['
        . \esc_attr($args['name']) . '][' . $i . '][cf_name]" value="'
        . \esc_attr($cf_name) . '" class="text" placeholder="Name"  type="text"/>';
        echo '<input name="' . \esc_attr($args['option_name']) . '['
        . \esc_attr($args['name']) . '][' . $i . '][cf_value]" value="'
        . \esc_attr($cf_value) . '" class="regular-text ltr" placeholder="Value"  type="text"/>';
    }

    public function render_custom_fields_block($args)
    {
        if (is_array($args['value']))
            $total = count($args['value']) + 3;
        else
            $total = 3;

        for ($i = 0; $i < $total; $i++)
        {
            echo '<div class="ei_custom_fields_wrap" style="padding-bottom: 5px;">';
            $args['_field'] = $i;
            $this->render_custom_fields_line($args);
            echo '</div>';
        }
        if ($args['description'])
            echo '<p class="description">' . $args['description'] . '</p>';
    }

    public function formatCustomFields($values)
    {
        foreach ($values as $k => $value)
        {
            $values[$k]['cf_name'] = \sanitize_text_field($value['cf_name']);
            $values[$k]['cf_value'] = \sanitize_text_field($value['cf_value']);

            if (!$values[$k]['cf_name'] || !$values[$k]['cf_value'])
                unset($values[$k]);
        }

        return array_values($values);
    }

}
