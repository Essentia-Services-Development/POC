<?php

namespace ContentEgg\application\admin;

defined('\ABSPATH') || exit;

use ContentEgg\application\Plugin;
use ContentEgg\application\models\ProductModel;
use ContentEgg\application\helpers\TemplateHelper;

/**
 * ProductController class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class ProductController {

    const slug = 'content-egg-product';

    public function __construct()
    {
        \add_action('admin_menu', array($this, 'add_admin_menu'));
        \add_action('admin_init', array($this, 'remove_http_referer'));
    }

    public function remove_http_referer()
    {
        global $pagenow;

        // If we're on an admin page with the referer passed in the QS, prevent it nesting and becoming too long.
        if ($pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page'] == 'content-egg-product' && !empty($_GET['_wp_http_referer']) && isset($_SERVER['REQUEST_URI'] ))
        {
            \wp_safe_redirect(\remove_query_arg(array('_wp_http_referer', '_wpnonce'), esc_url_raw(\wp_unslash($_SERVER['REQUEST_URI']))));
            exit;
        }
    }

    public function add_admin_menu()
    {
        \add_submenu_page(Plugin::slug, __('Products', 'content-egg') . ' &lsaquo; Content Egg', __('Products', 'content-egg'), 'publish_posts', self::slug, array($this, 'actionIndex'));
    }

    public function actionIndex()
    {
        \wp_enqueue_script('content-egg-blockUI', \ContentEgg\PLUGIN_RES . '/js/jquery.blockUI.js', array('jquery'));

        if (isset($_GET['action']) && $_GET['action'] === 'scan')
            $forced = true;
        else
            $forced = false;
        ProductModel::model()->maybeScanProducts($forced);
        $table = new ProductTable(ProductModel::model());
        $table->prepare_items();

        $last_scaned = ProductModel::model()->getLastSync();
        if (time() - $last_scaned <= 3600)
            $last_scaned_str = sprintf(__('%s ago', '%s = human-readable time difference', 'content-egg'), \human_time_diff($last_scaned, time()));
        else
            $last_scaned_str = TemplateHelper::dateFormatFromGmt($last_scaned, true);

        PluginAdmin::getInstance()->render('product_index', array('table' => $table, 'last_scaned_str' => $last_scaned_str));
    }

}
