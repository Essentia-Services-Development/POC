<?php

namespace ExternalImporter\application;

defined('\ABSPATH') || exit;

use ExternalImporter\application\admin\FrontendConfig;
use ExternalImporter\application\helpers\TextHelper;
use ExternalImporter\application\helpers\InputHelper;
use ExternalImporter\application\components\WooImporter;
use ExternalImporter\application\LinkProcessor;

/**
 * Redirect class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class Redirect {

    const DEFAULT_PREFIX = 'out';

    private static $prefix;

    public static function initAction()
    {
        if (!FrontendConfig::getInstance()->option('local_redirect'))
            return;

        \add_action('template_redirect', array(__CLASS__, 'process'));
    }

    public static function process()
    {
        if (\get_option('permalink_structure'))
        {
            global $wp;
            if (!preg_match("/" . preg_quote(self::getPrefix()) . "\/(\d+)$/", $wp->request, $match))
                return;

            $product_id = (int) $match[1];
        } else
            $product_id = (int) InputHelper::get(self::getPrefix());

        if (!$product_id)
            return;

        if (!$url = WooImporter::getProductUrlMeta($product_id))
            return;

        $affiliate_url = LinkProcessor::generateAffiliateUrl($url);
        $status = (int) FrontendConfig::getInstance()->option('redirect_status');

        \wp_redirect(\esc_url_raw($affiliate_url), $status);
        exit;
    }

    public static function getPrefix()
    {
        if (!self::$prefix)
        {
            $prefix = FrontendConfig::getInstance()->option('redirect_prefix');
            $prefix = TextHelper::clear($prefix);
            if (!$prefix)
                $prefix = self::DEFAULT_PREFIX;
            self::$prefix = $prefix;
        }

        return self::$prefix;
    }

}
