<?php

namespace ContentEgg\application\modules\Daisycon;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\AffiliateFeedParserModuleConfig;

/**
 * DaisyconConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class DaisyconConfig extends AffiliateFeedParserModuleConfig
{

    public function options()
    {
        $options = array(
            'datafeed_url' => array(
                'title' => 'Datafeed Download URL <span class="cegg_required">*</span>',
                'description' => sprintf(__('Go to Material -> <a target="_blank" href="%s">Product feeds</a>. Read more <a href="%s">here</a>.', 'content-egg'), 'https://my.daisycon.com/publisher/material/productfeeds', 'https://ce-docs.keywordrush.com/modules/affiliate/daisycon'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                    array(
                        'call' => array('\ContentEgg\application\helpers\FormValidator', 'required'),
                        'when' => 'is_active',
                        'message' => sprintf(__('The field "%s" can not be empty.', 'content-egg'), 'Datafeed Download URL'),
                    ),
                ),
            ),
            'publisher_id' => array(
                'title' => 'Publisher ID',
                'description' => __('Your Daisycon publisher ID.', 'content-egg') . ' ' . __('Go to SETTINGS -> Account.', 'content-egg') . ' ' . __('It will be used for API authentication to retrieve certain campaign settings (currency and domain name).', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
            ),
            'username' => array(
                'title' => 'Email address',
                'description' => __('Your Daisycon email address.', 'content-egg') . ' ' . __('It will be used for API authentication to retrieve certain campaign settings (currency and domain name).', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
            ),
            'password' => array(
                'title' => 'Password',
                'description' => __('Your Daisycon password.', 'content-egg') . ' ' . __('It will be used for API authentication to retrieve certain campaign settings (currency and domain name)..', 'content-egg'),
                'callback' => array($this, 'render_password'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
            ),
            'entries_per_page' => array(
                'title' => __('Results', 'content-egg'),
                'description' => __('Number of results for one search query.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => 10,
                'validator' => array(
                    'trim',
                    'absint',
                    array(
                        'call' => array('\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to'),
                        'arg' => 100,
                        'message' => sprintf(__('The field "%s" can not be more than %d.', 'content-egg'), 'Results', 100),
                    ),
                ),
            ),
            'entries_per_page_update' => array(
                'title' => __('Results for updates', 'content-egg'),
                'description' => __('Number of results for automatic updates and autoblogging.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => 6,
                'validator' => array(
                    'trim',
                    'absint',
                    array(
                        'call' => array('\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to'),
                        'arg' => 100,
                        'message' => sprintf(__('The field "%s" can not be more than %d.', 'content-egg'), 'Results', 100),
                    ),
                ),
            ),
            'in_stock' => array(
                'title' => __('In stock', 'content-egg'),
                'description' => __('Search only products in stock.', 'content-egg'),
                'callback' => array($this, 'render_checkbox'),
                'default' => true,
                'section' => 'default',
            ),
            'save_img' => array(
                'title' => __('Save images', 'content-egg'),
                'description' => __('Save images on server', 'content-egg'),
                'callback' => array($this, 'render_checkbox'),
                'default' => false,
                'section' => 'default',
            ),
        );

        $options = array_merge(parent::options(), $options);

        return self::moveRequiredUp($options);
    }

}
