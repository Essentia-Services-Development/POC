<?php

namespace ContentEgg\application\modules\AmazonNoApi;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\AffiliateParserModuleConfig;
use ContentEgg\application\libs\amazon\AmazonLocales;

/**
 * AmazonNoApiConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class AmazonNoApiConfig extends AffiliateParserModuleConfig {

    const ALLOWED_LOCALES = array('us', 'ca', 'de', 'es', 'fr', 'in', 'it', 'uk');

    public function options()
    {
        $options = array(
            'associate_tag' => array(
                'title' => __('Default Associate Tag', 'content-egg') . ' <span class="cegg_required">*</span>',
                'description' => __('An alphanumeric token that uniquely identifies you as an Associate. To obtain an Associate Tag, refer to <a target="_blank" href="https://webservices.amazon.com/paapi5/documentation/troubleshooting/sign-up-as-an-associate.html">Becoming an Associate</a>.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                    array(
                        'call' => array('\ContentEgg\application\helpers\FormValidator', 'required'),
                        'when' => 'is_active',
                        'message' => __('The "Tracking ID" can not be empty.', 'content-egg'),
                    ),
                ),
                'section' => 'default',
            ),
            'locale' => array(
                'title' => __('Default locale', 'content-egg') . '<span class="cegg_required">*</span>',
                'description' => __('Your Amazon Associates tag works only in the locale in which you register. If you want to be an Amazon Associate in more than one locale, you must register separately for each locale.', 'content-egg'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => self::getLocalesList(),
                'default' => self::getDefaultLocale(),
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
                    array(
                        'call' => array('\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to'),
                        'arg' => 10,
                        'message' => __('The field "Results" can not be more than 10.', 'content-egg'),
                    ),
                ),
                'section' => 'default',
            ),
            'entries_per_page_update' => array(
                'title' => __('Results for updates', 'content-egg'),
                'description' => __('Number of results for automatic updates and autoblogging.', 'content-egg') . ' ' .
                __('It needs a bit more time to get more than 10 results in one request.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => 10,
                'validator' => array(
                    'trim',
                    'absint',
                    array(
                        'call' => array('\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to'),
                        'arg' => 10,
                        'message' => __('The field "Results" can not be more than 50.', 'content-egg'),
                    ),
                ),
                'section' => 'default',
            ),
            'link_type' => array(
                'title' => __('Link type', 'content-egg'),
                'description' => __('Type of partner links. Know more about amazon <a target="_blank" href="https://affiliate-program.amazon.com/gp/associates/help/t2/a11">90 day cookie</a>.', 'content-egg'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'product' => 'Product page',
                    'add_to_cart' => 'Add to cart',
                ),
                'default' => 'product',
                'section' => 'default',
            ),
            'save_img' => array(
                'title' => __('Save images', 'content-egg'),
                'description' => __('Save images to local server', 'content-egg')
                . ' <p class="description">' . __('Enabling this option may violate API rules.', 'content-egg') . '</p>',
                'callback' => array($this, 'render_checkbox'),
                'default' => false,
                'section' => 'default',
            ),
            'show_small_logos' => array(
                'title' => __('Small logos', 'content-egg'),
                'callback' => array($this, 'render_dropdown'),
                'description' => __('Enabling this option may violate API rules.', 'content-egg') . ' '
                . sprintf(__('Read more: <a target="_blank" href="%s">Amazon brand usage guidelines</a>.', 'content-egg'), 'https://advertising.amazon.com/ad-specs/en/policy/brand-usage'),
                'dropdown_options' => array(
                    'true' => __('Show small logos', 'content-egg'),
                    'false' => __('Hide small logos', 'content-egg'),
                ),
                'default' => 'false',
            ),
            'show_large_logos' => array(
                'title' => __('Large logos', 'content-egg'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'true' => __('Show large logos', 'content-egg'),
                    'false' => __('Hide large logos', 'content-egg'),
                ),
                'default' => 'true',
            ),
        );

        foreach (self::getLocalesList() as $locale_id => $locale_name)
        {
            $options['associate_tag_' . $locale_id] = array(
                'title' => sprintf(__('Associate Tag for %s locale', 'content-egg'), $locale_name),
                'description' => __('Type here your tracking ID for this locale if you need multiple locale parsing', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
            );
        }

        $parent = parent::options();
        $parent['ttl_items']['default'] = 86400;
        $options = array_merge($parent, $options);

        return self::moveRequiredUp($options);
    }

    public static function getLocalesList()
    {
        $locales = array_keys(self::locales());
        sort($locales);

        return array_combine($locales, array_map('strtoupper', $locales));
    }

    public static function getDefaultLocale()
    {
        return 'us';
    }

    public static function getActiveLocalesList()
    {
        $locales = self::getLocalesList();
        $active = array();

        $default = self::getInstance()->option('locale');
        $active[$default] = $locales[$default];

        foreach ($locales as $locale => $name)
        {
            if ($locale == $default)
            {
                continue;
            }
            if (self::getInstance()->option('associate_tag_' . $locale))
            {
                $active[$locale] = $name;
            }
        }

        return $active;
    }

    public static function getDomainByLocale($locale)
    {
        return AmazonLocales::getDomain($locale);
    }

    public static function locales()
    {
        return array_intersect_key(AmazonLocales::locales(), array_flip(self::ALLOWED_LOCALES));
    }

}
