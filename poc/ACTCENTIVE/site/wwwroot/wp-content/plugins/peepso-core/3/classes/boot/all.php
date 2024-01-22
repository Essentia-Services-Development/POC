<?php

$files = array_diff(scandir(dirname(__FILE__)), array('..', '.', 'index.html'));

foreach ($files as $file) {
    if(!is_dir($file) && file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . $file)) {
        if(strtolower(substr($file, -4,4)) == '.php') {
            require_once($file);
        }
    }
}
