<?php

namespace ContentEgg\application\modules\Shareasale;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * ShareasaleConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class ShareasaleConfig extends AffiliateParserModuleConfig {

    public function options()
    {
        $optiosn = array(
            'affiliateId' => array(
                'title' => 'Affiliate ID <span class="cegg_required">*</span>',
                'description' => __('Your Affiliate ID.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                    array(
                        'call' => array('\ContentEgg\application\helpers\FormValidator', 'required'),
                        'when' => 'is_active',
                        'message' => __('The field "Affiliate ID" can not be empty.', 'content-egg'),
                    ),
                ),
                'section' => 'default',
            ),
            'token' => array(
                'title' => 'Token <span class="cegg_required">*</span>',
                'description' => __('Token and IP address will be used for verification of API requests. You can get it <a target="_blank" href="https://account.shareasale.com/a-apimanager.cfm">here</a>.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                    array(
                        'call' => array('\ContentEgg\application\helpers\FormValidator', 'required'),
                        'when' => 'is_active',
                        'message' => __('The "Token" can not be empty.', 'content-egg'),
                    ),
                ),
                'section' => 'default',
            ),
            'secret' => array(
                'title' => 'Secret Key <span class="cegg_required">*</span>',
                'description' => __('Special key to access API. You can get it <a target="_blank" href="https://account.shareasale.com/a-apimanager.cfm">here</a>.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                    array(
                        'call' => array('\ContentEgg\application\helpers\FormValidator', 'required'),
                        'when' => 'is_active',
                        'message' => __('The "Secret Access Key" can not be empty.', 'content-egg'),
                    ),
                ),
                'section' => 'default',
            ),
            'entries_per_page' => array(
                'title' => __('Results', 'content-egg'),
                'description' => __('Number of results for one search query.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => 10,
                'validator' => array(
                    'trim',
                    'absint',
                ),
                'section' => 'default',
            ),
            'entries_per_page_update' => array(
                'title' => __('Results for updates and autoblogging', 'content-egg'),
                'description' => __('Number of results for automatic updates and autoblogging.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => 6,
                'validator' => array(
                    'trim',
                    'absint',
                ),
                'section' => 'default',
            ),
            'merchantId' => array(
                'title' => 'Merchant ID',
                'description' => __('Return products only from this merchant', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
                'section' => 'default',
            ),
            'excludeMerchants' => array(
                'title' => __('Exclude merchant', 'content-egg'),
                'description' => __('Set Merchant ID which you want to exclude from search', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
                'section' => 'default',
            ),
            'save_img' => array(
                'title' => __('Save images', 'content-egg'),
                'description' => __('Save images on server', 'content-egg'),
                'callback' => array($this, 'render_checkbox'),
                'default' => false,
                'section' => 'default',
            ),
            'description_size' => array(
                'title' => __('Trim description', 'content-egg'),
                'description' => __('Description size in characters (0 - do not cut)', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '300',
                'validator' => array(
                    'trim',
                    'absint',
                ),
                'section' => 'default',
            ),
        );
        $parent = parent::options();
        $parent['ttl']['default'] = 2592000;

        return array_merge($parent, $optiosn);
    }

}
