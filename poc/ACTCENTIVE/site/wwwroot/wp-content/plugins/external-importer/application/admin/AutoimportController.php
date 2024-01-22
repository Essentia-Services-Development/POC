<?php

namespace ExternalImporter\application\admin;

defined('\ABSPATH') || exit;

use ExternalImporter\application\Plugin;
use ExternalImporter\application\admin\PluginAdmin;
use ExternalImporter\application\admin\AutoimportTable;
use ExternalImporter\application\models\AutoimportModel;
use ExternalImporter\application\AutoimportSheduler;
use ExternalImporter\application\helpers\TextHelper;
use ExternalImporter\application\helpers\ParserHelper;
use ExternalImporter\application\components\Autoimport;

/**
 * AutoimportController class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class AutoimportController {

    const slug = 'external-importer-autoimport';

    public function __construct()
    {
        \add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    public function add_admin_menu()
    {
        \add_submenu_page(Plugin::slug, __('Auto import', 'external-importer') . ' &lsaquo; ' . Plugin::getName(), __('Auto import', 'external-importer'), 'manage_options', self::slug, array($this, 'actionIndex'));
        \add_submenu_page(Plugin::slug, __('Create auto import', 'external-importer') . ' &lsaquo; ' . Plugin::getName(), __('Create auto import', 'external-importer'), 'manage_options', 'external-importer-autoimport-edit', array($this, 'actionUpdate'));
    }

    public function actionIndex()
    {
        if (!empty($_GET['action']) && $_GET['action'] == 'run')
        {
            @set_time_limit(300);
            Autoimport::run((int) $_GET['id']);
        }
        \wp_enqueue_script('external-importer-blockUI', \ExternalImporter\PLUGIN_RES . '/js/jquery.blockUI.js', array('jquery'));
        $table = new AutoimportTable(AutoimportModel::model());
        $table->prepare_items();
        PluginAdmin::getInstance()->render('autoimport_index', array('table' => $table));
    }

    public function actionUpdate()
    {
        $_POST = array_map('stripslashes_deep', $_POST);

        $default = array(
            'id' => 0,
            'name' => '',
            'status' => 1,
            'recurrency' => 86400,
            'process_products' => 5,
            'listing_url' => '',
            'extra' => array('category' => WooConfig::getInstance()->option('default_category')),
        );

        $message = '';
        $notice = '';

        if (!empty($_POST['nonce']) && \wp_verify_nonce($_POST['nonce'], basename(__FILE__)) && !empty($_POST['item']))
        {
            $item = array();
            $item['id'] = (int) $_POST['item']['id'];
            $item['name'] = trim(strip_tags($_POST['item']['name']));
            $item['status'] = absint($_POST['item']['status']);
            $item['process_products'] = absint($_POST['item']['process_products']);
            $item['recurrency'] = absint($_POST['item']['recurrency']);
            $item['extra'] = $_POST['item']['extra'];

            if (!$item['id'])
            {
                $item['listing_url'] = trim(strip_tags(\wp_sanitize_redirect($_POST['item']['listing_url'])));
                $item['listing_url'] = filter_var($item['listing_url'], FILTER_SANITIZE_URL);
                $valid = self::validateListingUrl($item['listing_url']);
                if ($valid !== true)
                    $notice = sprintf(__('Error: %s', 'external-importer'), $valid);
            } else
                unset($item['listing_url']);

            if (!$notice)
            {
                $redirect_url = \get_admin_url(\get_current_blog_id(), 'admin.php?page=external-importer-autoimport');
                if ($id = AutoimportModel::model()->save($item))
                {
                    $redirect_url = AdminNotice::add2Url($redirect_url, 'autoimport_saved', 'success', $id);
                    AutoimportSheduler::maybeAddScheduleEvent();
                } else
                    $redirect_url = AdminNotice::add2Url($redirect_url, 'autoimport_create_error', 'error');

                \wp_redirect($redirect_url);
                exit;
            }
        } else
        {

            $item = $default;
            if (isset($_GET['id']))
            {
                $item = AutoimportModel::model()->findByPk((int) $_GET['id']);
                if (!$item)
                {
                    $item = $default;
                    $notice = __('Autoimport is not found', 'external-importer');
                } else
                    $item['extra'] = unserialize($item['extra']);
            }
        }

        \add_meta_box('autoimport_metabox', __('Auto import', 'external-importer'), array($this, 'metaboxAutoimportHandler'), 'autoimport_create', 'normal', 'default');

        if (isset($_GET['noheader']))
            require_once(ABSPATH . 'wp-admin/admin-header.php');

        PluginAdmin::getInstance()->render('autoimport_edit', array(
            'item' => $item,
            'notice' => $notice,
            'message' => $message,
            'nonce' => \wp_create_nonce(basename(__FILE__)),
        ));
    }

    public function metaboxAutoimportHandler($item)
    {
        PluginAdmin::getInstance()->render('_metabox_autoimport', array('item' => $item));
    }

    private static function validateListingUrl($url)
    {
        if (!$url)
            return __('Listing URL is required field.', 'external-importer');

        if (!TextHelper::isValidUrl($url))
            return __('The Listing URL is not valid.', 'external-importer');

        try
        {
            $listing = ParserHelper::parseListing($url);
        } catch (\Exception $e)
        {
            return $e->getMessage();
        }

        return true;
    }

}
