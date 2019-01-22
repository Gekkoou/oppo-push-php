<?php
define('OppoPush_Root', dirname(__FILE__) . '/');
function oppopushAutoload($classname) {
    $parts = explode('\\', $classname);
    $path = OppoPush_Root . 'src/' . implode('/', $parts) . '.php';
    
    if (file_exists($path)) {
        include($path);
    }
}

spl_autoload_register('oppopushAutoload');
