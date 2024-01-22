<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * HelpController class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class HelpController {

    public function __construct()
    {
        \add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    public function add_admin_menu()
    {
        \add_submenu_page(AffiliateEgg::slug, __('Search of products', 'affegg') . ' &lsaquo; Affiliate Egg', __('Search of products', 'affegg'), 'publish_posts', 'affiliate-egg-search', array($this, 'actionSearch'));
        \add_submenu_page(AffiliateEgg::slug, __('Help', 'affegg') . ' &lsaquo; Affiliate Egg', __('Help', 'affegg'), 'manage_options', 'affiliate-egg-help', array($this, 'actionHelp'));
    }

    function actionHelp()
    {
        AffiliateEggAdmin::getInstance()->render('help');
    }

    function actionSearch()
    {
        AffiliateEggAdmin::getInstance()->render('custom_search');
    }

}
