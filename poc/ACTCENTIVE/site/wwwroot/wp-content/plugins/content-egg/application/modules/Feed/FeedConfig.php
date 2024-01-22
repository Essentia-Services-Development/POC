<?php

namespace ContentEgg\application\modules\Feed;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\AffiliateFeedParserModuleConfig;
use ContentEgg\application\helpers\CurrencyHelper;
use ContentEgg\application\helpers\TextHelper;

/**
 * FeedConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class FeedConfig extends AffiliateFeedParserModuleConfig
{

    public function options()
    {
        $currencies = CurrencyHelper::getCurrenciesList();

        $options = array(
            'feed_name' => array(
                'title' => __('Feed name', 'content-egg') . ' <span class="cegg_required">*</span>',
                'description' => sprintf(__('For example: %s', 'content-egg'), 'Saturn.de'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                    '\sanitize_text_field',
                    array(
                        'call' => array('\ContentEgg\application\helpers\FormValidator', 'required'),
                        'message' => sprintf(__('The field "%s" can not be empty.', 'content-egg'), __('Feed name', 'content-egg')),
                    ),
                    array(
                        'call' => array($this, 'saveFeedName'),
                        'type' => 'filter',
                    ),
                ),
            ),
            'feed_url' => array(
                'title' => __('Feed download URL', 'content-egg') . ' <span class="cegg_required">*</span>',
                'description' => __('CSV or XML format.', 'content-egg') . ' ' .
                sprintf(__('Make sure your unzipped feed size is less than %s.', 'content-egg'), \WP_MAX_MEMORY_LIMIT),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                    array(
                        'call' => array('\ContentEgg\application\helpers\FormValidator', 'required'),
                        'when' => 'is_active',
                        'message' => sprintf(__('The field "%s" can not be empty.', 'content-egg'), 'Datafeed Download URL'),
                    ),
                    array(
                        'call' => array($this, 'validateFeedUrl'),
                        'when' => 'is_active',
                        'message' => sprintf(__('Field "%s" filled with wrong data.', 'content-egg'), 'Feed download URL'),
                    ),
                ),
            ),
            'feed_format' => array(
                'title' => __('Feed format', 'content-egg') . ' <span class="cegg_required">*</span>',
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'csv' => __('CSV', 'content-egg'),
                    'xml' => 'XML',
                    'json' => 'JSON',
                ),
                'default' => 'csv',
            ),
            'archive_format' => array(
                'title' => __('Archive format', 'content-egg') . ' <span class="cegg_required">*</span>',
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'none' => __('None', 'content-egg'),
                    'zip' => 'ZIP',
                ),
                'default' => 'none',
            ),
            'encoding' => array(
                'title' => __('Feed encoding', 'content-egg') . ' <span class="cegg_required">*</span>',
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'UTF-8' => 'UTF-8',
                    'ISO-8859-1' => 'ISO-8859-1',
                ),
                'default' => 'UTF-8',
            ),
            'currency' => array(
                'title' => __('Default currency', 'content-egg') . ' <span class="cegg_required">*</span>',
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array_combine($currencies, $currencies),
                'default' => 'USD',
            ),
            'domain' => array(
                'title' => __('Default merchant domain', 'content-egg') . ' <span class="cegg_required">*</span>',
                'description' => sprintf(__('For example: %s', 'content-egg'), 'saturn.de'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    array(
                        'call' => array('\ContentEgg\application\helpers\FormValidator', 'required'),
                        'when' => 'is_active',
                        'message' => sprintf(__('The field "%s" can not be empty.', 'content-egg'), 'Default merchant domain'),
                    ),
                    array(
                        'call' => array($this, 'sanitizeDomain'),
                        'type' => 'filter',
                    ),
                ),
            ),
            'mapping' => array(
                'title' => __('Field mapping', 'content-egg') . ' <span class="cegg_required">*</span>',
                'description' => '',
                'callback' => array($this, 'render_mapping_block'),
                'validator' => array(
                    array(
                        'call' => array($this, 'mappingSanitize'),
                        'type' => 'filter',
                    ),
                    array(
                        'call' => array($this, 'mappingValidate'),
                        'when' => 'is_active',
                        'message' => __('Please fill out all required mapping fields.', 'content-egg'),
                    ),
                ),
            ),
            'deeplink' => array(
                'title' => __('Deeplink', 'content-egg'),
                'description' => __('Set this option only if your feed does not contain affiliate links.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
                'section' => 'default',
            ),
            'search_type' => array(
                'title' => __('Search type', 'content-egg') . ' <span class="cegg_required">*</span>',
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'full' => 'Full text search',
                    'exact' => 'Exact phrase search',
                ),
                'default' => 'full',
            ),            
        );
        $options = array_merge(parent::options(), $options);

        return self::moveRequiredUp($options);
    }

    public function render_mapping_row($args)
    {
        $field_name = $args['_field_name'];
        $value = isset($args['value'][$field_name]) ? $args['value'][$field_name] : '';

        $display_name = $field_name;

        if ($field_name == 'product node')
        {
            $display_name .= ' ' . __('(required for XML/JSON feed only)', 'content-egg');
        } elseif (self::isMappingFieldRequared($field_name))
        {
            $display_name .= ' ' . __('(required)', 'content-egg');
        } else
        {
            $display_name .= ' ' . __('(optional)', 'content-egg');
        }


        echo '<input value="' . \esc_attr($display_name) . '" class="regular-text ltr" type="text" readonly />';
        echo ' &#x203A; ';
        echo '<input name="' . \esc_attr($args['option_name']) . '['
        . \esc_attr($args['name']) . '][' . \esc_attr($field_name) . ']" value="'
        . \esc_attr($value) . '" class="regular-text ltr" placeholder="' . \esc_attr(__('In your feed', 'content-egg')) . '"  type="text"/>';
    }

    public function render_mapping_block($args)
    {
        if (!$args['value'])
        {
            $args['value'] = array();
        }

        foreach (array_keys(self::mappingFields()) as $str)
        {
            echo '<div style="padding-bottom: 5px;">';
            $args['_field_name'] = $str;
            $this->render_mapping_row($args);
            echo '</div>';
        }
        if ($args['description'])
        {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }

    public static function mappingFields()
    {
        return array(
            'product node' => false,
            'id' => true,
            'title' => true,
            'description' => true,
            'affiliate link' => true,
            'image ​​link' => true,
            'price' => true,
            'sale price' => false,
            'currency' => false,
            'availability' => false,
            'is in stock' => false,
            'direct link' => false,
            'brand' => false,
            'category' => false,
            'gtin' => false,
        );
    }

    public static function isMappingFieldRequared($field)
    {
        $fields = self::mappingFields();
        if (isset($fields[$field]) && $fields[$field])
        {
            return true;
        } else
        {
            return false;
        }
    }

    public function mappingSanitize($values)
    {
        foreach ($values as $k => $value)
        {
            $values[$k] = trim(\sanitize_text_field($value));
        }

        return $values;
    }

    public function mappingValidate($values)
    {
        foreach ($values as $field => $value)
        {
            if (self::isMappingFieldRequared($field) && !$value)
            {
                return false;
            }
        }

        return true;
    }

    public function saveFeedName($value)
    {
        FeedName::getInstance()->saveName($this->getModuleId(), $value);

        return $value;
    }

    public function sanitizeDomain($value)
    {
        $value = trim(\sanitize_text_field($value));
        if ($host = TextHelper::getHostName($value))
        {
            $value = $host;
        }

        $value = strtolower($value);
        $value = str_replace('www.', '', $value);
        $value = trim($value, "/");

        return $value;
    }

    public function validateFeedUrl($value)
    {
        if (filter_var($value, FILTER_VALIDATE_URL) === false)
        {
            return false;
        } else
        {
            return true;
        }
    }

}
