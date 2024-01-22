<?php

namespace ContentEgg\application\components;

defined('\ABSPATH') || exit;

use \ContentEgg\application\Plugin;
use \ContentEgg\application\helpers\TextHelper;
use \ContentEgg\application\admin\AeIntegrationConfig;

/**
 * ModuleManager class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class ModuleManager {

    const DEFAULT_MODULES_DIR = 'application/modules';
    const AE_MODULES_PREFIX = 'AE';
    const FEED_MODULES_PREFIX = 'Feed';
    const MAX_NUM_FEED_MODULES = 25;

    private static $modules = array();
    private static $active_modules = array();
    private static $configs = array();
    private static $instance = null;
    // hidden system modules
    private static $hidden_modules = array('AE', 'Feed');
    private static $custom_modules = array();

    public static function getInstance()
    {
        if (self::$instance == null)
        {
            self::$instance = new self;
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->initModules();
    }

    public function adminInit()
    {
        foreach ($this->getConfigurableModules() as $module)
        {
            $config = self::configFactory($module->getId());
            $config->adminInit();
        }
    }

    /**
     *  Highlight the proper submenu item
     */
    public function highlightAdminMenu($parent_file)
    {
        global $plugin_page;

        if (substr($plugin_page, 0, strlen(Plugin::slug())) !== Plugin::slug())
        {
            return $parent_file;
        }

        if ($parent_file == 'options.php' && $plugin_page == 'content-egg-settings-affiliate')
        {
            $plugin_page = 'content-egg-settings-affiliate';
        }

        return $parent_file;
    }

    private function initModules()
    {
        $modules_ids = $this->scanForDefaultModules();

        if (defined('\CONTENT_EGG_CUSTOM_MODULES') && \CONTENT_EGG_CUSTOM_MODULES)
        {
            $modules_ids = array_merge($modules_ids, $this->scanForCustomModules());
        }

        sort($modules_ids);

        $feed_modules_ids = $this->getFeedModules();
        natsort($feed_modules_ids);

        $ae_modules_ids = $this->getAffEggModules();
        sort($ae_modules_ids);

        $modules_ids = array_merge($modules_ids, $feed_modules_ids, $ae_modules_ids);
        $modules_ids = \apply_filters('content_egg_modules', $modules_ids);

        // create modules
        foreach ($modules_ids as $module_id)
        {
            // create module
            self::factory($module_id);
        }

        // fill active modules
        foreach (self::$modules as $module)
        {
            if ($module->isActive())
            {
                self::$active_modules[$module->getId()] = $module;
            }
        }
    }

    private function scanForDefaultModules()
    {
        $path = \ContentEgg\PLUGIN_PATH . self::DEFAULT_MODULES_DIR . DIRECTORY_SEPARATOR;

        return $this->scanForModules($path);
    }

    private function scanForCustomModules()
    {
        $path = \WP_CONTENT_DIR . DIRECTORY_SEPARATOR . \ContentEgg\CUSTOM_MODULES_DIR . DIRECTORY_SEPARATOR;
        if (!is_dir($path))
        {
            return array();
        }

        self::$custom_modules = $this->scanForModules($path);

        return self::$custom_modules;
    }

    private function scanForModules($path)
    {
        $folder_handle = @opendir($path);
        if ($folder_handle === false)
        {
            return;
        }

        $founded_modules = array();

        while (( $m_dir = readdir($folder_handle) ) !== false)
        {
            if ($m_dir == '.' || $m_dir == '..')
            {
                continue;
            }
            $module_path = $path . $m_dir;
            if (!is_dir($module_path))
            {
                continue;
            }

            $module_id = $m_dir;
            if (in_array($module_id, self::$hidden_modules))
            {
                continue;
            }

            $founded_modules[] = TextHelper::clear($module_id);
        }
        closedir($folder_handle);

        return $founded_modules;
    }

    private function getAffEggModules()
    {
        if (!AeIntegrationConfig::isAEIntegrationPosible())
        {
            return array();
        }

        $module_ids = AeIntegrationConfig::getInstance()->option('modules');
        if (!$module_ids)
        {
            return array();
        }
        $result = array();
        foreach ($module_ids as $module_id)
        {
            $result[] = self::AE_MODULES_PREFIX . '__' . $module_id;
        }

        return $result;
    }

    private function getFeedModules()
    {
        $result = array();

        for ($i = 1; $i <= self::MAX_NUM_FEED_MODULES; $i++)
        {
            if (get_option('content-egg_Feed__' . $i))
            {
                $result[] = self::FEED_MODULES_PREFIX . '__' . $i;
            } else
            {
                break;
            }
        }

        if (Plugin::isFree())
        {
            $max = 3;
        } else
        {
            $max = self::MAX_NUM_FEED_MODULES;
        }

        if (count($result) < $max)
        {
            $num = count($result) + 1;
            $result[] = self::FEED_MODULES_PREFIX . '__' . $num;
        }

        return $result;
    }

    public static function isCustomModule($module_id)
    {
        if (in_array($module_id, self::$custom_modules))
        {
            return true;
        } else
        {
            return false;
        }
    }

    public static function factory($module_id)
    {
        if (!isset(self::$modules[$module_id]))
        {
            $path_prefix = Module::getPathId($module_id);
            if (self::isCustomModule($module_id))
            {
                $module_class = "\\ContentEggCustomModule\\" . $path_prefix . "\\" . $path_prefix . 'Module';
            } else
            {
                $module_class = "\\ContentEgg\\application\\modules\\" . $path_prefix . "\\" . $path_prefix . 'Module';
            }

            if (class_exists($module_class, true) === false)
            {
                throw new \Exception("Unable to load module class: '{$module_class}'.");
            }

            $module = new $module_class($module_id);

            if (!( $module instanceof \ContentEgg\application\components\Module ))
            {
                throw new \Exception("The module '{$module_id}' must inherit from Module.");
            }

            if (Plugin::isFree() && !$module->isFree())
            {
                return false;
            }

            self::$modules[$module_id] = $module;
        }

        return self::$modules[$module_id];
    }

    public static function parserFactory($module_id)
    {
        $module = self::factory($module_id);
        if (!( $module instanceof \ContentEgg\application\components\ParserModule ))
        {
            throw new \Exception("The parser module '{$module_id}' must inherit from ParserModule.");
        }

        return $module;
    }

    public static function configFactory($module_id)
    {
        if (!isset(self::$configs[$module_id]))
        {
            $path_prefix = Module::getPathId($module_id);

            if (self::isCustomModule($module_id))
            {
                $config_class = "\\ContentEggCustomModule\\" . $path_prefix . "\\" . $path_prefix . 'Config';
            } else
            {
                $config_class = "\\ContentEgg\\application\\modules\\" . $path_prefix . "\\" . $path_prefix . 'Config';
            }

            if (class_exists($config_class, true) === false)
            {
                throw new \Exception("Unable to load module config class: '{$config_class}'.");
            }

            $config = $config_class::getInstance($module_id);

            if (self::factory($module_id)->isParser())
            {
                if (!( $config instanceof \ContentEgg\application\components\ParserModuleConfig ))
                {
                    throw new \Exception("The parser module config '{$config_class}' must inherit from ParserModuleConfig.");
                }
            } else
            {
                if (!( $config instanceof \ContentEgg\application\components\ModuleConfig ))
                {
                    throw new \Exception("The module config '{$config_class}' must inherit from ModuleConfig.");
                }
            }

            self::$configs[$module_id] = $config;
        }

        return self::$configs[$module_id];
    }

    public function getModules($only_active = false)
    {
        if ($only_active)
        {
            return self::$active_modules;
        } else
        {
            return self::$modules;
        }
    }

    public function getModulesIdList($only_active = false)
    {
        return array_keys($this->getModules($only_active));
    }

    public function getParserModules($only_active = false)
    {
        $modules = $this->getModules($only_active);
        $parsers = array();
        foreach ($modules as $module)
        {
            if ($module->isParser())
            {
                $parsers[$module->getId()] = $module;
            }
        }

        return $parsers;
    }

    public function getAffiliateParsers($only_active = false, $only_product = false)
    {
        $modules = $this->getModules($only_active);
        $parsers = array();
        foreach ($modules as $module)
        {
            if ($only_product && strstr($module->getId(), 'Coupons'))
            {
                continue;
            }

            if (!$module->isAffiliateParser())
            {
                continue;
            }

            $parsers[$module->getId()] = $module;
        }

        return $parsers;
    }

    public function getParserModulesIdList($only_active = false)
    {
        return array_keys($this->getParserModules($only_active));
    }

    public function getParserModulesByTypes($types, $only_active = true)
    {
        if ($types == 'ALL')
        {
            $types = null;
        }

        if ($types && !is_array($types))
        {
            $types = array($types);
        }
        $res = array();
        foreach ($this->getParserModules($only_active) as $module)
        {
            if ($types && !in_array($module->getParserType(), $types))
            {
                continue;
            }
            $res[$module->getId()] = $module;
        }

        return $res;
    }

    public function getParserModuleIdsByTypes($types, $only_active = true)
    {
        return array_keys($this->getParserModulesByTypes($types, $only_active));
    }

    public function getConfigurableModules($active_only = false)
    {
        $result = array();
        foreach ($this->getModules($active_only) as $module)
        {
            if ($module->isConfigurable())
            {
                $result[] = $module;
            }
        }

        return $result;
    }

    public function moduleExists($module_id)
    {
        if (isset(self::$modules[$module_id]))
        {
            return true;
        } else
        {
            return false;
        }
    }

    public function isModuleActive($module_id)
    {
        if (isset(self::$active_modules[$module_id]))
        {
            return true;
        } else
        {
            return false;
        }
    }

    public function getOptionsList()
    {
        $options = array();
        foreach ($this->getConfigurableModules() as $module)
        {
            $config = $module->getConfigInstance();
            $options[$config->option_name()] = $config->getOptionValues();
            //$opt_name = $module->getConfigInstance()->option_name();
            //$options[$opt_name] = \get_option($opt_name);
        }

        return $options;
    }

    public function getItemsUpdateModuleIds()
    {
        $result = array();
        foreach ($this->getAffiliateParsers(true) as $module)
        {
            if (!$module->isItemsUpdateAvailable() || !$module->config('ttl_items'))
            {
                continue;
            }

            if ($module->config('update_mode') == 'cron' || $module->config('update_mode') == 'visit_cron')
            {
                $result[] = $module->getId();
            }
        }

        return $result;
    }

    public function getByKeywordUpdateModuleIds()
    {
        $result = array();
        foreach ($this->getAffiliateParsers(true) as $module)
        {
            if (!$module->config('ttl'))
            {
                continue;
            }

            if ($module->config('update_mode') == 'cron' || $module->config('update_mode') == 'visit_cron')
            {
                $result[] = $module->getId();
            }
        }

        return $result;
    }

    public function getAffiliteModulesList($only_active = true)
    {
        $results = array();
        $modules = ModuleManager::getInstance()->getAffiliateParsers($only_active);
        $feeds = array();
        foreach ($modules as $module_id => $module)
        {
            /*
              if ($module->isFeedParser())
              $feeds[$module_id] = $module->getName();
              else
             *
             */
            $results[$module_id] = $module->getName();
        }

        /*
          if ($feeds)
          {
          array_pop($feeds);
          $results = array_merge($results, $feeds);
          }
         *
         */

        return $results;
    }

}
