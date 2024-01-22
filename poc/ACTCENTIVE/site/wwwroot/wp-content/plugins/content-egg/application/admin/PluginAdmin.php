<?php

namespace ContentEgg\application\admin;

defined('\ABSPATH') || exit;

use ContentEgg\application\Plugin;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\admin\GeneralConfig;
use ContentEgg\application\components\ModuleManager;
use ContentEgg\application\components\ModuleApi;
use ContentEgg\application\components\LManager;
use ContentEgg\application\components\ReviewNotice;
use ContentEgg\application\components\FeaturedImage;
use ContentEgg\application\ModuleUpdateScheduler;

/**
 * PluginAdmin class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2023 keywordrush.com
 */
class PluginAdmin
{

    protected static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new self;

        return self::$instance;
    }

    private function __construct()
    {
        if (!\is_admin())
            die('You are not authorized to perform the requested action.');

        \add_action('admin_menu', array($this, 'add_admin_menu'));
        \add_action('admin_enqueue_scripts', array($this, 'admin_load_scripts'));
        \add_filter('parent_file', array($this, 'highlight_admin_menu'));

        if (isset($GLOBALS['pagenow']) && $GLOBALS['pagenow'] == 'plugins.php')
        {
            \add_filter('plugin_row_meta', array($this, 'add_plugin_row_meta'), 10, 2);
        }

        AdminNotice::getInstance()->adminInit();
        if (!Plugin::isFree())
            LManager::getInstance()->adminInit();

        if (Plugin::isFree())
            ReviewNotice::getInstance()->adminInit();

        if (Plugin::isFree() || (Plugin::isPro() && Plugin::isActivated()) || Plugin::isEnvato())
        {
            GeneralConfig::getInstance()->adminInit();
            ModuleManager::getInstance()->adminInit();
            new ModuleSettingsContoller;
            new ProductController;
            new EggMetabox;
            new ModuleApi;
            new FeaturedImage;
            new PrefillController;
            new AutoblogController;
            new ToolsController;
            new ImportExportController;
            AeIntegrationConfig::getInstance()->adminInit();
            ModuleUpdateScheduler::addScheduleEvent();
        }

        if (Plugin::isEnvato() && !Plugin::isActivated() && !\get_option(Plugin::slug . '_env_install'))
            EnvatoConfig::getInstance()->adminInit();
        elseif (Plugin::isPro())
            LicConfig::getInstance()->adminInit();

        if (Plugin::isPro() && Plugin::isActivated())
        {
            new \ContentEgg\application\Autoupdate(Plugin::version(), plugin_basename(\ContentEgg\PLUGIN_FILE), Plugin::getApiBase(), Plugin::slug);
        }
    }

    function admin_load_scripts()
    {
        if ($GLOBALS['pagenow'] != 'admin.php' || empty($_GET['page']))
            return;

        $page_pats = explode('-', sanitize_key(wp_unslash($_GET['page'])));

        if (count($page_pats) < 2 || $page_pats[0] . '-' . $page_pats[1] != 'content-egg')
            return;

        \wp_enqueue_script('content_egg_common', \ContentEgg\PLUGIN_RES . '/js/common.js', array('jquery'));
        \wp_localize_script('content_egg_common', 'contenteggL10n', array(
            'are_you_shure' => __('Are you sure?', 'content-egg'),
            'sitelang' => GeneralConfig::getInstance()->option('lang'),
        ));

        \wp_enqueue_style('contentegg-admin', \ContentEgg\PLUGIN_RES . '/css/admin.css', null, '' . Plugin::version());
    }

    public function add_plugin_row_meta(array $links, $file)
    {
        if ($file == plugin_basename(\ContentEgg\PLUGIN_FILE) && (Plugin::isActivated() || Plugin::isFree()))
        {
            return array_merge(
                $links,
                array(
                    '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=content-egg">' . __('Settings', 'content-egg') . '</a>',
                )
            );
        }
        return $links;
    }

    public function add_admin_menu()
    {
        $icon_svg = 'data:image/svg+xml;base64,iVBORw0KGgoAAAANSUhEUgAAABMAAAAUCAYAAABvVQZ0AAAACXBIWXMAAC4jAAAuIwF4pT92AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA1dJREFUeNqNVElMU1EUfQShIIkYNUETBkUIRoNGRUwxYWMUQoxGwSnCBkIUcGRuS39/p99+OkBpy9gCZSodgIImQIACKtGtG+OGhIXRBFmQ4EKjpt77BVILDSzu4v/33rnnnnvuJTRNE/+QQFAQWoGIGCtrSZGeTeMPtDPZVlMpTVHEXFlDpBIJdy/w7RYgjHphHamSy2KSPLaXofPu73unHcthXtdq1Izz882WxnwNJJJSWwG3gOHFhxrVWfJ25Gfcq15vsVadIabpqGqF7MiVTnMVeTfqS+9v02trRYQOYPgfkEpUR14winiy4PFl9LbKdbVCwgJLOZSnFItJQ42APFErUyCR73KnqUIL59uCIW0E2zdpXzzltNrwoX8pGxJggkIde5EsjPgAOJGBNxJ/MPzALFkWY0norGtNXicmcjG1rcj4DxOlOiydcaM9MyiLBMrdBJNBGVKKCgn1utZutDTeD6QfCKaEZNVy2X4y5/5VWs8kM/DNgeEh6pJrbri1Z8a5isD4zYqChxqiuaKGnHBZB9P625o4dhtg9UIROTbSPX56sMOKmjxo0l7Na264nWcOHvkG7e0zDoslcmrwiwKYQWWEKEAbGoTmTTu+3THprmOJicPd49FTjqXoSXvQgEYtHZoY+Ai6vRbIpBFYKseqnFHEhMy6f0D9KagHWgGT7Cb04Dc5kMGp4Vr9SKM6iWAVjDwGL+C47BQySsI1LhfkeK5SxnHMtAIhecwyx8n80J+nauVR5XpndgoZJKUAFN757hl1OYZqASFoOpGUDoc2/75r1F3DsiW7AMOkIM9htEcZyyRpoFxuvtAKsaM9s/Ee27QeyqaDbAV/r+FWyWk3FKOdsJNocu4AfVOoZy+Q92O+gkZNJjp8Y3y2A1LAnGJFPK9zhd/bqtz0GXcBmOCP9L42DW4FAL6EgGqRmBMamWJgl1GGeribMtzl5HldK/A/bGP0NrPhUOMlfl8riwxTXVZbiUZ17t8CEBMVaETRkvACgyb74KT9A2/OvQIbJkEdOOibgOsMi3QsP2GsZzrC61o+MDHwCYz5JnasZwHMuhjpdX49b+8w18GO8wcKumkNUKIJ1vMzsEqmrUWUPNztSRnqcmVZTWXQ+ciW8hpOAipAz788QXuyosK+HQAAAABJRU5ErkJggg==';
        $title = 'Content Egg';
        if (Plugin::isPro())
            $title .= ' Pro';
        \add_menu_page($title, $title, 'publish_posts', Plugin::slug, null, $icon_svg);
    }

    public static function render($view_name, $_data = null)
    {
        if (is_array($_data))
            extract($_data, EXTR_PREFIX_SAME, 'data');
        else
            $data = $_data;

        include \ContentEgg\PLUGIN_PATH . 'application/admin/views/' . TextHelper::clear($view_name) . '.php';
    }

    /**
     * Highlight menu for hidden submenu item
     */
    function highlight_admin_menu($file)
    {
        global $plugin_page;

        // options.php - hidden submenu items
        if ($file != 'options.php' || substr($plugin_page, 0, strlen(Plugin::slug())) !== Plugin::slug())
            return $file;

        $page_parts = explode('--', $plugin_page);
        if (count($page_parts) > 1)
        {
            $plugin_page = $page_parts[0];
        }
        else
            $plugin_page = Plugin::slug();

        return $file;
    }
}
