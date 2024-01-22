<?php

namespace ContentEgg\application\modules\Ebay2;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;
use ContentEgg\application\Plugin;

/**
 * Ebay2Config class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class Ebay2Config extends AffiliateParserModuleConfig {

    public function options()
    {
        $options = array(
            'app_id' => array(
                'title' => 'App ID (Client ID) <span class="cegg_required">*</span>',
                'description' => __("Your application's OAuth credentials.", 'content-egg') . ' ' . sprintf(__('You can get it in <a target="_blank" href="%s">eBay Developers Program</a>.', 'content-egg'), 'http://developer.ebay.com/join'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                    array(
                        'call' => array('\ContentEgg\application\helpers\FormValidator', 'required'),
                        'when' => 'is_active',
                        'message' => sprintf(__('The field "%s" can not be empty.', 'content-egg'), 'App ID (Client ID)'),
                    ),
                ),
            ),
            'cert_id' => array(
                'title' => 'Cert ID (Client Secret) <span class="cegg_required">*</span>',
                'description' => __("Your application's OAuth credentials.", 'content-egg') . ' ' . sprintf(__('You can get it in <a target="_blank" href="%s">eBay Developers Program</a>.', 'content-egg'), 'http://developer.ebay.com/join'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                    array(
                        'call' => array('\ContentEgg\application\helpers\FormValidator', 'required'),
                        'when' => 'is_active',
                        'message' => sprintf(__('The field "%s" can not be empty.', 'content-egg'), 'Cert ID (Client Secret)'),
                    ),
                ),
            ),
            'tracking_id' => array(
                'title' => 'ePN Campaign ID' . '**',
                'description' => __('This is a 10-digit unique number provided by the eBay Partner Network. This is embedded in the campid part of the ePN affiliate link.', 'content-egg') .
                ' ' . __('Campaign ID is valid for all programs which were approved for you on EPN. If you leave this field blank - you will not get commissions from sales.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                    'absint',
                ),
            ),
            'custom_id' => array(
                'title' => __('ePN Custom ID', 'content-egg') . '**',
                'description' => __('This can be any value you want to use to identify this item or purchase order and can be a maximum of 256 characters. This is embedded in the customid part of the ePN affiliate link. Note: The Custom ID is the same as SUB-ID.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
            ),
            'deeplink' => array(
                'title' => __('Deeplink', 'content-egg'),
                'description' => __('Set this parameter only if you want to send traffic through third party affiliate networks.', 'content-egg') . ' ' .
                sprintf(__('Read more: <a target="_blank" href="%s">How to find your deeplink</a>.', 'content-egg'), 'https://ce-docs.keywordrush.com/modules/deeplink-settings'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
            ),
            'locale' => array(
                'title' => __('Default locale', 'content-egg'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => self::getLocalesList(),
                'default' => self::getDefaultLocale(),
                'metaboxInit' => true,
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
                        'arg' => 200,
                        'message' => sprintf(__('The field "%s" can not be more than %d.', 'content-egg'), 'Results', 200),
                    ),
                ),
            ),
            'entries_per_page_update' => array(
                'title' => __('Results for updates and autoblogging', 'content-egg'),
                'description' => __('Number of results for automatic updates and autoblogging.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => 9,
                'validator' => array(
                    'trim',
                    'absint',
                    array(
                        'call' => array('\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to'),
                        'arg' => 200,
                        'message' => sprintf(__('The field "%s" can not be more than %d.', 'content-egg'), 'Results for updates', 200),
                    ),
                ),
            ),
            'sort_order' => array(
                'title' => __('Sorting', 'content-egg'),
                'description' => '',
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    '' => 'Best Match (recommended)',
                    'price' => 'Lowest price first',
                    '-price' => 'Highest price first',
                    'newlyListed' => 'Most recently listed/newest items first',
                    'endingSoonest' => 'Listings nearest to end date/time first',
                ),
                'default' => '',
            ),
            'priority_listing' => array(
                'title' => __('Priority listings only', 'content-egg'),
                'description' => __('Priority listings are a subset of eBay listings identified with a higher priority to promote and are eligible for a higher commission.', 'content-egg'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'enabled' => __('Enabled', 'content-egg'),
                    'disabled' => __('Disabled', 'content-egg'),
                ),
                'default' => 'disabled',
            ),
            'category_id' => array(
                'title' => __('Category ID', 'content-egg'),
                'description' => __('The category ID is used to limit the results.', 'content-egg') . ' ' .
                sprintf(__('Use the <a target="_blank" href="%s">Category Changes page</a> to find IDs.', 'content-egg'), 'https://pages.ebay.com/sellerinformation/news/categorychanges.html'),
                // This field can have one category ID or a comma separated list of IDs. ->> Currently, you can pass in only one category ID per request.
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
            ),
            'exclude_category_ids' => array(
                'title' => __('Exclude category IDs', 'content-egg'),
                'description' => __('Any item in the specified categories will not be returned. Multiple values can be used for this filter and are separated by comma.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
            ),
            'description_search' => array(
                'title' => __('Search in description', 'content-egg'),
                'description' => __('Only items with a title or description matching the specified keyword are returned.', 'content-egg'),
                'callback' => array($this, 'render_checkbox'),
                'default' => false,
            ),
            'free_shipping_only' => array(
                'title' => __('Free Shipping', 'content-egg'),
                'description' => __('Only items with free shipping are returned', 'content-egg'),
                'callback' => array($this, 'render_checkbox'),
                'default' => false,
            ),
            'payment_methods' => array(
                'title' => __('Payment method', 'content-egg'),
                'description' => __('Only items that offer payment by credit card are returned', 'content-egg'),
                'callback' => array($this, 'render_checkbox'),
                'default' => false,
            ),
            'returns_accepted' => array(
                'title' => __('Returns accepted', 'content-egg'),
                'description' => __('Only items that can be returned to the seller are returned', 'content-egg'),
                'callback' => array($this, 'render_checkbox'),
                'default' => false,
            ),
            'buying_options' => array(
                'title' => __('Buying options', 'content-egg'),
                'description' => __('Only items offering the specified buying formats are returned.', 'content-egg') . '<br>' .
                'Buy It Now - items offered for a fixed-price. These items can also be offered as an auction. Once a bid is placed, Fixed Price is no longer available and the item is now only available as an auction.',
                'checkbox_options' => array(
                    'FIXED_PRICE' => 'Fixed Price (Buy It Now)',
                    'AUCTION' => 'Auction',
                    'BEST_OFFER' => 'Best Offer',
                ),
                'callback' => array($this, 'render_checkbox_list'),
                'default' => array('FIXED_PRICE'),
            ),
            'condition_ids' => array(
                'title' => __('Condition IDs', 'content-egg'),
                'description' => __('Only items with the specified condition ID are returned. Multiple values separated by comma can be used for this filter.', 'content-egg') . '<br>' .
                sprintf('For more information on item conditions for some popular eBay categories, see the <a target="_blank" href="%s">Item Condition IDs and Names</a>.', 'https://developer.ebay.com/devzone/finding/callref/Enums/conditionIdList.html'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
            ),
            'condition' => array(
                'title' => __('Condition', 'content-egg'),
                'description' => __('Unlike the Condition ID filter, the Condition filter will not return items of a specific condition such as Good, Very Good, or Seller Refurbished. It will only return items that are categorized by the broader conditions of NEW and USED.', 'content-egg'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    '' => 'All',
                    'NEW' => 'New',
                    'USED' => 'Used',
                    'UNSPECIFIED' => 'Unspecified',
                ),
            ),
            'location_country' => array(
                'title' => __('Location country', 'content-egg'),
                'description' => __('Only items located in the specified country are returned. Expects the two-letter ISO 3166 country code.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
            ),
            'available_to' => array(
                'title' => __('Delivery country', 'content-egg'),
                'description' => __('Only items that can be shipped to the specified country are returned. Expects the two-letter ISO 3166 country code.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
            ),
            'local_pickup_only' => array(
                'title' => __('Local pickup', 'content-egg'),
                'description' => __('Only local pickup items are returned.', 'content-egg'),
                'callback' => array($this, 'render_checkbox'),
                'default' => false,
            ),
            'delivery_postal_code' => array(
                'title' => __('Delivery postal code', 'content-egg'),
                'description' => __('Only items that can be shipped to the specified postal/zip code are returned.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
            ),
            'sellers' => array(
                'title' => __('Sellers', 'content-egg'),
                'description' => __('Only items from the specified sellers are returned in the response. Multiple values can be used for this filter and are separated by by comma.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
            ),
            'exclude_sellers' => array(
                'title' => __('Exclude sellers', 'content-egg'),
                'description' => __('Any items from the specified sellers are not returned in the response. Multiple values can be used for this filter and are separated by comma.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
            ),
            'min_bids' => array(
                'title' => __('Minimum bids', 'content-egg'),
                'description' => __('Example, 3', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
            ),
            'max_bids' => array(
                'title' => __('Maximum bids', 'content-egg'),
                'description' => __('Example, 10', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
            ),
            'min_price' => array(
                'title' => __('Minimal price', 'content-egg'),
                'description' => __('Example, 10.98', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
                'metaboxInit' => true,
            ),
            'max_price' => array(
                'title' => __('Maximal price', 'content-egg'),
                'description' => __('Example, 300.50', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
                'metaboxInit' => true,
            ),
            'save_img' => array(
                'title' => __('Save images', 'content-egg'),
                'description' => __('Save images on server', 'content-egg'),
                'callback' => array($this, 'render_checkbox'),
                'default' => false,
            ),
            'image_size' => array(
                'title' => __('Image size', 'content-egg'),
                'description' => '',
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'small' => __('Small', 'content-egg'),
                    'large' => __('Large', 'content-egg'),
                ),
                'default' => 'large',
            ),
        );
        $parent = parent::options();
        $parent['ttl']['default'] = 28800;
        $parent['update_mode']['default'] = 'cron';
        $parent['update_mode']['validator'][] = array(
            'call' => array($this, 'deleteToken'),
        );  
        $options = array_merge($parent, $options);

        return self::moveRequiredUp($options);
    }

    public static function getLocalesList()
    {
        // @link: https://developer.ebay.com/api-docs/buy/static/ref-marketplace-supported.html
        // Buy API Support by Marketplace
        return array(
            'EBAY_AT' => 'AT',
            'EBAY_AU' => 'AU',
            'EBAY_CA' => 'CA',
            'EBAY_CH' => 'CH',
            'EBAY_DE' => 'DE',
            'EBAY_ES' => 'ES',
            'EBAY_FR' => 'FR',
            'EBAY_GB' => 'GB',
            'EBAY_HK' => 'HK',
            'EBAY_IE' => 'EI',
            'EBAY_IT' => 'IT',
            'EBAY_NL' => 'NL',
            'EBAY_PL' => 'PL',
            'EBAY_SG' => 'SG',
            'EBAY_US' => 'US',
        );
    }

    public static function getCurrencyByLocale($locale)
    {
        $currencies = array(
            'EBAY_AT' => 'EUR',
            'EBAY_AU' => 'AUD',
            'EBAY_CA' => 'CAD',
            'EBAY_CH' => 'CHF',
            'EBAY_DE' => 'EUR',
            'EBAY_ES' => 'EUR',
            'EBAY_FR' => 'EUR',
            'EBAY_GB' => 'GBP',
            'EBAY_HK' => 'HKD',
            'EBAY_IE' => 'EUR',
            'EBAY_IT' => 'EUR',
            'EBAY_NL' => 'EUR',
            'EBAY_PL' => 'EUR',
            'EBAY_SG' => 'SGD',
            'EBAY_US' => 'USD',
        );

        if (isset($currencies[$locale]))
        {
            return $currencies[$locale];
        } else
        {
            return 'USD';
        }
    }

    public static function getDefaultLocale()
    {
        return 'EBAY_US';
    }
    
    public function deleteToken()
    {
        $id = 'Ebay2';
        $transient_name = Plugin::slug() . '-' . $id . '-access_token';
        \delete_transient($transient_name);   
        return true;
    }

}
