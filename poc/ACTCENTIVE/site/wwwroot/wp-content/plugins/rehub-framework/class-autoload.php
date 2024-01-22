<?php

namespace Rehub;
defined('ABSPATH') OR exit;

final class Autoload {
    private static $inited = false;

    public function __construct(){
        try {
            spl_autoload_register(array( __CLASS__, 'autoload' ));
        } catch(\Exception $e) {
        }
    }

    public static function autoload($className){
        if(false === strpos($className, __NAMESPACE__.'\\')) {
            return;
        }
        $className = str_replace(__NAMESPACE__.'\\', '', $className);

        $filePath = explode('\\', strtolower($className));
        $fileName = '';
        if(isset($filePath[count($filePath)-1])) {
            $fileName      = strtolower(
                $filePath[count($filePath)-1]
            );
            $fileName      = str_replace(array( '_', '--' ), array( '-', '-' ), $fileName);
            $fileNameParts = explode('-', $fileName);
            if(false !== strpos($fileName, 'trait')) {
                $index = array_search('trait', $fileNameParts);
                unset($fileNameParts[$index]);
                $fileName = implode('-', $fileNameParts);
                $fileName = "trait-{$fileName}.php";
            } else if(false !== strpos($fileName, 'interface')) {
                $index = array_search('interface', $fileNameParts);
                unset($fileNameParts[$index]);
                $fileName = implode('-', $fileNameParts);
                $fileName = "interface-{$fileName}.php";
            } else {
                $fileName = "class-{$fileName}.php";
            }
        }

        $fullPath = trailingslashit(__DIR__);
        for($i = 0; $i < count($filePath)-1; $i++) {
            $fullPath .= trailingslashit($filePath[$i]);
        }
        $fullPath .= $fileName;

        if(stream_resolve_include_path($fullPath)) {
            require_once $fullPath;
        }
    }
}

new Autoload;
