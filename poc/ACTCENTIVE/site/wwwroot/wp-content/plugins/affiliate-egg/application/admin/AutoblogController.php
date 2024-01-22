<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * AutoblogController class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 */
class AutoblogController {

    const slug = 'affiliate-egg-autoblog';

    public function __construct()
    {
        \add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    public function add_admin_menu()
    {
        \add_submenu_page(AffiliateEgg::slug, __('Autobloging', 'affegg') . ' &lsaquo; Affiliate Egg', __('Autobloging', 'affegg'), 'manage_options', self::slug, array($this, 'actionIndex'));
        \add_submenu_page(AffiliateEgg::slug, __('Add autoblog', 'affegg') . ' &lsaquo; Affiliate Egg', __('Add autoblog', 'affegg'), 'manage_options', 'affiliate-egg-autoblog-edit', array($this, 'actionUpdate'));
    }

    function actionIndex()
    {
        if (!empty($_GET['action']) && $_GET['action'] == 'run')
        {
            AutoblogModel::model()->parseAndPost((int) $_GET['id']);
        }
        wp_enqueue_script('custom_script', PLUGIN_RES . '/js/jquery.blockUI.js', array('jquery'));
        AffiliateEggAdmin::getInstance()->render('autoblog_index', array('table' => new AutoblogTable(AutoblogModel::model())));
    }

    function actionUpdate()
    {

        $_POST = array_map('stripslashes_deep', $_POST);

        $default = array(
            'id' => 0,
            'name' => '',
            'url' => '',
            'status' => 1,
            //'duplicate_type' => 1,
            'items_per_check' => 3,
            'check_frequency' => 86400,
            'items_per_post' => 1,
            'post_status' => 1,
            'user_id' => get_current_user_id(),
            'template' => 'egg_item_autoblog',
            'title_tpl' => '%PRODUCT.TITLE% %PRODUCT.MANUFACTURER%',
            'category' => get_option('default_category'),
        );

        $message = '';
        $notice = '';

        if (!empty($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], basename(__FILE__)) && !empty($_POST['item']))
        {
            $item = array();
            $item['id'] = (int) $_POST['item']['id'];
            $item['name'] = trim(strip_tags($_POST['item']['name']));

            if (!$item['id'])
                $item['url'] = trim(strip_tags(esc_url_raw($_POST['item']['url'])));
            else
            {
                $db_item = AutoblogModel::model()->findByPk($_GET['id']);
                $item['url'] = $db_item['url'];
            }

            $item['status'] = absint($_POST['item']['status']);
            $item['items_per_check'] = absint($_POST['item']['items_per_check']);
            $item['check_frequency'] = absint($_POST['item']['check_frequency']);
            $item['items_per_post'] = absint($_POST['item']['items_per_post']);
            $item['post_status'] = absint($_POST['item']['post_status']);
            $item['user_id'] = absint($_POST['item']['user_id']);
            $item['template'] = trim(strip_tags($_POST['item']['template']));
            $item['title_tpl'] = trim(wp_strip_all_tags($_POST['item']['title_tpl']));
            $item['category'] = absint($_POST['item']['category']);
            //$item['duplicate_type'] = absint($_POST['item']['duplicate_type']);

            if (AutoblogModel::model()->validate($item))
            {
                $item['id'] = AutoblogModel::model()->save($item);
                if ($item['id'])
                {
                    $message = __('Task for autoblog was saved', 'affegg') . ' <a href="?page=affiliate-egg-autoblog&action=run&id=' . $item['id'] . '">' . __('Run now', 'affegg') . '</a>';
                } else
                    $notice = __('Error occurred during saving task for autoblog.', 'affegg');
            } else
            {
                $notice = __('Not valid URL of directory or this shop is not supported.', 'affegg');
            }
        } else
        {
            $item = $default;
            if (isset($_GET['id']))
            {
                $item = AutoblogModel::model()->findByPk($_GET['id']);
                if (!$item)
                {
                    $item = $default;
                    $notice = __('Autoblog is not found', 'affegg');
                }
            }
        }

        add_meta_box('autoblog_metabox', 'Autoblog data', array($this, 'egg_form_meta_box_handler'), 'person', 'normal', 'default');
        AffiliateEggAdmin::getInstance()->render('autoblog_edit', array(
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
        AffiliateEggAdmin::getInstance()->render('autoblog_metabox', array('item' => $item, 'templates' => TemplateManager::getInstance()->getEggTemplatesList()));
    }

}
