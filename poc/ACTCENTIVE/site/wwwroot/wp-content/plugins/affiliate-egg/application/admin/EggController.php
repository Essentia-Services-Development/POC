<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * EggController class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class EggController {

    public function __construct()
    {
        \add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    public function add_admin_menu()
    {
        $capability = \apply_filters('affegg_storefront_capability', 'publish_posts');
        \add_submenu_page(AffiliateEgg::slug, __('Storefronts', 'affegg') . ' &lsaquo; Affiliate Egg', __('Storefronts', 'affegg'), $capability, AffiliateEgg::slug, array($this, 'actionIndex'));
        \add_submenu_page(AffiliateEgg::slug, __('Add storefront', 'affegg') . ' &lsaquo; Affiliate Egg', __('Add storefront', 'affegg'), $capability, 'affiliate-egg-edit', array($this, 'actionUpdate'));
    }

    function actionIndex()
    {
        if (!empty($_GET['action']) && !empty($_GET['id']))
        {
            $id = (int) $_GET['id'];
            switch ($_GET['action'])
            {
                case 'update_products':
                    $this->ownerFilter($id);
                    EggModel::model()->forcedUpdateProducts($id);
                    break;
                case 'update_catalogs':
                    $this->ownerFilter($id);
                    EggModel::model()->forcedUpdateCatalogs($id);
                    break;
            }
        }

        \wp_enqueue_script('custom_script', PLUGIN_RES . '/js/jquery.blockUI.js', array('jquery'));
        if (\apply_filters('affegg_ignore_owner_filter', false))
            $owner_check = false;
        else
            $owner_check = true;

        AffiliateEggAdmin::getInstance()->render('eggs_index', array(
            'table' => new EggTable(EggModel::model(), array('owner_check' => $owner_check, 'numeric_search' => true))));
    }

    function actionUpdate()
    {
        $_POST = array_map('stripslashes_deep', $_POST);

        $default = array(
            'id' => 0,
            'name' => '',
            'template' => 'egg_grid_3cols',
            'prod_limit' => 100,
            'urls' => '',
        );

        $message = '';
        $notice = '';

        if (!empty($_POST['nonce']) && \wp_verify_nonce($_POST['nonce'], basename(__FILE__)) && !empty($_POST['item']))
        {
            $this->ownerFilter((int) $_POST['item']['id']);

            @set_time_limit(600);
            $item = array();
            $item['id'] = (int) $_POST['item']['id'];
            $item['update_date'] = current_time('mysql');
            $item['name'] = trim(strip_tags($_POST['item']['name']));
            $item['prod_limit'] = absint($_POST['item']['prod_limit']);
            $item['template'] = trim(strip_tags($_POST['item']['template']));
            if ($item['prod_limit'] > EggManager::TOTAL_PRODUCT_LIMIT)
                $item['prod_limit'] = EggManager::TOTAL_PRODUCT_LIMIT;

            if (EggModel::model()->validate($item))
            {
                $item['id'] = EggModel::model()->save($item);
                if ($item['id'])
                {
                    $urls = explode("\r\n", trim($_POST['item']['urls']));
                    $urls = \apply_filters('affegg_storefront_urls', $urls);
                    EggManager::getInstance()->updateUrls($urls, $item['id'], $item['prod_limit']);

                    $item['urls'] = join("\r\n", EggManager::getInstance()->getFormattedUrls($item['id']));
                    $message = __('Storefront was saved', 'affegg') . ' <a href="' . get_admin_url(get_current_blog_id()) . 'admin.php?page=affiliate-egg-edit">' . __('Add new', 'affegg') . '</a>.';
                } else
                    $notice = __('Error occurred while saving storefront', 'affegg');
            } else
            {
                $notice = __('Not valid data for storefront', 'affegg');
            }
        } else
        {
            $item = $default;
            if (isset($_GET['id']))
            {

                $this->ownerFilter((int) $_GET['id']);

                $item = EggModel::model()->findByPk((int) $_GET['id']);
                if (!$item)
                {
                    $item = $default;
                    $notice = __('Storefront is not found', 'affegg');
                } else
                    $item['urls'] = join("\r\n", EggManager::getInstance()->getFormattedUrls($item['id']));
            }
        }

        \wp_enqueue_script('custom_script', PLUGIN_RES . '/js/jquery.blockUI.js', array('jquery'));

        \add_meta_box('egg_form_meta_box', 'Egg data', array($this, 'egg_form_meta_box_handler'), 'person', 'normal', 'default');
        AffiliateEggAdmin::getInstance()->render('edit_egg', array(
            'item' => $item,
            'notice' => $notice,
            'message' => $message,
            'nonce' => wp_create_nonce(basename(__FILE__)),
        ));
    }

    /**
     * This function renders our custom meta box
     * $item is row
     *
     * @param $item
     */
    function egg_form_meta_box_handler($item)
    {
        AffiliateEggAdmin::getInstance()->render('edit_egg_meta_box', array('item' => $item, 'templates' => TemplateManager::getInstance()->getEggTemplatesList()));
    }

    private function ownerFilter($egg_id)
    {
        if (\apply_filters('affegg_ignore_owner_filter', false))
            return;

        if (!$egg_id || is_super_admin())
            return;
        $egg = EggModel::model()->findByPk($egg_id);
        if (!$egg || $egg['user_id'] != get_current_user_id())
        {
            \wp_die(__('You do not have sufficient permissions to access this page.', 'default'));
        }
    }

}
