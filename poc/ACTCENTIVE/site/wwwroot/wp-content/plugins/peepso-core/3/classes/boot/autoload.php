<?php

/**
 * Class PeepSo3_Autoload
 *
 * This is the primary PeepSo3 autoloader. It is supposed to be used ONLY ONCE in the PeepSo3 constructor.
 *
 * @TODO @MAYBE somehow cache the list of file paths to avoid constant directory re-scanning
 */
class PeepSo3_Autoload {

    private static $instance;

    public static function get_instance() {
        return isset(self::$instance) ? self::$instance : self::$instance = new self;
    }

    /**
     * Gather primary class paths and require_once everything recursively
     */
    private function __construct()
    {
        $class_paths = array();
        $class_paths[] = dirname(dirname(__FILE__));

        // $class_paths = apply_filters()

        if(count($class_paths)) {
            foreach ($class_paths as $class_path) {
                $this->autoload($class_path);
            }
        }
    }

    /**
     * Load all files in the given path
     * Recursively load sub-directories in the given path
     */
    private function autoload($class_path) {

        if (file_exists($class_path) && is_dir($class_path)) {

            foreach (scandir($class_path) as $file) {

                if(in_array($file, array('.','..'))) { continue; }

                $path = $class_path . DIRECTORY_SEPARATOR . $file;

                if(is_dir($path)) {

                    $this->autoload($path);

                } else {

                    if(defined('PEEPSO_AUTOLOAD_FIX')) {
                        // skip "boot" to avoid issues reported by Alejandro Schmeichler
                        if(strtolower(substr($path, -4,4)) == '.php' && strpos($path, '/boot/') === false) {
                            require_once($path);
                        }
                    } else {
                        if (strtolower(substr($path, -4, 4)) == '.php') {
                            require_once($path);
                        }
                    }
                }
            }
        }
    }
}