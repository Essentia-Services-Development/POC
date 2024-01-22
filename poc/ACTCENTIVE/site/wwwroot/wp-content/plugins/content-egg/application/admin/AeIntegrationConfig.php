<?php

namespace ContentEgg\application\admin;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\Config;
use ContentEgg\application\Plugin;

/**
 * AeIntegrationConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class AeIntegrationConfig extends Config {

    const MIN_AE_VERSION = '7.1.0';

    public function page_slug()
    {
        return Plugin::slug . '-ae-integration';
    }

    public function option_name()
    {
        return Plugin::slug . '_ae_integration';
    }

    public function add_admin_menu()
    {
        \add_submenu_page(Plugin::slug, __('Affiliate Egg integration', 'content-egg') . ' &lsaquo; Content Egg', __('Affiliate Egg integration', 'content-egg'), 'manage_options', $this->page_slug(), array($this, 'settings_page'));
        
        if (Plugin::isFree() && !Plugin::isEnvato())
        {
            global $submenu;
            $submenu['content-egg'][] = array('<b style="color: #00C0AC;">Go PRO</b>', 'manage_options', Plugin::pluginSiteUrl());
        }
        
    }

    protected function options()
    {
        if (!self::isAEIntegrationPosible())
            return array();

        $aff_egg_modules = \Keywordrush\AffiliateEgg\ShopManager::getInstance()->getSearchableItemsList(true, false, false);
        return array(
            'modules' => array(
                'title' => __('Activate modules', 'content-egg'),
                'description' => '',
                'checkbox_options' => $aff_egg_modules,
                'callback' => array($this, 'render_checkbox_list'),
                'default' => array(),
                'section' => 'default',
            ),
        );
    }

    public function settings_page()
    {
        PluginAdmin::render('ae_integration', array('page_slug' => $this->page_slug()));
    }

    public static function isAEIntegrationPosible()
    {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');

        if (!\is_plugin_active('affiliate-egg/affiliate-egg.php'))
            return false;

        if (!class_exists('\Keywordrush\AffiliateEgg\ShopManager') || !\Keywordrush\AffiliateEgg\LicConfig::getInstance()->option('license_key'))
            return false;

        $v = \Keywordrush\AffiliateEgg\AffiliateEgg::version();

        if (version_compare(self::MIN_AE_VERSION, $v, '>'))
            return false;

        return true;
    }

}
