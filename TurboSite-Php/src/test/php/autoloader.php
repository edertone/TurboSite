<?php

/**
 * TurboSite is a web site creation framework.
 *
 * Website : -> http://www.turboframework.org
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2018 Edertone Advanded Solutions. http://www.edertone.com
 */


require_once __DIR__.'/resources/libs/turbocommons-php-2.1.1.phar';
require_once __DIR__.'/resources/libs/turbodepot-php-3.2.0.phar';
require_once __DIR__.'/../../main/php/autoloader.php';


// Register the autoload method that will locate and automatically load the library classes
spl_autoload_register(function($className){

    // Replace all slashes to the correct OS directory separator
    $classPath = str_replace('\\', DIRECTORY_SEPARATOR, str_replace('/', DIRECTORY_SEPARATOR, $className));

    // Remove unwanted classname path parts
    $classPath = explode('src'.DIRECTORY_SEPARATOR.'test'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR, $classPath);
    $classPath = array_pop($classPath).'.php';

    if(file_exists(__DIR__.DIRECTORY_SEPARATOR.$classPath)){

        require_once __DIR__.DIRECTORY_SEPARATOR.$classPath;
    }
});

?>