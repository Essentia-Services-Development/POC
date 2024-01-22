<?php

namespace ContentEgg\application\admin;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\ModuleManager;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\components\ContentManager;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\components\ContentCoupon;
use ContentEgg\application\components\ExtraData;
use ContentEgg\application\components\LManager;
use ContentEgg\application\Plugin;

/**
 * EggMetabox class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class EggMetabox {

    private $app_params = array();

    public function __construct()
    {
        if (Plugin::isActivated() && LManager::isNulled())
            return;

        \add_action('add_meta_boxes', array($this, 'addMetabox'));
        \add_action('save_post', array($this, 'saveMeta'));
    }

    private function addAppParam($param, $value)
    {
        $this->app_params[$param] = $value;
    }

    private function getAppParams()
    {
        return $this->app_params;
    }

    public function addMetabox($post_type)
    {
        if (!in_array($post_type, GeneralConfig::getInstance()->option('post_types')))
            return;

        if (!ModuleManager::getInstance()->getModules(true))
        {
            \add_meta_box('content_meta_box', 'Content Egg', array($this, 'renderBlankMetabox'), $post_type, 'normal', 'high');
            return;
        }
        $this->modulesOptionsInit();
        $this->metadataInit();
        $title = 'Content Egg';
        if (Plugin::isFree())
            $title .= '&nbsp;&nbsp;&nbsp;<a target="_blank" href="' . Plugin::pluginSiteUrl() . '">' . __('Upgrade to PRO Version', 'content-egg') . '</a>';
        else
            $title .= ' Pro';

        $title .= '&nbsp;<a target="_blank" href="' . Plugin::pluginDocsUrl() . '">' . __('user guide', 'content-egg') . '</a>';

        \add_meta_box('content_meta_box', $title, array($this, 'renderMetabox'), $post_type, 'normal', 'high');
        $this->angularInit();
    }

    /**
     * Render Meta Box content.
     *
     * @param WP_Post $post The post object.
     */
    public function renderMetabox($post)
    {
        echo '<div ng-app="contentEgg" class="egg-container" id="content-egg" ng-cloak>';
        echo '<div ng-controller="ContentEggController" class="container-fluid">';

        PluginAdmin::render('metabox_general');

        $modules = ModuleManager::getInstance()->getModules(true);
        $module_ids = \apply_filters('content_egg_metabox_modules', array_keys($modules));
        $modules = array_intersect_key($modules, array_flip($module_ids));

        foreach ($modules as $module)
        {
            $module->enqueueScripts();
            $module->renderMetaboxModule();
        }
        echo '</div>';
        echo '</div>';
    }

    public function renderBlankMetabox($post)
    {
        esc_attr_e('Configure and activate modules of Content Egg plugin', 'content-egg');
    }

    private function metadataInit()
    {
        global $post;

        $modules = ModuleManager::getInstance()->getModules(true);

        // modules data
        $init_data = array();
        $init_productGroups = array();
        foreach ($modules as $module)
        {
            $post_meta = ContentManager::getData($post->ID, $module->getId());

            if (!$post_meta)
                continue;
            foreach ($post_meta as $key => $meta)
            {
                if (!empty($meta['description']) && !TextHelper::isHtmlTagDetected($meta['description']))
                    $post_meta[$key]['description'] = TextHelper::br2nl($meta['description']);

                if ($module->getId() == 'Coupon')
                {
                    if (!empty($post_meta[$key]['startDate']))
                        $post_meta[$key]['startDate'] *= 1000;
                    if (!empty($post_meta[$key]['endDate']))
                        $post_meta[$key]['endDate'] *= 1000;
                }
                if (!empty($meta['group']) && !in_array($meta['group'], $init_productGroups))
                    $init_productGroups[] = $meta['group'];
            }
            $init_data[$module->getId()] = array_values($post_meta);
        }
        $this->addAppParam('initData', $init_data);
        $this->addAppParam('initProductGroups', $init_productGroups);

        // keywords
        $init_keywords = array();
        $init_updateParams = array();
        foreach ($modules as $module)
        {
            if (!$module->isAffiliateParser())
                continue;
            $keywords_meta = \get_post_meta($post->ID, ContentManager::META_PREFIX_KEYWORD . $module->getId(), true);
            if (!$keywords_meta)
                continue;
            $init_keywords[$module->getId()] = $keywords_meta;

            $update_params_meta = \get_post_meta($post->ID, ContentManager::META_PREFIX_UPDATE_PARAMS . $module->getId(), true);
            if (!$update_params_meta)
                continue;
            $init_updateParams[$module->getId()] = $update_params_meta;
        }
        $this->addAppParam('initKeywords', $init_keywords);
        $this->addAppParam('initUpdateParams', $init_updateParams);

        // blank content model
        $content = new ContentProduct;
        $content->extra = new ExtraData;
        $this->addAppParam('contentProduct', $content);

        // blank Coupon
        $coupon = new ContentCoupon;
        $this->addAppParam('contentCoupon', $coupon);
    }

    private function modulesOptionsInit()
    {
        $init_options = array();
        foreach (ModuleManager::getInstance()->getModules(true) as $module)
        {
            $init_options[$module->getId()] = array();
            foreach ($module->getConfigInstance()->options() as $option_name => $option)
            {
                if (isset($option['metaboxInit']) && $option['metaboxInit'])
                {
                    $init_options[$module->getId()][$option_name] = $module->config($option_name);
                }
            }
        }
        $this->addAppParam('modulesOptions', $init_options);
    }

    private function angularInit()
    {
        // Justified gallery jquery plugin
        \wp_enqueue_script('justified-gallery', \ContentEgg\PLUGIN_RES . '/justified_gallery/jquery.justifiedGallery.min.js', array('jquery'), Plugin::version());
        \wp_enqueue_style('justified-gallery', \ContentEgg\PLUGIN_RES . '/justified_gallery/justifiedGallery.min.css');

        // Angular core
        \wp_enqueue_script('angularjs', \ContentEgg\PLUGIN_RES . '/app/vendor/angular.min.js', array('jquery'), null);

        // ContentEgg angular application
        \wp_enqueue_style('contentegg-admin', \ContentEgg\PLUGIN_RES . '/css/admin.css');
        \wp_enqueue_script('angular-ui-bootstrap', \ContentEgg\PLUGIN_RES . '/app/vendor/angular-ui-bootstrap/ui-bootstrap-tpls-2.5.0.min.js', array('angularjs'), Plugin::version);

        \wp_enqueue_script('angular-sortable', \ContentEgg\PLUGIN_RES . '/app/vendor/angular-sortable.js', array('angularjs', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-mouse', 'jquery-ui-sortable'), Plugin::version);
        \wp_register_script('contentegg-metabox-app', \ContentEgg\PLUGIN_RES . '/app/app.js', array('angularjs'), Plugin::version());
        \wp_enqueue_script('contentegg-metabox-service', \ContentEgg\PLUGIN_RES . '/app/ModuleService.js', array('contentegg-metabox-app'), Plugin::version());

        // Bootstrap
        \wp_enqueue_style('egg-bootstrap', \ContentEgg\PLUGIN_RES . '/bootstrap/css/egg-bootstrap.min.css', array(), Plugin::version() . 'd11');
        \wp_enqueue_style('egg-glyphicons', \ContentEgg\PLUGIN_RES . '/bootstrap/css/glyphicons.css', array(), Plugin::version());
        \wp_enqueue_script('bootstrap', \ContentEgg\PLUGIN_RES . '/bootstrap/js/bootstrap.min.js', array('jquery'), Plugin::version());

        // ContentEgg application params
        $this->addAppParam('active_modules', ModuleManager::getInstance()->getModulesIdList(true));
        $this->addAppParam('nonce', \wp_create_nonce('contentegg-metabox'));

        \wp_localize_script('contentegg-metabox-app', 'contentegg_params', $this->getAppParams());
    }

    /**
     * Save the meta when the post is saved.
     *
     * @param int $post_id The ID of the post being saved.
     */
    public function saveMeta($post_id)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;

        if (!isset($_POST['contentegg_nonce']))
            return;

        /*
         * why shouldn't i save metadata when its a revision?
         *
         * Apparently *_post_meta functions will automatically change
         * to parent post id if passed revision post id. So you might modify original post,
         * thinking you are modifying revision.
         * 
          if (\wp_is_post_revision($post_id))
          return;
         * 
         */

        \check_admin_referer('contentegg_metabox', 'contentegg_nonce');

        if (isset($_POST['post_type']) && $_POST['post_type'] == 'page')
        {
            if (!current_user_can('edit_page', $post_id))
                return;
        } else
        {
            if (!current_user_can('edit_post', $post_id))
                return;
        }

        // need stripslashes? wp bug with revision post type?
        if (\wp_is_post_revision($post_id))
            $stripslashes = false;
        else
            $stripslashes = true;

        // keywords for automatic updates
        $keywords = array();
        if (isset($_POST['cegg_updateKeywords']))
        {
            $keywords = array_map('sanitize_text_field', wp_unslash($_POST['cegg_updateKeywords']));
        }
        
        $update_params = array();
        if (isset($_POST['cegg_updateParams']))
        {
            $update_params = array_map('sanitize_text_field', wp_unslash($_POST['cegg_updateParams']));
        }

        foreach ($keywords as $module_id => $keyword)
        {
            if (!ModuleManager::getInstance()->moduleExists($module_id) || !ModuleManager::getInstance()->isModuleActive($module_id))
                continue;

            $module = ModuleManager::getInstance()->factory($module_id);
            if (!$module->isAffiliateParser())
                continue;

            $keyword = \sanitize_text_field($keyword);
            if ($keyword)
            {
                \update_post_meta($post_id, ContentManager::META_PREFIX_KEYWORD . $module_id, $keyword);
                if (isset($update_params[$module_id]))
                {
                    \update_post_meta($post_id, ContentManager::META_PREFIX_UPDATE_PARAMS . $module_id, json_decode($update_params[$module_id], true));
                }
            } else
            {
                \delete_post_meta($post_id, ContentManager::META_PREFIX_KEYWORD . $module_id);
                \delete_post_meta($post_id, ContentManager::META_PREFIX_UPDATE_PARAMS . $module_id);
            }
        }

        // save content data
        if (isset($_POST['cegg_data']))
            $content = wp_unslash($_POST['cegg_data']);  // phpcs:ignore
        else
            $content = array();

        if (!is_array($content))
            return;

        $i = 0;
        foreach ($content as $module_id => $data)
        {
            $module_id = sanitize_text_field($module_id);
            $i++;
            if (!ModuleManager::getInstance()->moduleExists($module_id) || !ModuleManager::getInstance()->isModuleActive($module_id))
                continue;

            $data = json_decode($data, true);
            $data = $this->dataPrepare($data);
            if ($i == count($content))
                $last_iteration = true;
            else
                $last_iteration = false;
            ContentManager::saveData($data, $module_id, $post_id, $last_iteration);
        }
    }

    private function dataPrepare($data)
    {
        if (!is_array($data))
            return array();
        foreach ($data as $i => $d)
        {
            foreach ($d as $key => $value)
            {
                if ($key == 'description' && !TextHelper::isHtmlTagDetected($value))
                    $data[$i][$key] = TextHelper::nl2br($value);
                if ($key == 'price')
                    $data[$i][$key] = (float) $value;
            }
        }
        return $data;
    }

}
