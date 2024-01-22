<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * DeeplinkConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class DeeplinkConfig extends Config {

    public function page_slug()
    {
        return 'affiliate-egg-deeplink-settings';
    }

    public function option_name()
    {
        return 'affegg_deeplink';
    }

    public function add_admin_menu()
    {
        \add_submenu_page('options.php', __('Affiliate Links', 'affegg') . ' &lsaquo; Affiliate Egg', __('Affiliate Links', 'affegg'), 'manage_options', $this->page_slug, array($this, 'settings_page'));
    }

    protected function options()
    {
        $options = array();
        $shops = ShopManager::getInstance()->getActiveItems(true, true, true);
        foreach ($shops as $shop)
        {
            $options[$shop->id] = array(
                'title' => $shop->getName(true, true),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                    'allow_empty',
                    NS . 'Cpa::deeplinkPrepare'
                ),
                'section' => 'Default parsers',
            );
            if ($shop->isCustom())
                $options[$shop->id]['section'] = 'Custom parsers';
            elseif ($shop->isDeprecated())
                $options[$shop->id]['section'] = 'Deprecated parsers';
        }
        return $options;
    }

    public function settings_page()
    {
        AffiliateEggAdmin::getInstance()->render('settings', array('page_slug' => $this->page_slug()));
    }

}
