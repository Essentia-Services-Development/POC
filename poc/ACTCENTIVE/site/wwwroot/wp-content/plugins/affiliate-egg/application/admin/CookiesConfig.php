<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * CookiesConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class CookiesConfig extends Config {

    public function page_slug()
    {
        return 'affiliate-egg-cookies-settings';
    }

    public function option_name()
    {
        return 'affegg_cookies';
    }

    public function add_admin_menu()
    {
        \add_submenu_page('options.php', __('Custom Cookies', 'affegg') . ' &lsaquo; Affiliate Egg', __('Custom Cookies', 'affegg'), 'manage_options', $this->page_slug, array($this, 'settings_page'));
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
