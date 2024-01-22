<?php

namespace ContentEgg;

use ContentEgg\application\vendor\CVarDumper;

defined( '\ABSPATH' ) || exit;

/**
 * AutoLoader class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class AutoLoader {

    const NS_CUSTOM_MODULES = 'ContentEggCustomModule';

    private static $base_dir;
    private static $classMap = array(
            //'ContentEgg\application\ContentEgg' => 'application/ContentEgg.php',
    );

    public function __construct()
    {

        self::$base_dir = PLUGIN_PATH;
        $this->register_auto_loader();
    }

    public function register_auto_loader()
    {
        spl_autoload_register(array($this, 'autoload'));
        if (defined('\CONTENT_EGG_CUSTOM_MODULES') && \CONTENT_EGG_CUSTOM_MODULES)
            spl_autoload_register(array($this, 'autoloadCustomModule'));
    }

    /**
     * Implementations of PSR-4
     * @link: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
     */
    public static function autoload($className)
    {
        $prefix = __NAMESPACE__ . '\\';
        // does the class use the namespace prefix?
        $len = strlen($prefix);

        if (strncmp($prefix, $className, $len) !== 0)
        {
            // no, move to the next registered autoloader
            return;
        }

        // trying map autoloader first
        if (isset(self::$classMap[$className]))
        {
            include(self::$base_dir . self::$classMap[$className]);
        }

        // get the relative class name
        $relative_class = substr($className, $len);

        // replace the namespace prefix with the base directory, replace namespace
        // separators with directory separators in the relative class name, append
        // with .php
        $file = self::$base_dir . str_replace('\\', '/', $relative_class) . '.php';

        // if the file exists, require it
        if (file_exists($file))
        {
            require $file;
        }
    }

    public static function autoloadCustomModule($className)
    {
        $prefix = self::NS_CUSTOM_MODULES . '\\';
        $len = strlen($prefix);

        if (strncmp($prefix, $className, $len) !== 0)
        {
            return;
        }

        $relative_class = substr($className, $len);
        $path = \WP_CONTENT_DIR . DIRECTORY_SEPARATOR . CUSTOM_MODULES_DIR . DIRECTORY_SEPARATOR;
        $file = $path . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file))
        {
            require $file;
        }
    }

}

new AutoLoader();

function prn($var, $depth = 10, $highlight = true)
{
    echo CVarDumper::dumpAsString($var, $depth, $highlight); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo '<br />';
}

function prnx($var, $depth = 10, $highlight = true)
{
    echo CVarDumper::dumpAsString($var, $depth, $highlight); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    die('Exit');
}
