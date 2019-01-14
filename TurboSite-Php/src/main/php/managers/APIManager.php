<?php

/**
 * TurboSite is a web site creation framework.
 *
 * Website : -> http://www.turboframework.org
 * License : -> Licensed under the Apache License, Version 2.0. You may not use this file except in compliance with the License.
 * License Url : -> http://www.apache.org/licenses/LICENSE-2.0
 * CopyRight : -> Copyright 2018 Edertone Advanded Solutions. http://www.edertone.com
 */


namespace org\turbosite\src\main\php\managers;

use UnexpectedValueException;
use org\turbocommons\src\main\php\utils\StringUtils;
use org\turbocommons\src\main\php\managers\LocalizationManager;
use org\turbocommons\src\main\php\managers\FilesManager;
use org\turbocommons\src\main\php\model\BaseSingletonClass;
use org\turbocommons\src\main\php\managers\BrowserManager;


/**
 * Manages all the operations related to a REST api
 */
class APIManager extends BaseSingletonClass{


    /**
     * Stores the filesystem location for the index of the project (the point where src/main folder points),
     * to be used when loading other files or resources
     */
    private $_mainPath = '';


    /**
     * TODO
     *
     * @param string $rootProjectPath The filesystem path to the root of the website project src/main folder
     * @param string $apiFolder The api subfolder to which this API manager is binded. All API calls must be performed as
     *        /api/apiFolder/api-operations. This API manager will only accept
     */
    public function processApiURL($rootProjectPath, $apiFolder){

        $this->_mainPath = StringUtils::formatPath(StringUtils::getPath($rootProjectPath));
    }
}

?>