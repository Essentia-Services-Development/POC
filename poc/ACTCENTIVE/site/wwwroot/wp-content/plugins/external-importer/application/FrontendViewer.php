<?php

namespace ExternalImporter\application;

defined('\ABSPATH') || exit;

use ExternalImporter\application\admin\FrontendConfig;
use ExternalImporter\application\helpers\WooHelper;
use ExternalImporter\application\components\WooImporter;
use ExternalImporter\application\admin\SyncConfig;
use ExternalImporter\application\Translator;
use ExternalImporter\application\helpers\CurrencyHelper;
use ExternalImporter\application\admin\WooConfig;

/**
 * FrontendViewer class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class FrontendViewer {

    public static function initAction()
    {
        if (\is_admin())
            return;

        if (FrontendConfig::getInstance()->option('show_update_date'))
            \add_action('woocommerce_single_product_summary', array(__CLASS__, 'showUpdateDate'), 25);
        elseif (FrontendConfig::getInstance()->option('show_disclaimer'))
            \add_action('woocommerce_single_product_summary', array(__CLASS__, 'showDisclaimer'), 25);

        if (SyncConfig::getInstance()->option('outofstock_product') == 'hide_price')
            \add_filter('woocommerce_get_price_html', array(__CLASS__, 'hideOutOfStockPrice'), 10, 2);

        if (FrontendConfig::getInstance()->option('buy_button_text'))
        {
            \add_filter('woocommerce_product_single_add_to_cart_text', array(__CLASS__, 'customButtonText'), 10, 2);
            \add_filter('woocommerce_product_add_to_cart_text', array(__CLASS__, 'customButtonText'), 10, 2);
        }

        \add_filter('woocommerce_currency_symbol', array(__CLASS__, 'changeCurrencySymbol'), 10, 2);
    }

    public static function changeCurrencySymbol($currency_symbol, $currency)
    {
        global $product;

        if (!is_object($product) || !isset($product))
            return $currency_symbol;

        if ($product->is_type('simple'))
            return $currency_symbol;

        if (WooConfig::getInstance()->option('currency') == 'convert')
            return $currency_symbol;

        if (!$offer = WooImporter::getProductMeta($product->get_id()))
            return $currency_symbol;

        if ($offer->currencyCode == $currency)
            return $currency_symbol;

        return CurrencyHelper::getInstance()->getSymbol($offer->currencyCode);
    }

    public static function showUpdateDate()
    {
        global $post;

        if (!$last_update = WooImporter::getLastUpdateMeta($post->ID))
            return '';

        $date = WooHelper::dateFormatFromGmt($last_update, true);
        $t = Translator::translate('Last updated on %s');

        echo '<div class="ei_last_updated">' . sprintf($t, $date);
        self::showDisclaimer();
        echo '</div>';
    }

    public static function showDisclaimer()
    {
        if (!FrontendConfig::getInstance()->option('show_disclaimer'))
            return;

        global $post;

        if (!$product = WooImporter::getProductMeta($post->ID))
            return;

        if (FrontendConfig::getInstance()->option('show_update_date'))
            $t = Translator::translate('Details');
        else
            $t = Translator::translate('Disclosure');

        if (!$disclaimer_text = WooHelper::buildTemplate(FrontendConfig::getInstance()->option('disclaimer_text'), $product))
            return;

        self::disclaimerScript();
        echo ' <span id="ei-disclaimer" title="' . \esc_attr($disclaimer_text) . '" style="cursor:help;border-bottom:1px dashed;font-size:0.9em;">' . $t . '</span>';
    }

    public static function disclaimerScript()
    {
        $script = 'jQuery(document).ready(function (){ jQuery("#ei-disclaimer").click(function () { var $title = jQuery(this).find(".ei-disclaimer-title"); if (!$title.length) { jQuery(this).append(\'<div class="ei-disclaimer-title" style="cursor: default;">\' + jQuery(this).attr("title") + \'</div>\'); } else { $title.remove(); } });});';
        \wp_register_script('ei-disclaimer', '', array('jquery'));
        \wp_enqueue_script('ei-disclaimer');
        \wp_add_inline_script('ei-disclaimer', $script);
    }

    public static function hideOutOfStockPrice($price, $that)
    {
        global $post;

        if (!$product = WooImporter::getProductMeta($post->ID))
            return $price;

        if (!$product->inStock)
            return '';
        else
            return $price;
    }

    public static function customButtonText($default, $product)
    {
        if ($product->get_type() != 'external')
            return $default;

        if (!$item = WooImporter::getProductMeta($product->get_id()))
            return $default;

        if (!$btn_txt = FrontendConfig::getInstance()->option('buy_button_text'))
            return $default;

        return \esc_attr(WooHelper::buildTemplate($btn_txt, $item));
    }

}
