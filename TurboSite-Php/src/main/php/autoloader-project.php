<?php

/**
 * This autoloader must be included by all the site-php projects in order for the project class loading to work.
 *
 * Register the autoload method that will locate and automatically load the classes
 */
spl_autoload_register(function($className){

    // This autoloader will only load php classes which namespace begins with "project\\src".
    // This is the namespace that is mandatory for all the classes that are defined on the site_php project itself
    if(strpos($className, 'project\\src\\') === 0 && !class_exists($className)){

        // Replace all slashes to the correct OS directory separator
        $classPath = str_replace('\\', DIRECTORY_SEPARATOR, str_replace('/', DIRECTORY_SEPARATOR, $className));

        // Remove unwanted classname path parts
        $classPath = explode('src'.DIRECTORY_SEPARATOR.'main'.DIRECTORY_SEPARATOR, $classPath);

        $file = dirname($_SERVER["SCRIPT_FILENAME"]).DIRECTORY_SEPARATOR.array_pop($classPath).'.php';

        if(is_file($file)){

            require_once $file;
        }
    }
});

?>