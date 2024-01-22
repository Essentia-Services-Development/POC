<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * AffiliateEggAdmin class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class AffiliateEggAdmin {

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
        \add_action('admin_init', array($this, 'redirect_after_activation'));
        \add_action('admin_notices', array($this, 'activate_notice'));
        \add_action('admin_menu', array($this, 'add_admin_menu'));
        \add_action('admin_enqueue_scripts', array($this, 'admin_load_scripts'));
        \add_filter('parent_file', array($this, 'highlight_admin_menu'));

        if (isset($GLOBALS['pagenow']) && $GLOBALS['pagenow'] == 'plugins.php')
        {
            \add_filter('plugin_row_meta', array($this, 'add_plugin_row_meta'), 10, 2);
        }

        AdminNotice::getInstance()->adminInit();

        if (AffiliateEgg::isFree() || (AffiliateEgg::isPro() && AffiliateEgg::isActivated()) || AffiliateEgg::isEnvato())
        {
            new EggController;
            new AutoblogController;
            DeeplinkConfig::getInstance()->adminInit();
            ProxyConfig::getInstance()->adminInit();
            ExtractorConfig::getInstance()->adminInit();
            CookiesConfig::getInstance()->adminInit();
            GeneralConfig::getInstance()->adminInit();
            new EggThickboxEditorButton;
            LManager::getInstance()->adminInit();
        }
        if (AffiliateEgg::isEnvato() && !AffiliateEgg::isActivated() && !\get_option(AffiliateEgg::slug . '_env_install'))
            EnvatoConfig::getInstance()->adminInit();
        elseif (AffiliateEgg::isPro())
            LicConfig::getInstance()->adminInit();

        if (AffiliateEgg::isPro() && AffiliateEgg::isActivated())
        {
            new Autoupdate(AffiliateEgg::version(), \plugin_basename(PLUGIN_FILE), AffiliateEgg::getApiBase(), AffiliateEgg::slug);
        }
    }

    public function admin_load_scripts()
    {
        if ($GLOBALS['pagenow'] != 'admin.php')
            return;

        if (!empty($_GET['page']))
        {
            $page_pats = explode('-', $_GET['page']);

            if ($page_pats[0] != 'affiliate' && $page_pats[0] != 'affegg')
                return;
        }

        \wp_enqueue_script('affegg_common', PLUGIN_RES . '/js/common.js', array('jquery'));
        \wp_localize_script('affegg_common', 'affeggL10n', array(
            'are_you_shure' => __('Are you sure?', 'affegg'),
            'use_shortcode' => __('Use shortcode:', 'affegg'),
        ));
    }

    public function add_plugin_row_meta(array $links, $file)
    {
        if ($file == plugin_basename(PLUGIN_FILE) && LicConfig::getInstance()->option('license_key'))
        {
            return array_merge(
                    $links, array(
                '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=affiliate-egg-settings">' . __('Settings', 'affegg') . '</a>',
                '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=affiliate-egg-deeplink-settings">' . __('Deeplink settings', 'affegg') . '</a>',
                    )
            );
        }
        return $links;
    }

    public function add_admin_menu()
    {
        $icon_svg = 'data:image/svg+xml;base64,iVBORw0KGgoAAAANSUhEUgAAABIAAAASCAYAAABWzo5XAAAACXBIWXMAAC4jAAAuIwF4pT92AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAsBJREFUeNqNVF1IVEEYnbuL5ksQZVTQS0RQrwXWQ+EPpW5W6q6SBWEUmkZQ6q7u3Zn5ZvZumWlhSBEiYS+SrvtUESQGkVAg6QqmFIb9bLhGZBoVQSB9c9fFdr2iD4fLvTPznfOd890hUkqSACGIQEgQxOcXZKiE3Xl0llf4jNg3cy35DMLyoxRAuJTaLTff+Xcf/f09h74XAlKwkKaIpEUxSzV+ELb6y4KMF7Le0SLWFc1jr/pO81pvQOIa2FanCACLSe1GA2z+dYB+ba2Hrd1VvHgmh07qhrApEmGhKuFFbQhwkVLbKMjLMtYaOUwH8GBqkw7r57JopKeaH9MNqSGZfXlFJgsgpE1ysXY2h34IVvITXgMJJNhflLHmyQLW78aWDRD2ZFUJVQ2AFPcVSR6f4dUzB+nb617YwvxSw/S0jkuQoVS118Buaiz1KqEtZNAoRv7RwZ4/O8lEHbJTPxAvRu9BjBeznqFS1lHTGLPg/1EwC8SSAns9ptJ1Hhw/smmkzQ07+so5nc2in74dYhPTDj7+Zz+djWb7wjcbYBuLBaMlKMIZMSNvCAgSdrLO0UJ2X6nANvYEK6G0uwqcPdXChSRHOi9AZotPbOQyyaP43KgZuV0Hu+Zw+IJVUKy8Uhu5Xy6BMMNZeC4WUmqkzYN+9J9i9HMeG0TJaWqWTOnxdJIg4knHCylvKLK06GLDl1w68qScu2tjZqYu919ZTnasLUGC56B0Jpu+a9ZhE8gFNassYhZSagIc0iaOsodDJbxdDRyqWaMI/ELYV4At3ibxoKkdF2Hvz0w23eaB7epdXRk6mr8SfIFF481DoQp+fD6Dzr8pYg9GXTw45mSh104eGnNZQK25eO9YEQvhXXX3qk+s40qRuryadJ4+4qL3Ivl0MOpgw1MOOozPsCXyaXgK90RzaXiygD69pkM6/kbkH5jyKDIRbkdeAAAAAElFTkSuQmCC';
        \add_menu_page(__('Storefronts', 'affegg') . ' &lsaquo; Affiliate Egg', 'Affiliate Egg Pro', 'publish_posts', AffiliateEgg::slug, null, $icon_svg);
    }

    public static function render($view_name, $_data = null)
    {
        if (is_array($_data))
            extract($_data, EXTR_PREFIX_SAME, 'data');
        else
            $data = $_data;

        include PLUGIN_PATH . 'application/admin/views/' . TextHelper::clear($view_name) . '.php';
    }

    public function redirect_after_activation()
    {
        if (\get_option('affegg_do_activation_redirect', false))
        {
            delete_option('affegg_do_activation_redirect');
            wp_redirect(get_admin_url(get_current_blog_id(), 'admin.php?page=affiliate-egg'));
        }
    }

    /**
     * Highlight menu for hidden submenu item
     */
    public function highlight_admin_menu($file)
    {
        global $plugin_page;

        // options.php - hidden submenu items
        if ($file != 'options.php' || substr($plugin_page, 0, strlen(AffiliateEgg::slug())) !== AffiliateEgg::slug())
            return $file;

        if (preg_match('/affiliate-egg-(\w+?)-settings/', $plugin_page))
            $plugin_page = 'affiliate-egg-settings';

        return $file;
    }

    public function activate_notice()
    {
        if (!AffiliateEgg::isEnvato())
            return;
        if (AffiliateEgg::isActivated() || !AffiliateEggAdmin::isPluginPage())
            return;

        $link = 'admin.php?page=';
        if (AffiliateEgg::isEnvato())
            $link .= 'affiliate-egg-lic';
        else
            $link .= 'affiliate-egg';
        $uri = \get_admin_url(\get_current_blog_id(), $link);

        echo '<div class="notice notice-error"><p>';
        echo sprintf(__('<a href="%s">Please activate</a> your copy of the Affiliate Egg to receive automatic updates and get direct support.', 'affegg'), $uri);
        echo '</p></div>';
    }

    public static function isPluginPage()
    {
        if ($GLOBALS['pagenow'] != 'admin.php' || empty($_GET['page']))
            return false;

        $page_pats = explode('-', $_GET['page']);
        if (count($page_pats) < 2 || $page_pats[0] . '-' . $page_pats[1] != AffiliateEgg::slug())
            return false;

        return true;
    }

}
