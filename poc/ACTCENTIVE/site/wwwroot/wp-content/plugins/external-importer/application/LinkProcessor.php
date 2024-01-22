<?php

namespace ExternalImporter\application;

defined('\ABSPATH') || exit;

use ExternalImporter\application\admin\DeeplinkConfig;
use ExternalImporter\application\components\WooImporter;
use ExternalImporter\application\components\LinkHandler;
use ExternalImporter\application\admin\FrontendConfig;
use ExternalImporter\application\Redirect;
use ExternalImporter\application\admin\LicConfig;

/**
 * LinkProcessor class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class LinkProcessor {

    public static function initAction()
    {
        $l = LicConfig::getInstance()->option('lic' . 'ense' . '_key');
        if (strlen($l) != 32 && strlen($l) != 36)
            return;
        
        \add_filter('woocommerce_product_add_to_cart_url', array(__CLASS__, 'process'), 10, 2);
    }

    public static function process($permalink, $product)
    {
        if ($product->get_type() != 'external')
            return $permalink;

        if (!$url = WooImporter::getProductUrlMeta($product->get_id()))
            return $permalink;

        // URL changed?
        if ($product->get_product_url() !== $url)
            return $permalink;

        // local redirect enabled?
        if (FrontendConfig::getInstance()->option('local_redirect'))
            return self::generateRedirectedUrl($product->get_id());

        return self::generateAffiliateUrl($url);
    }

    public static function generateRedirectedUrl($product_id)
    {
        $prefix = Redirect::getPrefix();
        if (\get_option('permalink_structure'))
            $path = urlencode($prefix) . '/';
        else
            $path = '?' . urlencode($prefix) . '=';

        $path .= urlencode($product_id);

        return \get_site_url(\get_current_blog_id(), $path);
    }

    public static function generateAffiliateUrl($url)
    {
        if (!$deeplink = DeeplinkConfig::getInstance()->getDeeplinkByUrl($url))
            return $url;

        return LinkHandler::createAffUrl($url, $deeplink);
    }

}
