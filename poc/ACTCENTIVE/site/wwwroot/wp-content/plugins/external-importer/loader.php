<?php

namespace ExternalImporter;
defined( '\ABSPATH' ) || exit;
use ExternalImporter\application\vendor\CVarDumper;

/**
 * AutoLoader class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class AutoLoader {

    private static $base_dir;
    private static $classMap = array(
    );

    public function __construct()
    {

        self::$base_dir = PLUGIN_PATH;
        $this->register_auto_loader();
    }

    public function register_auto_loader()
    {
        spl_autoload_register(array($this, 'autoload'));
    }

    /**
     * Implementations of PSR-4
     * @link: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
     */
    public static function autoload($className)
    {

        $prefix = __NAMESPACE__ . '\\';
        $len = strlen($prefix);

        if (strncmp($prefix, $className, $len) !== 0)
        {
            return;
        }

        if (isset(self::$classMap[$className]))
        {
            include(self::$base_dir . self::$classMap[$className]);
        }

        $relative_class = substr($className, $len);
        $file = self::$base_dir . str_replace('\\', '/', $relative_class) . '.php';

        if (file_exists($file))
        {
            require $file;
        }
    }

}

new AutoLoader();

function prn($var, $depth = 10, $highlight = true)
{
    echo CVarDumper::dumpAsString($var, $depth, $highlight);
    echo '<br />';
}

function prnx($var, $depth = 10, $highlight = true)
{
    echo CVarDumper::dumpAsString($var, $depth, $highlight);
    die('Exit');
}
