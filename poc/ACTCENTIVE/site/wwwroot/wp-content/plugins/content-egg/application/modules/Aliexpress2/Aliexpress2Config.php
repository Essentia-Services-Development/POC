<?php

namespace ContentEgg\application\modules\Aliexpress2;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * AliexpressConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class Aliexpress2Config extends AffiliateParserModuleConfig {

    public function options()
    {
        $optiosn = array(
            'app_key' => array(
                'title' => 'App Key <span class="cegg_required">*</span>',
                'description' => sprintf(__('Special key to access Aliexpress API. You can apply in the <a href="%s">Aliexpress console</a>.', 'content-egg'), 'https://console.aliexpress.com'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                    array(
                        'call' => array('\ContentEgg\application\helpers\FormValidator', 'required'),
                        'when' => 'is_active',
                        'message' => sprintf(__('The "%s" can not be empty', 'content-egg'), 'App Key'),
                    ),
                ),
                'section' => 'default',
            ),
            'app_secret' => array(
                'title' => 'App Secret <span class="cegg_required">*</span>',
                'description' => sprintf(__('Special key to access Aliexpress API. You can apply in the <a href="%s">Aliexpress console</a>.', 'content-egg'), 'https://console.aliexpress.com'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                    array(
                        'call' => array('\ContentEgg\application\helpers\FormValidator', 'required'),
                        'when' => 'is_active',
                        'message' => sprintf(__('The "%s" can not be empty', 'content-egg'), 'App Secret'),
                    ),
                ),
            ),
            'tracking_id' => array(
                'title' => 'Tracking ID',
                'description' => sprintf(__('Set this field if you want to send traffic through AliExpress Portals. You can find your Tracking ID <a target="_blank" href="%s">here</a>.', 'content-egg'), 'http://portals.aliexpress.com/track_id_manage.htm'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
            ),
            'deeplink' => array(
                'title' => 'Deeplink',
                'description' => sprintf(__('Set this field if you want to send traffic through one of the affiliate networks with AliExpress support. Read more how to find your Deeplink <a target="_blank" href="%s">here</a>.', 'content-egg'), 'https://www.keywordrush.com/docs/content-egg/DeeplinkSettings.html'),
                'callback' => array($this, 'render_input'),
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
                        'arg' => 50,
                        'message' => sprintf(__('The field "%s" can not be more than %d.', 'content-egg'), 'Results', 50),
                    ),
                ),
            ),
            'entries_per_page_update' => array(
                'title' => __('Results for updates and autoblogging', 'content-egg'),
                'description' => __('Number of results for automatic updates and autoblogging.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => 10,
                'validator' => array(
                    'trim',
                    'absint',
                    array(
                        'call' => array('\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to'),
                        'arg' => 50,
                        'message' => sprintf(__('The field "%s" can not be more than %d.', 'content-egg'), 'Results', 50),
                    ),
                ),
            ),
            'target_currency' => array(
                'title' => __('Currency', 'content-egg'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'AUD' => 'AUD',
                    'BRL' => 'BRL',
                    'CAD' => 'CAD',
                    'CLP' => 'CLP',
                    'EUR' => 'EUR',
                    'GBP' => 'GBP',
                    'IDR' => 'IDR',
                    'ILS' => 'ILS',
                    'INR' => 'INR',
                    'JPY' => 'JPY',
                    'KRW' => 'KRW',
                    'MXN' => 'MXN',
                    'NGZ' => 'NGZ',
                    'RUB' => 'RUB',
                    'SEK' => 'SEK',
                    'THB' => 'THB',
                    'TRY' => 'TRY',
                    'UAH' => 'UAH',
                    'USD' => 'USD',
                    'VND' => 'VND',
                ),
                'default' => 'USD',
            ),
            'target_language' => array(
                'title' => __('Language', 'content-egg'),
                'description' => '',
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'EN' => 'EN',
                    'RU' => 'RU',
                    'PT' => 'PT',
                    'ES' => 'ES',
                    'FR' => 'FR',
                    'ID' => 'ID',
                    'IT' => 'IT',
                    'TH' => 'TH',
                    'JA' => 'JA',
                    'AR' => 'AR',
                    'VI' => 'VI',
                    'TR' => 'TR',
                    'DE' => 'DE',
                    'HE' => 'HE',
                    'KO' => 'KO',
                    'NL' => 'NL',
                    'PL' => 'PL',
                    'MX' => 'MX',
                    'CL' => 'CL',
                    'IW' => 'IW',
                    'IN' => 'IN',
                ),
                'default' => 'EN',
            ),
            'category_id' => array(
                'title' => __('Category ', 'content-egg'),
                'description' => __('Limit the search of goods by this category.', 'content-egg'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    '0.' => __('All categories', 'content-egg'),
                    '21.' => 'Office & School Supplies',
                    '200574005.' => 'Underwear',
                    '3.' => 'Apparel & Accessories',
                    '34.' => 'Automobiles & Motorcycles',
                    '66.' => 'Beauty & Health',
                    '7.' => 'Computer & Office',
                    '44.' => 'Consumer Electronics',
                    '502.' => 'Electronic Components & Supplies',
                    '2.' => 'Food',
                    '1503.' => 'Furniture',
                    '200165144.' => 'Hair Extensions & Wigs',
                    '15.' => 'Home & Garden',
                    '6.' => 'Home Appliances',
                    '13.' => 'Home Improvement',
                    '36.' => 'Jewelry & Accessories',
                    '39.' => 'Lights & Lighting',
                    '1524.' => 'Luggage & Bags',
                    '1501.' => 'Mother & Kids',
                    '509.' => 'Phones & Telecommunications',
                    '30.' => 'Security & Protection',
                    '322.' => 'Shoes',
                    '200001075.' => 'Special Category',
                    '18.' => 'Sports & Entertainment',
                    '1420.' => 'Tools',
                    '26.' => 'Toys & Hobbies',
                    '1511.' => 'Watches',
                    '320.' => 'Weddings & Events',
                    '200000343.' => 'Men\'s Clothing',
                    '200000532.' => 'Novelty & Special Use',
                    '200000297.' => 'Apparel Accessories',
                    '200000345.' => 'Women\'s Clothing',
                ),
                'default' => '0.',
            ),
            'min_sale_price' => array(
                'title' => __('Maximum price', 'content-egg'),
                'description' => __('The price must be set in USD. Example: 99', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
                'metaboxInit' => true,
            ),
            'max_sale_price' => array(
                'title' => __('Minimum price', 'content-egg'),
                'description' => __('The price must be set in USD. Example: 10', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
                'metaboxInit' => true,
            ),
            'platform_product_type' => array(
                'title' => __('Platform', 'content-egg'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'ALL' => __('All', 'content-egg'),
                    'PLAZA' => 'Plaza',
                    'TMALL' => 'Tmall',
                ),
                'default' => 'ALL',
            ),
            'sort' => array(
                'title' => __('Sorting', 'content-egg'),
                'description' => '',
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    '' => __('Default', 'content-egg'),
                    'SALE_PRICE_ASC' => __('Price ASC', 'content-egg'),
                    'SALE_PRICE_DESC' => __('Price DESC', 'content-egg'),
                    'LAST_VOLUME_ASC' => __('Volume ASC', 'content-egg'),
                    'LAST_VOLUME_DESC' => __('Volume DESC', 'content-egg'),
                ),
                'default' => '',
            ),
            /*
              'ship_to_country' => array(
              'title' => __('Oversea warehouse', 'content-egg'),
              'callback' => array($this, 'render_dropdown'),
              'description' => __('Can be shipped from overseas warehouses, and logistics is highly efficient.', 'content-egg'),
              'dropdown_options' => array(
              '' => '- ' . __('Not selected', 'content-egg') . ' -',
              'AT' => 'Austria',
              'BE' => 'Belgium',
              'CZ' => 'Czech Republic',
              'DE' => 'Germany',
              'DK' => 'Denmark',
              'ES' => 'Spain',
              'FR' => 'France',
              'HU' => 'Hungary',
              'IT' => 'Italy',
              'LU' => 'Luxembourg',
              'NL' => 'Netherlands',
              'PL' => 'Poland',
              'PT' => 'Portugal',
              'RU' => 'Russia',
              'SI' => 'Slovenia',
              'SK' => 'Slovakia',
              'UK' => 'United Kingdom',
              ),
              'default' => '',
              ),
             *
             */
            'save_img' => array(
                'title' => __('Save images', 'content-egg'),
                'description' => __('Save images locally', 'content-egg'),
                'callback' => array($this, 'render_checkbox'),
                'default' => false,
                'section' => 'default',
            ),
        );

        return array_merge(parent::options(), $optiosn);
    }

}
