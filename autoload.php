<?php

// Register an auto-loader that works
// for the tests and the demonstration script
spl_autoload_register(
    function ($className) {
        $className = ltrim($className, '\\');
        $filename  = '';
        if (false !== $lastNsPos = strripos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $filename  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace).DIRECTORY_SEPARATOR;
        }
        $filename .= str_replace('_', DIRECTORY_SEPARATOR, $className).'.php';
        require __DIR__."/lib/$filename";
    }
);


