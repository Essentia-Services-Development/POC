<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * GeneralSettings class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class GeneralConfig extends Config {

    public function page_slug()
    {
        return 'affiliate-egg-settings';
    }

    public function option_name()
    {
        return 'affegg_options';
    }

    public function add_admin_menu()
    {
        \add_submenu_page(AffiliateEgg::slug, __('Settings', 'affegg') . ' &lsaquo; Affiliate Egg', __('Settings', 'affegg'), 'manage_options', $this->page_slug, array($this, 'settings_page'));
    }

    protected function options()
    {
        $total_price_alerts = PriceAlertModel::model()->count('status = ' . PriceAlertModel::STATUS_ACTIVE);
        $sent_price_alerts = PriceAlertModel::model()->count('status = ' . PriceAlertModel::STATUS_DELETED
                . ' AND TIMESTAMPDIFF( DAY, complet_date, "' . \current_time('mysql') . '") <= ' . PriceAlertModel::CLEAN_DELETED_DAYS);

        return array(
            'lang' => array(
                'title' => __('Website language', 'affegg'),
                'description' => __('Language of output templates.', 'affegg'),
                'dropdown_options' => self::langs(),
                'callback' => array($this, 'render_dropdown'),
                'default' => self::getDefaultLang(),
                'section' => 'default',
            ),
            'cashback_integration' => array(
                'title' => __('Cashback Tracker integration', 'affegg'),
                'description' => sprintf(__('Integration with %s plugin.', 'affegg'), '<a target="_blanl" href="https://www.keywordrush.com/cashbacktracker">Cashback Tracker</a>') . ' ' .
                __('Convert all affiliate links to trackable cashback links if possible.', 'affegg'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'enabled' => __('Enabled', 'affegg'),
                    'disabled' => __('Disabled', 'affegg'),
                ),
                'default' => 'enabled',
                'section' => __('default'),
            ),
            'product_ttl' => array(
                'title' => __('Update products', 'affegg'),
                'description' => __('Cache lifetime of products in seconds, after which it is necessary to update the price and availability. 0 - never update.', 'affegg'),
                'callback' => array($this, 'render_input'),
                'default' => 2592000,
                'validator' => array(
                    'trim',
                    array(
                        'call' => array(NS . 'FormValidator', 'required'),
                        'message' => __('Field "Update products" - can\'t be empty.', 'affegg'),
                    ),
                    'absint',
                ),
                'section' => 'default',
            ),
            'catalog_ttl' => array(
                'title' => __('Update Catalogs', 'affegg'),
                'description' => __('Cache lifetime of catalogs in seconds, after which it is necessary to update the price and availability of catalog products. 0 - never update.', 'affegg'),
                'callback' => array($this, 'render_input'),
                'default' => 3888000,
                'validator' => array(
                    'trim',
                    array(
                        'call' => array(NS . 'FormValidator', 'required'),
                        'message' => __('Field "Update catalogs" - can\'t be empty.', 'affegg'),
                    ),
                    'absint',
                ),
                'section' => 'default',
            ),
            'product_sleep' => array(
                'title' => __('Delay while updating', 'affegg'),
                'description' => __('Pause in microseconds between a caching of products. More pause - less load on server. 1000000 microseconds = 1 second.', 'affegg'),
                'callback' => array($this, 'render_input'),
                'default' => 500000,
                'validator' => array(
                    'trim',
                    array(
                        'call' => array(NS . 'FormValidator', 'required'),
                        'message' => __('Field "Delay while updating" - can\'t be empty.', 'affegg'),
                    ),
                    'absint',
                ),
                'section' => 'default',
            ),
            'product_update_sleep' => array(
                'title' => __('Pause when updating', 'affegg'),
                'description' => __('Pause in microseconds when autoupdating catalogs and products. 1000000 microseconds = 1 second.', 'affegg'),
                'callback' => array($this, 'render_input'),
                'default' => 2000000,
                'validator' => array(
                    'trim',
                    array(
                        'call' => array(NS . 'FormValidator', 'required'),
                        'message' => __('Field "Pause when updating" - can\'t be empty.', 'affegg'),
                    ),
                    'absint',
                ),
                'section' => 'default',
            ),
            'description_max_size' => array(
                'title' => __('Size of description', 'affegg'),
                'description' => __('Maximum size of description in symbols. 0 - don\'t trim description', 'affegg'),
                'callback' => array($this, 'render_input'),
                'default' => 300,
                'validator' => array(
                    'trim',
                    array(
                        'call' => array(NS . 'FormValidator', 'required'),
                        'message' => __('Field "Size of description" - can\'t be empty.', 'affegg'),
                    ),
                    'absint',
                ),
                'section' => 'default',
            ),
            'save_img' => array(
                'title' => __('Save images', 'affegg'),
                'description' => __('Save images to local server.', 'affegg'),
                'callback' => array($this, 'render_checkbox'),
                'default' => 0,
                'validator' => array(
                    'Keywordrush\AffiliateEgg\affegg_intval_bool',
                ),
                'section' => 'default',
            ),
            'set_featured_img' => array(
                'title' => 'Featured image',
                'description' => __('Set product image as Featured image for a post. <p class="description"> Image is also loaded into Media library. It is applied only to the new/updated posts.</p>', 'affegg'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    0 => __('Don\'t set', 'affegg'),
                    'first' => __('Set first image', 'affegg'),
                    'second' => __('Set second image', 'affegg'),
                    'last' => __('Set last image', 'affegg'),
                    'rand' => __('Set random image', 'affegg'),
                ),
                'default' => 0,
                'validator' => array(
                ),
                'section' => 'default',
            ),
            'button_color' => array(
                'title' => __('Button color', 'affegg'),
                'description' => __('Button color for standard templates.', 'affegg'),
                'callback' => array($this, 'render_color_picker'),
                'default' => '#5cb85c',
                'validator' => array(
                    'trim',
                ),
                'section' => 'default',
            ),
            'btn_text_buy_now' => array(
                'title' => __('Buy now button text', 'affegg'),
                'description' => sprintf(__('It will be used instead of "%s".', 'affegg'), __('Buy Now', 'affegg-tpl')),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'strip_tags',
                ),
            ),            
            'price_history_days' => array(
                'title' => __('Price history', 'affegg'),
                'description' => __('How long save price history. 0 - deactivate price history.', 'affegg') . ' ' . 
                 __('You can use tags: %DOMAIN%, %PRICE%.', 'affegg'),
                'callback' => array($this, 'render_input'),
                'default' => 180,
                'validator' => array(
                    'trim',
                    'absint',
                    array(
                        'call' => array(NS . 'FormValidator', 'less_than_equal_to'),
                        'arg' => 365,
                        'message' => sprintf(__('The field "%s" can\'t be more than %d.', 'affegg'), __('Price history', 'affegg'), 365),
                    ),
                ),
                'section' => 'default',
            ),
            'price_alert_enabled' => array(
                'title' => 'Price alert',
                'description' => __('Allow members to subscribe for price drop alert on email.', 'affegg') .
                '<p class="description">' . sprintf(__('Active subscriptions now: <b>%d</b>', 'affegg'), $total_price_alerts) .
                '. ' . sprintf(__('Messages are sent for last %d days: <b>%d</b>', 'affegg'), PriceAlertModel::CLEAN_DELETED_DAYS, $sent_price_alerts) . '.</p>' .
                '<p class="description">' . __('This option requires "Price history" option (must be enabled) to work.', 'affegg') . '</p>',
                'callback' => array($this, 'render_checkbox'),
                'default' => true,
                'section' => 'default',
            ),
            'noindex' => array(
                'title' => __('Noindex tag', 'affegg'),
                'description' => __('Add tag &lt;noindex&gt; for all storefronts.', 'affegg'),
                'callback' => array($this, 'render_checkbox'),
                'default' => 0,
                'validator' => array(
                    'Keywordrush\AffiliateEgg\affegg_intval_bool',
                ),
                'section' => 'default',
            ),
            'ajax_eggs' => array(
                'title' => __('Show storefront with ajax', 'affegg'),
                'description' => __('Load storefronts on via JavaScript. <p class="description"> Usually it hides storefronts from indexing by search bots. This is not worked if you use next parameter in shortcode of storefront. </p>', 'affegg'),
                'callback' => array($this, 'render_checkbox'),
                'default' => 0,
                'validator' => array(
                    'Keywordrush\AffiliateEgg\affegg_intval_bool',
                ),
                'section' => 'default',
            ),
            'set_subid' => array(
                'title' => __('Subid from ID of storefront', 'affegg'),
                'description' => __('Auto add subid in Deeplink url. <p class="description">It will not adding, if subid is already exist in Deeplink.</p>', 'affegg'),
                'callback' => array($this, 'render_checkbox'),
                'default' => 0,
                'validator' => array(
                    'Keywordrush\AffiliateEgg\affegg_intval_bool',
                ),
                'section' => 'default',
            ),
            'set_ext_subid' => array(
                'title' => __('Subid from GET parameter', 'affegg'),
                'description' => __('Automatically add subid to Deeplink from GET parameter "affegg_subid ". <p class="description"> For example, it is possible to use this for tracking of conversion of ads. Has the top priority if other subid are set. </p>', 'affegg'),
                'callback' => array($this, 'render_checkbox'),
                'default' => 0,
                'validator' => array(
                    'Keywordrush\AffiliateEgg\affegg_intval_bool',
                ),
                'section' => 'default',
            ),
            'set_local_redirect' => array(
                'title' => __('Local redirect', 'affegg'),
                'description' => __('Make links with local 301 redirect', 'affegg'),
                'callback' => array($this, 'render_checkbox'),
                'default' => 0,
                'validator' => array(
                    'Keywordrush\AffiliateEgg\affegg_intval_bool',
                ),
                'section' => 'default',
            ),
            'comments_max_count' => array(
                'title' => __('Reviews', 'affegg'),
                'description' => __('How many reviews will be parsed. "0" - all. "-1" - disabled', 'affegg'),
                'callback' => array($this, 'render_input'),
                'default' => 3,
                'validator' => array(
                    'trim',
                    'intval',
                ),
                'section' => 'default',
            ),
            'ga_events' => array(
                'title' => __('Google Analytics Events', 'affegg'),
                'description' => __('Add Google Analytics tracking. Also, you need to add to your site Universal Analytics (analytics.js)', 'affegg'),
                'callback' => array($this, 'render_checkbox'),
                'default' => 0,
                'validator' => array(
                    'Keywordrush\AffiliateEgg\affegg_intval_bool',
                ),
                'section' => 'default',
            ),
            'see_more_link' => array(
                'title' => __('Link  "See more"', 'affegg'),
                'description' => __('Show Link  "See more" after storefront', 'affegg'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    0 => __('Don\'t use Link  "See more"', 'affegg'),
                    'single' => __('Show only if storefront has only one link', 'affegg'),
                    'first' => __('Use first link on catalog', 'affegg'),
                    'last' => __('Use last link on catalog', 'affegg'),
                ),
                'default' => 0,
                'validator' => array(
                ),
                'section' => 'default',
            ),
            'save_custom_fields' => array(
                'title' => __('Save meta to post', 'affegg'),
                'description' => __('When you will add storefronts to post, data will be added also to post meta. ', 'affegg'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    0 => __('Don\'t add', 'affegg'),
                    'price_min' => __('Add data from product with minimum price', 'affegg'),
                    'price_max' => __('Add data from product with maximum price', 'affegg'),
                    'first' => __('Add first product', 'affegg'),
                    'last' => __('Add last product', 'affegg'),
                    'rand' => __('Add data from random product', 'affegg'),
                ),
                'default' => 0,
                'validator' => array(
                ),
                'section' => 'default',
            ),
        );
    }

    public function settings_page()
    {
        AffiliateEggAdmin::getInstance()->render('settings', array('page_slug' => $this->page_slug()));
    }

    public static function getDefaultLang()
    {
        $locale = \get_locale();
        $lang = explode('_', $locale);
        if (array_key_exists($lang[0], self::langs()))
            return $lang[0];
        else
            return 'en';
    }

    public static function langs()
    {
        return array(
            'ar' => 'Arabic',
            'bg' => 'Bulgarian',
            'ca' => 'Catalan',
            //'zh_CN' => 'Chinese (simplified)',
            //'zh_TW' => 'Chinese (traditional)',
            'hr' => 'Croatian',
            'cs' => 'Czech',
            'da' => 'Danish',
            'nl' => 'Dutch',
            'en' => 'English',
            'et' => 'Estonian',
            'tl' => 'Filipino',
            'fi' => 'Finnish',
            'fr' => 'French',
            'de' => 'German',
            'el' => 'Greek',
            'iw' => 'Hebrew',
            'hi' => 'Hindi',
            'hu' => 'Hungarian',
            'is' => 'Icelandic',
            'id' => 'Indonesian',
            'it' => 'Italian',
            'ja' => 'Japanese',
            'ko' => 'Korean',
            'lv' => 'Latvian',
            'lt' => 'Lithuanian',
            'ms' => 'Malay',
            'no' => 'Norwegian',
            'fa' => 'Persian',
            'pl' => 'Polish',
            'pt' => 'Portuguese',
            'ro' => 'Romanian',
            'ru' => 'Russian',
            'sr' => 'Serbian',
            'sk' => 'Slovak',
            'sl' => 'Slovenian',
            'es' => 'Spanish',
            'sv' => 'Swedish',
            'th' => 'Thai',
            'tr' => 'Turkish',
            'uk' => 'Ukrainian',
            'ur' => 'Urdu',
            'vi' => 'Vietnamese',
        );
    }

}
