<?php

namespace ExternalImporter\application;

defined('\ABSPATH') || exit;

use ExternalImporter\application\components\logger\Logger;
use ExternalImporter\application\admin\GeneralConfig;
use ExternalImporter\application\components\Synchronizer;

/**
 * Plugin class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class Plugin {

    const version = '1.9.12';
    const db_version = 25;
    const wp_requires = '4.5.0';
    const product_id = 304;
    const slug = 'external-importer';
    const short_slug = 'exi';
    const name = 'External Importer';
    const api_base = 'https://www.keywordrush.com/api/v1';
    const api_base2 = '';
    const website = 'https://www.keywordrush.com';
    const supportUri = 'https://www.keywordrush.com/contact';
    const panelUri = 'https://www.keywordrush.com/panel';

    private static $instance = null;
    private static $is_pro = null;
    private static $is_envato = null;

    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new self;

        return self::$instance;
    }

    private function __construct()
    {
        self::initLogger();
        $this->loadTextdomain();
        if (self::isPro() && self::isActivated())
        {
            if (class_exists('\WooCommerce'))
            {
                GalleryScheduler::initAction();
                Synchronizer::initAction();
                SyncScheduler::initAction();
                SyncFrontend::initAction();
                AutoimportSheduler::initAction();
                LinkProcessor::initAction();
                ExternalImage::initAction();
                FrontendViewer::initAction();
                Redirect::initAction();
            }
        }
        if (Plugin::isPro() && Plugin::isActivated())
            new Autoupdate(Plugin::version(), \plugin_basename(\ExternalImporter\PLUGIN_FILE), Plugin::getApiBase(), Plugin::slug);
    }

    static public function version()
    {
        return self::version;
    }

    static public function slug()
    {
        return self::slug;
    }

    public static function getApiBase()
    {
        return self::api_base;
    }

    public static function isFree()
    {
        return !self::isPro();
    }

    public static function isPro()
    {
        if (self::$is_pro === null)
        {
            if (class_exists("\\ExternalImporter\\application\\Autoupdate", true))
                self::$is_pro = true;
            else
                self::$is_pro = false;
        }
        return self::$is_pro;
    }

    public static function isEnvato()
    {
        if (self::$is_envato === null)
        {
            if (class_exists("\\ExternalImporter\\application\\admin\\EnvatoConfig", true) || \get_option(Plugin::slug . '_env_install'))
                self::$is_envato = true;
            else
                self::$is_envato = false;
        }
        return self::$is_envato;
    }

    public static function isActivated()
    {
        if (self::isPro() && \ExternalImporter\application\admin\LicConfig::getInstance()->option('license_key'))
            return true;
        else
            return false;
    }

    public static function isInactiveEnvato()
    {
        if (self::isEnvato() && !self::isActivated())
            return true;
        else
            return false;
    }

    public static function apiRequest($params = array())
    {
        $api_urls = array(self::api_base);
        if (self::api_base2)
            $api_urls[] = self::api_base2;

        foreach ($api_urls as $api_url)
        {
            $response = \wp_remote_post($api_url, $params);
            if (\is_wp_error($response))
                continue; // try alternative api uri

            $response_code = (int) \wp_remote_retrieve_response_code($response);
            if ($response_code == 200)
                return $response;
            else
                return false;
        }
        return false;
    }

    public static function getSlug()
    {
        return self::slug;
    }

    public static function getShortSlug()
    {
        return self::short_slug;
    }

    public static function getName()
    {
        return self::name;
    }

    public static function getWebsite()
    {
        return self::website;
    }

    public static function getDocsUrl()
    {
        return self::getWebsite() . '/docs/external-importer/';
    }

    public static function logger()
    {
        return Logger::getInstance();
    }

    public static function initLogger()
    {
        $logger = self::logger();

        //email
        $email_target = GeneralConfig::getInstance()->option('log_target_email');
        $levels = '';
        if ($email_target == 'error')
            $levels = array(Logger::LEVEL_ERROR);
        elseif ($email_target == 'warning_error')
            $levels = array(Logger::LEVEL_ERROR, Logger::LEVEL_WARNING);
        elseif ($email_target == 'all')
            $levels = '';
        else
            $logger->getDispatcher()->targets['email']->enabled = false;

        if ($logger->getDispatcher()->targets['email']->enabled)
        {
            $logger->getDispatcher()->targets['email']->levels = $levels;
            $logger->getDispatcher()->targets['email']->config['to'] = \get_bloginfo('admin_email');
        }

        //db
        $db_target = GeneralConfig::getInstance()->option('log_target_db');
        if ($db_target == 'all_without_debug')
            $levels = array(Logger::LEVEL_ERROR, Logger::LEVEL_INFO, Logger::LEVEL_WARNING);
        else
            $levels = array(Logger::LEVEL_DEBUG, Logger::LEVEL_ERROR, Logger::LEVEL_INFO, Logger::LEVEL_WARNING);
        if ($logger->getDispatcher()->targets['db']->enabled)
            $logger->getDispatcher()->targets['db']->levels = $levels;
    }

    private function loadTextdomain()
    {
        \load_plugin_textdomain('external-importer', false, \ExternalImporter\PLUGIN_PATH . 'languages');
    }

    public static function isDevEnvironment()
    {
        if (defined('EXTERNAL_IMPORTER_DEBUG') && EXTERNAL_IMPORTER_DEBUG)
            return true;
        else
            return false;
    }

    public static function getPluginDomain()
    {
        return 'https://www.keywordrush.com/';
    }

    public static function pluginSiteUrl()
    {
        return self::getPluginDomain() . 'externalimporter?utm_source=ei&utm_medium=referral&utm_campaign=plugin';
    }

}
